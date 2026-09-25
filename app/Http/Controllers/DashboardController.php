<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    /**
     * ============================================================
     * DASHBOARD UTAMA SI BAWANG MERAH
     * ============================================================
     *
     * Semua data Machine Learning disimpan di:
     *
     *     ml_model/data/
     *
     * File yang digunakan antara lain:
     *
     * - feature_importance.json
     * - model_metrics.json
     * - Yield_Dataset_Nganjuk_2023_2025.csv
     * - OOf_Site_Prediction_Nganjuk_V4.csv
     * - dataset_ml_nganjuk_v4.csv
     * - dataset_ml_nganjuk_v4_qc.csv
     * - dataset_ml_nganjuk_v4_1_STRICT.csv
     * - dataset_ml_nganjuk_v4_1_EXTENDED.csv
     * - FeatureStack_Nganjuk_2025.tif
     * - FeatureStack_Nganjuk_V3_FINAL.tif
     * - ground_truth_bawang_training_v4.csv
     * - ground_truth_bukan_bawang_training_v4.csv
     * - ground_truth_candidate_bawang_v4.csv
     * - negative_dynamicworld_nganjuk_*.csv
     *
     * ============================================================
     */
    public function index(): View
    {
        /*
        |--------------------------------------------------------------------------
        | 1. LOKASI UTAMA DATASET MACHINE LEARNING
        |--------------------------------------------------------------------------
        |
        | Semua file ML berada di:
        |
        |     project-laravel/ml_model/data/
        |
        | Karena folder ml_model berada di root project Laravel,
        | kita menggunakan base_path().
        |
        */
        $mlPath = base_path('ml_model/data');

        /*
        |--------------------------------------------------------------------------
        | 2. MEMBACA FEATURE IMPORTANCE
        |--------------------------------------------------------------------------
        |
        | File:
        |
        |     ml_model/data/feature_importance.json
        |
        | File ini berisi tingkat kepentingan fitur yang digunakan
        | oleh model XGBoost.
        |
        */
        $featureImportanceRaw = $this->readJsonFile(
            $mlPath.'/feature_importance.json'
        );

        /*
        |--------------------------------------------------------------------------
        | 3. MEMBACA METRIK MODEL
        |--------------------------------------------------------------------------
        |
        | File:
        |
        |     ml_model/data/model_metrics.json
        |
        | Digunakan untuk mengambil:
        |
        | - MAPE
        | - MAE
        | - RMSE
        | - R2
        | - Accuracy Approximation
        |
        */
        $modelMetricsRaw = $this->readJsonFile(
            $mlPath.'/model_metrics.json'
        );

        /*
        |--------------------------------------------------------------------------
        | 4. TRANSFORMASI FEATURE IMPORTANCE
        |--------------------------------------------------------------------------
        |
        | Data JSON dari Colab diubah menjadi format yang lebih mudah
        | digunakan oleh Blade.
        |
        */
        $featureImportance = [];

        foreach ($featureImportanceRaw as $item) {

            /*
             * Pastikan item memiliki struktur yang benar.
             */
            if (! is_array($item)) {
                continue;
            }

            $feature = $item['feature'] ?? '';
            $description = $item['description'] ?? '';
            $importance = isset($item['importance'])
                ? (float) $item['importance']
                : 0;

            $featureImportance[] = [
                'feature' => $feature,

                /*
                 * Mengubah nama B2, B3, B4, B8, B11, B12
                 * menjadi label yang lebih mudah dipahami.
                 */
                'name' => $this->bandLabel($feature),

                'role' => $description,

                /*
                 * Importance dari model biasanya berupa 0.x.
                 * Diubah menjadi persentase.
                 */
                'weight' => round($importance * 100, 1),
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | 5. MENGAMBIL METRIK TEMPORAL CROSS VALIDATION
        |--------------------------------------------------------------------------
        |
        | Jika file model_metrics.json belum tersedia atau strukturnya
        | berbeda, kita menggunakan array kosong agar dashboard tidak
        | langsung mengalami error.
        |
        */
        $tc = $modelMetricsRaw['temporal_cv'] ?? [];

        $tc_ton = $modelMetricsRaw['temporal_cv_ton_ha'] ?? [];

        /*
        |--------------------------------------------------------------------------
        | 6. MEMBACA DATASET SENTINEL-2 + BPS
        |--------------------------------------------------------------------------
        |
        | File:
        |
        |     Yield_Dataset_Nganjuk_2023_2025.csv
        |
        | Dataset ini digunakan untuk menampilkan data historis
        | produktivitas dan indikator Sentinel-2.
        |
        */
        $yieldCsvPath = $mlPath.'/YIELD_PREDICTION/Yield_Dataset_Nganjuk_2023_2025.csv';

        $csvData = $this->parseCsv($yieldCsvPath);

        /*
        |--------------------------------------------------------------------------
        | 7. MENGAMBIL DATA TERBARU TAHUN 2025
        |--------------------------------------------------------------------------
        |
        | Data tahun 2025 digunakan sebagai kondisi terbaru
        | masing-masing kecamatan.
        |
        */
        $latest = [];

        foreach ($csvData as $row) {

            $year = (int) ($row['Year'] ?? 0);

            $district = strtolower(
                trim((string) ($row['Kecamatan'] ?? ''))
            );

            if ($year === 2025 && $district !== '') {
                $latest[$district] = $row;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | 8. MEMBACA HASIL PREDIKSI XGBOOST
        |--------------------------------------------------------------------------
        |
        | File:
        |
        |     OOf_Site_Prediction_Nganjuk_V4.csv
        |
        | File ini digunakan jika memiliki kolom:
        |
        | - Kecamatan / District / Site
        | - Tahun / Year
        | - Predicted / Prediction / y_pred
        |
        | Tujuannya supaya prediksi Bagor, Gondang, dan Rejoso
        | tidak menggunakan prediksi Sukomoro secara sembarangan.
        |
        */
        $xgboostPredictions = $this->loadXgboostPredictions($mlPath);

        /*
        |--------------------------------------------------------------------------
        | 9. PREDIKSI SUKOMORO
        |--------------------------------------------------------------------------
        |
        | Nilai berikut merupakan nilai prediksi yang sudah digunakan
        | pada dashboard sebelumnya dari hasil Leave-One-Year-Out CV.
        |
        | Jika data OOF memiliki nilai yang sesuai, nilai tersebut
        | akan digunakan terlebih dahulu.
        |
        */
        $xgboostPredSukomoro = [
            2023 => 10.65,
            2024 => 11.14,
            2025 => 10.97,
        ];

        /*
         * Jika OOF Prediction memiliki prediksi Sukomoro,
         * gunakan nilai tersebut.
         */
        foreach ([2023, 2024, 2025] as $year) {

            if (
                isset($xgboostPredictions['sukomoro'][$year])
                && $xgboostPredictions['sukomoro'][$year] !== null
            ) {
                $xgboostPredSukomoro[$year] =
                    $xgboostPredictions['sukomoro'][$year];
            }
        }

        /*
        |--------------------------------------------------------------------------
        | 10. PERBANDINGAN AKTUAL VS PREDIKSI SUKOMORO
        |--------------------------------------------------------------------------
        |
        | Data ini digunakan pada grafik/tabel produktivitas.
        |
        */
        $sukomoroHistory = array_filter(
            $csvData,
            fn ($r) => strtolower(trim((string) ($r['Kecamatan'] ?? ''))) === 'sukomoro'
        );

        $comparisons = [];

        foreach ($sukomoroHistory as $row) {

            $year = (int) ($row['Year'] ?? 0);

            $actualTon = isset($row['Produktivitas_ton_ha'])
                ? (float) $row['Produktivitas_ton_ha']
                : 0;

            $predTon = $xgboostPredSukomoro[$year] ?? null;

            $comparisons[] = [
                'season' => "Panen {$year} (BPS Sukomoro)",

                'actual' => number_format($actualTon, 2).' T',

                'predicted' => $predTon !== null
                        ? number_format($predTon, 2).' T'
                        : '—',

                'actual_pct' => (int) min(
                    95,
                    max(0, $actualTon * 8)
                ),

                'pred_pct' => $predTon !== null
                        ? (int) min(95, max(0, $predTon * 8))
                        : 0,

                'is_current' => false,
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | 11. DATA PROYEKSI MUSIM TANAM SEKARANG
        |--------------------------------------------------------------------------
        |
        | Tahun 2026 ditampilkan sebagai proyeksi.
        |
        */
        $comparisons[] = [
            'season' => 'Panen Tanam Sekarang (2026)',

            'actual' => 'Target: 11.50 T',

            'predicted' => 'Prediksi: '
                .number_format(
                    $xgboostPredSukomoro[2025],
                    2
                )
                .' Ton/Ha',

            'actual_pct' => 90,

            'pred_pct' => 86,

            'is_current' => true,
        ];

        /*
        |--------------------------------------------------------------------------
        | 12. EMPAT KECAMATAN PENELITIAN
        |--------------------------------------------------------------------------
        |
        | Wilayah penelitian:
        |
        | - Sukomoro
        | - Bagor
        | - Gondang
        | - Rejoso
        |
        */
        $districts = [
            'Sukomoro',
            'Bagor',
            'Gondang',
            'Rejoso',
        ];

        /*
        |--------------------------------------------------------------------------
        | 13. KONFIGURASI PARSEL / KECAMATAN
        |--------------------------------------------------------------------------
        |
        | Data ini digunakan untuk informasi detail ketika pengguna
        | memilih kecamatan pada peta.
        |
        */
        $parcelsConfig = [

            'sukomoro' => [
                'name' => 'Sukomoro (Sentra Utama)',
                'area' => '2,523 Ha',
                'production' => '292,095 Q',
                'status' => 'Sangat Subur',
                'status_code' => 'good',
                'status_color' => '#5E9759',
                'soil_type' => 'Aluvial Berpasir',
                'pest' => 'Bebas Hama Ulat',
            ],

            'bagor' => [
                'name' => 'Bagor',
                'area' => '4,784 Ha',
                'production' => '571,720 Q',
                'status' => 'Produktivitas Tinggi',
                'status_code' => 'good',
                'status_color' => '#74A870',
                'soil_type' => 'Aluvial Endapan',
                'pest' => 'Bebas Hama',
            ],

            'gondang' => [
                'name' => 'Gondang',
                'area' => '5,502 Ha',
                'production' => '523,154 Q',
                'status' => 'Perlu Pantauan Air',
                'status_code' => 'warning',
                'status_color' => '#E4A879',
                'soil_type' => 'Lempung Liat',
                'pest' => 'Pengawasan Gulma & Air',
            ],

            'rejoso' => [
                'name' => 'Rejoso',
                'area' => '4,922 Ha',
                'production' => '567,106 Q',
                'status' => 'Subur & Sehat',
                'status_code' => 'good',
                'status_color' => '#86A77B',
                'soil_type' => 'Lempung Berpasir',
                'pest' => 'Kondisi Prima',
            ],
        ];

        /*
        |--------------------------------------------------------------------------
        | 14. MEMBENTUK DATA PARSEL
        |--------------------------------------------------------------------------
        |
        | Setiap kecamatan digabungkan dengan data Sentinel-2 tahun 2025.
        |
        */
        $parcels = [];

        foreach ($parcelsConfig as $id => $cfg) {

            /*
             * Ambil data terbaru kecamatan.
             */
            $row = $latest[$id] ?? null;

            /*
             * NDWI digunakan sebagai indikator kelembapan.
             */
            $ndwi = $row !== null
                ? $this->toNullableFloat($row['NDWI'] ?? null)
                : null;

            /*
             * Konversi sederhana NDWI menjadi persentase tampilan.
             *
             * Catatan:
             * Ini merupakan nilai visualisasi dashboard,
             * bukan pengukuran kadar air tanah secara langsung.
             */
            $moisturePct = null;

            if ($ndwi !== null) {
                $moisturePct = max(
                    40,
                    min(
                        90,
                        (int) round(68 + ($ndwi + 0.47) * 100)
                    )
                );
            }

            /*
             * Prediksi produktivitas khusus kecamatan.
             *
             * Sukomoro mempunyai fallback dari data prediksi yang
             * sudah tersedia.
             *
             * Kecamatan lain tidak akan dipaksa memakai prediksi
             * Sukomoro.
             */
            $year = $row !== null
                ? (int) ($row['Year'] ?? 2025)
                : 2025;

            $districtPrediction =
                $xgboostPredictions[$id][$year]
                ?? (
                    $id === 'sukomoro'
                        ? ($xgboostPredSukomoro[$year] ?? null)
                        : null
                );

            /*
             * Membentuk data akhir yang dikirim ke Blade.
             */
            $parcels[] = array_merge(
                $cfg,
                [
                    'id' => $id,

                    'actual_yield' => $row !== null
                            && isset($row['Produktivitas_ton_ha'])
                            ? number_format(
                                (float) $row['Produktivitas_ton_ha'],
                                2
                            ).' Ton/Ha'
                            : null,

                    'predicted_yield' => $districtPrediction !== null
                            ? number_format(
                                $districtPrediction,
                                2
                            ).' Ton/Ha'
                            : null,

                    'ndvi' => $row !== null
                            ? $this->toNullableFloat($row['NDVI'] ?? null)
                            : null,

                    'ndwi' => $ndwi,

                    'b12' => $row !== null
                            ? $this->toNullableFloat($row['B12'] ?? null)
                            : null,

                    'b4' => $row !== null
                            ? $this->toNullableFloat($row['B4'] ?? null)
                            : null,

                    'b11' => $row !== null
                            ? $this->toNullableFloat($row['B11'] ?? null)
                            : null,

                    'b8' => $row !== null
                            ? $this->toNullableFloat($row['B8'] ?? null)
                            : null,

                    'b3' => $row !== null
                            ? $this->toNullableFloat($row['B3'] ?? null)
                            : null,

                    'b2' => $row !== null
                            ? $this->toNullableFloat($row['B2'] ?? null)
                            : null,

                    'moisture' => $moisturePct !== null
                            ? "{$moisturePct}%"
                            : null,
                ]
            );
        }

        /*
        |--------------------------------------------------------------------------
        | 15. KPI UTAMA
        |--------------------------------------------------------------------------
        |
        | KPI dashboard menggunakan Sukomoro sebagai kecamatan
        | referensi utama.
        |
        */
        $sukomoroLatest = $latest['sukomoro'] ?? null;

        $ndviLatest = $sukomoroLatest !== null
            ? $this->toNullableFloat($sukomoroLatest['NDVI'] ?? null)
            : null;

        $kpiSummary = [

            [
                'title' => 'Kondisi Lahan',

                'value' => $ndviLatest !== null
                        ? (
                            $ndviLatest >= 0.5
                                ? 'Subur & Sehat'
                                : 'Perlu Pantauan'
                        )
                        : null,

                'subtitle' => $ndviLatest !== null
                        ? 'NDVI '
                        .number_format($ndviLatest, 3)
                        .' (Sentinel-2 2025)'
                        : null,

                'color' => 'emerald',

                'icon' => 'leaf',
            ],

            [
                'title' => 'Produktivitas Model',

                'value' => number_format(
                    $xgboostPredSukomoro[2025],
                    2
                )
                    .' Ton/Ha',

                'subtitle' => 'Estimasi XGBoost Temporal CV',

                'color' => 'rose',

                'icon' => 'chart-bar',
            ],

            [
                'title' => 'Analisis Pertumbuhan',

                'value' => 'Fase Vegetatif',

                'subtitle' => 'Estimasi Musim Tanam 2026',

                'color' => 'green',

                'icon' => 'activity',
            ],

            [
                'title' => 'Rekomendasi',

                'value' => 'Siram Pagi Hari',

                'subtitle' => 'Kelembapan SWIR-2 Terjaga',

                'color' => 'amber',

                'icon' => 'droplet',
            ],
        ];

        /*
        |--------------------------------------------------------------------------
        | 16. DATA KECAMATAN TERPILIH
        |--------------------------------------------------------------------------
        |
        | Default ketika halaman pertama kali dibuka adalah Sukomoro.
        |
        */
        $ndwi2025 = $sukomoroLatest !== null
            ? $this->toNullableFloat($sukomoroLatest['NDWI'] ?? null)
            : null;

        $selectedDistrict = [

            'name' => 'Kecamatan Sukomoro',

            'condition_badge' => 'Kondisi Sangat Baik',

            'total_area' => '2,523 Hektar',

            'total_plots' => 'Sentra Utama Nganjuk',

            'ndvi_average' => $ndviLatest !== null
                    ? 'Sehat ('
                    .number_format($ndviLatest, 3)
                    .')'
                    : null,

            'ndvi_note' => $ndviLatest !== null
                    ? 'Sentinel-2 Komposit 2025'
                    : null,

            'moisture_average' => $ndwi2025 !== null
                    ? 'NDWI '
                    .number_format($ndwi2025, 3)
                    : null,

            'moisture_note' => $ndwi2025 !== null
                    ? 'Kelembapan kanopi (SWIR-2/NIR)'
                    : null,

            'pest_status' => null,

            'pest_note' => null,
        ];

        /*
        |--------------------------------------------------------------------------
        | 17. DATA SENSOR TANAMAN
        |--------------------------------------------------------------------------
        */
        $b8 = $sukomoroLatest !== null
            ? $this->toNullableFloat($sukomoroLatest['B8'] ?? null)
            : null;

        $b4 = $sukomoroLatest !== null
            ? $this->toNullableFloat($sukomoroLatest['B4'] ?? null)
            : null;

        $ndviPct = $ndviLatest !== null
            ? (int) round($ndviLatest * 100)
            : null;

        $b12 = $sukomoroLatest !== null
            ? $this->toNullableFloat($sukomoroLatest['B12'] ?? null)
            : null;

        $moisturePctMain = null;

        if ($ndwi2025 !== null) {
            $moisturePctMain = max(
                40,
                min(
                    90,
                    (int) round(
                        68 + ($ndwi2025 + 0.47) * 100
                    )
                )
            );
        }

        $cropSensors = [

            'ndvi' => [

                'score' => $ndviLatest !== null
                        ? number_format($ndviLatest, 3)
                        : null,

                'status' => $ndviLatest !== null
                        ? (
                            $ndviLatest >= 0.5
                                ? 'Sehat'
                                : 'Perlu Pantau'
                        )
                        : null,

                'status_type' => 'good',

                'label' => $ndviLatest !== null
                        ? (
                            $ndviLatest >= 0.5
                                ? 'Kondisi Vegetasi Baik'
                                : 'Vegetasi Kurang Rapat'
                        )
                        : null,

                'description' => $b8 !== null && $b4 !== null
                        ? "Indeks klorofil daun: B8 NIR={$b8}, B4 Red={$b4}. Menunjukkan kerapatan vegetasi aktif fase tanam 2025."
                        : null,

                'percent' => $ndviPct,
            ],

            'growth' => [

                'score' => null,

                'status' => null,

                'status_type' => 'good',

                'label' => null,

                'description' => null,

                'percent' => null,
            ],

            'moisture' => [

                'score' => $moisturePctMain !== null
                        ? "{$moisturePctMain}%"
                        : null,

                'status' => $moisturePctMain !== null
                        ? (
                            $moisturePctMain >= 60
                                ? 'Optimal'
                                : 'Kurang'
                        )
                        : null,

                'status_type' => 'warning',

                'label' => $b12 !== null
                        ? 'Kadar Air SWIR-2 (B12: '
                        .number_format($b12, 4)
                        .')'
                        : null,

                'description' => $b12 !== null && $ndwi2025 !== null
                        ? "Band SWIR-2 B12={$b12} dan NDWI={$ndwi2025} menunjukkan kondisi kelembapan tajuk tanaman dari citra Sentinel-2 2025."
                        : null,

                'percent' => $moisturePctMain,
            ],
        ];

        /*
        |--------------------------------------------------------------------------
        | 18. TREN NDVI MINGGUAN
        |--------------------------------------------------------------------------
        |
        | Digunakan untuk grafik perkembangan vegetasi.
        |
        */
        $ndviPeak = $ndviLatest ?? 0.50;

        $weeklyTrends = [

            [
                'week' => 'Mgg 1',
                'value' => 0.32,
                'height_pct' => 32,
                'is_peak' => false,
                'badge' => null,
            ],

            [
                'week' => 'Mgg 2',
                'value' => 0.44,
                'height_pct' => 44,
                'is_peak' => false,
                'badge' => null,
            ],

            [
                'week' => 'Mgg 3',
                'value' => 0.58,
                'height_pct' => 58,
                'is_peak' => false,
                'badge' => null,
            ],

            [
                'week' => 'Mgg 4',
                'value' => round($ndviPeak, 2),
                'height_pct' => (int) round($ndviPeak * 100),
                'is_peak' => true,
                'badge' => 'Optimal',
            ],

            [
                'week' => 'Mgg 5',
                'value' => 0.46,
                'height_pct' => 46,
                'is_peak' => false,
                'badge' => null,
            ],

            [
                'week' => 'Mgg 6',
                'value' => 0.41,
                'height_pct' => 41,
                'is_peak' => false,
                'badge' => null,
            ],
        ];

        /*
        |--------------------------------------------------------------------------
        | 19. INSIGHT ANALISIS CITRA
        |--------------------------------------------------------------------------
        */
        $b11 = $sukomoroLatest !== null
            ? $this->toNullableFloat($sukomoroLatest['B11'] ?? null)
            : null;

        $b11Fmt = $b11 !== null
            ? number_format($b11, 4)
            : '—';

        $b12Fmt = $b12 !== null
            ? number_format($b12, 4)
            : '—';

        $ndwiInsight = $ndwi2025 !== null
            ? number_format($ndwi2025, 3)
            : '—';

        $insights = [

            [
                'number' => '1',

                'title' => 'Hasil Interpretasi Satelit Sentinel-2',

                'highlight' => $ndviLatest !== null
                        ? 'NDVI: '
                        .number_format($ndviLatest, 3)
                        .' (Vegetasi '
                        .(
                            $ndviLatest >= 0.5
                                ? 'Baik'
                                : 'Sedang'
                        )
                        .')'
                        : null,

                'description' => 'Dedaunan bawang merah Sukomoro: '
                    .'B4 Red='
                    .($b4 !== null ? $b4 : '—')
                    .', B8 NIR='
                    .($b8 !== null ? $b8 : '—')
                    .'. Nilai NDVI menunjukkan serapan klorofil dari komposit tahunan Sentinel-2 2025.',

                'color' => 'emerald',
            ],

            [
                'number' => '2',

                'title' => 'Analisis Kelembapan & Risiko Penyakit',

                'highlight' => "SWIR-1 B11={$b11Fmt} | SWIR-2 B12={$b12Fmt}",

                'description' => 'Kombinasi SWIR-1 dan SWIR-2 mengindikasikan kadar air tajuk. '
                    ."NDWI={$ndwiInsight} menunjukkan kelembapan kanopi dari ekstraksi Sentinel-2 2025.",

                'color' => 'rose',
            ],
        ];

        /*
        |--------------------------------------------------------------------------
        | 20. SIKLUS PERTUMBUHAN BAWANG MERAH
        |--------------------------------------------------------------------------
        */
        $growthLifecycle = [

            'subtitle' => 'Fase Vegetatif (Estimasi Musim Tanam 2026)',

            'description' => 'Tanaman bawang merah sedang aktif membentuk anakan dan memperkuat daun sebelum memasuki fase pembentukan umbi.',

            'stages' => [

                [
                    'number' => 1,
                    'name' => 'Tanam',
                    'period' => '0 - 10 HST',
                    'status' => 'completed',
                ],

                [
                    'number' => 2,
                    'name' => 'Vegetatif',
                    'period' => '11 - 35 HST',
                    'status' => 'active',
                ],

                [
                    'number' => 3,
                    'name' => 'Pembentukan Umbi',
                    'period' => '36 - 55 HST',
                    'status' => 'pending',
                ],

                [
                    'number' => 4,
                    'name' => 'Panen',
                    'period' => '56 - 70 HST',
                    'status' => 'pending',
                ],
            ],
        ];

        /*
        |--------------------------------------------------------------------------
        | 21. REKOMENDASI PERTANIAN
        |--------------------------------------------------------------------------
        */
        $ndwiLabel = $ndwi2025 !== null
            ? number_format($ndwi2025, 2)
            : '—';

        $ndviLabel = $ndviLatest !== null
            ? number_format($ndviLatest, 3)
            : '—';

        $recommendations = [

            [
                'title' => 'Penyiraman Rutin',

                'description' => 'Pertahankan kelembapan tanah 60–70%. '
                    .'Siram pagi hari sebelum jam 08.00 WIB. '
                    ."NDWI Sukomoro 2025: {$ndwiLabel} — dalam kisaran optimal.",

                'icon' => 'droplet',

                'badge' => 'Irigasi',

                'color' => 'emerald',
            ],

            [
                'title' => 'Waspada Daun',

                'description' => 'Periksa bercak ungu (Alternaria porri) secara berkala. '
                    ."NDVI Sukomoro 2025: {$ndviLabel} — vegetasi aktif, risiko penyakit rendah.",

                'icon' => 'shield',

                'badge' => 'Proteksi',

                'color' => 'blue',
            ],

            [
                'title' => 'Drainase Lahan',

                'description' => 'Pastikan saluran drainase tidak mampet menjelang musim hujan agar umbi tidak tergenang. Pantau reflektansi SWIR-2 (B12) secara berkala.',

                'icon' => 'cloud-sun',

                'badge' => 'Cuaca',

                'color' => 'amber',
            ],
        ];

        /*
        |--------------------------------------------------------------------------
        | 22. PREDIKSI PRODUKTIVITAS XGBOOST
        |--------------------------------------------------------------------------
        */
        $projectedYield = $xgboostPredSukomoro[2025];

        /*
        |--------------------------------------------------------------------------
        | 23. MEMBACA NILAI METRIK DENGAN AMAN
        |--------------------------------------------------------------------------
        |
        | Jika model_metrics.json tersedia, nilainya ditampilkan.
        | Jika tidak tersedia, dashboard tetap dapat dibuka.
        |
        */
        $mape = $this->toNullableFloat(
            $tc['MAPE_pct'] ?? null
        );

        $accuracyApprox = $this->toNullableFloat(
            $tc['accuracy_approx_pct'] ?? null
        );

        $maeTon = $this->toNullableFloat(
            $tc_ton['MAE_ton_ha'] ?? null
        );

        $rmseTon = $this->toNullableFloat(
            $tc_ton['RMSE_ton_ha'] ?? null
        );

        $r2 = $this->toNullableFloat(
            $tc['R2'] ?? null
        );

        /*
        |--------------------------------------------------------------------------
        | 24. TEKS AKURASI MODEL
        |--------------------------------------------------------------------------
        */
        if (
            $mape !== null
            && $accuracyApprox !== null
        ) {
            $accuracyBadge =
                'MAPE '
                .number_format($mape, 2)
                .'% | Akurasi ~'
                .number_format($accuracyApprox, 0)
                .'% (Temporal CV)';
        } else {
            $accuracyBadge = 'Metrik Temporal CV belum tersedia';
        }

        /*
        |--------------------------------------------------------------------------
        | 25. DATA PREDIKSI PRODUKTIVITAS
        |--------------------------------------------------------------------------
        */
        $yieldPrediction = [

            'status_badge' => 'Status Prediksi: Produktivitas Sangat Baik',

            'estimate_title' => 'Estimasi Panen Musim Ini (Sukomoro)',

            'estimate_desc' => 'Diproyeksikan dari 2,523 hektar berdasarkan model XGBoost terlatih dengan komposit tahunan Sentinel-2 (Leave-One-Year-Out CV).',

            'projected_yield' => number_format($projectedYield, 2),

            'unit' => 'Ton / Hektar',

            'accuracy_badge' => $accuracyBadge,

            'comparisons' => $comparisons,

            'model_accuracy' => [

                'rate' => $accuracyApprox !== null
                        ? number_format(
                            $accuracyApprox,
                            0
                        ).'%'
                        : '—',

                'mae' => $maeTon !== null
                        ? number_format(
                            $maeTon,
                            2
                        ).' T'
                        : '—',

                'rmse' => $rmseTon !== null
                        ? number_format(
                            $rmseTon,
                            2
                        ).' T'
                        : '—',

                'r_score' => $r2 !== null
                        ? number_format(
                            $r2,
                            3
                        )
                        : '—',

                'note' => $mape !== null && $r2 !== null
                        ? 'Evaluasi Temporal Cross-Validation (Leave-One-Year-Out) XGBoost, 4 kecamatan Nganjuk 2023-2025. MAPE: '
                        .number_format($mape, 2)
                        .'%. R² = '
                        .number_format($r2, 3)
                        .' (12 observasi tahunan).'
                        : 'Data evaluasi model belum tersedia.',
            ],
        ];

        /*
        |--------------------------------------------------------------------------
        | 26. PILAR INFORMASI SISTEM
        |--------------------------------------------------------------------------
        */
        $aboutPillars = [

            [
                'title' => 'Citra Satelit Sentinel-2',

                'description' => 'Data citra multispektral resolusi 10m (B2, B3, B4, B8, B11, B12) untuk memantau klorofil dan kelembapan lahan secara berkala.',

                'icon' => 'satellite',

                'color' => 'rose',
            ],

            [
                'title' => 'Algoritma XGBoost',

                'description' => 'Model machine learning gradient boosting terlatih dengan validasi silang temporal dan spasial untuk estimasi panen presisi.',

                'icon' => 'cpu',

                'color' => 'emerald',
            ],

            [
                'title' => 'Validasi Data BPS Nganjuk',

                'description' => 'Tervalidasi dengan data produktivitas BPS Kabupaten Nganjuk (Sukomoro, Bagor, Gondang, Rejoso) tahun 2023-2025.',

                'icon' => 'clipboard',

                'color' => 'amber',
            ],

            [
                'title' => 'Panduan Agronomi Nyata',

                'description' => 'Memberikan saran teknis pertanian berbasis data sensor satelit seperti jadwal siram, pupuk susulan, dan pencegahan hama.',

                'icon' => 'sprout',

                'color' => 'purple',
            ],
        ];

        /*
        |--------------------------------------------------------------------------
        | 27. DATA UNTUK LAYER PETA
        |--------------------------------------------------------------------------
        |
        | Bagian ini disiapkan khusus untuk checkbox / combo box
        | pada peta.
        |
        | Layer yang akan ditampilkan:
        |
        | 1. Batas Kecamatan
        | 2. Probability Map
        | 3. Candidate T = 0.50
        | 4. Candidate + DOA
        |
        */
        $mapLayers = [

            [
                'id' => 'boundary',

                'label' => 'Batas Kecamatan',

                'description' => 'Area administrasi penelitian',

                'type' => 'boundary',

                /*
                 * Batas kecamatan aktif ketika halaman pertama
                 * kali dibuka.
                 */
                'checked' => true,

                'group' => 'Layer Peta',
            ],

            [
                'id' => 'probability',

                'label' => 'Probability Map',

                'description' => 'Probabilitas kandidat bawang',

                'type' => 'probability',

                'checked' => false,

                'group' => 'Layer Peta',
            ],

            [
                'id' => 'candidate',

                'label' => 'Candidate T = 0.50',

                'description' => 'Kandidat setelah threshold',

                'type' => 'candidate',

                'threshold' => 0.50,

                'checked' => false,

                'group' => 'Layer Peta',
            ],

            [
                'id' => 'candidate_doa',

                'label' => 'Candidate + DOA',

                'description' => 'Kandidat setelah pembatasan DOA',

                'type' => 'candidate_doa',

                'checked' => false,

                'group' => 'Layer Peta',
            ],
        ];

        /*
        |--------------------------------------------------------------------------
        | 28. LEGENDA PROBABILITAS MODEL
        |--------------------------------------------------------------------------
        |
        | Digunakan oleh checkbox/legenda pada peta.
        |
        */
        $probabilityLegend = [

            [
                'id' => 'low',
                'label' => 'Rendah',
                'description' => 'Probabilitas rendah',
            ],

            [
                'id' => 'medium',
                'label' => 'Sedang',
                'description' => 'Probabilitas sedang',
            ],

            [
                'id' => 'high',
                'label' => 'Tinggi',
                'description' => 'Probabilitas tinggi',
            ],
        ];

        /*
        |--------------------------------------------------------------------------
        | 29. RINGKASAN LUAS AREA HASIL MODEL
        |--------------------------------------------------------------------------
        |
        | Nilai ini digunakan untuk kartu ringkasan pada bagian peta.
        |
        */
        $mapSummary = [

            'valid_area' => [
                'label' => 'Area Valid',
                'value' => '33,224.52 ha',
            ],

            'candidate_area' => [
                'label' => 'Kandidat T = 0.50',
                'value' => '26,854.45 ha',
            ],

            'candidate_doa_area' => [
                'label' => 'Kandidat + DOA',
                'value' => '26,047.32 ha',
            ],

            'threshold' => 0.50,
        ];

        /*
        |--------------------------------------------------------------------------
        | 30. MEMBACA BATAS KECAMATAN GEOJSON
        |--------------------------------------------------------------------------
        |
        | Controller mencari file GeoJSON di ml_model/data.
        |
        | Jika file belum ada, boundaryGeoJson menjadi array kosong
        | sehingga dashboard tidak langsung error.
        |
        */
        $boundaryGeoJson = [];

        $boundaryCandidates = [

            $mlPath.'/OFFICIAL_DATA_2025/Batas_4_Kecamatan_Nganjuk.geojson',

            $mlPath.'/OFFICIAL_DATA_2025/batas_4_kecamatan_nganjuk.geojson',

            $mlPath.'/Batas_4_Kecamatan_Nganjuk.geojson',

            $mlPath.'/batas_4_kecamatan_nganjuk.geojson',

            $mlPath.'/Batas_Kecamatan_Nganjuk.geojson',

            $mlPath.'/batas_kecamatan_nganjuk.geojson',
        ];

        $boundaryPath = $this->firstExistingPath(
            $boundaryCandidates
        );

        if ($boundaryPath !== null) {

            $boundaryData = $this->readJsonFile(
                $boundaryPath
            );

            /*
             * Pastikan data yang dimasukkan memang merupakan
             * struktur GeoJSON.
             */
            if (
                isset($boundaryData['type'])
                && is_array($boundaryData)
            ) {
                $boundaryGeoJson = $boundaryData;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | 31. STATUS FILE MODEL
        |--------------------------------------------------------------------------
        |
        | Bagian ini membantu mengecek apakah file penting sudah
        | berada di folder ml_model/data.
        |
        | Data ini bisa digunakan untuk debugging pada dashboard.
        |
        */
        $modelFiles = [

            'feature_importance' => is_file(
                $mlPath.'/feature_importance.json'
            ),

            'model_metrics' => is_file(
                $mlPath.'/model_metrics.json'
            ),

            'yield_dataset' => is_file(
                $mlPath.'/YIELD_PREDICTION/Yield_Dataset_Nganjuk_2023_2025.csv'
            ),

            'oof_prediction' => is_file(
                $mlPath.'/OOf_Site_Prediction_Nganjuk_V4.csv'
            ),

            'dataset_ml' => is_file(
                $mlPath.'/dataset_ml_nganjuk_v4.csv'
            ),

            'dataset_qc' => is_file(
                $mlPath.'/dataset_ml_nganjuk_v4_qc.csv'
            ),

            'dataset_strict' => is_file(
                $mlPath.'/dataset_ml_nganjuk_v4_1_STRICT.csv'
            ),

            'dataset_extended' => is_file(
                $mlPath.'/dataset_ml_nganjuk_v4_1_EXTENDED.csv'
            ),

            'feature_stack_2025' => is_file(
                $mlPath.'/FeatureStack_Nganjuk_2025.tif'
            ),

            'feature_stack_final' => is_file(
                $mlPath.'/FeatureStack_Nganjuk_V3_FINAL.tif'
            ),
        ];

        /*
        |--------------------------------------------------------------------------
        | 32. GOOGLE MAPS API KEY
        |--------------------------------------------------------------------------
        */
        $googleMapsKey = config(
            'services.google_maps.api_key',
            ''
        );

        /*
        |--------------------------------------------------------------------------
        | 33. KIRIM SEMUA DATA KE VIEW
        |--------------------------------------------------------------------------
        |
        | Semua variabel dikirim ke:
        |
        |     resources/views/welcome.blade.php
        |
        */
        return view(
            'welcome',
            compact(

                /*
                 * KPI dan data utama dashboard
                 */
                'kpiSummary',

                /*
                 * Daftar kecamatan
                 */
                'districts',

                /*
                 * Data setiap kecamatan/parsel
                 */
                'parcels',

                /*
                 * Kecamatan yang pertama kali dipilih
                 */
                'selectedDistrict',

                /*
                 * Sensor tanaman
                 */
                'cropSensors',

                /*
                 * Grafik NDVI
                 */
                'weeklyTrends',

                /*
                 * Insight analisis
                 */
                'insights',

                /*
                 * Siklus pertumbuhan
                 */
                'growthLifecycle',

                /*
                 * Rekomendasi
                 */
                'recommendations',

                /*
                 * Prediksi produktivitas
                 */
                'yieldPrediction',

                /*
                 * Feature importance XGBoost
                 */
                'featureImportance',

                /*
                 * Informasi sistem
                 */
                'aboutPillars',

                /*
                 * Google Maps
                 */
                'googleMapsKey',

                /*
                 * ============================
                 * DATA KHUSUS PETA
                 * ============================
                 */

                /*
                 * Checkbox layer peta
                 */
                'mapLayers',

                /*
                 * Legenda probability
                 */
                'probabilityLegend',

                /*
                 * Ringkasan luas area
                 */
                'mapSummary',

                /*
                 * Batas kecamatan GeoJSON
                 */
                'boundaryGeoJson',

                /*
                 * Status file ML
                 */
                'modelFiles'
            )
        );
    }

    /**
     * ============================================================
     * MEMBACA FILE JSON
     * ============================================================
     *
     * Fungsi ini digunakan supaya controller tidak langsung error
     * ketika file JSON tidak ditemukan atau JSON rusak.
     */
    private function readJsonFile(string $path): array
    {
        /*
         * Jika file tidak ada, kembalikan array kosong.
         */
        if (! is_file($path)) {
            return [];
        }

        /*
         * Membaca isi file.
         */
        $content = file_get_contents($path);

        /*
         * Jika file gagal dibaca, kembalikan array kosong.
         */
        if ($content === false) {
            return [];
        }

        /*
         * Jika file kosong, kembalikan array kosong.
         */
        if (trim($content) === '') {
            return [];
        }

        /*
         * Decode JSON menjadi array PHP.
         */
        $decoded = json_decode(
            $content,
            true
        );

        /*
         * Jika hasil decode bukan array,
         * kembalikan array kosong.
         */
        return is_array($decoded)
            ? $decoded
            : [];
    }

    /**
     * ============================================================
     * PARSE CSV
     * ============================================================
     *
     * Membaca file CSV menjadi:
     *
     * [
     *     [
     *         'Kecamatan' => 'Sukomoro',
     *         'Year' => 2025,
     *         ...
     *     ]
     * ]
     */
    private function parseCsv(string $path): array
    {
        /*
         * Jika file tidak ditemukan,
         * jangan menyebabkan dashboard crash.
         */
        if (! is_file($path)) {
            return [];
        }

        /*
         * Membuka file CSV.
         */
        $handle = fopen($path, 'r');

        /*
         * Jika gagal dibuka.
         */
        if ($handle === false) {
            return [];
        }

        /*
         * Membaca header CSV.
         */
        $headers = fgetcsv(
            $handle,
            0,
            ','
        );

        /*
         * Jika header kosong,
         * tutup file lalu kembalikan array kosong.
         */
        if ($headers === false) {
            fclose($handle);

            return [];
        }

        /*
         * Membersihkan header.
         *
         * Bagian BOM digunakan untuk mengatasi CSV yang
         * dibuat dari Excel/Windows sehingga karakter pertama
         * kadang bukan "Kecamatan" murni.
         */
        $headers = array_map(
            function ($header) {

                $header = (string) $header;

                /*
                 * Menghapus UTF-8 BOM.
                 */
                $header = preg_replace(
                    '/^\xEF\xBB\xBF/',
                    '',
                    $header
                );

                return trim((string) $header);
            },
            $headers
        );

        $rows = [];

        /*
         * Membaca baris satu per satu.
         */
        while (($data = fgetcsv(
            $handle,
            0,
            ','
        )) !== false) {

            /*
             * Jika baris kosong, lewati.
             */
            if (
                count($data) === 1
                && trim((string) $data[0]) === ''
            ) {
                continue;
            }

            /*
             * Jika jumlah kolom berbeda dengan header,
             * kita tetap mencoba menyesuaikan.
             */
            $data = array_pad(
                $data,
                count($headers),
                null
            );

            /*
             * Batasi data agar jumlahnya sama dengan header.
             */
            $data = array_slice(
                $data,
                0,
                count($headers)
            );

            /*
             * Gabungkan header dengan data.
             */
            $row = array_combine(
                $headers,
                $data
            );

            if ($row === false) {
                continue;
            }

            /*
             * Membersihkan nilai setiap kolom.
             */
            foreach ($row as $key => $value) {

                if (is_string($value)) {
                    $row[$key] = trim($value);
                }
            }

            $rows[] = $row;
        }

        /*
         * Tutup file.
         */
        fclose($handle);

        return $rows;
    }

    /**
     * ============================================================
     * MEMUAT PREDIKSI XGBOOST
     * ============================================================
     *
     * Fungsi ini mencoba membaca:
     *
     *     OOf_Site_Prediction_Nganjuk_V4.csv
     *
     * tanpa memaksakan nama kolom tertentu.
     *
     * Hal ini penting karena hasil CSV dari Colab bisa memiliki
     * nama kolom yang sedikit berbeda.
     */
    private function loadXgboostPredictions(
        string $mlPath
    ): array {

        /*
         * File utama hasil prediksi OOF.
         */
        $predictionPath = $mlPath
            .'/OOf_Site_Prediction_Nganjuk_V4.csv';

        /*
         * Jika file belum ada,
         * kembalikan array kosong.
         */
        if (! is_file($predictionPath)) {
            return [];
        }

        /*
         * Baca CSV.
         */
        $rows = $this->parseCsv(
            $predictionPath
        );

        if (empty($rows)) {
            return [];
        }

        /*
         * Ambil struktur kolom dari baris pertama.
         */
        $firstRow = $rows[0];

        /*
         * Cari kolom kecamatan/site.
         */
        $districtColumn = $this->findColumn(
            $firstRow,
            [
                'Kecamatan',
                'kecamatan',
                'District',
                'district',
                'Site',
                'site',
                'Lokasi',
                'lokasi',
            ]
        );

        /*
         * Cari kolom tahun.
         */
        $yearColumn = $this->findColumn(
            $firstRow,
            [
                'Year',
                'year',
                'Tahun',
                'tahun',
            ]
        );

        /*
         * Cari kolom prediksi.
         */
        $predictionColumn = $this->findColumn(
            $firstRow,
            [
                'Predicted',
                'predicted',
                'Prediction',
                'prediction',
                'Predicted_ton_ha',
                'predicted_ton_ha',
                'Prediksi_ton_ha',
                'prediksi_ton_ha',
                'y_pred',
                'Y_pred',
                'pred_ton_ha',
                'pred_yield_ton_ha',
            ]
        );

        /*
         * Jika kolom kecamatan atau prediksi tidak ditemukan,
         * jangan membuat asumsi terhadap data.
         */
        if (
            $districtColumn === null
            || $predictionColumn === null
        ) {
            return [];
        }

        $predictions = [];

        /*
         * Membaca setiap baris prediksi.
         */
        foreach ($rows as $row) {

            /*
             * Ambil nama kecamatan/site.
             */
            $district = strtolower(
                trim(
                    (string) (
                        $row[$districtColumn] ?? ''
                    )
                )
            );

            if ($district === '') {
                continue;
            }

            /*
             * Jika tahun tidak tersedia,
             * sementara digunakan 2025.
             */
            $year = $yearColumn !== null
                ? (int) (
                    $row[$yearColumn] ?? 2025
                )
                : 2025;

            /*
             * Ambil nilai prediksi.
             */
            $prediction = $this->toNullableFloat(
                $row[$predictionColumn] ?? null
            );

            /*
             * Jangan masukkan nilai yang tidak valid.
             */
            if ($prediction === null) {
                continue;
            }

            /*
             * Simpan dengan struktur:
             *
             * [
             *     'sukomoro' => [
             *         2025 => 10.97
             *     ]
             * ]
             */
            $predictions[$district][$year] =
                $prediction;
        }

        return $predictions;
    }

    /**
     * ============================================================
     * MENCARI NAMA KOLOM CSV
     * ============================================================
     *
     * Fungsi ini membuat controller lebih fleksibel terhadap
     * perbedaan penamaan kolom dari hasil Google Colab.
     */
    private function findColumn(
        array $row,
        array $candidates
    ): ?string {

        /*
         * Normalisasi semua nama kolom yang tersedia.
         */
        $available = [];

        foreach (array_keys($row) as $key) {

            $normalized = $this->normalizeColumnName(
                (string) $key
            );

            $available[$normalized] = $key;
        }

        /*
         * Coba satu per satu nama kolom yang mungkin.
         */
        foreach ($candidates as $candidate) {

            $normalizedCandidate =
                $this->normalizeColumnName(
                    $candidate
                );

            if (
                isset(
                    $available[$normalizedCandidate]
                )
            ) {
                return $available[$normalizedCandidate];
            }
        }

        return null;
    }

    /**
     * ============================================================
     * NORMALISASI NAMA KOLOM
     * ============================================================
     */
    private function normalizeColumnName(
        string $value
    ): string {

        return strtolower(
            preg_replace(
                '/[^a-zA-Z0-9]/',
                '',
                trim($value)
            )
        );
    }

    /**
     * ============================================================
     * KONVERSI NILAI KE FLOAT
     * ============================================================
     *
     * Jika nilai bukan angka,
     * fungsi mengembalikan null.
     */
    private function toNullableFloat(
        mixed $value
    ): ?float {

        if (
            $value === null
            || $value === ''
        ) {
            return null;
        }

        /*
         * Ubah koma desimal menjadi titik.
         */
        if (is_string($value)) {
            $value = str_replace(
                ',',
                '.',
                trim($value)
            );
        }

        return is_numeric($value)
            ? (float) $value
            : null;
    }

    /**
     * ============================================================
     * MENCARI FILE PERTAMA YANG TERSEDIA
     * ============================================================
     */
    private function firstExistingPath(
        array $paths
    ): ?string {

        foreach ($paths as $path) {

            if (is_file($path)) {
                return $path;
            }
        }

        return null;
    }

    /**
     * ============================================================
     * LABEL BAND SENTINEL-2
     * ============================================================
     *
     * Mengubah kode band menjadi nama yang lebih mudah dibaca.
     */
    private function bandLabel(
        string $feature
    ): string {

        $labels = [

            'B2' => 'B2 - Blue',

            'B3' => 'B3 - Green',

            'B4' => 'B4 - Red',

            'B8' => 'B8 - NIR',

            'B11' => 'B11 - SWIR-1',

            'B12' => 'B12 - SWIR-2',

            'NDVI' => 'NDVI - Indeks Vegetasi',

            'NDWI' => 'NDWI - Indeks Kelembapan',

            'NDBI' => 'NDBI - Indeks Bangunan',

            'BSI' => 'BSI - Bare Soil Index',
        ];

        /*
         * Jika label ditemukan,
         * gunakan label tersebut.
         */
        if (isset($labels[$feature])) {
            return $labels[$feature];
        }

        /*
         * Jika tidak ditemukan,
         * tampilkan nama feature asli.
         */
        return $feature;
    }
}
