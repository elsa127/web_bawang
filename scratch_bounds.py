"""Hitung bounds tepat dari TIF menggunakan rasterio."""
import rasterio, json

tif = r"d:/vscode/penelitian/web_bawang/ml_model/data/XGBOOST_V4_1/Binary_Bawang_Nganjuk_4Kec_V4_1_STRICT_T050.tif"
with rasterio.open(tif) as src:
    b = src.bounds
    print(json.dumps({
        "left":   b.left,
        "bottom": b.bottom,
        "right":  b.right,
        "top":    b.top,
        "width":  src.width,
        "height": src.height,
        "crs":    str(src.crs),
        # Leaflet imageOverlay bounds: [[south, west], [north, east]]
        "leaflet_bounds": [[b.bottom, b.left], [b.top, b.right]]
    }, indent=2))
