@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-plus-circle"></i> إضافة نوع حساب تحليلي جديد
                    </h3>
                </div>

                <form action="{{ route('analytical-account-types.store') }}" method="POST">
                    @csrf
                    
                    <div class="card-body">
                        {{-- Company Info --}}
                        <div class="alert alert-info">
                            <i class="fas fa-building"></i> المؤسسة: <strong>{{ $company->name }}</strong>
                        </div>

                        {{-- Info Alert --}}
                        <div class="alert alert-warning">
                            <i class="fas fa-lightbulb"></i> 
                            <strong>ما هي الحسابات التحليلية؟</strong><br>
                            تُستخدم لتصنيف الحسابات الفرعية في دليل الحسابات. مثلاً:
                            <ul class="mb-0 mt-2">
                                <li><strong>صندوق:</strong> للحسابات النقدية (الصندوق الرئيسي، صندوق الفرع...)</li>
                                <li><strong>بنك:</strong> للحسابات البنكية (بنك الرافدين، بنك الرشيد...)</li>
                                <li><strong>مورد:</strong> لحسابات الموردين</li>
                                <li><strong>عميل:</strong> لحسابات العملاء</li>
                            </ul>
                        </div>

                        {{-- Code --}}
                        <div class="form-group">
                            <label for="code">الرمز <span class="text-danger">*</span></label>
                            <input type="text" 
                                   name="code" 
                                   id="code" 
                                   class="form-control @error('code') is-invalid @enderror" 
                                   value="{{ old('code') }}"
                                   placeholder="مثال: CASH"
                                   required>
                            @error('code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">رمز مختصر (مثل: CASH للصندوق، BANK للبنك، SUPP للمورد)</small>
                        </div>

                        {{-- Name --}}
                        <div class="form-group">
                            <label for="name">الاسم <span class="text-danger">*</span></label>
                            <input type="text" 
                                   name="name" 
                                   id="name" 
                                   class="form-control @error('name') is-invalid @enderror" 
                                   value="{{ old('name') }}"
                                   placeholder="مثال: صندوق"
                                   required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Description --}}
                        <div class="form-group">
                            <label for="description">الوصف</label>
                            <textarea name="description" 
                                      id="description" 
                                      class="form-control @error('description') is-invalid @enderror" 
                                      rows="3"
                                      placeholder="وصف اختياري للنوع التحليلي...">{{ old('description') }}</textarea>
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
                            <small class="form-text text-muted">يمكن استخدام الأنواع النشطة فقط عند إنشاء الحسابات</small>
                        </div>

                        {{-- Examples --}}
                        <div class="card bg-light">
                            <div class="card-body">
                                <h6 class="card-title"><i class="fas fa-lightbulb text-warning"></i> أمثلة شائعة:</h6>
                                <div class="row">
                                    <div class="col-md-6">
                                        <ul class="small mb-0">
                                            <li><strong>CASH</strong> - صندوق</li>
                                            <li><strong>BANK</strong> - بنك</li>
                                            <li><strong>CASHIER</strong> - صراف</li>
                                        </ul>
                                    </div>
                                    <div class="col-md-6">
                                        <ul class="small mb-0">
                                            <li><strong>WALLET</strong> - محفظة</li>
                                            <li><strong>SUPP</strong> - مورد</li>
                                            <li><strong>CUST</strong> - عميل</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save"></i> حفظ
                        </button>
                        <a href="{{ route('analytical-account-types.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i> إلغاء
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
