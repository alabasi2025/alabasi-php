<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use App\Models\Company;
use Illuminate\Http\Request;

/**
 * Controller لإدارة سياق العمل (الوحدة والمؤسسة النشطة)
 */
class ContextSelectorController extends Controller
{
    /**
     * تعيين الوحدة النشطة
     */
    public function setActiveUnit(Request $request)
    {
        $unitId = $request->input('unit_id');
        
        // التحقق من وجود الوحدة
        $unit = Unit::findOrFail($unitId);
        
        // حفظ الوحدة في Session
        session(['active_unit_id' => $unit->id]);
        session(['active_unit_name' => $unit->unit_name]);
        
        // تعيين أول مؤسسة في هذه الوحدة كمؤسسة نشطة
        $firstCompany = Company::where('unit_id', $unit->id)
            ->where('is_active', true)
            ->first();
        
        if ($firstCompany) {
            session(['active_company_id' => $firstCompany->id]);
            session(['active_company_name' => $firstCompany->company_name]);
        } else {
            session()->forget(['active_company_id', 'active_company_name']);
        }
        
        return redirect()->back()->with('success', 'تم تغيير الوحدة إلى: ' . $unit->unit_name);
    }
    
    /**
     * تعيين المؤسسة النشطة
     */
    public function setActiveCompany(Request $request)
    {
        $companyId = $request->input('company_id');
        
        // التحقق من وجود المؤسسة
        $company = Company::with('unit')->findOrFail($companyId);
        
        // التحقق من أن المؤسسة تابعة للوحدة النشطة
        $activeUnitId = session('active_unit_id');
        if ($company->unit_id != $activeUnitId) {
            return redirect()->back()->with('error', 'هذه المؤسسة غير تابعة للوحدة النشطة');
        }
        
        // حفظ المؤسسة في Session
        session(['active_company_id' => $company->id]);
        session(['active_company_name' => $company->company_name]);
        
        return redirect()->back()->with('success', 'تم تغيير المؤسسة إلى: ' . $company->company_name);
    }
    
    /**
     * الحصول على الوحدة النشطة
     */
    public static function getActiveUnit()
    {
        $unitId = session('active_unit_id');
        
        if (!$unitId) {
            // إذا لم تكن هناك وحدة محددة، اختر أول وحدة نشطة
            $unit = Unit::where('is_active', true)->first();
            
            if ($unit) {
                session(['active_unit_id' => $unit->id]);
                session(['active_unit_name' => $unit->unit_name]);
                
                // تعيين أول مؤسسة في هذه الوحدة
                $firstCompany = Company::where('unit_id', $unit->id)
                    ->where('is_active', true)
                    ->first();
                
                if ($firstCompany) {
                    session(['active_company_id' => $firstCompany->id]);
                    session(['active_company_name' => $firstCompany->company_name]);
                }
                
                return $unit;
            }
            
            return null;
        }
        
        return Unit::find($unitId);
    }
    
    /**
     * الحصول على المؤسسة النشطة
     */
    public static function getActiveCompany()
    {
        $companyId = session('active_company_id');
        
        if (!$companyId) {
            // إذا لم تكن هناك مؤسسة محددة، حاول تعيين أول مؤسسة في الوحدة النشطة
            $unit = self::getActiveUnit();
            
            if ($unit) {
                $firstCompany = Company::where('unit_id', $unit->id)
                    ->where('is_active', true)
                    ->first();
                
                if ($firstCompany) {
                    session(['active_company_id' => $firstCompany->id]);
                    session(['active_company_name' => $firstCompany->company_name]);
                    return $firstCompany;
                }
            }
            
            return null;
        }
        
        return Company::find($companyId);
    }
    
    /**
     * الحصول على ID الوحدة النشطة
     */
    public static function getActiveUnitId()
    {
        $unit = self::getActiveUnit();
        return $unit ? $unit->id : null;
    }
    
    /**
     * الحصول على ID المؤسسة النشطة
     */
    public static function getActiveCompanyId()
    {
        $company = self::getActiveCompany();
        return $company ? $company->id : null;
    }
    
    /**
     * عرض صفحة اختيار السياق
     */
    public function showSelector()
    {
        $units = Unit::where('is_active', true)->orderBy('unit_name')->get();
        $activeUnit = self::getActiveUnit();
        $activeCompany = self::getActiveCompany();
        
        $companies = [];
        if ($activeUnit) {
            $companies = Company::where('unit_id', $activeUnit->id)
                ->where('is_active', true)
                ->orderBy('company_name')
                ->get();
        }
        
        return view('context.selector', compact('units', 'companies', 'activeUnit', 'activeCompany'));
    }
}
