<?php require dirname(__DIR__) . '/layout/header.php'; ?>

<!-- Welcome & Greeting Card -->
<div class="card" style="background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%); color:#ffffff; padding:24px; position:relative; overflow:hidden;">
    <div style="position:relative; z-index:2; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:16px;">
        <div>
            <div style="font-size:0.85rem; color:var(--accent); font-weight:700; text-transform:uppercase; letter-spacing:0.05em; margin-bottom:4px;">
                Selamat Datang di Sistem Operasional
            </div>
            <h2 style="color:#ffffff; font-size:1.5rem; font-weight:800;"><?= htmlspecialchars($user['name']) ?></h2>
            <p style="color:#cbd5e1; font-size:0.9rem; margin-top:2px;">
                Peran: <strong><?= htmlspecialchars($user['role_display_name']) ?></strong> &bull; <?= date('l, d F Y') ?> (WIB)
            </p>
        </div>
        <div style="display:flex; gap:10px; flex-wrap:wrap;">
            <a href="/work-orders/create" class="btn btn-accent" style="box-shadow: 0 4px 12px rgba(6,182,212,0.4);">
                <i data-lucide="plus-circle" style="width:18px; height:18px;"></i>
                <span>Work Order Baru</span>
            </a>
            <a href="/customers/create" class="btn btn-outline" style="background:rgba(255,255,255,0.15); color:#ffffff; border-color:rgba(255,255,255,0.3);">
                <i data-lucide="user-plus" style="width:18px; height:18px;"></i>
                <span>Tambah Pelanggan</span>
            </a>
        </div>
    </div>
</div>

<!-- Primary KPI Stat Cards -->
<div class="stat-grid">
    <div class="stat-card">
        <div class="stat-icon primary">
            <i data-lucide="calendar-check" style="width:24px; height:24px;"></i>
        </div>
        <div>
            <div class="stat-value"><?= number_format($woToday) ?></div>
            <div class="stat-label">Pekerjaan Hari Ini</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon warning">
            <i data-lucide="wrench" style="width:24px; height:24px;"></i>
        </div>
        <div>
            <div class="stat-value"><?= number_format($woInProgress) ?></div>
            <div class="stat-label">Sedang Dikerjakan</div>
        </div>
    </div>

    <?php if (in_array($role, ['owner', 'cashier', 'admin'])): ?>
    <div class="stat-card">
        <div class="stat-icon success">
            <i data-lucide="trending-up" style="width:24px; height:24px;"></i>
        </div>
        <div>
            <div class="stat-value" style="font-size:1.25rem;"><?= format_rupiah($incomeMonth) ?></div>
            <div class="stat-label">Pendapatan Bulan Ini</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon danger">
            <i data-lucide="clock" style="width:24px; height:24px;"></i>
        </div>
        <div>
            <div class="stat-value" style="font-size:1.25rem;"><?= format_rupiah($totalReceivables) ?></div>
            <div class="stat-label">Total Piutang Berjalan</div>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Operational Status Summary Pills -->
<div class="card" style="padding:16px 20px;">
    <div style="font-size:0.85rem; font-weight:700; color:var(--text-muted); text-transform:uppercase; margin-bottom:12px;">
        Status Operasional Work Order
    </div>
    <div style="display:flex; flex-wrap:wrap; gap:10px;">
        <a href="/work-orders?status=BARU" class="badge badge-BARU" style="padding:8px 14px; font-size:0.8rem; text-decoration:none;">
            Baru Masuk: <strong><?= $woNew ?></strong>
        </a>
        <a href="/work-orders?status=DIKONFIRMASI" class="badge badge-DIKONFIRMASI" style="padding:8px 14px; font-size:0.8rem; text-decoration:none;">
            Dikonfirmasi: <strong><?= $woConfirmed ?></strong>
        </a>
        <a href="/work-orders?status=DIJADWALKAN" class="badge badge-DIJADWALKAN" style="padding:8px 14px; font-size:0.8rem; text-decoration:none;">
            Terjadwal: <strong><?= $woScheduled ?></strong>
        </a>
        <a href="/work-orders?status=SEDANG_DIKERJAKAN" class="badge badge-SEDANG_DIKERJAKAN" style="padding:8px 14px; font-size:0.8rem; text-decoration:none;">
            Sedang Jalan: <strong><?= $woInProgress ?></strong>
        </a>
        <a href="/work-orders?status=SELESAI" class="badge badge-SELESAI" style="padding:8px 14px; font-size:0.8rem; text-decoration:none;">
            Selesai Hari Ini: <strong><?= $woCompleted ?></strong>
        </a>
        <a href="/work-orders?status=DITUNDA" class="badge badge-DITUNDA" style="padding:8px 14px; font-size:0.8rem; text-decoration:none;">
            Tertunda: <strong><?= $woPending ?></strong>
        </a>
    </div>
</div>

