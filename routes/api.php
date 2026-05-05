<?php

use App\Http\Controllers\TrackController;
use Illuminate\Support\Facades\Route;

Route::get('/tracks', [TrackController::class, 'index']);
Route::post('/tracks/fetch', [TrackController::class, 'fetch']);
