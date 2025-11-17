@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-edit"></i> تعديل نوع الحساب
                    </h3>
                </div>

                <form action="{{ route('account-types.update', $accountType) }}" method="POST">
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
                                   value="{{ old('code', $accountType->code) }}"
                                   placeholder="مثال: AST"
                                   required>
                            @error('code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">رمز مختصر لنوع الحساب (مثل: AST للأصول، LIA للخصوم)</small>
                        </div>

                        {{-- Name --}}
                        <div class="form-group">
                            <label for="name">الاسم <span class="text-danger">*</span></label>
                            <input type="text" 
                                   name="name" 
                                   id="name" 
                                   class="form-control @error('name') is-invalid @enderror" 
                                   value="{{ old('name', $accountType->name) }}"
                                   placeholder="مثال: أصول"
                                   required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Nature --}}
                        <div class="form-group">
                            <label>الطبيعة <span class="text-danger">*</span></label>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="custom-control custom-radio">
                                        <input type="radio" 
                                               id="nature_debit" 
                                               name="nature" 
                                               value="debit" 
                                               class="custom-control-input @error('nature') is-invalid @enderror"
                                               {{ old('nature', $accountType->nature) == 'debit' ? 'checked' : '' }}
                                               required>
                                        <label class="custom-control-label" for="nature_debit">
                                            <i class="fas fa-arrow-left text-info"></i> مدين (Debit)
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="custom-control custom-radio">
                                        <input type="radio" 
                                               id="nature_credit" 
                                               name="nature" 
                                               value="credit" 
                                               class="custom-control-input @error('nature') is-invalid @enderror"
                                               {{ old('nature', $accountType->nature) == 'credit' ? 'checked' : '' }}
                                               required>
                                        <label class="custom-control-label" for="nature_credit">
                                            <i class="fas fa-arrow-right text-success"></i> دائن (Credit)
                                        </label>
                                    </div>
                                </div>
                            </div>
                            @error('nature')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">
                                الأصول والمصروفات: مدين | الخصوم والإيرادات وحقوق الملكية: دائن
                            </small>
                        </div>

                        {{-- Description --}}
                        <div class="form-group">
                            <label for="description">الوصف</label>
                            <textarea name="description" 
                                      id="description" 
                                      class="form-control @error('description') is-invalid @enderror" 
                                      rows="3"
                                      placeholder="وصف اختياري لنوع الحساب...">{{ old('description', $accountType->description) }}</textarea>
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
                                       {{ old('is_active', $accountType->is_active) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="is_active">
                                    <i class="fas fa-check-circle text-success"></i> نشط
                                </label>
                            </div>
                            <small class="form-text text-muted">يمكن استخدام الأنواع النشطة فقط عند إنشاء الحسابات</small>
                        </div>

                        {{-- Accounts Count Info --}}
                        @if($accountType->accounts()->count() > 0)
                            <div class="alert alert-warning">
                                <i class="fas fa-info-circle"></i> 
                                هذا النوع مرتبط بـ <strong>{{ $accountType->accounts()->count() }}</strong> حساب
                            </div>
                        @endif
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save"></i> حفظ التعديلات
                        </button>
                        <a href="{{ route('account-types.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i> إلغاء
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