<!-- Grid: Charts & Leaderboard -->
<div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap:20px; margin-bottom:20px;">
    <!-- Revenue Chart Card -->
    <?php if (in_array($role, ['owner', 'admin'])): ?>
    <div class="card" style="margin-bottom:0;">
        <div class="card-header">
            <div class="card-title">
                <i data-lucide="bar-chart-2"></i>
                <span>Tren Pendapatan & Pengeluaran</span>
            </div>
            <span style="font-size:0.75rem; color:var(--text-muted);">6 Bulan Terakhir</span>
        </div>
        <div style="position:relative; height:240px;">
            <canvas id="revenueChart"></canvas>
        </div>
    </div>
    <?php endif; ?>

    <!-- Technician Productivity Leaderboard -->
    <div class="card" style="margin-bottom:0;">
        <div class="card-header">
            <div class="card-title">
                <i data-lucide="award"></i>
                <span>Performa Teknisi Terbaik</span>
            </div>
            <a href="/technicians" style="font-size:0.8rem; font-weight:600;">Lihat Semua</a>
        </div>
        <div style="display:flex; flex-direction:column; gap:12px;">
            <?php if (empty($technicianLeaderboard)): ?>
                <div style="text-align:center; padding:20px; color:var(--text-muted); font-size:0.85rem;">Belum ada data teknisi.</div>
            <?php else: ?>
                <?php foreach ($technicianLeaderboard as $idx => $t): ?>
                    <div style="display:flex; align-items:center; justify-content:space-between; padding:8px 12px; background:var(--surface-alt); border-radius:var(--radius-sm);">
                        <div style="display:flex; align-items:center; gap:12px;">
                            <div style="width:28px; height:28px; border-radius:50%; background:<?= $idx === 0 ? '#fef08a' : '#e2e8f0' ?>; color:<?= $idx === 0 ? '#854d0e' : '#475569' ?>; display:flex; align-items:center; justify-content:center; font-weight:700; font-size:0.8rem;">
                                <?= $idx + 1 ?>
                            </div>
                            <div>
                                <div style="font-weight:700; font-size:0.9rem; color:var(--primary);"><?= htmlspecialchars($t['full_name']) ?></div>
                                <div style="font-size:0.75rem; color:var(--text-muted);"><?= htmlspecialchars($t['code']) ?> &bull; <?= htmlspecialchars($t['work_area']) ?></div>
                            </div>
                        </div>
                        <div style="text-align:right;">
                            <div style="font-weight:700; font-size:0.85rem; color:var(--success);">⭐ <?= number_format((float)$t['rating'], 1) ?></div>
                            <div style="font-size:0.75rem; color:var(--text-muted);"><?= (int)$t['total_jobs_done'] ?> servis selesai</div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Grid: Work Orders & Stock Alerts -->
