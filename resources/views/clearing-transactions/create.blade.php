@extends('layouts.app')

@section('title', 'تحويل جديد')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-plus"></i>
                        إنشاء تحويل جديد
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('clearing-transactions.index') }}" class="btn btn-sm btn-default">
                            <i class="fas fa-arrow-right"></i> رجوع
                        </a>
                    </div>
                </div>

                <form action="{{ route('clearing-transactions.store') }}" method="POST">
                    @csrf
                    <div class="card-body">
                        @if(session('error'))
                            <div class="alert alert-danger alert-dismissible fade show">
                                {{ session('error') }}
                                <button type="button" class="close" data-dismiss="alert">&times;</button>
                            </div>
                        @endif

                        <div class="row">
                            <div class="col-md-6">
                                <div class="card bg-light">
                                    <div class="card-header">
                                        <h5 class="mb-0"><i class="fas fa-arrow-up text-danger"></i> من (المصدر)</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="form-group">
                                            <label for="source_unit_id">الوحدة <span class="text-danger">*</span></label>
                                            <select name="source_unit_id" id="source_unit_id" class="form-control @error('source_unit_id') is-invalid @enderror" required>
                                                <option value="">-- اختر الوحدة --</option>
                                                @foreach($units as $unit)
                                                    <option value="{{ $unit->id }}" {{ old('source_unit_id') == $unit->id ? 'selected' : '' }}>
                                                        {{ $unit->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('source_unit_id')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                        </div>

                                        <div class="form-group">
                                            <label for="source_company_id">المؤسسة <span class="text-danger">*</span></label>
                                            <select name="source_company_id" id="source_company_id" class="form-control @error('source_company_id') is-invalid @enderror" required>
                                                <option value="">-- اختر المؤسسة --</option>
                                                @foreach($companies as $company)
                                                    <option value="{{ $company->id }}" data-unit="{{ $company->unit_id }}" {{ old('source_company_id') == $company->id ? 'selected' : '' }}>
                                                        {{ $company->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('source_company_id')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                        </div>

                                        <div class="form-group">
                                            <label for="source_account_id">الحساب <span class="text-danger">*</span></label>
                                            <select name="source_account_id" id="source_account_id" class="form-control @error('source_account_id') is-invalid @enderror" required>
                                                <option value="">-- اختر الحساب --</option>
                                            </select>
                                            @error('source_account_id')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                            <small class="form-text text-muted">سيتم تحميل الحسابات بعد اختيار المؤسسة</small>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="card bg-light">
                                    <div class="card-header">
                                        <h5 class="mb-0"><i class="fas fa-arrow-down text-success"></i> إلى (الهدف)</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="form-group">
                                            <label for="target_unit_id">الوحدة <span class="text-danger">*</span></label>
                                            <select name="target_unit_id" id="target_unit_id" class="form-control @error('target_unit_id') is-invalid @enderror" required>
                                                <option value="">-- اختر الوحدة --</option>
                                                @foreach($units as $unit)
                                                    <option value="{{ $unit->id }}" {{ old('target_unit_id') == $unit->id ? 'selected' : '' }}>
                                                        {{ $unit->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('target_unit_id')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                        </div>

                                        <div class="form-group">
                                            <label for="target_company_id">المؤسسة <span class="text-danger">*</span></label>
                                            <select name="target_company_id" id="target_company_id" class="form-control @error('target_company_id') is-invalid @enderror" required>
                                                <option value="">-- اختر المؤسسة --</option>
                                                @foreach($companies as $company)
                                                    <option value="{{ $company->id }}" data-unit="{{ $company->unit_id }}" {{ old('target_company_id') == $company->id ? 'selected' : '' }}>
                                                        {{ $company->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('target_company_id')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                        </div>

                                        <div class="form-group">
                                            <label for="target_account_id">الحساب <span class="text-danger">*</span></label>
                                            <select name="target_account_id" id="target_account_id" class="form-control @error('target_account_id') is-invalid @enderror" required>
                                                <option value="">-- اختر الحساب --</option>
                                            </select>
                                            @error('target_account_id')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                            <small class="form-text text-muted">سيتم تحميل الحسابات بعد اختيار المؤسسة</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-md-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="mb-0"><i class="fas fa-info-circle"></i> معلومات التحويل</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="form-group">
                                            <label for="amount">المبلغ (ريال يمني) <span class="text-danger">*</span></label>
                                            <input type="number" 
                                                   name="amount" 
                                                   id="amount" 
                                                   class="form-control @error('amount') is-invalid @enderror" 
                                                   value="{{ old('amount') }}" 
                                                   step="0.01" 
                                                   min="0.01"
                                                   required>
                                            @error('amount')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                        </div>

                                        <div class="form-group">
                                            <label for="description">الوصف / البيان <span class="text-danger">*</span></label>
                                            <textarea name="description" 
                                                      id="description" 
                                                      class="form-control @error('description') is-invalid @enderror" 
                                                      rows="3" 
                                                      required>{{ old('description') }}</textarea>
                                            @error('description')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> حفظ التحويل
                        </button>
                        <a href="{{ route('clearing-transactions.index') }}" class="btn btn-default">
                            <i class="fas fa-times"></i> إلغاء
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// تصفية المؤسسات حسب الوحدة المختارة
function filterCompanies(unitSelectId, companySelectId) {
    const unitSelect = document.getElementById(unitSelectId);
    const companySelect = document.getElementById(companySelectId);
    
    unitSelect.addEventListener('change', function() {
        const selectedUnit = this.value;
        const options = companySelect.querySelectorAll('option');
        
        options.forEach(option => {
            if (option.value === '') {
                option.style.display = 'block';
                return;
            }
            
            const optionUnit = option.getAttribute('data-unit');
            if (selectedUnit === '' || optionUnit === selectedUnit) {
                option.style.display = 'block';
            } else {
                option.style.display = 'none';
            }
        });
        
        // إعادة تعيين القيمة المختارة
        companySelect.value = '';
    });
}

// تطبيق التصفية على المصدر والهدف
filterCompanies('source_unit_id', 'source_company_id');
filterCompanies('target_unit_id', 'target_company_id');

// TODO: إضافة كود لتحميل الحسابات عبر AJAX عند اختيار المؤسسة
</script>
@endpush
@endsection
