<?php

declare(strict_types=1);

namespace App\Services;

use RuntimeException;
use Throwable;

class Router
{
    private static array $routes = [];
    private static array $namedRoutes = [];
    private static ?string $basePath = null;
    private static ?string $currentRoutePath = null;

    public static function getBasePath(): string
    {
        if (self::$basePath !== null) {
            return self::$basePath;
        }

        $configBase = getenv('APP_BASE_PATH') ?: ($_SERVER['APP_BASE_PATH'] ?? null);
        if ($configBase !== null) {
            $normalized = '/' . trim($configBase, '/');
            self::$basePath = $normalized === '/' ? '' : $normalized;
            return self::$basePath;
        }

        $requestUri = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?: '/';
        $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
        $scriptDir = str_replace('\\', '/', dirname($scriptName));

        if ($scriptDir === '/' || $scriptDir === '.' || $scriptDir === '') {
            self::$basePath = '';
            return self::$basePath;
        }

        if (str_starts_with($requestUri, $scriptDir)) {
            self::$basePath = rtrim($scriptDir, '/');
            return self::$basePath;
        }

        if (str_ends_with($scriptDir, '/public')) {
            $parentDir = substr($scriptDir, 0, -7);
            if ($parentDir !== '' && (str_starts_with($requestUri, $parentDir . '/') || $requestUri === $parentDir)) {
                self::$basePath = rtrim($parentDir, '/');
                return self::$basePath;
            }
        }

        self::$basePath = '';
        return self::$basePath;
    }

    public static function getCurrentRoutePath(): string
    {
        return self::$currentRoutePath ?? '/';
    }

    public static function add(string $method, string $path, mixed $handler, array $middlewares = []): void
    {
        $normalizedPath = '/' . trim($path, '/');
        if ($normalizedPath !== '/') {
            $normalizedPath = rtrim($normalizedPath, '/');
        }

        self::$routes[] = [
            'method' => strtoupper($method),
            'path' => $normalizedPath,
            'handler' => $handler,
            'middlewares' => $middlewares,
        ];
    }

    public static function get(string $path, mixed $handler, array $middlewares = []): void
    {
        self::add('GET', $path, $handler, $middlewares);
    }

    public static function post(string $path, mixed $handler, array $middlewares = []): void
    {
        self::add('POST', $path, $handler, $middlewares);
    }

    public static function put(string $path, mixed $handler, array $middlewares = []): void
    {
        self::add('PUT', $path, $handler, $middlewares);
    }

    public static function delete(string $path, mixed $handler, array $middlewares = []): void
    {
        self::add('DELETE', $path, $handler, $middlewares);
    }

    public static function dispatch(string $method, string $uri): void
    {
        Auth::startSession();

        $rawPath = parse_url($uri, PHP_URL_PATH) ?: '/';
        $basePath = self::getBasePath();

        if ($basePath !== '' && ($rawPath === $basePath || str_starts_with($rawPath, $basePath . '/'))) {
            $path = substr($rawPath, strlen($basePath));
            if ($path === '' || $path === false) {
                $path = '/';
            }
        } else {
            $path = $rawPath;
        }

        $path = '/' . trim($path, '/');
        if ($path !== '/') {
            $path = rtrim($path, '/');
        }

        self::$currentRoutePath = $path;

        if ($method === 'HEAD') {
            $method = 'GET';
        }

        // Handle method override from POST form (e.g. _method=PUT)
        if ($method === 'POST' && isset($_POST['_method'])) {
            $method = strtoupper($_POST['_method']);
        }

        foreach (self::$routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<$1>[^/]+)', $route['path']);
            $pattern = '#^' . $pattern . '$#';

            if (preg_match($pattern, $path, $matches)) {
                $params = [];
                foreach ($matches as $key => $value) {
                    if (is_string($key)) {
                        $params[$key] = urldecode($value);
                    }
                }

                // Execute middlewares
                foreach ($route['middlewares'] as $mw) {
                    self::runMiddleware($mw, $path);
                }

                // Execute handler
                self::executeHandler($route['handler'], $params);
                return;
            }
        }

        // Route not found
        self::handleNotFound($path);
    }

    private static function runMiddleware(string $middleware, string $currentPath): void
    {
        $parts = explode(':', $middleware, 2);
        $name = $parts[0];
        $arg = $parts[1] ?? null;

        $isApi = str_starts_with($currentPath, '/api');

        if ($name === 'auth') {
            if (!Auth::check()) {
                if ($isApi) {
                    json_response(['success' => false, 'message' => 'Unauthenticated'], 401);
                } else {
                    $_SESSION['intended_url'] = $_SERVER['REQUEST_URI'] ?? '/';
                    redirect('login');
                }
            }
        } elseif ($name === 'guest') {
            if (Auth::check()) {
                redirect('dashboard');
            }
        } elseif ($name === 'role') {
            $allowedRoles = explode(',', $arg ?? '');
            if (!Auth::hasRole($allowedRoles)) {
                if ($isApi) {
                    json_response(['success' => false, 'message' => 'Akses ditolak (Unauthorized role)'], 403);
                } else {
                    http_response_code(403);
                    view('layout.error', [
                        'code' => 403,
                        'title' => 'Akses Terbatas',
                        'message' => 'Peran pengguna Anda tidak memiliki otorisasi untuk mengakses halaman ini.',
                    ]);
                    exit;
                }
            }
        } elseif ($name === 'permission') {
            if (!Auth::hasPermission($arg)) {
                if ($isApi) {
                    json_response(['success' => false, 'message' => 'Akses ditolak (Missing permission: ' . $arg . ')'], 403);
                } else {
                    http_response_code(403);
                    view('layout.error', [
                        'code' => 403,
                        'title' => 'Akses Terbatas',
                        'message' => 'Anda tidak memiliki hak akses izin (' . htmlspecialchars($arg) . ') untuk tindakan ini.',
                    ]);
                    exit;
                }
            }
        }
    }

    private static function executeHandler(mixed $handler, array $params): void
    {
        try {
            if (is_callable($handler)) {
                call_user_func_array($handler, $params);
            } elseif (is_array($handler) && count($handler) === 2) {
                [$class, $method] = $handler;
                $instance = new $class();
                call_user_func_array([$instance, $method], $params);
            } else {
                throw new RuntimeException("Invalid route handler");
            }
        } catch (Throwable $e) {
            error_log('Router Execution Error: ' . $e->getMessage() . PHP_EOL . $e->getTraceAsString());
            $isApi = str_starts_with(self::getCurrentRoutePath(), '/api');
            if ($isApi) {
                json_response([
                    'success' => false,
                    'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage(),
                ], 500);
            } else {
                http_response_code(500);
                view('layout.error', [
                    'code' => 500,
                    'title' => 'Kesalahan Server',
                    'message' => 'Terjadi kendala pada pemrosesan sistem: ' . $e->getMessage(),
                ]);
                exit;
            }
        }
    }

    private static function handleNotFound(string $path): void
    {
        $isApi = str_starts_with($path, '/api');
        if ($isApi) {
            json_response(['success' => false, 'message' => 'Endpoint tidak ditemukan: ' . $path], 404);
        } else {
            http_response_code(404);
            view('layout.error', [
                'code' => 404,
                'title' => 'Halaman Tidak Ditemukan',
                'message' => 'Halaman atau rute (' . htmlspecialchars($path) . ') yang Anda cari tidak tersedia.',
            ]);
            exit;
        }
    }
}
