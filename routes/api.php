<?php

use App\Http\Controllers\TrackController;
use Illuminate\Support\Facades\Route;

Route::post('/tracks/fetch', [TrackController::class, 'fetch']);
