<?php require_once VIEWS_PATH . '/layouts/header.php'; ?>

<div class="page-header">
    <h2><i class="fas fa-brain"></i> تحليل الذكاء الاصطناعي</h2>
</div>

<!-- Analysis Stats -->
<div class="stats-grid mb-4">
    <div class="stat-card">
        <div class="stat-icon stat-blue"><i class="fas fa-file-alt"></i></div>
        <div class="stat-info">
            <span class="stat-value"><?= $analysis_stats['total_posts'] ?></span>
            <span class="stat-label">إجمالي المنشورات</span>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon stat-green"><i class="fas fa-check-circle"></i></div>
        <div class="stat-info">
            <span class="stat-value"><?= $analysis_stats['analyzed_posts'] ?></span>
            <span class="stat-label">تم تحليلها</span>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon stat-orange"><i class="fas fa-hourglass-half"></i></div>
        <div class="stat-info">
            <span class="stat-value"><?= $analysis_stats['unanalyzed_posts'] ?></span>
            <span class="stat-label">بانتظار التحليل</span>
        </div>
    </div>
</div>

<!-- Run Analysis -->
<div class="card mb-4">
    <div class="card-header"><h3><i class="fas fa-play-circle"></i> تشغيل التحليل</h3></div>
    <div class="card-body">
        <form method="POST" action="/analysis/run">
            <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">
            <div class="form-row">
                <div class="form-group col-md-4">
                    <label for="project_id">الجهة *</label>
                    <select id="project_id" name="project_id" class="form-select" required>
                        <option value="">اختر الجهة</option>
                        <?php foreach ($projects as $p): ?>
                            <option value="<?= $p['id'] ?>" <?= $p['id'] == $current_project_id ? 'selected' : '' ?>>
                                <?= e($p['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group col-md-4">
                    <label>&nbsp;</label>
                    <label class="checkbox-label d-block">
                        <input type="checkbox" name="force_reanalyze" value="1">
                        إعادة تحليل جميع المنشورات (حتى المحللة)
                    </label>
                </div>
                <div class="form-group col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary btn-block">
                        <i class="fas fa-brain"></i> تشغيل التحليل
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Generate Summary -->
<div class="card mb-4">
    <div class="card-header"><h3><i class="fas fa-file-contract"></i> إنشاء ملخص تنفيذي</h3></div>
    <div class="card-body">
        <form method="POST" action="/analysis/summary">
            <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">
            <div class="form-row">
                <div class="form-group col-md-3">
                    <label for="project_id_sum">الجهة *</label>
                    <select id="project_id_sum" name="project_id" class="form-select" required>
                        <option value="">اختر الجهة</option>
                        <?php foreach ($projects as $p): ?>
                            <option value="<?= $p['id'] ?>" <?= $p['id'] == $current_project_id ? 'selected' : '' ?>>
                                <?= e($p['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group col-md-2">
                    <label>نوع الملخص</label>
                    <select name="summary_type" class="form-select">
                        <option value="manual">يدوي</option>
                        <option value="daily">يومي</option>
                        <option value="weekly">أسبوعي</option>
                        <option value="crisis">أزمة</option>
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
                        <i class="fas fa-magic"></i> إنشاء الملخص
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Crisis Detection -->
<div class="card mb-4">
    <div class="card-header"><h3><i class="fas fa-exclamation-triangle text-danger"></i> فحص الأزمة</h3></div>
    <div class="card-body">
        <form method="POST" action="/analysis/crisis-check">
            <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">
            <div class="form-row">
                <div class="form-group col-md-4">
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
                <div class="form-group col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-shield-alt"></i> فحص الأزمة الآن
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Saved Summaries -->
<div class="card">
    <div class="card-header"><h3><i class="fas fa-list"></i> الملخصات السابقة</h3></div>
    <div class="card-body">
        <?php 
        $summaryModel = new SummaryModel();
        $summaries = $current_project_id ? $summaryModel->getByProject($current_project_id, 10) : [];
        ?>
        <?php if (empty($summaries)): ?>
            <p class="text-muted">لا توجد ملخصات سابقة</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>النوع</th>
                            <th>الفترة</th>
                            <th>حالة السمعة</th>
                            <th>المنشورات</th>
                            <th>التاريخ</th>
                            <th>إجراء</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($summaries as $s): ?>
                            <tr>
                                <td>
                                    <?php
                                    $typeLabels = ['daily' => 'يومي', 'weekly' => 'أسبوعي', 'manual' => 'يدوي', 'crisis' => 'أزمة'];
                                    echo $typeLabels[$s['summary_type']] ?? $s['summary_type'];
                                    ?>
                                </td>
                                <td><?= $s['period_start'] ?? '—' ?> ~ <?= $s['period_end'] ?? '—' ?></td>
                                <td>
                                    <?php if ($s['reputation_status']): ?>
                                        <span class="badge badge-<?= $s['reputation_status'] === 'critical' || $s['reputation_status'] === 'concerning' ? 'negative' : ($s['reputation_status'] === 'excellent' || $s['reputation_status'] === 'good' ? 'positive' : 'neutral') ?>">
                                            <?= $s['reputation_status'] ?>
                                        </span>
                                    <?php else: ?>—<?php endif; ?>
                                </td>
                                <td><?= $s['posts_analyzed'] ?></td>
                                <td><?= formatDate($s['created_at']) ?></td>
                                <td>
                                    <a href="/analysis/summary/<?= $s['id'] ?>" class="btn btn-sm btn-outline">
                                        <i class="fas fa-eye"></i> عرض
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
