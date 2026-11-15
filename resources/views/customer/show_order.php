<?php
view('layout.header', ['title' => $title]);
view('layout.sidebar');

$statusSteps = ['BARU', 'DIKONFIRMASI', 'DIJADWALKAN', 'DALAM_PERJALANAN', 'SEDANG_DIKERJAKAN', 'SELESAI', 'LUNAS'];
$currentStatus = $order['status'];
$currentIndex = array_search($currentStatus, $statusSteps);
if ($currentIndex === false) {
    if ($currentStatus === 'DITAGIHKAN') $currentIndex = 5; // Between SELESAI and LUNAS
    else $currentIndex = 0;
}
?>

<div class="content-wrapper">
    <div class="page-header d-flex justify-content-between align-items-center mb-4">
        <div>
            <div class="text-muted small">
                <a href="<?= url('customer-portal/orders') ?>" class="text-decoration-none">Pesanan Saya</a> / Detail
            </div>
            <h1 class="h3 font-weight-bold text-navy mb-0">Pesanan #<?= htmlspecialchars($order['order_number']) ?></h1>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= url('customer-portal/orders') ?>" class="btn btn-outline-secondary">
                <i data-lucide="arrow-left" class="icon-sm me-1"></i> Kembali
            </a>
            <?php if (!empty($order['invoice_id'])): ?>
                <a href="<?= url('invoices/' . $order['invoice_id'] . '/print') ?>" target="_blank" class="btn btn-outline-primary">
                    <i data-lucide="file-text" class="icon-sm me-1"></i> Lihat Faktur Tagihan
                </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Live Status Tracker Stepper -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4">
            <h5 class="fw-bold text-navy mb-3">Status Pengerjaan Real-time</h5>
            <div class="d-flex justify-content-between align-items-center position-relative flex-wrap gap-2">
                <?php
                $labels = [
                    'BARU' => 'Pesanan Diterima',
                    'DIKONFIRMASI' => 'Dikonfirmasi',
                    'DIJADWALKAN' => 'Dijadwalkan',
                    'DALAM_PERJALANAN' => 'Teknisi Menuju Lokasi',
                    'SEDANG_DIKERJAKAN' => 'Sedang Dikerjakan',
                    'SELESAI' => 'Selesai Servis',
                    'LUNAS' => 'Selesai & Lunas'
                ];
                foreach ($statusSteps as $idx => $st):
                    $isActive = ($idx <= $currentIndex);
                    $isCurrent = ($st === $currentStatus);
                ?>
                    <div class="text-center" style="flex: 1; min-width: 100px;">
                        <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-2 shadow-sm
                            <?= $isCurrent ? 'bg-primary text-white ring-4' : ($isActive ? 'bg-success text-white' : 'bg-light text-muted border') ?>"
                            style="width: 40px; height: 40px; font-weight: bold;">
                            <?= $isActive ? '✓' : ($idx + 1) ?>
                        </div>
                        <div class="small <?= $isCurrent ? 'fw-bold text-primary' : ($isActive ? 'fw-semibold text-navy' : 'text-muted') ?>">
                            <?= $labels[$st] ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Main Column: Order Details & Services -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-bold text-navy">Informasi Pengerjaan & Keluhan</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-sm-4 text-muted">Jadwal Servis:</div>
                        <div class="col-sm-8 fw-semibold text-navy">
                            <?= format_date($order['scheduled_date']) ?> (<?= htmlspecialchars($order['scheduled_time'] ?? '-') ?>)
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4 text-muted">Alamat Kunjungan:</div>
                        <div class="col-sm-8">
                            <i data-lucide="map-pin" class="icon-xs text-danger me-1"></i>
                            <?= nl2br(htmlspecialchars($order['address_text'])) ?>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4 text-muted">Keluhan / Catatan:</div>
                        <div class="col-sm-8 bg-light p-3 rounded">
                            <?= nl2br(htmlspecialchars($order['complaints'])) ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Services Requested -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-bold text-navy">Layanan yang Dipesan</h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Jenis Layanan</th>
                                <th class="text-center">Jumlah</th>
                                <th class="text-end">Estimasi Biaya</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($services)): ?>
                                <tr>
                                    <td colspan="4" class="text-center py-3 text-muted">Servis AC Rutin / Pengecekan</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($services as $idx => $s): ?>
                                    <tr>
                                        <td><?= $idx + 1 ?></td>
                                        <td class="fw-semibold text-navy"><?= htmlspecialchars($s['service_name']) ?></td>
                                        <td class="text-center"><?= (float)$s['quantity'] ?></td>
                                        <td class="text-end fw-bold"><?= format_rupiah($s['subtotal']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Review / Rating Section (If finished or paid) -->
            <?php if (in_array($order['status'], ['SELESAI', 'DITAGIHKAN', 'LUNAS'])): ?>
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0 fw-bold text-navy">Penilaian & Ulasan Layanan</h5>
                    </div>
                    <div class="card-body">
                        <?php if ($review): ?>
                            <div class="alert alert-success d-flex align-items-center gap-3">
                                <div class="fs-1 text-warning">
                                    <?= str_repeat('★', (int)$review['rating']) . str_repeat('☆', 5 - (int)$review['rating']) ?>
                                </div>
                                <div>
                                    <div class="fw-bold">Ulasan Anda Telah Tersimpan:</div>
                                    <div>"<?= htmlspecialchars($review['review_text'] ?: 'Pelayanan memuaskan dan AC dingin kembali!') ?>"</div>
                                    <small class="text-muted">Dikirim pada: <?= format_datetime($review['created_at']) ?></small>
                                </div>
                            </div>
                        <?php else: ?>
                            <form action="<?= url('customer-portal/orders/' . $order['id'] . '/review') ?>" method="POST">
                                <?= csrf_field() ?>
                                <p class="text-muted small mb-3">Bagikan pengalaman Anda menggunakan jasa teknisi AC Service Pro kami untuk membantu kami terus meningkatkan kualitas pelayanan.</p>
                                
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Beri Nilai Bintang <span class="text-danger">*</span></label>
                                    <select name="rating" class="form-select form-select-lg" style="max-width: 250px;" required>
                                        <option value="5">⭐⭐⭐⭐⭐ Sangat Puas (5)</option>
                                        <option value="4">⭐⭐⭐⭐ Puas (4)</option>
                                        <option value="3">⭐⭐⭐ Cukup (3)</option>
                                        <option value="2">⭐⭐ Kurang Puas (2)</option>
                                        <option value="1">⭐ Kecewa (1)</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Tulis Komentar / Ulasan</label>
                                    <textarea name="review_text" class="form-control" rows="3" placeholder="Bagikan komentar Anda mengenai keramahan teknisi, kerapian pengerjaan, dan kondisi AC..."></textarea>
                                </div>

                                <button type="submit" class="btn btn-warning text-navy fw-bold">
                                    <i data-lucide="star" class="icon-sm me-1"></i> Kirim Ulasan
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Sidebar Column: Assigned Technician & Billing Status -->
        <div class="col-lg-4">
            <!-- Technician Profile Card -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-bold text-navy">Teknisi yang Bertugas</h5>
                </div>
                <div class="card-body text-center">
                    <?php if (!empty($order['technician_name'])): ?>
                        <div class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center mb-3 fs-3 fw-bold" style="width: 70px; height: 70px;">
                            <?= strtoupper(substr($order['technician_name'], 0, 1)) ?>
                        </div>
                        <h5 class="fw-bold text-navy mb-1"><?= htmlspecialchars($order['technician_name']) ?></h5>
                        <div class="text-warning small mb-3">
                            ⭐ <?= number_format((float)($order['tech_rating'] ?? 5.0), 1) ?> / 5.0 Teknisi Berpengalaman
                        </div>
                        <?php if (!empty($order['technician_phone'])): ?>
                            <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $order['technician_phone']) ?>?text=<?= urlencode("Halo Mas {$order['technician_name']}, saya {$customer['name']} terkait pesanan servis AC #{$order['order_number']}.") ?>" target="_blank" class="btn btn-success w-100">
                                <i data-lucide="message-circle" class="icon-sm me-1"></i> Hubungi via WhatsApp
                            </a>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="text-muted py-4">
                            <i data-lucide="user-x" class="icon-lg mb-2"></i>
                            <div>Teknisi Belum Ditugaskan</div>
                            <small>Admin sedang mengatur jadwal teknisi terdekat dari lokasi Anda.</small>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Invoice Status Card -->
            <?php if (!empty($order['invoice_id'])): ?>
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0 fw-bold text-navy">Status Faktur Tagihan</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted small">No. Faktur:</span>
                            <span class="fw-bold text-navy">#<?= htmlspecialchars($order['invoice_number']) ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted small">Total Biaya:</span>
                            <span class="fw-bold text-navy"><?= format_rupiah($order['inv_total']) ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted small">Sudah Dibayar:</span>
                            <span class="fw-bold text-success"><?= format_rupiah($order['paid_amount']) ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-3">
                            <span class="text-muted small">Sisa Tagihan:</span>
                            <span class="fw-bold text-danger"><?= format_rupiah($order['balance_due']) ?></span>
                        </div>

                        <?php if ($order['payment_status'] === 'paid'): ?>
                            <div class="badge bg-success w-100 py-2 fs-6">LUNAS</div>
                        <?php else: ?>
                            <div class="badge bg-warning text-dark w-100 py-2 fs-6 mb-2">MENUNGGU PEMBAYARAN</div>
                            <a href="<?= url('invoices/' . $order['invoice_id'] . '/print') ?>" target="_blank" class="btn btn-outline-primary btn-sm w-100">
                                <i data-lucide="printer" class="icon-xs me-1"></i> Cetak / Rekening Pembayaran
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Timeline Activity Logs -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-bold text-navy">Riwayat Perjalanan Pesanan</h5>
                </div>
                <div class="card-body p-3">
                    <ul class="list-unstyled mb-0 position-relative">
                        <?php if (empty($logs)): ?>
                            <li class="text-muted small">Belum ada riwayat aktivitas.</li>
                        <?php else: ?>
                            <?php foreach ($logs as $l): ?>
                                <li class="mb-3 d-flex gap-3 align-items-start">
                                    <div class="rounded-circle bg-primary-subtle text-primary p-1 mt-1">
                                        <i data-lucide="clock" class="icon-xs"></i>
                                    </div>
                                    <div>
                                        <div class="fw-semibold small text-navy"><?= htmlspecialchars(str_replace('_', ' ', strtoupper($l['action']))) ?></div>
                                        <div class="text-muted" style="font-size: 0.75rem;">Oleh: <?= htmlspecialchars($l['user_name'] ?? 'Sistem') ?></div>
                                        <div class="text-muted" style="font-size: 0.7rem;"><?= format_datetime($l['created_at']) ?></div>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<?php view('layout.footer'); ?>
