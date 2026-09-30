<section id="analisis" class="max-w-7xl mx-auto px-4 py-8 space-y-5">
    <h2 class="text-2xl font-bold text-brand-800">Analisis Kondisi Lahan</h2>
    <p class="text-sm text-slate-600">Satelit membantu melihat vegetasi atau tumbuhan dalam satu kecamatan. Indeks vegetasi disebut NDVI. Angka ini tidak menunjukkan langsung kondisi tanaman bawang pada setiap petak.</p>
    <details class="rounded-xl border border-slate-200 bg-white p-5">
    <summary class="cursor-pointer font-semibold text-brand-800">Lihat kondisi vegetasi dari satelit</summary>
    @foreach($districtReports as $districtKey => $districtReport)
        <div x-show="reportDistrict === '{{ $districtKey }}'" @if($districtKey !== $activeDistrict) style="display:none" @endif class="space-y-3">
            <h3 class="font-semibold">{{ $districtReport['scope'] }}</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                @foreach(collect($districtReport['rows'])->groupBy('district') as $name => $districtRows)
                    @include('partials.data-chart', ['chartTitle' => $name.' - NDVI tahunan', 'chartUnit' => 'indeks tanpa satuan', 'chartRows' => $districtRows->sortBy('year')->values()->all(), 'chartMin' => -1, 'chartMax' => 1, 'chartSeries' => [
                        ['key' => 'ndvi', 'label' => 'NDVI wilayah', 'color' => '#047857'],
                    ]])
                @endforeach
            </div>
        </div>
    @endforeach
    </details>
    <a href="#produktivitas" class="inline-block font-semibold text-brand-800">Lanjut ke grafik produktivitas &rarr;</a>
</section>
