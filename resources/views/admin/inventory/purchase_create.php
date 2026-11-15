<?php require dirname(__DIR__, 2) . '/layout/header.php'; ?>

<div class="card" style="max-width:820px; margin:0 auto;">
    <div class="card-header">
        <div class="card-title">
            <i data-lucide="shopping-cart"></i>
            <span>Buat Purchase Order (PO) Belanja Sparepart</span>
        </div>
        <a href="/purchases" class="btn btn-outline btn-sm">Kembali</a>
    </div>

    <form method="POST" action="/purchases">
        <?= csrf_field() ?>
        <div class="form-row">
            <div class="form-group" style="flex:2;">
                <label class="form-label" for="supplier_id">Pilih Supplier / Pemasok *</label>
                <select id="supplier_id" name="supplier_id" class="form-select" required>
                    <option value="">-- Pilih Supplier --</option>
                    <?php foreach ($suppliers as $s): ?>
                        <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group" style="flex:1;">
                <label class="form-label" for="purchase_date">Tanggal Pembelian *</label>
                <input type="date" id="purchase_date" name="purchase_date" class="form-control" required value="<?= date('Y-m-d') ?>">
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">Daftar Barang yang Dibeli</label>
            <div id="po-items-container" style="display:flex; flex-direction:column; gap:10px; margin-bottom:12px;">
                <div class="po-item-row" style="display:flex; gap:10px; align-items:center; background:var(--surface-alt); padding:10px; border-radius:var(--radius-sm); border:1px solid var(--border);">
                    <div style="flex:3;">
                        <select name="items[]" class="form-select po-item-select" required onchange="updateRowCost(this)">
                            <option value="">-- Pilih Barang --</option>
                            <?php foreach ($items as $it): ?>
                                <option value="<?= $it['id'] ?>" data-cost="<?= $it['cost_price'] ?>">
                                    <?= htmlspecialchars($it['name']) ?> (HPP: <?= format_rupiah($it['cost_price']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div style="flex:1;">
                        <input type="number" step="1" name="quantities[]" class="form-control po-qty" value="1" min="1" placeholder="Qty" required onchange="calculateGrandTotal()">
                    </div>
                    <div style="flex:2;">
                        <input type="number" name="unit_costs[]" class="form-control po-cost" placeholder="Harga Beli (Rp)" required onchange="calculateGrandTotal()">
                    </div>
                </div>
            </div>
            <button type="button" class="btn btn-outline btn-sm" onclick="addPoRow()">+ Tambah Baris Barang</button>
        </div>

        <div class="form-group">
            <label class="form-label" for="notes">Catatan Pembelian</label>
            <textarea id="notes" name="notes" class="form-control" rows="2" placeholder="Nomor faktur supplier / tempo pembayaran..."></textarea>
        </div>

        <div style="display:flex; justify-content:space-between; align-items:center; margin-top:20px; padding-top:16px; border-top:1px solid var(--border);">
            <div style="font-size:1.1rem; font-weight:800; color:var(--primary);">
                Total: <span id="grand-total-display">Rp 0</span>
            </div>
            <div style="display:flex; gap:10px;">
                <a href="/purchases" class="btn btn-outline">Batal</a>
                <button type="submit" class="btn btn-primary">
                    <i data-lucide="check" style="width:18px; height:18px;"></i>
                    <span>Simpan & Tambah ke Stok Gudang</span>
                </button>
            </div>
        </div>
    </form>
</div>

<script>
    function updateRowCost(selectEl) {
        const selectedOpt = selectEl.options[selectEl.selectedIndex];
        const cost = selectedOpt.getAttribute('data-cost') || 0;
        const row = selectEl.closest('.po-item-row');
        const costInput = row.querySelector('.po-cost');
        if (costInput) {
            costInput.value = cost;
        }
        calculateGrandTotal();
    }

    function calculateGrandTotal() {
        let total = 0;
        document.querySelectorAll('.po-item-row').forEach(row => {
            const qty = parseFloat(row.querySelector('.po-qty')?.value) || 0;
            const cost = parseFloat(row.querySelector('.po-cost')?.value) || 0;
            total += (qty * cost);
        });
        document.getElementById('grand-total-display').innerText = 'Rp ' + total.toLocaleString('id-ID');
    }

    function addPoRow() {
        const container = document.getElementById('po-items-container');
        const firstRow = container.querySelector('.po-item-row');
        const clone = firstRow.cloneNode(true);
        clone.querySelector('.po-item-select').value = '';
        clone.querySelector('.po-qty').value = '1';
        clone.querySelector('.po-cost').value = '';
        container.appendChild(clone);
    }
</script>

<?php require dirname(__DIR__, 2) . '/layout/footer.php'; ?>
