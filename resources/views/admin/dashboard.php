<?php require dirname(__DIR__) . '/layout/header.php'; ?>

<!-- 1. MODERN HERO WELCOME BANNER -->
<div class="hero-banner-modern">
    <div class="hero-banner-left">
        <div class="hero-brand-pill">
            <i data-lucide="snowflake" style="width:14px; height:14px; stroke-width:2.5;"></i>
            <span>AC SERVICE PRO • SISTEM OPERASIONAL</span>
        </div>
        <h2 class="hero-banner-title">
            Selamat Datang,<br>di <span>AC Service Pro</span>
        </h2>
        <p class="hero-banner-subtitle">
            Solusi Service AC Cepat, Profesional dan Terpercaya. Kelola pemesanan, teknisi lapangan, stok sparepart & kasir dalam satu sistem terpadu.
        </p>
        <div style="display:flex; gap:10px; flex-wrap:wrap;">
            <a href="/work-orders/create" class="hero-btn-order">
                <i data-lucide="play" style="fill:currentColor; width:16px; height:16px;"></i>
                <span>Pesan Sekarang</span>
                <i data-lucide="arrow-right" style="width:16px; height:16px;"></i>
            </a>
            <a href="/customers/create" class="btn btn-outline" style="background:rgba(255,255,255,0.75); border-color:rgba(37,99,235,0.3); color:#1d4ed8; border-radius:var(--radius-full); font-weight:700;">
                <i data-lucide="user-plus"></i>
                <span>Tambah Pelanggan</span>
            </a>
        </div>
    </div>
    <div class="hero-banner-right">
        <img src="<?= asset('images/banner_ac.jpg') ?>" class="hero-ac-img" alt="AC Split Unit Inverter">
    </div>
</div>

<!-- 2. PRIMARY 4 KPI STAT CARDS (Desktop 4-Col, Mobile 2x2 Grid) -->
<div class="stat-grid" style="margin-bottom:24px;">
    <!-- 1. Total Pesanan -->
    <div class="stat-card-modern">
        <div class="stat-icon-box blue">
            <i data-lucide="calendar"></i>
        </div>
        <div class="stat-info-modern">
            <div class="stat-val-modern"><?= $totalOrdersDisplay ?? 24 ?></div>
            <div class="stat-lbl-modern">Total Pesanan</div>
            <div class="stat-trend up">
                <i data-lucide="trending-up" style="width:12px; height:12px;"></i> ↑ 12%
            </div>
        </div>
    </div>

    <!-- 2. Selesai -->
    <div class="stat-card-modern">
        <div class="stat-icon-box green">
            <i data-lucide="check-circle-2"></i>
        </div>
        <div class="stat-info-modern">
            <div class="stat-val-modern"><?= $completedOrdersDisplay ?? 20 ?></div>
            <div class="stat-lbl-modern">Selesai</div>
            <div class="stat-trend up">
                <i data-lucide="trending-up" style="width:12px; height:12px;"></i> ↑ 8%
            </div>
        </div>
    </div>

    <!-- 3. Proses -->
    <div class="stat-card-modern">
        <div class="stat-icon-box yellow">
            <i data-lucide="clock"></i>
        </div>
        <div class="stat-info-modern">
            <div class="stat-val-modern"><?= $inProgressOrdersDisplay ?? 4 ?></div>
            <div class="stat-lbl-modern">Proses</div>
            <div class="stat-trend neutral">
                <i data-lucide="minus" style="width:12px; height:12px;"></i> ↑ 0%
            </div>
        </div>
    </div>

    <!-- 4. Batal -->
    <div class="stat-card-modern">
        <div class="stat-icon-box red">
            <i data-lucide="x-circle"></i>
        </div>
        <div class="stat-info-modern">
            <div class="stat-val-modern"><?= $pendingOrdersDisplay ?? 0 ?></div>
            <div class="stat-lbl-modern">Batal</div>
            <div class="stat-trend down">
                <i data-lucide="trending-down" style="width:12px; height:12px;"></i> ↓ 0%
            </div>
        </div>
    </div>
</div>

