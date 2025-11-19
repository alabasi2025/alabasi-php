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
use App\Http\Controllers\ContextSelectorController;
use App\Http\Controllers\ClearingTransactionController;
use App\Http\Controllers\Auth\AuthController;

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

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/

// عرض شاشة الدخول
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');

// معالجة تسجيل الدخول
Route::post('/login', [AuthController::class, 'login']);

// تسجيل الخروج
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// الصفحة الرئيسية - لوحة التحكم
Route::get('/', function() {
    if (!session('unit_id') || !session('database')) {
        return redirect('/login');
    }
    return app(DashboardController::class)->index();
})->name('dashboard');

// تغيير السياق (الوحدة والمؤسسة)
Route::post('/context/set-unit', [ContextSelectorController::class, 'setActiveUnit'])->name('context.set-unit');
Route::post('/context/set-company', [ContextSelectorController::class, 'setActiveCompany'])->name('context.set-company');
Route::get('/context/selector', [ContextSelectorController::class, 'showSelector'])->name('context.selector');

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

// الوحدات (محصورة بالقاعدة المركزية فقط)
Route::middleware(['check.main.unit'])->group(function () {
    Route::resource('units', UnitController::class);
});

// الفروع
Route::resource('branches', BranchController::class);
Route::get('units/get-by-company', [UnitController::class, 'getByCompany'])->name('units.get-by-company');

// دليل النظام
Route::get('guide', [GuideController::class, 'index'])->name('guide.index');
Route::get('guide/download-pdf', [GuideController::class, 'downloadGuidePdf'])->name('guide.download-pdf');
Route::get('guide/download-changelog-pdf', [GuideController::class, 'downloadChangelogPdf'])->name('guide.download-changelog-pdf');

// دليل الاستخدام
Route::get('/manual', [App\Http\Controllers\ManualController::class, 'index'])->name('manual.index');
Route::post('/api/manual/update', [App\Http\Controllers\ManualController::class, 'update'])->name('manual.update');
Route::get('/manual/export', [App\Http\Controllers\ManualController::class, 'export'])->name('manual.export');

// سجل التحديثات
Route::get('/updates', [App\Http\Controllers\UpdateController::class, 'index'])->name('updates.index');
Route::post('/api/updates/sync', [App\Http\Controllers\UpdateController::class, 'sync'])->name('updates.sync');

// الصناديق والبنوك
Route::resource('cashboxes', App\Http\Controllers\CashBoxController::class);
Route::resource('bank-accounts', App\Http\Controllers\BankAccountController::class);

// العملاء والموردين والموظفين
Route::resource('customers', App\Http\Controllers\CustomerController::class);
Route::resource('suppliers', App\Http\Controllers\SupplierController::class);
Route::resource('employees', App\Http\Controllers\EmployeeController::class);

// التحويلات بين المؤسسات والوحدات (جديد)
Route::resource('clearing-transactions', ClearingTransactionController::class);
Route::post('clearing-transactions/{id}/approve', [ClearingTransactionController::class, 'approve'])->name('clearing-transactions.approve');
Route::delete('clearing-transactions/{id}/cancel', [ClearingTransactionController::class, 'cancel'])->name('clearing-transactions.cancel');
Route::post('clearing-transactions/{id}/sync', [ClearingTransactionController::class, 'sync'])->name('clearing-transactions.sync');
Route::post('clearing-transactions/{id}/post', [ClearingTransactionController::class, 'sync'])->name('clearing-transactions.post');
Route::get('clearing-transactions-report', [ClearingTransactionController::class, 'report'])->name('clearing-transactions.report');

// API endpoints للتحويلات
Route::get('/api/units/{unitId}/companies', [ClearingTransactionController::class, 'getCompaniesByUnit']);
Route::get('/api/units/{unitId}/companies/{companyId}/accounts', [ClearingTransactionController::class, 'getAccountsByCompany']);

// مسارات الوحدة المركزية - جميع الميزات المتقدمة
require __DIR__.'/admin.php';
