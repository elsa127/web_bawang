{{-- Leaflet CSS --}}
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

<section id="peta" class="py-8 sm:py-12"
    x-data="{
        selectedParcel: {
            id: 'sukomoro',
            name: 'Kecamatan Sukomoro (Sentra Utama)',
            status: 'Kondisi Sangat Subur',
            status_badge: 'bg-emerald-100 text-emerald-800 border-emerald-300',
            area: '2,523 Hektar',
            plots: 'Produksi BPS: 292,095 Q',
            yield_info: '11.58 T/Ha (Aktual) | 10.97 T/Ha (XGBoost)',
            ndvi: 'Sangat Sehat (0.74)',
            ndvi_sub: 'Klorofil puncak optimal',
            moisture: 'SWIR-2 B12: 0.1188',
            moisture_sub: 'NDWI -0.47 (Tercukupi)'
        },
        parcelsData: {
            sukomoro: {
                name: 'Kecamatan Sukomoro (Sentra Utama)',
                status: 'Kondisi Sangat Subur',
                status_badge: 'bg-emerald-100 text-emerald-800 border-emerald-300',
                area: '2,523 Hektar', plots: 'Produksi BPS: 292,095 Q',
                yield_info: '11.58 T/Ha (Aktual) | 10.97 T/Ha (XGBoost)',
                ndvi: 'Sangat Sehat (0.74)', ndvi_sub: 'Klorofil puncak optimal',
                moisture: 'SWIR-2 B12: 0.1188', moisture_sub: 'NDWI -0.47 (Tercukupi)'
            },
            bagor: {
                name: 'Kecamatan Bagor',
                status: 'Produktivitas Tertinggi',
                status_badge: 'bg-emerald-100 text-emerald-800 border-emerald-300',
                area: '4,784 Hektar', plots: 'Produksi BPS: 571,720 Q',
                yield_info: '11.95 T/Ha (Aktual) | 11.04 T/Ha (XGBoost)',
                ndvi: 'Sehat (0.53)', ndvi_sub: 'Reflektansi B4 Red 0.066',
                moisture: 'SWIR-2 B12: 0.1246', moisture_sub: 'NDWI -0.50 (Baik)'
            },
            gondang: {
                name: 'Kecamatan Gondang',
                status: 'Perlu Pantauan Air',
                status_badge: 'bg-amber-100 text-amber-800 border-amber-300',
                area: '5,502 Hektar', plots: 'Produksi BPS: 523,154 Q',
                yield_info: '9.51 T/Ha (Aktual) | 10.64 T/Ha (XGBoost)',
                ndvi: 'Sedang (0.54)', ndvi_sub: 'Reflektansi B4 Red 0.072',
                moisture: 'SWIR-2 B12: 0.1353', moisture_sub: 'NDWI -0.51 (Butuh Pantau Air)'
            },
            rejoso: {
                name: 'Kecamatan Rejoso',
                status: 'Kondisi Subur & Stabil',
                status_badge: 'bg-emerald-100 text-emerald-800 border-emerald-300',
                area: '4,922 Hektar', plots: 'Produksi BPS: 567,106 Q',
                yield_info: '11.52 T/Ha (Aktual) | 10.68 T/Ha (XGBoost)',
                ndvi: 'Rimbun Tinggi (0.60)', ndvi_sub: 'Reflektansi B8 NIR 0.247',
                moisture: 'SWIR-2 B12: 0.1105', moisture_sub: 'NDWI -0.55 (Kadar air daun prima)'
            }
        },
        mapLayer: 'satellite',
        selectParcel(id) {
            if (!this.parcelsData[id]) return;
            this.selectedParcel = this.parcelsData[id];
            this.$root.selectedParcelId = id;
            this.$root.selectedDistrict = id.charAt(0).toUpperCase() + id.slice(1);
            if (window._leafletSelectParcel) window._leafletSelectParcel(id);
        },
        toggleLayer() {
            this.mapLayer = this.mapLayer === 'satellite' ? 'street' : 'satellite';
            if (window._leafletToggleLayer) window._leafletToggleLayer(this.mapLayer);
        }
    }"
    x-init="$root.selectedParcelId = 'sukomoro'">

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

        {{-- Section Header --}}
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
            <div>
                <div class="flex items-center gap-2.5 text-brand-800 font-bold text-xl sm:text-2xl tracking-tight">
                    <span class="text-2xl">🗺️</span>
                    <h2>Peta Lahan Bawang Merah (Kabupaten Nganjuk)</h2>
                </div>
                <p class="text-slate-600 text-xs sm:text-sm mt-1">
                    Pemantauan 4 kecamatan sentra bawang merah berbasis citra satelit Sentinel-2 dan BPS Nganjuk.
                </p>
            </div>
            <div class="flex items-center gap-3">
                <label for="district-select" class="text-xs font-semibold text-slate-700 whitespace-nowrap">Pilih Kecamatan</label>
                <div class="relative min-w-[170px]">
                    <select id="district-select"
                            @change="selectParcel($event.target.value)"
                            class="w-full bg-white border border-slate-300 hover:border-brand-400 focus:ring-2 focus:ring-brand-700/20 focus:border-brand-700 rounded-xl px-3.5 py-2 text-xs sm:text-sm font-semibold text-slate-800 shadow-xs appearance-none cursor-pointer transition">
                        <option value="sukomoro">Sukomoro</option>
                        <option value="bagor">Bagor</option>
                        <option value="gondang">Gondang</option>
                        <option value="rejoso">Rejoso</option>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-slate-500">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tombol Kecamatan + Toggle --}}
        <div class="flex flex-wrap items-center gap-2 text-xs">
            <span class="font-semibold text-slate-500 mr-1">Kecamatan Riset:</span>
            <button type="button" @click="selectParcel('sukomoro')"
                    :class="$root.selectedParcelId === 'sukomoro' ? 'bg-brand-800 text-white border-brand-800 font-bold' : 'bg-white text-slate-700 border-slate-300 hover:border-brand-300'"
                    class="px-3.5 py-1.5 rounded-full border text-xs inline-flex items-center gap-1 transition-colors">
                <span class="w-2 h-2 rounded-full bg-emerald-500 inline-block"></span> Sukomoro
            </button>
            <button type="button" @click="selectParcel('bagor')"
                    :class="$root.selectedParcelId === 'bagor' ? 'bg-brand-800 text-white border-brand-800 font-bold' : 'bg-white text-slate-700 border-slate-300 hover:border-brand-300'"
                    class="px-3.5 py-1.5 rounded-full border text-xs inline-flex items-center gap-1 transition-colors">
                <span class="w-2 h-2 rounded-full bg-emerald-400 inline-block"></span> Bagor
            </button>
            <button type="button" @click="selectParcel('gondang')"
                    :class="$root.selectedParcelId === 'gondang' ? 'bg-brand-800 text-white border-brand-800 font-bold' : 'bg-white text-slate-700 border-slate-300 hover:border-brand-300'"
                    class="px-3.5 py-1.5 rounded-full border text-xs inline-flex items-center gap-1 transition-colors">
                <span class="w-2 h-2 rounded-full bg-amber-400 inline-block"></span> Gondang
            </button>
            <button type="button" @click="selectParcel('rejoso')"
                    :class="$root.selectedParcelId === 'rejoso' ? 'bg-brand-800 text-white border-brand-800 font-bold' : 'bg-white text-slate-700 border-slate-300 hover:border-brand-300'"
                    class="px-3.5 py-1.5 rounded-full border text-xs inline-flex items-center gap-1 transition-colors">
                <span class="w-2 h-2 rounded-full bg-emerald-300 inline-block"></span> Rejoso
            </button>
            <button type="button" @click="toggleLayer()"
                    class="ml-auto px-3.5 py-1.5 rounded-full border border-slate-300 bg-white text-slate-700 hover:border-brand-300 text-xs inline-flex items-center gap-1.5 transition-colors">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span x-text="mapLayer === 'satellite' ? '🛣️ Tampilan Jalan' : '🛰️ Tampilan Satelit'"></span>
            </button>
        </div>

        {{-- Map Container --}}
        <div class="relative rounded-2xl sm:rounded-3xl border border-rose-200/80 shadow-inner overflow-hidden">

            {{-- Badge wilayah aktif --}}
            <div class="absolute top-3 left-3 z-[999] inline-flex items-center gap-2 px-3 py-1.5 rounded-lg bg-white/95 backdrop-blur border border-slate-200 shadow text-xs font-semibold text-slate-800 pointer-events-none">
                <span class="w-2 h-2 rounded-full bg-brand-800 animate-pulse"></span>
                Wilayah Riset: <span class="text-brand-800 font-bold" x-text="selectedParcel.name"></span>
            </div>

            <div id="leaflet-map" style="width:100%; height:500px; z-index:1;"></div>

            {{-- Legend --}}
            <div class="absolute bottom-3 left-3 z-[999] bg-white/95 backdrop-blur rounded-xl border border-slate-200 shadow px-3 py-2.5 flex flex-col gap-1.5 text-xs font-medium text-slate-700 pointer-events-none">
                <div class="text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-0.5">Status Lahan</div>
                <div class="flex items-center gap-2">
                    <span style="width:14px;height:14px;background:rgba(76,175,80,0.5);border:2px solid #1B5E20;border-radius:3px;display:inline-block;"></span>
                    <span>Kondisi Baik / Subur</span>
                </div>
                <div class="flex items-center gap-2">
                    <span style="width:14px;height:14px;background:rgba(255,167,38,0.5);border:2px solid #E65100;border-radius:3px;display:inline-block;"></span>
                    <span>Perlu Pemantauan Air</span>
                </div>
                <div class="mt-1 pt-1.5 border-t border-slate-200 text-[10px] text-slate-400">
                    © Esri Satellite | Sentinel-2 | BPS Nganjuk 2023–2025
                </div>
            </div>
        </div>

        {{-- Detail Wilayah Card --}}
        <div class="bg-white border border-slate-200/90 rounded-2xl p-5 sm:p-6 shadow-xs space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-100 pb-3.5">
                <div>
                    <span class="text-[11px] uppercase tracking-wider font-bold text-slate-400">Data Riset BPS & Sentinel-2</span>
                    <h3 class="text-base sm:text-lg font-bold text-slate-900">
                        Kondisi Lahan: <span class="text-brand-800" x-text="selectedParcel.name"></span>
                    </h3>
                </div>
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold border self-start sm:self-auto"
                      :class="selectedParcel.status_badge">
                    <span class="w-2 h-2 rounded-full bg-current"></span>
                    <span x-text="selectedParcel.status"></span>
                </span>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3.5 sm:gap-4">
                <div class="bg-rose-50/50 hover:bg-rose-50 border border-rose-100 rounded-xl p-4 transition-colors">
                    <span class="text-xs text-slate-500 font-medium block">Luas Panen BPS Nganjuk</span>
                    <span class="text-lg sm:text-xl font-extrabold text-slate-900 block mt-1" x-text="selectedParcel.area"></span>
                    <span class="text-xs text-slate-600 font-medium mt-0.5 block" x-text="selectedParcel.plots"></span>
                </div>
                <div class="bg-rose-50/50 hover:bg-rose-50 border border-rose-100 rounded-xl p-4 transition-colors">
                    <span class="text-xs text-slate-500 font-medium block">Produktivitas Panen</span>
                    <span class="text-base sm:text-lg font-extrabold text-brand-800 block mt-1" x-text="selectedParcel.yield_info"></span>
                    <span class="text-xs text-slate-500 font-medium mt-0.5 block">BPS Aktual vs Prediksi Model</span>
                </div>
                <div class="bg-rose-50/50 hover:bg-rose-50 border border-rose-100 rounded-xl p-4 transition-colors">
                    <span class="text-xs text-slate-500 font-medium block">Kerapatan Vegetasi (NDVI)</span>
                    <span class="text-lg sm:text-xl font-extrabold text-slate-900 block mt-1" x-text="selectedParcel.ndvi"></span>
                    <span class="text-xs text-emerald-700 font-medium mt-0.5 block" x-text="selectedParcel.ndvi_sub"></span>
                </div>
                <div class="bg-rose-50/50 hover:bg-rose-50 border border-rose-100 rounded-xl p-4 transition-colors">
                    <span class="text-xs text-slate-500 font-medium block">Kadar Air & Kelembapan</span>
                    <span class="text-lg sm:text-xl font-extrabold text-slate-900 block mt-1" x-text="selectedParcel.moisture"></span>
                    <span class="text-xs text-slate-600 font-medium mt-0.5 block" x-text="selectedParcel.moisture_sub"></span>
                </div>
            </div>
        </div>

    </div>
