<?php
view('layout.header', ['title' => $title]);
view('layout.sidebar');
?>

<div class="content-wrapper">
    <div class="page-header d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 font-weight-bold text-navy mb-0">Kontrak Maintenance Servis AC</h1>
            <p class="text-muted small mb-0">Pengelolaan kontrak pemeliharaan berkala untuk gedung perkantoran, hotel, sekolah, dan industri</p>
        </div>
        <a href="<?= url('contracts/create') ?>" class="btn btn-primary">
            <i data-lucide="plus-circle" class="icon-sm me-1"></i> Buat Kontrak Baru
        </a>
    </div>

    <!-- Metric Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <span class="text-muted small text-uppercase fw-semibold">Kontrak Berjalan Aktif</span>
                    <div class="fs-3 fw-bold text-navy mt-1"><?= $activeCount ?> Kontrak</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <span class="text-muted small text-uppercase fw-semibold">Total Nilai Kontrak Aktif</span>
                    <div class="fs-3 fw-bold text-success mt-1"><?= format_rupiah($totalContractValue) ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <span class="text-muted small text-uppercase fw-semibold">Mendekati Berakhir (<= 30 Hari)</span>
                    <div class="fs-3 fw-bold text-danger mt-1"><?= $expiringSoonCount ?> Kontrak</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter & Search Card -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-3">
            <form action="<?= url('contracts') ?>" method="GET" class="row g-2 align-items-center">
                <div class="col-md-6">
                    <input type="text" name="q" class="form-control" placeholder="Cari No. Kontrak, nama perusahaan, atau judul..." value="<?= htmlspecialchars($search) ?>">
                </div>
                <div class="col-md-4">
                    <select name="status" class="form-select">
                        <option value="">-- Semua Status Kontrak --</option>
                        <option value="active" <?= $currentStatus === 'active' ? 'selected' : '' ?>>Aktif Berjalan</option>
                        <option value="draft" <?= $currentStatus === 'draft' ? 'selected' : '' ?>>Draft</option>
                        <option value="expired" <?= $currentStatus === 'expired' ? 'selected' : '' ?>>Kedaluwarsa (Expired)</option>
                        <option value="terminated" <?= $currentStatus === 'terminated' ? 'selected' : '' ?>>Dihentikan</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-secondary w-100">
                        <i data-lucide="search" class="icon-sm me-1"></i> Filter
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Table -->
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>No. Kontrak</th>
                        <th>Perusahaan / Pelanggan</th>
                        <th>Judul Kontrak</th>
                        <th>Periode Kontrak</th>
                        <th class="text-center">Jml Unit</th>
                        <th class="text-center">Kunjungan/Thn</th>
                        <th class="text-end">Nilai Kontrak</th>
                        <th>Status</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($contracts)): ?>
                        <tr>
                            <td colspan="9" class="text-center py-5 text-muted">Belum ada kontrak maintenance ditemukan.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($contracts as $c): ?>
                            <tr>
                                <td class="fw-bold text-navy">
                                    <a href="<?= url('contracts/' . $c['id']) ?>" class="text-decoration-none">
                                        <?= htmlspecialchars($c['contract_number']) ?>
                                    </a>
                                </td>
                                <td>
                                    <div class="fw-semibold text-navy"><?= htmlspecialchars($c['customer_name']) ?></div>
                                    <small class="text-muted"><?= htmlspecialchars($c['customer_code']) ?> | <?= htmlspecialchars($c['customer_phone']) ?></small>
                                </td>
                                <td><?= htmlspecialchars($c['title']) ?></td>
                                <td>
                                    <small class="d-block"><?= format_date($c['start_date']) ?> s/d</small>
                                    <strong><?= format_date($c['end_date']) ?></strong>
                                </td>
                                <td class="text-center"><span class="badge bg-light text-dark border"><?= (int)$c['total_units'] ?> Unit</span></td>
                                <td class="text-center"><span class="badge bg-info-subtle text-info"><?= (int)$c['visits_per_year'] ?>x Servis</span></td>
                                <td class="text-end fw-bold text-navy"><?= format_rupiah($c['contract_amount']) ?></td>
                                <td>
                                    <?php if ($c['status'] === 'active'): ?>
                                        <span class="badge bg-success">AKTIF</span>
                                    <?php elseif ($c['status'] === 'expired'): ?>
                                        <span class="badge bg-danger">BERAKHIR</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary"><?= strtoupper($c['status']) ?></span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <a href="<?= url('contracts/' . $c['id']) ?>" class="btn-action btn-action-view">
                                        <i data-lucide="eye"></i> <span>Detail</span>
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
