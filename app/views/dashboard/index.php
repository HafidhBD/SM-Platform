<?php require_once VIEWS_PATH . '/layouts/header.php'; ?>

<!-- Project Selector -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="/" class="form-inline">
            <label class="form-label ml-3"><i class="fas fa-building"></i> الجهة:</label>
            <select name="project" class="form-select" onchange="this.form.submit()">
                <?php foreach ($projects as $p): ?>
                    <option value="<?= $p['id'] ?>" <?= $p['id'] == $current_project_id ? 'selected' : '' ?>>
                        <?= e($p['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>
    </div>
</div>

<?php if (!$current_project_id): ?>
    <div class="empty-state">
        <i class="fas fa-building"></i>
        <h3>لا توجد جهات</h3>
        <p>أضف جهة أو مشروع أولاً لبدء المراقبة</p>
        <a href="/projects/create" class="btn btn-primary"><i class="fas fa-plus"></i> إضافة جهة</a>
    </div>
<?php else: ?>

<!-- Stats Cards -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon stat-blue"><i class="fas fa-file-alt"></i></div>
        <div class="stat-info">
            <span class="stat-value"><?= formatNumber($stats['total_posts']) ?></span>
            <span class="stat-label">إجمالي المنشورات</span>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon stat-red"><i class="fas fa-thumbs-down"></i></div>
        <div class="stat-info">
            <span class="stat-value"><?= formatNumber($stats['negative_count']) ?></span>
            <span class="stat-label">منشورات سلبية</span>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon stat-green"><i class="fas fa-thumbs-up"></i></div>
        <div class="stat-info">
            <span class="stat-value"><?= formatNumber($stats['positive_count']) ?></span>
            <span class="stat-label">منشورات إيجابية</span>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon stat-orange"><i class="fas fa-percentage"></i></div>
        <div class="stat-info">
            <span class="stat-value"><?= $stats['negative_percent'] ?>%</span>
            <span class="stat-label">نسبة السلبية</span>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon stat-danger"><i class="fas fa-bell"></i></div>
        <div class="stat-info">
            <span class="stat-value"><?= $stats['alerts_count'] ?></span>
            <span class="stat-label">تنبيهات</span>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon stat-purple"><i class="fas fa-clock"></i></div>
        <div class="stat-info">
            <span class="stat-value"><?= $stats['last_collection'] ? timeAgo($stats['last_collection']['created_at']) : '—' ?></span>
            <span class="stat-label">آخر جلب</span>
        </div>
    </div>
</div>

<!-- Charts Row -->
<div class="charts-grid">
    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-chart-area"></i> المنشورات عبر الزمن</h3>
        </div>
        <div class="card-body">
            <canvas id="timelineChart" height="250"></canvas>
        </div>
    </div>
    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-smile"></i> توزيع المشاعر</h3>
        </div>
        <div class="card-body">
            <canvas id="sentimentChart" height="250"></canvas>
        </div>
    </div>
</div>

<!-- Bottom Row -->
<div class="dashboard-bottom">
    <!-- Top Topics -->
    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-tags"></i> أكثر المواضيع تداولًا</h3>
        </div>
        <div class="card-body">
            <?php if (empty($top_topics)): ?>
                <p class="text-muted">لا توجد بيانات بعد</p>
            <?php else: ?>
                <div class="topic-list">
                    <?php foreach ($top_topics as $i => $topic): ?>
                        <div class="topic-item">
                            <span class="topic-rank"><?= $i + 1 ?></span>
                            <span class="topic-name"><?= topicLabel($topic['topic_label']) ?></span>
                            <span class="topic-count"><?= $topic['count'] ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Top Words -->
    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-font"></i> أكثر الكلمات تكرارًا</h3>
        </div>
        <div class="card-body">
            <?php if (empty($top_words)): ?>
                <p class="text-muted">لا توجد بيانات بعد</p>
            <?php else: ?>
                <div class="word-cloud">
                    <?php foreach ($top_words as $word => $count): ?>
                        <span class="word-tag" style="font-size: <?= min(14 + $count * 2, 32) ?>px"><?= e($word) ?></span>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Top Authors -->
    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-users"></i> أكثر الحسابات تأثيرًا</h3>
        </div>
        <div class="card-body">
            <?php if (empty($top_authors)): ?>
                <p class="text-muted">لا توجد بيانات بعد</p>
            <?php else: ?>
                <div class="author-list">
                    <?php foreach ($top_authors as $author): ?>
                        <div class="author-item">
                            <div class="author-info">
                                <span class="author-name"><?= e($author['author_name'] ?? $author['author_username']) ?></span>
                                <span class="author-handle">@<?= e($author['author_username']) ?></span>
                            </div>
                            <div class="author-stats">
                                <span title="إعجابات"><i class="fas fa-heart"></i> <?= formatNumber($author['total_likes']) ?></span>
                                <span title="منشورات"><?= $author['post_count'] ?></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Most Engaged Posts -->
<div class="card mt-4">
    <div class="card-header">
        <h3><i class="fas fa-fire"></i> أكثر المنشورات تفاعلًا</h3>
    </div>
    <div class="card-body">
        <?php if (empty($most_engaged)): ?>
            <p class="text-muted">لا توجد بيانات بعد</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>المنشور</th>
                            <th>الحساب</th>
                            <th>المشاعر</th>
                            <th>❤️</th>
                            <th>💬</th>
                            <th>🔁</th>
                            <th>التاريخ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($most_engaged as $post): ?>
                            <tr>
                                <td class="post-text-cell"><?= truncate(e($post['content_text']), 80) ?></td>
                                <td>@<?= e($post['author_username'] ?? '') ?></td>
                                <td>
                                    <?php if (isset($post['sentiment'])): ?>
                                        <span class="badge <?= sentimentClass($post['sentiment']) ?>"><?= sentimentLabel($post['sentiment']) ?></span>
                                    <?php else: ?>
                                        <span class="badge badge-neutral">غير محلل</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= formatNumber($post['likes_count']) ?></td>
                                <td><?= formatNumber($post['replies_count']) ?></td>
                                <td><?= formatNumber($post['reposts_count']) ?></td>
                                <td><?= formatDate($post['posted_at'], 'm/d H:i') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Unread Alerts -->
<?php if (!empty($unread_alerts)): ?>
<div class="card mt-4">
    <div class="card-header">
        <h3><i class="fas fa-bell text-danger"></i> تنبيهات غير مقروءة</h3>
    </div>
    <div class="card-body">
        <div class="alerts-list">
            <?php foreach (array_slice($unread_alerts, 0, 5) as $alert): ?>
                <div class="alert-item alert-<?= $alert['severity'] ?>">
                    <div class="alert-content">
                        <span class="alert-severity"><?= riskLabel($alert['severity']) ?></span>
                        <strong><?= e($alert['title']) ?></strong>
                        <p><?= e($alert['description']) ?></p>
                    </div>
                    <span class="alert-time"><?= timeAgo($alert['created_at']) ?></span>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?php endif; ?>

<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    <?php if (!empty($timeline)): ?>
    // Timeline Chart
    const timelineCtx = document.getElementById('timelineChart');
    if (timelineCtx) {
        new Chart(timelineCtx, {
            type: 'line',
            data: {
                labels: <?= json_encode(array_column($timeline, 'period')) ?>,
                datasets: [{
                    label: 'المنشورات',
                    data: <?= json_encode(array_column($timeline, 'count')) ?>,
                    borderColor: '#4f46e5',
                    backgroundColor: 'rgba(79, 70, 229, 0.1)',
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, ticks: { stepSize: 1 } },
                    x: { ticks: { maxTicksLimit: 10 } }
                }
            }
        });
    }
    <?php endif; ?>

    <?php if (!empty($sentiment_timeline)): ?>
    // Sentiment Chart
    const sentimentCtx = document.getElementById('sentimentChart');
    if (sentimentCtx) {
        const periods = [...new Set(<?= json_encode(array_column($sentiment_timeline, 'period')) ?>)];
        const posData = periods.map(p => {
            const item = <?= json_encode($sentiment_timeline) ?>.find(s => s.period === p && s.sentiment === 'positive');
            return item ? item.count : 0;
        });
        const negData = periods.map(p => {
            const item = <?= json_encode($sentiment_timeline) ?>.find(s => s.period === p && s.sentiment === 'negative');
            return item ? item.count : 0;
        });
        const neuData = periods.map(p => {
            const item = <?= json_encode($sentiment_timeline) ?>.find(s => s.period === p && s.sentiment === 'neutral');
            return item ? item.count : 0;
        });

        new Chart(sentimentCtx, {
            type: 'bar',
            data: {
                labels: periods,
                datasets: [
                    { label: 'إيجابي', data: posData, backgroundColor: '#10b981' },
                    { label: 'سلبي', data: negData, backgroundColor: '#ef4444' },
                    { label: 'محايد', data: neuData, backgroundColor: '#6b7280' }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'top' } },
                scales: {
                    x: { stacked: true, ticks: { maxTicksLimit: 10 } },
                    y: { stacked: true, beginAtZero: true }
                }
            }
        });
    }
    <?php endif; ?>
});
</script>

<?php require_once VIEWS_PATH . '/layouts/footer.php'; ?>
