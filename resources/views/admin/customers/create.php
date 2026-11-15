<?php require dirname(__DIR__, 2) . '/layout/header.php'; ?>

<div class="card" style="max-width:760px; margin:0 auto;">
    <div class="card-header">
        <div class="card-title">
            <i data-lucide="user-plus"></i>
            <span>Tambah Data Pelanggan Baru</span>
        </div>
        <a href="/customers" class="btn btn-outline btn-sm">Kembali</a>
    </div>

    <form method="POST" action="/customers">
        <?= csrf_field() ?>
        <div class="form-row">
            <div class="form-group">
                <label class="form-label" for="name">Nama Lengkap / Instansi *</label>
                <input type="text" id="name" name="name" class="form-control" required placeholder="Contoh: Bpk. Haryanto">
            </div>
            <div class="form-group">
                <label class="form-label" for="category">Kategori Pelanggan *</label>
                <select id="category" name="category" class="form-select">
                    <option value="household">Rumah Tangga</option>
                    <option value="office">Perkantoran</option>
                    <option value="school">Sekolah / Kampus</option>
                    <option value="shop">Toko / Ruko</option>
                    <option value="hotel">Hotel / Villa</option>
                    <option value="contract">Pelanggan Kontrak</option>
                </select>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label" for="phone">Nomor WhatsApp / HP *</label>
                <input type="tel" id="phone" name="phone" class="form-control" required placeholder="08123456789">
            </div>
            <div class="form-group">
                <label class="form-label" for="email">Alamat Email (Opsional)</label>
                <input type="email" id="email" name="email" class="form-control" placeholder="nama@email.com">
            </div>
        </div>

        <div class="form-group">
            <label class="form-label" for="address">Alamat Lengkap Lokasi *</label>
            <textarea id="address" name="address" class="form-control" rows="3" required placeholder="Jalan, nomor, blok, RT/RW, patokan lokasi..."></textarea>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label" for="latitude">Latitude (Opsional)</label>
                <input type="number" step="any" id="latitude" name="latitude" class="form-control" placeholder="-6.2088">
            </div>
            <div class="form-group">
                <label class="form-label" for="longitude">Longitude (Opsional)</label>
                <input type="number" step="any" id="longitude" name="longitude" class="form-control" placeholder="106.8456">
            </div>
        </div>

        <div class="form-group">
            <label class="form-label" for="notes">Catatan Khusus Lokasi</label>
            <textarea id="notes" name="notes" class="form-control" rows="2" placeholder="Catatan parkir, tangga, izin satpam..."></textarea>
        </div>

        <div style="display:flex; justify-content:flex-end; gap:12px; margin-top:20px;">
            <a href="/customers" class="btn btn-outline">Batal</a>
            <button type="submit" class="btn btn-primary">
                <i data-lucide="save" style="width:18px; height:18px;"></i>
                <span>Simpan Pelanggan</span>
            </button>
        </div>
    </form>
</div>

<?php require dirname(__DIR__, 2) . '/layout/footer.php'; ?>
