<?php

namespace App\Services;

use App\Models\DiseaseRecommendation;
use App\Models\DiseaseScan;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiRecommendationService
{
    /**
     * Generate structured agricultural recommendation via Gemini API with deep knowledge base fallback.
     *
     * @param DiseaseScan $scan
     * @param array<string, mixed> $weatherContext
     * @return array<string, mixed>
     */
    public function generateForScan(DiseaseScan $scan, array $weatherContext = []): array
    {
        $diseaseName = $scan->predicted_class ?? 'Blast (Penyakit Blas)';
        $temperature = $weatherContext['temperature'] ?? 28.0;
        $humidity = $weatherContext['humidity'] ?? 80.0;
        $weatherCondition = $weatherContext['condition'] ?? 'Normal / Cerah Berawan';

        $apiKey = config('services.gemini.api_key') ?: env('GEMINI_API_KEY');
        $model = config('services.gemini.model', 'gemini-1.5-flash');

        $result = null;

        if (!empty($apiKey)) {
            try {
                $result = $this->callGeminiApi($apiKey, $model, $diseaseName, $temperature, $humidity, $weatherCondition);
            } catch (\Throwable $e) {
                Log::warning('[Gemini API Error] ' . $e->getMessage() . '. Falling back to knowledge base.');
            }
        }

        if (!$result) {
            $result = $this->generateFromKnowledgeBase($diseaseName, $temperature, $humidity, $weatherCondition);
        }

        // Save or update DiseaseRecommendation record
        try {
            DiseaseRecommendation::query()->updateOrCreate(
                ['scan_id' => $scan->id],
                [
                    'source' => !empty($apiKey) ? 'gemini_ai' : 'knowledge_base',
                    'llm_model' => !empty($apiKey) ? $model : 'padi-rag-v2',
                    'explanation' => $result['analisis'] ?? '',
                    'action' => $result['langkah_preventif'] ?? '',
                    'safety_note' => $result['rekomendasi_obat'] ?? '',
                ]
            );
        } catch (\Throwable $e) {
            Log::warning('[DiseaseRecommendation Save Error] ' . $e->getMessage());
        }

        return $result;
    }

    /**
     * Call Google Gemini API to generate structured recommendation.
     */
    private function callGeminiApi(
        string $apiKey,
        string $model,
        string $diseaseName,
        float $temperature,
        float $humidity,
        string $weatherCondition
    ): ?array {
        $kbInfo = $this->getKnowledgeBaseEntry($diseaseName);
        $kbContext = "";
        if ($kbInfo) {
            $pencegahan = implode(", ", $kbInfo['agronomi_modern']['pencegahan'] ?? []);
            $pengendalian = implode(", ", $kbInfo['agronomi_modern']['pengendalian'] ?? []);
            $kbContext = "Referensi Agronomi: Penyebab: " . ($kbInfo['scientific_name'] ?? '-') . "\nPencegahan: $pencegahan\nPengendalian: $pengendalian";
        }

        $prompt = <<<PROMPT
Anda adalah pakar agronomi senior tanaman padi di Indonesia.
Analisis penyakit padi berikut berdasarkan kondisi lingkungan dan berikan rekomendasi teknis terbaik untuk petani.

Data Input:
- Nama Penyakit: {$diseaseName}
- Suhu Saat Ini: {$temperature}°C
- Kelembaban Saat Ini: {$humidity}%
- Kondisi Cuaca: {$weatherCondition}
{$kbContext}

INSTRUKSI FORMAT:
Berikan jawaban menggunakan format tag XML berikut persis tanpa teks lain di luar tag:

<analisis>
Jelaskan 1-2 paragraf analisis kondisi tanaman, tingkat keparahan potensial, dan dampak cuaca/kelembaban terhadap perkembangan penyakit ini.
</analisis>

<langkah>
1. **Langkah 1** — penjelasan tindakan praktis
2. **Langkah 2** — penjelasan tindakan praktis
3. **Langkah 3** — penjelasan tindakan praktis
4. **Langkah 4** — penjelasan tindakan praktis
</langkah>

<obat>
1. **Nama Bahan Aktif/Teknik** — Dosis, cara semprot, dan waktu aplikasi (pagi/sore).
2. **Nama Bahan Aktif/Teknik** — Dosis, cara semprot, dan waktu aplikasi.
3. **Nama Bahan Aktif/Teknik** — Dosis, cara semprot, dan waktu aplikasi.
</obat>

<produk>
NamaProduk|BahanAktif|KisaranHarga|KataKunciCari
(Sertakan 3-4 produk asli berizin di Indonesia per baris, contoh: Nordox 56 WP|Tembaga Oksida 56%|Rp 45.000 - 65.000/250g|fungisida nordox)
</produk>

<diy>
1. **Nama Pestisida Nabati/Racikan** — Bahan: (daftar bahan). Cara Buat: (langkah ringkas). Cara Pakai: (dosis dan cara semprot).
2. **Nama Pestisida Nabati/Racikan** — Bahan: (daftar bahan). Cara Buat: (langkah ringkas). Cara Pakai: (dosis dan cara semprot).
</diy>
PROMPT;

        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";

        $response = Http::timeout(25)->post($url, [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt]
                    ]
                ]
            ],
            'generationConfig' => [
                'temperature' => 0.2,
                'maxOutputTokens' => 2048,
            ]
        ]);

        if (!$response->successful()) {
            return null;
        }

        $body = $response->json();
        $text = $body['candidates'][0]['content']['parts'][0]['text'] ?? '';

        if (empty($text)) {
            return null;
        }

        return $this->parseXmlResponse($text, $diseaseName);
    }

    /**
     * Parse XML tagged response from Gemini.
     */
    private function parseXmlResponse(string $text, string $diseaseName): array
    {
        preg_match('/<analisis>(.*?)<\/analisis>/s', $text, $analisisMatch);
        preg_match('/<langkah>(.*?)<\/langkah>/s', $text, $langkahMatch);
        preg_match('/<obat>(.*?)<\/obat>/s', $text, $obatMatch);
        preg_match('/<produk>(.*?)<\/produk>/s', $text, $produkMatch);
        preg_match('/<diy>(.*?)<\/diy>/s', $text, $diyMatch);

        $produkList = [];
        if (!empty($produkMatch[1])) {
            $lines = explode("\n", trim($produkMatch[1]));
            foreach ($lines as $line) {
                $line = trim($line);
                if (str_contains($line, '|')) {
                    $parts = array_map('trim', explode('|', $line));
                    if (count($parts) >= 4) {
                        $produkList[] = [
                            'nama' => $parts[0],
                            'bahan_aktif' => $parts[1],
                            'harga' => $parts[2],
                            'keyword' => $parts[3],
                        ];
                    }
                }
            }
        }

        if (empty($analisisMatch[1]) && empty($langkahMatch[1])) {
            return $this->generateFromKnowledgeBase($diseaseName, 28, 80, 'Normal');
        }

        return [
            'penyakit' => $diseaseName,
            'analisis' => trim($analisisMatch[1] ?? 'Analisis tanaman berdasarkan gejala daun padi.'),
            'langkah_preventif' => trim($langkahMatch[1] ?? "- Jaga sanitasi dan kebersihan pematang sawah.\n- Atur pengairan secara berselang (intermittent)."),
            'rekomendasi_obat' => trim($obatMatch[1] ?? 'Konsultasikan dengan PPL setempat untuk dosis bahan aktif terbaik.'),
            'produk' => !empty($produkList) ? $produkList : $this->getDefaultProducts($diseaseName),
            'diy' => trim($diyMatch[1] ?? $this->getDefaultDiy($diseaseName)),
            'source' => 'Gemini AI Pro',
        ];
    }

    /**
     * Fallback to rich local knowledge base.
     */
    public function generateFromKnowledgeBase(
        string $diseaseName,
        float $temperature,
        float $humidity,
        string $weatherCondition
    ): array {
        $kb = $this->getKnowledgeBaseEntry($diseaseName);

        $analisis = "Terdeteksi gejala **{$diseaseName}**. Kondisi suhu sekitar {$temperature}°C dan kelembaban {$humidity}% dengan cuaca {$weatherCondition} berpotensi mempengaruhi laju penyebaran patogen pada tanaman padi Anda.";

        if (!empty($kb['deskripsi'])) {
            $analisis .= " " . $kb['deskripsi'];
        }

        $pencegahanArr = $kb['agronomi_modern']['pencegahan'] ?? [
            "Lakukan pergiliran varietas padi tahan penyakit.",
            "Atur jarak tanam menggunakan sistem jajar legowo (2:1 atau 4:1) untuk sirkulasi udara optimal.",
            "Hindari pemberian pupuk Nitrogen (Urea) berlebihan saat cuaca lembab.",
            "Bersihkan gulma dan rumput liar di pematang sawah yang menjadi inang patogen.",
        ];

        $langkahPreventif = "";
        foreach ($pencegahanArr as $i => $item) {
            $num = $i + 1;
            $langkahPreventif .= "{$num}. {$item}\n";
        }

        $pengendalianArr = $kb['agronomi_modern']['pengendalian'] ?? [
            "Aplikasikan bakterisida / fungisida protektif pada pagi hari sebelum pukul 09.00.",
            "Gunakan dosis anjuran 1.5 - 2 gram/liter air dengan sprayer bertekanan merata.",
            "Lakukan pengeringan sawah secara berkala (intermittent irrigation) selama 3-5 hari.",
        ];

        $rekomendasiObat = "";
        foreach ($pengendalianArr as $i => $item) {
            $num = $i + 1;
            $rekomendasiObat .= "{$num}. {$item}\n";
        }

        return [
            'penyakit' => $diseaseName,
            'analisis' => trim($analisis),
            'langkah_preventif' => trim($langkahPreventif),
            'rekomendasi_obat' => trim($rekomendasiObat),
            'produk' => $this->getDefaultProducts($diseaseName),
            'diy' => $this->getDefaultDiy($diseaseName),
            'source' => 'Basis Pengetahuan Agronomi P.A.D.I.',
        ];
    }

    /**
     * Retrieve entry from knowledge_base.json if available.
     */
    private function getKnowledgeBaseEntry(string $diseaseName): ?array
    {
        $filePath = storage_path('app/knowledge_base.json');
        if (!file_exists($filePath)) {
            $filePath = base_path('../ai-service/knowledge_base/knowledge_base.json');
        }

        if (file_exists($filePath)) {
            try {
                $content = file_get_contents($filePath);
                $data = json_decode($content, true);
                if (is_array($data)) {
                    foreach ($data as $key => $val) {
                        if (stripos($diseaseName, $key) !== false || stripos($key, $diseaseName) !== false) {
                            return $val;
                        }
                    }
                    if (isset($data[$diseaseName])) {
                        return $data[$diseaseName];
                    }
                }
            } catch (\Throwable $e) {}
        }

        return null;
    }

    private function getDefaultProducts(string $diseaseName): array
    {
        $diseaseLower = strtolower($diseaseName);

        if (str_contains($diseaseLower, 'bacterial') || str_contains($diseaseLower, 'hawar') || str_contains($diseaseLower, 'bakteri')) {
            return [
                ['nama' => 'Nordox 56 WP', 'bahan_aktif' => 'Tembaga Oksida 56%', 'harga' => 'Rp 45.000 - 65.000 / 250g', 'keyword' => 'fungisida nordox 56 wp'],
                ['nama' => 'Agrept 20 WP', 'bahan_aktif' => 'Streptomisin Sulfat 20%', 'harga' => 'Rp 35.000 - 48.000 / 50g', 'keyword' => 'bakterisida agrept 20 wp'],
                ['nama' => 'Bactocyn 150 AL', 'bahan_aktif' => 'Oksitetrasiklin 150 g/l', 'harga' => 'Rp 50.000 - 75.000 / 200ml', 'keyword' => 'baktocyn 150 al'],
            ];
        }

        if (str_contains($diseaseLower, 'blast') || str_contains($diseaseLower, 'blas') || str_contains($diseaseLower, 'brown') || str_contains($diseaseLower, 'spot') || str_contains($diseaseLower, 'bercak')) {
            return [
                ['nama' => 'Amistartop 325 SC', 'bahan_aktif' => 'Azoksistrobin 200 g/l + Difenokonazol 125 g/l', 'harga' => 'Rp 75.000 - 110.000 / 100ml', 'keyword' => 'amistartop 325 sc'],
                ['nama' => 'Fujiwan 400 EC', 'bahan_aktif' => 'Isoprotiolan 400 g/l', 'harga' => 'Rp 45.000 - 60.000 / 100ml', 'keyword' => 'fujiwan 400 ec'],
                ['nama' => 'Score 250 EC', 'bahan_aktif' => 'Difenokonazol 250 g/l', 'harga' => 'Rp 65.000 - 95.000 / 100ml', 'keyword' => 'score 250 ec'],
            ];
        }

        if (str_contains($diseaseLower, 'dead heart') || str_contains($diseaseLower, 'penggerek') || str_contains($diseaseLower, 'hispa')) {
            return [
                ['nama' => 'Prevathon 50 SC', 'bahan_aktif' => 'Klorantraniliprol 50 g/l', 'harga' => 'Rp 85.000 - 130.000 / 100ml', 'keyword' => 'prevathon 50 sc'],
                ['nama' => 'Virtako 300 SC', 'bahan_aktif' => 'Klorantraniliprol + Tiametoksam', 'harga' => 'Rp 95.000 - 145.000 / 100ml', 'keyword' => 'virtako 300 sc'],
                ['nama' => 'Regent 50 SC', 'bahan_aktif' => 'Fipronil 50 g/l', 'harga' => 'Rp 40.000 - 65.000 / 100ml', 'keyword' => 'regent 50 sc'],
            ];
        }

        return [
            ['nama' => 'Nordox 56 WP', 'bahan_aktif' => 'Tembaga Oksida 56%', 'harga' => 'Rp 45.000 - 65.000 / 250g', 'keyword' => 'fungisida nordox'],
            ['nama' => 'Score 250 EC', 'bahan_aktif' => 'Difenokonazol 250 g/l', 'harga' => 'Rp 65.000 - 95.000 / 100ml', 'keyword' => 'score 250 ec'],
        ];
    }

    private function getDefaultDiy(string $diseaseName): string
    {
        return "1. **Pestisida Nabati Bawang Putih & Kunyit** — Bahan: 250g bawang putih, 250g kunyit parut, 1 sdm sabun cair, 5 liter air. Cara buat: Blender halus, saring dan campur dengan air. Cara pakai: Larutkan 100ml ekstrak per tangki 14L air, semprotkan sore hari.\n2. **Kapur Sirih & Abu Sekam Padi** — Bahan: 1 kg kapur tohor/sirih, 2 kg abu sekam. Cara buat: Campurkan dan taburkan di sela rumpun padi yang terserang untuk menekan kelembaban asam tanah.";
    }
}
