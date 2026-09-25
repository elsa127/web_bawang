{{-- =========================================================
    LEAFLET CSS
    Library Leaflet untuk menampilkan peta interaktif.
========================================================= --}}
<link
    rel="stylesheet"
    href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
/>


{{-- =========================================================
    LEAFLET JS
    Diletakkan sebelum Alpine component agar object "L"
    sudah tersedia ketika x-init="init()" dijalankan.
========================================================= --}}
<script
    src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
></script>


{{-- =========================================================
    SECTION PETA
========================================================= --}}
<section
    id="peta"
    class="py-8 sm:py-12"
    x-data="mapNganjuk()"
    x-init="init()"
>

    <div
        class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6"
    >

        {{-- =====================================================
            HEADER SECTION
        ====================================================== --}}
        <div
            class="flex flex-col md:flex-row
                   md:items-end justify-between gap-4"
        >

            {{-- Judul dan deskripsi --}}
            <div>

                <div
                    class="flex items-center gap-2.5
                           text-brand-800 font-bold
                           text-xl sm:text-2xl tracking-tight"
                >

                    <span class="text-2xl">
                        🗺️
                    </span>

                    <h2>
                        Peta Lahan Bawang Merah
                        (Kabupaten Nganjuk)
                    </h2>

                </div>

                <p
                    class="text-slate-600
                           text-xs sm:text-sm mt-1"
                >
                    Pemantauan 4 kecamatan sentra bawang merah
                    berbasis citra satelit Sentinel-2,
                    BPS Nganjuk, dan Machine Learning.
                </p>

            </div>


            {{-- =================================================
                DROPDOWN PILIH KECAMATAN
            ================================================== --}}
            <div
                class="flex items-center gap-3"
            >

                <label
                    for="district-select"
                    class="text-xs font-semibold
                           text-slate-700
                           whitespace-nowrap"
                >
                    Pilih Kecamatan
                </label>

                <div
                    class="relative min-w-[170px]"
                >

                    <select
                        id="district-select"
                        x-model="selectedDistrict"
                        @change="selectDistrict($event.target.value)"
                        class="w-full bg-white
                               border border-slate-300
                               hover:border-brand-400
                               focus:ring-2
                               focus:ring-brand-700/20
                               focus:border-brand-700
                               rounded-xl px-3.5 py-2
                               text-xs sm:text-sm
                               font-semibold text-slate-800
                               shadow-xs appearance-none
                               cursor-pointer transition"
                    >

                        <option value="sukomoro">
                            Sukomoro
                        </option>

                        <option value="bagor">
                            Bagor
                        </option>

                        <option value="gondang">
                            Gondang
                        </option>

                        <option value="rejoso">
                            Rejoso
                        </option>

                    </select>


                    {{-- Icon dropdown --}}
                    <div
                        class="pointer-events-none
                               absolute inset-y-0 right-0
                               flex items-center px-3
                               text-slate-500"
                    >

                        <svg
                            class="w-4 h-4"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >

                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M19 9l-7 7-7-7"
                            />

                        </svg>

                    </div>

                </div>

            </div>

        </div>


        {{-- =====================================================
            TOMBOL KECAMATAN
        ====================================================== --}}
        <div
            class="flex flex-wrap
                   items-center gap-2
                   text-xs"
        >

            <span
                class="font-semibold
                       text-slate-500 mr-1"
            >
                Kecamatan Riset:
            </span>


            {{-- Sukomoro --}}
            <button
                type="button"
                @click="selectDistrict('sukomoro')"
                :class="
                    selectedDistrict === 'sukomoro'
                        ? 'bg-brand-800 text-white border-brand-800 font-bold'
                        : 'bg-white text-slate-700 border-slate-300 hover:border-brand-300'
                "
                class="px-3.5 py-1.5
                       rounded-full border
                       text-xs inline-flex
                       items-center gap-1
                       transition-colors"
            >

                <span
                    class="w-2 h-2 rounded-full
                           bg-emerald-500"
                ></span>

                Sukomoro

            </button>


            {{-- Bagor --}}
            <button
                type="button"
                @click="selectDistrict('bagor')"
                :class="
                    selectedDistrict === 'bagor'
                        ? 'bg-brand-800 text-white border-brand-800 font-bold'
                        : 'bg-white text-slate-700 border-slate-300 hover:border-brand-300'
                "
                class="px-3.5 py-1.5
                       rounded-full border
                       text-xs inline-flex
                       items-center gap-1
                       transition-colors"
            >

                <span
                    class="w-2 h-2 rounded-full
                           bg-emerald-400"
                ></span>

                Bagor

            </button>


            {{-- Gondang --}}
            <button
                type="button"
                @click="selectDistrict('gondang')"
                :class="
                    selectedDistrict === 'gondang'
                        ? 'bg-brand-800 text-white border-brand-800 font-bold'
                        : 'bg-white text-slate-700 border-slate-300 hover:border-brand-300'
                "
                class="px-3.5 py-1.5
                       rounded-full border
                       text-xs inline-flex
                       items-center gap-1
                       transition-colors"
            >

                <span
                    class="w-2 h-2 rounded-full
                           bg-amber-400"
                ></span>

                Gondang

            </button>


            {{-- Rejoso --}}
            <button
                type="button"
                @click="selectDistrict('rejoso')"
                :class="
                    selectedDistrict === 'rejoso'
                        ? 'bg-brand-800 text-white border-brand-800 font-bold'
                        : 'bg-white text-slate-700 border-slate-300 hover:border-brand-300'
                "
                class="px-3.5 py-1.5
                       rounded-full border
                       text-xs inline-flex
                       items-center gap-1
                       transition-colors"
            >

                <span
                    class="w-2 h-2 rounded-full
                           bg-emerald-300"
                ></span>

                Rejoso

            </button>


            {{-- Toggle satelit / jalan --}}
            <button
                type="button"
                @click="toggleLayer()"
                class="ml-auto px-3.5 py-1.5
                       rounded-full
                       border border-slate-300
                       bg-white text-slate-700
                       hover:border-brand-300
                       text-xs inline-flex
                       items-center gap-1.5
                       transition-colors"
            >

                <svg
                    class="w-3.5 h-3.5"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >

                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                    />

                </svg>

                <span
                    x-text="
                        mapLayer === 'satellite'
                            ? '🛣️ Tampilan Jalan'
                            : '🛰️ Tampilan Satelit'
                    "
                ></span>

            </button>

        </div>


        {{-- =====================================================
            CONTAINER PETA
        ====================================================== --}}
        <div
            class="relative
                   rounded-2xl sm:rounded-3xl
                   border border-rose-200/80
                   shadow-inner
                   overflow-hidden"
        >

            {{-- Badge wilayah aktif --}}
            <div
                class="absolute top-3 left-3 z-[999]
                       inline-flex items-center gap-2
                       px-3 py-1.5 rounded-lg
                       bg-white/95 backdrop-blur
                       border border-slate-200 shadow
                       text-xs font-semibold
                       text-slate-800
                       pointer-events-none"
            >

                <span
                    class="w-2 h-2 rounded-full
                           bg-brand-800
                           animate-pulse"
                ></span>

                Wilayah Riset:

                <span
                    class="text-brand-800 font-bold"
                    x-text="selectedData.name"
                ></span>

            </div>


            {{-- =================================================
                MAP LEAFLET
            ================================================== --}}
            <div
                id="leaflet-map"
                class="w-full"
                style="height: 500px; z-index: 1;"
            ></div>


            {{-- =================================================
                LOADING
            ================================================== --}}
            <div
                x-show="loading"
                x-transition
                class="absolute inset-0 z-[1000]
                       bg-white/70
                       backdrop-blur-sm
                       flex items-center
                       justify-center"
            >

                <div
                    class="bg-white
                           rounded-2xl
                           shadow-lg
                           px-5 py-4
                           text-sm
                           font-semibold
                           text-slate-700"
                >
                    Memuat batas kecamatan...
                </div>

            </div>


            {{-- =================================================
                ERROR MAP
            ================================================== --}}
            <div
                x-show="mapError"
                x-transition
                class="absolute
                       top-16 left-1/2
                       -translate-x-1/2
                       z-[1001]
                       bg-red-50
                       border border-red-200
                       text-red-700
                       rounded-xl
                       px-4 py-3
                       shadow-lg
                       text-xs
                       max-w-sm"
            >

                <div
                    class="font-bold mb-1"
                >
                    Peta gagal dimuat
                </div>

                <div
                    x-text="mapError"
                ></div>

            </div>


            {{-- =================================================
                LEGEND PETA
            ================================================== --}}
            <div
                class="absolute bottom-3 left-3 z-[999]
                       bg-white/95 backdrop-blur
                       rounded-xl
                       border border-slate-200
                       shadow
                       px-3 py-2.5
                       flex flex-col gap-1.5
                       text-xs font-medium
                       text-slate-700
                       pointer-events-none"
            >

                <div
                    class="text-[10px]
                           font-bold
                           text-slate-500
                           uppercase
                           tracking-wider
                           mb-0.5"
                >
                    Status Lahan
                </div>


                {{-- Kondisi baik --}}
                <div
                    class="flex items-center gap-2"
                >

                    <span
                        style="
                            width:14px;
                            height:14px;
                            background:rgba(76,175,80,0.5);
                            border:2px solid #1B5E20;
                            border-radius:3px;
                            display:inline-block;
                        "
                    ></span>

                    <span>
                        Kondisi Baik / Subur
                    </span>

                </div>


                {{-- Perlu pemantauan --}}
                <div
                    class="flex items-center gap-2"
                >

                    <span
                        style="
                            width:14px;
                            height:14px;
                            background:rgba(255,167,38,0.5);
                            border:2px solid #E65100;
                            border-radius:3px;
                            display:inline-block;
                        "
                    ></span>

                    <span>
                        Perlu Pemantauan
                    </span>

                </div>


                {{-- Sumber data --}}
                <div
                    class="mt-1 pt-1.5
                           border-t border-slate-200
                           text-[10px]
                           text-slate-400"
                >
                    © Esri Satellite |
                    OpenStreetMap |
                    Sentinel-2 |
                    BPS Nganjuk
                </div>

            </div>

        </div>


        {{-- =====================================================
            DETAIL WILAYAH
        ====================================================== --}}
        <div
            class="bg-white
                   border border-slate-200/90
                   rounded-2xl
                   p-5 sm:p-6
                   shadow-xs
                   space-y-4"
        >

            {{-- Header detail --}}
            <div
                class="flex flex-col
                       sm:flex-row
                       sm:items-center
                       justify-between
                       gap-2
                       border-b
                       border-slate-100
                       pb-3.5"
            >

                <div>

                    <span
                        class="text-[11px]
                               uppercase
                               tracking-wider
                               font-bold
                               text-slate-400"
                    >
                        Data Riset BPS & Sentinel-2
                    </span>

                    <h3
                        class="text-base sm:text-lg
                               font-bold
                               text-slate-900"
                    >

                        Kondisi Lahan:

                        <span
                            class="text-brand-800"
                            x-text="selectedData.name"
                        ></span>

                    </h3>

                </div>


                {{-- Status ML --}}
                <span
                    class="inline-flex
                           items-center
                           gap-1.5
                           px-3 py-1
                           rounded-full
                           text-xs
                           font-bold
                           border
                           self-start
                           sm:self-auto"
                    :class="selectedData.status_badge"
                >

                    <span
                        class="w-2 h-2
                               rounded-full
                               bg-current"
                    ></span>

                    <span
                        x-text="selectedData.status"
                    ></span>

                </span>

            </div>


            {{-- =================================================
                GRID DATA
            ================================================== --}}
            <div
                class="grid grid-cols-1
                       sm:grid-cols-2
                       lg:grid-cols-4
                       gap-3.5 sm:gap-4"
            >

                {{-- Luas --}}
                <div
                    class="bg-rose-50/50
                           hover:bg-rose-50
                           border border-rose-100
                           rounded-xl p-4
                           transition-colors"
                >

                    <span
                        class="text-xs
                               text-slate-500
                               font-medium block"
                    >
                        Luas Panen BPS Nganjuk
                    </span>

                    <span
                        class="text-lg sm:text-xl
                               font-extrabold
                               text-slate-900
                               block mt-1"
                        x-text="selectedData.area"
                    ></span>

                    <span
                        class="text-xs
                               text-slate-600
                               font-medium
                               mt-0.5
                               block"
                        x-text="selectedData.plots"
                    ></span>

                </div>


                {{-- Produktivitas --}}
                <div
                    class="bg-rose-50/50
                           hover:bg-rose-50
                           border border-rose-100
                           rounded-xl p-4
                           transition-colors"
                >

                    <span
                        class="text-xs
                               text-slate-500
                               font-medium block"
                    >
                        Produktivitas Panen
                    </span>

                    <span
                        class="text-base sm:text-lg
                               font-extrabold
                               text-brand-800
                               block mt-1"
                        x-text="selectedData.yield_info"
                    ></span>

                    <span
                        class="text-xs
                               text-slate-500
                               font-medium
                               mt-0.5
                               block"
                    >
                        BPS Aktual vs Prediksi XGBoost
                    </span>

                </div>


                {{-- NDVI --}}
                <div
                    class="bg-rose-50/50
                           hover:bg-rose-50
                           border border-rose-100
                           rounded-xl p-4
                           transition-colors"
                >

                    <span
                        class="text-xs
                               text-slate-500
                               font-medium block"
                    >
                        Kerapatan Vegetasi (NDVI)
                    </span>

                    <span
                        class="text-lg sm:text-xl
                               font-extrabold
                               text-slate-900
                               block mt-1"
                        x-text="selectedData.ndvi"
                    ></span>

                    <span
                        class="text-xs
                               text-emerald-700
                               font-medium
                               mt-0.5
                               block"
                        x-text="selectedData.ndvi_sub"
                    ></span>

                </div>


                {{-- Kelembapan --}}
                <div
                    class="bg-rose-50/50
                           hover:bg-rose-50
                           border border-rose-100
                           rounded-xl p-4
                           transition-colors"
                >

                    <span
                        class="text-xs
                               text-slate-500
                               font-medium block"
                    >
                        Kadar Air & Kelembapan
                    </span>

                    <span
                        class="text-lg sm:text-xl
                               font-extrabold
                               text-slate-900
                               block mt-1"
                        x-text="selectedData.moisture"
                    ></span>

                    <span
                        class="text-xs
                               text-slate-600
                               font-medium
                               mt-0.5
                               block"
                        x-text="selectedData.moisture_sub"
                    ></span>

                </div>

            </div>

        </div>

    </div>

