<?php
view('layout.header', ['title' => $title]);
view('layout.sidebar');
?>

<div class="content-wrapper">
    <div class="page-header d-flex justify-content-between align-items-center mb-4">
        <div>
            <div class="text-muted small">
                <a href="<?= url('reports') ?>" class="text-decoration-none">Laporan</a> / Inventori
            </div>
            <h1 class="h3 font-weight-bold text-navy mb-0">Laporan Nilai Stok & Pemakaian Sparepart</h1>
            <p class="text-muted small mb-0">Valuasi aset persediaan suku cadang, rekap pemakaian pengerjaan servis, dan status batas minimum</p>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= url('reports/inventory?export=csv') ?>" class="btn btn-success">
                <i data-lucide="download" class="icon-sm me-1"></i> Ekspor Excel (CSV)
            </a>
            <button onclick="window.print()" class="btn btn-outline-secondary">
                <i data-lucide="printer" class="icon-sm me-1"></i> Cetak Laporan
            </button>
        </div>
    </div>

    <!-- Inventory Valuation Metrics -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <span class="text-muted small text-uppercase fw-semibold">Total Nilai Persediaan (HPP)</span>
                    <div class="fs-3 fw-bold text-navy mt-1"><?= format_rupiah($totalValuation) ?></div>
                    <small class="text-muted">Berdasarkan Harga Beli Terakhir</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <span class="text-muted small text-uppercase fw-semibold">Total Fisik Unit Barang</span>
                    <div class="fs-3 fw-bold text-primary mt-1"><?= number_format($totalItemsCount, 0, ',', '.') ?> Pcs/Kg</div>
                    <small class="text-muted">Seluruh kategori suku cadang</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <span class="text-muted small text-uppercase fw-semibold">Item Mendekati Habis</span>
                    <div class="fs-3 fw-bold <?= $lowStockCount > 0 ? 'text-danger' : 'text-success' ?> mt-1">
                        <?= $lowStockCount ?> SKU
                    </div>
                    <small class="<?= $lowStockCount > 0 ? 'text-danger fw-semibold' : 'text-muted' ?>">
                        <?= $lowStockCount > 0 ? 'Perlu dibuatkan Purchase Order (PO)' : 'Semua stok dalam batas aman' ?>
                    </small>
                </div>
            </div>
        </div>
    </div>

    <!-- Inventory Table -->
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Kode SKU</th>
                        <th>Nama Sparepart / Material</th>
                        <th>Kategori</th>
                        <th class="text-center">Stok Saat Ini</th>
                        <th class="text-center">Min. Stok</th>
                        <th class="text-end">Harga Beli (HPP)</th>
                        <th class="text-end">Harga Jual</th>
                        <th class="text-end">Total Nilai Aset</th>
                        <th class="text-center">Terpakai di WO</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($items)): ?>
                        <tr>
                            <td colspan="10" class="text-center py-5 text-muted">Belum ada data barang inventori.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($items as $it): 
                            $isLow = (int)$it['current_stock'] <= (int)$it['min_stock'];
                        ?>
                            <tr class="<?= $isLow ? 'table-warning-subtle' : '' ?>">
                                <td class="fw-bold text-navy">
                                    <a href="<?= url('inventory/' . $it['id']) ?>" class="text-decoration-none">
                                        <?= htmlspecialchars($it['sku']) ?>
                                    </a>
                                </td>
                                <td class="fw-semibold"><?= htmlspecialchars($it['name']) ?></td>
                                <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($it['category'] ?? 'sparepart') ?></span></td>
                                <td class="text-center fw-bold fs-6 <?= $isLow ? 'text-danger' : 'text-navy' ?>">
                                    <?= (float)$it['current_stock'] ?> <?= htmlspecialchars($it['unit']) ?>
                                </td>
                                <td class="text-center text-muted"><?= (float)$it['min_stock'] ?></td>
                                <td class="text-end"><?= format_rupiah($it['cost_price']) ?></td>
                                <td class="text-end fw-semibold text-primary"><?= format_rupiah($it['selling_price']) ?></td>
                                <td class="text-end fw-bold text-navy"><?= format_rupiah($it['valuation_purchase']) ?></td>
                                <td class="text-center fw-semibold text-secondary"><?= (float)$it['total_used'] ?></td>
                                <td>
                                    <?php if ($isLow): ?>
                                        <span class="badge bg-danger">STOK MENIPIS</span>
                                    <?php else: ?>
                                        <span class="badge bg-success">AMAN</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php view('layout.footer'); ?>
