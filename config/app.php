<?php

declare(strict_types=1);

$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443) ? 'https://' : 'http://';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost:8000';
$baseUrl = $protocol . $host;

return [
    'name' => 'AC Service Pro',
    'app_title' => 'AC SERVICE PRO - Integrated AC Service Management',
    'version' => '1.0.0',
    'asset_version' => '1.0.0',
    'env' => 'production',
    'debug' => true,
    'timezone' => 'Asia/Jakarta',
    'currency' => 'IDR',
    'currency_symbol' => 'Rp',
    'url' => $baseUrl,
    'base_dir' => dirname(__DIR__),
    'storage_dir' => dirname(__DIR__) . '/storage',
    'database' => [
        'path' => dirname(__DIR__) . '/storage/database/ac_service_pro.db',
        'timeout' => 5000,
        'busy_timeout' => 5000,
    ],
    'session' => [
        'name' => 'ac_service_pro_session',
        'lifetime' => 86400 * 7, // 7 days
    ],
    'company' => [
        'name' => 'PT AC Service Pro Indonesia',
        'tagline' => 'Solusi Cepat, Bersih & Profesional Servis AC',
        'address' => 'Jl. Merdeka Sejahtera No. 88, Jakarta Selatan',
        'phone' => '+62 812-3456-7890',
        'whatsapp' => '+62 812-3456-7890',
        'email' => 'admin@acservicepro.id',
        'website' => 'https://acservicepro.id',
    ],
];
