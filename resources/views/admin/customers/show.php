<?php require dirname(__DIR__, 2) . '/layout/header.php'; ?>

<!-- Customer Header Card -->
<div class="card" style="margin-bottom:20px;">
    <div style="display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:16px;">
        <div>
            <div style="display:flex; align-items:center; gap:10px; margin-bottom:6px;">
                <span class="badge badge-blue"><?= htmlspecialchars($customer['customer_code']) ?></span>
                <span class="badge badge-gray"><?= htmlspecialchars($customer['category']) ?></span>
                <span class="badge <?= $customer['status'] === 'active' ? 'badge-green' : 'badge-red' ?>">
                    <?= $customer['status'] === 'active' ? 'Aktif' : 'Nonaktif' ?>
                </span>
            </div>
            <h2 style="font-size:1.6rem; color:var(--primary); font-weight:800;"><?= htmlspecialchars($customer['name']) ?></h2>
            <div style="color:var(--text-muted); font-size:0.9rem; margin-top:4px; display:flex; align-items:center; gap:16px; flex-wrap:wrap;">
                <span><i data-lucide="phone" style="width:14px; height:14px; display:inline-block; vertical-align:middle;"></i> <?= htmlspecialchars($customer['phone']) ?></span>
                <?php if (!empty($customer['email'])): ?>
                    <span><i data-lucide="mail" style="width:14px; height:14px; display:inline-block; vertical-align:middle;"></i> <?= htmlspecialchars($customer['email']) ?></span>
                <?php endif; ?>
                <span><i data-lucide="calendar" style="width:14px; height:14px; display:inline-block; vertical-align:middle;"></i> Terdaftar sejak: <?= format_date($customer['created_at']) ?></span>
            </div>
            <div style="margin-top:8px; font-size:0.9rem; color:var(--text-main); display:flex; align-items:flex-start; gap:6px;">
                <i data-lucide="map-pin" style="width:16px; height:16px; color:var(--danger); flex-shrink:0; margin-top:3px;"></i>
                <span><?= htmlspecialchars($customer['address']) ?></span>
            </div>
            <?php if (!empty($customer['notes'])): ?>
                <div style="margin-top:6px; font-size:0.85rem; color:var(--text-muted); background:var(--surface-alt); padding:6px 12px; border-radius:var(--radius-sm); display:inline-block;">
                    <strong>Catatan:</strong> <?= htmlspecialchars($customer['notes']) ?>
                </div>
            <?php endif; ?>
        </div>

        <div style="display:flex; gap:8px; flex-wrap:wrap;">
            <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $customer['phone']) ?>" target="_blank" class="btn btn-success btn-sm">
                <i data-lucide="message-circle" style="width:16px; height:16px;"></i> WhatsApp
            </a>
            <a href="/work-orders/create?customer_id=<?= $customer['id'] ?>" class="btn btn-accent btn-sm">
                <i data-lucide="plus-circle" style="width:16px; height:16px;"></i> Order Servis
            </a>
            <a href="/ac-units/create?customer_id=<?= $customer['id'] ?>" class="btn btn-secondary btn-sm">
                <i data-lucide="snowflake" style="width:16px; height:16px;"></i> Tambah Unit AC
            </a>
            <a href="/customers/<?= $customer['id'] ?>/edit" class="btn btn-outline btn-sm">
                <i data-lucide="edit-2" style="width:16px; height:16px;"></i> Ubah
            </a>
        </div>
    </div>
</div>

