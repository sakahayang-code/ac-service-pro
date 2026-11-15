<?php require dirname(__DIR__, 2) . '/layout/header.php'; ?>

<div class="card" style="max-width:760px; margin:0 auto;">
    <div class="card-header">
        <div class="card-title">
            <i data-lucide="edit-3"></i>
            <span>Ubah Data Barang (<?= htmlspecialchars($item['sku']) ?>)</span>
        </div>
        <a href="/inventory/<?= $item['id'] ?>" class="btn btn-outline btn-sm">Kembali</a>
    </div>

    <form method="POST" action="/inventory/<?= $item['id'] ?>/edit">
        <?= csrf_field() ?>
        <div class="form-group">
            <label class="form-label" for="name">Nama Barang / Suku Cadang *</label>
            <input type="text" id="name" name="name" class="form-control" required value="<?= htmlspecialchars($item['name']) ?>">
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label" for="category">Kategori *</label>
                <input type="text" id="category" name="category" class="form-control" required value="<?= htmlspecialchars($item['category']) ?>">
            </div>
            <div class="form-group">
                <label class="form-label" for="unit">Satuan Barang *</label>
                <input type="text" id="unit" name="unit" class="form-control" required value="<?= htmlspecialchars($item['unit']) ?>">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label" for="cost_price">Harga Beli / HPP (Rp) *</label>
                <input type="number" id="cost_price" name="cost_price" class="form-control" required value="<?= (int)$item['cost_price'] ?>">
            </div>
            <div class="form-group">
                <label class="form-label" for="selling_price">Harga Jual ke Pelanggan (Rp) *</label>
                <input type="number" id="selling_price" name="selling_price" class="form-control" required value="<?= (int)$item['selling_price'] ?>">
            </div>
        </div>

        <div class="form-group">
            <label class="form-label" for="min_stock">Batas Minimum Peringatan Stok</label>
            <input type="number" step="1" id="min_stock" name="min_stock" class="form-control" value="<?= (int)$item['min_stock'] ?>">
        </div>

        <div style="display:flex; justify-content:flex-end; gap:12px; margin-top:20px;">
            <a href="/inventory/<?= $item['id'] ?>" class="btn btn-outline">Batal</a>
            <button type="submit" class="btn btn-primary">
                <i data-lucide="save" style="width:18px; height:18px;"></i>
                <span>Simpan Perubahan</span>
            </button>
        </div>
    </form>
</div>

<?php require dirname(__DIR__, 2) . '/layout/footer.php'; ?>
