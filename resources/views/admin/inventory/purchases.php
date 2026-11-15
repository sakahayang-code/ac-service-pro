<?php require dirname(__DIR__, 2) . '/layout/header.php'; ?>

<div class="card">
    <div class="card-header">
        <div class="card-title">
            <i data-lucide="shopping-cart"></i>
            <span>Daftar Pembelian & Pengadaan (Purchase Order)</span>
        </div>
        <a href="/purchases/create" class="btn btn-primary btn-sm">
            <i data-lucide="plus-circle" style="width:16px; height:16px;"></i>
            <span>Buat PO Baru</span>
        </a>
    </div>

    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>No. PO</th>
                    <th>Supplier / Pemasok</th>
                    <th>Tanggal Belanja</th>
                    <th>Total Belanja</th>
                    <th>Status Barang</th>
                    <th>Status Bayar</th>
                    <th>Dibuat Oleh</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($purchases)): ?>
                    <tr><td colspan="8" style="text-align:center; padding:24px; color:var(--text-muted);">Belum ada riwayat pembelian (PO).</td></tr>
                <?php else: ?>
                    <?php foreach ($purchases as $p): ?>
                        <tr>
                            <td><strong style="color:var(--primary);"><?= htmlspecialchars($p['purchase_number']) ?></strong></td>
                            <td><strong style="color:var(--text-main);"><?= htmlspecialchars($p['supplier_name']) ?></strong></td>
                            <td><?= format_date($p['purchase_date']) ?></td>
                            <td><strong><?= format_rupiah($p['total_amount']) ?></strong></td>
                            <td><span class="badge badge-green"><?= strtoupper($p['status']) ?></span></td>
                            <td><span class="badge badge-green"><?= strtoupper($p['payment_status']) ?></span></td>
                            <td><?= htmlspecialchars($p['created_by_name'] ?? 'Admin') ?></td>
                            <td>
                                <a href="/purchases/<?= $p['id'] ?>" class="btn btn-outline btn-sm">Lihat Detail</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require dirname(__DIR__, 2) . '/layout/footer.php'; ?>
