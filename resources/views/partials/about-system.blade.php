<section id="tentang" class="py-8 sm:py-12 bg-white/70 border-t border-rose-100/60">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
        
        <!-- Section Header -->
        <div class="max-w-3xl">
            <div class="flex items-center gap-2.5 text-brand-800 font-bold text-xl sm:text-2xl tracking-tight">
                <h2>Tentang Sistem SI Bawang Merah</h2>
            </div>
            <p class="text-slate-600 text-xs sm:text-sm mt-2 leading-relaxed">
                Sistem ini menampilkan peta area kandidat bawang merah dan hasil penelitian Sentinel-2 serta XGBoost di empat kecamatan Nganjuk. Informasi mengikuti periode data penelitian yang tersedia.
            </p>
        </div>

        <!-- 4 Technological Pillar Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
            
            <!-- Pillar 1: Citra Satelit Sentinel-2 -->
            <div class="bg-white rounded-2xl p-5 border border-slate-200/90 shadow-xs hover:shadow-md transition space-y-3">
                <div class="w-11 h-11 rounded-xl bg-rose-50 text-brand-800 border border-rose-100 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <h4 class="text-sm sm:text-base font-bold text-slate-900 leading-snug">
                    Citra Satelit Sentinel-2
                </h4>
                <p class="text-xs text-slate-500 leading-relaxed">
                    Citra Sentinel-2 diolah menjadi fitur spektral, termasuk NDVI dan NDWI. Nilai yang ditampilkan berasal dari hasil pengolahan penelitian yang tersimpan.
                </p>
            </div>

            <!-- Pillar 2: Algoritma XGBoost -->
            <div class="bg-white rounded-2xl p-5 border border-slate-200/90 shadow-xs hover:shadow-md transition space-y-3">
                <div class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-700 border border-emerald-100 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"/>
                    </svg>
                </div>
                <h4 class="text-sm sm:text-base font-bold text-slate-900 leading-snug">
                    Algoritma XGBoost
                </h4>
                <p class="text-xs text-slate-500 leading-relaxed">
                    Model klasifikasi mengenali kandidat bawang merah dari delapan fitur Sentinel-2. Model regresi produktivitas dievaluasi secara terpisah pada data kecamatan-tahun.
                </p>
            </div>

            <!-- Pillar 3: Validasi Tim Lapangan -->
            <div class="bg-white rounded-2xl p-5 border border-slate-200/90 shadow-xs hover:shadow-md transition space-y-3">
                <div class="w-11 h-11 rounded-xl bg-amber-50 text-amber-700 border border-amber-100 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                    </svg>
                </div>
                <h4 class="text-sm sm:text-base font-bold text-slate-900 leading-snug">
                    Evaluasi Model
                </h4>
                <p class="text-xs text-slate-500 leading-relaxed">
                    Validasi klasifikasi dikelompokkan berdasarkan lokasi. Referensi negatif dari Dynamic World merupakan referensi semu; konfirmasi lapangan tetap diperlukan untuk menilai kandidat hasil pemetaan.
                </p>
            </div>

            <!-- Pillar 4: Panduan Agronomi Nyata -->
            <div class="bg-white rounded-2xl p-5 border border-slate-200/90 shadow-xs hover:shadow-md transition space-y-3">
                <div class="w-11 h-11 rounded-xl bg-purple-50 text-purple-700 border border-purple-100 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                </div>
                <h4 class="text-sm sm:text-base font-bold text-slate-900 leading-snug">
                    Batas Penggunaan
                </h4>
                <p class="text-xs text-slate-500 leading-relaxed">
                    Hasil penelitian membantu peninjauan awal wilayah kandidat. Dataset yang tersedia belum menentukan jadwal siram, dosis pupuk, serangan hama, maupun fase tanam per lahan.
                </p>
            </div>

        </div>

    </div>
</section>
