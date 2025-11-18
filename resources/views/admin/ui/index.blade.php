@extends('layouts.admin')

@section('page-title', 'مكونات الواجهة')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item active">UI Components</li>
@endsection

@section('content')
<div class="container-fluid">
    <!-- UI System Info -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-primary">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-palette"></i> نظام مكونات الواجهة</h5>
                </div>
                <div class="card-body">
                    <p class="lead">مكتبة شاملة من المكونات الجاهزة لبناء واجهات احترافية</p>
                    
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <h6><i class="fas fa-check-circle text-success"></i> المميزات:</h6>
                            <ul>
                                <li>Bootstrap 5.3</li>
                                <li>دعم RTL كامل</li>
                                <li>Responsive Design</li>
                                <li>Font Awesome Icons</li>
                                <li>مكونات قابلة لإعادة الاستخدام</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6><i class="fas fa-cubes text-info"></i> المكونات المتاحة:</h6>
                            <ul>
                                <li>Buttons & Forms</li>
                                <li>Cards & Modals</li>
                                <li>Tables & Charts</li>
                                <li>Alerts & Badges</li>
                                <li>Navigation & Breadcrumbs</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Buttons -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="fas fa-hand-pointer"></i> الأزرار (Buttons)</h5>
                </div>
                <div class="card-body">
                    <h6>الأزرار الأساسية:</h6>
                    <div class="mb-3">
                        <button class="btn btn-primary">Primary</button>
                        <button class="btn btn-secondary">Secondary</button>
                        <button class="btn btn-success">Success</button>
                        <button class="btn btn-danger">Danger</button>
                        <button class="btn btn-warning">Warning</button>
                        <button class="btn btn-info">Info</button>
                        <button class="btn btn-light">Light</button>
                        <button class="btn btn-dark">Dark</button>
                    </div>

                    <h6 class="mt-4">أزرار مع أيقونات:</h6>
                    <div class="mb-3">
                        <button class="btn btn-primary">
                            <i class="fas fa-save"></i> حفظ
                        </button>
                        <button class="btn btn-success">
                            <i class="fas fa-plus"></i> إضافة
                        </button>
                        <button class="btn btn-danger">
                            <i class="fas fa-trash"></i> حذف
                        </button>
                        <button class="btn btn-info">
                            <i class="fas fa-edit"></i> تعديل
                        </button>
                    </div>

                    <h6 class="mt-4">أحجام مختلفة:</h6>
                    <div>
                        <button class="btn btn-primary btn-lg">Large</button>
                        <button class="btn btn-primary">Normal</button>
                        <button class="btn btn-primary btn-sm">Small</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Cards -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card text-center">
                <div class="card-header bg-primary text-white">
                    Card Header
                </div>
                <div class="card-body">
                    <h5 class="card-title">Card Title</h5>
                    <p class="card-text">محتوى البطاقة هنا</p>
                    <button class="btn btn-primary">إجراء</button>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card text-center border-success">
                <div class="card-body">
                    <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                    <h5>نجاح</h5>
                    <p class="text-muted">تم العملية بنجاح</p>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card text-center border-danger">
                <div class="card-body">
                    <i class="fas fa-times-circle fa-3x text-danger mb-3"></i>
                    <h5>خطأ</h5>
                    <p class="text-muted">حدث خطأ ما</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Alerts -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-warning">
                    <h5 class="mb-0"><i class="fas fa-exclamation-triangle"></i> التنبيهات (Alerts)</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-primary" role="alert">
                        <i class="fas fa-info-circle"></i> تنبيه معلوماتي
                    </div>
                    <div class="alert alert-success" role="alert">
                        <i class="fas fa-check-circle"></i> تم العملية بنجاح!
                    </div>
                    <div class="alert alert-warning" role="alert">
                        <i class="fas fa-exclamation-triangle"></i> تحذير: انتبه لهذا الأمر
                    </div>
                    <div class="alert alert-danger" role="alert">
                        <i class="fas fa-times-circle"></i> خطأ: حدث خطأ ما
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Badges & Progress -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="fas fa-tag"></i> الشارات (Badges)</h5>
                </div>
                <div class="card-body">
                    <h6>الشارات الأساسية:</h6>
                    <span class="badge bg-primary">Primary</span>
                    <span class="badge bg-secondary">Secondary</span>
                    <span class="badge bg-success">Success</span>
                    <span class="badge bg-danger">Danger</span>
                    <span class="badge bg-warning">Warning</span>
                    <span class="badge bg-info">Info</span>

                    <h6 class="mt-4">شارات مع أيقونات:</h6>
                    <span class="badge bg-success">
                        <i class="fas fa-check"></i> نشط
                    </span>
                    <span class="badge bg-danger">
                        <i class="fas fa-times"></i> معطل
                    </span>
                    <span class="badge bg-warning">
                        <i class="fas fa-clock"></i> معلق
                    </span>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0"><i class="fas fa-tasks"></i> شريط التقدم (Progress)</h5>
                </div>
                <div class="card-body">
                    <h6>تقدم بسيط:</h6>
                    <div class="progress mb-3">
                        <div class="progress-bar" style="width: 25%">25%</div>
                    </div>

                    <h6>تقدم ملون:</h6>
                    <div class="progress mb-3">
                        <div class="progress-bar bg-success" style="width: 50%">50%</div>
                    </div>

                    <h6>تقدم متحرك:</h6>
                    <div class="progress">
                        <div class="progress-bar progress-bar-striped progress-bar-animated" style="width: 75%">75%</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Forms -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0"><i class="fas fa-edit"></i> النماذج (Forms)</h5>
                </div>
                <div class="card-body">
                    <form>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">حقل نصي</label>
                                    <input type="text" class="form-control" placeholder="أدخل النص">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">بريد إلكتروني</label>
                                    <input type="email" class="form-control" placeholder="email@example.com">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">قائمة منسدلة</label>
                                    <select class="form-select">
                                        <option>اختر...</option>
                                        <option>خيار 1</option>
                                        <option>خيار 2</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">تاريخ</label>
                                    <input type="date" class="form-control">
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">نص طويل</label>
                            <textarea class="form-control" rows="3"></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> حفظ
                        </button>
                        <button type="reset" class="btn btn-secondary">
                            <i class="fas fa-undo"></i> إعادة تعيين
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
