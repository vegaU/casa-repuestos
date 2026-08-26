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
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\Admin\TenantAdministrationController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('auth.logout');
    Route::get('/me', [AuthController::class, 'me'])->name('auth.me');
    Route::post('/change-password', [AuthController::class, 'changePassword'])->name('auth.password.change');
    Route::middleware('password.changed')->group(function () {
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
        Route::get('/suppliers/{supplier}', [SupplierController::class, 'show']);
        Route::patch('/suppliers/{supplier}', [SupplierController::class, 'update']);
        Route::get('/customers', [CustomerController::class, 'index']);
        Route::post('/customers', [CustomerController::class, 'store']);
        Route::get('/customers/{customer}', [CustomerController::class, 'show']);
        Route::patch('/customers/{customer}', [CustomerController::class, 'update']);
        Route::get('/products', [ProductController::class, 'index']);
        Route::post('/products', [ProductController::class, 'store']);
        Route::get('/products/{product}', [ProductController::class, 'show']);
        Route::patch('/products/{product}', [ProductController::class, 'update']);
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
    Route::prefix('/admin')->middleware('superadmin')->group(function () {
        Route::get('/tenants', [TenantAdministrationController::class, 'index']);
        Route::post('/tenants', [TenantAdministrationController::class, 'store']);
        Route::get('/tenants/{tenant}', [TenantAdministrationController::class, 'show']);
        Route::patch('/tenants/{tenant}', [TenantAdministrationController::class, 'update']);
        Route::post('/tenants/{tenant}/administrators', [TenantAdministrationController::class, 'assignAdministrator']);
        Route::delete('/tenants/{tenant}/administrators/{user}', [TenantAdministrationController::class, 'removeAdministrator']);
        Route::post('/tenants/{tenant}/support', [TenantAdministrationController::class, 'support']);
    });
    });
});
