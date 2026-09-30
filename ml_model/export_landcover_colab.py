"""Jalankan di Colab setelah login Earth Engine dan memasang Google Drive.

Contoh:
%run export_landcover_colab.py --project ID_PROYEK --boundary /content/drive/MyDrive/GEE/OFFICIAL_DATA_2025/Batas_4_Kecamatan_Nganjuk.geojson

Tunggu kedua ekspor Drive selesai. Salin LandCover_Eligibility_2025.tif dan
Sentinel2_RGB_CloudMasked_2025.tif ke
ml_model/data/XGBOOST_V4_1/, lalu jalankan generator PNG lokal.
Ambang 0.60 adalah pilihan penyaringan awal yang perlu divalidasi lapangan.
Sumber: https://developers.google.com/earth-engine/datasets/catalog/GOOGLE_DYNAMICWORLD_V1
"""
import argparse
import json
from pathlib import Path


def main():
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument('--project', required=True)
    parser.add_argument('--boundary', required=True)
    parser.add_argument('--start', default='2025-07-01')
    parser.add_argument('--end', default='2025-09-01', help='Tanggal akhir eksklusif')
    parser.add_argument('--confidence', type=float, default=0.60)
    args = parser.parse_args()
    if not 0.5 < args.confidence <= 1:
        parser.error('Confidence harus lebih dari 0.5 dan maksimal 1.')
    import ee
    ee.Initialize(project=args.project)
    boundary = json.loads(Path(args.boundary).read_text(encoding='utf-8-sig'))
    region = ee.FeatureCollection(boundary).geometry()
    collection = ee.ImageCollection('GOOGLE/DYNAMICWORLD/V1').filterBounds(region).filterDate(args.start, args.end)
    if collection.size().getInfo() == 0:
        raise ValueError('Tidak ada citra Dynamic World pada periode dan wilayah ini.')
    bands = ['water', 'trees', 'grass', 'flooded_vegetation', 'crops', 'shrub_and_scrub', 'built', 'bare', 'snow_and_ice']
    probabilities = collection.select(bands).mean()
    confidence = probabilities.reduce(ee.Reducer.max())
    # 1 = pertanian berkeyakinan tinggi, 0 = nonpertanian berkeyakinan tinggi.
    # 255 = belum pasti/tidak ada data, tidak boleh dianggap bukan kandidat.
    eligibility = (probabilities.select('crops').gte(args.confidence)
                   .updateMask(confidence.gte(args.confidence))
                   .rename('eligibility').unmask(255).toUint8().clip(region))
    # Mask diterapkan pada citra asli, sebelum median dan tanpa menskalakan SCL.
    def mask_clouds(image):
        clear = image.select('SCL').remap([4, 5, 6], [1, 1, 1], 0)
        edges = image.select('B8A').mask().And(image.select('B9').mask())
        return image.select(['B4', 'B3', 'B2']).updateMask(clear).updateMask(edges)

    sentinel = (ee.ImageCollection('COPERNICUS/S2_SR_HARMONIZED')
                .filterBounds(region).filterDate(args.start, args.end))
    if sentinel.size().getInfo() == 0:
        raise ValueError('Tidak ada citra Sentinel-2 pada periode ini.')
    rgb = (sentinel.map(mask_clouds).median().unmask(65535)
           .toUint16().clip(region))
    exports = [
        ('LandCover_Eligibility_2025', eligibility, 255),
        ('Sentinel2_RGB_CloudMasked_2025', rgb, 65535),
    ]
    for name, image, nodata in exports:
        task = ee.batch.Export.image.toDrive(
            image=image, description=name, folder='GEE_MAP_REPAIR', fileNamePrefix=name,
            region=region, scale=10, crs='EPSG:32749', maxPixels=1e10,
            fileFormat='GeoTIFF', formatOptions={'noData': nodata})
        task.start()
        print(json.dumps({'task_id': task.id, 'file': name + '.tif',
                          'start': args.start, 'end_exclusive': args.end,
                          'confidence': args.confidence, 'status': 'SUBMITTED'}, indent=2))
    print('Selesaikan pemeriksaan lapangan dan kecocokan periode sebelum menggunakan hasil sebagai peta akhir.')


if __name__ == '__main__':
    main()