<!-- 3. MIDDLE ROW: CHARTS (Grafik Pesanan 7 Hari & Layanan Populer) -->
<div class="dashboard-grid-2col" style="margin-bottom:24px;">
    <!-- Left: Grafik Pesanan 7 Hari Terakhir -->
    <div class="chart-card-modern">
        <div class="card-header" style="border-bottom:none; margin-bottom:6px; padding-bottom:0;">
            <div class="card-title">
                <i data-lucide="line-chart" style="color:var(--secondary);"></i>
                <span>Grafik Pesanan 7 Hari Terakhir</span>
            </div>
            <span class="badge badge-blue" style="font-size:0.75rem;">18 Pesanan (Puncak)</span>
        </div>
        <div style="position:relative; height:240px; width:100%; max-width:100%;">
            <canvas id="weeklyOrdersChart"></canvas>
        </div>
    </div>

    <!-- Right: Layanan Populer (Donut) -->
    <div class="chart-card-modern">
        <div class="card-header" style="border-bottom:none; margin-bottom:6px; padding-bottom:0;">
            <div class="card-title">
                <i data-lucide="pie-chart" style="color:var(--secondary);"></i>
                <span>Layanan Populer</span>
            </div>
            <span style="font-size:0.75rem; color:var(--text-muted);">Berdasarkan Frekuensi</span>
        </div>
        <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:16px;">
            <div class="donut-wrapper-modern" style="flex:1 1 180px; max-width:210px; margin:0 auto;">
                <canvas id="popularServicesChart"></canvas>
                <div class="donut-center-badge">
                    <strong>45%</strong>
                    <span>Cuci AC</span>
                </div>
            </div>
            <div style="flex:1 1 160px; display:flex; flex-direction:column; gap:8px;">
                <div style="display:flex; align-items:center; justify-content:space-between; font-size:0.82rem; font-weight:600;">
                    <span style="display:flex; align-items:center; gap:6px;">
                        <span style="width:10px; height:10px; border-radius:50%; background:#2563eb; display:inline-block;"></span>
                        Cuci AC
                    </span>
                    <strong style="color:#2563eb;">45%</strong>
                </div>
                <div style="display:flex; align-items:center; justify-content:space-between; font-size:0.82rem; font-weight:600;">
                    <span style="display:flex; align-items:center; gap:6px;">
                        <span style="width:10px; height:10px; border-radius:50%; background:#16a34a; display:inline-block;"></span>
                        Isi Freon
                    </span>
                    <strong style="color:#16a34a;">30%</strong>
                </div>
                <div style="display:flex; align-items:center; justify-content:space-between; font-size:0.82rem; font-weight:600;">
                    <span style="display:flex; align-items:center; gap:6px;">
                        <span style="width:10px; height:10px; border-radius:50%; background:#f59e0b; display:inline-block;"></span>
                        Service Ringan
                    </span>
                    <strong style="color:#f59e0b;">15%</strong>
                </div>
                <div style="display:flex; align-items:center; justify-content:space-between; font-size:0.82rem; font-weight:600;">
                    <span style="display:flex; align-items:center; gap:6px;">
                        <span style="width:10px; height:10px; border-radius:50%; background:#8b5cf6; display:inline-block;"></span>
                        Service Berat
                    </span>
                    <strong style="color:#8b5cf6;">10%</strong>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 4. BOTTOM ROW: PESANAN TERBARU & INFO CEPAT -->