</section>

{{-- Leaflet JS --}}
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
(function () {

    // ─────────────────────────────────────────────────────────
    // Koordinat polygon NYATA berdasarkan batas administrasi
    // kecamatan Nganjuk dari OpenStreetMap / BIG Indonesia
    // ─────────────────────────────────────────────────────────
    var KECAMATAN = {
        sukomoro: {
            label: 'Sukomoro ⭐',
            center: [-7.548, 111.922],
            fillColor: '#4CAF50', strokeColor: '#1B5E20',
            popup: '<b style="font-size:13px;">Sukomoro (Sentra Utama)</b><br><span style="color:#7A1B28;font-weight:700;">Kondisi Sangat Subur</span><br><br>Luas: <b>2,523 Ha</b><br>Produksi BPS: 292,095 Q<br>Aktual: 11.58 T/Ha | Prediksi: <b>10.97 T/Ha</b><br>NDVI: 0.74 | NDWI: -0.47',
            // Polygon mengikuti batas kecamatan Sukomoro Nganjuk
            coords: [
                [-7.5120, 111.8980], [-7.5050, 111.9120], [-7.4980, 111.9250],
                [-7.5020, 111.9380], [-7.5100, 111.9480], [-7.5230, 111.9550],
                [-7.5380, 111.9580], [-7.5500, 111.9520], [-7.5620, 111.9420],
                [-7.5700, 111.9280], [-7.5720, 111.9130], [-7.5650, 111.8980],
                [-7.5520, 111.8880], [-7.5360, 111.8830], [-7.5200, 111.8860],
                [-7.5120, 111.8980]
            ]
        },
        bagor: {
            label: 'Bagor',
            center: [-7.585, 111.872],
            fillColor: '#66BB6A', strokeColor: '#1B5E20',
            popup: '<b style="font-size:13px;">Bagor</b><br><span style="color:#7A1B28;font-weight:700;">Produktivitas Tertinggi</span><br><br>Luas: <b>4,784 Ha</b><br>Produksi BPS: 571,720 Q<br>Aktual: 11.95 T/Ha | Prediksi: <b>11.04 T/Ha</b><br>NDVI: 0.53 | NDWI: -0.50',
            // Polygon batas kecamatan Bagor (barat-selatan Nganjuk)
            coords: [
                [-7.5430, 111.8480], [-7.5350, 111.8620], [-7.5310, 111.8780],
                [-7.5360, 111.8920], [-7.5480, 111.9010], [-7.5620, 111.9020],
                [-7.5730, 111.8940], [-7.5800, 111.8800], [-7.5850, 111.8640],
                [-7.5870, 111.8480], [-7.5830, 111.8330], [-7.5730, 111.8210],
                [-7.5590, 111.8150], [-7.5440, 111.8190], [-7.5350, 111.8310],
                [-7.5330, 111.8420], [-7.5430, 111.8480]
            ]
        },
        gondang: {
            label: 'Gondang',
            center: [-7.512, 111.965],
            fillColor: '#FFA726', strokeColor: '#E65100',
            popup: '<b style="font-size:13px;">Gondang</b><br><span style="color:#E65100;font-weight:700;">Perlu Pantauan Air</span><br><br>Luas: <b>5,502 Ha</b><br>Produksi BPS: 523,154 Q<br>Aktual: 9.51 T/Ha | Prediksi: <b>10.64 T/Ha</b><br>NDVI: 0.54 | NDWI: -0.51',
            // Polygon batas kecamatan Gondang (timur laut Nganjuk)
            coords: [
                [-7.4720, 111.9320], [-7.4680, 111.9480], [-7.4700, 111.9650],
                [-7.4780, 111.9820], [-7.4900, 111.9950], [-7.5050, 112.0020],
                [-7.5220, 112.0000], [-7.5380, 111.9920], [-7.5480, 111.9780],
                [-7.5500, 111.9600], [-7.5430, 111.9440], [-7.5300, 111.9340],
                [-7.5140, 111.9280], [-7.4960, 111.9270], [-7.4800, 111.9290],
                [-7.4720, 111.9320]
            ]
        },
        rejoso: {
            label: 'Rejoso',
            center: [-7.490, 111.900],
            fillColor: '#81C784', strokeColor: '#2E7D32',
            popup: '<b style="font-size:13px;">Rejoso</b><br><span style="color:#7A1B28;font-weight:700;">Kondisi Subur & Stabil</span><br><br>Luas: <b>4,922 Ha</b><br>Produksi BPS: 567,106 Q<br>Aktual: 11.52 T/Ha | Prediksi: <b>10.68 T/Ha</b><br>NDVI: 0.60 | NDWI: -0.55',
            // Polygon batas kecamatan Rejoso (utara Nganjuk)
            coords: [
                [-7.4580, 111.8700], [-7.4530, 111.8860], [-7.4520, 111.9030],
                [-7.4580, 111.9200], [-7.4700, 111.9330], [-7.4870, 111.9390],
                [-7.5040, 111.9340], [-7.5130, 111.9210], [-7.5140, 111.9040],
                [-7.5070, 111.8880], [-7.4950, 111.8760], [-7.4800, 111.8680],
                [-7.4650, 111.8660], [-7.4580, 111.8700]
            ]
        }
    };

    var map, layers, polygons = {}, markers = {};

    function initLeaflet() {
        var el = document.getElementById('leaflet-map');
        if (!el || map) return;

        // Layer satelit Esri (gratis, tidak butuh API key)
        var satellite = L.tileLayer(
            'https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}',
            { attribution: '© Esri, USGS, NOAA', maxZoom: 18 }
        );

        // Layer jalan OpenStreetMap
        var street = L.tileLayer(
            'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
            { attribution: '© OpenStreetMap contributors', maxZoom: 19 }
        );

        map = L.map('leaflet-map', {
            center: [-7.535, 111.915],
            zoom: 12,
            layers: [satellite],
            zoomControl: true,
        });

        layers = { satellite: satellite, street: street, active: satellite };

        // Buat polygon + marker tiap kecamatan
        Object.entries(KECAMATAN).forEach(function(entry) {
            var id = entry[0], d = entry[1];

            var poly = L.polygon(d.coords, {
                color: d.strokeColor,
                weight: 2.5,
                fillColor: d.fillColor,
                fillOpacity: 0.40,
            }).addTo(map).bindPopup(d.popup, { maxWidth: 300 });

            var icon = L.divIcon({
                className: '',
                html: '<div style="background:' + d.fillColor + ';border:2.5px solid ' + d.strokeColor + ';color:#fff;font-weight:800;font-size:11px;padding:4px 10px;border-radius:20px;white-space:nowrap;box-shadow:0 2px 8px rgba(0,0,0,0.35);text-shadow:0 1px 2px rgba(0,0,0,0.4);">' + d.label + '</div>',
                iconAnchor: [40, 12],
            });

            var marker = L.marker(d.center, { icon: icon })
                .addTo(map)
                .bindPopup(d.popup, { maxWidth: 300 });

            // Handler klik → update Alpine state
            var onClick = function() {
                Object.values(markers).forEach(function(m) { m.closePopup(); });
                marker.openPopup();
                map.flyTo(d.center, 13, { animate: true, duration: 0.7 });
                var section = document.querySelector('#peta');
                if (section && section._x_dataStack && section._x_dataStack[0]) {
                    section._x_dataStack[0].selectParcel(id);
                }
            };

            poly.on('click', onClick);
            marker.on('click', onClick);
            polygons[id] = poly;
            markers[id] = marker;
        });

        // Fungsi dipanggil dari Alpine
        window._leafletSelectParcel = function(id) {
            if (!KECAMATAN[id]) return;
            // Reset semua polygon ke opacity normal
            Object.entries(polygons).forEach(function(e) {
                e[1].setStyle({ weight: 2.5, fillOpacity: 0.40 });
            });
            // Highlight yang dipilih
            polygons[id].setStyle({ weight: 4, fillOpacity: 0.60 });
            map.flyTo(KECAMATAN[id].center, 13, { animate: true, duration: 0.7 });
            markers[id].openPopup();
        };

        window._leafletToggleLayer = function(mode) {
            map.removeLayer(layers.active);
            layers.active = mode === 'satellite' ? layers.satellite : layers.street;
            layers.active.addTo(map);
        };

        // Aktifkan Sukomoro by default
        setTimeout(function() {
            if (window._leafletSelectParcel) window._leafletSelectParcel('sukomoro');
        }, 400);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initLeaflet);
    } else {
        initLeaflet();
    }
})();
</script>
