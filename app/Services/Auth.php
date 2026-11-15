<?php

declare(strict_types=1);

namespace App\Services;

use PDO;

class Auth
{
    private static ?array $currentUser = null;

    public static function startSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            $sessDir = dirname(__DIR__, 2) . '/storage/sessions';
            if (!is_dir($sessDir)) {
                mkdir($sessDir, 0755, true);
            }
            if (!headers_sent()) {
                session_save_path($sessDir);
                $lifetime = (int)app_config('session.lifetime', 86400 * 7);
                ini_set('session.gc_maxlifetime', (string)$lifetime);
                session_set_cookie_params([
                    'lifetime' => $lifetime,
                    'path' => '/',
                    'httponly' => true,
                    'samesite' => 'Lax',
                ]);
                session_name(app_config('session.name', 'ac_service_pro_session'));
            }
            if (session_status() === PHP_SESSION_NONE) {
                @session_start();
            }
        }
    }

    public static function attempt(string $email, string $password, bool $remember = false): bool
    {
        self::startSession();
        $pdo = Database::getConnection();

        $stmt = $pdo->prepare("
            SELECT u.*, r.name as role_name, r.display_name as role_display_name
            FROM users u
            JOIN roles r ON u.role_id = r.id
            WHERE (u.email = :email OR u.phone = :phone) AND u.deleted_at IS NULL
            LIMIT 1
        ");
        $stmt->execute([
            ':email' => trim($email),
            ':phone' => trim($email),
        ]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            return false;
        }

        if (!password_verify($password, $user['password_hash'])) {
            return false;
        }

        if ($user['status'] !== 'active') {
            return false;
        }

        self::login($user, $remember);
        return true;
    }

    public static function login(array $user, bool $remember = false): void
    {
        self::startSession();
        $pdo = Database::getConnection();

        $_SESSION['user_id'] = $user['id'];
        self::$currentUser = $user;

        // Record last login
        $pdo->prepare("UPDATE users SET last_login_at = datetime('now', 'localtime') WHERE id = :id")
            ->execute([':id' => $user['id']]);

        // Record session
        $sessionToken = bin2hex(random_bytes(32));
        $_SESSION['session_token'] = $sessionToken;

        $expiresAt = date('Y-m-d H:i:s', time() + ($remember ? 86400 * 30 : 86400 * 7));
        $stmtSession = $pdo->prepare("
            INSERT INTO user_sessions (user_id, session_token, ip_address, user_agent, last_activity, expires_at)
            VALUES (:user_id, :session_token, :ip_address, :user_agent, :last_activity, :expires_at)
        ");
        $stmtSession->execute([
            ':user_id' => $user['id'],
            ':session_token' => $sessionToken,
            ':ip_address' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
            ':user_agent' => substr($_SERVER['HTTP_USER_AGENT'] ?? 'Unknown', 0, 255),
            ':last_activity' => time(),
            ':expires_at' => $expiresAt,
        ]);

        audit_log('login', 'users', (int)$user['id'], null, ['email' => $user['email'], 'role' => $user['role_name'] ?? '']);
    }

    public static function loginById(int $userId): bool
    {
        self::startSession();
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            SELECT u.*, r.name as role_name, r.display_name as role_display_name
            FROM users u
            JOIN roles r ON u.role_id = r.id
            WHERE u.id = :id AND u.deleted_at IS NULL
            LIMIT 1
        ");
        $stmt->execute([':id' => $userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            return false;
        }

        self::login($user);
        return true;
    }

    public static function logout(): void
    {
        self::startSession();
        $userId = self::id();
        if ($userId) {
            audit_log('logout', 'users', $userId);
        }

        if (!empty($_SESSION['session_token'])) {
            $pdo = Database::getConnection();
            $pdo->prepare("DELETE FROM user_sessions WHERE session_token = :token")
                ->execute([':token' => $_SESSION['session_token']]);
        }

        self::$currentUser = null;
        if (!headers_sent() && ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        if (session_status() === PHP_SESSION_ACTIVE) {
            @session_destroy();
        }
    }

    public static function user(): ?array
    {
        if (self::$currentUser !== null) {
            return self::$currentUser;
        }

        self::startSession();
        $userId = $_SESSION['user_id'] ?? null;
        if (!$userId) {
            return null;
        }

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            SELECT u.id, u.role_id, u.name, u.email, u.phone, u.avatar, u.status,
                   r.name as role_name, r.display_name as role_display_name
            FROM users u
            JOIN roles r ON u.role_id = r.id
            WHERE u.id = :id AND u.deleted_at IS NULL AND u.status = 'active'
            LIMIT 1
        ");
        $stmt->execute([':id' => $userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            self::$currentUser = null;
            return null;
        }

        // If user is technician, load technician metadata
        if ($user['role_name'] === 'technician') {
            $techStmt = $pdo->prepare("SELECT id, code, rating, total_jobs_done FROM technicians WHERE user_id = :uid LIMIT 1");
            $techStmt->execute([':uid' => $user['id']]);
            $tech = $techStmt->fetch(PDO::FETCH_ASSOC);
            $user['technician_id'] = $tech['id'] ?? null;
            $user['technician_code'] = $tech['code'] ?? null;
            $user['rating'] = $tech['rating'] ?? 5.0;
        }

        // If user is customer, load customer metadata
        if ($user['role_name'] === 'customer') {
            $custStmt = $pdo->prepare("SELECT id, customer_code FROM customers WHERE user_id = :uid LIMIT 1");
            $custStmt->execute([':uid' => $user['id']]);
            $cust = $custStmt->fetch(PDO::FETCH_ASSOC);
            $user['customer_id'] = $cust['id'] ?? null;
            $user['customer_code'] = $cust['customer_code'] ?? null;
        }

        self::$currentUser = $user;
        return self::$currentUser;
    }

    public static function id(): ?int
    {
        $user = self::user();
        return $user ? (int)$user['id'] : null;
    }

    public static function check(): bool
    {
        return self::user() !== null;
    }

    public static function hasRole(string|array $roles): bool
    {
        $user = self::user();
        if (!$user) {
            return false;
        }

        if (is_string($roles)) {
            $roles = [$roles];
        }

        // Owner has access to everything
        if ($user['role_name'] === 'owner') {
            return true;
        }

        return in_array($user['role_name'], $roles, true);
    }

    public static function hasPermission(string $permission): bool
    {
        $user = self::user();
        if (!$user) {
            return false;
        }

        if ($user['role_name'] === 'owner') {
            return true;
        }

        static $permissionsCache = [];
        $userId = $user['id'];

        if (!isset($permissionsCache[$userId])) {
            $pdo = Database::getConnection();
            $stmt = $pdo->prepare("
                SELECT p.code
                FROM role_permissions rp
                JOIN permissions p ON rp.permission_id = p.id
                WHERE rp.role_id = :role_id
            ");
            $stmt->execute([':role_id' => $user['role_id']]);
            $permissionsCache[$userId] = $stmt->fetchAll(PDO::FETCH_COLUMN) ?: [];
        }

        return in_array($permission, $permissionsCache[$userId], true);
    }
}