</section>


<script>

/*
|--------------------------------------------------------------------------
| PETA NGANJUK
|--------------------------------------------------------------------------
|
| Komponen Alpine.js untuk:
|
| 1. Membuat peta Leaflet.
| 2. Memuat GeoJSON batas kecamatan.
| 3. Menampilkan 4 kecamatan penelitian.
| 4. Memilih kecamatan.
| 5. Highlight polygon.
| 6. Menampilkan popup.
| 7. Mengganti tampilan satelit / jalan.
| 8. Menampilkan data dari Laravel.
|
|--------------------------------------------------------------------------
| LOKASI DATA
|--------------------------------------------------------------------------
|
| Dataset Machine Learning:
|
|     ml_model/data/
|
| GeoJSON batas kecamatan:
|
|     public/geojson/
|     └── Batas_4_Kecamatan_Nganjuk.geojson
|
|--------------------------------------------------------------------------
*/

function mapNganjuk() {

    return {

        /* =====================================================
           STATE PETA
        ===================================================== */

        // Objek Leaflet
        map: null,

        // Layer GeoJSON
        geojsonLayer: null,

        // Polygon setiap kecamatan
        polygons: {},

        // Layer dasar aktif
        mapLayer: 'satellite',

        // Kecamatan yang sedang dipilih
        selectedDistrict: 'sukomoro',

        // Data kecamatan aktif
        selectedData: {},

        // Status loading
        loading: true,

        // Pesan error
        mapError: '',


        /* =====================================================
           LAYER DASAR LEAFLET
        ===================================================== */

        satelliteLayer: null,

        streetLayer: null,


        /* =====================================================
           DATA MACHINE LEARNING
        =====================================================

        ===================================================== */

        mlData: @json($mlResults ?? []),


        /* =====================================================
           INIT
        ===================================================== */

        async init() {

            /*
             * Pastikan Leaflet sudah tersedia.
             */
            if (
                typeof L === 'undefined'
            ) {

                this.loading = false;

                this.mapError =
                    'Library Leaflet belum berhasil dimuat.';

                console.error(
                    'Leaflet L tidak ditemukan.'
                );

                return;

            }


            /*
             * Pastikan elemen map tersedia.
             */
            const mapElement =
                document.getElementById(
                    'leaflet-map'
                );


            if (!mapElement) {

                this.loading = false;

                this.mapError =
                    'Elemen #leaflet-map tidak ditemukan.';

                console.error(
                    'Elemen #leaflet-map tidak ditemukan.'
                );

                return;

            }


            try {

                /*
                 * Buat peta.
                 */
                this.initMap();


                /*
                 * Muat GeoJSON.
                 */
                await this.loadBoundary();


                /*
                 * Pilih Sukomoro sebagai default.
                 */
                this.updateSelectedData(
                    'sukomoro'
                );


                /*
                 * Memaksa Leaflet menghitung ulang
                 * ukuran container.
                 *
                 * Ini membantu menghindari peta abu-abu /
                 * tile tidak muncul.
                 */
                setTimeout(() => {

                    if (this.map) {

                        this.map.invalidateSize();

                    }

                }, 250);

            } catch (error) {

                console.error(
                    'Inisialisasi peta gagal:',
                    error
                );

                this.loading = false;

                this.mapError =
                    error.message ||
                    'Peta gagal diinisialisasi.';

            }

        },


        /* =====================================================
           DATA KECAMATAN
        ===================================================== */

        getDistrictData(id) {

            /*
             * Ambil data dari $mlResults jika tersedia.
             */
            const data =
                this.mlData?.[id];


            /*
             * Jika data ML tidak tersedia,
             * gunakan data fallback.
             */
            if (!data) {

                const districtName =
                    id.charAt(0).toUpperCase() +
                    id.slice(1);

                return {

                    name:
                        'Kecamatan ' +
                        districtName,

                    status:
                        'Data ML belum tersedia',

                    status_badge:
                        'bg-slate-100 text-slate-700 border-slate-300',

                    area:
                        '-',

                    plots:
                        'Data belum tersedia',

                    yield_info:
                        'Belum ada prediksi',

                    ndvi:
                        '-',

                    ndvi_sub:
                        'Data Sentinel-2 belum tersedia',

                    moisture:
                        '-',

                    moisture_sub:
                        'Data kelembapan belum tersedia'

                };

            }


            /*
             * Jika data tersedia tetapi ada field kosong,
             * gunakan fallback per field.
             */
            return {

                name:
                    data.name ??
                    'Kecamatan ' +
                    id.charAt(0).toUpperCase() +
                    id.slice(1),

                status:
                    data.status ??
                    'Data tersedia',

                status_badge:
                    data.status_badge ??
                    'bg-slate-100 text-slate-700 border-slate-300',

                area:
                    data.area ??
                    '-',

                plots:
                    data.plots ??
                    '-',

                yield_info:
                    data.yield_info ??
                    'Belum tersedia',

                ndvi:
                    data.ndvi ??
                    '-',

                ndvi_sub:
                    data.ndvi_sub ??
                    'Data Sentinel-2 belum tersedia',

                moisture:
                    data.moisture ??
                    '-',

                moisture_sub:
                    data.moisture_sub ??
                    'Data kelembapan belum tersedia'

            };

        },


        /* =====================================================
           UPDATE KECAMATAN AKTIF
        ===================================================== */

        updateSelectedData(id) {

            /*
             * Simpan ID kecamatan.
             */
            this.selectedDistrict =
                id;


            /*
             * Ambil data kecamatan.
             */
            this.selectedData =
                this.getDistrictData(id);


            /*
             * Highlight polygon.
             */
            this.highlightDistrict(id);

        },


        /* =====================================================
           PILIH KECAMATAN
        ===================================================== */

        selectDistrict(id) {

            const allowedDistricts = [

                'sukomoro',
                'bagor',
                'gondang',
                'rejoso'

            ];


            /*
             * Cegah ID yang tidak dikenal.
             */
            if (
                !allowedDistricts.includes(id)
            ) {

                return;

            }


            /*
             * Update data.
             */
            this.updateSelectedData(id);

        },


        /* =====================================================
           MEMBUAT MAP
        ===================================================== */

        initMap() {

            /*
             * Jangan membuat map dua kali.
             */
            if (this.map) {

                return;

            }


            /* -------------------------------------------------
               SATELLITE
            ------------------------------------------------- */

            this.satelliteLayer =
                L.tileLayer(

                    'https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}',

                    {

                        attribution:
                            '© Esri, USGS, NOAA',

                        maxZoom:
                            18

                    }

                );


            /* -------------------------------------------------
               OPENSTREETMAP
            ------------------------------------------------- */

            this.streetLayer =
                L.tileLayer(

                    'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',

                    {

                        attribution:
                            '© OpenStreetMap contributors',

                        maxZoom:
                            19

                    }

                );


            /* -------------------------------------------------
               MEMBUAT MAP
            ------------------------------------------------- */

            this.map =
                L.map(

                    'leaflet-map',

                    {

                        center: [
                            -7.535,
                            111.915
                        ],

                        zoom:
                            12,

                        layers: [
                            this.satelliteLayer
                        ],

                        zoomControl:
                            true,

                        attributionControl:
                            true

                    }

                );

        },


        /* =====================================================
           MEMUAT GEOJSON
        ===================================================== */

        async loadBoundary() {

            this.mapError = '';


            try {

                /*
                 * GeoJSON disimpan di public/geojson.
                 *
                 * Browser mengaksesnya melalui:
                 *
                 * /geojson/Batas_4_Kecamatan_Nganjuk.geojson
                 */
                const response =
                    await fetch(

                        '/geojson/Batas_4_Kecamatan_Nganjuk.geojson',

                        {

                            method:
                                'GET',

                            headers: {

                                'Accept':
                                    'application/json'

                            },

                            cache:
                                'no-cache'

                        }

                    );


                /*
                 * Periksa HTTP status.
                 */
                if (!response.ok) {

                    throw new Error(

                        'HTTP ' +
                        response.status +
                        ' - File GeoJSON tidak ditemukan.'

                    );

                }


                /*
                 * Decode JSON.
                 */
                const geojson =
                    await response.json();


                /*
                 * Validasi struktur GeoJSON.
                 */
                if (
                    !geojson ||
                    geojson.type !== 'FeatureCollection' ||
                    !Array.isArray(
                        geojson.features
                    )
                ) {

                    throw new Error(
                        'Format GeoJSON tidak valid.'
                    );

                }


                /*
                 * Pastikan feature tersedia.
                 */
                if (
                    geojson.features.length === 0
                ) {

                    throw new Error(
                        'GeoJSON tidak memiliki feature.'
                    );

                }


                /*
                 * Buat layer.
                 */
                this.createGeoJsonLayer(
                    geojson
                );


                /*
                 * Loading selesai.
                 */
                this.loading =
                    false;

            } catch (error) {

                console.error(
                    'Gagal memuat GeoJSON:',
                    error
                );


                this.loading =
                    false;


                this.mapError =
                    error.message ||
                    'GeoJSON gagal dimuat.';

            }

        },


        /* =====================================================
           MEMBUAT LAYER GEOJSON
        ===================================================== */

        createGeoJsonLayer(geojson) {

            const self =
                this;


            /*
             * Reset polygon.
             */
            this.polygons = {};


            /*
             * Hapus layer lama.
             */
            if (
                this.geojsonLayer &&
                this.map
            ) {

                this.map.removeLayer(
                    this.geojsonLayer
                );

            }


            /*
             * Buat layer GeoJSON.
             */
            this.geojsonLayer =
                L.geoJSON(

                    geojson,

                    {

                        /* -------------------------------------
                           STYLE
                        ------------------------------------- */

                        style(feature) {

                            const id =
                                self.getDistrictId(
                                    feature
                                );


                            const color =
                                self.getDistrictColor(
                                    id
                                );


                            return {

                                color:
                                    color.stroke,

                                weight:
                                    2.5,

                                fillColor:
                                    color.fill,

                                fillOpacity:
                                    0.40

                            };

                        },


                        /* -------------------------------------
                           EVENT SETIAP FEATURE
                        ------------------------------------- */

                        onEachFeature(
                            feature,
                            layer
                        ) {

                            const id =
                                self.getDistrictId(
                                    feature
                                );


                            /*
                             * Hanya empat kecamatan
                             * penelitian yang diproses.
                             */
                            if (!id) {

                                return;

                            }


                            /*
                             * Simpan polygon.
                             */
                            self.polygons[id] =
                                layer;


                            /*
                             * Data kecamatan.
                             */
                            const data =
                                self.getDistrictData(
                                    id
                                );


                            /*
                             * Popup.
                             */
                            layer.bindPopup(

                                self.createPopup(
                                    id,
                                    data
                                ),

                                {

                                    maxWidth:
                                        320

                                }

                            );


                            /*
                             * Event click.
                             */
                            layer.on(
                                'click',
                                function() {

                                    /*
                                     * Pilih kecamatan.
                                     */
                                    self.selectDistrict(
                                        id
                                    );


                                    /*
                                     * Zoom ke polygon.
                                     */
                                    if (
                                        layer.getBounds()
                                            .isValid()
                                    ) {

                                        self.map.fitBounds(

                                            layer.getBounds(),

                                            {

                                                padding: [
                                                    30,
                                                    30
                                                ],

                                                maxZoom:
                                                    13

                                            }

                                        );

                                    }


                                    /*
                                     * Buka popup.
                                     */
                                    layer.openPopup();

                                }
                            );

                        }

                    }

                );


            /*
             * Tambahkan ke map.
             */
            this.geojsonLayer.addTo(
                this.map
            );


            /*
             * Ambil bounds.
             */
            const bounds =
                this.geojsonLayer.getBounds();


            /*
             * Zoom ke seluruh wilayah.
             */
            if (
                bounds &&
                bounds.isValid()
            ) {

                this.map.fitBounds(

                    bounds,

                    {

                        padding: [
                            20,
                            20
                        ]

                    }

                );

            }

        },


        /* =====================================================
           MENCARI ID KECAMATAN
        ===================================================== */

        getDistrictId(feature) {

            const properties =
                feature?.properties || {};


            /*
             * Beberapa kemungkinan nama property
             * dari file GeoJSON.
             */
            const rawName =

                properties.Kecamatan ??
                properties.KECAMATAN ??
                properties.kecamatan ??
                properties.NAMOBJ ??
                properties.WADMKC ??
                properties.NAME_3 ??
                properties.name ??
                '';


            const name =
                String(rawName)
                    .trim()
                    .toLowerCase();


            /*
             * Normalisasi nama kecamatan.
             */
            if (
                name.includes('sukomoro')
            ) {

                return 'sukomoro';

            }


            if (
                name.includes('bagor')
            ) {

                return 'bagor';

            }


            if (
                name.includes('gondang')
            ) {

                return 'gondang';

            }


            if (
                name.includes('rejoso')
            ) {

                return 'rejoso';

            }


            return null;

        },


        /* =====================================================
           WARNA KECAMATAN
        ===================================================== */

        getDistrictColor(id) {

            const colors = {

                sukomoro: {

                    fill:
                        '#4CAF50',

                    stroke:
                        '#1B5E20'

                },

                bagor: {

                    fill:
                        '#66BB6A',

                    stroke:
                        '#1B5E20'

                },

                gondang: {

                    fill:
                        '#FFA726',

                    stroke:
                        '#E65100'

                },

                rejoso: {

                    fill:
                        '#81C784',

                    stroke:
                        '#2E7D32'

                }

            };


            /*
             * Jika ID tidak ditemukan,
             * gunakan warna netral.
             */
            return (
                colors[id] || {

                    fill:
                        '#90A4AE',

                    stroke:
                        '#455A64'

                }
            );

        },


        /* =====================================================
           POPUP
        ===================================================== */

        createPopup(id, data) {

            const name =
                data?.name ??
                'Kecamatan';


            const status =
                data?.status ??
                'Belum tersedia';


            const yieldInfo =
                data?.yield_info ??
                '-';


            const ndvi =
                data?.ndvi ??
                '-';


            const moisture =
                data?.moisture ??
                '-';


            return `

                <div
                    style="
                        font-family: Arial, sans-serif;
                        min-width: 220px;
                        line-height: 1.5;
                    "
                >

                    <div
                        style="
                            font-size: 14px;
                            font-weight: 700;
                            margin-bottom: 10px;
                        "
                    >
                        ${name}
                    </div>


                    <div
                        style="
                            margin-bottom: 8px;
                        "
                    >

                        <span
                            style="
                                font-weight: 700;
                                color: #7A1B28;
                            "
                        >
                            Status ML:
                        </span>

                        <b>
                            ${status}
                        </b>

                    </div>


                    <div>
                        Produktivitas:
                        <b>
                            ${yieldInfo}
                        </b>
                    </div>


                    <div>
                        NDVI:
                        <b>
                            ${ndvi}
                        </b>
                    </div>


                    <div>
                        Kelembapan:
                        <b>
                            ${moisture}
                        </b>
                    </div>

                </div>

            `;

        },


        /* =====================================================
           HIGHLIGHT KECAMATAN
        ===================================================== */

        highlightDistrict(id) {

            /*
             * Reset seluruh polygon.
             */
            Object.entries(
                this.polygons
            ).forEach(
                ([districtId, polygon]) => {

                    const color =
                        this.getDistrictColor(
                            districtId
                        );


                    polygon.setStyle({

                        color:
                            color.stroke,

                        weight:
                            districtId === id
                                ? 4
                                : 2.5,

                        fillColor:
                            color.fill,

                        fillOpacity:
                            districtId === id
                                ? 0.60
                                : 0.40

                    });

                }
            );


            /*
             * Polygon yang aktif.
             */
            const polygon =
                this.polygons[id];


            if (!polygon) {

                return;

            }


            /*
             * Zoom hanya jika map tersedia.
             */
            if (
                this.map &&
                polygon.getBounds().isValid()
            ) {

                this.map.fitBounds(

                    polygon.getBounds(),

                    {

                        padding: [
                            30,
                            30
                        ],

                        maxZoom:
                            13

                    }

                );

            }


            /*
             * Buka popup.
             */
            polygon.openPopup();

        },


        /* =====================================================
           TOGGLE SATELLITE / STREET
        ===================================================== */

        toggleLayer() {

            /*
             * Pastikan map tersedia.
             */
            if (
                !this.map
            ) {

                return;

            }


            /*
             * SATELLITE -> STREET
             */
            if (
                this.mapLayer === 'satellite'
            ) {

                if (
                    this.map.hasLayer(
                        this.satelliteLayer
                    )
                ) {

                    this.map.removeLayer(
                        this.satelliteLayer
                    );

                }


                this.streetLayer.addTo(
                    this.map
                );


                this.mapLayer =
                    'street';


                return;

            }


            /*
             * STREET -> SATELLITE
             */
            if (
                this.mapLayer === 'street'
            ) {

                if (
                    this.map.hasLayer(
                        this.streetLayer
                    )
                ) {

                    this.map.removeLayer(
                        this.streetLayer
                    );

                }


                this.satelliteLayer.addTo(
                    this.map
                );


                this.mapLayer =
                    'satellite';

            }

        }

    };

}

</script>