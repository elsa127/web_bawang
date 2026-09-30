import sys
print("Python:", sys.version)

libs = ['rasterio', 'numpy', 'PIL', 'gdal', 'osgeo']
for lib in libs:
    try:
        __import__(lib)
        print(f"{lib}: OK")
    except ImportError as e:
        print(f"{lib}: NOT FOUND ({e})")
