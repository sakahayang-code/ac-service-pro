<?php require dirname(__DIR__) . '/layout/header.php'; ?>

<!-- Top Action Header -->
<div class="card" style="margin-bottom:14px; padding:16px;">
    <div style="display:flex; justify-content:space-between; align-items:center;">
        <a href="/technician/tasks" class="btn btn-outline btn-sm" style="padding:4px 10px;">
            &larr; Daftar Tugas
        </a>
        <span class="badge badge-<?= $task['status'] ?>" style="font-size:0.8rem;">
            <?= str_replace('_', ' ', $task['status']) ?>
        </span>
    </div>
    <div style="margin-top:10px;">
        <h2 style="font-size:1.3rem; color:var(--primary); font-weight:800;"><?= htmlspecialchars($task['customer_name']) ?></h2>
        <div style="font-size:0.8rem; color:var(--text-muted);">
            WO: <strong><?= htmlspecialchars($task['order_number']) ?></strong> &bull; Jadwal: <?= format_date($task['scheduled_date']) ?> (<?= htmlspecialchars($task['scheduled_time']) ?>)
        </div>
        <div style="font-size:0.85rem; color:var(--text-main); margin-top:4px;">
            📍 <?= htmlspecialchars($task['customer_address']) ?>
        </div>
        <div style="display:flex; gap:8px; margin-top:8px;">
            <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $task['customer_phone']) ?>" target="_blank" class="btn btn-outline btn-sm" style="color:#16a34a; border-color:#bbf7d0;">
                <i data-lucide="message-circle" style="width:14px; height:14px;"></i> Hubungi Pelanggan
            </a>
            <a href="https://maps.google.com/?q=<?= urlencode($task['customer_address']) ?>" target="_blank" class="btn btn-outline btn-sm">
                <i data-lucide="navigation" style="width:14px; height:14px;"></i> Petunjuk Arah
            </a>
        </div>
    </div>
</div>

<!-- STEP 1: CHECK-IN LOKASI -->
<?php if (empty($task['checkin_at'])): ?>
    <div class="card" style="background:#fffbeb; border-color:#fde68a; padding:18px; text-align:center; margin-bottom:14px;">
        <div style="width:48px; height:48px; border-radius:50%; background:#fef3c7; color:#b45309; display:flex; align-items:center; justify-content:center; margin:0 auto 10px;">
            <i data-lucide="map-pin" style="width:26px; height:26px;"></i>
        </div>
        <h3 style="font-size:1.1rem; color:#92400e; font-weight:800; margin-bottom:4px;">Sudah Tiba di Lokasi Pelanggan?</h3>
        <p style="font-size:0.85rem; color:#b45309; margin-bottom:14px;">
            Klik tombol di bawah ini untuk mencatat waktu mulai pengerjaan dan mengubah status menjadi SEDANG DIKERJAKAN.
        </p>
        <form method="POST" action="/technician/tasks/<?= $task['id'] ?>/checkin">
            <?= csrf_field() ?>
            <button type="submit" class="btn btn-primary btn-block btn-lg" style="background:#b45309; border-color:#92400e;">
                <i data-lucide="check" style="width:18px; height:18px;"></i>
                <span>Check-in Lokasi Sekarang</span>
            </button>
        </form>
    </div>
<?php else: ?>
    <div class="card" style="background:#f0fdf4; border-color:#bbf7d0; padding:12px 16px; margin-bottom:14px; display:flex; align-items:center; justify-content:space-between;">
        <div style="display:flex; align-items:center; gap:8px;">
            <i data-lucide="check-circle" style="width:20px; height:20px; color:#16a34a;"></i>
            <span style="font-size:0.85rem; font-weight:600; color:#166534;">
                Telah Check-in: <?= format_datetime($task['checkin_at'], 'H:i WIB') ?>
            </span>
        </div>
        <span class="badge badge-green" style="font-size:0.7rem;">DALAM PENGERJAAN</span>
    </div>
<?php endif; ?>

