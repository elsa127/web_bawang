<section id="produktivitas" class="max-w-7xl mx-auto px-4 py-8 space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-brand-800">Prediksi Produktivitas</h2>
            <p class="mt-2 text-slate-600">Perkiraan hasil bawang merah untuk setiap hektare lahan.</p>
        </div>
        <button type="button" @click="isReportModalOpen = true" class="rounded-xl bg-brand-800 px-5 py-3 text-white">Lihat laporan lengkap / PDF</button>
    </div>
    @php
        $allRows = $districtReports['all']['rows'];
        $yieldChartMax = max(1, ceil(max(array_merge([0], array_column($allRows, 'actual'), array_column($allRows, 'prediction')))));
    @endphp
    <p class="rounded-xl bg-amber-50 p-4 text-sm text-slate-700">Angka di bawah adalah prediksi model saat diuji pada data tahun 2025. Data yang dipakai berasal dari 2023-2025.</p>
    {{-- Data terbaru menjadi ringkasan utama; riwayat tetap dapat dibuka tanpa pindah halaman. --}}
    @foreach($districtReports as $districtKey => $districtReport)
        <div x-show="reportDistrict === '{{ $districtKey }}'" @if($districtKey !== $activeDistrict) style="display:none" @endif class="space-y-5">
            <h3 class="text-lg font-semibold">{{ $districtReport['scope'] }} <span class="text-sm font-normal text-slate-500">| Tahun {{ $districtReport['latestYear'] ?? '-' }}</span></h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @forelse($districtReport['latestRows'] as $row)
                    @continue($row['prediction'] === null)
                    <article class="rounded-2xl border border-slate-200 bg-white p-6 space-y-5">
                        <h4 class="text-lg font-semibold">{{ $row['district'] }}</h4>
                        <div>
                            <p class="text-sm text-brand-800">Prediksi hasil bawang merah</p>
                            <p class="mt-1 text-4xl font-bold text-brand-800">{{ $row['prediction'] !== null ? number_format($row['prediction'], 2, ',', '.') : 'Belum tersedia' }}</p>
                            <p class="text-sm text-slate-500">ton per hektare (ton/ha)</p>
                        </div>
                        <div class="space-y-3">
                            @foreach([['key' => 'prediction', 'label' => 'Prediksi model', 'color' => '#9f1239'], ['key' => 'actual', 'label' => 'Hasil tercatat dalam data', 'color' => '#047857']] as $bar)
                                @continue($row[$bar['key']] === null)
                                <div>
                                    <div class="flex justify-between gap-3 text-sm"><span>{{ $bar['label'] }}</span><strong>{{ $row[$bar['key']] !== null ? number_format($row[$bar['key']], 2, ',', '.').' ton/ha' : 'Belum tersedia' }}</strong></div>
                                    <div class="mt-1 h-3 rounded-full bg-slate-100" aria-hidden="true"><div class="h-3 rounded-full" style="width:{{ $row[$bar['key']] !== null ? max(0, min(100, $row[$bar['key']] / $yieldChartMax * 100)) : 0 }}%;background:{{ $bar['color'] }}"></div></div>
                                </div>
                            @endforeach
                        </div>
                        @if($row['error'] !== null)
                        <p class="border-t border-slate-100 pt-3 text-sm text-slate-600">Selisih prediksi dengan data tercatat: <strong>{{ $row['error'] !== null ? number_format($row['error'], 2, ',', '.').' ton/ha' : 'Belum tersedia' }}</strong>. Semakin kecil selisihnya, semakin dekat prediksi dengan hasil tercatat.</p>
                        @endif
                    </article>
                @empty

                @endforelse
            </div>
            <p class="text-sm text-slate-500">Angka ini mewakili rata-rata kecamatan. Hasil setiap petak lahan dapat berbeda. Panjang batang memakai skala yang sama antar kecamatan.</p>
            <details class="rounded-xl border border-slate-200 bg-white p-5">
                <summary class="cursor-pointer font-semibold text-brand-800">Lihat perbandingan dari tahun ke tahun (2023-2025)</summary>
                <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach(collect($districtReport['rows'])->groupBy('district') as $name => $districtRows)
                        @include('partials.data-chart', ['chartTitle' => $name, 'chartUnit' => 'ton/ha', 'chartRows' => $districtRows->sortBy('year')->values()->all(), 'chartMin' => 0, 'chartMax' => $yieldChartMax, 'chartSeries' => [
                            ['key' => 'actual', 'label' => 'Hasil tercatat', 'color' => '#047857'],
                            ['key' => 'prediction', 'label' => 'Prediksi model', 'color' => '#9f1239'],
                        ]])
                    @endforeach
                </div>
            </details>
        </div>
    @endforeach
</section>
