<?php
/**
 * Session & Authentication Manager
 */
class Auth
{
    /**
     * Start secure session
     */
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            ini_set('session.cookie_httponly', 1);
            ini_set('session.cookie_samesite', 'Strict');
            session_name(SESSION_NAME);
            session_start();
        }
    }

    /**
     * Attempt login
     */
    public static function attempt(string $username, string $password): bool
    {
        $db = Database::getInstance();
        $user = $db->queryOne(
            "SELECT * FROM users WHERE username = ? AND deleted_at IS NULL",
            [$username]
        );

        if (!$user) {
            return false;
        }

        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['display_name'] = $user['display_name'];
            $_SESSION['login_time'] = time();
            
            // Update last login
            $db->update('users', ['last_login' => date('Y-m-d H:i:s')], 'id = ?', [$user['id']]);
            
            return true;
        }

        return false;
    }

    /**
     * Check if user is logged in
     */
    public static function check(): bool
    {
        return isset($_SESSION['user_id']);
    }

    /**
     * Get current user ID
     */
    public static function userId(): ?int
    {
        return $_SESSION['user_id'] ?? null;
    }

    /**
     * Get current user data
     */
    public static function user(): ?array
    {
        if (!self::check()) {
            return null;
        }
        $db = Database::getInstance();
        return $db->queryOne("SELECT * FROM users WHERE id = ?", [$_SESSION['user_id']]);
    }

    /**
     * Logout
     */
    public static function logout(): void
    {
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();
    }

    /**
     * Check session expiry
     */
    public static function checkExpiry(): void
    {
        if (isset($_SESSION['login_time'])) {
            if (time() - $_SESSION['login_time'] > SESSION_LIFETIME) {
                self::logout();
                header('Location: /login?expired=1');
                exit;
            }
        }
    }

    /**
     * Create initial admin user
     */
    public static function createAdmin(): int
    {
        $db = Database::getInstance();
        $existing = $db->queryOne("SELECT id FROM users WHERE username = ?", [ADMIN_USERNAME]);
        if ($existing) {
            return $existing['id'];
        }
        return $db->insert('users', [
            'username' => ADMIN_USERNAME,
            'password' => password_hash(ADMIN_PASSWORD, PASSWORD_DEFAULT),
            'display_name' => 'مدير النظام',
            'email' => ADMIN_EMAIL,
            'role' => 'admin',
            'is_active' => 1,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }
}
