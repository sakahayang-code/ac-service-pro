<?php require dirname(__DIR__, 2) . '/layout/header.php'; ?>

<!-- Top Action Header -->
<div class="card" style="margin-bottom:20px;">
    <div style="display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:16px;">
        <div>
            <div style="display:flex; align-items:center; gap:8px; margin-bottom:6px; flex-wrap:wrap;">
                <span class="badge badge-blue" style="font-size:0.85rem; padding:6px 12px;"><?= htmlspecialchars($order['order_number']) ?></span>
                <span class="badge badge-<?= $order['status'] ?>" style="font-size:0.85rem; padding:6px 12px;"><?= str_replace('_', ' ', $order['status']) ?></span>
                <span class="badge <?= $order['priority'] === 'emergency' ? 'badge-red' : ($order['priority'] === 'high' ? 'badge-orange' : 'badge-gray') ?>">
                    Prioritas: <?= strtoupper($order['priority']) ?>
                </span>
                <span class="badge badge-purple">Sumber: <?= htmlspecialchars($order['order_source']) ?></span>
            </div>
            <h2 style="font-size:1.4rem; color:var(--primary); font-weight:800;">
                Jadwal: <?= format_date($order['scheduled_date']) ?> &bull; Jam: <?= htmlspecialchars($order['scheduled_time']) ?> WIB
            </h2>
            <div style="color:var(--text-muted); font-size:0.85rem; margin-top:2px;">
                Dibuat: <?= format_datetime($order['created_at']) ?>
                <?php if ($order['checkin_at']): ?>
                    &bull; Check-in: <?= format_datetime($order['checkin_at']) ?>
                <?php endif; ?>
                <?php if ($order['checkout_at']): ?>
                    &bull; Check-out: <?= format_datetime($order['checkout_at']) ?>
                <?php endif; ?>
            </div>
        </div>

        <div style="display:flex; gap:8px; flex-wrap:wrap;">
            <a href="/work-orders/<?= $order['id'] ?>/spk" target="_blank" class="btn btn-outline btn-sm">
                <i data-lucide="printer" style="width:16px; height:16px;"></i> Cetak SPK
            </a>
            <?php if ($invoice): ?>
                <a href="/invoices/<?= $invoice['id'] ?>" class="btn btn-success btn-sm">
                    <i data-lucide="receipt" style="width:16px; height:16px;"></i> Lihat Faktur (<?= htmlspecialchars($invoice['invoice_number']) ?>)
                </a>
            <?php elseif (in_array($order['status'], ['SELESAI', 'DITAGIHKAN'])): ?>
                <a href="/invoices/create?work_order_id=<?= $order['id'] ?>" class="btn btn-accent btn-sm">
                    <i data-lucide="plus-circle" style="width:16px; height:16px;"></i> Terbitkan Faktur
                </a>
            <?php endif; ?>
            <a href="/work-orders/<?= $order['id'] ?>/edit" class="btn btn-outline btn-sm">
                <i data-lucide="edit-2" style="width:16px; height:16px;"></i> Ubah Jadwal
            </a>
            <a href="/work-orders" class="btn btn-outline btn-sm">Daftar WO</a>
        </div>
    </div>

    <!-- Status Change Fast Action Bar -->
    <div style="margin-top:20px; padding-top:16px; border-top:1px solid var(--border); display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px;">
        <form method="POST" action="/work-orders/<?= $order['id'] ?>/status" style="display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
            <?= csrf_field() ?>
            <span style="font-size:0.85rem; font-weight:700; color:var(--text-main);">Ubah Status Work Order:</span>
            <select name="status" class="form-select" style="width:auto; min-width:180px; padding:6px 12px; font-size:0.85rem;">
                <option value="BARU" <?= $order['status'] === 'BARU' ? 'selected' : '' ?>>1. BARU</option>
                <option value="DIKONFIRMASI" <?= $order['status'] === 'DIKONFIRMASI' ? 'selected' : '' ?>>2. DIKONFIRMASI</option>
                <option value="DIJADWALKAN" <?= $order['status'] === 'DIJADWALKAN' ? 'selected' : '' ?>>3. DIJADWALKAN</option>
                <option value="DALAM_PERJALANAN" <?= $order['status'] === 'DALAM_PERJALANAN' ? 'selected' : '' ?>>4. DALAM PERJALANAN</option>
                <option value="SEDANG_DIKERJAKAN" <?= $order['status'] === 'SEDANG_DIKERJAKAN' ? 'selected' : '' ?>>5. SEDANG DIKERJAKAN</option>
                <option value="MENUNGGU_SPAREPART" <?= $order['status'] === 'MENUNGGU_SPAREPART' ? 'selected' : '' ?>>6. MENUNGGU SPAREPART</option>
                <option value="SELESAI" <?= $order['status'] === 'SELESAI' ? 'selected' : '' ?>>7. SELESAI</option>
                <option value="DITAGIHKAN" <?= $order['status'] === 'DITAGIHKAN' ? 'selected' : '' ?>>8. DITAGIHKAN</option>
                <option value="LUNAS" <?= $order['status'] === 'LUNAS' ? 'selected' : '' ?>>9. LUNAS</option>
                <option value="DITUNDA" <?= $order['status'] === 'DITUNDA' ? 'selected' : '' ?>>DITUNDA</option>
                <option value="KUNJUNGAN_ULANG" <?= $order['status'] === 'KUNJUNGAN_ULANG' ? 'selected' : '' ?>>KUNJUNGAN ULANG</option>
            </select>
            <button type="submit" class="btn btn-secondary btn-sm">Perbarui Status</button>
        </form>

        <?php if ($order['status'] !== 'DIBATALKAN'): ?>
            <button type="button" class="btn btn-danger btn-sm" onclick="openModal('cancel-wo-modal')">
                <i data-lucide="x-circle" style="width:14px; height:14px;"></i> Batalkan Order
            </button>
        <?php else: ?>
            <div style="font-size:0.85rem; color:var(--danger); font-weight:700;">
                Dibatalkan: <?= htmlspecialchars($order['cancellation_reason'] ?? '-') ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Grid: Customer Info & Technician Assignment -->
