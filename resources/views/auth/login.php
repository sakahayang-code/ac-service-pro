<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <title>Masuk - AC Service Pro</title>
    <link rel="manifest" href="/manifest.json">
    <link rel="icon" type="image/png" href="/assets/icons/favicon.png">
    <link rel="stylesheet" href="/assets/css/style.css">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        .login-bg {
            min-height: 100vh;
            background: linear-gradient(135deg, #091a30 0%, #0f2a4a 50%, #1e3a8a 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .login-card {
            background: #ffffff;
            border-radius: var(--radius-lg);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            max-width: 440px;
            width: 100%;
            padding: 32px 28px;
        }
        .demo-roles-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
            margin-top: 14px;
        }
        .demo-role-btn {
            background: var(--surface-alt);
            border: 1px solid var(--border);
            padding: 8px 10px;
            border-radius: var(--radius-sm);
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--text-main);
            text-align: left;
            cursor: pointer;
            transition: all 0.15s ease;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .demo-role-btn:hover {
            border-color: var(--secondary);
            background: #eff6ff;
            color: var(--secondary);
        }
    </style>
</head>
<body class="login-bg">
    <div class="login-card">
        <!-- Logo & Branding -->
        <div style="text-align:center; margin-bottom:24px;">
            <div style="width:56px; height:56px; background:var(--primary); color:var(--accent); border-radius:16px; margin:0 auto 12px; display:flex; align-items:center; justify-content:center; box-shadow:0 8px 16px rgba(15,42,74,0.3);">
                <i data-lucide="snowflake" style="width:32px; height:32px;"></i>
            </div>
            <h1 style="font-size:1.4rem; color:var(--primary); font-weight:800;">AC SERVICE PRO</h1>
            <p style="color:var(--text-muted); font-size:0.85rem; margin-top:2px;">Integrated Management System v1.0</p>
        </div>

        <?php if (!empty($error)): ?>
            <div style="background:#fef2f2; border:1px solid #fecaca; color:#991b1b; padding:10px 14px; border-radius:var(--radius-sm); font-size:0.85rem; margin-bottom:18px; display:flex; align-items:center; gap:8px;">
                <i data-lucide="alert-circle" style="width:18px; height:18px; flex-shrink:0;"></i>
                <span><?= htmlspecialchars($error) ?></span>
            </div>
        <?php endif; ?>

        <?php if (!empty($_SESSION['flash_success'])): ?>
            <div style="background:#f0fdf4; border:1px solid #bbf7d0; color:#166534; padding:10px 14px; border-radius:var(--radius-sm); font-size:0.85rem; margin-bottom:18px; display:flex; align-items:center; gap:8px;">
                <i data-lucide="check-circle" style="width:18px; height:18px; flex-shrink:0;"></i>
                <span><?= htmlspecialchars($_SESSION['flash_success']) ?></span>
            </div>
            <?php unset($_SESSION['flash_success']); ?>
        <?php endif; ?>

        <form method="POST" action="/login">
            <?= csrf_field() ?>
            <div class="form-group">
                <label class="form-label" for="email">Email atau Nomor HP</label>
                <input type="text" id="email" name="email" class="form-control" placeholder="nama@email.com atau 08123xxx" required value="<?= htmlspecialchars($_POST['email'] ?? 'owner@acservicepro.id') ?>">
            </div>

            <div class="form-group">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px;">
                    <label class="form-label" for="password" style="margin-bottom:0;">Kata Sandi</label>
                </div>
                <input type="password" id="password" name="password" class="form-control" placeholder="Masukkan kata sandi" required value="Password123!">
            </div>

            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:20px; font-size:0.85rem;">
                <label style="display:flex; align-items:center; gap:6px; cursor:pointer; color:var(--text-muted);">
                    <input type="checkbox" name="remember" value="1" checked>
                    <span>Ingat saya di perangkat ini</span>
                </label>
            </div>

            <button type="submit" class="btn btn-primary btn-block btn-lg" style="box-shadow:0 4px 12px rgba(15,42,74,0.3);">
                <i data-lucide="log-in" style="width:18px; height:18px;"></i>
                <span>Masuk ke Sistem</span>
            </button>
        </form>

        <div style="margin-top:20px; text-align:center; font-size:0.85rem; color:var(--text-muted);">
            Pelanggan baru? <a href="/register" style="color:var(--secondary); font-weight:600;">Daftar akun di sini</a>
        </div>

        <!-- 1-Click Role Switcher Demo Box -->
        <div style="margin-top:24px; padding-top:18px; border-top:1px solid var(--border);">
            <div style="font-size:0.75rem; font-weight:700; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.05em; text-align:center; margin-bottom:8px;">
                ⚡ Masuk Cepat Sebagai Peran:
            </div>
            <div class="demo-roles-grid">
                <button type="button" class="demo-role-btn" onclick="quickFill('owner@acservicepro.id')">
                    <span>👑</span> <span>Owner</span>
                </button>
                <button type="button" class="demo-role-btn" onclick="quickFill('admin@acservicepro.id')">
                    <span>⚙️</span> <span>Admin</span>
                </button>
                <button type="button" class="demo-role-btn" onclick="quickFill('cs@acservicepro.id')">
                    <span>🎧</span> <span>Customer Service</span>
                </button>
                <button type="button" class="demo-role-btn" onclick="quickFill('kasir@acservicepro.id')">
                    <span>💰</span> <span>Kasir</span>
                </button>
                <button type="button" class="demo-role-btn" onclick="quickFill('teknisi1@acservicepro.id')">
                    <span>🔧</span> <span>Teknisi (Budi)</span>
                </button>
                <button type="button" class="demo-role-btn" onclick="quickFill('gudang@acservicepro.id')">
                    <span>📦</span> <span>Gudang</span>
                </button>
                <button type="button" class="demo-role-btn" style="grid-column: span 2;" onclick="quickFill('pelanggan@gmail.com')">
                    <span>👤</span> <span>Pelanggan (Dr. Hendra Wijaya)</span>
                </button>
            </div>
        </div>
    </div>

    <script>
        function quickFill(email) {
            document.getElementById('email').value = email;
            document.getElementById('password').value = 'Password123!';
            document.querySelector('form').submit();
        }
        if (typeof lucide !== 'undefined') lucide.createIcons();
    </script>
</body>
</html>