<!-- STEP 2: CHECKLIST PEMERIKSAAN FISIK -->
<div class="card" style="margin-bottom:14px;">
    <div class="card-header">
        <div class="card-title">
            <i data-lucide="check-square"></i>
            <span>Checklist Pemeriksaan Standar</span>
        </div>
    </div>
    <form method="POST" action="/technician/tasks/<?= $task['id'] ?>/checklist">
        <?= csrf_field() ?>
        <div style="display:flex; flex-direction:column; gap:10px;">
            <?php foreach ($checklists as $chk): ?>
                <div style="padding:10px 12px; background:var(--surface-alt); border-radius:var(--radius-sm); border:1px solid var(--border);">
                    <div style="font-size:0.9rem; font-weight:700; color:var(--primary); margin-bottom:6px;">
                        <?= htmlspecialchars($chk['checklist_item']) ?>
                    </div>
                    <div style="display:flex; gap:12px; align-items:center; flex-wrap:wrap;">
                        <label style="display:flex; align-items:center; gap:4px; font-size:0.8rem; cursor:pointer;">
                            <input type="radio" name="checklist[<?= $chk['id'] ?>]" value="ok" <?= $chk['status'] === 'ok' ? 'checked' : '' ?>>
                            <span style="color:#16a34a; font-weight:700;">OK / Normal</span>
                        </label>
                        <label style="display:flex; align-items:center; gap:4px; font-size:0.8rem; cursor:pointer;">
                            <input type="radio" name="checklist[<?= $chk['id'] ?>]" value="perbaikan" <?= $chk['status'] === 'perbaikan' ? 'checked' : '' ?>>
                            <span style="color:#d97706; font-weight:700;">Perlu Servis</span>
                        </label>
                        <label style="display:flex; align-items:center; gap:4px; font-size:0.8rem; cursor:pointer;">
                            <input type="radio" name="checklist[<?= $chk['id'] ?>]" value="perlu_ganti" <?= $chk['status'] === 'perlu_ganti' ? 'checked' : '' ?>>
                            <span style="color:#dc2626; font-weight:700;">Perlu Ganti</span>
                        </label>
                    </div>
                    <div style="margin-top:6px;">
                        <input type="text" name="notes[<?= $chk['id'] ?>]" class="form-control" style="font-size:0.8rem; padding:4px 8px;" placeholder="Catatan teknis (opsional)" value="<?= htmlspecialchars($chk['notes'] ?? '') ?>">
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <button type="submit" class="btn btn-outline btn-sm btn-block" style="margin-top:12px;">
            <i data-lucide="save" style="width:14px; height:14px;"></i> Simpan Checklist
        </button>
    </form>
</div>

<!-- STEP 3: PENGUKURAN TEKNIS & HASIL DIAGNOSIS -->
<div class="card" style="margin-bottom:14px;">
    <div class="card-header">
        <div class="card-title">
            <i data-lucide="gauge"></i>
            <span>Pengukuran & Diagnosis Teknis</span>
        </div>
    </div>
    <form method="POST" action="/technician/tasks/<?= $task['id'] ?>/diagnostics">
        <?= csrf_field() ?>
        <div class="form-row">
            <div class="form-group">
                <label class="form-label" for="current_ampere">Arus Listrik (Ampere)</label>
                <input type="number" step="0.1" id="current_ampere" name="current_ampere" class="form-control" placeholder="Contoh: 3.2" value="<?= htmlspecialchars((string)($diagnostics['current_ampere'] ?? '')) ?>">
            </div>
            <div class="form-group">
                <label class="form-label" for="voltage_volt">Tegangan (Volt AC)</label>
                <input type="number" step="1" id="voltage_volt" name="voltage_volt" class="form-control" placeholder="Contoh: 220" value="<?= htmlspecialchars((string)($diagnostics['voltage_volt'] ?? '')) ?>">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label" for="pressure_psi">Tekanan Freon (PSI)</label>
                <input type="number" step="1" id="pressure_psi" name="pressure_psi" class="form-control" placeholder="Contoh: 140" value="<?= htmlspecialchars((string)($diagnostics['pressure_psi'] ?? '')) ?>">
            </div>
            <div class="form-group">
                <label class="form-label" for="temperature_celsius">Suhu Hembus (°C)</label>
                <input type="number" step="0.1" id="temperature_celsius" name="temperature_celsius" class="form-control" placeholder="Contoh: 16.5" value="<?= htmlspecialchars((string)($diagnostics['temperature_celsius'] ?? '')) ?>">
            </div>
        </div>

        <div class="form-group">
            <label class="form-label" for="diagnostic_result">Hasil Temuan Kerusakan / Diagnosis</label>
            <textarea id="diagnostic_result" name="diagnostic_result" class="form-control" rows="2" placeholder="Jelaskan kondisi evaporator, kompresor, kebocoran..."><?= htmlspecialchars($diagnostics['diagnostic_result'] ?? '') ?></textarea>
        </div>

        <div class="form-group">
            <label class="form-label" for="technician_notes">Rekomendasi Tindakan Teknisi</label>
            <textarea id="technician_notes" name="technician_notes" class="form-control" rows="2" placeholder="Rekomendasi perawatan atau penggantian suku cadang..."><?= htmlspecialchars($diagnostics['technician_notes'] ?? '') ?></textarea>
        </div>

        <button type="submit" class="btn btn-outline btn-sm btn-block">
            <i data-lucide="save" style="width:14px; height:14px;"></i> Simpan Diagnosis
        </button>
    </form>
