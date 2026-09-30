<?php

use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', [DashboardController::class, 'index'])->name('home');

Route::get('/map-data/{asset}', [DashboardController::class, 'mapAsset'])->name('map.asset');

// Informasi bounds & ketersediaan file raster (dipakai JavaScript peta)
Route::get('/map-info', [DashboardController::class, 'mapInfo'])->name('map.info');

// PNG overlay yang stabil saat zoom (di-generate dari TIF oleh Python)
Route::get('/map-overlay/{layer}', [DashboardController::class, 'mapOverlay'])->name('map.overlay');

// DEBUG: cek apakah TIF bisa diakses — hapus setelah selesai debug
Route::get('/debug-tif/{asset}', function (string $asset) {
    $allowed = ['probability', 'candidate', 'candidate_doa'];
    if (! in_array($asset, $allowed, true)) {
        return response('not allowed', 403);
    }
    $map = [
        'probability' => 'Probability_Bawang_Nganjuk_4Kec_V4_1_STRICT.tif',
        'candidate' => 'Binary_Bawang_Nganjuk_4Kec_V4_1_STRICT_T050.tif',
        'candidate_doa' => 'Candidate_Bawang_DOA_T050_V4_1.tif',
    ];
    $path = base_path('ml_model/data/XGBOOST_V4_1/'.$map[$asset]);

    return response()->json([
        'asset' => $asset,
        'path' => $path,
        'exists' => file_exists($path),
        'size_kb' => file_exists($path) ? round(filesize($path) / 1024, 1) : null,
        'readable' => file_exists($path) ? is_readable($path) : false,
    ]);
});
