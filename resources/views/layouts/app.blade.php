<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'نظام الأباسي المحاسبي')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .sidebar { min-height: 100vh; background-color: #2c3e50; }
        .sidebar a { color: #ecf0f1; text-decoration: none; padding: 10px 15px; display: block; }
        .sidebar a:hover { background-color: #34495e; }
        .main-content { padding: 20px; }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-2 sidebar p-0">
                <div class="p-3 text-white">
                    <h4><i class="bi bi-calculator"></i> نظام الأباسي</h4>
                </div>
                <nav>
                    <a href="{{ route('dashboard') }}"><i class="bi bi-speedometer2"></i> لوحة التحكم</a>
                    <a href="{{ route('accounts.index') }}"><i class="bi bi-list-ul"></i> دليل الحسابات</a>
                    <a href="{{ route('account-types.index') }}"><i class="bi bi-tags"></i> أنواع الحسابات</a>
                    <a href="{{ route('analytical-account-types.index') }}"><i class="bi bi-layers"></i> أنواع الحسابات التحليلية</a>
                    <a href="{{ route('journal-entries.index') }}"><i class="bi bi-journal-text"></i> القيود اليومية</a>
                    <a href="#"><i class="bi bi-receipt"></i> السندات</a>
                    <a href="#"><i class="bi bi-people"></i> الحسابات التحليلية</a>
                    <a href="#"><i class="bi bi-file-earmark-text"></i> التقارير</a>
                    <a href="#"><i class="bi bi-gear"></i> الإعدادات</a>
                    <a href="{{ route('guide.index') }}"><i class="bi bi-book"></i> الدليل</a>
                </nav>
            </div>

            <!-- Main Content -->
            <div class="col-md-10 main-content">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @yield('content')
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    @yield('scripts')
</body>
</html>
