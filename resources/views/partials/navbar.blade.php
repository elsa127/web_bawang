<a href="#main-content" class="sr-only focus:not-sr-only focus:fixed focus:top-2 focus:left-2 focus:z-50 focus:bg-white focus:p-3">Lewati navigasi</a>
<header class="sticky top-0 z-40 border-b border-rose-100 bg-white/95 backdrop-blur">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between gap-3 py-3">
            <a href="#beranda" class="flex items-center gap-3" aria-label="ShallotWatch - Beranda">
                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-brand-800 text-white font-bold" aria-hidden="true">SW</span>
                <span><span class="block text-xl font-bold text-brand-900">ShallotWatch</span><span class="block text-xs text-slate-500">Peta dan prediksi bawang merah</span></span>
            </a>
            <button type="button" @click="isReportModalOpen = true" class="min-h-11 rounded-xl border border-brand-800 px-4 py-2 text-sm font-semibold text-brand-800 hover:bg-rose-50">Cetak laporan</button>
        </div>
        <nav aria-label="Navigasi utama" class="flex gap-1 overflow-x-auto pb-2">
            @foreach(['beranda' => 'Beranda', 'peta' => 'Peta lahan', 'produktivitas' => 'Prediksi', 'analisis' => 'Kondisi lahan', 'tentang' => 'Tentang'] as $anchor => $label)
                <a href="#{{ $anchor }}" @click="activeSection = '{{ $anchor }}'" :aria-current="activeSection === '{{ $anchor }}' ? 'location' : null" :class="activeSection === '{{ $anchor }}' ? 'bg-brand-800 text-white' : 'text-slate-600 hover:bg-rose-50'" class="shrink-0 rounded-lg px-4 py-3 text-sm font-semibold">{{ $label }}</a>
            @endforeach
        </nav>
    </div>
</header>
