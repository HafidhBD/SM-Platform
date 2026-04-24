<?php require_once VIEWS_PATH . '/layouts/header.php'; ?>

<div class="page-header">
    <h2><i class="fas fa-file-alt"></i> استكشاف المنشورات</h2>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-header"><h3><i class="fas fa-filter"></i> فلترة</h3></div>
    <div class="card-body">
        <form method="GET" action="/posts" class="filters-form">
            <div class="form-row">
                <div class="form-group col-md-2">
                    <label>الجهة</label>
                    <select name="project" class="form-select">
                        <option value="">الكل</option>
                        <?php foreach ($projects as $p): ?>
                            <option value="<?= $p['id'] ?>" <?= $p['id'] == $current_project_id ? 'selected' : '' ?>>
                                <?= e($p['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group col-md-2">
                    <label>المشاعر</label>
                    <select name="sentiment" class="form-select">
                        <option value="">الكل</option>
                        <option value="positive" <?= ($filters['sentiment'] ?? '') === 'positive' ? 'selected' : '' ?>>إيجابي</option>
                        <option value="negative" <?= ($filters['sentiment'] ?? '') === 'negative' ? 'selected' : '' ?>>سلبي</option>
                        <option value="neutral" <?= ($filters['sentiment'] ?? '') === 'neutral' ? 'selected' : '' ?>>محايد</option>
                    </select>
                </div>
                <div class="form-group col-md-2">
                    <label>الموضوع</label>
                    <select name="topic" class="form-select">
                        <option value="">الكل</option>
                        <option value="service" <?= ($filters['topic'] ?? '') === 'service' ? 'selected' : '' ?>>خدمة</option>
                        <option value="quality" <?= ($filters['topic'] ?? '') === 'quality' ? 'selected' : '' ?>>جودة</option>
                        <option value="pricing" <?= ($filters['topic'] ?? '') === 'pricing' ? 'selected' : '' ?>>أسعار</option>
                        <option value="customer_experience" <?= ($filters['topic'] ?? '') === 'customer_experience' ? 'selected' : '' ?>>تجربة عميل</option>
                        <option value="campaign" <?= ($filters['topic'] ?? '') === 'campaign' ? 'selected' : '' ?>>إعلان / حملة</option>
                        <option value="delay" <?= ($filters['topic'] ?? '') === 'delay' ? 'selected' : '' ?>>تأخير</option>
                        <option value="support" <?= ($filters['topic'] ?? '') === 'support' ? 'selected' : '' ?>>دعم فني</option>
                        <option value="competition" <?= ($filters['topic'] ?? '') === 'competition' ? 'selected' : '' ?>>منافسة</option>
                        <option value="management" <?= ($filters['topic'] ?? '') === 'management' ? 'selected' : '' ?>>إدارة</option>
                        <option value="general" <?= ($filters['topic'] ?? '') === 'general' ? 'selected' : '' ?>>عام</option>
                    </select>
                </div>
                <div class="form-group col-md-2">
                    <label>السمعة</label>
                    <select name="reputation" class="form-select">
                        <option value="">الكل</option>
                        <option value="praise" <?= ($filters['reputation'] ?? '') === 'praise' ? 'selected' : '' ?>>إشادة</option>
                        <option value="complaint" <?= ($filters['reputation'] ?? '') === 'complaint' ? 'selected' : '' ?>>شكوى</option>
                        <option value="attack" <?= ($filters['reputation'] ?? '') === 'attack' ? 'selected' : '' ?>>هجوم</option>
                        <option value="sarcasm" <?= ($filters['reputation'] ?? '') === 'sarcasm' ? 'selected' : '' ?>>سخرية</option>
                        <option value="inquiry" <?= ($filters['reputation'] ?? '') === 'inquiry' ? 'selected' : '' ?>>استفسار</option>
                        <option value="rumor" <?= ($filters['reputation'] ?? '') === 'rumor' ? 'selected' : '' ?>>إشاعة</option>
                    </select>
                </div>
                <div class="form-group col-md-2">
                    <label>من تاريخ</label>
                    <input type="date" name="date_from" class="form-control" value="<?= e($filters['date_from'] ?? '') ?>">
                </div>
                <div class="form-group col-md-2">
                    <label>إلى تاريخ</label>
                    <input type="date" name="date_to" class="form-control" value="<?= e($filters['date_to'] ?? '') ?>">
                </div>
            </div>
            <div class="form-row mt-2">
                <div class="form-group col-md-3">
                    <label>بحث في النص</label>
                    <input type="text" name="search" class="form-control" placeholder="بحث..." value="<?= e($filters['search'] ?? '') ?>">
                </div>
                <div class="form-group col-md-2">
                    <label>الحساب</label>
                    <input type="text" name="author" class="form-control" placeholder="@username" value="<?= e($filters['author'] ?? '') ?>">
                </div>
                <div class="form-group col-md-2">
                    <label class="checkbox-label">
                        <input type="checkbox" name="crisis_flag" value="1" <?= !empty($filters['crisis_flag']) ? 'checked' : '' ?>>
                        أزمة فقط
                    </label>
                </div>
                <div class="form-group col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> فلترة</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Posts Table -->
<div class="card">
    <div class="card-header">
        <h3><i class="fas fa-list"></i> المنشورات (<?= $posts['total'] ?>)</h3>
    </div>
    <div class="card-body">
        <?php if (empty($posts['items'])): ?>
            <div class="empty-state-sm">
                <p>لا توجد منشورات مطابقة</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th style="width:40%">المنشور</th>
                            <th>الحساب</th>
                            <th>المشاعر</th>
                            <th>السمعة</th>
                            <th>الموضوع</th>
                            <th>الخطورة</th>
                            <th>❤️</th>
                            <th>💬</th>
                            <th>🔁</th>
                            <th>التاريخ</th>
                            <th>إجراء</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($posts['items'] as $post): ?>
                            <tr class="<?= !empty($post['crisis_flag']) ? 'row-crisis' : '' ?>">
                                <td class="post-text-cell">
                                    <?= e(truncate($post['content_text'], 100)) ?>
                                    <?php if (!empty($post['crisis_flag'])): ?>
                                        <span class="badge badge-danger-sm">⚠️ أزمة</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="author-cell">
                                        @<?= e($post['author_username'] ?? '') ?>
                                        <?php if (!empty($post['author_verified'])): ?>
                                            <i class="fas fa-check-circle text-blue"></i>
                                        <?php endif; ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if (isset($post['sentiment'])): ?>
                                        <span class="badge <?= sentimentClass($post['sentiment']) ?>"><?= sentimentLabel($post['sentiment']) ?></span>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?= isset($post['reputation_label']) ? reputationLabel($post['reputation_label']) : '—' ?>
                                </td>
                                <td>
                                    <?= isset($post['topic_label']) ? topicLabel($post['topic_label']) : '—' ?>
                                </td>
                                <td>
                                    <?php if (isset($post['risk_score'])): ?>
                                        <div class="risk-bar">
                                            <div class="risk-fill" style="width:<?= $post['risk_score'] ?>%; background-color: <?= $post['risk_score'] > 70 ? '#ef4444' : ($post['risk_score'] > 40 ? '#f59e0b' : '#10b981') ?>"></div>
                                            <span class="risk-value"><?= $post['risk_score'] ?></span>
                                        </div>
                                    <?php else: ?>
                                        —
                                    <?php endif; ?>
                                </td>
                                <td><?= formatNumber($post['likes_count']) ?></td>
                                <td><?= formatNumber($post['replies_count']) ?></td>
                                <td><?= formatNumber($post['reposts_count']) ?></td>
                                <td><?= formatDate($post['posted_at'], 'm/d H:i') ?></td>
                                <td>
                                    <button class="btn btn-sm btn-outline" onclick="viewPost(<?= $post['id'] ?>)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if ($posts['total_pages'] > 1): ?>
                <div class="pagination">
                    <?php if ($posts['page'] > 1): ?>
                        <a href="?page=<?= $posts['page'] - 1 ?>&<?= http_build_query(array_filter($filters)) ?>&project=<?= $current_project_id ?>" class="btn btn-sm btn-outline">
                            <i class="fas fa-chevron-right"></i> السابق
                        </a>
                    <?php endif; ?>
                    <span class="pagination-info">صفحة <?= $posts['page'] ?> من <?= $posts['total_pages'] ?></span>
                    <?php if ($posts['page'] < $posts['total_pages']): ?>
                        <a href="?page=<?= $posts['page'] + 1 ?>&<?= http_build_query(array_filter($filters)) ?>&project=<?= $current_project_id ?>" class="btn btn-sm btn-outline">
                            التالي <i class="fas fa-chevron-left"></i>
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Post Detail Modal -->
<div class="modal" id="postModal">
    <div class="modal-overlay" onclick="closePostModal()"></div>
    <div class="modal-content">
        <div class="modal-header">
            <h3>تفاصيل المنشور</h3>
            <button class="modal-close" onclick="closePostModal()"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body" id="postModalBody">
            <div class="loading-spinner"><i class="fas fa-spinner fa-spin"></i> جاري التحميل...</div>
        </div>
    </div>
</div>

<script>
function viewPost(postId) {
    const modal = document.getElementById('postModal');
    const body = document.getElementById('postModalBody');
    modal.classList.add('active');
    body.innerHTML = '<div class="loading-spinner"><i class="fas fa-spinner fa-spin"></i> جاري التحميل...</div>';
    
    fetch('/posts/view/' + postId)
        .then(r => r.json())
        .then(data => {
            body.innerHTML = `
                <div class="post-detail">
                    <div class="post-detail-header">
                        <strong>${data.author_name || ''}</strong>
                        <a href="https://x.com/${data.author_username}" target="_blank">@${data.author_username || ''}</a>
                        ${data.author_verified ? '<i class="fas fa-check-circle text-blue"></i>' : ''}
                        <span class="text-muted">${data.author_followers ? data.author_followers + ' متابع' : ''}</span>
                    </div>
                    <div class="post-detail-text">${data.content_text || ''}</div>
                    <div class="post-detail-meta">
                        <span>❤️ ${data.likes_count || 0}</span>
                        <span>💬 ${data.replies_count || 0}</span>
                        <span>🔁 ${data.reposts_count || 0}</span>
                        <span>👁️ ${data.views_count || 0}</span>
                        <span>📅 ${data.posted_at || ''}</span>
                    </div>
                    ${data.post_url ? '<a href="' + data.post_url + '" target="_blank" class="btn btn-sm btn-outline mt-2"><i class="fas fa-external-link-alt"></i> فتح المنشور</a>' : ''}
                    ${data.sentiment ? `
                    <div class="post-detail-ai mt-3">
                        <h4><i class="fas fa-brain"></i> تحليل الذكاء الاصطناعي</h4>
                        <div class="ai-grid">
                            <div><strong>المشاعر:</strong> <span class="badge badge-${data.sentiment}">${data.sentiment === 'positive' ? 'إيجابي' : data.sentiment === 'negative' ? 'سلبي' : 'محايد'}</span></div>
                            <div><strong>السمعة:</strong> ${data.reputation_label || '—'}</div>
                            <div><strong>الموضوع:</strong> ${data.topic_label || '—'}</div>
                            <div><strong>الخطورة:</strong> ${data.risk_score || 0}/100</div>
                            ${data.crisis_flag ? '<div class="text-danger"><strong>⚠️ علامة أزمة</strong></div>' : ''}
                            ${data.attack_flag ? '<div class="text-danger"><strong>⚠️ هجوم</strong></div>' : ''}
                        </div>
                        ${data.ai_summary ? '<p class="mt-2"><strong>ملخص:</strong> ' + data.ai_summary + '</p>' : ''}
                    </div>` : '<p class="text-muted mt-2">لم يتم تحليل هذا المنشور بعد</p>'}
                </div>
            `;
        })
        .catch(err => {
            body.innerHTML = '<div class="text-danger">خطأ في تحميل البيانات</div>';
        });
}

function closePostModal() {
    document.getElementById('postModal').classList.remove('active');
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closePostModal();
});
</script>

<?php require_once VIEWS_PATH . '/layouts/footer.php'; ?>
