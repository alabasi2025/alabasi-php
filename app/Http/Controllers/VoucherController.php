<?php

namespace App\Http\Controllers;

use App\Models\Voucher;
use App\Models\Account;
use App\Models\AnalyticalAccount;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class VoucherController extends Controller
{
    /**
     * Display a listing of vouchers.
     */
    public function index(Request $request)
    {
        $query = Voucher::with(['account', 'analyticalAccount', 'creator', 'branch']);
        
        // Filter by type
        if ($request->has('type') && in_array($request->type, ['payment', 'receipt'])) {
            $query->where('voucher_type', $request->type);
        }
        
        // Filter by payment method
        if ($request->has('method') && in_array($request->method, ['cash', 'bank'])) {
            $query->where('payment_method', $request->method);
        }
        
        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        
        // Filter by date range
        if ($request->has('from_date')) {
            $query->where('voucher_date', '>=', $request->from_date);
        }
        if ($request->has('to_date')) {
            $query->where('voucher_date', '<=', $request->to_date);
        }
        
        $vouchers = $query->orderBy('voucher_date', 'desc')
                          ->orderBy('voucher_number', 'desc')
                          ->paginate(20);
        
        return view('vouchers.index', compact('vouchers'));
    }

    /**
     * Show the form for creating a new voucher.
     */
    public function create(Request $request)
    {
        $type = $request->get('type', 'payment'); // payment or receipt
        
        // Get branches for selection
        $branches = Branch::all();
        
        // Get analytical accounts (customers/suppliers)
        $analyticalAccounts = AnalyticalAccount::all();
        
        return view('vouchers.create', compact('type', 'branches', 'analyticalAccounts'));
    }

    /**
     * Store a newly created voucher in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'voucher_type' => 'required|in:payment,receipt',
            'payment_method' => 'required|in:cash,bank',
            'voucher_date' => 'required|date',
            'amount' => 'required|numeric|min:0.01',
            'currency' => 'required|string|max:3',
            'beneficiary_name' => 'required|string|max:255',
            'analytical_account_id' => 'nullable|exists:analytical_accounts,id',
            'account_id' => 'required|exists:accounts,id',
            'description' => 'nullable|string',
            'notes' => 'nullable|string',
            'branch_id' => 'nullable|exists:branches,id',
        ]);
        
        DB::beginTransaction();
        try {
            // Generate voucher number
            $prefix = $validated['voucher_type'] === 'payment' ? 'PAY' : 'REC';
            $lastVoucher = Voucher::where('voucher_type', $validated['voucher_type'])
                                  ->orderBy('id', 'desc')
                                  ->first();
            $number = $lastVoucher ? (int)substr($lastVoucher->voucher_number, strlen($prefix)) + 1 : 1;
            $validated['voucher_number'] = $prefix . str_pad($number, 6, '0', STR_PAD_LEFT);
            
            // Set creator
            $validated['created_by'] = Auth::id();
            $validated['status'] = 'draft';
            
            $voucher = Voucher::create($validated);
            
            DB::commit();
            
            return redirect()->route('vouchers.show', $voucher)
                           ->with('success', 'تم إنشاء السند بنجاح');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()
                       ->with('error', 'حدث خطأ أثناء إنشاء السند: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified voucher.
     */
    public function show(Voucher $voucher)
    {
        $voucher->load(['account', 'analyticalAccount', 'creator', 'approver', 'branch', 'journalEntry']);
        return view('vouchers.show', compact('voucher'));
    }

    /**
     * Show the form for editing the specified voucher.
     */
    public function edit(Voucher $voucher)
    {
        // Only draft vouchers can be edited
        if ($voucher->status !== 'draft') {
            return redirect()->route('vouchers.show', $voucher)
                           ->with('error', 'لا يمكن تعديل السند إلا إذا كان في حالة مسودة');
        }
        
        $branches = Branch::all();
        $analyticalAccounts = AnalyticalAccount::all();
        
        // Get accounts for the current branch and payment method
        $accounts = Account::where('branch_id', $voucher->branch_id)
                          ->where('account_type', $voucher->payment_method === 'cash' ? 'cash' : 'bank')
                          ->get();
        
        return view('vouchers.edit', compact('voucher', 'branches', 'analyticalAccounts', 'accounts'));
    }

    /**
     * Update the specified voucher in storage.
     */
    public function update(Request $request, Voucher $voucher)
    {
        // Only draft vouchers can be updated
        if ($voucher->status !== 'draft') {
            return redirect()->route('vouchers.show', $voucher)
                           ->with('error', 'لا يمكن تعديل السند إلا إذا كان في حالة مسودة');
        }
        
        $validated = $request->validate([
            'voucher_date' => 'required|date',
            'amount' => 'required|numeric|min:0.01',
            'currency' => 'required|string|max:3',
            'beneficiary_name' => 'required|string|max:255',
            'analytical_account_id' => 'nullable|exists:analytical_accounts,id',
            'account_id' => 'required|exists:accounts,id',
            'description' => 'nullable|string',
            'notes' => 'nullable|string',
            'branch_id' => 'nullable|exists:branches,id',
        ]);
        
        $voucher->update($validated);
        
        return redirect()->route('vouchers.show', $voucher)
                       ->with('success', 'تم تحديث السند بنجاح');
    }

    /**
     * Remove the specified voucher from storage.
     */
    public function destroy(Voucher $voucher)
    {
        // Only draft vouchers can be deleted
        if ($voucher->status !== 'draft') {
            return back()->with('error', 'لا يمكن حذف السند إلا إذا كان في حالة مسودة');
        }
        
        $voucher->delete();
        
        return redirect()->route('vouchers.index')
                       ->with('success', 'تم حذف السند بنجاح');
    }
    
    /**
     * Approve the voucher
     */
    public function approve(Voucher $voucher)
    {
        if ($voucher->status !== 'pending' && $voucher->status !== 'draft') {
            return back()->with('error', 'لا يمكن اعتماد هذا السند');
        }
        
        $voucher->update([
            'status' => 'approved',
            'approved_by' => Auth::id(),
            'approved_at' => now()
        ]);
        
        return back()->with('success', 'تم اعتماد السند بنجاح');
    }
    
    /**
     * Reject the voucher
     */
    public function reject(Voucher $voucher)
    {
        if ($voucher->status !== 'pending') {
            return back()->with('error', 'لا يمكن رفض هذا السند');
        }
        
        $voucher->update([
            'status' => 'rejected'
        ]);
        
        return back()->with('success', 'تم رفض السند');
    }
    
    /**
     * Submit voucher for approval
     */
    public function submit(Voucher $voucher)
    {
        if ($voucher->status !== 'draft') {
            return back()->with('error', 'لا يمكن إرسال هذا السند للاعتماد');
        }
        
        $voucher->update([
            'status' => 'pending'
        ]);
        
        return back()->with('success', 'تم إرسال السند للاعتماد');
    }
    
    /**
     * Get accounts filtered by branch and type
     */
    public function getAccounts(Request $request)
    {
        $branchId = $request->get('branch_id');
        $paymentMethod = $request->get('payment_method');
        
        $query = Account::query();
        
        // Filter by branch if provided
        if ($branchId) {
            $query->where('branch_id', $branchId);
        }
        
        // Filter by account type based on payment method
        if ($paymentMethod === 'cash') {
            // Get cash accounts (صناديق)
            $query->where('account_type', 'cash');
        } elseif ($paymentMethod === 'bank') {
            // Get bank accounts (بنوك)
            $query->where('account_type', 'bank');
        }
        
        $accounts = $query->get(['id', 'account_code', 'account_name']);
        
        return response()->json($accounts);
    }
}
