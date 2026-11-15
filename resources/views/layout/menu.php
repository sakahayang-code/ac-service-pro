<?php view('layout.header', ['title' => 'Daftar Menu']); ?>

<?php
$user = auth_user() ?? ['name' => 'Pengguna', 'role_name' => 'guest', 'role_display_name' => 'Tamu'];
$currentRole = $user['role_name'] ?? 'guest';
?>

<div style="max-width: 900px; margin: 0 auto; width: 100%;">
    <!-- User Profile Header Card -->
    <div class="card" style="background: linear-gradient(135deg, var(--primary) 0%, #1e3a5f 100%); color: #ffffff; padding: 20px; margin-bottom: 20px; border: none; box-shadow: 0 4px 16px rgba(15, 42, 74, 0.25);">
        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 14px;">
            <div style="display: flex; align-items: center; gap: 14px; min-width: 0;">
                <div style="width: 52px; height: 52px; border-radius: 50%; background: var(--accent); color: var(--primary); display: flex; align-items: center; justify-content: center; font-size: 1.4rem; font-weight: 800; flex-shrink: 0; box-shadow: 0 2px 8px rgba(0,0,0,0.2);">
                    <?= strtoupper(substr($user['name'] ?? 'U', 0, 1)) ?>
                </div>
                <div style="min-width: 0;">
                    <h2 style="color: #ffffff; font-size: 1.2rem; margin: 0 0 4px 0; font-weight: 700; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                        <?= htmlspecialchars($user['name'] ?? 'Pengguna') ?>
                    </h2>
                    <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                        <span class="badge" style="background: rgba(6, 182, 212, 0.2); color: var(--accent); border: 1px solid rgba(6, 182, 212, 0.4); font-size: 0.75rem; font-weight: 700; padding: 3px 8px;">
                            <?= htmlspecialchars($user['role_display_name'] ?? strtoupper($currentRole)) ?>
                        </span>
                        <span style="font-size: 0.75rem; color: rgba(255, 255, 255, 0.75);"><?= htmlspecialchars($user['email'] ?? '') ?></span>
                    </div>
                </div>
            </div>
            <div>
                <a href="<?= url('logout') ?>" class="btn btn-sm" style="background: rgba(220, 38, 38, 0.2); color: #fca5a5; border: 1px solid rgba(220, 38, 38, 0.4); font-weight: 600;">
                    <i data-lucide="log-out" style="width: 14px; height: 14px;"></i>
                    <span>Keluar</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Quick Switch Role Card (for easy demonstration) -->
    <div class="card" style="padding: 14px 18px; margin-bottom: 24px; background: #f8fafc; border: 1px solid #e2e8f0; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px;">
        <div style="display: flex; align-items: center; gap: 8px;">
            <i data-lucide="shield-check" style="width: 18px; height: 18px; color: var(--primary);"></i>
            <span style="font-size: 0.85rem; font-weight: 700; color: var(--primary);">Beralih Hak Akses (Demo):</span>
        </div>
        <select class="role-switcher-select" style="min-width: 150px; font-size: 0.8rem; padding: 6px 12px;" onchange="window.location.href=(window.APP_BASE_URL || '') + '/auth/switch-role?role='+encodeURIComponent(this.value)">
            <option value="owner" <?= $currentRole === 'owner' ? 'selected' : '' ?>>👑 Owner (Semua Akses)</option>
            <option value="admin" <?= $currentRole === 'admin' ? 'selected' : '' ?>>⚙️ Administrator</option>
            <option value="customer_service" <?= $currentRole === 'customer_service' ? 'selected' : '' ?>>🎧 Customer Service</option>
            <option value="cashier" <?= $currentRole === 'cashier' ? 'selected' : '' ?>>💰 Kasir</option>
            <option value="technician" <?= $currentRole === 'technician' ? 'selected' : '' ?>>🔧 Teknisi Lapangan</option>
            <option value="inventory" <?= $currentRole === 'inventory' ? 'selected' : '' ?>>📦 Petugas Gudang</option>
            <option value="customer" <?= $currentRole === 'customer' ? 'selected' : '' ?>>👤 Pelanggan</option>
        </select>
    </div>

    <!-- MENU LIST SECTIONS (Exact match to Laptop Sidebar) -->

    <?php if ($currentRole === 'customer'): ?>
        <!-- 1. PORTAL PELANGGAN -->
        <div style="margin-bottom: 24px;">
            <div style="font-size: 0.85rem; font-weight: 800; color: var(--primary); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 12px; display: flex; align-items: center; gap: 8px;">
                <i data-lucide="sparkles" style="width: 16px; height: 16px; color: var(--secondary);"></i>
                <span>Layanan Mandiri Pelanggan</span>
            </div>
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(min(100%, 260px), 1fr)); gap: 12px;">
                <a href="<?= url('customer-portal/book') ?>" class="card menu-card-item">
                    <div class="menu-card-icon" style="background: #eff6ff; color: #2563eb;">
                        <i data-lucide="calendar-plus"></i>
                    </div>
                    <div class="menu-card-text">
                        <strong>Pesan Servis Baru</strong>
                        <span>Order cuci, tambah freon, atau perbaikan AC</span>
                    </div>
                    <i data-lucide="chevron-right" class="menu-card-arrow"></i>
                </a>

                <a href="<?= url('customer-portal/units') ?>" class="card menu-card-item">
                    <div class="menu-card-icon" style="background: #ecfeff; color: #0891b2;">
                        <i data-lucide="airplay"></i>
                    </div>
                    <div class="menu-card-text">
                        <strong>Unit AC Saya</strong>
                        <span>Daftar AC terpasang, merek, PK, & riwayat</span>
                    </div>
                    <i data-lucide="chevron-right" class="menu-card-arrow"></i>
                </a>

                <a href="<?= url('customer-portal/orders') ?>" class="card menu-card-item">
                    <div class="menu-card-icon" style="background: #f0fdf4; color: #16a34a;">
                        <i data-lucide="clipboard-list"></i>
                    </div>
                    <div class="menu-card-text">
                        <strong>Status Pesanan</strong>
                        <span>Pantau progres teknisi & pengerjaan servis</span>
                    </div>
                    <i data-lucide="chevron-right" class="menu-card-arrow"></i>
                </a>

                <a href="<?= url('customer-portal/invoices') ?>" class="card menu-card-item">
                    <div class="menu-card-icon" style="background: #fefce8; color: #ca8a04;">
                        <i data-lucide="receipt"></i>
                    </div>
                    <div class="menu-card-text">
                        <strong>Faktur & Pembayaran</strong>
                        <span>Tagihan, bukti pembayaran & cetak faktur</span>
                    </div>
                    <i data-lucide="chevron-right" class="menu-card-arrow"></i>
                </a>

                <a href="<?= url('customer-portal/complaints') ?>" class="card menu-card-item">
                    <div class="menu-card-icon" style="background: #fef2f2; color: #dc2626;">
                        <i data-lucide="message-square-warning"></i>
                    </div>
                    <div class="menu-card-text">
                        <strong>Komplain & Garansi</strong>
                        <span>Klaim garansi pengerjaan & pengaduan</span>
                    </div>
                    <i data-lucide="chevron-right" class="menu-card-arrow"></i>
                </a>
            </div>
        </div>

    <?php elseif ($currentRole === 'technician'): ?>
        <!-- 2. PORTAL TEKNISI -->
        <div style="margin-bottom: 24px;">
            <div style="font-size: 0.85rem; font-weight: 800; color: var(--primary); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 12px; display: flex; align-items: center; gap: 8px;">
                <i data-lucide="wrench" style="width: 16px; height: 16px; color: var(--secondary);"></i>
                <span>Pengerjaan Lapangan</span>
            </div>
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(min(100%, 260px), 1fr)); gap: 12px;">
                <a href="<?= url('dashboard') ?>" class="card menu-card-item">
                    <div class="menu-card-icon" style="background: #eff6ff; color: #2563eb;">
                        <i data-lucide="layout-dashboard"></i>
                    </div>
                    <div class="menu-card-text">
                        <strong>Dashboard Teknisi</strong>
                        <span>Ringkasan tugas hari ini & performa servis</span>
                    </div>
                    <i data-lucide="chevron-right" class="menu-card-arrow"></i>
                </a>

                <a href="<?= url('technician/tasks') ?>" class="card menu-card-item">
                    <div class="menu-card-icon" style="background: #fef3c7; color: #d97706;">
                        <i data-lucide="wrench"></i>
                    </div>
                    <div class="menu-card-text">
                        <strong>Tugas Saya (Aktif)</strong>
                        <span>Check-in, navigasi peta, checklist & eksekusi</span>
                    </div>
                    <i data-lucide="chevron-right" class="menu-card-arrow"></i>
                </a>

                <a href="<?= url('technician/history') ?>" class="card menu-card-item">
                    <div class="menu-card-icon" style="background: #f0fdf4; color: #16a34a;">
                        <i data-lucide="history"></i>
                    </div>
                    <div class="menu-card-text">
                        <strong>Riwayat Servis</strong>
                        <span>Daftar pekerjaan yang telah selesai dikerjakan</span>
                    </div>
                    <i data-lucide="chevron-right" class="menu-card-arrow"></i>
                </a>

                <a href="<?= url('technician/profile') ?>" class="card menu-card-item">
                    <div class="menu-card-icon" style="background: #f5f3ff; color: #7c3aed;">
                        <i data-lucide="user-check"></i>
                    </div>
                    <div class="menu-card-text">
                        <strong>Profil & Komisi</strong>
                        <span>Data diri, riwayat honor & insentif servis</span>
                    </div>
                    <i data-lucide="chevron-right" class="menu-card-arrow"></i>
                </a>
            </div>
        </div>

    <?php else: ?>
        <!-- 3. OPERASIONAL UTAMA (OWNER, ADMIN, CS, KASIR, GUDANG) -->
        
        <!-- DASHBOARD UTAMA -->
        <div style="margin-bottom: 24px;">
            <div style="font-size: 0.85rem; font-weight: 800; color: var(--primary); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 12px; display: flex; align-items: center; gap: 8px;">
                <i data-lucide="layout-dashboard" style="width: 16px; height: 16px; color: var(--secondary);"></i>
                <span>Ringkasan Utama</span>
            </div>
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(min(100%, 260px), 1fr)); gap: 12px;">
                <a href="<?= url('dashboard') ?>" class="card menu-card-item">
                    <div class="menu-card-icon" style="background: #eff6ff; color: #2563eb;">
                        <i data-lucide="layout-dashboard"></i>
                    </div>
                    <div class="menu-card-text">
                        <strong>Dashboard Utama</strong>
                        <span>Metrik bisnis, antrean servis & grafik usaha</span>
                    </div>
                    <i data-lucide="chevron-right" class="menu-card-arrow"></i>
                </a>
            </div>
        </div>

        <?php if (in_array($currentRole, ['owner', 'admin', 'customer_service'])): ?>
            <!-- OPERASIONAL SERVIS -->
            <div style="margin-bottom: 24px;">
                <div style="font-size: 0.85rem; font-weight: 800; color: var(--primary); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 12px; display: flex; align-items: center; gap: 8px;">
                    <i data-lucide="clipboard-check" style="width: 16px; height: 16px; color: #2563eb;"></i>
                    <span>Operasional Servis</span>
                </div>
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(min(100%, 260px), 1fr)); gap: 12px;">
                    <a href="<?= url('work-orders') ?>" class="card menu-card-item">
                        <div class="menu-card-icon" style="background: #eff6ff; color: #2563eb;">
                            <i data-lucide="clipboard-check"></i>
                        </div>
                        <div class="menu-card-text">
                            <strong>Work Order (WO / SPK)</strong>
                            <span>Penjadwalan, status pengerjaan & penugasan</span>
                        </div>
                        <i data-lucide="chevron-right" class="menu-card-arrow"></i>
                    </a>

                    <a href="<?= url('customers') ?>" class="card menu-card-item">
                        <div class="menu-card-icon" style="background: #f0fdf4; color: #16a34a;">
                            <i data-lucide="users"></i>
                        </div>
                        <div class="menu-card-text">
                            <strong>Data Pelanggan</strong>
                            <span>Database kontak, alamat, kategori & riwayat</span>
                        </div>
                        <i data-lucide="chevron-right" class="menu-card-arrow"></i>
                    </a>

                    <a href="<?= url('ac-units') ?>" class="card menu-card-item">
                        <div class="menu-card-icon" style="background: #ecfeff; color: #0891b2;">
                            <i data-lucide="snowflake"></i>
                        </div>
                        <div class="menu-card-text">
                            <strong>Data Unit AC</strong>
                            <span>Merek, tipe PK, jenis freon & catatan teknis</span>
                        </div>
                        <i data-lucide="chevron-right" class="menu-card-arrow"></i>
                    </a>

                    <a href="<?= url('technicians') ?>" class="card menu-card-item">
                        <div class="menu-card-icon" style="background: #fef3c7; color: #d97706;">
                            <i data-lucide="user-cog"></i>
                        </div>
                        <div class="menu-card-text">
                            <strong>Teknisi & Jadwal</strong>
                            <span>Manajemen teknisi, keahlian & absensi</span>
                        </div>
                        <i data-lucide="chevron-right" class="menu-card-arrow"></i>
                    </a>

                    <a href="<?= url('service-catalog') ?>" class="card menu-card-item">
                        <div class="menu-card-icon" style="background: #f5f3ff; color: #7c3aed;">
                            <i data-lucide="tag"></i>
                        </div>
                        <div class="menu-card-text">
                            <strong>Katalog & Harga</strong>
                            <span>Tarif cuci AC, isi freon, perbaikan & paket</span>
                        </div>
                        <i data-lucide="chevron-right" class="menu-card-arrow"></i>
                    </a>

                    <a href="<?= url('contracts') ?>" class="card menu-card-item">
                        <div class="menu-card-icon" style="background: #e0f2fe; color: #0284c7;">
                            <i data-lucide="file-signature"></i>
                        </div>
                        <div class="menu-card-text">
                            <strong>Kontrak Maintenance</strong>
                            <span>Perjanjian servis berkala kantor/perusahaan</span>
                        </div>
                        <i data-lucide="chevron-right" class="menu-card-arrow"></i>
                    </a>
                </div>
            </div>
        <?php endif; ?>

        <?php if (in_array($currentRole, ['owner', 'admin', 'inventory'])): ?>
            <!-- INVENTORI & SPAREPART -->
            <div style="margin-bottom: 24px;">
                <div style="font-size: 0.85rem; font-weight: 800; color: var(--primary); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 12px; display: flex; align-items: center; gap: 8px;">
                    <i data-lucide="boxes" style="width: 16px; height: 16px; color: #d97706;"></i>
                    <span>Inventori & Logistik</span>
                </div>
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(min(100%, 260px), 1fr)); gap: 12px;">
                    <a href="<?= url('inventory') ?>" class="card menu-card-item">
                        <div class="menu-card-icon" style="background: #fef3c7; color: #d97706;">
                            <i data-lucide="boxes"></i>
                        </div>
                        <div class="menu-card-text">
                            <strong>Stok Sparepart</strong>
                            <span>Freon, pipa, kapasitor & kartu stok mutasi</span>
                        </div>
                        <i data-lucide="chevron-right" class="menu-card-arrow"></i>
                    </a>

                    <a href="<?= url('purchases') ?>" class="card menu-card-item">
                        <div class="menu-card-icon" style="background: #eff6ff; color: #2563eb;">
                            <i data-lucide="shopping-cart"></i>
                        </div>
                        <div class="menu-card-text">
                            <strong>Pembelian (PO)</strong>
                            <span>Pencatatan pembelian barang & sparepart baru</span>
                        </div>
                        <i data-lucide="chevron-right" class="menu-card-arrow"></i>
                    </a>

                    <a href="<?= url('suppliers') ?>" class="card menu-card-item">
                        <div class="menu-card-icon" style="background: #f0fdf4; color: #16a34a;">
                            <i data-lucide="truck"></i>
                        </div>
                        <div class="menu-card-text">
                            <strong>Pemasok / Supplier</strong>
                            <span>Data vendor, distributor & kontak supplier</span>
                        </div>
                        <i data-lucide="chevron-right" class="menu-card-arrow"></i>
                    </a>
                </div>
            </div>
        <?php endif; ?>

        <?php if (in_array($currentRole, ['owner', 'admin', 'cashier'])): ?>
            <!-- KASIR & KEUANGAN -->
            <div style="margin-bottom: 24px;">
                <div style="font-size: 0.85rem; font-weight: 800; color: var(--primary); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 12px; display: flex; align-items: center; gap: 8px;">
                    <i data-lucide="wallet" style="width: 16px; height: 16px; color: #16a34a;"></i>
                    <span>Kasir & Keuangan</span>
                </div>
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(min(100%, 260px), 1fr)); gap: 12px;">
                    <a href="<?= url('invoices') ?>" class="card menu-card-item">
                        <div class="menu-card-icon" style="background: #f0fdf4; color: #16a34a;">
                            <i data-lucide="receipt"></i>
                        </div>
                        <div class="menu-card-text">
                            <strong>Faktur / Invoice</strong>
                            <span>Penagihan jasa servis, cetak PDF & status bayar</span>
                        </div>
                        <i data-lucide="chevron-right" class="menu-card-arrow"></i>
                    </a>

                    <a href="<?= url('payments') ?>" class="card menu-card-item">
                        <div class="menu-card-icon" style="background: #eff6ff; color: #2563eb;">
                            <i data-lucide="wallet"></i>
                        </div>
                        <div class="menu-card-text">
                            <strong>Pembayaran</strong>
                            <span>Catat pelunasan cash, transfer, & kartu kredit</span>
                        </div>
                        <i data-lucide="chevron-right" class="menu-card-arrow"></i>
                    </a>

                    <a href="<?= url('expenses') ?>" class="card menu-card-item">
                        <div class="menu-card-icon" style="background: #fef2f2; color: #dc2626;">
                            <i data-lucide="arrow-up-right"></i>
                        </div>
                        <div class="menu-card-text">
                            <strong>Pengeluaran Kas</strong>
                            <span>Biaya operasional, bensin, konsumsi & utilitas</span>
                        </div>
                        <i data-lucide="chevron-right" class="menu-card-arrow"></i>
                    </a>

                    <a href="<?= url('finance') ?>" class="card menu-card-item">
                        <div class="menu-card-icon" style="background: #ecfeff; color: #0891b2;">
                            <i data-lucide="landmark"></i>
                        </div>
                        <div class="menu-card-text">
                            <strong>Buku Kas & Rekening</strong>
                            <span>Buku kas toko, mutasi rekening bank BCA/Mandiri</span>
                        </div>
                        <i data-lucide="chevron-right" class="menu-card-arrow"></i>
                    </a>
                </div>
            </div>
        <?php endif; ?>

        <?php if (in_array($currentRole, ['owner', 'admin'])): ?>
            <!-- LAPORAN & ANALITIK -->
            <div style="margin-bottom: 24px;">
                <div style="font-size: 0.85rem; font-weight: 800; color: var(--primary); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 12px; display: flex; align-items: center; gap: 8px;">
                    <i data-lucide="bar-chart-3" style="width: 16px; height: 16px; color: #7c3aed;"></i>
                    <span>Laporan Usaha</span>
                </div>
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(min(100%, 260px), 1fr)); gap: 12px;">
                    <a href="<?= url('reports') ?>" class="card menu-card-item">
                        <div class="menu-card-icon" style="background: #f5f3ff; color: #7c3aed;">
                            <i data-lucide="bar-chart-3"></i>
                        </div>
                        <div class="menu-card-text">
                            <strong>Dashboard Laporan</strong>
                            <span>Laba rugi, kinerja teknisi, WO & inventori</span>
                        </div>
                        <i data-lucide="chevron-right" class="menu-card-arrow"></i>
                    </a>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($currentRole === 'owner'): ?>
            <!-- PENGATURAN & MASTER -->
            <div style="margin-bottom: 24px;">
                <div style="font-size: 0.85rem; font-weight: 800; color: var(--primary); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 12px; display: flex; align-items: center; gap: 8px;">
                    <i data-lucide="settings" style="width: 16px; height: 16px; color: #475569;"></i>
                    <span>Sistem & Master</span>
                </div>
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(min(100%, 260px), 1fr)); gap: 12px;">
                    <a href="<?= url('settings') ?>" class="card menu-card-item">
                        <div class="menu-card-icon" style="background: #f1f5f9; color: #334155;">
                            <i data-lucide="settings"></i>
                        </div>
                        <div class="menu-card-text">
                            <strong>Pengaturan Usaha</strong>
                            <span>Profil usaha, backup database SQLite & sistem</span>
                        </div>
                        <i data-lucide="chevron-right" class="menu-card-arrow"></i>
                    </a>

                    <a href="<?= url('users') ?>" class="card menu-card-item">
                        <div class="menu-card-icon" style="background: #ecfeff; color: #0e7490;">
                            <i data-lucide="shield-check"></i>
                        </div>
                        <div class="menu-card-text">
                            <strong>Pengguna & Hak Akses</strong>
                            <span>Kelola akun login admin, kasir, teknisi & CS</span>
                        </div>
                        <i data-lucide="chevron-right" class="menu-card-arrow"></i>
                    </a>
                </div>
            </div>
        <?php endif; ?>

    <?php endif; ?>

    <!-- AKUN & NOTIFIKASI -->
    <div style="margin-bottom: 30px;">
        <div style="font-size: 0.85rem; font-weight: 800; color: var(--primary); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 12px; display: flex; align-items: center; gap: 8px;">
            <i data-lucide="user" style="width: 16px; height: 16px; color: var(--primary);"></i>
            <span>Akun & Sesi</span>
        </div>
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(min(100%, 260px), 1fr)); gap: 12px;">
            <a href="<?= url('notifications') ?>" class="card menu-card-item">
                <div class="menu-card-icon" style="background: #fefce8; color: #ca8a04;">
                    <i data-lucide="bell"></i>
                </div>
                <div class="menu-card-text">
                    <strong>Notifikasi</strong>
                    <span>Peringatan sistem, stok menipis & servis rutin</span>
                </div>
                <i data-lucide="chevron-right" class="menu-card-arrow"></i>
            </a>

            <a href="<?= url('logout') ?>" class="card menu-card-item" style="border-color: #fee2e2;">
                <div class="menu-card-icon" style="background: #fef2f2; color: #dc2626;">
                    <i data-lucide="log-out"></i>
                </div>
                <div class="menu-card-text">
                    <strong style="color: #dc2626;">Keluar Aplikasi</strong>
                    <span>Mengakhiri sesi akun saat ini</span>
                </div>
                <i data-lucide="chevron-right" class="menu-card-arrow"></i>
            </a>
        </div>
    </div>
