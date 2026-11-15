<?php
view('layout.header', ['title' => $title]);
view('layout.sidebar');
?>

<div class="content-wrapper">
    <!-- Welcome Banner for Mobile & Desktop -->
    <div class="card border-0 shadow-sm mb-4 bg-primary text-white" style="border-radius: 12px; background: linear-gradient(135deg, #0f2a4a 0%, #2563eb 100%) !important;">
        <div class="card-body p-4 d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <span class="badge bg-light text-primary mb-2">Portal Pelanggan</span>
                <h2 class="fw-bold mb-1">Halo, <?= htmlspecialchars($customer['name']) ?>!</h2>
                <p class="mb-0 text-white-50 small">Kelola jadwal servis AC, riwayat perbaikan, dan pantau teknisi secara mudah dari smartphone Anda.</p>
            </div>
            <div>
                <a href="<?= url('customer-portal/book') ?>" class="btn btn-warning text-navy fw-bold px-4 py-2 shadow-sm">
                    <i data-lucide="plus-circle" class="icon-sm me-1"></i> Pesan Servis Sekarang
                </a>
            </div>
        </div>
    </div>

    <!-- Quick Navigation Tabs for Mobile -->
    <div class="d-flex gap-2 overflow-x-auto pb-3 mb-3">
        <a href="<?= url('customer-portal/orders') ?>" class="btn btn-primary btn-sm rounded-pill px-3">
            <i data-lucide="list-ordered" class="icon-xs me-1"></i> Pesanan Servis
        </a>
        <a href="<?= url('customer-portal/units') ?>" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
            <i data-lucide="cpu" class="icon-xs me-1"></i> Unit AC Saya
        </a>
        <a href="<?= url('customer-portal/invoices') ?>" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
            <i data-lucide="file-text" class="icon-xs me-1"></i> Tagihan
        </a>
        <a href="<?= url('customer-portal/complaints') ?>" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
            <i data-lucide="help-circle" class="icon-xs me-1"></i> Klaim Garansi / Bantuan
        </a>
    </div>

    <!-- Orders List -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold text-navy">Riwayat & Status Pesanan Servis</h5>
            <span class="badge bg-secondary"><?= count($orders) ?> Pesanan</span>
        </div>
        <div class="card-body p-0">
            <?php if (empty($orders)): ?>
                <div class="text-center py-5">
                    <div class="rounded-circle bg-light d-inline-flex p-3 mb-3 text-muted">
                        <i data-lucide="clipboard-x" class="icon-lg"></i>
                    </div>
                    <h5 class="text-navy fw-bold">Belum Ada Pesanan Servis</h5>
                    <p class="text-muted small mb-3">Jadwalkan cuci AC atau perbaikan unit Anda dengan cepat dan mudah.</p>
                    <a href="<?= url('customer-portal/book') ?>" class="btn btn-primary">
                        Buat Pesanan Pertama
                    </a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>No. Pesanan</th>
                                <th>Jadwal Kunjungan</th>
                                <th>Keluhan / Layanan</th>
                                <th>Status Pekerjaan</th>
                                <th>Teknisi Bertugas</th>
                                <th>Tagihan</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orders as $wo): ?>
                                <tr>
                                    <td>
                                        <a href="<?= url('customer-portal/orders/' . $wo['id']) ?>" class="fw-bold text-navy text-decoration-none">
                                            #<?= htmlspecialchars($wo['order_number']) ?>
                                        </a>
                                    </td>
                                    <td>
                                        <div class="fw-semibold"><?= format_date($wo['scheduled_date']) ?></div>
                                        <small class="text-muted"><?= htmlspecialchars($wo['scheduled_time'] ?? '-') ?></small>
                                    </td>
                                    <td>
                                        <div class="small fw-semibold text-truncate" style="max-width: 200px;">
                                            <?= htmlspecialchars($wo['complaints'] ?? '-') ?>
                                        </div>
                                    </td>
                                    <td>
                                        <?php
                                        $badgeClass = match ($wo['status']) {
                                            'BARU' => 'bg-secondary',
                                            'DIKONFIRMASI' => 'bg-info text-dark',
                                            'DIJADWALKAN' => 'bg-primary',
                                            'DALAM_PERJALANAN' => 'bg-warning text-dark',
                                            'SEDANG_DIKERJAKAN' => 'bg-warning text-dark',
                                            'SELESAI' => 'bg-success',
                                            'DITAGIHKAN' => 'bg-warning text-dark',
                                            'LUNAS' => 'bg-success',
                                            'DIBATALKAN' => 'bg-danger',
                                            default => 'bg-light text-dark'
                                        };
                                        ?>
                                        <span class="badge <?= $badgeClass ?>"><?= htmlspecialchars(str_replace('_', ' ', $wo['status'])) ?></span>
                                    </td>
                                    <td>
                                        <?php if (!empty($wo['technician_name'])): ?>
                                            <div class="d-flex align-items-center gap-2">
                                                <i data-lucide="user-check" class="icon-sm text-primary"></i>
                                                <span class="fw-semibold small"><?= htmlspecialchars($wo['technician_name']) ?></span>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-muted small">Menunggu penugasan</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($wo['invoice_id'])): ?>
                                            <?php if ($wo['payment_status'] === 'paid'): ?>
                                                <span class="badge bg-success-subtle text-success">Lunas</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger-subtle text-danger">Belum Lunas: <?= format_rupiah($wo['balance_due']) ?></span>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span class="text-muted small">Estimasi <?= format_rupiah($wo['estimated_price']) ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <a href="<?= url('customer-portal/orders/' . $wo['id']) ?>" class="btn btn-sm btn-outline-primary">
                                            <i data-lucide="eye" class="icon-xs me-1"></i> Pantau
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php view('layout.footer'); ?>
