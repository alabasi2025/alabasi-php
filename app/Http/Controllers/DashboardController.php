<?php

namespace App\Http\Controllers;

use App\Models\Main\Unit;
use App\Models\Main\Company;
use App\Models\Main\ClearingTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $is_main = session('is_main', false);
        
        if ($is_main) {
            return $this->mainDashboard();
        } else {
            return $this->unitDashboard();
        }
    }
    
    private function mainDashboard()
    {
        try {
            // إحصائيات عامة
            $total_units = Unit::count();
            $total_companies = Company::count();
            $total_transfers = ClearingTransaction::count();
            $completed_transfers = ClearingTransaction::where('status', 'completed')->count();
            $pending_transfers = ClearingTransaction::where('status', 'pending')->count();
            $total_amount = ClearingTransaction::where('status', 'completed')->sum('amount');
            
            // آخر التحويلات
            $recent_transfers = ClearingTransaction::with(['sourceCompany', 'targetCompany'])
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();
            
            // إحصائيات حسب الوحدة
            $units_stats = Unit::withCount('companies')->get()->map(function($unit) {
                $company_ids = $unit->companies->pluck('id');
                $transfers_count = ClearingTransaction::where(function($query) use ($company_ids) {
                    $query->whereIn('source_company_id', $company_ids)
                          ->orWhereIn('target_company_id', $company_ids);
                })->count();
                
                return [
                    'name' => $unit->name,
                    'companies_count' => $unit->companies_count,
                    'transfers_count' => $transfers_count
                ];
            });
            
            // إحصائيات حسب الشهر (آخر 6 أشهر)
            $monthly_stats = ClearingTransaction::selectRaw('
                    DATE_FORMAT(created_at, "%Y-%m") as month,
                    COUNT(*) as count,
                    SUM(amount) as total_amount
                ')
                ->where('created_at', '>=', now()->subMonths(6))
                ->groupBy('month')
                ->orderBy('month')
                ->get();
            
            // إحصائيات حسب النوع
            $type_stats = ClearingTransaction::selectRaw('
                    type,
                    COUNT(*) as count,
                    SUM(amount) as total_amount
                ')
                ->groupBy('type')
                ->get();
            
            return view('dashboard.main', compact(
                'total_units',
                'total_companies',
                'total_transfers',
                'completed_transfers',
                'pending_transfers',
                'total_amount',
                'recent_transfers',
                'units_stats',
                'monthly_stats',
                'type_stats'
            ));
        } catch (\Exception $e) {
            // في حالة وجود خطأ، عرض واجهة بسيطة
            return view('dashboard.main_simple', [
                'error' => $e->getMessage()
            ]);
        }
    }
    
    private function unitDashboard()
    {
        // واجهة وحدة العمل
        return view('dashboard.unit');
    }
}
