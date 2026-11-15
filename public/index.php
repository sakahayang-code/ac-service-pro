<?php

declare(strict_types=1);

// AC SERVICE PRO - Integrated Air Conditioning Service Management System
// Entry point

define('APP_START', microtime(true));
define('ROOT_PATH', dirname(__DIR__));

require_once ROOT_PATH . '/vendor/autoload.php';

date_default_timezone_set(app_config('timezone', 'Asia/Jakarta'));

$sessionDir = ROOT_PATH . '/storage/sessions';
if (!is_dir($sessionDir)) {
    mkdir($sessionDir, 0755, true);
}
session_save_path($sessionDir);

if (app_config('debug', false)) {
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
    error_reporting(0);
}

// Ensure database exists; if not run initial migrations automatically
try {
    $dbPath = app_config('database.path');
    if (!file_exists($dbPath)) {
        $runner = new \App\Services\MigrationRunner();
        $runner->run();
        $seeder = new \Database\Seeds\DatabaseSeeder();
        $seeder->run();
    }
} catch (Throwable $e) {
    error_log('Database auto-initialization error: ' . $e->getMessage());
}

// Load routes
require_once ROOT_PATH . '/routes/web.php';
require_once ROOT_PATH . '/routes/api.php';

// Dispatch request
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$uri = $_SERVER['REQUEST_URI'] ?? '/';

\App\Services\Router::dispatch($method, $uri);
