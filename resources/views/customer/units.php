<?php
view('layout.header', ['title' => $title]);
view('layout.sidebar');
?>

<div class="content-wrapper">
    <div class="page-header d-flex justify-content-between align-items-center mb-4">
        <div>
            <div class="text-muted small">
                <a href="<?= url('customer-portal/orders') ?>" class="text-decoration-none">Portal</a> / Unit AC
            </div>
            <h1 class="h3 font-weight-bold text-navy mb-0">Daftar Unit AC Saya</h1>
            <p class="text-muted small mb-0">Inventaris seluruh unit pendingin ruangan yang terdaftar di akun Anda</p>
        </div>
        <button type="button" class="btn btn-primary" onclick="openAddUnitModal()">
            <i data-lucide="plus-circle" class="icon-sm me-1"></i> Daftarkan Unit Baru
        </button>
    </div>

    <!-- Units Cards / Grid -->
    <div class="row g-4">
        <?php if (empty($units)): ?>
            <div class="col-12">
                <div class="card border-0 shadow-sm text-center py-5">
                    <div class="card-body">
                        <div class="rounded-circle bg-light d-inline-flex p-3 mb-3 text-muted">
                            <i data-lucide="cpu" class="icon-lg"></i>
                        </div>
                        <h5 class="fw-bold text-navy">Belum Ada Unit AC Terdaftar</h5>
                        <p class="text-muted small mb-3">Daftarkan unit AC rumah atau kantor Anda untuk memudahkan penjadwalan servis dan pencatatan riwayat teknis.</p>
                        <button type="button" class="btn btn-primary" onclick="openAddUnitModal()">
                            Tambah Unit AC Sekarang
                        </button>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($units as $u): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <span class="badge bg-primary-subtle text-primary fw-bold"><?= htmlspecialchars($u['unit_code']) ?></span>
                                <span class="badge bg-light text-dark border"><?= htmlspecialchars($u['model_type'] ?? 'Split Wall') ?></span>
                            </div>
                            <h4 class="fw-bold text-navy mb-1"><?= htmlspecialchars($u['brand']) ?> <?= htmlspecialchars($u['capacity_pk']) ?></h4>
                            <div class="text-muted small mb-3">
                                <i data-lucide="map-pin" class="icon-xs me-1"></i> Lokasi: <strong><?= htmlspecialchars($u['room_location']) ?></strong>
                            </div>

                            <div class="p-2 bg-light rounded small mb-3">
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="text-muted">Jenis Freon:</span>
                                    <span class="fw-semibold"><?= htmlspecialchars($u['freon_type'] ?? 'R32') ?></span>
                                </div>
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="text-muted">Total Servis:</span>
                                    <span class="fw-semibold text-primary"><?= (int)$u['service_count'] ?> kali</span>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span class="text-muted">Servis Terakhir:</span>
                                    <span class="fw-semibold"><?= !empty($u['last_service_date']) ? format_date($u['last_service_date']) : 'Belum pernah' ?></span>
                                </div>
                            </div>

                            <?php if (!empty($u['notes'])): ?>
                                <p class="small text-muted mb-3">Catatan: <?= htmlspecialchars($u['notes']) ?></p>
                            <?php endif; ?>

                            <a href="<?= url('customer-portal/book') ?>" class="btn btn-outline-primary btn-sm w-100">
                                <i data-lucide="calendar" class="icon-xs me-1"></i> Pesan Servis untuk Unit Ini
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Modal Daftarkan Unit Baru -->
<div class="modal fade" id="addUnitModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form action="<?= url('customer-portal/units') ?>" method="POST" class="modal-content">
            <?= csrf_field() ?>
            <div class="modal-header">
                <h5 class="modal-title fw-bold text-navy">Daftarkan Unit AC Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Merek AC <span class="text-danger">*</span></label>
                    <input type="text" name="brand" class="form-control" placeholder="Contoh: Daikin, Panasonic, Sharp, LG, Mitsubishi" required>
                </div>

                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label class="form-label fw-semibold">Kapasitas (PK) <span class="text-danger">*</span></label>
                        <select name="capacity_pk" class="form-select" required>
                            <option value="0.5 PK">0.5 PK</option>
                            <option value="0.75 PK">0.75 PK</option>
                            <option value="1 PK" selected>1 PK</option>
                            <option value="1.5 PK">1.5 PK</option>
                            <option value="2 PK">2 PK</option>
                            <option value="2.5 PK">2.5 PK</option>
                        </select>
                    </div>
                    <div class="col-6">
                        <label class="form-label fw-semibold">Jenis Freon</label>
                        <select name="freon_type" class="form-select">
                            <option value="R32" selected>R32</option>
                            <option value="R410A">R410A</option>
                            <option value="R22">R22</option>
                            <option value="R134a">R134a</option>
                        </select>
                    </div>
                </div>

                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label class="form-label fw-semibold">Tipe AC</label>
                        <select name="model_type" class="form-select">
                            <option value="Split Wall" selected>Split Wall</option>
                            <option value="Inverter">Inverter</option>
                            <option value="Cassette">Cassette</option>
                            <option value="Standing Floor">Standing Floor</option>
                        </select>
                    </div>
                    <div class="col-6">
                        <label class="form-label fw-semibold">Lokasi Ruangan <span class="text-danger">*</span></label>
                        <input type="text" name="room_location" class="form-control" placeholder="Contoh: Kamar Tidur Utama" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Nomor Seri Unit (Jika ada)</label>
                    <input type="text" name="serial_number" class="form-control" placeholder="Opsional">
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Catatan Khusus</label>
                    <textarea name="notes" class="form-control" rows="2" placeholder="Catatan kondisi unit..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Unit AC</button>
            </div>
        </form>
    </div>
</div>

<script>
function openAddUnitModal() {
    const modal = new bootstrap.Modal(document.getElementById('addUnitModal'));
    modal.show();
}
</script>

<?php view('layout.footer'); ?>
