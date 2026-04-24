<?php require_once VIEWS_PATH . '/layouts/header.php'; ?>

<div class="page-header">
    <h2><i class="fas fa-chart-bar"></i> التقارير</h2>
</div>

<!-- Generate Report -->
<div class="card mb-4">
    <div class="card-header"><h3><i class="fas fa-plus-circle"></i> إنشاء تقرير جديد</h3></div>
    <div class="card-body">
        <form method="POST" action="/reports/generate">
            <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">
            <div class="form-row">
                <div class="form-group col-md-3">
                    <label>الجهة *</label>
                    <select name="project_id" class="form-select" required>
                        <option value="">اختر الجهة</option>
                        <?php foreach ($projects as $p): ?>
                            <option value="<?= $p['id'] ?>" <?= $p['id'] == $current_project_id ? 'selected' : '' ?>>
                                <?= e($p['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group col-md-2">
                    <label>نوع التقرير</label>
                    <select name="report_type" class="form-select">
                        <option value="manual">مخصص</option>
                        <option value="daily">يومي</option>
                        <option value="weekly">أسبوعي</option>
                    </select>
                </div>
                <div class="form-group col-md-2">
                    <label>من تاريخ</label>
                    <input type="date" name="period_start" class="form-control">
                </div>
                <div class="form-group col-md-2">
                    <label>إلى تاريخ</label>
                    <input type="date" name="period_end" class="form-control">
                </div>
                <div class="form-group col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary btn-block">
                        <i class="fas fa-magic"></i> إنشاء التقرير
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Reports List -->
<div class="card">
    <div class="card-header"><h3><i class="fas fa-list"></i> التقارير السابقة</h3></div>
    <div class="card-body">
        <?php if (empty($summaries)): ?>
            <div class="empty-state-sm">
                <p>لا توجد تقارير بعد</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>النوع</th>
                            <th>الفترة</th>
                            <th>حالة السمعة</th>
                            <th>المنشورات المحللة</th>
                            <th>تاريخ الإنشاء</th>
                            <th>إجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($summaries as $s): ?>
                            <tr>
                                <td>
                                    <?php
                                    $typeLabels = ['daily' => 'يومي', 'weekly' => 'أسبوعي', 'manual' => 'مخصص', 'crisis' => 'أزمة'];
                                    echo $typeLabels[$s['summary_type']] ?? $s['summary_type'];
                                    ?>
                                </td>
                                <td><?= $s['period_start'] ?? '—' ?> ~ <?= $s['period_end'] ?? '—' ?></td>
                                <td>
                                    <?php if ($s['reputation_status']): ?>
                                        <span class="badge badge-<?= in_array($s['reputation_status'], ['critical', 'concerning']) ? 'negative' : (in_array($s['reputation_status'], ['excellent', 'good']) ? 'positive' : 'neutral') ?>">
                                            <?= $s['reputation_status'] ?>
                                        </span>
                                    <?php else: ?>—<?php endif; ?>
                                </td>
                                <td><?= $s['posts_analyzed'] ?></td>
                                <td><?= formatDate($s['created_at']) ?></td>
                                <td>
                                    <a href="/reports/view/<?= $s['id'] ?>" class="btn btn-sm btn-outline">
                                        <i class="fas fa-eye"></i> عرض
                                    </a>
                                    <a href="/reports/export/<?= $s['id'] ?>" class="btn btn-sm btn-outline" target="_blank">
                                        <i class="fas fa-print"></i> طباعة
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once VIEWS_PATH . '/layouts/footer.php'; ?>
