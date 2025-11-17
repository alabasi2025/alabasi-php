@extends('layouts.app')

@section('title', 'سندات الصرف والقبض')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="mb-0">سندات الصرف والقبض</h3>
                    <div>
                        <a href="{{ route('vouchers.create', ['type' => 'payment']) }}" class="btn btn-danger">
                            <i class="fas fa-plus"></i> سند صرف جديد
                        </a>
                        <a href="{{ route('vouchers.create', ['type' => 'receipt']) }}" class="btn btn-success">
                            <i class="fas fa-plus"></i> سند قبض جديد
                        </a>
                    </div>
                </div>
                
                <div class="card-body">
                    <!-- Filters -->
                    <form method="GET" action="{{ route('vouchers.index') }}" class="row g-3 mb-4">
                        <div class="col-md-2">
                            <label class="form-label">نوع السند</label>
                            <select name="type" class="form-select">
                                <option value="">الكل</option>
                                <option value="payment" {{ request('type') === 'payment' ? 'selected' : '' }}>صرف</option>
                                <option value="receipt" {{ request('type') === 'receipt' ? 'selected' : '' }}>قبض</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">طريقة الدفع</label>
                            <select name="method" class="form-select">
                                <option value="">الكل</option>
                                <option value="cash" {{ request('method') === 'cash' ? 'selected' : '' }}>نقدي</option>
                                <option value="bank" {{ request('method') === 'bank' ? 'selected' : '' }}>بنكي</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">الحالة</label>
                            <select name="status" class="form-select">
                                <option value="">الكل</option>
                                <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>مسودة</option>
                                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>قيد الانتظار</option>
                                <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>معتمد</option>
                                <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>مرفوض</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">من تاريخ</label>
                            <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">إلى تاريخ</label>
                            <input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">&nbsp;</label>
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-search"></i> بحث
                            </button>
                        </div>
                    </form>
                    
                    <!-- Table -->
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>رقم السند</th>
                                    <th>النوع</th>
                                    <th>التاريخ</th>
                                    <th>المستفيد/الدافع</th>
                                    <th>الحساب</th>
                                    <th>المبلغ</th>
                                    <th>الحالة</th>
                                    <th>الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($vouchers as $voucher)
                                <tr>
                                    <td>
                                        <a href="{{ route('vouchers.show', $voucher) }}">
                                            {{ $voucher->voucher_number }}
                                        </a>
                                    </td>
                                    <td>
                                        @if($voucher->voucher_type === 'payment')
                                            <span class="badge bg-danger">صرف</span>
                                        @else
                                            <span class="badge bg-success">قبض</span>
                                        @endif
                                        
                                        @if($voucher->payment_method === 'cash')
                                            <span class="badge bg-secondary">نقدي</span>
                                        @else
                                            <span class="badge bg-info">بنكي</span>
                                        @endif
                                    </td>
                                    <td>{{ $voucher->voucher_date->format('Y-m-d') }}</td>
                                    <td>{{ $voucher->beneficiary_name }}</td>
                                    <td>{{ $voucher->account->account_name ?? 'غير محدد' }}</td>
                                    <td>{{ number_format($voucher->amount, 2) }} {{ $voucher->currency }}</td>
                                    <td>
                                        @switch($voucher->status)
                                            @case('draft')
                                                <span class="badge bg-secondary">مسودة</span>
                                                @break
                                            @case('pending')
                                                <span class="badge bg-warning">قيد الانتظار</span>
                                                @break
                                            @case('approved')
                                                <span class="badge bg-success">معتمد</span>
                                                @break
                                            @case('rejected')
                                                <span class="badge bg-danger">مرفوض</span>
                                                @break
                                            @case('cancelled')
                                                <span class="badge bg-dark">ملغي</span>
                                                @break
                                        @endswitch
                                    </td>
                                    <td>
                                        <a href="{{ route('vouchers.show', $voucher) }}" class="btn btn-sm btn-info">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @if($voucher->status === 'draft')
                                            <a href="{{ route('vouchers.edit', $voucher) }}" class="btn btn-sm btn-warning">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center py-4">
                                        <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                        <p class="text-muted">لا توجد سندات</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    <div class="d-flex justify-content-center">
                        {{ $vouchers->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
