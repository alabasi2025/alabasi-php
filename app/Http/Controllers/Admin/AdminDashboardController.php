<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Unit;
use App\Models\Company;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Admin Dashboard Controller
 * 
 * المسؤول عن لوحة التحكم المركزية مع جميع الميزات المتقدمة
 */
class AdminDashboardController extends Controller
{
    /**
     * عرض لوحة التحكم الرئيسية
     */
    public function index(Request $request)
    {
        // الحصول على معلومات الوحدة والمؤسسة من الجلسة
        $unitId = session('unit_id');
        $companyId = session('company_id');
        
        // تحميل معلومات الوحدة والمؤسسة
        $currentUnit = $unitId ? \App\Models\Main\Unit::find($unitId) : null;
        $currentCompany = $companyId ? \App\Models\Main\Company::find($companyId) : null;
        
        $stats = $this->getSystemStats();
        
        return view('admin.dashboard.index', compact('stats', 'currentUnit', 'currentCompany'));
    }

    /**
     * الحصول على إحصائيات النظام
     */
    private function getSystemStats(): array
    {
        return Cache::remember('admin_stats', 300, function () {
            try {
                return [
                    'total_units' => \DB::table('units')->count(),
                    'active_units' => \DB::table('units')->where('is_active', true)->count(),
                    'total_companies' => \DB::table('companies')->count(),
                    'total_users' => \DB::table('users')->count(),
                    'database_size' => $this->getDatabaseSize(),
                    'cache_size' => $this->getCacheSize(),
                    'recent_activities' => $this->getRecentActivities(),
                ];
            } catch (\Exception $e) {
                return [
                    'total_units' => 0,
                    'active_units' => 0,
                    'total_companies' => 0,
                    'total_users' => 0,
                    'database_size' => 0,
                    'cache_size' => 0,
                    'recent_activities' => [],
                ];
            }
        });
    }

    /**
     * الحصول على حجم قاعدة البيانات
     */
    private function getDatabaseSize(): string
    {
        try {
            $database = config('database.connections.mysql.database');
            $result = DB::selectOne("
                SELECT 
                    ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS size_mb
                FROM information_schema.TABLES
                WHERE table_schema = ?
            ", [$database]);

            return $result->size_mb . ' MB';
        } catch (\Exception $e) {
            return 'N/A';
        }
    }

    /**
     * الحصول على حجم الذاكرة المؤقتة
     */
    private function getCacheSize(): string
    {
        try {
            $driver = config('cache.default');
            if ($driver === 'file') {
                $path = storage_path('framework/cache');
                $size = $this->getDirectorySize($path);
                return round($size / 1024 / 1024, 2) . ' MB';
            }
            return 'N/A';
        } catch (\Exception $e) {
            return 'N/A';
        }
    }

    /**
     * حساب حجم المجلد
     */
    private function getDirectorySize(string $path): int
    {
        $size = 0;
        if (is_dir($path)) {
            foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path)) as $file) {
                $size += $file->getSize();
            }
        }
        return $size;
    }

    /**
     * الحصول على النشاطات الأخيرة
     */
    private function getRecentActivities(): array
    {
        // سيتم تفعيلها بعد تثبيت spatie/laravel-activitylog
        return [];
    }

    /**
     * عرض صفحة الميزات
     */
    public function features()
    {
        $features = [
            'telescope' => [
                'name' => 'Laravel Telescope',
                'status' => 'active',
                'url' => '/telescope',
                'description' => 'مراقبة الاستعلامات والأخطاء والمهام',
            ],
            'pint' => [
                'name' => 'Laravel Pint',
                'status' => 'active',
                'url' => '/admin/pint',
                'description' => 'ضبط جودة الكود',
            ],
            'pennant' => [
                'name' => 'Laravel Pennant',
                'status' => 'pending',
                'url' => '/admin/features',
                'description' => 'إدارة الميزات',
            ],
            'sanctum' => [
                'name' => 'Laravel Sanctum',
                'status' => 'active',
                'url' => '/admin/auth',
                'description' => 'المصادقة والصلاحيات',
            ],
            'livewire' => [
                'name' => 'Laravel Livewire',
                'status' => 'pending',
                'url' => '/admin/components',
                'description' => 'المكونات التفاعلية',
            ],
        ];

        return view('admin.features.index', compact('features'));
    }

    /**
     * عرض صفحة إدارة الذاكرة المؤقتة
     */
    public function cache()
    {
        return view('admin.cache.index');
    }

    /**
     * مسح الذاكرة المؤقتة
     */
    public function clearCache(Request $request)
    {
        $type = $request->input('type', 'all');

        try {
            switch ($type) {
                case 'config':
                    \Artisan::call('config:clear');
                    break;
                case 'route':
                    \Artisan::call('route:clear');
                    break;
                case 'view':
                    \Artisan::call('view:clear');
                    break;
                case 'cache':
                    \Artisan::call('cache:clear');
                    break;
                default:
                    \Artisan::call('optimize:clear');
            }

            return response()->json([
                'success' => true,
                'message' => 'تم مسح الذاكرة المؤقتة بنجاح',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * عرض صفحة إدارة قواعد البيانات
     */
    public function database()
    {
        $tables = $this->getDatabaseTables();
        
        return view('admin.database.index', compact('tables'));
    }

    /**
     * الحصول على جداول قاعدة البيانات
     */
    private function getDatabaseTables(): array
    {
        try {
            $database = config('database.connections.mysql.database');
            $tables = DB::select("
                SELECT 
                    table_name,
                    table_rows,
                    ROUND((data_length + index_length) / 1024 / 1024, 2) AS size_mb
                FROM information_schema.TABLES
                WHERE table_schema = ?
                ORDER BY (data_length + index_length) DESC
            ", [$database]);

            return array_map(function ($table) {
                return (array) $table;
            }, $tables);
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * عرض صفحة جدولة المهام
     */
    public function scheduler()
    {
        return view('admin.scheduler.index');
    }

    /**
     * عرض صفحة الإشعارات
     */
    public function notifications()
    {
        return view('admin.notifications.index');
    }

    /**
     * عرض صفحة API
     */
    public function api()
    {
        return view('admin.api.index');
    }

    /**
     * عرض صفحة التخزين
     */
    public function storage()
    {
        $storageInfo = [
            'total_size' => $this->getStorageSize(),
            'uploads_count' => $this->getUploadsCount(),
        ];

        return view('admin.storage.index', compact('storageInfo'));
    }

    /**
     * الحصول على حجم التخزين
     */
    private function getStorageSize(): string
    {
        try {
            $path = storage_path('app');
            $size = $this->getDirectorySize($path);
            return round($size / 1024 / 1024, 2) . ' MB';
        } catch (\Exception $e) {
            return 'N/A';
        }
    }

    /**
     * الحصول على عدد الملفات المرفوعة
     */
    private function getUploadsCount(): int
    {
        try {
            $path = storage_path('app/public');
            if (!is_dir($path)) {
                return 0;
            }
            $count = 0;
            foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path)) as $file) {
                if ($file->isFile()) {
                    $count++;
                }
            }
            return $count;
        } catch (\Exception $e) {
            return 0;
        }
    }
}
