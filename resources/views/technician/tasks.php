<?php require dirname(__DIR__) . '/layout/header.php'; ?>

<!-- Technician Mobile Top Bar -->
<div class="card" style="background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%); color:#ffffff; padding:18px; margin-bottom:16px;">
    <div style="display:flex; justify-content:space-between; align-items:center;">
        <div style="display:flex; align-items:center; gap:12px;">
            <div style="width:44px; height:44px; border-radius:50%; background:var(--accent); color:var(--primary-dark); display:flex; align-items:center; justify-content:center; font-weight:800; font-size:1.1rem;">
                <?= strtoupper(substr($technician['full_name'] ?? 'T', 0, 1)) ?>
            </div>
            <div>
                <h2 style="font-size:1.15rem; color:#ffffff; font-weight:700;"><?= htmlspecialchars($technician['full_name'] ?? 'Teknisi') ?></h2>
                <div style="font-size:0.75rem; color:var(--accent);">
                    <?= htmlspecialchars($technician['code'] ?? 'TEK-001') ?> &bull; ⭐ <?= number_format((float)($technician['rating'] ?? 5.0), 1) ?>
                </div>
            </div>
        </div>
        <div style="text-align:right;">
            <div style="font-size:1.2rem; font-weight:800; color:#ffffff;"><?= count($tasks) ?></div>
            <div style="font-size:0.7rem; color:#cbd5e1;">Tugas Aktif</div>
        </div>
    </div>
</div>

<!-- Offline Cache Helper Bar -->
<div class="card" style="padding:12px 16px; margin-bottom:16px; background:#eff6ff; border-color:#bfdbfe; display:flex; justify-content:space-between; align-items:center;">
    <div style="display:flex; align-items:center; gap:8px;">
        <i data-lucide="cloud" style="width:18px; height:18px; color:var(--secondary);"></i>
        <span style="font-size:0.8rem; font-weight:600; color:var(--primary);">Mode Offline Tersedia</span>
    </div>
    <button type="button" class="btn btn-outline btn-sm" style="background:#ffffff; font-size:0.75rem; padding:4px 10px;" onclick="cacheAllTasksLocally()">
        <i data-lucide="download" style="width:14px; height:14px;"></i> Simpan di HP
    </button>
</div>

