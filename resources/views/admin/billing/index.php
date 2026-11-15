<?php require dirname(__DIR__, 2) . '/layout/header.php'; ?>

<!-- Billing Stats -->
<div class="stat-grid" style="margin-bottom:16px;">
    <div class="stat-card">
        <div class="stat-icon success">
            <i data-lucide="wallet" style="width:24px; height:24px;"></i>
        </div>
        <div>
            <div class="stat-value"><?= format_rupiah($totalPaidMonth) ?></div>
            <div class="stat-label">Penerimaan Kas Bulan Ini</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon danger">
            <i data-lucide="clock" style="width:24px; height:24px;"></i>
        </div>
        <div>
            <div class="stat-value"><?= format_rupiah($totalReceivables) ?></div>
            <div class="stat-label">Total Piutang Belum Lunas</div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <div class="card-title">
            <i data-lucide="receipt"></i>
            <span>Daftar Faktur / Invoice Pelanggan</span>
        </div>
        <div style="display:flex; gap:10px;">
            <a href="/payments" class="btn btn-outline btn-sm">
                <i data-lucide="history" style="width:16px; height:16px;"></i> Riwayat Pembayaran
            </a>
            <a href="/invoices/create" class="btn btn-primary btn-sm">
                <i data-lucide="plus-circle" style="width:16px; height:16px;"></i> Terbitkan Faktur Baru
            </a>
        </div>
    </div>

    <!-- Status Tabs -->
    <div style="display:flex; gap:8px; margin-bottom:16px; overflow-x:auto;">
        <a href="/invoices" class="badge <?= $currentStatus === '' ? 'badge-blue' : 'badge-gray' ?>" style="padding:8px 14px; font-size:0.75rem; text-decoration:none;">
            Semua Faktur
        </a>
        <a href="/invoices?status=unpaid" class="badge <?= $currentStatus === 'unpaid' ? 'badge-blue' : 'badge-gray' ?>" style="padding:8px 14px; font-size:0.75rem; text-decoration:none;">
            Belum Dibayar
        </a>
        <a href="/invoices?status=partial" class="badge <?= $currentStatus === 'partial' ? 'badge-blue' : 'badge-gray' ?>" style="padding:8px 14px; font-size:0.75rem; text-decoration:none;">
            Dibayar Sebagian (DP)
        </a>
        <a href="/invoices?status=paid" class="badge <?= $currentStatus === 'paid' ? 'badge-blue' : 'badge-gray' ?>" style="padding:8px 14px; font-size:0.75rem; text-decoration:none;">
            Lunas
        </a>
    </div>

    <!-- Filter Bar -->
    <form method="GET" action="/invoices" style="display:flex; gap:12px; margin-bottom:20px;">
        <div style="flex:1;">
            <input type="text" name="q" class="form-control" placeholder="Cari nomor faktur, nama pelanggan, no WO..." value="<?= htmlspecialchars($search) ?>">
        </div>
        <button type="submit" class="btn btn-secondary btn-sm">Cari</button>
        <?php if ($search !== '' || $currentStatus !== ''): ?>
            <a href="/invoices" class="btn btn-outline btn-sm">Reset</a>
        <?php endif; ?>
    </form>

    <!-- Table -->
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>No. Faktur</th>
                    <th>Pelanggan</th>
                    <th>No. Work Order</th>
                    <th>Tgl Terbit</th>
                    <th>Jatuh Tempo</th>
                    <th>Total Tagihan</th>
                    <th>Sudah Dibayar</th>
                    <th>Sisa Piutang</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($invoices)): ?>
                    <tr><td colspan="10" style="text-align:center; padding:24px; color:var(--text-muted);">Tidak ada faktur tagihan yang sesuai.</td></tr>
                <?php else: ?>
                    <?php foreach ($invoices as $inv): ?>
                        <tr>
                            <td>
                                <a href="/invoices/<?= $inv['id'] ?>" style="font-weight:700; color:var(--primary);">
                                    <?= htmlspecialchars($inv['invoice_number']) ?>
                                </a>
                            </td>
                            <td>
                                <div><strong><?= htmlspecialchars($inv['customer_name']) ?></strong></div>
                                <div style="font-size:0.75rem; color:var(--text-muted);"><?= htmlspecialchars($inv['customer_phone']) ?></div>
                            </td>
                            <td>
                                <?php if (!empty($inv['order_number'])): ?>
                                    <a href="/work-orders/<?= $inv['work_order_id'] ?>" style="color:var(--secondary); font-weight:600;">
                                        <?= htmlspecialchars($inv['order_number']) ?>
                                    </a>
                                <?php else: ?>
                                    <span style="color:var(--text-muted); font-size:0.8rem;">Non-WO / Langsung</span>
                                <?php endif; ?>
                            </td>
                            <td><?= format_date($inv['issue_date']) ?></td>
                            <td><?= format_date($inv['due_date']) ?></td>
                            <td><strong><?= format_rupiah($inv['total_amount']) ?></strong></td>
                            <td style="color:var(--success); font-weight:700;"><?= format_rupiah($inv['paid_amount']) ?></td>
                            <td>
                                <strong style="color:<?= $inv['balance_due'] > 0 ? 'var(--danger)' : 'var(--text-muted)' ?>;">
                                    <?= format_rupiah($inv['balance_due']) ?>
                                </strong>
                            </td>
                            <td>
                                <span class="badge <?= $inv['payment_status'] === 'paid' ? 'badge-green' : ($inv['payment_status'] === 'partial' ? 'badge-blue' : 'badge-amber') ?>">
                                    <?= strtoupper($inv['payment_status']) ?>
                                </span>
                            </td>
                            <td>
                                <div style="display:flex; gap:6px;">
                                    <a href="/invoices/<?= $inv['id'] ?>" class="btn btn-outline btn-sm" title="Buka Kasir">
                                        <i data-lucide="eye" style="width:14px; height:14px;"></i>
                                    </a>
                                    <a href="/invoices/<?= $inv['id'] ?>/print" target="_blank" class="btn btn-outline btn-sm" title="Cetak / PDF">
                                        <i data-lucide="printer" style="width:14px; height:14px;"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require dirname(__DIR__, 2) . '/layout/footer.php'; ?>
