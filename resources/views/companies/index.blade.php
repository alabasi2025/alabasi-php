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
                                    <th>عدد الوحدات</th>
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
                                        <span class="badge bg-info">{{ $company->units_count }}</span>
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
                                        <a href="{{ route('companies.show', $company) }}" class="btn btn-sm btn-info">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('companies.edit', $company) }}" class="btn btn-sm btn-warning">
                                            <i class="fas fa-edit"></i>
                                        </a>
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
