@extends('layouts.app')

@section('title', 'المؤسسات')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="mb-0"><i class="fas fa-building"></i> المؤسسات</h3>
                    <a href="{{ route('companies.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> مؤسسة جديدة
                    </a>
                </div>
                
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>الرمز</th>
                                    <th>اسم المؤسسة</th>
                                    <th>المدير</th>
                                    <th>الهاتف</th>
                                    <th>البريد الإلكتروني</th>
                                    <th>الوحدة</th>
                                    <th>عدد الفروع</th>
                                    <th>الحالة</th>
                                    <th>الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($companies as $company)
                                <tr>
                                    <td><strong>{{ $company->company_code }}</strong></td>
                                    <td>
                                        <a href="{{ route('companies.show', $company) }}">
                                            {{ $company->company_name }}
                                        </a>
                                    </td>
                                    <td>{{ $company->director_name ?? '-' }}</td>
                                    <td>{{ $company->phone ?? '-' }}</td>
                                    <td>{{ $company->email ?? '-' }}</td>
                                    <td>
                                        @if($company->unit)
                                            <span class="badge bg-info">{{ $company->unit->unit_name }}</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">{{ $company->branches_count }}</span>
                                    </td>
                                    <td>
                                        @if($company->is_active)
                                            <span class="badge bg-success">نشط</span>
                                        @else
                                            <span class="badge bg-danger">غير نشط</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('companies.show', $company) }}" class="btn btn-sm btn-info" title="عرض">
                                                <i class="fas fa-eye"></i> عرض
                                            </a>
                                            <a href="{{ route('companies.edit', $company) }}" class="btn btn-sm btn-warning" title="تعديل">
                                                <i class="fas fa-edit"></i> تعديل
                                            </a>
                                            <form action="{{ route('companies.destroy', $company) }}" method="POST" class="d-inline" 
                                                  onsubmit="return confirm('هل أنت متأكد من حذف هذه المؤسسة؟')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" title="حذف">
                                                    <i class="fas fa-trash"></i> حذف
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="9" class="text-center py-4">
                                        <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                        <p class="text-muted">لا توجد مؤسسات</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    <div class="d-flex justify-content-center">
                        {{ $companies->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
