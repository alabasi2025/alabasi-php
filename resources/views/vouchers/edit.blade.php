@extends('layouts.app')

@section('title', $voucher->voucher_type === 'payment' ? 'تعديل سند صرف' : 'تعديل سند قبض')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="mb-0">
                        @if($voucher->voucher_type === 'payment')
                            <i class="fas fa-money-bill-wave text-danger"></i> تعديل سند صرف
                        @else
                            <i class="fas fa-hand-holding-usd text-success"></i> تعديل سند قبض
                        @endif
                        <span class="badge bg-secondary">{{ $voucher->voucher_number }}</span>
                    </h3>
                </div>
                
                <div class="card-body">
                    @if($voucher->status !== 'draft')
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i>
                            <strong>تنبيه:</strong> لا يمكن تعديل السند إلا إذا كان في حالة "مسودة"
                        </div>
                    @endif
                    
                    <form method="POST" action="{{ route('vouchers.update', $voucher) }}" id="voucherForm">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="voucher_type" value="{{ $voucher->voucher_type }}">
                        
                        <!-- Step 1: Branch Selection -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">الفرع <span class="text-danger">*</span></label>
                                <select name="branch_id" id="branch_id" class="form-select @error('branch_id') is-invalid @enderror" required>
                                    <option value="">اختر الفرع</option>
                                    @foreach($branches as $branch)
                                        <option value="{{ $branch->id }}" {{ old('branch_id', $voucher->branch_id) == $branch->id ? 'selected' : '' }}>
                                            {{ $branch->branch_name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('branch_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label">تاريخ السند <span class="text-danger">*</span></label>
                                <input type="date" name="voucher_date" class="form-control @error('voucher_date') is-invalid @enderror" 
                                       value="{{ old('voucher_date', $voucher->voucher_date->format('Y-m-d')) }}" required>
                                @error('voucher_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <!-- Step 2: Payment Method -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">طريقة الدفع <span class="text-danger">*</span></label>
                                <select name="payment_method" id="payment_method" class="form-select @error('payment_method') is-invalid @enderror" required>
                                    <option value="">اختر طريقة الدفع</option>
                                    <option value="cash" {{ old('payment_method', $voucher->payment_method) === 'cash' ? 'selected' : '' }}>نقدي (صندوق)</option>
                                    <option value="bank" {{ old('payment_method', $voucher->payment_method) === 'bank' ? 'selected' : '' }}>بنكي (حساب بنكي)</option>
                                </select>
                                @error('payment_method')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <!-- Step 3: Account Selection -->
                            <div class="col-md-6">
                                <label class="form-label">
                                    <span id="account_label">الحساب</span> <span class="text-danger">*</span>
                                </label>
                                <select name="account_id" id="account_id" class="form-select @error('account_id') is-invalid @enderror" required>
                                    <option value="">اختر الحساب</option>
                                    @foreach($accounts as $account)
                                        <option value="{{ $account->id }}" {{ old('account_id', $voucher->account_id) == $account->id ? 'selected' : '' }}>
                                            {{ $account->account_code }} - {{ $account->account_name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('account_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <!-- Beneficiary and Amount -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">
                                    @if($voucher->voucher_type === 'payment')
                                        المستفيد
                                    @else
                                        الدافع
                                    @endif
                                    <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="beneficiary_name" class="form-control @error('beneficiary_name') is-invalid @enderror" 
                                       value="{{ old('beneficiary_name', $voucher->beneficiary_name) }}" required>
                                @error('beneficiary_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label">الحساب التحليلي (اختياري)</label>
                                <select name="analytical_account_id" class="form-select @error('analytical_account_id') is-invalid @enderror">
                                    <option value="">بدون حساب تحليلي</option>
                                    @foreach($analyticalAccounts as $account)
                                        <option value="{{ $account->id }}" {{ old('analytical_account_id', $voucher->analytical_account_id) == $account->id ? 'selected' : '' }}>
                                            {{ $account->account_name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('analytical_account_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">المبلغ <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" min="0.01" name="amount" class="form-control @error('amount') is-invalid @enderror" 
                                       value="{{ old('amount', $voucher->amount) }}" required>
                                @error('amount')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label">العملة <span class="text-danger">*</span></label>
                                <select name="currency" class="form-select @error('currency') is-invalid @enderror" required>
                                    <option value="IQD" {{ old('currency', $voucher->currency) === 'IQD' ? 'selected' : '' }}>دينار عراقي (IQD)</option>
                                    <option value="USD" {{ old('currency', $voucher->currency) === 'USD' ? 'selected' : '' }}>دولار أمريكي (USD)</option>
                                    <option value="EUR" {{ old('currency', $voucher->currency) === 'EUR' ? 'selected' : '' }}>يورو (EUR)</option>
                                </select>
                                @error('currency')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <!-- Description and Notes -->
                        <div class="mb-3">
                            <label class="form-label">البيان</label>
                            <textarea name="description" rows="3" class="form-control @error('description') is-invalid @enderror">{{ old('description', $voucher->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">ملاحظات</label>
                            <textarea name="notes" rows="2" class="form-control @error('notes') is-invalid @enderror">{{ old('notes', $voucher->notes) }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <!-- Actions -->
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('vouchers.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-right"></i> رجوع
                            </a>
                            @if($voucher->status === 'draft')
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> حفظ التعديلات
                                </button>
                            @endif
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Voucher Info Panel -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-info-circle"></i> معلومات السند</h5>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tr>
                            <th>رقم السند:</th>
                            <td>{{ $voucher->voucher_number }}</td>
                        </tr>
                        <tr>
                            <th>الحالة:</th>
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
                                @endswitch
                            </td>
                        </tr>
                        <tr>
                            <th>تاريخ الإنشاء:</th>
                            <td>{{ $voucher->created_at->format('Y-m-d H:i') }}</td>
                        </tr>
                        <tr>
                            <th>آخر تحديث:</th>
                            <td>{{ $voucher->updated_at->format('Y-m-d H:i') }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const branchSelect = document.getElementById('branch_id');
    const paymentMethodSelect = document.getElementById('payment_method');
    const accountSelect = document.getElementById('account_id');
    const accountLabel = document.getElementById('account_label');
    
    // Update label based on payment method
    function updateAccountLabel() {
        const paymentMethod = paymentMethodSelect.value;
        if (paymentMethod === 'cash') {
            accountLabel.textContent = 'الصندوق';
        } else if (paymentMethod === 'bank') {
            accountLabel.textContent = 'الحساب البنكي';
        }
    }
    
    // Function to load accounts
    function loadAccounts() {
        const branchId = branchSelect.value;
        const paymentMethod = paymentMethodSelect.value;
        
        if (!branchId || !paymentMethod) {
            return;
        }
        
        updateAccountLabel();
        
        // Fetch accounts via AJAX
        fetch(`/vouchers/get-accounts?branch_id=${branchId}&payment_method=${paymentMethod}`)
            .then(response => response.json())
            .then(accounts => {
                const currentValue = accountSelect.value;
                accountSelect.innerHTML = '<option value="">اختر الحساب</option>';
                accounts.forEach(account => {
                    const option = document.createElement('option');
                    option.value = account.id;
                    option.textContent = `${account.account_code} - ${account.account_name}`;
                    if (account.id == currentValue) {
                        option.selected = true;
                    }
                    accountSelect.appendChild(option);
                });
            })
            .catch(error => {
                console.error('Error loading accounts:', error);
            });
    }
    
    // Event listeners
    branchSelect.addEventListener('change', loadAccounts);
    paymentMethodSelect.addEventListener('change', loadAccounts);
    
    // Initialize label
    updateAccountLabel();
});
</script>
@endpush
@endsection
