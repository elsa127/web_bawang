"""Siapkan peta web: python ml_model/generate_map_overlays.py .

Raster asli tetap utuh. Jalankan ulang setelah mengganti hasil Colab.
"""
import hashlib
import json
import sys
from pathlib import Path

import numpy as np
import rasterio
from PIL import Image
from rasterio.features import geometry_mask
from rasterio.warp import Resampling, calculate_default_transform, reproject, transform_bounds, transform_geom

RASTERS = {
    'probability': 'Probability_Bawang_Nganjuk_4Kec_V4_1_STRICT.tif',
    'candidate': 'Binary_Bawang_Nganjuk_4Kec_V4_1_STRICT_T050.tif',
    'candidate_doa': 'Candidate_Bawang_DOA_T050_V4_1.tif',
}


def classify(data, valid, inside, layer):
    """Abu-abu menandai NoData; transparan berarti bukan kandidat atau di luar wilayah."""
    codes = np.zeros(data.shape, dtype=np.uint8)
    codes[inside & ~valid] = 4
    if layer == 'probability':
        codes[inside & valid & (data < 0.35)] = 1
        codes[inside & valid & (data >= 0.35) & (data < 0.70)] = 2
        codes[inside & valid & (data >= 0.70)] = 3
    else:
        codes[inside & valid & (data == 1)] = 1
    return codes


def filter_candidate(data, valid, eligibility):
    """Hanya kelas pertanian yang diterima; tutupan lahan tak pasti tetap NoData."""
    filtered_valid = valid & (eligibility != 255)
    filtered = ((data == 1) & (eligibility == 1)).astype(np.uint8)
    return filtered, filtered_valid


def save_overlay(codes, source, path, layer):
    """Web Mercator menyamakan posisi piksel dengan proyeksi basemap Leaflet."""
    transform, width, height = calculate_default_transform(
        source.crs, 'EPSG:3857', source.width, source.height, *source.bounds)
    projected = np.zeros((height, width), dtype=np.uint8)
    reproject(codes, projected, src_transform=source.transform, src_crs=source.crs,
              dst_transform=transform, dst_crs='EPSG:3857',
              resampling=Resampling.nearest, src_nodata=0, dst_nodata=0)
    palette = np.zeros((5, 4), dtype=np.uint8)
    palette[4] = [100, 116, 139, 190]
    if layer == 'probability':
        palette[1:4] = [[239, 68, 68, 180], [245, 158, 11, 180], [22, 163, 74, 180]]
    else:
        palette[1] = [168, 85, 247, 191] if layer == 'candidate' else [8, 145, 178, 204]
    Image.fromarray(palette[projected]).save(path)
    west, south, east, north = transform_bounds(
        'EPSG:3857', 'EPSG:4326', *rasterio.transform.array_bounds(height, width, transform))
    return [[south, west], [north, east]]


def satellite_rgba(rgb, valid):
    """RGB Sentinel ekspor memakai DN 0?10000; NoData tetap transparan."""
    rgba = np.zeros((*valid.shape, 4), dtype=np.uint8)
    colors = np.power(np.clip(np.nan_to_num(rgb) / 3000.0, 0, 1), 1 / 1.3)
    rgba[:, :, :3] = np.moveaxis(np.rint(colors * 255).astype(np.uint8), 0, -1)
    rgba[~valid] = 0
    rgba[valid, 3] = 255
    return rgba


def save_satellite(path, output):
    """Gunakan ekspor RGB yang sudah dimask per citra di Colab, bukan ubin Esri."""
    with rasterio.open(path) as source:
        if source.count != 3 or source.nodata != 65535:
            raise ValueError('RGB harus terdiri dari B4, B3, B2 dengan NoData 65535 sesuai skrip ekspor.')
        transform, width, height = calculate_default_transform(
            source.crs, 'EPSG:3857', source.width, source.height, *source.bounds)
        projected = np.full((3, height, width), 65535, dtype=np.float32)
        for band in range(1, 4):
            reproject(rasterio.band(source, band), projected[band - 1],
                      src_transform=source.transform, src_crs=source.crs, src_nodata=65535,
                      dst_transform=transform, dst_crs='EPSG:3857', dst_nodata=65535,
                      resampling=Resampling.nearest)
        valid = np.all(np.isfinite(projected) & (projected != 65535), axis=0)
        Image.fromarray(satellite_rgba(projected, valid)).save(output)
        west, south, east, north = transform_bounds(
            'EPSG:3857', 'EPSG:4326', *rasterio.transform.array_bounds(height, width, transform))
        return {'bounds': [[south, west], [north, east]]}