<!-- Grid: AC Units & Service History -->
<div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap:20px; margin-bottom:20px;">
    <!-- Registered AC Units Card -->
    <div class="card" style="margin-bottom:0;">
        <div class="card-header">
            <div class="card-title">
                <i data-lucide="snowflake"></i>
                <span>Unit AC Terdaftar (<?= count($units) ?>)</span>
            </div>
            <a href="/ac-units/create?customer_id=<?= $customer['id'] ?>" class="btn btn-outline btn-sm">+ Unit AC</a>
        </div>
        <div style="display:flex; flex-direction:column; gap:12px;">
            <?php if (empty($units)): ?>
                <div style="text-align:center; padding:20px; color:var(--text-muted); font-size:0.85rem;">
                    Belum ada unit AC yang didaftarkan untuk pelanggan ini.
                </div>
            <?php else: ?>
                <?php foreach ($units as $u): ?>
                    <div style="padding:12px 14px; background:var(--surface-alt); border:1px solid var(--border); border-radius:var(--radius-sm); display:flex; justify-content:space-between; align-items:center;">
                        <div>
                            <div style="display:flex; align-items:center; gap:8px;">
                                <strong style="color:var(--primary); font-size:0.95rem;"><?= htmlspecialchars($u['brand']) ?></strong>
                                <span class="badge badge-blue" style="font-size:0.7rem;"><?= htmlspecialchars($u['capacity_pk']) ?></span>
                                <span class="badge badge-gray" style="font-size:0.7rem;"><?= htmlspecialchars($u['refrigerant_type']) ?></span>
                            </div>
                            <div style="font-size:0.8rem; color:var(--text-muted); margin-top:2px;">
                                Lokasi: <strong><?= htmlspecialchars($u['room_location']) ?></strong> &bull; Tipe: <?= htmlspecialchars($u['model_type']) ?>
                            </div>
                            <div style="font-size:0.75rem; color:var(--text-muted); margin-top:4px;">
                                Servis Berikutnya: <strong style="color:var(--secondary);"><?= format_date($u['next_service_due']) ?></strong>
                            </div>
                        </div>
                        <div>
                            <a href="/ac-units/<?= $u['id'] ?>" class="btn btn-outline btn-sm">Lihat</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Invoices & Payment Summary -->
    <div class="card" style="margin-bottom:0;">
        <div class="card-header">
            <div class="card-title">
                <i data-lucide="receipt"></i>
                <span>Faktur & Pembayaran</span>
            </div>
        </div>
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>No. Faktur</th>
                        <th>Tanggal</th>
                        <th>Total (Rp)</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($invoices)): ?>
                        <tr><td colspan="4" style="text-align:center; color:var(--text-muted); padding:16px;">Belum ada invoice.</td></tr>
                    <?php else: ?>
                        <?php foreach ($invoices as $inv): ?>
                            <tr>
                                <td><a href="/invoices/<?= $inv['id'] ?>" style="font-weight:700; color:var(--secondary);"><?= htmlspecialchars($inv['invoice_number']) ?></a></td>
                                <td><?= format_date($inv['issue_date']) ?></td>
                                <td><strong><?= format_rupiah($inv['total_amount']) ?></strong></td>
                                <td>
                                    <span class="badge <?= $inv['payment_status'] === 'paid' ? 'badge-green' : 'badge-amber' ?>">
                                        <?= $inv['payment_status'] === 'paid' ? 'LUNAS' : 'BELUM LUNAS' ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Work Orders History -->
<div class="card">
    <div class="card-header">
        <div class="card-title">
            <i data-lucide="clipboard-list"></i>
            <span>Riwayat Work Order Servis</span>
        </div>
        <a href="/work-orders/create?customer_id=<?= $customer['id'] ?>" class="btn btn-primary btn-sm">+ Work Order Baru</a>
    </div>
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>No. Order</th>
                    <th>Unit AC</th>
                    <th>Keluhan / Layanan</th>
                    <th>Jadwal</th>
                    <th>Status</th>
                    <th>Biaya Akhir</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($orders)): ?>
                    <tr><td colspan="7" style="text-align:center; padding:20px; color:var(--text-muted);">Belum ada riwayat servis untuk pelanggan ini.</td></tr>
                <?php else: ?>
                    <?php foreach ($orders as $wo): ?>
                        <tr>
                            <td><strong style="color:var(--primary);"><?= htmlspecialchars($wo['order_number']) ?></strong></td>
                            <td><?= htmlspecialchars($wo['brand'] ?? 'Semua Unit') ?> (<?= htmlspecialchars($wo['capacity_pk'] ?? '-') ?>)</td>
                            <td>
                                <div style="max-width:260px; font-size:0.85rem; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                                    <?= htmlspecialchars($wo['complaints']) ?>
                                </div>
                            </td>
                            <td><?= format_date($wo['scheduled_date']) ?> (<?= htmlspecialchars($wo['scheduled_time']) ?>)</td>
                            <td>
                                <span class="badge badge-<?= $wo['status'] ?>"><?= str_replace('_', ' ', $wo['status']) ?></span>
                            </td>
                            <td><strong><?= format_rupiah($wo['final_price'] ?: $wo['estimated_price']) ?></strong></td>
                            <td>
                                <a href="/work-orders/<?= $wo['id'] ?>" class="btn btn-outline btn-sm">Detail</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require dirname(__DIR__, 2) . '/layout/footer.php'; ?>
