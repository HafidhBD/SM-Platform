<?php require_once VIEWS_PATH . '/layouts/header.php'; ?>

<div class="page-header">
    <h2><i class="fas fa-file-contract"></i> الملخص التنفيذي</h2>
    <a href="/analysis" class="btn btn-outline"><i class="fas fa-arrow-right"></i> رجوع</a>
</div>

<div class="card mb-4">
    <div class="card-header">
        <h3>ملخص <?= e($summary['period_start'] ?? '') ?> - <?= e($summary['period_end'] ?? '') ?></h3>
        <div>
            <?php
            $typeLabels = ['daily' => 'يومي', 'weekly' => 'أسبوعي', 'manual' => 'يدوي', 'crisis' => 'أزمة'];
            ?>
            <span class="badge badge-info"><?= $typeLabels[$summary['summary_type']] ?? $summary['summary_type'] ?></span>
            <?php if ($summary['reputation_status']): ?>
                <span class="badge badge-<?= in_array($summary['reputation_status'], ['critical', 'concerning']) ? 'negative' : (in_array($summary['reputation_status'], ['excellent', 'good']) ? 'positive' : 'neutral') ?>">
                    <?= $summary['reputation_status'] ?>
                </span>
            <?php endif; ?>
        </div>
    </div>
    <div class="card-body">
        <!-- Executive Summary -->
        <?php if ($summary['executive_summary']): ?>
            <div class="summary-section">
                <h4><i class="fas fa-clipboard"></i> الملخص التنفيذي</h4>
                <div class="summary-text"><?= nl2br(e($summary['executive_summary'])) ?></div>
            </div>
        <?php endif; ?>

        <!-- Positive Points -->
        <?php $positive = safeJsonDecode($summary['top_positive_points'], []); ?>
        <?php if (!empty($positive)): ?>
            <div class="summary-section">
                <h4 class="text-green"><i class="fas fa-thumbs-up"></i> أبرز النقاط الإيجابية</h4>
                <ul class="summary-list list-positive">
                    <?php foreach ($positive as $point): ?>
                        <li>
                            <?= e(is_array($point) ? ($point['point'] ?? $point['impact'] ?? json_encode($point, JSON_UNESCAPED_UNICODE)) : $point) ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <!-- Negative Points -->
        <?php $negative = safeJsonDecode($summary['top_negative_points'], []); ?>
        <?php if (!empty($negative)): ?>
            <div class="summary-section">
                <h4 class="text-red"><i class="fas fa-thumbs-down"></i> أبرز النقاط السلبية</h4>
                <ul class="summary-list list-negative">
                    <?php foreach ($negative as $point): ?>
                        <li>
                            <?= e(is_array($point) ? ($point['point'] ?? $point['severity'] ?? json_encode($point, JSON_UNESCAPED_UNICODE)) : $point) ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <!-- Recommendations -->
        <?php $recs = safeJsonDecode($summary['recommendations'], []); ?>
        <?php if (!empty($recs)): ?>
            <div class="summary-section">
                <h4><i class="fas fa-lightbulb"></i> التوصيات</h4>
                <ul class="summary-list">
                    <?php foreach ($recs as $rec): ?>
                        <li>
                            <strong><?= e(is_array($rec) ? ($rec['recommendation'] ?? '') : $rec) ?></strong>
                            <?php if (is_array($rec) && isset($rec['priority'])): ?>
                                <span class="badge badge-<?= $rec['priority'] === 'high' ? 'negative' : 'neutral' ?>"><?= $rec['priority'] ?></span>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <!-- Campaign Opportunities -->
        <?php $campaigns = safeJsonDecode($summary['campaign_opportunities'], []); ?>
        <?php if (!empty($campaigns)): ?>
            <div class="summary-section">
                <h4><i class="fas fa-bullhorn"></i> فرص الحملات</h4>
                <ul class="summary-list">
                    <?php foreach ($campaigns as $camp): ?>
                        <li><?= e(is_array($camp) ? ($camp['opportunity'] ?? json_encode($camp, JSON_UNESCAPED_UNICODE)) : $camp) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <!-- Audience Interests -->
        <?php $interests = safeJsonDecode($summary['audience_interests'], []); ?>
        <?php if (!empty($interests)): ?>
            <div class="summary-section">
                <h4><i class="fas fa-users"></i> اهتمامات الجمهور</h4>
                <div class="tag-list">
                    <?php foreach ($interests as $int): ?>
                        <span class="tag"><?= e(is_array($int) ? ($int['interest'] ?? json_encode($int, JSON_UNESCAPED_UNICODE)) : $int) ?></span>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Repeated Messages -->
        <?php $messages = safeJsonDecode($summary['repeated_messages'], []); ?>
        <?php if (!empty($messages)): ?>
            <div class="summary-section">
                <h4><i class="fas fa-redo"></i> الرسائل المتكررة</h4>
                <ul class="summary-list">
                    <?php foreach ($messages as $msg): ?>
                        <li><?= e(is_array($msg) ? ($msg['message'] ?? json_encode($msg, JSON_UNESCAPED_UNICODE)) : $msg) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <!-- Market Gaps -->
        <?php $gaps = safeJsonDecode($summary['market_gaps'], []); ?>
        <?php if (!empty($gaps)): ?>
            <div class="summary-section">
                <h4><i class="fas fa-search"></i> الفجوات السوقية</h4>
                <ul class="summary-list">
                    <?php foreach ($gaps as $gap): ?>
                        <li><?= e(is_array($gap) ? ($gap['gap'] ?? json_encode($gap, JSON_UNESCAPED_UNICODE)) : $gap) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="form-actions">
    <a href="/reports/export/<?= $summary['id'] ?>" class="btn btn-outline" target="_blank">
        <i class="fas fa-print"></i> طباعة / تصدير HTML
    </a>
</div>

<?php require_once VIEWS_PATH . '/layouts/footer.php'; ?>
