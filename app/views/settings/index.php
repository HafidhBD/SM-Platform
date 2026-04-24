<?php require_once VIEWS_PATH . '/layouts/header.php'; ?>

<div class="page-header">
    <h2><i class="fas fa-cog"></i> الإعدادات</h2>
</div>

<!-- Tabs -->
<div class="tabs mb-4">
    <a href="/settings?tab=apify" class="tab <?= $tab === 'apify' ? 'active' : '' ?>"><i class="fas fa-robot"></i> Apify</a>
    <a href="/settings?tab=openai" class="tab <?= $tab === 'openai' ? 'active' : '' ?>"><i class="fas fa-brain"></i> OpenAI</a>
    <a href="/settings?tab=alerts" class="tab <?= $tab === 'alerts' ? 'active' : '' ?>"><i class="fas fa-bell"></i> التنبيهات</a>
    <a href="/settings?tab=general" class="tab <?= $tab === 'general' ? 'active' : '' ?>"><i class="fas fa-sliders-h"></i> عام</a>
    <a href="/settings?tab=account" class="tab <?= $tab === 'account' ? 'active' : '' ?>"><i class="fas fa-user"></i> الحساب</a>
</div>

<?php if ($tab === 'apify'): ?>
<div class="card">
    <div class="card-header"><h3><i class="fas fa-robot"></i> إعدادات Apify</h3></div>
    <div class="card-body">
        <form method="POST" action="/settings/save-apify">
            <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">
            <div class="form-group">
                <label for="apify_api_token">Apify API Token</label>
                <input type="password" id="apify_api_token" name="apify_api_token" class="form-control" 
                       value="<?= e(getSetting('apify_api_token', '')) ?>" placeholder="أدخل Apify API Token">
                <small class="form-hint">يمكنك الحصول عليه من <a href="https://console.apify.com/settings/integrations" target="_blank">Apify Console</a></small>
            </div>
            <div class="form-group">
                <label for="apify_actor_id">Actor ID</label>
                <input type="text" id="apify_actor_id" name="apify_actor_id" class="form-control" 
                       value="<?= e(getSetting('apify_actor_id', 'scraply~x-twitter-posts-search')) ?>">
            </div>
            <div class="form-row">
                <div class="form-group col-md-4">
                    <label for="apify_max_tweets">عدد المنشورات الافتراضي</label>
                    <input type="number" id="apify_max_tweets" name="apify_max_tweets" class="form-control" 
                           value="<?= e(getSetting('apify_max_tweets', '50')) ?>" min="1" max="1000">
                </div>
                <div class="form-group col-md-4">
                    <label for="apify_time_window">النافذة الزمنية الافتراضية (أيام)</label>
                    <input type="number" id="apify_time_window" name="apify_time_window" class="form-control" 
                           value="<?= e(getSetting('apify_time_window', '7')) ?>" min="1" max="30">
                </div>
                <div class="form-group col-md-4">
                    <label for="apify_search_type">نوع البحث الافتراضي</label>
                    <select id="apify_search_type" name="apify_search_type" class="form-select">
                        <option value="latest" <?= getSetting('apify_search_type', 'latest') === 'latest' ? 'selected' : '' ?>>الأحدث</option>
                        <option value="top" <?= getSetting('apify_search_type', 'latest') === 'top' ? 'selected' : '' ?>>الأكثر تفاعلًا</option>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label>
                    <input type="checkbox" name="apify_use_proxy" value="1" <?= getSetting('apify_use_proxy', '1') === '1' ? 'checked' : '' ?>>
                    استخدام Apify Proxy (موصى به)
                </label>
            </div>
            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> حفظ الإعدادات</button>
        </form>
    </div>
</div>
<?php endif; ?>

<?php if ($tab === 'openai'): ?>
<div class="card">
    <div class="card-header"><h3><i class="fas fa-brain"></i> إعدادات OpenAI</h3></div>
    <div class="card-body">
        <form method="POST" action="/settings/save-openai">
            <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">
            <div class="form-group">
                <label for="openai_api_key">OpenAI API Key</label>
                <input type="password" id="openai_api_key" name="openai_api_key" class="form-control" 
                       value="<?= e(getSetting('openai_api_key', '')) ?>" placeholder="sk-...">
                <small class="form-hint">يمكنك الحصول عليه من <a href="https://platform.openai.com/api-keys" target="_blank">OpenAI Platform</a></small>
            </div>
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="openai_model">الموديل</label>
                    <select id="openai_model" name="openai_model" class="form-select">
                        <option value="gpt-4o-mini" <?= getSetting('openai_model', 'gpt-4o-mini') === 'gpt-4o-mini' ? 'selected' : '' ?>>GPT-4o Mini (اقتصادي)</option>
                        <option value="gpt-4o" <?= getSetting('openai_model', 'gpt-4o-mini') === 'gpt-4o' ? 'selected' : '' ?>>GPT-4o (أفضل جودة)</option>
                        <option value="gpt-3.5-turbo" <?= getSetting('openai_model', 'gpt-4o-mini') === 'gpt-3.5-turbo' ? 'selected' : '' ?>>GPT-3.5 Turbo (الأرخص)</option>
                    </select>
                </div>
                <div class="form-group col-md-6">
                    <label for="openai_batch_size">عدد المنشورات في الدفعة</label>
                    <input type="number" id="openai_batch_size" name="openai_batch_size" class="form-control" 
                           value="<?= e(getSetting('openai_batch_size', '20')) ?>" min="5" max="50">
                    <small class="form-hint">أقل = أدق وأغلى | أكثر = أوفر وأقل دقة</small>
                </div>
            </div>
            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> حفظ الإعدادات</button>
        </form>
    </div>
