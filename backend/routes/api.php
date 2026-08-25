<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\BrandController;
use App\Http\Controllers\Api\SupplierController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\PurchaseController;
use App\Http\Controllers\Api\SaleController;
use App\Http\Controllers\Api\InventoryController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\TenantController;
use App\Http\Controllers\Api\CancellationController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::get('/tenants', [TenantController::class, 'index']);
    Route::get('/tenants/{tenant}', [TenantController::class, 'show'])
        ->middleware('tenant.access');
    Route::prefix('/tenants/{tenant}')->middleware('tenant.access')->group(function () {
        Route::get('/categories', [CategoryController::class, 'index']);
        Route::post('/categories', [CategoryController::class, 'store']);
        Route::patch('/categories/{category}', [CategoryController::class, 'update']);
        Route::get('/brands', [BrandController::class, 'index']);
        Route::post('/brands', [BrandController::class, 'store']);
        Route::patch('/brands/{brand}', [BrandController::class, 'update']);
        Route::get('/suppliers', [SupplierController::class, 'index']);
        Route::post('/suppliers', [SupplierController::class, 'store']);
        Route::get('/products', [ProductController::class, 'index']);
        Route::post('/products', [ProductController::class, 'store']);
        Route::post('/purchases', [PurchaseController::class, 'store']);
        Route::post('/purchases/{purchase}/receive', [PurchaseController::class, 'receive']);
        Route::post('/purchases/{purchase}/cancel', [CancellationController::class, 'purchase']);
        Route::post('/sales', [SaleController::class, 'store']);
        Route::post('/sales/{sale}/complete', [SaleController::class, 'complete']);
        Route::post('/sales/{sale}/cancel', [CancellationController::class, 'sale']);
        Route::post('/sales/{sale}/payments', [PaymentController::class, 'store']);
        Route::get('/inventory', [InventoryController::class, 'index']);
        Route::get('/stock-movements', [InventoryController::class, 'movements']);
    });
});
