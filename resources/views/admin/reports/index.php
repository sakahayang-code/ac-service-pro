<?php
view('layout.header', ['title' => $title]);
view('layout.sidebar');
?>

<div class="content-wrapper">
    <div class="page-header mb-4">
        <h1 class="h3 font-weight-bold text-navy mb-0">Pusat Laporan & Analisis Bisnis</h1>
        <p class="text-muted small mb-0">Statistik operasional terintegrasi, performa keuangan, teknisi, dan aset inventori</p>
    </div>

    <!-- Monthly Snapshot Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <span class="text-muted small text-uppercase fw-semibold">Total Pekerjaan Bulan Ini</span>
                    <div class="fs-3 fw-bold text-navy mt-1"><?= $totalOrders ?> SPK</div>
                    <small class="text-success fw-semibold"><?= $completedOrders ?> Selesai Dikerjakan</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <span class="text-muted small text-uppercase fw-semibold">Omset Pendapatan Faktur</span>
                    <div class="fs-3 fw-bold text-primary mt-1"><?= format_rupiah($revenue) ?></div>
                    <small class="text-muted">Bulan <?= date('F Y') ?></small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <span class="text-muted small text-uppercase fw-semibold">Estimasi Laba Bersih</span>
                    <div class="fs-3 fw-bold <?= $netProfit >= 0 ? 'text-success' : 'text-danger' ?> mt-1">
                        <?= format_rupiah($netProfit) ?>
                    </div>
                    <small class="text-muted">Setelah HPP & Biaya Rutin</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <span class="text-muted small text-uppercase fw-semibold">Nilai Aset Stok Gudang</span>
                    <div class="fs-3 fw-bold text-navy mt-1"><?= format_rupiah($inventoryValuation) ?></div>
                    <small class="text-muted">Valuasi Harga Beli (HPP)</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Specialized Reports Navigation Grid -->
    <h5 class="fw-bold text-navy mb-3">Pilihan Laporan Spesifik</h5>
    <div class="row g-4">
        <!-- 1. Work Orders -->
        <div class="col-md-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center p-4">
                    <div class="rounded-circle bg-primary-subtle text-primary d-inline-flex p-3 mb-3">
                        <i data-lucide="clipboard-check" class="icon-lg"></i>
                    </div>
                    <h5 class="fw-bold text-navy mb-2">Laporan SPK & Servis</h5>
                    <p class="text-muted small mb-3">Statistik penyelesaian order, pembatalan, persebaran pelanggan, dan durasi kerja.</p>
                    <a href="<?= url('reports/work-orders') ?>" class="btn btn-outline-primary btn-sm w-100">
                        Buka Laporan SPK
                    </a>
                </div>
            </div>
        </div>

        <!-- 2. Finance -->
        <div class="col-md-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center p-4">
                    <div class="rounded-circle bg-success-subtle text-success d-inline-flex p-3 mb-3">
                        <i data-lucide="trending-up" class="icon-lg"></i>
                    </div>
                    <h5 class="fw-bold text-navy mb-2">Laporan Keuangan & Kas</h5>
                    <p class="text-muted small mb-3">Rincian pendapatan jasa vs part, biaya operasional, laba kotor, dan laba bersih.</p>
                    <a href="<?= url('reports/finance') ?>" class="btn btn-outline-success btn-sm w-100">
                        Buka Laporan Keuangan
                    </a>
                </div>
            </div>
        </div>

        <!-- 3. Technician -->
        <div class="col-md-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center p-4">
                    <div class="rounded-circle bg-warning-subtle text-warning d-inline-flex p-3 mb-3">
                        <i data-lucide="users" class="icon-lg"></i>
                    </div>
                    <h5 class="fw-bold text-navy mb-2">Produktivitas Teknisi</h5>
                    <p class="text-muted small mb-3">Leaderboard teknisi, tingkat keberhasilan penyelesaian, komisi, dan rating kepuasan.</p>
                    <a href="<?= url('reports/technician') ?>" class="btn btn-outline-warning text-dark btn-sm w-100">
                        Buka Laporan Teknisi
                    </a>
                </div>
            </div>
        </div>

        <!-- 4. Inventory -->
        <div class="col-md-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center p-4">
                    <div class="rounded-circle bg-info-subtle text-info d-inline-flex p-3 mb-3">
                        <i data-lucide="package" class="icon-lg"></i>
                    </div>
                    <h5 class="fw-bold text-navy mb-2">Stok & Nilai Sparepart</h5>
                    <p class="text-muted small mb-3">Valuasi aset freon dan sparepart, mutasi masuk/keluar, dan peringatan stok minimum.</p>
                    <a href="<?= url('reports/inventory') ?>" class="btn btn-outline-info btn-sm w-100">
                        Buka Laporan Stok
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php view('layout.footer'); ?>
