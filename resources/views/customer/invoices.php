<?php
view('layout.header', ['title' => $title]);
view('layout.sidebar');
?>

<div class="content-wrapper">
    <div class="page-header d-flex justify-content-between align-items-center mb-4">
        <div>
            <div class="text-muted small">
                <a href="<?= url('customer-portal/orders') ?>" class="text-decoration-none">Portal</a> / Faktur
            </div>
            <h1 class="h3 font-weight-bold text-navy mb-0">Tagihan & Faktur Servis</h1>
            <p class="text-muted small mb-0">Daftar seluruh invoice pengerjaan servis AC dan bukti pembayaran Anda</p>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0 fw-bold text-navy">Semua Faktur Tagihan</h5>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>No. Faktur</th>
                        <th>Tanggal Terbit</th>
                        <th>Jatuh Tempo</th>
                        <th>Terkait SPK</th>
                        <th>Status</th>
                        <th class="text-end">Total Biaya</th>
                        <th class="text-end">Sisa Bayar</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($invoices)): ?>
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">Belum ada faktur tagihan untuk akun Anda.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($invoices as $inv): ?>
                            <tr>
                                <td class="fw-bold text-navy">#<?= htmlspecialchars($inv['invoice_number']) ?></td>
                                <td><?= format_date($inv['issue_date']) ?></td>
                                <td><?= format_date($inv['due_date']) ?></td>
                                <td>
                                    <?php if (!empty($inv['order_number'])): ?>
                                        <a href="<?= url('customer-portal/orders/' . $inv['work_order_id']) ?>" class="badge bg-light text-primary border text-decoration-none">
                                            #<?= htmlspecialchars($inv['order_number']) ?>
                                        </a>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($inv['payment_status'] === 'paid'): ?>
                                        <span class="badge bg-success">LUNAS</span>
                                    <?php elseif ($inv['payment_status'] === 'partial'): ?>
                                        <span class="badge bg-warning text-dark">SEBAGIAN</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">BELUM LUNAS</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end fw-bold text-navy"><?= format_rupiah($inv['total_amount']) ?></td>
                                <td class="text-end fw-bold <?= $inv['balance_due'] > 0 ? 'text-danger' : 'text-success' ?>">
                                    <?= format_rupiah($inv['balance_due']) ?>
                                </td>
                                <td class="text-center">
                                    <a href="<?= url('invoices/' . $inv['id'] . '/print') ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                        <i data-lucide="printer" class="icon-xs me-1"></i> Cetak / Bayar
                                    </a>
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
