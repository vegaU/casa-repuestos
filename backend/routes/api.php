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
        Route::get('/categories', [CategoryController::class, 'index'])->middleware('tenant.permission:catalog.view');
        Route::post('/categories', [CategoryController::class, 'store'])->middleware('tenant.permission:catalog.manage');
        Route::patch('/categories/{category}', [CategoryController::class, 'update'])->middleware('tenant.permission:catalog.manage');
        Route::get('/brands', [BrandController::class, 'index'])->middleware('tenant.permission:catalog.view');
        Route::post('/brands', [BrandController::class, 'store'])->middleware('tenant.permission:catalog.manage');
        Route::patch('/brands/{brand}', [BrandController::class, 'update'])->middleware('tenant.permission:catalog.manage');
        Route::get('/suppliers', [SupplierController::class, 'index'])->middleware('tenant.permission:purchases.view');
        Route::post('/suppliers', [SupplierController::class, 'store'])->middleware('tenant.permission:purchases.manage');
        Route::get('/suppliers/{supplier}', [SupplierController::class, 'show'])->middleware('tenant.permission:purchases.view');
        Route::patch('/suppliers/{supplier}', [SupplierController::class, 'update'])->middleware('tenant.permission:purchases.manage');
        Route::get('/customers', [CustomerController::class, 'index'])->middleware('tenant.permission:sales.view');
        Route::post('/customers', [CustomerController::class, 'store'])->middleware('tenant.permission:customers.manage');
        Route::get('/customers/{customer}', [CustomerController::class, 'show'])->middleware('tenant.permission:sales.view');
        Route::patch('/customers/{customer}', [CustomerController::class, 'update'])->middleware('tenant.permission:customers.manage');
        Route::get('/products', [ProductController::class, 'index'])->middleware('tenant.permission:catalog.view');
        Route::post('/products', [ProductController::class, 'store'])->middleware('tenant.permission:catalog.manage');
        Route::get('/products/{product}', [ProductController::class, 'show'])->middleware('tenant.permission:catalog.view');
        Route::patch('/products/{product}', [ProductController::class, 'update'])->middleware('tenant.permission:catalog.manage');
        Route::get('/purchases', [PurchaseController::class, 'index'])->middleware('tenant.permission:purchases.view');
        Route::post('/purchases', [PurchaseController::class, 'store'])->middleware('tenant.permission:purchases.manage');
        Route::get('/purchases/{purchase}', [PurchaseController::class, 'show'])->middleware('tenant.permission:purchases.view');
        Route::post('/purchases/{purchase}/receive', [PurchaseController::class, 'receive'])->middleware('tenant.permission:purchases.manage');
        Route::post('/purchases/{purchase}/cancel', [CancellationController::class, 'purchase'])->middleware('tenant.permission:purchases.manage');
        Route::get('/sales', [SaleController::class, 'index'])->middleware('tenant.permission:sales.view');
        Route::post('/sales/checkout', [SaleController::class, 'checkout'])->middleware('tenant.permission:sales.manage');
        Route::post('/sales', [SaleController::class, 'store'])->middleware('tenant.permission:sales.manage');
        Route::get('/sales/{sale}', [SaleController::class, 'show'])->middleware('tenant.permission:sales.view');
        Route::post('/sales/{sale}/complete', [SaleController::class, 'complete'])->middleware('tenant.permission:sales.manage');
        Route::post('/sales/{sale}/cancel', [CancellationController::class, 'sale'])->middleware('tenant.permission:sales.manage');
        Route::post('/sales/{sale}/payments', [PaymentController::class, 'store'])->middleware('tenant.permission:payments.manage');
        Route::get('/inventory', [InventoryController::class, 'index'])->middleware('tenant.permission:inventory.view');
        Route::get('/stock-movements', [InventoryController::class, 'movements'])->middleware('tenant.permission:inventory.view');
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
