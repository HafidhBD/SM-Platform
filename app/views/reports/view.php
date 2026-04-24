<?php require_once VIEWS_PATH . '/layouts/header.php'; ?>

<div class="page-header">
    <h2><i class="fas fa-chart-bar"></i> تقرير تحليلي</h2>
    <a href="/reports" class="btn btn-outline"><i class="fas fa-arrow-right"></i> رجوع</a>
</div>

<div class="report-container">
    <div class="report-header">
        <h1><?= e(getSetting('system_name', APP_NAME)) ?></h1>
        <h2>تقرير تحليلي - <?= e($summary['period_start'] ?? '') ?> إلى <?= e($summary['period_end'] ?? '') ?></h2>
        <p>تاريخ الإنشاء: <?= formatDate($summary['created_at']) ?> | المنشورات المحللة: <?= $summary['posts_analyzed'] ?></p>
    </div>

    <?php if ($summary['reputation_status']): ?>
        <div class="report-status-bar status-<?= $summary['reputation_status'] ?>">
            حالة السمعة: <strong><?= $summary['reputation_status'] ?></strong>
        </div>
    <?php endif; ?>

    <?php if ($summary['executive_summary']): ?>
        <div class="report-section">
            <h3>الملخص التنفيذي</h3>
            <div class="report-text"><?= nl2br(e($summary['executive_summary'])) ?></div>
        </div>
    <?php endif; ?>

    <?php $positive = safeJsonDecode($summary['top_positive_points'], []); ?>
    <?php if (!empty($positive)): ?>
        <div class="report-section">
            <h3>النقاط الإيجابية</h3>
            <ul>
                <?php foreach ($positive as $point): ?>
                    <li><?= e(is_array($point) ? ($point['point'] ?? json_encode($point, JSON_UNESCAPED_UNICODE)) : $point) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php $negative = safeJsonDecode($summary['top_negative_points'], []); ?>
    <?php if (!empty($negative)): ?>
        <div class="report-section">
            <h3>النقاط السلبية</h3>
            <ul>
                <?php foreach ($negative as $point): ?>
                    <li><?= e(is_array($point) ? ($point['point'] ?? json_encode($point, JSON_UNESCAPED_UNICODE)) : $point) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php $recs = safeJsonDecode($summary['recommendations'], []); ?>
    <?php if (!empty($recs)): ?>
        <div class="report-section">
            <h3>التوصيات</h3>
            <ul>
                <?php foreach ($recs as $rec): ?>
                    <li>
                        <?= e(is_array($rec) ? ($rec['recommendation'] ?? json_encode($rec, JSON_UNESCAPED_UNICODE)) : $rec) ?>
                        <?php if (is_array($rec) && isset($rec['priority'])): ?>
                            [<?= $rec['priority'] ?>]
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php $campaigns = safeJsonDecode($summary['campaign_opportunities'], []); ?>
    <?php if (!empty($campaigns)): ?>
        <div class="report-section">
            <h3>فرص الحملات</h3>
            <ul>
                <?php foreach ($campaigns as $camp): ?>
                    <li><?= e(is_array($camp) ? ($camp['opportunity'] ?? json_encode($camp, JSON_UNESCAPED_UNICODE)) : $camp) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php $interests = safeJsonDecode($summary['audience_interests'], []); ?>
    <?php if (!empty($interests)): ?>
        <div class="report-section">
            <h3>اهتمامات الجمهور</h3>
            <p><?= e(implode(' • ', array_map(fn($i) => is_array($i) ? ($i['interest'] ?? '') : $i, $interests))) ?></p>
        </div>
    <?php endif; ?>

    <?php $messages = safeJsonDecode($summary['repeated_messages'], []); ?>
    <?php if (!empty($messages)): ?>
        <div class="report-section">
            <h3>الرسائل المتكررة</h3>
            <ul>
                <?php foreach ($messages as $msg): ?>
                    <li><?= e(is_array($msg) ? ($msg['message'] ?? json_encode($msg, JSON_UNESCAPED_UNICODE)) : $msg) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php $gaps = safeJsonDecode($summary['market_gaps'], []); ?>
    <?php if (!empty($gaps)): ?>
        <div class="report-section">
            <h3>الفجوات السوقية</h3>
            <ul>
                <?php foreach ($gaps as $gap): ?>
                    <li><?= e(is_array($gap) ? ($gap['gap'] ?? json_encode($gap, JSON_UNESCAPED_UNICODE)) : $gap) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
</div>

<div class="form-actions mt-4">
    <button onclick="window.print()" class="btn btn-primary">
        <i class="fas fa-print"></i> طباعة
    </button>
    <a href="/reports" class="btn btn-outline">رجوع للتقارير</a>
</div>

<?php require_once VIEWS_PATH . '/layouts/footer.php'; ?>
