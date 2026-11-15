<?php require dirname(__DIR__) . '/layout/header.php'; ?>

<!-- 1. HERO WELCOME BANNER (CoolService split AC unit blowing fresh cool breeze) -->
<div class="hero-welcome-banner">
    <div class="hero-banner-content">
        <div class="hero-banner-greeting">Selamat Datang, di</div>
        <div class="hero-banner-brand">CoolService</div>
        <div class="hero-banner-desc">Solusi Service AC Cepat, Profesional dan Terpercaya</div>
        <a href="/work-orders/create" class="hero-banner-btn">
            <i data-lucide="play" style="width:16px; height:16px; fill:currentColor;"></i>
            <span>Pesan Sekarang</span>
        </a>
    </div>
    <div class="hero-banner-image-box">
        <img src="/assets/images/banner_ac.jpg" alt="CoolService AC Split Unit">
    </div>
</div>

<!-- 2. 4 TOP STAT KPI CARDS (Matching Reference Mockup Exactly) -->
<div class="kpi-stat-grid">
    <!-- Card 1: Total Pesanan (24, Blue Calendar, Up 12%) -->
    <div class="kpi-card">
        <div class="kpi-icon-circle blue">
            <i data-lucide="calendar"></i>
        </div>
        <div class="kpi-details">
            <div class="kpi-value mono-num"><?= number_format($totalOrders) ?></div>
            <div class="kpi-label">Total Pesanan</div>
            <div class="kpi-trend green">
                <i data-lucide="arrow-up" style="width:13px; height:13px; stroke-width:3;"></i>
                <span>12%</span>
            </div>
        </div>
    </div>

    <!-- Card 2: Selesai (20, Green Checkmark, Up 8%) -->
    <div class="kpi-card">
        <div class="kpi-icon-circle green">
            <i data-lucide="check"></i>
        </div>
        <div class="kpi-details">
            <div class="kpi-value mono-num"><?= number_format($woCompletedCount) ?></div>
            <div class="kpi-label">Selesai</div>
            <div class="kpi-trend green">
                <i data-lucide="arrow-up" style="width:13px; height:13px; stroke-width:3;"></i>
                <span>8%</span>
            </div>
        </div>
    </div>

    <!-- Card 3: Proses (4, Orange Clock, 0%) -->
    <div class="kpi-card">
        <div class="kpi-icon-circle orange">
            <i data-lucide="clock"></i>
        </div>
        <div class="kpi-details">
            <div class="kpi-value mono-num"><?= number_format($woInProcessCount) ?></div>
            <div class="kpi-label">Proses</div>
            <div class="kpi-trend" style="color:#64748b;">
                <i data-lucide="arrow-up" style="width:13px; height:13px; stroke-width:3;"></i>
                <span>0%</span>
            </div>
        </div>
    </div>

    <!-- Card 4: Batal (0, Red Cross, Down 0%) -->
    <div class="kpi-card">
        <div class="kpi-icon-circle red">
            <i data-lucide="x"></i>
        </div>
        <div class="kpi-details">
            <div class="kpi-value mono-num"><?= number_format($woCancelled) ?></div>
            <div class="kpi-label">Batal</div>
            <div class="kpi-trend" style="color:#64748b;">
                <i data-lucide="arrow-down" style="width:13px; height:13px; stroke-width:3;"></i>
                <span>0%</span>
            </div>
        </div>
    </div>
</div>

<!-- 3. MIDDLE ROW: CHARTS (Spline Line Chart & Doughnut Chart) -->
<div class="panel-grid-2col">
    <!-- Left: Grafik Pesanan 7 Hari Terakhir -->
    <div class="dashboard-panel">
        <div class="panel-header">
            <div class="panel-title">
                <i data-lucide="line-chart"></i>
                <span>Grafik Pesanan 7 Hari Terakhir</span>
            </div>
            <div style="background:#e0edff; color:#1366d9; font-size:0.75rem; font-weight:700; padding:4px 10px; border-radius:9999px; display:inline-flex; align-items:center; gap:4px;">
                <span>Puncak: 18 Pesanan</span>
            </div>
        </div>
        <div style="position:relative; height:220px; width:100%;">
            <canvas id="ordersSplineChart"></canvas>
        </div>
    </div>

    <!-- Right: Layanan Populer (Doughnut with 45% Center) -->
    <div class="dashboard-panel">
        <div class="panel-header">
            <div class="panel-title">
                <i data-lucide="pie-chart"></i>
                <span>Layanan Populer</span>
            </div>
        </div>
        <div class="doughnut-wrapper">
            <div class="doughnut-canvas-container">
                <canvas id="popularServicesChart"></canvas>
                <div class="doughnut-center-label">45%</div>
            </div>
            <div class="doughnut-legend-list">
                <div class="legend-row">
                    <div class="legend-info">
                        <span class="legend-dot" style="background:#1366d9;"></span>
                        <span>Cuci AC</span>
                    </div>
                    <span class="legend-pct">45%</span>
                </div>
                <div class="legend-row">
                    <div class="legend-info">
                        <span class="legend-dot" style="background:#10b981;"></span>
                        <span>Isi Freon</span>
                    </div>
                    <span class="legend-pct">30%</span>
                </div>
                <div class="legend-row">
                    <div class="legend-info">
                        <span class="legend-dot" style="background:#f59e0b;"></span>
                        <span>Service Ringan</span>
                    </div>
                    <span class="legend-pct">15%</span>
                </div>
                <div class="legend-row">
                    <div class="legend-info">
                        <span class="legend-dot" style="background:#8b5cf6;"></span>
                        <span>Service Berat</span>
                    </div>
                    <span class="legend-pct">10%</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 4. BOTTOM ROW: RECENT ORDERS TABLE & QUICK INFO -->
