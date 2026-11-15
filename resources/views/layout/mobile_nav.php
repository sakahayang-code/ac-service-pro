<?php
$user = auth_user() ?? ['role_name' => 'guest'];
$currentRole = $user['role_name'] ?? 'guest';
$currentPath = \App\Services\Router::getCurrentRoutePath();
?>
<nav class="mobile-bottom-nav">
    <?php if ($currentRole === 'technician'): ?>
        <a href="<?= url('dashboard') ?>" class="mobile-nav-item <?= $currentPath === '/dashboard' ? 'active' : '' ?>">
            <i data-lucide="layout-dashboard"></i>
            <span>Beranda</span>
        </a>
        <a href="<?= url('technician/tasks') ?>" class="mobile-nav-item <?= str_starts_with($currentPath, '/technician/tasks') ? 'active' : '' ?>">
            <i data-lucide="wrench"></i>
            <span>Tugas</span>
        </a>
        <a href="<?= url('technician/history') ?>" class="mobile-nav-item <?= str_starts_with($currentPath, '/technician/history') ? 'active' : '' ?>">
            <i data-lucide="history"></i>
            <span>Riwayat</span>
        </a>
        <a href="<?= url('technician/profile') ?>" class="mobile-nav-item <?= str_starts_with($currentPath, '/technician/profile') ? 'active' : '' ?>">
            <i data-lucide="user"></i>
            <span>Profil</span>
        </a>
        <a href="<?= url('menu') ?>" class="mobile-nav-item <?= $currentPath === '/menu' ? 'active' : '' ?>" aria-label="Daftar Menu">
            <i data-lucide="menu"></i>
            <span>Menu</span>
        </a>

    <?php elseif ($currentRole === 'customer'): ?>
        <a href="<?= url('dashboard') ?>" class="mobile-nav-item <?= $currentPath === '/dashboard' ? 'active' : '' ?>">
            <i data-lucide="home"></i>
            <span>Beranda</span>
        </a>
        <a href="<?= url('customer-portal/book') ?>" class="mobile-nav-item <?= str_starts_with($currentPath, '/customer-portal/book') ? 'active' : '' ?>">
            <i data-lucide="calendar-plus"></i>
            <span>Pesan</span>
        </a>
        <a href="<?= url('customer-portal/orders') ?>" class="mobile-nav-item <?= str_starts_with($currentPath, '/customer-portal/orders') ? 'active' : '' ?>">
            <i data-lucide="clipboard-list"></i>
            <span>Status</span>
        </a>
        <a href="<?= url('customer-portal/invoices') ?>" class="mobile-nav-item <?= str_starts_with($currentPath, '/customer-portal/invoices') ? 'active' : '' ?>">
            <i data-lucide="receipt"></i>
            <span>Faktur</span>
        </a>
        <a href="<?= url('menu') ?>" class="mobile-nav-item <?= $currentPath === '/menu' ? 'active' : '' ?>" aria-label="Daftar Menu">
            <i data-lucide="menu"></i>
            <span>Menu</span>
        </a>

    <?php elseif ($currentRole === 'cashier'): ?>
        <a href="<?= url('dashboard') ?>" class="mobile-nav-item <?= $currentPath === '/dashboard' ? 'active' : '' ?>">
            <i data-lucide="layout-dashboard"></i>
            <span>Beranda</span>
        </a>
        <a href="<?= url('invoices') ?>" class="mobile-nav-item <?= str_starts_with($currentPath, '/invoices') ? 'active' : '' ?>">
            <i data-lucide="receipt"></i>
            <span>Faktur</span>
        </a>
        <a href="<?= url('payments') ?>" class="mobile-nav-item <?= str_starts_with($currentPath, '/payments') ? 'active' : '' ?>">
            <i data-lucide="wallet"></i>
            <span>Bayar</span>
        </a>
        <a href="<?= url('expenses') ?>" class="mobile-nav-item <?= str_starts_with($currentPath, '/expenses') ? 'active' : '' ?>">
            <i data-lucide="arrow-up-right"></i>
            <span>Pengeluaran</span>
        </a>
        <a href="<?= url('menu') ?>" class="mobile-nav-item <?= $currentPath === '/menu' ? 'active' : '' ?>" aria-label="Daftar Menu">
            <i data-lucide="menu"></i>
            <span>Menu</span>
        </a>

    <?php elseif ($currentRole === 'inventory'): ?>
        <a href="<?= url('dashboard') ?>" class="mobile-nav-item <?= $currentPath === '/dashboard' ? 'active' : '' ?>">
            <i data-lucide="layout-dashboard"></i>
            <span>Beranda</span>
        </a>
        <a href="<?= url('inventory') ?>" class="mobile-nav-item <?= str_starts_with($currentPath, '/inventory') ? 'active' : '' ?>">
            <i data-lucide="boxes"></i>
            <span>Stok</span>
        </a>
        <a href="<?= url('purchases') ?>" class="mobile-nav-item <?= str_starts_with($currentPath, '/purchases') ? 'active' : '' ?>">
            <i data-lucide="shopping-cart"></i>
            <span>Beli</span>
        </a>
        <a href="<?= url('suppliers') ?>" class="mobile-nav-item <?= str_starts_with($currentPath, '/suppliers') ? 'active' : '' ?>">
            <i data-lucide="truck"></i>
            <span>Supplier</span>
        </a>
        <a href="<?= url('menu') ?>" class="mobile-nav-item <?= $currentPath === '/menu' ? 'active' : '' ?>" aria-label="Daftar Menu">
            <i data-lucide="menu"></i>
            <span>Menu</span>
        </a>

    <?php else: ?>
        <!-- OWNER, ADMIN, CS -->
        <a href="<?= url('dashboard') ?>" class="mobile-nav-item <?= $currentPath === '/dashboard' ? 'active' : '' ?>">
            <i data-lucide="layout-dashboard"></i>
            <span>Beranda</span>
        </a>
        <a href="<?= url('work-orders') ?>" class="mobile-nav-item <?= str_starts_with($currentPath, '/work-orders') ? 'active' : '' ?>">
            <i data-lucide="clipboard-check"></i>
            <span>Order</span>
        </a>
        <a href="<?= url('customers') ?>" class="mobile-nav-item <?= str_starts_with($currentPath, '/customers') ? 'active' : '' ?>">
            <i data-lucide="users"></i>
            <span>Pelanggan</span>
        </a>
        <a href="<?= url('invoices') ?>" class="mobile-nav-item <?= str_starts_with($currentPath, '/invoices') ? 'active' : '' ?>">
            <i data-lucide="receipt"></i>
            <span>Faktur</span>
        </a>
        <a href="<?= url('menu') ?>" class="mobile-nav-item <?= $currentPath === '/menu' ? 'active' : '' ?>" aria-label="Daftar Menu">
            <i data-lucide="menu"></i>
            <span>Menu</span>
        </a>
    <?php endif; ?>
</nav>
