@extends('layouts.app')

@section('title', 'الوحدات')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="mb-0"><i class="fas fa-sitemap"></i> الوحدات</h3>
                    <a href="{{ route('units.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> وحدة جديدة
                    </a>
                </div>
                
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>الرمز</th>
                                    <th>اسم الوحدة</th>
                                    <th>عدد المؤسسات</th>
                                    <th>المدير</th>
                                    <th>الهاتف</th>
                                    <th>الحالة</th>
                                    <th>الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($units as $unit)
                                <tr>
                                    <td><strong>{{ $unit->unit_code }}</strong></td>
                                    <td>
                                        <a href="{{ route('units.show', $unit) }}">
                                            {{ $unit->unit_name }}
                                        </a>
                                    </td>
                                    <td>
                                        <span class="badge bg-info">{{ $unit->companies_count }}</span>
                                    </td>
                                    <td>{{ $unit->manager_name ?? '-' }}</td>
                                    <td>{{ $unit->phone ?? '-' }}</td>
                                    <td>
                                        @if($unit->is_active)
                                            <span class="badge bg-success">نشط</span>
                                        @else
                                            <span class="badge bg-danger">غير نشط</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('units.show', $unit) }}" class="btn btn-sm btn-info" title="عرض">
                                                <i class="fas fa-eye"></i> عرض
                                            </a>
                                            <a href="{{ route('units.edit', $unit) }}" class="btn btn-sm btn-warning" title="تعديل">
                                                <i class="fas fa-edit"></i> تعديل
                                            </a>
                                            <form action="{{ route('units.destroy', $unit) }}" method="POST" class="d-inline" 
                                                  onsubmit="return confirm('هل أنت متأكد من حذف هذه الوحدة؟')">
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
                                    <td colspan="8" class="text-center py-4">
                                        <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                        <p class="text-muted">لا توجد وحدات</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    <div class="d-flex justify-content-center">
                        {{ $units->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
