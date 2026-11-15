<?php require dirname(__DIR__, 2) . '/layout/header.php'; ?>

<div class="card">
    <div class="card-header">
        <div class="card-title">
            <i data-lucide="snowflake"></i>
            <span>Daftar Unit AC Pelanggan</span>
        </div>
        <a href="/ac-units/create" class="btn btn-primary btn-sm">
            <i data-lucide="plus-circle" style="width:16px; height:16px;"></i>
            <span>Daftarkan Unit Baru</span>
        </a>
    </div>

    <!-- Filter & Search Bar -->
    <form method="GET" action="/ac-units" style="display:flex; gap:12px; margin-bottom:20px; flex-wrap:wrap;">
        <div style="flex:1; min-width:240px;">
            <input type="text" name="q" class="form-control" placeholder="Cari kode unit, merek, tipe, lokasi, nama pemilik..." value="<?= htmlspecialchars($search) ?>">
        </div>
        <div style="width:180px;">
            <select name="brand" class="form-select" onchange="this.form.submit()">
                <option value="">Semua Merek</option>
                <?php foreach ($brands as $b): ?>
                    <option value="<?= htmlspecialchars($b) ?>" <?= $brand === $b ? 'selected' : '' ?>><?= htmlspecialchars($b) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <button type="submit" class="btn btn-secondary btn-sm">Cari</button>
        <?php if ($search !== '' || $brand !== ''): ?>
            <a href="/ac-units" class="btn btn-outline btn-sm">Reset</a>
        <?php endif; ?>
    </form>

    <!-- AC Units Data Table -->
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Kode Unit</th>
                    <th>Pelanggan Pemilik</th>
                    <th>Merek & Tipe</th>
                    <th>Kapasitas</th>
                    <th>Freon</th>
                    <th>Lokasi Pasang</th>
                    <th>Kondisi</th>
                    <th>Servis Berikutnya</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($units)): ?>
                    <tr><td colspan="9" style="text-align:center; padding:24px; color:var(--text-muted);">Tidak ada data unit AC.</td></tr>
                <?php else: ?>
                    <?php foreach ($units as $u): ?>
                        <tr>
                            <td><strong style="color:var(--primary);"><?= htmlspecialchars($u['unit_code']) ?></strong></td>
                            <td>
                                <a href="/customers/<?= $u['customer_id'] ?>" style="font-weight:600; color:var(--secondary);">
                                    <?= htmlspecialchars($u['customer_name']) ?>
                                </a>
                                <div style="font-size:0.75rem; color:var(--text-muted);"><?= htmlspecialchars($u['customer_phone']) ?></div>
                            </td>
                            <td>
                                <strong><?= htmlspecialchars($u['brand']) ?></strong>
                                <div style="font-size:0.75rem; color:var(--text-muted);"><?= htmlspecialchars($u['model_type']) ?></div>
                            </td>
                            <td><span class="badge badge-blue"><?= htmlspecialchars($u['capacity_pk']) ?></span></td>
                            <td><span class="badge badge-gray"><?= htmlspecialchars($u['refrigerant_type']) ?></span></td>
                            <td><?= htmlspecialchars($u['room_location']) ?></td>
                            <td>
                                <span class="badge <?= str_contains(strtolower($u['condition']), 'bocor') || str_contains(strtolower($u['condition']), 'rusak') ? 'badge-red' : (str_contains(strtolower($u['condition']), 'kurang') ? 'badge-amber' : 'badge-green') ?>">
                                    <?= htmlspecialchars($u['condition']) ?>
                                </span>
                            </td>
                            <td>
                                <strong style="color:var(--primary);"><?= format_date($u['next_service_due']) ?></strong>
                            </td>
                            <td>
                                <div style="display:flex; gap:6px;">
                                    <a href="/ac-units/<?= $u['id'] ?>" class="btn btn-outline btn-sm" title="Lihat Riwayat">
                                        <i data-lucide="eye" style="width:14px; height:14px;"></i>
                                    </a>
                                    <a href="/ac-units/<?= $u['id'] ?>/edit" class="btn btn-outline btn-sm" title="Ubah">
                                        <i data-lucide="edit-2" style="width:14px; height:14px;"></i>
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
