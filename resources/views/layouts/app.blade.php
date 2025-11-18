<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'نظام الأباسي المحاسبي')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            overflow-x: hidden;
        }
        
        /* القائمة اليمنى */
        .sidebar-right { 
            position: fixed;
            right: 0;
            top: 0;
            min-height: 100vh; 
            background-color: #2c3e50;
            width: 250px;
            transition: transform 0.3s ease;
            z-index: 1000;
        }
        .sidebar-right.collapsed {
            transform: translateX(250px);
        }
        .sidebar-right a { 
            color: #ecf0f1; 
            text-decoration: none; 
            padding: 10px 15px; 
            display: block;
            transition: background-color 0.2s;
        }
        .sidebar-right a:hover { 
            background-color: #34495e; 
        }
        .sidebar-right .nav-dropdown .dropdown-toggle {
            position: relative;
        }
        .sidebar-right .nav-dropdown .sub-item {
            padding-right: 40px;
            background-color: #1a252f;
            font-size: 0.9em;
        }
        .sidebar-right .nav-dropdown .sub-item:hover {
            background-color: #243447;
        }
        .sidebar-right .nav-dropdown .sub-sub-item {
            padding-right: 60px;
            background-color: #0f1419;
            font-size: 0.85em;
        }
        .sidebar-right .nav-dropdown .sub-sub-item:hover {
            background-color: #1a1f26;
        }
        
        /* القائمة اليسرى */
        .sidebar-left {
            position: fixed;
            left: 0;
            top: 0;
            min-height: 100vh;
            background-color: #34495e;
            width: 250px;
            transition: transform 0.3s ease;
            z-index: 1000;
            padding: 20px;
        }
        .sidebar-left.collapsed {
            transform: translateX(-250px);
        }
        
        /* المحتوى الرئيسي */
        .main-content { 
            padding: 20px;
            margin-right: 250px;
            margin-left: 0;
            transition: margin 0.3s ease;
        }
        .main-content.right-collapsed {
            margin-right: 0;
        }
        .main-content.left-expanded {
            margin-left: 250px;
        }
        
        /* أزرار التحكم */
        .toggle-btn {
            position: fixed;
            top: 10px;
            background-color: #2c3e50;
            color: white;
            border: none;
            padding: 10px 15px;
            cursor: pointer;
            border-radius: 5px;
            z-index: 1001;
            transition: all 0.3s ease;
        }
        .toggle-btn:hover {
            background-color: #34495e;
        }
        .toggle-btn-right {
            right: 10px;
        }
        .toggle-btn-right.sidebar-collapsed {
            right: 10px;
        }
        .toggle-btn-left {
            left: 10px;
        }
        .toggle-btn-left.sidebar-expanded {
            left: 260px;
        }
        
        /* محتوى التبويبة اليسرى */
        .sidebar-left h5 {
            color: #ecf0f1;
            border-bottom: 2px solid #2c3e50;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        .sidebar-left .info-item {
            background-color: #2c3e50;
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 5px;
            color: #ecf0f1;
        }
        .sidebar-left .info-item strong {
            display: block;
            color: #3498db;
            margin-bottom: 5px;
        }
    </style>
