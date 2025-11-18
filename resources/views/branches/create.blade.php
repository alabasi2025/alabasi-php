@extends('layouts.app')

@section('title', 'إضافة فرع جديد')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-plus-circle me-2"></i>
                        إضافة فرع جديد
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('branches.store') }}" method="POST">
                        @csrf
                        
                        <div class="row">
                            <!-- رمز الفرع -->
                            <div class="col-md-6 mb-3">
                                <label for="branch_code" class="form-label">
                                    <i class="fas fa-barcode text-primary me-1"></i>
                                    رمز الفرع <span class="text-danger">*</span>
                                </label>
                                <input type="text" 
                                       class="form-control @error('branch_code') is-invalid @enderror" 
                                       id="branch_code" 
                                       name="branch_code" 
                                       value="{{ old('branch_code') }}" 
                                       placeholder="مثال: BR002"
                                       required>
                                @error('branch_code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">رمز فريد للفرع</small>
                            </div>

                            <!-- اسم الفرع -->
                            <div class="col-md-6 mb-3">
                                <label for="branch_name" class="form-label">
                                    <i class="fas fa-building text-primary me-1"></i>
                                    اسم الفرع <span class="text-danger">*</span>
                                </label>
                                <input type="text" 
                                       class="form-control @error('branch_name') is-invalid @enderror" 
                                       id="branch_name" 
                                       name="branch_name" 
                                       value="{{ old('branch_name') }}" 
                                       placeholder="مثال: فرع الحديدة"
                                       required>
                                @error('branch_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- الوحدة -->
                            <div class="col-md-6 mb-3">
                                <label for="unit_id" class="form-label">
                                    <i class="fas fa-layer-group text-primary me-1"></i>
                                    الوحدة <span class="text-danger">*</span>
                                </label>
                                <select class="form-select @error('unit_id') is-invalid @enderror" 
                                        id="unit_id" 
                                        name="unit_id"
                                        required>
                                    <option value="">اختر الوحدة</option>
                                    @foreach($units as $unit)
                                        <option value="{{ $unit->id }}" {{ old('unit_id') == $unit->id ? 'selected' : '' }}>
                                            {{ $unit->unit_name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('unit_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- المؤسسة -->
                            <div class="col-md-6 mb-3">
                                <label for="company_id" class="form-label">
                                    <i class="fas fa-city text-primary me-1"></i>
                                    المؤسسة <span class="text-danger">*</span>
                                </label>
                                <select class="form-select @error('company_id') is-invalid @enderror" 
                                        id="company_id" 
                                        name="company_id" 
                                        required
                                        disabled>
                                    <option value="">اختر الوحدة أولاً</option>
                                </select>
                                @error('company_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">سيتم عرض المؤسسات بعد اختيار الوحدة</small>
                            </div>

                            <!-- العنوان -->
                            <div class="col-md-12 mb-3">
                                <label for="address" class="form-label">
                                    <i class="fas fa-map-marker-alt text-primary me-1"></i>
                                    العنوان
                                </label>
                                <textarea class="form-control @error('address') is-invalid @enderror" 
                                          id="address" 
                                          name="address" 
                                          rows="2"
                                          placeholder="مثال: اليمن - الحديدة - شارع الكورنيش">{{ old('address') }}</textarea>
                                @error('address')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- الهاتف -->
                            <div class="col-md-4 mb-3">
                                <label for="phone" class="form-label">
                                    <i class="fas fa-phone text-primary me-1"></i>
                                    الهاتف
                                </label>
                                <input type="text" 
                                       class="form-control @error('phone') is-invalid @enderror" 
                                       id="phone" 
                                       name="phone" 
                                       value="{{ old('phone') }}"
                                       placeholder="مثال: 777123456">
                                @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- البريد الإلكتروني -->
                            <div class="col-md-4 mb-3">
                                <label for="email" class="form-label">
                                    <i class="fas fa-envelope text-primary me-1"></i>
                                    البريد الإلكتروني
                                </label>
                                <input type="email" 
                                       class="form-control @error('email') is-invalid @enderror" 
                                       id="email" 
                                       name="email" 
                                       value="{{ old('email') }}"
                                       placeholder="مثال: branch@company.com">
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- اسم المدير -->
                            <div class="col-md-4 mb-3">
                                <label for="manager_name" class="form-label">
                                    <i class="fas fa-user text-primary me-1"></i>
                                    اسم المدير
                                </label>
                                <input type="text" 
                                       class="form-control @error('manager_name') is-invalid @enderror" 
                                       id="manager_name" 
                                       name="manager_name" 
                                       value="{{ old('manager_name') }}"
                                       placeholder="مثال: أحمد محمد">
                                @error('manager_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- الحالة -->
                            <div class="col-md-12 mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" 
                                           type="checkbox" 
                                           id="is_active" 
                                           name="is_active" 
                                           value="1"
                                           {{ old('is_active', true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_active">
                                        <i class="fas fa-toggle-on text-success me-1"></i>
                                        الفرع نشط
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- الأزرار -->
                        <div class="row mt-3">
                            <div class="col-12">
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-save me-1"></i>
                                    حفظ الفرع
                                </button>
                                <a href="{{ route('branches.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-times me-1"></i>
                                    إلغاء
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- مولد الرموز التلقائي -->
<script src="{{ asset('js/auto-code-generator.js?v=2') }}"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded - initializing code generator');
    // تفعيل مولد الرموز التلقائي
    linkNameToCode('branch_name', 'branch_code', 'BR', 15);
    console.log('Code generator initialized');
});

// تصفية المؤسسات بناءً على الوحدة المختارة
document.addEventListener('DOMContentLoaded', function() {
    const unitSelect = document.getElementById('unit_id');
    if (unitSelect) {
        unitSelect.addEventListener('change', function() {
            const unitId = this.value;
            const companySelect = document.getElementById('company_id');
            
            // إعادة تعيين قائمة المؤسسات
            companySelect.innerHTML = '<option value="">جاري التحميل...</option>';
            companySelect.disabled = true;
            
            if (unitId) {
                // جلب المؤسسات التابعة للوحدة المختارة
                fetch(`/api/companies-by-unit/${unitId}`)
                    .then(response => response.json())
                    .then(data => {
                        companySelect.innerHTML = '<option value="">اختر المؤسسة</option>';
                        
                        if (data.length > 0) {
                            data.forEach(company => {
                                const option = document.createElement('option');
                                option.value = company.id;
                                option.textContent = company.company_name;
                                companySelect.appendChild(option);
                            });
                            companySelect.disabled = false;
                        } else {
                            companySelect.innerHTML = '<option value="">لا توجد مؤسسات لهذه الوحدة</option>';
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        companySelect.innerHTML = '<option value="">حدث خطأ في التحميل</option>';
                    });
            } else {
                companySelect.innerHTML = '<option value="">اختر الوحدة أولاً</option>';
            }
        });
    }
});
</script>
@endsection
