<section id="hasil-penelitian" class="py-8 sm:py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        <div>
            <h2 class="text-xl sm:text-2xl font-bold text-brand-800">Hasil Penelitian Machine Learning</h2>
            <p class="text-base text-slate-600 mt-2">Model dilatih di Google Colab. Website membaca hasil pengolahan yang tersimpan; membuka halaman tidak menjalankan pelatihan ulang.</p>
        </div>

        {{-- Nilai yang tidak tersedia tetap ditandai kosong, bukan diganti angka perkiraan. --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
            <article class="bg-white border border-slate-200 rounded-2xl p-5 space-y-3">
                <h3 class="font-bold text-slate-900">Model pembuat peta</h3>
                <p class="text-base">{{ $researchResults['mapping']['Model'] ?? 'Data belum tersedia' }}</p>
                <p class="text-base text-slate-600">Balanced accuracy rata-rata:
                    <strong>
                        @isset($researchResults['repeated']['Strict']['Balanced_Accuracy_Mean'])
                            {{ number_format($researchResults['repeated']['Strict']['Balanced_Accuracy_Mean'] * 100, 2, ',', '.') }}%
                        @else
                            Data belum tersedia
                        @endisset
                    </strong>
                </p>
                <p class="text-sm text-slate-600">Rata-rata kemampuan mengenali kedua kelas, bawang dan bukan bawang. Evaluasi memakai 10 pengulangan validasi 4 bagian berdasarkan kelompok lokasi.</p>
                <p class="text-sm text-slate-500">Sumber: ringkasan pemetaan FASE 4B dan RepeatedCV V4.1.</p>
            </article>

            <article class="bg-white border border-slate-200 rounded-2xl p-5 space-y-3">
                <h3 class="font-bold text-slate-900">Model pembanding SELECTED</h3>
                <p class="text-base">Dataset: {{ $researchResults['selected']['Selected_Dataset'] ?? 'Data belum tersedia' }}</p>
                <p class="text-base text-slate-600">Accuracy validasi awal:
                    <strong>
                        @isset($researchResults['selected']['Selected_Metrics']['Accuracy'])
                            {{ number_format($researchResults['selected']['Selected_Metrics']['Accuracy'] * 100, 2, ',', '.') }}%
                        @else
                            Data belum tersedia
                        @endisset
                    </strong>
                </p>
                <p class="text-sm text-slate-600">Proporsi prediksi yang benar pada validasi 4 bagian. Tahap perbandingan awal memilih EXTENDED; evaluasi berulang berikutnya merekomendasikan STRICT untuk pemetaan. Angka kedua kartu berasal dari prosedur evaluasi yang berbeda.</p>
                <p class="text-sm text-slate-500">Sumber: metadata XGBoost V4.1 SELECTED.</p>
            </article>

            <article class="bg-white border border-slate-200 rounded-2xl p-5 space-y-3">
                <h3 class="font-bold text-slate-900">Luas kandidat setelah filter DOA</h3>
                <p class="text-xl font-bold text-brand-800">
                    @isset($researchResults['doa']['DOA_Filtered_T050_Ha'])
                        {{ number_format($researchResults['doa']['DOA_Filtered_T050_Ha'], 2, ',', '.') }} ha
                    @else
                        Data belum tersedia
                    @endisset
                </p>
                <p class="text-sm text-slate-600">Total empat kecamatan pada ambang probabilitas 0,50. DOA menyaring kandidat berdasarkan kemiripan fitur dengan referensi positif. Luas ini merupakan estimasi kandidat, bukan luas panen resmi atau kepastian tanaman di lapangan.</p>
                <p class="text-sm text-slate-500">Sumber: FASE_4D_DOA_Summary.json.</p>
            </article>
        </div>

        <details class="bg-white border border-slate-200 rounded-2xl p-5">
            <summary class="font-bold text-slate-900 cursor-pointer">Mengenal data penelitian</summary>
            <dl class="mt-4 space-y-4 text-base text-slate-600">
                <div><dt class="font-semibold text-slate-900">Dataset STRICT dan EXTENDED (.csv)</dt><dd>Contoh lokasi dengan label bawang atau bukan bawang dan delapan fitur Sentinel-2. File ini menjadi bahan pelatihan, bukan hasil prediksi peta. Sebagian referensi negatif berasal dari Dynamic World dan belum merupakan verifikasi lapangan independen.</dd></div>
                <div><dt class="font-semibold text-slate-900">Model XGBoost (.json)</dt><dd>Hasil pelatihan yang menyimpan aturan prediksi. STRICT FINAL digunakan untuk peta; SELECTED menyimpan model EXTENDED dari perbandingan awal. Model produktivitas disimpan terpisah dalam YIELD_PREDICTION.</dd></div>
                <div><dt class="font-semibold text-slate-900">FeatureStack (.tif)</dt><dd>Kumpulan fitur citra yang menjadi masukan model saat pemetaan di Colab.</dd></div>
                <div><dt class="font-semibold text-slate-900">Probability, Binary, dan Candidate DOA (.tif)</dt><dd>Hasil pemetaan: nilai probabilitas, kandidat pada ambang 0,50, dan kandidat setelah penyaringan DOA. Website menampilkan raster yang sudah tersedia.</dd></div>
                <div><dt class="font-semibold text-slate-900">Area, OOF, Fold Metrics, dan Feature Importance (.csv)</dt><dd>Ringkasan luas kandidat, prediksi pada data uji validasi, nilai evaluasi, dan kontribusi fitur. Gunakan berkas dari versi model yang sama agar interpretasinya sesuai.</dd></div>
                <div><dt class="font-semibold text-slate-900">Batas wilayah (.geojson) dan OFFICIAL_DATA_2025</dt><dd>Batas administrasi untuk peta serta data resmi sebagai pembanding. Luas panen tahunan dan luas kandidat citra memiliki makna yang berbeda.</dd></div>
                <div><dt class="font-semibold text-slate-900">YIELD_PREDICTION</dt><dd>Penelitian produktivitas dengan 12 pengamatan kecamatan-tahun pada 2023–2025. Prediksi temporal merupakan hasil uji antar tahun, bukan ramalan musim mendatang atau produktivitas per piksel. MAPE menyatakan besar kesalahan relatif, bukan akurasi klasifikasi.</dd></div>
                <div><dt class="font-semibold text-slate-900">V3, V4, dan PAPER_FINAL_V4</dt><dd>Arsip tahapan penelitian sebelumnya. Hasilnya perlu dibedakan dari keluaran pemetaan V4.1 yang ditampilkan saat ini.</dd></div>
            </dl>
        </details>
    </div>
</section>