<!-- Task Cards List -->
<div style="display:flex; flex-direction:column; gap:14px;">
    <?php if (empty($tasks)): ?>
        <div class="card" style="text-align:center; padding:36px 20px;">
            <div style="width:56px; height:56px; border-radius:50%; background:var(--success-light); color:var(--success); display:flex; align-items:center; justify-content:center; margin:0 auto 12px;">
                <i data-lucide="check-circle-2" style="width:32px; height:32px;"></i>
            </div>
            <h3 style="font-size:1.1rem; color:var(--primary); font-weight:700;">Semua Tugas Selesai!</h3>
            <p style="color:var(--text-muted); font-size:0.85rem; margin-top:4px;">
                Tidak ada antrian pekerjaan aktif saat ini. Anda dapat beristirahat atau memeriksa riwayat pekerjaan selesai.
            </p>
            <a href="/technician/history" class="btn btn-outline btn-sm" style="margin-top:14px;">Lihat Riwayat Servis</a>
        </div>
    <?php else: ?>
        <?php foreach ($tasks as $task): ?>
            <div class="card" style="margin-bottom:0; border-left: 5px solid <?= $task['status'] === 'SEDANG_DIKERJAKAN' ? '#d97706' : ($task['status'] === 'DALAM_PERJALANAN' ? '#7c3aed' : '#2563eb') ?>;">
                <!-- Card Header -->
                <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:8px;">
                    <div>
                        <span class="badge badge-<?= $task['status'] ?>" style="font-size:0.75rem;">
                            <?= str_replace('_', ' ', $task['status']) ?>
                        </span>
                        <?php if ($task['priority'] === 'emergency'): ?>
                            <span class="badge badge-red" style="font-size:0.7rem;">DARURAT</span>
                        <?php endif; ?>
                    </div>
                    <strong style="color:var(--primary); font-size:0.9rem;"><?= htmlspecialchars($task['order_number']) ?></strong>
                </div>

                <!-- Customer & Schedule -->
                <h3 style="font-size:1.1rem; color:var(--text-main); font-weight:800; margin-bottom:2px;">
                    <?= htmlspecialchars($task['customer_name']) ?>
                </h3>
                <div style="font-size:0.8rem; color:var(--secondary); font-weight:700; margin-bottom:8px;">
                    <i data-lucide="clock" style="width:13px; height:13px; display:inline-block; vertical-align:middle;"></i>
                    <?= format_date($task['scheduled_date']) ?> &bull; Pukul <?= htmlspecialchars($task['scheduled_time']) ?> WIB
                </div>

                <!-- AC Unit & Complaints -->
                <div style="background:var(--surface-alt); padding:10px; border-radius:var(--radius-sm); font-size:0.85rem; margin-bottom:12px;">
                    <div style="font-weight:700; color:var(--primary);">
                        ❄️ <?= htmlspecialchars($task['ac_brand'] ?? 'Unit AC Pelanggan') ?> (<?= htmlspecialchars($task['capacity_pk'] ?? '1 PK') ?>)
                        <?php if (!empty($task['room_location'])): ?>
                            &bull; Lokasi: <?= htmlspecialchars($task['room_location']) ?>
                        <?php endif; ?>
                    </div>
                    <div style="color:var(--text-muted); font-size:0.8rem; margin-top:4px;">
                        <strong>Keluhan:</strong> <?= htmlspecialchars($task['complaints']) ?>
                    </div>
                </div>

                <!-- Address -->
                <div style="font-size:0.8rem; color:var(--text-main); margin-bottom:14px; display:flex; align-items:flex-start; gap:6px;">
                    <i data-lucide="map-pin" style="width:16px; height:16px; color:var(--danger); flex-shrink:0; margin-top:2px;"></i>
                    <span><?= htmlspecialchars($task['customer_address']) ?></span>
                </div>

                <!-- Actions Button Row -->
                <div style="display:flex; gap:8px; flex-wrap:wrap;">
                    <a href="https://maps.google.com/?q=<?= urlencode($task['customer_address']) ?>" target="_blank" class="btn btn-outline btn-sm" style="flex:1; min-width:100px;">
                        <i data-lucide="navigation" style="width:15px; height:15px; color:var(--secondary);"></i> Peta
                    </a>
                    <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $task['customer_phone']) ?>?text=Halo%20<?= urlencode($task['customer_name']) ?>,%20saya%20teknisi%20AC%20Service%20Pro%20yang%20akan%20menuju%20lokasi%20Anda." target="_blank" class="btn btn-outline btn-sm" style="flex:1; min-width:110px; color:#16a34a; border-color:#bbf7d0;">
                        <i data-lucide="message-circle" style="width:15px; height:15px;"></i> WhatsApp
                    </a>
                    <a href="/technician/tasks/<?= $task['id'] ?>" class="btn btn-primary btn-sm" style="flex:2; min-width:140px;">
                        <i data-lucide="play" style="width:15px; height:15px;"></i>
                        <span><?= $task['status'] === 'SEDANG_DIKERJAKAN' ? 'Lanjutkan Servis' : 'Buka Pengerjaan' ?></span>
                    </a>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<script>
    async function cacheAllTasksLocally() {
        if (!window.OfflineDB) {
            alert('Offline DB belum aktif');
            return;
        }
        const tasks = <?= json_encode($tasks) ?>;
        for (const t of tasks) {
            await window.OfflineDB.saveTask(t);
        }
        window.showToast(`${tasks.length} pekerjaan berhasil disimpan di memori HP untuk dibuka saat offline!`, 'success');
    }
</script>

<?php require dirname(__DIR__) . '/layout/footer.php'; ?>
