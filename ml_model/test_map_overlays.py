"""Uji piksel sintetis untuk mencegah NoData atau kelas negatif menjadi kandidat."""
import tempfile
import unittest
from pathlib import Path

import numpy as np
import rasterio
from PIL import Image
from rasterio.transform import from_origin
from generate_map_overlays import classify, save_overlay, filter_candidate, satellite_rgba, save_satellite


class MapOverlayTests(unittest.TestCase):
    def test_candidate_nodata_and_outside_have_distinct_colors(self):
        values = np.array([[1, 0, 255, 1]], dtype=np.uint8)
        valid = values != 255
        inside = np.array([[True, True, True, False]])
        np.testing.assert_array_equal(classify(values, valid, inside, 'candidate'), [[1, 0, 4, 0]])

    def test_landcover_removes_non_agriculture_and_preserves_uncertainty(self):
        data = np.array([[1, 1, 1, 0, 255]], dtype=np.uint8)
        eligibility = np.array([[1, 0, 255, 1, 1]], dtype=np.uint8)
        filtered, valid = filter_candidate(data, data != 255, eligibility)
        np.testing.assert_array_equal(
            classify(filtered, valid, np.ones(data.shape, dtype=bool), 'candidate_landcover'),
            [[1, 0, 4, 0, 4]])

    def test_satellite_nodata_does_not_turn_into_white_clouds(self):
        rgb = np.array([[[3000, 65535]], [[0, 65535]], [[0, 65535]]], dtype=np.float32)
        rgba = satellite_rgba(rgb, np.array([[True, False]]))
        np.testing.assert_array_equal(rgba, [[[255, 0, 0, 255], [0, 0, 0, 0]]])

    def test_satellite_export_is_reprojected_with_transparent_nodata(self):
        with tempfile.TemporaryDirectory() as directory:
            path = Path(directory)
            with rasterio.open(path / 'rgb.tif', 'w', driver='GTiff', width=4, height=4,
                               count=3, dtype='uint16', crs='EPSG:4326', nodata=65535,
                               transform=from_origin(111.9, -7.5, 0.001, 0.001)) as source:
                rgb = np.full((3, 4, 4), 1000, dtype=np.uint16)
                rgb[:, :, 2:] = 65535
                source.write(rgb)
            metadata = save_satellite(path / 'rgb.tif', path / 'rgb.png')
            rgba = np.array(Image.open(path / 'rgb.png'))
            self.assertEqual(set(rgba[:, :, 3].flat), {0, 255})
            self.assertAlmostEqual(metadata['bounds'][1][0], -7.5, places=6)

    def test_probability_breaks_preserve_missing_pixels(self):
        values = np.array([[0.0, 0.35, 0.70, 1.0, np.nan]])
        np.testing.assert_array_equal(
            classify(values, np.isfinite(values), np.ones(values.shape, dtype=bool), 'probability'),
            [[1, 2, 3, 3, 4]])

    def test_overlay_preserves_classes_and_returns_geographic_bounds(self):
        with tempfile.TemporaryDirectory() as directory:
            path = Path(directory)
            with rasterio.open(path / 'input.tif', 'w', driver='GTiff', width=4, height=4,
                               count=1, dtype='uint8', crs='EPSG:4326',
                               transform=from_origin(111.9, -7.5, 0.001, 0.001)) as source:
                codes = np.array([[1, 0, 4, 0]] * 4, dtype=np.uint8)
                bounds = save_overlay(codes, source, path / 'output.png', 'candidate')
            rgba = np.array(Image.open(path / 'output.png'))
            self.assertEqual(set(map(tuple, rgba.reshape(-1, 4))),
                             {(168, 85, 247, 191), (0, 0, 0, 0), (100, 116, 139, 190)})
            self.assertAlmostEqual(bounds[1][0], -7.5, places=6)
            self.assertAlmostEqual(bounds[0][1], 111.9, places=6)


if __name__ == '__main__':
    unittest.main()
