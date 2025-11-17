@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-layer-group"></i> أنواع الحسابات التحليلية
                    </h3>
                    <a href="{{ route('analytical-account-types.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> إضافة نوع تحليلي جديد
                    </a>
                </div>

                <div class="card-body">
                    {{-- Info Alert --}}
                    <div class="alert alert-info alert-dismissible fade show">
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                        <i class="fas fa-info-circle"></i> 
                        <strong>الحسابات التحليلية:</strong> تُستخدم لتصنيف الحسابات الفرعية (مثل: صندوق، بنك، صراف، محفظة، مورد، عميل)
                    </div>

                    {{-- Filters --}}
                    <form method="GET" action="{{ route('analytical-account-types.index') }}" class="mb-4">
                        <div class="row">
                            <div class="col-md-5">
                                <input type="text" name="search" class="form-control" placeholder="بحث بالرمز أو الاسم..." value="{{ request('search') }}">
                            </div>
                            <div class="col-md-4">
                                <select name="is_active" class="form-control">
                                    <option value="">جميع الحالات</option>
                                    <option value="1" {{ request('is_active') == '1' ? 'selected' : '' }}>نشط</option>
                                    <option value="0" {{ request('is_active') == '0' ? 'selected' : '' }}>غير نشط</option>
                                </select>
                            </div>
                            <div class="col-md-3">
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
                                    <th width="40%">الوصف</th>
                                    <th width="10%">الحالة</th>
                                    <th width="15%">الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($analyticalAccountTypes as $type)
                                    <tr>
                                        <td><strong class="text-primary">{{ $type->code }}</strong></td>
                                        <td>
                                            <i class="fas fa-tag text-muted"></i> {{ $type->name }}
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
                                                <a href="{{ route('analytical-account-types.edit', $type) }}" class="btn btn-warning" title="تعديل">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form action="{{ route('analytical-account-types.destroy', $type) }}" method="POST" class="d-inline" onsubmit="return confirm('هل أنت متأكد من حذف هذا النوع؟')">
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
                                        <td colspan="5" class="text-center text-muted py-5">
                                            <i class="fas fa-inbox fa-3x mb-3"></i>
                                            <p>لا توجد أنواع حسابات تحليلية.</p>
                                            <p><a href="{{ route('analytical-account-types.create') }}" class="btn btn-primary">إضافة نوع تحليلي جديد</a></p>
                                            <hr class="my-4">
                                            <p class="text-muted small">
                                                <strong>أمثلة:</strong> صندوق، بنك، صراف، محفظة، مورد، عميل، موظف...
                                            </p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Pagination --}}
                    <div class="d-flex justify-content-center">
                        {{ $analyticalAccountTypes->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
