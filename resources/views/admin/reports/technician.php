<?php
view('layout.header', ['title' => $title]);
view('layout.sidebar');
?>

<div class="content-wrapper">
    <div class="page-header d-flex justify-content-between align-items-center mb-4">
        <div>
            <div class="text-muted small">
                <a href="<?= url('reports') ?>" class="text-decoration-none">Laporan</a> / Teknisi
            </div>
            <h1 class="h3 font-weight-bold text-navy mb-0">Laporan Produktivitas & Komisi Teknisi</h1>
            <p class="text-muted small mb-0">Evaluasi performa penyelesaian pekerjaan, kepuasan pelanggan, dan rekapitulasi komisi teknisi</p>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= url('reports/technician?export=csv') ?>" class="btn btn-success">
                <i data-lucide="download" class="icon-sm me-1"></i> Ekspor Excel (CSV)
            </a>
            <button onclick="window.print()" class="btn btn-outline-secondary">
                <i data-lucide="printer" class="icon-sm me-1"></i> Cetak Laporan
            </button>
        </div>
    </div>

    <!-- Leaderboard Table -->
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Peringkat</th>
                        <th>Kode & Nama Teknisi</th>
                        <th>Wilayah Kerja</th>
                        <th class="text-center">Rating Kepuasan</th>
                        <th class="text-center">Total Ditugaskan</th>
                        <th class="text-center">Berhasil Selesai</th>
                        <th class="text-center">Tingkat Sukses (%)</th>
                        <th class="text-end">Total Komisi Teknisi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($technicians)): ?>
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">Belum ada data teknisi tercatat.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($technicians as $rank => $t): 
                            $rate = $t['total_assigned'] > 0 ? round(($t['total_completed'] / $t['total_assigned']) * 100, 1) : 0;
                        ?>
                            <tr>
                                <td class="fw-bold fs-5 text-center text-navy">
                                    <?php if ($rank === 0): ?>
                                        🥇 1
                                    <?php elseif ($rank === 1): ?>
                                        🥈 2
                                    <?php elseif ($rank === 2): ?>
                                        🥉 3
                                    <?php else: ?>
                                        <?= $rank + 1 ?>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="fw-bold text-navy"><?= htmlspecialchars($t['full_name']) ?></div>
                                    <small class="text-muted"><?= htmlspecialchars($t['code']) ?> | <?= htmlspecialchars($t['phone']) ?></small>
                                </td>
                                <td><?= htmlspecialchars($t['work_area'] ?? 'Jabodetabek') ?></td>
                                <td class="text-center">
                                    <span class="badge bg-warning-subtle text-dark fs-6">
                                        ⭐ <?= number_format((float)$t['rating'], 1) ?>
                                    </span>
                                </td>
                                <td class="text-center fw-semibold"><?= (int)$t['total_assigned'] ?> SPK</td>
                                <td class="text-center fw-bold text-success"><?= (int)$t['total_completed'] ?> SPK</td>
                                <td class="text-center">
                                    <div class="d-flex align-items-center justify-content-center gap-2">
                                        <div class="progress flex-grow-1" style="height: 6px; max-width: 80px;">
                                            <div class="progress-bar bg-success" style="width: <?= min(100, $rate) ?>%;"></div>
                                        </div>
                                        <span class="small fw-semibold"><?= $rate ?>%</span>
                                    </div>
                                </td>
                                <td class="text-end fw-bold text-primary"><?= format_rupiah($t['total_commissions']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php view('layout.footer'); ?>
