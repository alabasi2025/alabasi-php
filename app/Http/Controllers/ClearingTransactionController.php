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
    public function index()
    {
        $isMain = session('is_main');
        $unitId = session('unit_id');

        if ($isMain) {
            // القاعدة المركزية: عرض جميع التحويلات
            $transactions = ClearingTransaction::with(['sourceUnit', 'targetUnit', 'sourceCompany', 'targetCompany'])
                ->orderBy('created_at', 'desc')
                ->paginate(20);
        } else {
            // الوحدات: عرض التحويلات الخاصة بالوحدة فقط
            $transactions = ClearingTransaction::with(['sourceUnit', 'targetUnit', 'sourceCompany', 'targetCompany'])
                ->where(function($query) use ($unitId) {
                    $query->where('source_unit_id', $unitId)
                          ->orWhere('target_unit_id', $unitId);
                })
                ->orderBy('created_at', 'desc')
                ->paginate(20);
        }

        return view('clearing-transactions.index', compact('transactions', 'isMain'));
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
}
