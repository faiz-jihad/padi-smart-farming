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
    ) {
    }

    /**
     * @param  array{farm_id: int, plant_age_days?: int|null, latitude?: float|null, longitude?: float|null}  $data
     */
    public function scan(int $farmerId, UploadedFile $image, array $data): DiseaseScan
    {
        $farm = null;
        if (!empty($data['farm_id'])) {
            $farm = Farm::query()
                ->whereKey($data['farm_id'])
                ->where('farmer_user_id', $farmerId)
                ->first();
        }

        if (!$farm) {
            $farm = Farm::query()
                ->where('farmer_user_id', $farmerId)
                ->first() ?? Farm::query()->create([
                    'farmer_user_id' => $farmerId,
                    'name' => 'Lahan Sawah Utama',
                    'location' => 'Indramayu, Jawa Barat',
                    'area_ha' => 1.0,
                    'soil_type' => 'Lempung Berliat',
                    'irrigation_type' => 'Irigasi Teknis',
                    'latitude' => (float) ($data['latitude'] ?? -6.3266),
                    'longitude' => (float) ($data['longitude'] ?? 108.3200),
                ]);
        }

        $lat = (float) ($data['latitude'] ?? $farm->latitude ?? -6.3266);
        $lng = (float) ($data['longitude'] ?? $farm->longitude ?? 108.3200);
        $plantAgeDays = isset($data['plant_age_days']) ? (int) $data['plant_age_days'] : null;

        $aiResult = $this->detectWithAi($image, [
            'plant_age_days' => $plantAgeDays,
            'latitude' => $lat,
            'longitude' => $lng,
        ]);

        $path = $image->store('disease-scans', 'public');

        if (!$path) {
            throw new RuntimeException('Foto gagal disimpan.');
        }

        /** @var DiseaseScan $scan */
        $scan = DiseaseScan::query()->create([
            'farmer_id' => $farmerId,
            'farm_id' => $farm->id,
            'image_url' => Storage::disk('public')->url($path),
            'image_hash' => hash_file('sha256', $image->getRealPath()),
            'quality_status' => $this->qualityStatusFromAiResult($aiResult),
            'predicted_class' => (string) $aiResult['disease_name'],
            'confidence' => (float) $aiResult['confidence'],
            'detection_metadata' => $this->buildDetectionMetadata($aiResult),
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
            if (!empty($weatherResponse['data'])) {
                $parsed = $this->weatherService->parseWeatherData($weatherResponse['data']);
                $weatherContext = [
                    'temperature' => (float) ($parsed['temperature'] ?? 29.0),
                    'humidity' => (float) ($parsed['humidity'] ?? 78.0),
                    'condition' => (string) ($parsed['description'] ?? $parsed['weather'] ?? 'Cerah Berawan'),
                ];
            }
        } catch (\Throwable $e) {
            Log::warning('[Weather Realtime Failed] ' . $e->getMessage());
        }

        // 2. Libatkan AI Microservice Python (/treatments/recommend) untuk rekomendasi agronomi terstruktur
        $diseaseCode = (string) $aiResult['disease_code'];
        $aiTreatments = $this->getAiServiceTreatments(
            $diseaseCode,
            (float) ($scan->confidence ?? 0.90),
            $weatherContext,
            $plantAgeDays
        );
        if ($aiTreatments) {
            $scan->setAttribute('ai_service_treatments', $aiTreatments);
        }

        // 3. Generate Gemini / Agricultural RAG recommendations dengan cuaca real-time
        try {
            $recommendations = $this->geminiService->generateForScan($scan, $weatherContext);
            $scan->setAttribute('gemini_recommendations', $recommendations);
        } catch (\Throwable $e) {
            Log::warning('[Gemini Rec Service] ' . $e->getMessage());
        }

        return $scan;
    }

    /**
     * Mengambil rekomendasi penanganan terstruktur dari Python AI Microservice.
     *
     * @param  array{temperature: float, humidity: float, condition: string}  $weatherContext
     * @return array<string, mixed>|null
     */
    private function getAiServiceTreatments(string $diseaseCode, float $confidence, array $weatherContext, ?int $plantAgeDays): ?array
    {
        $baseUrl = rtrim((string) config('services.ai.base_url'), '/');
        if (empty($baseUrl)) {
            return null;
        }

        try {
            $response = Http::timeout((int) config('services.ai.timeout', 15))->post("{$baseUrl}/treatments/recommend", [
                'disease_code' => $diseaseCode,
                'confidence' => min(max($confidence, 0.0), 1.0),
                'plant_age_days' => $plantAgeDays,
                'severity' => $confidence >= 0.85 ? 'high' : 'medium',
                'affected_area_percentage' => $confidence >= 0.85 ? 30.0 : 15.0,
                'weather_condition' => $weatherContext['condition'] ?? 'Cerah Berawan',
                'actions_already_taken' => [],
            ]);

            if ($response->successful()) {
                $payload = $response->json();

                return is_array($payload) ? ($payload['data'] ?? null) : null;
            }
        } catch (\Throwable $e) {
            Log::info('[AI Service Treatment Recommendation Skip] ' . $e->getMessage());
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $aiResult
     */
    private function qualityStatusFromAiResult(array $aiResult): string
    {
        $imageQuality = is_array($aiResult['image_quality'] ?? null) ? $aiResult['image_quality'] : [];
        $status = trim((string) ($imageQuality['status'] ?? ''));

        if ($status !== '') {
            return $status;
        }

        if (($imageQuality['is_acceptable'] ?? false) === true) {
            return 'passed';
        }

        return (string) ($aiResult['confidence_level'] ?? 'model_checked');
    }

    /**
     * @param  array<string, mixed>  $aiResult
     * @return array<string, mixed>
     */
    private function buildDetectionMetadata(array $aiResult): array
    {
        return [
            'disease_code' => $aiResult['disease_code'] ?? null,
            'confidence_level' => $aiResult['confidence_level'] ?? null,
            'needs_expert_review' => (bool) ($aiResult['needs_expert_review'] ?? false),
            'image_quality' => $aiResult['image_quality'] ?? null,
            'top_predictions' => $aiResult['top_predictions'] ?? [],
            'prediction_margin' => $aiResult['prediction_margin'] ?? null,
            'model_accuracy' => $aiResult['model_accuracy'] ?? null,
            'processing_time_ms' => $aiResult['processing_time_ms'] ?? null,
            'detection_status' => $aiResult['detection_status'] ?? 'DETECTED',
            'status_message' => $aiResult['status_message'] ?? null,
            'ai_request_id' => $aiResult['ai_request_id'] ?? null,
            'pipeline_stages' => $aiResult['pipeline_stages'] ?? null,
            'segmentation' => $aiResult['segmentation'] ?? null,
            'features' => $aiResult['features'] ?? null,
        ];
    }

    /**
     * Mengirimkan umpan balik pengguna dan mendaftarkan daun ke AI learning memory bank.
     */
    public function submitFeedback(DiseaseScan $scan, string $status, ?string $correctedClass = null, ?string $notes = null): DiseaseScan
    {
        $finalClass = ($status === 'corrected' && !empty($correctedClass))
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
            Log::warning('[Regenerate Rec on Feedback Failed] ' . $e->getMessage());
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
            $imagePath = storage_path('app/public/' . $cleanPath);

            if (!file_exists($imagePath)) {
                $imagePath = public_path('storage/' . $cleanPath);
            }

            if (!file_exists($imagePath)) {
                return;
            }

            $diseaseCode = $this->normalizeDiseaseCode($diseaseName);
            if ($diseaseCode === null) {
                Log::warning('[AI Learning Sync Skipped] Unsupported disease class: ' . $diseaseName);

                return;
            }

            Http::timeout((int) config('services.ai.timeout', 5))
                ->attach('image', fopen($imagePath, 'r'), basename($imagePath))
                ->post("{$baseUrl}/diseases/learn", [
                    'disease_code' => $diseaseCode,
                    'disease_name' => $diseaseName,
                    'confidence' => (float) ($scan->confidence ?? 1.0),
                    'source' => $source,
                    'sample_id' => 'scan_' . $scan->id,
                ]);
        } catch (\Throwable $e) {
            Log::warning('[AI Learning Sync Failed] ' . $e->getMessage());
        }
    }

    private function normalizeDiseaseCode(string $diseaseName): ?string
    {
        $baseName = trim(explode('(', $diseaseName)[0]);
        $slug = trim(strtolower(preg_replace('/[^a-zA-Z0-9]+/', '_', $baseName)), '_');

        return match ($slug) {
            'bacterial_leaf_blight', 'hawar_daun_bakteri', 'kresek' => 'bacterial_leaf_blight',
            'bacterial_leaf_streak', 'bercak_daun_bakteri' => 'bacterial_leaf_streak',
            'bacterial_panicle_blight', 'hawar_malai_bakteri' => 'bacterial_panicle_blight',
            'blast', 'penyakit_blas', 'blas' => 'blast',
            'brown_spot', 'bercak_cokelat', 'bercak_coklat' => 'brown_spot',
            'dead_heart', 'penggerek_batang', 'sundep', 'beluk' => 'dead_heart',
            'downy_mildew', 'bulu_embun' => 'downy_mildew',
            'hispa', 'hama_hispa' => 'hispa',
            'normal', 'healthy', 'padi_sehat', 'sehat' => 'normal',
            'tungro', 'penyakit_tungro' => 'tungro',
            default => null,
        };
    }

    /**
     * @param  array{plant_age_days?: int|null, latitude?: float|null, longitude?: float|null}  $context
     * @return array<string, mixed>
     */
    private function detectWithAi(UploadedFile $image, array $context): array
    {
        $imagePath = $image->getRealPath();
        if (!$imagePath || !file_exists($imagePath)) {
            throw new RuntimeException('File foto tidak dapat dibaca untuk inferensi AI.');
        }

        $configuredUrl = rtrim((string) config('services.ai.base_url', 'http://127.0.0.1:8003/api/v1'), '/');
        $candidates = array_values(array_unique(array_filter([
            $configuredUrl,
            'http://127.0.0.1:8003/api/v1',
            'http://127.0.0.1:8002/api/v1',
            'http://localhost:8003/api/v1',
            'http://localhost:8002/api/v1',
        ])));

        $response = null;
        $lastException = null;

        foreach ($candidates as $baseUrl) {
            $endpoints = [
                "{$baseUrl}/diseases/detect",
                "{$baseUrl}/ai/padi/diagnose",
            ];

            foreach ($endpoints as $url) {
                try {
                    $postData = array_filter([
                        'plant_age_days' => $context['plant_age_days'] ?? null,
                        'latitude' => $context['latitude'] ?? null,
                        'longitude' => $context['longitude'] ?? null,
                        'locale' => 'id',
                    ], fn($value) => $value !== null);

                    $req = Http::timeout((int) config('services.ai.timeout', 20))
                        ->attach(
                            'image',
                            fopen($imagePath, 'r'),
                            $image->getClientOriginalName()
                        );

                    $response = $req->post($url, $postData);

                    if ($response->status() !== 404) {
                        // Respon didapat (baik 200, 422, atau status lain dari endpoint aktif)
                        break 2;
                    }
                } catch (\Illuminate\Http\Client\ConnectionException $e) {
                    $lastException = $e;
                    continue; // Coba kandidat URL berikutnya
                } catch (\Throwable $e) {
                    $lastException = $e;
                    break 2;
                }
            }
        }

        if ($response === null) {
            Log::warning('[AI Microservice Detection Failed] Tidak dapat terhubung ke service: ' . ($lastException?->getMessage() ?? 'unknown'));

            throw new RuntimeException('AI service real tidak dapat dihubungi. Pastikan AI service berjalan di port 8003 atau 8002.');
        }

        if ($response->clientError()) {
            $payload = $response->json();
            $errorMessage = $payload['error']['message'] ?? $payload['message'] ?? 'Objek pada gambar bukan daun padi. Silakan ambil foto daun padi dengan jelas.';
            throw new \InvalidArgumentException($errorMessage);
        }

        if (!$response->successful()) {
            Log::warning('[AI Microservice Detection Error] HTTP ' . $response->status() . ' : ' . $response->body());

            throw new RuntimeException($this->aiServiceErrorMessage($response->json()));
        }

        $payload = $response->json();
        $data = is_array($payload) ? ($payload['data'] ?? null) : null;
        if (!is_array($data)) {
            Log::warning('[AI Microservice Detection Error] Invalid response payload.');

            throw new RuntimeException('AI service real tidak mengembalikan data deteksi yang valid.');
        }

        if (isset($payload['meta']['request_id'])) {
            $data['ai_request_id'] = $payload['meta']['request_id'];
        }

        try {
            return $this->normalizeAiDetectionResult($data);
        } catch (RuntimeException $e) {
            Log::warning('[AI Microservice Detection Normalization Failed] ' . $e->getMessage());

            throw $e;
        }
    }

    /**
     * @param  array<string, mixed>|null  $payload
     */
    private function aiServiceErrorMessage(?array $payload): string
    {
        $message = $payload['error']['message'] ?? $payload['message'] ?? null;

        if (is_string($message) && trim($message) !== '') {
            return $message;
        }

        return 'AI service real gagal memproses gambar. Deteksi tidak dijalankan.';
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function normalizeAiDetectionResult(array $data): array
    {
        // Support rich format from /api/v1/ai/padi/diagnose
        if (isset($data['prediction']['class_name'])) {
            $data['disease_code'] = $data['prediction']['class_name'];
            $data['disease_name'] = $data['prediction']['display_name'] ?? ucfirst(str_replace('_', ' ', (string) $data['prediction']['class_name']));
            $data['confidence'] = $data['prediction']['confidence'] ?? 0.0;
            $data['model_version'] = (($data['model']['name'] ?? 'paddy_doctor') . '_' . ($data['model']['version'] ?? 'v3'));
            $data['image_quality'] = $data['quality'] ?? [];
            $data['needs_expert_review'] = (bool) ($data['decision']['needs_ppl_review'] ?? false);
            $data['prediction_margin'] = $data['prediction']['margin'] ?? null;
            if (isset($data['prediction']['top_predictions']) && is_array($data['prediction']['top_predictions'])) {
                $data['top_predictions'] = array_map(fn($p) => [
                    'disease_code' => $p['class_name'] ?? '',
                    'disease_name' => ucfirst(str_replace('_', ' ', (string) ($p['class_name'] ?? ''))),
                    'confidence' => (float) ($p['confidence'] ?? 0),
                ], $data['prediction']['top_predictions']);
            }
        }

        $diseaseCode = trim((string) ($data['disease_code'] ?? ''));
        $diseaseName = trim((string) ($data['disease_name'] ?? ''));
        $modelVersion = trim((string) ($data['model_version'] ?? ''));

        if ($diseaseCode === '' || $diseaseCode === 'unknown' || $diseaseName === '' || $diseaseName === 'Tidak Dapat Dipastikan') {
            throw new RuntimeException('AI service belum yakin dengan hasil deteksi. Silakan ambil foto ulang dengan daun lebih jelas.');
        }

        if (!isset($data['confidence']) || !is_numeric($data['confidence'])) {
            throw new RuntimeException('AI service tidak mengembalikan confidence model yang valid.');
        }

        $confidence = $this->clampProbability((float) $data['confidence']);
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
        $data['needs_expert_review'] = (bool) ($data['needs_expert_review'] ?? $data['confidence_level'] === 'low');
        $data['top_predictions'] = $this->normalizeTopPredictions(
            is_array($data['top_predictions'] ?? null) ? $data['top_predictions'] : [],
            $diseaseCode,
            $diseaseName,
            $confidence
        );
        $data['prediction_margin'] = isset($data['prediction_margin']) && is_numeric($data['prediction_margin'])
            ? $this->clampProbability((float) $data['prediction_margin'])
            : $this->calculatePredictionMargin($data['top_predictions']);
        $data['model_accuracy'] = isset($data['model_accuracy']) && is_numeric($data['model_accuracy'])
            ? $this->clampProbability((float) $data['model_accuracy'])
            : null;
        $data['processing_time_ms'] = isset($data['processing_time_ms']) && is_numeric($data['processing_time_ms'])
            ? max(0, (int) $data['processing_time_ms'])
            : null;
        $data['detection_status'] = trim((string) ($data['detection_status'] ?? 'DETECTED')) ?: 'DETECTED';
        $data['status_message'] = isset($data['status_message']) && is_string($data['status_message'])
            ? trim($data['status_message'])
            : null;

        return $data;
    }

    private function clampProbability(float $value): float
    {
        return min(max($value, 0.0), 1.0);
    }

    /**
     * @param  array<int, mixed>  $predictions
     * @return array<int, array{disease_code: string, disease_name: string, confidence: float}>
     */
    private function normalizeTopPredictions(array $predictions, string $diseaseCode, string $diseaseName, float $confidence): array
    {
        $normalized = [];

        foreach ($predictions as $prediction) {
            if (!is_array($prediction)) {
                continue;
            }

            $candidateCode = trim((string) ($prediction['disease_code'] ?? ''));
            $candidateName = trim((string) ($prediction['disease_name'] ?? ''));
            $candidateConfidence = $prediction['confidence'] ?? null;

            if ($candidateCode === '' || $candidateName === '' || !is_numeric($candidateConfidence)) {
                continue;
            }

            $normalized[] = [
                'disease_code' => $candidateCode,
                'disease_name' => $candidateName,
                'confidence' => $this->clampProbability((float) $candidateConfidence),
            ];
        }

        if ($normalized === [] || $normalized[0]['disease_code'] !== $diseaseCode) {
            array_unshift($normalized, [
                'disease_code' => $diseaseCode,
                'disease_name' => $diseaseName,
                'confidence' => $confidence,
            ]);
        }

        return array_slice($normalized, 0, 3);
    }

    /**
     * @param  array<int, array{disease_code: string, disease_name: string, confidence: float}>  $predictions
     */
    private function calculatePredictionMargin(array $predictions): float
    {
        if (count($predictions) < 2) {
            return 0.0;
        }

        return $this->clampProbability((float) $predictions[0]['confidence'] - (float) $predictions[1]['confidence']);
    }

}
