<?php require dirname(__DIR__, 2) . '/layout/header.php'; ?>

<div class="card" style="max-width:800px; margin:0 auto;">
    <div class="card-header">
        <div class="card-title">
            <i data-lucide="plus-circle"></i>
            <span>Buat Work Order (Perintah Kerja Servis) Baru</span>
        </div>
        <a href="/work-orders" class="btn btn-outline btn-sm">Kembali</a>
    </div>

    <form method="POST" action="/work-orders">
        <?= csrf_field() ?>
        
        <!-- Customer Selection -->
        <div class="form-row">
            <div class="form-group" style="flex:2;">
                <label class="form-label" for="customer_id">Pilih Pelanggan *</label>
                <select id="customer_id" name="customer_id" class="form-select" required onchange="window.location.href='/work-orders/create?customer_id='+this.value">
                    <option value="">-- Pilih Pelanggan --</option>
                    <?php foreach ($customers as $c): ?>
                        <option value="<?= $c['id'] ?>" <?= (isset($selectedCustomerId) && $selectedCustomerId === (int)$c['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($c['name']) ?> (<?= htmlspecialchars($c['customer_code']) ?> &bull; <?= htmlspecialchars($c['phone']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group" style="flex:1;">
                <label class="form-label">Pelanggan Belum Ada?</label>
                <a href="/customers/create" class="btn btn-outline btn-block" style="font-size:0.8rem;">+ Pelanggan Baru</a>
            </div>
        </div>

        <!-- AC Unit Selection (if customer has units) -->
        <div class="form-group">
            <label class="form-label" for="ac_unit_id">Pilih Unit AC yang Dikerjakan (Opsional)</label>
            <select id="ac_unit_id" name="ac_unit_id" class="form-select">
                <option value="">-- Servis Umum / Seluruh Unit / Unit Baru --</option>
                <?php foreach ($customerUnits as $u): ?>
                    <option value="<?= $u['id'] ?>" <?= (isset($selectedUnitId) && $selectedUnitId === (int)$u['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($u['brand']) ?> (<?= htmlspecialchars($u['capacity_pk']) ?> - <?= htmlspecialchars($u['refrigerant_type']) ?>) &bull; Lokasi: <?= htmlspecialchars($u['room_location']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <?php if ($selectedCustomerId && empty($customerUnits)): ?>
                <div style="font-size:0.75rem; color:var(--text-muted); margin-top:4px;">
                    Pelanggan ini belum mendaftarkan unit AC. <a href="/ac-units/create?customer_id=<?= $selectedCustomerId ?>" target="_blank" style="color:var(--secondary);">+ Daftarkan unit AC sekarang</a>.
                </div>
            <?php endif; ?>
        </div>

        <!-- Services Multi-select Checkboxes -->
        <div class="form-group">
            <label class="form-label">Pilih Jenis Layanan Servis</label>
            <div style="max-height:180px; overflow-y:auto; border:1px solid var(--border); border-radius:var(--radius-sm); padding:10px; background:var(--surface-alt); display:grid; grid-template-columns:repeat(auto-fit, minmax(240px, 1fr)); gap:8px;">
                <?php foreach ($services as $srv): ?>
                    <label style="display:flex; align-items:center; gap:8px; font-size:0.85rem; cursor:pointer; background:#ffffff; padding:6px 10px; border-radius:4px; border:1px solid var(--border);">
                        <input type="checkbox" name="services[]" value="<?= $srv['id'] ?>">
                        <div style="flex:1;">
                            <div style="font-weight:600; color:var(--text-main);"><?= htmlspecialchars($srv['name']) ?></div>
                            <div style="font-size:0.75rem; color:var(--primary); font-weight:700;"><?= format_rupiah($srv['base_price']) ?></div>
                        </div>
                    </label>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Schedule & Priority -->
        <div class="form-row">
            <div class="form-group">
                <label class="form-label" for="scheduled_date">Tanggal Kunjungan *</label>
                <input type="date" id="scheduled_date" name="scheduled_date" class="form-control" required value="<?= date('Y-m-d') ?>">
            </div>
            <div class="form-group">
                <label class="form-label" for="scheduled_time">Jam Kunjungan *</label>
                <input type="time" id="scheduled_time" name="scheduled_time" class="form-control" required value="09:00">
            </div>
            <div class="form-group">
                <label class="form-label" for="priority">Tingkat Prioritas</label>
                <select id="priority" name="priority" class="form-select">
                    <option value="normal" selected>Normal</option>
                    <option value="high">Tinggi (Segera)</option>
                    <option value="emergency">Darurat (AC Mati Total / Server)</option>
                    <option value="low">Rendah (Fleksibel)</option>
                </select>
            </div>
        </div>

        <!-- Technician Assignment -->
        <div class="form-row">
            <div class="form-group">
                <label class="form-label" for="lead_technician_id">Tugaskan Teknisi Utama (Opsional)</label>
                <select id="lead_technician_id" name="lead_technician_id" class="form-select">
                    <option value="">-- Tetapkan Nanti / Pending Dispatch --</option>
                    <?php foreach ($technicians as $t): ?>
                        <option value="<?= $t['id'] ?>"><?= htmlspecialchars($t['full_name']) ?> (<?= htmlspecialchars($t['code']) ?> - Area: <?= htmlspecialchars($t['work_area']) ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label" for="order_source">Sumber Pesanan</label>
                <select id="order_source" name="order_source" class="form-select">
                    <option value="admin">Admin / Telepon Kantor</option>
                    <option value="whatsapp" selected>WhatsApp Customer Service</option>
                    <option value="customer_portal">Portal Mandiri Pelanggan</option>
                    <option value="contract">Kontrak Maintenance Rutin</option>
                </select>
            </div>
        </div>

        <!-- Address Text -->
        <div class="form-group">
            <label class="form-label" for="address_text">Alamat Lengkap Kunjungan</label>
            <textarea id="address_text" name="address_text" class="form-control" rows="2" placeholder="Kosongkan jika menggunakan alamat profil pelanggan"></textarea>
        </div>

        <!-- Complaints & Issues -->
        <div class="form-group">
            <label class="form-label" for="complaints">Keluhan Pelanggan & Uraian Masalah *</label>
            <textarea id="complaints" name="complaints" class="form-control" rows="3" required placeholder="Contoh: AC kamar anak menetes air, dingin berkurang hanya hembus angin kipas."></textarea>
        </div>

        <div style="display:flex; justify-content:flex-end; gap:12px; margin-top:20px;">
            <a href="/work-orders" class="btn btn-outline">Batal</a>
            <button type="submit" class="btn btn-primary">
                <i data-lucide="check" style="width:18px; height:18px;"></i>
                <span>Terbitkan Work Order</span>
            </button>
        </div>
    </form>
</div>

<?php require dirname(__DIR__, 2) . '/layout/footer.php'; ?>
