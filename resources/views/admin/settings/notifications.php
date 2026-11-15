<?php
view('layout.header', ['title' => $title]);
view('layout.sidebar');
?>

<div class="content-wrapper">
    <div class="page-header d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 font-weight-bold text-navy mb-0">Pusat Notifikasi & Pengingat</h1>
            <p class="text-muted small mb-0">Pemberitahuan otomatis terkait pesanan baru, batas stok minimum, dan konfirmasi pembayaran</p>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold text-navy">Semua Notifikasi</h5>
            <span class="badge bg-secondary"><?= count($notifications) ?> Notifikasi</span>
        </div>
        <div class="card-body p-0">
            <?php if (empty($notifications)): ?>
                <div class="text-center py-5 text-muted">
                    <div class="rounded-circle bg-light d-inline-flex p-3 mb-3">
                        <i data-lucide="bell-off" class="icon-lg"></i>
                    </div>
                    <h5 class="fw-bold text-navy">Tidak Ada Notifikasi Baru</h5>
                    <p class="small text-muted mb-0">Semua aktivitas sistem telah Anda tinjau.</p>
                </div>
            <?php else: ?>
                <ul class="list-group list-group-flush">
                    <?php foreach ($notifications as $n): ?>
                        <li class="list-group-item p-3 d-flex justify-content-between align-items-center <?= !$n['is_read'] ? 'bg-light' : '' ?>">
                            <div class="d-flex align-items-start gap-3">
                                <div class="rounded-circle p-2 <?= !$n['is_read'] ? 'bg-primary text-white' : 'bg-light text-muted' ?>">
                                    <i data-lucide="<?= $n['type'] === 'stock' ? 'alert-triangle' : ($n['type'] === 'payment' ? 'credit-card' : 'bell') ?>" class="icon-sm"></i>
                                </div>
                                <div>
                                    <div class="fw-bold text-navy <?= !$n['is_read'] ? 'fs-6' : '' ?>">
                                        <?= htmlspecialchars($n['title']) ?>
                                        <?php if (!$n['is_read']): ?>
                                            <span class="badge bg-danger ms-2">Baru</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="text-muted small"><?= htmlspecialchars($n['message']) ?></div>
                                    <div class="text-muted mt-1" style="font-size: 0.75rem;">
                                        <i data-lucide="clock" class="icon-xs me-1"></i> <?= format_datetime($n['created_at']) ?>
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex gap-2 align-items-center">
                                <?php if (!empty($n['link_url'])): ?>
                                    <a href="<?= url($n['link_url']) ?>" class="btn-action btn-action-view">
                                        <i data-lucide="external-link"></i> <span>Buka Tautan</span>
                                    </a>
                                <?php endif; ?>
                                <?php if (!$n['is_read']): ?>
                                    <form action="<?= url('notifications/' . $n['id'] . '/read') ?>" method="POST" style="margin:0;">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="redirect_url" value="notifications">
                                        <button type="submit" class="btn-action btn-action-print" title="Tandai Sudah Dibaca">
                                            <i data-lucide="check"></i> <span>Tandai Dibaca</span>
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php view('layout.footer'); ?>