</head>
<body>
    <!-- زر التحكم في القائمة اليمنى -->
    <button class="toggle-btn toggle-btn-right" id="toggleRightSidebar">
        <i class="bi bi-list"></i>
    </button>
    
    <!-- زر التحكم في القائمة اليسرى -->
    <button class="toggle-btn toggle-btn-left" id="toggleLeftSidebar">
        <i class="bi bi-info-circle"></i>
    </button>

    <!-- القائمة اليمنى (القائمة الرئيسية) -->
    <div class="sidebar-right" id="rightSidebar">
        <div class="p-3 text-white">
            <h4><i class="bi bi-calculator"></i> نظام الأباسي</h4>
        </div>
        <nav>
            <a href="{{ route('dashboard') }}"><i class="bi bi-speedometer2"></i> لوحة التحكم</a>
            
            <!-- إدارة النظام -->
            <div class="nav-dropdown">
                <a href="#" class="dropdown-toggle" data-bs-toggle="collapse" data-bs-target="#systemManagement">
                    <i class="bi bi-gear-wide-connected"></i> إدارة النظام
                    <i class="bi bi-chevron-down float-start"></i>
                </a>
                <div class="collapse" id="systemManagement">
                    <a href="{{ route('units.index') }}" class="sub-item"><i class="bi bi-building"></i> الوحدات</a>
                    <a href="{{ route('companies.index') }}" class="sub-item"><i class="bi bi-buildings"></i> المؤسسات</a>
                    <a href="#" class="sub-item"><i class="bi bi-diagram-3"></i> الفروع</a>
                </div>
            </div>
            
            <!-- نظام الحسابات -->
            <div class="nav-dropdown">
                <a href="#" class="dropdown-toggle" data-bs-toggle="collapse" data-bs-target="#accountingSystem">
                    <i class="bi bi-calculator-fill"></i> نظام الحسابات
                    <i class="bi bi-chevron-down float-start"></i>
                </a>
                <div class="collapse" id="accountingSystem">
                    <!-- إعدادات النظام المحاسبي -->
                    <div class="nav-dropdown">
                        <a href="#" class="sub-item dropdown-toggle" data-bs-toggle="collapse" data-bs-target="#accountingSettings">
                            <i class="bi bi-gear"></i> إعدادات النظام المحاسبي
                            <i class="bi bi-chevron-down float-start"></i>
                        </a>
                        <div class="collapse" id="accountingSettings">
                            <a href="{{ route('accounts.index') }}" class="sub-sub-item"><i class="bi bi-list-ul"></i> الدليل المحاسبي</a>
                            <a href="{{ route('account-types.index') }}" class="sub-sub-item"><i class="bi bi-tags"></i> أنواع الحسابات</a>
                            <a href="{{ route('analytical-account-types.index') }}" class="sub-sub-item"><i class="bi bi-layers"></i> أنواع الحسابات التحليلية</a>
                            <a href="{{ route('analytical-accounts.index') }}" class="sub-sub-item"><i class="bi bi-people"></i> الحسابات التحليلية</a>
                        </div>
                    </div>
                    
                    <a href="{{ route('journal-entries.index') }}" class="sub-item"><i class="bi bi-journal-text"></i> القيود</a>
                    <a href="#" class="sub-item"><i class="bi bi-receipt"></i> السندات</a>
                    <a href="{{ route('cashboxes.index') }}" class="sub-item"><i class="bi bi-cash-stack"></i> الصناديق</a>
                    <a href="{{ route('bank-accounts.index') }}" class="sub-item"><i class="bi bi-bank"></i> الحسابات البنكية</a>
                    <a href="{{ route('customers.index') }}" class="sub-item"><i class="bi bi-people"></i> العملاء</a>
                    <a href="{{ route('suppliers.index') }}" class="sub-item"><i class="bi bi-truck"></i> الموردين</a>
                    <a href="{{ route('employees.index') }}" class="sub-item"><i class="bi bi-person-badge"></i> الموظفين</a>
                </div>
            </div>
            <a href="#"><i class="bi bi-file-earmark-text"></i> التقارير</a>
            <a href="#"><i class="bi bi-gear"></i> الإعدادات</a>
            <a href="{{ route('guide.index') }}"><i class="bi bi-book"></i> الدليل</a>
            <a href="{{ route('setup.index') }}"><i class="bi bi-gear-fill"></i> إعداد النظام</a>
        </nav>
    </div>

    <!-- القائمة اليسرى (معلومات إضافية) -->
    <div class="sidebar-left collapsed" id="leftSidebar">
        <h5><i class="bi bi-info-circle"></i> معلومات النظام</h5>
        
        <div class="info-item">
            <strong><i class="bi bi-calendar"></i> التاريخ</strong>
            <span id="currentDate"></span>
        </div>
        
        <div class="info-item">
            <strong><i class="bi bi-clock"></i> الوقت</strong>
            <span id="currentTime"></span>
        </div>
        
        <div class="info-item">
            <strong><i class="bi bi-building"></i> الوحدات</strong>
            <span>4 وحدات نشطة</span>
        </div>
        
        <div class="info-item">
            <strong><i class="bi bi-journal-text"></i> القيود اليوم</strong>
            <span>0 قيد</span>
        </div>
        
        <div class="info-item">
            <strong><i class="bi bi-person-circle"></i> المستخدم</strong>
            <span>مدير النظام</span>
        </div>
        
        <h5 class="mt-4"><i class="bi bi-bookmark"></i> اختصارات سريعة</h5>
        
        <a href="{{ route('journal-entries.create') }}" class="btn btn-primary btn-sm w-100 mb-2">
            <i class="bi bi-plus-circle"></i> قيد جديد
        </a>
        
        <a href="{{ route('accounts.create') }}" class="btn btn-success btn-sm w-100 mb-2">
            <i class="bi bi-plus-circle"></i> حساب جديد
        </a>
        
        <a href="{{ route('units.create') }}" class="btn btn-info btn-sm w-100">
            <i class="bi bi-plus-circle"></i> وحدة جديدة
        </a>
    </div>

    <!-- المحتوى الرئيسي -->
    <div class="main-content" id="mainContent">
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // التحكم في القائمة اليمنى
        const rightSidebar = document.getElementById('rightSidebar');
        const toggleRightBtn = document.getElementById('toggleRightSidebar');
        const mainContent = document.getElementById('mainContent');
        
        toggleRightBtn.addEventListener('click', function() {
            rightSidebar.classList.toggle('collapsed');
            mainContent.classList.toggle('right-collapsed');
            toggleRightBtn.classList.toggle('sidebar-collapsed');
        });
        
        // التحكم في القائمة اليسرى
        const leftSidebar = document.getElementById('leftSidebar');
        const toggleLeftBtn = document.getElementById('toggleLeftSidebar');
        
        toggleLeftBtn.addEventListener('click', function() {
            leftSidebar.classList.toggle('collapsed');
            mainContent.classList.toggle('left-expanded');
            toggleLeftBtn.classList.toggle('sidebar-expanded');
        });
        
        // تحديث التاريخ والوقت
        function updateDateTime() {
            const now = new Date();
            const dateOptions = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
            const timeOptions = { hour: '2-digit', minute: '2-digit', second: '2-digit' };
            
            document.getElementById('currentDate').textContent = now.toLocaleDateString('ar-SA', dateOptions);
            document.getElementById('currentTime').textContent = now.toLocaleTimeString('ar-SA', timeOptions);
        }
        
        updateDateTime();
        setInterval(updateDateTime, 1000);
    </script>
    @yield('scripts')
</body>
</html>
