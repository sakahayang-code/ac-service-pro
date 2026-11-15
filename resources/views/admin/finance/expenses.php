<?php
view('layout.header', ['title' => $title]);
view('layout.sidebar');
?>

<div class="content-wrapper">
    <div class="page-header d-flex justify-content-between align-items-center mb-4">
        <div>
            <div class="text-muted small">
                <a href="<?= url('finance') ?>" class="text-decoration-none">Keuangan</a> / Pengeluaran
            </div>
            <h1 class="h3 font-weight-bold text-navy mb-0">Buku Beban & Biaya Operasional</h1>
        </div>
        <a href="<?= url('expenses/create') ?>" class="btn btn-primary">
            <i data-lucide="plus-circle" class="icon-sm me-1"></i> Catat Pengeluaran Baru
        </a>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0 fw-bold text-navy">Daftar Pengeluaran Operasional</h5>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>No. Beban</th>
                        <th>Tanggal</th>
                        <th>Kategori</th>
                        <th>Penerima Dana</th>
                        <th>Sumber Kas/Bank</th>
                        <th>Keterangan</th>
                        <th class="text-end">Jumlah</th>
                        <th>Pencatat</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($expenses)): ?>
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">Belum ada catatan pengeluaran operasional.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($expenses as $e): ?>
                            <tr>
                                <td class="fw-bold text-navy"><?= htmlspecialchars($e['expense_number']) ?></td>
                                <td><?= format_date($e['expense_date']) ?></td>
                                <td>
                                    <span class="badge bg-secondary-subtle text-dark text-uppercase"><?= str_replace('_', ' ', htmlspecialchars($e['expense_category'])) ?></span>
                                </td>
                                <td class="fw-semibold"><?= htmlspecialchars($e['recipient']) ?></td>
                                <td><?= htmlspecialchars($e['account_name'] ?? 'Kasir') ?></td>
                                <td><small class="text-muted"><?= htmlspecialchars($e['description']) ?></small></td>
                                <td class="text-end fw-bold text-danger">- <?= format_rupiah($e['amount']) ?></td>
                                <td><small class="text-muted"><?= htmlspecialchars($e['created_by_name'] ?? 'Admin') ?></small></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php view('layout.footer'); ?>
