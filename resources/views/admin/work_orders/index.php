<?php require dirname(__DIR__, 2) . '/layout/header.php'; ?>

<div class="card">
    <div class="card-header">
        <div class="card-title">
            <i data-lucide="clipboard-check"></i>
            <span>Daftar Work Order (Perintah Kerja)</span>
        </div>
        <a href="/work-orders/create" class="btn btn-primary btn-sm">
            <i data-lucide="plus-circle" style="width:16px; height:16px;"></i>
            <span>Work Order Baru</span>
        </a>
    </div>

    <!-- Status Tabs Nav -->
    <div style="display:flex; gap:8px; margin-bottom:16px; overflow-x:auto; padding-bottom:6px;">
        <a href="/work-orders" class="badge <?= $currentStatus === '' ? 'badge-blue' : 'badge-gray' ?>" style="padding:8px 12px; font-size:0.75rem; text-decoration:none;">
            Semua (<?= count($orders) ?>)
        </a>
        <?php 
        $statuses = [
            'BARU' => 'Baru',
            'DIKONFIRMASI' => 'Dikonfirmasi',
            'DIJADWALKAN' => 'Terjadwal',
            'DALAM_PERJALANAN' => 'Di Jalan',
            'SEDANG_DIKERJAKAN' => 'Sedang Kerja',
            'MENUNGGU_SPAREPART' => 'Tunggu Part',
            'SELESAI' => 'Selesai',
            'DITAGIHKAN' => 'Ditagihkan',
            'LUNAS' => 'Lunas',
            'DITUNDA' => 'Tertunda',
            'DIBATALKAN' => 'Dibatalkan'
        ];
        foreach ($statuses as $stKey => $stLabel): 
            $cnt = $statusCounts[$stKey] ?? 0;
        ?>
            <a href="/work-orders?status=<?= $stKey ?>" class="badge <?= $currentStatus === $stKey ? 'badge-blue' : 'badge-gray' ?>" style="padding:8px 12px; font-size:0.75rem; text-decoration:none;">
                <?= $stLabel ?> <?php if ($cnt > 0): ?>(<?= $cnt ?>)<?php endif; ?>
            </a>
        <?php endforeach; ?>
    </div>

    <!-- Filter Bar -->
    <form method="GET" action="/work-orders" style="display:flex; gap:12px; margin-bottom:20px; flex-wrap:wrap;">
        <?php if ($currentStatus !== ''): ?>
            <input type="hidden" name="status" value="<?= htmlspecialchars($currentStatus) ?>">
        <?php endif; ?>
        <div style="flex:1; min-width:240px;">
            <input type="text" name="q" class="form-control" placeholder="Cari No. WO, nama pelanggan, keluhan..." value="<?= htmlspecialchars($search) ?>">
        </div>
        <div style="width:160px;">
            <input type="date" name="date" class="form-control" value="<?= htmlspecialchars($date) ?>" onchange="this.form.submit()">
        </div>
        <button type="submit" class="btn btn-secondary btn-sm">Filter</button>
        <?php if ($search !== '' || $date !== '' || $currentStatus !== ''): ?>
            <a href="/work-orders" class="btn btn-outline btn-sm">Reset</a>
        <?php endif; ?>
    </form>

    <!-- Work Orders Table -->
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>No. Order</th>
                    <th>Pelanggan</th>
                    <th>Jadwal Kunjungan</th>
                    <th>Unit AC</th>
                    <th>Keluhan & Masalah</th>
                    <th>Teknisi Utama</th>
                    <th>Prioritas</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($orders)): ?>
                    <tr><td colspan="9" style="text-align:center; padding:28px; color:var(--text-muted);">Tidak ada work order yang sesuai filter.</td></tr>
                <?php else: ?>
                    <?php foreach ($orders as $wo): ?>
                        <tr>
                            <td>
                                <a href="/work-orders/<?= $wo['id'] ?>" style="font-weight:700; color:var(--primary);">
                                    <?= htmlspecialchars($wo['order_number']) ?>
                                </a>
                                <div style="font-size:0.7rem; color:var(--text-muted);">Sumber: <?= htmlspecialchars($wo['order_source']) ?></div>
                            </td>
                            <td>
                                <a href="/customers/<?= $wo['customer_id'] ?>" style="font-weight:600; color:var(--secondary);">
                                    <?= htmlspecialchars($wo['customer_name']) ?>
                                </a>
                                <div style="font-size:0.75rem; color:var(--text-muted);"><?= htmlspecialchars($wo['customer_phone']) ?></div>
                            </td>
                            <td>
                                <div><strong><?= format_date($wo['scheduled_date']) ?></strong></div>
                                <div style="font-size:0.75rem; color:var(--text-muted);">Jam: <?= htmlspecialchars($wo['scheduled_time']) ?> WIB</div>
                            </td>
                            <td>
                                <?php if (!empty($wo['ac_brand'])): ?>
                                    <div><?= htmlspecialchars($wo['ac_brand']) ?> (<?= htmlspecialchars($wo['capacity_pk']) ?>)</div>
                                    <div style="font-size:0.75rem; color:var(--text-muted);"><?= htmlspecialchars($wo['ac_model'] ?? 'Split') ?></div>
                                <?php else: ?>
                                    <span style="color:var(--text-muted); font-size:0.8rem;">Unit Baru / Seluruh Unit</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div style="max-width:220px; font-size:0.85rem; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                                    <?= htmlspecialchars($wo['complaints']) ?>
                                </div>
                            </td>
                            <td>
                                <?php if (!empty($wo['lead_technician_name'])): ?>
                                    <span class="badge badge-purple"><?= htmlspecialchars($wo['lead_technician_name']) ?></span>
                                <?php else: ?>
                                    <span class="badge badge-gray">Belum Ditugaskan</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge <?= $wo['priority'] === 'emergency' ? 'badge-red' : ($wo['priority'] === 'high' ? 'badge-orange' : 'badge-gray') ?>">
                                    <?= strtoupper($wo['priority']) ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge badge-<?= $wo['status'] ?>"><?= str_replace('_', ' ', $wo['status']) ?></span>
                            </td>
                            <td>
                                <div style="display:flex; gap:6px; flex-wrap:wrap;">
                                    <a href="/work-orders/<?= $wo['id'] ?>" class="btn-action btn-action-view" title="Buka Detail">
                                        <i data-lucide="eye"></i>
                                        <span>Detail</span>
                                    </a>
                                    <a href="/work-orders/<?= $wo['id'] ?>/spk" target="_blank" class="btn-action btn-action-print" title="Cetak SPK">
                                        <i data-lucide="printer"></i>
                                        <span>SPK</span>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require dirname(__DIR__, 2) . '/layout/footer.php'; ?>
