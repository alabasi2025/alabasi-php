<?php
session_start();

// إذا كان المستخدم مسجل دخول واختار القاعدة، انتقل للوحة التحكم
if (isset($_SESSION['unit_id']) && isset($_SESSION['database'])) {
    header('Location: dashboard.php');
    exit;
}

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Main\Unit;
use App\Models\Main\Company;

// جلب جميع الوحدات
$units = Unit::all();
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>نظام Alabasi المحاسبي - تسجيل الدخول</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            padding: 40px;
            max-width: 500px;
            width: 100%;
        }
        
        .logo {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .logo h1 {
            color: #667eea;
            font-size: 32px;
            margin-bottom: 10px;
        }
        
        .logo p {
            color: #666;
            font-size: 14px;
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        label {
            display: block;
            margin-bottom: 10px;
            color: #333;
            font-weight: 600;
            font-size: 14px;
        }
        
        select {
            width: 100%;
            padding: 15px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 16px;
            transition: all 0.3s;
            background: white;
        }
        
        select:focus {
            outline: none;
            border-color: #667eea;
        }
        
        button {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 18px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s;
        }
        
        button:hover {
            transform: translateY(-2px);
        }
        
        button:active {
            transform: translateY(0);
        }
        
        .info-box {
            background: #f8f9fa;
            border-right: 4px solid #667eea;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 25px;
        }
        
        .info-box p {
            color: #666;
            font-size: 14px;
            line-height: 1.6;
        }
        
        #company-group {
            display: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">
            <h1>🏢 نظام Alabasi</h1>
            <p>نظام محاسبي متعدد الوحدات والمؤسسات</p>
        </div>
        
        <div class="info-box">
            <p><strong>مرحباً بك!</strong> اختر الوحدة التي تريد الدخول إليها. إذا اخترت وحدة عمل، ستحتاج لاختيار المؤسسة أيضاً.</p>
        </div>
        
        <form method="POST" action="login_process.php">
            <div class="form-group">
                <label for="unit">📍 اختر الوحدة:</label>
                <select name="unit_id" id="unit" required onchange="handleUnitChange()">
                    <option value="">-- اختر الوحدة --</option>
                    <option value="main">📊 القاعدة المركزية (عرض التقارير)</option>
                    <?php foreach ($units as $unit): ?>
                        <option value="<?= $unit->id ?>" data-db="<?= $unit->database_name ?>">
                            🏢 <?= $unit->name ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group" id="company-group">
                <label for="company">🏪 اختر المؤسسة:</label>
                <select name="company_id" id="company">
                    <option value="">-- اختر المؤسسة --</option>
                </select>
            </div>
            
            <button type="submit">🚀 دخول</button>
        </form>
    </div>
    
    <script>
        const companies = <?= json_encode(Company::all()->groupBy('unit_id')->toArray()) ?>;
        
        function handleUnitChange() {
            const unitSelect = document.getElementById('unit');
            const companyGroup = document.getElementById('company-group');
            const companySelect = document.getElementById('company');
            const unitValue = unitSelect.value;
            
            // إذا كانت القاعدة المركزية، لا نحتاج اختيار مؤسسة
            if (unitValue === 'main') {
                companyGroup.style.display = 'none';
                companySelect.required = false;
                return;
            }
            
            // إذا كانت وحدة عمل، نعرض المؤسسات
            if (unitValue && companies[unitValue]) {
                companyGroup.style.display = 'block';
                companySelect.required = true;
                
                // ملء قائمة المؤسسات
                companySelect.innerHTML = '<option value="">-- اختر المؤسسة --</option>';
                companies[unitValue].forEach(company => {
                    const option = document.createElement('option');
                    option.value = company.id;
                    option.textContent = company.name;
                    companySelect.appendChild(option);
                });
            } else {
                companyGroup.style.display = 'none';
                companySelect.required = false;
            }
        }
    </script>
</body>
</html>
