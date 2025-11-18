@extends('layouts.app')

@section('content')
<div class="container-fluid" dir="rtl">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">
                        <i class="fas fa-file-alt me-2"></i>
                        تقرير الحسابات الوسيطة
                    </h4>
                    <div>
                        <button onclick="window.print()" class="btn btn-light btn-sm me-2">
                            <i class="fas fa-print me-1"></i>
                            طباعة
                        </button>
                        <a href="{{ route('clearing-transactions.index') }}" class="btn btn-light btn-sm">
                            <i class="fas fa-arrow-right me-1"></i>
                            رجوع
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <!-- فلاتر التقرير -->
                    <div class="row mb-4 no-print">
                        <div class="col-md-3">
                            <label class="form-label">الوحدة</label>
                            <select class="form-select" id="filter-unit">
                                <option value="">جميع الوحدات</option>
                                @foreach($units as $unit)
                                    <option value="{{ $unit->id }}" {{ request('unit_id') == $unit->id ? 'selected' : '' }}>
                                        {{ $unit->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">المؤسسة</label>
                            <select class="form-select" id="filter-company">
                                <option value="">جميع المؤسسات</option>
                                @foreach($companies as $company)
                                    <option value="{{ $company->id }}" {{ request('company_id') == $company->id ? 'selected' : '' }}>
                                        {{ $company->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">من تاريخ</label>
                            <input type="date" class="form-control" id="filter-from-date" value="{{ request('from_date') }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">إلى تاريخ</label>
                            <input type="date" class="form-control" id="filter-to-date" value="{{ request('to_date') }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">&nbsp;</label>
                            <button onclick="applyFilters()" class="btn btn-primary w-100">
                                <i class="fas fa-filter me-1"></i>
                                تطبيق
                            </button>
                        </div>
                    </div>

                    <!-- معلومات التقرير -->
                    <div class="row mb-3 print-header">
                        <div class="col-12 text-center">
                            <h3>نظام العباسي المحاسبي</h3>
                            <h5>تقرير الحسابات الوسيطة</h5>
                            <p class="text-muted">
                                @if(request('from_date') && request('to_date'))
                                    من {{ request('from_date') }} إلى {{ request('to_date') }}
                                @else
                                    جميع الفترات
                                @endif
                            </p>
                            <p class="text-muted">تاريخ الطباعة: {{ now()->format('Y-m-d H:i') }}</p>
                        </div>
                    </div>

                    <!-- جدول التقرير -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th width="60" class="text-center">م</th>
                                    <th width="100">التاريخ</th>
                                    <th>الوحدة المصدر</th>
                                    <th>المؤسسة المصدر</th>
                                    <th>الحساب الوسيط</th>
                                    <th>الوحدة الهدف</th>
                                    <th>المؤسسة الهدف</th>
                                    <th>الحساب الوسيط</th>
                                    <th width="120" class="text-end">المبلغ (مدين)</th>
                                    <th width="120" class="text-end">المبلغ (دائن)</th>
                                    <th>البيان</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $counter = 1;
                                @endphp
                                @forelse($transactions as $transaction)
                                    <tr>
                                        <td class="text-center">{{ $counter++ }}</td>
                                        <td>{{ $transaction->transaction_date }}</td>
                                        <td>{{ $transaction->sourceUnit->name ?? 'N/A' }}</td>
                                        <td>{{ $transaction->sourceCompany->name ?? 'N/A' }}</td>
                                        <td class="text-center">
                                            <span class="badge bg-danger">
                                                {{ $transaction->sourceCompany->clearing_account_number ?? 'N/A' }}
                                            </span>
                                        </td>
                                        <td>{{ $transaction->targetUnit->name ?? 'N/A' }}</td>
                                        <td>{{ $transaction->targetCompany->name ?? 'N/A' }}</td>
                                        <td class="text-center">
                                            <span class="badge bg-success">
                                                {{ $transaction->targetCompany->clearing_account_number ?? 'N/A' }}
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            <strong>{{ number_format($transaction->amount, 2) }}</strong>
                                        </td>
                                        <td class="text-end">
                                            <strong>{{ number_format($transaction->amount, 2) }}</strong>
                                        </td>
                                        <td>{{ $transaction->description }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="11" class="text-center text-muted py-4">
                                            لا توجد تحويلات مكتملة في الفترة المحددة
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                            @if($transactions->count() > 0)
                                <tfoot class="table-light">
                                    <tr>
                                        <th colspan="8" class="text-start">الإجمالي:</th>
                                        <th class="text-end">{{ number_format($totalDebit, 2) }}</th>
                                        <th class="text-end">{{ number_format($totalCredit, 2) }}</th>
                                        <th></th>
                                    </tr>
                                    <tr>
                                        <th colspan="8" class="text-start">الرصيد (يجب أن يكون صفر):</th>
                                        <th colspan="2" class="text-center">
                                            @if($balance == 0)
                                                <span class="badge bg-success fs-6">{{ number_format($balance, 2) }}</span>
                                            @else
                                                <span class="badge bg-danger fs-6">{{ number_format($balance, 2) }}</span>
                                            @endif
                                        </th>
                                        <th></th>
                                    </tr>
                                </tfoot>
                            @endif
                        </table>
                    </div>

                    <!-- ملاحظات -->
                    <div class="mt-4 print-footer">
                        <h6>ملاحظات:</h6>
                        <ul>
                            <li>هذا التقرير يعرض جميع التحويلات المكتملة بين المؤسسات والوحدات</li>
                            <li>كل تحويل يظهر كمدين في الحساب الوسيط للمؤسسة المصدر ودائن في الحساب الوسيط للمؤسسة الهدف</li>
                            <li>يجب أن يكون الرصيد النهائي صفراً لضمان توازن الحسابات</li>
                            <li>الحسابات الوسيطة المستخدمة:
                                <ul>
                                    <li>9001 - حساب وسيط أعمال الموظفين</li>
                                    <li>9002 - حساب وسيط أعمال المحاسب</li>
                                    <li>9003 - حساب وسيط الأنظمة</li>
                                    <li>9004 - حساب وسيط النقدية</li>
                                </ul>
                            </li>
                        </ul>
                    </div>

                    <!-- توقيعات -->
                    <div class="row mt-5 print-signatures">
                        <div class="col-4 text-center">
                            <div class="border-top pt-2">
                                <strong>المحاسب</strong>
                            </div>
                        </div>
                        <div class="col-4 text-center">
                            <div class="border-top pt-2">
                                <strong>المدير المالي</strong>
                            </div>
                        </div>
                        <div class="col-4 text-center">
                            <div class="border-top pt-2">
                                <strong>المدير العام</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
@media print {
    .no-print {
        display: none !important;
    }
    
    .print-header {
        margin-bottom: 30px;
    }
    
    .print-footer {
        page-break-inside: avoid;
    }
    
    .print-signatures {
        margin-top: 80px;
        page-break-inside: avoid;
    }
    
    table {
        font-size: 12px;
    }
    
    .card {
        border: none;
        box-shadow: none;
    }
    
    .card-header {
        display: none;
    }
}
</style>
@endpush

@push('scripts')
<script>
function applyFilters() {
    const params = new URLSearchParams();
    
    const unitId = document.getElementById('filter-unit').value;
    const companyId = document.getElementById('filter-company').value;
    const fromDate = document.getElementById('filter-from-date').value;
    const toDate = document.getElementById('filter-to-date').value;
    
    if (unitId) params.set('unit_id', unitId);
    if (companyId) params.set('company_id', companyId);
    if (fromDate) params.set('from_date', fromDate);
    if (toDate) params.set('to_date', toDate);
    
    window.location.href = '{{ route("clearing-transactions.report") }}?' + params.toString();
}
</script>
@endpush
@endsection
