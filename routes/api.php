<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\MenuItemController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\SalesController;
use App\Http\Controllers\Api\VendorController;
use App\Http\Controllers\Api\PurchaseOrderController;
use App\Http\Controllers\Api\InventoryController;
use App\Http\Controllers\Api\StaffController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\SettingController;
use App\Http\Controllers\Api\ExpenseController;

// Menu
Route::apiResource('categories', CategoryController::class);
Route::apiResource('menu-items', MenuItemController::class);

// Orders / Billing
Route::apiResource('orders', OrderController::class);

// Sales
Route::get('sales/summary',     [SalesController::class, 'summary']);
Route::get('sales/by-category', [SalesController::class, 'byCategory']);
Route::get('sales/by-day',      [SalesController::class, 'byDay']);

// Purchasing
Route::apiResource('vendors',          VendorController::class);
Route::apiResource('purchase-orders',  PurchaseOrderController::class);

// Inventory
Route::get('inventory',           [InventoryController::class, 'index']);
Route::post('inventory',          [InventoryController::class, 'store']);
Route::put('inventory/{item}',    [InventoryController::class, 'update']);
Route::delete('inventory/{item}', [InventoryController::class, 'destroy']);
Route::post('inventory/adjust',   [InventoryController::class, 'adjust']);
Route::get('inventory/movements', [InventoryController::class, 'movements']);

// Staff
Route::apiResource('staff', StaffController::class);

// Customers
Route::apiResource('customers', CustomerController::class);
Route::get('customers/{customer}/orders', [CustomerController::class, 'orders']);

// Reports
Route::get('reports/summary',    [ReportController::class, 'summary']);
Route::get('reports/top-items',  [ReportController::class, 'topItems']);
Route::get('reports/by-category',[ReportController::class, 'byCategory']);

// Settings
Route::get('settings',  [SettingController::class, 'index']);
Route::put('settings',  [SettingController::class, 'update']);

// Expenses — summary must be before apiResource to avoid 'summary' being treated as an ID
Route::get('expenses/summary', [ExpenseController::class, 'summary']);
Route::apiResource('expenses', ExpenseController::class)->except(['show']);
