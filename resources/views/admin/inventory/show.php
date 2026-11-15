<?php require dirname(__DIR__, 2) . '/layout/header.php'; ?>

<!-- Stock Card Header -->
<div class="card">
    <div style="display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:16px;">
        <div>
            <div style="display:flex; align-items:center; gap:8px; margin-bottom:6px;">
                <span class="badge badge-blue"><?= htmlspecialchars($item['sku']) ?></span>
                <span class="badge badge-purple"><?= htmlspecialchars($item['category']) ?></span>
                <?php if ((float)$item['current_stock'] <= (float)$item['min_stock']): ?>
                    <span class="badge badge-red">STOK MENIPIS</span>
                <?php else: ?>
                    <span class="badge badge-green">STOK AMAN</span>
                <?php endif; ?>
            </div>
            <h2 style="font-size:1.5rem; color:var(--primary); font-weight:800;"><?= htmlspecialchars($item['name']) ?></h2>
            <div style="font-size:0.85rem; color:var(--text-muted); margin-top:2px;">
                Harga Beli (HPP): <strong><?= format_rupiah($item['cost_price']) ?></strong> &bull;
                Harga Jual: <strong style="color:var(--success);"><?= format_rupiah($item['selling_price']) ?></strong> &bull;
                Batas Minimum: <?= $item['min_stock'] ?> <?= htmlspecialchars($item['unit']) ?>
            </div>
        </div>

        <div style="text-align:right;">
            <div style="font-size:0.8rem; color:var(--text-muted);">Sisa Stok Saat Ini</div>
            <div style="font-size:2rem; font-weight:900; color:var(--primary); line-height:1.1;">
                <?= $item['current_stock'] ?> <span style="font-size:1rem; font-weight:600;"><?= htmlspecialchars($item['unit']) ?></span>
            </div>
            <div style="display:flex; gap:8px; margin-top:10px;">
                <a href="/inventory/<?= $item['id'] ?>/edit" class="btn btn-outline btn-sm">Ubah Data</a>
                <a href="/inventory" class="btn btn-outline btn-sm">Daftar Barang</a>
            </div>
        </div>
    </div>
</div>

<!-- Kartu Stok Movements Table -->
<div class="card">
    <div class="card-header">
        <div class="card-title">
            <i data-lucide="file-spreadsheet"></i>
            <span>Kartu Stok & Riwayat Mutasi Barang</span>
        </div>
        <span style="font-size:0.8rem; color:var(--text-muted);"><?= count($movements) ?> Catatan Mutasi</span>
    </div>

    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Waktu / Tanggal</th>
                    <th>Jenis Mutasi</th>
                    <th>Referensi Dokumen</th>
                    <th>Perubahan Qty</th>
                    <th>Harga Satuan</th>
                    <th>Keterangan / Catatan</th>
                    <th>Petugas</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($movements)): ?>
                    <tr><td colspan="7" style="text-align:center; padding:24px; color:var(--text-muted);">Belum ada riwayat mutasi untuk barang ini.</td></tr>
                <?php else: ?>
                    <?php foreach ($movements as $m): 
                        $isIn = in_array($m['movement_type'], ['in', 'purchase']);
                        $isOut = in_array($m['movement_type'], ['out', 'service_usage']);
                    ?>
                        <tr>
                            <td><?= format_datetime($m['created_at']) ?></td>
                            <td>
                                <?php if ($m['movement_type'] === 'service_usage'): ?>
                                    <span class="badge badge-amber">Servis Lapangan</span>
                                <?php elseif ($m['movement_type'] === 'in' || $m['movement_type'] === 'purchase'): ?>
                                    <span class="badge badge-green">Stok Masuk</span>
                                <?php elseif ($m['movement_type'] === 'out'): ?>
                                    <span class="badge badge-red">Stok Keluar</span>
                                <?php else: ?>
                                    <span class="badge badge-blue">Penyesuaian</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($m['reference_type'] === 'work_order'): ?>
                                    <a href="/work-orders/<?= $m['reference_id'] ?>" style="font-weight:700; color:var(--secondary);">
                                        Work Order #<?= $m['reference_id'] ?>
                                    </a>
                                <?php elseif ($m['reference_type'] === 'purchase'): ?>
                                    <a href="/purchases/<?= $m['reference_id'] ?>" style="font-weight:700; color:var(--secondary);">
                                        Purchase Order #<?= $m['reference_id'] ?>
                                    </a>
                                <?php else: ?>
                                    <span style="color:var(--text-muted); font-size:0.8rem;"><?= htmlspecialchars($m['reference_type'] ?? 'Manual') ?></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <strong style="font-size:1rem; color:<?= $isIn ? 'var(--success)' : ($isOut ? 'var(--danger)' : 'var(--primary)') ?>;">
                                    <?= $isIn ? '+' : ($isOut ? '-' : '') ?><?= $m['quantity'] ?> <?= htmlspecialchars($item['unit']) ?>
                                </strong>
                            </td>
                            <td><?= format_rupiah($m['unit_price']) ?></td>
                            <td><?= htmlspecialchars($m['notes'] ?? '-') ?></td>
                            <td>
                                <span style="font-size:0.8rem; color:var(--text-muted);"><?= htmlspecialchars($m['created_by_name'] ?? 'Sistem') ?></span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require dirname(__DIR__, 2) . '/layout/footer.php'; ?>
