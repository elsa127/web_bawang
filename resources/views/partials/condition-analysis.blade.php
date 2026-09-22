<section id="analisis" class="py-8 sm:py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
        
        <!-- Section Header -->
        <div>
            <div class="flex items-center gap-2.5 text-brand-800 font-bold text-xl sm:text-2xl tracking-tight">
                <span class="text-2xl">🌱</span>
                <h2>Analisis Kondisi Lahan & Tanaman</h2>
            </div>
            <p class="text-slate-600 text-xs sm:text-sm mt-1 max-w-2xl">
                Analisis kondisi lahan berdasarkan data sensor satelit, pengamatan tanaman, dan kelembapan lingkungan.
            </p>
        </div>

        <!-- 3 Sensor Metric Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
            
            <!-- Sensor 1: NDVI — ✅ REAL dari CSV 2025 -->
            <div class="bg-white rounded-2xl p-5 border {{ $cropSensors['ndvi']['score'] ? 'border-slate-200/90' : 'border-amber-200' }} shadow-xs hover:shadow-md transition space-y-3">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-1.5 text-xs font-bold text-slate-700">
                        <span>NDVI</span>
                    </div>
                    @if($cropSensors['ndvi']['score'])
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">
                            ✅ {{ $cropSensors['ndvi']['status'] }}
                        </span>
                    @else
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-700">⚠️ DUMMY</span>
                    @endif
                </div>
                <div>
                    @if($cropSensors['ndvi']['score'])
                        <span class="text-3xl font-extrabold text-slate-900 tracking-tight">{{ $cropSensors['ndvi']['score'] }}</span>
                        <span class="text-xs font-semibold text-emerald-700 block mt-0.5">{{ $cropSensors['ndvi']['label'] }}</span>
                    @else
                        <span class="text-lg font-bold text-amber-500">Belum Tersambung</span>
                        <span class="text-xs text-slate-400 block mt-0.5">Perlu ekstraksi Sentinel-2</span>
                    @endif
                </div>
                <p class="text-xs text-slate-500 leading-relaxed min-h-[40px]">
                    {{ $cropSensors['ndvi']['description'] ?? 'Data NDVI belum tersedia.' }}
                </p>
                <div class="w-full bg-slate-100 h-2 rounded-full overflow-hidden">
                    <div class="bg-emerald-600 h-full rounded-full transition-all duration-500" style="width: {{ $cropSensors['ndvi']['percent'] ?? 0 }}%"></div>
                </div>
            </div>

            <!-- Sensor 2: Pertumbuhan — ⚠️ DUMMY -->
            <div class="bg-white rounded-2xl p-5 border border-amber-200 shadow-xs hover:shadow-md transition space-y-3">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-1.5 text-xs font-bold text-slate-700">
                        <span>Pertumbuhan Tanaman</span>
                    </div>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-700">⚠️ DUMMY</span>
                </div>
                <div>
                    <span class="text-lg font-bold text-amber-500">Belum Tersambung</span>
                    <span class="text-xs text-slate-400 block mt-0.5">Perlu data HST dari lapangan</span>
                </div>
                <p class="text-xs text-slate-400 leading-relaxed min-h-[40px]">
                    Data pertumbuhan fisik tanaman (tinggi, jumlah anakan, HST) belum tersedia. Perlu input lapangan atau sensor IoT.
                </p>
                <div class="w-full bg-slate-100 h-2 rounded-full overflow-hidden">
                    <div class="bg-amber-300 h-full rounded-full" style="width: 0%"></div>
                </div>
            </div>

            <!-- Sensor 3: Kelembapan — ✅ REAL dari NDWI CSV 2025 -->
            <div class="bg-white rounded-2xl p-5 border {{ $cropSensors['moisture']['score'] ? 'border-slate-200/90' : 'border-amber-200' }} shadow-xs hover:shadow-md transition space-y-3">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-1.5 text-xs font-bold text-slate-700">
                        <span>Kelembapan (NDWI)</span>
                    </div>
                    @if($cropSensors['moisture']['score'])
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">
                            ✅ {{ $cropSensors['moisture']['status'] }}
                        </span>
                    @else
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-700">⚠️ DUMMY</span>
                    @endif
                </div>
                <div>
                    @if($cropSensors['moisture']['score'])
                        <span class="text-3xl font-extrabold text-slate-900 tracking-tight">{{ $cropSensors['moisture']['score'] }}</span>
                        <span class="text-xs font-semibold text-amber-700 block mt-0.5">{{ $cropSensors['moisture']['label'] }}</span>
                    @else
                        <span class="text-lg font-bold text-amber-500">Belum Tersambung</span>
                        <span class="text-xs text-slate-400 block mt-0.5">Perlu ekstraksi NDWI Sentinel-2</span>
                    @endif
                </div>
                <p class="text-xs text-slate-500 leading-relaxed min-h-[40px]">
                    {{ $cropSensors['moisture']['description'] ?? 'Data kelembapan belum tersedia.' }}
                </p>
                <div class="w-full bg-slate-100 h-2 rounded-full overflow-hidden">
                    <div class="bg-amber-500 h-full rounded-full transition-all duration-500" style="width: {{ $cropSensors['moisture']['percent'] ?? 0 }}%"></div>
                </div>
            </div>

        </div>

        <!-- Weekly Health Bar Chart -->
        <div class="bg-white rounded-2xl sm:rounded-3xl p-5 sm:p-6 border border-amber-200 shadow-xs space-y-5">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                <div>
                    <div class="flex items-center gap-2">
                        <h3 class="text-sm sm:text-base font-bold text-slate-900">Grafik Tren Kesehatan Daun Mingguan</h3>
                        <span class="text-[9px] font-bold px-1.5 py-0.5 rounded bg-amber-100 text-amber-700 border border-amber-200">⚠️ DUMMY</span>
                    </div>
                    <span class="text-xs text-slate-400 font-medium">Perlu ekstraksi multi-temporal Sentinel-2 per minggu tanam</span>
                </div>
            </div>
            <div class="pt-4 pb-2 border-t border-slate-100">
                <div class="flex items-center justify-center h-32 rounded-xl bg-amber-50/60 border border-dashed border-amber-300">
                    <div class="text-center">
                        <div class="text-2xl mb-1">📡</div>
                        <p class="text-xs font-semibold text-amber-700">Data time-series mingguan belum tersedia</p>
                        <p class="text-xs text-slate-400 mt-0.5">Sambungkan dengan Google Earth Engine untuk data NDVI per minggu</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Two Insight Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <!-- Insight 1: Satelit -->
            <div class="bg-white rounded-2xl p-5 border border-slate-200/90 shadow-xs flex items-start gap-4">
                <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-700 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="space-y-1">
                    <span class="text-xs font-semibold text-slate-500 block">1. Hasil Interpretasi Satelit</span>
                    <h4 class="text-sm sm:text-base font-bold text-slate-900">Nilai: 0.72 (Vegetasi Baik)</h4>
                    <p class="text-xs text-slate-600 leading-relaxed">
                        Dedaunan bawang merah memiliki tingkat serapan klorofil optimal, menandakan fotosintesis berjalan maksimal tanpa hambatan defisiensi hara.
                    </p>
                </div>
            </div>

            <!-- Insight 2: Hama & Jamur -->
            <div class="bg-white rounded-2xl p-5 border border-slate-200/90 shadow-xs flex items-start gap-4">
                <div class="w-10 h-10 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                <div class="space-y-1">
                    <span class="text-xs font-semibold text-slate-500 block">2. Analisis Potensi Jamur & Hama</span>
                    <h4 class="text-sm sm:text-base font-bold text-slate-900">Kondisi Lahan: Baik (Kepercayaan 94%)</h4>
                    <p class="text-xs text-slate-600 leading-relaxed">
                        Model algoritma menganalisis pengukuran kelembaban (68%), curah hujan ringan, dan tekstur tanah lempung berpasir berada dalam kategori sangat sehat.
                    </p>
                </div>
            </div>
        </div>

        <!-- Siklus Pertumbuhan Tanaman (Stepper) -->
        <div class="bg-white rounded-2xl sm:rounded-3xl p-5 sm:p-6 border border-amber-200 shadow-xs space-y-5">
            <div>
                <div class="flex items-center gap-2">
                    <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Siklus Pertumbuhan Saat Ini</span>
                    <span class="text-[9px] font-bold px-1.5 py-0.5 rounded bg-amber-100 text-amber-700 border border-amber-200">⚠️ DUMMY</span>
                </div>
                <h3 class="text-base sm:text-lg font-bold text-slate-900 mt-0.5">
                    {{ $growthLifecycle['subtitle'] ?? 'Fase Pertumbuhan Belum Tersambung' }}
                </h3>
                <p class="text-xs text-slate-400 mt-1">
                    {{ $growthLifecycle['description'] ?? 'Perlu data tanggal tanam aktual dari lapangan untuk menentukan fase HST saat ini.' }}
                </p>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 pt-1">
                @foreach($growthLifecycle['stages'] as $stage)
                    <div class="rounded-xl p-4 border transition-all duration-200 {{ $stage['status'] === 'active' ? 'bg-amber-50 border-amber-300 ring-1 ring-amber-200' : ($stage['status'] === 'completed' ? 'bg-slate-50 border-slate-200' : 'bg-slate-50/70 border-slate-200 text-slate-400') }}">
                        <div class="flex items-center justify-between mb-2">
                            <span class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold {{ $stage['status'] === 'active' ? 'bg-amber-400 text-white' : ($stage['status'] === 'completed' ? 'bg-slate-400 text-white' : 'bg-slate-200 text-slate-400') }}">
                                {{ $stage['status'] === 'completed' ? '✓' : $stage['number'] }}
                            </span>
                            <span class="text-[10px] font-semibold {{ $stage['status'] === 'active' ? 'text-amber-600' : 'text-slate-400' }}">
                                {{ $stage['status'] === 'active' ? '⚠️ Estimasi' : ($stage['status'] === 'completed' ? 'Selesai' : 'Menunggu') }}
                            </span>
                        </div>
                        <h4 class="text-sm font-bold leading-snug text-slate-800">{{ $stage['name'] }}</h4>
                        <span class="text-xs font-medium block mt-0.5 text-slate-500">{{ $stage['period'] }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Rekomendasi Tindakan -->
        <div class="bg-white rounded-2xl sm:rounded-3xl p-5 sm:p-6 border border-amber-200 shadow-xs space-y-4">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                </svg>
                <h3 class="text-base sm:text-lg font-bold text-slate-900">Rekomendasi Tindakan Lapangan</h3>
                <span class="text-[9px] font-bold px-1.5 py-0.5 rounded bg-amber-100 text-amber-700 border border-amber-200">⚠️ DUMMY</span>
            </div>
            <div class="flex items-center justify-center h-24 rounded-xl bg-amber-50/60 border border-dashed border-amber-300">
                <div class="text-center px-4">
                    <p class="text-xs font-semibold text-amber-700">Rekomendasi belum tersambung ke sensor real-time</p>
                    <p class="text-xs text-slate-400 mt-0.5">Perlu rules engine berbasis data NDVI, NDWI, dan cuaca aktual</p>
                </div>
            </div>
        </div>

    </div>
</section>