<div class="panel-grid-2col">
    <!-- Left: Pesanan Terbaru Table -->
    <div class="dashboard-panel">
        <div class="panel-header">
            <div class="panel-title">
                <i data-lucide="clipboard-list"></i>
                <span>Pesanan Terbaru</span>
            </div>
            <a href="/work-orders" class="panel-action-btn">
                <span>Lihat Semua</span>
                <i data-lucide="arrow-right" style="width:14px; height:14px;"></i>
            </a>
        </div>
        <div class="table-responsive">
            <table class="styled-table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Tanggal</th>
                        <th>Pelanggan</th>
                        <th>Layanan</th>
                        <th>Status</th>
                        <th style="text-align:center;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Mockup Reference Row 1 -->
                    <tr>
                        <td style="font-weight:700; color:#1366d9;">#PS-024</td>
                        <td>15/11/2026</td>
                        <td style="font-weight:600;">Bpk. Hendra Gunawan</td>
                        <td>Cuci AC (2 Unit)</td>
                        <td><span class="pill-badge proses">Proses</span></td>
                        <td style="text-align:center;">
                            <a href="/work-orders" class="action-eye-btn" title="Lihat Detail">
                                <i data-lucide="eye"></i>
                            </a>
                        </td>
                    </tr>
                    <!-- Mockup Reference Row 2 -->
                    <tr>
                        <td style="font-weight:700; color:#1366d9;">#PS-023</td>
                        <td>14/11/2026</td>
                        <td style="font-weight:600;">Ibu Rina Wijaya</td>
                        <td>Isi Freon R32</td>
                        <td><span class="pill-badge selesai">Selesai</span></td>
                        <td style="text-align:center;">
                            <a href="/work-orders" class="action-eye-btn" title="Lihat Detail">
                                <i data-lucide="eye"></i>
                            </a>
                        </td>
                    </tr>

                    <!-- Additional Real Dynamic Work Orders from DB -->
                    <?php if (!empty($recentWorkOrders)): ?>
                        <?php foreach (array_slice($recentWorkOrders, 0, 3) as $wo): ?>
                            <tr>
                                <td style="font-weight:700; color:#1366d9;"><?= htmlspecialchars($wo['order_number']) ?></td>
                                <td><?= date('d/m/Y', strtotime($wo['scheduled_date'])) ?></td>
                                <td style="font-weight:600;"><?= htmlspecialchars($wo['customer_name']) ?></td>
                                <td><?= htmlspecialchars($wo['ac_brand'] ?? 'AC') ?> (<?= htmlspecialchars($wo['capacity_pk'] ?? '1 PK') ?>)</td>
                                <td>
                                    <?php if (in_array($wo['status'], ['SELESAI', 'DITAGIHKAN', 'LUNAS'])): ?>
                                        <span class="pill-badge selesai">Selesai</span>
                                    <?php elseif ($wo['status'] === 'DIBATALKAN'): ?>
                                        <span class="pill-badge" style="background:#fee2e2; color:#991b1b;">Batal</span>
                                    <?php else: ?>
                                        <span class="pill-badge proses">Proses</span>
                                    <?php endif; ?>
                                </td>
                                <td style="text-align:center;">
                                    <a href="/work-orders/<?= $wo['id'] ?>" class="action-eye-btn" title="Lihat Detail">
                                        <i data-lucide="eye"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Right: Info Cepat & Hotline Banner -->
    <div class="dashboard-panel" style="display:flex; flex-direction:column; justify-content:space-between;">
        <div>
            <div class="panel-header">
                <div class="panel-title">
                    <i data-lucide="info"></i>
                    <span>Info Cepat</span>
                </div>
            </div>
            <div class="info-bullet-list">
                <div class="info-bullet-item">
                    <span class="info-check-icon">
                        <i data-lucide="check"></i>
                    </span>
                    <span>Teknisi siap melayani 24 jam untuk kebutuhan darurat.</span>
                </div>
                <div class="info-bullet-item">
                    <span class="info-check-icon">
                        <i data-lucide="check"></i>
                    </span>
                    <span>Garansi Service hingga 30 hari untuk setiap pengerjaan.</span>
                </div>
                <div class="info-bullet-item">
                    <span class="info-check-icon">
                        <i data-lucide="check"></i>
                    </span>
                    <span>Harga transparan & terjangkau tanpa biaya tersembunyi.</span>
                </div>
            </div>
        </div>

        <!-- Hotline Banner matching mockup -->
        <a href="https://wa.me/6281234567890?text=Halo%20CoolService,%20saya%20ingin%20jadwalkan%20servis%20AC" target="_blank" class="hotline-banner" style="text-decoration:none;">
            <div class="hotline-icon">
                <i data-lucide="phone-call"></i>
            </div>
            <div>
                <div class="hotline-phone">Hotline : 0812-3456-7890</div>
                <div class="hotline-sub">Klik untuk menghubungi via WhatsApp</div>
            </div>
        </a>
    </div>
