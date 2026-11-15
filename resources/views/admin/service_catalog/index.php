<?php require dirname(__DIR__, 2) . '/layout/header.php'; ?>

<div class="card">
    <div class="card-header">
        <div class="card-title">
            <i data-lucide="tag"></i>
            <span>Katalog Layanan & Tarif Servis AC</span>
        </div>
        <a href="/service-catalog/create" class="btn btn-primary btn-sm">
            <i data-lucide="plus-circle" style="width:16px; height:16px;"></i>
            <span>Tambah Layanan Baru</span>
        </a>
    </div>

    <!-- Category Filter Tabs -->
    <div style="display:flex; gap:8px; margin-bottom:20px; overflow-x:auto; padding-bottom:6px;">
        <a href="/service-catalog" class="badge <?= $selectedCategoryId === null ? 'badge-blue' : 'badge-gray' ?>" style="padding:8px 14px; font-size:0.8rem; text-decoration:none;">
            Semua Kategori (<?= count($services) ?>)
        </a>
        <?php foreach ($categories as $cat): ?>
            <a href="/service-catalog?category_id=<?= $cat['id'] ?>" class="badge <?= $selectedCategoryId === (int)$cat['id'] ? 'badge-blue' : 'badge-gray' ?>" style="padding:8px 14px; font-size:0.8rem; text-decoration:none;">
                <?= htmlspecialchars($cat['name']) ?>
            </a>
        <?php endforeach; ?>
    </div>

    <!-- Services Table -->
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Kode Layanan</th>
                    <th>Nama Layanan & Deskripsi</th>
                    <th>Kategori</th>
                    <th>Filter PK</th>
                    <th>Filter Freon</th>
                    <th>Tarif Umum (Rp)</th>
                    <th>Tarif Kontrak (Rp)</th>
                    <th>Estimasi Waktu</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($services)): ?>
                    <tr><td colspan="10" style="text-align:center; padding:24px; color:var(--text-muted);">Tidak ada layanan dalam kategori ini.</td></tr>
                <?php else: ?>
                    <?php foreach ($services as $srv): ?>
                        <tr style="<?= $srv['is_active'] ? '' : 'opacity:0.6;' ?>">
                            <td><strong style="color:var(--primary);"><?= htmlspecialchars($srv['code']) ?></strong></td>
                            <td>
                                <strong style="font-size:0.95rem; color:var(--text-main);"><?= htmlspecialchars($srv['name']) ?></strong>
                                <?php if (!empty($srv['description'])): ?>
                                    <div style="font-size:0.75rem; color:var(--text-muted); max-width:260px;"><?= htmlspecialchars($srv['description']) ?></div>
                                <?php endif; ?>
                            </td>
                            <td><span class="badge badge-purple"><?= htmlspecialchars($srv['category_name']) ?></span></td>
                            <td><span class="badge badge-blue"><?= htmlspecialchars($srv['capacity_pk_filter'] ?? 'ALL') ?></span></td>
                            <td><span class="badge badge-gray"><?= htmlspecialchars($srv['refrigerant_filter'] ?? 'ALL') ?></span></td>
                            <td><strong style="color:var(--primary); font-size:0.95rem;"><?= format_rupiah($srv['base_price']) ?></strong></td>
                            <td><strong style="color:var(--success);"><?= format_rupiah($srv['contract_price']) ?></strong></td>
                            <td><?= (int)$srv['estimated_duration_minutes'] ?> Menit</td>
                            <td>
                                <span class="badge <?= $srv['is_active'] ? 'badge-green' : 'badge-red' ?>">
                                    <?= $srv['is_active'] ? 'Aktif' : 'Nonaktif' ?>
                                </span>
                            </td>
                            <td>
                                <div style="display:flex; gap:6px;">
                                    <a href="/service-catalog/<?= $srv['id'] ?>/edit" class="btn btn-outline btn-sm" title="Ubah Tarif">
                                        <i data-lucide="edit-2" style="width:14px; height:14px;"></i>
                                    </a>
                                    <form method="POST" action="/service-catalog/<?= $srv['id'] ?>/toggle" style="display:inline;">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn btn-outline btn-sm" title="<?= $srv['is_active'] ? 'Nonaktifkan' : 'Aktifkan' ?>">
                                            <i data-lucide="<?= $srv['is_active'] ? 'eye-off' : 'eye' ?>" style="width:14px; height:14px;"></i>
                                        </button>
                                    </form>
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
