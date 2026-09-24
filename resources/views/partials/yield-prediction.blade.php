<section id="produktivitas" class="py-8 sm:py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6 sm:space-y-8">
        
        <!-- Section Header -->
        <div>
            <div class="flex items-center gap-2.5 text-brand-800 font-bold text-xl sm:text-2xl tracking-tight">
                <h2>Prediksi Produktivitas Panen</h2>
            </div>
            <p class="text-slate-600 text-xs sm:text-sm mt-1 max-w-2xl">
                Lihat tren estimasi panen bawang merah berdasarkan model data historis dan kondisi cuaca saat ini.
            </p>
        </div>

        <!-- Highlight Banner Card -->
        <div class="bg-white border border-slate-200/90 rounded-2xl sm:rounded-3xl p-6 sm:p-8 shadow-xs">
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
                <!-- Left Details -->
                <div class="space-y-3 max-w-2xl">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-50 text-emerald-800 border border-emerald-200 text-xs font-bold">
                        <span class="w-2 h-2 rounded-full bg-emerald-600"></span>
                        <span>{{ $yieldPrediction['status_badge'] }}</span>
                    </div>

                    <h3 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
                        {{ $yieldPrediction['estimate_title'] }}
                    </h3>

                    <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">
                        {{ $yieldPrediction['estimate_desc'] }}
                    </p>
                </div>

                <!-- Right Big Projected Box -->
                <div class="bg-brand-800 text-white rounded-2xl p-6 sm:p-8 flex flex-col items-center justify-center text-center shadow-lg min-w-[240px] shrink-0 border border-brand-700">
                    <span class="text-xs uppercase tracking-wider font-semibold text-rose-200">
                        Perkiraan Hasil Panen
                    </span>
                    <span class="text-5xl sm:text-6xl font-extrabold tracking-tight my-1">
                        {{ $yieldPrediction['projected_yield'] }}
                    </span>
                    <span class="text-xs sm:text-sm font-semibold text-rose-100">
                        {{ $yieldPrediction['unit'] }}
                    </span>
                    <span class="mt-3 px-3 py-1 rounded-full bg-black/20 text-rose-100 text-[11px] font-medium border border-white/10">
                        {{ $yieldPrediction['accuracy_badge'] }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Two Column Comparison & Accuracy Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            
            <!-- Left Column: Perbandingan Hasil Produktivitas (7 Cols) -->
            <div class="lg:col-span-7 bg-white border border-slate-200/90 rounded-2xl sm:rounded-3xl p-5 sm:p-6 shadow-xs space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-100 pb-3">
                    <div>
                        <h3 class="text-sm sm:text-base font-bold text-slate-900">Perbandingan Hasil Produktivitas</h3>
                        <span class="text-xs text-slate-500 font-medium">Perbandingan tren aktual dengan estimasi per musim tanam</span>
                    </div>

                    <!-- Legend -->
                    <div class="flex items-center gap-3 text-xs font-semibold text-slate-700">
                        <div class="flex items-center gap-1.5">
                            <span class="w-3 h-3 rounded-xs bg-slate-300 border border-slate-400"></span>
                            <span>Aktual</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span class="w-3 h-3 rounded-xs bg-slate-900"></span>
                            <span>Prediksi AI</span>
                        </div>
                    </div>
                </div>

                <!-- Comparison List -->
                <div class="space-y-4 pt-1">
                    @foreach($yieldPrediction['comparisons'] as $comp)
                        <div class="space-y-1.5">
                            <div class="flex items-center justify-between text-xs font-semibold">
                                <span class="{{ $comp['is_current'] ? 'text-brand-800 font-bold' : 'text-slate-700' }}">
                                    {{ $comp['season'] }}
                                </span>
                                <span class="text-slate-500 font-medium">
                                    Aktual: <strong class="text-slate-800">{{ $comp['actual'] }}</strong> | Prediksi: <strong class="text-brand-800">{{ $comp['predicted'] }}</strong>
                                </span>
                            </div>

                            @if($comp['is_current'])
                                <!-- Highlighted Current Bar -->
                                <div class="w-full bg-brand-800 rounded-lg p-2 flex items-center justify-between text-white text-xs font-bold shadow-xs">
                                    <span>{{ $comp['actual'] }}</span>
                                    <span>{{ $comp['predicted'] }}</span>
                                </div>
                            @else
                                <!-- Dual Comparison Bar -->
                                <div class="w-full bg-slate-100 h-6 rounded-lg p-0.5 flex gap-1">
                                    <div class="bg-slate-300 h-full rounded-md flex items-center px-2 text-[10px] font-bold text-slate-700" style="width: {{ $comp['actual_pct'] }}%">
                                        {{ $comp['actual'] }}
                                    </div>
                                    <div class="bg-slate-800 h-full rounded-md flex items-center px-2 text-[10px] font-bold text-white" style="width: {{ $comp['pred_pct'] }}%">
                                        {{ $comp['predicted'] }}
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>

                <p class="text-[11px] text-slate-400 leading-relaxed pt-2 border-t border-slate-100">
                    *Rerata grafik panen berbobot dibandingkan terhadap data historis hasil panen riil petani dan granulasi model sistem cerdas.
                </p>
            </div>

            <!-- Right Column: Model Accuracy Metrics (5 Cols) -->
            <div class="lg:col-span-5 space-y-4">
                
                <!-- Card: Tingkat Keakuratan Model -->
                <div class="bg-white border border-slate-200/90 rounded-2xl sm:rounded-3xl p-5 sm:p-6 shadow-xs space-y-4">
                    <h3 class="text-sm sm:text-base font-bold text-slate-900">Tingkat Keakuratan Model</h3>

                    <!-- Accuracy Level Bar -->
                    <div class="space-y-1.5">
                        <div class="flex items-center justify-between text-xs font-bold">
                            <span class="text-slate-700">Akurasi Sistem (1 - MAPE)</span>
                            <span class="text-emerald-700 font-extrabold text-sm">{{ $yieldPrediction['model_accuracy']['rate'] }}</span>
                        </div>
                        <div class="w-full bg-slate-100 h-2.5 rounded-full overflow-hidden">
                            <div class="bg-emerald-600 h-full rounded-full" style="width: {{ $yieldPrediction['model_accuracy']['rate'] }}"></div>
                        </div>
                    </div>

                    <!-- 3 Stat Metrics Grid -->
                    <div class="grid grid-cols-3 gap-2 sm:gap-3 pt-2">
                        <div class="bg-slate-50 border border-slate-200 rounded-xl p-3 text-center">
                            <span class="text-[11px] font-semibold text-slate-500 uppercase block">MAE</span>
                            <span class="text-base sm:text-lg font-extrabold text-slate-900 block mt-0.5">{{ $yieldPrediction['model_accuracy']['mae'] }}</span>
                        </div>
                        <div class="bg-slate-50 border border-slate-200 rounded-xl p-3 text-center">
                            <span class="text-[11px] font-semibold text-slate-500 uppercase block">RMSE</span>
                            <span class="text-base sm:text-lg font-extrabold text-slate-900 block mt-0.5">{{ $yieldPrediction['model_accuracy']['rmse'] }}</span>
                        </div>
                        <div class="bg-slate-50 border border-slate-200 rounded-xl p-3 text-center">
                            <span class="text-[11px] font-semibold text-slate-500 uppercase block">R-Score</span>
                            <span class="text-base sm:text-lg font-extrabold text-emerald-700 block mt-0.5">{{ $yieldPrediction['model_accuracy']['r_score'] }}</span>
                        </div>
                    </div>

                    <p class="text-[11px] text-slate-500 leading-relaxed">
                        {{ $yieldPrediction['model_accuracy']['note'] }}
                    </p>
                </div>

                <!-- Card: Selisih Sangat Minim -->
                <div class="bg-emerald-50/60 border border-emerald-200 rounded-2xl p-4.5 sm:p-5 flex items-start gap-3.5">
                    <div class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <h4 class="text-xs sm:text-sm font-bold text-emerald-950">Selisih Sangat Minim</h4>
                        <p class="text-xs text-emerald-900/80 leading-relaxed mt-1">
                            Grafik menunjukkan kesesuaian antara model data prediksi vs data aktual pada rentang yang sangat kecil (±0.2 Ton) dan menandakan proyeksi model dapat diandalkan.
                        </p>
                    </div>
                </div>

                <!-- Card: Bobot Pengaruh Fitur Sentinel-2 (Feature Importance dari Colab) -->
                <div class="bg-white border border-slate-200/90 rounded-2xl sm:rounded-3xl p-5 sm:p-6 shadow-xs space-y-3.5">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-sm sm:text-base font-bold text-slate-900">Pengaruh Fitur Sentinel-2 (XGBoost)</h3>
                            <span class="text-xs text-slate-500 font-medium">Tingkat kepentingan prediktor hasil panen</span>
                        </div>
                        <span class="text-[10px] font-bold uppercase tracking-wider bg-rose-50 text-brand-800 px-2 py-0.5 rounded border border-rose-200">
                            Feature Importance
                        </span>
                    </div>

                    <div class="space-y-2 pt-1 max-h-[260px] overflow-y-auto pr-1">
                        @foreach($featureImportance as $item)
                            <div class="space-y-1">
                                <div class="flex items-center justify-between text-xs">
                                    <span class="font-bold text-slate-800">
                                        {{ $item['feature'] }} <span class="font-normal text-slate-500">({{ $item['name'] }})</span>
                                    </span>
                                    <span class="font-bold text-brand-800">{{ $item['weight'] }}%</span>
                                </div>
                                <div class="w-full bg-slate-100 h-1.5 rounded-full overflow-hidden">
                                    <div class="bg-brand-800 h-full rounded-full transition-all duration-500" style="width: {{ $item['weight'] * 3.5 }}%"></div>
                                </div>
                                <span class="text-[10px] text-slate-400 block">{{ $item['role'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>

            </div>
        </div>

        <!-- Full Width Report Download Banner Card -->
        <div class="bg-white border border-slate-200/90 rounded-2xl sm:rounded-3xl p-5 sm:p-6 shadow-xs">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-5">
                <!-- Left Description -->
                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-rose-50 border border-rose-100 text-brand-800 flex items-center justify-center shrink-0">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <div class="space-y-1">
                        <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider block">Unduh Hasil Lapangan</span>
                        <h3 class="text-base sm:text-lg font-bold text-slate-900">Laporan Analisis Lengkap</h3>
                        <p class="text-xs text-slate-600 leading-relaxed max-w-2xl">
                            Simpan hasil analisis kondisi tanaman dan prediksi produktivitas sebagai dokumen laporan PDF resmi untuk dinas, mingguan kelompok tani, atau disimpan di HP.
                        </p>
                    </div>
                </div>

                <!-- Right Action Button -->
                <div class="flex flex-col items-start md:items-end gap-1.5 shrink-0">
                    <button type="button" 
                            @click="isReportModalOpen = true"
                            class="inline-flex items-center gap-2.5 px-5 py-3 bg-brand-800 hover:bg-brand-900 active:bg-brand-950 text-white text-xs sm:text-sm font-bold rounded-xl shadow-md hover:shadow-lg transition-all duration-150">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                        </svg>
                        <span>Unduh PDF Laporan</span>
                    </button>
                    <span class="text-[11px] text-slate-400 font-medium">
                        Terakhir diperbarui: Hari ini jam 07.45 WIB
                    </span>
                </div>
            </div>
        </div>

    </div>
</section>
