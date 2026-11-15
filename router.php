<?php

declare(strict_types=1);

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$file = __DIR__ . '/public' . $uri;

if ($uri !== '/' && file_exists($file) && !is_dir($file)) {
    return false; // serve the requested file directly
}

require_once __DIR__ . '/public/index.php';
