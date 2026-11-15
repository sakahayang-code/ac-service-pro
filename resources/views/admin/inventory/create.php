<?php require dirname(__DIR__, 2) . '/layout/header.php'; ?>

<div class="card" style="max-width:760px; margin:0 auto;">
    <div class="card-header">
        <div class="card-title">
            <i data-lucide="plus-circle"></i>
            <span>Tambah Suku Cadang / Sparepart Baru</span>
        </div>
        <a href="/inventory" class="btn btn-outline btn-sm">Kembali</a>
    </div>

    <form method="POST" action="/inventory">
        <?= csrf_field() ?>
        <div class="form-row">
            <div class="form-group">
                <label class="form-label" for="sku">Kode SKU Barang *</label>
                <input type="text" id="sku" name="sku" class="form-control" required placeholder="Contoh: PRT-CAP-45UF">
            </div>
            <div class="form-group">
                <label class="form-label" for="barcode">Barcode (Opsional)</label>
                <input type="text" id="barcode" name="barcode" class="form-control" placeholder="Scan barcode produk">
            </div>
        </div>

        <div class="form-group">
            <label class="form-label" for="name">Nama Barang / Suku Cadang *</label>
            <input type="text" id="name" name="name" class="form-control" required placeholder="Contoh: Kapasitor Kompresor 45 uF 450V">
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label" for="category">Kategori *</label>
                <select id="category" name="category" class="form-select">
                    <option value="Kapasitor">Kapasitor</option>
                    <option value="Refrigerant">Freon / Refrigerant</option>
                    <option value="Pipa & Isolasi">Pipa & Isolasi</option>
                    <option value="Elektrikal">Komponen Elektrikal</option>
                    <option value="Protektor">Protektor & Overload</option>
                    <option value="Motor & Fan">Motor Fan & Blower</option>
                    <option value="Aksesoris">Aksesoris & Remote</option>
                    <option value="Bahan Kimia">Pembersih & Kimia AC</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label" for="unit">Satuan Barang *</label>
                <select id="unit" name="unit" class="form-select">
                    <option value="Pcs" selected>Pcs (Buah)</option>
                    <option value="Tabung">Tabung (Cylinder)</option>
                    <option value="Meter">Meter</option>
                    <option value="Roll">Roll</option>
                    <option value="Set">Set</option>
                    <option value="Botol">Botol / Kaleng</option>
                    <option value="Liter">Liter</option>
                </select>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label" for="cost_price">Harga Beli / HPP (Rp) *</label>
                <input type="number" id="cost_price" name="cost_price" class="form-control" required placeholder="Contoh: 60000">
            </div>
            <div class="form-group">
                <label class="form-label" for="selling_price">Harga Jual ke Pelanggan (Rp) *</label>
                <input type="number" id="selling_price" name="selling_price" class="form-control" required placeholder="Contoh: 110000">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label" for="initial_stock">Stok Awal Saat Ini</label>
                <input type="number" step="1" id="initial_stock" name="initial_stock" class="form-control" value="0">
            </div>
            <div class="form-group">
                <label class="form-label" for="min_stock">Batas Minimum Peringatan Stok</label>
                <input type="number" step="1" id="min_stock" name="min_stock" class="form-control" value="5">
            </div>
        </div>

        <div style="display:flex; justify-content:flex-end; gap:12px; margin-top:20px;">
            <a href="/inventory" class="btn btn-outline">Batal</a>
            <button type="submit" class="btn btn-primary">
                <i data-lucide="save" style="width:18px; height:18px;"></i>
                <span>Simpan Barang</span>
            </button>
        </div>
    </form>
</div>

<?php require dirname(__DIR__, 2) . '/layout/footer.php'; ?>
