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

// صفحة اختبار
Route::get('/test-login', function() {
    return view('test_login');
});

// صفحة تسجيل الدخول الجديدة
Route::get('/login', function() {
    if (session('unit_id')) {
        return redirect('/dashboard');
    }
    
    $units = \App\Models\Main\Unit::all();
    return view('login', compact('units'));
})->name('login');

Route::post('/login-process', function(\Illuminate\Http\Request $request) {
    $unit_id = $request->input('unit_id');
    $company_id = $request->input('company_id');
    
    if (!$unit_id) {
        return redirect('/login?error=no_unit');
    }
    
    session(['unit_id' => $unit_id]);
    
    if ($unit_id === 'main') {
        session([
            'unit_name' => 'القاعدة المركزية',
            'database' => 'main',
            'is_main' => true
        ]);
    } else {
        if (!$company_id) {
            return redirect('/login?error=no_company');
        }
        
        $unit = \App\Models\Main\Unit::find($unit_id);
        $company = \App\Models\Main\Company::find($company_id);
        
        if (!$unit || !$company) {
            return redirect('/login?error=invalid');
        }
        
        session([
            'unit_name' => $unit->name,
            'company_id' => $company_id,
            'company_name' => $company->name,
            'database' => $unit->database_name,
            'is_main' => false
        ]);
    }
    
    return redirect('/dashboard');
})->name('login.process');

Route::get('/dashboard', function() {
    if (!session('unit_id')) {
        return redirect('/login');
    }
    return view('new_dashboard');
})->name('new.dashboard');

Route::get('/logout', function() {
    session()->flush();
    return redirect('/login');
})->name('logout');

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

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

// الوحدات
Route::resource('units', UnitController::class);
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
