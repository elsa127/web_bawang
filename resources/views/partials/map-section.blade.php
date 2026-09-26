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
        height: 500px;
        min-height: 500px;
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

                    <p class="text-sm font-semibold text-brand-600 mb-1">
                        Analisis Spasial
                    </p>

                    <h2 class="text-2xl sm:text-3xl
                               font-bold text-slate-800">
                        🗺️ Peta Lahan Bawang Merah
                    </h2>

                    <p class="text-sm text-slate-500 mt-2 max-w-2xl">
                        Visualisasi hasil analisis spasial dan model
                        Machine Learning pada wilayah penelitian Nganjuk.
                    </p>

                </div>

            </div>

        </div>


        {{-- ====================================================
            LAYOUT
            ==================================================== --}}
        <div class="grid grid-cols-1
                    lg:grid-cols-[1fr_370px]
                    gap-5">


            {{-- =================================================
                KIRI : PETA
                ================================================= --}}
            <div class="bg-white
                        rounded-3xl
                        border border-slate-200
                        shadow-sm
                        overflow-hidden">


                {{-- =================================================
                    LAYER PETA
                    LANGSUNG TAMPIL
                    TIDAK ADA DROPDOWN
                    ================================================= --}}
                <div class="px-4 sm:px-5 py-4
                            border-b border-slate-100">

                    <div class="flex flex-col
                                sm:flex-row
                                sm:items-center
                                sm:justify-between
                                gap-3">

                        <div>

                            <p class="text-xs text-slate-400">
                                Layer Peta
                            </p>

                            <p class="text-sm font-bold text-slate-700">
                                Pilih layer yang ingin ditampilkan
                            </p>

                        </div>


                        <button
                            type="button"
                            @click="clearMapLayers()"
                            class="text-xs font-semibold
                                   text-rose-500
                                   hover:text-rose-600
                                   transition"
                        >
                            Hapus semua
                        </button>

                    </div>


                    {{-- =================================================
                        SEMUA LAYER SEJAJAR
                        ================================================= --}}
                    <div class="flex flex-wrap
                                items-center
                                gap-2
                                mt-4">


                        {{-- BATAS KECAMATAN --}}
                        <button
                            type="button"
                            @click="toggleMapLayer('boundary')"
                            class="inline-flex
                                   items-center
                                   gap-2
                                   rounded-xl
                                   border
                                   px-4 py-2.5
                                   text-sm
                                   font-semibold
                                   transition"
                            :class="
                                isMapLayerActive('boundary')
                                ?
                                'bg-emerald-500 border-emerald-500 text-white shadow-sm'
                                :
                                'bg-white border-slate-200 text-slate-600 hover:bg-slate-50'
                            "
                        >

                            <span>
                                🧭
                            </span>

                            <span>
                                Batas Kecamatan
                            </span>

                            <span
                                x-show="isMapLayerActive('boundary')"
                                x-cloak
                            >
                                ✓
                            </span>

                        </button>


                        {{-- PROBABILITY --}}
                        <button
                            type="button"
                            @click="toggleMapLayer('probability')"
                            class="inline-flex
                                   items-center
                                   gap-2
                                   rounded-xl
                                   border
                                   px-4 py-2.5
                                   text-sm
                                   font-semibold
                                   transition"
                            :class="
                                isMapLayerActive('probability')
                                ?
                                'bg-emerald-500 border-emerald-500 text-white shadow-sm'
                                :
                                'bg-white border-slate-200 text-slate-600 hover:bg-slate-50'
                            "
                        >

                            <span>
                                📊
                            </span>

                            <span>
                                Probability Map
                            </span>

                            <span
                                x-show="isMapLayerActive('probability')"
                                x-cloak
                            >
                                ✓
                            </span>

                        </button>


                        {{-- CANDIDATE --}}
                        <button
                            type="button"
                            @click="toggleMapLayer('candidate')"
                            class="inline-flex
                                   items-center
                                   gap-2
                                   rounded-xl
                                   border
                                   px-4 py-2.5
                                   text-sm
                                   font-semibold
                                   transition"
                            :class="
                                isMapLayerActive('candidate')
                                ?
                                'bg-emerald-500 border-emerald-500 text-white shadow-sm'
                                :
                                'bg-white border-slate-200 text-slate-600 hover:bg-slate-50'
                            "
                        >

                            <span>
                                🎯
                            </span>

                            <span>
                                Candidate T = 0.50
                            </span>

                            <span
                                x-show="isMapLayerActive('candidate')"
                                x-cloak
                            >
                                ✓
                            </span>

                        </button>

                    </div>

                </div>


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

                        <p class="text-xs text-slate-400">
                            Wilayah Terpilih
                        </p>

                        <h3
                            class="text-base font-bold text-slate-700"
                            x-text="selectedData.name"
                        >
                        </h3>

                    </div>


                    {{-- BASEMAP --}}
                    <button
                        type="button"
                        @click="toggleBaseLayer()"
                        class="inline-flex
                               items-center
                               gap-2
                               px-3 py-2
                               rounded-xl
                               bg-slate-50
                               hover:bg-slate-100
                               border border-slate-200
                               text-xs
                               font-semibold
                               text-slate-600"
                    >

                        <span
                            x-show="mapBaseLayer==='satellite'"
                        >
                            🗺️ Jalan
                        </span>

                        <span
                            x-show="mapBaseLayer==='street'"
                        >
                            🛰️ Satelit
                        </span>

                    </button>

                </div>


                {{-- =================================================
                    FILTER KECAMATAN
                    ================================================= --}}
                <div class="px-4 sm:px-5 py-3
                            border-b border-slate-100
                            flex flex-wrap gap-2">

                    <button
                        type="button"
                        @click="selectAllDistricts()"
                        class="px-3 py-1.5
                               rounded-full
                               text-xs
                               font-semibold
                               border
                               transition"
                        :class="
                            selectedDistrict==='all'
                            ?
                            'bg-emerald-500 text-white border-emerald-500'
                            :
                            'bg-white text-slate-600 border-slate-200 hover:bg-slate-50'
                        "
                    >
                        Semua Kecamatan
                    </button>


                    <button
                        type="button"
                        @click="selectDistrict('sukomoro')"
                        class="px-3 py-1.5
                               rounded-full
                               text-xs
                               font-semibold
                               border
                               transition"
                        :class="
                            selectedDistrict==='sukomoro'
                            ?
                            'bg-emerald-500 text-white border-emerald-500'
                            :
                            'bg-white text-slate-600 border-slate-200 hover:bg-slate-50'
                        "
                    >
                        Sukomoro
                    </button>


                    <button
                        type="button"
                        @click="selectDistrict('bagor')"
                        class="px-3 py-1.5
                               rounded-full
                               text-xs
                               font-semibold
                               border
                               transition"
                        :class="
                            selectedDistrict==='bagor'
                            ?
                            'bg-emerald-500 text-white border-emerald-500'
                            :
                            'bg-white text-slate-600 border-slate-200 hover:bg-slate-50'
                        "
                    >
                        Bagor
                    </button>


                    <button
                        type="button"
                        @click="selectDistrict('gondang')"
                        class="px-3 py-1.5
                               rounded-full
                               text-xs
                               font-semibold
                               border
                               transition"
                        :class="
                            selectedDistrict==='gondang'
                            ?
                            'bg-emerald-500 text-white border-emerald-500'
                            :
                            'bg-white text-slate-600 border-slate-200 hover:bg-slate-50'
                        "
                    >
                        Gondang
                    </button>


                    <button
                        type="button"
                        @click="selectDistrict('rejoso')"
                        class="px-3 py-1.5
                               rounded-full
                               text-xs
                               font-semibold
                               border
                               transition"
                        :class="
                            selectedDistrict==='rejoso'
                            ?
                            'bg-emerald-500 text-white border-emerald-500'
                            :
                            'bg-white text-slate-600 border-slate-200 hover:bg-slate-50'
                        "
                    >
                        Rejoso
                    </button>

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

                            <p class="text-sm
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

                            <p class="text-sm
                                      font-bold
                                      text-slate-700
                                      mb-1">
                                Peta tidak dapat dimuat
                            </p>

                            <p
                                class="text-xs
                                       text-slate-500"
                                x-text="mapError"
                            >
                            </p>

                        </div>

                    </div>


                    {{-- LEGEND CANDIDATE --}}
                    <div
                        x-show="isMapLayerActive('candidate')"
                        x-cloak
                        class="absolute
                               z-[500]
                               bottom-4
                               left-4
                               bg-white/95
                               backdrop-blur-sm
                               rounded-xl
                               shadow-lg
                               border border-slate-200
                               px-4 py-3"
                    >

                        <p class="text-xs
                                  font-bold
                                  text-slate-700
                                  mb-2">
                            Candidate T = 0.50
                        </p>

                        <div class="flex
                                    items-center
                                    gap-2 mb-1">

                            <span
                                class="w-4 h-4
                                       rounded
                                       bg-orange-500">
                            </span>

                            <span class="text-[11px]
                                         text-slate-600">
                                Kandidat ≥ 0.50
                            </span>

                        </div>

                        <div class="flex
                                    items-center
                                    gap-2">

                            <span
                                class="w-4 h-4
                                       rounded
                                       bg-slate-300">
                            </span>

                            <span class="text-[11px]
                                         text-slate-600">
                                Bukan kandidat
                            </span>

                        </div>

                    </div>


                    {{-- LEGEND PROBABILITY --}}
                    <div
                        x-show="isMapLayerActive('probability')"
                        x-cloak
                        class="absolute
                               z-[500]
                               bottom-4
                               right-4
                               bg-white/95
                               backdrop-blur-sm
                               rounded-xl
                               shadow-lg
                               border border-slate-200
                               px-4 py-3"
                    >

                        <p class="text-xs
                                  font-bold
                                  text-slate-700
                                  mb-2">
                            Probability
                        </p>

                        <div class="flex
                                    items-center
                                    gap-2 mb-1">

                            <span
                                class="w-4 h-4
                                       rounded
                                       bg-red-500">
                            </span>

                            <span class="text-[11px]
                                         text-slate-600">
                                &lt; 0.35
                            </span>

                        </div>

                        <div class="flex
                                    items-center
                                    gap-2 mb-1">

                            <span
                                class="w-4 h-4
                                       rounded
                                       bg-amber-500">
                            </span>

                            <span class="text-[11px]
                                         text-slate-600">
                                0.35 – &lt; 0.70
                            </span>

                        </div>

                        <div class="flex
                                    items-center
                                    gap-2">

                            <span
                                class="w-4 h-4
                                       rounded
                                       bg-green-600">
                            </span>

                            <span class="text-[11px]
                                         text-slate-600">
                                ≥ 0.70
                            </span>

                        </div>

                    </div>

                </div>


                {{-- =================================================
                    SUMBER
                    ================================================= --}}
                <div
                    class="px-4 sm:px-5 py-3
                           border-t border-slate-100
                           bg-slate-50"
                >

                    <p class="text-[11px] text-slate-400">

                        Sumber batas wilayah:

                        <span class="font-semibold
                                     text-slate-500">
                            Batas_4_Kecamatan_Nganjuk.geojson
                        </span>

                    </p>

                    <p class="text-[11px]
                              text-slate-400
                              mt-0.5">
                        Data analisis spasial dan model ML
                    </p>

                </div>

            </div>


            {{-- =================================================
                KANAN : DETAIL
                ================================================= --}}
            <div class="space-y-4">


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

                            <p class="text-xs text-slate-400">
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
                                   text-[10px]
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

                            <p class="text-[10px]
                                      text-slate-400">
                                Luas
                            </p>

                            <p
                                class="text-sm
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

                            <p class="text-[10px]
                                      text-slate-400">
                                Plot
                            </p>

                            <p
                                class="text-sm
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

                            <p class="text-[10px]
                                      text-slate-400">
                                Prediksi
                            </p>

                            <p
                                class="text-sm
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

                            <p class="text-[10px]
                                      text-slate-400">
                                NDVI
                            </p>

                            <p
                                class="text-sm
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

                            <p class="text-[10px]
                                      text-slate-400">
                                Kelembapan
                            </p>

                            <p
                                class="text-sm
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

                    <p class="text-sm
                              font-bold
                              text-slate-700
                              mb-4">
                        Probabilitas Model
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

                                <p class="text-xs
                                          font-semibold
                                          text-slate-600">
                                    Rendah
                                </p>

                                <p class="text-[10px]
                                          text-slate-400">
                                    &lt; 0.35
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

                                <p class="text-xs
                                          font-semibold
                                          text-slate-600">
                                    Sedang
                                </p>

                                <p class="text-[10px]
                                          text-slate-400">
                                    0.35 – &lt; 0.70
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

                                <p class="text-xs
                                          font-semibold
                                          text-slate-600">
                                    Tinggi
                                </p>

                                <p class="text-[10px]
                                          text-slate-400">
                                    ≥ 0.70
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

                    <p class="text-sm
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

                            <span class="text-xs
                                         text-slate-500">
                                Area Valid
                            </span>

                            <span class="text-xs
                                         font-bold
                                         text-slate-700">
                                33,224.52 ha
                            </span>

                        </div>


                        <div
                            class="flex
                                   items-center
                                   justify-between"
                        >

                            <span class="text-xs
                                         text-slate-500">
                                Kandidat T = 0.50
                            </span>

                            <span class="text-xs
                                         font-bold
                                         text-slate-700">
                                26,854.45 ha
                            </span>

                        </div>


                        <div
                            class="flex
                                   items-center
                                   justify-between"
                        >

                            <span class="text-xs
                                         text-slate-500">
                                Kandidat + DOA
                            </span>

                            <span class="text-xs
                                         font-bold
                                         text-slate-700">
                                26,047.32 ha
                            </span>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