def generate(root):
    root = Path(root).resolve()
    data_dir = root / 'ml_model/data'
    output = root / 'storage/app/map-overlays'
    output.mkdir(parents=True, exist_ok=True)
    boundary_path = data_dir / 'OFFICIAL_DATA_2025/Batas_4_Kecamatan_Nganjuk.geojson'
    boundary = json.loads(boundary_path.read_text(encoding='utf-8-sig'))
    eligibility_path = data_dir / 'XGBOOST_V4_1/LandCover_Eligibility_2025.tif'
    sources = [Path(__file__).resolve(), boundary_path]
    if eligibility_path.is_file():
        sources.append(eligibility_path)
    manifest = {'schema': 2, 'layers': {}, 'coverage': [], 'landcover_available': False}
    for layer, filename in RASTERS.items():
        path = data_dir / 'XGBOOST_V4_1' / filename
        sources.append(path)
        with rasterio.open(path) as source:
            values = source.read(1, masked=True)
            valid = ~np.ma.getmaskarray(values) & np.isfinite(values.data)
            observed = values.data[valid]
            if layer == 'probability':
                if np.any((observed < 0) | (observed > 1)):
                    raise ValueError('Probabilitas harus 0 sampai 1; periksa ekspor Colab.')
            elif not np.all(np.isin(observed, [0, 1])):
                raise ValueError(f'{filename}: kelas harus bernilai 0 atau 1.')
            geometries = [transform_geom('EPSG:4326', source.crs, f['geometry']) for f in boundary['features']]
            inside = geometry_mask(geometries, out_shape=source.shape, transform=source.transform, invert=True)
            codes = classify(values.data, valid, inside, layer)
            bounds = save_overlay(codes, source, output / f'overlay_{layer}.png', layer)
            manifest['layers'][layer] = {'bounds': bounds}
            if layer == 'candidate_doa' and eligibility_path.is_file():
                # Kelas tutupan lahan: 1 pertanian, 0 nonpertanian, 255 tidak pasti.
                eligibility = np.full(source.shape, 255, dtype=np.uint8)
                with rasterio.open(eligibility_path) as landcover:
                    observed_mask = landcover.read(1, masked=True).compressed()
                    if not np.all(np.isin(observed_mask, [0, 1, 255])):
                        raise ValueError('Eligibility harus 0, 1, atau 255.')
                    reproject(rasterio.band(landcover, 1), eligibility,
                              src_transform=landcover.transform, src_crs=landcover.crs,
                              src_nodata=landcover.nodata, dst_transform=source.transform,
                              dst_crs=source.crs, dst_nodata=255, resampling=Resampling.nearest)
                filtered, filtered_valid = filter_candidate(values.data, valid, eligibility)
                filtered_codes = classify(filtered, filtered_valid, inside, 'candidate_landcover')
                filtered_bounds = save_overlay(filtered_codes, source, output / 'overlay_candidate_landcover.png', 'candidate_landcover')
                manifest['layers']['candidate_landcover'] = {'bounds': filtered_bounds}
                manifest['landcover_available'] = True
            if layer == 'probability':
                for feature, geometry in zip(boundary['features'], geometries):
                    district = geometry_mask([geometry], out_shape=source.shape, transform=source.transform, invert=True)
                    total = int(district.sum())
                    missing = int((district & ~valid).sum())
                    manifest['coverage'].append({
                        'district': feature['properties']['Kecamatan'],
                        'total_pixels': total, 'valid_pixels': total - missing,
                        'missing_pixels': missing,
                        'missing_percent': round(100 * missing / total, 2) if total else None})
    satellite_path = data_dir / 'XGBOOST_V4_1/Sentinel2_RGB_CloudMasked_2025.tif'
    manifest['satellite_available'] = satellite_path.is_file()
    if satellite_path.is_file():
        sources.append(satellite_path)
        manifest['layers']['satellite'] = save_satellite(satellite_path, output / 'overlay_satellite.png')
    # Manifest diterbitkan terakhir setelah seluruh PNG selesai dibuat.
    manifest['sources'] = {
        path.relative_to(root).as_posix(): {'mtime': int(path.stat().st_mtime), 'size': path.stat().st_size}
        for path in sources}
    manifest['version'] = hashlib.sha256(json.dumps(manifest, sort_keys=True).encode()).hexdigest()[:16]
    (output / 'manifest.json').write_text(json.dumps(manifest, indent=2), encoding='utf-8')
    return manifest


if __name__ == '__main__':
    print(json.dumps(generate(sys.argv[1] if len(sys.argv) > 1 else '.'), indent=2))
