<section id="beranda" class="pt-6 sm:pt-10 pb-8 sm:pb-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center">
            
            <!-- Left Column: Copywriting & CTA -->
            <div class="lg:col-span-7 flex flex-col items-start space-y-5 sm:space-y-6">
                <!-- Status Badge -->
                <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-rose-100/80 border border-rose-200/90 text-brand-900 text-xs sm:text-sm font-semibold tracking-wide">
                    <span class="w-2 h-2 rounded-full bg-emerald-600 animate-pulse"></span>
                    <span>Musim Tanam Aktif 2026</span>
                </div>

                <!-- Main Headline -->
                <h1 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-slate-900 tracking-tight leading-[1.15]">
                    Pantau Kondisi Lahan <br class="hidden sm:inline">
                    <span class="text-brand-800">Bawang Merah</span> Anda
                </h1>

                <!-- Subtitle / Value Proposition -->
                <p class="text-base sm:text-lg text-slate-600 leading-relaxed max-w-2xl font-normal">
                    Lihat kondisi lahan, analisis tanaman, prediksi produktivitas, dan rekomendasi agronomi dalam satu sistem yang memudahkan petani seluruh kelompok tani.
                </p>

                <!-- CTA Action Buttons -->
                <div class="flex flex-wrap items-center gap-3.5 pt-2">
                    <a href="#peta" 
                       class="inline-flex items-center gap-2.5 px-6 py-3 bg-brand-800 hover:bg-brand-900 active:bg-brand-950 text-white text-sm sm:text-base font-semibold rounded-xl shadow-md hover:shadow-lg transition-all duration-200 transform hover:-translate-y-0.5">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                        </svg>
                        <span>Lihat Peta Lahan</span>
                    </a>

                    <a href="#analisis" 
                       class="inline-flex items-center gap-2.5 px-6 py-3 bg-white hover:bg-rose-50/70 border border-slate-300 hover:border-brand-300 text-slate-800 text-sm sm:text-base font-semibold rounded-xl shadow-xs hover:shadow transition-all duration-200">
                        <svg class="w-5 h-5 text-brand-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                        <span>Mulai Analisis</span>
                    </a>
                </div>

                <!-- Trust Features Under Buttons -->
                <div class="flex flex-wrap items-center gap-5 sm:gap-8 pt-2 text-xs sm:text-sm text-slate-600 font-medium">
                    <div class="flex items-center gap-2">
                        <div class="w-4 h-4 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>
                        <span>Data Satelit Terbaru</span>
                    </div>

                    <div class="flex items-center gap-2">
                        <div class="w-4 h-4 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>
                        <span>Akurasi Model Tinggi</span>
                    </div>
                </div>
            </div>

            <!-- Right Column: Hero Visual Image with Floating Badge -->
            <div class="lg:col-span-5">
                <div class="relative group">
                    <!-- Decorative Soft Ambient Glow -->
                    <div class="absolute -inset-1.5 bg-gradient-to-tr from-brand-700/20 to-emerald-500/20 rounded-3xl blur-lg opacity-70 group-hover:opacity-100 transition duration-300"></div>

                    <!-- Image Frame -->
                    <div class="relative rounded-2xl sm:rounded-3xl overflow-hidden shadow-xl border border-rose-100/80 bg-white">
                        <img src="{{ asset('images/hero-farmer.jpg') }}" 
                             alt="Petani Bawang Merah Indonesia di Lahan Pertanian" 
                             class="w-full h-[320px] sm:h-[380px] lg:h-[400px] object-cover object-center group-hover:scale-102 transition-transform duration-500">

                        <!-- Floating Glassmorphic Location Card -->
                        <div class="absolute bottom-3 left-3 right-3 sm:bottom-4 sm:left-4 sm:right-4 bg-white/95 backdrop-blur-md p-3.5 sm:p-4 rounded-xl sm:rounded-2xl border border-white/80 shadow-lg flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-lg bg-brand-50 text-brand-800 flex items-center justify-center shrink-0">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                </div>
                                <div class="flex flex-col">
                                    <span class="text-xs sm:text-sm font-bold text-slate-900 leading-tight">
                                        Kelompok Tani Sumber Makmur
                                    </span>
                                    <span class="text-[11px] sm:text-xs text-slate-500 font-medium">
                                        Lahan Bawang Merah Pajagan, Jawa Timur
                                    </span>
                                </div>
                            </div>

                            <!-- Right Badge Icon -->
                            <div class="w-7 h-7 rounded-full bg-slate-100 flex items-center justify-center text-slate-600">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>
