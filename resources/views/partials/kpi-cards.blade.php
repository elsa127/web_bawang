{{-- Komponen badge dummy yang dipakai di seluruh dashboard --}}

<section class="pb-8 sm:pb-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- Info bar status data --}}
        <div class="mb-4 flex flex-wrap items-center gap-3 text-xs">
            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-emerald-50 border border-emerald-200 text-emerald-800 font-semibold">
                <span class="w-2 h-2 rounded-full bg-emerald-500"></span> ✅ Data Nyata — CSV / Colab
            </span>
            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-amber-50 border border-amber-200 text-amber-800 font-semibold">
                <span class="w-2 h-2 rounded-full bg-amber-400"></span> ⚠️ Data Belum Tersambung
            </span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-5">

            {{-- Card 1: Kondisi Lahan — ✅ REAL dari NDVI CSV 2025 --}}
            <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs hover:shadow-md transition-shadow relative overflow-hidden flex items-center gap-4">
                <div class="absolute left-0 top-0 bottom-0 w-1.5 bg-emerald-600"></div>
                @if($kpiSummary[0]['value'])
                    <div class="absolute top-2 right-2">
                        <span class="text-[9px] font-bold px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 border border-emerald-200">✅ REAL</span>
                    </div>
                @endif
                <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-700 flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                    </svg>
                </div>
                <div class="flex flex-col">
                    <span class="text-xs font-medium text-slate-500">{{ $kpiSummary[0]['title'] }}</span>
                    @if($kpiSummary[0]['value'])
                        <span class="text-base sm:text-lg font-bold text-slate-900 leading-snug">{{ $kpiSummary[0]['value'] }}</span>
                        <span class="text-xs text-emerald-700 font-medium mt-0.5">{{ $kpiSummary[0]['subtitle'] }}</span>
                    @else
                        <span class="text-sm font-bold text-amber-600 leading-snug">⚠️ Belum Tersambung</span>
                        <span class="text-xs text-slate-400 font-medium mt-0.5">Perlu data sensor lapangan</span>
                    @endif
                </div>
            </div>

            {{-- Card 2: Produktivitas Model — ✅ REAL prediksi XGBoost --}}
            <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs hover:shadow-md transition-shadow relative overflow-hidden flex items-center gap-4">
                <div class="absolute left-0 top-0 bottom-0 w-1.5 bg-rose-500"></div>
                @if($kpiSummary[1]['value'])
                    <div class="absolute top-2 right-2">
                        <span class="text-[9px] font-bold px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 border border-emerald-200">✅ REAL</span>
                    </div>
                @endif
                <div class="w-12 h-12 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                    </svg>
                </div>
                <div class="flex flex-col">
                    <span class="text-xs font-medium text-slate-500">{{ $kpiSummary[1]['title'] }}</span>
                    @if($kpiSummary[1]['value'])
                        <span class="text-base sm:text-lg font-bold text-slate-900 leading-snug">{{ $kpiSummary[1]['value'] }}</span>
                        <span class="text-xs text-rose-600 font-medium mt-0.5">{{ $kpiSummary[1]['subtitle'] }}</span>
                    @else
                        <span class="text-sm font-bold text-amber-600 leading-snug">⚠️ Belum Tersambung</span>
                        <span class="text-xs text-slate-400 font-medium mt-0.5">Perlu model prediksi</span>
                    @endif
                </div>
            </div>

            {{-- Card 3: Analisis Pertumbuhan — ⚠️ DUMMY --}}
            <div class="bg-white rounded-2xl p-5 border border-amber-200/80 shadow-xs hover:shadow-md transition-shadow relative overflow-hidden flex items-center gap-4">
                <div class="absolute left-0 top-0 bottom-0 w-1.5 bg-amber-400"></div>
                <div class="absolute top-2 right-2">
                    <span class="text-[9px] font-bold px-1.5 py-0.5 rounded bg-amber-100 text-amber-700 border border-amber-200">⚠️ DUMMY</span>
                </div>
                <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                </div>
                <div class="flex flex-col">
                    <span class="text-xs font-medium text-slate-500">{{ $kpiSummary[2]['title'] }}</span>
                    <span class="text-sm font-bold text-amber-600 leading-snug">Belum Tersambung</span>
                    <span class="text-xs text-slate-400 font-medium mt-0.5">Perlu data HST lapangan</span>
                </div>
            </div>

            {{-- Card 4: Rekomendasi — ⚠️ DUMMY --}}
            <div class="bg-white rounded-2xl p-5 border border-amber-200/80 shadow-xs hover:shadow-md transition-shadow relative overflow-hidden flex items-center gap-4">
                <div class="absolute left-0 top-0 bottom-0 w-1.5 bg-amber-400"></div>
                <div class="absolute top-2 right-2">
                    <span class="text-[9px] font-bold px-1.5 py-0.5 rounded bg-amber-100 text-amber-700 border border-amber-200">⚠️ DUMMY</span>
                </div>
                <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-500 flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
                    </svg>
                </div>
                <div class="flex flex-col">
                    <span class="text-xs font-medium text-slate-500">{{ $kpiSummary[3]['title'] }}</span>
                    <span class="text-sm font-bold text-amber-600 leading-snug">Belum Tersambung</span>
                    <span class="text-xs text-slate-400 font-medium mt-0.5">Perlu rules engine sensor</span>
                </div>
            </div>

        </div>
    </div>
</section>