<div class="dashboard-grid-2col" style="margin-bottom:24px;">
    <!-- Left: Pesanan Terbaru -->
    <div class="chart-card-modern">
        <div class="card-header" style="margin-bottom:12px; padding-bottom:12px;">
            <div class="card-title">
                <i data-lucide="clipboard-list" style="color:var(--secondary);"></i>
                <span>Pesanan Terbaru</span>
            </div>
            <a href="/work-orders" class="btn-lihat-semua">Lihat Semua →</a>
        </div>
        <div class="table-responsive">
            <table class="data-table" style="font-size:0.8rem; width:100%;">
                <thead>
                    <tr>
                        <th style="width:65px; padding:8px 4px;">No</th>
                        <th style="width:60px; padding:8px 4px;">Tgl</th>
                        <th style="padding:8px 6px;">Pelanggan</th>
                        <th style="padding:8px 6px;">Layanan</th>
                        <th style="width:70px; text-align:center; padding:8px 4px;">Status</th>
                        <th style="width:40px; text-align:center; padding:8px 4px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($recentWorkOrders)): ?>
                        <tr><td colspan="6" style="text-align:center; color:var(--text-muted); padding:20px;">Belum ada pesanan terbaru.</td></tr>
                    <?php else: ?>
                        <?php foreach ($recentWorkOrders as $idx => $wo): ?>
                            <?php
                                $psNo = '#PS-' . str_pad((string)($wo['id'] ?? ($idx + 22)), 3, '0', STR_PAD_LEFT);
                                $isDone = in_array($wo['status'], ['SELESAI', 'DITAGIHKAN', 'LUNAS']);
                                $shortDate = date('d M', strtotime($wo['scheduled_date'] ?? date('Y-m-d')));
                            ?>
                            <tr>
                                <td style="padding:8px 4px;"><strong style="color:var(--secondary);"><?= $psNo ?></strong></td>
                                <td style="padding:8px 4px; white-space:nowrap;"><?= $shortDate ?></td>
                                <td style="padding:8px 6px; font-weight:700; max-width:120px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;" title="<?= htmlspecialchars($wo['customer_name'] ?? 'Pelanggan') ?>"><?= htmlspecialchars($wo['customer_name'] ?? 'Pelanggan') ?></td>
                                <td style="padding:8px 6px; max-width:110px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;" title="<?= htmlspecialchars($wo['service_name'] ?? 'Cuci AC') ?>"><?= htmlspecialchars($wo['service_name'] ?? 'Cuci AC') ?></td>
                                <td style="text-align:center; padding:8px 4px;">
                                    <?php if ($isDone): ?>
                                        <span class="badge badge-SELESAI" style="padding:3px 8px; font-size:0.72rem;">Selesai</span>
                                    <?php else: ?>
                                        <span class="badge badge-SEDANG_DIKERJAKAN" style="padding:3px 8px; font-size:0.72rem;">Proses</span>
                                    <?php endif; ?>
                                </td>
                                <td style="text-align:center; padding:8px 4px;">
                                    <a href="/work-orders/<?= $wo['id'] ?>" class="btn-action btn-action-view" style="padding:4px 7px; min-height:24px; border-radius:50px;" title="Lihat Detail">
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

    <!-- Right: Info Cepat & Hotline -->
    <div class="chart-card-modern">
        <div class="card-header" style="margin-bottom:12px; padding-bottom:12px;">
            <div class="card-title">
                <i data-lucide="zap" style="color:#f59e0b;"></i>
                <span>Info Cepat</span>
            </div>
        </div>
        <div style="display:flex; flex-direction:column; gap:10px;">
            <div class="info-cepat-item">
                <i data-lucide="check-circle-2"></i>
                <span>Teknisi siap melayani 24 jam</span>
            </div>
            <div class="info-cepat-item">
                <i data-lucide="check-circle-2"></i>
                <span>Garansi Service hingga 30 hari</span>
            </div>
            <div class="info-cepat-item">
                <i data-lucide="check-circle-2"></i>
                <span>Harga transparan & terjangkau</span>
            </div>
            <a href="https://wa.me/6285318766057?text=Halo%20AC%20Service%20Pro,%20saya%20ingin%20konsultasi%20servis%20AC" target="_blank" class="hotline-btn-modern">
                <i data-lucide="phone-call"></i>
                <span>Hotline : 085318766057 • Hubungi WhatsApp</span>
            </a>
        </div>
    </div>
</div>

<!-- 5. STATUS OPERASIONAL & PERFORMA BISNIS -->
<div class="card" style="padding:16px 20px; margin-bottom:24px;">
    <div style="font-size:0.82rem; font-weight:700; color:var(--text-muted); text-transform:uppercase; margin-bottom:12px;">
        Status Operasional Antrean Work Order
    </div>
    <div style="display:flex; flex-wrap:wrap; gap:8px;">
        <a href="/work-orders?status=BARU" class="badge badge-BARU" style="padding:6px 12px; font-size:0.75rem; text-decoration:none; white-space:nowrap;">
            Baru Masuk: <strong><?= $woNew ?></strong>
        </a>
        <a href="/work-orders?status=DIKONFIRMASI" class="badge badge-DIKONFIRMASI" style="padding:6px 12px; font-size:0.75rem; text-decoration:none; white-space:nowrap;">
            Dikonfirmasi: <strong><?= $woConfirmed ?></strong>
        </a>
        <a href="/work-orders?status=DIJADWALKAN" class="badge badge-DIJADWALKAN" style="padding:6px 12px; font-size:0.75rem; text-decoration:none; white-space:nowrap;">
            Terjadwal: <strong><?= $woScheduled ?></strong>
        </a>
        <a href="/work-orders?status=SEDANG_DIKERJAKAN" class="badge badge-SEDANG_DIKERJAKAN" style="padding:6px 12px; font-size:0.75rem; text-decoration:none; white-space:nowrap;">
            Sedang Jalan: <strong><?= $woInProgress ?></strong>
        </a>
        <a href="/work-orders?status=SELESAI" class="badge badge-SELESAI" style="padding:6px 12px; font-size:0.75rem; text-decoration:none; white-space:nowrap;">
            Selesai Hari Ini: <strong><?= $woCompleted ?></strong>
        </a>
        <a href="/work-orders?status=DITUNDA" class="badge badge-DITUNDA" style="padding:6px 12px; font-size:0.75rem; text-decoration:none; white-space:nowrap;">
            Tertunda: <strong><?= $woPending ?></strong>
        </a>
    </div>
</div>

