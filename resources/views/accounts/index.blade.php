@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-sitemap"></i> دليل الحسابات
                    </h3>
                    <a href="{{ route('accounts.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> إضافة حساب جديد
                    </a>
                </div>

                <div class="card-body">
                    {{-- Filters --}}
                    <form method="GET" action="{{ route('accounts.index') }}" class="mb-4">
                        <div class="row">
                            <div class="col-md-3">
                                <input type="text" name="search" class="form-control" placeholder="بحث بالرمز أو الاسم..." value="{{ request('search') }}">
                            </div>
                            <div class="col-md-3">
                                <select name="account_type_id" class="form-control">
                                    <option value="">جميع أنواع الحسابات</option>
                                    @foreach($accountTypes as $type)
                                        <option value="{{ $type->id }}" {{ request('account_type_id') == $type->id ? 'selected' : '' }}>
                                            {{ $type->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select name="analytical_account_type_id" class="form-control">
                                    <option value="">جميع الأنواع التحليلية</option>
                                    @foreach($analyticalAccountTypes as $type)
                                        <option value="{{ $type->id }}" {{ request('analytical_account_type_id') == $type->id ? 'selected' : '' }}>
                                            {{ $type->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select name="is_active" class="form-control">
                                    <option value="">جميع الحالات</option>
                                    <option value="1" {{ request('is_active') == '1' ? 'selected' : '' }}>نشط</option>
                                    <option value="0" {{ request('is_active') == '0' ? 'selected' : '' }}>غير نشط</option>
                                </select>
                            </div>
                            <div class="col-md-1">
                                <button type="submit" class="btn btn-info btn-block">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                    </form>

                    {{-- Success/Error Messages --}}
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show">
                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                            {{ session('success') }}
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show">
                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                            {{ session('error') }}
                        </div>
                    @endif

                    {{-- Tree View --}}
                    <div class="accounts-tree">
                        @forelse($accounts as $account)
                            @include('accounts.partials.tree-item', ['account' => $account, 'level' => 0])
                        @empty
                            <div class="text-center text-muted py-5">
                                <i class="fas fa-folder-open fa-3x mb-3"></i>
                                <p>لا توجد حسابات في الدليل.</p>
                                <p><a href="{{ route('accounts.create') }}" class="btn btn-primary">إضافة حساب جديد</a></p>
                                <hr class="my-4">
                                <div class="alert alert-info">
                                    <strong>تلميح:</strong> ابدأ بإضافة الحسابات الرئيسية أولاً (مثل: الأصول، الخصوم، الإيرادات، المصروفات)، ثم أضف الحسابات الفرعية تحتها.
                                </div>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.accounts-tree {
    font-family: 'Courier New', monospace;
}

.account-item {
    padding: 10px;
    margin: 5px 0;
    border: 1px solid #e0e0e0;
    border-radius: 5px;
    background-color: #fff;
    transition: all 0.3s;
}

.account-item:hover {
    background-color: #f8f9fa;
    border-color: #007bff;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}

.account-item.main-account {
    background-color: #e3f2fd;
    border-left: 4px solid #2196F3;
    font-weight: bold;
}

.account-item.sub-account {
    background-color: #f1f8e9;
    border-left: 4px solid #8BC34A;
}

.account-children {
    margin-left: 30px;
    border-left: 2px dashed #ccc;
    padding-left: 10px;
}

.account-code {
    font-weight: bold;
    color: #2196F3;
    font-size: 1.1em;
}

.account-name {
    font-size: 1em;
    color: #333;
}

.account-type-badge {
    font-size: 0.75em;
    padding: 3px 8px;
    border-radius: 3px;
}

.btn-group-sm .btn {
    padding: 2px 8px;
    font-size: 0.875rem;
}
</style>

@endsection
