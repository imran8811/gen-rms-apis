<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\MenuItemController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\SalesController;
use App\Http\Controllers\Api\PurchaseController;
use App\Http\Controllers\Api\VendorController;
use App\Http\Controllers\Api\InventoryController;
use App\Http\Controllers\Api\StaffController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\SettingController;
use App\Http\Controllers\Api\ExpenseController;
use App\Http\Controllers\Api\StaffFoodLogController;
use App\Http\Controllers\Api\StaffAttendanceController;
use App\Http\Controllers\Api\StaffLeaveController;
use App\Http\Controllers\Api\StaffAdvanceController;
use App\Http\Controllers\Api\StaffPayrollController;

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
Route::get('purchases',               [PurchaseController::class, 'index']);
Route::post('purchases',              [PurchaseController::class, 'store']);
Route::delete('purchases/{purchase}', [PurchaseController::class, 'destroy']);

// Vendors
Route::get('vendors',  [VendorController::class, 'index']);
Route::post('vendors', [VendorController::class, 'store']);

// Inventory
Route::get('inventory',           [InventoryController::class, 'index']);
Route::post('inventory',          [InventoryController::class, 'store']);
Route::put('inventory/{item}',    [InventoryController::class, 'update']);
Route::delete('inventory/{item}', [InventoryController::class, 'destroy']);
Route::post('inventory/adjust',   [InventoryController::class, 'adjust']);
Route::get('inventory/movements', [InventoryController::class, 'movements']);

// Staff
Route::apiResource('staff', StaffController::class);

// Staff Attendance
Route::get('staff-attendance/summary',   [StaffAttendanceController::class, 'summary']);
Route::get('staff-attendance',           [StaffAttendanceController::class, 'index']);
Route::post('staff-attendance/bulk',     [StaffAttendanceController::class, 'bulkStore']);

// Staff Leaves
Route::get('staff-leaves',               [StaffLeaveController::class, 'index']);
Route::post('staff-leaves',              [StaffLeaveController::class, 'store']);
Route::delete('staff-leaves/{staffLeave}', [StaffLeaveController::class, 'destroy']);

// Staff Advances
Route::get('staff-advances',             [StaffAdvanceController::class, 'index']);
Route::post('staff-advances',            [StaffAdvanceController::class, 'store']);
Route::delete('staff-advances/{staffAdvance}', [StaffAdvanceController::class, 'destroy']);

// Staff Payroll
Route::get('staff-payroll',              [StaffPayrollController::class, 'index']);

// Staff Food Logs
Route::get('staff-food/summary',     [StaffFoodLogController::class, 'summary']);
Route::get('staff-food',             [StaffFoodLogController::class, 'index']);
Route::post('staff-food',            [StaffFoodLogController::class, 'store']);
Route::delete('staff-food/{staffFoodLog}', [StaffFoodLogController::class, 'destroy']);

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

Route::get('/run-migration', function () {
    try {
        Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
        return 'Database migrated successfully!';
    } catch (\Exception $e) {
        return 'Error: ' . $e->getMessage();
    }
});
