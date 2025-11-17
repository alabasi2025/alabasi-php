<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\JournalEntryController;
use App\Http\Controllers\VoucherController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\UnitController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

// دليل الحسابات
Route::resource('accounts', AccountController::class);

// القيود اليومية
Route::resource('journal-entries', JournalEntryController::class);

// سندات الصرف والقبض
Route::resource('vouchers', VoucherController::class);
Route::get('vouchers/get-accounts', [VoucherController::class, 'getAccounts'])->name('vouchers.get-accounts');
Route::post('vouchers/{voucher}/approve', [VoucherController::class, 'approve'])->name('vouchers.approve');
Route::post('vouchers/{voucher}/reject', [VoucherController::class, 'reject'])->name('vouchers.reject');
Route::post('vouchers/{voucher}/submit', [VoucherController::class, 'submit'])->name('vouchers.submit');

// المؤسسات
Route::resource('companies', CompanyController::class);

// الوحدات
Route::resource('units', UnitController::class);
Route::get('units/get-by-company', [UnitController::class, 'getByCompany'])->name('units.get-by-company');
