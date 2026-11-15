<?php
view('layout.header', ['title' => $title]);
view('layout.sidebar');
?>

<div class="content-wrapper">
    <div class="page-header d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 font-weight-bold text-navy mb-0">Pengaturan Sistem & Database</h1>
            <p class="text-muted small mb-0">Konfigurasi profil perusahaan, aturan operasional, dan cadangan database SQLite</p>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= url('settings/backup?download=1') ?>" class="btn btn-outline-primary">
                <i data-lucide="download" class="icon-sm me-1"></i> Unduh Backup Database (.sqlite)
            </a>
            <a href="<?= url('settings/backup') ?>" class="btn btn-primary">
                <i data-lucide="database" class="icon-sm me-1"></i> Buat Backup Sekarang
            </a>
        </div>
    </div>

    <div class="row g-4">
        <!-- Main Column: Settings Form -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-bold text-navy">Konfigurasi Profil Perusahaan & Bisnis</h5>
                </div>
                <div class="card-body p-4">
                    <form action="<?= url('settings') ?>" method="POST">
                        <?= csrf_field() ?>

                        <?php foreach ($settings as $groupName => $items): ?>
                            <h6 class="fw-bold text-primary border-bottom pb-2 mb-3 text-uppercase">
                                <?= $groupName === 'company' ? 'Profil Perusahaan' : ($groupName === 'finance' ? 'Keuangan & Penagihan' : ($groupName === 'operational' ? 'Operasional & Garansi' : 'CRM & Pelanggan')) ?>
                            </h6>
                            <div class="row g-3 mb-4">
                                <?php foreach ($items as $item): ?>
                                    <div class="<?= in_array($item['setting_key'], ['company_address', 'invoice_footer_note']) ? 'col-12' : 'col-md-6' ?>">
                                        <label class="form-label small fw-semibold text-navy"><?= htmlspecialchars($item['description'] ?? $item['setting_key']) ?></label>
                                        <?php if (in_array($item['setting_key'], ['company_address', 'invoice_footer_note'])): ?>
                                            <textarea name="settings[<?= $item['setting_key'] ?>]" class="form-control" rows="2"><?= htmlspecialchars($item['setting_value']) ?></textarea>
                                        <?php else: ?>
                                            <input type="text" name="settings[<?= $item['setting_key'] ?>]" class="form-control" value="<?= htmlspecialchars($item['setting_value']) ?>">
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endforeach; ?>

                        <div class="d-flex justify-content-end pt-3 border-top">
                            <button type="submit" class="btn btn-primary px-4">
                                <i data-lucide="check" class="icon-sm me-1"></i> Simpan Seluruh Pengaturan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Sidebar Column: Database Health & Backups -->
        <div class="col-lg-4">
            <!-- Database Health Card -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-bold text-navy">Kondisi Database SQLite</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted small">Ukuran File Database:</span>
                        <span class="fw-bold text-navy"><?= round($dbSize / 1024, 1) ?> KB</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted small">Ukuran WAL Journal:</span>
                        <span class="fw-bold text-navy"><?= round($walSize / 1024, 1) ?> KB</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted small">Journal Mode:</span>
                        <span class="badge bg-success-subtle text-success text-uppercase"><?= htmlspecialchars($journalMode) ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted small">Foreign Keys:</span>
                        <span class="badge bg-success-subtle text-success"><?= $foreignKeys ? 'ENABLED' : 'DISABLED' ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <span class="text-muted small">Busy Timeout:</span>
                        <span class="badge bg-info-subtle text-info"><?= $busyTimeout ?> ms</span>
                    </div>
                    <div class="alert alert-success py-2 small mb-0">
                        <i data-lucide="shield-check" class="icon-xs me-1"></i> SQLite WAL Mode aktif dan aman dari konkurensi multi-user.
                    </div>
                </div>
            </div>

            <!-- Restore Database Card -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-bold text-navy">Pulihkan Database (Restore)</h5>
                </div>
                <div class="card-body">
                    <form action="<?= url('settings/restore') ?>" method="POST" enctype="multipart/form-data" onsubmit="return confirm('PERINGATAN: Memulihkan database akan menimpa seluruh data saat ini dengan data dari file cadangan. Lanjutkan?')">
                        <?= csrf_field() ?>
                        <div class="mb-3">
                            <label class="form-label small text-muted">Pilih File Cadangan (.sqlite / .db)</label>
                            <input type="file" name="backup_file" class="form-control form-control-sm" accept=".sqlite,.db" required>
                        </div>
                        <button type="submit" class="btn btn-danger btn-sm w-100">
                            <i data-lucide="upload" class="icon-xs me-1"></i> Upload & Pulihkan Database
                        </button>
                    </form>
                </div>
            </div>

            <!-- Previous Backups List -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold text-navy">File Cadangan Tersimpan</h5>
                    <span class="badge bg-secondary"><?= count($backupFiles) ?> File</span>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush small">
                        <?php if (empty($backupFiles)): ?>
                            <li class="list-group-item text-center py-4 text-muted">Belum ada file cadangan yang dibuat.</li>
                        <?php else: ?>
                            <?php foreach ($backupFiles as $bf): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="fw-semibold text-navy"><?= htmlspecialchars($bf['filename']) ?></div>
                                        <div class="text-muted" style="font-size: 0.75rem;"><?= format_datetime($bf['created_at']) ?> (<?= round($bf['size'] / 1024, 1) ?> KB)</div>
                                    </div>
                                    <a href="<?= url('settings/backup?download=1') ?>" class="btn btn-sm btn-outline-secondary py-1 px-2" title="Unduh">
                                        <i data-lucide="download" class="icon-xs"></i>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<?php view('layout.footer'); ?>
