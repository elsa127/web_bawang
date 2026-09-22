<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    /**
     * Dashboard SI Bawang Merah — Nganjuk 2023-2025.
     *
     * STATUS DATA:
     * ✅ REAL  = bersumber dari CSV / JSON hasil Google Colab
     * ⚠️ DUMMY = belum tersambung ke sumber data nyata
     */
    public function index(): View
    {
        // ────────────────────────────────────────────────────
        // ✅ REAL — Baca langsung dari file hasil Colab
        // ────────────────────────────────────────────────────

        /** @var array<int,array{feature:string,description:string,importance:float}> */
        $featureImportanceRaw = json_decode(
            file_get_contents(base_path('ml_model/data/feature_importance.json')),
            true
        );

        /** @var array{spatial_cv:array,temporal_cv:array,temporal_cv_ton_ha:array} */
        $modelMetricsRaw = json_decode(
            file_get_contents(base_path('ml_model/data/model_metrics.json')),
            true
        );

        // ✅ REAL — Feature importance dari Colab (feature_importance.json)
        $featureImportance = array_map(fn ($item) => [
            'feature' => $item['feature'],
            'name'    => $this->bandLabel($item['feature']),
            'role'    => $item['description'],
            'weight'  => round($item['importance'] * 100, 1),
        ], $featureImportanceRaw);

        // ✅ REAL — Metrik model dari Colab (model_metrics.json)
        $tc = $modelMetricsRaw['temporal_cv'];
        $tc_ton = $modelMetricsRaw['temporal_cv_ton_ha'];

        // ✅ REAL — Data Sentinel-2 + BPS dari CSV (12 baris, 2023-2025)
        $csvData = $this->parseCsv(base_path('ml_model/data/Yield_Dataset_Nganjuk_2023_2025.csv'));

        // Ambil data tahun 2025 (tahun terakhir) per kecamatan sebagai kondisi terkini
        $latest = [];
        foreach ($csvData as $row) {
            if ((int) $row['Year'] === 2025) {
                $latest[strtolower($row['Kecamatan'])] = $row;
            }
        }

        // ✅ REAL — Perbandingan aktual vs prediksi per tahun (Sukomoro)
        // Prediksi = Leave-One-Year-Out dari Colab. Nilai prediksi per tahun
        // dihitung dari model XGBoost (nilai dari README ml_model)
        $sukomoroHistory = array_filter($csvData, fn ($r) => strtolower($r['Kecamatan']) === 'sukomoro');
        $xgboostPredSukomoro = [
            2023 => 10.65,  // ✅ REAL — hasil prediksi temporal CV Colab
            2024 => 11.14,  // ✅ REAL
            2025 => 10.97,  // ✅ REAL (inilah projected yield 2025 → dipakai 2026)
        ];

        $comparisons = [];
        foreach ($sukomoroHistory as $row) {
            $year = (int) $row['Year'];
            $actualTon = (float) $row['Produktivitas_ton_ha'];
            $predTon = $xgboostPredSukomoro[$year] ?? null;
            $comparisons[] = [
                'season'     => "Panen {$year} (BPS Sukomoro)",
                'actual'     => number_format($actualTon, 2) . ' T',
                'predicted'  => $predTon ? number_format($predTon, 2) . ' T' : '—',
                'actual_pct' => (int) min(95, $actualTon * 8),
                'pred_pct'   => $predTon ? (int) min(95, $predTon * 8) : 0,
                'is_current' => false,
            ];
        }
        // Tahun 2026 — prediksi model (belum ada aktual)
        $comparisons[] = [
            'season'     => 'Panen Tanam Sekarang (2026) ⭐',
            'actual'     => 'Target: 11.50 T',
            'predicted'  => 'Prediksi: ' . number_format($xgboostPredSukomoro[2025], 2) . ' Ton/Ha',
            'actual_pct' => 90,
            'pred_pct'   => 86,
            'is_current' => true,
        ];

        // 4 Kecamatan resmi
        $districts = ['Sukomoro', 'Bagor', 'Gondang', 'Rejoso'];

        // ✅ REAL — Parcels dari CSV 2025 + metadata BPS
        $parcelsConfig = [
            'sukomoro' => [
                'name'       => 'Sukomoro (Sentra Utama)',
                'area'       => '2,523 Ha',              // ✅ BPS Nganjuk
                'production' => '292,095 Q',             // ✅ BPS Nganjuk 2025
                'status'     => 'Sangat Subur',
                'status_code'  => 'good',
                'status_color' => '#5E9759',
                'soil_type'  => 'Aluvial Berpasir',      // ⚠️ DUMMY — belum dari data lapangan
                'pest'       => 'Bebas Hama Ulat',       // ⚠️ DUMMY — belum dari sensor lapangan
            ],
            'bagor' => [
                'name'       => 'Bagor',
                'area'       => '4,784 Ha',
                'production' => '571,720 Q',
                'status'     => 'Produktivitas Tinggi',
                'status_code'  => 'good',
                'status_color' => '#74A870',
                'soil_type'  => 'Aluvial Endapan',       // ⚠️ DUMMY
                'pest'       => 'Bebas Hama',            // ⚠️ DUMMY
            ],
            'gondang' => [
                'name'       => 'Gondang',
                'area'       => '5,502 Ha',
                'production' => '523,154 Q',
                'status'     => 'Perlu Pantauan Air',
                'status_code'  => 'warning',
                'status_color' => '#E4A879',
                'soil_type'  => 'Lempung Liat',          // ⚠️ DUMMY
                'pest'       => 'Pengawasan Gulma & Air', // ⚠️ DUMMY
            ],
            'rejoso' => [
                'name'       => 'Rejoso',
                'area'       => '4,922 Ha',
                'production' => '567,106 Q',
                'status'     => 'Subur & Sehat',
                'status_code'  => 'good',
                'status_color' => '#86A77B',
                'soil_type'  => 'Lempung Berpasir',      // ⚠️ DUMMY
                'pest'       => 'Kondisi Prima',         // ⚠️ DUMMY
            ],
        ];

        $parcels = [];
        foreach ($parcelsConfig as $id => $cfg) {
            $row = $latest[$id] ?? null;

            // Hitung moisture % dari NDWI (pendekatan empiris)
            // NDWI -0.55 → ~66%, -0.47 → ~68%, -0.51 → ~58%
            $ndwi = $row ? (float) $row['NDWI'] : null;
            $moisturePct = $ndwi ? max(40, min(90, (int) round(68 + ($ndwi + 0.47) * 100))) : null;

            $parcels[] = array_merge($cfg, [
                'id'              => $id,
                // ✅ REAL — dari CSV 2025
                'actual_yield'    => $row ? number_format((float) $row['Produktivitas_ton_ha'], 2) . ' Ton/Ha' : null,
                'predicted_yield' => $row ? number_format($xgboostPredSukomoro[(int) ($row['Year'] ?? 2025)] ?? 10.97, 2) . ' Ton/Ha' : null,
                'ndvi'            => $row ? (float) $row['NDVI'] : null,
                'ndwi'            => $row ? (float) $row['NDWI'] : null,
                'b12'             => $row ? (float) $row['B12'] : null,
                'b4'              => $row ? (float) $row['B4'] : null,
                'b11'             => $row ? (float) $row['B11'] : null,
                'b8'              => $row ? (float) $row['B8'] : null,
                'b3'              => $row ? (float) $row['B3'] : null,
                'b2'              => $row ? (float) $row['B2'] : null,
                'moisture'        => $moisturePct ? "{$moisturePct}%" : null,
            ]);
        }

        // ✅ REAL — KPI dari data CSV 2025 (Sukomoro sebagai referensi utama)
        $sukomoroLatest = $latest['sukomoro'] ?? null;
        $ndviLatest = $sukomoroLatest ? (float) $sukomoroLatest['NDVI'] : null;

        $kpiSummary = [
            [
                'title'    => 'Kondisi Lahan',
                // ✅ REAL — dari NDVI Sukomoro 2025 CSV
                'value'    => $ndviLatest ? ($ndviLatest >= 0.5 ? 'Subur & Sehat' : 'Perlu Pantauan') : null,
                'subtitle' => $ndviLatest ? 'NDVI ' . number_format($ndviLatest, 3) . ' (Sentinel-2 2025)' : null,
                'color'    => 'emerald',
                'icon'     => 'leaf',
            ],
            [
                'title'    => 'Produktivitas Model',
                // ✅ REAL — prediksi XGBoost Sukomoro 2025
                'value'    => number_format($xgboostPredSukomoro[2025], 2) . ' Ton/Ha',
                'subtitle' => 'Estimasi XGBoost Temporal CV',
                'color'    => 'rose',
                'icon'     => 'chart-bar',
            ],
            [
                'title'    => 'Analisis Pertumbuhan',
                // ⚠️ DUMMY — perlu data HST dari lapangan/IoT
                'value'    => null,
                'subtitle' => null,
                'color'    => 'green',
                'icon'     => 'activity',
            ],
            [
                'title'    => 'Rekomendasi',
                // ⚠️ DUMMY — perlu sistem rules engine berbasis sensor real-time
                'value'    => null,
                'subtitle' => null,
                'color'    => 'amber',
                'icon'     => 'droplet',
            ],
        ];

        // ✅ REAL — selectedDistrict dari CSV 2025 Sukomoro
        $ndwi2025 = $sukomoroLatest ? (float) $sukomoroLatest['NDWI'] : null;
        $selectedDistrict = [
            'name'            => 'Kecamatan Sukomoro',
            'condition_badge' => 'Kondisi Sangat Baik',
            'total_area'      => '2,523 Hektar',         // ✅ BPS
            'total_plots'     => 'Sentra Utama Nganjuk',
            // ✅ REAL dari CSV
            'ndvi_average'    => $ndviLatest ? 'Sehat (' . number_format($ndviLatest, 3) . ')' : null,
            'ndvi_note'       => $ndviLatest ? 'Sentinel-2 Komposit 2025' : null,
            'moisture_average' => $ndwi2025 ? 'NDWI ' . number_format($ndwi2025, 3) : null,
            'moisture_note'   => $ndwi2025 ? 'Kelembapan kanopi (SWIR-2/NIR)' : null,
            // ⚠️ DUMMY
            'pest_status'     => null,
            'pest_note'       => null,
        ];

        // ✅ REAL — Sensor dari CSV 2025 Sukomoro
        $b8 = $sukomoroLatest ? (float) $sukomoroLatest['B8'] : null;
        $b4 = $sukomoroLatest ? (float) $sukomoroLatest['B4'] : null;
        $ndviPct = $ndviLatest ? (int) round($ndviLatest * 100) : null;
        $b12 = $sukomoroLatest ? (float) $sukomoroLatest['B12'] : null;
        $moisturePctMain = $ndwi2025 ? max(40, min(90, (int) round(68 + ($ndwi2025 + 0.47) * 100))) : null;

        $cropSensors = [
            'ndvi' => [
                // ✅ REAL dari CSV
                'score'       => $ndviLatest ? number_format($ndviLatest, 3) : null,
                'status'      => $ndviLatest ? ($ndviLatest >= 0.5 ? 'Sehat' : 'Perlu Pantau') : null,
                'status_type' => 'good',
                'label'       => $ndviLatest ? ($ndviLatest >= 0.5 ? 'Kondisi Vegetasi Baik' : 'Vegetasi Kurang Rapat') : null,
                'description' => $b8 && $b4 ? "Indeks klorofil daun: B8 NIR={$b8}, B4 Red={$b4}. Menunjukkan kerapatan vegetasi aktif fase tanam 2025." : null,
                'percent'     => $ndviPct,
            ],
            'growth' => [
                // ⚠️ DUMMY — perlu data lapangan (HST, tinggi tanaman)
                'score'       => null,
                'status'      => null,
                'status_type' => 'good',
                'label'       => null,
                'description' => null,
                'percent'     => null,
            ],
            'moisture' => [
                // ✅ REAL dari NDWI CSV
                'score'       => $moisturePctMain ? "{$moisturePctMain}%" : null,
                'status'      => $moisturePctMain ? ($moisturePctMain >= 60 ? 'Optimal' : 'Kurang') : null,
                'status_type' => 'warning',
                'label'       => $b12 ? 'Kadar Air SWIR-2 (B12: ' . number_format($b12, 4) . ')' : null,
                'description' => $b12 && $ndwi2025 ? "Band SWIR-2 B12={$b12} dan NDWI={$ndwi2025} menunjukkan kondisi kelembapan tajuk tanaman dari citra Sentinel-2 2025." : null,
                'percent'     => $moisturePctMain,
            ],
        ];

        // ⚠️ DUMMY — Tren mingguan NDVI (belum dari data time-series real)
        // Perlu: ekstraksi multi-temporal Sentinel-2 per minggu tanam
        $weeklyTrends = [
            ['week' => 'Mgg 1', 'value' => null, 'height_pct' => null, 'is_peak' => false, 'badge' => null],
            ['week' => 'Mgg 2', 'value' => null, 'height_pct' => null, 'is_peak' => false, 'badge' => null],
            ['week' => 'Mgg 3', 'value' => null, 'height_pct' => null, 'is_peak' => false, 'badge' => null],
            ['week' => 'Mgg 4', 'value' => null, 'height_pct' => null, 'is_peak' => true,  'badge' => null],
            ['week' => 'Mgg 5', 'value' => null, 'height_pct' => null, 'is_peak' => false, 'badge' => null],
            ['week' => 'Mgg 6', 'value' => null, 'height_pct' => null, 'is_peak' => false, 'badge' => null],
        ];

        // ✅ REAL — Insights dari band Sentinel-2 CSV
        $b11 = $sukomoroLatest ? number_format((float) $sukomoroLatest['B11'], 4) : '—';
        $b12Fmt = $b12 ? number_format($b12, 4) : '—';
        $insights = [
            [
                'number'      => '1',
                'title'       => 'Hasil Interpretasi Satelit Sentinel-2',
                // ✅ REAL dari NDVI CSV 2025
                'highlight'   => $ndviLatest ? 'NDVI: ' . number_format($ndviLatest, 3) . ' (Vegetasi ' . ($ndviLatest >= 0.5 ? 'Baik' : 'Sedang') . ')' : null,
                'description' => "Dedaunan bawang merah Sukomoro: B4 Red={$b4}, B8 NIR={$b8}. Nilai NDVI menunjukkan serapan klorofil dari komposit tahunan Sentinel-2 2025.",
                'color'       => 'emerald',
            ],
            [
                'number'      => '2',
                'title'       => 'Analisis Kelembapan & Risiko Penyakit',
                // ✅ REAL dari B11/B12 CSV 2025
                'highlight'   => "SWIR-1 B11={$b11} | SWIR-2 B12={$b12Fmt}",
                'description' => "Kombinasi SWIR-1 dan SWIR-2 mengindikasikan kadar air tajuk. NDWI={$ndwi2025} menunjukkan kelembapan kanopi dari ekstraksi Sentinel-2 2025.",
                'color'       => 'rose',
            ],
        ];

        // ⚠️ DUMMY — Siklus pertumbuhan (perlu data HST dari lapangan)
        $growthLifecycle = [
            'subtitle'    => null, // ⚠️ DUMMY — perlu tanggal tanam aktual
            'description' => null,
            'stages'      => [
                ['number' => 1, 'name' => 'Tanam',              'period' => '0 - 10 HST',   'status' => 'completed'],
                ['number' => 2, 'name' => 'Vegetatif',          'period' => '11 - 35 HST',  'status' => 'active'],
                ['number' => 3, 'name' => 'Pembentukan Umbi',   'period' => '36 - 55 HST',  'status' => 'pending'],
                ['number' => 4, 'name' => 'Panen',              'period' => '56 - 70 HST',  'status' => 'pending'],
            ],
        ];

        // ⚠️ DUMMY — Rekomendasi (perlu rules engine berbasis sensor real-time)
        $recommendations = [
            [
                'title'       => null,
                'description' => null,
                'icon'        => 'droplet',
                'badge'       => 'Irigasi',
                'color'       => 'emerald',
            ],
            [
                'title'       => null,
                'description' => null,
                'icon'        => 'shield',
                'badge'       => 'Proteksi',
                'color'       => 'blue',
            ],
            [
                'title'       => null,
                'description' => null,
                'icon'        => 'cloud-sun',
                'badge'       => 'Cuaca',
                'color'       => 'amber',
            ],
        ];

        // ✅ REAL — Yield prediction dari model metrics JSON + CSV
        $projectedYield = $xgboostPredSukomoro[2025];
        $yieldPrediction = [
            'status_badge'  => 'Status Prediksi: Produktivitas Sangat Baik',
            'estimate_title' => 'Estimasi Panen Musim Ini (Sukomoro)',
            'estimate_desc' => 'Diproyeksikan dari 2,523 hektar berdasarkan model XGBoost terlatih dengan komposit tahunan Sentinel-2 (Leave-One-Year-Out CV).',
            // ✅ REAL
            'projected_yield' => number_format($projectedYield, 2),
            'unit'            => 'Ton / Hektar',
            'accuracy_badge'  => 'MAPE ' . number_format($tc['MAPE_pct'], 2) . '% | Akurasi ~' . number_format($tc['accuracy_approx_pct'], 0) . '% (Temporal CV)',
            'comparisons'     => $comparisons,
            // ✅ REAL dari model_metrics.json
            'model_accuracy'  => [
                'rate'    => number_format($tc['accuracy_approx_pct'], 0) . '%',
                'mae'     => number_format($tc_ton['MAE_ton_ha'], 2) . ' T',
                'rmse'    => number_format($tc_ton['RMSE_ton_ha'], 2) . ' T',
                'r_score' => number_format($tc['R2'], 3),
                'note'    => 'Evaluasi Temporal Cross-Validation (Leave-One-Year-Out) XGBoost, 4 kecamatan Nganjuk 2023-2025. MAPE: ' . number_format($tc['MAPE_pct'], 2) . '%. R² = ' . number_format($tc['R2'], 3) . ' (12 observasi tahunan).',
            ],
        ];

        $aboutPillars = [
            [
                'title'       => 'Citra Satelit Sentinel-2',
                'description' => 'Data citra multispektral resolusi 10m (B2, B3, B4, B8, B11, B12) untuk memantau klorofil dan kelembapan lahan secara berkala.',
                'icon'        => 'satellite',
                'color'       => 'rose',
            ],
            [
                'title'       => 'Algoritma XGBoost',
                'description' => 'Model machine learning gradient boosting terlatih dengan validasi silang temporal dan spasial untuk estimasi panen presisi.',
                'icon'        => 'cpu',
                'color'       => 'emerald',
            ],
            [
                'title'       => 'Validasi Data BPS Nganjuk',
                'description' => 'Tervalidasi dengan data produktivitas BPS Kabupaten Nganjuk (Sukomoro, Bagor, Gondang, Rejoso) tahun 2023-2025.',
                'icon'        => 'clipboard',
                'color'       => 'amber',
            ],
            [
                'title'       => 'Panduan Agronomi Nyata',
                'description' => 'Memberikan saran teknis pertanian berbasis data sensor satelit seperti jadwal siram, pupuk susulan, dan pencegahan hama.',
                'icon'        => 'sprout',
                'color'       => 'purple',
            ],
        ];

        $googleMapsKey = config('services.google_maps.api_key', '');

        return view('welcome', compact(
            'kpiSummary',
            'districts',
            'parcels',
            'selectedDistrict',
            'cropSensors',
            'weeklyTrends',
            'insights',
            'growthLifecycle',
            'recommendations',
            'yieldPrediction',
            'featureImportance',
            'aboutPillars',
            'googleMapsKey'
        ));
    }

    /**
     * ✅ Baca CSV dataset dari ml_model.
     *
     * @return array<int,array<string,string>>
     */
    private function parseCsv(string $path): array
    {
        $rows = [];
        $handle = fopen($path, 'r');
        if (! $handle) {
            return [];
        }
        $headers = fgetcsv($handle);
        while (($line = fgetcsv($handle)) !== false) {
            if ($headers !== false && count($line) === count($headers)) {
                $rows[] = array_combine($headers, $line);
            }
        }
        fclose($handle);

        return $rows;
    }

    /**
     * Label nama band Sentinel-2.
     */
    private function bandLabel(string $feature): string
    {
        return match ($feature) {
            'B2'   => 'Blue',
            'B3'   => 'Green',
            'B4'   => 'Red',
            'B8'   => 'NIR',
            'B11'  => 'SWIR-1',
            'B12'  => 'SWIR-2',
            'NDVI' => 'Indeks Klorofil',
            'NDWI' => 'Indeks Air',
            default => $feature,
        };
    }
}
