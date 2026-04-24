<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسجيل الدخول - <?= e(getSetting('system_name', APP_NAME)) ?></title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="login-page">
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <div class="login-logo">
                    <i class="fas fa-chart-line"></i>
                </div>
                <h1><?= e(getSetting('system_name', APP_NAME)) ?></h1>
                <p>منصة الاستخبارات التسويقية</p>
            </div>
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle"></i>
                    <?= e($error) ?>
                </div>
            <?php endif; ?>
            <form method="POST" action="/login" class="login-form">
                <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">
                <div class="form-group">
                    <label for="username">
                        <i class="fas fa-user"></i>
                        اسم المستخدم
                    </label>
                    <input type="text" id="username" name="username" 
                           placeholder="أدخل اسم المستخدم" required autofocus
                           autocomplete="username">
                </div>
                <div class="form-group">
                    <label for="password">
                        <i class="fas fa-lock"></i>
                        كلمة المرور
                    </label>
                    <input type="password" id="password" name="password" 
                           placeholder="أدخل كلمة المرور" required
                           autocomplete="current-password">
                </div>
                <button type="submit" class="btn btn-primary btn-block">
                    <i class="fas fa-sign-in-alt"></i>
                    تسجيل الدخول
                </button>
            </form>
        </div>
        <div class="login-footer">
            <p>&copy; <?= date('Y') ?> <?= e(getSetting('system_name', APP_NAME)) ?></p>
        </div>
    </div>
</body>
</html>
