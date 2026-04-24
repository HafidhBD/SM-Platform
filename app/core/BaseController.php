<?php
/**
 * Base Controller - All controllers extend this
 */
class BaseController
{
    protected Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Load a view file with data
     */
    protected function view(string $view, array $data = []): void
    {
        $viewFile = VIEWS_PATH . '/' . $view . '.php';
        if (!file_exists($viewFile)) {
            http_response_code(500);
            echo "View not found: {$view}";
            return;
        }
        extract($data);
        require_once $viewFile;
    }

    /**
     * Return JSON response
     */
    protected function json(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * Redirect to URL
     */
    protected function redirect(string $url): void
    {
        header("Location: {$url}");
        exit;
    }

    /**
     * Get POST data
     */
    protected function post(string $key, $default = null)
    {
        return $_POST[$key] ?? $default;
    }

    /**
     * Get GET data
     */
    protected function get(string $key, $default = null)
    {
        return $_GET[$key] ?? $default;
    }

    /**
     * Validate required fields
     */
    protected function validateRequired(array $fields, array $data): array
    {
        $errors = [];
        foreach ($fields as $field => $label) {
            if (!isset($data[$field]) || trim($data[$field]) === '') {
                $errors[] = "{$label} مطلوب";
            }
        }
        return $errors;
    }

    /**
     * Sanitize input
     */
    protected function sanitize(string $input): string
    {
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }

    /**
     * Check if request is AJAX
     */
    protected function isAjax(): bool
    {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) 
            && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }

    /**
     * Require authentication
     */
    protected function requireAuth(): void
    {
        if (!isset($_SESSION['user_id'])) {
            if ($this->isAjax()) {
                $this->json(['error' => 'غير مصرح'], 401);
            } else {
                $this->redirect('/login');
            }
        }
    }

    /**
     * Generate CSRF token
     */
    protected function generateCsrfToken(): string
    {
        $token = bin2hex(random_bytes(32));
        $_SESSION[CSRF_TOKEN_NAME] = $token;
        $_SESSION[CSRF_TOKEN_NAME . '_time'] = time();
        return $token;
    }

    /**
     * Verify CSRF token
     */
    protected function verifyCsrf(): bool
    {
        $token = $_POST[CSRF_TOKEN_NAME] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        $sessionToken = $_SESSION[CSRF_TOKEN_NAME] ?? '';
        $tokenTime = $_SESSION[CSRF_TOKEN_NAME . '_time'] ?? 0;

        if (empty($token) || empty($sessionToken)) {
            return false;
        }
        if (!hash_equals($sessionToken, $token)) {
            return false;
        }
        if (time() - $tokenTime > CSRF_TOKEN_LIFETIME) {
            return false;
        }
        return true;
    }

    /**
     * Log system event
     */
    protected function log(string $type, string $message, array $context = []): void
    {
        $logEntry = [
            'type' => $type,
            'message' => $message,
            'context' => json_encode($context, JSON_UNESCAPED_UNICODE),
            'created_at' => date('Y-m-d H:i:s')
        ];
        $this->db->insert('logs', $logEntry);
    }
}
