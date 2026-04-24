<?php require_once VIEWS_PATH . '/layouts/header.php'; ?>

<div class="page-header">
    <h2><i class="fas fa-heartbeat"></i> فحص النظام</h2>
</div>

<div class="health-grid">
    <!-- Database -->
    <div class="card health-card <?= $results['database']['connected'] ? 'health-ok' : 'health-error' ?>">
        <div class="card-body">
            <div class="health-icon">
                <i class="fas fa-database"></i>
            </div>
            <h3>قاعدة البيانات</h3>
            <span class="health-status"><?= $results['database']['connected'] ? '✓ متصلة' : '✗ غير متصلة' ?></span>
            <p class="health-detail"><?= e($results['database']['message']) ?></p>
        </div>
    </div>

    <!-- Apify -->
    <div class="card health-card <?= $results['apify']['connected'] ? 'health-ok' : 'health-error' ?>">
        <div class="card-body">
            <div class="health-icon">
                <i class="fas fa-robot"></i>
            </div>
            <h3>Apify API</h3>
            <span class="health-status"><?= $results['apify']['connected'] ? '✓ متصل' : '✗ غير متصل' ?></span>
            <p class="health-detail"><?= e($results['apify']['message']) ?></p>
        </div>
    </div>

    <!-- OpenAI -->
    <div class="card health-card <?= $results['openai']['connected'] ? 'health-ok' : 'health-error' ?>">
        <div class="card-body">
            <div class="health-icon">
                <i class="fas fa-brain"></i>
            </div>
            <h3>OpenAI API</h3>
            <span class="health-status"><?= $results['openai']['connected'] ? '✓ متصل' : '✗ غير متصل' ?></span>
            <p class="health-detail"><?= e($results['openai']['message']) ?></p>
        </div>
    </div>

    <!-- PHP -->
    <div class="card health-card health-ok">
        <div class="card-body">
            <div class="health-icon">
                <i class="fas fa-code"></i>
            </div>
            <h3>PHP</h3>
            <span class="health-status">الإصدار <?= $results['php']['version'] ?></span>
            <div class="health-extensions">
                <?php foreach ($results['php']['extensions'] as $ext => $loaded): ?>
                    <span class="ext-badge <?= $loaded ? 'ext-ok' : 'ext-missing' ?>">
                        <?= $loaded ? '✓' : '✗' ?> <?= $ext ?>
                    </span>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<div class="mt-4">
    <a href="/settings" class="btn btn-outline"><i class="fas fa-arrow-right"></i> رجوع للإعدادات</a>
</div>

<?php require_once VIEWS_PATH . '/layouts/footer.php'; ?>
