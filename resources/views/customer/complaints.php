<?php
view('layout.header', ['title' => $title]);
view('layout.sidebar');
?>

<div class="content-wrapper">
    <div class="page-header d-flex justify-content-between align-items-center mb-4">
        <div>
            <div class="text-muted small">
                <a href="<?= url('customer-portal/orders') ?>" class="text-decoration-none">Portal</a> / Keluhan & Garansi
            </div>
            <h1 class="h3 font-weight-bold text-navy mb-0">Pusat Bantuan & Klaim Garansi</h1>
            <p class="text-muted small mb-0">Ajukan komplain layanan atau klaim garansi jika hasil pengerjaan kurang maksimal</p>
        </div>
        <button type="button" class="btn btn-danger" onclick="openComplaintModal()">
            <i data-lucide="alert-triangle" class="icon-sm me-1"></i> Ajukan Keluhan / Garansi
        </button>
    </div>

    <!-- Guarantee Notice Box -->
    <div class="card border-0 shadow-sm mb-4 bg-info-subtle border-start border-info border-4">
        <div class="card-body p-3 d-flex align-items-center gap-3">
            <i data-lucide="shield-check" class="icon-lg text-info"></i>
            <div>
                <h6 class="fw-bold text-navy mb-1">Jaminan Garansi 30 Hari Kerja</h6>
                <p class="mb-0 small text-muted">
                    Setiap pengerjaan cuci AC, perbaikan, dan pengisian freon oleh teknisi <strong>AC Service Pro</strong> dilindungi garansi 30 hari. Jika AC Anda kembali bocor atau kurang dingin dalam periode garansi, teknisi kami akan melakukan kunjungan ulang tanpa biaya tambahan.
                </p>
            </div>
        </div>
    </div>

    <!-- Complaints Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0 fw-bold text-navy">Daftar Tiket Keluhan & Klaim Anda</h5>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>No. Tiket</th>
                        <th>Tanggal Ajuan</th>
                        <th>Terkait SPK</th>
                        <th>Judul Keluhan</th>
                        <th>Prioritas</th>
                        <th>Status Tiket</th>
                        <th>Tindakan / Solusi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($complaints)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">Belum ada tiket keluhan yang diajukan.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($complaints as $c): ?>
                            <tr>
                                <td class="fw-bold text-navy"><?= htmlspecialchars($c['complaint_number']) ?></td>
                                <td><?= format_date($c['created_at']) ?></td>
                                <td>
                                    <?php if (!empty($c['order_number'])): ?>
                                        <span class="badge bg-light text-primary border">#<?= htmlspecialchars($c['order_number']) ?></span>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="fw-semibold text-navy"><?= htmlspecialchars($c['title']) ?></div>
                                    <small class="text-muted text-truncate d-inline-block" style="max-width: 250px;">
                                        <?= htmlspecialchars($c['description']) ?>
                                    </small>
                                </td>
                                <td>
                                    <?php if ($c['priority'] === 'urgent'): ?>
                                        <span class="badge bg-danger">MENDESAK</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">NORMAL</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($c['status'] === 'resolved'): ?>
                                        <span class="badge bg-success">SELESAI DITANGANI</span>
                                    <?php elseif ($c['status'] === 'in_progress'): ?>
                                        <span class="badge bg-warning text-dark">SEDANG DIPROSES</span>
                                    <?php else: ?>
                                        <span class="badge bg-info text-dark">TIKET TERBUKA</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($c['resolution_notes'])): ?>
                                        <small class="text-success fw-semibold"><?= htmlspecialchars($c['resolution_notes']) ?></small>
                                    <?php else: ?>
                                        <small class="text-muted">Dalam antrean CS</small>
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

<!-- Modal Form Ajukan Keluhan -->
<div class="modal fade" id="complaintModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form action="<?= url('customer-portal/complaints') ?>" method="POST" class="modal-content">
            <?= csrf_field() ?>
            <div class="modal-header">
                <h5 class="modal-title fw-bold text-navy">Ajukan Keluhan atau Klaim Garansi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Pilih Pesanan Servis Terkait</label>
                    <select name="work_order_id" class="form-select">
                        <option value="">-- Umum / Tanpa Kaitan Pesanan Tertentu --</option>
                        <?php foreach ($orders as $wo): ?>
                            <option value="<?= $wo['id'] ?>">#<?= htmlspecialchars($wo['order_number']) ?> (<?= format_date($wo['scheduled_date']) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Judul Masalah / Keluhan <span class="text-danger">*</span></label>
                    <input type="text" name="title" class="form-control" placeholder="Contoh: AC Menetes Air Setelah 3 Hari Dicuci" required>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Tingkat Urgensi</label>
                    <select name="priority" class="form-select">
                        <option value="normal">Normal (Respon < 24 Jam)</option>
                        <option value="urgent">Mendesak / Butuh Penanganan Segera</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Penjelasan Lengkap Keluhan <span class="text-danger">*</span></label>
                    <textarea name="description" class="form-control" rows="4" placeholder="Jelaskan secara detail gejala masalah yang terjadi agar teknisi kami dapat mempersiapkan suku cadang yang tepat..." required></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-danger">Kirimkan Tiket Keluhan</button>
            </div>
        </form>
    </div>
</div>

<script>
function openComplaintModal() {
    const modal = new bootstrap.Modal(document.getElementById('complaintModal'));
    modal.show();
}
</script>

<?php view('layout.footer'); ?>