<div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap:20px; margin-bottom:20px;">
    <!-- Customer & Location Card -->
    <div class="card" style="margin-bottom:0;">
        <div class="card-header">
            <div class="card-title">
                <i data-lucide="user"></i>
                <span>Informasi Pelanggan & Lokasi</span>
            </div>
            <a href="/customers/<?= $order['customer_id'] ?>" style="font-size:0.8rem; font-weight:600;">Profil Pelanggan</a>
        </div>
        <div>
            <h3 style="font-size:1.1rem; color:var(--primary); font-weight:700;"><?= htmlspecialchars($order['customer_name']) ?></h3>
            <div style="color:var(--text-muted); font-size:0.85rem; margin-top:2px;">
                Kode: <?= htmlspecialchars($order['customer_code']) ?> &bull;
                Telp: <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $order['customer_phone']) ?>" target="_blank" style="color:#16a34a; font-weight:600;"><?= htmlspecialchars($order['customer_phone']) ?> (Chat WA)</a>
            </div>
            <div style="margin-top:10px; font-size:0.85rem; color:var(--text-main); line-height:1.4;">
                <strong>Alamat Kunjungan:</strong><br>
                <?= nl2br(htmlspecialchars($order['address_text'] ?: $order['customer_full_address'])) ?>
            </div>
            <div style="margin-top:12px; padding:10px; background:#fff7ed; border-radius:var(--radius-sm); border:1px solid #ffedd5;">
                <div style="font-size:0.75rem; font-weight:700; color:#c2410c; text-transform:uppercase;">Keluhan Pelanggan:</div>
                <div style="font-size:0.85rem; color:#9a3412; margin-top:2px; font-weight:500;">
                    <?= nl2br(htmlspecialchars($order['complaints'])) ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Assigned Technicians Card -->
    <div class="card" style="margin-bottom:0;">
        <div class="card-header">
            <div class="card-title">
                <i data-lucide="user-check"></i>
                <span>Teknisi Bertugas</span>
            </div>
            <button type="button" class="btn btn-outline btn-sm" onclick="openModal('assign-tech-modal')">+ Tugaskan Teknisi</button>
        </div>
        <div style="display:flex; flex-direction:column; gap:10px;">
            <?php if (empty($assignedTechnicians)): ?>
                <div style="text-align:center; padding:20px; color:var(--text-muted); font-size:0.85rem;">
                    Belum ada teknisi yang ditugaskan untuk pekerjaan ini.<br>
                    <button type="button" class="btn btn-primary btn-sm" style="margin-top:8px;" onclick="openModal('assign-tech-modal')">Tugaskan Sekarang</button>
                </div>
            <?php else: ?>
                <?php foreach ($assignedTechnicians as $tech): ?>
                    <div style="display:flex; justify-content:space-between; align-items:center; padding:10px 14px; background:var(--surface-alt); border-radius:var(--radius-sm); border:1px solid var(--border);">
                        <div>
                            <div style="display:flex; align-items:center; gap:8px;">
                                <strong style="color:var(--primary); font-size:0.95rem;"><?= htmlspecialchars($tech['full_name']) ?></strong>
                                <?php if ($tech['is_lead']): ?>
                                    <span class="badge badge-purple" style="font-size:0.7rem;">Ketua Tim</span>
                                <?php else: ?>
                                    <span class="badge badge-gray" style="font-size:0.7rem;">Asisten</span>
                                <?php endif; ?>
                            </div>
                            <div style="font-size:0.75rem; color:var(--text-muted); margin-top:2px;">
                                Kode: <?= htmlspecialchars($tech['code']) ?> &bull; HP: <?= htmlspecialchars($tech['phone']) ?> &bull; Area: <?= htmlspecialchars($tech['work_area']) ?>
                            </div>
                        </div>
                        <div>
                            <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $tech['phone']) ?>" target="_blank" class="btn btn-outline btn-sm" style="color:#16a34a; border-color:#bbf7d0;">
                                <i data-lucide="message-circle" style="width:14px; height:14px;"></i> Hubungi
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Grid: Services, Diagnostics & Checklist -->
<div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap:20px; margin-bottom:20px;">
    <!-- Service Items & Materials Card -->
    <div class="card" style="margin-bottom:0;">
        <div class="card-header">
            <div class="card-title">
                <i data-lucide="tag"></i>
                <span>Rincian Layanan & Material</span>
            </div>
        </div>
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Deskripsi</th>
                        <th>Qty</th>
                        <th>Harga (Rp)</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $grandTotal = 0;
                    foreach ($orderServices as $s): 
                        $grandTotal += (int)$s['subtotal'];
                    ?>
                        <tr>
                            <td><?= htmlspecialchars($s['service_name']) ?></td>
                            <td><?= (int)$s['quantity'] ?></td>
                            <td><?= format_rupiah($s['unit_price']) ?></td>
                            <td><strong><?= format_rupiah($s['subtotal']) ?></strong></td>
                        </tr>
                    <?php endforeach; ?>

                    <?php foreach ($orderMaterials as $m): 
                        $grandTotal += (int)$m['subtotal'];
                    ?>
                        <tr style="background:#eff6ff;">
                            <td>
                                <div><?= htmlspecialchars($m['item_name']) ?></div>
                                <span class="badge badge-blue" style="font-size:0.65rem;">Sparepart</span>
                            </td>
                            <td><?= $m['quantity'] ?> <?= htmlspecialchars($m['unit']) ?></td>
                            <td><?= format_rupiah($m['unit_price']) ?></td>
                            <td><strong><?= format_rupiah($m['subtotal']) ?></strong></td>
                        </tr>
                    <?php endforeach; ?>

                    <?php if (empty($orderServices) && empty($orderMaterials)): ?>
                        <tr><td colspan="4" style="text-align:center; color:var(--text-muted);">Belum ada item layanan.</td></tr>
                    <?php endif; ?>
                </tbody>
                <tfoot>
                    <tr style="background:var(--surface-alt);">
                        <td colspan="3" style="text-align:right; font-weight:700;">Total Biaya Servis:</td>
                        <td><strong style="color:var(--primary); font-size:1.1rem;"><?= format_rupiah($grandTotal) ?></strong></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <!-- Diagnostics & Signature Card -->
    <div class="card" style="margin-bottom:0;">
        <div class="card-header">
            <div class="card-title">
                <i data-lucide="activity"></i>
                <span>Hasil Diagnosis & Tanda Tangan Pelanggan</span>
            </div>
        </div>
        <div>
            <!-- Technical measurements -->
            <div style="display:grid; grid-template-columns:1fr 1fr 1fr 1fr; gap:8px; margin-bottom:14px;">
                <div style="background:var(--surface-alt); padding:8px; border-radius:var(--radius-sm); text-align:center;">
                    <div style="font-size:0.7rem; color:var(--text-muted);">Arus (Ampere)</div>
                    <div style="font-weight:800; font-size:1rem; color:var(--primary);"><?= $diagnostics['current_ampere'] ? number_format((float)$diagnostics['current_ampere'], 1) . ' A' : '-' ?></div>
                </div>
                <div style="background:var(--surface-alt); padding:8px; border-radius:var(--radius-sm); text-align:center;">
                    <div style="font-size:0.7rem; color:var(--text-muted);">Tegangan (Volt)</div>
                    <div style="font-weight:800; font-size:1rem; color:var(--primary);"><?= $diagnostics['voltage_volt'] ? number_format((float)$diagnostics['voltage_volt'], 0) . ' V' : '-' ?></div>
                </div>
                <div style="background:var(--surface-alt); padding:8px; border-radius:var(--radius-sm); text-align:center;">
                    <div style="font-size:0.7rem; color:var(--text-muted);">Tekanan Freon</div>
                    <div style="font-weight:800; font-size:1rem; color:var(--primary);"><?= $diagnostics['pressure_psi'] ? number_format((float)$diagnostics['pressure_psi'], 0) . ' PSI' : '-' ?></div>
                </div>
                <div style="background:var(--surface-alt); padding:8px; border-radius:var(--radius-sm); text-align:center;">
                    <div style="font-size:0.7rem; color:var(--text-muted);">Suhu Hembus</div>
                    <div style="font-weight:800; font-size:1rem; color:var(--primary);"><?= $diagnostics['temperature_celsius'] ? number_format((float)$diagnostics['temperature_celsius'], 1) . ' °C' : '-' ?></div>
                </div>
            </div>

            <?php if (!empty($diagnostics['diagnostic_result'])): ?>
                <div style="font-size:0.85rem; color:var(--text-main); background:var(--surface-alt); padding:10px; border-radius:var(--radius-sm); margin-bottom:14px;">
                    <strong>Hasil Diagnosis:</strong> <?= htmlspecialchars($diagnostics['diagnostic_result']) ?>
                </div>
            <?php endif; ?>

            <!-- Digital Signature Section -->
            <?php if ($signature): ?>
                <div style="border:1px dashed var(--border); border-radius:var(--radius-sm); padding:12px; background:var(--surface-alt); text-align:center;">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px;">
                        <span style="font-size:0.75rem; font-weight:700; color:var(--text-muted); text-transform:uppercase;">Tanda Tangan Pelanggan</span>
                        <span class="badge badge-amber">⭐ Rating: <?= (int)$signature['rating'] ?> / 5</span>
                    </div>
                    <div style="max-height:80px; overflow:hidden; margin:8px auto; background:#ffffff; border-radius:4px; padding:4px;">
                        <img src="<?= $signature['signature_data'] ?>" alt="Tanda Tangan" style="max-height:70px; max-width:100%;">
                    </div>
                    <div style="font-size:0.85rem; font-weight:700; color:var(--primary);"><?= htmlspecialchars($signature['signer_name']) ?></div>
                    <?php if (!empty($signature['customer_notes'])): ?>
                        <div style="font-size:0.8rem; color:var(--text-muted); font-style:italic; margin-top:4px;">"<?= htmlspecialchars($signature['customer_notes']) ?>"</div>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div style="padding:16px; border:1px dashed var(--border); border-radius:var(--radius-sm); text-align:center; color:var(--text-muted); font-size:0.85rem;">
                    Pelanggan belum menandatangani penyelesaian pekerjaan. Teknisi dapat meminta tanda tangan langsung melalui antarmuka mobile teknisi.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal: Assign Technician -->
