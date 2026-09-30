<section id="analisis" class="py-8 sm:py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        <div>
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Komposit Sentinel-2 · Sukomoro · 2025</p>
            <h2 class="mt-2 text-xl sm:text-2xl font-bold text-brand-800">Analisis Indeks Spektral Wilayah</h2>
            <p class="mt-2 max-w-3xl text-sm text-slate-600">Rata-rata wilayah administrasi dari hasil pengolahan Colab. Angka ini mencakup tutupan lahan dalam kecamatan, bukan pengukuran khusus tanaman bawang pada setiap petak.</p>
        </div>

        {{-- Kartu hanya memakai kolom yang tersedia; nilai kosong tidak diganti nol. --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
            @foreach([
                ['key' => 'NDVI', 'title' => 'NDVI · Indeks vegetasi', 'decimals' => 3, 'description' => 'Indeks dari band B8 dan B4. Menunjukkan karakter spektral vegetasi pada tingkat wilayah, tanpa menentukan fase tanam.'],
                ['key' => 'NDWI', 'title' => 'NDWI · Indeks spektral air', 'decimals' => 3, 'description' => 'Dalam penelitian ini dihitung dari band B3 dan B8. Nilai negatif bukan persentase kelembapan tanah atau kebutuhan penyiraman.'],
                ['key' => 'B12', 'title' => 'B12 · Reflektansi SWIR-2', 'decimals' => 4, 'description' => 'Reflektansi inframerah gelombang pendek yang digunakan sebagai fitur model. Nilai ini bukan kadar air tanah.'],
            ] as $metric)
                <article class="rounded-2xl border border-slate-200 bg-white p-5 sm:p-6 space-y-4">
                    <h3 class="text-sm font-semibold text-slate-700">{{ $metric['title'] }}</h3>
                    <p class="text-4xl font-bold tracking-tight text-slate-900 tabular-nums">
                        {{ isset($conditionAnalysis['current'][$metric['key']]) ? number_format($conditionAnalysis['current'][$metric['key']], $metric['decimals'], ',', '.') : 'Belum tersedia' }}
                    </p>
                    <p class="text-xs font-semibold text-brand-800">Rata-rata Sukomoro 2025 · tanpa satuan</p>
                    <p class="text-xs leading-relaxed text-slate-500">{{ $metric['description'] }}</p>
                </article>
            @endforeach
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-2 gap-5">
            <article class="min-w-0 rounded-2xl border border-slate-200 bg-white p-5 sm:p-6">
                <h3 class="font-bold text-slate-900">Riwayat tahunan Sukomoro</h3>
                <p class="mt-1 text-xs text-slate-500">Perbandingan komposit tahunan 2023–2025.</p>
                <div class="mt-4 overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <caption class="sr-only">NDVI, NDWI, dan B12 Sukomoro per tahun</caption>
                        <thead class="border-b border-slate-200 text-xs text-slate-500"><tr><th scope="col" class="py-3 pr-3">Tahun</th><th scope="col" class="py-3 px-2 text-right">NDVI</th><th scope="col" class="py-3 px-2 text-right">NDWI</th><th scope="col" class="py-3 pl-2 text-right">B12</th></tr></thead>
                        <tbody class="divide-y divide-slate-100 tabular-nums">
                            @forelse($conditionAnalysis['annual'] as $row)
                                <tr><th scope="row" class="py-3 pr-3 font-medium text-slate-800">{{ $row['year'] }}</th>
                                    @foreach(['ndvi' => 3, 'ndwi' => 3, 'b12' => 4] as $key => $decimals)
                                        <td class="py-3 px-2 text-right text-slate-600">{{ $row[$key] !== null ? number_format($row[$key], $decimals, ',', '.') : '—' }}</td>
                                    @endforeach
                                </tr>
                            @empty
                                <tr><td colspan="4" class="py-4 text-slate-500">Data tahunan belum tersedia.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </article>
            <article class="min-w-0 rounded-2xl border border-slate-200 bg-white p-5 sm:p-6">
                <h3 class="font-bold text-slate-900">Perbandingan kecamatan · 2025</h3>
                <p class="mt-1 text-xs text-slate-500">Ringkasan indeks pada periode tahun yang sama.</p>
                <div class="mt-4 overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <caption class="sr-only">NDVI dan NDWI empat kecamatan tahun 2025</caption>
                        <thead class="border-b border-slate-200 text-xs text-slate-500"><tr><th scope="col" class="py-3 pr-3">Kecamatan</th><th scope="col" class="py-3 px-2 text-right">NDVI</th><th scope="col" class="py-3 pl-2 text-right">NDWI</th></tr></thead>
                        <tbody class="divide-y divide-slate-100 tabular-nums">
                            @forelse($conditionAnalysis['districts'] as $row)
                                <tr><th scope="row" class="py-3 pr-3 font-medium text-slate-800">{{ $row['district'] }}</th>
                                    @foreach(['ndvi', 'ndwi'] as $key)
                                        <td class="py-3 px-2 text-right text-slate-600">{{ $row[$key] !== null ? number_format($row[$key], 3, ',', '.') : '—' }}</td>
                                    @endforeach
                                </tr>
                            @empty
                                <tr><td colspan="3" class="py-4 text-slate-500">Data kecamatan belum tersedia.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </article>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5 text-xs leading-relaxed text-slate-600 space-y-2">
            <p><strong class="text-slate-800">Sumber:</strong> <span class="break-all">YIELD_PREDICTION/Yield_Dataset_Nganjuk_2023_2025.csv</span>. Metode: median citra multitemporal per tahun, kemudian rata-rata dalam batas kecamatan.</p>
            <p>NDVI dan NDWI ditampilkan langsung dari kolom hasil Colab, bukan dihitung ulang dari rata-rata band. Angka dibulatkan hanya untuk tampilan.</p>
            <p>Tanggal tanam, fase pertumbuhan, kadar air tanah, dan penyakit tanaman belum diukur dalam dataset ini. Data tersebut diperlukan sebelum menyusun rekomendasi penyiraman atau perawatan per lahan.</p>
        </div>
    </div>
</section>
