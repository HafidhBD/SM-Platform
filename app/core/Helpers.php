<?php
/**
 * Helper Functions
 */

/**
 * Escape HTML output
 */
function e(string $string): string
{
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

/**
 * Format date in Arabic-friendly format
 */
function formatDate(?string $date, string $format = 'Y-m-d H:i'): string
{
    if (!$date) return '—';
    $d = new DateTime($date);
    return $d->format($format);
}

/**
 * Format number with Arabic-friendly display
 */
function formatNumber(int $num): string
{
    if ($num >= 1000000) {
        return round($num / 1000000, 1) . 'M';
    }
    if ($num >= 1000) {
        return round($num / 1000, 1) . 'K';
    }
    return (string) $num;
}

/**
 * Percentage format
 */
function formatPercent(float $val): string
{
    return round($val, 1) . '%';
}

/**
 * Get sentiment label in Arabic
 */
function sentimentLabel(string $sentiment): string
{
    return match ($sentiment) {
        'positive' => 'إيجابي',
        'negative' => 'سلبي',
        'neutral' => 'محايد',
        default => 'غير محدد'
    };
}

/**
 * Get sentiment CSS class
 */
function sentimentClass(string $sentiment): string
{
    return match ($sentiment) {
        'positive' => 'badge-positive',
        'negative' => 'badge-negative',
        'neutral' => 'badge-neutral',
        default => 'badge-neutral'
    };
}

/**
 * Get risk level label in Arabic
 */
function riskLabel(string $level): string
{
    return match ($level) {
        'low' => 'منخفض',
        'medium' => 'متوسط',
        'high' => 'مرتفع',
        'critical' => 'حرج',
        default => 'غير محدد'
    };
}

/**
 * Get risk CSS class
 */
function riskClass(string $level): string
{
    return match ($level) {
        'low' => 'risk-low',
        'medium' => 'risk-medium',
        'high' => 'risk-high',
        'critical' => 'risk-critical',
        default => 'risk-low'
    };
}

/**
 * Get reputation label in Arabic
 */
function reputationLabel(string $label): string
{
    $labels = [
        'praise' => 'إشادة / مدح',
        'complaint' => 'شكوى',
        'attack' => 'هجوم',
        'sarcasm' => 'سخرية',
        'inquiry' => 'استفسار',
        'rumor' => 'إشاعة / اتهام',
        'escalation' => 'تصعيد',
        'neutral_mention' => 'إشارة محايدة',
        'other' => 'أخرى'
    ];
    return $labels[$label] ?? $label;
}

/**
 * Get topic label in Arabic
 */
function topicLabel(string $topic): string
{
    $topics = [
        'service' => 'خدمة',
        'quality' => 'جودة',
        'pricing' => 'أسعار',
        'customer_experience' => 'تجربة عميل',
        'campaign' => 'إعلان / حملة',
        'delay' => 'تأخير',
        'support' => 'دعم فني',
        'competition' => 'منافسة',
        'hiring' => 'توظيف',
        'management' => 'إدارة / قرارات',
        'general' => 'محتوى عام',
        'other' => 'أخرى'
    ];
    return $topics[$topic] ?? $topic;
}

/**
 * Truncate text
 */
function truncate(string $text, int $length = 100): string
{
    if (mb_strlen($text) <= $length) return $text;
    return mb_substr($text, 0, $length) . '...';
}

/**
 * Time ago in Arabic
 */
function timeAgo(string $datetime): string
{
    $now = new DateTime();
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    if ($diff->y > 0) return $diff->y . ' سنة';
    if ($diff->m > 0) return $diff->m . ' شهر';
    if ($diff->d > 0) return $diff->d . ' يوم';
    if ($diff->h > 0) return $diff->h . ' ساعة';
    if ($diff->i > 0) return $diff->i . ' دقيقة';
    return 'الآن';
}

/**
 * Generate random string
 */
function generateToken(int $length = 32): string
{
    return bin2hex(random_bytes($length));
}

/**
 * Check string contains Arabic
 */
function isArabic(string $text): bool
{
    return preg_match('/[\x{0600}-\x{06FF}]/u', $text) > 0;
}

/**
 * Safe JSON decode
 */
function safeJsonDecode(?string $json, $default = null)
{
    if (!$json) return $default;
    $decoded = json_decode($json, true);
    return $decoded ?? $default;
}

/**
 * Flash message helper
 */
function flash(string $key, ?string $message = null): ?string
{
    if ($message !== null) {
        $_SESSION['flash_' . $key] = $message;
        return null;
    }
    $msg = $_SESSION['flash_' . $key] ?? null;
    unset($_SESSION['flash_' . $key]);
    return $msg;
}

/**
 * Get setting value from database
 */
function getSetting(string $key, $default = null)
{
    static $settings = null;
    if ($settings === null) {
        $db = Database::getInstance();
        $rows = $db->query("SELECT setting_key, setting_value FROM settings");
        $settings = [];
        foreach ($rows as $row) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
    }
    return $settings[$key] ?? $default;
}
