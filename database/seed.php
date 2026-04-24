<?php
/**
 * Mock Data Seeder
 * 
 * Run this script to populate the database with sample data for testing.
 * Usage: php database/seed.php
 * 
 * WARNING: Only run this in development/testing environments!
 */

define('APP_ROOT', dirname(__DIR__));
require_once APP_ROOT . '/config/config.php';
require_once APP_ROOT . '/app/core/Database.php';
require_once APP_ROOT . '/app/core/Helpers.php';

echo "=== Market Intelligence Platform - Mock Data Seeder ===\n\n";

$db = Database::getInstance();
if (!$db->isConnected()) {
    die("ERROR: Database connection failed. Please configure config/env.php first.\n");
}

// Ensure admin user exists
$admin = $db->queryOne("SELECT id FROM users WHERE username = 'admin'");
if (!$admin) {
    require_once APP_ROOT . '/app/core/Auth.php';
    Auth::createAdmin();
    echo "[+] Admin user created\n";
}

// Create sample project
$project = $db->queryOne("SELECT id FROM projects WHERE name = 'شركة النخبة للخدمات'");
if (!$project) {
    $projectId = $db->insert('projects', [
        'name' => 'شركة النخبة للخدمات',
        'description' => 'شركة رائدة في مجال الخدمات والاستشارات',
        'is_active' => 1,
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s')
    ]);
    echo "[+] Sample project created (ID: {$projectId})\n";

    // Add keywords
    $keywords = ['النخبة', 'خدمات النخبة', 'شركة النخبة'];
    foreach ($keywords as $kw) {
        $db->insert('project_keywords', ['project_id' => $projectId, 'keyword' => $kw, 'type' => 'search']);
    }
    $crisisKeywords = ['فضيحة النخبة', 'مقاطعة النخبة', 'احتيال'];
    foreach ($crisisKeywords as $kw) {
        $db->insert('project_keywords', ['project_id' => $projectId, 'keyword' => $kw, 'type' => 'crisis']);
    }

    // Add hashtags
    $hashtags = ['#النخبة', '#خدمات_النخبة', '#شركة_النخبة'];
    foreach ($hashtags as $ht) {
        $db->insert('project_hashtags', ['project_id' => $projectId, 'hashtag' => $ht]);
    }

    // Add accounts
    $accounts = ['@alnokhba', '@nokhba_services'];
    foreach ($accounts as $acc) {
        $db->insert('project_accounts', ['project_id' => $projectId, 'account_username' => $acc]);
    }

    // Add competitor
    $db->insert('competitors', ['project_id' => $projectId, 'name' => 'المنافس الذهبي', 'username' => '@golden_competitor']);

    echo "[+] Project keywords, hashtags, accounts added\n";
} else {
    $projectId = $project['id'];
    echo "[i] Sample project already exists (ID: {$projectId})\n";
}

// Create sample collection run
$run = $db->queryOne("SELECT id FROM collection_runs WHERE project_id = ? LIMIT 1", [$projectId]);
if (!$run) {
    $runId = $db->insert('collection_runs', [
        'project_id' => $projectId,
        'actor_id' => 'scraply~x-twitter-posts-search',
        'status' => 'completed',
        'targets' => json_encode(['النخبة', '#النخبة', '@alnokhba'], JSON_UNESCAPED_UNICODE),
        'posts_found' => 30,
        'posts_stored' => 30,
        'started_at' => date('Y-m-d H:i:s', strtotime('-1 hour')),
        'completed_at' => date('Y-m-d H:i:s'),
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s')
    ]);
    echo "[+] Sample collection run created\n";
} else {
    $runId = $run['id'];
    echo "[i] Collection run already exists\n";
}

