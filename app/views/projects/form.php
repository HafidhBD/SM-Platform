<?php require_once VIEWS_PATH . '/layouts/header.php'; ?>

<div class="page-header">
    <h2><i class="fas fa-building"></i> <?= $mode === 'create' ? 'إضافة جهة جديدة' : 'تعديل الجهة' ?></h2>
    <a href="/projects" class="btn btn-outline"><i class="fas fa-arrow-right"></i> رجوع</a>
</div>

<form method="POST" action="<?= $mode === 'create' ? '/projects/create' : '/projects/edit/' . ($project['id'] ?? '') ?>" class="form-card">
    <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">
    
    <!-- Basic Info -->
    <div class="card mb-4">
        <div class="card-header"><h3><i class="fas fa-info-circle"></i> المعلومات الأساسية</h3></div>
        <div class="card-body">
            <div class="form-group">
                <label for="name">اسم الجهة / العلامة التجارية *</label>
                <input type="text" id="name" name="name" class="form-control" 
                       value="<?= e($project['name'] ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label for="description">الوصف</label>
                <textarea id="description" name="description" class="form-control" rows="3"><?= e($project['description'] ?? '') ?></textarea>
            </div>
            <div class="form-group">
                <label>
                    <input type="checkbox" name="is_active" value="1" <?= !isset($project) || !empty($project['is_active']) ? 'checked' : '' ?>>
                    الجهة نشطة
                </label>
            </div>
        </div>
    </div>

    <!-- Keywords -->
    <div class="card mb-4">
        <div class="card-header"><h3><i class="fas fa-key"></i> الكلمات المفتاحية</h3></div>
        <div class="card-body">
            <div class="form-group">
                <label for="search_keywords">كلمات البحث (كل كلمة في سطر)</label>
                <textarea id="search_keywords" name="search_keywords" class="form-control" rows="4" 
                          placeholder="مثال:&#10;الشركة&#10;المنتج&#10;الخدمة"><?= e(implode("\n", array_map(fn($k) => $k['keyword'], array_filter($project['keywords'] ?? [], fn($k) => $k['type'] === 'search')))) ?></textarea>
            </div>
            <div class="form-group">
                <label for="crisis_keywords">كلمات الأزمة (كل كلمة في سطر)</label>
                <textarea id="crisis_keywords" name="crisis_keywords" class="form-control" rows="4"
                          placeholder="مثال:&#10;فضيحة&#10;احتيال&#10;مقاطعة"><?= e(implode("\n", array_map(fn($k) => $k['keyword'], array_filter($project['keywords'] ?? [], fn($k) => $k['type'] === 'crisis')))) ?></textarea>
            </div>
        </div>
    </div>

    <!-- Competitors -->
    <div class="card mb-4">
        <div class="card-header"><h3><i class="fas fa-chess"></i> المنافسون</h3></div>
        <div class="card-body">
            <div class="form-group">
                <label for="competitors">أسماء المنافسين (كل اسم في سطر)</label>
                <textarea id="competitors" name="competitors" class="form-control" rows="3"
                          placeholder="مثال:&#10;المنافس أ&#10;المنافس ب"><?= e(implode("\n", array_map(fn($c) => $c['name'], $project['competitors'] ?? []))) ?></textarea>
            </div>
        </div>
    </div>

    <!-- Hashtags & Accounts -->
    <div class="card mb-4">
        <div class="card-header"><h3><i class="fas fa-hashtag"></i> الهاشتاقات والحسابات</h3></div>
        <div class="card-body">
            <div class="form-group">
                <label for="hashtags">الهاشتاقات (كل هاشتاق في سطر)</label>
                <textarea id="hashtags" name="hashtags" class="form-control" rows="3"
                          placeholder="مثال:&#10;#الشركة&#10;#المنتج"><?= e(implode("\n", array_map(fn($h) => $h['hashtag'], $project['hashtags'] ?? []))) ?></textarea>
            </div>
            <div class="form-group">
                <label for="accounts">الحسابات المستهدفة (كل حساب في سطر)</label>
                <textarea id="accounts" name="accounts" class="form-control" rows="3"
                          placeholder="مثال:&#10;@company&#10;@brand"><?= e(implode("\n", array_map(fn($a) => $a['account_username'], $project['accounts'] ?? []))) ?></textarea>
            </div>
        </div>
    </div>

    <div class="form-actions">
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-save"></i> <?= $mode === 'create' ? 'إنشاء الجهة' : 'حفظ التعديلات' ?>
        </button>
        <a href="/projects" class="btn btn-outline">إلغاء</a>
    </div>
</form>

<?php require_once VIEWS_PATH . '/layouts/footer.php'; ?>
