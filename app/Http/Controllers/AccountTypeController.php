<?php

namespace App\Http\Controllers;

use App\Models\AccountType;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AccountTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Get current company (for now, we'll use the first company)
        // TODO: Implement proper company selection based on user session
        $company = Company::first();
        
        if (!$company) {
            return redirect()->route('companies.index')
                ->with('error', 'يجب إنشاء مؤسسة أولاً قبل إدارة أنواع الحسابات');
        }

        // Get account types for the current company
        $query = AccountType::where('company_id', $company->id);

        // Apply filters
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%");
            });
        }

        if ($request->filled('nature')) {
            $query->where('nature', $request->nature);
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->is_active);
        }

        $accountTypes = $query->orderBy('code')->paginate(15);

        return view('account-types.index', compact('accountTypes', 'company'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $company = Company::first();
        
        if (!$company) {
            return redirect()->route('companies.index')
                ->with('error', 'يجب إنشاء مؤسسة أولاً قبل إضافة أنواع الحسابات');
        }

        return view('account-types.create', compact('company'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:20',
            'name' => 'required|string|max:255',
            'nature' => 'required|in:debit,credit',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $company = Company::first();
        
        if (!$company) {
            return redirect()->route('companies.index')
                ->with('error', 'يجب إنشاء مؤسسة أولاً');
        }

        // Check if code already exists for this company
        $exists = AccountType::where('company_id', $company->id)
            ->where('code', $validated['code'])
            ->exists();

        if ($exists) {
            return back()->withInput()
                ->withErrors(['code' => 'رمز النوع موجود مسبقاً']);
        }

        $validated['company_id'] = $company->id;
        $validated['is_active'] = $request->has('is_active') ? 1 : 0;

        AccountType::create($validated);

        return redirect()->route('account-types.index')
            ->with('success', 'تم إضافة نوع الحساب بنجاح');
    }

    /**
     * Display the specified resource.
     */
    public function show(AccountType $accountType)
    {
        return view('account-types.show', compact('accountType'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(AccountType $accountType)
    {
        $company = $accountType->company;
        return view('account-types.edit', compact('accountType', 'company'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, AccountType $accountType)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:20',
            'name' => 'required|string|max:255',
            'nature' => 'required|in:debit,credit',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        // Check if code already exists for this company (excluding current record)
        $exists = AccountType::where('company_id', $accountType->company_id)
            ->where('code', $validated['code'])
            ->where('id', '!=', $accountType->id)
            ->exists();

        if ($exists) {
            return back()->withInput()
                ->withErrors(['code' => 'رمز النوع موجود مسبقاً']);
        }

        $validated['is_active'] = $request->has('is_active') ? 1 : 0;

        $accountType->update($validated);

        return redirect()->route('account-types.index')
            ->with('success', 'تم تحديث نوع الحساب بنجاح');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AccountType $accountType)
    {
        // Check if there are accounts using this type
        if ($accountType->accounts()->count() > 0) {
            return back()->with('error', 'لا يمكن حذف نوع الحساب لأنه مرتبط بحسابات موجودة');
        }

        $accountType->delete();

        return redirect()->route('account-types.index')
            ->with('success', 'تم حذف نوع الحساب بنجاح');
    }
}
