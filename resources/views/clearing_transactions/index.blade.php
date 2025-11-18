@extends('layouts.app')

@section('content')
<div class="container-fluid" dir="rtl">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">
                        <i class="fas fa-exchange-alt me-2"></i>
                        التحويلات بين المؤسسات والوحدات
                    </h4>
                    <a href="{{ route('clearing-transactions.create') }}" class="btn btn-light btn-sm">
                        <i class="fas fa-plus me-1"></i>
                        تحويل جديد
                    </a>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i>
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <!-- فلاتر البحث -->
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label class="form-label">نوع التحويل</label>
                            <select class="form-select" id="filter-type">
                                <option value="">الكل</option>
                                <option value="inter_company">بين مؤسسات</option>
                                <option value="inter_unit">بين وحدات</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">الحالة</label>
                            <select class="form-select" id="filter-status">
                                <option value="">الكل</option>
                                <option value="pending">قيد الانتظار</option>
                                <option value="completed">مكتمل</option>
                                <option value="cancelled">ملغي</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">من تاريخ</label>
                            <input type="date" class="form-control" id="filter-from-date">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">إلى تاريخ</label>
                            <input type="date" class="form-control" id="filter-to-date">
                        </div>
                    </div>

                    <!-- جدول التحويلات -->
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th width="80">الرقم</th>
                                    <th>التاريخ</th>
                                    <th>النوع</th>
                                    <th>من</th>
                                    <th>إلى</th>
                                    <th>المبلغ</th>
                                    <th>الحالة</th>
                                    <th>البيان</th>
                                    <th width="150">الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($transactions as $transaction)
                                    <tr>
                                        <td class="text-center">{{ $transaction->id }}</td>
                                        <td>{{ $transaction->transaction_date }}</td>
                                        <td>
                                            @if($transaction->transfer_type == 'inter_company')
                                                <span class="badge bg-info">بين مؤسسات</span>
                                            @else
                                                <span class="badge bg-warning">بين وحدات</span>
                                            @endif
                                        </td>
                                        <td>
                                            <small class="text-muted">{{ $transaction->sourceUnit->name ?? 'N/A' }}</small><br>
                                            <strong>{{ $transaction->sourceCompany->name ?? 'N/A' }}</strong>
                                        </td>
                                        <td>
                                            <small class="text-muted">{{ $transaction->targetUnit->name ?? 'N/A' }}</small><br>
                                            <strong>{{ $transaction->targetCompany->name ?? 'N/A' }}</strong>
                                        </td>
                                        <td class="text-end">
                                            <strong>{{ number_format($transaction->amount, 2) }}</strong>
                                        </td>
                                        <td>
                                            @if($transaction->status == 'pending')
                                                <span class="badge bg-warning">قيد الانتظار</span>
                                            @elseif($transaction->status == 'completed')
                                                <span class="badge bg-success">مكتمل</span>
                                            @else
                                                <span class="badge bg-danger">ملغي</span>
                                            @endif
                                        </td>
                                        <td>{{ Str::limit($transaction->description, 50) }}</td>
                                        <td class="text-center">
                                            @if($transaction->status == 'pending')
                                                <form action="{{ route('clearing-transactions.post', $transaction->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-success btn-sm" 
                                                            onclick="return confirm('هل أنت متأكد من ترحيل هذا التحويل؟')">
                                                        <i class="fas fa-check me-1"></i>
                                                        ترحيل
                                                    </button>
                                                </form>
                                                <form action="{{ route('clearing-transactions.cancel', $transaction->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger btn-sm"
                                                            onclick="return confirm('هل أنت متأكد من إلغاء هذا التحويل؟')">
                                                        <i class="fas fa-times me-1"></i>
                                                        إلغاء
                                                    </button>
                                                </form>
                                            @else
                                                <a href="{{ route('clearing-transactions.show', $transaction->id) }}" class="btn btn-info btn-sm">
                                                    <i class="fas fa-eye"></i>
                                                    عرض
                                                </a>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center text-muted py-4">
                                            <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                                            لا توجد تحويلات حالياً
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-center">
                        {{ $transactions->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// فلترة التحويلات
document.addEventListener('DOMContentLoaded', function() {
    const filterType = document.getElementById('filter-type');
    const filterStatus = document.getElementById('filter-status');
    const filterFromDate = document.getElementById('filter-from-date');
    const filterToDate = document.getElementById('filter-to-date');

    function applyFilters() {
        const params = new URLSearchParams(window.location.search);
        
        if (filterType.value) params.set('type', filterType.value);
        else params.delete('type');
        
        if (filterStatus.value) params.set('status', filterStatus.value);
        else params.delete('status');
        
        if (filterFromDate.value) params.set('from_date', filterFromDate.value);
        else params.delete('from_date');
        
        if (filterToDate.value) params.set('to_date', filterToDate.value);
        else params.delete('to_date');
        
        window.location.search = params.toString();
    }

    filterType.addEventListener('change', applyFilters);
    filterStatus.addEventListener('change', applyFilters);
    filterFromDate.addEventListener('change', applyFilters);
    filterToDate.addEventListener('change', applyFilters);

    // تعيين القيم من URL
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('type')) filterType.value = urlParams.get('type');
    if (urlParams.get('status')) filterStatus.value = urlParams.get('status');
    if (urlParams.get('from_date')) filterFromDate.value = urlParams.get('from_date');
    if (urlParams.get('to_date')) filterToDate.value = urlParams.get('to_date');
});
</script>
@endpush
@endsection
