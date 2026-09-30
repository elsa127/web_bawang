<style>
.research-tables table { width:100%; border-collapse:collapse; margin:12px 0 18px; font-size:14px; }
.research-tables th,.research-tables td { padding:10px; border-bottom:1px solid #dbe2ea; text-align:right; font-variant-numeric:tabular-nums; }
.research-tables th:first-child,.research-tables td:first-child { text-align:left; }
.research-tables th { background:#f1f5f9; }
.research-tables p,.report-sources { font-size:13px; line-height:1.6; color:#475569; }
#research-report-shell article { max-width:1000px; margin:24px auto; background:white; padding:32px; }
@media print {
    @page { size:A4 portrait; margin:15mm; }
    body { display:block!important; background:white!important; }
    body > :not(#research-report-shell) { display:none!important; }
    #research-report-shell { display:block!important; position:static!important; overflow:visible!important; background:white!important; }
    #research-report-shell article { margin:0; padding:0; max-width:none; }
    #research-report-shell .no-print { display:none!important; }
    .research-tables table { font-size:9pt; }
    .research-tables th,.research-tables td { padding:5px; }
    .research-tables p,.report-sources { font-size:9pt; overflow-wrap:anywhere; }
    .research-tables thead { display:table-header-group; }
    .research-tables tr { break-inside:avoid; }
    .research-tables h3 { break-after:avoid; margin-top:18px; }
}
</style>
<div id="research-report-shell" x-show="isReportModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60" role="dialog" aria-modal="true" :aria-labelledby="'report-title-' + reportDistrict" @keydown.escape.window="isReportModalOpen = false">
    @foreach($districtReports as $districtKey => $districtReport)
    <article x-show="reportDistrict === '{{ $districtKey }}'" @if($districtKey !== $activeDistrict) style="display:none" @endif>
        <div class="no-print flex flex-wrap justify-between gap-3 mb-6">
            <button type="button" @click="isReportModalOpen = false" class="rounded-lg border px-4 py-2">Tutup</button>
            <button type="button" onclick="window.print()" class="rounded-lg bg-brand-800 px-4 py-2 text-white">Cetak / Simpan sebagai PDF</button>
            <p class="w-full text-sm text-slate-600">Pilih tujuan &quot;Simpan sebagai PDF&quot;, ukuran A4. Matikan header/footer bawaan browser agar alamat halaman tidak ikut tercetak.</p>
        </div>
        <header class="mb-6 border-b pb-5">
            <p>ShallotWatch | HASIL PENELITIAN</p>
            <h1 id="report-title-{{ $districtKey }}" class="text-2xl font-bold">Laporan Data dan Evaluasi Machine Learning</h1>
            <p>Wilayah: <strong>{{ $districtReport['scope'] }}</strong> | Periode dataset: 2023-2025</p>
            <p>Dicetak: {{ now()->timezone('Asia/Jakarta')->format('d-m-Y H:i') }} WIB | {{ count($districtReport['rows']) }} observasi ditampilkan</p>
        </header>
        @include('partials.research-report-data', ['reportData' => $districtReport])
    </article>
    @endforeach
</div>
