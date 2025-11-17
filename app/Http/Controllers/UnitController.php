<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use App\Models\Company;
use Illuminate\Http\Request;

class UnitController extends Controller
{
    /**
     * Display a listing of units.
     */
    public function index()
    {
        $units = Unit::with('company')
                    ->withCount('branches')
                    ->orderBy('unit_name')
                    ->paginate(20);
        
        return view('units.index', compact('units'));
    }

    /**
     * Show the form for creating a new unit.
     */
    public function create()
    {
        $companies = Company::active()->get();
        
        return view('units.create', compact('companies'));
    }

    /**
     * Store a newly created unit in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'unit_code' => 'required|string|max:50|unique:units',
            'unit_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:100',
            'manager_name' => 'nullable|string|max:255',
            'is_active' => 'boolean'
        ]);

        $unit = Unit::create($validated);

        return redirect()->route('units.show', $unit)
                       ->with('success', 'تم إنشاء الوحدة بنجاح');
    }

    /**
     * Display the specified unit.
     */
    public function show(Unit $unit)
    {
        $unit->load(['company', 'branches']);
        
        return view('units.show', compact('unit'));
    }

    /**
     * Show the form for editing the specified unit.
     */
    public function edit(Unit $unit)
    {
        $companies = Company::active()->get();
        
        return view('units.edit', compact('unit', 'companies'));
    }

    /**
     * Update the specified unit in storage.
     */
    public function update(Request $request, Unit $unit)
    {
        $validated = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'unit_code' => 'required|string|max:50|unique:units,unit_code,' . $unit->id,
            'unit_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:100',
            'manager_name' => 'nullable|string|max:255',
            'is_active' => 'boolean'
        ]);

        $unit->update($validated);

        return redirect()->route('units.show', $unit)
                       ->with('success', 'تم تحديث الوحدة بنجاح');
    }

    /**
     * Remove the specified unit from storage.
     */
    public function destroy(Unit $unit)
    {
        // Check if unit has branches
        if ($unit->branches()->count() > 0) {
            return back()->with('error', 'لا يمكن حذف الوحدة لأنها تحتوي على فروع');
        }

        $unit->delete();

        return redirect()->route('units.index')
                       ->with('success', 'تم حذف الوحدة بنجاح');
    }
    
    /**
     * Get units by company (AJAX)
     */
    public function getByCompany(Request $request)
    {
        $companyId = $request->get('company_id');
        
        $units = Unit::where('company_id', $companyId)
                    ->where('is_active', true)
                    ->get(['id', 'unit_code', 'unit_name']);
        
        return response()->json($units);
    }
}
