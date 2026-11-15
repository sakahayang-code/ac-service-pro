<?php
view('layout.header', ['title' => $title]);
view('layout.sidebar');
?>

<div class="content-wrapper">
    <div class="page-header mb-4">
        <div class="text-muted small">
            <a href="<?= url('customer-portal/orders') ?>" class="text-decoration-none">Pesanan Saya</a> / Pemesanan Baru
        </div>
        <h1 class="h3 font-weight-bold text-navy mb-0">Pesan Layanan Servis AC Online</h1>
        <p class="text-muted small mb-0">Lengkapi formulir di bawah ini untuk memesan teknisi profesional ke lokasi Anda</p>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <form action="<?= url('customer-portal/book') ?>" method="POST">
                        <?= csrf_field() ?>

                        <!-- 1. Pilih atau Tambah Unit AC -->
                        <div class="mb-4">
                            <label class="form-label fw-bold text-navy">1. Pilih Unit AC yang Akan Diservis <span class="text-danger">*</span></label>
                            
                            <?php if (!empty($units)): ?>
                                <div class="mb-3">
                                    <select name="ac_unit_id" id="select-unit" class="form-select" onchange="toggleUnitForm(this.value)">
                                        <option value="">-- Pilih dari Unit AC Terdaftar Saya --</option>
                                        <?php foreach ($units as $u): ?>
                                            <option value="<?= $u['id'] ?>">
                                                <?= htmlspecialchars($u['brand']) ?> (<?= htmlspecialchars($u['capacity_pk']) ?>) - <?= htmlspecialchars($u['room_location']) ?> [<?= htmlspecialchars($u['unit_code']) ?>]
                                            </option>
                                        <?php endforeach; ?>
                                        <option value="new">+ Daftarkan Unit AC Baru Lainnya</option>
                                    </select>
                                </div>
                            <?php endif; ?>

                            <!-- Inline Form for New Unit if no units or selected new -->
                            <div id="new-unit-box" class="p-3 bg-light rounded border <?= !empty($units) ? 'd-none' : '' ?>">
                                <div class="fw-semibold small text-primary mb-2">Informasi Unit AC Baru:</div>
                                <div class="row g-2 mb-2">
                                    <div class="col-md-6">
                                        <input type="text" name="new_unit_brand" class="form-control" placeholder="Merek AC (Contoh: Daikin / Panasonic)">
                                    </div>
                                    <div class="col-md-6">
                                        <input type="text" name="new_unit_room" class="form-control" placeholder="Lokasi Ruangan (Contoh: Kamar Tidur Utama)">
                                    </div>
                                </div>
                                <div class="row g-2">
                                    <div class="col-md-4">
                                        <select name="new_unit_pk" class="form-select">
                                            <option value="0.5 PK">0.5 PK</option>
                                            <option value="0.75 PK">0.75 PK</option>
                                            <option value="1 PK" selected>1 PK</option>
                                            <option value="1.5 PK">1.5 PK</option>
                                            <option value="2 PK">2 PK</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <select name="new_unit_freon" class="form-select">
                                            <option value="R32" selected>Freon R32</option>
                                            <option value="R410A">Freon R410A</option>
                                            <option value="R22">Freon R22</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <select name="new_unit_model" class="form-select">
                                            <option value="Split Wall" selected>Split Wall</option>
                                            <option value="Inverter">Inverter</option>
                                            <option value="Cassette">Cassette</option>
                                            <option value="Standing Floor">Standing Floor</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 2. Jenis Layanan -->
                        <div class="mb-4">
                            <label class="form-label fw-bold text-navy">2. Jenis Layanan yang Dibutuhkan <span class="text-danger">*</span></label>
                            <select name="service_id" class="form-select" required>
                                <option value="">-- Pilih Jenis Layanan --</option>
                                <?php foreach ($services as $s): ?>
                                    <option value="<?= $s['id'] ?>">
                                        <?= htmlspecialchars($s['name']) ?> (Estimasi: <?= format_rupiah($s['base_price']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- 3. Jadwal & Waktu Kunjungan -->
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-navy">3. Tanggal Kunjungan Dikehendaki <span class="text-danger">*</span></label>
                                <input type="date" name="scheduled_date" class="form-control" value="<?= date('Y-m-d', strtotime('+1 day')) ?>" min="<?= date('Y-m-d') ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-navy">Pilihan Jam Kunjungan <span class="text-danger">*</span></label>
                                <select name="scheduled_time_slot" class="form-select" required>
                                    <option value="Pagi (08:30 - 11:30)">Pagi (08:30 - 11:30 WIB)</option>
                                    <option value="Siang (13:00 - 15:30)" selected>Siang (13:00 - 15:30 WIB)</option>
                                    <option value="Sore (16:00 - 18:30)">Sore (16:00 - 18:30 WIB)</option>
                                </select>
                            </div>
                        </div>

                        <!-- 4. Alamat Layanan -->
                        <div class="mb-4">
                            <label class="form-label fw-bold text-navy">4. Alamat Lengkap Kunjungan <span class="text-danger">*</span></label>
                            <textarea name="service_address" class="form-control" rows="3" required><?= htmlspecialchars($customer['address']) ?></textarea>
                            <div class="form-text">Pastikan alamat mencakup nama jalan, nomor rumah/gedung, patokan lokasi, atau catatan keamanan.</div>
                        </div>

                        <!-- 5. Keluhan / Gejala Kerusakan -->
                        <div class="mb-4">
                            <label class="form-label fw-bold text-navy">5. Keluhan / Gejala Kerusakan pada AC <span class="text-danger">*</span></label>
                            <textarea name="complaints" class="form-control" rows="3" placeholder="Contoh: AC kurang dingin, mengeluarkan air menetes di indoor, atau terdengar suara berisik dari blower..." required></textarea>
                        </div>

                        <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                            <a href="<?= url('customer-portal/orders') ?>" class="btn btn-light">Batal</a>
                            <button type="submit" class="btn btn-primary px-4 py-2">
                                <i data-lucide="send" class="icon-sm me-1"></i> Kirim Pesanan Servis
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function toggleUnitForm(val) {
    const box = document.getElementById('new-unit-box');
    if (val === 'new') {
        box.classList.remove('d-none');
    } else {
        box.classList.add('d-none');
    }
}
</script>

<?php view('layout.footer'); ?>
