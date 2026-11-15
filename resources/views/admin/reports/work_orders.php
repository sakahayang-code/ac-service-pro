<?php
view('layout.header', ['title' => $title]);
view('layout.sidebar');

$totalCount = count($orders);
$completedCount = 0;
$cancelledCount = 0;
$totalBilled = 0;
foreach ($orders as $o) {
    if (in_array($o['status'], ['SELESAI', 'DITAGIHKAN', 'LUNAS'])) $completedCount++;
    if ($o['status'] === 'DIBATALKAN') $cancelledCount++;
    $totalBilled += (int)($o['total_amount'] ?? 0);
}
?>

<div class="content-wrapper">
    <div class="page-header d-flex justify-content-between align-items-center mb-4">
        <div>
            <div class="text-muted small">
                <a href="<?= url('reports') ?>" class="text-decoration-none">Laporan</a> / Work Orders
            </div>
            <h1 class="h3 font-weight-bold text-navy mb-0">Laporan Surat Perintah Kerja (SPK)</h1>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= url('reports/work-orders?start_date=' . $startDate . '&end_date=' . $endDate . '&status=' . $selectedStatus . '&export=csv') ?>" class="btn btn-success">
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
            <form action="<?= url('reports/work-orders') ?>" method="GET" class="row g-2 align-items-center">
                <div class="col-md-3">
                    <label class="form-label small text-muted mb-1">Dari Tanggal</label>
                    <input type="date" name="start_date" class="form-control" value="<?= htmlspecialchars($startDate) ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-muted mb-1">Sampai Tanggal</label>
                    <input type="date" name="end_date" class="form-control" value="<?= htmlspecialchars($endDate) ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label small text-muted mb-1">Status SPK</label>
                    <select name="status" class="form-select">
                        <option value="">-- Semua Status --</option>
                        <option value="BARU" <?= $selectedStatus === 'BARU' ? 'selected' : '' ?>>Baru</option>
                        <option value="DIJADWALKAN" <?= $selectedStatus === 'DIJADWALKAN' ? 'selected' : '' ?>>Dijadwalkan</option>
                        <option value="SEDANG_DIKERJAKAN" <?= $selectedStatus === 'SEDANG_DIKERJAKAN' ? 'selected' : '' ?>>Sedang Dikerjakan</option>
                        <option value="SELESAI" <?= $selectedStatus === 'SELESAI' ? 'selected' : '' ?>>Selesai</option>
                        <option value="LUNAS" <?= $selectedStatus === 'LUNAS' ? 'selected' : '' ?>>Lunas</option>
                        <option value="DIBATALKAN" <?= $selectedStatus === 'DIBATALKAN' ? 'selected' : '' ?>>Dibatalkan</option>
                    </select>
                </div>
                <div class="col-md-2 mt-auto">
                    <button type="submit" class="btn btn-primary w-100">
                        <i data-lucide="filter" class="icon-sm me-1"></i> Terapkan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Summary Stats -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="p-3 bg-white rounded shadow-sm border text-center">
                <div class="text-muted small">Total SPK Terbit</div>
                <div class="fs-4 fw-bold text-navy"><?= $totalCount ?></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="p-3 bg-white rounded shadow-sm border text-center">
                <div class="text-muted small">SPK Berhasil Selesai</div>
                <div class="fs-4 fw-bold text-success"><?= $completedCount ?></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="p-3 bg-white rounded shadow-sm border text-center">
                <div class="text-muted small">SPK Dibatalkan</div>
                <div class="fs-4 fw-bold text-danger"><?= $cancelledCount ?></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="p-3 bg-white rounded shadow-sm border text-center">
                <div class="text-muted small">Total Tagihan SPK</div>
                <div class="fs-4 fw-bold text-primary"><?= format_rupiah($totalBilled) ?></div>
            </div>
        </div>
    </div>

    <!-- Orders Table -->
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>No. SPK</th>
                        <th>Tanggal Servis</th>
                        <th>Pelanggan</th>
                        <th>Kategori</th>
                        <th>Teknisi Bertugas</th>
                        <th>Keluhan</th>
                        <th>Status</th>
                        <th class="text-end">Nilai Faktur</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($orders)): ?>
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">Tidak ada data pekerjaan untuk periode yang dipilih.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($orders as $o): ?>
                            <tr>
                                <td class="fw-bold text-navy">
                                    <a href="<?= url('work-orders/' . $o['id']) ?>" class="text-decoration-none">
                                        #<?= htmlspecialchars($o['order_number']) ?>
                                    </a>
                                </td>
                                <td><?= format_date($o['scheduled_date']) ?></td>
                                <td class="fw-semibold"><?= htmlspecialchars($o['customer_name']) ?></td>
                                <td><span class="badge bg-light text-dark border text-capitalize"><?= htmlspecialchars($o['customer_category']) ?></span></td>
                                <td><?= htmlspecialchars($o['technician_name'] ?? 'Belum Ditugaskan') ?></td>
                                <td><small class="text-muted text-truncate d-inline-block" style="max-width: 180px;"><?= htmlspecialchars($o['complaints']) ?></small></td>
                                <td>
                                    <?php
                                    $badge = match ($o['status']) {
                                        'SELESAI', 'LUNAS' => 'bg-success',
                                        'DIBATALKAN' => 'bg-danger',
                                        'SEDANG_DIKERJAKAN', 'DALAM_PERJALANAN' => 'bg-warning text-dark',
                                        default => 'bg-primary'
                                    };
                                    ?>
                                    <span class="badge <?= $badge ?>"><?= htmlspecialchars(str_replace('_', ' ', $o['status'])) ?></span>
                                </td>
                                <td class="text-end fw-bold text-navy"><?= format_rupiah($o['total_amount'] ?? 0) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php view('layout.footer'); ?>
