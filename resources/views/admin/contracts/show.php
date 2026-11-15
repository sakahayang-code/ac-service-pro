<?php
view('layout.header', ['title' => $title]);
view('layout.sidebar');
?>

<div class="content-wrapper">
    <div class="page-header d-flex justify-content-between align-items-center mb-4">
        <div>
            <div class="text-muted small">
                <a href="<?= url('contracts') ?>" class="text-decoration-none">Kontrak</a> / Detail
            </div>
            <h1 class="h3 font-weight-bold text-navy mb-0">Kontrak #<?= htmlspecialchars($contract['contract_number']) ?></h1>
            <p class="text-muted small mb-0"><?= htmlspecialchars($contract['title']) ?></p>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= url('contracts') ?>" class="btn btn-outline-secondary">
                <i data-lucide="arrow-left" class="icon-sm me-1"></i> Kembali ke Daftar
            </a>
            <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $contract['customer_phone']) ?>?text=<?= urlencode("Halo {$contract['customer_name']}, terkait pemeliharaan rutin kontrak AC #{$contract['contract_number']}.") ?>" target="_blank" class="btn btn-success">
                <i data-lucide="message-circle" class="icon-sm me-1"></i> Hubungi PIC
            </a>
        </div>
    </div>

    <!-- Status & Expiration Banner -->
    <div class="card mb-4 border-0 shadow-sm">
        <div class="card-body p-3 d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-circle p-2 <?= $contract['status'] === 'active' ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary' ?>">
                    <i data-lucide="shield-check" class="icon-md"></i>
                </div>
                <div>
                    <div class="fw-bold fs-5">
                        Status Kontrak: 
                        <?php if ($contract['status'] === 'active'): ?>
                            <span class="badge bg-success">AKTIF BERJALAN</span>
                        <?php else: ?>
                            <span class="badge bg-secondary"><?= strtoupper($contract['status']) ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="text-muted small">
                        Periode: <?= format_date($contract['start_date']) ?> s/d <?= format_date($contract['end_date']) ?>
                    </div>
                </div>
            </div>
            <div class="d-flex gap-4 text-end">
                <div>
                    <div class="text-muted small">Sisa Masa Berlaku</div>
                    <div class="fs-4 fw-bold <?= $daysRemaining <= 30 ? 'text-danger' : 'text-navy' ?>">
                        <?= $daysRemaining > 0 ? "{$daysRemaining} Hari" : "Telah Berakhir" ?>
                    </div>
                </div>
                <div>
                    <div class="text-muted small">Total Nilai Kontrak</div>
                    <div class="fs-4 fw-bold text-success"><?= format_rupiah($contract['contract_amount']) ?></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Main Column: Periodic Schedules List -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0 fw-bold text-navy">Jadwal Kunjungan Pemeliharaan Berkala</h5>
                        <small class="text-muted">Total <?= count($schedules) ?> sesi kunjungan terjadwal</small>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Tanggal Rencana</th>
                                <th>Deskripsi Kunjungan</th>
                                <th>Status Jadwal</th>
                                <th>Surat Perintah Kerja (SPK)</th>
                                <th class="text-center">Aksi Disposisi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($schedules)): ?>
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">Belum ada jadwal pemeliharaan terdaftar.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($schedules as $idx => $s): ?>
                                    <tr>
                                        <td><?= $idx + 1 ?></td>
                                        <td class="fw-bold text-navy"><?= format_date($s['scheduled_date']) ?></td>
                                        <td><?= htmlspecialchars($s['notes']) ?></td>
                                        <td>
                                            <?php if ($s['status'] === 'completed'): ?>
                                                <span class="badge bg-success">SELESAI</span>
                                            <?php elseif ($s['status'] === 'in_progress'): ?>
                                                <span class="badge bg-warning text-dark">DIPROSES</span>
                                            <?php else: ?>
                                                <span class="badge bg-primary-subtle text-primary">TERJADWAL</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if (!empty($s['work_order_id'])): ?>
                                                <a href="<?= url('work-orders/' . $s['work_order_id']) ?>" class="badge bg-light text-primary border text-decoration-none">
                                                    #<?= htmlspecialchars($s['order_number'] ?? 'WO-' . $s['work_order_id']) ?>
                                                </a>
                                                <small class="d-block text-muted"><?= htmlspecialchars($s['wo_status'] ?? '') ?></small>
                                            <?php else: ?>
                                                <span class="text-muted small">Belum Diterbitkan</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <?php if (empty($s['work_order_id'])): ?>
                                                <form action="<?= url('contracts/' . $contract['id'] . '/generate-wo') ?>" method="POST" onsubmit="return confirm('Terbitkan Surat Perintah Kerja (SPK) untuk kunjungan ini?')">
                                                    <?= csrf_field() ?>
                                                    <input type="hidden" name="schedule_id" value="<?= $s['id'] ?>">
                                                    <button type="submit" class="btn btn-sm btn-primary">
                                                        <i data-lucide="play-circle" class="icon-xs me-1"></i> Terbitkan SPK
                                                    </button>
                                                </form>
                                            <?php else: ?>
                                                <a href="<?= url('work-orders/' . $s['work_order_id']) ?>" class="btn btn-sm btn-outline-secondary">
                                                    <i data-lucide="eye" class="icon-xs me-1"></i> Buka SPK
                                                </a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Sidebar Column: Corporate Client & Terms Info -->
        <div class="col-lg-4">
            <!-- Customer Card -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-bold text-navy">Klien / Perusahaan</h5>
                </div>
                <div class="card-body">
                    <h5 class="fw-bold text-navy mb-1"><?= htmlspecialchars($contract['customer_name']) ?></h5>
                    <div class="text-muted small mb-3">Kode: <?= htmlspecialchars($contract['customer_code']) ?></div>
                    <div class="mb-2">
                        <i data-lucide="phone" class="icon-sm text-muted me-2"></i>
                        <a href="tel:<?= htmlspecialchars($contract['customer_phone']) ?>" class="text-decoration-none">
                            <?= htmlspecialchars($contract['customer_phone']) ?>
                        </a>
                    </div>
                    <div>
                        <i data-lucide="map-pin" class="icon-sm text-muted me-2"></i>
                        <span><?= nl2br(htmlspecialchars($contract['customer_address'])) ?></span>
                    </div>
                </div>
            </div>

            <!-- Contract Scope Card -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-bold text-navy">Cakupan Kontrak (SLA)</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted small">Jumlah Unit AC:</span>
                        <span class="fw-bold text-navy"><?= (int)$contract['total_units'] ?> Unit</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted small">Frekuensi Servis:</span>
                        <span class="fw-bold text-navy"><?= (int)$contract['visits_per_year'] ?>x per Tahun</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted small">Respon Darurat SLA:</span>
                        <span class="badge bg-info-subtle text-info"><?= (int)$contract['sla_hours'] ?> Jam</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted small">Termin Penagihan:</span>
                        <span class="fw-semibold text-navy text-capitalize"><?= htmlspecialchars($contract['billing_frequency']) ?></span>
                    </div>
                </div>
            </div>

            <!-- Notes Card -->
            <?php if (!empty($contract['notes'])): ?>
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0 fw-bold text-navy">Ketentuan Khusus</h5>
                    </div>
                    <div class="card-body">
                        <p class="small text-muted mb-0"><?= nl2br(htmlspecialchars($contract['notes'])) ?></p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php view('layout.footer'); ?>
