<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <title>Daftar Pelanggan - AC Service Pro</title>
    <link rel="manifest" href="/manifest.json">
    <link rel="icon" type="image/png" href="/assets/icons/favicon.png">
    <link rel="stylesheet" href="/assets/css/style.css">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        .register-bg {
            min-height: 100vh;
            background: linear-gradient(135deg, #091a30 0%, #0f2a4a 50%, #1e3a8a 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px 16px;
        }
        .register-card {
            background: #ffffff;
            border-radius: var(--radius-lg);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            max-width: 500px;
            width: 100%;
            padding: 32px 28px;
        }
    </style>
</head>
<body class="register-bg">
    <div class="register-card">
        <div style="text-align:center; margin-bottom:20px;">
            <div style="width:52px; height:52px; background:var(--primary); color:var(--accent); border-radius:14px; margin:0 auto 10px; display:flex; align-items:center; justify-content:center;">
                <i data-lucide="user-plus" style="width:28px; height:28px;"></i>
            </div>
            <h1 style="font-size:1.35rem; color:var(--primary); font-weight:800;">Pendaftaran Pelanggan Baru</h1>
            <p style="color:var(--text-muted); font-size:0.85rem;">Pesan servis AC lebih mudah & lacak garansi secara online</p>
        </div>

        <?php if (!empty($error)): ?>
            <div style="background:#fef2f2; border:1px solid #fecaca; color:#991b1b; padding:10px 14px; border-radius:var(--radius-sm); font-size:0.85rem; margin-bottom:18px;">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="/register">
            <?= csrf_field() ?>
            <div class="form-group">
                <label class="form-label" for="name">Nama Lengkap *</label>
                <input type="text" id="name" name="name" class="form-control" required placeholder="Contoh: Bpk. Bambang Sutrisno" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="phone">Nomor WhatsApp / HP *</label>
                    <input type="tel" id="phone" name="phone" class="form-control" required placeholder="08123456789" value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label class="form-label" for="email">Alamat Email *</label>
                    <input type="email" id="email" name="email" class="form-control" required placeholder="nama@email.com" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="category">Kategori Pelanggan</label>
                <select id="category" name="category" class="form-select">
                    <option value="household">Rumah Tangga / Tempat Tinggal</option>
                    <option value="office">Perkantoran / Bisnis</option>
                    <option value="shop">Ruko / Toko</option>
                    <option value="school">Sekolah / Kampus</option>
                    <option value="hotel">Hotel / Penginapan</option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label" for="address">Alamat Lengkap Kunjungan *</label>
                <textarea id="address" name="address" class="form-control" rows="2" required placeholder="Nama jalan, nomor rumah, RT/RW, kelurahan, kecamatan"><?= htmlspecialchars($_POST['address'] ?? '') ?></textarea>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="password">Kata Sandi *</label>
                    <input type="password" id="password" name="password" class="form-control" required minlength="6" placeholder="Minimal 6 karakter">
                </div>
                <div class="form-group">
                    <label class="form-label" for="password_confirmation">Ulangi Sandi *</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" required placeholder="Ketik ulang kata sandi">
                </div>
            </div>

            <button type="submit" class="btn btn-primary btn-block btn-lg" style="margin-top:10px;">
                <i data-lucide="check" style="width:18px; height:18px;"></i>
                <span>Daftar Sekarang</span>
            </button>
        </form>

        <div style="margin-top:18px; text-align:center; font-size:0.85rem; color:var(--text-muted);">
            Sudah punya akun? <a href="/login" style="color:var(--secondary); font-weight:600;">Masuk di sini</a>
        </div>
    </div>
    <script>
        if (typeof lucide !== 'undefined') lucide.createIcons();
    </script>
</body>
</html>
