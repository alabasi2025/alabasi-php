<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Main\ClearingTransaction;
use App\Models\Main\Unit;
use App\Models\Main\Company;
use App\Services\ClearingAccountService;

class ClearingTransactionController extends Controller
{
    protected $clearingService;

    public function __construct(ClearingAccountService $clearingService)
    {
        $this->clearingService = $clearingService;
    }

    /**
     * عرض قائمة التحويلات
     */
    public function index(Request $request)
    {
        $isMain = session('is_main');
        $unitId = session('unit_id');

        $query = ClearingTransaction::with(['sourceUnit', 'targetUnit', 'sourceCompany', 'targetCompany'])
            ->orderBy('created_at', 'desc');

        // فلترة حسب الصلاحيات
        if (!$isMain) {
            // الوحدات: عرض التحويلات الخاصة بالوحدة فقط
            $query->where(function($q) use ($unitId) {
                $q->where('source_unit_id', $unitId)
                  ->orWhere('target_unit_id', $unitId);
            });
        }

        // تطبيق الفلاتر
        if ($request->filled('type')) {
            $query->where('transfer_type', $request->type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('from_date')) {
            $query->whereDate('transaction_date', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('transaction_date', '<=', $request->to_date);
        }

        $transactions = $query->paginate(20);

        return view('clearing_transactions.index', compact('transactions', 'isMain'));
    }

    /**
     * عرض نموذج إنشاء تحويل جديد
     */
    public function create()
    {
        $units = Unit::all();
        $companies = Company::all();

        return view('clearing-transactions.create', compact('units', 'companies'));
    }

    /**
     * حفظ تحويل جديد
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'source_unit_id' => 'required|exists:units,id',
            'source_company_id' => 'required|exists:companies,id',
            'source_account_id' => 'required',
            'target_unit_id' => 'required|exists:units,id',
            'target_company_id' => 'required|exists:companies,id',
            'target_account_id' => 'required',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'required|string',
        ]);

        try {
            $transaction = $this->clearingService->createTransfer([
                'source_unit_id' => $validated['source_unit_id'],
                'source_company_id' => $validated['source_company_id'],
                'source_account_id' => $validated['source_account_id'],
                'target_unit_id' => $validated['target_unit_id'],
                'target_company_id' => $validated['target_company_id'],
                'target_account_id' => $validated['target_account_id'],
                'amount' => $validated['amount'],
                'description' => $validated['description'],
                'user_id' => 1, // TODO: استخدام المستخدم الحالي
            ]);

            return redirect()->route('clearing-transactions.show', $transaction->id)
                ->with('success', 'تم إنشاء التحويل بنجاح');
        } catch (\Exception $e) {
            return back()->with('error', 'حدث خطأ: ' . $e->getMessage());
        }
    }

    /**
     * عرض تفاصيل تحويل
     */
    public function show($id)
    {
        $transaction = ClearingTransaction::with([
            'sourceUnit',
            'targetUnit',
            'sourceCompany',
            'targetCompany'
        ])->findOrFail($id);

        return view('clearing-transactions.show', compact('transaction'));
    }

    /**
     * الموافقة على تحويل
     */
    public function approve($id)
    {
        $transaction = ClearingTransaction::findOrFail($id);
        $transaction->markAsCompleted();

        return back()->with('success', 'تمت الموافقة على التحويل');
    }

    /**
     * إلغاء تحويل
     */
    public function cancel($id)
    {
        $transaction = ClearingTransaction::findOrFail($id);
        $transaction->markAsCancelled();

        return back()->with('success', 'تم إلغاء التحويل');
    }

    /**
     * ترحيل/مزامنة تحويل بين وحدات
     */
    public function sync($id)
    {
        try {
            $clearingTransaction = $this->clearingService->syncTransfer($id);

            return redirect()
                ->route('clearing-transactions.show', $id)
                ->with('success', 'تم ترحيل التحويل بنجاح');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'فشل ترحيل التحويل: ' . $e->getMessage());
        }
    }

    /**
     * تقرير الحسابات الوسيطة
     */
    public function report(Request $request)
    {
        $query = ClearingTransaction::with(['sourceUnit', 'targetUnit', 'sourceCompany', 'targetCompany'])
            ->where('status', 'completed')
            ->orderBy('transaction_date', 'desc');

        // تطبيق الفلاتر
        if ($request->filled('unit_id')) {
            $query->where(function($q) use ($request) {
                $q->where('source_unit_id', $request->unit_id)
                  ->orWhere('target_unit_id', $request->unit_id);
            });
        }

        if ($request->filled('company_id')) {
            $query->where(function($q) use ($request) {
                $q->where('source_company_id', $request->company_id)
                  ->orWhere('target_company_id', $request->company_id);
            });
        }

        if ($request->filled('from_date')) {
            $query->whereDate('transaction_date', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('transaction_date', '<=', $request->to_date);
        }

        $transactions = $query->get();

        // حساب الإجماليات
        $totalDebit = $transactions->sum('amount');
        $totalCredit = $transactions->sum('amount');
        $balance = $totalDebit - $totalCredit; // يجب أن يكون صفر

        $units = Unit::where('is_active', true)->get();
        $companies = Company::where('is_active', true)->get();

        return view('clearing_transactions.report', compact(
            'transactions', 
            'totalDebit', 
            'totalCredit', 
            'balance',
            'units',
            'companies'
        ));
    }

    /**
     * API: جلب المؤسسات حسب الوحدة
     */
    public function getCompaniesByUnit($unitId)
    {
        try {
            $companies = Company::where('unit_id', $unitId)
                ->where('is_active', true)
                ->select('id', 'name', 'clearing_account_number')
                ->get();

            return response()->json($companies);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * API: جلب الحسابات حسب المؤسسة
     */
    public function getAccountsByCompany($unitId, $companyId)
    {
        try {
            // التبديل إلى قاعدة بيانات الوحدة
            $unit = Unit::find($unitId);
            if (!$unit) {
                return response()->json(['error' => 'Unit not found'], 404);
            }

            config(['database.default' => $unit->database_name]);
            
            $accounts = \App\Models\Unit\Account::where('company_id', $companyId)
                ->where('is_active', true)
                ->select('id', 'account_number', 'name')
                ->get();

            // إعادة الاتصال إلى القاعدة المركزية
            config(['database.default' => 'main']);

            return response()->json($accounts);
        } catch (\Exception $e) {
            config(['database.default' => 'main']);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
