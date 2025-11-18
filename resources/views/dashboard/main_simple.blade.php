<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>القاعدة المركزية - نظام Alabasi</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f7fa;
        }
        
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .header h1 {
            font-size: 24px;
        }
        
        .header a {
            color: white;
            text-decoration: none;
            background: rgba(255,255,255,0.2);
            padding: 10px 20px;
            border-radius: 8px;
            transition: all 0.3s;
        }
        
        .header a:hover {
            background: rgba(255,255,255,0.3);
        }
        
        .container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }
        
        .welcome {
            background: white;
            padding: 60px;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            text-align: center;
        }
        
        .welcome h2 {
            color: #667eea;
            font-size: 32px;
            margin-bottom: 20px;
        }
        
        .welcome p {
            color: #666;
            font-size: 18px;
            line-height: 1.8;
            margin-bottom: 30px;
        }
        
        .error-box {
            background: #fff3cd;
            border: 2px solid #ffc107;
            padding: 20px;
            border-radius: 10px;
            margin-top: 20px;
            text-align: right;
        }
        
        .error-box h3 {
            color: #856404;
            margin-bottom: 10px;
        }
        
        .error-box p {
            color: #856404;
            font-size: 14px;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 40px;
        }
        
        .stat-card {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            text-align: center;
        }
        
        .stat-card h3 {
            color: #667eea;
            font-size: 48px;
            margin-bottom: 10px;
        }
        
        .stat-card p {
            color: #666;
            font-size: 16px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>🏢 القاعدة المركزية - نظام Alabasi</h1>
        <div>
            <a href="/logout">🚪 خروج</a>
        </div>
    </div>
    
    <div class="container">
        <div class="welcome">
            <h2>مرحباً بك في القاعدة المركزية</h2>
            <p>نظام محاسبي متعدد الوحدات والمؤسسات</p>
            
            @if(isset($error))
                <div class="error-box">
                    <h3>⚠️ تنبيه</h3>
                    <p>يرجى التأكد من إعداد قاعدة البيانات بشكل صحيح.</p>
                    <p style="margin-top: 10px; font-size: 12px;">{{ $error }}</p>
                </div>
            @endif
        </div>
        
        <div class="stats-grid">
            <div class="stat-card">
                <h3>0</h3>
                <p>الوحدات النشطة</p>
            </div>
            
            <div class="stat-card">
                <h3>0</h3>
                <p>المؤسسات المسجلة</p>
            </div>
            
            <div class="stat-card">
                <h3>0</h3>
                <p>التحويلات</p>
            </div>
            
            <div class="stat-card">
                <h3>0</h3>
                <p>ريال يمني</p>
            </div>
        </div>
    </div>
</body>
</html>
