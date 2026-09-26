<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Response as ResponseFacade;

class DashboardController extends Controller
{
    /**
     * ============================================================
     * DASHBOARD UTAMA SI BAWANG MERAH
     * ============================================================
     *
     * SEMUA DATA TETAP BERADA DI:
     *
     *     ml_model/data/
     *
     * Struktur penting:
     *
     * ml_model/
     * └── data/
     *     ├── feature_importance.json
     *     ├── model_metrics.json
     *     ├── OOf_Site_Prediction_Nganjuk_V4.csv
     *     ├── dataset_ml_nganjuk_v4.csv
     *     ├── dataset_ml_nganjuk_v4_qc.csv
     *     ├── dataset_ml_nganjuk_v4_1_STRICT.csv
     *     ├── dataset_ml_nganjuk_v4_1_EXTENDED.csv
     *     ├── FeatureStack_Nganjuk_2025.tif
     *     ├── FeatureStack_Nganjuk_V3_FINAL.tif
     *     ├── OFFICIAL_DATA_2025/
     *     │   └── Batas_4_Kecamatan_Nganjuk.geojson
     *     ├── YIELD_PREDICTION/
     *     │   ├── Yield_Dataset_Nganjuk_2023_2025.csv
     *     │   └── ...
     *     └── PAPER_FINAL_V4/
     *         └── XGBOOST_V4_1/
     *             ├── FASE_4B_STRICT_Mapping_Summary.json
     *             ├── Probability_Bawang_Nganjuk_4Kec_V4_1_STRICT.tif
     *             ├── Binary_Bawang_Nganjuk_4Kec_V4_1_STRICT_T050.tif
     *             ├── Area_Bawang_Per_Kecamatan_V4_1_STRICT.csv
     *             ├── Candidate_Bawang_DOA_T050_V4_1_*.tif
     *             └── ...
     *
     * CATATAN:
     * - TIF TIDAK dipindahkan ke public/data.
     * - TIF tetap berada di ml_model/data.
     * - TIF disajikan ke browser melalui route whitelist di bawah.
     * - JSON mapping dibaca langsung dari ml_model/data.
     * - GeoJSON juga dibaca langsung dari ml_model/data.
     */
    public function index(): View
    {
        /*
        |--------------------------------------------------------------------------
        | 1. LOKASI UTAMA DATASET
        |--------------------------------------------------------------------------
        */
        $mlPath = base_path('ml_model/data');

        /*
        |--------------------------------------------------------------------------
        | 2. PATH DATASET UTAMA
        |--------------------------------------------------------------------------
        */
        $mappingPath = $mlPath
            .'/PAPER_FINAL_V4/XGBOOST_V4_1/FASE_4B_STRICT_Mapping_Summary.json';

        $mappingDir = $mlPath
            .'/PAPER_FINAL_V4/XGBOOST_V4_1';

        /*
        |--------------------------------------------------------------------------
        | 3. FEATURE IMPORTANCE
        |--------------------------------------------------------------------------
        */
        $featureImportanceRaw = $this->readJsonFile(
            $mlPath.'/feature_importance.json'
        );

        /*
        |--------------------------------------------------------------------------
        | 4. MODEL METRICS
        |--------------------------------------------------------------------------
        */
        $modelMetricsRaw = $this->readJsonFile(
            $mlPath.'/model_metrics.json'
        );

        /*
        |--------------------------------------------------------------------------
        | 5. FEATURE IMPORTANCE -> FORMAT BLADE
        |--------------------------------------------------------------------------
        */
        $featureImportance = [];

        foreach ($featureImportanceRaw as $item) {

            if (! is_array($item)) {
                continue;
            }

            $feature = (string) ($item['feature'] ?? '');
            $description = (string) ($item['description'] ?? '');

            $importance = isset($item['importance'])
                ? $this->toNullableFloat($item['importance'])
                : null;

            $featureImportance[] = [
                'feature' => $feature,
                'name' => $this->bandLabel($feature),
                'role' => $description,
                'weight' => $importance !== null
                    ? round($importance * 100, 1)
                    : null,
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | 6. TEMPORAL CROSS VALIDATION
        |--------------------------------------------------------------------------
        */
        $tc = $modelMetricsRaw['temporal_cv'] ?? [];
        $tc_ton = $modelMetricsRaw['temporal_cv_ton_ha'] ?? [];

        /*
        |--------------------------------------------------------------------------
        | 7. DATA YIELD SENTINEL-2 + BPS
        |--------------------------------------------------------------------------
        */
        $yieldCsvPath = $mlPath
            .'/YIELD_PREDICTION/Yield_Dataset_Nganjuk_2023_2025.csv';

        $csvData = $this->parseCsv($yieldCsvPath);

        /*
        |--------------------------------------------------------------------------
        | 8. DATA TERBARU 2025
        |--------------------------------------------------------------------------
        */
        $latest = [];

        foreach ($csvData as $row) {

            $year = (int) ($row['Year'] ?? 0);

            $district = $this->normalizeDistrict(
                $row['Kecamatan'] ?? ''
            );

            if ($year === 2025 && $district !== '') {
                $latest[$district] = $row;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | 9. PREDIKSI XGBOOST
        |--------------------------------------------------------------------------
        */
        $xgboostPredictions = $this->loadXgboostPredictions($mlPath);

        /*
        |--------------------------------------------------------------------------
        | 10. FALLBACK SUKOMORO
        |--------------------------------------------------------------------------
        |
        | Dipertahankan dari controller lama agar bagian dashboard
        | produktivitas tidak hilang.
        |
        | Nilai OOF tetap diprioritaskan apabila tersedia.
        |--------------------------------------------------------------------------
        */
        $xgboostPredSukomoro = [
            2023 => 10.65,
            2024 => 11.14,
            2025 => 10.97,
        ];

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
        | 11. HISTORY SUKOMORO
        |--------------------------------------------------------------------------
        */
        $sukomoroHistory = array_filter(
            $csvData,
            fn ($r) =>
                $this->normalizeDistrict(
                    $r['Kecamatan'] ?? ''
                ) === 'sukomoro'
        );

        $comparisons = [];

        foreach ($sukomoroHistory as $row) {

            $year = (int) ($row['Year'] ?? 0);

            $actualTon = $this->toNullableFloat(
                $row['Produktivitas_ton_ha'] ?? null
            );

            $predTon = $xgboostPredSukomoro[$year] ?? null;

            $comparisons[] = [
                'season' => "Panen {$year} (BPS Sukomoro)",
                'actual' => $actualTon !== null
                    ? number_format($actualTon, 2).' T'
                    : '—',
                'predicted' => $predTon !== null
                    ? number_format($predTon, 2).' T'
                    : '—',
                'actual_pct' => $actualTon !== null
                    ? (int) min(95, max(0, $actualTon * 8))
                    : 0,
                'pred_pct' => $predTon !== null
                    ? (int) min(95, max(0, $predTon * 8))
                    : 0,
                'is_current' => false,
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | 12. PROYEKSI 2026
        |--------------------------------------------------------------------------
        |
        | Tetap dipertahankan supaya tidak mengurangi tampilan lama.
        |--------------------------------------------------------------------------
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
        | 13. EMPAT KECAMATAN
        |--------------------------------------------------------------------------
        */
        $districts = [
            'Sukomoro',
            'Bagor',
            'Gondang',
            'Rejoso',
        ];

        /*
        |--------------------------------------------------------------------------
        | 14. KONFIGURASI PARSEL
        |--------------------------------------------------------------------------
        |
        | Struktur tampilan lama dipertahankan.
        | Data luas/produksi akan dioverride jika dataset area resmi
        | menyediakan data yang sesuai.
        |--------------------------------------------------------------------------
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
        | 15. DATA AREA HASIL MODEL
        |--------------------------------------------------------------------------
        |
        | Tidak lagi menulis angka luas peta secara manual.
        | Controller mencari CSV area dari output FASE_4B.
        |--------------------------------------------------------------------------
        */
        $areaData = $this->loadAreaData($mappingDir);

        foreach ($areaData['districts'] as $districtId => $areaRow) {

            if (! isset($parcelsConfig[$districtId])) {
                continue;
            }

            if (
                isset($areaRow['area_ha'])
                && $areaRow['area_ha'] !== null
            ) {
                $parcelsConfig[$districtId]['area'] =
                    number_format(
                        $areaRow['area_ha'],
                        2
                    ).' Ha';
            }

            if (
                isset($areaRow['production'])
                && $areaRow['production'] !== null
            ) {
                $parcelsConfig[$districtId]['production'] =
                    $this->formatNumberSmart(
                        $areaRow['production']
                    );
            }
        }

        /*
        |--------------------------------------------------------------------------
        | 16. DATA PARSEL + SENTINEL-2
        |--------------------------------------------------------------------------
        */
        $parcels = [];

        foreach ($parcelsConfig as $id => $cfg) {

            $row = $latest[$id] ?? null;

            $ndwi = $row !== null
                ? $this->toNullableFloat(
                    $row['NDWI'] ?? null
                )
                : null;

            $moisturePct = null;

            if ($ndwi !== null) {
                $moisturePct = max(
                    40,
                    min(
                        90,
                        (int) round(
                            68 + ($ndwi + 0.47) * 100
                        )
                    )
                );
            }

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

            $parcels[] = array_merge(
                $cfg,
                [
                    'id' => $id,

                    'actual_yield' => $row !== null
                        && isset(
                            $row['Produktivitas_ton_ha']
                        )
                        ? number_format(
                            (float) $row[
                                'Produktivitas_ton_ha'
                            ],
                            2
                        ).' Ton/Ha'
                        : null,

                    'predicted_yield' =>
                        $districtPrediction !== null
                            ? number_format(
                                $districtPrediction,
                                2
                            ).' Ton/Ha'
                            : null,

                    'ndvi' => $row !== null
                        ? $this->toNullableFloat(
                            $row['NDVI'] ?? null
                        )
                        : null,

                    'ndwi' => $ndwi,

                    'b12' => $row !== null
                        ? $this->toNullableFloat(
                            $row['B12'] ?? null
                        )
                        : null,

                    'b4' => $row !== null
                        ? $this->toNullableFloat(
                            $row['B4'] ?? null
                        )
                        : null,

                    'b11' => $row !== null
                        ? $this->toNullableFloat(
                            $row['B11'] ?? null
                        )
                        : null,

                    'b8' => $row !== null
                        ? $this->toNullableFloat(
                            $row['B8'] ?? null
                        )
                        : null,

                    'b3' => $row !== null
                        ? $this->toNullableFloat(
                            $row['B3'] ?? null
                        )
                        : null,

                    'b2' => $row !== null
                        ? $this->toNullableFloat(
                            $row['B2'] ?? null
                        )
                        : null,

                    'moisture' => $moisturePct !== null
                        ? "{$moisturePct}%"
                        : null,

                    'probability' =>
                        $areaData['districts'][$id]['probability']
                        ?? null,

                    'candidate' =>
                        $areaData['districts'][$id]['candidate']
                        ?? null,

                    'candidate_doa' =>
                        $areaData['districts'][$id]['candidate_doa']
                        ?? null,
                ]
            );
        }

        /*
        |--------------------------------------------------------------------------
        | 17. ML RESULTS UNTUK PETA
        |--------------------------------------------------------------------------
        |
        | Blade lama menggunakan $mlResults.
        | Variabel ini tetap disediakan.
        |--------------------------------------------------------------------------
        */
        $mlResults = [];

        foreach ($parcels as $parcel) {

            $mlResults[$parcel['id']] = [
                'name' => 'Kecamatan '
                    .ucwords(
                        str_replace(
                            '-',
                            ' ',
                            $parcel['id']
                        )
                    ),

                'status' => $parcel['status']
                    ?? 'Data ML tersedia',

                'status_badge' =>
                    $parcel['status_code'] === 'warning'
                        ? 'bg-amber-50 text-amber-700 border-amber-200'
                        : 'bg-emerald-50 text-emerald-700 border-emerald-200',

                'area' => $parcel['area'] ?? '-',

                'plots' =>
                    $parcel['production']
                    ?? 'Data area tersedia',

                'yield_info' =>
                    $parcel['predicted_yield']
                    ?? 'Belum ada prediksi',

                'ndvi' =>
                    $parcel['ndvi'] !== null
                        ? number_format(
                            $parcel['ndvi'],
                            3
                        )
                        : '-',

                'ndvi_sub' => 'Sentinel-2 2025',

                'moisture' =>
                    $parcel['moisture']
                    ?? 'Data kelembapan belum tersedia',

                'moisture_sub' => $parcel['ndwi'] !== null
                    ? 'NDWI '
                        .number_format(
                            $parcel['ndwi'],
                            3
                        )
                    : 'Data NDWI belum tersedia',

                'probability' =>
                    $parcel['probability'],

                'candidate' =>
                    $parcel['candidate'],

                'candidate_doa' =>
                    $parcel['candidate_doa'],
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | 18. KPI UTAMA
        |--------------------------------------------------------------------------
        */
        $sukomoroLatest = $latest['sukomoro'] ?? null;

        $ndviLatest = $sukomoroLatest !== null
            ? $this->toNullableFloat(
                $sukomoroLatest['NDVI'] ?? null
            )
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
                        .number_format(
                            $ndviLatest,
                            3
                        )
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
                ).' Ton/Ha',

                'subtitle' =>
                    'Estimasi XGBoost Temporal CV',

                'color' => 'rose',

                'icon' => 'chart-bar',
            ],

            [
                'title' => 'Analisis Pertumbuhan',

                'value' => 'Fase Vegetatif',

                'subtitle' =>
                    'Estimasi Musim Tanam 2026',

                'color' => 'green',

                'icon' => 'activity',
            ],

            [
                'title' => 'Rekomendasi',

                'value' => 'Siram Pagi Hari',

                'subtitle' =>
                    'Kelembapan SWIR-2 Terjaga',

                'color' => 'amber',

                'icon' => 'droplet',
            ],
        ];

        /*
        |--------------------------------------------------------------------------
        | 19. DATA KECAMATAN TERPILIH
        |--------------------------------------------------------------------------
        */
        $ndwi2025 = $sukomoroLatest !== null
            ? $this->toNullableFloat(
                $sukomoroLatest['NDWI'] ?? null
            )
            : null;

        $selectedDistrict = [

            'name' => 'Kecamatan Sukomoro',

            'condition_badge' =>
                'Kondisi Sangat Baik',

            'total_area' =>
                $mlResults['sukomoro']['area']
                ?? 'Data belum tersedia',

            'total_plots' =>
                $mlResults['sukomoro']['plots']
                ?? 'Data belum tersedia',

            'ndvi_average' =>
                $ndviLatest !== null
                    ? 'Sehat ('
                        .number_format(
                            $ndviLatest,
                            3
                        )
                        .')'
                    : null,

            'ndvi_note' =>
                $ndviLatest !== null
                    ? 'Sentinel-2 Komposit 2025'
                    : null,

            'moisture_average' =>
                $ndwi2025 !== null
                    ? 'NDWI '
                        .number_format(
                            $ndwi2025,
                            3
                        )
                    : null,

            'moisture_note' =>
                $ndwi2025 !== null
                    ? 'Kelembapan kanopi (SWIR-2/NIR)'
                    : null,

            'pest_status' => null,

            'pest_note' => null,
        ];

        /*
        |--------------------------------------------------------------------------
        | 20. SENSOR TANAMAN
        |--------------------------------------------------------------------------
        */
        $b8 = $sukomoroLatest !== null
            ? $this->toNullableFloat(
                $sukomoroLatest['B8'] ?? null
            )
            : null;

        $b4 = $sukomoroLatest !== null
            ? $this->toNullableFloat(
                $sukomoroLatest['B4'] ?? null
            )
            : null;

        $ndviPct = $ndviLatest !== null
            ? (int) round($ndviLatest * 100)
            : null;

        $b12 = $sukomoroLatest !== null
            ? $this->toNullableFloat(
                $sukomoroLatest['B12'] ?? null
            )
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
                    ? number_format(
                        $ndviLatest,
                        3
                    )
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

                'description' =>
                    $b8 !== null && $b4 !== null
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
                        .number_format(
                            $b12,
                            4
                        )
                        .')'
                    : null,

                'description' =>
                    $b12 !== null && $ndwi2025 !== null
                        ? "Band SWIR-2 B12={$b12} dan NDWI={$ndwi2025} menunjukkan kondisi kelembapan tajuk tanaman dari citra Sentinel-2 2025."
                        : null,

                'percent' => $moisturePctMain,
            ],
        ];

        /*
        |--------------------------------------------------------------------------
        | 21. TREN NDVI MINGGUAN
        |--------------------------------------------------------------------------
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
                'height_pct' =>
                    (int) round(
                        $ndviPeak * 100
                    ),
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
        | 22. INSIGHT ANALISIS CITRA
        |--------------------------------------------------------------------------
        */
        $b11 = $sukomoroLatest !== null
            ? $this->toNullableFloat(
                $sukomoroLatest['B11'] ?? null
            )
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

                'title' =>
                    'Hasil Interpretasi Satelit Sentinel-2',

                'highlight' => $ndviLatest !== null
                    ? 'NDVI: '
                        .number_format(
                            $ndviLatest,
                            3
                        )
                        .' (Vegetasi '
                        .(
                            $ndviLatest >= 0.5
                                ? 'Baik'
                                : 'Sedang'
                        )
                        .')'
                    : null,

                'description' =>
                    'Dedaunan bawang merah Sukomoro: '
                    .'B4 Red='
                    .($b4 !== null ? $b4 : '—')
                    .', B8 NIR='
                    .($b8 !== null ? $b8 : '—')
                    .'. Nilai NDVI menunjukkan serapan klorofil dari komposit tahunan Sentinel-2 2025.',

                'color' => 'emerald',
            ],

            [
                'number' => '2',

                'title' =>
                    'Analisis Kelembapan & Risiko Penyakit',

                'highlight' =>
                    "SWIR-1 B11={$b11Fmt} | SWIR-2 B12={$b12Fmt}",

                'description' =>
                    'Kombinasi SWIR-1 dan SWIR-2 mengindikasikan kadar air tajuk. '
                    ."NDWI={$ndwiInsight} menunjukkan kelembapan kanopi dari ekstraksi Sentinel-2 2025.",

                'color' => 'rose',
            ],
        ];

        /*
        |--------------------------------------------------------------------------
        | 23. SIKLUS PERTUMBUHAN
        |--------------------------------------------------------------------------
        */
        $growthLifecycle = [

            'subtitle' =>
                'Fase Vegetatif (Estimasi Musim Tanam 2026)',

            'description' =>
                'Tanaman bawang merah sedang aktif membentuk anakan dan memperkuat daun sebelum memasuki fase pembentukan umbi.',

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
        | 24. REKOMENDASI
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

                'description' =>
                    'Pertahankan kelembapan tanah 60–70%. '
                    .'Siram pagi hari sebelum jam 08.00 WIB. '
                    ."NDWI Sukomoro 2025: {$ndwiLabel} — dalam kisaran optimal.",

                'icon' => 'droplet',

                'badge' => 'Irigasi',

                'color' => 'emerald',
            ],

            [
                'title' => 'Waspada Daun',

                'description' =>
                    'Periksa bercak ungu (Alternaria porri) secara berkala. '
                    ."NDVI Sukomoro 2025: {$ndviLabel} — vegetasi aktif, risiko penyakit rendah.",

                'icon' => 'shield',

                'badge' => 'Proteksi',

                'color' => 'blue',
            ],

            [
                'title' => 'Drainase Lahan',

                'description' =>
                    'Pastikan saluran drainase tidak mampet menjelang musim hujan agar umbi tidak tergenang. Pantau reflektansi SWIR-2 (B12) secara berkala.',

                'icon' => 'cloud-sun',

                'badge' => 'Cuaca',

                'color' => 'amber',
            ],
        ];

        /*
        |--------------------------------------------------------------------------
        | 25. PREDIKSI PRODUKTIVITAS
        |--------------------------------------------------------------------------
        */
        $projectedYield =
            $xgboostPredSukomoro[2025];

        /*
        |--------------------------------------------------------------------------
        | 26. METRIK MODEL
        |--------------------------------------------------------------------------
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
        | 27. AKURASI MODEL
        |--------------------------------------------------------------------------
        */
        if (
            $mape !== null
            && $accuracyApprox !== null
        ) {

            $accuracyBadge =
                'MAPE '
                .number_format(
                    $mape,
                    2
                )
                .'% | Akurasi ~'
                .number_format(
                    $accuracyApprox,
                    0
                )
                .'% (Temporal CV)';

        } else {

            $accuracyBadge =
                'Metrik Temporal CV belum tersedia';
        }

        /*
        |--------------------------------------------------------------------------
        | 28. YIELD PREDICTION
        |--------------------------------------------------------------------------
        */
        $yieldPrediction = [

            'status_badge' =>
                'Status Prediksi: Produktivitas Sangat Baik',

            'estimate_title' =>
                'Estimasi Panen Musim Ini (Sukomoro)',

            'estimate_desc' =>
                'Diproyeksikan dari data wilayah berdasarkan model XGBoost terlatih dengan komposit tahunan Sentinel-2 (Leave-One-Year-Out CV).',

            'projected_yield' =>
                number_format(
                    $projectedYield,
                    2
                ),

            'unit' =>
                'Ton / Hektar',

            'accuracy_badge' =>
                $accuracyBadge,

            'comparisons' =>
                $comparisons,

            'model_accuracy' => [

                'rate' =>
                    $accuracyApprox !== null
                        ? number_format(
                            $accuracyApprox,
                            0
                        ).'%'
                        : '—',

                'mae' =>
                    $maeTon !== null
                        ? number_format(
                            $maeTon,
                            2
                        ).' T'
                        : '—',

                'rmse' =>
                    $rmseTon !== null
                        ? number_format(
                            $rmseTon,
                            2
                        ).' T'
                        : '—',

                'r_score' =>
                    $r2 !== null
                        ? number_format(
                            $r2,
                            3
                        )
                        : '—',

                'note' =>
                    $mape !== null && $r2 !== null
                        ? 'Evaluasi Temporal Cross-Validation (Leave-One-Year-Out) XGBoost, 4 kecamatan Nganjuk 2023-2025. MAPE: '
                            .number_format(
                                $mape,
                                2
                            )
                            .'%. R² = '
                            .number_format(
                                $r2,
                                3
                            )
                            .' (12 observasi tahunan).'
                        : 'Data evaluasi model belum tersedia.',
            ],
        ];

        /*
        |--------------------------------------------------------------------------
        | 29. PILAR SISTEM
        |--------------------------------------------------------------------------
        */
        $aboutPillars = [

            [
                'title' =>
                    'Citra Satelit Sentinel-2',

                'description' =>
                    'Data citra multispektral resolusi 10m (B2, B3, B4, B8, B11, B12) untuk memantau klorofil dan kelembapan lahan secara berkala.',

                'icon' => 'satellite',

                'color' => 'rose',
            ],

            [
                'title' =>
                    'Algoritma XGBoost',

                'description' =>
                    'Model machine learning gradient boosting terlatih dengan validasi silang temporal dan spasial untuk estimasi panen presisi.',

                'icon' => 'cpu',

                'color' => 'emerald',
            ],

            [
                'title' =>
                    'Validasi Data BPS Nganjuk',

                'description' =>
                    'Tervalidasi dengan data produktivitas BPS Kabupaten Nganjuk (Sukomoro, Bagor, Gondang, Rejoso) tahun 2023-2025.',

                'icon' => 'clipboard',

                'color' => 'amber',
            ],

            [
                'title' =>
                    'Panduan Agronomi Nyata',

                'description' =>
                    'Memberikan saran teknis pertanian berbasis data sensor satelit seperti jadwal siram, pupuk susulan, dan pencegahan hama.',

                'icon' => 'sprout',

                'color' => 'purple',
            ],
        ];

        /*
        |--------------------------------------------------------------------------
        | 30. LAYER PETA
        |--------------------------------------------------------------------------
        */
        $mapLayers = [

            [
                'id' => 'boundary',
                'label' => 'Batas Kecamatan',
                'description' =>
                    'Area administrasi penelitian',
                'type' => 'boundary',
                'checked' => true,
                'group' => 'Layer Peta',
            ],

            [
                'id' => 'probability',
                'label' => 'Probability Map',
                'description' =>
                    'Probabilitas kandidat bawang',
                'type' => 'probability',
                'checked' => false,
                'group' => 'Layer Peta',
            ],

            [
                'id' => 'candidate',
                'label' => 'Candidate T = 0.50',
                'description' =>
                    'Kandidat setelah threshold',
                'type' => 'candidate',
                'checked' => false,
                'group' => 'Layer Peta',
                'threshold' => 0.50,
            ],

            [
                'id' => 'candidate_doa',
                'label' => 'Candidate + DOA',
                'description' =>
                    'Kandidat setelah pembatasan DOA',
                'type' => 'candidate_doa',
                'checked' => false,
                'group' => 'Layer Peta',
            ],
        ];

        /*
        |--------------------------------------------------------------------------
        | 31. LEGEND PROBABILITY
        |--------------------------------------------------------------------------
        |
        | Threshold mengikuti file mapping:
        | <0.35, 0.35-<0.70, >=0.70.
        |--------------------------------------------------------------------------
        */
        $probabilityLegend = [

            [
                'id' => 'low',
                'label' => 'Rendah',
                'description' => '< 0.35',
                'min' => 0,
                'max' => 0.35,
            ],

            [
                'id' => 'medium',
                'label' => 'Sedang',
                'description' => '0.35 – 0.69',
                'min' => 0.35,
                'max' => 0.70,
            ],

            [
                'id' => 'high',
                'label' => 'Tinggi',
                'description' => '≥ 0.70',
                'min' => 0.70,
                'max' => 1,
            ],
        ];

        /*
        |--------------------------------------------------------------------------
        | 32. GEOJSON BATAS KECAMATAN
        |--------------------------------------------------------------------------
        */
        $boundaryGeoJson = [];

        $boundaryCandidates = [

            $mlPath
                .'/OFFICIAL_DATA_2025/Batas_4_Kecamatan_Nganjuk.geojson',

            $mlPath
                .'/OFFICIAL_DATA_2025/batas_4_kecamatan_nganjuk.geojson',

            $mlPath
                .'/Batas_4_Kecamatan_Nganjuk.geojson',

            $mlPath
                .'/batas_4_kecamatan_nganjuk.geojson',

            base_path(
                'public/geojson/Batas_4_Kecamatan_Nganjuk.geojson'
            ),
        ];

        $boundaryPath =
            $this->firstExistingPath(
                $boundaryCandidates
            );

        if ($boundaryPath !== null) {

            $boundaryData =
                $this->readJsonFile(
                    $boundaryPath
                );

            if (
                isset($boundaryData['type'])
                && is_array($boundaryData)
            ) {
                $boundaryGeoJson =
                    $boundaryData;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | 33. RINGKASAN PETA DARI DATASET AKTUAL
        |--------------------------------------------------------------------------
        |
        | Angka tidak ditulis lagi sebagai:
        | 33,224.52 / 26,854.45 / 26,047.32
        | secara manual.
        |
        | Controller membaca:
        | - FASE_4B_STRICT_Mapping_Summary.json
        | - Area_Bawang_Per_Kecamatan_V4_1_STRICT.csv
        | - file area/candidate/DOA jika tersedia.
        |--------------------------------------------------------------------------
        */
        $mappingSummary =
            $this->readJsonFile(
                $mappingPath
            );

        $mapSummary = $this->buildMapSummary(
            $mappingSummary,
            $areaData
        );

        /*
        |--------------------------------------------------------------------------
        | 34. URL ASSET MAP
        |--------------------------------------------------------------------------
        |
        | TIF tetap berada di ml_model/data.
        | Browser mengambil TIF melalui:
        |
        |     /map-data/probability
        |
        | bukan dari public/data.
        |--------------------------------------------------------------------------
        */
        $mapAssets = [

            'probability' => route(
                'map.asset',
                ['asset' => 'probability']
            ),

            'candidate' => route(
                'map.asset',
                ['asset' => 'candidate']
            ),

            'candidate_doa' => route(
                'map.asset',
                ['asset' => 'candidate_doa']
            ),
        ];

        /*
        |--------------------------------------------------------------------------
        | 35. STATUS FILE MODEL
        |--------------------------------------------------------------------------
        */
        $modelFiles = [

            'feature_importance' =>
                is_file(
                    $mlPath.'/feature_importance.json'
                ),

            'model_metrics' =>
                is_file(
                    $mlPath.'/model_metrics.json'
                ),

            'yield_dataset' =>
                is_file(
                    $mlPath
                    .'/YIELD_PREDICTION/Yield_Dataset_Nganjuk_2023_2025.csv'
                ),

            'oof_prediction' =>
                is_file(
                    $mlPath
                    .'/OOf_Site_Prediction_Nganjuk_V4.csv'
                ),

            'dataset_ml' =>
                is_file(
                    $mlPath
                    .'/dataset_ml_nganjuk_v4.csv'
                ),

            'dataset_qc' =>
                is_file(
                    $mlPath
                    .'/dataset_ml_nganjuk_v4_qc.csv'
                ),

            'dataset_strict' =>
                is_file(
                    $mlPath
                    .'/dataset_ml_nganjuk_v4_1_STRICT.csv'
                ),

            'dataset_extended' =>
                is_file(
                    $mlPath
                    .'/dataset_ml_nganjuk_v4_1_EXTENDED.csv'
                ),

            'feature_stack_2025' =>
                is_file(
                    $mlPath
                    .'/FeatureStack_Nganjuk_2025.tif'
                ),

            'feature_stack_final' =>
                is_file(
                    $mlPath
                    .'/FeatureStack_Nganjuk_V3_FINAL.tif'
                ),

            'mapping_summary' =>
                is_file($mappingPath),

            'probability_tif' =>
                $this->mapAssetPath('probability') !== null,

            'candidate_tif' =>
                $this->mapAssetPath('candidate') !== null,

            'candidate_doa_tif' =>
                $this->mapAssetPath('candidate_doa') !== null,

            'area_table' =>
                $areaData['source'] !== null,
        ];

        /*
        |--------------------------------------------------------------------------
        | 36. GOOGLE MAPS API KEY
        |--------------------------------------------------------------------------
        */
        $googleMapsKey =
            config(
                'services.google_maps.api_key',
                ''
            );

        /*
        |--------------------------------------------------------------------------
        | 37. KIRIM SEMUA DATA KE VIEW
        |--------------------------------------------------------------------------
        */
        return view(
            'welcome',
            compact(

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

                'googleMapsKey',

                'mapLayers',

                'probabilityLegend',

                'mapSummary',

                'boundaryGeoJson',

                'modelFiles',

                'mlResults',

                'mappingSummary',

                'mapAssets'
            )
        );
    }

    /**
     * ============================================================
     * STREAM TIF MAP
     * ============================================================
     *
     * TIF TETAP DI:
     *
     * ml_model/data/PAPER_FINAL_V4/XGBOOST_V4_1/
     *
     * Browser tidak perlu mengakses folder ml_model secara langsung.
     */
    public function mapAsset(string $asset): Response
    {
        $path = $this->mapAssetPath($asset);

        abort_if(
            $path === null,
            404,
            'File raster peta tidak ditemukan.'
        );

        return ResponseFacade::file(
            $path,
            [
                'Content-Type' => 'image/tiff',
                'Cache-Control' =>
                    'public, max-age=3600',
            ]
        );
    }

    /**
     * ============================================================
     * MENCARI PATH ASSET MAP
     * ============================================================
     */
    private function mapAssetPath(
        string $asset
    ): ?string {

        $dir =
            base_path(
                'ml_model/data/PAPER_FINAL_V4/XGBOOST_V4_1'
            );

        /*
         * Whitelist.
         * Tidak menerima path bebas dari user/browser.
         */
        $candidates = [

            'probability' => [
                $dir
                .'/Probability_Bawang_Nganjuk_4Kec_V4_1_STRICT.tif',
            ],

            'candidate' => [
                $dir
                .'/Binary_Bawang_Nganjuk_4Kec_V4_1_STRICT_T050.tif',
            ],

            'candidate_doa' => [
                $dir
                .'/Candidate_Bawang_DOA_T050_V4_1.tif',

                $dir
                .'/Candidate_Bawang_DOA_T050_V4_1_STRICT.tif',
            ],
        ];

        if (! isset($candidates[$asset])) {
            return null;
        }

        $path =
            $this->firstExistingPath(
                $candidates[$asset]
            );

        /*
         * Jika Candidate DOA mempunyai nama file sedikit berbeda,
         * coba cari hanya pada folder XGBOOST_V4_1.
         */
        if (
            $path === null
            && $asset === 'candidate_doa'
        ) {

            $globResults = glob(
                $dir
                .'/*Candidate*DOA*T050*.tif'
            );

            if (
                is_array($globResults)
                && ! empty($globResults)
            ) {
                $path = $globResults[0];
            }
        }

        return $path;
    }

    /**
     * ============================================================
     * MEMBACA JSON
     * ============================================================
     */
    private function readJsonFile(
        string $path
    ): array {

        if (! is_file($path)) {
            return [];
        }

        $content =
            file_get_contents($path);

        if (
            $content === false
            || trim($content) === ''
        ) {
            return [];
        }

        $decoded =
            json_decode(
                $content,
                true
            );

        return is_array($decoded)
            ? $decoded
            : [];
    }

    /**
     * ============================================================
     * PARSE CSV
     * ============================================================
     */
    private function parseCsv(
        string $path
    ): array {

        if (! is_file($path)) {
            return [];
        }

        $handle =
            fopen($path, 'r');

        if ($handle === false) {
            return [];
        }

        $headers =
            fgetcsv(
                $handle,
                0,
                ','
            );

        if ($headers === false) {

            fclose($handle);

            return [];
        }

        $headers =
            array_map(
                function ($header) {

                    $header =
                        (string) $header;

                    $header =
                        preg_replace(
                            '/^\xEF\xBB\xBF/',
                            '',
                            $header
                        );

                    return trim(
                        (string) $header
                    );
                },
                $headers
            );

        $rows = [];

        while (
            ($data = fgetcsv(
                $handle,
                0,
                ','
            )) !== false
        ) {

            if (
                count($data) === 1
                && trim(
                    (string) $data[0]
                ) === ''
            ) {
                continue;
            }

            $data =
                array_pad(
                    $data,
                    count($headers),
                    null
                );

            $data =
                array_slice(
                    $data,
                    0,
                    count($headers)
                );

            $row =
                array_combine(
                    $headers,
                    $data
                );

            if ($row === false) {
                continue;
            }

            foreach (
                $row as $key => $value
            ) {

                if (is_string($value)) {
                    $row[$key] =
                        trim($value);
                }
            }

            $rows[] = $row;
        }

        fclose($handle);

        return $rows;
    }

    /**
     * ============================================================
     * LOAD AREA MODEL
     * ============================================================
     *
     * Mencari:
     *
     * Area_Bawang_Per_Kecamatan_V4_1_STRICT.csv
     *
     * dan file area kandidat/DOA jika tersedia.
     */
    private function loadAreaData(
        string $mappingDir
    ): array {

        $areaCandidates = [

            $mappingDir
            .'/Area_Bawang_Per_Kecamatan_V4_1_STRICT.csv',

            $mappingDir
            .'/Area_Bawang_Per_Kecamatan_V4_1.csv',
        ];

        $areaPath =
            $this->firstExistingPath(
                $areaCandidates
            );

        /*
         * Jika nama sedikit berbeda, cari berdasarkan pola.
         */
        if ($areaPath === null) {

            $matches =
                glob(
                    $mappingDir
                    .'/*Area*Bawang*Per*Kecamatan*.csv'
                );

            if (
                is_array($matches)
                && ! empty($matches)
            ) {
                $areaPath = $matches[0];
            }
        }

        $rows =
            $areaPath !== null
                ? $this->parseCsv($areaPath)
                : [];

        $result = [

            'source' => $areaPath,

            'districts' => [],

            'valid_area_ha' => null,

            'candidate_area_ha' => null,

            'candidate_doa_area_ha' => null,
        ];

        /*
         * Baca area per kecamatan.
         */
        foreach ($rows as $row) {

            $district =
                $this->findColumnValue(
                    $row,
                    [
                        'Kecamatan',
                        'kecamatan',
                        'District',
                        'district',
                        'Site',
                        'site',
                    ]
                );

            $districtId =
                $this->normalizeDistrict(
                    $district
                );

            if ($districtId === '') {
                continue;
            }

            $area =
                $this->findNumericColumn(
                    $row,
                    [
                        'Area_Ha',
                        'area_ha',
                        'AreaHa',
                        'Area',
                        'area',
                        'Luas_Ha',
                        'luas_ha',
                        'Luas',
                        'luas',
                        'Candidate_Area_Ha',
                        'candidate_area_ha',
                    ]
                );

            $probability =
                $this->findNumericColumn(
                    $row,
                    [
                        'Probability',
                        'probability',
                        'Mean_Probability',
                        'mean_probability',
                        'Probability_Mean',
                        'probability_mean',
                        'Mean',
                        'mean',
                    ]
                );

            $candidate =
                $this->findNumericColumn(
                    $row,
                    [
                        'Candidate_Area_Ha',
                        'candidate_area_ha',
                        'Candidate_Ha',
                        'candidate_ha',
                        'Area_Candidate_Ha',
                    ]
                );

            $candidateDoa =
                $this->findNumericColumn(
                    $row,
                    [
                        'Candidate_DOA_Area_Ha',
                        'candidate_doa_area_ha',
                        'Candidate_Doa_Ha',
                        'candidate_doa_ha',
                        'DOA_Area_Ha',
                        'doa_area_ha',
                    ]
                );

            $result['districts'][$districtId] = [

                'area_ha' =>
                    $area,

                'probability' =>
                    $probability,

                'candidate' =>
                    $candidate,

                'candidate_doa' =>
                    $candidateDoa,

                'production' =>
                    $this->findNumericColumn(
                        $row,
                        [
                            'Production',
                            'production',
                            'Produksi',
                            'produksi',
                            'Production_Q',
                            'production_q',
                        ]
                    ),
            ];
        }

        /*
         * Coba membaca file area kandidat terpisah.
         */
        $candidatePath =
            $this->findFileByGlob(
                $mappingDir,
                [
                    '*Area*Candidate*Bawang*.csv',
                    '*Area*Candidate*.csv',
                ]
            );

        if ($candidatePath !== null) {

            $candidateRows =
                $this->parseCsv(
                    $candidatePath
                );

            $candidateTotal = 0.0;
            $hasCandidateTotal = false;

            foreach ($candidateRows as $row) {

                $value =
                    $this->findNumericColumn(
                        $row,
                        [
                            'Area_Ha',
                            'area_ha',
                            'Candidate_Area_Ha',
                            'candidate_area_ha',
                            'Area_Candidate_Ha',
                        ]
                    );

                if ($value !== null) {
                    $candidateTotal += $value;
                    $hasCandidateTotal = true;
                }
            }

            if ($hasCandidateTotal) {
                $result['candidate_area_ha'] =
                    $candidateTotal;
            }
        }

        /*
         * Coba file Candidate + DOA.
         */
        $doaPath =
            $this->findFileByGlob(
                $mappingDir,
                [
                    '*Area*Candidate*DOA*.csv',
                    '*Candidate*DOA*.csv',
                ]
            );

        if ($doaPath !== null) {

            $doaRows =
                $this->parseCsv(
                    $doaPath
                );

            $doaTotal = 0.0;
            $hasDoaTotal = false;

            foreach ($doaRows as $row) {

                $value =
                    $this->findNumericColumn(
                        $row,
                        [
                            'Area_Ha',
                            'area_ha',
                            'Candidate_DOA_Area_Ha',
                            'candidate_doa_area_ha',
                            'DOA_Area_Ha',
                            'doa_area_ha',
                        ]
                    );

                if ($value !== null) {
                    $doaTotal += $value;
                    $hasDoaTotal = true;
                }
            }

            if ($hasDoaTotal) {
                $result['candidate_doa_area_ha'] =
                    $doaTotal;
            }
        }

        /*
         * Hitung total area dari baris kecamatan.
         */
        $validTotal = 0.0;
        $validHasValue = false;

        $candidateTotalFromDistrict = 0.0;
        $candidateHasValue = false;

        $doaTotalFromDistrict = 0.0;
        $doaHasValue = false;

        foreach (
            $result['districts']
            as $districtRow
        ) {

            if (
                $districtRow['area_ha']
                !== null
            ) {

                $validTotal +=
                    $districtRow['area_ha'];

                $validHasValue = true;
            }

            if (
                $districtRow['candidate']
                !== null
            ) {

                $candidateTotalFromDistrict +=
                    $districtRow['candidate'];

                $candidateHasValue = true;
            }

            if (
                $districtRow['candidate_doa']
                !== null
            ) {

                $doaTotalFromDistrict +=
                    $districtRow['candidate_doa'];

                $doaHasValue = true;
            }
        }

        if ($validHasValue) {
            $result['valid_area_ha'] =
                $validTotal;
        }

        if (
            $result['candidate_area_ha']
            === null
            && $candidateHasValue
        ) {
            $result['candidate_area_ha'] =
                $candidateTotalFromDistrict;
        }

        if (
            $result['candidate_doa_area_ha']
            === null
            && $doaHasValue
        ) {
            $result['candidate_doa_area_ha'] =
                $doaTotalFromDistrict;
        }

        return $result;
    }

    /**
     * ============================================================
     * BUILD MAP SUMMARY
     * ============================================================
     */
    private function buildMapSummary(
        array $mappingSummary,
        array $areaData
    ): array {

        $threshold =
            $this->toNullableFloat(
                $mappingSummary['Main_Threshold']
                ?? null
            );

        if ($threshold === null) {
            $threshold = 0.50;
        }

        $valid =
            $areaData['valid_area_ha']
            ?? null;

        $candidate =
            $areaData['candidate_area_ha']
            ?? null;

        $candidateDoa =
            $areaData['candidate_doa_area_ha']
            ?? null;

        return [

            'valid_area' => [

                'label' => 'Area Valid',

                'value' =>
                    $valid !== null
                        ? number_format(
                            $valid,
                            2
                        ).' ha'
                        : 'Data belum tersedia',

                'raw' => $valid,
            ],

            'candidate_area' => [

                'label' =>
                    'Kandidat T = '
                    .number_format(
                        $threshold,
                        2
                    ),

                'value' =>
                    $candidate !== null
                        ? number_format(
                            $candidate,
                            2
                        ).' ha'
                        : 'Data belum tersedia',

                'raw' => $candidate,
            ],

            'candidate_doa_area' => [

                'label' =>
                    'Kandidat + DOA',

                'value' =>
                    $candidateDoa !== null
                        ? number_format(
                            $candidateDoa,
                            2
                        ).' ha'
                        : 'Data belum tersedia',

                'raw' => $candidateDoa,
            ],

            'threshold' => $threshold,

            'valid_pixels' =>
                $mappingSummary[
                    'Valid_Predicted_Pixels'
                ] ?? null,

            'probability_summary' =>
                $mappingSummary[
                    'Probability_Summary'
                ] ?? [],

            'model' =>
                $mappingSummary[
                    'Model'
                ] ?? null,

            'primary_dataset' =>
                $mappingSummary[
                    'Primary_Dataset'
                ] ?? null,

            'important_limitation' =>
                $mappingSummary[
                    'Important_Limitation'
                ] ?? null,
        ];
    }

    /**
     * ============================================================
     * LOAD PREDIKSI XGBOOST
     * ============================================================
     */
    private function loadXgboostPredictions(
        string $mlPath
    ): array {

        $predictionPath =
            $mlPath
            .'/OOf_Site_Prediction_Nganjuk_V4.csv';

        if (! is_file($predictionPath)) {
            return [];
        }

        $rows =
            $this->parseCsv(
                $predictionPath
            );

        if (empty($rows)) {
            return [];
        }

        $firstRow =
            $rows[0];

        $districtColumn =
            $this->findColumn(
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

        $yearColumn =
            $this->findColumn(
                $firstRow,
                [
                    'Year',
                    'year',
                    'Tahun',
                    'tahun',
                ]
            );

        $predictionColumn =
            $this->findColumn(
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

        if (
            $districtColumn === null
            || $predictionColumn === null
        ) {
            return [];
        }

        $predictions = [];

        foreach ($rows as $row) {

            $district =
                $this->normalizeDistrict(
                    $row[$districtColumn]
                    ?? ''
                );

            if ($district === '') {
                continue;
            }

            $year =
                $yearColumn !== null
                    ? (int) (
                        $row[
                            $yearColumn
                        ] ?? 2025
                    )
                    : 2025;

            $prediction =
                $this->toNullableFloat(
                    $row[
                        $predictionColumn
                    ] ?? null
                );

            if ($prediction === null) {
                continue;
            }

            $predictions[
                $district
            ][$year] =
                $prediction;
        }

        return $predictions;
    }

    /**
     * ============================================================
     * FIND COLUMN
     * ============================================================
     */
    private function findColumn(
        array $row,
        array $candidates
    ): ?string {

        $available = [];

        foreach (
            array_keys($row)
            as $key
        ) {

            $available[
                $this->normalizeColumnName(
                    (string) $key
                )
            ] = $key;
        }

        foreach (
            $candidates
            as $candidate
        ) {

            $normalizedCandidate =
                $this->normalizeColumnName(
                    $candidate
                );

            if (
                isset(
                    $available[
                        $normalizedCandidate
                    ]
                )
            ) {
                return $available[
                    $normalizedCandidate
                ];
            }
        }

        return null;
    }

    /**
     * ============================================================
     * FIND COLUMN VALUE
     * ============================================================
     */
    private function findColumnValue(
        array $row,
        array $candidates
    ): mixed {

        $column =
            $this->findColumn(
                $row,
                $candidates
            );

        return $column !== null
            ? ($row[$column] ?? null)
            : null;
    }

    /**
     * ============================================================
     * FIND NUMERIC COLUMN
     * ============================================================
     */
    private function findNumericColumn(
        array $row,
        array $candidates
    ): ?float {

        $column =
            $this->findColumn(
                $row,
                $candidates
            );

        if ($column === null) {
            return null;
        }

        return $this->toNullableFloat(
            $row[$column] ?? null
        );
    }

    /**
     * ============================================================
     * FIND FILE BY GLOB
     * ============================================================
     */
    private function findFileByGlob(
        string $directory,
        array $patterns
    ): ?string {

        foreach ($patterns as $pattern) {

            $matches =
                glob(
                    $directory.'/'.$pattern
                );

            if (
                is_array($matches)
                && ! empty($matches)
            ) {
                return $matches[0];
            }
        }

        return null;
    }

    /**
     * ============================================================
     * NORMALIZE DISTRICT
     * ============================================================
     */
    private function normalizeDistrict(
        mixed $value
    ): string {

        if (
            $value === null
            || $value === ''
        ) {
            return '';
        }

        $value =
            strtolower(
                trim(
                    (string) $value
                )
            );

        $value =
            preg_replace(
                '/^(kec\\.?|kecamatan)\\s+/i',
                '',
                $value
            );

        return trim(
            (string) $value
        );
    }

    /**
     * ============================================================
     * NORMALIZE COLUMN NAME
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
     * KONVERSI FLOAT
     * ============================================================
     *
     * Perbaikan:
     * - "10,97" -> 10.97
     * - "1,234.56" -> 1234.56
     * - "1.234,56" -> 1234.56
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

        if (is_int($value) || is_float($value)) {
            return (float) $value;
        }

        $value =
            trim(
                (string) $value
            );

        if ($value === '') {
            return null;
        }

        /*
         * Hapus simbol selain angka, titik, koma, minus.
         */
        $value =
            preg_replace(
                '/[^0-9,\.\-]/',
                '',
                $value
            );

        if ($value === '') {
            return null;
        }

        $hasComma =
            str_contains(
                $value,
                ','
            );

        $hasDot =
            str_contains(
                $value,
                '.'
            );

        if (
            $hasComma
            && $hasDot
        ) {

            /*
             * Format 1.234,56
             */
            if (
                strrpos(
                    $value,
                    ','
                )
                >
                strrpos(
                    $value,
                    '.'
                )
            ) {

                $value =
                    str_replace(
                        '.',
                        '',
                        $value
                    );

                $value =
                    str_replace(
                        ',',
                        '.',
                        $value
                    );

            /*
             * Format 1,234.56
             */
            } else {

                $value =
                    str_replace(
                        ',',
                        '',
                        $value
                    );
            }

        } elseif ($hasComma) {

            $parts =
                explode(
                    ',',
                    $value
                );

            /*
             * 1234,56 -> desimal
             */
            if (
                count($parts) === 2
                && strlen(
                    $parts[1]
                ) <= 4
            ) {

                $value =
                    str_replace(
                        ',',
                        '.',
                        $value
                    );

            /*
             * 1,234 -> ribuan
             */
            } else {

                $value =
                    str_replace(
                        ',',
                        '',
                        $value
                    );
            }
        }

        return is_numeric($value)
            ? (float) $value
            : null;
    }

    /**
     * ============================================================
     * FORMAT NUMBER
     * ============================================================
     */
    private function formatNumberSmart(
        mixed $value
    ): string {

        $number =
            $this->toNullableFloat(
                $value
            );

        if ($number === null) {
            return 'Data belum tersedia';
        }

        if (
            abs(
                $number
                - round($number)
            ) < 0.000001
        ) {

            return number_format(
                $number,
                0
            );
        }

        return number_format(
            $number,
            2
        );
    }

    /**
     * ============================================================
     * FIRST EXISTING PATH
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
     * BAND LABEL SENTINEL-2
     * ============================================================
     */
    private function bandLabel(
        string $feature
    ): string {

        $labels = [

            'B2' =>
                'B2 - Blue',

            'B3' =>
                'B3 - Green',

            'B4' =>
                'B4 - Red',

            'B8' =>
                'B8 - NIR',

            'B11' =>
                'B11 - SWIR-1',

            'B12' =>
                'B12 - SWIR-2',

            'NDVI' =>
                'NDVI - Indeks Vegetasi',

            'NDWI' =>
                'NDWI - Indeks Kelembapan',

            'NDBI' =>
                'NDBI - Indeks Bangunan',

            'BSI' =>
                'BSI - Bare Soil Index',
        ];

        /*
         * Tambahan agar "b2", "ndvi", dll juga terbaca.
         */
        $upper =
            strtoupper(
                trim($feature)
            );

        return $labels[$upper]
            ?? $feature;
    }
}
