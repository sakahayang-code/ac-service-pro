<?php
view('layout.header', ['title' => $title]);
view('layout.sidebar');
?>

<div class="content-wrapper">
    <div class="page-header d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 font-weight-bold text-navy mb-0">Riwayat Pembayaran Kasir</h1>
            <p class="text-muted small mb-0">Daftar seluruh penerimaan pembayaran tunai, transfer, dan QRIS</p>
        </div>
        <a href="<?= url('invoices') ?>" class="btn btn-outline-primary">
            <i data-lucide="file-text" class="icon-sm me-1"></i> Lihat Daftar Faktur
        </a>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0 fw-bold text-navy">Semua Kwitansi Pembayaran</h5>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>No. Kwitansi</th>
                        <th>Tanggal</th>
                        <th>No. Faktur</th>
                        <th>Pelanggan</th>
                        <th>Metode</th>
                        <th>Rekening Kas/Bank</th>
                        <th>No. Ref</th>
                        <th class="text-end">Jumlah</th>
                        <th>Petugas</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($payments)): ?>
                        <tr>
                            <td colspan="10" class="text-center py-5 text-muted">Belum ada catatan pembayaran.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($payments as $pay): ?>
                            <tr>
                                <td class="fw-bold text-navy"><?= htmlspecialchars($pay['payment_number']) ?></td>
                                <td><?= format_date($pay['payment_date']) ?></td>
                                <td>
                                    <a href="<?= url('invoices/' . $pay['invoice_id']) ?>" class="fw-semibold text-primary text-decoration-none">
                                        <?= htmlspecialchars($pay['invoice_number']) ?>
                                    </a>
                                </td>
                                <td><?= htmlspecialchars($pay['customer_name']) ?></td>
                                <td>
                                    <span class="badge bg-light text-dark border text-uppercase"><?= htmlspecialchars($pay['payment_method']) ?></span>
                                </td>
                                <td><?= htmlspecialchars($pay['account_name'] ?? 'Kasir') ?></td>
                                <td><small class="text-muted"><?= htmlspecialchars($pay['reference_number'] ?: '-') ?></small></td>
                                <td class="text-end fw-bold text-success">+ <?= format_rupiah($pay['amount']) ?></td>
                                <td><small class="text-muted"><?= htmlspecialchars($pay['received_by_name'] ?? 'Admin') ?></small></td>
                                <td class="text-center">
                                    <a href="<?= url('invoices/' . $pay['invoice_id']) ?>" class="btn btn-sm btn-outline-secondary" title="Lihat Faktur">
                                        <i data-lucide="eye" class="icon-sm"></i>
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