<div id="assign-tech-modal" class="modal-overlay">
    <div class="modal-dialog">
        <div class="modal-header">
            <h3 class="modal-title">Tugaskan Teknisi ke Work Order</h3>
            <button type="button" onclick="closeModal('assign-tech-modal')" style="background:none;border:none;font-size:20px;cursor:pointer;">&times;</button>
        </div>
        <form method="POST" action="/work-orders/<?= $order['id'] ?>/assign">
            <?= csrf_field() ?>
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label" for="assign_technician_id">Pilih Teknisi *</label>
                    <select id="assign_technician_id" name="technician_id" class="form-select" required>
                        <?php foreach ($availableTechnicians as $at): ?>
                            <option value="<?= $at['id'] ?>"><?= htmlspecialchars($at['full_name']) ?> (<?= htmlspecialchars($at['code']) ?> - <?= htmlspecialchars($at['work_area']) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label style="display:flex; align-items:center; gap:8px; cursor:pointer;">
                        <input type="checkbox" name="is_lead" value="1" checked>
                        <span>Tetapkan sebagai Ketua Tim (Lead Technician)</span>
                    </label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('assign-tech-modal')">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Penugasan</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Cancel Work Order -->
<div id="cancel-wo-modal" class="modal-overlay">
    <div class="modal-dialog">
        <div class="modal-header">
            <h3 class="modal-title" style="color:var(--danger);">Batalkan Work Order</h3>
            <button type="button" onclick="closeModal('cancel-wo-modal')" style="background:none;border:none;font-size:20px;cursor:pointer;">&times;</button>
        </div>
        <form method="POST" action="/work-orders/<?= $order['id'] ?>/cancel">
            <?= csrf_field() ?>
            <div class="modal-body">
                <p style="font-size:0.9rem; color:var(--text-main); margin-bottom:12px;">
                    Apakah Anda yakin ingin membatalkan Work Order ini? Mohon tuliskan alasan pembatalan untuk arsip.
                </p>
                <div class="form-group">
                    <label class="form-label" for="cancellation_reason">Alasan Pembatalan *</label>
                    <textarea id="cancellation_reason" name="cancellation_reason" class="form-control" rows="3" required placeholder="Contoh: Pelanggan mendadak keluar kota / tidak bisa dihubungi..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('cancel-wo-modal')">Batal</button>
                <button type="submit" class="btn btn-danger">Ya, Batalkan Order</button>
            </div>
        </form>
    </div>
</div>

<?php require dirname(__DIR__, 2) . '/layout/footer.php'; ?>
