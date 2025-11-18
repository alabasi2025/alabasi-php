@extends('layouts.app')

@section('content')
<div class="container-fluid" dir="rtl">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">
                        <i class="fas fa-exchange-alt me-2"></i>
                        إنشاء تحويل جديد
                    </h4>
                </div>
                <div class="card-body">
                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('clearing-transactions.store') }}" method="POST" id="transfer-form">
                        @csrf

                        <!-- نوع التحويل -->
                        <div class="mb-4">
                            <label class="form-label required">نوع التحويل</label>
                            <div class="btn-group w-100" role="group">
                                <input type="radio" class="btn-check" name="transfer_type" id="inter_company" 
                                       value="inter_company" checked>
                                <label class="btn btn-outline-primary" for="inter_company">
                                    <i class="fas fa-building me-2"></i>
                                    بين مؤسسات (نفس الوحدة)
                                </label>

                                <input type="radio" class="btn-check" name="transfer_type" id="inter_unit" 
                                       value="inter_unit">
                                <label class="btn btn-outline-warning" for="inter_unit">
                                    <i class="fas fa-network-wired me-2"></i>
                                    بين وحدات (مؤسسات مختلفة)
                                </label>
                            </div>
                        </div>

                        <div class="row">
                            <!-- القسم الأيسر: من -->
                            <div class="col-md-6">
                                <div class="card bg-light mb-3">
                                    <div class="card-header bg-danger text-white">
                                        <h5 class="mb-0">من (المصدر)</h5>
                                    </div>
                                    <div class="card-body">
                                        <!-- الوحدة المصدر -->
                                        <div class="mb-3">
                                            <label class="form-label required">الوحدة</label>
                                            <select name="source_unit_id" id="source_unit_id" class="form-select" required>
                                                <option value="">اختر الوحدة</option>
                                                @foreach($units as $unit)
                                                    <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <!-- المؤسسة المصدر -->
                                        <div class="mb-3">
                                            <label class="form-label required">المؤسسة</label>
                                            <select name="source_company_id" id="source_company_id" class="form-select" required disabled>
                                                <option value="">اختر المؤسسة</option>
                                            </select>
                                        </div>

                                        <!-- الحساب المصدر -->
                                        <div class="mb-3">
                                            <label class="form-label required">الحساب</label>
                                            <select name="source_account_id" id="source_account_id" class="form-select" required disabled>
                                                <option value="">اختر الحساب</option>
                                            </select>
                                            <small class="text-muted">سيتم استخدام الحساب الوسيط تلقائياً</small>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- القسم الأيمن: إلى -->
                            <div class="col-md-6">
                                <div class="card bg-light mb-3">
                                    <div class="card-header bg-success text-white">
                                        <h5 class="mb-0">إلى (الهدف)</h5>
                                    </div>
                                    <div class="card-body">
                                        <!-- الوحدة الهدف -->
                                        <div class="mb-3">
                                            <label class="form-label required">الوحدة</label>
                                            <select name="target_unit_id" id="target_unit_id" class="form-select" required>
                                                <option value="">اختر الوحدة</option>
                                                @foreach($units as $unit)
                                                    <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <!-- المؤسسة الهدف -->
                                        <div class="mb-3">
                                            <label class="form-label required">المؤسسة</label>
                                            <select name="target_company_id" id="target_company_id" class="form-select" required disabled>
                                                <option value="">اختر المؤسسة</option>
                                            </select>
                                        </div>

                                        <!-- الحساب الهدف -->
                                        <div class="mb-3">
                                            <label class="form-label required">الحساب</label>
                                            <select name="target_account_id" id="target_account_id" class="form-select" required disabled>
                                                <option value="">اختر الحساب</option>
                                            </select>
                                            <small class="text-muted">سيتم استخدام الحساب الوسيط تلقائياً</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- تفاصيل التحويل -->
                        <div class="card mb-3">
                            <div class="card-header bg-info text-white">
                                <h5 class="mb-0">تفاصيل التحويل</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label required">المبلغ</label>
                                            <input type="number" name="amount" id="amount" class="form-control" 
                                                   step="0.01" min="0.01" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label required">تاريخ التحويل</label>
                                            <input type="date" name="transaction_date" class="form-control" 
                                                   value="{{ date('Y-m-d') }}" required>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label required">البيان</label>
                                    <textarea name="description" class="form-control" rows="3" required 
                                              placeholder="اكتب وصفاً للتحويل..."></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- الأزرار -->
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('clearing-transactions.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-right me-2"></i>
                                رجوع
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>
                                حفظ التحويل
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const transferTypeInputs = document.querySelectorAll('input[name="transfer_type"]');
    const sourceUnitSelect = document.getElementById('source_unit_id');
    const sourceCompanySelect = document.getElementById('source_company_id');
    const sourceAccountSelect = document.getElementById('source_account_id');
    const targetUnitSelect = document.getElementById('target_unit_id');
    const targetCompanySelect = document.getElementById('target_company_id');
    const targetAccountSelect = document.getElementById('target_account_id');

    // تغيير نوع التحويل
    transferTypeInputs.forEach(input => {
        input.addEventListener('change', function() {
            if (this.value === 'inter_company') {
                // بين مؤسسات: نفس الوحدة
                targetUnitSelect.value = sourceUnitSelect.value;
                targetUnitSelect.disabled = true;
            } else {
                // بين وحدات: وحدات مختلفة
                targetUnitSelect.disabled = false;
            }
            loadTargetCompanies();
        });
    });

    // تحميل المؤسسات عند اختيار الوحدة المصدر
    sourceUnitSelect.addEventListener('change', function() {
        loadSourceCompanies();
        
        const transferType = document.querySelector('input[name="transfer_type"]:checked').value;
        if (transferType === 'inter_company') {
            targetUnitSelect.value = this.value;
            loadTargetCompanies();
        }
    });

    // تحميل الحسابات عند اختيار المؤسسة المصدر
    sourceCompanySelect.addEventListener('change', function() {
        loadSourceAccounts();
    });

    // تحميل المؤسسات عند اختيار الوحدة الهدف
    targetUnitSelect.addEventListener('change', function() {
        loadTargetCompanies();
    });

    // تحميل الحسابات عند اختيار المؤسسة الهدف
    targetCompanySelect.addEventListener('change', function() {
        loadTargetAccounts();
    });

    // دالة تحميل المؤسسات المصدر
    function loadSourceCompanies() {
        const unitId = sourceUnitSelect.value;
        if (!unitId) {
            sourceCompanySelect.disabled = true;
            sourceCompanySelect.innerHTML = '<option value="">اختر المؤسسة</option>';
            return;
        }

        fetch(`/api/units/${unitId}/companies`)
            .then(response => response.json())
            .then(data => {
                sourceCompanySelect.innerHTML = '<option value="">اختر المؤسسة</option>';
                data.forEach(company => {
                    sourceCompanySelect.innerHTML += `<option value="${company.id}">${company.name}</option>`;
                });
                sourceCompanySelect.disabled = false;
            })
            .catch(error => {
                console.error('Error loading companies:', error);
                alert('حدث خطأ في تحميل المؤسسات');
            });
    }

    // دالة تحميل الحسابات المصدر
    function loadSourceAccounts() {
        const companyId = sourceCompanySelect.value;
        const unitId = sourceUnitSelect.value;
        
        if (!companyId || !unitId) {
            sourceAccountSelect.disabled = true;
            sourceAccountSelect.innerHTML = '<option value="">اختر الحساب</option>';
            return;
        }

        fetch(`/api/units/${unitId}/companies/${companyId}/accounts`)
            .then(response => response.json())
            .then(data => {
                sourceAccountSelect.innerHTML = '<option value="">اختر الحساب</option>';
                data.forEach(account => {
                    sourceAccountSelect.innerHTML += `<option value="${account.id}">${account.account_number} - ${account.name}</option>`;
                });
                sourceAccountSelect.disabled = false;
            })
            .catch(error => {
                console.error('Error loading accounts:', error);
                alert('حدث خطأ في تحميل الحسابات');
            });
    }

    // دالة تحميل المؤسسات الهدف
    function loadTargetCompanies() {
        const unitId = targetUnitSelect.value;
        if (!unitId) {
            targetCompanySelect.disabled = true;
            targetCompanySelect.innerHTML = '<option value="">اختر المؤسسة</option>';
            return;
        }

        fetch(`/api/units/${unitId}/companies`)
            .then(response => response.json())
            .then(data => {
                targetCompanySelect.innerHTML = '<option value="">اختر المؤسسة</option>';
                data.forEach(company => {
                    targetCompanySelect.innerHTML += `<option value="${company.id}">${company.name}</option>`;
                });
                targetCompanySelect.disabled = false;
            })
            .catch(error => {
                console.error('Error loading companies:', error);
                alert('حدث خطأ في تحميل المؤسسات');
            });
    }

    // دالة تحميل الحسابات الهدف
    function loadTargetAccounts() {
        const companyId = targetCompanySelect.value;
        const unitId = targetUnitSelect.value;
        
        if (!companyId || !unitId) {
            targetAccountSelect.disabled = true;
            targetAccountSelect.innerHTML = '<option value="">اختر الحساب</option>';
            return;
        }

        fetch(`/api/units/${unitId}/companies/${companyId}/accounts`)
            .then(response => response.json())
            .then(data => {
                targetAccountSelect.innerHTML = '<option value="">اختر الحساب</option>';
                data.forEach(account => {
                    targetAccountSelect.innerHTML += `<option value="${account.id}">${account.account_number} - ${account.name}</option>`;
                });
                targetAccountSelect.disabled = false;
            })
            .catch(error => {
                console.error('Error loading accounts:', error);
                alert('حدث خطأ في تحميل الحسابات');
            });
    }

    // التحقق من صحة النموذج قبل الإرسال
    document.getElementById('transfer-form').addEventListener('submit', function(e) {
        const transferType = document.querySelector('input[name="transfer_type"]:checked').value;
        const sourceUnitId = sourceUnitSelect.value;
        const targetUnitId = targetUnitSelect.value;
        const sourceCompanyId = sourceCompanySelect.value;
        const targetCompanyId = targetCompanySelect.value;

        if (transferType === 'inter_company') {
            // بين مؤسسات: نفس الوحدة، مؤسسات مختلفة
            if (sourceUnitId !== targetUnitId) {
                e.preventDefault();
                alert('خطأ: التحويل بين مؤسسات يتطلب نفس الوحدة');
                return false;
            }
            if (sourceCompanyId === targetCompanyId) {
                e.preventDefault();
                alert('خطأ: لا يمكن التحويل من وإلى نفس المؤسسة');
                return false;
            }
        } else {
            // بين وحدات: وحدات مختلفة
            if (sourceUnitId === targetUnitId) {
                e.preventDefault();
                alert('خطأ: التحويل بين وحدات يتطلب وحدات مختلفة');
                return false;
            }
        }
    });
});
</script>
@endpush
@endsection