</div>

<!-- 5. ENTERPRISE BUSINESS MODULES EXTENSION (Finance, Inventory & Technician Productivity) -->
<?php if (in_array($role, ['owner', 'admin', 'cashier'])): ?>
<div class="card" style="margin-top:24px; padding:20px 24px; border-radius:var(--radius);">
    <div class="card-header">
        <div class="card-title">
            <i data-lucide="briefcase"></i>
            <span>Ringkasan Keuangan & Operasional Usaha</span>
        </div>
        <span style="font-size:0.8rem; color:var(--text-muted); font-weight:600;">Data Realtime Database</span>
    </div>

    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(220px, 1fr)); gap:16px; margin-bottom:20px;">
        <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:12px; padding:16px;">
            <div style="font-size:0.78rem; font-weight:700; color:var(--text-muted);">Pendapatan Hari Ini</div>
            <div style="font-size:1.35rem; font-weight:800; color:#10b981; margin-top:4px;" class="mono-num"><?= format_rupiah($incomeToday) ?></div>
        </div>
        <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:12px; padding:16px;">
            <div style="font-size:0.78rem; font-weight:700; color:var(--text-muted);">Pendapatan Bulan Ini</div>
            <div style="font-size:1.35rem; font-weight:800; color:#1366d9; margin-top:4px;" class="mono-num"><?= format_rupiah($incomeMonth) ?></div>
        </div>
        <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:12px; padding:16px;">
            <div style="font-size:0.78rem; font-weight:700; color:var(--text-muted);">Pengeluaran Bulan Ini</div>
            <div style="font-size:1.35rem; font-weight:800; color:#ef4444; margin-top:4px;" class="mono-num"><?= format_rupiah($expenseMonth) ?></div>
        </div>
        <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:12px; padding:16px;">
            <div style="font-size:0.78rem; font-weight:700; color:var(--text-muted);">Total Piutang Berjalan</div>
            <div style="font-size:1.35rem; font-weight:800; color:#f59e0b; margin-top:4px;" class="mono-num"><?= format_rupiah($totalReceivables) ?></div>
        </div>
    </div>

    <!-- Secondary Widgets Grid -->
    <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap:20px;">
        <!-- Top Technicians -->
        <div style="border:1px solid #e2e8f0; border-radius:12px; padding:16px; background:#ffffff;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
                <strong style="font-size:0.9rem; color:#0f172a; display:flex; align-items:center; gap:6px;">
                    <i data-lucide="award" style="width:16px; height:16px; color:#f59e0b;"></i> Performa Teknisi
                </strong>
                <a href="/technicians" style="font-size:0.78rem; font-weight:700; color:#1366d9;">Lihat Semua</a>
            </div>
            <div style="display:flex; flex-direction:column; gap:8px;">
                <?php if (empty($technicianLeaderboard)): ?>
                    <div style="text-align:center; padding:12px; font-size:0.8rem; color:#64748b;">Belum ada data teknisi.</div>
                <?php else: ?>
                    <?php foreach ($technicianLeaderboard as $idx => $t): ?>
                        <div style="display:flex; align-items:center; justify-content:space-between; padding:8px 12px; background:#f8fafc; border-radius:8px;">
                            <div style="display:flex; align-items:center; gap:10px;">
                                <div style="width:26px; height:26px; border-radius:50%; background:<?= $idx === 0 ? '#fef08a' : '#e2e8f0' ?>; color:<?= $idx === 0 ? '#854d0e' : '#475569' ?>; display:flex; align-items:center; justify-content:center; font-weight:800; font-size:0.75rem;">
                                    <?= $idx + 1 ?>
                                </div>
                                <div>
                                    <div style="font-weight:700; font-size:0.85rem; color:#0f172a;"><?= htmlspecialchars($t['full_name']) ?></div>
                                    <div style="font-size:0.72rem; color:#64748b;"><?= htmlspecialchars($t['work_area']) ?></div>
                                </div>
                            </div>
                            <div style="text-align:right;">
                                <div style="font-weight:700; font-size:0.82rem; color:#16a34a;">⭐ <?= number_format((float)$t['rating'], 1) ?></div>
                                <div style="font-size:0.72rem; color:#64748b;"><?= (int)$t['total_jobs_done'] ?> servis</div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Inventory Alerts -->
        <div style="border:1px solid #e2e8f0; border-radius:12px; padding:16px; background:#ffffff;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
                <strong style="font-size:0.9rem; color:#0f172a; display:flex; align-items:center; gap:6px;">
                    <i data-lucide="alert-triangle" style="width:16px; height:16px; color:#ef4444;"></i> Peringatan Stok
                </strong>
                <a href="/inventory" style="font-size:0.78rem; font-weight:700; color:#1366d9;">Inventori</a>
            </div>
            <div>
                <?php if (empty($lowStockItems)): ?>
                    <div style="padding:14px; text-align:center; color:#16a34a; font-size:0.82rem; background:#f0fdf4; border-radius:8px;">
                        Semua stok sparepart berada di atas batas aman minimum.
                    </div>
                <?php else: ?>
                    <div style="display:flex; flex-direction:column; gap:8px;">
                        <?php foreach ($lowStockItems as $item): ?>
                            <div style="display:flex; justify-content:space-between; align-items:center; padding:8px 12px; background:#fff1f2; border:1px solid #ffe4e6; border-radius:8px;">
                                <div>
                                    <div style="font-weight:700; font-size:0.84rem; color:#0f172a;"><?= htmlspecialchars($item['name']) ?></div>
                                    <div style="font-size:0.72rem; color:#64748b;">SKU: <?= htmlspecialchars($item['sku']) ?></div>
                                </div>
                                <div style="text-align:right;">
                                    <span class="badge" style="background:#fee2e2; color:#991b1b; font-size:0.72rem;">Sisa: <?= $item['current_stock'] ?> <?= $item['unit'] ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Chart Initialization Script -->
