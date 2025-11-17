<?php

namespace App\Http\Controllers;

use App\Models\AnalyticalAccountType;
use App\Models\Company;
use Illuminate\Http\Request;

class AnalyticalAccountTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $company = Company::first();
        
        if (!$company) {
            return redirect()->route('companies.index')
                ->with('error', 'يجب إنشاء مؤسسة أولاً قبل إدارة أنواع الحسابات التحليلية');
        }

        $query = AnalyticalAccountType::where('company_id', $company->id);

        // Apply filters
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%");
            });
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->is_active);
        }

        $analyticalAccountTypes = $query->orderBy('code')->paginate(15);

        return view('analytical-account-types.index', compact('analyticalAccountTypes', 'company'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $company = Company::first();
        
        if (!$company) {
            return redirect()->route('companies.index')
                ->with('error', 'يجب إنشاء مؤسسة أولاً قبل إضافة أنواع الحسابات التحليلية');
        }

        return view('analytical-account-types.create', compact('company'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:20',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $company = Company::first();
        
        if (!$company) {
            return redirect()->route('companies.index')
                ->with('error', 'يجب إنشاء مؤسسة أولاً');
        }

        // Check if code already exists for this company
        $exists = AnalyticalAccountType::where('company_id', $company->id)
            ->where('code', $validated['code'])
            ->exists();

        if ($exists) {
            return back()->withInput()
                ->withErrors(['code' => 'رمز النوع موجود مسبقاً']);
        }

        $validated['company_id'] = $company->id;
        $validated['is_active'] = $request->has('is_active') ? 1 : 0;

        AnalyticalAccountType::create($validated);

        return redirect()->route('analytical-account-types.index')
            ->with('success', 'تم إضافة نوع الحساب التحليلي بنجاح');
    }

    /**
     * Display the specified resource.
     */
    public function show(AnalyticalAccountType $analyticalAccountType)
    {
        return view('analytical-account-types.show', compact('analyticalAccountType'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(AnalyticalAccountType $analyticalAccountType)
    {
        $company = $analyticalAccountType->company;
        return view('analytical-account-types.edit', compact('analyticalAccountType', 'company'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, AnalyticalAccountType $analyticalAccountType)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:20',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        // Check if code already exists for this company (excluding current record)
        $exists = AnalyticalAccountType::where('company_id', $analyticalAccountType->company_id)
            ->where('code', $validated['code'])
            ->where('id', '!=', $analyticalAccountType->id)
            ->exists();

        if ($exists) {
            return back()->withInput()
                ->withErrors(['code' => 'رمز النوع موجود مسبقاً']);
        }

        $validated['is_active'] = $request->has('is_active') ? 1 : 0;

        $analyticalAccountType->update($validated);

        return redirect()->route('analytical-account-types.index')
            ->with('success', 'تم تحديث نوع الحساب التحليلي بنجاح');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AnalyticalAccountType $analyticalAccountType)
    {
        // Check if there are accounts using this type
        if ($analyticalAccountType->accounts()->count() > 0) {
            return back()->with('error', 'لا يمكن حذف نوع الحساب التحليلي لأنه مرتبط بحسابات موجودة');
        }

        // Check if there are analytical accounts using this type
        if ($analyticalAccountType->analyticalAccounts()->count() > 0) {
            return back()->with('error', 'لا يمكن حذف نوع الحساب التحليلي لأنه مرتبط بحسابات تحليلية موجودة');
        }

        $analyticalAccountType->delete();

        return redirect()->route('analytical-account-types.index')
            ->with('success', 'تم حذف نوع الحساب التحليلي بنجاح');
    }
}
