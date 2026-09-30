{{-- ============================================================
    LEAFLET CSS
    ============================================================ --}}
<link
    rel="stylesheet"
    href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
/>

<style>
    [x-cloak] {
        display: none !important;
    }

    #leaflet-map {
        width: 100%;
        height: clamp(360px, 65vh, 620px);
        min-height: 360px;
        background: #f8fafc;
    }

    .leaflet-container {
        font-family: inherit;
        z-index: 1;
    }

    .custom-popup .leaflet-popup-content-wrapper {
        border-radius: 16px;
        padding: 0;
        overflow: hidden;
    }

    .custom-popup .leaflet-popup-content {
        margin: 0;
        width: auto !important;
    }
</style>


{{-- ============================================================
    MAP SECTION
    ============================================================ --}}
<section
    id="peta"
    class="py-8 sm:py-10"
    x-data="mapNganjuk()"
    @district-requested.window="updateSelectedData($event.detail)"
    x-init="startMap()"
>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- ====================================================
            HEADER
            ==================================================== --}}
        <div class="mb-6">

            <div class="flex flex-col sm:flex-row
                        sm:items-end sm:justify-between
                        gap-4">

                <div>

                    <p class="text-base font-semibold text-brand-600 mb-1">
                        Analisis Spasial
                    </p>

                    <h2 class="text-2xl sm:text-3xl
                               font-bold text-slate-800">
                        🗺️ Peta Kandidat Bawang Merah
                    </h2>

                    <p class="text-base text-slate-500 mt-2 max-w-2xl">
                        Jelajahi hasil model di Bagor, Gondang, Rejoso, dan Sukomoro. Warna peta menunjukkan perkiraan model, bukan kepastian lahan bawang merah.
                    </p>

                    <p class="mt-3 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm leading-relaxed text-amber-900">
                        <strong>Hasil penelitian.</strong> Kandidat belum memastikan keberadaan bawang merah. Permukiman masih dapat masuk pada hasil model awal; DOA bukan penyaring permukiman.
                    </p>

                </div>

            </div>

        </div>


        {{-- ====================================================
            LAYOUT
            ==================================================== --}}
        <div class="grid grid-cols-1
                    lg:grid-cols-[340px_minmax(0,1fr)] items-start
                    gap-5">


            {{-- =================================================
                KIRI : PETA
                ================================================= --}}
            <div class="order-1 lg:order-2 min-w-0 lg:sticky lg:top-24 bg-white
                        rounded-3xl
                        border border-slate-200
                        shadow-sm
                        overflow-hidden">


                {{-- =================================================
                    HEADER WILAYAH
                    ================================================= --}}
                <div class="px-4 sm:px-5 py-4
                            border-b border-slate-100
                            flex flex-col sm:flex-row
                            sm:items-center
                            sm:justify-between
                            gap-3">

                    <div>

                        <p class="text-sm text-slate-400">
                            Wilayah Terpilih
                        </p>

                        <h3
                            class="text-base font-bold text-slate-700"
                            x-text="selectedData.name"
                        >
                        </h3>

                    </div>


                    <div class="flex flex-wrap items-center gap-2">
                        <a href="#map-filters" class="lg:hidden rounded-lg border border-slate-200 px-3 py-2 text-sm font-semibold text-brand-800">Filter peta</a>
                        <div class="inline-flex rounded-xl border border-slate-200 bg-slate-50 p-1" role="group" aria-label="Pilih peta dasar">
                            <button type="button" @click="if (mapBaseLayer !== 'satellite') toggleBaseLayer()"
                                    :aria-pressed="mapBaseLayer === 'satellite'"
                                    :class="mapBaseLayer === 'satellite' ? 'bg-white text-brand-800 shadow-sm' : 'text-slate-500 hover:text-slate-800'"
                                    class="rounded-lg px-3 py-2 text-sm font-semibold focus-visible:outline focus-visible:outline-2 focus-visible:outline-brand-800">
                                {{ ($mapManifest['satellite_available'] ?? false) ? 'Sentinel-2' : 'Satelit Esri' }}
                            </button>
                            <button type="button" @click="if (mapBaseLayer !== 'street') toggleBaseLayer()"
                                    :aria-pressed="mapBaseLayer === 'street'"
                                    :class="mapBaseLayer === 'street' ? 'bg-white text-brand-800 shadow-sm' : 'text-slate-500 hover:text-slate-800'"
                                    class="rounded-lg px-3 py-2 text-sm font-semibold focus-visible:outline focus-visible:outline-2 focus-visible:outline-brand-800">Peta jalan</button>
                        </div>
                    </div>

                </div>


                {{-- =================================================
                    MAP CONTAINER
                    ================================================= --}}
                <div class="relative">

                    <div id="leaflet-map"></div>


                    {{-- LOADING --}}
                    <div
                        x-show="loading"
                        x-cloak
                        class="absolute inset-0
                               z-[500]
                               bg-white/80
                               backdrop-blur-sm
                               flex items-center
                               justify-center"
                    >

                        <div class="text-center">

                            <div
                                class="w-10 h-10
                                       mx-auto mb-3
                                       rounded-full
                                       border-4
                                       border-slate-200
                                       border-t-emerald-500
                                       animate-spin"
                            >
                            </div>

                            <p class="text-base
                                      font-semibold
                                      text-slate-600">
                                Memuat peta...
                            </p>

                        </div>

                    </div>


                    {{-- ERROR --}}
                    <div
                        x-show="mapError"
                        x-cloak
                        class="absolute inset-0
                               z-[600]
                               bg-white/95
                               flex items-center
                               justify-center
                               p-6"
                    >

                        <div class="text-center max-w-md">

                            <div class="text-4xl mb-3">
                                ⚠️
                            </div>

                            <p class="text-base
                                      font-bold
                                      text-slate-700
                                      mb-1">
                                Peta tidak dapat dimuat
                            </p>

                            <p
                                class="text-sm
                                       text-slate-500"
                                x-text="mapError"
                            >
                            </p>

                        </div>

                    </div>


                </div>
                <div class="flex flex-wrap items-center gap-x-4 gap-y-2 border-t border-slate-100 px-4 py-3 text-sm text-slate-600" aria-label="Legenda peta">
                    <span class="font-semibold text-slate-800">Legenda</span>
                    <template x-if="isMapLayerActive('probability')">
                        <span class="flex flex-wrap gap-x-3 gap-y-2">
                            <span><i class="inline-block h-2.5 w-2.5 rounded-sm bg-red-500"></i> Rendah &lt;0,35</span>
                            <span><i class="inline-block h-2.5 w-2.5 rounded-sm bg-amber-500"></i> Sedang 0,35 sampai &lt;0,70</span>
                            <span><i class="inline-block h-2.5 w-2.5 rounded-sm bg-green-600"></i> Tinggi &ge;0,70</span>
                        </span>
                    </template>
                    <span x-show="isMapLayerActive('candidate')" x-cloak><i class="inline-block h-2.5 w-2.5 rounded-sm bg-purple-500"></i> Kandidat awal</span>
                    <span x-show="isMapLayerActive('candidate_doa') || isMapLayerActive('candidate_landcover')" x-cloak><i class="inline-block h-2.5 w-2.5 rounded-sm bg-cyan-600"></i> Kandidat tersaring</span>
                    <span x-show="activeMapLayers.some(id => id !== 'boundary')" x-cloak><i class="inline-block h-2.5 w-2.5 rounded-sm bg-slate-500"></i> Data tidak tersedia</span>
                    <span x-show="!activeMapLayers.some(id => id !== 'boundary')">Peta dasar tanpa hasil model</span>
                </div>

                {{-- =================================================
                    SUMBER
                    ================================================= --}}
                <div
                    class="px-4 sm:px-5 py-3
                           border-t border-slate-100
                           bg-slate-50"
                >

                    <p class="text-sm text-slate-400">

                        Sumber batas wilayah:

                        <span class="font-semibold
                                     text-slate-500">
                            Batas_4_Kecamatan_Nganjuk.geojson
                        </span>

                    </p>

                    <p class="text-sm
                              text-slate-400
                              mt-0.5">
                        @if($mapManifest['satellite_available'] ?? false)
                            Citra Sentinel-2 hasil penyaringan awan per pengamatan. Bagian tanpa citra bersih memperlihatkan peta jalan. Masih mungkin ada sisa awan yang perlu diperiksa.
                        @else
                            Basemap satelit berasal dari Esri dan dapat mengandung awan. Basemap ini bukan citra Sentinel-2 hasil pengolahan Colab.
                        @endif
                    </p>

                </div>

            </div>


            {{-- =================================================
                KANAN : DETAIL
                ================================================= --}}
            <div class="order-2 lg:order-1 min-w-0 space-y-4">

                {{-- Radio native tetap dapat dipilih dengan keyboard; hanya satu hasil raster aktif. --}}
                <div id="map-filters" class="scroll-mt-24 rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
                    <div class="flex items-center justify-between gap-3 border-b border-slate-100 px-5 py-4">
                        <div>
                            <h3 class="text-base font-bold text-slate-900">Filter peta</h3>
                            <p class="mt-1 text-sm text-slate-500">Atur wilayah dan hasil yang ditampilkan.</p>
                        </div>
                        <button type="button" @click="selectRasterLayer('none'); selectAllDistricts()"
                                class="rounded-lg px-2 py-2 text-sm font-semibold text-brand-800 hover:bg-rose-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-brand-800">Reset</button>
                    </div>
                    <div class="space-y-5 p-5">
                        <div>
                            <label for="map-district" class="mb-2 block text-sm font-bold uppercase tracking-wide text-slate-500">Wilayah</label>
                            <select id="map-district" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-3 text-base text-slate-800 focus:border-brand-800 focus:outline-none focus:ring-2 focus:ring-rose-100"
                                    :value="selectedDistrict" @change="$event.target.value === 'all' ? selectAllDistricts() : selectDistrict($event.target.value)">
                                <option value="all">Semua kecamatan</option>
                                @foreach(['Bagor', 'Gondang', 'Rejoso', 'Sukomoro'] as $districtName)
                                    <option value="{{ strtolower($districtName) }}">{{ $districtName }}</option>
                                @endforeach
                            </select>
                        </div>
                        <fieldset class="space-y-2">
                            <legend class="mb-2 text-sm font-bold uppercase tracking-wide text-slate-500">Tampilan hasil</legend>
                            @foreach([
                                ['id' => 'none', 'title' => 'Peta dasar', 'description' => 'Tanpa lapisan hasil model.'],
                                ['id' => 'probability', 'title' => 'Probabilitas', 'description' => 'Skor model dari rendah hingga tinggi.'],
                                ['id' => 'candidate', 'title' => 'Kandidat awal', 'description' => 'Skor minimal 0,50. Belum disaring permukiman.'],
                                ['id' => 'candidate_doa', 'title' => 'Kandidat tersaring DOA', 'description' => 'Lolos pemeriksaan kemiripan spektral.'],
                                ['id' => 'candidate_landcover', 'title' => 'Kandidat area pertanian', 'description' => 'DOA dengan penyaring tutupan lahan.'],
                            ] as $option)
                                @continue($option['id'] === 'candidate_landcover' && !($mapManifest['landcover_available'] ?? false))
                                <label class="flex items-start gap-3 rounded-xl border px-3 py-3 transition focus-within:ring-2 focus-within:ring-rose-200 {{ $option['id'] === 'candidate_landcover' && !($mapManifest['landcover_available'] ?? false) ? 'cursor-not-allowed bg-slate-50 opacity-60' : 'cursor-pointer hover:border-brand-800' }}"
                                       :class="( '{{ $option['id'] }}' === 'none' ? !activeMapLayers.some(id => id !== 'boundary') : isMapLayerActive('{{ $option['id'] }}')) ? 'border-brand-800 bg-rose-50' : 'border-slate-200'">
                                    <input type="radio" name="map_raster_layer" value="{{ $option['id'] }}" class="mt-1 h-4 w-4 shrink-0 accent-brand-800"
                                           @disabled($option['id'] === 'candidate_landcover' && !($mapManifest['landcover_available'] ?? false))
                                           :checked="'{{ $option['id'] }}' === 'none' ? !activeMapLayers.some(id => id !== 'boundary') : isMapLayerActive('{{ $option['id'] }}')"
                                           @change="selectRasterLayer('{{ $option['id'] }}')">
                                    <span class="min-w-0">
                                        <span class="block text-base font-semibold text-slate-800">{{ $option['title'] }}</span>
                                        <span class="mt-1 block text-sm leading-relaxed text-slate-500">{{ $option['description'] }}</span>

                                    </span>
                                </label>
                            @endforeach
                        </fieldset>
                        <label class="flex items-center gap-3 border-t border-slate-100 pt-4 text-base text-slate-700 cursor-pointer">
                            <input type="checkbox" class="h-4 w-4 accent-brand-800" :checked="isMapLayerActive('boundary')" @change="toggleMapLayer('boundary')">
                            Garis batas kecamatan
                        </label>
                        <template x-for="layerId in ['probability','candidate','candidate_doa','candidate_landcover']" :key="layerId">
                            <div aria-live="polite">
                                <p x-show="isMapLayerActive(layerId) && layers[layerId].status === 'loading'" x-cloak class="rounded-lg bg-slate-50 p-3 text-sm text-slate-600">Memuat lapisan peta...</p>
                                <p x-show="isMapLayerActive(layerId) && layers[layerId].status === 'error'" x-cloak role="alert" class="rounded-lg bg-rose-50 p-3 text-sm text-rose-800" x-text="layers[layerId].error"></p>
                            </div>
                        </template>
                        <details class="border-t border-slate-100 pt-4">
                            <summary class="cursor-pointer text-sm font-semibold text-slate-700">Tentang data dan cakupan wilayah</summary>
                            <div class="mt-3 space-y-3 text-sm leading-relaxed text-slate-500">
                                <p>Warna menunjukkan hasil model, bukan konfirmasi lapangan. Abu-abu berarti data belum tersedia; area tersebut belum dapat dinilai.</p>
                                @unless($mapManifest['landcover_available'] ?? false)
                                    <p>Penyaring permukiman belum tersedia pada hasil penelitian ini.</p>
                                @endunless
                                <div class="space-y-2 rounded-xl bg-slate-50 p-3">
                                    <p class="font-semibold text-slate-700">Piksel tanpa prediksi</p>
                                    @forelse($mapManifest['coverage'] ?? [] as $coverage)
                                        <div class="flex justify-between gap-3"><span>{{ $coverage['district'] }}</span><span class="font-semibold tabular-nums">{{ number_format($coverage['missing_percent'] ?? 0, 2, ',', '.') }}%</span></div>
                                    @empty
                                        <p>Peta belum disiapkan atau sumbernya berubah.</p>
                                    @endforelse
                                </div>
                            </div>
                        </details>
                    </div>
                </div>

                {{-- DETAIL KECAMATAN --}}
                <div
                    class="bg-white
                           rounded-3xl
                           border border-slate-200
                           shadow-sm
                           p-5"
                >

                    <div
                        class="flex
                               items-start
                               justify-between
                               gap-3"
                    >

                        <div>

                            <p class="text-sm text-slate-400">
                                Detail Kecamatan
                            </p>

                            <h3
                                class="text-lg
                                       font-bold
                                       text-slate-800
                                       mt-1"
                                x-text="selectedData.name"
                            >
                            </h3>

                        </div>


                        <span
                            class="px-2.5 py-1
                                   rounded-full
                                   text-sm
                                   font-bold
                                   border"
                            :class="selectedData.status_badge"
                            x-text="selectedData.status"
                        >
                        </span>

                    </div>


                    <div
                        class="grid
                               grid-cols-2
                               gap-3
                               mt-5"
                    >

                        <div
                            class="rounded-2xl
                                   bg-slate-50
                                   p-3"
                        >

                            <p class="text-sm
                                      text-slate-400">
                                Area raster valid
                            </p>

                            <p
                                class="text-base
                                       font-bold
                                       text-slate-700
                                       mt-1"
                                x-text="selectedData.area"
                            >
                            </p>

                        </div>


                        <div
                            class="rounded-2xl
                                   bg-slate-50
                                   p-3"
                        >

                            <p class="text-sm
                                      text-slate-400">
                                Produksi BPS
                            </p>

                            <p
                                class="text-base
                                       font-bold
                                       text-slate-700
                                       mt-1"
                                x-text="selectedData.plots"
                            >
                            </p>

                        </div>


                        <div
                            class="rounded-2xl
                                   bg-slate-50
                                   p-3"
                        >

                            <p class="text-sm
                                      text-slate-400">
                                Prediksi produktivitas 2025
                            </p>

                            <p
                                class="text-base
                                       font-bold
                                       text-slate-700
                                       mt-1"
                                x-text="selectedData.yield_info"
                            >
                            </p>

                        </div>


                        <div
                            class="rounded-2xl
                                   bg-slate-50
                                   p-3"
                        >

                            <p class="text-sm
                                      text-slate-400">
                                NDVI
                            </p>

                            <p
                                class="text-base
                                       font-bold
                                       text-slate-700
                                       mt-1"
                                x-text="selectedData.ndvi"
                            >
                            </p>

                            <p
                                class="text-[9px]
                                       text-slate-400"
                                x-text="selectedData.ndvi_sub"
                            >
                            </p>

                        </div>


                        <div
                            class="col-span-2
                                   rounded-2xl
                                   bg-slate-50
                                   p-3"
                        >

                            <p class="text-sm
                                      text-slate-400">
                                NDWI (indeks satelit)
                            </p>

                            <p
                                class="text-base
                                       font-bold
                                       text-slate-700
                                       mt-1"
                                x-text="selectedData.moisture"
                            >
                            </p>

                            <p
                                class="text-[9px]
                                       text-slate-400"
                                x-text="selectedData.moisture_sub"
                            >
                            </p>

                        </div>

                        <div class="rounded-2xl bg-purple-50 p-3">
                            <p class="text-sm text-purple-700">Area kandidat model</p>
                            <p class="mt-1 text-base font-bold text-slate-800" x-text="selectedData.candidate_area"></p>
                        </div>
                        <div class="rounded-2xl bg-cyan-50 p-3">
                            <p class="text-sm text-cyan-800">Kandidat tersaring</p>
                            <p class="mt-1 text-base font-bold text-slate-800" x-text="selectedData.candidate_doa_area"></p>
                        </div>

                    </div>

                </div>


                {{-- PROBABILITAS --}}
                <div
                    class="bg-white
                           rounded-3xl
                           border border-slate-200
                           shadow-sm
                           p-5"
                >

                    <p class="text-base
                              font-bold
                              text-slate-700
                              mb-4">
                        Tingkat Kecocokan Menurut Model
                    </p>


                    <div class="space-y-3">

                        <div class="flex
                                    items-center
                                    gap-3">

                            <span
                                class="w-4 h-4
                                       rounded
                                       bg-red-500">
                            </span>

                            <div>

                                <p class="text-sm
                                          font-semibold
                                          text-slate-600">
                                    Rendah
                                </p>

                                <p class="text-sm
                                          text-slate-400">
                                    Skor &lt; 0,35
                                </p>

                            </div>

                        </div>


                        <div class="flex
                                    items-center
                                    gap-3">

                            <span
                                class="w-4 h-4
                                       rounded
                                       bg-amber-500">
                            </span>

                            <div>

                                <p class="text-sm
                                          font-semibold
                                          text-slate-600">
                                    Sedang
                                </p>

                                <p class="text-sm
                                          text-slate-400">
                                    Skor 0,35–0,69
                                </p>

                            </div>

                        </div>


                        <div class="flex
                                    items-center
                                    gap-3">

                            <span
                                class="w-4 h-4
                                       rounded
                                       bg-green-600">
                            </span>

                            <div>

                                <p class="text-sm
                                          font-semibold
                                          text-slate-600">
                                    Tinggi
                                </p>

                                <p class="text-sm
                                          text-slate-400">
                                    Skor ≥ 0,70
                                </p>

                            </div>

                        </div>

                    </div>

                </div>


                {{-- RINGKASAN --}}
                <div
                    class="bg-white
                           rounded-3xl
                           border border-slate-200
                           shadow-sm
                           p-5"
                >

                    <p class="text-base
                              font-bold
                              text-slate-700
                              mb-4">
                        Ringkasan Dataset
                    </p>


                    <div class="space-y-3">

                        <div
                            class="flex
                                   items-center
                                   justify-between"
                        >

                            <span class="text-sm
                                         text-slate-500">
                                Area Valid
                            </span>

                            <span class="text-sm
                                         font-bold
                                         text-slate-700">
                                {{ $mapSummary['valid_area']['value'] }}
                            </span>

                        </div>


                        <div
                            class="flex
                                   items-center
                                   justify-between"
                        >

                            <span class="text-sm
                                         text-slate-500">
                                Area kandidat (skor ≥ 0,50)
                            </span>

                            <span class="text-sm
                                         font-bold
                                         text-slate-700">
                                {{ $mapSummary['candidate_area']['value'] }}
                            </span>

                        </div>


                        <div
                            class="flex
                                   items-center
                                   justify-between"
                        >

                            <span class="text-sm
                                         text-slate-500">
                                Kandidat setelah saringan spektral
                            </span>

                            <span class="text-sm
                                         font-bold
                                         text-slate-700">
                                {{ $mapSummary['candidate_doa_area']['value'] }}
                            </span>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

