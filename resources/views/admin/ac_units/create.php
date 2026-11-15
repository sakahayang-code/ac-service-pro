<?php require dirname(__DIR__, 2) . '/layout/header.php'; ?>

<div class="card" style="max-width:760px; margin:0 auto;">
    <div class="card-header">
        <div class="card-title">
            <i data-lucide="snowflake"></i>
            <span>Daftarkan Unit AC Baru</span>
        </div>
        <a href="/ac-units" class="btn btn-outline btn-sm">Kembali</a>
    </div>

    <form method="POST" action="/ac-units">
        <?= csrf_field() ?>
        <div class="form-group">
            <label class="form-label" for="customer_id">Pelanggan Pemilik Unit *</label>
            <select id="customer_id" name="customer_id" class="form-select" required>
                <option value="">-- Pilih Pelanggan --</option>
                <?php foreach ($customers as $c): ?>
                    <option value="<?= $c['id'] ?>" <?= (isset($selectedCustomerId) && $selectedCustomerId === (int)$c['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($c['name']) ?> (<?= htmlspecialchars($c['customer_code']) ?> - <?= htmlspecialchars($c['phone']) ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label" for="brand">Merek AC *</label>
                <input type="text" id="brand" name="brand" class="form-control" list="brand-suggestions" required placeholder="Contoh: Daikin, Panasonic, LG, Sharp">
                <datalist id="brand-suggestions">
                    <option value="Daikin">
                    <option value="Panasonic">
                    <option value="LG">
                    <option value="Sharp">
                    <option value="Mitsubishi Electric">
                    <option value="Mitsubishi Heavy Duty">
                    <option value="Gree">
                    <option value="Aux">
                    <option value="Polytron">
                    <option value="Samsung">
                </datalist>
            </div>
            <div class="form-group">
                <label class="form-label" for="model_type">Tipe / Model AC</label>
                <select id="model_type" name="model_type" class="form-select">
                    <option value="Split Wall">Split Wall</option>
                    <option value="Cassette">Cassette</option>
                    <option value="Ceiling Suspended">Ceiling Suspended</option>
                    <option value="Floor Standing">Floor Standing</option>
                    <option value="Ducting / VRV">Ducting / VRV</option>
                    <option value="Window">Window</option>
                </select>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label" for="capacity_pk">Kapasitas (PK) *</label>
                <select id="capacity_pk" name="capacity_pk" class="form-select">
                    <option value="0.5 PK">0.5 PK (1/2 PK)</option>
                    <option value="0.75 PK">0.75 PK (3/4 PK)</option>
                    <option value="1 PK" selected>1 PK</option>
                    <option value="1.5 PK">1.5 PK</option>
                    <option value="2 PK">2 PK</option>
                    <option value="2.5 PK">2.5 PK</option>
                    <option value="3 PK">3 PK</option>
                    <option value="5 PK">5 PK</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label" for="refrigerant_type">Jenis Freon *</label>
                <select id="refrigerant_type" name="refrigerant_type" class="form-select">
                    <option value="R32" selected>R32 (Ramah Lingkungan)</option>
                    <option value="R410A">R410A (Inverter)</option>
                    <option value="R22">R22 (Konvensional/Lama)</option>
                    <option value="R134a">R134a</option>
                </select>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label" for="room_location">Lokasi Pemasangan Ruangan *</label>
                <input type="text" id="room_location" name="room_location" class="form-control" required placeholder="Contoh: Kamar Tidur Utama, R. Meeting A">
            </div>
            <div class="form-group">
                <label class="form-label" for="serial_number">Nomor Seri (Opsional)</label>
                <input type="text" id="serial_number" name="serial_number" class="form-control" placeholder="Nomor seri pada stiker indoor/outdoor">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label" for="condition">Kondisi Fisik Saat Ini</label>
                <select id="condition" name="condition" class="form-select">
                    <option value="Normal Dingin" selected>Normal Dingin</option>
                    <option value="Kurang Dingin">Kurang Dingin</option>
                    <option value="Hanya Hembus Angin (Mati Kompresor)">Hanya Hembus Angin (Mati Kompresor)</option>
                    <option value="Bocor Air Menetes">Bocor Air Menetes</option>
                    <option value="Mati Total">Mati Total</option>
                    <option value="Berisik / Getar Kencang">Berisik / Getar Kencang</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label" for="warranty_status">Status Garansi</label>
                <select id="warranty_status" name="warranty_status" class="form-select">
                    <option value="Garansi Resmi Pabrik">Garansi Resmi Pabrik</option>
                    <option value="Garansi Servis Toko">Garansi Servis Toko</option>
                    <option value="Habis Garansi" selected>Habis Garansi</option>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label class="form-label" for="notes">Catatan Teknis Unit</label>
            <textarea id="notes" name="notes" class="form-control" rows="2" placeholder="Posisi outdoor di lantai atas / perlu tangga panjang..."></textarea>
        </div>

        <div style="display:flex; justify-content:flex-end; gap:12px; margin-top:20px;">
            <a href="/ac-units" class="btn btn-outline">Batal</a>
            <button type="submit" class="btn btn-primary">
                <i data-lucide="save" style="width:18px; height:18px;"></i>
                <span>Simpan Unit AC</span>
            </button>
        </div>
    </form>
</div>

<?php require dirname(__DIR__, 2) . '/layout/footer.php'; ?>
