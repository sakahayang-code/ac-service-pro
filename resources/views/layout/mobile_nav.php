<?php
$user = auth_user() ?? ['role_name' => 'guest'];
$currentRole = $user['role_name'] ?? 'guest';
$currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?: '/';
?>
<nav class="mobile-bottom-nav">
    <?php if ($currentRole === 'technician'): ?>
        <a href="/dashboard" class="mobile-nav-item <?= $currentPath === '/dashboard' ? 'active' : '' ?>">
            <i data-lucide="layout-dashboard"></i>
            <span>Beranda</span>
        </a>
        <a href="/technician/tasks" class="mobile-nav-item <?= str_starts_with($currentPath, '/technician/tasks') ? 'active' : '' ?>">
            <i data-lucide="wrench"></i>
            <span>Tugas Saya</span>
        </a>
        <a href="/technician/history" class="mobile-nav-item <?= str_starts_with($currentPath, '/technician/history') ? 'active' : '' ?>">
            <i data-lucide="history"></i>
            <span>Riwayat</span>
        </a>
        <a href="/technician/profile" class="mobile-nav-item <?= str_starts_with($currentPath, '/technician/profile') ? 'active' : '' ?>">
            <i data-lucide="user"></i>
            <span>Profil</span>
        </a>

    <?php elseif ($currentRole === 'customer'): ?>
        <a href="/dashboard" class="mobile-nav-item <?= $currentPath === '/dashboard' ? 'active' : '' ?>">
            <i data-lucide="home"></i>
            <span>Beranda</span>
        </a>
        <a href="/customer-portal/book" class="mobile-nav-item <?= str_starts_with($currentPath, '/customer-portal/book') ? 'active' : '' ?>">
            <i data-lucide="calendar-plus"></i>
            <span>Pesan</span>
        </a>
        <a href="/customer-portal/units" class="mobile-nav-item <?= str_starts_with($currentPath, '/customer-portal/units') ? 'active' : '' ?>">
            <i data-lucide="airplay"></i>
            <span>AC Saya</span>
        </a>
        <a href="/customer-portal/orders" class="mobile-nav-item <?= str_starts_with($currentPath, '/customer-portal/orders') ? 'active' : '' ?>">
            <i data-lucide="clipboard-list"></i>
            <span>Status</span>
        </a>
        <a href="/customer-portal/invoices" class="mobile-nav-item <?= str_starts_with($currentPath, '/customer-portal/invoices') ? 'active' : '' ?>">
            <i data-lucide="receipt"></i>
            <span>Faktur</span>
        </a>

    <?php elseif ($currentRole === 'cashier'): ?>
        <a href="/dashboard" class="mobile-nav-item <?= $currentPath === '/dashboard' ? 'active' : '' ?>">
            <i data-lucide="layout-dashboard"></i>
            <span>Beranda</span>
        </a>
        <a href="/invoices" class="mobile-nav-item <?= str_starts_with($currentPath, '/invoices') ? 'active' : '' ?>">
            <i data-lucide="receipt"></i>
            <span>Faktur</span>
        </a>
        <a href="/payments" class="mobile-nav-item <?= str_starts_with($currentPath, '/payments') ? 'active' : '' ?>">
            <i data-lucide="wallet"></i>
            <span>Bayar</span>
        </a>
        <a href="/expenses" class="mobile-nav-item <?= str_starts_with($currentPath, '/expenses') ? 'active' : '' ?>">
            <i data-lucide="arrow-up-right"></i>
            <span>Pengeluaran</span>
        </a>

    <?php elseif ($currentRole === 'inventory'): ?>
        <a href="/dashboard" class="mobile-nav-item <?= $currentPath === '/dashboard' ? 'active' : '' ?>">
            <i data-lucide="layout-dashboard"></i>
            <span>Beranda</span>
        </a>
        <a href="/inventory" class="mobile-nav-item <?= str_starts_with($currentPath, '/inventory') ? 'active' : '' ?>">
            <i data-lucide="boxes"></i>
            <span>Stok</span>
        </a>
        <a href="/purchases" class="mobile-nav-item <?= str_starts_with($currentPath, '/purchases') ? 'active' : '' ?>">
            <i data-lucide="shopping-cart"></i>
            <span>Beli</span>
        </a>
        <a href="/suppliers" class="mobile-nav-item <?= str_starts_with($currentPath, '/suppliers') ? 'active' : '' ?>">
            <i data-lucide="truck"></i>
            <span>Supplier</span>
        </a>

    <?php else: ?>
        <!-- OWNER, ADMIN, CS -->
        <a href="/dashboard" class="mobile-nav-item <?= $currentPath === '/dashboard' ? 'active' : '' ?>">
            <i data-lucide="layout-dashboard"></i>
            <span>Beranda</span>
        </a>
        <a href="/work-orders" class="mobile-nav-item <?= str_starts_with($currentPath, '/work-orders') ? 'active' : '' ?>">
            <i data-lucide="clipboard-check"></i>
            <span>Order</span>
        </a>
        <a href="/customers" class="mobile-nav-item <?= str_starts_with($currentPath, '/customers') ? 'active' : '' ?>">
            <i data-lucide="users"></i>
            <span>Pelanggan</span>
        </a>
        <a href="/invoices" class="mobile-nav-item <?= str_starts_with($currentPath, '/invoices') ? 'active' : '' ?>">
            <i data-lucide="receipt"></i>
            <span>Faktur</span>
        </a>
        <a href="/reports" class="mobile-nav-item <?= str_starts_with($currentPath, '/reports') ? 'active' : '' ?>">
            <i data-lucide="bar-chart-3"></i>
            <span>Laporan</span>
        </a>
    <?php endif; ?>
</nav>
