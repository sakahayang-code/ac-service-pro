<?php
view('layout.header', ['title' => $title]);
view('layout.sidebar');
?>

<div class="content-wrapper">
    <div class="page-header d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 font-weight-bold text-navy mb-0">Keuangan & Laba Rugi</h1>
            <p class="text-muted small mb-0">Buku kas, mutasi rekening, dan laporan laba rugi operasional usaha</p>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-outline-primary" onclick="openTransferModal()">
                <i data-lucide="arrow-left-right" class="icon-sm me-1"></i> Transfer Kas / Bank
            </button>
            <a href="<?= url('expenses/create') ?>" class="btn btn-primary">
                <i data-lucide="plus-circle" class="icon-sm me-1"></i> Catat Pengeluaran
            </a>
        </div>
    </div>

    <!-- Account Balances Row -->
    <div class="row g-3 mb-4">
        <?php 
        $totalCash = 0;
        foreach ($accounts as $acc): 
            $totalCash += (int)$acc['balance'];
        ?>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <span class="badge bg-primary-subtle text-primary mb-2"><?= htmlspecialchars($acc['code']) ?></span>
                                <h5 class="fw-bold text-navy mb-1"><?= htmlspecialchars($acc['name']) ?></h5>
                                <div class="text-muted small"><?= ucfirst($acc['type']) ?> Account</div>
                            </div>
                            <div class="rounded-circle p-2 bg-light text-navy">
                                <i data-lucide="wallet" class="icon-md"></i>
                            </div>
                        </div>
                        <div class="mt-3">
                            <div class="fs-4 fw-bold text-navy"><?= format_rupiah($acc['balance']) ?></div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Profit & Loss Summary Card -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0 fw-bold text-navy">Laporan Laba Rugi (Bulan <?= date('F Y') ?>)</h5>
                <span class="text-muted small">Kompilasi otomatis dari faktur, mutasi HPP servis, dan beban operasional</span>
            </div>
            <a href="<?= url('expenses') ?>" class="btn btn-sm btn-outline-secondary">
                <i data-lucide="list" class="icon-sm me-1"></i> Lihat Buku Beban
            </a>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered mb-0">
                    <tbody>
                        <tr class="table-light">
                            <th colspan="2" class="text-navy fw-bold text-uppercase">1. Pendapatan Usaha</th>
                            <th class="text-end text-navy fw-bold" style="width: 25%;">Nominal</th>
                        </tr>
                        <tr>
                            <td style="width: 5%;" class="text-center">1.1</td>
                            <td>Pendapatan Jasa Servis AC</td>
                            <td class="text-end fw-semibold text-success">+ <?= format_rupiah($revenueServices) ?></td>
                        </tr>
                        <tr>
                            <td class="text-center">1.2</td>
                            <td>Pendapatan Penjualan Sparepart & Material</td>
                            <td class="text-end fw-semibold text-success">+ <?= format_rupiah($revenueMaterials) ?></td>
                        </tr>
                        <tr class="fw-bold bg-light">
                            <td colspan="2" class="text-end">TOTAL PENDAPATAN OPERASIONAL:</td>
                            <td class="text-end text-navy"><?= format_rupiah($totalRevenue) ?></td>
                        </tr>

                        <tr class="table-light">
                            <th colspan="2" class="text-navy fw-bold text-uppercase">2. Harga Pokok Penjualan (HPP / Biaya Material)</th>
                            <th class="text-end text-navy fw-bold">Nominal</th>
                        </tr>
                        <tr>
                            <td class="text-center">2.1</td>
                            <td>Biaya Pemakaian Freon & Sparepart Pengerjaan (HPP)</td>
                            <td class="text-end fw-semibold text-danger">- <?= format_rupiah($cogs) ?></td>
                        </tr>
                        <tr class="fw-bold bg-light">
                            <td colspan="2" class="text-end">LABA KOTOR (GROSS PROFIT):</td>
                            <td class="text-end text-primary"><?= format_rupiah($grossProfit) ?></td>
                        </tr>

                        <tr class="table-light">
                            <th colspan="2" class="text-navy fw-bold text-uppercase">3. Beban Operasional Usaha</th>
                            <th class="text-end text-navy fw-bold">Nominal</th>
                        </tr>
                        <tr>
                            <td class="text-center">3.1</td>
                            <td>Beban Operasional, Transport, Gaji, Komisi, dan Perlengkapan</td>
                            <td class="text-end fw-semibold text-danger">- <?= format_rupiah($operatingExpenses) ?></td>
                        </tr>

                        <tr class="fs-5 bg-white">
                            <td colspan="2" class="text-end fw-bold text-navy">LABA BERSIH BERJALAN (NET PROFIT):</td>
                            <td class="text-end fw-bold <?= $netProfit >= 0 ? 'text-success' : 'text-danger' ?>">
                                <?= format_rupiah($netProfit) ?>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Recent Cash Ledger Transactions -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold text-navy">Buku Kas Mutasi Terakhir</h5>
            <span class="badge bg-secondary">30 Transaksi Terakhir</span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>No. Mutasi</th>
                        <th>Waktu</th>
                        <th>Rekening</th>
                        <th>Tipe</th>
                        <th>Keterangan</th>
                        <th class="text-end">Nominal</th>
                        <th>User</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($transactions)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">Belum ada transaksi mutasi kas tercatat.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($transactions as $t): ?>
                            <tr>
                                <td class="fw-bold text-navy"><?= htmlspecialchars($t['transaction_number']) ?></td>
                                <td><small class="text-muted"><?= format_datetime($t['created_at']) ?></small></td>
                                <td>
                                    <span class="badge bg-light text-dark border"><?= htmlspecialchars($t['account_name']) ?></span>
                                </td>
                                <td>
                                    <?php if ($t['type'] === 'in'): ?>
                                        <span class="badge bg-success-subtle text-success">KAS MASUK</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger-subtle text-danger">KAS KELUAR</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($t['description']) ?></td>
                                <td class="text-end fw-bold <?= $t['type'] === 'in' ? 'text-success' : 'text-danger' ?>">
                                    <?= $t['type'] === 'in' ? '+' : '-' ?> <?= format_rupiah($t['amount']) ?>
                                </td>
                                <td><small class="text-muted"><?= htmlspecialchars($t['created_by_name'] ?? 'Sistem') ?></small></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Transfer Dana Kas / Bank -->
