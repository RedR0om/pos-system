<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\SaleController;
use App\Http\Controllers\Api\InventoryController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;

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

// Auth
Route::post('login', [AuthController::class, 'login']);
Route::middleware('auth:sanctum')->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('me', [AuthController::class, 'me']);
});

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

// Reports
Route::get('reports/sales-summary', [ReportController::class, 'salesSummary']);
Route::get('reports/sales-by-day', [ReportController::class, 'salesByDay']);
Route::get('reports/sales-by-category', [ReportController::class, 'salesByCategory']);
Route::get('reports/dashboard-summary', [ReportController::class, 'dashboardSummary']);
