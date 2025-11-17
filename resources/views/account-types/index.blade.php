@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-list-alt"></i> أنواع الحسابات
                    </h3>
                    <a href="{{ route('account-types.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> إضافة نوع جديد
                    </a>
                </div>

                <div class="card-body">
                    {{-- Filters --}}
                    <form method="GET" action="{{ route('account-types.index') }}" class="mb-4">
                        <div class="row">
                            <div class="col-md-4">
                                <input type="text" name="search" class="form-control" placeholder="بحث بالرمز أو الاسم..." value="{{ request('search') }}">
                            </div>
                            <div class="col-md-3">
                                <select name="nature" class="form-control">
                                    <option value="">جميع الطبائع</option>
                                    <option value="debit" {{ request('nature') == 'debit' ? 'selected' : '' }}>مدين</option>
                                    <option value="credit" {{ request('nature') == 'credit' ? 'selected' : '' }}>دائن</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select name="is_active" class="form-control">
                                    <option value="">جميع الحالات</option>
                                    <option value="1" {{ request('is_active') == '1' ? 'selected' : '' }}>نشط</option>
                                    <option value="0" {{ request('is_active') == '0' ? 'selected' : '' }}>غير نشط</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-info btn-block">
                                    <i class="fas fa-search"></i> بحث
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

                    {{-- Table --}}
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="thead-light">
                                <tr>
                                    <th width="10%">الرمز</th>
                                    <th width="25%">الاسم</th>
                                    <th width="15%">الطبيعة</th>
                                    <th width="30%">الوصف</th>
                                    <th width="10%">الحالة</th>
                                    <th width="10%">الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($accountTypes as $type)
                                    <tr>
                                        <td><strong>{{ $type->code }}</strong></td>
                                        <td>{{ $type->name }}</td>
                                        <td>
                                            @if($type->nature == 'debit')
                                                <span class="badge badge-info">مدين</span>
                                            @else
                                                <span class="badge badge-success">دائن</span>
                                            @endif
                                        </td>
                                        <td>{{ $type->description ?? '-' }}</td>
                                        <td>
                                            @if($type->is_active)
                                                <span class="badge badge-success">نشط</span>
                                            @else
                                                <span class="badge badge-secondary">غير نشط</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="{{ route('account-types.edit', $type) }}" class="btn btn-warning" title="تعديل">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form action="{{ route('account-types.destroy', $type) }}" method="POST" class="d-inline" onsubmit="return confirm('هل أنت متأكد من حذف هذا النوع؟')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger" title="حذف">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted">
                                            <i class="fas fa-inbox fa-3x mb-3"></i>
                                            <p>لا توجد أنواع حسابات. <a href="{{ route('account-types.create') }}">إضافة نوع جديد</a></p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Pagination --}}
                    <div class="d-flex justify-content-center">
                        {{ $accountTypes->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
