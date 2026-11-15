<?php
view('layout.header', ['title' => $title]);
view('layout.sidebar');
?>

<div class="content-wrapper">
    <div class="page-header mb-4">
        <div class="text-muted small">
            <a href="<?= url('contracts') ?>" class="text-decoration-none">Kontrak</a> / Buat Baru
        </div>
        <h1 class="h3 font-weight-bold text-navy mb-0">Buat Kontrak Maintenance Baru</h1>
        <p class="text-muted small mb-0">Perjanjian kerjasama pemeliharaan AC terjadwal untuk korporasi dan institusi</p>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-9">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <form action="<?= url('contracts') ?>" method="POST">
                        <?= csrf_field() ?>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Pilih Pelanggan / Perusahaan <span class="text-danger">*</span></label>
                            <select name="customer_id" class="form-select" required>
                                <option value="">-- Pilih Pelanggan --</option>
                                <?php foreach ($customers as $c): ?>
                                    <option value="<?= $c['id'] ?>">
                                        <?= htmlspecialchars($c['name']) ?> (<?= htmlspecialchars($c['customer_code']) ?>) - <?= htmlspecialchars($c['phone']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Judul / Nama Kontrak <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control" placeholder="Contoh: Kontrak Pemeliharaan Rutin AC Gedung Wisma 46 (Tahun 2026/2027)" required>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Tanggal Mulai Berlaku <span class="text-danger">*</span></label>
                                <input type="date" name="start_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Tanggal Berakhir Kontrak <span class="text-danger">*</span></label>
                                <input type="date" name="end_date" class="form-control" value="<?= date('Y-m-d', strtotime('+1 year')) ?>" required>
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Jumlah Unit AC Tercakup <span class="text-danger">*</span></label>
                                <input type="number" name="total_units" class="form-control" value="5" min="1" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Frekuensi Servis per Tahun <span class="text-danger">*</span></label>
                                <select name="visits_per_year" class="form-select" required>
                                    <option value="4" selected>4x Setahun (Tiap 3 Bulan / Kuartal)</option>
                                    <option value="6">6x Setahun (Tiap 2 Bulan)</option>
                                    <option value="12">12x Setahun (Tiap Bulan)</option>
                                    <option value="2">2x Setahun (Tiap 6 Bulan)</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Total Nilai Kontrak (Rp) <span class="text-danger">*</span></label>
                                <input type="number" name="contract_amount" class="form-control" placeholder="Contoh: 12000000" min="0" required>
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Termin Penagihan (Billing Frequency)</label>
                                <select name="billing_frequency" class="form-select">
                                    <option value="quarterly" selected>Per Kuartal (3 Bulan Sekali)</option>
                                    <option value="monthly">Per Bulan</option>
                                    <option value="semi_annual">Per Semester (6 Bulan Sekali)</option>
                                    <option value="annual">Dibayar di Muka (1 Tahun Penuh)</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Komitmen Respon SLA (Service Level Agreement)</label>
                                <select name="sla_hours" class="form-select">
                                    <option value="12">Maksimal 12 Jam (High Priority VIP)</option>
                                    <option value="24" selected>Maksimal 24 Jam (Standard Corporate)</option>
                                    <option value="48">Maksimal 48 Jam (Reguler)</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold">Catatan Khusus & Ketentuan Tambahan</label>
                            <textarea name="notes" class="form-control" rows="3" placeholder="SOP keamanan gedung, syarat seragam teknisi, jadwal kerja saat hari libur..."></textarea>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="<?= url('contracts') ?>" class="btn btn-light">Batal</a>
                            <button type="submit" class="btn btn-primary px-4">
                                <i data-lucide="check" class="icon-sm me-1"></i> Simpan Kontrak & Buat Jadwal Otomatis
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php view('layout.footer'); ?>
