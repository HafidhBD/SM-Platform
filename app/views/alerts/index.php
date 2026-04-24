<?php require_once VIEWS_PATH . '/layouts/header.php'; ?>

<div class="page-header">
    <h2><i class="fas fa-bell"></i> التنبيهات</h2>
    <div class="page-header-actions">
        <form method="POST" action="/alerts/mark-all-read" style="display:inline">
            <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">
            <button type="submit" class="btn btn-sm btn-outline"><i class="fas fa-check-double"></i> تحديد الكل كمقروء</button>
        </form>
    </div>
</div>

<!-- Severity Summary -->
<div class="stats-grid mb-4">
    <div class="stat-card clickable" onclick="filterSeverity('critical')">
        <div class="stat-icon stat-danger"><i class="fas fa-exclamation-circle"></i></div>
        <div class="stat-info">
            <span class="stat-value"><?= $severity_counts['critical'] ?></span>
            <span class="stat-label">حرج</span>
        </div>
    </div>
    <div class="stat-card clickable" onclick="filterSeverity('high')">
        <div class="stat-icon stat-red"><i class="fas fa-exclamation-triangle"></i></div>
        <div class="stat-info">
            <span class="stat-value"><?= $severity_counts['high'] ?></span>
            <span class="stat-label">مرتفع</span>
        </div>
    </div>
    <div class="stat-card clickable" onclick="filterSeverity('medium')">
        <div class="stat-icon stat-orange"><i class="fas fa-exclamation"></i></div>
        <div class="stat-info">
            <span class="stat-value"><?= $severity_counts['medium'] ?></span>
            <span class="stat-label">متوسط</span>
        </div>
    </div>
    <div class="stat-card clickable" onclick="filterSeverity('low')">
        <div class="stat-icon stat-blue"><i class="fas fa-info-circle"></i></div>
        <div class="stat-info">
            <span class="stat-value"><?= $severity_counts['low'] ?></span>
            <span class="stat-label">منخفض</span>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="/alerts" class="form-inline">
            <label class="form-label">الجهة:</label>
            <select name="project" class="form-select" onchange="this.form.submit()">
                <option value="">الكل</option>
                <?php foreach ($projects as $p): ?>
                    <option value="<?= $p['id'] ?>" <?= $p['id'] == $current_project_id ? 'selected' : '' ?>>
                        <?= e($p['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <label class="form-label mr-3">الخطورة:</label>
            <select name="severity" class="form-select" onchange="this.form.submit()">
                <option value="">الكل</option>
                <option value="critical" <?= $current_severity === 'critical' ? 'selected' : '' ?>>حرج</option>
                <option value="high" <?= $current_severity === 'high' ? 'selected' : '' ?>>مرتفع</option>
                <option value="medium" <?= $current_severity === 'medium' ? 'selected' : '' ?>>متوسط</option>
                <option value="low" <?= $current_severity === 'low' ? 'selected' : '' ?>>منخفض</option>
            </select>
            <label class="mr-3">
                <input type="checkbox" name="resolved" value="1" <?= $show_resolved ? 'checked' : '' ?> onchange="this.form.submit()">
                عرض المحلولة
            </label>
        </form>
    </div>
</div>

<!-- Alerts List -->
<div class="card">
    <div class="card-body">
        <?php if (empty($alerts)): ?>
            <div class="empty-state-sm">
                <i class="fas fa-check-circle text-green"></i>
                <p>لا توجد تنبيهات حاليًا</p>
            </div>
        <?php else: ?>
            <div class="alerts-list">
                <?php foreach ($alerts as $alert): ?>
                    <div class="alert-item alert-<?= $alert['severity'] ?> <?= $alert['is_read'] ? 'read' : '' ?> <?= $alert['is_resolved'] ? 'resolved' : '' ?>">
                        <div class="alert-severity-badge">
                            <span class="severity-dot severity-<?= $alert['severity'] ?>"></span>
                            <?= riskLabel($alert['severity']) ?>
                        </div>
                        <div class="alert-content">
                            <strong><?= e($alert['title']) ?></strong>
                            <p><?= e($alert['description']) ?></p>
                            <div class="alert-meta">
                                <span><i class="fas fa-tag"></i> <?= $alert['alert_type'] ?></span>
                                <span><i class="fas fa-clock"></i> <?= timeAgo($alert['created_at']) ?></span>
                            </div>
                        </div>
                        <div class="alert-actions">
                            <?php if (!$alert['is_read']): ?>
                                <button class="btn btn-sm btn-outline" onclick="markRead(<?= $alert['id'] ?>)">
                                    <i class="fas fa-check"></i>
                                </button>
                            <?php endif; ?>
                            <?php if (!$alert['is_resolved']): ?>
                                <form method="POST" action="/alerts/resolve/<?= $alert['id'] ?>" style="display:inline">
                                    <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">
                                    <button type="submit" class="btn btn-sm btn-primary">
                                        <i class="fas fa-check-double"></i> حل
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
function filterSeverity(severity) {
    const url = new URL(window.location);
    url.searchParams.set('severity', severity);
    window.location = url.toString();
}

function markRead(alertId) {
    fetch('/alerts/mark-read/' + alertId, { method: 'POST' })
        .then(() => location.reload());
}
</script>

<?php require_once VIEWS_PATH . '/layouts/footer.php'; ?>
