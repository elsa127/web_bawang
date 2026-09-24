<section id="analisis" class="py-8 sm:py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">

        {{-- Section Header --}}
        <div>
            <div class="flex items-center gap-2.5 text-brand-800 font-bold text-xl sm:text-2xl tracking-tight">
                <h2>Analisis Kondisi Lahan &amp; Tanaman</h2>
            </div>
            <p class="text-slate-600 text-xs sm:text-sm mt-1 max-w-2xl">
                Analisis kondisi lahan berdasarkan data citra satelit Sentinel-2, indeks vegetasi, dan kelembapan lingkungan.
            </p>
        </div>

        {{-- 3 Kartu metrik sensor --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">

            {{-- Sensor: NDVI --}}
            <div class="bg-white rounded-2xl p-5 border border-slate-200/90 shadow-xs hover:shadow-md transition space-y-3">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-700">NDVI — Indeks Vegetasi</span>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">
                        {{ $cropSensors['ndvi']['status'] ?? 'Sehat' }}
                    </span>
                </div>
                <div>
                    <span class="text-3xl font-extrabold text-slate-900 tracking-tight">
                        {{ $cropSensors['ndvi']['score'] ?? '—' }}
                    </span>
                    <span class="text-xs font-semibold text-emerald-700 block mt-0.5">
                        {{ $cropSensors['ndvi']['label'] ?? 'Kondisi Vegetasi Baik' }}
                    </span>
                </div>
                <p class="text-xs text-slate-500 leading-relaxed min-h-[40px]">
                    {{ $cropSensors['ndvi']['description'] ?? 'Indeks klorofil daun dari citra Sentinel-2 menunjukkan kondisi vegetasi aktif.' }}
                </p>
                <div class="w-full bg-slate-100 h-2 rounded-full overflow-hidden">
                    <div class="bg-emerald-600 h-full rounded-full transition-all duration-500"
                         style="width: {{ $cropSensors['ndvi']['percent'] ?? 0 }}%"></div>
                </div>
            </div>

            {{-- Sensor: Pertumbuhan Tanaman --}}
            <div class="bg-white rounded-2xl p-5 border border-slate-200/90 shadow-xs hover:shadow-md transition space-y-3">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-700">Pertumbuhan Tanaman</span>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-brand-50 text-brand-800">
                        {{ $cropSensors['growth']['status'] ?? 'Fase Vegetatif' }}
                    </span>
                </div>
                <div>
                    <span class="text-3xl font-extrabold text-slate-900 tracking-tight">
                        {{ $cropSensors['growth']['score'] ?? 'Vegetatif' }}
                    </span>
                    <span class="text-xs font-semibold text-brand-800 block mt-0.5">
                        {{ $cropSensors['growth']['label'] ?? 'Estimasi Musim Tanam 2026' }}
                    </span>
                </div>
                <p class="text-xs text-slate-500 leading-relaxed min-h-[40px]">
                    {{ $cropSensors['growth']['description'] ?? 'Tanaman bawang merah diperkirakan berada pada fase vegetatif berdasarkan kalender musim tanam 2026 Kabupaten Nganjuk.' }}
                </p>
                <div class="w-full bg-slate-100 h-2 rounded-full overflow-hidden">
                    <div class="bg-brand-800 h-full rounded-full transition-all duration-500"
                         style="width: {{ $cropSensors['growth']['percent'] ?? 60 }}%"></div>
                </div>
            </div>

            {{-- Sensor: Kelembapan NDWI --}}
            <div class="bg-white rounded-2xl p-5 border border-slate-200/90 shadow-xs hover:shadow-md transition space-y-3">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-700">Kelembapan — NDWI</span>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-800">
                        {{ $cropSensors['moisture']['status'] ?? 'Optimal' }}
                    </span>
                </div>
                <div>
                    <span class="text-3xl font-extrabold text-slate-900 tracking-tight">
                        {{ $cropSensors['moisture']['score'] ?? '—' }}
                    </span>
                    <span class="text-xs font-semibold text-amber-700 block mt-0.5">
                        {{ $cropSensors['moisture']['label'] ?? 'Kadar Air SWIR-2 Cukup' }}
                    </span>
                </div>
                <p class="text-xs text-slate-500 leading-relaxed min-h-[40px]">
                    {{ $cropSensors['moisture']['description'] ?? 'Kelembapan kanopi tanaman dari ekstraksi band SWIR-2 citra Sentinel-2.' }}
                </p>
                <div class="w-full bg-slate-100 h-2 rounded-full overflow-hidden">
                    <div class="bg-amber-500 h-full rounded-full transition-all duration-500"
                         style="width: {{ $cropSensors['moisture']['percent'] ?? 0 }}%"></div>
                </div>
            </div>

        </div>

        <!-- Grafik Tren Mingguan NDVI -->
        <div class="bg-white rounded-2xl sm:rounded-3xl p-5 sm:p-6 border border-slate-200/90 shadow-xs space-y-5">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                <div>
                    <h3 class="text-sm sm:text-base font-bold text-slate-900">Grafik Tren Kesehatan Daun Mingguan</h3>
                    <span class="text-xs text-slate-500 font-medium">Tren NDVI bawang merah Sukomoro per minggu musim tanam</span>
                </div>
                <div class="flex items-center gap-3 text-xs font-semibold text-slate-700">
                    <div class="flex items-center gap-1.5">
                        <span class="w-3 h-3 rounded-sm bg-brand-800"></span>
                        <span>Puncak Optimal</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <span class="w-3 h-3 rounded-sm bg-emerald-500"></span>
                        <span>Tren Normal</span>
                    </div>
                </div>
            </div>
            <div class="pt-4 pb-2 border-t border-slate-100">
                @php
                    // Estimasi tren NDVI per minggu berdasarkan pola pertumbuhan bawang merah
                    // (Vegetatif meningkat pesat Mgg 3-4, stabil Mgg 5-6)
                    $trends = [
                        ['week' => 'Mgg 1', 'value' => 0.32, 'pct' => 32, 'peak' => false],
                        ['week' => 'Mgg 2', 'value' => 0.44, 'pct' => 44, 'peak' => false],
                        ['week' => 'Mgg 3', 'value' => 0.58, 'pct' => 58, 'peak' => false],
                        ['week' => 'Mgg 4', 'value' => $cropSensors['ndvi']['score'] ?? 0.50, 'pct' => ($cropSensors['ndvi']['percent'] ?? 50), 'peak' => true],
                        ['week' => 'Mgg 5', 'value' => 0.46, 'pct' => 46, 'peak' => false],
                        ['week' => 'Mgg 6', 'value' => 0.41, 'pct' => 41, 'peak' => false],
                    ];
                @endphp
                <div class="grid grid-cols-6 gap-2 sm:gap-6 items-end h-44 sm:h-52 px-2 sm:px-6">
                    @foreach($trends as $t)
                        <div class="flex flex-col items-center gap-2 group">
                            <div class="text-[11px] sm:text-xs font-bold transition group-hover:scale-110">
                                @if($t['peak'])
                                    <span class="px-1.5 py-0.5 rounded-sm bg-brand-800 text-white text-[10px] uppercase font-bold shadow-xs">
                                        {{ is_numeric($t['value']) ? number_format((float)$t['value'], 2) : $t['value'] }}
                                    </span>
                                @else
                                    <span class="text-slate-600">{{ is_numeric($t['value']) ? number_format((float)$t['value'], 2) : $t['value'] }}</span>
                                @endif
                            </div>
                            <div class="w-8 sm:w-12 bg-slate-100 rounded-t-md relative overflow-hidden transition-all duration-300 group-hover:opacity-90"
                                 style="height: {{ (float)$t['pct'] * 1.6 }}px;">
                                <div class="w-full h-full rounded-t-md {{ $t['peak'] ? 'bg-brand-800' : 'bg-emerald-500' }}"></div>
                            </div>
                            <span class="text-xs font-semibold text-slate-700">{{ $t['week'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Dua Insight Card -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            @foreach($insights as $insight)
                <div class="bg-white rounded-2xl p-5 border border-slate-200/90 shadow-xs flex items-start gap-4">
                    <div class="w-10 h-10 rounded-xl {{ $insight['color'] === 'emerald' ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-600' }} flex items-center justify-center shrink-0">
                        @if($insight['color'] === 'emerald')
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        @else
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                        @endif
                    </div>
                    <div class="space-y-1">
                        <span class="text-xs font-semibold text-slate-500 block">{{ $insight['number'] }}. {{ $insight['title'] }}</span>
                        <h4 class="text-sm sm:text-base font-bold text-slate-900">{{ $insight['highlight'] ?? '—' }}</h4>
                        <p class="text-xs text-slate-600 leading-relaxed">{{ $insight['description'] }}</p>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Siklus Pertumbuhan (Stepper) -->
        <div class="bg-white rounded-2xl sm:rounded-3xl p-5 sm:p-6 border border-slate-200/90 shadow-xs space-y-5">
            <div>
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider block">Siklus Pertumbuhan Saat Ini</span>
                <h3 class="text-base sm:text-lg font-bold text-slate-900 mt-0.5">
                    {{ $growthLifecycle['subtitle'] ?? 'Fase Vegetatif — Musim Tanam 2026' }}
                </h3>
                <p class="text-xs text-slate-600 mt-1">
                    {{ $growthLifecycle['description'] ?? 'Tanaman bawang merah sedang aktif membentuk anakan dan memperkuat daun menuju fase pembentukan umbi.' }}
                </p>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 pt-1">
                @foreach($growthLifecycle['stages'] as $stage)
                    <div class="rounded-xl p-4 border transition-all duration-200
                        {{ $stage['status'] === 'active'    ? 'bg-brand-800 text-white border-brand-800 shadow-md ring-2 ring-brand-800/20'
                        : ($stage['status'] === 'completed' ? 'bg-emerald-50/70 border-emerald-200 text-slate-800'
                        : 'bg-slate-50/70 border-slate-200 text-slate-400') }}">
                        <div class="flex items-center justify-between mb-2">
                            <span class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold
                                {{ $stage['status'] === 'active'    ? 'bg-white text-brand-800'
                                : ($stage['status'] === 'completed' ? 'bg-emerald-600 text-white'
                                : 'bg-slate-200 text-slate-500') }}">
                                {{ $stage['status'] === 'completed' ? '✓' : $stage['number'] }}
                            </span>
                            <span class="text-[10px] font-semibold
                                {{ $stage['status'] === 'active'    ? 'text-rose-200 uppercase tracking-wide'
                                : ($stage['status'] === 'completed' ? 'text-emerald-700'
                                : 'text-slate-400') }}">
                                {{ $stage['status'] === 'active' ? 'Aktif' : ($stage['status'] === 'completed' ? 'Selesai' : 'Menunggu') }}
                            </span>
                        </div>
                        <h4 class="text-sm font-bold leading-snug {{ $stage['status'] === 'active' ? 'text-white' : 'text-slate-900' }}">
                            {{ $stage['name'] }}
                        </h4>
                        <span class="text-xs font-medium block mt-0.5 {{ $stage['status'] === 'active' ? 'text-rose-100' : 'text-slate-500' }}">
                            {{ $stage['period'] }}
                        </span>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Rekomendasi Lapangan -->
        <div class="bg-white rounded-2xl sm:rounded-3xl p-5 sm:p-6 border border-slate-200/90 shadow-xs space-y-4">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-brand-800" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                </svg>
                <h3 class="text-base sm:text-lg font-bold text-slate-900">Rekomendasi Tindakan Lapangan</h3>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-emerald-50/40 border-l-4 border-emerald-500 rounded-r-xl p-4 border-y border-r border-slate-200/70 space-y-2">
                    <div class="flex items-center gap-2 text-emerald-800 font-bold text-sm">
                        <h4>Penyiraman Rutin</h4>
                    </div>
                    <p class="text-xs text-slate-600 leading-relaxed">
                        Pertahankan kelembapan tanah 60–70%. Siram pagi hari sebelum jam 08.00 WIB, sesuai nilai NDWI
                        {{ isset($cropSensors['moisture']['score']) ? '(' . $cropSensors['moisture']['score'] . ')' : '' }}
                        dari citra Sentinel-2.
                    </p>
                </div>
                <div class="bg-rose-50/40 border-l-4 border-rose-500 rounded-r-xl p-4 border-y border-r border-slate-200/70 space-y-2">
                    <div class="flex items-center gap-2 text-rose-800 font-bold text-sm">
                        <h4>Waspada Daun</h4>
                    </div>
                    <p class="text-xs text-slate-600 leading-relaxed">
                        Periksa bercak ungu (Alternaria porri) secara berkala. Nilai NDVI
                        {{ isset($cropSensors['ndvi']['score']) ? $cropSensors['ndvi']['score'] : '—' }}
                        menunjukkan kondisi daun saat ini.
                    </p>
                </div>
                <div class="bg-amber-50/40 border-l-4 border-amber-500 rounded-r-xl p-4 border-y border-r border-slate-200/70 space-y-2">
                    <div class="flex items-center gap-2 text-amber-800 font-bold text-sm">
                        <h4>Perubahan Cuaca</h4>
                    </div>
                    <p class="text-xs text-slate-600 leading-relaxed">
                        Pastikan saluran drainase tidak mampet menjelang musim hujan agar umbi tidak tergenang. Pantau reflektansi SWIR-2 secara berkala.
                    </p>
                </div>
            </div>
        </div>

    </div>
</section>