</div>

<style>
.menu-card-item {
    display: flex !important;
    align-items: center !important;
    gap: 14px !important;
    padding: 14px 16px !important;
    margin-bottom: 0 !important;
    text-decoration: none !important;
    color: inherit !important;
    background: var(--surface) !important;
    border: 1px solid var(--border) !important;
    border-radius: var(--radius) !important;
    box-shadow: 0 1px 3px rgba(0,0,0,0.04) !important;
    transition: all 0.15s ease !important;
    min-height: 64px !important;
    box-sizing: border-box !important;
}

.menu-card-item:hover, .menu-card-item:active {
    background: #f8fafc !important;
    border-color: #93c5fd !important;
    transform: translateY(-1px) !important;
    box-shadow: 0 4px 12px rgba(37, 99, 235, 0.1) !important;
}

.menu-card-icon {
    width: 42px !important;
    height: 42px !important;
    min-width: 42px !important;
    min-height: 42px !important;
    border-radius: 10px !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    flex-shrink: 0 !important;
}

.menu-card-icon svg, .menu-card-icon i {
    width: 22px !important;
    height: 22px !important;
    stroke-width: 2.2 !important;
}

.menu-card-text {
    flex: 1 1 auto !important;
    min-width: 0 !important;
    display: flex !important;
    flex-direction: column !important;
    gap: 2px !important;
}

.menu-card-text strong {
    font-size: 0.92rem !important;
    color: var(--text-main) !important;
    font-weight: 700 !important;
    white-space: nowrap !important;
    overflow: hidden !important;
    text-overflow: ellipsis !important;
}

.menu-card-text span {
    font-size: 0.75rem !important;
    color: var(--text-muted) !important;
    line-height: 1.3 !important;
    white-space: nowrap !important;
    overflow: hidden !important;
    text-overflow: ellipsis !important;
}

.menu-card-arrow {
    width: 18px !important;
    height: 18px !important;
    min-width: 18px !important;
    color: #94a3b8 !important;
    flex-shrink: 0 !important;
    transition: transform 0.15s ease !important;
}

.menu-card-item:hover .menu-card-arrow {
    color: var(--primary) !important;
    transform: translateX(3px) !important;
}
</style>

<?php view('layout.footer'); ?>
