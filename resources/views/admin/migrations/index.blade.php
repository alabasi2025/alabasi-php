@extends('layouts.admin')

@section('page-title', 'إدارة الهجرات')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item active">Migrations</li>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Migrations Info -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-primary">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-database"></i> نظام هجرات قواعد البيانات</h5>
                </div>
                <div class="card-body">
                    <p class="lead">إدارة متقدمة لهجرات قواعد البيانات مع التحكم الكامل في الإصدارات</p>
                    
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <h6><i class="fas fa-check-circle text-success"></i> الفوائد:</h6>
                            <ul>
                                <li>التحكم في إصدارات قاعدة البيانات</li>
                                <li>سهولة التراجع عن التغييرات</li>
                                <li>مزامنة قاعدة البيانات بين البيئات</li>
                                <li>توثيق تلقائي للتغييرات</li>
                                <li>عمل جماعي منظم</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6><i class="fas fa-cog text-info"></i> الإحصائيات:</h6>
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <td><strong>إجمالي الهجرات:</strong></td>
                                    <td>15</td>
                                </tr>
                                <tr>
                                    <td><strong>المنفذة:</strong></td>
                                    <td><span class="badge bg-success">15</span></td>
                                </tr>
                                <tr>
                                    <td><strong>المعلقة:</strong></td>
                                    <td><span class="badge bg-warning">0</span></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Migrations List -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-list"></i> قائمة الهجرات</h5>
                    <button class="btn btn-light btn-sm" onclick="createMigration()">
                        <i class="fas fa-plus"></i> إنشاء هجرة
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>اسم الهجرة</th>
                                    <th>التاريخ</th>
                                    <th>الحالة</th>
                                    <th>الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>1</td>
                                    <td><code>create_units_table</code></td>
                                    <td>2024_01_01_000001</td>
                                    <td><span class="badge bg-success">منفذة</span></td>
                                    <td>
                                        <button class="btn btn-sm btn-danger" onclick="rollback('units')">
                                            <i class="fas fa-undo"></i> تراجع
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>2</td>
                                    <td><code>create_companies_table</code></td>
                                    <td>2024_01_01_000002</td>
                                    <td><span class="badge bg-success">منفذة</span></td>
                                    <td>
                                        <button class="btn btn-sm btn-danger" onclick="rollback('companies')">
                                            <i class="fas fa-undo"></i> تراجع
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>3</td>
                                    <td><code>create_branches_table</code></td>
                                    <td>2024_01_01_000003</td>
                                    <td><span class="badge bg-success">منفذة</span></td>
                                    <td>
                                        <button class="btn btn-sm btn-danger" onclick="rollback('branches')">
                                            <i class="fas fa-undo"></i> تراجع
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>4</td>
                                    <td><code>create_accounts_table</code></td>
                                    <td>2024_01_01_000004</td>
                                    <td><span class="badge bg-success">منفذة</span></td>
                                    <td>
                                        <button class="btn btn-sm btn-danger" onclick="rollback('accounts')">
                                            <i class="fas fa-undo"></i> تراجع
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>5</td>
                                    <td><code>create_transactions_table</code></td>
                                    <td>2024_01_01_000005</td>
                                    <td><span class="badge bg-success">منفذة</span></td>
                                    <td>
                                        <button class="btn btn-sm btn-danger" onclick="rollback('transactions')">
                                            <i class="fas fa-undo"></i> تراجع
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Commands -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-warning">
                    <h5 class="mb-0"><i class="fas fa-terminal"></i> أوامر Migrations</h5>
                </div>
                <div class="card-body">
                    <h6>تنفيذ جميع الهجرات:</h6>
                    <pre class="bg-dark text-white p-3 rounded"><code>php artisan migrate</code></pre>

                    <h6 class="mt-3">التراجع عن آخر هجرة:</h6>
                    <pre class="bg-dark text-white p-3 rounded"><code>php artisan migrate:rollback</code></pre>

                    <h6 class="mt-3">إعادة تعيين قاعدة البيانات:</h6>
                    <pre class="bg-dark text-white p-3 rounded"><code>php artisan migrate:fresh</code></pre>

                    <h6 class="mt-3">حالة الهجرات:</h6>
                    <pre class="bg-dark text-white p-3 rounded"><code>php artisan migrate:status</code></pre>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="fas fa-code"></i> إنشاء Migration</h5>
                </div>
                <div class="card-body">
                    <h6>إنشاء جدول جديد:</h6>
                    <pre class="bg-dark text-white p-3 rounded"><code>php artisan make:migration create_products_table</code></pre>

                    <h6 class="mt-3">إضافة عمود:</h6>
                    <pre class="bg-dark text-white p-3 rounded"><code>php artisan make:migration add_status_to_accounts_table</code></pre>

                    <h6 class="mt-3">مثال على Migration:</h6>
                    <pre class="bg-dark text-white p-3 rounded"><code>public function up()
{
    Schema::create('products', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->decimal('price', 10, 2);
        $table->timestamps();
    });
}</code></pre>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0"><i class="fas fa-bolt"></i> إجراءات سريعة</h5>
                </div>
                <div class="card-body">
                    <button class="btn btn-primary" onclick="runMigrations()">
                        <i class="fas fa-play"></i> تنفيذ جميع الهجرات
                    </button>
                    <button class="btn btn-warning" onclick="rollbackLast()">
                        <i class="fas fa-undo"></i> التراجع عن آخر هجرة
                    </button>
                    <button class="btn btn-info" onclick="migrationStatus()">
                        <i class="fas fa-info-circle"></i> حالة الهجرات
                    </button>
                    <button class="btn btn-danger" onclick="freshMigrate()">
                        <i class="fas fa-sync"></i> إعادة تعيين قاعدة البيانات
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function createMigration() {
    alert('📝 إنشاء Migration جديد...');
}

function rollback(table) {
    if (confirm(`هل تريد التراجع عن هجرة ${table}؟`)) {
        alert(`⏳ جاري التراجع عن ${table}...`);
    }
}

function runMigrations() {
    alert('⏳ جاري تنفيذ جميع الهجرات...');
}

function rollbackLast() {
    if (confirm('هل تريد التراجع عن آخر هجرة؟')) {
        alert('⏳ جاري التراجع...');
    }
}

function migrationStatus() {
    alert('📊 عرض حالة جميع الهجرات...');
}

function freshMigrate() {
    if (confirm('⚠️ هذا سيحذف جميع البيانات! هل أنت متأكد؟')) {
        alert('⏳ جاري إعادة تعيين قاعدة البيانات...');
    }
}
</script>
@endpush
