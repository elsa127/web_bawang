# Folder Machine Learning: Riset Bawang Merah Nganjuk

Dokumentasi berkas model dan data penelitian berbasis Google Colab: **`Salinan_dari_qonita_bawang_merah.ipynb`** yang telah disinkronisasikan ke dalam sistem dasbor SI Bawang Merah.

---

## 1. Wilayah Riset (4 Kecamatan Sentra Nganjuk)
Data resmi bersumber dari BPS Kabupaten Nganjuk (2023–2025):
- **Sukomoro** (Sentra Utama): Luas panen 2,523 Ha | Produksi 292,095 Q | Produktivitas BPS: 11.58 Ton/Ha
- **Bagor**: Luas panen 4,784 Ha | Produksi 571,720 Q | Produktivitas BPS: 11.95 Ton/Ha
- **Gondang**: Luas panen 5,502 Ha | Produksi 523,154 Q | Produktivitas BPS: 9.51 Ton/Ha
- **Rejoso**: Luas panen 4,922 Ha | Produksi 567,106 Q | Produktivitas BPS: 11.52 Ton/Ha

---

## 2. 8 Fitur Spektral Citra Satelit Sentinel-2 (Feature Importance)
Tingkat pengaruh terhadap hasil panen bawang merah berdasarkan model XGBoost terlatih:
| Fitur | Deskripsi Band | Peran Agronomi | Bobot Pengaruh |
|---|---|---|---|
| **B12** | SWIR-2 | Kandungan Air & Bahan Kering Daun/Tanah | **24.18%** |
| **B4** | Red | Penyerapan Klorofil Merah | **23.92%** |
| **B11** | SWIR-1 | Kelembapan Tajuk Tanaman | **12.07%** |
| **B2** | Blue | Reflektansi Spektral Biru | **11.77%** |
| **NDWI** | Water Index | Indeks Kelembapan Kanopi | **9.34%** |
| **B8** | NIR | Struktur Seluler & Biomassa Daun | **7.24%** |
| **B3** | Green | Pantulan Pigmen Hijau | **6.30%** |
| **NDVI** | Vegetation Index | Kerapatan Vegetasi & Klorofil | **5.17%** |

---

## 3. Hasil Evaluasi Model XGBoost Regressor
- **Validasi Temporal (Leave-One-Year-Out):**
  - **MAE:** $8.829\text{ q/ha}$ ($\approx 0.88\text{ Ton/Ha}$)
  - **RMSE:** $11.629\text{ q/ha}$ ($\approx 1.16\text{ Ton/Ha}$)
  - **MAPE:** $9.27\%$ (**Tingkat Akurasi $\approx 90.73\% - 92\%$**)
  - **$R^2$:** $0.228$
- **Hyperparameter Model (XGB_PARAMS):**
  - `n_estimators`: 100
  - `max_depth`: 2
  - `learning_rate`: 0.03
  - `min_child_weight`: 2
  - `subsample`: 0.80
  - `colsample_bytree`: 0.80
  - `gamma`: 0.10
  - `reg_alpha`: 0.50
  - `reg_lambda`: 5.0
  - `objective`: `reg:squarederror`

---

## 4. Struktur Berkas
- `ml_model/notebooks/`: Berisi file notebook utama dari Colab (`Salinan_dari_qonita_bawang_merah.ipynb`).
- `ml_model/data/Yield_Dataset_Nganjuk_2023_2025.csv`: Dataset tabel 12 observasi tahunan Nganjuk.
- `ml_model/data/feature_importance.json`: Peringkat bobot fitur prediktor Sentinel-2.
- `ml_model/data/model_metrics.json`: Metrik evaluasi akurasi model.
