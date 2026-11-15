<?php

declare(strict_types=1);

if (!function_exists('app_config')) {
    function app_config(string $key, mixed $default = null): mixed
    {
        static $config = null;
        if ($config === null) {
            $config = require __DIR__ . '/../../config/app.php';
        }
        $parts = explode('.', $key);
        $value = $config;
        foreach ($parts as $part) {
            if (is_array($value) && array_key_exists($part, $value)) {
                $value = $value[$part];
            } else {
                return $default;
            }
        }
        return $value;
    }
}

if (!function_exists('db')) {
    function db(): PDO
    {
        return \App\Services\Database::getConnection();
    }
}

if (!function_exists('format_rupiah')) {
    function format_rupiah(int|float|null $amount, bool $withPrefix = true): string
    {
        $val = (int)($amount ?? 0);
        $formatted = number_format($val, 0, ',', '.');
        return $withPrefix ? 'Rp ' . $formatted : $formatted;
    }
}

if (!function_exists('format_datetime')) {
    function format_datetime(?string $dateStr, string $format = 'd M Y, H:i'): string
    {
        if (empty($dateStr)) {
            return '-';
        }
        try {
            $dt = new DateTime($dateStr, new DateTimeZone('Asia/Jakarta'));
            return $dt->format($format);
        } catch (Exception $e) {
            return $dateStr;
        }
    }
}

if (!function_exists('format_date')) {
    function format_date(?string $dateStr, string $format = 'd M Y'): string
    {
        return format_datetime($dateStr, $format);
    }
}

if (!function_exists('csrf_token')) {
    function csrf_token(): string
    {
        if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
            @session_start();
        }
        if (empty($_SESSION['_csrf_token'])) {
            $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['_csrf_token'] ?? '';
    }
}

if (!function_exists('csrf_field')) {
    function csrf_field(): string
    {
        return '<input type="hidden" name="_csrf_token" value="' . htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') . '">';
    }
}

if (!function_exists('validate_csrf')) {
    function validate_csrf(?string $token): bool
    {
        if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
            @session_start();
        }
        if (empty($token) || empty($_SESSION['_csrf_token'])) {
            return false;
        }
        return hash_equals($_SESSION['_csrf_token'], $token);
    }
}

if (!function_exists('url')) {
    function url(string $path = ''): string
    {
        $base = rtrim(app_config('app.url', ''), '/');
        $path = ltrim($path, '/');
        return $path === '' ? $base . '/' : $base . '/' . $path;
    }
}

if (!function_exists('asset')) {
    function asset(string $path): string
    {
        $version = app_config('app.asset_version', '1.0.0');
        $cleanPath = ltrim($path, '/');
        return url('assets/' . $cleanPath . '?v=' . $version);
    }
}

if (!function_exists('auth_user')) {
    function auth_user(): ?array
    {
        return \App\Services\Auth::user();
    }
}

if (!function_exists('auth_check')) {
    function auth_check(): bool
    {
        return \App\Services\Auth::check();
    }
}

if (!function_exists('has_role')) {
    function has_role(string|array $roles): bool
    {
        return \App\Services\Auth::hasRole($roles);
    }
}

if (!function_exists('has_permission')) {
    function has_permission(string $permission): bool
    {
        return \App\Services\Auth::hasPermission($permission);
    }
}

if (!function_exists('json_response')) {
    function json_response(mixed $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }
}

if (!function_exists('redirect')) {
    function redirect(string $path): void
    {
        header('Location: ' . url($path));
        exit;
    }
}

if (!function_exists('view')) {
    function view(string $viewPath, array $data = []): void
    {
        extract($data);
        $file = __DIR__ . '/../../resources/views/' . str_replace('.', '/', $viewPath) . '.php';
        if (!file_exists($file)) {
            throw new RuntimeException("View file not found: {$viewPath} ({$file})");
        }
        require $file;
    }
}

if (!function_exists('uuid_v4')) {
    function uuid_v4(): string
    {
        $data = random_bytes(16);
        $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);
        $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}

if (!function_exists('audit_log')) {
    function audit_log(string $action, string $entityType, ?int $entityId = null, ?array $oldValues = null, ?array $newValues = null): void
    {
        try {
            $user = auth_user();
            $stmt = db()->prepare("
                INSERT INTO audit_logs (user_id, action, entity_type, entity_id, old_values, new_values, ip_address, user_agent, created_at)
                VALUES (:user_id, :action, :entity_type, :entity_id, :old_values, :new_values, :ip_address, :user_agent, datetime('now', 'localtime'))
            ");
            $stmt->execute([
                ':user_id' => $user['id'] ?? null,
                ':action' => $action,
                ':entity_type' => $entityType,
                ':entity_id' => $entityId,
                ':old_values' => $oldValues ? json_encode($oldValues, JSON_UNESCAPED_UNICODE) : null,
                ':new_values' => $newValues ? json_encode($newValues, JSON_UNESCAPED_UNICODE) : null,
                ':ip_address' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
                ':user_agent' => substr($_SERVER['HTTP_USER_AGENT'] ?? 'CLI', 0, 255),
            ]);
        } catch (Throwable $e) {
            error_log('Failed to write audit log: ' . $e->getMessage());
        }
    }
}
