<?php require dirname(__DIR__, 2) . '/layout/header.php'; ?>

<div class="card" style="max-width:760px; margin:0 auto;">
    <div class="card-header">
        <div class="card-title">
            <i data-lucide="edit-3"></i>
            <span>Ubah Jadwal & Detail Work Order (<?= htmlspecialchars($order['order_number']) ?>)</span>
        </div>
        <a href="/work-orders/<?= $order['id'] ?>" class="btn btn-outline btn-sm">Kembali</a>
    </div>

    <form method="POST" action="/work-orders/<?= $order['id'] ?>/edit">
        <?= csrf_field() ?>
        <div class="form-row">
            <div class="form-group">
                <label class="form-label" for="scheduled_date">Tanggal Kunjungan Baru *</label>
                <input type="date" id="scheduled_date" name="scheduled_date" class="form-control" required value="<?= htmlspecialchars($order['scheduled_date']) ?>">
            </div>
            <div class="form-group">
                <label class="form-label" for="scheduled_time">Jam Kunjungan Baru *</label>
                <input type="time" id="scheduled_time" name="scheduled_time" class="form-control" required value="<?= htmlspecialchars($order['scheduled_time']) ?>">
            </div>
            <div class="form-group">
                <label class="form-label" for="priority">Prioritas</label>
                <select id="priority" name="priority" class="form-select">
                    <option value="normal" <?= $order['priority'] === 'normal' ? 'selected' : '' ?>>Normal</option>
                    <option value="high" <?= $order['priority'] === 'high' ? 'selected' : '' ?>>Tinggi (Segera)</option>
                    <option value="emergency" <?= $order['priority'] === 'emergency' ? 'selected' : '' ?>>Darurat</option>
                    <option value="low" <?= $order['priority'] === 'low' ? 'selected' : '' ?>>Rendah</option>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label class="form-label" for="complaints">Keluhan Pelanggan & Catatan Masalah *</label>
            <textarea id="complaints" name="complaints" class="form-control" rows="3" required><?= htmlspecialchars($order['complaints']) ?></textarea>
        </div>

        <div class="form-group">
            <label class="form-label" for="notes">Catatan Tambahan Dispatcher / CS</label>
            <textarea id="notes" name="notes" class="form-control" rows="2"><?= htmlspecialchars($order['notes'] ?? '') ?></textarea>
        </div>

        <div style="display:flex; justify-content:flex-end; gap:12px; margin-top:20px;">
            <a href="/work-orders/<?= $order['id'] ?>" class="btn btn-outline">Batal</a>
            <button type="submit" class="btn btn-primary">
                <i data-lucide="save" style="width:18px; height:18px;"></i>
                <span>Simpan Perubahan</span>
            </button>
        </div>
    </form>
</div>

<?php require dirname(__DIR__, 2) . '/layout/footer.php'; ?>
