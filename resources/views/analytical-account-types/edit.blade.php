@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-edit"></i> تعديل نوع الحساب التحليلي
                    </h3>
                </div>

                <form action="{{ route('analytical-account-types.update', $analyticalAccountType) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="card-body">
                        {{-- Company Info --}}
                        <div class="alert alert-info">
                            <i class="fas fa-building"></i> المؤسسة: <strong>{{ $company->name }}</strong>
                        </div>

                        {{-- Code --}}
                        <div class="form-group">
                            <label for="code">الرمز <span class="text-danger">*</span></label>
                            <input type="text" 
                                   name="code" 
                                   id="code" 
                                   class="form-control @error('code') is-invalid @enderror" 
                                   value="{{ old('code', $analyticalAccountType->code) }}"
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
                                   value="{{ old('name', $analyticalAccountType->name) }}"
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
                                      placeholder="وصف اختياري للنوع التحليلي...">{{ old('description', $analyticalAccountType->description) }}</textarea>
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
                                       {{ old('is_active', $analyticalAccountType->is_active) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="is_active">
                                    <i class="fas fa-check-circle text-success"></i> نشط
                                </label>
                            </div>
                            <small class="form-text text-muted">يمكن استخدام الأنواع النشطة فقط عند إنشاء الحسابات</small>
                        </div>

                        {{-- Usage Info --}}
                        @php
                            $accountsCount = $analyticalAccountType->accounts()->count();
                            $analyticalAccountsCount = $analyticalAccountType->analyticalAccounts()->count();
                            $totalUsage = $accountsCount + $analyticalAccountsCount;
                        @endphp

                        @if($totalUsage > 0)
                            <div class="alert alert-warning">
                                <i class="fas fa-info-circle"></i> 
                                <strong>هذا النوع مستخدم في:</strong>
                                <ul class="mb-0 mt-2">
                                    @if($accountsCount > 0)
                                        <li>{{ $accountsCount }} حساب في دليل الحسابات</li>
                                    @endif
                                    @if($analyticalAccountsCount > 0)
                                        <li>{{ $analyticalAccountsCount }} حساب تحليلي</li>
                                    @endif
                                </ul>
                            </div>
                        @endif
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save"></i> حفظ التعديلات
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
