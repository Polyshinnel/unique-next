<?php

use App\Http\Controllers\Api\BannerController;
use App\Http\Controllers\Api\CatalogCategoryController;
use App\Http\Controllers\Api\CatalogPageController;
use App\Http\Controllers\Api\CatalogProductController;
use App\Http\Controllers\Api\ContactController;
use App\Http\Controllers\Api\HealthController;
use App\Http\Controllers\Api\PageSeoController;
use App\Http\Controllers\Api\ShipmentController;
use Illuminate\Support\Facades\Route;

Route::get('/banners', [BannerController::class, 'index']);
Route::get('/contacts', ContactController::class);
Route::get('/health', HealthController::class);
Route::get('/seo/by-key/{key}', [PageSeoController::class, 'byKey']);
Route::get('/seo/by-path', [PageSeoController::class, 'byPath']);
Route::get('/shipments', [ShipmentController::class, 'index']);
Route::get('/shipments/{shipment}', [ShipmentController::class, 'show']);

Route::prefix('catalog')->group(function (): void {
    Route::get('/page', [CatalogPageController::class, 'show']);
    Route::get('/categories/by-path', [CatalogCategoryController::class, 'byPath']);
    Route::get('/products/{product}', [CatalogProductController::class, 'show']);
});
