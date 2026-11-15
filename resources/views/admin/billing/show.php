<?php
view('layout.header', ['title' => $title]);
view('layout.sidebar');
?>

<div class="content-wrapper">
    <div class="page-header d-flex justify-content-between align-items-center mb-4">
        <div>
            <div class="text-muted small">
                <a href="<?= url('invoices') ?>" class="text-decoration-none">Faktur</a> / Detail
            </div>
            <h1 class="h3 font-weight-bold text-navy mb-0">Faktur #<?= htmlspecialchars($invoice['invoice_number']) ?></h1>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= url('invoices/' . $invoice['id'] . '/print') ?>" target="_blank" class="btn btn-outline-secondary">
                <i data-lucide="printer" class="icon-sm me-1"></i> Cetak / PDF
            </a>
            <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $invoice['customer_phone']) ?>?text=<?= urlencode("Halo Bapak/Ibu {$invoice['customer_name']}, berikut tagihan servis AC Service Pro No. {$invoice['invoice_number']} sebesar " . format_rupiah($invoice['total_amount']) . " (Sisa: " . format_rupiah($invoice['balance_due']) . "). Terima kasih.") ?>" target="_blank" class="btn btn-success">
                <i data-lucide="message-circle" class="icon-sm me-1"></i> Kirim WhatsApp
            </a>
            <?php if ($invoice['payment_status'] !== 'paid'): ?>
                <button type="button" class="btn btn-primary" onclick="openPaymentModal()">
                    <i data-lucide="credit-card" class="icon-sm me-1"></i> Catat Pembayaran
                </button>
            <?php endif; ?>
        </div>
    </div>

    <!-- Alert / Status Banner -->
    <div class="card mb-4 border-0 shadow-sm">
        <div class="card-body p-3 d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-circle p-2 <?= $invoice['payment_status'] === 'paid' ? 'bg-success-subtle text-success' : ($invoice['payment_status'] === 'partial' ? 'bg-warning-subtle text-warning' : 'bg-danger-subtle text-danger') ?>">
                    <i data-lucide="<?= $invoice['payment_status'] === 'paid' ? 'check-circle' : 'alert-circle' ?>" class="icon-md"></i>
                </div>
                <div>
                    <div class="fw-bold fs-5">
                        Status Pembayaran: 
                        <?php if ($invoice['payment_status'] === 'paid'): ?>
                            <span class="badge bg-success">LUNAS</span>
                        <?php elseif ($invoice['payment_status'] === 'partial'): ?>
                            <span class="badge bg-warning text-dark">DIBAYAR SEBAGIAN</span>
                        <?php else: ?>
                            <span class="badge bg-danger">BELUM DIBAYAR</span>
                        <?php endif; ?>
                    </div>
                    <div class="text-muted small">
                        Tgl Terbit: <?= format_date($invoice['issue_date']) ?> | Jatuh Tempo: <?= format_date($invoice['due_date']) ?>
                    </div>
                </div>
            </div>
            <div class="d-flex gap-4 text-end">
                <div>
                    <div class="text-muted small">Total Tagihan</div>
                    <div class="fs-4 fw-bold text-navy"><?= format_rupiah($invoice['total_amount']) ?></div>
                </div>
                <div>
                    <div class="text-muted small">Sudah Dibayar</div>
                    <div class="fs-4 fw-bold text-success"><?= format_rupiah($invoice['paid_amount']) ?></div>
                </div>
                <div>
                    <div class="text-muted small">Sisa Piutang</div>
                    <div class="fs-4 fw-bold text-danger"><?= format_rupiah($invoice['balance_due']) ?></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Main Column: Invoice Items -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-bold text-navy">Rincian Jasa & Material</h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Tipe</th>
                                <th>Deskripsi Item</th>
                                <th class="text-center">Qty</th>
                                <th class="text-end">Harga Satuan</th>
                                <th class="text-end">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($items)): ?>
                                <tr>
                                    <td colspan="6" class="text-center py-3 text-muted">Belum ada item tagihan</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($items as $idx => $it): ?>
                                    <tr>
                                        <td><?= $idx + 1 ?></td>
                                        <td>
                                            <?php if ($it['item_type'] === 'service'): ?>
                                                <span class="badge bg-primary-subtle text-primary">Jasa Servis</span>
                                            <?php else: ?>
                                                <span class="badge bg-info-subtle text-info">Material/Part</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="fw-semibold"><?= htmlspecialchars($it['description']) ?></td>
                                        <td class="text-center"><?= (float)$it['quantity'] ?></td>
                                        <td class="text-end"><?= format_rupiah($it['unit_price']) ?></td>
                                        <td class="text-end fw-bold"><?= format_rupiah($it['subtotal']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <td colspan="5" class="text-end fw-semibold">Subtotal Jasa:</td>
                                <td class="text-end fw-bold"><?= format_rupiah($invoice['subtotal_services']) ?></td>
                            </tr>
                            <tr>
                                <td colspan="5" class="text-end fw-semibold">Subtotal Material / Sparepart:</td>
                                <td class="text-end fw-bold"><?= format_rupiah($invoice['subtotal_materials']) ?></td>
                            </tr>
                            <?php if ($invoice['visit_fee'] > 0): ?>
                                <tr>
                                    <td colspan="5" class="text-end fw-semibold">Biaya Kunjungan / Transport:</td>
                                    <td class="text-end fw-bold text-navy"><?= format_rupiah($invoice['visit_fee']) ?></td>
                                </tr>
                            <?php endif; ?>
                            <?php if ($invoice['discount_amount'] > 0): ?>
                                <tr>
                                    <td colspan="5" class="text-end fw-semibold text-danger">Diskon / Potongan:</td>
                                    <td class="text-end fw-bold text-danger">- <?= format_rupiah($invoice['discount_amount']) ?></td>
                                </tr>
                            <?php endif; ?>
                            <tr class="fs-5 bg-white">
                                <td colspan="5" class="text-end fw-bold text-navy">TOTAL AKHIR:</td>
                                <td class="text-end fw-bold text-navy"><?= format_rupiah($invoice['total_amount']) ?></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <!-- Payment History Table -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold text-navy">Riwayat Pembayaran Diterima</h5>
                    <span class="badge bg-secondary"><?= count($payments) ?> Transaksi</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>No. Kwitansi</th>
                                <th>Tanggal</th>
                                <th>Metode</th>
                                <th>Rekening Kas</th>
                                <th>No. Ref</th>
                                <th class="text-end">Jumlah</th>
                                <th>Penerima</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($payments)): ?>
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-muted">Belum ada pembayaran yang dicatat untuk faktur ini.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($payments as $pay): ?>
                                    <tr>
                                        <td class="fw-bold text-navy"><?= htmlspecialchars($pay['payment_number']) ?></td>
                                        <td><?= format_date($pay['payment_date']) ?></td>
                                        <td>
                                            <span class="badge bg-light text-dark text-uppercase border"><?= htmlspecialchars($pay['payment_method']) ?></span>
                                        </td>
                                        <td><?= htmlspecialchars($pay['account_name'] ?? 'Kasir') ?></td>
                                        <td><small class="text-muted"><?= htmlspecialchars($pay['reference_number'] ?: '-') ?></small></td>
                                        <td class="text-end fw-bold text-success">+ <?= format_rupiah($pay['amount']) ?></td>
                                        <td><small class="text-muted"><?= htmlspecialchars($pay['received_by_name'] ?? 'Sistem') ?></small></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Sidebar Column: Customer Info & WO Link -->
        <div class="col-lg-4">
            <!-- Customer Card -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-bold text-navy">Informasi Pelanggan</h5>
                </div>
                <div class="card-body">
                    <h5 class="fw-bold text-navy mb-1"><?= htmlspecialchars($invoice['customer_name']) ?></h5>
                    <div class="text-muted small mb-3">Kode: <?= htmlspecialchars($invoice['customer_code']) ?></div>
                    <div class="mb-2">
                        <i data-lucide="phone" class="icon-sm text-muted me-2"></i>
                        <a href="tel:<?= htmlspecialchars($invoice['customer_phone']) ?>" class="text-decoration-none">
                            <?= htmlspecialchars($invoice['customer_phone']) ?>
                        </a>
                    </div>
                    <div>
                        <i data-lucide="map-pin" class="icon-sm text-muted me-2"></i>
                        <span><?= nl2br(htmlspecialchars($invoice['customer_address'])) ?></span>
                    </div>
                </div>
            </div>

            <!-- Linked Work Order Card -->
            <?php if (!empty($invoice['work_order_id'])): ?>
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0 fw-bold text-navy">Tautan Surat Perintah Kerja</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="fw-bold text-primary">#<?= htmlspecialchars($invoice['order_number']) ?></span>
                            <a href="<?= url('work-orders/' . $invoice['work_order_id']) ?>" class="btn btn-sm btn-outline-primary">
                                Buka SPK
                            </a>
                        </div>
                        <div class="text-muted small">
                            Tgl Servis: <?= format_date($invoice['wo_date'] ?? $invoice['issue_date']) ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Notes Card -->
            <?php if (!empty($invoice['notes'])): ?>
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0 fw-bold text-navy">Catatan Faktur</h5>
                    </div>
                    <div class="card-body">
                        <p class="small text-muted mb-0"><?= nl2br(htmlspecialchars($invoice['notes'])) ?></p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal Catat Pembayaran -->