</div>

<!-- STEP 4: PENGGUNAAN SPAREPART & MATERIAL -->
<div class="card" style="margin-bottom:14px;">
    <div class="card-header">
        <div class="card-title">
            <i data-lucide="boxes"></i>
            <span>Penggunaan Sparepart & Material</span>
        </div>
    </div>

    <!-- Used Materials List -->
    <div style="display:flex; flex-direction:column; gap:8px; margin-bottom:14px;">
        <?php if (empty($materials)): ?>
            <div style="font-size:0.85rem; color:var(--text-muted); text-align:center; padding:12px; background:var(--surface-alt); border-radius:var(--radius-sm);">
                Belum ada material/sparepart yang digunakan. Tambahkan jika ada penggantian komponen.
            </div>
        <?php else: ?>
            <?php foreach ($materials as $m): ?>
                <div style="display:flex; justify-content:space-between; align-items:center; padding:8px 12px; background:#eff6ff; border:1px solid #bfdbfe; border-radius:var(--radius-sm);">
                    <div>
                        <strong style="color:var(--primary); font-size:0.85rem;"><?= htmlspecialchars($m['item_name']) ?></strong>
                        <div style="font-size:0.75rem; color:var(--text-muted);">
                            <?= $m['quantity'] ?> <?= htmlspecialchars($m['unit']) ?> &times; <?= format_rupiah($m['unit_price']) ?>
                        </div>
                    </div>
                    <div style="display:flex; align-items:center; gap:10px;">
                        <strong style="color:var(--primary); font-size:0.9rem;"><?= format_rupiah($m['subtotal']) ?></strong>
                        <form method="POST" action="/technician/tasks/<?= $task['id'] ?>/material/remove" style="display:inline;" onsubmit="return confirm('Hapus pemakaian sparepart ini?')">
                            <?= csrf_field() ?>
                            <input type="hidden" name="material_id" value="<?= $m['id'] ?>">
                            <button type="submit" style="background:none; border:none; color:var(--danger); cursor:pointer;" title="Hapus">
                                <i data-lucide="trash-2" style="width:16px; height:16px;"></i>
                            </button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Add Material Form -->
    <form method="POST" action="/technician/tasks/<?= $task['id'] ?>/material" style="background:var(--surface-alt); padding:12px; border-radius:var(--radius-sm); border:1px solid var(--border);">
        <?= csrf_field() ?>
        <div style="font-size:0.8rem; font-weight:700; color:var(--primary); margin-bottom:8px;">+ Tambah Sparepart dari Stok</div>
        <div class="form-row">
            <div class="form-group" style="flex:2;">
                <label class="form-label" for="inventory_item_id">Pilih Barang</label>
                <select id="inventory_item_id" name="inventory_item_id" class="form-select" required>
                    <option value="">-- Pilih Suku Cadang --</option>
                    <?php foreach ($inventoryItems as $item): ?>
                        <option value="<?= $item['id'] ?>">
                            <?= htmlspecialchars($item['name']) ?> (Stok: <?= $item['current_stock'] ?> <?= $item['unit'] ?> - <?= format_rupiah($item['selling_price']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group" style="flex:1;">
                <label class="form-label" for="quantity">Jumlah Qty</label>
                <input type="number" step="1" id="quantity" name="quantity" class="form-control" value="1" min="1" required>
            </div>
        </div>
        <button type="submit" class="btn btn-secondary btn-sm btn-block">
            <i data-lucide="plus" style="width:14px; height:14px;"></i> Tambah & Potong Stok Gudang
        </button>
    </form>
</div>

<!-- STEP 5: TANDA TANGAN DIGITAL & SELESAIKAN PEKERJAAN -->
<div class="card" style="border: 2px solid var(--secondary); margin-bottom:20px;">
    <div class="card-header" style="background:#eff6ff;">
        <div class="card-title" style="color:var(--secondary);">
            <i data-lucide="pen-tool"></i>
            <span>Tanda Tangan Pelanggan & Penyelesaian</span>
        </div>
    </div>

    <form id="finish-task-form" method="POST" action="/technician/tasks/<?= $task['id'] ?>/finish">
        <?= csrf_field() ?>
        <input type="hidden" id="signature_data" name="signature_data" value="<?= htmlspecialchars($signature['signature_data'] ?? '') ?>">

        <div class="form-group">
            <label class="form-label" for="signer_name">Nama Pelanggan yang Menandatangani *</label>
            <input type="text" id="signer_name" name="signer_name" class="form-control" required placeholder="Nama penerima pekerjaan" value="<?= htmlspecialchars($signature['signer_name'] ?? $task['customer_name']) ?>">
        </div>

        <div class="form-group">
            <label class="form-label">Tanda Tangan Digital pada Kotak di Bawah Ini *</label>
            <div style="border: 2px dashed #93c5fd; border-radius: var(--radius-sm); background: #ffffff; position: relative;">
                <canvas id="signature-canvas" style="width: 100%; height: 160px; touch-action: none; cursor: crosshair; display: block;"></canvas>
            </div>
            <div style="display:flex; justify-content:space-between; align-items:center; margin-top:6px;">
                <span style="font-size:0.75rem; color:var(--text-muted);">Gunakan jari atau stylus untuk bertanda tangan</span>
                <button type="button" id="clear-sig-btn" class="btn btn-outline btn-sm" style="padding:2px 8px; font-size:0.75rem;">Hapus Tanda Tangan</button>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label" for="rating">Rating Kepuasan Pelanggan</label>
                <select id="rating" name="rating" class="form-select">
                    <option value="5" selected>⭐⭐⭐⭐⭐ (5 - Sangat Puas)</option>
                    <option value="4">⭐⭐⭐⭐ (4 - Puas)</option>
                    <option value="3">⭐⭐⭐ (3 - Cukup)</option>
                    <option value="2">⭐⭐ (2 - Kurang Puas)</option>
                    <option value="1">⭐ (1 - Tidak Puas)</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label" for="customer_notes">Ulasan / Catatan Pelanggan</label>
                <input type="text" id="customer_notes" name="customer_notes" class="form-control" placeholder="Contoh: Sangat bersih, AC dingin kembali." value="<?= htmlspecialchars($signature['customer_notes'] ?? '') ?>">
            </div>
        </div>

        <button type="submit" class="btn btn-success btn-block btn-lg" style="margin-top:10px; box-shadow:0 4px 14px rgba(22,163,74,0.4);">
            <i data-lucide="check-circle" style="width:20px; height:20px;"></i>
            <span>Selesaikan Servis & Check-Out</span>
        </button>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const sigPad = window.initSignaturePad('signature-canvas');
        const clearBtn = document.getElementById('clear-sig-btn');
        const form = document.getElementById('finish-task-form');
        const sigInput = document.getElementById('signature_data');

        if (clearBtn && sigPad) {
            clearBtn.addEventListener('click', () => {
                sigPad.clear();
                sigInput.value = '';
            });
        }

        if (form && sigPad) {
            form.addEventListener('submit', (e) => {
                if (sigPad.isEmpty() && !sigInput.value) {
                    e.preventDefault();
                    alert('Mohon minta pelanggan untuk membubuhkan tanda tangan terlebih dahulu pada kotak tanda tangan.');
                    return false;
                }
                if (!sigPad.isEmpty()) {
                    sigInput.value = sigPad.toDataURL();
                }
            });
        }
    });
</script>

<?php require dirname(__DIR__) . '/layout/footer.php'; ?>
