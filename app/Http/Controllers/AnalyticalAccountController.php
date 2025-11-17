<?php

namespace App\Http\Controllers;

use App\Models\AnalyticalAccount;
use App\Models\AnalyticalAccountType;
use App\Models\Account;
use App\Models\Company;
use Illuminate\Http\Request;

class AnalyticalAccountController extends Controller
{
    public function index(Request $request)
    {
        // Get selected company from request or session
        $companyId = $request->get('company_id', session('selected_company_id'));
        
        // Get all companies for selection
        $companies = Company::where('is_active', true)->orderBy('company_name')->get();
        
        if ($companies->isEmpty()) {
            return redirect()->route('companies.index')->with('error', 'يجب إنشاء مؤسسة أولاً');
        }
        
        // If no company selected, use first company
        if (!$companyId) {
            $companyId = $companies->first()->id;
        }
        
        // Save selected company in session
        session(['selected_company_id' => $companyId]);
        
        $company = Company::find($companyId);
        
        if (!$company) {
            return redirect()->route('companies.index')->with('error', 'المؤسسة المحددة غير موجودة');
        }

        $query = AnalyticalAccount::where('company_id', $company->id)
            ->with(['analyticalAccountType', 'account']);

        if ($request->filled('analytical_account_type_id')) {
            $query->where('analytical_account_type_id', $request->analytical_account_type_id);
        }

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

        $analyticalAccounts = $query->orderBy('code')->paginate(20);

        $analyticalAccountTypes = AnalyticalAccountType::where('company_id', $company->id)
            ->where('is_active', true)
            ->orderBy('code')
            ->get();

        return view('analytical-accounts.index', compact('analyticalAccounts', 'company', 'companies', 'analyticalAccountTypes'));
    }

    public function create(Request $request)
    {
        $company = Company::first();
        
        if (!$company) {
            return redirect()->route('companies.index')->with('error', 'يجب إنشاء مؤسسة أولاً');
        }

        $analyticalAccountTypeId = $request->get('analytical_account_type_id');
        $analyticalAccountType = $analyticalAccountTypeId ? AnalyticalAccountType::find($analyticalAccountTypeId) : null;

        $analyticalAccountTypes = AnalyticalAccountType::where('company_id', $company->id)
            ->where('is_active', true)
            ->orderBy('code')
            ->get();

        $accounts = collect();
        if ($analyticalAccountType) {
            $accounts = Account::where('company_id', $company->id)
                ->where('analytical_account_type_id', $analyticalAccountType->id)
                ->where('is_main', false)
                ->where('is_active', true)
                ->orderBy('account_code')
                ->get();
        }

        return view('analytical-accounts.create', compact('company', 'analyticalAccountType', 'analyticalAccountTypes', 'accounts'));
    }

    public function store(Request $request)
    {
        $company = Company::first();
        
        if (!$company) {
            return redirect()->route('companies.index')->with('error', 'يجب إنشاء مؤسسة أولاً');
        }

        $validated = $request->validate([
            'code' => 'required|string|max:50',
            'name' => 'required|string|max:255',
            'analytical_account_type_id' => 'required|exists:analytical_account_types,id',
            'account_id' => 'required|exists:accounts,id',
            'description' => 'nullable|string',
            'contact_info' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $exists = AnalyticalAccount::where('company_id', $company->id)
            ->where('code', $validated['code'])
            ->exists();

        if ($exists) {
            return back()->withInput()->withErrors(['code' => 'رمز الحساب التحليلي موجود مسبقاً']);
        }

        $validated['company_id'] = $company->id;
        $validated['is_active'] = $request->has('is_active') ? 1 : 0;

        AnalyticalAccount::create($validated);

        return redirect()->route('analytical-accounts.index')->with('success', 'تم إضافة الحساب التحليلي بنجاح');
    }

    public function edit(AnalyticalAccount $analyticalAccount)
    {
        $company = $analyticalAccount->company;

        $analyticalAccountTypes = AnalyticalAccountType::where('company_id', $company->id)
            ->where('is_active', true)
            ->orderBy('code')
            ->get();

        $accounts = Account::where('company_id', $company->id)
            ->where('analytical_account_type_id', $analyticalAccount->analytical_account_type_id)
            ->where('is_main', false)
            ->where('is_active', true)
            ->orderBy('account_code')
            ->get();

        return view('analytical-accounts.edit', compact('analyticalAccount', 'company', 'analyticalAccountTypes', 'accounts'));
    }

    public function update(Request $request, AnalyticalAccount $analyticalAccount)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50',
            'name' => 'required|string|max:255',
            'analytical_account_type_id' => 'required|exists:analytical_account_types,id',
            'account_id' => 'required|exists:accounts,id',
            'description' => 'nullable|string',
            'contact_info' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $exists = AnalyticalAccount::where('company_id', $analyticalAccount->company_id)
            ->where('code', $validated['code'])
            ->where('id', '!=', $analyticalAccount->id)
            ->exists();

        if ($exists) {
            return back()->withInput()->withErrors(['code' => 'رمز الحساب التحليلي موجود مسبقاً']);
        }

        $validated['is_active'] = $request->has('is_active') ? 1 : 0;

        $analyticalAccount->update($validated);

        return redirect()->route('analytical-accounts.index')->with('success', 'تم تحديث الحساب التحليلي بنجاح');
    }

    public function destroy(AnalyticalAccount $analyticalAccount)
    {
        $analyticalAccount->delete();
        return redirect()->route('analytical-accounts.index')->with('success', 'تم حذف الحساب التحليلي بنجاح');
    }

    public function getAccountsByType(Request $request)
    {
        $company = Company::first();
        
        if (!$company) {
            return response()->json(['error' => 'No company found'], 404);
        }

        $analyticalAccountTypeId = $request->get('analytical_account_type_id');

        if (!$analyticalAccountTypeId) {
            return response()->json(['error' => 'Analytical account type ID is required'], 400);
        }

        $accounts = Account::where('company_id', $company->id)
            ->where('analytical_account_type_id', $analyticalAccountTypeId)
            ->where('is_main', false)
            ->where('is_active', true)
            ->orderBy('account_code')
            ->get(['id', 'account_code', 'name']);

        return response()->json($accounts);
    }
}
