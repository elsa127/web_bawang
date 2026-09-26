<?php

use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Dashboard
|--------------------------------------------------------------------------
*/

Route::get('/', [DashboardController::class, 'index'])
    ->name('home');


/*
|--------------------------------------------------------------------------
| MAP ASSET
|--------------------------------------------------------------------------
|
| File raster tetap disimpan di:
|
| ml_model/data/
|
| Laravel yang akan mengirimkan file TIF ke Leaflet.
|
*/

Route::get('/map-data/{asset}', [DashboardController::class, 'mapAsset'])
    ->where('asset', 'probability|candidate|candidate_doa')
    ->name('map.asset');