<div class="modal fade" id="transferModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form action="<?= url('finance/transfer') ?>" method="POST" class="modal-content">
            <?= csrf_field() ?>
            <div class="modal-header">
                <h5 class="modal-title fw-bold text-navy">Transfer Antar Rekening Kas / Bank</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Rekening Asal (Sumber Dana) <span class="text-danger">*</span></label>
                    <select name="from_account_id" class="form-select" required>
                        <?php foreach ($accounts as $acc): ?>
                            <option value="<?= $acc['id'] ?>"><?= htmlspecialchars($acc['name']) ?> (Saldo: <?= format_rupiah($acc['balance']) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Rekening Tujuan <span class="text-danger">*</span></label>
                    <select name="to_account_id" class="form-select" required>
                        <?php foreach ($accounts as $acc): ?>
                            <option value="<?= $acc['id'] ?>"><?= htmlspecialchars($acc['name']) ?> (Saldo: <?= format_rupiah($acc['balance']) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Nominal Transfer (Rp) <span class="text-danger">*</span></label>
                    <input type="number" name="amount" class="form-control" placeholder="Contoh: 1000000" min="1000" required>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Catatan / Keperluan</label>
                    <textarea name="notes" class="form-control" rows="2" placeholder="Contoh: Setoran uang tunai kasir ke Bank BCA..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary">Proses Transfer</button>
            </div>
        </form>
    </div>
</div>

<script>
function openTransferModal() {
    const modal = new bootstrap.Modal(document.getElementById('transferModal'));
    modal.show();
}
</script>

<?php view('layout.footer'); ?>