// Create sample posts with analysis
$existingPosts = $db->count('posts', 'project_id = ?', [$projectId]);
if ($existingPosts == 0) {
    $samplePosts = [
        ['text' => 'خدمة ممتازة من شركة النخبة، أنصح الجميع بالتعامل معهم', 'sentiment' => 'positive', 'score' => 85, 'rep' => 'praise', 'topic' => 'service', 'risk' => 5, 'likes' => 120, 'replies' => 15, 'reposts' => 30, 'views' => 5000],
        ['text' => 'تأخير رهيب في التوصيل! انتظرت أسبوع كامل بدون أي رد', 'sentiment' => 'negative', 'score' => 15, 'rep' => 'complaint', 'topic' => 'delay', 'risk' => 60, 'likes' => 250, 'replies' => 80, 'reposts' => 45, 'views' => 12000, 'complaint' => true],
        ['text' => 'شركة النخبة أعلنت عن عرض جديد الصيف، أسعار منافسة', 'sentiment' => 'neutral', 'score' => 50, 'rep' => 'neutral_mention', 'topic' => 'campaign', 'risk' => 10, 'likes' => 50, 'replies' => 5, 'reposts' => 20, 'views' => 3000],
        ['text' => 'هل صحيح أن النخبة رفعت الأسعار بشكل كبير؟ حدثني أحد الأصدقاء بذلك', 'sentiment' => 'neutral', 'score' => 40, 'rep' => 'inquiry', 'topic' => 'pricing', 'risk' => 35, 'likes' => 30, 'replies' => 12, 'reposts' => 8, 'views' => 2000],
        ['text' => 'فضيحة! شركة النخبة تبيع بيانات العملاء لأطراف ثالثة #مقاطعة_النخبة', 'sentiment' => 'negative', 'score' => 5, 'rep' => 'rumor', 'topic' => 'management', 'risk' => 90, 'likes' => 500, 'replies' => 200, 'reposts' => 150, 'views' => 50000, 'crisis' => true, 'attack' => true],
        ['text' => 'هاهاها شركة النخبة بتقول خدمات ممتازة 😂 والواقع عكس كذا تماماً', 'sentiment' => 'negative', 'score' => 20, 'rep' => 'sarcasm', 'topic' => 'service', 'risk' => 45, 'likes' => 180, 'replies' => 40, 'reposts' => 60, 'views' => 8000, 'sarcasm' => true],
        ['text' => 'الدعم الفني في النخبة سريع الاستجابة وحلوا مشكلتي بسرعة', 'sentiment' => 'positive', 'score' => 80, 'rep' => 'praise', 'topic' => 'support', 'risk' => 5, 'likes' => 40, 'replies' => 8, 'reposts' => 10, 'views' => 1500],
        ['text' => 'هجوم منسق على شركة النخبة من حسابات وهمية، واضح إن المنافسين وراها', 'sentiment' => 'negative', 'score' => 10, 'rep' => 'attack', 'topic' => 'competition', 'risk' => 75, 'likes' => 90, 'replies' => 30, 'reposts' => 25, 'views' => 6000, 'attack' => true],
        ['text' => 'تجربتي مع النخبة كانت عادية، لا سيء ولا ممتاز', 'sentiment' => 'neutral', 'score' => 50, 'rep' => 'neutral_mention', 'topic' => 'customer_experience', 'risk' => 10, 'likes' => 15, 'replies' => 3, 'reposts' => 2, 'views' => 800],
        ['text' => 'النخبة توظف الآن! فرص وظيفية رائعة في مجال التقنية', 'sentiment' => 'positive', 'score' => 70, 'rep' => 'neutral_mention', 'topic' => 'hiring', 'risk' => 5, 'likes' => 60, 'replies' => 20, 'reposts' => 35, 'views' => 4000],
        ['text' => 'مقاطعة النخبة فوراً! لا نثق بهذه الشركة بعد ما حدث #مقاطعة_النخبة', 'sentiment' => 'negative', 'score' => 8, 'rep' => 'attack', 'topic' => 'management', 'risk' => 85, 'likes' => 350, 'replies' => 120, 'reposts' => 90, 'views' => 25000, 'crisis' => true, 'attack' => true],
        ['text' => 'أسعار النخبة مرتفعة مقارنة بالمنافسين لكن الجودة أفضل', 'sentiment' => 'neutral', 'score' => 45, 'rep' => 'neutral_mention', 'topic' => 'pricing', 'risk' => 25, 'likes' => 25, 'replies' => 10, 'reposts' => 5, 'views' => 1800],
        ['text' => 'النخبة أعلنت عن شراكة استراتيجية جديدة، خطوة ممتازة!', 'sentiment' => 'positive', 'score' => 75, 'rep' => 'praise', 'topic' => 'management', 'risk' => 5, 'likes' => 80, 'replies' => 15, 'reposts' => 25, 'views' => 3500],
        ['text' => 'شركة النخبة ما ترد على الشكاوى، هذا يدل على ضعف الإدارة', 'sentiment' => 'negative', 'score' => 25, 'rep' => 'complaint', 'topic' => 'management', 'risk' => 55, 'likes' => 100, 'replies' => 35, 'reposts' => 20, 'views' => 5000, 'complaint' => true],
        ['text' => 'إشاعة: النخبة على وشك الإفلاس! هل هذا صحيح؟', 'sentiment' => 'negative', 'score' => 12, 'rep' => 'rumor', 'topic' => 'management', 'risk' => 80, 'likes' => 200, 'replies' => 90, 'reposts' => 70, 'views' => 30000, 'crisis' => true],
        ['text' => 'خدمة التوصيل من النخبة تحسنت كثيرًا مؤخراً، شكراً لكم', 'sentiment' => 'positive', 'score' => 78, 'rep' => 'praise', 'topic' => 'service', 'risk' => 5, 'likes' => 55, 'replies' => 8, 'reposts' => 12, 'views' => 2200],
        ['text' => 'المنافس الذهبي أفضل بكثير من النخبة، جربوه وما تندمون', 'sentiment' => 'negative', 'score' => 30, 'rep' => 'neutral_mention', 'topic' => 'competition', 'risk' => 40, 'likes' => 70, 'replies' => 25, 'reposts' => 15, 'views' => 4000],
        ['text' => 'تصعيد: موظف في النخبة يتهم الإدارة بالتمييز والعنصرية!', 'sentiment' => 'negative', 'score' => 8, 'rep' => 'escalation', 'topic' => 'management', 'risk' => 88, 'likes' => 400, 'replies' => 150, 'reposts' => 100, 'views' => 40000, 'crisis' => true, 'attack' => true],
        ['text' => 'الحملة الإعلانية الجديدة للنخبة إبداع، أعجبتني الفكرة', 'sentiment' => 'positive', 'score' => 82, 'rep' => 'praise', 'topic' => 'campaign', 'risk' => 5, 'likes' => 90, 'replies' => 12, 'reposts' => 30, 'views' => 5500],
        ['text' => 'لماذا لا توجد قنوات تواصل واضحة لشركة النخبة؟ صعب التواصل معهم', 'sentiment' => 'negative', 'score' => 30, 'rep' => 'complaint', 'topic' => 'support', 'risk' => 40, 'likes' => 60, 'replies' => 20, 'reposts' => 10, 'views' => 3000, 'complaint' => true],
        ['text' => 'جودة منتجات النخبة ممتازة بسعرهم شوي غالي', 'sentiment' => 'neutral', 'score' => 55, 'rep' => 'neutral_mention', 'topic' => 'quality', 'risk' => 15, 'likes' => 35, 'replies' => 8, 'reposts' => 5, 'views' => 1500],
        ['text' => 'النخبة خسرت عميل وفي، لن أتعامل معهم مرة أخرى', 'sentiment' => 'negative', 'score' => 18, 'rep' => 'complaint', 'topic' => 'customer_experience', 'risk' => 50, 'likes' => 85, 'replies' => 30, 'reposts' => 18, 'views' => 4500, 'complaint' => true],
        ['text' => 'استفسار: هل النخبة تقدم خصومات للعملاء الدائمين؟', 'sentiment' => 'neutral', 'score' => 50, 'rep' => 'inquiry', 'topic' => 'pricing', 'risk' => 10, 'likes' => 20, 'replies' => 5, 'reposts' => 3, 'views' => 1200],
        ['text' => 'النخبة تستحق التقدير على جهودها في خدمة المجتمع', 'sentiment' => 'positive', 'score' => 88, 'rep' => 'praise', 'topic' => 'general', 'risk' => 3, 'likes' => 110, 'replies' => 18, 'reposts' => 28, 'views' => 4200],
        ['text' => 'هجوم منسق وواضح على حساب النخبة من حسابات جديدة بدون متابعين', 'sentiment' => 'negative', 'score' => 10, 'rep' => 'attack', 'topic' => 'management', 'risk' => 70, 'likes' => 150, 'replies' => 50, 'reposts' => 35, 'views' => 10000, 'attack' => true],
        ['text' => 'تجربتي مع دعم النخبة الفني كانت سلبية، انتظرت ساعتين بدون رد', 'sentiment' => 'negative', 'score' => 22, 'rep' => 'complaint', 'topic' => 'support', 'risk' => 45, 'likes' => 75, 'replies' => 28, 'reposts' => 12, 'views' => 3800, 'complaint' => true],
        ['text' => 'النخبة أطلقت تطبيق جديد، واجهة سهلة الاستخدام 👏', 'sentiment' => 'positive', 'score' => 80, 'rep' => 'praise', 'topic' => 'service', 'risk' => 5, 'likes' => 95, 'replies' => 10, 'reposts' => 22, 'views' => 3200],
        ['text' => 'مقارنة بين النخبة والمنافس الذهبي: النخبة أفضل في الجودة لكن الأسعار أعلى', 'sentiment' => 'neutral', 'score' => 48, 'rep' => 'neutral_mention', 'topic' => 'competition', 'risk' => 20, 'likes' => 45, 'replies' => 15, 'reposts' => 8, 'views' => 2500],
        ['text' => 'النخبة تماطل في رد الشكاوى وهذا يزيد من استفزاز العملاء', 'sentiment' => 'negative', 'score' => 20, 'rep' => 'escalation', 'topic' => 'customer_experience', 'risk' => 55, 'likes' => 130, 'replies' => 45, 'reposts' => 25, 'views' => 7000],
        ['text' => 'عرض خاص من النخبة بمناسبة العيد، خصومات تصل إلى 30%', 'sentiment' => 'positive', 'score' => 72, 'rep' => 'neutral_mention', 'topic' => 'campaign', 'risk' => 5, 'likes' => 200, 'replies' => 25, 'reposts' => 50, 'views' => 8000],
    ];

    $authors = [
        ['name' => 'أحمد الراشد', 'user' => 'ahmed_rashd', 'followers' => 15000, 'verified' => 1],
        ['name' => 'سارة المحمد', 'user' => 'sara_mohmd', 'followers' => 25000, 'verified' => 1],
        ['name' => 'خالد العتيبي', 'user' => 'khaled_otibi', 'followers' => 8000, 'verified' => 0],
        ['name' => 'نورة السعيد', 'user' => 'noura_saeed', 'followers' => 45000, 'verified' => 1],
        ['name' => 'محمد القحطاني', 'user' => 'mohmd_qahtani', 'followers' => 3200, 'verified' => 0],
        ['name' => 'فاطمة الدوسري', 'user' => 'fatma_dosari', 'followers' => 12000, 'verified' => 0],
        ['name' => 'عبدالله الشمري', 'user' => 'abdullah_shmri', 'followers' => 5000, 'verified' => 0],
        ['name' => 'ريم الحربي', 'user' => 'reem_harbi', 'followers' => 18000, 'verified' => 1],
        ['name' => 'حسام بوتفقة', 'user' => 'hussam_botfqa', 'followers' => 950, 'verified' => 0],
        ['name' => 'ليلى الزهراني', 'user' => 'layla_zahrani', 'followers' => 6700, 'verified' => 0],
    ];

    foreach ($samplePosts as $i => $sp) {
        $author = $authors[$i % count($authors)];
        $postedAt = date('Y-m-d H:i:s', strtotime('-' . rand(1, 168) . ' hours'));

        $postId = $db->insert('posts', [
            'project_id' => $projectId,
            'collection_run_id' => $runId,
            'platform' => 'x_twitter',
            'external_post_id' => 'mock_' . uniqid(),
            'post_url' => 'https://x.com/' . $author['user'] . '/status/mock_' . $i,
            'author_name' => $author['name'],
            'author_username' => $author['user'],
            'author_followers' => $author['followers'],
            'author_verified' => $author['verified'],
            'content_text' => $sp['text'],
            'posted_at' => $postedAt,
            'likes_count' => $sp['likes'],
            'replies_count' => $sp['replies'],
            'reposts_count' => $sp['reposts'],
            'views_count' => $sp['views'],
            'hashtags' => json_encode(['النخبة'], JSON_UNESCAPED_UNICODE),
            'language' => isArabic($sp['text']) ? 'ar' : 'en',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        // Add AI analysis
        $db->insert('post_ai_analysis', [
            'post_id' => $postId,
            'sentiment' => $sp['sentiment'],
            'sentiment_score' => $sp['score'],
            'reputation_label' => $sp['rep'],
            'crisis_flag' => !empty($sp['crisis']) ? 1 : 0,
            'attack_flag' => !empty($sp['attack']) ? 1 : 0,
            'complaint_flag' => !empty($sp['complaint']) ? 1 : 0,
            'sarcasm_flag' => !empty($sp['sarcasm']) ? 1 : 0,
            'topic_label' => $sp['topic'],
            'risk_score' => $sp['risk'],
            'ai_summary' => 'تحليل تلقائي: المنشور يتحدث عن ' . $sp['topic'] . ' بمشاعر ' . ($sp['sentiment'] === 'positive' ? 'إيجابية' : ($sp['sentiment'] === 'negative' ? 'سلبية' : 'محايدة')),
            'ai_keywords' => json_encode(array_slice(explode(' ', $sp['text']), 0, 5), JSON_UNESCAPED_UNICODE),
            'analysis_model' => 'mock-data',
            'analyzed_at' => date('Y-m-d H:i:s')
        ]);
    }

    echo "[+] " . count($samplePosts) . " sample posts with AI analysis created\n";
} else {
    echo "[i] Posts already exist ({$existingPosts} found)\n";
}

// Create sample alerts
$existingAlerts = $db->count('alerts', 'project_id = ?', [$projectId]);
if ($existingAlerts == 0) {
    $alerts = [
        ['type' => 'negative_spike', 'severity' => 'high', 'title' => 'ارتفاع نسبة السلبية', 'desc' => 'نسبة المنشورات السلبية وصلت إلى 47%'],
        ['type' => 'attack_detected', 'severity' => 'critical', 'title' => 'هجوم محتمل مكتشف', 'desc' => 'تم تصنيف 3 منشورات كهدجم خلال 24 ساعة'],
        ['type' => 'crisis_keyword', 'severity' => 'high', 'title' => 'كلمة أزمة مكتشفة: مقاطعة النخبة', 'desc' => 'تكررت كلمة الأزمة 5 مرات خلال 24 ساعة'],
        ['type' => 'complaint_surge', 'severity' => 'medium', 'title' => 'ارتفاع الشكاوى', 'desc' => 'تم رصد 6 شكاوى خلال 24 ساعة'],
        ['type' => 'rumor_detected', 'severity' => 'high', 'title' => 'إشاعة مكتشفة', 'desc' => 'تم رصد إشاعة عن إفلاس الشركة'],
    ];

    foreach ($alerts as $alert) {
        $db->insert('alerts', [
            'project_id' => $projectId,
            'alert_type' => $alert['type'],
            'severity' => $alert['severity'],
            'title' => $alert['title'],
            'description' => $alert['desc'],
            'evidence' => '{}',
            'is_read' => 0,
            'is_resolved' => 0,
            'triggered_at' => date('Y-m-d H:i:s', strtotime('-' . rand(1, 48) . ' hours')),
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }
    echo "[+] " . count($alerts) . " sample alerts created\n";
} else {
    echo "[i] Alerts already exist\n";
}

echo "\n=== Seeding complete! ===\n";
echo "Login: admin / Admin@123456\n";
