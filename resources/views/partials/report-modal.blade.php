<!-- Report Preview & Print Modal -->
<div x-show="isReportModalOpen" 
     x-cloak
     class="fixed inset-0 z-50 overflow-y-auto no-print"
     aria-labelledby="modal-title" role="dialog" aria-modal="true">
    
    <!-- Backdrop overlay -->
    <div x-show="isReportModalOpen"
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click="isReportModalOpen = false"
         class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity"></div>

    <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
        <div x-show="isReportModalOpen"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-2xl border border-slate-200">
            
            <!-- Modal Header -->
            <div class="bg-brand-800 text-white px-6 py-4 flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <svg class="w-5 h-5 text-rose-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <h3 class="text-base font-bold" id="modal-title">Dokumen Laporan Lapangan Resmi</h3>
                </div>
                <button type="button" 
                        @click="isReportModalOpen = false"
                        class="text-rose-200 hover:text-white rounded-lg p-1 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <!-- Modal Body (Printable Report Preview) -->
            <div class="p-6 space-y-5 text-slate-800 text-xs sm:text-sm">
                
                <!-- Report Header Info -->
                <div class="flex items-start justify-between border-b border-slate-200 pb-4">
                    <div>
                        <h4 class="font-extrabold text-base text-slate-900">SI BAWANG MERAH - LEMBAR ANALISIS</h4>
                        <span class="text-xs text-slate-500 font-medium block">Wilayah: {{ $selectedDistrict['name'] }}, Jawa Timur</span>
                        <span class="text-xs text-slate-500 font-medium block">Tanggal: {{ date('d F Y') }} | Pukul: 07.45 WIB</span>
                    </div>
                    <div class="text-right">
                        <span class="inline-block px-2.5 py-1 rounded bg-emerald-100 text-emerald-800 font-bold text-xs">
                            STATUS: {{ strtoupper($selectedDistrict['condition_badge']) }}
                        </span>
                        <span class="text-[11px] text-slate-400 block mt-1">Ref: SBM-{{ date('Y') }}-XGB-092</span>
                    </div>
                </div>

                <!-- Report Summary Table -->
                <div class="border border-slate-200 rounded-xl overflow-hidden">
                    <table class="min-w-full divide-y divide-slate-200 text-left">
                        <thead class="bg-slate-50 text-slate-700 font-bold text-xs uppercase">
                            <tr>
                                <th class="px-4 py-2.5">Parameter Pantau</th>
                                <th class="px-4 py-2.5">Nilai Sensor</th>
                                <th class="px-4 py-2.5">Interpretasi AI</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 text-xs">
                            <tr>
                                <td class="px-4 py-2.5 font-medium">Indeks Klorofil (NDVI)</td>
                                <td class="px-4 py-2.5 font-bold text-emerald-700">{{ $cropSensors['ndvi']['score'] }}</td>
                                <td class="px-4 py-2.5 text-slate-600">{{ $cropSensors['ndvi']['label'] }}</td>
                            </tr>
                            <tr class="bg-slate-50/50">
                                <td class="px-4 py-2.5 font-medium">Kelembapan Tanah Rata-rata</td>
                                <td class="px-4 py-2.5 font-bold text-amber-700">{{ $cropSensors['moisture']['score'] }}</td>
                                <td class="px-4 py-2.5 text-slate-600">{{ $cropSensors['moisture']['label'] }}</td>
                            </tr>
                            <tr>
                                <td class="px-4 py-2.5 font-medium">Fase Pertumbuhan Tanaman</td>
                                <td class="px-4 py-2.5 font-bold text-brand-800">{{ $growthLifecycle['subtitle'] }}</td>
                                <td class="px-4 py-2.5 text-slate-600">{{ $growthLifecycle['description'] }}</td>
                            </tr>
                            <tr class="bg-slate-50/50">
                                <td class="px-4 py-2.5 font-medium">Estimasi Produktivitas Panen</td>
                                <td class="px-4 py-2.5 font-bold text-rose-600">{{ $yieldPrediction['projected_yield'] }} {{ $yieldPrediction['unit'] }}</td>
                                <td class="px-4 py-2.5 text-slate-600">{{ $yieldPrediction['accuracy_badge'] }}</td>
                            </tr>
                            <tr>
                                <td class="px-4 py-2.5 font-medium">MAE Model XGBoost</td>
                                <td class="px-4 py-2.5 font-bold text-slate-700">{{ $yieldPrediction['model_accuracy']['mae'] }}</td>
                                <td class="px-4 py-2.5 text-slate-600">Mean Absolute Error (Temporal CV)</td>
                            </tr>
                            <tr class="bg-slate-50/50">
                                <td class="px-4 py-2.5 font-medium">RMSE Model XGBoost</td>
                                <td class="px-4 py-2.5 font-bold text-slate-700">{{ $yieldPrediction['model_accuracy']['rmse'] }}</td>
                                <td class="px-4 py-2.5 text-slate-600">Root Mean Square Error (Temporal CV)</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Action Notes in Report -->
                <div class="bg-rose-50/60 border border-rose-200 rounded-xl p-3.5 space-y-1">
                    <span class="font-bold text-brand-900 text-xs block">Catatan Rekomendasi Petani:</span>
                    <p class="text-xs text-slate-600 leading-relaxed">
                        {{ $recommendations[0]['description'] }}
                    </p>
                </div>

            </div>

            <!-- Modal Footer -->
            <div class="bg-slate-50 px-6 py-4 flex flex-col sm:flex-row items-center justify-end gap-3 border-t border-slate-200">
                <button type="button" 
                        @click="isReportModalOpen = false"
                        class="w-full sm:w-auto px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-200 rounded-lg transition">
                    Tutup Pratinjau
                </button>
                <button type="button" 
                        @click="window.print()"
                        class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-brand-800 hover:bg-brand-900 text-white text-xs font-bold rounded-lg shadow-sm transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                    </svg>
                    <span>Cetak / Unduh PDF</span>
                </button>
            </div>

        </div>
    </div>
</div>
