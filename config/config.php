<?php
/**
 * Market Intelligence Platform - Configuration
 * 
 * All environment-specific settings should be placed in config/env.php
 * which is NOT committed to version control.
 */

// Prevent direct access
if (!defined('APP_ROOT')) {
    die('Direct access not permitted');
}

// Application
define('APP_NAME', 'Market Intelligence Platform');
define('APP_VERSION', '1.0.0');
define('APP_LANG', 'ar');
define('APP_DIR', 'ltr'); // Direction for admin UI - can be toggled

// Paths
define('APP_ROOT', dirname(__DIR__));
define('PUBLIC_ROOT', APP_ROOT . '/public');
define('VIEWS_PATH', APP_ROOT . '/app/views');
define('STORAGE_PATH', APP_ROOT . '/storage');
define('LOGS_PATH', STORAGE_PATH . '/logs');
define('PROMPTS_PATH', APP_ROOT . '/app/prompts');

// Database defaults (override in env.php)
define('DB_HOST', 'localhost');
define('DB_NAME', 'mi_platform');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Session
define('SESSION_LIFETIME', 7200); // 2 hours
define('SESSION_NAME', 'mi_platform_session');

// Apify defaults (override in env.php)
define('APIFY_API_TOKEN', '');
define('APIFY_ACTOR_ID', 'scraply~x-twitter-posts-search');
define('APIFY_BASE_URL', 'https://api.apify.com/v2');
define('APIFY_MAX_RETRIES', 3);
define('APIFY_RETRY_DELAY', 5); // seconds
define('APIFY_POLL_INTERVAL', 10); // seconds
define('APIFY_DEFAULT_MAX_TWEETS', 50);
define('APIFY_DEFAULT_TIME_WINDOW', 7); // days
define('APIFY_DEFAULT_SEARCH_TYPE', 'latest');

// OpenAI defaults (override in env.php)
define('OPENAI_API_KEY', '');
define('OPENAI_MODEL', 'gpt-4o-mini');
define('OPENAI_BASE_URL', 'https://api.openai.com/v1');
define('OPENAI_MAX_TOKENS', 2000);
define('OPENAI_TEMPERATURE', 0.3);
define('OPENAI_BATCH_SIZE', 20); // posts per analysis batch
define('OPENAI_MAX_RETRIES', 2);

// Alert thresholds
define('ALERT_NEGATIVE_THRESHOLD', 40); // percentage
define('ALERT_VOLUME_SPIKE_PERCENT', 50); // percentage increase
define('ALERT_CRISIS_KEYWORD_THRESHOLD', 5); // count of crisis keywords
define('ALERT_ATTACK_POST_THRESHOLD', 10); // posts classified as attack

// CSRF
define('CSRF_TOKEN_NAME', 'csrf_token');
define('CSRF_TOKEN_LIFETIME', 3600); // 1 hour

// Timezone
date_default_timezone_set('Asia/Riyadh');

// Load environment overrides
$envFile = APP_ROOT . '/config/env.php';
if (file_exists($envFile)) {
    require_once $envFile;
}

// Ensure logs directory exists
if (!is_dir(LOGS_PATH)) {
    mkdir(LOGS_PATH, 0755, true);
}