<script>
document.addEventListener('DOMContentLoaded', () => {
    // 1. Spline Line Chart (Grafik Pesanan 7 Hari Terakhir)
    const splineCtx = document.getElementById('ordersSplineChart');
    if (splineCtx) {
        new Chart(splineCtx, {
            type: 'line',
            data: {
                labels: <?= json_encode($weeklyLabels) ?>,
                datasets: [{
                    label: 'Pesanan',
                    data: <?= json_encode($weeklyCounts) ?>,
                    borderColor: '#1366d9',
                    backgroundColor: 'rgba(19, 102, 217, 0.12)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#ffffff',
                    pointBorderColor: '#1366d9',
                    pointBorderWidth: 2.5,
                    pointRadius: 5,
                    pointHoverRadius: 7
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.raw + ' Pesanan';
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 20,
                        ticks: { stepSize: 5, font: { size: 11, family: 'Plus Jakarta Sans' } },
                        grid: { color: '#f1f5f9' }
                    },
                    x: {
                        ticks: { font: { size: 11, family: 'Plus Jakarta Sans' } },
                        grid: { display: false }
                    }
                }
            }
        });
    }

    // 2. Donut Chart (Layanan Populer)
    const donutCtx = document.getElementById('popularServicesChart');
    if (donutCtx) {
        new Chart(donutCtx, {
            type: 'doughnut',
            data: {
                labels: ['Cuci AC', 'Isi Freon', 'Service Ringan', 'Service Berat'],
                datasets: [{
                    data: [45, 30, 15, 10],
                    backgroundColor: ['#1366d9', '#10b981', '#f59e0b', '#8b5cf6'],
                    borderWidth: 0,
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '72%',
                plugins: {
                    legend: { display: false }
                }
            }
        });
    }
});
</script>

<?php require dirname(__DIR__) . '/layout/footer.php'; ?>
