<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\AccountType;
use App\Models\AnalyticalAccountType;
use App\Models\Company;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    /**
     * Display a listing of the resource (Tree View).
     */
    public function index(Request $request)
    {
        // Get selected company from request or session
        $companyId = $request->get('company_id', session('selected_company_id'));
        
        // Get all companies for selection
        $companies = Company::where('is_active', true)->orderBy('company_name')->get();
        
        if ($companies->isEmpty()) {
            return redirect()->route('companies.index')
                ->with('error', 'يجب إنشاء مؤسسة أولاً قبل إدارة دليل الحسابات');
        }
        
        // If no company selected, use first company
        if (!$companyId) {
            $companyId = $companies->first()->id;
        }
        
        // Save selected company in session
        session(['selected_company_id' => $companyId]);
        
        $company = Company::find($companyId);
        
        if (!$company) {
            return redirect()->route('companies.index')
                ->with('error', 'المؤسسة المحددة غير موجودة');
        }

        // Get filters
        $accountTypeId = $request->get('account_type_id');
        $analyticalTypeId = $request->get('analytical_account_type_id');
        $search = $request->get('search');
        $isActive = $request->get('is_active');

        // Build query for root accounts (no parent)
        $query = Account::where('company_id', $company->id)
            ->with(['accountType', 'analyticalAccountType', 'children' => function($q) {
                $q->orderBy('code');
            }])
            ->whereNull('parent_id');

        // Apply filters
        if ($accountTypeId) {
            $query->where('account_type_id', $accountTypeId);
        }

        if ($analyticalTypeId) {
            $query->where('analytical_account_type_id', $analyticalTypeId);
        }

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%");
            });
        }

        if ($isActive !== null && $isActive !== '') {
            $query->where('is_active', $isActive);
        }

        $accounts = $query->orderBy('code')->get();

        // Get filter options
        $accountTypes = AccountType::where('company_id', $company->id)
            ->where('is_active', true)
            ->orderBy('code')
            ->get();

        $analyticalAccountTypes = AnalyticalAccountType::where('company_id', $company->id)
            ->where('is_active', true)
            ->orderBy('code')
            ->get();

        return view('accounts.index', compact('accounts', 'company', 'companies', 'accountTypes', 'analyticalAccountTypes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $company = Company::first();
        
        if (!$company) {
            return redirect()->route('companies.index')
                ->with('error', 'يجب إنشاء مؤسسة أولاً');
        }

        // Get parent account if specified
        $parentId = $request->get('parent_id');
        $parentAccount = $parentId ? Account::find($parentId) : null;

        // Get all accounts for parent selection (only main accounts can be parents)
        $parentAccounts = Account::where('company_id', $company->id)
            ->where('is_main', true)
            ->orderBy('code')
            ->get();

        // Get account types
        $accountTypes = AccountType::where('company_id', $company->id)
            ->where('is_active', true)
            ->orderBy('code')
            ->get();

        // Get analytical account types
        $analyticalAccountTypes = AnalyticalAccountType::where('company_id', $company->id)
            ->where('is_active', true)
            ->orderBy('code')
            ->get();

        return view('accounts.create', compact('company', 'parentAccount', 'parentAccounts', 'accountTypes', 'analyticalAccountTypes'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $company = Company::first();
        
        if (!$company) {
            return redirect()->route('companies.index')
                ->with('error', 'يجب إنشاء مؤسسة أولاً');
        }

        $validated = $request->validate([
            'account_code' => 'required|string|max:50',
            'name' => 'required|string|max:255',
            'account_type_id' => 'required|exists:account_types,id',
            'is_main' => 'required|boolean',
            'parent_id' => 'nullable|exists:accounts,id',
            'analytical_account_type_id' => 'nullable|exists:analytical_account_types,id',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        // Check if account code already exists for this company
        $exists = Account::where('company_id', $company->id)
            ->where('code', $validated['account_code'])
            ->exists();

        if ($exists) {
            return back()->withInput()
                ->withErrors(['account_code' => 'رمز الحساب موجود مسبقاً']);
        }

        // Validate: if is_main = false, analytical_account_type_id is required
        if (!$validated['is_main'] && empty($validated['analytical_account_type_id'])) {
            return back()->withInput()
                ->withErrors(['analytical_account_type_id' => 'يجب تحديد نوع الحساب التحليلي للحسابات الفرعية']);
        }

        // Validate: if is_main = true, analytical_account_type_id must be null
        if ($validated['is_main']) {
            $validated['analytical_account_type_id'] = null;
        }

        $validated['company_id'] = $company->id;
        $validated['is_active'] = $request->has('is_active') ? 1 : 0;

        Account::create($validated);

        return redirect()->route('accounts.index')
            ->with('success', 'تم إضافة الحساب بنجاح');
    }

    /**
     * Display the specified resource.
     */
    public function show(Account $account)
    {
        $account->load(['accountType', 'analyticalAccountType', 'parent', 'children', 'company']);
        return view('accounts.show', compact('account'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Account $account)
    {
        $company = $account->company;

        // Get all accounts for parent selection (only main accounts, excluding current and its children)
        $parentAccounts = Account::where('company_id', $company->id)
            ->where('is_main', true)
            ->where('id', '!=', $account->id)
            ->orderBy('code')
            ->get();

        // Get account types
        $accountTypes = AccountType::where('company_id', $company->id)
            ->where('is_active', true)
            ->orderBy('code')
            ->get();

        // Get analytical account types
        $analyticalAccountTypes = AnalyticalAccountType::where('company_id', $company->id)
            ->where('is_active', true)
            ->orderBy('code')
            ->get();

        return view('accounts.edit', compact('account', 'company', 'parentAccounts', 'accountTypes', 'analyticalAccountTypes'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Account $account)
    {
        $validated = $request->validate([
            'account_code' => 'required|string|max:50',
            'name' => 'required|string|max:255',
            'account_type_id' => 'required|exists:account_types,id',
            'is_main' => 'required|boolean',
            'parent_id' => 'nullable|exists:accounts,id',
            'analytical_account_type_id' => 'nullable|exists:analytical_account_types,id',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        // Check if account code already exists for this company (excluding current)
        $exists = Account::where('company_id', $account->company_id)
            ->where('code', $validated['account_code'])
            ->where('id', '!=', $account->id)
            ->exists();

        if ($exists) {
            return back()->withInput()
                ->withErrors(['account_code' => 'رمز الحساب موجود مسبقاً']);
        }

        // Validate: if is_main = false, analytical_account_type_id is required
        if (!$validated['is_main'] && empty($validated['analytical_account_type_id'])) {
            return back()->withInput()
                ->withErrors(['analytical_account_type_id' => 'يجب تحديد نوع الحساب التحليلي للحسابات الفرعية']);
        }

        // Validate: if is_main = true, analytical_account_type_id must be null
        if ($validated['is_main']) {
            $validated['analytical_account_type_id'] = null;
        }

        // Prevent setting parent to self or its own children
        if ($validated['parent_id'] == $account->id) {
            return back()->withInput()
                ->withErrors(['parent_id' => 'لا يمكن ربط الحساب بنفسه']);
        }

        $validated['is_active'] = $request->has('is_active') ? 1 : 0;

        $account->update($validated);

        return redirect()->route('accounts.index')
            ->with('success', 'تم تحديث الحساب بنجاح');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Account $account)
    {
        // Check if account has children
        if ($account->children()->count() > 0) {
            return back()->with('error', 'لا يمكن حذف الحساب لأنه يحتوي على حسابات فرعية');
        }

        // Check if account has transactions (vouchers, journal entries, etc.)
        // TODO: Add checks for vouchers and journal entries when implemented

        $account->delete();

        return redirect()->route('accounts.index')
            ->with('success', 'تم حذف الحساب بنجاح');
    }

    /**
     * Get accounts as JSON (for AJAX requests).
     */
    public function getAccounts(Request $request)
    {
        $company = Company::first();
        
        if (!$company) {
            return response()->json(['error' => 'No company found'], 404);
        }

        $query = Account::where('company_id', $company->id)
            ->where('is_active', true);

        // Filter by analytical account type
        if ($request->filled('analytical_account_type_id')) {
            $query->where('analytical_account_type_id', $request->analytical_account_type_id);
        }

        // Filter by account type
        if ($request->filled('account_type_id')) {
            $query->where('account_type_id', $request->account_type_id);
        }

        // Only sub accounts (not main)
        if ($request->filled('sub_only')) {
            $query->where('is_main', false);
        }

        $accounts = $query->orderBy('code')
            ->get(['id', 'code', 'name']);

        return response()->json($accounts);
    }
}