</section>


{{-- ============================================================
    LEAFLET JS
    ============================================================ --}}
<script
    src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js">
</script>


{{-- ============================================================
    GEORASTER
    DIGUNAKAN UNTUK MEMBACA FILE GeoTIFF
    ============================================================ --}}
<script
    src="https://unpkg.com/georaster">
</script>


{{-- ============================================================
    GEORASTER LAYER FOR LEAFLET
    DIGUNAKAN UNTUK MENAMPILKAN GeoTIFF DI LEAFLET
    ============================================================ --}}
<script
    src="https://unpkg.com/georaster-layer-for-leaflet/dist/georaster-layer-for-leaflet.min.js">
</script>


<script>

function mapNganjuk(){

    return {

        /* ========================================================
           STATE
           ======================================================== */

        map:null,

        geojsonLayer:null,

        polygons:{},

        satelliteLayer:null,

        streetLayer:null,

        mapBaseLayer:'satellite',


        /*
         * Batas Kecamatan aktif dari awal.
         *
         * Probability juga langsung aktif supaya
         * GeoTIFF langsung terlihat ketika peta dibuka.
         */
        activeMapLayers:[
            'boundary',
            'probability'
        ],


        selectedDistrict:'all',

        selectedData:{},

        loading:true,

        mapError:'',

        initialized:false,


        /*
         * Data ML dari Laravel.
         */
        mlData:@json($mlResults ?? []),


        /* ========================================================
           GEOTIFF STATE
           ======================================================== */
            probabilityGeoRaster:null,

            candidateGeoRaster:null,

            probabilityRasterLayer:null,

            candidateRasterLayer:null,

            rasterLoaded:false,

            candidateRasterLoaded:false,


        /* ========================================================
           URL GEOTIFF
           ======================================================== */

        probabilityRasterUrl:
            "{{ asset('ml_model/data/XGBOOST_V4_1/Probability_Bawang_Nganjuk_4Kec_V4_1_STRICT.tif') }}",

        candidateRasterUrl:
            "{{ asset('ml_model/data/XGBOOST_V4_1/Candidate_Bawang_DOA_T050_V4_1.tif') }}",

        /* ========================================================
           START MAP
           ======================================================== */

            async startMap() {
                if (this.initialized) {
                    if (this.map) {
                        setTimeout(() => {
                            this.map.invalidateSize(true);
                        }, 100);
                    }
                    return;
                }

                this.initialized = true;
                this.loading = true;
                this.mapError = '';

                try {
                    console.log('=== MULAI MEMUAT PETA ===');

                    // Data awal
                    this.selectedData = this.getDistrictData('all');

                    // Cek Leaflet
                    if (typeof L === 'undefined') {
                        throw new Error(
                            'Library Leaflet belum berhasil dimuat.'
                        );
                    }

                    // Cek GeoRaster
                    if (typeof parseGeoraster !== 'function') {
                        throw new Error(
                            'Library GeoRaster belum berhasil dimuat.'
                        );
                    }

                    // Cek GeoRasterLayer
                    if (typeof GeoRasterLayer === 'undefined') {
                        throw new Error(
                            'Library GeoRasterLayer belum berhasil dimuat.'
                        );
                    }

                    // Cek container peta
                    const container = document.getElementById('leaflet-map');

                    if (!container) {
                        throw new Error(
                            'Element #leaflet-map tidak ditemukan.'
                        );
                    }

                    console.log('Container peta ditemukan.');

                    // =========================
                    // 1. INIT LEAFLET
                    // =========================
                    this.initMap(container);

                    console.log('Leaflet berhasil dibuat.');

                    // =========================
                    // 2. LOAD GEOJSON
                    // =========================
                    await this.loadBoundary();

                    console.log('GeoJSON berhasil dimuat.');
                    // =========================
                    // 3. LOAD GEOTIFF PROBABILITY
                    // =========================
                    await this.loadProbabilityRaster();

                    // =========================
                    // 4. LOAD GEOTIFF CANDIDATE
                    // =========================
                    await this.loadCandidateRaster();

                    // =========================
                    // 5. UPDATE DATA
                    // =========================
                    this.updateSelectedData('all');

                    // =========================
                    // 5. UPDATE VISIBILITY
                    // =========================
                    this.updateRasterVisibility();

                    // =========================
                    // SELESAI
                    // =========================
                    this.loading = false;

                    console.log('=== PETA SELESAI DIMUAT ===');

                    // Memastikan ukuran Leaflet benar
                    setTimeout(() => {
                        if (this.map) {
                            this.map.invalidateSize(true);
                        }
                    }, 500);

                } catch (error) {

                    console.error('==============================');
                    console.error('MAP ERROR');
                    console.error(error);
                    console.error('==============================');

                    this.mapError =
                        error?.message ||
                        'Terjadi kesalahan ketika memuat peta.';

                    this.loading = false;
                }
            },


        /* ========================================================
           NORMALIZE DISTRICT
           ======================================================== */

        normalizeDistrict(value){

            if(
                value===null ||
                value===undefined
            ){

                return '';
            }


            return String(value)
                .toLowerCase()
                .replace(/^kecamatan\s+/,'')
                .trim();
        },


        /* ========================================================
           CARI DATA ML
           ======================================================== */

        findMLData(id){

            if(!this.mlData){

                return null;
            }


            const normalizedId=
                this.normalizeDistrict(id);


            /*
             * FORMAT OBJECT
             *
             * {
             *   sukomoro:{...},
             *   bagor:{...}
             * }
             */
            if(
                !Array.isArray(this.mlData) &&
                typeof this.mlData==='object'
            ){

                if(this.mlData[id]){

                    return this.mlData[id];
                }


                for(
                    const key of Object.keys(
                        this.mlData
                    )
                ){

                    if(
                        this.normalizeDistrict(key)===
                        normalizedId
                    ){

                        return this.mlData[key];
                    }
                }
            }


            /*
             * FORMAT ARRAY.
             */
            if(
                Array.isArray(this.mlData)
            ){

                const found=
                    this.mlData.find(row=>{

                        if(
                            !row ||
                            typeof row!=='object'
                        ){

                            return false;
                        }


                        const district=
                            row.kecamatan ??
                            row.Kecamatan ??
                            row.KECAMATAN ??
                            row.nama_kecamatan ??
                            row.namaKecamatan ??
                            row.district ??
                            row.name;


                        return(
                            this.normalizeDistrict(
                                district
                            )===
                            normalizedId
                        );
                    });


                if(found){

                    return found;
                }
            }


            return null;
        },


        /* ========================================================
           DATA KECAMATAN
           ======================================================== */

        getDistrictData(id){

            /*
             * SEMUA KECAMATAN
             */
            if(id==='all'){

                return {

                    name:
                        'Semua Kecamatan',

                    status:
                        'Ringkasan seluruh wilayah penelitian',

                    status_badge:
                        'bg-brand-50 text-brand-700 border-brand-200',

                    area:
                        '33,224.52 ha',

                    plots:
                        'Seluruh wilayah',

                    yield_info:
                        'Hasil model ML',

                    ndvi:
                        'Data gabungan',

                    ndvi_sub:
                        'Sentinel-2',

                    moisture:
                        'Data gabungan',

                    moisture_sub:
                        'Data kelembapan'
                };
            }


            /*
             * FALLBACK
             */
            const fallback={

                name:
                    `Kecamatan ${
                        id.charAt(0).toUpperCase()+
                        id.slice(1)
                    }`,

                status:
                    'Data ML belum tersedia',

                status_badge:
                    'bg-slate-100 text-slate-700 border-slate-300',

                area:'-',

                plots:
                    'Data belum tersedia',

                yield_info:
                    'Belum ada prediksi',

                ndvi:'-',

                ndvi_sub:
                    'Data Sentinel-2 belum tersedia',

                moisture:'-',

                moisture_sub:
                    'Data kelembapan belum tersedia'
            };


            const ml=
                this.findMLData(id);


            if(!ml){

                return fallback;
            }


            return {

                ...fallback,

                ...ml,

                name:
                    ml.name ??
                    ml.nama ??
                    ml.nama_kecamatan ??
                    ml.kecamatan ??
                    fallback.name,

                status:
                    ml.status ??
                    fallback.status,

                area:
                    ml.area ??
                    ml.luas ??
                    fallback.area,

                plots:
                    ml.plots ??
                    ml.plot ??
                    ml.jumlah_plot ??
                    fallback.plots,

                yield_info:
                    ml.yield_info ??
                    ml.prediksi ??
                    ml.prediction ??
                    ml.yield ??
                    fallback.yield_info,

                ndvi:
                    ml.ndvi ??
                    ml.NDVI ??
                    fallback.ndvi,

                ndvi_sub:
                    ml.ndvi_sub ??
                    'Sentinel-2',

                moisture:
                    ml.moisture ??
                    ml.kelembapan ??
                    ml.kelengasan ??
                    fallback.moisture,

                moisture_sub:
                    ml.moisture_sub ??
                    'Data kelembapan'
            };
        },


        /* ========================================================
           UPDATE DATA TERPILIH
           ======================================================== */

        updateSelectedData(id){

            this.selectedDistrict=id;

            this.selectedData=
                this.getDistrictData(id);


            if(id==='all'){

                if(
                    this.geojsonLayer &&
                    this.map
                ){

                    const bounds=
                        this.geojsonLayer.getBounds();


                    if(bounds.isValid()){

                        this.map.fitBounds(
                            bounds,
                            {
                                padding:[
                                    20,
                                    20
                                ]
                            }
                        );
                    }
                }


                this.updateMapStyle();

                return;
            }


            this.highlightDistrict(id);
        },


        /* ========================================================
           SEMUA KECAMATAN
           ======================================================== */

        selectAllDistricts(){

            this.updateSelectedData(
                'all'
            );
        },


        /* ========================================================
           KECAMATAN
           ======================================================== */

        selectDistrict(id){

            const allowed=[
                'sukomoro',
                'bagor',
                'gondang',
                'rejoso'
            ];


            if(
                !allowed.includes(id)
            ){

                return;
            }


            this.updateSelectedData(id);
        },


        /* ========================================================
           TOGGLE LAYER
           ======================================================== */

        toggleMapLayer(layer){

            const allowed=[
                'boundary',
                'probability',
                'candidate'
            ];


            if(
                !allowed.includes(layer)
            ){

                return;
            }


            if(
                this.activeMapLayers.includes(
                    layer
                )
            ){

                this.activeMapLayers=
                    this.activeMapLayers.filter(
                        item=>item!==layer
                    );

            }else{

                this.activeMapLayers.push(
                    layer
                );
            }


            /*
             * Update GeoJSON boundary.
             */
            this.updateMapStyle();


            /*
             * Update GeoTIFF.
             */
            this.updateRasterVisibility();
        },


        /* ========================================================
           HAPUS LAYER
           ======================================================== */

        clearMapLayers(){

            this.activeMapLayers=[];

            this.updateMapStyle();

            this.updateRasterVisibility();
        },


        /* ========================================================
           CEK LAYER
           ======================================================== */

        isMapLayerActive(layer){

            return this.activeMapLayers.includes(
                layer
            );
        },


        /* ========================================================
           INIT MAP
           ======================================================== */

        initMap(container){

            /*
             * Kalau instance sudah ada,
             * jangan buat lagi.
             */
            if(this.map){

                this.map.invalidateSize(true);

                return;
            }


            /*
             * Pengaman tambahan:
             * Leaflet menyimpan instance pada element.
             */
            if(container.__leaflet_map){

                this.map=
                    container.__leaflet_map;

                this.map.invalidateSize(true);

                return;
            }


            /*
             * SATELIT
             */
            this.satelliteLayer=
                L.tileLayer(

                    'https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}',

                    {
                        attribution:
                            '© Esri, USGS, NOAA',

                        maxZoom:18
                    }
                );


            /*
             * JALAN
             */
            this.streetLayer=
                L.tileLayer(

                    'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',

                    {
                        attribution:
                            '© OpenStreetMap contributors',

                        maxZoom:19
                    }
                );


            /*
             * BUAT MAP
             */
            this.map=
                L.map(
                    container,
                    {

                        center:[
                            -7.535,
                            111.915
                        ],

                        zoom:12,

                        zoomControl:true,

                        layers:[
                            this.satelliteLayer
                        ]
                    }
                );


            /*
             * Simpan instance pada container.
             */
            container.__leaflet_map=
                this.map;
        },


        /* ========================================================
           LOAD GEOJSON
           ======================================================== */

        async loadBoundary(){

            try{

                const url=
                    "{{ asset('geojson/Batas_4_Kecamatan_Nganjuk.geojson') }}";


                console.log(
                    'Loading GeoJSON:',
                    url
                );


                const response=
                    await fetch(
                        url,
                        {
                            method:'GET',
                            cache:'no-cache'
                        }
                    );


                if(!response.ok){

                    throw new Error(
                        `GeoJSON gagal dimuat. HTTP ${response.status}`
                    );
                }


                const geojson=
                    await response.json();


                if(
                    !geojson ||
                    geojson.type!=='FeatureCollection' ||
                    !Array.isArray(
                        geojson.features
                    )
                ){

                    throw new Error(
                        'Format GeoJSON tidak valid.'
                    );
                }


                console.log(
                    'GeoJSON berhasil:',
                    geojson.features.length,
                    'features'
                );


                this.createGeoJsonLayer(
                    geojson
                );


            }catch(error){

                console.error(
                    'GEOJSON ERROR:',
                    error
                );


                this.mapError=
                    error?.message ||
                    'File batas wilayah tidak dapat dimuat.';


                this.loading=false;

                throw error;
            }
        },


        /* ========================================================
           LOAD GEOTIFF PROBABILITY
           ======================================================== */

  async loadProbabilityRaster() {

    console.log('==============================');
    console.log('MEMUAT GEOTIFF');
    console.log('==============================');

    console.log(
        'URL GeoTIFF:',
        this.probabilityRasterUrl
    );

    try {

        // =========================
        // CEK URL
        // =========================

        if (!this.probabilityRasterUrl) {
            throw new Error(
                'URL GeoTIFF kosong.'
            );
        }

        // =========================
        // FETCH GEOTIFF
        // =========================

        console.log(
            'Mengambil file GeoTIFF...'
        );

        const response = await fetch(
            this.probabilityRasterUrl,
            {
                method: 'GET',
                cache: 'no-store'
            }
        );

        console.log(
            'HTTP status:',
            response.status
        );

        console.log(
            'HTTP status text:',
            response.statusText
        );

        // =========================
        // CEK RESPONSE
        // =========================

        if (!response.ok) {

            throw new Error(
                `File GeoTIFF gagal diambil. ` +
                `HTTP ${response.status} ${response.statusText}`
            );
        }

        // =========================
        // CEK CONTENT TYPE
        // =========================

        console.log(
            'Content-Type:',
            response.headers.get('content-type')
        );

        // =========================
        // ARRAY BUFFER
        // =========================

        console.log(
            'Membaca file sebagai ArrayBuffer...'
        );

        const arrayBuffer =
            await response.arrayBuffer();

        console.log(
            'Ukuran GeoTIFF:',
            arrayBuffer.byteLength,
            'bytes'
        );

        if (!arrayBuffer.byteLength) {

            throw new Error(
                'File GeoTIFF kosong.'
            );
        }

        // =========================
        // PARSE GEORASTER
        // =========================

        console.log(
            'Memproses GeoTIFF dengan GeoRaster...'
        );

        this.probabilityGeoRaster =
            await parseGeoraster(arrayBuffer);

        // =========================
        // CEK HASIL PARSING
        // =========================

        if (!this.probabilityGeoRaster) {

            throw new Error(
                'GeoTIFF berhasil diambil tetapi gagal diproses oleh GeoRaster.'
            );
        }

        console.log(
            'GeoTIFF berhasil diproses.'
        );

        // =========================
        // INFORMASI GEOTIFF
        // =========================

        console.log(
            'GeoTIFF projection:',
            this.probabilityGeoRaster.projection
        );

        console.log(
            'GeoTIFF bounds:',
            {
                xmin:
                    this.probabilityGeoRaster.xmin,

                ymin:
                    this.probabilityGeoRaster.ymin,

                xmax:
                    this.probabilityGeoRaster.xmax,

                ymax:
                    this.probabilityGeoRaster.ymax
            }
        );

        console.log(
            'GeoTIFF dimensions:',
            {
                width:
                    this.probabilityGeoRaster.width,

                height:
                    this.probabilityGeoRaster.height,

                numberOfRasters:
                    this.probabilityGeoRaster.numberOfRasters
            }
        );

        console.log(
            'GeoTIFF noData:',
            this.probabilityGeoRaster.noDataValue
        );

        // =========================
        // CEK BAND
        // =========================

        if (
            !this.probabilityGeoRaster.values ||
            !this.probabilityGeoRaster.values.length
        ) {

            console.warn(
                'GeoTIFF tidak memiliki data raster yang dapat dibaca.'
            );
        }

        // =========================
        // LAYER PROBABILITY
        // =========================

        console.log(
            'Membuat layer probability...'
        );

        this.probabilityRasterLayer =
            new GeoRasterLayer({

                georaster:
                    this.probabilityGeoRaster,

                opacity: 0.82,

                resolution: 256,

                pixelValuesToColorFn: (values) => {

                    const value =
                        this.getRasterValue(values);

                    if (
                        value === null ||
                        value === undefined
                    ) {
                        return null;
                    }

                    // < 0.35 = MERAH
                    if (value < 0.35) {

                        return 'rgba(239,68,68,0.82)';
                    }

                    // 0.35 - < 0.70 = ORANYE
                    if (value < 0.70) {

                        return 'rgba(245,158,11,0.82)';
                    }

                    // >= 0.70 = HIJAU
                    return 'rgba(22,163,74,0.82)';
                }
            });



        // =========================
        // RASTER BERHASIL
        // =========================

        this.rasterLoaded = true;

        console.log(
            'GeoTIFF berhasil dibuat menjadi layer.'
        );

        // =========================
        // UPDATE VISIBILITY
        // =========================

        this.updateRasterVisibility();

        console.log(
            'Visibility raster berhasil diperbarui.'
        );

    } catch (error) {

        // =========================
        // GEOTIFF ERROR
        // =========================

        console.error(
            '=============================='
        );

        console.error(
            'GEOTIFF ERROR'
        );

        console.error(
            error
        );

        console.error(
            '=============================='
        );

        this.rasterLoaded = false;

        this.probabilityGeoRaster = null;

        this.probabilityRasterLayer = null;

        this.candidateRasterLayer = null;

        /*
         * Jangan membuat seluruh peta gagal
         * hanya karena GeoTIFF gagal.
         */

        console.warn(
            'GeoTIFF tidak dapat ditampilkan.'
        );

        console.warn(
            'GeoJSON tetap digunakan sebagai batas wilayah.'
        );

        return;
    }
},
/* ========================================================
   LOAD GEOTIFF CANDIDATE
   ======================================================== */

async loadCandidateRaster() {

    console.log('==============================');
    console.log('MEMUAT GEOTIFF CANDIDATE');
    console.log('==============================');

    console.log(
        'URL Candidate GeoTIFF:',
        this.candidateRasterUrl
    );

    try {

        // =========================
        // CEK URL
        // =========================

        if (!this.candidateRasterUrl) {

            throw new Error(
                'URL Candidate GeoTIFF kosong.'
            );
        }


        // =========================
        // FETCH CANDIDATE TIFF
        // =========================

        console.log(
            'Mengambil Candidate GeoTIFF...'
        );

        const response =
            await fetch(
                this.candidateRasterUrl,
                {
                    method: 'GET',
                    cache: 'no-store'
                }
            );


        console.log(
            'Candidate HTTP status:',
            response.status
        );


        console.log(
            'Candidate status text:',
            response.statusText
        );


        // =========================
        // CEK RESPONSE
        // =========================

        if (!response.ok) {

            throw new Error(
                `Candidate GeoTIFF gagal diambil. ` +
                `HTTP ${response.status} ${response.statusText}`
            );
        }


        // =========================
        // ARRAY BUFFER
        // =========================

        const arrayBuffer =
            await response.arrayBuffer();


        console.log(
            'Ukuran Candidate GeoTIFF:',
            arrayBuffer.byteLength,
            'bytes'
        );


        if (!arrayBuffer.byteLength) {

            throw new Error(
                'File Candidate GeoTIFF kosong.'
            );
        }


        // =========================
        // PARSE CANDIDATE
        // =========================

        console.log(
            'Memproses Candidate GeoTIFF...'
        );


        this.candidateGeoRaster =
            await parseGeoraster(
                arrayBuffer
            );


        // =========================
        // CEK HASIL
        // =========================

        if (!this.candidateGeoRaster) {

            throw new Error(
                'Candidate GeoTIFF gagal diproses oleh GeoRaster.'
            );
        }


        console.log(
            'Candidate GeoTIFF berhasil diproses.'
        );


        // =========================
        // INFO CANDIDATE
        // =========================

        console.log(
            'Candidate projection:',
            this.candidateGeoRaster.projection
        );


        console.log(
            'Candidate bounds:',
            {
                xmin:
                    this.candidateGeoRaster.xmin,

                ymin:
                    this.candidateGeoRaster.ymin,

                xmax:
                    this.candidateGeoRaster.xmax,

                ymax:
                    this.candidateGeoRaster.ymax
            }
        );


        console.log(
            'Candidate dimensions:',
            {
                width:
                    this.candidateGeoRaster.width,

                height:
                    this.candidateGeoRaster.height,

                numberOfRasters:
                    this.candidateGeoRaster.numberOfRasters
            }
        );


        console.log(
            'Candidate noData:',
            this.candidateGeoRaster.noDataValue
        );


        // =========================
        // BUAT LAYER CANDIDATE
        // =========================

        this.candidateRasterLayer =
            new GeoRasterLayer({

                /*
                 * PENTING:
                 *
                 * Candidate menggunakan
                 * candidateGeoRaster.
                 *
                 * BUKAN probabilityGeoRaster.
                 */

                georaster:
                    this.candidateGeoRaster,


                opacity:
                    0.88,


                resolution:
                    256,


                pixelValuesToColorFn:
                    (values) => {

                    if (
                        !values ||
                        !values.length
                    ) {

                        return null;
                    }


                    const rawValue =
                        Number(
                            values[0]
                        );


                    if (
                        !Number.isFinite(
                            rawValue
                        )
                    ) {

                        return null;
                    }


                    // =========================
                    // NODATA
                    // =========================

                    const noData =
                        this
                            .candidateGeoRaster
                            ?.noDataValue;


                    if (
                        noData !== undefined &&
                        noData !== null &&
                        Number(noData) === rawValue
                    ) {

                        return null;
                    }


                    // =========================
                    // NORMALISASI
                    // =========================

                    let value =
                        rawValue;


                    /*
                     * Jika nilai TIFF adalah
                     * 0 - 100, ubah menjadi
                     * 0 - 1.
                     */

                    if (
                        value > 1
                    ) {

                        value =
                            value / 100;
                    }


                    // =========================
                    // CANDIDATE >= 0.50
                    // =========================

                    if (
                        value >= 0.50
                    ) {

                        return 'rgba(168,85,247,0.90)';
                    }


                    /*
                     * Di bawah 0.50
                     * dibuat transparan.
                     */

                    return null;
                }

            });


        // =========================
        // CANDIDATE BERHASIL
        // =========================

        this.candidateRasterLoaded =
            true;


        console.log(
            'Candidate GeoTIFF berhasil menjadi layer.'
        );


        // =========================
        // UPDATE VISIBILITY
        // =========================

        this.updateRasterVisibility();


    } catch (error) {

        console.error(
            '=============================='
        );

        console.error(
            'CANDIDATE GEOTIFF ERROR'
        );

        console.error(
            error
        );

        console.error(
            '=============================='
        );


        this.candidateRasterLoaded =
            false;


        this.candidateGeoRaster =
            null;


        this.candidateRasterLayer =
            null;


        console.warn(
            'Candidate GeoTIFF tidak dapat ditampilkan.'
        );
    }
},

        /* ========================================================
           NORMALIZE NILAI RASTER
           ======================================================== */

        normalizeRasterProbability(value){

            if(
                value===null ||
                value===undefined
            ){

                return null;
            }


            let number=
                Number(value);


            if(
                !Number.isFinite(number)
            ){

                return null;
            }


            /*
             * Jika GeoTIFF menyimpan probability
             * sebagai 0 - 100, ubah menjadi 0 - 1.
             *
             * Contoh:
             *
             * 35 -> 0.35
             * 50 -> 0.50
             * 70 -> 0.70
             */
            if(number>1){

                number=
                    number/100;
            }


            /*
             * Pastikan tetap berada
             * pada rentang probability.
             */
            if(number<0){

                return null;
            }


            if(number>1){

                return null;
            }


            return number;
        },


        /* ========================================================
           GET RASTER VALUE
           ======================================================== */

        getRasterValue(values){

            if(
                !values ||
                !Array.isArray(values) ||
                values.length===0
            ){

                return null;
            }


            const rawValue=
                values[0];


            if(
                rawValue===null ||
                rawValue===undefined
            ){

                return null;
            }


            const number=
                Number(rawValue);


            if(
                !Number.isFinite(number)
            ){

                return null;
            }


            /*
             * Cek NoData GeoTIFF.
             */
            const noData=
                this.probabilityGeoRaster
                ?.noDataValue;


            if(
                noData!==undefined &&
                noData!==null
            ){

                const numericNoData=
                    Number(noData);


                if(
                    Number.isFinite(
                        numericNoData
                    ) &&
                    number===numericNoData
                ){

                    return null;
                }
            }


            return this.normalizeRasterProbability(
                number
            );
        },


        /* ========================================================
           UPDATE RASTER VISIBILITY
           ======================================================== */

        updateRasterVisibility(){

            if(!this.map){

                return;
            }


            /*
             * ================================================
             * PROBABILITY
             * ================================================
             */
            const probabilityActive =
                this.isMapLayerActive(
                    'probability'
                );


            if(
                probabilityActive &&
                this.rasterLoaded
            ){

                if(
                    this.probabilityRasterLayer &&
                    !this.map.hasLayer(
                        this.probabilityRasterLayer
                    )
                ){

                    this.probabilityRasterLayer.addTo(
                        this.map
                    );
                }

            }else{

                if(
                    this.probabilityRasterLayer &&
                    this.map.hasLayer(
                        this.probabilityRasterLayer
                    )
                ){

                    this.map.removeLayer(
                        this.probabilityRasterLayer
                    );
                }
            }


            /* ========================================================
            CANDIDATE
            ======================================================== */

            const candidateActive =
                this.isMapLayerActive(
                    'candidate'
                );


            if(
                candidateActive &&
                this.candidateRasterLoaded
            ){

                if(
                    this.candidateRasterLayer &&
                    !this.map.hasLayer(
                        this.candidateRasterLayer
                    )
                ){

                    this.candidateRasterLayer.addTo(
                        this.map
                    );
                }

            }else{

                if(
                    this.candidateRasterLayer &&
                    this.map.hasLayer(
                        this.candidateRasterLayer
                    )
                ){

                    this.map.removeLayer(
                        this.candidateRasterLayer
                    );
                }
            }


            /*
             * GeoJSON boundary harus berada
             * di atas raster agar garis batas
             * tetap terlihat.
             */
            if(
                this.geojsonLayer &&
                this.map.hasLayer(
                    this.geojsonLayer
                )
            ){

                this.geojsonLayer.bringToFront();
            }
        },


        /* ========================================================
           CREATE GEOJSON
           ======================================================== */

        createGeoJsonLayer(geojson){

            if(!this.map){

                throw new Error(
                    'Map belum dibuat.'
                );
            }


            /*
             * Hapus layer lama.
             */
            if(this.geojsonLayer){

                this.map.removeLayer(
                    this.geojsonLayer
                );

                this.geojsonLayer=null;
            }


            this.polygons={};


            /*
             * BUAT GEOJSON
             */
            this.geojsonLayer=
                L.geoJSON(

                    geojson,

                    {

                        style:(feature)=>{

                            const id=
                                this.getDistrictId(
                                    feature
                                );


                            const color=
                                this.getDistrictColor(
                                    id
                                );


                            return {

                                fillColor:
                                    color.fillColor,

                                color:
                                    color.color,

                                weight:2,

                                opacity:1,

                                fillOpacity:1
                            };
                        },


                        onEachFeature:
                            (feature,layer)=>{

                            const id=
                                this.getDistrictId(
                                    feature
                                );


                            this.polygons[id]=
                                layer;


                            /*
                             * POPUP
                             */
                            layer.bindPopup(

                                this.createPopup(
                                    id,
                                    this.getDistrictData(id),
                                    feature
                                ),

                                {
                                    className:
                                        'custom-popup',

                                    maxWidth:320
                                }
                            );


                            /*
                             * KLIK
                             */
                            layer.on(
                                'click',
                                ()=>{

                                    this.selectDistrict(
                                        id
                                    );


                                    layer.setPopupContent(

                                        this.createPopup(
                                            id,
                                            this.getDistrictData(id),
                                            feature
                                        )

                                    );


                                    layer.openPopup();
                                }
                            );


                            /*
                             * HOVER
                             */
                            layer.on(
                                'mouseover',
                                ()=>{

                                    layer.setStyle({

                                        weight:4,

                                        fillOpacity:
                                            this.getGeoJsonFillOpacity()
                                    });
                                }
                            );


                            /*
                             * MOUSEOUT
                             */
                            layer.on(
                                'mouseout',
                                ()=>{

                                    this.updateMapStyle();
                                }
                            );

                        }
                    }
                );


            /*
             * TAMBAHKAN KE MAP
             */
            this.geojsonLayer.addTo(
                this.map
            );


            /*
             * ZOOM KE WILAYAH
             */
            const bounds=
                this.geojsonLayer.getBounds();


            if(bounds.isValid()){

                this.map.fitBounds(
                    bounds,
                    {
                        padding:[
                            20,
                            20
                        ]
                    }
                );
            }


            /*
             * Terapkan style.
             */
            this.updateMapStyle();


            /*
             * Pastikan raster juga sesuai.
             */
            this.updateRasterVisibility();


            /*
             * Pastikan Leaflet menghitung ulang.
             */
            setTimeout(()=>{

                if(this.map){

                    this.map.invalidateSize(
                        true
                    );
                }

            },200);
        },


        /* ========================================================
           OPACITY GEOJSON
           ======================================================== */

        getGeoJsonFillOpacity(){

            const probabilityActive=
                this.isMapLayerActive(
                    'probability'
                );


            const candidateActive=
                this.isMapLayerActive(
                    'candidate'
                );


            /*
             * Jika raster aktif,
             * GeoJSON hanya menjadi batas.
             */
            if(
                probabilityActive ||
                candidateActive
            ){

                return 0.02;
            }


            return 0.4;
        },


        /* ========================================================
           GET DISTRICT ID
           ======================================================== */

        getDistrictId(feature){

            const props=
                feature?.properties || {};


            const keys=[
                'Kecamatan',
                'KECAMATAN',
                'kecamatan',
                'NAMOBJ',
                'WADMKC',
                'NAME_3',
                'name',
                'NAME'
            ];


            let value='';


            for(
                const key of keys
            ){

                if(
                    props[key]!==undefined &&
                    props[key]!==null
                ){

                    value=
                        String(
                            props[key]
                        )
                        .toLowerCase()
                        .trim();

                    break;
                }
            }


            if(
                value.includes('sukomoro')
            ){

                return 'sukomoro';
            }


            if(
                value.includes('bagor')
            ){

                return 'bagor';
            }


            if(
                value.includes('gondang')
            ){

                return 'gondang';
            }


            if(
                value.includes('rejoso')
            ){

                return 'rejoso';
            }


            return 'unknown';
        },


        /* ========================================================
           WARNA KECAMATAN
           ======================================================== */

        getDistrictColor(id){

            const colors={

                sukomoro:{
                    fillColor:'#4CAF50',
                    color:'#1B5E20'
                },

                bagor:{
                    fillColor:'#66BB6A',
                    color:'#1B5E20'
                },

                gondang:{
                    fillColor:'#FFA726',
                    color:'#E65100'
                },

                rejoso:{
                    fillColor:'#81C784',
                    color:'#2E7D32'
                }
            };


            return (
                colors[id] ||
                {
                    fillColor:'#90A4AE',
                    color:'#455A64'
                }
            );
        },


        /* ========================================================
           WARNA PROBABILITY
           ======================================================== */

        getProbabilityColor(value){

            if(
                value===null ||
                value===undefined ||
                !Number.isFinite(
                    Number(value)
                )
            ){

                return {

                    fillColor:'#CBD5E1',

                    color:'#64748B'
                };
            }


            /*
             * Normalisasi.
             *
             * Jika nilai 35 -> 0.35
             * Jika nilai 0.35 -> tetap 0.35
             */
            value=
                this.normalizeRasterProbability(
                    value
                );


            if(value===null){

                return {

                    fillColor:'#CBD5E1',

                    color:'#64748B'
                };
            }


            /*
             * < 0.35 = MERAH
             */
            if(value<0.35){

                return {

                    fillColor:'#EF4444',

                    color:'#B91C1C'
                };
            }


            /*
             * 0.35 - < 0.70
             * = KUNING / ORANYE
             */
            if(value<0.70){

                return {

                    fillColor:'#F59E0B',

                    color:'#D97706'
                };
            }


            /*
             * >= 0.70 = HIJAU
             */
            return {

                fillColor:'#16A34A',

                color:'#166534'
            };
        },


        /* ========================================================
           GET PROBABILITY
           ======================================================== */

        getFeatureProbability(
            feature,
            id
        ){

            const props=
                feature?.properties || {};


            const keys=[

                'probability',

                'probability_score',

                'prob_bawang',

                'prob_bawang_merah',

                'prediction_probability',

                'probabilitas'
            ];


            /*
             * Cek GeoJSON.
             */
            for(
                const key of keys
            ){

                if(
                    props[key]!==undefined &&
                    props[key]!==null
                ){

                    const value=
                        Number(
                            props[key]
                        );


                    if(
                        Number.isFinite(
                            value
                        )
                    ){

                        return this.normalizeRasterProbability(
                            value
                        );
                    }
                }
            }


            /*
             * Cek data ML.
             */
            const districtData=
                this.findMLData(id);


            if(districtData){

                for(
                    const key of keys
                ){

                    if(
                        districtData[key]!==undefined &&
                        districtData[key]!==null
                    ){

                        const value=
                            Number(
                                districtData[key]
                            );


                        if(
                            Number.isFinite(
                                value
                            )
                        ){

                            return this.normalizeRasterProbability(
                                value
                            );
                        }
                    }
                }
            }


            return null;
        },


        /* ========================================================
           FORMAT PROBABILITY
           ======================================================== */

        formatProbability(value){

            if(
                value===null ||
                value===undefined ||
                !Number.isFinite(
                    Number(value)
                )
            ){

                return '-';
            }


            let number=
                Number(value);


            /*
             * Normalisasi jika nilai
             * diberikan dalam bentuk persen.
             */
            if(number>1){

                number=
                    number/100;
            }


            return (
                number*100
            ).toFixed(1)+'%';
        },


        /* ========================================================
           STATUS PROBABILITY
           ======================================================== */

        getProbabilityStatus(value){

            if(
                value===null ||
                value===undefined
            ){

                return 'Data tidak tersedia';
            }


            value=
                this.normalizeRasterProbability(
                    value
                );


            if(value===null){

                return 'Data tidak tersedia';
            }


            /*
             * < 0.35
             */
            if(value<0.35){

                return 'Rendah';
            }


            /*
             * 0.35 - < 0.70
             */
            if(value<0.70){

                return 'Sedang';
            }


            /*
             * >= 0.70
             */
            return 'Tinggi';
        },


        /* ========================================================
           POPUP
           ======================================================== */

        createPopup(
            id,
            data,
            feature
        ){

            const name=
                data?.name ||
                `Kecamatan ${
                    id.charAt(0).toUpperCase()+
                    id.slice(1)
                }`;


            const status=
                data?.status ||
                'Data belum tersedia';


            const area=
                data?.area || '-';


            const plots=
                data?.plots || '-';


            const yieldInfo=
                data?.yield_info || '-';


            const ndvi=
                data?.ndvi || '-';


            const moisture=
                data?.moisture || '-';


            const probability=
                this.getFeatureProbability(
                    feature,
                    id
                );


            const probabilityText=
                this.formatProbability(
                    probability
                );


            const probabilityStatus=
                this.getProbabilityStatus(
                    probability
                );


            return `

                <div style="
                    width:280px;
                    font-family:Arial,sans-serif;
                    padding:16px;
                ">

                    <div style="
                        font-size:17px;
                        font-weight:800;
                        color:#1e293b;
                        margin-bottom:4px;
                    ">
                        ${name}
                    </div>


                    <div style="
                        font-size:11px;
                        color:#64748b;
                        margin-bottom:14px;
                    ">
                        ${status}
                    </div>


                    <div style="
                        background:#f0fdf4;
                        border:1px solid #bbf7d0;
                        border-radius:12px;
                        padding:10px;
                        margin-bottom:10px;
                    ">

                        <div style="
                            display:flex;
                            justify-content:space-between;
                            align-items:center;
                        ">

                            <div>

                                <div style="
                                    font-size:10px;
                                    color:#64748b;
                                ">
                                    Probability
                                </div>

                                <div style="
                                    font-size:17px;
                                    font-weight:800;
                                    color:#166534;
                                    margin-top:2px;
                                ">
                                    ${probabilityText}
                                </div>

                            </div>


                            <div style="
                                font-size:10px;
                                font-weight:700;
                                color:#166534;
                                background:#dcfce7;
                                padding:5px 8px;
                                border-radius:999px;
                            ">
                                ${probabilityStatus}
                            </div>

                        </div>

                    </div>


                    <div style="
                        display:grid;
                        grid-template-columns:1fr 1fr;
                        gap:7px;
                    ">


                        <div style="
                            background:#f8fafc;
                            padding:9px;
                            border-radius:9px;
                        ">

                            <div style="
                                font-size:9px;
                                color:#94a3b8;
                            ">
                                Luas
                            </div>

                            <div style="
                                font-size:11px;
                                font-weight:700;
                                color:#334155;
                                margin-top:2px;
                            ">
                                ${area}
                            </div>

                        </div>


                        <div style="
                            background:#f8fafc;
                            padding:9px;
                            border-radius:9px;
                        ">

                            <div style="
                                font-size:9px;
                                color:#94a3b8;
                            ">
                                Plot
                            </div>

                            <div style="
                                font-size:11px;
                                font-weight:700;
                                color:#334155;
                                margin-top:2px;
                            ">
                                ${plots}
                            </div>

                        </div>


                        <div style="
                            background:#f8fafc;
                            padding:9px;
                            border-radius:9px;
                            grid-column:span 2;
                        ">

                            <div style="
                                font-size:9px;
                                color:#94a3b8;
                            ">
                                Prediksi Model
                            </div>

                            <div style="
                                font-size:11px;
                                font-weight:700;
                                color:#334155;
                                margin-top:2px;
                            ">
                                ${yieldInfo}
                            </div>

                        </div>


                        <div style="
                            background:#f8fafc;
                            padding:9px;
                            border-radius:9px;
                        ">

                            <div style="
                                font-size:9px;
                                color:#94a3b8;
                            ">
                                NDVI
                            </div>

                            <div style="
                                font-size:11px;
                                font-weight:700;
                                color:#334155;
                                margin-top:2px;
                            ">
                                ${ndvi}
                            </div>

                        </div>


                        <div style="
                            background:#f8fafc;
                            padding:9px;
                            border-radius:9px;
                        ">

                            <div style="
                                font-size:9px;
                                color:#94a3b8;
                            ">
                                Kelembapan
                            </div>

                            <div style="
                                font-size:11px;
                                font-weight:700;
                                color:#334155;
                                margin-top:2px;
                            ">
                                ${moisture}
                            </div>

                        </div>

                    </div>

                </div>
            `;
        },


        /* ========================================================
           HIGHLIGHT KECAMATAN
           ======================================================== */

        highlightDistrict(id){

            if(
                !this.geojsonLayer ||
                !this.map
            ){

                return;
            }


            this.updateMapStyle();


            const polygon=
                this.polygons[id];


            if(!polygon){

                console.warn(
                    'Polygon tidak ditemukan:',
                    id
                );

                return;
            }


            /*
             * Jika raster probability/candidate aktif,
             * jangan membuat fill GeoJSON menutupi raster.
             * Hanya garis batas yang ditebalkan.
             */
            polygon.setStyle({

                color:'#111827',

                weight:4,

                fillOpacity:
                    this.getGeoJsonFillOpacity()
            });


            const bounds=
                polygon.getBounds();


            if(
                bounds &&
                bounds.isValid()
            ){

                this.map.fitBounds(
                    bounds,
                    {
                        maxZoom:13,
                        padding:[
                            20,
                            20
                        ]
                    }
                );
            }


            /*
             * Raster tetap berada di bawah
             * GeoJSON boundary.
             */
            this.updateRasterVisibility();
        },


        /* ========================================================
           UPDATE STYLE
           ======================================================== */

        updateMapStyle(){

            if(!this.geojsonLayer){

                return;
            }


            const boundaryActive=
                this.isMapLayerActive(
                    'boundary'
                );


            const probabilityActive=
                this.isMapLayerActive(
                    'probability'
                );


            const candidateActive=
                this.isMapLayerActive(
                    'candidate'
                );


            this.geojsonLayer.eachLayer(
                layer=>{

                    const feature=
                        layer.feature;


                    const id=
                        this.getDistrictId(
                            feature
                        );


                    const baseColor=
                        this.getDistrictColor(
                            id
                        );


                    /*
                     * =================================================
                     * TIDAK ADA LAYER
                     * =================================================
                     */
                    if(
                        !boundaryActive &&
                        !probabilityActive &&
                        !candidateActive
                    ){

                        layer.setStyle({

                            fillColor:'#E2E8F0',

                            color:'#94A3B8',

                            weight:1,

                            fillOpacity:0.03,

                            opacity:0.25
                        });
                    }


                    /*
                     * =================================================
                     * PROBABILITY ATAU CANDIDATE AKTIF
                     *
                     * GeoTIFF yang memberikan warna.
                     *
                     * GeoJSON dibuat transparan supaya tidak
                     * menutupi warna raster.
                     * =================================================
                     */
                    if(
                        probabilityActive ||
                        candidateActive
                    ){

                        layer.setStyle({

                            fillColor:'#FFFFFF',

                            color:'#1F2937',

                            weight:
                                boundaryActive
                                ? 2.5
                                : 1.5,

                            fillOpacity:0.01,

                            opacity:1
                        });
                    }


                    /*
                     * =================================================
                     * HANYA BOUNDARY
                     * =================================================
                     */
                    if(
                        boundaryActive &&
                        !probabilityActive &&
                        !candidateActive
                    ){

                        layer.setStyle({

                            fillColor:
                                baseColor.fillColor,

                            color:
                                baseColor.color,

                            weight:2,

                            fillOpacity:0.4,

                            opacity:1
                        });
                    }


                    /*
                     * =================================================
                     * BOUNDARY AKTIF BERSAMA RASTER
                     *
                     * Garis batas tetap terlihat.
                     * =================================================
                     */
                    if(
                        boundaryActive &&
                        (
                            probabilityActive ||
                            candidateActive
                        )
                    ){

                        layer.setStyle({

                            color:'#1F2937',

                            weight:2.5,

                            fillOpacity:0.01,

                            opacity:1
                        });
                    }


                    /*
                     * =================================================
                     * KECAMATAN TERPILIH
                     * =================================================
                     */
                    if(
                        id===
                        this.selectedDistrict
                    ){

                        layer.setStyle({

                            color:'#111827',

                            weight:4,

                            fillOpacity:
                                probabilityActive ||
                                candidateActive
                                ? 0.02
                                : 0.6
                        });
                    }

                }
            );


            /*
             * GeoJSON boundary harus berada di atas raster.
             */
            this.updateRasterVisibility();
        },


        /* ========================================================
           TOGGLE BASEMAP
           ======================================================== */

        toggleBaseLayer(){

            if(!this.map){

                return;
            }


            if(
                this.mapBaseLayer===
                'satellite'
            ){

                if(
                    this.map.hasLayer(
                        this.satelliteLayer
                    )
                ){

                    this.map.removeLayer(
                        this.satelliteLayer
                    );
                }


                this.streetLayer.addTo(
                    this.map
                );


                this.mapBaseLayer='street';


            }else{

                if(
                    this.map.hasLayer(
                        this.streetLayer
                    )
                ){

                    this.map.removeLayer(
                        this.streetLayer
                    );
                }


                this.satelliteLayer.addTo(
                    this.map
                );


                this.mapBaseLayer='satellite';
            }


            setTimeout(()=>{

                if(this.map){

                    this.map.invalidateSize(
                        true
                    );
                }

            },100);
        }

    };
}

</script>