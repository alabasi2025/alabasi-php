<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Company;
use App\Models\Unit;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $companyId = $request->get('company_id', 1);
        $company = Company::with('unit')->findOrFail($companyId);
        $companies = Company::with('unit')->get();
        
        // تحميل الحسابات بشكل متداخل (5 مستويات)
        $accounts = Account::where('company_id', $companyId)
            ->whereNull('parent_id')
            ->with([
                'children' => function($q) {
                    $q->orderBy('code');
                },
                'children.children' => function($q) {
                    $q->orderBy('code');
                },
                'children.children.children' => function($q) {
                    $q->orderBy('code');
                },
                'children.children.children.children' => function($q) {
                    $q->orderBy('code');
                }
            ])
            ->orderBy('code')
            ->get();
        
        return view('accounts.index', compact('accounts', 'company', 'companies'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        // Get active company from session
        $companyId = session('active_company_id');
        $company = Company::with('unit')->findOrFail($companyId);
        
        // Get all account types for this company
        $accountTypes = \App\Models\AccountType::where('company_id', $companyId)->get();
        
        // Get parent accounts for this company
        $parentAccounts = Account::where('company_id', $companyId)
            ->where('is_parent', true)
            ->orderBy('code')
            ->get();
        
        return view('accounts.create', compact('company', 'parentAccounts', 'accountTypes'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:20|unique:accounts,code',
            'name_ar' => 'required|string|max:255',
            'name_en' => 'nullable|string|max:255',
            'parent_id' => 'nullable|exists:accounts,id',
            'is_parent' => 'boolean',
            'allow_posting' => 'boolean',
            'account_nature' => 'nullable|string|in:general,cash_box,bank,customer,supplier,employee,debtor,creditor',
            'description' => 'nullable|string',
        ]);

        // Set default values
        $validated['is_parent'] = $request->has('is_parent') ? true : false;
        $validated['allow_posting'] = $request->has('allow_posting') ? true : false;
        
        // Add unit_id and company_id from session
        $validated['unit_id'] = session('active_unit_id');
        $validated['company_id'] = session('active_company_id');

        $account = Account::create($validated);

        return redirect()
            ->route('accounts.index', ['company_id' => $account->company_id])
            ->with('success', 'تم إضافة الحساب بنجاح');
    }

    /**
     * Display the specified resource.
     */
    public function show(Account $account)
    {
        $account->load(['parent', 'children', 'company.unit']);
        
        return view('accounts.show', compact('account'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Account $account)
    {
        $companies = Company::with('unit')->get();
        $company = $account->company;
        
        // Get all accounts for parent selection (excluding current account and its children)
        $parentAccounts = Account::where('company_id', $account->company_id)
            ->where('is_parent', true)
            ->where('id', '!=', $account->id)
            ->orderBy('code')
            ->get();
        
        return view('accounts.edit', compact('account', 'company', 'companies', 'parentAccounts'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Account $account)
    {
        $validated = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'code' => 'required|string|max:20|unique:accounts,code,' . $account->id,
            'name_ar' => 'required|string|max:255',
            'name_en' => 'nullable|string|max:255',
            'parent_id' => 'nullable|exists:accounts,id',
            'is_parent' => 'boolean',
            'allow_posting' => 'boolean',
            'analytical_type' => 'nullable|string|max:50',
            'description' => 'nullable|string',
        ]);

        // Set default values
        $validated['is_parent'] = $request->has('is_parent') ? true : false;
        $validated['allow_posting'] = $request->has('allow_posting') ? true : false;

        $account->update($validated);

        return redirect()
            ->route('accounts.index', ['company_id' => $account->company_id])
            ->with('success', 'تم تحديث الحساب بنجاح');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Account $account)
    {
        $companyId = $account->company_id;
        
        // Check if account has children
        if ($account->children()->count() > 0) {
            return redirect()
                ->route('accounts.index', ['company_id' => $companyId])
                ->with('error', 'لا يمكن حذف الحساب لأنه يحتوي على حسابات فرعية');
        }
        
        // Check if account has transactions (if you have a transactions table)
        // if ($account->transactions()->count() > 0) {
        //     return redirect()
        //         ->route('accounts.index', ['company_id' => $companyId])
        //         ->with('error', 'لا يمكن حذف الحساب لأنه يحتوي على حركات مالية');
        // }
        
        $account->delete();

        return redirect()
            ->route('accounts.index', ['company_id' => $companyId])
            ->with('success', 'تم حذف الحساب بنجاح');
    }
}
