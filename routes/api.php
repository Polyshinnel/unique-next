<?php

use App\Http\Controllers\Api\BannerController;
use App\Http\Controllers\Api\ContactController;
use App\Http\Controllers\Api\HealthController;
use App\Http\Controllers\Api\PageSeoController;
use Illuminate\Support\Facades\Route;

Route::get('/banners', [BannerController::class, 'index']);
Route::get('/contacts', ContactController::class);
Route::get('/health', HealthController::class);
Route::get('/seo/by-key/{key}', [PageSeoController::class, 'byKey']);
Route::get('/seo/by-path', [PageSeoController::class, 'byPath']);
