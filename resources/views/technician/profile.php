<?php require dirname(__DIR__) . '/layout/header.php'; ?>

<!-- Technician Profile Summary Card -->
<div class="card" style="max-width:680px; margin:0 auto 20px;">
    <div style="text-align:center; padding:10px 0 20px;">
        <div style="width:72px; height:72px; border-radius:50%; background:var(--primary); color:var(--accent); font-size:1.8rem; font-weight:800; display:flex; align-items:center; justify-content:center; margin:0 auto 12px; box-shadow:0 6px 16px rgba(15,42,74,0.3);">
            <?= strtoupper(substr($technician['full_name'] ?? 'T', 0, 1)) ?>
        </div>
        <h2 style="font-size:1.35rem; color:var(--primary); font-weight:800;"><?= htmlspecialchars($technician['full_name'] ?? 'Teknisi') ?></h2>
        <div style="color:var(--text-muted); font-size:0.85rem; margin-top:2px;">
            <?= htmlspecialchars($technician['code'] ?? 'TEK-001') ?> &bull; <?= htmlspecialchars($technician['work_area'] ?? 'Jabodetabek') ?>
        </div>
        <div style="margin-top:8px;">
            <span class="badge badge-amber" style="font-size:0.85rem; padding:6px 14px;">
                ⭐ Rating: <?= number_format((float)($technician['rating'] ?? 5.0), 1) ?> / 5.0
            </span>
        </div>
    </div>

    <!-- Earnings Cards -->
    <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:20px;">
        <div style="background:#f0fdf4; border:1px solid #bbf7d0; padding:14px; border-radius:var(--radius-sm); text-align:center;">
            <div style="font-size:0.75rem; color:#166534; font-weight:700; text-transform:uppercase;">Komisi Bulan Ini</div>
            <div style="font-size:1.3rem; font-weight:800; color:#15803d; margin-top:4px;"><?= format_rupiah($thisMonthCommission) ?></div>
        </div>
        <div style="background:#eff6ff; border:1px solid #bfdbfe; padding:14px; border-radius:var(--radius-sm); text-align:center;">
            <div style="font-size:0.75rem; color:#1e40af; font-weight:700; text-transform:uppercase;">Total Komisi Akumulasi</div>
            <div style="font-size:1.3rem; font-weight:800; color:#1d4ed8; margin-top:4px;"><?= format_rupiah($totalCommission) ?></div>
        </div>
    </div>

    <!-- Details -->
    <div style="display:flex; flex-direction:column; gap:10px; font-size:0.85rem; border-top:1px solid var(--border); padding-top:16px;">
        <div style="display:flex; justify-content:space-between; padding:6px 0; border-bottom:1px solid var(--border);">
            <span style="color:var(--text-muted);">Nomor Telepon / WhatsApp:</span>
            <strong><?= htmlspecialchars($technician['phone'] ?? '-') ?></strong>
        </div>
        <div style="display:flex; justify-content:space-between; padding:6px 0; border-bottom:1px solid var(--border);">
            <span style="color:var(--text-muted);">Email Login:</span>
            <strong><?= htmlspecialchars($technician['user_email'] ?? '-') ?></strong>
        </div>
        <div style="display:flex; justify-content:space-between; padding:6px 0; border-bottom:1px solid var(--border);">
            <span style="color:var(--text-muted);">Persentase Komisi:</span>
            <strong><?= number_format((float)($technician['commission_rate_percent'] ?? 20.0), 1) ?>% dari biaya jasa</strong>
        </div>
        <div style="display:flex; justify-content:space-between; padding:6px 0; border-bottom:1px solid var(--border);">
            <span style="color:var(--text-muted);">Pekerjaan Selesai:</span>
            <strong><?= (int)($technician['total_jobs_done'] ?? 0) ?> Servis</strong>
        </div>
        <div style="padding:6px 0;">
            <div style="color:var(--text-muted); margin-bottom:4px;">Keahlian & Sertifikasi:</div>
            <div style="background:var(--surface-alt); padding:8px 12px; border-radius:var(--radius-sm); color:var(--text-main);">
                <?= htmlspecialchars($technician['skills'] ?? 'Servis AC Standar') ?>
                <?php if (!empty($technician['certifications'])): ?>
                    <div style="margin-top:4px; font-size:0.8rem; color:var(--secondary);">📜 <?= htmlspecialchars($technician['certifications']) ?></div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require dirname(__DIR__) . '/layout/footer.php'; ?>
