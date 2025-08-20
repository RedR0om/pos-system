<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\SaleController;
use App\Http\Controllers\Api\InventoryController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\UserController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Auth routes
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
Route::get('/me', [AuthController::class, 'me'])->middleware('auth:sanctum');

// User management routes (admin only)
Route::middleware(['auth:sanctum', 'admin'])->group(function () {
    Route::get('/users', [UserController::class, 'index']);
    Route::post('/users', [UserController::class, 'store']);
    Route::get('/users/{user}', [UserController::class, 'show']);
    Route::put('/users/{user}', [UserController::class, 'update']);
    Route::delete('/users/{user}', [UserController::class, 'destroy']);
    Route::put('/users/{user}/status', [UserController::class, 'updateStatus']);
});

// Change password route (authenticated users)
Route::post('/change-password', [AuthController::class, 'changePassword'])->middleware('auth:sanctum');

// Products CRUD
Route::apiResource('products', ProductController::class)->middleware('api');
Route::get('categories', [CategoryController::class, 'index']);

// Sales and payments (require auth so user_id is set)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('sales', [SaleController::class, 'index']);
    Route::post('sales', [SaleController::class, 'store']);
    Route::get('sales/{sale}', [SaleController::class, 'show']);
    Route::post('sales/{sale}/payments', [SaleController::class, 'addPayment']);
});

// Inventory adjustments
Route::get('inventory', [InventoryController::class, 'index']);
Route::post('inventory/adjust', [InventoryController::class, 'adjust']);

// Reports routes
Route::get('/reports/sales-summary', [ReportController::class, 'salesSummary']);
Route::get('/reports/sales-by-day', [ReportController::class, 'salesByDay']);
Route::get('/reports/sales-by-category', [ReportController::class, 'salesByCategory']);
Route::get('/reports/dashboard-summary', [ReportController::class, 'dashboardSummary']);
Route::get('/reports/top-products', [ReportController::class, 'topProducts']);
Route::get('/reports/recent-sales', [ReportController::class, 'recentSales']);
Route::get('/reports/export', [ReportController::class, 'export']);
Route::get('/reports/inventory-export', [ReportController::class, 'inventoryExport']);
Route::get('/reports/products-export', [ReportController::class, 'productsExport']);
