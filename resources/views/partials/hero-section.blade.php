<section id="beranda" class="py-8 sm:py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="rounded-3xl border border-rose-100 bg-white p-6 sm:p-10 grid grid-cols-1 lg:grid-cols-2 gap-8 items-center">
            <div class="space-y-5">
                <p class="text-sm font-semibold text-brand-800">Penelitian Nganjuk | Data 2023-2025</p>
                <h1 class="text-3xl sm:text-4xl font-bold leading-tight text-slate-900">Pantau Kondisi Lahan dan Lihat Prediksi Bawang Merah</h1>
                <p class="text-slate-600 leading-relaxed">Pilih kecamatan untuk melihat area kandidat lahan dan perkiraan hasil bawang merah per hektare dari hasil penelitian.</p>
                <div class="flex flex-wrap gap-3">
                    <a href="#peta" class="rounded-xl bg-brand-800 px-5 py-3 font-semibold text-white hover:bg-brand-900">Mulai dari peta</a>
                    <a href="#produktivitas" class="rounded-xl border border-slate-300 px-5 py-3 font-semibold text-brand-800 hover:bg-rose-50">Lihat prediksi</a>
                </div>
                <p class="text-xs text-slate-500">Mencakup Bagor, Gondang, Rejoso, dan Sukomoro.</p>
            </div>
            <ol aria-label="Cara menggunakan ShallotWatch" class="space-y-4 rounded-2xl bg-rose-50 p-5 sm:p-6">
                @foreach([
                    ['title' => 'Pilih kecamatan', 'text' => 'Pilih wilayah di peta atau bagian prediksi. Semua data langsung mengikuti pilihanmu.'],
                    ['title' => 'Lihat peta dan prediksi', 'text' => 'Bandingkan angka prediksi dengan hasil yang tercatat dalam data penelitian.'],
                    ['title' => 'Simpan laporan', 'text' => 'Tekan Cetak laporan untuk melihat tabel lengkap dan menyimpannya sebagai PDF.'],
                ] as $step)
                    <li class="flex gap-3"><span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-white text-brand-800 font-bold">{{ $loop->iteration }}</span><div><h2 class="font-semibold text-slate-900">{{ $step['title'] }}</h2><p class="mt-1 text-sm leading-relaxed text-slate-600">{{ $step['text'] }}</p></div></li>
                @endforeach
            </ol>
        </div>
    </div>
</section>