<div class="modal fade" id="paymentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form action="<?= url('invoices/' . $invoice['id'] . '/payment') ?>" method="POST" class="modal-content">
            <?= csrf_field() ?>
            <div class="modal-header">
                <h5 class="modal-title fw-bold text-navy">Catat Pembayaran Faktur</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info py-2 small mb-3">
                    Faktur <strong>#<?= htmlspecialchars($invoice['invoice_number']) ?></strong><br>
                    Sisa Piutang: <strong><?= format_rupiah($invoice['balance_due']) ?></strong>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Jumlah Pembayaran (Rp) <span class="text-danger">*</span></label>
                    <input type="number" name="amount" class="form-control" value="<?= (int)$invoice['balance_due'] ?>" min="1" max="<?= (int)$invoice['balance_due'] ?>" required>
                    <div class="form-text">Masukkan jumlah pembayaran penuh atau bertahap (DP / termin).</div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Metode Pembayaran <span class="text-danger">*</span></label>
                    <select name="payment_method" class="form-select" required>
                        <option value="cash">Tunai (Cash)</option>
                        <option value="transfer">Transfer Bank</option>
                        <option value="qris">QRIS</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Rekening Penerima Kas / Bank <span class="text-danger">*</span></label>
                    <select name="account_id" class="form-select" required>
                        <?php foreach ($accounts as $acc): ?>
                            <option value="<?= $acc['id'] ?>"><?= htmlspecialchars($acc['name']) ?> (<?= htmlspecialchars($acc['code']) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Tanggal Pembayaran <span class="text-danger">*</span></label>
                    <input type="date" name="payment_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">No. Referensi / Bukti Transfer</label>
                    <input type="text" name="reference_number" class="form-control" placeholder="Contoh: REF-BCA-847291">
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Catatan</label>
                    <textarea name="notes" class="form-control" rows="2" placeholder="Catatan pembayaran..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan & Perbarui Saldo</button>
            </div>
        </form>
    </div>
</div>

<script>
function openPaymentModal() {
    const modal = new bootstrap.Modal(document.getElementById('paymentModal'));
    modal.show();
}
</script>

<?php view('layout.footer'); ?>
