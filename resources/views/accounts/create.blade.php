@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-10 offset-md-1">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-plus-circle"></i> إضافة حساب جديد
                    </h3>
                </div>

                <form action="{{ route('accounts.store') }}" method="POST" id="accountForm">
                    @csrf
                    
                    <div class="card-body">
                        {{-- Company Info --}}
                        <div class="alert alert-info">
                            <i class="fas fa-building"></i> <strong>الوحدة:</strong> {{ $company->unit->unit_name }}
                            &nbsp;|&nbsp;
                            <i class="fas fa-briefcase"></i> <strong>المؤسسة:</strong> {{ $company->company_name }}
                            &nbsp;
                            <a href="{{ route('context.selector') }}" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-exchange-alt"></i> تغيير
                            </a>
                        </div>

                        {{-- Parent Account Info (if adding sub-account) --}}
                        @if(isset($parentAccount))
                            <div class="alert alert-success">
                                <i class="fas fa-level-up-alt"></i> 
                                <strong>إضافة حساب فرعي تحت:</strong> 
                                {{ $parentAccount->account_code }} - {{ $parentAccount->name }}
                                <input type="hidden" name="parent_id" value="{{ $parentAccount->id }}">
                            </div>
                        @endif

                        <div class="row">
                            {{-- Account Code --}}
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="account_code">رمز الحساب <span class="text-danger">*</span></label>
                                    <input type="text" 
                                           name="account_code" 
                                           id="account_code" 
                                           class="form-control @error('account_code') is-invalid @enderror" 
                                           value="{{ old('account_code') }}"
                                           placeholder="مثال: 1000"
                                           required>
                                    @error('account_code')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">رقم الحساب (يمكن استخدام أي رقم)</small>
                                </div>
                            </div>

                            {{-- Account Name --}}
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label for="name">اسم الحساب <span class="text-danger">*</span></label>
                                    <input type="text" 
                                           name="name" 
                                           id="name" 
                                           class="form-control @error('name') is-invalid @enderror" 
                                           value="{{ old('name') }}"
                                           placeholder="مثال: الأصول المتداولة"
                                           required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            {{-- Account Type --}}
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="account_type_id">نوع الحساب <span class="text-danger">*</span></label>
                                    <select name="account_type_id" 
                                            id="account_type_id" 
                                            class="form-control @error('account_type_id') is-invalid @enderror"
                                            required>
                                        <option value="">-- اختر نوع الحساب --</option>
                                        @foreach($accountTypes as $type)
                                            <option value="{{ $type->id }}" {{ old('account_type_id') == $type->id ? 'selected' : '' }}>
                                                {{ $type->name }} ({{ $type->nature == 'debit' ? 'مدين' : 'دائن' }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('account_type_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">
                                        <a href="{{ route('account-types.create') }}" target="_blank">إضافة نوع جديد</a>
                                    </small>
                                </div>
                            </div>

                            {{-- Is Main Account --}}
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>نوع الحساب <span class="text-danger">*</span></label>
                                    <div class="form-check">
                                        <input class="form-check-input" 
                                               type="radio" 
                                               name="is_main" 
                                               id="is_main_true" 
                                               value="1"
                                               {{ old('is_main', isset($parentAccount) ? '0' : '1') == '1' ? 'checked' : '' }}
                                               onchange="toggleAnalyticalType()">
                                        <label class="form-check-label" for="is_main_true">
                                            <i class="fas fa-folder text-primary"></i> حساب رئيسي (للترتيب فقط)
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" 
                                               type="radio" 
                                               name="is_main" 
                                               id="is_main_false" 
                                               value="0"
                                               {{ old('is_main', isset($parentAccount) ? '0' : '1') == '0' ? 'checked' : '' }}
                                               onchange="toggleAnalyticalType()">
                                        <label class="form-check-label" for="is_main_false">
                                            <i class="fas fa-file text-success"></i> حساب فرعي (يمكن الترحيل عليه)
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Parent Account (only for main accounts) --}}
                        <div class="row" id="parent_account_row" style="display: {{ old('is_main', isset($parentAccount) ? '0' : '1') == '1' ? 'block' : 'none' }};">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="parent_id">الحساب الأب (اختياري)</label>
                                    <select name="parent_id" 
                                            id="parent_id" 
                                            class="form-control @error('parent_id') is-invalid @enderror">
                                        <option value="">-- بدون حساب أب --</option>
                                        @foreach($parentAccounts as $parent)
                                            <option value="{{ $parent->id }}" {{ old('parent_id', isset($parentAccount) ? $parentAccount->id : '') == $parent->id ? 'selected' : '' }}>
                                                {{ $parent->account_code }} - {{ $parent->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('parent_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">يمكن ربط الحساب الرئيسي بحساب رئيسي آخر للتصنيف</small>
                                </div>
                            </div>
                        </div>

                        {{-- Account Nature (only for sub accounts) --}}
                        <div class="row" id="account_nature_row" style="display: {{ old('is_main', isset($parentAccount) ? '0' : '1') == '0' ? 'block' : 'none' }};">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="account_nature">طبيعة الحساب <span class="text-danger">*</span></label>
                                    <select name="account_nature" 
                                            id="account_nature" 
                                            class="form-control @error('account_nature') is-invalid @enderror">
                                        <option value="general" {{ old('account_nature', 'general') == 'general' ? 'selected' : '' }}>حساب عام</option>
                                        <option value="cash_box" {{ old('account_nature') == 'cash_box' ? 'selected' : '' }}>💰 صندوق</option>
                                        <option value="bank" {{ old('account_nature') == 'bank' ? 'selected' : '' }}>🏦 بنك</option>
                                        <option value="customer" {{ old('account_nature') == 'customer' ? 'selected' : '' }}>👥 عميل</option>
                                        <option value="supplier" {{ old('account_nature') == 'supplier' ? 'selected' : '' }}>🏭 مورد</option>
                                        <option value="employee" {{ old('account_nature') == 'employee' ? 'selected' : '' }}>👔 موظف</option>
                                        <option value="debtor" {{ old('account_nature') == 'debtor' ? 'selected' : '' }}>📗 حساب مدين</option>
                                        <option value="creditor" {{ old('account_nature') == 'creditor' ? 'selected' : '' }}>📕 حساب دائن</option>
                                        <option value="analytical" {{ old('account_nature') == 'analytical' ? 'selected' : '' }}>📊 حساب تحليلي</option>
                                    </select>
                                    @error('account_nature')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">
                                        حدد طبيعة الحساب ليظهر في الواجهات المناسبة
                                    </small>
                                </div>
                            </div>
                        </div>

                        {{-- Description --}}
                        <div class="form-group">
                            <label for="description">الوصف (اختياري)</label>
                            <textarea name="description" 
                                      id="description" 
                                      class="form-control @error('description') is-invalid @enderror" 
                                      rows="3"
                                      placeholder="وصف اختياري للحساب...">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Is Active --}}
                        <div class="form-group">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" 
                                       class="custom-control-input" 
                                       id="is_active" 
                                       name="is_active" 
                                       value="1"
                                       {{ old('is_active', true) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="is_active">
                                    <i class="fas fa-check-circle text-success"></i> نشط
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save"></i> حفظ
                        </button>
                        <a href="{{ route('accounts.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i> إلغاء
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function toggleAnalyticalType() {
    const isMain = document.querySelector('input[name="is_main"]:checked').value;
    const parentRow = document.getElementById('parent_account_row');
    const natureRow = document.getElementById('account_nature_row');
    const natureSelect = document.getElementById('account_nature');
    
    if (isMain == '1') {
        // Main account
        parentRow.style.display = 'block';
        natureRow.style.display = 'none';
        natureSelect.removeAttribute('required');
        natureSelect.value = 'general';
    } else {
        // Sub account
        parentRow.style.display = 'none';
        natureRow.style.display = 'block';
        natureSelect.setAttribute('required', 'required');
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    toggleAnalyticalType();
    
    // Handle unit change to load companies
    const unitSelect = document.getElementById('unit_id');
    const companySelect = document.getElementById('company_id');
    
    if (unitSelect) {
        unitSelect.addEventListener('change', function() {
            const unitId = this.value;
            companySelect.innerHTML = '<option value="">-- اختر المؤسسة --</option>';
            
            if (unitId) {
                fetch(`/api/companies-by-unit/${unitId}`)
                    .then(response => response.json())
                    .then(companies => {
                        companies.forEach(company => {
                            const option = document.createElement('option');
                            option.value = company.id;
                            option.textContent = company.company_name;
                            companySelect.appendChild(option);
                        });
                    })
                    .catch(error => {
                        console.error('خطأ في تحميل المؤسسات:', error);
                    });
            }
        });
    }
});
</script>

@endsection
