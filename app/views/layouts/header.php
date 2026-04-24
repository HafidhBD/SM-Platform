<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($page_title ?? 'لوحة التحكم') ?> - <?= e(getSetting('system_name', APP_NAME)) ?></title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.min.css">
</head>
<body class="admin-layout">
    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <div class="logo">
                <i class="fas fa-chart-line"></i>
                <span class="logo-text"><?= e(getSetting('system_name', APP_NAME)) ?></span>
            </div>
        </div>
        <nav class="sidebar-nav">
            <a href="/" class="nav-item <?= ($page_title ?? '') === 'لوحة التحكم' ? 'active' : '' ?>">
                <i class="fas fa-tachometer-alt"></i>
                <span>لوحة التحكم</span>
            </a>
            <a href="/projects" class="nav-item <?= strpos($_SERVER['REQUEST_URI'], '/projects') !== false ? 'active' : '' ?>">
                <i class="fas fa-building"></i>
                <span>الجهات والمشاريع</span>
            </a>
            <a href="/collection" class="nav-item <?= strpos($_SERVER['REQUEST_URI'], '/collection') !== false ? 'active' : '' ?>">
                <i class="fas fa-download"></i>
                <span>جلب البيانات</span>
            </a>
            <a href="/posts" class="nav-item <?= strpos($_SERVER['REQUEST_URI'], '/posts') !== false ? 'active' : '' ?>">
                <i class="fas fa-file-alt"></i>
                <span>استكشاف المنشورات</span>
            </a>
            <a href="/analysis" class="nav-item <?= strpos($_SERVER['REQUEST_URI'], '/analysis') !== false ? 'active' : '' ?>">
                <i class="fas fa-brain"></i>
                <span>تحليل الذكاء الاصطناعي</span>
            </a>
            <a href="/alerts" class="nav-item <?= strpos($_SERVER['REQUEST_URI'], '/alerts') !== false ? 'active' : '' ?>">
                <i class="fas fa-bell"></i>
                <span>التنبيهات</span>
                <?php
                try {
                    $unreadCnt = Database::getInstance()->queryOne("SELECT COUNT(*) as cnt FROM alerts WHERE is_resolved = 0");
                    $totalAlerts = (int)($unreadCnt['cnt'] ?? 0);
                    if ($totalAlerts > 0): ?>
                        <span class="badge badge-danger"><?= $totalAlerts ?></span>
                    <?php endif;
                } catch (Exception $ex) { /* ignore if DB not ready */ }
                ?>
            </a>
            <a href="/reports" class="nav-item <?= strpos($_SERVER['REQUEST_URI'], '/reports') !== false ? 'active' : '' ?>">
                <i class="fas fa-chart-bar"></i>
                <span>التقارير</span>
            </a>
            <a href="/settings" class="nav-item <?= strpos($_SERVER['REQUEST_URI'], '/settings') !== false ? 'active' : '' ?>">
                <i class="fas fa-cog"></i>
                <span>الإعدادات</span>
            </a>
        </nav>
        <div class="sidebar-footer">
            <a href="/health" class="nav-item">
                <i class="fas fa-heartbeat"></i>
                <span>فحص النظام</span>
            </a>
        </div>
    </aside>

    <!-- Main Content -->
    <div class="main-wrapper" id="mainWrapper">
        <!-- Topbar -->
        <header class="topbar">
            <div class="topbar-right">
                <button class="sidebar-toggle" id="sidebarToggle">
                    <i class="fas fa-bars"></i>
                </button>
                <h1 class="page-title"><?= e($page_title ?? '') ?></h1>
            </div>
            <div class="topbar-left">
                <div class="user-menu">
                    <span class="user-name">
                        <i class="fas fa-user-circle"></i>
                        <?= e($_SESSION['display_name'] ?? 'مدير') ?>
                    </span>
                    <a href="/logout" class="btn-logout" title="تسجيل الخروج">
                        <i class="fas fa-sign-out-alt"></i>
                    </a>
                </div>
            </div>
        </header>

        <!-- Flash Messages -->
        <?php if ($msg = flash('success')): ?>
            <div class="toast toast-success" id="toastSuccess">
                <i class="fas fa-check-circle"></i>
                <span><?= e($msg) ?></span>
                <button class="toast-close" onclick="this.parentElement.remove()"><i class="fas fa-times"></i></button>
            </div>
        <?php endif; ?>
        <?php if ($msg = flash('error')): ?>
            <div class="toast toast-error" id="toastError">
                <i class="fas fa-exclamation-circle"></i>
                <span><?= e($msg) ?></span>
                <button class="toast-close" onclick="this.parentElement.remove()"><i class="fas fa-times"></i></button>
            </div>
        <?php endif; ?>

        <!-- Page Content -->
        <main class="content">
