<?php

namespace App\Services;

use App\Models\DiseaseScan;
use App\Models\Farm;
use App\Services\Weather\WeatherService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class DiseaseDetectionService
{
    public function __construct(
        private readonly GeminiRecommendationService $geminiService,
        private readonly WeatherService $weatherService
    ) {}

    /**
     * @param  array{farm_id: int, plant_age_days?: int|null, latitude?: float|null, longitude?: float|null}  $data
     */
    public function scan(int $farmerId, UploadedFile $image, array $data): DiseaseScan
    {
        $farm = Farm::query()
            ->whereKey($data['farm_id'])
            ->where('farmer_user_id', $farmerId)
            ->firstOrFail();

        $lat = (float) ($data['latitude'] ?? $farm->latitude ?? -6.3266);
        $lng = (float) ($data['longitude'] ?? $farm->longitude ?? 108.3200);

        $aiResult = $this->detectWithAi($image, [
            'plant_age_days' => $data['plant_age_days'] ?? null,
            'latitude' => $lat,
            'longitude' => $lng,
        ]);

        $path = $image->store('disease-scans', 'public');

        if (! $path) {
            throw new RuntimeException('Foto gagal disimpan.');
        }

        /** @var DiseaseScan $scan */
        $scan = DiseaseScan::query()->create([
            'farmer_id' => $farmerId,
            'farm_id' => $farm->id,
            'image_url' => Storage::disk('public')->url($path),
            'image_hash' => hash_file('sha256', $image->getRealPath()),
            'quality_status' => (string) ($aiResult['image_quality']['status'] ?? $aiResult['confidence_level'] ?? 'model_checked'),
            'predicted_class' => (string) $aiResult['disease_name'],
            'confidence' => (float) $aiResult['confidence'],
            'model_version' => (string) $aiResult['model_version'],
            'scanned_at' => now(),
        ])->load('farm');

        // 1. Ambil data cuaca REAL-TIME berdasarkan koordinat sawah aktual
        $weatherContext = [
            'temperature' => 29.0,
            'humidity' => 78.0,
            'condition' => 'Cerah Berawan',
        ];

        try {
            $weatherResponse = $this->weatherService->getCurrentWeather($lat, $lng);
            if (! empty($weatherResponse['data'])) {
                $parsed = $this->weatherService->parseWeatherData($weatherResponse['data']);
                $weatherContext = [
                    'temperature' => (float) ($parsed['temperature'] ?? 29.0),
                    'humidity' => (float) ($parsed['humidity'] ?? 78.0),
                    'condition' => (string) ($parsed['description'] ?? $parsed['weather'] ?? 'Cerah Berawan'),
                ];
            }
        } catch (\Throwable $e) {
            Log::warning('[Weather Realtime Failed] '.$e->getMessage());
        }

        // 2. Libatkan AI Microservice Python (/treatments/recommend) untuk rekomendasi agronomi terstruktur
        $diseaseCode = (string) $aiResult['disease_code'];
        $aiTreatments = $this->getAiServiceTreatments($diseaseCode, (float) ($scan->confidence ?? 0.90), $weatherContext);
        if ($aiTreatments) {
            $scan->setAttribute('ai_service_treatments', $aiTreatments);
        }

        // 3. Generate Gemini / Agricultural RAG recommendations dengan cuaca real-time
        try {
            $recommendations = $this->geminiService->generateForScan($scan, $weatherContext);
            $scan->setAttribute('gemini_recommendations', $recommendations);
        } catch (\Throwable $e) {
            Log::warning('[Gemini Rec Service] '.$e->getMessage());
        }

        return $scan;
    }

    /**
     * Mengambil rekomendasi penanganan terstruktur dari Python AI Microservice.
     *
     * @param  array{temperature: float, humidity: float, condition: string}  $weatherContext
     * @return array<string, mixed>|null
     */
    private function getAiServiceTreatments(string $diseaseCode, float $confidence, array $weatherContext): ?array
    {
        $baseUrl = rtrim((string) config('services.ai.base_url'), '/');
        if (empty($baseUrl)) {
            return null;
        }

        try {
            $response = Http::timeout((int) config('services.ai.timeout', 15))->post("{$baseUrl}/treatments/recommend", [
                'disease_code' => $diseaseCode,
                'confidence' => min(max($confidence, 0.0), 1.0),
                'plant_age_days' => 45,
                'severity' => $confidence >= 0.85 ? 'tinggi' : 'sedang',
                'affected_area_percentage' => $confidence >= 0.85 ? 30.0 : 15.0,
                'weather_condition' => $weatherContext['condition'] ?? 'Cerah Berawan',
                'actions_already_taken' => [],
            ]);

            if ($response->successful()) {
                $payload = $response->json();

                return is_array($payload) ? ($payload['data'] ?? null) : null;
            }
        } catch (\Throwable $e) {
            Log::info('[AI Service Treatment Recommendation Skip] '.$e->getMessage());
        }

        return null;
    }

    /**
     * Mengirimkan umpan balik pengguna dan mendaftarkan daun ke AI learning memory bank.
     */
    public function submitFeedback(DiseaseScan $scan, string $status, ?string $correctedClass = null, ?string $notes = null): DiseaseScan
    {
        $finalClass = ($status === 'corrected' && ! empty($correctedClass))
            ? $correctedClass
            : ($scan->predicted_class ?? 'Tidak Dapat Dipastikan');

        $scan->update([
            'user_feedback' => $status,
            'verified_class' => $finalClass,
            'feedback_notes' => $notes,
            'is_learned' => true,
        ]);

        // Perbarui rekomendasi obat dan penanganan agar langsung cocok dengan penyakit hasil koreksi
        try {
            $scan->setAttribute('predicted_class', $finalClass);
            $newRecs = $this->geminiService->generateForScan($scan, [
                'temperature' => 29.0,
                'humidity' => 78.0,
                'condition' => 'Normal',
            ]);
            $scan->setAttribute('gemini_recommendations', $newRecs);
        } catch (\Throwable $e) {
            Log::warning('[Regenerate Rec on Feedback Failed] '.$e->getMessage());
        }

        // Kirimkan ke AI microservice agar memori daun diperbarui secara real-time
        $this->learnWithAi($scan, $finalClass, $status === 'corrected' ? 'farmer_corrected' : 'farmer_confirmed');

        return $scan;
    }

    /**
     * Mendaftarkan sampel daun ke AI learning memory bank.
     */
    private function learnWithAi(DiseaseScan $scan, string $diseaseName, string $source): void
    {
        $baseUrl = rtrim((string) config('services.ai.base_url'), '/');
        if (empty($baseUrl)) {
            return;
        }

        try {
            $parsedPath = parse_url($scan->image_url, PHP_URL_PATH);
            $cleanPath = ltrim(str_replace(['/storage/', 'storage/'], '', (string) $parsedPath), '/');
            $imagePath = storage_path('app/public/'.$cleanPath);

            if (! file_exists($imagePath)) {
                $imagePath = public_path('storage/'.$cleanPath);
            }

            if (! file_exists($imagePath)) {
                return;
            }

            $diseaseCode = $this->normalizeDiseaseCode($diseaseName);
            if ($diseaseCode === null) {
                Log::warning('[AI Learning Sync Skipped] Unsupported disease class: '.$diseaseName);

                return;
            }

            Http::timeout((int) config('services.ai.timeout', 5))
                ->attach('image', fopen($imagePath, 'r'), basename($imagePath))
                ->post("{$baseUrl}/diseases/learn", [
                    'disease_code' => $diseaseCode,
                    'disease_name' => $diseaseName,
                    'confidence' => (float) ($scan->confidence ?? 1.0),
                    'source' => $source,
                    'sample_id' => 'scan_'.$scan->id,
                ]);
        } catch (\Throwable $e) {
            Log::warning('[AI Learning Sync Failed] '.$e->getMessage());
        }
    }

    private function normalizeDiseaseCode(string $diseaseName): ?string
    {
        $baseName = trim(explode('(', $diseaseName)[0]);
        $slug = trim(strtolower(preg_replace('/[^a-zA-Z0-9]+/', '_', $baseName)), '_');

        return match ($slug) {
            'bacterial_leaf_blight' => 'bacterial_leaf_blight',
            'bacterial_leaf_streak' => 'bacterial_leaf_streak',
            'bacterial_panicle_blight' => 'bacterial_panicle_blight',
            'blast' => 'blast',
            'brown_spot' => 'brown_spot',
            'dead_heart' => 'dead_heart',
            'downy_mildew' => 'downy_mildew',
            'hispa' => 'hispa',
            'normal', 'healthy', 'padi_sehat' => 'healthy',
            'tungro' => 'tungro',
            default => null,
        };
    }

    /**
     * @param  array{plant_age_days?: int|null, latitude?: float|null, longitude?: float|null}  $context
     * @return array<string, mixed>
     */
    private function detectWithAi(UploadedFile $image, array $context): array
    {
        $baseUrl = rtrim((string) config('services.ai.base_url'), '/');

        $imagePath = $image->getRealPath();
        if (! $imagePath || ! file_exists($imagePath)) {
            throw new RuntimeException('File foto tidak dapat dibaca untuk inferensi AI.');
        }

        if (empty($baseUrl)) {
            Log::warning('[AI Microservice Detection Skipped] AI_SERVICE_URL is empty.');

            return $this->fallbackDetectionResult($image, 'AI service belum dikonfigurasi.');
        }

        try {
            $response = Http::timeout((int) config('services.ai.timeout', 20))
                ->attach(
                    'image',
                    fopen($imagePath, 'r'),
                    $image->getClientOriginalName()
                )
                ->post("{$baseUrl}/diseases/detect", array_filter([
                    'plant_age_days' => $context['plant_age_days'] ?? null,
                    'latitude' => $context['latitude'] ?? null,
                    'longitude' => $context['longitude'] ?? null,
                ], fn ($value) => $value !== null));
        } catch (\Throwable $e) {
            Log::warning('[AI Microservice Detection Failed] '.$e->getMessage());

            return $this->fallbackDetectionResult($image, 'AI service deteksi penyakit belum tersedia.');
        }

        if ($response->clientError()) {
            $payload = $response->json();
            $errorMessage = $payload['error']['message'] ?? $payload['message'] ?? 'Objek pada gambar bukan daun padi. Silakan ambil foto daun padi dengan jelas.';
            throw new \InvalidArgumentException($errorMessage);
        }

        if (! $response->successful()) {
            Log::warning('[AI Microservice Detection Error] HTTP '.$response->status().' : '.$response->body());

            return $this->fallbackDetectionResult($image, 'AI service deteksi penyakit gagal memproses gambar.');
        }

        $payload = $response->json();
        $data = is_array($payload) ? ($payload['data'] ?? null) : null;
        if (! is_array($data)) {
            Log::warning('[AI Microservice Detection Error] Invalid response payload.');

            return $this->fallbackDetectionResult($image, 'AI service tidak mengembalikan data deteksi yang valid.');
        }

        try {
            return $this->normalizeAiDetectionResult($data);
        } catch (RuntimeException $e) {
            Log::warning('[AI Microservice Detection Normalization Failed] '.$e->getMessage());

            return $this->fallbackDetectionResult($image, $e->getMessage());
        }
    }

    /**
     * Return a clearly labelled fallback so the mobile detection flow still completes while the ML
     * service is not available in local development.
     *
     * @return array<string, mixed>
     */
    private function fallbackDetectionResult(UploadedFile $image, string $reason): array
    {
        $fallbackMode = strtolower((string) config('services.ai.detection_fallback', 'manual'));

        if ($fallbackMode === 'demo' && app()->environment(['local', 'development', 'testing'])) {
            $classes = [
                ['bacterial_leaf_blight', 'Bacterial Leaf Blight (Hawar Daun Bakteri)'],
                ['bacterial_leaf_streak', 'Bacterial Leaf Streak (Bercak Daun Bakteri)'],
                ['bacterial_panicle_blight', 'Bacterial Panicle Blight (Hawar Malai Bakteri)'],
                ['blast', 'Blast (Penyakit Blas)'],
                ['brown_spot', 'Brown Spot (Bercak Cokelat)'],
                ['dead_heart', 'Dead Heart (Penggerek Batang)'],
                ['downy_mildew', 'Downy Mildew (Bulu Embun)'],
                ['hispa', 'Hispa (Hama Hispa)'],
                ['healthy', 'Normal (Padi Sehat)'],
                ['tungro', 'Tungro (Penyakit Tungro)'],
            ];

            $imageContent = file_get_contents((string) $image->getRealPath());
            $hash = crc32($imageContent !== false ? $imageContent : $image->getClientOriginalName());
            $selected = $classes[abs($hash) % count($classes)];
            $confidence = 0.62 + ((abs($hash) % 18) / 100);

            return [
                'disease_code' => $selected[0],
                'disease_name' => $selected[1],
                'confidence' => min($confidence, 0.80),
                'confidence_level' => 'medium',
                'model_version' => 'local-demo-fallback-v1',
                'image_quality' => [
                    'status' => 'fallback_demo',
                    'reason' => $reason,
                ],
                'needs_expert_review' => true,
            ];
        }

        return [
            'disease_code' => 'unknown',
            'disease_name' => 'Perlu Pemeriksaan Manual',
            'confidence' => 0.0,
            'confidence_level' => 'low',
            'model_version' => 'ai-service-unavailable',
            'image_quality' => [
                'status' => 'manual_review',
                'reason' => $reason,
            ],
            'needs_expert_review' => true,
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function normalizeAiDetectionResult(array $data): array
    {
        $diseaseCode = trim((string) ($data['disease_code'] ?? ''));
        $diseaseName = trim((string) ($data['disease_name'] ?? ''));
        $modelVersion = trim((string) ($data['model_version'] ?? ''));

        if ($diseaseCode === '' || $diseaseCode === 'unknown' || $diseaseName === '' || $diseaseName === 'Tidak Dapat Dipastikan') {
            throw new RuntimeException('AI service belum yakin dengan hasil deteksi. Silakan ambil foto ulang dengan daun lebih jelas.');
        }

        if (! isset($data['confidence']) || ! is_numeric($data['confidence'])) {
            throw new RuntimeException('AI service tidak mengembalikan confidence model yang valid.');
        }

        $confidence = min(max((float) $data['confidence'], 0.0), 1.0);
        if ($confidence <= 0.0) {
            throw new RuntimeException('AI service mengembalikan confidence model terlalu rendah.');
        }

        if ($modelVersion === '') {
            throw new RuntimeException('AI service tidak mengembalikan versi model.');
        }

        $data['disease_code'] = $diseaseCode;
        $data['disease_name'] = $diseaseName;
        $data['confidence'] = $confidence;
        $data['model_version'] = $modelVersion;
        $data['confidence_level'] = $data['confidence_level'] ?? ($confidence >= 0.85 ? 'high' : ($confidence >= 0.70 ? 'medium' : 'low'));
        $data['image_quality'] = is_array($data['image_quality'] ?? null) ? $data['image_quality'] : [];

        return $data;
    }
}
