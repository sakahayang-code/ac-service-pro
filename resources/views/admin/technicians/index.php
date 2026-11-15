<?php require dirname(__DIR__, 2) . '/layout/header.php'; ?>

<div class="card">
    <div class="card-header">
        <div class="card-title">
            <i data-lucide="user-cog"></i>
            <span>Daftar Teknisi Lapangan</span>
        </div>
        <a href="/technicians/create" class="btn btn-primary btn-sm">
            <i data-lucide="plus-circle" style="width:16px; height:16px;"></i>
            <span>Tambah Teknisi</span>
        </a>
    </div>

    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Kode</th>
                    <th>Nama Teknisi</th>
                    <th>Kontak / HP</th>
                    <th>Wilayah Kerja</th>
                    <th>Keahlian & Sertifikasi</th>
                    <th>Rating</th>
                    <th>Total Servis Selesai</th>
                    <th>Komisi Standar</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($technicians)): ?>
                    <tr><td colspan="10" style="text-align:center; padding:24px; color:var(--text-muted);">Belum ada teknisi terdaftar.</td></tr>
                <?php else: ?>
                    <?php foreach ($technicians as $t): ?>
                        <tr>
                            <td><strong style="color:var(--primary);"><?= htmlspecialchars($t['code']) ?></strong></td>
                            <td>
                                <a href="/technicians/<?= $t['id'] ?>" style="font-weight:700; color:var(--secondary);">
                                    <?= htmlspecialchars($t['full_name']) ?>
                                </a>
                                <div style="font-size:0.75rem; color:var(--text-muted);"><?= htmlspecialchars($t['user_email']) ?></div>
                            </td>
                            <td>
                                <div><?= htmlspecialchars($t['phone']) ?></div>
                                <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $t['phone']) ?>" target="_blank" style="font-size:0.75rem; color:#16a34a; font-weight:600;">
                                    WhatsApp
                                </a>
                            </td>
                            <td><?= htmlspecialchars($t['work_area']) ?></td>
                            <td>
                                <div style="max-width:220px; font-size:0.8rem; color:var(--text-main);">
                                    <?= htmlspecialchars($t['skills'] ?? '-') ?>
                                </div>
                                <?php if (!empty($t['certifications'])): ?>
                                    <div style="font-size:0.7rem; color:var(--text-muted); margin-top:2px;">📜 <?= htmlspecialchars($t['certifications']) ?></div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge badge-amber" style="font-size:0.8rem;">
                                    ⭐ <?= number_format((float)$t['rating'], 1) ?>
                                </span>
                            </td>
                            <td><strong><?= (int)$t['total_jobs_done'] ?> Servis</strong></td>
                            <td><?= number_format((float)$t['commission_rate_percent'], 1) ?>%</td>
                            <td>
                                <span class="badge <?= $t['status'] === 'active' ? 'badge-green' : 'badge-red' ?>">
                                    <?= $t['status'] === 'active' ? 'Aktif' : 'Nonaktif' ?>
                                </span>
                            </td>
                            <td>
                                <a href="/technicians/<?= $t['id'] ?>" class="btn-action btn-action-view">
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

<?php require dirname(__DIR__, 2) . '/layout/footer.php'; ?>
