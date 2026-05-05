<?php

use Music\Http\Controllers\TrackController;
use Illuminate\Support\Facades\Route;

Route::get('/tracks', [TrackController::class, 'index']);
