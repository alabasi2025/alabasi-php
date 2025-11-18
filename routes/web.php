<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\JournalEntryController;
use App\Http\Controllers\VoucherController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\GuideController;
use App\Http\Controllers\AccountTypeController;
use App\Http\Controllers\AnalyticalAccountTypeController;
use App\Http\Controllers\AnalyticalAccountController;
use App\Http\Controllers\SetupController;

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

// إعداد النظام
Route::get('/setup', [SetupController::class, 'index'])->name('setup.index');
Route::post('/setup/execute', [SetupController::class, 'execute'])->name('setup.execute');
Route::delete('/setup/reset', [SetupController::class, 'reset'])->name('setup.reset');

// دليل الحسابات
Route::resource('accounts', AccountController::class);

// أنواع الحسابات
Route::resource('account-types', AccountTypeController::class);

// أنواع الحسابات التحليلية
Route::resource('analytical-account-types', AnalyticalAccountTypeController::class);

// الحسابات التحليلية الفعلية
Route::resource('analytical-accounts', AnalyticalAccountController::class);
Route::get('analytical-accounts/get-accounts-by-type', [AnalyticalAccountController::class, 'getAccountsByType'])->name('analytical-accounts.get-accounts-by-type');

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
// الفروع
Route::resource('branches', BranchController::class);
Route::get('units/get-by-company', [UnitController::class, 'getByCompany'])->name('units.get-by-company');

// دليل النظام
Route::get('guide', [GuideController::class, 'index'])->name('guide.index');
Route::get('guide/download-pdf', [GuideController::class, 'downloadGuidePdf'])->name('guide.download-pdf');
Route::get('guide/download-changelog-pdf', [GuideController::class, 'downloadChangelogPdf'])->name('guide.download-changelog-pdf');
