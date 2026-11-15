<?php require dirname(__DIR__, 2) . '/layout/header.php'; ?>

<div class="card">
    <div class="card-header">
        <div class="card-title">
            <i data-lucide="users"></i>
            <span>Daftar Pelanggan</span>
        </div>
        <div style="display:flex; gap:10px;">
            <a href="/customers/export/csv" class="btn btn-outline btn-sm">
                <i data-lucide="file-spreadsheet" style="width:16px; height:16px;"></i>
                <span>Ekspor CSV</span>
            </a>
            <a href="/customers/create" class="btn btn-primary btn-sm">
                <i data-lucide="user-plus" style="width:16px; height:16px;"></i>
                <span>Tambah Pelanggan</span>
            </a>
        </div>
    </div>

    <!-- Filter & Search Bar -->
    <form method="GET" action="/customers" style="display:flex; gap:12px; margin-bottom:20px; flex-wrap:wrap;">
        <div style="flex:1; min-width:240px;">
            <input type="text" name="q" class="form-control" placeholder="Cari nama, kode, nomor HP, alamat..." value="<?= htmlspecialchars($search) ?>">
        </div>
        <div style="width:180px;">
            <select name="category" class="form-select" onchange="this.form.submit()">
                <option value="">Semua Kategori</option>
                <option value="household" <?= $category === 'household' ? 'selected' : '' ?>>Rumah Tangga</option>
                <option value="office" <?= $category === 'office' ? 'selected' : '' ?>>Perkantoran</option>
                <option value="school" <?= $category === 'school' ? 'selected' : '' ?>>Sekolah</option>
                <option value="hotel" <?= $category === 'hotel' ? 'selected' : '' ?>>Hotel</option>
                <option value="contract" <?= $category === 'contract' ? 'selected' : '' ?>>Pelanggan Kontrak</option>
            </select>
        </div>
        <button type="submit" class="btn btn-secondary btn-sm">Cari</button>
        <?php if ($search !== '' || $category !== ''): ?>
            <a href="/customers" class="btn btn-outline btn-sm">Reset</a>
        <?php endif; ?>
    </form>

    <!-- Customers Data Table -->
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Kode</th>
                    <th>Nama Pelanggan</th>
                    <th>Kategori</th>
                    <th>Kontak / WhatsApp</th>
                    <th>Alamat</th>
                    <th>Unit AC</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($customers)): ?>
                    <tr><td colspan="8" style="text-align:center; padding:24px; color:var(--text-muted);">Tidak ada data pelanggan yang sesuai pencarian.</td></tr>
                <?php else: ?>
                    <?php foreach ($customers as $c): ?>
                        <tr>
                            <td><strong style="color:var(--primary);"><?= htmlspecialchars($c['customer_code']) ?></strong></td>
                            <td>
                                <a href="/customers/<?= $c['id'] ?>" style="font-weight:700; color:var(--secondary);">
                                    <?= htmlspecialchars($c['name']) ?>
                                </a>
                                <?php if (!empty($c['email'])): ?>
                                    <div style="font-size:0.75rem; color:var(--text-muted);"><?= htmlspecialchars($c['email']) ?></div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge badge-gray"><?= htmlspecialchars($c['category']) ?></span>
                            </td>
                            <td>
                                <div><?= htmlspecialchars($c['phone']) ?></div>
                                <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $c['phone']) ?>" target="_blank" style="font-size:0.75rem; color:#16a34a; font-weight:600; display:inline-flex; align-items:center; gap:4px;">
                                    <i data-lucide="message-circle" style="width:12px; height:12px;"></i> Chat WA
                                </a>
                            </td>
                            <td>
                                <div style="max-width:240px; font-size:0.85rem; color:var(--text-main); white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                                    <?= htmlspecialchars($c['address']) ?>
                                </div>
                            </td>
                            <td>
                                <span class="badge badge-blue"><?= (int)$c['unit_count'] ?> Unit</span>
                            </td>
                            <td>
                                <span class="badge <?= $c['status'] === 'active' ? 'badge-green' : 'badge-red' ?>">
                                    <?= $c['status'] === 'active' ? 'Aktif' : 'Nonaktif' ?>
                                </span>
                            </td>
                            <td>
                                <div style="display:flex; gap:6px; flex-wrap:wrap;">
                                    <a href="/customers/<?= $c['id'] ?>" class="btn-action btn-action-view" title="Lihat Profil">
                                        <i data-lucide="eye"></i>
                                        <span>Detail</span>
                                    </a>
                                    <a href="/customers/<?= $c['id'] ?>/edit" class="btn-action btn-action-edit" title="Ubah Data">
                                        <i data-lucide="edit"></i>
                                        <span>Ubah</span>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require dirname(__DIR__, 2) . '/layout/footer.php'; ?>
