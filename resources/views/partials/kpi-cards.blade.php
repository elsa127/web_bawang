<section class="pb-8 sm:pb-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-5">

            {{-- KPI Card 1: Kondisi Lahan --}}
            <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs hover:shadow-md transition-shadow relative overflow-hidden flex items-center gap-4">
                <div class="absolute left-0 top-0 bottom-0 w-1.5 bg-emerald-600"></div>
                <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-700 flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                    </svg>
                </div>
                <div class="flex flex-col">
                    <span class="text-xs font-medium text-slate-500">{{ $kpiSummary[0]['title'] }}</span>
                    <span class="text-base sm:text-lg font-bold text-slate-900 leading-snug">
                        {{ $kpiSummary[0]['value'] ?? 'Memuat Data...' }}
                    </span>
                    <span class="text-xs text-emerald-700 font-medium mt-0.5">
                        {{ $kpiSummary[0]['subtitle'] ?? 'Sentinel-2 Nganjuk' }}
                    </span>
                </div>
            </div>

            {{-- KPI Card 2: Produktivitas Model XGBoost --}}
            <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs hover:shadow-md transition-shadow relative overflow-hidden flex items-center gap-4">
                <div class="absolute left-0 top-0 bottom-0 w-1.5 bg-rose-500"></div>
                <div class="w-12 h-12 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                    </svg>
                </div>
                <div class="flex flex-col">
                    <span class="text-xs font-medium text-slate-500">{{ $kpiSummary[1]['title'] }}</span>
                    <span class="text-base sm:text-lg font-bold text-slate-900 leading-snug">
                        {{ $kpiSummary[1]['value'] ?? 'Memuat Data...' }}
                    </span>
                    <span class="text-xs text-rose-600 font-medium mt-0.5">
                        {{ $kpiSummary[1]['subtitle'] ?? 'Model XGBoost' }}
                    </span>
                </div>
            </div>

            {{-- KPI Card 3: Analisis Pertumbuhan --}}
            <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs hover:shadow-md transition-shadow relative overflow-hidden flex items-center gap-4">
                <div class="absolute left-0 top-0 bottom-0 w-1.5 bg-brand-800"></div>
                <div class="w-12 h-12 rounded-xl bg-brand-50 text-brand-800 flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                </div>
                <div class="flex flex-col">
                    <span class="text-xs font-medium text-slate-500">{{ $kpiSummary[2]['title'] }}</span>
                    <span class="text-base sm:text-lg font-bold text-slate-900 leading-snug">
                        {{ $kpiSummary[2]['value'] ?? 'Fase Vegetatif' }}
                    </span>
                    <span class="text-xs text-brand-800 font-medium mt-0.5">
                        {{ $kpiSummary[2]['subtitle'] ?? 'Estimasi Musim Tanam 2026' }}
                    </span>
                </div>
            </div>

            {{-- KPI Card 4: Rekomendasi Lapangan --}}
            <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs hover:shadow-md transition-shadow relative overflow-hidden flex items-center gap-4">
                <div class="absolute left-0 top-0 bottom-0 w-1.5 bg-amber-500"></div>
                <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
                    </svg>
                </div>
                <div class="flex flex-col">
                    <span class="text-xs font-medium text-slate-500">{{ $kpiSummary[3]['title'] }}</span>
                    <span class="text-base sm:text-lg font-bold text-slate-900 leading-snug">
                        {{ $kpiSummary[3]['value'] ?? 'Siram Pagi Hari' }}
                    </span>
                    <span class="text-xs text-amber-700 font-medium mt-0.5">
                        {{ $kpiSummary[3]['subtitle'] ?? 'Kelembapan SWIR-2 Terjaga' }}
                    </span>
                </div>
            </div>

        </div>
    </div>
</section>
