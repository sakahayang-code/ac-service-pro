<?php
view('layout.header', ['title' => $title]);
view('layout.sidebar');
?>

<div class="content-wrapper">
    <div class="page-header d-flex justify-content-between align-items-center mb-4">
        <div>
            <div class="text-muted small">
                <a href="<?= url('reports') ?>" class="text-decoration-none">Laporan</a> / Keuangan
            </div>
            <h1 class="h3 font-weight-bold text-navy mb-0">Laporan Keuangan & Laba Rugi</h1>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= url('reports/finance?start_date=' . $startDate . '&end_date=' . $endDate . '&export=csv') ?>" class="btn btn-success">
                <i data-lucide="download" class="icon-sm me-1"></i> Ekspor Excel (CSV)
            </a>
            <button onclick="window.print()" class="btn btn-outline-secondary">
                <i data-lucide="printer" class="icon-sm me-1"></i> Cetak Laporan
            </button>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-3">
            <form action="<?= url('reports/finance') ?>" method="GET" class="row g-2 align-items-center">
                <div class="col-md-5">
                    <label class="form-label small text-muted mb-1">Dari Tanggal</label>
                    <input type="date" name="start_date" class="form-control" value="<?= htmlspecialchars($startDate) ?>">
                </div>
                <div class="col-md-5">
                    <label class="form-label small text-muted mb-1">Sampai Tanggal</label>
                    <input type="date" name="end_date" class="form-control" value="<?= htmlspecialchars($endDate) ?>">
                </div>
                <div class="col-md-2 mt-auto">
                    <button type="submit" class="btn btn-primary w-100">
                        <i data-lucide="filter" class="icon-sm me-1"></i> Terapkan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Income Statement Card -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0 fw-bold text-navy">Laporan Laba Rugi Komprehensif (<?= format_date($startDate) ?> s/d <?= format_date($endDate) ?>)</h5>
        </div>
        <div class="table-responsive">
            <table class="table table-bordered mb-0">
                <tbody>
                    <tr class="table-light">
                        <th colspan="2" class="text-navy fw-bold">1. PENDAPATAN OPERASIONAL</th>
                        <th class="text-end text-navy fw-bold" style="width: 25%;">Nominal</th>
                    </tr>
                    <tr>
                        <td style="width: 5%;" class="text-center">1.1</td>
                        <td>Pendapatan Layanan Jasa Servis AC</td>
                        <td class="text-end fw-semibold text-success">+ <?= format_rupiah($serviceRevenue) ?></td>
                    </tr>
                    <tr>
                        <td class="text-center">1.2</td>
                        <td>Pendapatan Penjualan Sparepart, Freon & Material</td>
                        <td class="text-end fw-semibold text-success">+ <?= format_rupiah($materialRevenue) ?></td>
                    </tr>
                    <tr class="fw-bold bg-light">
                        <td colspan="2" class="text-end">TOTAL PENDAPATAN USAHA:</td>
                        <td class="text-end text-navy"><?= format_rupiah($totalRevenue) ?></td>
                    </tr>

                    <tr class="table-light">
                        <th colspan="2" class="text-navy fw-bold">2. BEBAN POKOK PENJUALAN (HPP / COGS)</th>
                        <th class="text-end text-navy fw-bold">Nominal</th>
                    </tr>
                    <tr>
                        <td class="text-center">2.1</td>
                        <td>Harga Pokok Pemakaian Freon & Sparepart Pengerjaan</td>
                        <td class="text-end fw-semibold text-danger">- <?= format_rupiah($cogs) ?></td>
                    </tr>
                    <tr class="fw-bold bg-light">
                        <td colspan="2" class="text-end">LABA KOTOR (GROSS PROFIT):</td>
                        <td class="text-end text-primary"><?= format_rupiah($grossProfit) ?></td>
                    </tr>

                    <tr class="table-light">
                        <th colspan="2" class="text-navy fw-bold">3. BEBAN OPERASIONAL USAHA</th>
                        <th class="text-end text-navy fw-bold">Nominal</th>
                    </tr>
                    <tr>
                        <td class="text-center">3.1</td>
                        <td>Total Biaya Operasional, Gaji, Transport & Perlengkapan</td>
                        <td class="text-end fw-semibold text-danger">- <?= format_rupiah($totalExpenses) ?></td>
                    </tr>

                    <tr class="fs-5 bg-white">
                        <td colspan="2" class="text-end fw-bold text-navy">LABA BERSIH PERIODE INI (NET PROFIT):</td>
                        <td class="text-end fw-bold <?= $netProfit >= 0 ? 'text-success' : 'text-danger' ?>">
                            <?= format_rupiah($netProfit) ?>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Invoices Breakdown Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0 fw-bold text-navy">Rincian Faktur Penjualan dalam Periode</h5>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>No. Faktur</th>
                        <th>Tanggal Terbit</th>
                        <th>Pelanggan</th>
                        <th class="text-end">Jasa</th>
                        <th class="text-end">Material</th>
                        <th class="text-end">Total</th>
                        <th class="text-end">Terbayar</th>
                        <th class="text-end">Piutang</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($invoices)): ?>
                        <tr>
                            <td colspan="9" class="text-center py-4 text-muted">Belum ada faktur yang diterbitkan pada rentang tanggal ini.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($invoices as $inv): ?>
                            <tr>
                                <td class="fw-bold text-navy">
                                    <a href="<?= url('invoices/' . $inv['id']) ?>" class="text-decoration-none">
                                        <?= htmlspecialchars($inv['invoice_number']) ?>
                                    </a>
                                </td>
                                <td><?= format_date($inv['issue_date']) ?></td>
                                <td><?= htmlspecialchars($inv['customer_name']) ?></td>
                                <td class="text-end"><?= format_rupiah($inv['subtotal_services']) ?></td>
                                <td class="text-end"><?= format_rupiah($inv['subtotal_materials']) ?></td>
                                <td class="text-end fw-bold text-navy"><?= format_rupiah($inv['total_amount']) ?></td>
                                <td class="text-end fw-semibold text-success"><?= format_rupiah($inv['paid_amount']) ?></td>
                                <td class="text-end fw-semibold text-danger"><?= format_rupiah($inv['balance_due']) ?></td>
                                <td>
                                    <?php if ($inv['payment_status'] === 'paid'): ?>
                                        <span class="badge bg-success">Lunas</span>
                                    <?php elseif ($inv['payment_status'] === 'partial'): ?>
                                        <span class="badge bg-warning text-dark">Sebagian</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Belum Lunas</span>
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
