<?php require dirname(__DIR__, 2) . '/layout/header.php'; ?>

<!-- Inventory KPI Cards -->
<div class="stat-grid" style="margin-bottom:16px;">
    <div class="stat-card">
        <div class="stat-icon primary">
            <i data-lucide="boxes" style="width:24px; height:24px;"></i>
        </div>
        <div>
            <div class="stat-value"><?= number_format($totalItems) ?></div>
            <div class="stat-label">Jenis Sparepart Aktif</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon success">
            <i data-lucide="coins" style="width:24px; height:24px;"></i>
        </div>
        <div>
            <div class="stat-value"><?= format_rupiah($totalValuation) ?></div>
            <div class="stat-label">Total Nilai Aset Stok (HPP)</div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <div class="card-title">
            <i data-lucide="package"></i>
            <span>Daftar Suku Cadang & Stok Gudang</span>
        </div>
        <div style="display:flex; gap:10px;">
            <button type="button" class="btn btn-outline btn-sm" onclick="openModal('quick-movement-modal')">
                <i data-lucide="arrow-left-right" style="width:16px; height:16px;"></i>
                <span>Mutasi Stok</span>
            </button>
            <a href="/inventory/create" class="btn btn-primary btn-sm">
                <i data-lucide="plus-circle" style="width:16px; height:16px;"></i>
                <span>Tambah Barang Baru</span>
            </a>
        </div>
    </div>

    <!-- Filter & Search Bar -->
    <form method="GET" action="/inventory" style="display:flex; gap:12px; margin-bottom:20px; flex-wrap:wrap;">
        <div style="flex:1; min-width:240px;">
            <input type="text" name="q" class="form-control" placeholder="Cari nama sparepart, SKU, kategori..." value="<?= htmlspecialchars($search) ?>">
        </div>
        <div style="width:180px;">
            <select name="category" class="form-select" onchange="this.form.submit()">
                <option value="">Semua Kategori</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= htmlspecialchars($cat) ?>" <?= $category === $cat ? 'selected' : '' ?>><?= htmlspecialchars($cat) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <label style="display:flex; align-items:center; gap:6px; font-size:0.85rem; cursor:pointer;">
            <input type="checkbox" name="low_stock" value="1" <?= $lowStock ? 'checked' : '' ?> onchange="this.form.submit()">
            <span style="color:var(--danger); font-weight:700;">Hanya Stok Menipis</span>
        </label>
        <button type="submit" class="btn btn-secondary btn-sm">Cari</button>
        <?php if ($search !== '' || $category !== '' || $lowStock): ?>
            <a href="/inventory" class="btn btn-outline btn-sm">Reset</a>
        <?php endif; ?>
    </form>

    <!-- Inventory Data Table -->
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>SKU</th>
                    <th>Nama Sparepart / Barang</th>
                    <th>Kategori</th>
                    <th>Satuan</th>
                    <th>Harga Beli (HPP)</th>
                    <th>Harga Jual</th>
                    <th>Stok Sekarang</th>
                    <th>Status Stok</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($items)): ?>
                    <tr><td colspan="9" style="text-align:center; padding:24px; color:var(--text-muted);">Tidak ada barang yang sesuai filter.</td></tr>
                <?php else: ?>
                    <?php foreach ($items as $it): 
                        $isLow = (float)$it['current_stock'] <= (float)$it['min_stock'];
                    ?>
                        <tr style="<?= $isLow ? 'background:#fff1f2;' : '' ?>">
                            <td><strong style="color:var(--primary);"><?= htmlspecialchars($it['sku']) ?></strong></td>
                            <td>
                                <a href="/inventory/<?= $it['id'] ?>" style="font-weight:700; color:var(--secondary);">
                                    <?= htmlspecialchars($it['name']) ?>
                                </a>
                            </td>
                            <td><span class="badge badge-purple"><?= htmlspecialchars($it['category']) ?></span></td>
                            <td><?= htmlspecialchars($it['unit']) ?></td>
                            <td><?= format_rupiah($it['cost_price']) ?></td>
                            <td><strong style="color:var(--primary);"><?= format_rupiah($it['selling_price']) ?></strong></td>
                            <td>
                                <strong style="font-size:1.05rem; color:<?= $isLow ? 'var(--danger)' : 'var(--text-main)' ?>;">
                                    <?= $it['current_stock'] ?>
                                </strong>
                                <span style="font-size:0.75rem; color:var(--text-muted);"><?= htmlspecialchars($it['unit']) ?></span>
                            </td>
                            <td>
                                <?php if ($isLow): ?>
                                    <span class="badge badge-red">MENIPIS (Min: <?= $it['min_stock'] ?>)</span>
                                <?php else: ?>
                                    <span class="badge badge-green">AMAN</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div style="display:flex; gap:6px; flex-wrap:wrap;">
                                    <a href="/inventory/<?= $it['id'] ?>" class="btn-action btn-action-view" title="Kartu Stok">
                                        <i data-lucide="file-text"></i>
                                        <span>Kartu</span>
                                    </a>
                                    <a href="/inventory/<?= $it['id'] ?>/edit" class="btn-action btn-action-edit" title="Ubah Data">
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

<!-- Modal: Quick Stock Movement -->
<div id="quick-movement-modal" class="modal-overlay">
    <div class="modal-dialog">
        <div class="modal-header">
            <h3 class="modal-title">Catat Mutasi Stok Manual</h3>
            <button type="button" onclick="closeModal('quick-movement-modal')" style="background:none;border:none;font-size:20px;cursor:pointer;">&times;</button>
        </div>
        <form method="POST" action="/inventory/movement">
            <?= csrf_field() ?>
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label" for="m_item_id">Pilih Barang *</label>
                    <select id="m_item_id" name="inventory_item_id" class="form-select" required>
                        <?php foreach ($items as $it): ?>
                            <option value="<?= $it['id'] ?>"><?= htmlspecialchars($it['name']) ?> (Sisa: <?= $it['current_stock'] ?> <?= $it['unit'] ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="movement_type">Jenis Mutasi *</label>
                        <select id="movement_type" name="movement_type" class="form-select" required>
                            <option value="in">Stok Masuk (+ Tambah)</option>
                            <option value="out">Stok Keluar (- Rusak/Hilang)</option>
                            <option value="adjustment">Penyesuaian Opname (Set Nilai Baru)</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="m_quantity">Jumlah Qty *</label>
                        <input type="number" step="1" id="m_quantity" name="quantity" class="form-control" value="1" min="1" required>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="m_notes">Keterangan / Alasan Mutasi</label>
                    <textarea id="m_notes" name="notes" class="form-control" rows="2" placeholder="Hasil stock opname bulanan / barang pecah..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('quick-movement-modal')">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Mutasi</button>
            </div>
        </form>
    </div>
</div>

<?php require dirname(__DIR__, 2) . '/layout/footer.php'; ?>
