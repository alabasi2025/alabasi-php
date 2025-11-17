@extends('layouts.app')

@section('title', $type === 'payment' ? 'سند صرف جديد' : 'سند قبض جديد')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="mb-0">
                        @if($type === 'payment')
                            <i class="fas fa-money-bill-wave text-danger"></i> سند صرف جديد
                        @else
                            <i class="fas fa-hand-holding-usd text-success"></i> سند قبض جديد
                        @endif
                    </h3>
                </div>
                
                <div class="card-body">
                    <form method="POST" action="{{ route('vouchers.store') }}" id="voucherForm">
                        @csrf
                        <input type="hidden" name="voucher_type" value="{{ $type }}">
                        
                        <!-- Step 1: Branch Selection -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">الفرع <span class="text-danger">*</span></label>
                                <select name="branch_id" id="branch_id" class="form-select @error('branch_id') is-invalid @enderror" required>
                                    <option value="">اختر الفرع</option>
                                    @foreach($branches as $branch)
                                        <option value="{{ $branch->id }}" {{ old('branch_id') == $branch->id ? 'selected' : '' }}>
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
                                       value="{{ old('voucher_date', date('Y-m-d')) }}" required>
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
                                    <option value="cash" {{ old('payment_method') === 'cash' ? 'selected' : '' }}>نقدي (صندوق)</option>
                                    <option value="bank" {{ old('payment_method') === 'bank' ? 'selected' : '' }}>بنكي (حساب بنكي)</option>
                                </select>
                                @error('payment_method')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <!-- Step 3: Account Selection (filtered by branch and payment method) -->
                            <div class="col-md-6">
                                <label class="form-label">
                                    <span id="account_label">الحساب</span> <span class="text-danger">*</span>
                                </label>
                                <select name="account_id" id="account_id" class="form-select @error('account_id') is-invalid @enderror" required disabled>
                                    <option value="">اختر الحساب</option>
                                </select>
                                @error('account_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">سيتم تصفية الحسابات حسب الفرع وطريقة الدفع</small>
                            </div>
                        </div>
                        
                        <!-- Beneficiary and Amount -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">
                                    @if($type === 'payment')
                                        المستفيد
                                    @else
                                        الدافع
                                    @endif
                                    <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="beneficiary_name" class="form-control @error('beneficiary_name') is-invalid @enderror" 
                                       value="{{ old('beneficiary_name') }}" required>
                                @error('beneficiary_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label">الحساب التحليلي (اختياري)</label>
                                <select name="analytical_account_id" class="form-select @error('analytical_account_id') is-invalid @enderror">
                                    <option value="">بدون حساب تحليلي</option>
                                    @foreach($analyticalAccounts as $account)
                                        <option value="{{ $account->id }}" {{ old('analytical_account_id') == $account->id ? 'selected' : '' }}>
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
                                       value="{{ old('amount') }}" required>
                                @error('amount')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label">العملة <span class="text-danger">*</span></label>
                                <select name="currency" class="form-select @error('currency') is-invalid @enderror" required>
                                    <option value="IQD" {{ old('currency', 'IQD') === 'IQD' ? 'selected' : '' }}>دينار عراقي (IQD)</option>
                                    <option value="USD" {{ old('currency') === 'USD' ? 'selected' : '' }}>دولار أمريكي (USD)</option>
                                    <option value="EUR" {{ old('currency') === 'EUR' ? 'selected' : '' }}>يورو (EUR)</option>
                                </select>
                                @error('currency')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <!-- Description and Notes -->
                        <div class="mb-3">
                            <label class="form-label">البيان</label>
                            <textarea name="description" rows="3" class="form-control @error('description') is-invalid @enderror">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">ملاحظات</label>
                            <textarea name="notes" rows="2" class="form-control @error('notes') is-invalid @enderror">{{ old('notes') }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <!-- Actions -->
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('vouchers.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-right"></i> رجوع
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> حفظ السند
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- AI Assistant Panel (for future implementation) -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header bg-gradient-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-robot"></i> المساعد الذكي</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <strong>قريباً:</strong> سيتم إضافة مساعد ذكاء اصطناعي هنا لمساعدتك في:
                        <ul class="mb-0 mt-2">
                            <li>إنشاء القيود المحاسبية تلقائياً</li>
                            <li>اقتراح الحسابات المناسبة</li>
                            <li>التحقق من صحة البيانات</li>
                        </ul>
                    </div>
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
    
    // Function to load accounts based on branch and payment method
    function loadAccounts() {
        const branchId = branchSelect.value;
        const paymentMethod = paymentMethodSelect.value;
        
        if (!branchId || !paymentMethod) {
            accountSelect.disabled = true;
            accountSelect.innerHTML = '<option value="">اختر الحساب</option>';
            return;
        }
        
        // Update label based on payment method
        if (paymentMethod === 'cash') {
            accountLabel.textContent = 'الصندوق';
        } else if (paymentMethod === 'bank') {
            accountLabel.textContent = 'الحساب البنكي';
        }
        
        // Fetch accounts via AJAX
        fetch(`/vouchers/get-accounts?branch_id=${branchId}&payment_method=${paymentMethod}`)
            .then(response => response.json())
            .then(accounts => {
                accountSelect.innerHTML = '<option value="">اختر الحساب</option>';
                accounts.forEach(account => {
                    const option = document.createElement('option');
                    option.value = account.id;
                    option.textContent = `${account.account_code} - ${account.account_name}`;
                    accountSelect.appendChild(option);
                });
                accountSelect.disabled = false;
            })
            .catch(error => {
                console.error('Error loading accounts:', error);
                alert('حدث خطأ أثناء تحميل الحسابات');
            });
    }
    
    // Event listeners
    branchSelect.addEventListener('change', loadAccounts);
    paymentMethodSelect.addEventListener('change', loadAccounts);
    
    // Load accounts if values are already selected (e.g., after validation error)
    if (branchSelect.value && paymentMethodSelect.value) {
        loadAccounts();
    }
});
</script>
@endpush
@endsection
