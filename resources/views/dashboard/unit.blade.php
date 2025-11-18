<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ session('unit_name') }} - نظام Alabasi</title>
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
        
        .header .user-info {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        
        .header .user-info span {
            background: rgba(255,255,255,0.2);
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 14px;
        }
        
        .header a {
            color: white;
            text-decoration: none;
            background: rgba(255,255,255,0.2);
            padding: 8px 16px;
            border-radius: 20px;
            transition: background 0.3s;
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
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>🏢 نظام Alabasi المحاسبي</h1>
        <div class="user-info">
            <span>📍 {{ session('unit_name') }}</span>
            <span>🏪 {{ session('company_name') }}</span>
            <a href="/logout">🚪 خروج</a>
        </div>
    </div>
    
    <div class="container">
        <div class="welcome">
            <h2>مرحباً بك في {{ session('unit_name') }}</h2>
            <p>هذه الواجهة قيد التطوير. سيتم إضافة المزيد من الميزات قريباً.</p>
        </div>
    </div>
</body>
</html>
