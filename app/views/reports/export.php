<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تقرير - <?= e(getSetting('system_name', APP_NAME)) ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Arial, sans-serif; direction: rtl; padding: 40px; color: #1e293b; line-height: 1.8; }
        .report-container { max-width: 900px; margin: 0 auto; }
        .report-header { text-align: center; margin-bottom: 30px; border-bottom: 3px solid #4f46e5; padding-bottom: 20px; }
        .report-header h1 { font-size: 24px; color: #4f46e5; }
        .report-header h2 { font-size: 18px; margin-top: 10px; }
        .report-header p { color: #64748b; margin-top: 5px; }
        .report-status-bar { padding: 10px 20px; border-radius: 8px; margin-bottom: 20px; text-align: center; font-weight: bold; }
        .status-excellent, .status-good { background: #d1fae5; color: #065f46; }
        .status-neutral { background: #e0e7ff; color: #3730a3; }
        .status-concerning { background: #fef3c7; color: #92400e; }
        .status-critical { background: #fee2e2; color: #991b1b; }
        .report-section { margin-bottom: 25px; }
        .report-section h3 { font-size: 16px; color: #1e293b; border-bottom: 1px solid #e2e8f0; padding-bottom: 8px; margin-bottom: 12px; }
        .report-text { background: #f8fafc; padding: 15px; border-radius: 8px; }
        .report-section ul { padding-right: 20px; }
        .report-section li { margin-bottom: 6px; }
        @media print { body { padding: 20px; } }
    </style>
</head>
<body>
    <div class="report-container">
        <div class="report-header">
            <h1><?= e(getSetting('system_name', APP_NAME)) ?></h1>
            <h2>تقرير تحليلي - <?= e($summary['period_start'] ?? '') ?> إلى <?= e($summary['period_end'] ?? '') ?></h2>
            <p>تاريخ الإنشاء: <?= formatDate($summary['created_at']) ?> | المنشورات المحللة: <?= $summary['posts_analyzed'] ?></p>
        </div>

        <?php if ($summary['reputation_status']): ?>
            <div class="report-status-bar status-<?= $summary['reputation_status'] ?>">
                حالة السمعة: <?= $summary['reputation_status'] ?>
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
                <ul><?php foreach ($positive as $point): ?>
                    <li><?= e(is_array($point) ? ($point['point'] ?? json_encode($point, JSON_UNESCAPED_UNICODE)) : $point) ?></li>
                <?php endforeach; ?></ul>
            </div>
        <?php endif; ?>

        <?php $negative = safeJsonDecode($summary['top_negative_points'], []); ?>
        <?php if (!empty($negative)): ?>
            <div class="report-section">
                <h3>النقاط السلبية</h3>
                <ul><?php foreach ($negative as $point): ?>
                    <li><?= e(is_array($point) ? ($point['point'] ?? json_encode($point, JSON_UNESCAPED_UNICODE)) : $point) ?></li>
                <?php endforeach; ?></ul>
            </div>
        <?php endif; ?>

        <?php $recs = safeJsonDecode($summary['recommendations'], []); ?>
        <?php if (!empty($recs)): ?>
            <div class="report-section">
                <h3>التوصيات</h3>
                <ul><?php foreach ($recs as $rec): ?>
                    <li><?= e(is_array($rec) ? ($rec['recommendation'] ?? json_encode($rec, JSON_UNESCAPED_UNICODE)) : $rec) ?></li>
                <?php endforeach; ?></ul>
            </div>
        <?php endif; ?>

        <?php $campaigns = safeJsonDecode($summary['campaign_opportunities'], []); ?>
        <?php if (!empty($campaigns)): ?>
            <div class="report-section">
                <h3>فرص الحملات</h3>
                <ul><?php foreach ($campaigns as $camp): ?>
                    <li><?= e(is_array($camp) ? ($camp['opportunity'] ?? json_encode($camp, JSON_UNESCAPED_UNICODE)) : $camp) ?></li>
                <?php endforeach; ?></ul>
            </div>
        <?php endif; ?>

        <?php $gaps = safeJsonDecode($summary['market_gaps'], []); ?>
        <?php if (!empty($gaps)): ?>
            <div class="report-section">
                <h3>الفجوات السوقية</h3>
                <ul><?php foreach ($gaps as $gap): ?>
                    <li><?= e(is_array($gap) ? ($gap['gap'] ?? json_encode($gap, JSON_UNESCAPED_UNICODE)) : $gap) ?></li>
                <?php endforeach; ?></ul>
            </div>
        <?php endif; ?>
    </div>

    <script>window.onload = function() { window.print(); }</script>
</body>
</html>