</div>
<?php endif; ?>

<?php if ($tab === 'alerts'): ?>
<div class="card">
    <div class="card-header"><h3><i class="fas fa-bell"></i> إعدادات التنبيهات</h3></div>
    <div class="card-body">
        <form method="POST" action="/settings/save-alerts">
            <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">
            <div class="form-group">
                <label for="alert_negative_threshold">نسبة السلبية للتنبيه (%)</label>
                <input type="number" id="alert_negative_threshold" name="alert_negative_threshold" class="form-control" 
                       value="<?= e(getSetting('alert_negative_threshold', '40')) ?>" min="10" max="100">
                <small class="form-hint">إذا تجاوزت نسبة السلبية هذا الرقم يتم إنشاء تنبيه</small>
            </div>
            <div class="form-group">
                <label for="alert_volume_spike_percent">نسبة الارتفاع المفاجئ (%)</label>
                <input type="number" id="alert_volume_spike_percent" name="alert_volume_spike_percent" class="form-control" 
                       value="<?= e(getSetting('alert_volume_spike_percent', '50')) ?>" min="10" max="200">
            </div>
            <div class="form-group">
                <label for="alert_crisis_keyword_threshold">عدد تكرار كلمات الأزمة</label>
                <input type="number" id="alert_crisis_keyword_threshold" name="alert_crisis_keyword_threshold" class="form-control" 
                       value="<?= e(getSetting('alert_crisis_keyword_threshold', '5')) ?>" min="1" max="100">
            </div>
            <div class="form-group">
                <label for="alert_attack_post_threshold">عدد منشورات الهجوم للتنبيه</label>
                <input type="number" id="alert_attack_post_threshold" name="alert_attack_post_threshold" class="form-control" 
                       value="<?= e(getSetting('alert_attack_post_threshold', '10')) ?>" min="1" max="100">
            </div>
            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> حفظ الإعدادات</button>
        </form>
    </div>
</div>
<?php endif; ?>

<?php if ($tab === 'general'): ?>
<div class="card">
    <div class="card-header"><h3><i class="fas fa-sliders-h"></i> الإعدادات العامة</h3></div>
    <div class="card-body">
        <form method="POST" action="/settings/save-general">
            <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">
            <div class="form-group">
                <label for="system_name">اسم المنصة</label>
                <input type="text" id="system_name" name="system_name" class="form-control" 
                       value="<?= e(getSetting('system_name', 'Market Intelligence Platform')) ?>">
            </div>
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="system_language">اللغة</label>
                    <select id="system_language" name="system_language" class="form-select">
                        <option value="ar" <?= getSetting('system_language', 'ar') === 'ar' ? 'selected' : '' ?>>العربية</option>
                        <option value="en" <?= getSetting('system_language', 'ar') === 'en' ? 'selected' : '' ?>>English</option>
                    </select>
                </div>
                <div class="form-group col-md-6">
                    <label for="system_timezone">المنطقة الزمنية</label>
                    <select id="system_timezone" name="system_timezone" class="form-select">
                        <option value="Asia/Riyadh" <?= getSetting('system_timezone', 'Asia/Riyadh') === 'Asia/Riyadh' ? 'selected' : '' ?>>الرياض</option>
                        <option value="Asia/Dubai" <?= getSetting('system_timezone', 'Asia/Riyadh') === 'Asia/Dubai' ? 'selected' : '' ?>>دبي</option>
                        <option value="Asia/Kuwait" <?= getSetting('system_timezone', 'Asia/Riyadh') === 'Asia/Kuwait' ? 'selected' : '' ?>>الكويت</option>
                        <option value="Africa/Cairo" <?= getSetting('system_timezone', 'Asia/Riyadh') === 'Africa/Cairo' ? 'selected' : '' ?>>القاهرة</option>
                        <option value="UTC" <?= getSetting('system_timezone', 'Asia/Riyadh') === 'UTC' ? 'selected' : '' ?>>UTC</option>
                    </select>
                </div>
            </div>
            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> حفظ الإعدادات</button>
        </form>
    </div>
</div>
<?php endif; ?>

<?php if ($tab === 'account'): ?>
<div class="card">
    <div class="card-header"><h3><i class="fas fa-user"></i> إعدادات الحساب</h3></div>
    <div class="card-body">
        <form method="POST" action="/settings/change-password">
            <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">
            <div class="form-group">
                <label for="current_password">كلمة المرور الحالية</label>
                <input type="password" id="current_password" name="current_password" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="new_password">كلمة المرور الجديدة</label>
                <input type="password" id="new_password" name="new_password" class="form-control" required minlength="8">
            </div>
            <div class="form-group">
                <label for="confirm_password">تأكيد كلمة المرور</label>
                <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary"><i class="fas fa-key"></i> تغيير كلمة المرور</button>
        </form>
    </div>
</div>
<?php endif; ?>

<?php require_once VIEWS_PATH . '/layouts/footer.php'; ?>
