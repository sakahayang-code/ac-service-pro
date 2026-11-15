<?php
view('layout.header', ['title' => $title]);
view('layout.sidebar');
?>

<div class="content-wrapper">
    <div class="page-header d-flex justify-content-between align-items-center mb-4">
        <div>
            <div class="text-muted small">
                <a href="<?= url('settings') ?>" class="text-decoration-none">Pengaturan</a> / Pengguna
            </div>
            <h1 class="h3 font-weight-bold text-navy mb-0">Manajemen Pengguna & Hak Akses (RBAC)</h1>
            <p class="text-muted small mb-0">Kelola akun staf, teknisi, kasir, dan penugasan peran keamanan sistem</p>
        </div>
        <button type="button" class="btn btn-primary" onclick="openAddUserModal()">
            <i data-lucide="user-plus" class="icon-sm me-1"></i> Tambah Pengguna Baru
        </button>
    </div>

    <!-- Users Table -->
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Nama Pengguna</th>
                        <th>Peran (Role)</th>
                        <th>Email Login</th>
                        <th>No. Telepon / WA</th>
                        <th>Status Akun</th>
                        <th>Login Terakhir</th>
                        <th class="text-center">Aksi Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $u): ?>
                        <tr>
                            <td><?= $u['id'] ?></td>
                            <td class="fw-bold text-navy">
                                <?= htmlspecialchars($u['name']) ?>
                                <?php if ($u['id'] === (int)auth_user()['id']): ?>
                                    <span class="badge bg-primary-subtle text-primary ms-1">Anda</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php
                                $roleBadge = match ($u['role_name']) {
                                    'owner' => 'bg-danger',
                                    'admin' => 'bg-primary',
                                    'customer_service' => 'bg-info text-dark',
                                    'cashier' => 'bg-success',
                                    'technician' => 'bg-warning text-dark',
                                    'inventory' => 'bg-secondary',
                                    default => 'bg-light text-dark border'
                                };
                                ?>
                                <span class="badge <?= $roleBadge ?>"><?= htmlspecialchars($u['role_display_name'] ?? $u['role_name']) ?></span>
                            </td>
                            <td><?= htmlspecialchars($u['email']) ?></td>
                            <td><?= htmlspecialchars($u['phone'] ?? '-') ?></td>
                            <td>
                                <?php if ($u['status'] === 'active'): ?>
                                    <span class="badge bg-success-subtle text-success">AKTIF</span>
                                <?php else: ?>
                                    <span class="badge bg-danger-subtle text-danger">NONAKTIF</span>
                                <?php endif; ?>
                            </td>
                            <td><small class="text-muted"><?= !empty($u['last_login_at']) ? format_datetime($u['last_login_at']) : 'Belum pernah' ?></small></td>
                            <td class="text-center">
                                <?php if ($u['id'] !== (int)auth_user()['id']): ?>
                                    <form action="<?= url('users/' . $u['id'] . '/toggle') ?>" method="POST" onsubmit="return confirm('Ubah status pengguna ini?')">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn-action <?= $u['status'] === 'active' ? 'btn-action-danger' : 'btn-action-view' ?>">
                                            <span><?= $u['status'] === 'active' ? 'Nonaktifkan' : 'Aktifkan' ?></span>
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <span class="text-muted small">-</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Tambah Pengguna Baru -->
<div class="modal fade" id="addUserModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form action="<?= url('users') ?>" method="POST" class="modal-content">
            <?= csrf_field() ?>
            <div class="modal-header">
                <h5 class="modal-title fw-bold text-navy">Tambah Pengguna Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Nama Lengkap <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" placeholder="Contoh: Rian Pratama" required>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Email Login <span class="text-danger">*</span></label>
                    <input type="email" name="email" class="form-control" placeholder="Contoh: rian@acservicepro.id" required>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Nomor WhatsApp / HP <span class="text-danger">*</span></label>
                    <input type="tel" name="phone" class="form-control" placeholder="Contoh: 081234567890" required>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Peran (Role Akses) <span class="text-danger">*</span></label>
                    <select name="role_id" class="form-select" required>
                        <?php foreach ($roles as $r): ?>
                            <option value="<?= $r['id'] ?>" <?= $r['name'] === 'technician' ? 'selected' : '' ?>>
                                <?= htmlspecialchars($r['display_name']) ?> (<?= htmlspecialchars($r['name']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="form-text">Jika memilih Teknisi, profil teknisi lapangan akan otomatis dibuatkan.</div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Kata Sandi (Password) <span class="text-danger">*</span></label>
                    <input type="password" name="password" class="form-control" placeholder="Minimal 8 karakter" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Pengguna</button>
            </div>
        </form>
    </div>
</div>

<script>
function openAddUserModal() {
    const modal = new bootstrap.Modal(document.getElementById('addUserModal'));
    modal.show();
}
</script>

<?php view('layout.footer'); ?>
