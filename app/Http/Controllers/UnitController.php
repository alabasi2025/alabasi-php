<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use Illuminate\Http\Request;

class UnitController extends Controller
{
    /**
     * Display a listing of units.
     */
    public function index()
    {
        $units = Unit::with('companies')
                    ->withCount('companies')
                    ->orderBy('unit_name')
                    ->paginate(20);
        
        return view('units.index', compact('units'));
    }

    /**
     * Show the form for creating a new unit.
     */
    public function create()
    {
        return view('units.create');
    }

    /**
     * Store a newly created unit in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
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

        return redirect()->route('units.index')
                       ->with('success', 'تم إنشاء الوحدة بنجاح');
    }

    /**
     * Display the specified unit.
     */
    public function show(Unit $unit)
    {
        $unit->load(['companies.branches']);
        
        return view('units.show', compact('unit'));
    }

    /**
     * Show the form for editing the specified unit.
     */
    public function edit(Unit $unit)
    {
        return view('units.edit', compact('unit'));
    }

    /**
     * Update the specified unit in storage.
     */
    public function update(Request $request, Unit $unit)
    {
        $validated = $request->validate([
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

        return redirect()->route('units.index')
                       ->with('success', 'تم تحديث الوحدة بنجاح');
    }

    /**
     * Remove the specified unit from storage.
     */
    public function destroy(Unit $unit)
    {
        // Check if unit has companies
        if ($unit->companies()->count() > 0) {
            return back()->with('error', 'لا يمكن حذف الوحدة لأنها تحتوي على مؤسسات');
        }

        $unit->delete();

        return redirect()->route('units.index')
                       ->with('success', 'تم حذف الوحدة بنجاح');
    }
}
