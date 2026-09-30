<?php

use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', [DashboardController::class, 'index'])->name('home');

Route::get('/map-data/{asset}', [DashboardController::class, 'mapAsset'])->name('map.asset');

// Informasi bounds & ketersediaan file raster (dipakai JavaScript peta)
Route::get('/map-info', [DashboardController::class, 'mapInfo'])->name('map.info');

// PNG overlay yang stabil saat zoom (di-generate dari TIF oleh Python)
Route::get('/map-overlay/{layer}', [DashboardController::class, 'mapOverlay'])->name('map.overlay');
