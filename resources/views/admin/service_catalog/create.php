<?php require dirname(__DIR__, 2) . '/layout/header.php'; ?>

<div class="card" style="max-width:760px; margin:0 auto;">
    <div class="card-header">
        <div class="card-title">
            <i data-lucide="tag"></i>
            <span>Tambah Layanan Servis Baru</span>
        </div>
        <a href="/service-catalog" class="btn btn-outline btn-sm">Kembali</a>
    </div>

    <form method="POST" action="/service-catalog">
        <?= csrf_field() ?>
        <div class="form-row">
            <div class="form-group">
                <label class="form-label" for="category_id">Kategori Layanan *</label>
                <select id="category_id" name="category_id" class="form-select" required>
                    <option value="">-- Pilih Kategori --</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label" for="code">Kode Layanan *</label>
                <input type="text" id="code" name="code" class="form-control" required placeholder="Contoh: SRV-WASH-04">
            </div>
        </div>

        <div class="form-group">
            <label class="form-label" for="name">Nama Layanan Servis *</label>
            <input type="text" id="name" name="name" class="form-control" required placeholder="Contoh: Cuci AC Inverter + Disinfektan">
        </div>

        <div class="form-group">
            <label class="form-label" for="description">Deskripsi Lengkap Pekerjaan</label>
            <textarea id="description" name="description" class="form-control" rows="2" placeholder="Uraian item yang dicakup dalam servis ini..."></textarea>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label" for="capacity_pk_filter">Filter Kapasitas PK</label>
                <select id="capacity_pk_filter" name="capacity_pk_filter" class="form-select">
                    <option value="ALL">Semua Kapasitas (ALL PK)</option>
                    <option value="0.5 - 1 PK">0.5 - 1 PK</option>
                    <option value="1.5 - 2 PK">1.5 - 2 PK</option>
                    <option value="> 2 PK">> 2 PK</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label" for="refrigerant_filter">Filter Jenis Freon</label>
                <select id="refrigerant_filter" name="refrigerant_filter" class="form-select">
                    <option value="ALL">Semua Freon (ALL)</option>
                    <option value="R32">R32</option>
                    <option value="R410A">R410A</option>
                    <option value="R22">R22</option>
                </select>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label" for="base_price">Tarif Standar / Reguler (Rp) *</label>
                <input type="number" id="base_price" name="base_price" class="form-control" required placeholder="Contoh: 75000">
            </div>
            <div class="form-group">
                <label class="form-label" for="contract_price">Tarif Pelanggan Kontrak (Rp)</label>
                <input type="number" id="contract_price" name="contract_price" class="form-control" placeholder="Contoh: 65000">
            </div>
            <div class="form-group">
                <label class="form-label" for="estimated_duration_minutes">Estimasi Durasi (Menit)</label>
                <input type="number" id="estimated_duration_minutes" name="estimated_duration_minutes" class="form-control" value="60">
            </div>
        </div>

        <div style="display:flex; justify-content:flex-end; gap:12px; margin-top:20px;">
            <a href="/service-catalog" class="btn btn-outline">Batal</a>
            <button type="submit" class="btn btn-primary">
                <i data-lucide="save" style="width:18px; height:18px;"></i>
                <span>Simpan Layanan</span>
            </button>
        </div>
    </form>
</div>

<?php require dirname(__DIR__, 2) . '/layout/footer.php'; ?>
