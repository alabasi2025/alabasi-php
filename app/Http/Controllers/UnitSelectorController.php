<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use Illuminate\Http\Request;

class UnitSelectorController extends Controller
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
        
        return redirect()->back()->with('success', 'تم تغيير الوحدة إلى: ' . $unit->unit_name);
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
                return $unit;
            }
            
            return null;
        }
        
        return Unit::find($unitId);
    }
    
    /**
     * الحصول على ID الوحدة النشطة
     */
    public static function getActiveUnitId()
    {
        $unit = self::getActiveUnit();
        return $unit ? $unit->id : null;
    }
}
