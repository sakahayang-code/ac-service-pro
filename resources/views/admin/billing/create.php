<?php require dirname(__DIR__, 2) . '/layout/header.php'; ?>

<div class="card" style="max-width:820px; margin:0 auto;">
    <div class="card-header">
        <div class="card-title">
            <i data-lucide="receipt"></i>
            <span>Terbitkan Faktur Tagihan Pelanggan Baru</span>
        </div>
        <a href="/invoices" class="btn btn-outline btn-sm">Kembali</a>
    </div>

    <form method="POST" action="/invoices">
        <?= csrf_field() ?>
        
        <!-- WO Selection -->
        <div class="form-row">
            <div class="form-group" style="flex:2;">
                <label class="form-label" for="work_order_id">Pilih Work Order (Opsional)</label>
                <select id="work_order_id" name="work_order_id" class="form-select" onchange="window.location.href='/invoices/create?work_order_id='+this.value">
                    <option value="">-- Faktur Tanpa WO (Manual / Penjualan Langsung) --</option>
                    <?php foreach ($workOrders as $wo): ?>
                        <option value="<?= $wo['id'] ?>" <?= ($selectedWoId === (int)$wo['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($wo['order_number']) ?> &bull; <?= htmlspecialchars($wo['customer_name']) ?> (Estimasi: <?= format_rupiah($wo['final_price'] ?: $wo['estimated_price']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group" style="flex:2;">
                <label class="form-label" for="customer_id">Pelanggan Penerima Tagihan *</label>
                <select id="customer_id" name="customer_id" class="form-select" required>
                    <option value="">-- Pilih Pelanggan --</option>
                    <?php foreach ($customers as $c): ?>
                        <option value="<?= $c['id'] ?>" <?= ($selectedWo && (int)$selectedWo['customer_id'] === (int)$c['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($c['name']) ?> (<?= htmlspecialchars($c['phone']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label" for="issue_date">Tanggal Faktur</label>
                <input type="date" id="issue_date" name="issue_date" class="form-control" required value="<?= date('Y-m-d') ?>">
            </div>
            <div class="form-group">
                <label class="form-label" for="due_date">Jatuh Tempo Pembayaran</label>
                <input type="date" id="due_date" name="due_date" class="form-control" required value="<?= date('Y-m-d', strtotime('+7 days')) ?>">
            </div>
            <div class="form-group">
                <label class="form-label" for="visit_fee">Biaya Kunjungan / Transport (Rp)</label>
                <input type="number" id="visit_fee" name="visit_fee" class="form-control" value="0" onchange="calculateInvoiceTotal()">
            </div>
            <div class="form-group">
                <label class="form-label" for="discount_amount">Potongan / Diskon (Rp)</label>
                <input type="number" id="discount_amount" name="discount_amount" class="form-control" value="0" onchange="calculateInvoiceTotal()">
            </div>
        </div>

        <!-- Items Table -->
        <div class="form-group">
            <label class="form-label">Rincian Item Pekerjaan & Sparepart</label>
            <div id="invoice-items-container" style="display:flex; flex-direction:column; gap:8px; margin-bottom:10px;">
                <?php if ($selectedWo && (!empty($woServices) || !empty($woMaterials))): ?>
                    <?php foreach ($woServices as $s): ?>
                        <div class="inv-item-row" style="display:flex; gap:8px; align-items:center; background:var(--surface-alt); padding:8px 10px; border-radius:var(--radius-sm); border:1px solid var(--border);">
                            <div style="width:130px;">
                                <select name="types[]" class="form-select">
                                    <option value="service" selected>Jasa Servis</option>
                                    <option value="material">Sparepart</option>
                                </select>
                            </div>
                            <div style="flex:3;">
                                <input type="text" name="descriptions[]" class="form-control" value="<?= htmlspecialchars($s['service_name']) ?>" required>
                            </div>
                            <div style="flex:1;">
                                <input type="number" step="1" name="quantities[]" class="form-control inv-qty" value="<?= (int)$s['quantity'] ?>" min="1" required onchange="calculateInvoiceTotal()">
                            </div>
                            <div style="flex:2;">
                                <input type="number" name="prices[]" class="form-control inv-price" value="<?= (int)$s['unit_price'] ?>" required onchange="calculateInvoiceTotal()">
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <?php foreach ($woMaterials as $m): ?>
                        <div class="inv-item-row" style="display:flex; gap:8px; align-items:center; background:#eff6ff; padding:8px 10px; border-radius:var(--radius-sm); border:1px solid #bfdbfe;">
                            <div style="width:130px;">
                                <select name="types[]" class="form-select">
                                    <option value="service">Jasa Servis</option>
                                    <option value="material" selected>Sparepart</option>
                                </select>
                            </div>
                            <div style="flex:3;">
                                <input type="text" name="descriptions[]" class="form-control" value="<?= htmlspecialchars($m['item_name']) ?>" required>
                            </div>
                            <div style="flex:1;">
                                <input type="number" step="1" name="quantities[]" class="form-control inv-qty" value="<?= (int)$m['quantity'] ?>" min="1" required onchange="calculateInvoiceTotal()">
                            </div>
                            <div style="flex:2;">
                                <input type="number" name="prices[]" class="form-control inv-price" value="<?= (int)$m['unit_price'] ?>" required onchange="calculateInvoiceTotal()">
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="inv-item-row" style="display:flex; gap:8px; align-items:center; background:var(--surface-alt); padding:8px 10px; border-radius:var(--radius-sm); border:1px solid var(--border);">
                        <div style="width:130px;">
                            <select name="types[]" class="form-select">
                                <option value="service" selected>Jasa Servis</option>
                                <option value="material">Sparepart</option>
                            </select>
                        </div>
                        <div style="flex:3;">
                            <input type="text" name="descriptions[]" class="form-control" placeholder="Nama jasa atau komponen..." required>
                        </div>
                        <div style="flex:1;">
                            <input type="number" step="1" name="quantities[]" class="form-control inv-qty" value="1" min="1" required onchange="calculateInvoiceTotal()">
                        </div>
                        <div style="flex:2;">
                            <input type="number" name="prices[]" class="form-control inv-price" placeholder="Harga Satuan (Rp)" required onchange="calculateInvoiceTotal()">
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            <button type="button" class="btn btn-outline btn-sm" onclick="addInvRow()">+ Tambah Baris Item</button>
        </div>

        <div class="form-group">
            <label class="form-label" for="notes">Catatan Faktur (Ditampilkan di Invoice Pelanggan)</label>
            <textarea id="notes" name="notes" class="form-control" rows="2" placeholder="Garansi servis 30 hari..."></textarea>
        </div>

        <div style="display:flex; justify-content:space-between; align-items:center; margin-top:20px; padding-top:16px; border-top:1px solid var(--border);">
            <div style="font-size:1.15rem; font-weight:800; color:var(--primary);">
                Total Tagihan: <span id="inv-total-display">Rp 0</span>
            </div>
            <div style="display:flex; gap:10px;">
                <a href="/invoices" class="btn btn-outline">Batal</a>
                <button type="submit" class="btn btn-primary">
                    <i data-lucide="check" style="width:18px; height:18px;"></i>
                    <span>Terbitkan Faktur</span>
                </button>
            </div>
        </div>
    </form>
</div>

<script>
    function calculateInvoiceTotal() {
        let subtotal = 0;
        document.querySelectorAll('.inv-item-row').forEach(row => {
            const qty = parseFloat(row.querySelector('.inv-qty')?.value) || 0;
            const price = parseFloat(row.querySelector('.inv-price')?.value) || 0;
            subtotal += (qty * price);
        });
        const fee = parseFloat(document.getElementById('visit_fee')?.value) || 0;
        const disc = parseFloat(document.getElementById('discount_amount')?.value) || 0;
        const total = Math.max(0, subtotal + fee - disc);
        document.getElementById('inv-total-display').innerText = 'Rp ' + total.toLocaleString('id-ID');
    }

    function addInvRow() {
        const container = document.getElementById('invoice-items-container');
        const firstRow = container.querySelector('.inv-item-row');
        const clone = firstRow.cloneNode(true);
        clone.querySelector('input[type="text"]').value = '';
        clone.querySelector('.inv-qty').value = '1';
        clone.querySelector('.inv-price').value = '';
        container.appendChild(clone);
    }

    document.addEventListener('DOMContentLoaded', calculateInvoiceTotal);
</script>

<?php require dirname(__DIR__, 2) . '/layout/footer.php'; ?>
