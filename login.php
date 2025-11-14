<?php
/**
 * صفحة تسجيل الدخول - دخول مباشر كمستخدم root
 * Login Page - Direct Login as Root User
 */

session_start();

require_once 'includes/db.php';
require_once 'includes/functions.php';

// إذا كان المستخدم مسجل دخول مسبقاً، اذهب إلى لوحة التحكم
if (isLoggedIn()) {
    header("Location: dashboard.php");
    exit;
}

// تسجيل دخول تلقائي كمستخدم root
try {
    // البحث عن مستخدم root أو إنشاؤه
    $stmt = $pdo->query("SELECT * FROM users WHERE email = 'root' OR username = 'root' LIMIT 1");
    $user = $stmt->fetch();
    
    if (!$user) {
        // إنشاء مستخدم root إذا لم يكن موجوداً
        $insertStmt = $pdo->prepare("INSERT INTO users (username, email, name, role, password, createdAt, lastSignedIn) VALUES (?, ?, ?, ?, '', NOW(), NOW())");
        $insertStmt->execute(['root', 'root', 'Root User', 'admin']);
        
        // جلب المستخدم المُنشأ
        $user = [
            'id' => $pdo->lastInsertId(),
            'username' => 'root',
            'email' => 'root',
            'name' => 'Root User',
            'role' => 'admin'
        ];
    }
    
    // تسجيل الدخول تلقائياً
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['email'] ?? $user['username'] ?? 'root';
    $_SESSION['user_name'] = $user['name'] ?? 'Root User';
    $_SESSION['user_role'] = $user['role'] ?? 'admin';
    
    // تحديث آخر تسجيل دخول
    $updateStmt = $pdo->prepare("UPDATE users SET lastSignedIn = NOW() WHERE id = ?");
    $updateStmt->execute([$user['id']]);
    
    // الذهاب إلى لوحة التحكم
    header("Location: dashboard.php");
    exit;
    
} catch (PDOException $e) {
    $error = "خطأ في الاتصال بقاعدة البيانات: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسجيل الدخول - نظام الأباسي الموحد</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-container {
            width: 100%;
            max-width: 450px;
            padding: 20px;
        }
        .login-box {
            background: white;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
        }
        .login-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px 30px;
            text-align: center;
        }
        .login-header h1 {
            margin: 0 0 10px 0;
            font-size: 28px;
            font-weight: 600;
        }
        .login-header p {
            margin: 0;
            opacity: 0.9;
            font-size: 16px;
        }
        .auto-login-message {
            text-align: center;
            padding: 50px 30px;
        }
        .spinner {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #667eea;
            border-radius: 50%;
            width: 60px;
            height: 60px;
            animation: spin 1s linear infinite;
            margin: 0 auto 30px;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        .auto-login-message h3 {
            color: #333;
            font-size: 20px;
            margin: 0 0 10px 0;
        }
        .auto-login-message p {
            color: #666;
            margin: 0;
        }
        .error-box {
            background: #fee;
            border: 2px solid #c33;
            color: #c33;
            padding: 30px;
            margin: 30px;
            border-radius: 8px;
            text-align: center;
        }
        .error-box h3 {
            margin: 0 0 10px 0;
        }
        .user-badge {
            display: inline-block;
            background: #667eea;
            color: white;
            padding: 8px 20px;
            border-radius: 20px;
            font-size: 14px;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-box">
            <div class="login-header">
                <h1>🏛️ نظام الأباسي الموحد</h1>
                <p>نظام محاسبي متكامل</p>
            </div>
            
            <?php if (!isset($error)): ?>
            <div class="auto-login-message">
                <div class="spinner"></div>
                <h3>جاري تسجيل الدخول...</h3>
                <p>تسجيل الدخول كمستخدم</p>
                <span class="user-badge">👤 root</span>
            </div>
            <?php else: ?>
            <div class="error-box">
                <h3>⚠️ خطأ</h3>
                <p><?php echo htmlspecialchars($error); ?></p>
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <script>
        // إعادة تحميل الصفحة بعد ثانية واحدة إذا لم يتم التوجيه
        setTimeout(function() {
            <?php if (!isset($error)): ?>
            window.location.href = 'dashboard.php';
            <?php endif; ?>
        }, 1500);
    </script>
</body>
</html>
