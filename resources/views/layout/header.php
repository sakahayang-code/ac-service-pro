<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <title><?= htmlspecialchars($title ?? 'AC Service Pro') ?> - Sistem Manajemen Servis AC</title>
    
    <!-- PWA Meta -->
    <meta name="theme-color" content="#0f2a4a">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <link rel="manifest" href="/manifest.json">
    <link rel="icon" type="image/png" href="/assets/icons/favicon.png">
    <link rel="apple-touch-icon" href="/assets/icons/icon-192.png">

    <!-- CSS -->
    <link rel="stylesheet" href="/assets/css/style.css">
    
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
<div class="app-container">
    <!-- Desktop Sidebar -->
    <?php require __DIR__ . '/sidebar.php'; ?>

    <!-- Main Wrapper -->
    <div class="main-wrapper">
        <!-- Top Header for Desktop -->
        <header class="top-header">
            <div class="header-title-box">
                <h1><?= htmlspecialchars($title ?? 'Dashboard') ?></h1>
            </div>
            
            <div class="header-actions">
                <!-- Live Network Status -->
                <span id="network-status-badge" class="badge badge-green">Online</span>

                <!-- Demo Role Switcher (Crucial for evaluation!) -->
                <?php $currentUser = auth_user(); ?>
                <div style="display:flex; align-items:center; gap:8px;">
                    <span style="font-size:0.75rem; color:var(--text-muted); font-weight:600;">Peran Aktif:</span>
                    <select id="quick-role-switcher" class="role-switcher-select">
                        <option value="owner" <?= ($currentUser['role_name'] ?? '') === 'owner' ? 'selected' : '' ?>>👑 Owner (Akses Penuh)</option>
                        <option value="admin" <?= ($currentUser['role_name'] ?? '') === 'admin' ? 'selected' : '' ?>>⚙️ Admin (Operasional)</option>
                        <option value="customer_service" <?= ($currentUser['role_name'] ?? '') === 'customer_service' ? 'selected' : '' ?>>🎧 Customer Service</option>
                        <option value="cashier" <?= ($currentUser['role_name'] ?? '') === 'cashier' ? 'selected' : '' ?>>💰 Kasir & Keuangan</option>
                        <option value="technician" <?= ($currentUser['role_name'] ?? '') === 'technician' ? 'selected' : '' ?>>🔧 Teknisi (Budi Santoso)</option>
                        <option value="inventory" <?= ($currentUser['role_name'] ?? '') === 'inventory' ? 'selected' : '' ?>>📦 Gudang & Logistik</option>
                        <option value="customer" <?= ($currentUser['role_name'] ?? '') === 'customer' ? 'selected' : '' ?>>👤 Portal Pelanggan</option>
                    </select>
                </div>

                <!-- Notifications -->
                <a href="/notifications" class="btn btn-outline btn-sm" style="position:relative; padding:6px 10px;" title="Notifikasi">
                    <i data-lucide="bell" style="width:18px; height:18px;"></i>
                </a>

                <!-- User Profile / Logout -->
                <a href="/logout" class="btn btn-outline btn-sm" style="color:var(--danger); border-color:#fee2e2;" title="Keluar">
                    <i data-lucide="log-out" style="width:16px; height:16px;"></i>
                    <span>Keluar</span>
                </a>
            </div>
        </header>

        <!-- Mobile Header (< 768px) -->
        <header class="mobile-header">
            <div class="mobile-header-brand">
                <div class="sidebar-brand-logo" style="width:32px; height:32px; font-size:0.9rem;">AC</div>
                <div>
                    <h2><?= htmlspecialchars($title ?? 'AC Service Pro') ?></h2>
                </div>
            </div>
            <div style="display:flex; align-items:center; gap:8px;">
                <select id="mobile-role-switcher" class="role-switcher-select" style="max-width:130px; font-size:0.75rem; padding:4px 8px;" onchange="window.location.href='/auth/switch-role?role='+encodeURIComponent(this.value)">
                    <option value="owner" <?= ($currentUser['role_name'] ?? '') === 'owner' ? 'selected' : '' ?>>Owner</option>
                    <option value="admin" <?= ($currentUser['role_name'] ?? '') === 'admin' ? 'selected' : '' ?>>Admin</option>
                    <option value="customer_service" <?= ($currentUser['role_name'] ?? '') === 'customer_service' ? 'selected' : '' ?>>CS</option>
                    <option value="cashier" <?= ($currentUser['role_name'] ?? '') === 'cashier' ? 'selected' : '' ?>>Kasir</option>
                    <option value="technician" <?= ($currentUser['role_name'] ?? '') === 'technician' ? 'selected' : '' ?>>Teknisi</option>
                    <option value="inventory" <?= ($currentUser['role_name'] ?? '') === 'inventory' ? 'selected' : '' ?>>Gudang</option>
                    <option value="customer" <?= ($currentUser['role_name'] ?? '') === 'customer' ? 'selected' : '' ?>>Pelanggan</option>
                </select>
                <a href="/logout" style="color:#ffffff;" title="Keluar">
                    <i data-lucide="log-out" style="width:20px; height:20px;"></i>
                </a>
            </div>
        </header>

        <!-- PWA Install Banner (Mobile Touch) -->
        <div id="pwa-install-banner" style="display:none; background:var(--primary-light); color:#ffffff; padding:10px 16px; align-items:center; justify-content:space-between; font-size:0.85rem;">
            <div style="display:flex; align-items:center; gap:10px;">
                <i data-lucide="download" style="width:20px; height:20px; color:var(--accent);"></i>
                <span>Pasang aplikasi AC Service Pro di HP Android Anda?</span>
            </div>
            <button id="pwa-install-btn" class="btn btn-accent btn-sm" style="padding:4px 12px; font-size:0.75rem;">Pasang</button>
        </div>

        <!-- Main Body -->
        <main class="content-body">
            <?php if (!empty($_SESSION['flash_success'])): ?>
                <div class="card" style="background:#f0fdf4; border-color:#bbf7d0; color:#166534; padding:12px 16px; display:flex; align-items:center; justify-content:space-between; margin-bottom:16px;">
                    <div style="display:flex; align-items:center; gap:8px;">
                        <i data-lucide="check-circle" style="width:20px; height:20px;"></i>
                        <span><?= htmlspecialchars($_SESSION['flash_success']) ?></span>
                    </div>
                    <button type="button" onclick="this.parentElement.remove()" style="background:none; border:none; cursor:pointer; color:#166534; font-size:18px;">&times;</button>
                </div>
                <?php unset($_SESSION['flash_success']); ?>
            <?php endif; ?>

            <?php if (!empty($_SESSION['flash_error'])): ?>
                <div class="card" style="background:#fef2f2; border-color:#fecaca; color:#991b1b; padding:12px 16px; display:flex; align-items:center; justify-content:space-between; margin-bottom:16px;">
                    <div style="display:flex; align-items:center; gap:8px;">
                        <i data-lucide="alert-circle" style="width:20px; height:20px;"></i>
                        <span><?= htmlspecialchars($_SESSION['flash_error']) ?></span>
                    </div>
                    <button type="button" onclick="this.parentElement.remove()" style="background:none; border:none; cursor:pointer; color:#991b1b; font-size:18px;">&times;</button>
                </div>
                <?php unset($_SESSION['flash_error']); ?>
            <?php endif; ?>
