<?php require dirname(__DIR__, 2) . '/layout/header.php'; ?>

<div class="card" style="max-width:760px; margin:0 auto;">
    <div class="card-header">
        <div class="card-title">
            <i data-lucide="edit-3"></i>
            <span>Ubah Data Unit AC (<?= htmlspecialchars($unit['unit_code']) ?>)</span>
        </div>
        <a href="/ac-units/<?= $unit['id'] ?>" class="btn btn-outline btn-sm">Kembali</a>
    </div>

    <form method="POST" action="/ac-units/<?= $unit['id'] ?>/edit">
        <?= csrf_field() ?>
        <div class="form-row">
            <div class="form-group">
                <label class="form-label" for="brand">Merek AC *</label>
                <input type="text" id="brand" name="brand" class="form-control" required value="<?= htmlspecialchars($unit['brand']) ?>">
            </div>
            <div class="form-group">
                <label class="form-label" for="model_type">Tipe / Model AC</label>
                <select id="model_type" name="model_type" class="form-select">
                    <option value="Split Wall" <?= $unit['model_type'] === 'Split Wall' ? 'selected' : '' ?>>Split Wall</option>
                    <option value="Cassette" <?= $unit['model_type'] === 'Cassette' ? 'selected' : '' ?>>Cassette</option>
                    <option value="Ceiling Suspended" <?= $unit['model_type'] === 'Ceiling Suspended' ? 'selected' : '' ?>>Ceiling Suspended</option>
                    <option value="Floor Standing" <?= $unit['model_type'] === 'Floor Standing' ? 'selected' : '' ?>>Floor Standing</option>
                    <option value="Ducting / VRV" <?= $unit['model_type'] === 'Ducting / VRV' ? 'selected' : '' ?>>Ducting / VRV</option>
                </select>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label" for="capacity_pk">Kapasitas (PK) *</label>
                <select id="capacity_pk" name="capacity_pk" class="form-select">
                    <option value="0.5 PK" <?= $unit['capacity_pk'] === '0.5 PK' ? 'selected' : '' ?>>0.5 PK (1/2 PK)</option>
                    <option value="0.75 PK" <?= $unit['capacity_pk'] === '0.75 PK' ? 'selected' : '' ?>>0.75 PK (3/4 PK)</option>
                    <option value="1 PK" <?= $unit['capacity_pk'] === '1 PK' ? 'selected' : '' ?>>1 PK</option>
                    <option value="1.5 PK" <?= $unit['capacity_pk'] === '1.5 PK' ? 'selected' : '' ?>>1.5 PK</option>
                    <option value="2 PK" <?= $unit['capacity_pk'] === '2 PK' ? 'selected' : '' ?>>2 PK</option>
                    <option value="2.5 PK" <?= $unit['capacity_pk'] === '2.5 PK' ? 'selected' : '' ?>>2.5 PK</option>
                    <option value="3 PK" <?= $unit['capacity_pk'] === '3 PK' ? 'selected' : '' ?>>3 PK</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label" for="refrigerant_type">Jenis Freon *</label>
                <select id="refrigerant_type" name="refrigerant_type" class="form-select">
                    <option value="R32" <?= $unit['refrigerant_type'] === 'R32' ? 'selected' : '' ?>>R32</option>
                    <option value="R410A" <?= $unit['refrigerant_type'] === 'R410A' ? 'selected' : '' ?>>R410A</option>
                    <option value="R22" <?= $unit['refrigerant_type'] === 'R22' ? 'selected' : '' ?>>R22</option>
                    <option value="R134a" <?= $unit['refrigerant_type'] === 'R134a' ? 'selected' : '' ?>>R134a</option>
                </select>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label" for="room_location">Lokasi Ruangan *</label>
                <input type="text" id="room_location" name="room_location" class="form-control" required value="<?= htmlspecialchars($unit['room_location']) ?>">
            </div>
            <div class="form-group">
                <label class="form-label" for="serial_number">Nomor Seri</label>
                <input type="text" id="serial_number" name="serial_number" class="form-control" value="<?= htmlspecialchars($unit['serial_number'] ?? '') ?>">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label" for="condition">Kondisi Fisik</label>
                <input type="text" id="condition" name="condition" class="form-control" value="<?= htmlspecialchars($unit['condition']) ?>">
            </div>
            <div class="form-group">
                <label class="form-label" for="warranty_status">Status Garansi</label>
                <input type="text" id="warranty_status" name="warranty_status" class="form-control" value="<?= htmlspecialchars($unit['warranty_status'] ?? '') ?>">
            </div>
        </div>

        <div class="form-group">
            <label class="form-label" for="notes">Catatan Teknis Unit</label>
            <textarea id="notes" name="notes" class="form-control" rows="2"><?= htmlspecialchars($unit['notes'] ?? '') ?></textarea>
        </div>

        <div style="display:flex; justify-content:space-between; align-items:center; margin-top:24px;">
            <button type="button" class="btn btn-danger btn-sm" onclick="if(confirm('Hapus/nonaktifkan unit AC ini?')) { document.getElementById('delete-ac-form').submit(); }">
                <i data-lucide="trash-2" style="width:16px; height:16px;"></i> Hapus Unit
            </button>
            <div style="display:flex; gap:10px;">
                <a href="/ac-units/<?= $unit['id'] ?>" class="btn btn-outline">Batal</a>
                <button type="submit" class="btn btn-primary">
                    <i data-lucide="save" style="width:18px; height:18px;"></i> Simpan Perubahan
                </button>
            </div>
        </div>
    </form>

    <form id="delete-ac-form" method="POST" action="/ac-units/<?= $unit['id'] ?>/delete" style="display:none;">
        <?= csrf_field() ?>
    </form>
</div>

<?php require dirname(__DIR__, 2) . '/layout/footer.php'; ?>