</section>


{{-- ============================================================
    LEAFLET JS — library peta interaktif
    Georaster tidak diperlukan lagi — overlay pakai L.imageOverlay
    dengan PNG yang di-generate server-side oleh Python.
    ============================================================ --}}
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>


<script>
/**
 * =============================================================
 * mapNganjuk() — Alpine.js component untuk peta Leaflet
 * =============================================================
 *
 * CARA KERJA LAYER RASTER (pendekatan baru):
 * - Setiap layer raster di-serve sebagai PNG RGBA dari server
 *   (di-generate oleh ml_model/generate_map_overlays.py).
 * - Browser menampilkan PNG via L.imageOverlay — tidak ada
 *   resampling piksel, sehingga warna KONSISTEN di semua zoom level.
 * - Layer hanya dimuat saat radio button dipilih (lazy load).
 *
 * STRUKTUR STATE RASTER (per layer):
 *   layers.probability = {
 *     status: 'idle' | 'loading' | 'ready' | 'error',
 *     progress: 0,     // selalu 0 (imageOverlay tidak ada progress)
 *     layer: null,     // L.imageOverlay instance
 *     error: '',       // pesan error jika gagal
 *   }
 */
function mapNganjuk() {
    return {

        /* --------------------------------------------------------
           STATE PETA DASAR
        -------------------------------------------------------- */

        /** Instance Leaflet map */
        map: null,

        /** Layer GeoJSON batas kecamatan */
        geojsonLayer: null,

        /** Object {id: leafletLayer} untuk setiap poligon kecamatan */
        polygons: {},

        /** Layer basemap satelit (Esri) */
        satelliteLayer: null,

        /** Layer basemap jalan (OpenStreetMap) */
        streetLayer: null,

        /** Basemap aktif: 'satellite' atau 'street' */
        mapBaseLayer: 'satellite',

        /** ID kecamatan yang sedang dipilih ('all' atau nama kecamatan) */
        selectedDistrict: @js($activeDistrict),

        /** Data kecamatan yang ditampilkan di panel kanan */
        selectedData: {},

        /** true = peta masih memuat GeoJSON pertama kali */
        loading: true,

        /** Pesan error fatal (peta tidak bisa tampil sama sekali) */
        mapError: '',

        /** Mencegah startMap() dipanggil dua kali */
        initialized: false,

        /* --------------------------------------------------------
           DATA ML DARI LARAVEL
           Dikirim dari DashboardController → $mlResults
        -------------------------------------------------------- */
        mlData: @json($mlResults ?? []),

        /* --------------------------------------------------------
           LAYER AKTIF
           Array berisi ID layer yang sedang dicentang.
           'boundary' aktif by default agar peta langsung informatif.
        -------------------------------------------------------- */
        activeMapLayers: ['boundary'],

        /* --------------------------------------------------------
           STATE TIAP RASTER LAYER
           status: 'idle'    = belum pernah dimuat
                   'loading' = sedang fetch TIF
                   'ready'   = layer siap pakai
                   'error'   = gagal dimuat
        -------------------------------------------------------- */
        layers: {
            candidate_landcover: { status: 'idle', progress: 0, layer: null, error: '' },
            probability:  { status: 'idle', progress: 0, layer: null, error: '' },
            candidate:    { status: 'idle', progress: 0, layer: null, error: '' },
            candidate_doa:{ status: 'idle', progress: 0, layer: null, error: '' },
        },

        /* --------------------------------------------------------
           URL PNG OVERLAY — dikirim dari DashboardController → $mapAssets
           Disiapkan pengelola melalui Python, lalu dibaca dari /map-overlay/{layer}.
           L.imageOverlay tidak melakukan resampling → warna konsisten di semua zoom.
        -------------------------------------------------------- */
        overlayUrls: {
            candidate_landcover: '{{ route("map.overlay", ["layer" => "candidate_landcover"]) }}',
            probability:   '{{ $mapAssets["overlay_probability"] }}',
            candidate:     '{{ $mapAssets["overlay_candidate"] }}',
            candidate_doa: '{{ $mapAssets["overlay_candidate_doa"] }}',
        },

        /* --------------------------------------------------------
           BOUNDING BOX RASTER
           Diverifikasi langsung dari TIF menggunakan rasterio.
           Format Leaflet imageOverlay: [[lat_south, lon_west], [lat_north, lon_east]]
        -------------------------------------------------------- */
        mapManifest: {{ Illuminate\Support\Js::from($mapManifest) }},

        /* ========================================================
           INISIALISASI PETA
           Dipanggil sekali oleh x-init="startMap()" di Blade.
        ======================================================== */
        async startMap() {
            // Jangan inisialisasi dua kali
            if (this.initialized) {
                this.map?.invalidateSize(true);
                return;
            }
            this.initialized = true;
            this.loading     = true;
            this.mapError    = '';

            try {
                // Tunggu sampai semua library eksternal tersedia.
                // Alpine.js memakai defer sehingga ada kemungkinan
                // Leaflet/georaster belum selesai dimuat saat ini dipanggil.
                await this.waitForLibraries();

                const container = document.getElementById('leaflet-map');
                if (!container) {
                    throw new Error('Elemen #leaflet-map tidak ditemukan di halaman.');
                }

                // 1. Buat instance Leaflet
                this.initLeafletMap(container);

                // 2. Muat GeoJSON batas kecamatan (wajib ada)
                await this.loadBoundaryGeoJson();

                // 3. Set data panel kanan ke "semua kecamatan"
                this.selectedData = this.getDistrictData(this.selectedDistrict);
                if (this.selectedDistrict !== 'all') this.zoomToDistrict(this.selectedDistrict);
                this.refreshBoundaryStyle();

                this.loading = false;

                // Pastikan ukuran peta terhitung ulang
                setTimeout(() => this.map?.invalidateSize(true), 300);

            } catch (err) {
                console.error('[Peta] Gagal inisialisasi:', err);
                this.mapError = err?.message || 'Terjadi kesalahan saat memuat peta.';
                this.loading  = false;
            }
        },

        /* ========================================================
           TUNGGU LIBRARY EKSTERNAL TERSEDIA
           Hanya Leaflet yang diperlukan sekarang.
           Georaster tidak dipakai — overlay pakai L.imageOverlay.
        ======================================================== */
        async waitForLibraries() {
            const MAX_WAIT_MS = 10000;
            const INTERVAL_MS = 100;
            let waited        = 0;

            while (waited < MAX_WAIT_MS) {
                if (typeof window.L !== 'undefined') return;
                await new Promise(resolve => setTimeout(resolve, INTERVAL_MS));
                waited += INTERVAL_MS;
            }

            if (typeof window.L === 'undefined') {
                throw new Error('Library Leaflet belum berhasil dimuat setelah 10 detik.');
            }
        },

        /* ========================================================
           INISIALISASI LEAFLET
        ======================================================== */
        initLeafletMap(container) {
            // Jika instance sudah ada (mis. hot reload), skip
            if (this.map || container._leaflet_id) {
                this.map?.invalidateSize(true);
                return;
            }

            // Basemap satelit (default)
            this.satelliteLayer = L.tileLayer(
                'https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}',
                { attribution: '© Esri, USGS, NOAA', maxZoom: 18 }
            );

            // Basemap jalan
            this.streetLayer = L.tileLayer(
                'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
                { attribution: '© OpenStreetMap contributors', maxZoom: 19 }
            );

            // Piksel tanpa pengamatan bersih memperlihatkan peta jalan, bukan citra Esri berawan.
            const satellite = this.mapManifest.layers?.satellite;
            if (satellite) {
                const image = L.imageOverlay(
                    '{{ route("map.overlay", ["layer" => "satellite"]) }}?v=' + this.mapManifest.version,
                    satellite.bounds,
                    { pane: 'tilePane', zIndex: 2, attribution: 'Contains modified Copernicus Sentinel data 2025' }
                );
                image.on('error', () => {
                    this.mapError = 'Citra Sentinel-2 gagal dimuat. Peta jalan tetap tersedia.';
                });
                this.satelliteLayer = L.layerGroup([this.streetLayer, image]);
            }

            this.map = L.map(container, {
                center:      [-7.535, 111.915],
                zoom:        11,
                zoomControl: true,
                layers:      [this.satelliteLayer],
            });
        },

        /* ========================================================
           MUAT GEOJSON BATAS KECAMATAN
        ======================================================== */
        async loadBoundaryGeoJson() {
            const url = '{{ asset("geojson/Batas_4_Kecamatan_Nganjuk.geojson") }}';

            const res = await fetch(url, { cache: 'no-cache' });
            if (!res.ok) {
                throw new Error(`GeoJSON gagal dimuat (HTTP ${res.status}).`);
            }

            const geojson = await res.json();

            if (geojson?.type !== 'FeatureCollection' || !Array.isArray(geojson.features)) {
                throw new Error('Format GeoJSON tidak valid.');
            }

            this.buildGeoJsonLayer(geojson);
        },

        /* ========================================================
           BUAT LAYER GEOJSON
        ======================================================== */
        buildGeoJsonLayer(geojson) {
            if (!this.map) return;

            // Hapus layer lama jika ada
            if (this.geojsonLayer) {
                this.map.removeLayer(this.geojsonLayer);
                this.geojsonLayer = null;
            }
            this.polygons = {};

            this.geojsonLayer = L.geoJSON(geojson, {
                // Warna awal tiap kecamatan
                style: (feature) => {
                    const id    = this.getDistrictId(feature);
                    const color = this.getDistrictColor(id);
                    return {
                        fillColor:   color.fill,
                        color:       color.border,
                        weight:      2,
                        opacity:     1,
                        fillOpacity: 0,
                    };
                },

                onEachFeature: (feature, layer) => {
                    const id = this.getDistrictId(feature);
                    this.polygons[id] = layer;

                    // Popup saat diklik
                    layer.bindPopup(
                        () => this.buildPopupHtml(id, feature),
                        { className: 'custom-popup', maxWidth: 300 }
                    );

                    // Klik → pilih kecamatan
                    layer.on('click', () => this.selectDistrict(id));

                    // Hover → tebalkan garis
                    layer.on('mouseover', () => layer.setStyle({ weight: 4 }));
                    layer.on('mouseout',  () => this.refreshBoundaryStyle());
                },
            });

            this.geojsonLayer.addTo(this.map);

            // Zoom ke seluruh wilayah
            const bounds = this.geojsonLayer.getBounds();
            if (bounds.isValid()) {
                this.map.fitBounds(bounds, { padding: [20, 20] });
            }

            this.refreshBoundaryStyle();
        },

        /* ========================================================
           PILIH SATU LAYER RASTER (dipanggil oleh radio button)
           Matikan semua raster lain dulu, lalu aktifkan yang dipilih.
           Boundary tidak disentuh.
        ======================================================== */
        async selectRasterLayer(layerId) {
            const rasterLayers = ['probability', 'candidate', 'candidate_doa', 'candidate_landcover'];

            // Matikan semua raster yang sedang aktif
            for (const id of rasterLayers) {
                if (this.activeMapLayers.includes(id)) {
                    const idx = this.activeMapLayers.indexOf(id);
                    this.activeMapLayers.splice(idx, 1);
                    this.hideLayer(id);
                }
            }

            // Aktifkan yang dipilih (kecuali 'none')
            if (layerId !== 'none' && rasterLayers.includes(layerId)) {
                this.activeMapLayers.push(layerId);
                const state = this.layers[layerId];

                if (state.status === 'idle' || state.status === 'error') {
                    await this.loadRasterLayer(layerId);
                } else if (state.status === 'ready' && state.layer) {
                    this.showLayer(layerId);
                }
            }

            this.refreshBoundaryStyle();
        },

        /* ========================================================
           TOGGLE LAYER (dipanggil saat checkbox dicentang/uncentang)
           Hanya dipakai untuk layer 'boundary' sekarang.
           Layer raster menggunakan selectRasterLayer() via radio button.
        ======================================================== */
        async toggleMapLayer(layerId) {
            const allowed = ['boundary', 'probability', 'candidate', 'candidate_doa', 'candidate_landcover'];
            if (!allowed.includes(layerId)) return;

            const isCurrentlyActive = this.activeMapLayers.includes(layerId);

            // Toggle di array — gunakan splice/push agar Alpine.js tidak
            // menganggap ini array baru dan tidak me-reset reactive state.
            if (isCurrentlyActive) {
                const idx = this.activeMapLayers.indexOf(layerId);
                if (idx > -1) this.activeMapLayers.splice(idx, 1);
            } else {
                this.activeMapLayers.push(layerId);
            }

            if (layerId === 'boundary') {
                // Boundary = GeoJSON, cukup perbarui style
                this.refreshBoundaryStyle();
                return;
            }

            // --- Layer raster ---
            const state = this.layers[layerId];

            if (!isCurrentlyActive) {
                // Checkbox baru dicentang (sebelumnya tidak aktif)
                if (state.status === 'idle' || state.status === 'error') {
                    // Belum pernah dimuat → fetch sekarang
                    await this.loadRasterLayer(layerId);
                } else if (state.status === 'ready' && state.layer) {
                    // Sudah pernah dimuat → langsung tampilkan tanpa fetch ulang
                    this.showLayer(layerId);
                }
                // Jika status 'loading' → sedang fetch, akan tampil otomatis setelah selesai
                // Jika status 'error'   → pesan error tampil di template
            } else {
                // Checkbox diuncentang → sembunyikan layer dari peta
                // (layer tetap disimpan di state.layer, tidak perlu fetch ulang)
                this.hideLayer(layerId);
            }

            // Perbarui transparansi GeoJSON agar tidak menutupi raster
            this.refreshBoundaryStyle();
        },

        // Metadata berisi batas PNG hasil reproyeksi. Status siap menunggu gambar selesai dimuat.
        async loadRasterLayer(layerId) {
            const state = this.layers[layerId];
            const url   = this.overlayUrls[layerId];

            if (!url) {
                state.status = 'error';
                state.error  = 'URL overlay tidak tersedia.';
                return;
            }

            state.status   = 'loading';
            state.progress = 0;
            state.error    = '';

            try {
                const metadata = this.mapManifest.layers?.[layerId];
                if (!metadata) {
                    throw new Error('Peta belum disiapkan atau sumber data berubah. Hubungi pengelola.');
                }
                // PNG sudah diproyeksikan ke Web Mercator oleh generator.
                state.layer = window.L.imageOverlay(url + '?v=' + this.mapManifest.version, metadata.bounds, {
                    opacity:     layerId === 'candidate' ? 0.85 : 0.88,
                    // crossOrigin diperlukan agar browser izinkan gambar dari origin sama
                    crossOrigin: true,
                    // zIndex mengatur urutan layer
                    zIndex:      layerId === 'probability' ? 200
                               : layerId === 'candidate'   ? 210
                               : 220,
                });

                await new Promise((resolve, reject) => {
                    state.layer.once('load', resolve);
                    state.layer.once('error', () => reject(new Error('Gambar peta gagal dimuat.')));
                    state.layer.addTo(this.map);
                });
                state.status = 'ready';
                state.progress = 100;
                if (!this.activeMapLayers.includes(layerId)) {
                    this.hideLayer(layerId);
                }

                // Tampilkan jika masih aktif (user belum uncentang saat loading)
                if (this.activeMapLayers.includes(layerId)) {
                    this.showLayer(layerId);
                    this.refreshBoundaryStyle();
                }

            } catch (err) {
                console.error(`[Peta] Layer ${layerId} gagal dimuat:`, err);
                state.status = 'error';
                state.error  = err?.message || 'Layer tidak dapat dimuat.';
                if (state.layer) this.map.removeLayer(state.layer);
                state.layer = null;
            }
        },

        /* ========================================================
           TAMPILKAN LAYER DI PETA
           Setelah menambah layer, panggil reorderLayers()
           agar urutan z-index selalu konsisten.
        ======================================================== */
        showLayer(layerId) {
            if (!this.map) return;
            const { layer } = this.layers[layerId];
            if (!layer) return;

            if (!this.map.hasLayer(layer)) {
                layer.addTo(this.map);
            }

            // Tata ulang urutan setelah layer baru masuk
            this.reorderLayers();
        },

        /* ========================================================
           SEMBUNYIKAN LAYER DARI PETA
        ======================================================== */
        hideLayer(layerId) {
            if (!this.map) return;
            const { layer } = this.layers[layerId];
            if (layer && this.map.hasLayer(layer)) {
                this.map.removeLayer(layer);
            }
        },

        /* ========================================================
           TATA URUTAN Z-INDEX LAYER
           Urutan dari bawah ke atas:
             1. probability  (paling bawah, warna area luas)
             2. candidate    (di atas probability)
             3. candidate_doa(di atas candidate)
             4. geojsonLayer (paling atas, batas kecamatan selalu terlihat)

           Ini memastikan:
           - Saat probability + candidate aktif bersamaan,
             candidate (ungu) menutupi probability di area yang sama
           - Batas kecamatan tidak pernah tertutup raster
        ======================================================== */
        reorderLayers() {
            if (!this.map) return;

            // Urutan yang benar: dari bawah ke atas
            const order = ['probability', 'candidate', 'candidate_doa', 'candidate_landcover'];

            for (const id of order) {
                const { layer } = this.layers[id];
                if (layer && this.map.hasLayer(layer)) {
                    layer.bringToFront();
                }
            }

            // GeoJSON boundary selalu paling atas
            if (this.geojsonLayer && this.map.hasLayer(this.geojsonLayer)) {
                this.geojsonLayer.bringToFront();
            }
        },

        /* ========================================================
           CEK APAKAH LAYER SEDANG AKTIF (dicentang)
        ======================================================== */
        isMapLayerActive(layerId) {
            return this.activeMapLayers.includes(layerId);
        },

        /* ========================================================
           CENTANG SEMUA LAYER
        ======================================================== */
        async selectAllMapLayers() {
            const all = ['boundary', 'probability', 'candidate', 'candidate_doa', 'candidate_landcover'];
            for (const id of all) {
                if (!this.activeMapLayers.includes(id)) {
                    await this.toggleMapLayer(id);
                }
            }
        },

        /* ========================================================
           HAPUS SEMUA LAYER
        ======================================================== */
        clearMapLayers() {
            // Sembunyikan semua layer dari peta terlebih dahulu
            for (const id of ['probability', 'candidate', 'candidate_doa', 'candidate_landcover']) {
                this.hideLayer(id);
            }

            // Kosongkan array dengan splice agar Alpine.js tidak
            // menganggap ini object baru dan tidak me-reset state
            this.activeMapLayers.splice(0, this.activeMapLayers.length);

            this.refreshBoundaryStyle();
        },

        /* ========================================================
           TOGGLE BASEMAP (satelit ↔ jalan)
        ======================================================== */
        toggleBaseLayer() {
            if (!this.map) return;

            if (this.mapBaseLayer === 'satellite') {
                this.map.removeLayer(this.satelliteLayer);
                this.streetLayer.addTo(this.map);
                this.mapBaseLayer = 'street';
            } else {
                this.map.removeLayer(this.streetLayer);
                this.satelliteLayer.addTo(this.map);
                this.mapBaseLayer = 'satellite';
            }

            setTimeout(() => this.map?.invalidateSize(true), 150);
        },

        /* ========================================================
           PILIH KECAMATAN (dari dropdown atau klik peta)
        ======================================================== */
        selectDistrict(id) {
            const allowed = ['sukomoro', 'bagor', 'gondang', 'rejoso'];
            if (!allowed.includes(id)) return;
            this.updateSelectedData(id);
        },

        selectAllDistricts() {
            this.updateSelectedData('all');
        },

        /* ========================================================
           UPDATE DATA PANEL KANAN
        ======================================================== */
        updateSelectedData(id) {
            if (!['all', 'bagor', 'gondang', 'rejoso', 'sukomoro'].includes(id)) return;
            // Satu peristiwa menyelaraskan ringkasan dan laporan tanpa memuat ulang peta.
            this.$dispatch('district-changed', id);
            const url = new URL(window.location.href);
            url.searchParams.set('district', id);
            window.history.replaceState(null, '', url.toString());
            this.selectedDistrict = id;
            this.selectedData     = this.getDistrictData(id);

            if (id === 'all') {
                // Zoom ke seluruh wilayah
                const bounds = this.geojsonLayer?.getBounds();
                if (bounds?.isValid()) {
                    this.map?.fitBounds(bounds, { padding: [20, 20] });
                }
            } else {
                this.zoomToDistrict(id);
            }

            this.refreshBoundaryStyle();
        },

        /* ========================================================
           ZOOM KE KECAMATAN TERTENTU
        ======================================================== */
        zoomToDistrict(id) {
            const poly = this.polygons[id];
            if (!poly) return;

            // Tebalkan garis kecamatan terpilih
            poly.setStyle({ weight: 4, color: '#111827' });

            const bounds = poly.getBounds();
            if (bounds?.isValid()) {
                this.map?.fitBounds(bounds, { maxZoom: 13, padding: [20, 20] });
            }

            // Pastikan raster tetap di bawah boundary
            if (this.geojsonLayer && this.map?.hasLayer(this.geojsonLayer)) {
                this.geojsonLayer.bringToFront();
            }
        },

        /* ========================================================
           PERBARUI STYLE GEOJSON BOUNDARY
           - Jika raster aktif → GeoJSON hampir transparan (hanya garis)
           - Jika hanya boundary → GeoJSON berwarna penuh
           - Jika boundary dinonaktifkan → sembunyikan
        ======================================================== */
        refreshBoundaryStyle() {
            if (!this.geojsonLayer) return;

            const boundaryOn   = this.isMapLayerActive('boundary');
            const rasterActive = this.isMapLayerActive('probability')
                              || this.isMapLayerActive('candidate')
                              || this.isMapLayerActive('candidate_doa')
                              || this.isMapLayerActive('candidate_landcover');

            this.geojsonLayer.eachLayer((layer) => {
                const id    = this.getDistrictId(layer.feature);
                const color = this.getDistrictColor(id);
                const isSelected = (id === this.selectedDistrict);

                if (!boundaryOn) {
                    // Boundary dimatikan → sembunyikan
                    layer.setStyle({ opacity: 0, fillOpacity: 0 });
                    return;
                }

                if (rasterActive) {
                    // Ada raster → garis tipis saja, isi transparan
                    layer.setStyle({
                        color:       '#1F2937',
                        weight:      isSelected ? 3.5 : 2,
                        opacity:     1,
                        fillOpacity: 0,
                    });
                } else {
                    // Hanya boundary → warna penuh per kecamatan
                    layer.setStyle({
                        fillColor:   color.fill,
                        color:       isSelected ? '#111827' : color.border,
                        weight:      isSelected ? 3.5 : 2,
                        opacity:     1,
                        fillOpacity: 0,
                    });
                }
            });

            // Pastikan boundary berada di atas raster
            if (this.map?.hasLayer(this.geojsonLayer)) {
                this.geojsonLayer.bringToFront();
            }

            // Tata ulang semua layer agar z-order konsisten
            this.reorderLayers();
        },

        /* ========================================================
           AMBIL ID KECAMATAN DARI PROPERTI GEOJSON
        ======================================================== */
        getDistrictId(feature) {
            const props = feature?.properties || {};

            // Coba berbagai nama properti yang mungkin ada di GeoJSON
            const keys = ['Kecamatan', 'KECAMATAN', 'kecamatan', 'NAMOBJ', 'WADMKC', 'NAME_3', 'name'];
            let raw = '';

            for (const key of keys) {
                if (props[key] != null) {
                    raw = String(props[key]).toLowerCase().trim();
                    break;
                }
            }

            if (raw.includes('sukomoro')) return 'sukomoro';
            if (raw.includes('bagor'))    return 'bagor';
            if (raw.includes('gondang'))  return 'gondang';
            if (raw.includes('rejoso'))   return 'rejoso';
            return 'unknown';
        },

        /* ========================================================
           WARNA PER KECAMATAN
        ======================================================== */
        getDistrictColor(id) {
            const palette = {
                sukomoro: { fill: '#4CAF50', border: '#1B5E20' },
                bagor:    { fill: '#66BB6A', border: '#1B5E20' },
                gondang:  { fill: '#FFA726', border: '#E65100' },
                rejoso:   { fill: '#81C784', border: '#2E7D32' },
            };
            return palette[id] ?? { fill: '#90A4AE', border: '#455A64' };
        },

        /* ========================================================
           BUAT HTML POPUP KECAMATAN
        ======================================================== */
        buildPopupHtml(id, feature) {
            const data = this.getDistrictData(id);

            return `
                <div style="width:260px;font-family:'Plus Jakarta Sans',sans-serif;padding:14px;">
                    <div style="font-size:15px;font-weight:800;color:#1e293b;margin-bottom:3px;">
                        ${data.name}
                    </div>
                    <div style="font-size:11px;color:#64748b;margin-bottom:12px;">
                        ${data.status}
                    </div>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:6px;">
                        <div style="background:#f8fafc;padding:8px;border-radius:8px;">
                            <div style="font-size:9px;color:#94a3b8;">Area Kandidat</div>
                            <div style="font-size:11px;font-weight:700;color:#334155;margin-top:2px;">${data.candidate_area}</div>
                        </div>
                        <div style="background:#f8fafc;padding:8px;border-radius:8px;">
                            <div style="font-size:9px;color:#94a3b8;">NDVI 2025</div>
                            <div style="font-size:11px;font-weight:700;color:#334155;margin-top:2px;">${data.ndvi}</div>
                        </div>
                        <div style="background:#f8fafc;padding:8px;border-radius:8px;grid-column:span 2;">
                            <div style="font-size:9px;color:#94a3b8;">Prediksi Produktivitas 2025</div>
                            <div style="font-size:11px;font-weight:700;color:#334155;margin-top:2px;">${data.yield_info}</div>
                        </div>
                    </div>
                </div>`;
        },

        /* ========================================================
           DATA KECAMATAN UNTUK PANEL KANAN
           Mengambil dari $mlResults yang dikirim controller.
        ======================================================== */
        getDistrictData(id) {
            const fallback = {
                name:              'Semua Kecamatan',
                status:            'Ringkasan seluruh wilayah penelitian',
                status_badge:      'bg-slate-100 text-slate-700 border-slate-200',
                area:              @json($mapSummary['valid_area']['value']),
                candidate_area:    @json($mapSummary['candidate_area']['value']),
                candidate_doa_area:@json($mapSummary['candidate_doa_area']['value']),
                plots:             'Pilih kecamatan untuk melihat data BPS 2025',
                yield_info:        'Pilih kecamatan untuk melihat prediksi',
                ndvi:              'Data gabungan',
                ndvi_sub:          'Sentinel-2 2025',
                moisture:          'Data gabungan',
                moisture_sub:      'Indeks satelit 2025',
            };

            if (id === 'all') return fallback;

            // Cari di mlData (object dengan key = id kecamatan)
            const ml = this.mlData?.[id] ?? null;
            if (!ml) {
                return {
                    ...fallback,
                    name:         `Kecamatan ${id.charAt(0).toUpperCase() + id.slice(1)}`,
                    status:       'Data ML tersedia',
                    status_badge: 'bg-emerald-50 text-emerald-700 border-emerald-200',
                    area:         '-',
                    candidate_area:    'Belum tersedia',
                    candidate_doa_area:'Belum tersedia',
                    plots:        '-',
                    yield_info:   '-',
                    ndvi:         '-',
                    ndvi_sub:     'Sentinel-2 2025',
                    moisture:     '-',
                    moisture_sub: 'Indeks satelit 2025',
                };
            }

            return {
                ...fallback,
                ...ml,
                name:              ml.name         ?? `Kecamatan ${id.charAt(0).toUpperCase() + id.slice(1)}`,
                status:            ml.status        ?? 'Kandidat menurut model',
                status_badge:      ml.status_badge  ?? 'bg-emerald-50 text-emerald-700 border-emerald-200',
                area:              ml.area          ?? '-',
                candidate_area:    ml.candidate_area     ?? ml.candidate != null ? (ml.candidate?.toFixed(2) + ' ha') : 'Belum tersedia',
                candidate_doa_area:ml.candidate_doa_area ?? ml.candidate_doa != null ? (ml.candidate_doa?.toFixed(2) + ' ha') : 'Belum tersedia',
                plots:             ml.plots         ?? '-',
                yield_info:        ml.yield_info    ?? '-',
                ndvi:              ml.ndvi != null  ? String(ml.ndvi) : '-',
                ndvi_sub:          ml.ndvi_sub      ?? 'Sentinel-2 2025',
                moisture:          ml.moisture      ?? '-',
                moisture_sub:      ml.moisture_sub  ?? 'Indeks satelit 2025',
            };
        },
    };
}
</script>