<div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap:20px;">
    <!-- Recent Work Orders -->
    <div class="card" style="grid-column: span 1;">
        <div class="card-header">
            <div class="card-title">
                <i data-lucide="clipboard-list"></i>
                <span>Work Order Terbaru</span>
            </div>
            <a href="/work-orders" class="btn btn-outline btn-sm">Lihat Semua</a>
        </div>
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>No. WO</th>
                        <th>Pelanggan</th>
                        <th>Jadwal</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($recentWorkOrders)): ?>
                        <tr><td colspan="5" style="text-align:center; color:var(--text-muted);">Belum ada order pekerjaan.</td></tr>
                    <?php else: ?>
                        <?php foreach ($recentWorkOrders as $wo): ?>
                            <tr>
                                <td>
                                    <strong style="color:var(--primary);"><?= htmlspecialchars($wo['order_number']) ?></strong>
                                    <div style="font-size:0.75rem; color:var(--text-muted);"><?= htmlspecialchars($wo['ac_brand'] ?? 'AC') ?> (<?= htmlspecialchars($wo['capacity_pk'] ?? '1 PK') ?>)</div>
                                </td>
                                <td>
                                    <div><?= htmlspecialchars($wo['customer_name']) ?></div>
                                    <div style="font-size:0.75rem; color:var(--text-muted);"><?= htmlspecialchars($wo['customer_phone']) ?></div>
                                </td>
                                <td>
                                    <div><?= format_date($wo['scheduled_date']) ?></div>
                                    <div style="font-size:0.75rem; color:var(--text-muted);">Jam: <?= htmlspecialchars($wo['scheduled_time']) ?></div>
                                </td>
                                <td>
                                    <span class="badge badge-<?= $wo['status'] ?>"><?= str_replace('_', ' ', $wo['status']) ?></span>
                                </td>
                                <td>
                                    <a href="/work-orders/<?= $wo['id'] ?>" class="btn btn-outline btn-sm" title="Detail">
                                        <i data-lucide="eye" style="width:14px; height:14px;"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Right Sidebar Widgets -->
    <div style="display:flex; flex-direction:column; gap:20px;">
        <!-- Low Stock Alerts -->
        <div class="card" style="margin-bottom:0;">
            <div class="card-header">
                <div class="card-title" style="color:var(--danger);">
                    <i data-lucide="alert-triangle"></i>
                    <span>Peringatan Stok Menipis</span>
                </div>
                <a href="/inventory" style="font-size:0.8rem; font-weight:600;">Inventori</a>
            </div>
            <div>
                <?php if (empty($lowStockItems)): ?>
                    <div style="padding:12px; text-align:center; color:var(--success); font-size:0.85rem;">
                        <i data-lucide="check-circle" style="width:20px; height:20px; display:inline-block; vertical-align:middle; margin-right:4px;"></i>
                        Semua stok sparepart berada di atas batas minimum.
                    </div>
                <?php else: ?>
                    <div style="display:flex; flex-direction:column; gap:8px;">
                        <?php foreach ($lowStockItems as $item): ?>
                            <div style="display:flex; justify-content:space-between; align-items:center; padding:8px 12px; background:#fff1f2; border:1px solid #ffe4e6; border-radius:var(--radius-sm);">
                                <div>
                                    <div style="font-weight:700; font-size:0.85rem; color:var(--text-main);"><?= htmlspecialchars($item['name']) ?></div>
                                    <div style="font-size:0.75rem; color:var(--text-muted);">SKU: <?= htmlspecialchars($item['sku']) ?></div>
                                </div>
                                <div style="text-align:right;">
                                    <span class="badge badge-red" style="font-size:0.75rem;">Sisa: <?= $item['current_stock'] ?> <?= $item['unit'] ?></span>
                                    <div style="font-size:0.7rem; color:var(--text-muted); margin-top:2px;">Min: <?= $item['min_stock'] ?></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Service Reminders Due -->
        <div class="card" style="margin-bottom:0;">
            <div class="card-header">
                <div class="card-title">
                    <i data-lucide="bell-ring"></i>
                    <span>Jadwal Pengingat Servis Rutin</span>
                </div>
            </div>
            <div>
                <?php if (empty($serviceReminders)): ?>
                    <div style="padding:12px; text-align:center; color:var(--text-muted); font-size:0.85rem;">
                        Tidak ada servis berkala yang jatuh tempo dalam 14 hari ke depan.
                    </div>
                <?php else: ?>
                    <div style="display:flex; flex-direction:column; gap:8px;">
                        <?php foreach ($serviceReminders as $rem): ?>
                            <div style="display:flex; justify-content:space-between; align-items:center; padding:8px 12px; background:var(--surface-alt); border-radius:var(--radius-sm);">
                                <div>
                                    <div style="font-weight:700; font-size:0.85rem; color:var(--primary);"><?= htmlspecialchars($rem['customer_name']) ?></div>
                                    <div style="font-size:0.75rem; color:var(--text-muted);"><?= htmlspecialchars($rem['brand']) ?> &bull; <?= htmlspecialchars($rem['room_location']) ?></div>
                                </div>
                                <div style="text-align:right;">
                                    <span class="badge badge-amber" style="font-size:0.7rem;">Tempo: <?= format_date($rem['due_date']) ?></span>
                                    <div style="margin-top:4px;">
                                        <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $rem['customer_phone']) ?>?text=Halo%20<?= urlencode($rem['customer_name']) ?>,%20AC%20Anda%20sudah%20waktunya%20servis%20rutin" target="_blank" class="btn btn-sm btn-outline" style="padding:2px 8px; font-size:0.7rem; color:#16a34a; border-color:#bbf7d0;">
                                            <i data-lucide="message-circle" style="width:12px; height:12px;"></i> WA
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const ctx = document.getElementById('revenueChart');
        if (ctx) {
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: <?= json_encode($chartLabels) ?>,
                    datasets: [
                        {
                            label: 'Pendapatan (Rp)',
                            data: <?= json_encode($chartIncome) ?>,
                            borderColor: '#2563eb',
                            backgroundColor: 'rgba(37, 99, 235, 0.1)',
                            borderWidth: 2.5,
                            fill: true,
                            tension: 0.35
                        },
                        {
                            label: 'Pengeluaran (Rp)',
                            data: <?= json_encode($chartExpenses) ?>,
                            borderColor: '#ef4444',
                            backgroundColor: 'rgba(239, 68, 68, 0.05)',
                            borderWidth: 2,
                            fill: true,
                            tension: 0.35
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'top', labels: { boxWidth: 12, font: { size: 11 } } }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return 'Rp ' + (value / 1000).toLocaleString('id-ID') + 'k';
                                },
                                font: { size: 10 }
                            }
                        },
                        x: {
                            ticks: { font: { size: 10 } }
                        }
                    }
                }
            });
        }
    });
</script>

<?php require dirname(__DIR__) . '/layout/footer.php'; ?>
