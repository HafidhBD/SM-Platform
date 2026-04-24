<?php
/**
 * Settings Controller
 */
class SettingsController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->requireAuth();
    }

    public function index(array $params = []): void
    {
        $tab = $this->get('tab', 'apify');
        $csrfToken = $this->generateCsrfToken();

        $this->view('settings/index', [
            'tab' => $tab,
            'csrf_token' => $csrfToken,
            'page_title' => 'الإعدادات'
        ]);
    }

    public function saveApify(array $params = []): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !$this->verifyCsrf()) {
            $this->redirect('/settings');
            return;
        }

        $this->saveSetting('apify_api_token', $this->post('apify_api_token', ''));
        $this->saveSetting('apify_actor_id', $this->post('apify_actor_id', 'scraply~x-twitter-posts-search'));
        $this->saveSetting('apify_max_tweets', $this->post('apify_max_tweets', '50'));
        $this->saveSetting('apify_time_window', $this->post('apify_time_window', '7'));
        $this->saveSetting('apify_search_type', $this->post('apify_search_type', 'latest'));
        $this->saveSetting('apify_use_proxy', $this->post('apify_use_proxy', '1'));

        $this->log('settings', 'تحديث إعدادات Apify');
        flash('success', 'تم حفظ إعدادات Apify');
        $this->redirect('/settings?tab=apify');
    }

    public function saveOpenai(array $params = []): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !$this->verifyCsrf()) {
            $this->redirect('/settings');
            return;
        }

        $this->saveSetting('openai_api_key', $this->post('openai_api_key', ''));
        $this->saveSetting('openai_model', $this->post('openai_model', 'gpt-4o-mini'));
        $this->saveSetting('openai_batch_size', $this->post('openai_batch_size', '20'));

        $this->log('settings', 'تحديث إعدادات OpenAI');
        flash('success', 'تم حفظ إعدادات OpenAI');
        $this->redirect('/settings?tab=openai');
    }

    public function saveAlerts(array $params = []): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !$this->verifyCsrf()) {
            $this->redirect('/settings');
            return;
        }

        $this->saveSetting('alert_negative_threshold', $this->post('alert_negative_threshold', '40'));
        $this->saveSetting('alert_volume_spike_percent', $this->post('alert_volume_spike_percent', '50'));
        $this->saveSetting('alert_crisis_keyword_threshold', $this->post('alert_crisis_keyword_threshold', '5'));
        $this->saveSetting('alert_attack_post_threshold', $this->post('alert_attack_post_threshold', '10'));

        $this->log('settings', 'تحديث إعدادات التنبيهات');
        flash('success', 'تم حفظ إعدادات التنبيهات');
        $this->redirect('/settings?tab=alerts');
    }

    public function saveGeneral(array $params = []): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !$this->verifyCsrf()) {
            $this->redirect('/settings');
            return;
        }

        $this->saveSetting('system_name', $this->post('system_name', 'Market Intelligence Platform'));
        $this->saveSetting('system_language', $this->post('system_language', 'ar'));
        $this->saveSetting('system_timezone', $this->post('system_timezone', 'Asia/Riyadh'));

        $this->log('settings', 'تحديث الإعدادات العامة');
        flash('success', 'تم حفظ الإعدادات العامة');
        $this->redirect('/settings?tab=general');
    }

    public function changePassword(array $params = []): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !$this->verifyCsrf()) {
            $this->redirect('/settings?tab=account');
            return;
        }

        $currentPassword = $this->post('current_password', '');
        $newPassword = $this->post('new_password', '');
        $confirmPassword = $this->post('confirm_password', '');

        $user = Auth::user();
        if (!password_verify($currentPassword, $user['password'])) {
            flash('error', 'كلمة المرور الحالية غير صحيحة');
            $this->redirect('/settings?tab=account');
            return;
        }

        if (strlen($newPassword) < 8) {
            flash('error', 'كلمة المرور الجديدة يجب أن تكون 8 أحرف على الأقل');
            $this->redirect('/settings?tab=account');
            return;
        }

        if ($newPassword !== $confirmPassword) {
            flash('error', 'كلمة المرور الجديدة غير متطابقة');
            $this->redirect('/settings?tab=account');
            return;
        }

        $userModel = new UserModel();
        $userModel->updatePassword(Auth::userId(), $newPassword);
        $this->log('settings', 'تغيير كلمة المرور');
        flash('success', 'تم تغيير كلمة المرور بنجاح');
        $this->redirect('/settings?tab=account');
    }

    /**
     * Health check page
     */
    public function health(array $params = []): void
    {
        $results = [];

        // Database check
        $db = Database::getInstance();
        $results['database'] = [
            'connected' => $db->isConnected(),
            'message' => $db->isConnected() ? 'متصل بنجاح' : 'فشل الاتصال: ' . $db->getLastError()
        ];

        // Apify check
        $apify = new ApifyService();
        $apifyResult = $apify->testConnection();
        $results['apify'] = [
            'connected' => $apifyResult['success'],
            'message' => $apifyResult['message']
        ];

        // OpenAI check
        $openai = new OpenAIService();
        $openaiResult = $openai->testConnection();
        $results['openai'] = [
            'connected' => $openaiResult['success'],
            'message' => $openaiResult['message']
        ];

        // PHP info
        $results['php'] = [
            'version' => PHP_VERSION,
            'extensions' => [
                'pdo_mysql' => extension_loaded('pdo_mysql'),
                'curl' => extension_loaded('curl'),
                'json' => extension_loaded('json'),
                'mbstring' => extension_loaded('mbstring')
            ]
        ];

        $this->view('settings/health', [
            'results' => $results,
            'page_title' => 'فحص النظام'
        ]);
    }

    private function saveSetting(string $key, string $value): void
    {
        $existing = $this->db->queryOne("SELECT id FROM settings WHERE setting_key = ?", [$key]);
        if ($existing) {
            $this->db->update('settings', ['setting_value' => $value], 'id = ?', [$existing['id']]);
        } else {
            $this->db->insert('settings', ['setting_key' => $key, 'setting_value' => $value, 'setting_group' => 'general']);
        }
    }
}
