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
    
    <!-- Lucide Icons (Local + CDN Fallback) -->
    <script src="/assets/js/lucide.min.js"></script>
    <script>if (typeof lucide === 'undefined') { document.write('<script src="https://unpkg.com/lucide@latest"><\/script>'); }</script>

    <!-- Chart.js (Local + CDN Fallback) -->
    <script src="/assets/js/chart.min.js"></script>
    <script>if (typeof Chart === 'undefined') { document.write('<script src="https://cdn.jsdelivr.net/npm/chart.js"><\/script>'); }</script>
    <script>window.APP_BASE_URL = '<?= base_path() ?>';</script>
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
                <span id="network-status-badge" class="badge badge-green" style="font-size:0.75rem;">Online</span>

                <!-- Demo Role Switcher -->
                <?php $currentUser = auth_user(); ?>
                <div style="display:flex; align-items:center; gap:8px;">
                    <span style="font-size:0.75rem; color:var(--text-muted); font-weight:600;">Peran:</span>
                    <select id="quick-role-switcher" class="role-switcher-select" onchange="window.location.href=(window.APP_BASE_URL || '') + '/auth/switch-role?role='+encodeURIComponent(this.value)">
                        <option value="owner" <?= ($currentUser['role_name'] ?? '') === 'owner' ? 'selected' : '' ?>>👑 Owner</option>
                        <option value="admin" <?= ($currentUser['role_name'] ?? '') === 'admin' ? 'selected' : '' ?>>⚙️ Admin</option>
                        <option value="customer_service" <?= ($currentUser['role_name'] ?? '') === 'customer_service' ? 'selected' : '' ?>>🎧 CS</option>
                        <option value="cashier" <?= ($currentUser['role_name'] ?? '') === 'cashier' ? 'selected' : '' ?>>💰 Kasir</option>
                        <option value="technician" <?= ($currentUser['role_name'] ?? '') === 'technician' ? 'selected' : '' ?>>🔧 Teknisi</option>
                        <option value="inventory" <?= ($currentUser['role_name'] ?? '') === 'inventory' ? 'selected' : '' ?>>📦 Gudang</option>
                        <option value="customer" <?= ($currentUser['role_name'] ?? '') === 'customer' ? 'selected' : '' ?>>👤 Pelanggan</option>
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
                <div class="sidebar-brand-logo" style="width:30px; height:30px; font-size:0.85rem; border-radius:8px;">AC</div>
                <div style="min-width:0; overflow:hidden;">
                    <h2><?= htmlspecialchars($title ?? 'AC Service Pro') ?></h2>
                </div>
            </div>
            <div style="display:flex; align-items:center; gap:6px; flex-shrink:0;">
                <select id="mobile-role-switcher" class="role-switcher-select" style="max-width:85px; font-size:0.7rem; padding:4px 6px; background:rgba(255,255,255,0.15); color:#ffffff; border-color:rgba(255,255,255,0.25);" onchange="window.location.href=(window.APP_BASE_URL || '') + '/auth/switch-role?role='+encodeURIComponent(this.value)">
                    <option value="owner" <?= ($currentUser['role_name'] ?? '') === 'owner' ? 'selected' : '' ?>>Owner</option>
                    <option value="admin" <?= ($currentUser['role_name'] ?? '') === 'admin' ? 'selected' : '' ?>>Admin</option>
                    <option value="customer_service" <?= ($currentUser['role_name'] ?? '') === 'customer_service' ? 'selected' : '' ?>>CS</option>
                    <option value="cashier" <?= ($currentUser['role_name'] ?? '') === 'cashier' ? 'selected' : '' ?>>Kasir</option>
                    <option value="technician" <?= ($currentUser['role_name'] ?? '') === 'technician' ? 'selected' : '' ?>>Teknisi</option>
                    <option value="inventory" <?= ($currentUser['role_name'] ?? '') === 'inventory' ? 'selected' : '' ?>>Gudang</option>
                    <option value="customer" <?= ($currentUser['role_name'] ?? '') === 'customer' ? 'selected' : '' ?>>Pelanggan</option>
                </select>
                <a href="/logout" style="color:#ffffff; opacity:0.9; display:flex; align-items:center; padding:4px;" title="Keluar">
                    <i data-lucide="log-out" style="width:18px; height:18px;"></i>
                </a>
            </div>
        </header>

        <!-- PWA Install Banner (Mobile Touch) -->
        <div id="pwa-install-banner" style="display:none; background:var(--primary-light); color:#ffffff; padding:10px 14px; align-items:center; justify-content:space-between; font-size:0.85rem; flex-wrap:wrap; gap:8px;">
            <div style="display:flex; align-items:center; gap:8px; min-width:0; flex:1;">
                <i data-lucide="download" style="width:18px; height:18px; color:var(--accent); flex-shrink:0;"></i>
                <span style="font-size:0.8rem;">Pasang AC Service Pro di HP Anda?</span>
            </div>
            <button id="pwa-install-btn" class="btn btn-accent btn-sm" style="padding:4px 12px; font-size:0.75rem;">Pasang</button>
        </div>

        <!-- Main Body -->
        <main class="content-body">
            <?php if (!empty($_SESSION['flash_success'])): ?>
                <div class="card" style="background:#f0fdf4; border-color:#bbf7d0; color:#166534; padding:12px 16px; display:flex; align-items:center; justify-content:space-between; margin-bottom:16px;">
                    <div style="display:flex; align-items:center; gap:8px; min-width:0; overflow:hidden;">
                        <i data-lucide="check-circle" style="width:20px; height:20px; flex-shrink:0;"></i>
                        <span style="word-break:break-word;"><?= htmlspecialchars($_SESSION['flash_success']) ?></span>
                    </div>
                    <button type="button" onclick="this.parentElement.remove()" style="background:none; border:none; cursor:pointer; color:#166534; font-size:18px; flex-shrink:0; margin-left:8px;">&times;</button>
                </div>
                <?php unset($_SESSION['flash_success']); ?>
            <?php endif; ?>

            <?php if (!empty($_SESSION['flash_error'])): ?>
                <div class="card" style="background:#fef2f2; border-color:#fecaca; color:#991b1b; padding:12px 16px; display:flex; align-items:center; justify-content:space-between; margin-bottom:16px;">
                    <div style="display:flex; align-items:center; gap:8px; min-width:0; overflow:hidden;">
                        <i data-lucide="alert-circle" style="width:20px; height:20px; flex-shrink:0;"></i>
                        <span style="word-break:break-word;"><?= htmlspecialchars($_SESSION['flash_error']) ?></span>
                    </div>
                    <button type="button" onclick="this.parentElement.remove()" style="background:none; border:none; cursor:pointer; color:#991b1b; font-size:18px; flex-shrink:0; margin-left:8px;">&times;</button>
                </div>
                <?php unset($_SESSION['flash_error']); ?>
            <?php endif; ?>
