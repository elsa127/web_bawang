<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class DashboardController extends Controller
{
    /**
     * Menampilkan hasil penelitian yang sudah diolah di Google Colab.
     * CSV dan JSON dibaca dari ml_model/data; raster disajikan melalui mapAsset.
     * Permintaan halaman tidak menjalankan pelatihan atau prediksi ulang.
     */
    public function index(Request $request): View
    {
        /*
        |--------------------------------------------------------------------------
        | 1. LOKASI UTAMA DATASET
        |--------------------------------------------------------------------------
        */
        $mlPath = base_path('ml_model/data');
        $mapManifest = $this->preparedMap();

        /*
        |--------------------------------------------------------------------------
        | 2. PATH DATASET UTAMA
        |--------------------------------------------------------------------------
        */
        $mappingPath = $mlPath
            .'/XGBOOST_V4_1/FASE_4B_STRICT_Mapping_Summary.json';

        $mappingDir = $mlPath
            .'/XGBOOST_V4_1';

        /**
         * Peta memakai STRICT FINAL, sedangkan model SELECTED memakai EXTENDED.
         * Baca ringkasan masing-masing agar evaluasi kedua model tidak tertukar.
         * Pelatihan dan prediksi raster sudah dilakukan di Colab.
         */
        $researchResults = [
            'mapping' => $this->readJsonFile($mappingPath),
            'selected' => $this->readJsonFile($mappingDir.'/XGBoost_Bawang_Nganjuk_V4_1_metadata.json'),
            'repeated' => $this->readJsonFile($mappingDir.'/RepeatedCV_Summary_V4_1.json'),
            'doa' => $this->readJsonFile($mappingDir.'/FASE_4D_DOA_Summary.json'),
        ];

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
            $mlPath.'/YIELD_PREDICTION/FASE_5_Yield_Prediction_Summary.json'
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
        $tc = $modelMetricsRaw['Temporal_CV'] ?? [];

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

        $xgboostPredSukomoro = $xgboostPredictions['sukomoro'] ?? [];

        /*
        |--------------------------------------------------------------------------
        | 11. HISTORY SUKOMORO
        |--------------------------------------------------------------------------
        */
        $sukomoroHistory = array_filter(
            $csvData,
            fn ($r) => $this->normalizeDistrict(
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
                    ? number_format($actualTon, 2).' ton/ha'
                    : '—',
                'predicted' => $predTon !== null
                    ? number_format($predTon, 2).' ton/ha'
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
                'area' => 'Data belum tersedia',
                'production' => 'Data belum tersedia',
                'status' => 'Kandidat menurut model',
                'status_code' => 'good',
                'status_color' => '#5E9759',
            ],

            'bagor' => [
                'name' => 'Bagor',
                'area' => 'Data belum tersedia',
                'production' => 'Data belum tersedia',
                'status' => 'Kandidat menurut model',
                'status_code' => 'good',
                'status_color' => '#74A870',
            ],

            'gondang' => [
                'name' => 'Gondang',
                'area' => 'Data belum tersedia',
                'production' => 'Data belum tersedia',
                'status' => 'Kandidat menurut model',
                'status_code' => 'warning',
                'status_color' => '#E4A879',
            ],

            'rejoso' => [
                'name' => 'Rejoso',
                'area' => 'Data belum tersedia',
                'production' => 'Data belum tersedia',
                'status' => 'Kandidat menurut model',
                'status_code' => 'good',
                'status_color' => '#86A77B',
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

            if ($row !== null) {
                $production = $this->toNullableFloat($row['Produksi_q'] ?? null);
                $cfg['production'] = $production !== null
                    ? number_format($production, 0).' kuintal (BPS 2025)'
                    : 'Data belum tersedia';
            }

            $ndwi = $row !== null
                ? $this->toNullableFloat(
                    $row['NDWI'] ?? null
                )
                : null;

            $moisturePct = null;

            $year = $row !== null
                ? (int) ($row['Year'] ?? 2025)
                : 2025;

            $districtPrediction = $xgboostPredictions[$id][$year] ?? null;

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

                    'predicted_yield' => $districtPrediction !== null
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

                    'probability' => $areaData['districts'][$id]['probability']
                        ?? null,

                    'candidate' => $areaData['districts'][$id]['candidate']
                        ?? null,

                    'candidate_doa' => $areaData['districts'][$id]['candidate_doa']
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

                'status_badge' => $parcel['status_code'] === 'warning'
                        ? 'bg-amber-50 text-amber-700 border-amber-200'
                        : 'bg-emerald-50 text-emerald-700 border-emerald-200',

                'area' => $parcel['area'] ?? '-',

                'plots' => $parcel['production']
                    ?? 'Data area tersedia',

                'yield_info' => $parcel['predicted_yield']
                    ?? 'Belum ada prediksi',

                'ndvi' => $parcel['ndvi'] !== null
                        ? number_format(
                            $parcel['ndvi'],
                            3
                        )
                        : '-',

                'ndvi_sub' => 'Sentinel-2 2025',

                'moisture' => $parcel['moisture']
                    ?? 'Data kelembapan belum tersedia',

                'moisture_sub' => $parcel['ndwi'] !== null
                    ? 'NDWI '
                        .number_format(
                            $parcel['ndwi'],
                            3
                        )
                    : 'Data NDWI belum tersedia',

                'probability' => $parcel['probability'],

                'candidate' => $parcel['candidate'],

                'candidate_doa' => $parcel['candidate_doa'],

                'candidate_area' => $parcel['candidate'] !== null
                    ? number_format($parcel['candidate'], 2).' ha'
                    : 'Data belum tersedia',

                'candidate_doa_area' => $parcel['candidate_doa'] !== null
                    ? number_format($parcel['candidate_doa'], 2).' ha'
                    : 'Data belum tersedia',
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
                'title' => 'NDVI Sukomoro 2025',

                'value' => $ndviLatest !== null
                    ? number_format($ndviLatest, 3)
                    : 'Data belum tersedia',

                'subtitle' => $ndviLatest !== null
                    ? 'Rata-rata wilayah Sentinel-2 2025'
                    : null,

                'color' => 'emerald',

                'icon' => 'leaf',
            ],

            [
                'title' => 'Produktivitas Sukomoro 2025',

                'value' => isset($xgboostPredSukomoro[2025])
                    ? number_format($xgboostPredSukomoro[2025], 2).' Ton/Ha'
                    : 'Data belum tersedia',

                'subtitle' => 'Prediksi validasi antar tahun (2025)',

                'color' => 'rose',

                'icon' => 'chart-bar',
            ],

            [
                'title' => 'Cakupan Data',

                'value' => '4 Kecamatan',

                'subtitle' => 'Sentinel-2 dan BPS 2023–2025',

                'color' => 'green',

                'icon' => 'activity',
            ],

            [
                'title' => 'Validasi Antar Tahun',

                'value' => isset($tc['MAPE'])
                    ? number_format((float) $tc['MAPE'], 2).'% MAPE'
                    : 'Data belum tersedia',

                'subtitle' => '12 observasi kecamatan–tahun',

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

            'condition_badge' => 'Data Sentinel-2 2025',

            'total_area' => $mlResults['sukomoro']['area']
                ?? 'Data belum tersedia',

            'total_plots' => $mlResults['sukomoro']['plots']
                ?? 'Data belum tersedia',

            'ndvi_average' => $ndviLatest !== null
                    ? 'NDVI ('
                        .number_format(
                            $ndviLatest,
                            3
                        )
                        .')'
                    : null,

            'ndvi_note' => $ndviLatest !== null
                    ? 'Sentinel-2 Komposit 2025'
                    : null,

            'moisture_average' => $ndwi2025 !== null
                    ? 'NDWI '
                        .number_format(
                            $ndwi2025,
                            3
                        )
                    : null,

            'moisture_note' => $ndwi2025 !== null
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

        /**
         * Nilai indeks berasal dari rata-rata piksel pada CSV Colab.
         * Jangan menghitung ulang indeks dari rata-rata band: hasilnya dapat berbeda.
         */
        $conditionAnalysis = [
            'current' => ['NDVI' => $ndviLatest, 'NDWI' => $ndwi2025, 'B12' => $b12],
            'annual' => [],
            'districts' => [],
        ];
        foreach ($csvData as $row) {
            $record = [
                'district' => (string) ($row['Kecamatan'] ?? ''),
                'year' => (int) ($row['Year'] ?? 0),
                'ndvi' => $this->toNullableFloat($row['NDVI'] ?? null),
                'ndwi' => $this->toNullableFloat($row['NDWI'] ?? null),
                'b12' => $this->toNullableFloat($row['B12'] ?? null),
            ];
            if ($this->normalizeDistrict($record['district']) === 'sukomoro') {
                $conditionAnalysis['annual'][] = $record;
            }
            if ($record['year'] === 2025) {
                $conditionAnalysis['districts'][] = $record;
            }
        }
        usort($conditionAnalysis['annual'], fn (array $left, array $right): int => $left['year'] <=> $right['year']);

        $moisturePctMain = null;

        $cropSensors = [

            'ndvi' => [

                'score' => $ndviLatest !== null
                    ? number_format(
                        $ndviLatest,
                        3
                    )
                    : null,

                'status' => 'Komposit 2025',

                'status_type' => 'good',

                'label' => 'Rata-rata wilayah Sukomoro',

                'description' => $b8 !== null && $b4 !== null
                        ? 'NDVI rata-rata wilayah dari komposit tahunan Sentinel-2; tidak menentukan fase tanam atau kesehatan tanaman per lahan.'
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

                'score' => $ndwi2025 !== null
                    ? number_format($ndwi2025, 3)
                    : null,

                'status' => 'Indeks satelit',

                'status_type' => 'warning',

                'label' => $b12 !== null
                    ? 'Reflektansi B12: '
                        .number_format(
                            $b12,
                            4
                        )
                    : null,

                'description' => $b12 !== null && $ndwi2025 !== null
                        ? 'NDWI adalah indeks spektral komposit tahunan wilayah, bukan pengukuran kadar air tanah.'
                        : null,

                'percent' => $moisturePctMain,
            ],
        ];

        /*
        |--------------------------------------------------------------------------
        | 21. TREN NDVI MINGGUAN
        |--------------------------------------------------------------------------
        */
        $weeklyTrends = [];

        foreach ($sukomoroHistory as $row) {
            $ndvi = $this->toNullableFloat($row['NDVI'] ?? null);

            if ($ndvi !== null) {
                $weeklyTrends[] = [
                    'week' => (string) ($row['Year'] ?? ''),
                    'value' => $ndvi,
                    'height_pct' => (int) round($ndvi * 100),
                    'is_peak' => false,
                    'badge' => null,
                ];
            }
        }

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

                'title' => 'Hasil Interpretasi Satelit Sentinel-2',

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

                'description' => 'Rata-rata wilayah Sukomoro: '
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
        | 23. SIKLUS PERTUMBUHAN
        |--------------------------------------------------------------------------
        */
        $growthLifecycle = [

            'subtitle' => 'Fase pertumbuhan belum tersedia',

            'description' => 'Dataset penelitian tidak memuat tanggal tanam per lahan, sehingga fase tanaman saat ini belum dapat ditentukan.',

            'stages' => [

                [
                    'number' => 1,
                    'name' => 'Tanam',
                    'period' => '0 - 10 HST',
                    'status' => 'pending',
                ],

                [
                    'number' => 2,
                    'name' => 'Vegetatif',
                    'period' => '11 - 35 HST',
                    'status' => 'pending',
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
                'title' => 'Cek Kebutuhan Air di Lapangan',

                'description' => "NDWI komposit wilayah Sukomoro 2025: {$ndwiLabel}. Periksa kelembapan tanah secara langsung sebelum menentukan penyiraman.",

                'icon' => 'droplet',

                'badge' => 'Irigasi',

                'color' => 'emerald',
            ],

            [
                'title' => 'Waspada Daun',

                'description' => 'Periksa bercak ungu (Alternaria porri) secara berkala. '
                    ."NDVI rata-rata wilayah Sukomoro 2025: {$ndviLabel}. Indeks ini tidak mendiagnosis penyakit tanaman.",

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
        | 25. PREDIKSI PRODUKTIVITAS
        |--------------------------------------------------------------------------
        */
        $projectedYield = $xgboostPredSukomoro[2025] ?? null;

        /*
        |--------------------------------------------------------------------------
        | 26. METRIK MODEL
        |--------------------------------------------------------------------------
        */
        $mape = $this->toNullableFloat(
            $tc['MAPE'] ?? null
        );

        $maeTon = $this->toNullableFloat(
            isset($tc['MAE']) ? (float) $tc['MAE'] / 10 : null
        );

        $rmseTon = $this->toNullableFloat(
            isset($tc['RMSE']) ? (float) $tc['RMSE'] / 10 : null
        );

        $r2 = $this->toNullableFloat(
            $tc['R2'] ?? null
        );

        /*
        |--------------------------------------------------------------------------
        | 27. AKURASI MODEL
        |--------------------------------------------------------------------------
        */
        if ($mape !== null) {

            $accuracyBadge =
                'MAPE '
                .number_format(
                    $mape,
                    2
                )
                .'% (validasi antar tahun)';

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

            'status_badge' => 'Hasil uji model 2025',

            'estimate_title' => 'Prediksi Produktivitas Sukomoro 2025',

            'estimate_desc' => 'Prediksi validasi saat data tahun 2025 tidak dipakai untuk melatih model. Ini bukan prakiraan panen musim berjalan.',

            'projected_yield' => $projectedYield !== null
                    ? number_format($projectedYield, 2)
                    : '—',

            'unit' => 'Ton / Hektar',

            'accuracy_badge' => $accuracyBadge,

            'comparisons' => $comparisons,

            'model_accuracy' => [

                'rate' => $mape !== null
                    ? number_format($mape, 2).' %'
                    : '—',

                'mae' => $maeTon !== null
                        ? number_format(
                            $maeTon,
                            2
                        ).' ton/ha'
                        : '—',

                'rmse' => $rmseTon !== null
                        ? number_format(
                            $rmseTon,
                            2
                        ).' ton/ha'
                        : '—',

                'r_score' => $r2 !== null
                        ? number_format(
                            $r2,
                            3
                        )
                        : '—',

                'note' => $mape !== null && $r2 !== null
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
                'title' => 'Citra Satelit Sentinel-2',

                'description' => 'Data citra multispektral resolusi 10m (B2, B3, B4, B8, B11, B12) untuk memantau klorofil dan kelembapan lahan secara berkala.',

                'icon' => 'satellite',

                'color' => 'rose',
            ],

            [
                'title' => 'Algoritma XGBoost',

                'description' => 'Model XGBoost dievaluasi dengan validasi antar tahun dan antar kecamatan; hasilnya bersifat eksploratif.',

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

                'description' => 'Indeks satelit memberi konteks wilayah; keputusan budidaya tetap memerlukan pemeriksaan kondisi lahan.',

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
                'description' => 'Area administrasi penelitian',
                'type' => 'boundary',
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
                'checked' => false,
                'group' => 'Layer Peta',
                'threshold' => 0.50,
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

            /*
             * URL PNG overlay yang stabil saat zoom.
             * Digunakan oleh L.imageOverlay di JavaScript.
             */
            'overlay_probability' => route(
                'map.overlay',
                ['layer' => 'probability']
            ),

            'overlay_candidate' => route(
                'map.overlay',
                ['layer' => 'candidate']
            ),

            'overlay_candidate_doa' => route(
                'map.overlay',
                ['layer' => 'candidate_doa']
            ),
        ];

        /*
        |--------------------------------------------------------------------------
        | 35. STATUS FILE MODEL
        |--------------------------------------------------------------------------
        */
        $modelFiles = [

            'feature_importance' => is_file(
                $mlPath.'/feature_importance.json'
            ),

            'model_metrics' => is_file(
                $mlPath.'/YIELD_PREDICTION/FASE_5_Yield_Prediction_Summary.json'
            ),

            'yield_dataset' => is_file(
                $mlPath
                .'/YIELD_PREDICTION/Yield_Dataset_Nganjuk_2023_2025.csv'
            ),

            'oof_prediction' => is_file(
                $mlPath.'/YIELD_PREDICTION/Yield_Temporal_CV_Prediction.csv'
            ),

            'dataset_ml' => is_file(
                $mlPath
                .'/dataset_ml_nganjuk_v4.csv'
            ),

            'dataset_qc' => is_file(
                $mlPath
                .'/dataset_ml_nganjuk_v4_qc.csv'
            ),

            'dataset_strict' => is_file(
                $mlPath
                .'/dataset_ml_nganjuk_v4_1_STRICT.csv'
            ),

            'dataset_extended' => is_file(
                $mlPath
                .'/dataset_ml_nganjuk_v4_1_EXTENDED.csv'
            ),

            'feature_stack_2025' => is_file(
                $mlPath
                .'/FeatureStack_Nganjuk_2025.tif'
            ),

            'feature_stack_final' => is_file(
                $mlPath
                .'/FeatureStack_Nganjuk_V3_FINAL.tif'
            ),

            'mapping_summary' => is_file($mappingPath),

            'probability_tif' => $this->mapAssetPath('probability') !== null,

            'candidate_tif' => $this->mapAssetPath('candidate') !== null,

            'candidate_doa_tif' => $this->mapAssetPath('candidate_doa') !== null,

            'area_table' => $areaData['source'] !== null,
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
        $activeDistrict = $request->query('district', 'all');
        $districtNames = ['all' => 'Semua kecamatan', 'bagor' => 'Bagor', 'gondang' => 'Gondang', 'rejoso' => 'Rejoso', 'sukomoro' => 'Sukomoro'];
        abort_unless(is_string($activeDistrict) && isset($districtNames[$activeDistrict]), 404);

        // Tabel layar dan laporan memakai baris sumber yang sama, tanpa melatih ulang model.
        $reportData = ['scope' => $districtNames[$activeDistrict], 'rows' => [], 'areas' => [], 'metrics' => $modelMetricsRaw];
        foreach ($csvData as $row) {
            $districtKey = strtolower(trim($row['Kecamatan'] ?? ''));
            $year = (int) ($row['Year'] ?? 0);
            $actual = $this->toNullableFloat($row['Produktivitas_ton_ha'] ?? null);
            $prediction = $xgboostPredictions[$districtKey][$year] ?? null;
            $reportData['rows'][] = [
                'district' => $row['Kecamatan'], 'year' => $year,
                'ndvi' => $this->toNullableFloat($row['NDVI'] ?? null),
                'ndwi' => $this->toNullableFloat($row['NDWI'] ?? null),
                'b12' => $this->toNullableFloat($row['B12'] ?? null),
                'actual' => $actual, 'prediction' => $prediction,
                'error' => $actual !== null && $prediction !== null ? abs($actual - $prediction) : null,
            ];
        }
        foreach ($this->parseCsv($mappingDir.'/Area_Candidate_Bawang_DOA_V4_1.csv') as $row) {
            $key = strtolower(trim($row['Kecamatan'] ?? ''));
            if (! isset($districtNames[$key])) {
                continue;
            }
            $reportData['areas'][] = $row;
        }

        /** Semua cakupan disiapkan sekali agar pilihan peta tidak meminta halaman ulang. */
        $districtReports = [];
        foreach ($districtNames as $key => $name) {
            $rows = array_values(array_filter($reportData['rows'], fn (array $row): bool => $key === 'all' || strtolower($row['district']) === $key));
            $areas = array_values(array_filter($reportData['areas'], fn (array $row): bool => $key === 'all' || strtolower($row['Kecamatan']) === $key));
            $latestYear = $rows === [] ? null : max(array_column($rows, 'year'));
            $districtReports[$key] = [
                'scope' => $name, 'rows' => $rows, 'areas' => $areas, 'metrics' => $modelMetricsRaw,
                'latestYear' => $latestYear,
                'latestRows' => array_values(array_filter($rows, fn (array $row): bool => $row['year'] === $latestYear)),
            ];
        }
        $reportData = $districtReports[$activeDistrict];

        return view(
            'welcome',
            compact(

                'districtReports',
                'activeDistrict',
                'reportData',
                'kpiSummary',

                'researchResults',
                'mapManifest',

                'districts',

                'parcels',

                'selectedDistrict',

                'cropSensors',
                'conditionAnalysis',

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
     * INFORMASI BOUNDS & UKURAN FILE RASTER
     * ============================================================
     *
     * Digunakan oleh JavaScript peta untuk mengetahui:
     * - Apakah file tersedia
     * - Ukuran file (agar JS bisa memutuskan strategi pemuatan)
     * - Bounding box koordinat (lat/lon) untuk overlay
     *
     * Bounds diambil dari FASE_4B_STRICT_Mapping_Summary.json
     * yang sudah menyimpan koordinat wilayah penelitian.
     */
    public function mapInfo(): JsonResponse
    {
        $manifest = $this->preparedMap();
        abort_if($manifest === [], 503, 'Peta belum disiapkan atau sumbernya berubah.');

        return response()->json($manifest)->header('Cache-Control', 'no-cache');
    }

    /** Menyajikan raster penelitian asli tanpa mengubah hasil model. */
    public function mapAsset(string $asset): Response
    {
        $path = $this->mapAssetPath($asset);

        abort_if(
            $path === null,
            404,
            'File raster peta tidak ditemukan.'
        );

        $content = file_get_contents($path);

        abort_if(
            $content === false,
            500,
            'File raster tidak dapat dibaca.'
        );

        return response($content, 200, [
            'Content-Type' => 'image/tiff',
            'Content-Length' => filesize($path),
            'Content-Disposition' => 'inline',
            'Cache-Control' => 'public, max-age=3600',
            'Access-Control-Allow-Origin' => '*',
        ]);
    }

    /** Menyajikan PNG yang sudah disiapkan tanpa menjalankan proses Python. */
    public function mapOverlay(string $layer): Response
    {
        abort_unless(in_array($layer, ['probability', 'candidate', 'candidate_doa', 'candidate_landcover', 'satellite'], true), 404);
        $manifest = $this->preparedMap();
        abort_if($manifest === [], 503, 'Peta perlu disiapkan ulang oleh pengelola.');
        abort_unless(isset($manifest['layers'][$layer]), 503, 'Data untuk layer ini belum tersedia.');
        $pngPath = storage_path("app/map-overlays/overlay_{$layer}.png");
        abort_unless(is_file($pngPath), 503, 'Gambar peta belum tersedia.');
        $content = file_get_contents($pngPath);
        abort_if($content === false, 503, 'Gambar peta tidak dapat dibaca.');

        return response($content, 200, [
            'Content-Type' => 'image/png',
            'Cache-Control' => 'no-cache',
        ]);
    }

    /**
     * Menolak hasil lama jika sumber berubah. Pengunjung hanya membaca hasil;
     * generator Python dijalankan pengelola setelah ekspor Colab diperbarui.
     *
     * @return array<string, mixed>
     */
    private function preparedMap(): array
    {
        $manifest = $this->readJsonFile(storage_path('app/map-overlays/manifest.json'));
        if (($manifest['schema'] ?? null) !== 2) {
            return [];
        }

        $paths = [
            base_path('ml_model/generate_map_overlays.py'),
            base_path('ml_model/data/OFFICIAL_DATA_2025/Batas_4_Kecamatan_Nganjuk.geojson'),
            $this->mapAssetPath('probability'),
            $this->mapAssetPath('candidate'),
            $this->mapAssetPath('candidate_doa'),
        ];
        $eligibilityPath = base_path('ml_model/data/XGBOOST_V4_1/LandCover_Eligibility_2025.tif');
        if (is_file($eligibilityPath) !== ($manifest['landcover_available'] ?? false)) {
            return [];
        }
        if (is_file($eligibilityPath)) {
            $paths[] = $eligibilityPath;
        }
        $satellitePath = base_path('ml_model/data/XGBOOST_V4_1/Sentinel2_RGB_CloudMasked_2025.tif');
        if (is_file($satellitePath) !== ($manifest['satellite_available'] ?? false)) {
            return [];
        }
        if (is_file($satellitePath)) {
            $paths[] = $satellitePath;
        }
        foreach ($paths as $path) {
            if ($path === null || ! is_file($path)) {
                return [];
            }
            $relative = str_replace('\\', '/', substr($path, strlen(base_path()) + 1));
            $source = $manifest['sources'][$relative] ?? [];
            if (($source['mtime'] ?? null) !== filemtime($path) || ($source['size'] ?? null) !== filesize($path)) {
                return [];
            }
        }

        return $manifest;
    }

    /** Membatasi akses raster pada nama layer yang dikenal aplikasi. */
    private function mapAssetPath(
        string $asset
    ): ?string {

        $dir =
            base_path(
                'ml_model/data/XGBOOST_V4_1'
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

            if (! in_array($districtId, ['bagor', 'gondang', 'rejoso', 'sukomoro'], true)) {
                continue;
            }

            $area =
                $this->findNumericColumn(
                    $row,
                    [
                        'Valid_Area_Ha',
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
                        'Area_Ha_T0_5',
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

                'area_ha' => $area,

                'probability' => $probability,

                'candidate' => $candidate,

                'candidate_doa' => $candidateDoa,

                'production' => $this->findNumericColumn(
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
        $candidatePath = null;

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
        $doaPath = $mappingDir.'/Area_Candidate_Bawang_DOA_V4_1.csv';

        if (! is_file($doaPath)) {
            $doaPath = null;
        }

        if ($doaPath !== null) {

            $doaRows =
                $this->parseCsv(
                    $doaPath
                );

            $doaTotal = 0.0;
            $hasDoaTotal = false;

            foreach ($doaRows as $row) {
                if (! in_array($this->normalizeDistrict($row['Kecamatan'] ?? ''), ['bagor', 'gondang', 'rejoso', 'sukomoro'], true)) {
                    continue;
                }

                $value =
                    $this->findNumericColumn(
                        $row,
                        [
                            'DOA_Filtered_Ha_T0_5',
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

            foreach ($doaRows as $row) {
                $districtId = $this->normalizeDistrict($row['Kecamatan'] ?? '');

                if (isset($result['districts'][$districtId])) {
                    $result['districts'][$districtId]['candidate_doa'] =
                        $this->toNullableFloat($row['DOA_Filtered_Ha_T0_5'] ?? null);
                }
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
            $result['districts'] as $districtRow
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

                'value' => $valid !== null
                        ? number_format(
                            $valid,
                            2
                        ).' ha'
                        : 'Data belum tersedia',

                'raw' => $valid,
            ],

            'candidate_area' => [

                'label' => 'Kandidat T = '
                    .number_format(
                        $threshold,
                        2
                    ),

                'value' => $candidate !== null
                        ? number_format(
                            $candidate,
                            2
                        ).' ha'
                        : 'Data belum tersedia',

                'raw' => $candidate,
            ],

            'candidate_doa_area' => [

                'label' => 'Kandidat + DOA',

                'value' => $candidateDoa !== null
                        ? number_format(
                            $candidateDoa,
                            2
                        ).' ha'
                        : 'Data belum tersedia',

                'raw' => $candidateDoa,
            ],

            'threshold' => $threshold,

            'valid_pixels' => $mappingSummary[
                    'Valid_Predicted_Pixels'
                ] ?? null,

            'probability_summary' => $mappingSummary[
                    'Probability_Summary'
                ] ?? [],

            'model' => $mappingSummary[
                    'Model'
                ] ?? null,

            'primary_dataset' => $mappingSummary[
                    'Primary_Dataset'
                ] ?? null,

            'important_limitation' => $mappingSummary[
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

        $predictionPath = $mlPath
            .'/YIELD_PREDICTION/Yield_Temporal_CV_Prediction.csv';

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
                    'Prediction_ton_ha',
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
            array_keys($row) as $key
        ) {

            $available[
                $this->normalizeColumnName(
                    (string) $key
                )
            ] = $key;
        }

        foreach (
            $candidates as $candidate
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
