@extends('layouts.app')

@section('title', $voucher->voucher_type === 'payment' ? 'سند صرف' : 'سند قبض')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="mb-0">
                        @if($voucher->voucher_type === 'payment')
                            <i class="fas fa-money-bill-wave text-danger"></i> سند صرف
                        @else
                            <i class="fas fa-hand-holding-usd text-success"></i> سند قبض
                        @endif
                        <span class="badge bg-secondary">{{ $voucher->voucher_number }}</span>
                    </h3>
                    <div>
                        <button onclick="window.print()" class="btn btn-primary">
                            <i class="fas fa-print"></i> طباعة
                        </button>
                        @if($voucher->status === 'draft')
                            <a href="{{ route('vouchers.edit', $voucher) }}" class="btn btn-warning">
                                <i class="fas fa-edit"></i> تعديل
                            </a>
                        @endif
                        <a href="{{ route('vouchers.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-right"></i> رجوع
                        </a>
                    </div>
                </div>
                
                <div class="card-body" id="printable-area">
                    <!-- Voucher Header -->
                    <div class="row mb-4">
                        <div class="col-12 text-center">
                            <h2 class="mb-1">
                                @if($voucher->voucher_type === 'payment')
                                    سند صرف
                                @else
                                    سند قبض
                                @endif
                            </h2>
                            <h4 class="text-muted">{{ $voucher->voucher_number }}</h4>
                        </div>
                    </div>
                    
                    <!-- Voucher Details -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <table class="table table-bordered">
                                <tr>
                                    <th width="40%" class="bg-light">التاريخ:</th>
                                    <td>{{ $voucher->voucher_date->format('Y-m-d') }}</td>
                                </tr>
                                <tr>
                                    <th class="bg-light">الفرع:</th>
                                    <td>{{ $voucher->branch->branch_name ?? 'غير محدد' }}</td>
                                </tr>
                                <tr>
                                    <th class="bg-light">طريقة الدفع:</th>
                                    <td>
                                        @if($voucher->payment_method === 'cash')
                                            <span class="badge bg-secondary">نقدي</span>
                                        @else
                                            <span class="badge bg-info">بنكي</span>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-bordered">
                                <tr>
                                    <th width="40%" class="bg-light">
                                        @if($voucher->voucher_type === 'payment')
                                            المستفيد:
                                        @else
                                            الدافع:
                                        @endif
                                    </th>
                                    <td>{{ $voucher->beneficiary_name }}</td>
                                </tr>
                                <tr>
                                    <th class="bg-light">الحساب:</th>
                                    <td>{{ $voucher->account->account_name ?? 'غير محدد' }}</td>
                                </tr>
                                <tr>
                                    <th class="bg-light">الحالة:</th>
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
                                </tr>
                            </table>
                        </div>
                    </div>
                    
                    <!-- Amount Section -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="alert alert-{{ $voucher->voucher_type === 'payment' ? 'danger' : 'success' }} text-center">
                                <h3 class="mb-0">
                                    المبلغ: <strong>{{ number_format($voucher->amount, 2) }} {{ $voucher->currency }}</strong>
                                </h3>
                                @if($voucher->amount_in_words)
                                    <p class="mb-0 mt-2">{{ $voucher->amount_in_words }}</p>
                                @endif
                            </div>
                        </div>
                    </div>
                    
                    <!-- Description -->
                    @if($voucher->description)
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header bg-light">
                                    <strong>البيان:</strong>
                                </div>
                                <div class="card-body">
                                    {{ $voucher->description }}
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                    
                    <!-- Notes -->
                    @if($voucher->notes)
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header bg-light">
                                    <strong>ملاحظات:</strong>
                                </div>
                                <div class="card-body">
                                    {{ $voucher->notes }}
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                    
                    <!-- Analytical Account -->
                    @if($voucher->analyticalAccount)
                    <div class="row mb-4">
                        <div class="col-12">
                            <table class="table table-bordered">
                                <tr>
                                    <th width="30%" class="bg-light">الحساب التحليلي:</th>
                                    <td>{{ $voucher->analyticalAccount->account_name }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    @endif
                    
                    <!-- Signatures -->
                    <div class="row mt-5 pt-5">
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
                                <strong>
                                    @if($voucher->voucher_type === 'payment')
                                        المستلم
                                    @else
                                        المستلم
                                    @endif
                                </strong>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Footer Info (for print) -->
                    <div class="row mt-5 d-none d-print-block">
                        <div class="col-12 text-center text-muted">
                            <small>
                                تاريخ الطباعة: {{ now()->format('Y-m-d H:i:s') }}
                            </small>
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
    .card-header,
    .btn,
    nav,
    .sidebar {
        display: none !important;
    }
    
    .card {
        border: none !important;
        box-shadow: none !important;
    }
    
    body {
        background: white !important;
    }
    
    #printable-area {
        padding: 20px;
    }
}
</style>
@endpush
@endsection
