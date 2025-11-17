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
                            <i class="fas fa-building"></i> المؤسسة: <strong>{{ $company->name }}</strong>
                        </div>

                        {{-- Parent Account Info (if adding sub-account) --}}
                        @if($parentAccount)
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
                                               {{ old('is_main', $parentAccount ? '0' : '1') == '1' ? 'checked' : '' }}
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
                                               {{ old('is_main', $parentAccount ? '0' : '1') == '0' ? 'checked' : '' }}
                                               onchange="toggleAnalyticalType()">
                                        <label class="form-check-label" for="is_main_false">
                                            <i class="fas fa-file text-success"></i> حساب فرعي (يمكن الترحيل عليه)
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Parent Account (only for main accounts) --}}
                        <div class="row" id="parent_account_row" style="display: {{ old('is_main', $parentAccount ? '0' : '1') == '1' ? 'block' : 'none' }};">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="parent_id">الحساب الأب (اختياري)</label>
                                    <select name="parent_id" 
                                            id="parent_id" 
                                            class="form-control @error('parent_id') is-invalid @enderror">
                                        <option value="">-- بدون حساب أب --</option>
                                        @foreach($parentAccounts as $parent)
                                            <option value="{{ $parent->id }}" {{ old('parent_id', $parentAccount ? $parentAccount->id : '') == $parent->id ? 'selected' : '' }}>
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

                        {{-- Analytical Account Type (only for sub accounts) --}}
                        <div class="row" id="analytical_type_row" style="display: {{ old('is_main', $parentAccount ? '0' : '1') == '0' ? 'block' : 'none' }};">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="analytical_account_type_id">النوع التحليلي <span class="text-danger">*</span></label>
                                    <select name="analytical_account_type_id" 
                                            id="analytical_account_type_id" 
                                            class="form-control @error('analytical_account_type_id') is-invalid @enderror">
                                        <option value="">-- اختر النوع التحليلي --</option>
                                        @foreach($analyticalAccountTypes as $type)
                                            <option value="{{ $type->id }}" {{ old('analytical_account_type_id') == $type->id ? 'selected' : '' }}>
                                                {{ $type->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('analytical_account_type_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">
                                        مثل: صندوق، بنك، مورد، عميل... 
                                        <a href="{{ route('analytical-account-types.create') }}" target="_blank">إضافة نوع تحليلي جديد</a>
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
    const analyticalRow = document.getElementById('analytical_type_row');
    const analyticalSelect = document.getElementById('analytical_account_type_id');
    
    if (isMain == '1') {
        // Main account
        parentRow.style.display = 'block';
        analyticalRow.style.display = 'none';
        analyticalSelect.removeAttribute('required');
        analyticalSelect.value = '';
    } else {
        // Sub account
        parentRow.style.display = 'none';
        analyticalRow.style.display = 'block';
        analyticalSelect.setAttribute('required', 'required');
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    toggleAnalyticalType();
});
</script>

@endsection
