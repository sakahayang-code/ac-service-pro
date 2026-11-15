<?php require dirname(__DIR__, 2) . '/layout/header.php'; ?>

<div class="card" style="max-width:760px; margin:0 auto;">
    <div class="card-header">
        <div class="card-title">
            <i data-lucide="edit-3"></i>
            <span>Ubah Data Pelanggan</span>
        </div>
        <a href="/customers/<?= $customer['id'] ?>" class="btn btn-outline btn-sm">Kembali</a>
    </div>

    <form method="POST" action="/customers/<?= $customer['id'] ?>/edit">
        <?= csrf_field() ?>
        <div class="form-row">
            <div class="form-group">
                <label class="form-label" for="name">Nama Lengkap / Instansi *</label>
                <input type="text" id="name" name="name" class="form-control" required value="<?= htmlspecialchars($customer['name']) ?>">
            </div>
            <div class="form-group">
                <label class="form-label" for="category">Kategori Pelanggan *</label>
                <select id="category" name="category" class="form-select">
                    <option value="household" <?= $customer['category'] === 'household' ? 'selected' : '' ?>>Rumah Tangga</option>
                    <option value="office" <?= $customer['category'] === 'office' ? 'selected' : '' ?>>Perkantoran</option>
                    <option value="school" <?= $customer['category'] === 'school' ? 'selected' : '' ?>>Sekolah / Kampus</option>
                    <option value="shop" <?= $customer['category'] === 'shop' ? 'selected' : '' ?>>Toko / Ruko</option>
                    <option value="hotel" <?= $customer['category'] === 'hotel' ? 'selected' : '' ?>>Hotel / Villa</option>
                    <option value="contract" <?= $customer['category'] === 'contract' ? 'selected' : '' ?>>Pelanggan Kontrak</option>
                </select>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label" for="phone">Nomor WhatsApp / HP *</label>
                <input type="tel" id="phone" name="phone" class="form-control" required value="<?= htmlspecialchars($customer['phone']) ?>">
            </div>
            <div class="form-group">
                <label class="form-label" for="email">Alamat Email</label>
                <input type="email" id="email" name="email" class="form-control" value="<?= htmlspecialchars($customer['email'] ?? '') ?>">
            </div>
        </div>

        <div class="form-group">
            <label class="form-label" for="address">Alamat Lengkap Lokasi *</label>
            <textarea id="address" name="address" class="form-control" rows="3" required><?= htmlspecialchars($customer['address']) ?></textarea>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label" for="status">Status Pelanggan</label>
                <select id="status" name="status" class="form-select">
                    <option value="active" <?= $customer['status'] === 'active' ? 'selected' : '' ?>>Aktif</option>
                    <option value="inactive" <?= $customer['status'] === 'inactive' ? 'selected' : '' ?>>Nonaktif</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label" for="notes">Catatan Khusus</label>
                <input type="text" id="notes" name="notes" class="form-control" value="<?= htmlspecialchars($customer['notes'] ?? '') ?>">
            </div>
        </div>

        <div style="display:flex; justify-content:space-between; align-items:center; margin-top:24px;">
            <button type="button" class="btn btn-danger btn-sm" onclick="if(confirm('Yakin ingin menonaktifkan pelanggan ini?')) { document.getElementById('delete-form').submit(); }">
                <i data-lucide="trash-2" style="width:16px; height:16px;"></i> Hapus Pelanggan
            </button>
            <div style="display:flex; gap:10px;">
                <a href="/customers/<?= $customer['id'] ?>" class="btn btn-outline">Batal</a>
                <button type="submit" class="btn btn-primary">
                    <i data-lucide="save" style="width:18px; height:18px;"></i> Simpan Perubahan
                </button>
            </div>
        </div>
    </form>

    <form id="delete-form" method="POST" action="/customers/<?= $customer['id'] ?>/delete" style="display:none;">
        <?= csrf_field() ?>
    </form>
</div>

<?php require dirname(__DIR__, 2) . '/layout/footer.php'; ?>
