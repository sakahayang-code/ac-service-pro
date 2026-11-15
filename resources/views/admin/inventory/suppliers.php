<?php require dirname(__DIR__, 2) . '/layout/header.php'; ?>

<div class="card">
    <div class="card-header">
        <div class="card-title">
            <i data-lucide="truck"></i>
            <span>Daftar Pemasok / Supplier Suku Cadang</span>
        </div>
        <button type="button" class="btn btn-primary btn-sm" onclick="openModal('add-supplier-modal')">
            <i data-lucide="plus-circle" style="width:16px; height:16px;"></i>
            <span>Tambah Supplier</span>
        </button>
    </div>

    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Kode</th>
                    <th>Nama Supplier / Perusahaan</th>
                    <th>Kontak Person</th>
                    <th>Nomor Telepon</th>
                    <th>Email</th>
                    <th>Alamat Lengkap</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($suppliers)): ?>
                    <tr><td colspan="7" style="text-align:center; padding:24px; color:var(--text-muted);">Belum ada data supplier.</td></tr>
                <?php else: ?>
                    <?php foreach ($suppliers as $s): ?>
                        <tr>
                            <td><strong style="color:var(--primary);"><?= htmlspecialchars($s['code']) ?></strong></td>
                            <td><strong style="color:var(--text-main);"><?= htmlspecialchars($s['name']) ?></strong></td>
                            <td><?= htmlspecialchars($s['contact_person'] ?? '-') ?></td>
                            <td>
                                <div><?= htmlspecialchars($s['phone']) ?></div>
                                <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $s['phone']) ?>" target="_blank" style="font-size:0.75rem; color:#16a34a; font-weight:600;">
                                    WhatsApp
                                </a>
                            </td>
                            <td><?= htmlspecialchars($s['email'] ?? '-') ?></td>
                            <td>
                                <div style="max-width:240px; font-size:0.8rem; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                                    <?= htmlspecialchars($s['address'] ?? '-') ?>
                                </div>
                            </td>
                            <td>
                                <span class="badge badge-green">Aktif</span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal: Add Supplier -->
<div id="add-supplier-modal" class="modal-overlay">
    <div class="modal-dialog">
        <div class="modal-header">
            <h3 class="modal-title">Tambah Supplier Baru</h3>
            <button type="button" onclick="closeModal('add-supplier-modal')" style="background:none;border:none;font-size:20px;cursor:pointer;">&times;</button>
        </div>
        <form method="POST" action="/suppliers">
            <?= csrf_field() ?>
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label" for="s_name">Nama Perusahaan / Supplier *</label>
                    <input type="text" id="s_name" name="name" class="form-control" required placeholder="Contoh: PT Mitra Sejuk Makmur">
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="s_cp">Nama Kontak Person</label>
                        <input type="text" id="s_cp" name="contact_person" class="form-control" placeholder="Contoh: Bpk. Suhendra">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="s_phone">Nomor Telepon / WA *</label>
                        <input type="tel" id="s_phone" name="phone" class="form-control" required placeholder="021-xxxx atau 0812xxxx">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label" for="s_email">Alamat Email</label>
                    <input type="email" id="s_email" name="email" class="form-control" placeholder="sales@mitrasejuk.com">
                </div>
                <div class="form-group">
                    <label class="form-label" for="s_address">Alamat Kantor / Gudang</label>
                    <textarea id="s_address" name="address" class="form-control" rows="2" placeholder="Komplek Pergudangan..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('add-supplier-modal')">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Supplier</button>
            </div>
        </form>
    </div>
</div>

<?php require dirname(__DIR__, 2) . '/layout/footer.php'; ?>
