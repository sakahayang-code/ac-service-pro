<?php
$user = auth_user() ?? ['name' => 'Admin', 'role_name' => 'admin', 'role_display_name' => 'Administrator'];
$currentRole = $user['role_name'] ?? 'admin';
$currentPath = \App\Services\Router::getCurrentRoutePath();

if (!function_exists('is_active_route')) {
    function is_active_route(string $pattern, string $currentPath): bool {
        if ($pattern === '/' && ($currentPath === '/' || $currentPath === '/dashboard')) return true;
        if ($pattern === '/dashboard' && ($currentPath === '/dashboard' || $currentPath === '/')) return true;
        if ($pattern !== '/' && $pattern !== '/dashboard' && str_starts_with($currentPath, $pattern)) return true;
        return false;
    }
}
?>
<aside class="desktop-sidebar">
    <!-- Brand Header matching reference mockup -->
    <div class="sidebar-brand">
        <div class="sidebar-brand-icon">
            <i data-lucide="snowflake" style="width:36px; height:36px; stroke-width:2.5;"></i>
        </div>
        <div class="sidebar-brand-title">CoolService</div>
        <div class="sidebar-brand-subtitle">Service AC Profesional</div>
    </div>

    <!-- Navigation Menu -->
    <nav class="sidebar-nav">
        <!-- 1. DASHBOARD -->
        <a href="/dashboard" class="nav-link <?= is_active_route('/dashboard', $currentPath) ? 'active' : '' ?>">
            <i data-lucide="layout-dashboard"></i>
            <span>Dashboard</span>
        </a>

        <?php if ($currentRole === 'customer'): ?>
            <a href="/customer-portal/orders" class="nav-link <?= is_active_route('/customer-portal/orders', $currentPath) ? 'active' : '' ?>">
                <i data-lucide="calendar"></i>
                <span>Pesanan Saya</span>
            </a>
            <a href="/customer-portal/book" class="nav-link <?= is_active_route('/customer-portal/book', $currentPath) ? 'active' : '' ?>">
                <i data-lucide="calendar-plus"></i>
                <span>Pesan Servis</span>
            </a>
            <a href="/customer-portal/units" class="nav-link <?= is_active_route('/customer-portal/units', $currentPath) ? 'active' : '' ?>">
                <i data-lucide="airplay"></i>
                <span>Unit AC Saya</span>
            </a>
            <a href="/customer-portal/invoices" class="nav-link <?= is_active_route('/customer-portal/invoices', $currentPath) ? 'active' : '' ?>">
                <i data-lucide="receipt"></i>
                <span>Pembayaran</span>
            </a>
            <a href="/customer-portal/complaints" class="nav-link <?= is_active_route('/customer-portal/complaints', $currentPath) ? 'active' : '' ?>">
                <i data-lucide="message-square-warning"></i>
                <span>Komplain & Garansi</span>
            </a>

        <?php elseif ($currentRole === 'technician'): ?>
            <a href="/technician/tasks" class="nav-link <?= is_active_route('/technician/tasks', $currentPath) ? 'active' : '' ?>">
                <i data-lucide="calendar"></i>
                <span>Pesanan / Tugas</span>
            </a>
            <a href="/technician/history" class="nav-link <?= is_active_route('/technician/history', $currentPath) ? 'active' : '' ?>">
                <i data-lucide="history"></i>
                <span>Riwayat Servis</span>
            </a>
            <a href="/technician/profile" class="nav-link <?= is_active_route('/technician/profile', $currentPath) ? 'active' : '' ?>">
                <i data-lucide="user-check"></i>
                <span>Profil & Komisi</span>
            </a>

        <?php else: ?>
            <!-- OWNER, ADMIN, CS, CASHIER, INVENTORY -->
            <a href="/work-orders" class="nav-link <?= is_active_route('/work-orders', $currentPath) ? 'active' : '' ?>">
                <i data-lucide="calendar"></i>
                <span>Pesanan</span>
            </a>

            <?php if (in_array($currentRole, ['owner', 'admin', 'customer_service'])): ?>
                <a href="/technicians" class="nav-link <?= is_active_route('/technicians', $currentPath) ? 'active' : '' ?>">
                    <i data-lucide="user-check"></i>
                    <span>Teknisi</span>
                </a>
                <a href="/customers" class="nav-link <?= is_active_route('/customers', $currentPath) ? 'active' : '' ?>">
                    <i data-lucide="users"></i>
                    <span>Pelanggan</span>
                </a>
                <a href="/service-catalog" class="nav-link <?= is_active_route('/service-catalog', $currentPath) ? 'active' : '' ?>">
                    <i data-lucide="wrench"></i>
                    <span>Layanan</span>
                </a>
            <?php endif; ?>

            <?php if (in_array($currentRole, ['owner', 'admin', 'cashier'])): ?>
                <a href="/invoices" class="nav-link <?= is_active_route('/invoices', $currentPath) ? 'active' : '' ?>">
                    <i data-lucide="credit-card"></i>
                    <span>Pembayaran</span>
                </a>
            <?php endif; ?>

            <?php if (in_array($currentRole, ['owner', 'admin'])): ?>
                <a href="/reports" class="nav-link <?= is_active_route('/reports', $currentPath) ? 'active' : '' ?>">
                    <i data-lucide="bar-chart-3"></i>
                    <span>Laporan</span>
                </a>
            <?php endif; ?>

            <?php if (in_array($currentRole, ['owner', 'admin', 'inventory'])): ?>
                <a href="/inventory" class="nav-link <?= is_active_route('/inventory', $currentPath) ? 'active' : '' ?>">
                    <i data-lucide="boxes"></i>
                    <span>Inventori</span>
                </a>
            <?php endif; ?>

            <?php if (in_array($currentRole, ['owner', 'admin', 'customer_service'])): ?>
                <a href="/contracts" class="nav-link <?= is_active_route('/contracts', $currentPath) ? 'active' : '' ?>">
                    <i data-lucide="file-signature"></i>
                    <span>Kontrak</span>
                </a>
            <?php endif; ?>

            <?php if ($currentRole === 'owner' || $currentRole === 'admin'): ?>
                <a href="/settings" class="nav-link <?= is_active_route('/settings', $currentPath) ? 'active' : '' ?>">
                    <i data-lucide="settings"></i>
                    <span>Pengaturan</span>
                </a>
            <?php endif; ?>
        <?php endif; ?>
    </nav>

    <!-- Sidebar Bottom Promo Card & Logout Link -->
    <div class="sidebar-footer-box">
        <a href="/logout" class="sidebar-logout-link">
            <i data-lucide="log-out"></i>
            <span>Keluar</span>
        </a>

        <div class="sidebar-promo-card">
            <img src="/assets/images/technician_card.jpg" class="sidebar-promo-img" alt="CoolService AC">
            <div class="sidebar-promo-text">AC Sejuk, Hidup Lebih Nyaman</div>
        </div>
    </div>
</aside>
