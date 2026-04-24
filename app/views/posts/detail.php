<?php require_once VIEWS_PATH . '/layouts/header.php'; ?>

<div class="page-header">
    <h2><i class="fas fa-file-alt"></i> تفاصيل المنشور</h2>
    <a href="/posts" class="btn btn-outline"><i class="fas fa-arrow-right"></i> رجوع</a>
</div>

<div class="card">
    <div class="card-body">
        <div class="post-detail">
            <div class="post-detail-header">
                <strong><?= e($post['author_name'] ?? '') ?></strong>
                <?php if ($post['author_username']): ?>
                    <a href="https://x.com/<?= e($post['author_username']) ?>" target="_blank">@<?= e($post['author_username']) ?></a>
                <?php endif; ?>
                <?php if (!empty($post['author_verified'])): ?>
                    <i class="fas fa-check-circle text-blue"></i>
                <?php endif; ?>
                <span class="text-muted"><?= $post['author_followers'] ? number_format($post['author_followers']) . ' متابع' : '' ?></span>
            </div>

            <div class="post-detail-text"><?= e($post['content_text'] ?? '') ?></div>

            <div class="post-detail-meta">
                <span>❤️ <?= number_format($post['likes_count'] ?? 0) ?></span>
                <span>💬 <?= number_format($post['replies_count'] ?? 0) ?></span>
                <span>🔁 <?= number_format($post['reposts_count'] ?? 0) ?></span>
                <span>👁️ <?= number_format($post['views_count'] ?? 0) ?></span>
                <span>📅 <?= formatDate($post['posted_at']) ?></span>
            </div>

            <?php if ($post['post_url']): ?>
                <a href="<?= e($post['post_url']) ?>" target="_blank" class="btn btn-sm btn-outline mt-2">
                    <i class="fas fa-external-link-alt"></i> فتح المنشور على X
                </a>
            <?php endif; ?>

            <?php if (isset($post['sentiment'])): ?>
            <div class="post-detail-ai mt-3">
                <h4><i class="fas fa-brain"></i> تحليل الذكاء الاصطناعي</h4>
                <div class="ai-grid">
                    <div><strong>المشاعر:</strong> <span class="badge <?= sentimentClass($post['sentiment']) ?>"><?= sentimentLabel($post['sentiment']) ?></span></div>
                    <div><strong>السمعة:</strong> <?= reputationLabel($post['reputation_label'] ?? '') ?></div>
                    <div><strong>الموضوع:</strong> <?= topicLabel($post['topic_label'] ?? '') ?></div>
                    <div><strong>الخطورة:</strong> <?= $post['risk_score'] ?? 0 ?>/100</div>
                    <?php if (!empty($post['crisis_flag'])): ?><div class="text-danger"><strong>⚠️ علامة أزمة</strong></div><?php endif; ?>
                    <?php if (!empty($post['attack_flag'])): ?><div class="text-danger"><strong>⚠️ هجوم</strong></div><?php endif; ?>
                    <?php if (!empty($post['complaint_flag'])): ?><div class="text-warning"><strong>📝 شكوى</strong></div><?php endif; ?>
                    <?php if (!empty($post['sarcasm_flag'])): ?><div class="text-muted"><strong>😏 سخرية</strong></div><?php endif; ?>
                </div>
                <?php if ($post['ai_summary']): ?>
                    <p class="mt-2"><strong>ملخص:</strong> <?= e($post['ai_summary']) ?></p>
                <?php endif; ?>
                <?php if ($post['ai_keywords']): ?>
                    <div class="tag-list mt-2">
                        <?php foreach (json_decode($post['ai_keywords'], true) ?? [] as $kw): ?>
                            <span class="tag"><?= e($kw) ?></span>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
            <?php else: ?>
                <p class="text-muted mt-3">لم يتم تحليل هذا المنشور بعد</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once VIEWS_PATH . '/layouts/footer.php'; ?>
