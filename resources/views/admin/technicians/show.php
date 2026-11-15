<?php require dirname(__DIR__, 2) . '/layout/header.php'; ?>

<!-- Technician Profile Header -->
<div class="card">
    <div style="display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:16px;">
        <div style="display:flex; gap:16px; align-items:center;">
            <div style="width:64px; height:64px; border-radius:50%; background:var(--secondary); color:#ffffff; display:flex; align-items:center; justify-content:center; font-size:1.5rem; font-weight:800;">
                <?= strtoupper(substr($technician['full_name'], 0, 1)) ?>
            </div>
            <div>
                <div style="display:flex; align-items:center; gap:8px;">
                    <span class="badge badge-blue"><?= htmlspecialchars($technician['code']) ?></span>
                    <span class="badge badge-amber">⭐ <?= number_format((float)$technician['rating'], 1) ?></span>
                    <span class="badge badge-green"><?= htmlspecialchars($technician['status']) ?></span>
                </div>
                <h2 style="font-size:1.4rem; color:var(--primary); font-weight:800; margin-top:2px;">
                    <?= htmlspecialchars($technician['full_name']) ?>
                </h2>
                <div style="font-size:0.85rem; color:var(--text-muted); margin-top:2px;">
                    <span><i data-lucide="phone" style="width:13px; height:13px; display:inline-block; vertical-align:middle;"></i> <?= htmlspecialchars($technician['phone']) ?></span> &bull;
                    <span><i data-lucide="mail" style="width:13px; height:13px; display:inline-block; vertical-align:middle;"></i> <?= htmlspecialchars($technician['user_email']) ?></span> &bull;
                    <span><i data-lucide="map-pin" style="width:13px; height:13px; display:inline-block; vertical-align:middle;"></i> <?= htmlspecialchars($technician['work_area']) ?></span>
                </div>
            </div>
        </div>

        <div style="display:flex; gap:10px;">
            <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $technician['phone']) ?>" target="_blank" class="btn btn-success btn-sm">
                <i data-lucide="message-circle" style="width:16px; height:16px;"></i> WhatsApp
            </a>
            <a href="/technicians" class="btn btn-outline btn-sm">Kembali</a>
        </div>
    </div>

    <!-- Quick Stats -->
    <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap:12px; margin-top:20px; padding-top:16px; border-top:1px solid var(--border);">
        <div style="background:var(--surface-alt); padding:12px; border-radius:var(--radius-sm);">
            <div style="font-size:0.75rem; color:var(--text-muted);">Total Pekerjaan Selesai</div>
            <div style="font-size:1.25rem; font-weight:800; color:var(--primary);"><?= (int)$technician['total_jobs_done'] ?> Servis</div>
        </div>
        <div style="background:var(--surface-alt); padding:12px; border-radius:var(--radius-sm);">
            <div style="font-size:0.75rem; color:var(--text-muted);">Tingkat Kepuasan Rating</div>
            <div style="font-size:1.25rem; font-weight:800; color:var(--warning);"><?= number_format((float)$technician['rating'], 1) ?> / 5.0</div>
        </div>
        <div style="background:var(--surface-alt); padding:12px; border-radius:var(--radius-sm);">
            <div style="font-size:0.75rem; color:var(--text-muted);">Persentase Komisi</div>
            <div style="font-size:1.25rem; font-weight:800; color:var(--success);"><?= number_format((float)$technician['commission_rate_percent'], 1) ?>%</div>
        </div>
        <div style="background:var(--surface-alt); padding:12px; border-radius:var(--radius-sm);">
            <div style="font-size:0.75rem; color:var(--text-muted);">Sertifikasi</div>
            <div style="font-size:0.85rem; font-weight:700; color:var(--text-main);"><?= htmlspecialchars($technician['certifications'] ?? 'Pelatihan Internal') ?></div>
        </div>
    </div>
</div>

<!-- Grid: Assigned Work Orders & Customer Reviews -->
<div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap:20px;">
    <!-- Work Orders -->
    <div class="card" style="margin-bottom:0;">
        <div class="card-header">
            <div class="card-title">
                <i data-lucide="clipboard-list"></i>
                <span>Riwayat Penugasan Servis</span>
            </div>
        </div>
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>No. WO</th>
                        <th>Pelanggan</th>
                        <th>Jadwal</th>
                        <th>Status</th>
                        <th>Komisi (Rp)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($orders)): ?>
                        <tr><td colspan="5" style="text-align:center; padding:20px; color:var(--text-muted);">Belum ada penugasan kerja.</td></tr>
                    <?php else: ?>
                        <?php foreach ($orders as $wo): ?>
                            <tr>
                                <td><a href="/work-orders/<?= $wo['id'] ?>" style="font-weight:700; color:var(--secondary);"><?= htmlspecialchars($wo['order_number']) ?></a></td>
                                <td><?= htmlspecialchars($wo['customer_name']) ?></td>
                                <td><?= format_date($wo['scheduled_date']) ?></td>
                                <td><span class="badge badge-<?= $wo['status'] ?>"><?= str_replace('_', ' ', $wo['status']) ?></span></td>
                                <td><strong><?= format_rupiah($wo['commission_amount']) ?></strong></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Customer Reviews -->
    <div class="card" style="margin-bottom:0;">
        <div class="card-header">
            <div class="card-title">
                <i data-lucide="star"></i>
                <span>Ulasan dari Pelanggan</span>
            </div>
        </div>
        <div style="display:flex; flex-direction:column; gap:12px;">
            <?php if (empty($reviews)): ?>
                <div style="text-align:center; padding:24px; color:var(--text-muted); font-size:0.85rem;">
                    Belum ada ulasan yang masuk untuk teknisi ini.
                </div>
            <?php else: ?>
                <?php foreach ($reviews as $rev): ?>
                    <div style="padding:12px; background:var(--surface-alt); border-radius:var(--radius-sm); border:1px solid var(--border);">
                        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:4px;">
                            <strong style="color:var(--primary); font-size:0.9rem;"><?= htmlspecialchars($rev['customer_name']) ?></strong>
                            <span class="badge badge-amber">⭐ <?= (int)$rev['rating'] ?> / 5</span>
                        </div>
                        <div style="font-size:0.85rem; color:var(--text-main); font-style:italic;">
                            "<?= htmlspecialchars($rev['review_text'] ?? 'Pelayanan sangat baik dan memuaskan.') ?>"
                        </div>
                        <div style="font-size:0.75rem; color:var(--text-muted); margin-top:6px;">
                            Order: <?= htmlspecialchars($rev['order_number']) ?> &bull; <?= format_date($rev['created_at']) ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require dirname(__DIR__, 2) . '/layout/footer.php'; ?>
