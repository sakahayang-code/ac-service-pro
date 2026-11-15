<?php require dirname(__DIR__, 2) . '/layout/header.php'; ?>

<div class="card">
    <div style="display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:16px;">
        <div>
            <div style="display:flex; align-items:center; gap:8px; margin-bottom:6px;">
                <span class="badge badge-blue"><?= htmlspecialchars($purchase['purchase_number']) ?></span>
                <span class="badge badge-green"><?= strtoupper($purchase['status']) ?></span>
                <span class="badge badge-green"><?= strtoupper($purchase['payment_status']) ?></span>
            </div>
            <h2 style="font-size:1.4rem; color:var(--primary); font-weight:800;">
                PO Pembelian Sparepart - <?= htmlspecialchars($purchase['supplier_name']) ?>
            </h2>
            <div style="font-size:0.85rem; color:var(--text-muted); margin-top:4px;">
                Tanggal Pembelian: <?= format_date($purchase['purchase_date']) ?> &bull; Petugas: <?= htmlspecialchars($purchase['created_by_name'] ?? 'Admin') ?>
            </div>
            <?php if (!empty($purchase['notes'])): ?>
                <div style="font-size:0.85rem; color:var(--text-main); margin-top:6px; background:var(--surface-alt); padding:6px 12px; border-radius:4px; display:inline-block;">
                    Catatan: <?= htmlspecialchars($purchase['notes']) ?>
                </div>
            <?php endif; ?>
        </div>

        <div style="text-align:right;">
            <div style="font-size:0.8rem; color:var(--text-muted);">Total Tagihan PO</div>
            <div style="font-size:1.6rem; font-weight:900; color:var(--primary);">
                <?= format_rupiah($purchase['total_amount']) ?>
            </div>
            <a href="/purchases" class="btn btn-outline btn-sm" style="margin-top:8px;">Kembali ke Daftar PO</a>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <div class="card-title">
            <i data-lucide="list"></i>
            <span>Item Suku Cadang yang Diterima</span>
        </div>
    </div>
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>SKU</th>
                    <th>Nama Barang</th>
                    <th>Jumlah Diterima</th>
                    <th>Harga Satuan (HPP)</th>
                    <th>Subtotal Biaya</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $it): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($it['sku']) ?></strong></td>
                        <td><?= htmlspecialchars($it['item_name']) ?></td>
                        <td><strong><?= $it['quantity'] ?> <?= htmlspecialchars($it['unit']) ?></strong></td>
                        <td><?= format_rupiah($it['unit_cost']) ?></td>
                        <td><strong><?= format_rupiah($it['subtotal']) ?></strong></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr style="background:var(--surface-alt);">
                    <td colspan="4" style="text-align:right; font-weight:700;">Grand Total:</td>
                    <td><strong style="color:var(--primary); font-size:1.1rem;"><?= format_rupiah($purchase['total_amount']) ?></strong></td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

<?php require dirname(__DIR__, 2) . '/layout/footer.php'; ?>
