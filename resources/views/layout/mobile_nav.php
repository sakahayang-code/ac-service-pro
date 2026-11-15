<?php
$user = auth_user() ?? ['role_name' => 'admin'];
$currentRole = $user['role_name'] ?? 'admin';
$currentPath = \App\Services\Router::getCurrentRoutePath();

// Determine order URL based on role
$orderUrl = '/work-orders';
if ($currentRole === 'customer') {
    $orderUrl = '/customer-portal/orders';
} elseif ($currentRole === 'technician') {
    $orderUrl = '/technician/tasks';
}

$profileUrl = ($currentRole === 'technician') ? '/technician/profile' : '/settings';
$isHome = ($currentPath === '/' || $currentPath === '/dashboard');
$isOrder = (str_starts_with($currentPath, '/work-orders') || str_starts_with($currentPath, '/customer-portal/orders') || str_starts_with($currentPath, '/technician/tasks'));
$isNotif = str_starts_with($currentPath, '/notifications');
$isProfile = (str_starts_with($currentPath, '/settings') || str_starts_with($currentPath, '/technician/profile'));
?>
<nav class="mobile-bottom-nav">
    <!-- 1. BERANDA -->
    <a href="/dashboard" class="mobile-bottom-nav-item <?= $isHome ? 'active' : '' ?>">
        <i data-lucide="home"></i>
        <span>Beranda</span>
    </a>

    <!-- 2. PESANAN -->
    <a href="<?= $orderUrl ?>" class="mobile-bottom-nav-item <?= $isOrder ? 'active' : '' ?>">
        <i data-lucide="calendar"></i>
        <span>Pesanan</span>
    </a>

    <!-- 3. NOTIFIKASI -->
    <a href="/notifications" class="mobile-bottom-nav-item <?= $isNotif ? 'active' : '' ?>">
        <i data-lucide="bell"></i>
        <span class="mobile-nav-badge">3</span>
        <span>Notifikasi</span>
    </a>

    <!-- 4. PROFIL -->
    <a href="<?= $profileUrl ?>" class="mobile-bottom-nav-item <?= $isProfile ? 'active' : '' ?>">
        <i data-lucide="user"></i>
        <span>Profil</span>
    </a>
</nav>
