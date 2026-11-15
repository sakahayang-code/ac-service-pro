<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <title><?= htmlspecialchars($title ?? 'Dashboard') ?> - CoolService</title>
    
    <!-- PWA Meta -->
    <meta name="theme-color" content="#1366d9">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <link rel="manifest" href="/manifest.json">
    <link rel="icon" type="image/png" href="/assets/icons/favicon.png">
    <link rel="apple-touch-icon" href="/assets/icons/icon-192.png">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- CSS -->
    <link rel="stylesheet" href="/assets/css/style.css">
    
    <!-- Lucide Icons & Chart.js -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>window.APP_BASE_URL = '<?= base_path() ?>';</script>
</head>
<body>
<div class="app-container">
    <!-- Desktop Sidebar matching CoolService reference design -->
    <?php require __DIR__ . '/sidebar.php'; ?>

    <!-- Main Wrapper -->
    <div class="main-wrapper">
        <!-- Top Desktop Header (White bar matching mockup) -->
        <header class="top-header">
            <div class="top-header-left">
                <button type="button" class="menu-toggle-btn" aria-label="Toggle Navigation" onclick="document.querySelector('.desktop-sidebar').classList.toggle('show-mobile')">
                    <i data-lucide="menu" style="width:22px; height:22px;"></i>
                </button>
                <h1 class="top-header-title"><?= htmlspecialchars($title ?? 'Dashboard') ?></h1>
            </div>
            
            <div class="top-header-right">
                <!-- Role Switcher for seamless testing -->
                <?php $currentUser = auth_user() ?? ['name' => 'Admin', 'role_name' => 'admin']; ?>
                <div style="display:flex; align-items:center; gap:8px;">
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

                <!-- Notifications with Red Badge '3' matching mockup -->
                <a href="/notifications" class="header-bell-btn" title="Notifikasi">
                    <i data-lucide="bell" style="width:20px; height:20px;"></i>
                    <span class="header-bell-badge">3</span>
                </a>

                <!-- User Profile Pill with Avatar and Dropdown Arrow matching mockup -->
                <a href="/settings" class="header-user-pill" title="Profil & Pengaturan" style="text-decoration:none;">
                    <div class="header-user-avatar">
                        <?= strtoupper(substr($currentUser['name'] ?? 'A', 0, 1)) ?>
                    </div>
                    <span class="header-user-name"><?= htmlspecialchars(explode(' ', $currentUser['name'] ?? 'Admin')[0]) ?></span>
                    <i data-lucide="chevron-down" style="width:16px; height:16px; color:#64748b;"></i>
                </a>
            </div>
        </header>

        <!-- Mobile Header (< 768px - Blue Top Bar matching mockup right phone) -->
        <header class="mobile-header">
            <div class="mobile-brand-box">
                <i data-lucide="snowflake" style="width:26px; height:26px; stroke-width:2.5;"></i>
                <div>
                    <div class="mobile-brand-title">CoolService</div>
                    <div class="mobile-brand-sub">Service AC Profesional</div>
                </div>
            </div>
            
            <div style="display:flex; align-items:center; gap:12px;">
                <select id="mobile-role-switcher" class="role-switcher-select" style="max-width:85px; font-size:0.7rem; padding:4px 6px; background:rgba(255,255,255,0.18); color:#ffffff; border:1px solid rgba(255,255,255,0.3); border-radius:8px;" onchange="window.location.href=(window.APP_BASE_URL || '') + '/auth/switch-role?role='+encodeURIComponent(this.value)">
                    <option value="owner" <?= ($currentUser['role_name'] ?? '') === 'owner' ? 'selected' : '' ?>>Owner</option>
                    <option value="admin" <?= ($currentUser['role_name'] ?? '') === 'admin' ? 'selected' : '' ?>>Admin</option>
                    <option value="customer_service" <?= ($currentUser['role_name'] ?? '') === 'customer_service' ? 'selected' : '' ?>>CS</option>
                    <option value="cashier" <?= ($currentUser['role_name'] ?? '') === 'cashier' ? 'selected' : '' ?>>Kasir</option>
                    <option value="technician" <?= ($currentUser['role_name'] ?? '') === 'technician' ? 'selected' : '' ?>>Teknisi</option>
                    <option value="inventory" <?= ($currentUser['role_name'] ?? '') === 'inventory' ? 'selected' : '' ?>>Gudang</option>
                    <option value="customer" <?= ($currentUser['role_name'] ?? '') === 'customer' ? 'selected' : '' ?>>Pelanggan</option>
                </select>

                <a href="/notifications" style="position:relative; color:#ffffff; display:flex; align-items:center;" title="Notifikasi">
                    <i data-lucide="bell" style="width:22px; height:22px;"></i>
                    <span style="position:absolute; top:-3px; right:-3px; width:15px; height:15px; background:#ef4444; color:#ffffff; font-size:0.6rem; font-weight:800; border-radius:50%; display:flex; align-items:center; justify-content:center; border:2px solid #1366d9;">3</span>
                </a>
            </div>
        </header>

        <!-- Flash Messages -->
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
