<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\CacheController;
use App\Http\Controllers\Admin\QueueController;

/*
|--------------------------------------------------------------------------
| Admin Routes - الوحدة المركزية
|--------------------------------------------------------------------------
|
| جميع المسارات الخاصة بالوحدة المركزية مع الميزات المتقدمة
|
*/

Route::prefix('admin')->name('admin.')->group(function () {
    
    // لوحة التحكم الرئيسية
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    
    // إدارة الميزات
    Route::get('/features', [AdminDashboardController::class, 'features'])->name('features');
    
    // إدارة الذاكرة المؤقتة
    Route::get('/cache', [CacheController::class, 'index'])->name('cache');
    Route::post('/cache/clear', [CacheController::class, 'clear'])->name('cache.clear');
    Route::post('/cache/clear-config', [CacheController::class, 'clearConfig'])->name('cache.clear.config');
    Route::post('/cache/clear-route', [CacheController::class, 'clearRoute'])->name('cache.clear.route');
    Route::post('/cache/clear-view', [CacheController::class, 'clearView'])->name('cache.clear.view');
    Route::post('/cache/optimize', [CacheController::class, 'optimize'])->name('cache.optimize');
    
    // إدارة قواعد البيانات
    Route::get('/database', [AdminDashboardController::class, 'database'])->name('database');
    
    // جدولة المهام
    Route::get('/scheduler', [AdminDashboardController::class, 'scheduler'])->name('scheduler');
    
    // الإشعارات
    Route::get('/notifications', [AdminDashboardController::class, 'notifications'])->name('notifications');
    
    // API Management
    Route::get('/api', [AdminDashboardController::class, 'api'])->name('api');
    
    // إدارة التخزين
    Route::get('/storage', [AdminDashboardController::class, 'storage'])->name('storage');
    
    // Laravel Pint - جودة الكود
    Route::prefix('pint')->name('pint.')->group(function () {
        Route::get('/', function () {
            return view('admin.pint.index');
        })->name('index');
    });
    
    // Laravel Pennant - Feature Flags
    Route::prefix('pennant')->name('pennant.')->group(function () {
        Route::get('/', function () {
            return view('admin.pennant.index');
        })->name('index');
    });
    
    // Authentication & Authorization
    Route::prefix('auth')->name('auth.')->group(function () {
        Route::get('/', function () {
            return view('admin.auth.index');
        })->name('index');
    });
    
    // Livewire Components
    Route::prefix('components')->name('components.')->group(function () {
        Route::get('/', function () {
            return view('admin.components.index');
        })->name('index');
    });
    
    // Events & Listeners
    Route::prefix('events')->name('events.')->group(function () {
        Route::get('/', function () {
            return view('admin.events.index');
        })->name('index');
    });
    
    // Testing
    Route::prefix('testing')->name('testing.')->group(function () {
        Route::get('/', function () {
            return view('admin.testing.index');
        })->name('index');
    });
    
    // Localization
    Route::prefix('localization')->name('localization.')->group(function () {
        Route::get('/', function () {
            return view('admin.localization.index');
        })->name('index');
    });
    
    // Queues & Jobs
    Route::prefix('queues')->name('queues.')->group(function () {
        Route::get('/', [QueueController::class, 'index'])->name('index');
        Route::get('/stats', [QueueController::class, 'stats'])->name('stats');
        Route::post('/retry-failed', [QueueController::class, 'retryFailed'])->name('retry.failed');
        Route::post('/clear-failed', [QueueController::class, 'clearFailed'])->name('clear.failed');
        Route::get('/failed-jobs', [QueueController::class, 'failedJobs'])->name('failed.jobs');
    });
    
    // Middleware Management
    Route::prefix('middleware')->name('middleware.')->group(function () {
        Route::get('/', function () {
            return view('admin.middleware.index');
        })->name('index');
    });
    
    // Migrations
    Route::prefix('migrations')->name('migrations.')->group(function () {
        Route::get('/', function () {
            return view('admin.migrations.index');
        })->name('index');
    });
    
    // Templates
    Route::prefix('templates')->name('templates.')->group(function () {
        Route::get('/', function () {
            return view('admin.templates.index');
        })->name('index');
    });
    
    // Deployment
    Route::prefix('deployment')->name('deployment.')->group(function () {
        Route::get('/', function () {
            return view('admin.deployment.index');
        })->name('index');
    });
    
    // UI Components
    Route::prefix('ui')->name('ui.')->group(function () {
        Route::get('/', function () {
            return view('admin.ui.index');
        })->name('index');
    });
});
