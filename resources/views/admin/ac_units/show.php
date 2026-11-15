<?php require dirname(__DIR__, 2) . '/layout/header.php'; ?>

<!-- Unit AC Header Card -->
<div class="card">
    <div style="display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:16px;">
        <div>
            <div style="display:flex; align-items:center; gap:8px; margin-bottom:6px;">
                <span class="badge badge-blue"><?= htmlspecialchars($unit['unit_code']) ?></span>
                <span class="badge badge-purple"><?= htmlspecialchars($unit['capacity_pk']) ?></span>
                <span class="badge badge-gray"><?= htmlspecialchars($unit['refrigerant_type']) ?></span>
                <span class="badge <?= str_contains(strtolower($unit['condition']), 'normal') ? 'badge-green' : 'badge-amber' ?>">
                    <?= htmlspecialchars($unit['condition']) ?>
                </span>
            </div>
            <h2 style="font-size:1.5rem; color:var(--primary); font-weight:800;">
                <?= htmlspecialchars($unit['brand']) ?> &bull; <?= htmlspecialchars($unit['model_type']) ?>
            </h2>
            <div style="color:var(--text-muted); font-size:0.9rem; margin-top:4px;">
                Pemilik: <a href="/customers/<?= $unit['customer_id'] ?>" style="font-weight:700; color:var(--secondary);"><?= htmlspecialchars($unit['customer_name']) ?></a>
                (<?= htmlspecialchars($unit['customer_code']) ?> &bull; <?= htmlspecialchars($unit['customer_phone']) ?>)
            </div>
            <div style="margin-top:6px; font-size:0.85rem; color:var(--text-main);">
                <strong>Lokasi Ruangan:</strong> <?= htmlspecialchars($unit['room_location']) ?> &bull;
                <strong>Alamat:</strong> <?= htmlspecialchars($unit['customer_address']) ?>
            </div>
            <?php if (!empty($unit['serial_number'])): ?>
                <div style="margin-top:4px; font-size:0.8rem; color:var(--text-muted);">
                    No. Seri: <code><?= htmlspecialchars($unit['serial_number']) ?></code> &bull;
                    Garansi: <?= htmlspecialchars($unit['warranty_status'] ?? 'Habis') ?>
                </div>
            <?php endif; ?>
        </div>

        <div style="display:flex; gap:8px; flex-wrap:wrap;">
            <a href="/work-orders/create?customer_id=<?= $unit['customer_id'] ?>&ac_unit_id=<?= $unit['id'] ?>" class="btn btn-accent btn-sm">
                <i data-lucide="plus-circle" style="width:16px; height:16px;"></i> Order Servis Unit Ini
            </a>
            <a href="/ac-units/<?= $unit['id'] ?>/edit" class="btn btn-outline btn-sm">
                <i data-lucide="edit-2" style="width:16px; height:16px;"></i> Ubah Unit
            </a>
            <a href="/customers/<?= $unit['customer_id'] ?>" class="btn btn-outline btn-sm">
                <i data-lucide="user" style="width:16px; height:16px;"></i> Profil Pelanggan
            </a>
        </div>
    </div>
</div>

<!-- Service History Linked to Work Orders -->
<div class="card">
    <div class="card-header">
        <div class="card-title">
            <i data-lucide="history"></i>
            <span>Riwayat Servis & Perbaikan Unit AC Ini</span>
        </div>
        <span style="font-size:0.8rem; color:var(--text-muted);"><?= count($serviceHistory) ?> Catatan Servis</span>
    </div>

    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>No. Work Order</th>
                    <th>Tanggal Pengerjaan</th>
                    <th>Keluhan / Pekerjaan</th>
                    <th>Teknisi Bertugas</th>
                    <th>Status</th>
                    <th>Total Biaya</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($serviceHistory)): ?>
                    <tr>
                        <td colspan="7" style="text-align:center; padding:28px; color:var(--text-muted);">
                            Belum ada riwayat servis untuk unit AC ini. Klik "Order Servis Unit Ini" di atas untuk membuat jadwal pekerjaan baru.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($serviceHistory as $hist): ?>
                        <tr>
                            <td>
                                <a href="/work-orders/<?= $hist['id'] ?>" style="font-weight:700; color:var(--secondary);">
                                    <?= htmlspecialchars($hist['order_number']) ?>
                                </a>
                            </td>
                            <td><?= format_date($hist['scheduled_date']) ?></td>
                            <td>
                                <div style="max-width:280px; font-size:0.85rem; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                                    <?= htmlspecialchars($hist['complaints']) ?>
                                </div>
                            </td>
                            <td>
                                <div><?= htmlspecialchars($hist['technician_name'] ?? 'Belum Ditugaskan') ?></div>
                            </td>
                            <td>
                                <span class="badge badge-<?= $hist['status'] ?>"><?= str_replace('_', ' ', $hist['status']) ?></span>
                            </td>
                            <td>
                                <strong><?= format_rupiah($hist['final_price'] ?: $hist['estimated_price']) ?></strong>
                            </td>
                            <td>
                                <a href="/work-orders/<?= $hist['id'] ?>" class="btn btn-outline btn-sm">Detail</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require dirname(__DIR__, 2) . '/layout/footer.php'; ?>
