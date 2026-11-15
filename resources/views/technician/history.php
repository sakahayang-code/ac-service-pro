<?php require dirname(__DIR__) . '/layout/header.php'; ?>

<div class="card">
    <div class="card-header">
        <div class="card-title">
            <i data-lucide="history"></i>
            <span>Riwayat Pekerjaan Selesai</span>
        </div>
        <a href="/technician/tasks" class="btn btn-outline btn-sm">Tugas Aktif</a>
    </div>

    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>No. WO</th>
                    <th>Pelanggan</th>
                    <th>Unit AC</th>
                    <th>Tanggal Selesai</th>
                    <th>Status</th>
                    <th>Komisi Teknisi</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($history)): ?>
                    <tr><td colspan="7" style="text-align:center; padding:24px; color:var(--text-muted);">Belum ada riwayat pekerjaan yang selesai.</td></tr>
                <?php else: ?>
                    <?php foreach ($history as $h): ?>
                        <tr>
                            <td><strong style="color:var(--primary);"><?= htmlspecialchars($h['order_number']) ?></strong></td>
                            <td>
                                <div><strong><?= htmlspecialchars($h['customer_name']) ?></strong></div>
                                <div style="font-size:0.75rem; color:var(--text-muted);"><?= htmlspecialchars($h['customer_phone']) ?></div>
                            </td>
                            <td><?= htmlspecialchars($h['ac_brand'] ?? 'AC') ?> (<?= htmlspecialchars($h['capacity_pk'] ?? '-') ?>)</td>
                            <td><?= format_datetime($h['checkout_at'] ?: $h['scheduled_date']) ?></td>
                            <td><span class="badge badge-<?= $h['status'] ?>"><?= str_replace('_', ' ', $h['status']) ?></span></td>
                            <td><strong style="color:var(--success);"><?= format_rupiah($h['commission_amount']) ?></strong></td>
                            <td>
                                <a href="/technician/tasks/<?= $h['id'] ?>" class="btn-action btn-action-view">
                                    <i data-lucide="eye"></i>
                                    <span>Detail</span>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require dirname(__DIR__) . '/layout/footer.php'; ?>