<!-- 6. GRID: TREN KEUANGAN & PERFORMA TEKNISI -->
<div class="dashboard-grid-2col">
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
        <div style="position:relative; height:240px; width:100%; max-width:100%; overflow:hidden;">
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
        <div style="display:flex; flex-direction:column; gap:10px;">
            <?php if (empty($technicianLeaderboard)): ?>
                <div style="text-align:center; padding:20px; color:var(--text-muted); font-size:0.85rem;">Belum ada data teknisi.</div>
            <?php else: ?>
                <?php foreach ($technicianLeaderboard as $idx => $t): ?>
                    <div style="display:flex; align-items:center; justify-content:space-between; padding:8px 12px; background:var(--surface-alt); border-radius:var(--radius-sm); min-width:0; gap:8px;">
                        <div style="display:flex; align-items:center; gap:10px; min-width:0; overflow:hidden;">
                            <div style="width:26px; height:26px; border-radius:50%; background:<?= $idx === 0 ? '#fef08a' : '#e2e8f0' ?>; color:<?= $idx === 0 ? '#854d0e' : '#475569' ?>; display:flex; align-items:center; justify-content:center; font-weight:700; font-size:0.75rem; flex-shrink:0;">
                                <?= $idx + 1 ?>
                            </div>
                            <div style="min-width:0; overflow:hidden;">
                                <div style="font-weight:700; font-size:0.85rem; color:var(--primary); white-space:nowrap; overflow:hidden; text-overflow:ellipsis;"><?= htmlspecialchars($t['full_name']) ?></div>
                                <div style="font-size:0.72rem; color:var(--text-muted); white-space:nowrap; overflow:hidden; text-overflow:ellipsis;"><?= htmlspecialchars($t['code']) ?> &bull; <?= htmlspecialchars($t['work_area']) ?></div>
                            </div>
                        </div>
                        <div style="text-align:right; flex-shrink:0;">
                            <div style="font-weight:700; font-size:0.82rem; color:var(--success);">⭐ <?= number_format((float)$t['rating'], 1) ?></div>
                            <div style="font-size:0.7rem; color:var(--text-muted);"><?= (int)$t['total_jobs_done'] ?> servis</div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Chart initialization -->
<script>
    document.addEventListener('DOMContentLoaded', () => {
        // 1. Weekly Orders Spline Chart
        const weeklyCtx = document.getElementById('weeklyOrdersChart');
        if (weeklyCtx && typeof Chart !== 'undefined') {
            const ctx2d = weeklyCtx.getContext('2d');
            const gradient = ctx2d.createLinearGradient(0, 0, 0, 240);
            gradient.addColorStop(0, 'rgba(37, 99, 235, 0.28)');
            gradient.addColorStop(1, 'rgba(37, 99, 235, 0.0)');

            new Chart(weeklyCtx, {
                type: 'line',
                data: {
                    labels: <?= json_encode($weeklyDays ?? ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min']) ?>,
                    datasets: [{
                        label: 'Pesanan',
                        data: <?= json_encode($weeklyCounts ?? [8, 10, 12, 11, 18, 14, 10]) ?>,
                        borderColor: '#2563eb',
                        backgroundColor: gradient,
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4,
                        pointBackgroundColor: '#2563eb',
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 2,
                        pointRadius: 4,
                        pointHoverRadius: 6
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
                                    return context.parsed.y + ' Pesanan';
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: { color: 'rgba(226, 232, 240, 0.6)' },
                            ticks: { font: { size: 10 } }
                        },
                        x: {
                            grid: { display: false },
                            ticks: { font: { size: 11, weight: '600' } }
                        }
                    }
                }
            });
        }

        // 2. Popular Services Donut Chart
        const donutCtx = document.getElementById('popularServicesChart');
        if (donutCtx && typeof Chart !== 'undefined') {
            new Chart(donutCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Cuci AC', 'Isi Freon', 'Service Ringan', 'Service Berat'],
                    datasets: [{
                        data: [45, 30, 15, 10],
                        backgroundColor: ['#2563eb', '#16a34a', '#f59e0b', '#8b5cf6'],
                        borderWidth: 2,
                        borderColor: '#ffffff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '72%',
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return context.label + ': ' + context.parsed + '%';
                                }
                            }
                        }
                    }
                }
            });
        }

        // 3. Revenue Trend Chart
        const revCtx = document.getElementById('revenueChart');
        if (revCtx && typeof Chart !== 'undefined') {
            new Chart(revCtx, {
                type: 'line',
                data: {
                    labels: <?= json_encode($chartLabels ?? []) ?>,
                    datasets: [
                        {
                            label: 'Pendapatan (Rp)',
                            data: <?= json_encode($chartIncome ?? []) ?>,
                            borderColor: '#2563eb',
                            backgroundColor: 'rgba(37, 99, 235, 0.1)',
                            borderWidth: 2.5,
                            fill: true,
                            tension: 0.35
                        },
                        {
                            label: 'Pengeluaran (Rp)',
                            data: <?= json_encode($chartExpenses ?? []) ?>,
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
