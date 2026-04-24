<?php require_once VIEWS_PATH . '/layouts/header.php'; ?>

<div class="page-header">
    <h2><i class="fas fa-download"></i> جلب البيانات</h2>
</div>

<!-- Collection Form -->
<div class="card mb-4">
    <div class="card-header"><h3><i class="fas fa-play-circle"></i> تشغيل جلب جديد</h3></div>
    <div class="card-body">
        <form method="POST" action="/collection/start">
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
                <div class="form-group col-md-2">
                    <label for="max_tweets">عدد المنشورات</label>
                    <input type="number" id="max_tweets" name="max_tweets" class="form-control" 
                           value="<?= e(getSetting('apify_max_tweets', 50)) ?>" min="1" max="1000">
                </div>
                <div class="form-group col-md-2">
                    <label for="time_window">النافذة الزمنية (أيام)</label>
                    <input type="number" id="time_window" name="time_window" class="form-control" 
                           value="<?= e(getSetting('apify_time_window', 7)) ?>" min="1" max="30">
                </div>
                <div class="form-group col-md-2">
                    <label for="search_type">نوع البحث</label>
                    <select id="search_type" name="search_type" class="form-select">
                        <option value="latest" <?= getSetting('apify_search_type', 'latest') === 'latest' ? 'selected' : '' ?>>الأحدث</option>
                        <option value="top" <?= getSetting('apify_search_type', 'latest') === 'top' ? 'selected' : '' ?>>الأكثر تفاعلًا</option>
                    </select>
                </div>
                <div class="form-group col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary btn-block">
                        <i class="fas fa-play"></i> تشغيل
                    </button>
                </div>
            </div>
            <div class="form-group">
                <label for="custom_targets">أهداف إضافية (اختياري - كل هدف في سطر)</label>
                <textarea id="custom_targets" name="custom_targets" class="form-control" rows="2"
                          placeholder="كلمة مفتاحية أو @حساب أو #هاشتاق أو رابط - كل واحد في سطر"></textarea>
            </div>
        </form>
    </div>
</div>

<!-- Collection History -->
<div class="card">
    <div class="card-header">
        <h3><i class="fas fa-history"></i> سجل عمليات الجلب</h3>
        <?php if ($current_project_id): ?>
            <form method="GET" class="form-inline">
                <select name="project" class="form-select form-select-sm" onchange="this.form.submit()">
                    <?php foreach ($projects as $p): ?>
                        <option value="<?= $p['id'] ?>" <?= $p['id'] == $current_project_id ? 'selected' : '' ?>>
                            <?= e($p['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </form>
        <?php endif; ?>
    </div>
    <div class="card-body">
        <?php if (empty($runs)): ?>
            <div class="empty-state-sm">
                <p>لا توجد عمليات جلب بعد</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>الحالة</th>
                            <th>الأهداف</th>
                            <th>النتائج</th>
                            <th>المخزنة</th>
                            <th>بدأ في</th>
                            <th>انتهى في</th>
                            <th>إجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($runs as $run): ?>
                            <tr>
                                <td><?= $run['id'] ?></td>
                                <td>
                                    <?php
                                    $statusMap = [
                                        'pending' => '<span class="badge badge-neutral">قيد الانتظار</span>',
                                        'running' => '<span class="badge badge-info">جاري التنفيذ</span>',
                                        'completed' => '<span class="badge badge-positive">مكتمل</span>',
                                        'failed' => '<span class="badge badge-negative">فاشل</span>',
                                        'timeout' => '<span class="badge badge-negative">انتهت المهلة</span>'
                                    ];
                                    echo $statusMap[$run['status']] ?? $run['status'];
                                    ?>
                                </td>
                                <td class="text-sm"><?= truncate($run['targets'] ?? '', 60) ?></td>
                                <td><?= $run['posts_found'] ?></td>
                                <td><?= $run['posts_stored'] ?></td>
                                <td><?= formatDate($run['started_at']) ?></td>
                                <td><?= formatDate($run['completed_at']) ?></td>
                                <td>
                                    <?php if ($run['status'] === 'completed'): ?>
                                        <form method="POST" action="/collection/rerun/<?= $run['id'] ?>" style="display:inline">
                                            <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">
                                            <button type="submit" class="btn btn-sm btn-outline" title="إعادة الجلب">
                                                <i class="fas fa-redo"></i>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                    <?php if ($run['error_message']): ?>
                                        <span class="text-danger text-sm" title="<?= e($run['error_message']) ?>">
                                            <i class="fas fa-exclamation-triangle"></i>
                                        </span>
                                    <?php endif; ?>
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
