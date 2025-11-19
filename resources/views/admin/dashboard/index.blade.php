@extends('layouts.admin')

@section('page-title', 'لوحة التحكم الرئيسية')

@section('breadcrumb')
    <li class="breadcrumb-item active">الرئيسية</li>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Current Unit & Company Info -->
    @if(isset($currentUnit))
    <div class="alert alert-info mb-4" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; border-radius: 10px;">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <h5 class="mb-2">
                    <i class="fas fa-building ms-2"></i>
                    <strong>الوحدة الحالية:</strong> {{ $currentUnit->name }}
                </h5>
                @if(isset($currentCompany))
                <p class="mb-0">
                    <i class="fas fa-briefcase ms-2"></i>
                    <strong>المؤسسة:</strong> {{ $currentCompany->name }}
                </p>
                @endif
            </div>
            <div class="text-end">
                <span class="badge bg-light text-dark" style="font-size: 14px;">
                    <i class="fas fa-code ms-1"></i> {{ $currentUnit->code }}
                </span>
                @if(isset($currentCompany))
                <br>
                <span class="badge bg-light text-dark mt-2" style="font-size: 14px;">
                    <i class="fas fa-hashtag ms-1"></i> {{ $currentCompany->code }}
                </span>
                @endif
            </div>
        </div>
    </div>
    @endif
    
    <!-- Statistics Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="stat-card primary">
                <div class="icon">
                    <i class="fas fa-building"></i>
                </div>
                <h3>{{ $stats['total_units'] ?? 0 }}</h3>
                <p>إجمالي الوحدات</p>
            </div>
        </div>

        <div class="col-md-3">
            <div class="stat-card success">
                <div class="icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <h3>{{ $stats['active_units'] ?? 0 }}</h3>
                <p>الوحدات النشطة</p>
            </div>
        </div>

        <div class="col-md-3">
            <div class="stat-card warning">
                <div class="icon">
                    <i class="fas fa-briefcase"></i>
                </div>
                <h3>{{ $stats['total_companies'] ?? 0 }}</h3>
                <p>إجمالي المؤسسات</p>
            </div>
        </div>

        <div class="col-md-3">
            <div class="stat-card danger">
                <div class="icon">
                    <i class="fas fa-users"></i>
                </div>
                <h3>{{ $stats['total_users'] ?? 0 }}</h3>
                <p>إجمالي المستخدمين</p>
            </div>
        </div>
    </div>

    <!-- System Information -->
    <div class="row g-4 mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-info-circle"></i> معلومات النظام</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <td><strong>حجم قاعدة البيانات:</strong></td>
                            <td>{{ $stats['database_size'] ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td><strong>حجم الذاكرة المؤقتة:</strong></td>
                            <td>{{ $stats['cache_size'] ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td><strong>إصدار Laravel:</strong></td>
                            <td>{{ app()->version() }}</td>
                        </tr>
                        <tr>
                            <td><strong>إصدار PHP:</strong></td>
                            <td>{{ PHP_VERSION }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="fas fa-chart-pie"></i> الأداء</h5>
                </div>
                <div class="card-body">
                    <canvas id="performanceChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Laravel Features Status -->
    <div class="row g-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="fas fa-star"></i> حالة ميزات Laravel</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="feature-card">
                                <div class="feature-icon">
                                    <i class="fas fa-telescope"></i>
                                </div>
                                <div class="feature-info">
                                    <h5>Laravel Telescope</h5>
                                    <p>مراقبة وتتبع النظام</p>
                                </div>
                                <div class="feature-status">
                                    <span class="status-badge active">نشط</span>
                                    <a href="/telescope" class="btn btn-sm btn-primary" target="_blank">
                                        <i class="fas fa-external-link-alt"></i>
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="feature-card">
                                <div class="feature-icon">
                                    <i class="fas fa-code"></i>
                                </div>
                                <div class="feature-info">
                                    <h5>Laravel Pint</h5>
                                    <p>جودة الكود</p>
                                </div>
                                <div class="feature-status">
                                    <span class="status-badge active">نشط</span>
                                    <a href="{{ route('admin.pint.index') }}" class="btn btn-sm btn-primary">
                                        <i class="fas fa-arrow-left"></i>
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="feature-card">
                                <div class="feature-icon">
                                    <i class="fas fa-shield-alt"></i>
                                </div>
                                <div class="feature-info">
                                    <h5>Laravel Sanctum</h5>
                                    <p>المصادقة والأمان</p>
                                </div>
                                <div class="feature-status">
                                    <span class="status-badge active">نشط</span>
                                    <a href="{{ route('admin.auth.index') }}" class="btn btn-sm btn-primary">
                                        <i class="fas fa-arrow-left"></i>
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="feature-card">
                                <div class="feature-icon">
                                    <i class="fas fa-flag"></i>
                                </div>
                                <div class="feature-info">
                                    <h5>Laravel Pennant</h5>
                                    <p>إدارة الميزات</p>
                                </div>
                                <div class="feature-status">
                                    <span class="status-badge pending">قريباً</span>
                                    <a href="{{ route('admin.pennant.index') }}" class="btn btn-sm btn-warning">
                                        <i class="fas fa-arrow-left"></i>
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="feature-card">
                                <div class="feature-icon">
                                    <i class="fas fa-bolt"></i>
                                </div>
                                <div class="feature-info">
                                    <h5>Laravel Livewire</h5>
                                    <p>المكونات التفاعلية</p>
                                </div>
                                <div class="feature-status">
                                    <span class="status-badge pending">قريباً</span>
                                    <a href="{{ route('admin.components.index') }}" class="btn btn-sm btn-warning">
                                        <i class="fas fa-arrow-left"></i>
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="feature-card">
                                <div class="feature-icon">
                                    <i class="fas fa-database"></i>
                                </div>
                                <div class="feature-info">
                                    <h5>Eloquent ORM</h5>
                                    <p>إدارة قواعد البيانات</p>
                                </div>
                                <div class="feature-status">
                                    <span class="status-badge active">نشط</span>
                                    <a href="{{ route('admin.database') }}" class="btn btn-sm btn-primary">
                                        <i class="fas fa-arrow-left"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row g-4 mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0"><i class="fas fa-bolt"></i> إجراءات سريعة</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex gap-2 flex-wrap">
                        <button class="btn btn-primary" onclick="clearCache('all')">
                            <i class="fas fa-broom"></i> مسح كل الذاكرة المؤقتة
                        </button>
                        <button class="btn btn-success" onclick="clearCache('config')">
                            <i class="fas fa-cog"></i> مسح ذاكرة الإعدادات
                        </button>
                        <button class="btn btn-info" onclick="clearCache('route')">
                            <i class="fas fa-route"></i> مسح ذاكرة المسارات
                        </button>
                        <button class="btn btn-warning" onclick="clearCache('view')">
                            <i class="fas fa-eye"></i> مسح ذاكرة العروض
                        </button>
                        <a href="/telescope" class="btn btn-secondary" target="_blank">
                            <i class="fas fa-telescope"></i> فتح Telescope
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Performance Chart
const ctx = document.getElementById('performanceChart');
if (ctx) {
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['قواعد البيانات', 'الذاكرة المؤقتة', 'الملفات', 'أخرى'],
            datasets: [{
                data: [35, 25, 20, 20],
                backgroundColor: [
                    'rgba(37, 99, 235, 0.8)',
                    'rgba(16, 185, 129, 0.8)',
                    'rgba(245, 158, 11, 0.8)',
                    'rgba(239, 68, 68, 0.8)'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
}

// Clear Cache Function
function clearCache(type) {
    if (!confirm('هل أنت متأكد من مسح الذاكرة المؤقتة؟')) {
        return;
    }

    fetch('{{ route("admin.cache.clear") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ type: type })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('✅ ' + data.message);
        } else {
            alert('❌ ' + data.message);
        }
    })
    .catch(error => {
        alert('❌ حدث خطأ: ' + error);
    });
}
</script>
@endpush
