<?php

use Integration\Http\Controllers\TrackFetchController;
use Illuminate\Support\Facades\Route;

Route::post('/tracks/fetch', TrackFetchController::class);
