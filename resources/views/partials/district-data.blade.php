<section id="produktivitas" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-10 space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-brand-800">Prediksi Produktivitas</h2>
            <p class="mt-2 text-slate-600">Perkiraan hasil bawang merah untuk setiap hektare lahan.</p>
        </div>
        <button type="button" @click="isReportModalOpen = true" class="w-full sm:w-auto rounded-xl bg-brand-800 px-5 py-3 text-white font-semibold">Lihat laporan lengkap / PDF</button>
    </div>
    <div class="flex flex-wrap items-center gap-3 rounded-xl border border-slate-200 bg-white p-4">
        <label for="prediction-district" class="text-base font-semibold">Kecamatan</label>
        <select id="prediction-district" :value="reportDistrict" @change="$dispatch('district-requested', $event.target.value)" class="min-h-11 w-full sm:w-auto rounded-lg border border-slate-300 bg-white px-4 py-2">
            @foreach($districtReports as $districtKey => $districtReport)
                <option value="{{ $districtKey }}" @selected($districtKey === $activeDistrict)>{{ $districtReport['scope'] }}</option>
            @endforeach
        </select>
        <span class="text-sm text-slate-500" role="status">Pilihan ini juga berlaku untuk peta dan laporan.</span>
    </div>
    @php
        $allRows = $districtReports['all']['rows'];
        $yieldChartMax = max(1, ceil(max(array_merge([0], array_column($allRows, 'actual'), array_column($allRows, 'prediction')))));
    @endphp
    <p class="rounded-xl bg-amber-50 p-4 text-base text-slate-700">Angka di bawah adalah prediksi model saat diuji pada data tahun 2025. Data yang dipakai berasal dari 2023-2025.</p>
    {{-- Ringkasan memakai data terbaru; riwayat lengkap tersedia pada laporan PDF. --}}
    @foreach($districtReports as $districtKey => $districtReport)
        <div x-show="reportDistrict === '{{ $districtKey }}'" @if($districtKey !== $activeDistrict) style="display:none" @endif class="space-y-5 {{ $districtKey !== 'all' ? 'max-w-2xl mx-auto' : '' }}">
            <h3 class="text-lg font-semibold">{{ $districtReport['scope'] }} <span class="text-base font-normal text-slate-500">| Tahun {{ $districtReport['latestYear'] ?? '-' }}</span></h3>
            <div class="grid grid-cols-1 {{ $districtKey === 'all' ? 'md:grid-cols-2' : '' }} gap-4">
                @forelse($districtReport['latestRows'] as $row)
                    @continue($row['prediction'] === null)
                    <article class="rounded-2xl border border-slate-200 bg-white p-5 sm:p-6 space-y-5">
                        <h4 class="text-lg font-semibold">{{ $row['district'] }}</h4>
                        <div class="rounded-xl bg-rose-50 p-4 sm:p-5">
                            <p class="text-base text-brand-800">Prediksi hasil bawang merah</p>
                            <p class="mt-2 text-4xl sm:text-5xl font-bold tabular-nums text-brand-800">{{ $row['prediction'] !== null ? number_format($row['prediction'], 2, ',', '.') : 'Belum tersedia' }}</p>
                            <p class="text-base text-slate-500">ton untuk setiap 1 hektare lahan (10.000 m&sup2;)</p>
                        </div>
                        <div class="space-y-3">
                            @foreach([['key' => 'prediction', 'label' => 'Prediksi model', 'color' => '#9f1239'], ['key' => 'actual', 'label' => 'Hasil panen tercatat', 'color' => '#047857']] as $bar)
                                @continue($row[$bar['key']] === null)
                                <div>
                                    <div class="flex flex-wrap justify-between gap-x-3 gap-y-1 text-base"><span>{{ $bar['label'] }}</span><strong class="tabular-nums whitespace-nowrap">{{ $row[$bar['key']] !== null ? number_format($row[$bar['key']], 2, ',', '.').' ton/ha' : 'Belum tersedia' }}</strong></div>
                                    <div class="mt-1 h-3 rounded-full bg-slate-100" aria-hidden="true"><div class="h-3 rounded-full" style="width:{{ $row[$bar['key']] !== null ? max(0, min(100, $row[$bar['key']] / $yieldChartMax * 100)) : 0 }}%;background:{{ $bar['color'] }}"></div></div>
                                </div>
                            @endforeach
                        </div>
                        @if($row['error'] !== null)
                        <p class="border-t border-slate-100 pt-3 text-base text-slate-600">Selisih: <strong>{{ $row['error'] !== null ? number_format($row['error'], 2, ',', '.').' ton/ha' : 'Belum tersedia' }}</strong>.</p>
                        @endif
                    </article>
                @empty

                @endforelse
            </div>
            <p class="text-base text-slate-500">Angka ini mewakili rata-rata kecamatan. Hasil setiap petak lahan dapat berbeda. Panjang batang memakai skala yang sama antar kecamatan.</p>

        </div>
    @endforeach
    <div class="rounded-2xl border border-slate-200 bg-white p-5 sm:p-6 space-y-4">
        <h3 class="text-lg font-bold">Seberapa dekat prediksi dengan data sebenarnya</h3>
        <p class="text-base text-slate-600">Hasil pengujian gabungan empat kecamatan, berdasarkan {{ $districtReports['all']['metrics']['Observations'] ?? 12 }} catatan kecamatan dan tahun dari 2023-2025. Angka evaluasi ini tetap sama saat memilih kecamatan.</p>
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            @foreach(['Temporal_CV' => 'Saat memperkirakan hasil pada tahun lain', 'Spatial_CV' => 'Saat memperkirakan hasil di kecamatan lain'] as $metricKey => $label)
                @php
                    $metrics = $districtReports['all']['metrics'][$metricKey] ?? [];
                @endphp
                @if($metrics !== [])
                    <div class="rounded-xl border border-slate-100 bg-slate-50 p-4 sm:p-5 space-y-4">
                        <h4 class="font-semibold">{{ $label }}</h4>
                        @isset($metrics['MAE'])
                            <p class="text-base text-slate-700">Dalam pengujian ini, prediksi berbeda dari hasil tercatat rata-rata sekitar <strong>{{ number_format($metrics['MAE'] / 10, 2, ',', '.') }} ton per hektare</strong>. Semakin kecil selisihnya, semakin dekat prediksi dengan hasil tercatat.</p>
                        @endisset
                        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            @foreach([
                                ['key' => 'MAE', 'label' => 'Rata-rata selisih (MAE)', 'divisor' => 10, 'unit' => 'ton/ha', 'decimals' => 3],
                                ['key' => 'RMSE', 'label' => 'Ukuran kesalahan besar (RMSE)', 'divisor' => 10, 'unit' => 'ton/ha', 'decimals' => 3],
                                ['key' => 'MAPE', 'label' => 'Rata-rata selisih dalam persen (MAPE)', 'divisor' => 1, 'unit' => '%', 'decimals' => 2],
                                ['key' => 'R2', 'label' => 'Kecocokan model (R2)', 'divisor' => 1, 'unit' => '', 'decimals' => 3],
                            ] as $metric)
                                @isset($metrics[$metric['key']])
                                    <div class="rounded-lg bg-white p-3"><dt class="text-sm text-slate-600 sm:min-h-10">{{ $metric['label'] }}</dt><dd class="mt-1 text-xl font-bold text-brand-800 tabular-nums">{{ number_format($metrics[$metric['key']] / $metric['divisor'], $metric['decimals'], ',', '.') }} <span class="text-sm font-normal">{{ $metric['unit'] }}</span></dd></div>
                                @endisset
                            @endforeach
                        </dl>
                    </div>
                @endif
            @endforeach
        </div>
        <p class="text-sm leading-relaxed text-slate-600">MAE adalah rata-rata selisih dalam ton per hektare. MAPE menyatakannya dalam persen, bukan persentase akurasi. RMSE lebih peka terhadap kesalahan besar. R2 menilai kecocokan prediksi; nilai negatif pada uji antar kecamatan berarti hasilnya lebih buruk daripada memakai rata-rata data uji. Prediksi ini masih perlu diuji lebih lanjut sebelum dipakai untuk memperkirakan panen tiap petak.</p>
    </div>
</section>
