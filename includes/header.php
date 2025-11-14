<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'نظام العباسي الموحد'; ?></title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="dashboard">
    <!-- القائمة الجانبية -->
    <div class="sidebar">
        <div class="sidebar-header">
            <h2>📊 نظام العباسي</h2>
        </div>
        
        <nav class="sidebar-menu">
            <a href="dashboard.php" class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
                <span class="icon">🏠</span>
                <span class="text">الرئيسية</span>
            </a>
            
            <a href="accounts.php" class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'accounts.php' ? 'active' : ''; ?>">
                <span class="icon">📊</span>
                <span class="text">دليل الحسابات</span>
            </a>
            
            <a href="accounts-manage.php" class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'accounts-manage.php' ? 'active' : ''; ?>">
                <span class="icon">⚙️</span>
                <span class="text">إدارة الحسابات</span>
            </a>
            
            <a href="analytical-accounts.php" class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'analytical-accounts.php' ? 'active' : ''; ?>">
                <span class="icon">🔍</span>
                <span class="text">الحسابات التحليلية</span>
            </a>
            
            <a href="journals.php" class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'journals.php' ? 'active' : ''; ?>">
                <span class="icon">📝</span>
                <span class="text">القيود اليومية</span>
            </a>
            
            <a href="reports.php" class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'reports.php' ? 'active' : ''; ?>">
                <span class="icon">📈</span>
                <span class="text">التقارير</span>
            </a>
            
            <div class="menu-divider"></div>
            
            <a href="#" class="menu-item" onclick="alert('قريباً'); return false;">
                <span class="icon">📦</span>
                <span class="text">المخزون</span>
            </a>
            
            <a href="#" class="menu-item" onclick="alert('قريباً'); return false;">
                <span class="icon">💰</span>
                <span class="text">المبيعات</span>
            </a>
            
            <a href="#" class="menu-item" onclick="alert('قريباً'); return false;">
                <span class="icon">🛒</span>
                <span class="text">المشتريات</span>
            </a>
            
            <div class="menu-divider"></div>
            
            <a href="units.php" class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'units.php' ? 'active' : ''; ?>">
                <span class="icon">🏛️</span>
                <span class="text">الوحدات</span>
            </a>
            
            <a href="companies.php" class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'companies.php' ? 'active' : ''; ?>">
                <span class="icon">🏢</span>
                <span class="text">المؤسسات</span>
            </a>
            
            <a href="branches.php" class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'branches.php' ? 'active' : ''; ?>">
                <span class="icon">🏪</span>
                <span class="text">الفروع</span>
            </a>
            
            <a href="warehouses.php" class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'warehouses.php' ? 'active' : ''; ?>">
                <span class="icon">📦</span>
                <span class="text">المخازن</span>
            </a>
            
            <a href="users.php" class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'users.php' ? 'active' : ''; ?>">
                <span class="icon">👥</span>
                <span class="text">المستخدمين</span>
            </a>
            
            <a href="accounting-cycles.php" class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'accounting-cycles.php' ? 'active' : ''; ?>">
                <span class="icon">📅</span>
                <span class="text">الدورات المحاسبية</span>
            </a>
            
            <a href="backup-manager.php" class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'backup-manager.php' ? 'active' : ''; ?>">
                <span class="icon">💾</span>
                <span class="text">النسخ الاحتياطي</span>
            </a>
            
            <a href="settings.php" class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'settings.php' ? 'active' : ''; ?>">
                <span class="icon">⚙️</span>
                <span class="text">الإعدادات</span>
            </a>
        </nav>
        
        <div class="sidebar-footer">
            <a href="logout.php" class="menu-item logout-btn">
                <span class="icon">🚪</span>
                <span class="text">تسجيل الخروج</span>
            </a>
        </div>
    </div>
    
    <!-- المحتوى الرئيسي -->
    <div class="main-content">
        <!-- شريط التنقل العلوي -->
        <nav class="topbar">
            <div class="topbar-content">
                <button class="sidebar-toggle" onclick="toggleSidebar()">☰</button>
                <div class="topbar-title">
                    <h1><?php echo $pageTitle ?? 'لوحة التحكم'; ?></h1>
                </div>
                <div class="topbar-user">
                    <span class="user-name">👤 <?php echo getCurrentUserName(); ?></span>
                </div>
            </div>
        </nav>
        
        <!-- محتوى الصفحة -->
        <div class="page-content">
