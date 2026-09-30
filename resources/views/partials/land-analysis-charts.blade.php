<section id="analisis" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-10 space-y-6">
    <h2 class="text-2xl font-bold text-brand-800">Analisis Kondisi Lahan</h2>
    <p class="text-base text-slate-600">Satelit membantu melihat vegetasi atau tumbuhan dalam satu kecamatan. Indeks vegetasi disebut NDVI. Angka ini tidak menunjukkan langsung kondisi tanaman bawang pada setiap petak.</p>
    {{-- Ringkasan terbaru memakai baris yang sama dengan tabel indeks pada laporan. --}}
    @foreach($districtReports as $districtKey => $districtReport)
        <div x-show="reportDistrict === '{{ $districtKey }}'" @if($districtKey !== $activeDistrict) style="display:none" @endif class="grid grid-cols-1 gap-4 {{ $districtKey === 'all' ? 'sm:grid-cols-2' : 'max-w-2xl mx-auto' }}">
            @foreach($districtReport['latestRows'] as $row)
                <article class="rounded-2xl border border-slate-200 bg-white p-5 sm:p-6 space-y-4">
                    <h3 class="text-lg font-semibold">{{ $row['district'] }} <span class="text-base font-normal text-slate-500">| {{ $row['year'] }}</span></h3>
                    <dl class="divide-y divide-slate-100">
                        @foreach(['ndvi' => 'Gambaran tumbuhan (NDVI)', 'ndwi' => 'Perbandingan pantulan cahaya (NDWI)', 'b12' => 'Pantulan inframerah (B12)'] as $key => $label)
                            @if($row[$key] !== null)
                                <div class="flex items-center justify-between gap-4 py-4 first:pt-0 last:pb-0">
                                    <dt class="text-base text-slate-600">{{ $label }}</dt>
                                    <dd class="shrink-0 text-xl sm:text-2xl font-bold text-brand-800 tabular-nums">{{ number_format($row[$key], $key === 'b12' ? 4 : 3, ',', '.') }}</dd>
                                </div>
                            @endif
                        @endforeach
                    </dl>
                </article>
            @endforeach
        </div>
    @endforeach
    <div class="rounded-2xl border border-slate-200 bg-white p-5 sm:p-6">
        <h3 class="font-semibold text-slate-900">Cara membaca angka satelit</h3>
        <dl class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-5 text-sm leading-relaxed">
            <div><dt class="font-semibold text-brand-800">NDVI - Gambaran tumbuhan</dt><dd class="mt-1 text-slate-600">Membantu melihat karakter tumbuhan di wilayah tersebut. Bukan persentase luas tanaman bawang.</dd></div>
            <div><dt class="font-semibold text-brand-800">NDWI - Pantulan cahaya</dt><dd class="mt-1 text-slate-600">Membantu model membedakan tutupan lahan. Nilai negatif tidak berarti tanah kekurangan air.</dd></div>
            <div><dt class="font-semibold text-brand-800">B12 - Pantulan inframerah</dt><dd class="mt-1 text-slate-600">Salah satu masukan satelit untuk model. Bukan ukuran kesuburan tanah.</dd></div>
        </dl>
    </div>
    <p class="text-sm leading-relaxed text-slate-600">Angka satelit ini tidak memiliki satuan dan merupakan rata-rata satu kecamatan. Untuk mengetahui kondisi tanah atau kebutuhan penyiraman pada petak tertentu, diperlukan pemeriksaan di lapangan.</p>
    <details class="rounded-xl border border-slate-200 bg-white p-5">
    <summary class="cursor-pointer font-semibold text-brand-800">Lihat kondisi vegetasi dari satelit</summary>
    @foreach($districtReports as $districtKey => $districtReport)
        <div x-show="reportDistrict === '{{ $districtKey }}'" @if($districtKey !== $activeDistrict) style="display:none" @endif class="space-y-3 {{ $districtKey !== 'all' ? 'max-w-2xl mx-auto' : '' }}">
            <h3 class="font-semibold">{{ $districtReport['scope'] }}</h3>
            <div class="grid grid-cols-1 {{ $districtKey === 'all' ? 'sm:grid-cols-2' : '' }} gap-4">
                @foreach(collect($districtReport['rows'])->groupBy('district') as $name => $districtRows)
                    @include('partials.data-chart', ['chartTitle' => $name.' - NDVI tahunan', 'chartUnit' => 'indeks tanpa satuan', 'chartRows' => $districtRows->sortBy('year')->values()->all(), 'chartMin' => -1, 'chartMax' => 1, 'chartSeries' => [
                        ['key' => 'ndvi', 'label' => 'NDVI wilayah', 'color' => '#047857'],
                    ]])
                @endforeach
            </div>
        </div>
    @endforeach
    </details>
</section>
