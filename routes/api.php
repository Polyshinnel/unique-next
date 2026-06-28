<?php

use App\Http\Controllers\Api\HealthController;
use App\Http\Controllers\Api\PageSeoController;
use Illuminate\Support\Facades\Route;

Route::get('/health', HealthController::class);
Route::get('/seo/by-key/{key}', [PageSeoController::class, 'byKey']);
Route::get('/seo/by-path', [PageSeoController::class, 'byPath']);
