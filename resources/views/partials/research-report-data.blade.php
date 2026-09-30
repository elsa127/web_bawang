@php
    $formatValue = static fn ($value, $decimals = 3) => is_numeric($value) ? number_format((float) $value, $decimals, ',', '.') : 'Belum tersedia';
@endphp
<div class="research-tables space-y-6">
    <section>
        <h3 class="text-lg font-bold">1. Indeks spektral wilayah</h3>
        <p>Rata-rata wilayah dari komposit Sentinel-2 tahunan. NDVI dan NDWI tanpa satuan; B12 adalah reflektansi SWIR-2. Nilai ini tidak menentukan fase tanam atau kebutuhan penyiraman.</p>
        <table><thead><tr><th>Kecamatan</th><th>Tahun</th><th>NDVI</th><th>NDWI</th><th>B12</th></tr></thead><tbody>
        @forelse($reportData['rows'] as $row)
            <tr><td>{{ $row['district'] }}</td><td>{{ $row['year'] }}</td><td>{{ $formatValue($row['ndvi']) }}</td><td>{{ $formatValue($row['ndwi']) }}</td><td>{{ $formatValue($row['b12'], 4) }}</td></tr>
        @empty
            <tr><td colspan="5">Data belum tersedia.</td></tr>
        @endforelse
        </tbody></table>
    </section>
    <section>
        <h3 class="text-lg font-bold">2. Produktivitas aktual dan hasil uji model</h3>
        <p>Semua angka dalam ton/ha. Prediksi berasal dari validasi silang temporal, bukan ramalan musim mendatang. Galat adalah selisih absolut aktual dan prediksi.</p>
        <table><thead><tr><th>Kecamatan</th><th>Tahun</th><th>Aktual</th><th>Prediksi hasil uji</th><th>Galat absolut</th></tr></thead><tbody>
        @forelse($reportData['rows'] as $row)
            <tr><td>{{ $row['district'] }}</td><td>{{ $row['year'] }}</td><td>{{ $formatValue($row['actual']) }}</td><td>{{ $formatValue($row['prediction']) }}</td><td>{{ $formatValue($row['error']) }}</td></tr>
        @empty
            <tr><td colspan="5">Hasil uji belum tersedia.</td></tr>
        @endforelse
        </tbody></table>
    </section>
    <section>
        <h3 class="text-lg font-bold">3. Luas kandidat hasil pemetaan</h3>
        <p>Satuan hektare; ambang skor 0,50. DOA menyaring kemiripan spektral. Luas kandidat bukan luas panen resmi dan belum menjamin seluruh permukiman tersaring.</p>
        <table><thead><tr><th>Kecamatan</th><th>Area valid</th><th>Kandidat awal</th><th>Kandidat setelah DOA</th></tr></thead><tbody>
        @forelse($reportData['areas'] as $area)
            <tr><td>{{ $area['Kecamatan'] }}</td><td>{{ $formatValue($area['Valid_Area_Ha'] ?? null, 2) }}</td><td>{{ $formatValue($area['XGB_Only_Ha_T0_5'] ?? null, 2) }}</td><td>{{ $formatValue($area['DOA_Filtered_Ha_T0_5'] ?? null, 2) }}</td></tr>
        @empty
            <tr><td colspan="4">Data luas belum tersedia.</td></tr>
        @endforelse
        </tbody></table>
        <p>Bagian peta tanpa data tidak berarti bukan kandidat. Data sumber Gondang belum mencakup seluruh wilayah.</p>
    </section>
    <section>
        <h3 class="text-lg font-bold">4. Evaluasi produktivitas - gabungan empat kecamatan</h3>
        <p>Evaluasi tetap memakai seluruh {{ $reportData['metrics']['Observations'] ?? '-' }} observasi kecamatan-tahun, meskipun filter memilih satu kecamatan. MAE dan RMSE dikonversi dari kuintal/ha ke ton/ha (dibagi 10).</p>
        <table><thead><tr><th>Pengujian</th><th>MAE (ton/ha)</th><th>RMSE (ton/ha)</th><th>MAPE (%)</th><th>R&sup2;</th></tr></thead><tbody>
        @foreach(['Temporal_CV' => 'Temporal (antar tahun)', 'Spatial_CV' => 'Spasial (antar kecamatan)'] as $key => $label)
            @php($metrics = $reportData['metrics'][$key] ?? [])
            <tr><td>{{ $label }}</td><td>{{ $formatValue(isset($metrics['MAE']) ? $metrics['MAE'] / 10 : null) }}</td><td>{{ $formatValue(isset($metrics['RMSE']) ? $metrics['RMSE'] / 10 : null) }}</td><td>{{ $formatValue($metrics['MAPE'] ?? null, 2) }}</td><td>{{ $formatValue($metrics['R2'] ?? null) }}</td></tr>
        @endforeach
        </tbody></table>
        <p>MAE: rata-rata galat absolut. RMSE: lebih peka terhadap galat besar. MAPE: rata-rata galat persentase. R&sup2; negatif pada uji spasial menunjukkan generalisasi antar kecamatan masih lemah. Penelitian dengan 12 observasi ini masih eksploratif; hasilnya belum tervalidasi untuk prediksi tiap petak.</p>
        <p>Fitur model: B2, B3, B4, B8, B11, B12, NDVI, dan NDWI. Model produktivitas berbeda dari model klasifikasi kandidat pada peta.</p>
    </section>
    <section class="report-sources">
        <h3 class="text-lg font-bold">Sumber data</h3>
        <ul>
            <li>YIELD_PREDICTION/Yield_Dataset_Nganjuk_2023_2025.csv - indeks dan produktivitas aktual.</li>
            <li>YIELD_PREDICTION/Yield_Temporal_CV_Prediction.csv - prediksi hasil uji.</li>
            <li>YIELD_PREDICTION/FASE_5_Yield_Prediction_Summary.json - metrik evaluasi.</li>
            <li>XGBOOST_V4_1/Area_Candidate_Bawang_DOA_V4_1.csv - luas kandidat STRICT dengan DOA.</li>
        </ul>
        <p>File tersedia di ml_model/data. Angka dibulatkan hanya saat ditampilkan; perhitungan galat memakai nilai asli.</p>
    </section>
</div>
