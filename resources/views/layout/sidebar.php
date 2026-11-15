<?php
$user = auth_user() ?? ['name' => 'Tamu', 'role_name' => 'guest', 'role_display_name' => 'Tamu'];
$currentRole = $user['role_name'] ?? 'guest';
$currentPath = \App\Services\Router::getCurrentRoutePath();

if (!function_exists('is_active_route')) {
    function is_active_route(string $pattern, string $currentPath): bool {
        if ($pattern === '/' && $currentPath === '/') return true;
        if ($pattern !== '/' && str_starts_with($currentPath, $pattern)) return true;
        return false;
    }
}
?>
<aside class="desktop-sidebar">
    <div class="sidebar-brand">
        <div class="sidebar-brand-logo">AC</div>
        <div class="sidebar-brand-text">
            <h2>AC SERVICE PRO</h2>
            <span>Management System</span>
        </div>
    </div>

    <nav class="sidebar-nav">
        <!-- 1. DASHBOARD -->
        <a href="/dashboard" class="nav-link <?= is_active_route('/dashboard', $currentPath) ? 'active' : '' ?>">
            <i data-lucide="layout-dashboard"></i>
            <span>Dashboard</span>
        </a>

        <?php if ($currentRole === 'customer'): ?>
            <!-- MENU PORTAL PELANGGAN -->
            <div class="nav-section-title">Layanan Mandiri</div>
            <a href="/customer-portal/book" class="nav-link <?= is_active_route('/customer-portal/book', $currentPath) ? 'active' : '' ?>">
                <i data-lucide="calendar-plus"></i>
                <span>Pesan Servis Baru</span>
            </a>
            <a href="/customer-portal/units" class="nav-link <?= is_active_route('/customer-portal/units', $currentPath) ? 'active' : '' ?>">
                <i data-lucide="airplay"></i>
                <span>Unit AC Saya</span>
            </a>
            <a href="/customer-portal/orders" class="nav-link <?= is_active_route('/customer-portal/orders', $currentPath) ? 'active' : '' ?>">
                <i data-lucide="clipboard-list"></i>
                <span>Status Pesanan</span>
            </a>
            <a href="/customer-portal/invoices" class="nav-link <?= is_active_route('/customer-portal/invoices', $currentPath) ? 'active' : '' ?>">
                <i data-lucide="receipt"></i>
                <span>Faktur & Pembayaran</span>
            </a>
            <a href="/customer-portal/complaints" class="nav-link <?= is_active_route('/customer-portal/complaints', $currentPath) ? 'active' : '' ?>">
                <i data-lucide="message-square-warning"></i>
                <span>Komplain & Garansi</span>
            </a>

        <?php elseif ($currentRole === 'technician'): ?>
            <!-- MENU KHUSUS TEKNISI -->
            <div class="nav-section-title">Pengerjaan Lapangan</div>
            <a href="/technician/tasks" class="nav-link <?= is_active_route('/technician/tasks', $currentPath) ? 'active' : '' ?>">
                <i data-lucide="wrench"></i>
                <span>Tugas Saya</span>
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
            <!-- OPERASIONAL UMUM (OWNER, ADMIN, CS, KASIR, GUDANG) -->
            <?php if (in_array($currentRole, ['owner', 'admin', 'customer_service'])): ?>
                <div class="nav-section-title">Operasional Servis</div>
                <a href="/work-orders" class="nav-link <?= is_active_route('/work-orders', $currentPath) ? 'active' : '' ?>">
                    <i data-lucide="clipboard-check"></i>
                    <span>Work Order (WO)</span>
                </a>
                <a href="/customers" class="nav-link <?= is_active_route('/customers', $currentPath) ? 'active' : '' ?>">
                    <i data-lucide="users"></i>
                    <span>Pelanggan</span>
                </a>
                <a href="/ac-units" class="nav-link <?= is_active_route('/ac-units', $currentPath) ? 'active' : '' ?>">
                    <i data-lucide="snowflake"></i>
                    <span>Data Unit AC</span>
                </a>
                <a href="/technicians" class="nav-link <?= is_active_route('/technicians', $currentPath) ? 'active' : '' ?>">
                    <i data-lucide="user-cog"></i>
                    <span>Teknisi & Jadwal</span>
                </a>
                <a href="/service-catalog" class="nav-link <?= is_active_route('/service-catalog', $currentPath) ? 'active' : '' ?>">
                    <i data-lucide="tag"></i>
                    <span>Katalog & Harga</span>
                </a>
                <a href="/contracts" class="nav-link <?= is_active_route('/contracts', $currentPath) ? 'active' : '' ?>">
                    <i data-lucide="file-signature"></i>
                    <span>Kontrak Maintenance</span>
                </a>
            <?php endif; ?>

            <?php if (in_array($currentRole, ['owner', 'admin', 'inventory'])): ?>
                <div class="nav-section-title">Inventori & Logistik</div>
                <a href="/inventory" class="nav-link <?= is_active_route('/inventory', $currentPath) ? 'active' : '' ?>">
                    <i data-lucide="boxes"></i>
                    <span>Stok Sparepart</span>
                </a>
                <a href="/purchases" class="nav-link <?= is_active_route('/purchases', $currentPath) ? 'active' : '' ?>">
                    <i data-lucide="shopping-cart"></i>
                    <span>Pembelian (PO)</span>
                </a>
                <a href="/suppliers" class="nav-link <?= is_active_route('/suppliers', $currentPath) ? 'active' : '' ?>">
                    <i data-lucide="truck"></i>
                    <span>Pemasok / Supplier</span>
                </a>
            <?php endif; ?>

            <?php if (in_array($currentRole, ['owner', 'admin', 'cashier'])): ?>
                <div class="nav-section-title">Kasir & Keuangan</div>
                <a href="/invoices" class="nav-link <?= is_active_route('/invoices', $currentPath) ? 'active' : '' ?>">
                    <i data-lucide="receipt"></i>
                    <span>Faktur / Invoice</span>
                </a>
                <a href="/payments" class="nav-link <?= is_active_route('/payments', $currentPath) ? 'active' : '' ?>">
                    <i data-lucide="wallet"></i>
                    <span>Pembayaran</span>
                </a>
                <a href="/expenses" class="nav-link <?= is_active_route('/expenses', $currentPath) ? 'active' : '' ?>">
                    <i data-lucide="arrow-up-right"></i>
                    <span>Pengeluaran Kas</span>
                </a>
                <a href="/finance" class="nav-link <?= is_active_route('/finance', $currentPath) ? 'active' : '' ?>">
                    <i data-lucide="landmark"></i>
                    <span>Buku Kas & Rekening</span>
                </a>
            <?php endif; ?>

            <?php if (in_array($currentRole, ['owner', 'admin'])): ?>
                <div class="nav-section-title">Laporan & Analitik</div>
                <a href="/reports" class="nav-link <?= is_active_route('/reports', $currentPath) ? 'active' : '' ?>">
                    <i data-lucide="bar-chart-3"></i>
                    <span>Laporan Usaha</span>
                </a>
            <?php endif; ?>

            <?php if ($currentRole === 'owner'): ?>
                <div class="nav-section-title">Sistem & Master</div>
                <a href="/settings" class="nav-link <?= is_active_route('/settings', $currentPath) ? 'active' : '' ?>">
                    <i data-lucide="settings"></i>
                    <span>Pengaturan Usaha</span>
                </a>
                <a href="/users" class="nav-link <?= is_active_route('/users', $currentPath) ? 'active' : '' ?>">
                    <i data-lucide="shield-check"></i>
                    <span>Pengguna & Hak Akses</span>
                </a>
            <?php endif; ?>
        <?php endif; ?>
    </nav>

    <div class="sidebar-user">
        <div class="user-avatar"><?= strtoupper(substr($user['name'] ?? 'U', 0, 1)) ?></div>
        <div class="user-info">
            <div class="user-name"><?= htmlspecialchars($user['name'] ?? 'User') ?></div>
            <span class="user-role-badge"><?= htmlspecialchars($user['role_display_name'] ?? $currentRole) ?></span>
        </div>
    </div>
</aside>
