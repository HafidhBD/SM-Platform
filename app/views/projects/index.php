<?php require_once VIEWS_PATH . '/layouts/header.php'; ?>

<div class="page-header">
    <h2><i class="fas fa-building"></i> الجهات والمشاريع</h2>
    <a href="/projects/create" class="btn btn-primary">
        <i class="fas fa-plus"></i> إضافة جهة جديدة
    </a>
</div>

<?php if (empty($projects)): ?>
    <div class="empty-state">
        <i class="fas fa-building"></i>
        <h3>لا توجد جهات بعد</h3>
        <p>أضف جهة أو علامة تجارية لبدء المراقبة</p>
        <a href="/projects/create" class="btn btn-primary"><i class="fas fa-plus"></i> إضافة جهة</a>
    </div>
<?php else: ?>
    <div class="projects-grid">
        <?php foreach ($projects as $project): ?>
            <div class="card project-card">
                <div class="card-body">
                    <div class="project-header">
                        <h3><?= e($project['name']) ?></h3>
                        <span class="badge <?= $project['is_active'] ? 'badge-positive' : 'badge-neutral' ?>">
                            <?= $project['is_active'] ? 'نشط' : 'غير نشط' ?>
                        </span>
                    </div>
                    <?php if ($project['description']): ?>
                        <p class="project-desc"><?= e(truncate($project['description'], 120)) ?></p>
                    <?php endif; ?>
                    <div class="project-meta">
                        <span><i class="fas fa-file-alt"></i> <?= $this->db->count('posts', 'project_id = ? AND deleted_at IS NULL', [$project['id']]) ?> منشور</span>
                        <span><i class="fas fa-bell"></i> <?= $this->db->count('alerts', 'project_id = ? AND is_resolved = 0', [$project['id']]) ?> تنبيه</span>
                    </div>
                    <div class="project-actions">
                        <a href="/?project=<?= $project['id'] ?>" class="btn btn-sm btn-outline">
                            <i class="fas fa-chart-line"></i> لوحة التحكم
                        </a>
                        <a href="/projects/edit/<?= $project['id'] ?>" class="btn btn-sm btn-outline">
                            <i class="fas fa-edit"></i> تعديل
                        </a>
                        <form method="POST" action="/projects/delete/<?= $project['id'] ?>" style="display:inline" onsubmit="return confirm('هل أنت متأكد من حذف هذه الجهة؟')">
                            <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">
                            <button type="submit" class="btn btn-sm btn-danger">
                                <i class="fas fa-trash"></i> حذف
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php require_once VIEWS_PATH . '/layouts/footer.php'; ?>
