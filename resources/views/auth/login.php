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
        .login-wrapper {
            min-height: 100vh;
            background: radial-gradient(circle at 80% 20%, rgba(6, 182, 212, 0.15), transparent 35%),
                        radial-gradient(circle at 20% 80%, rgba(37, 99, 235, 0.18), transparent 45%),
                        linear-gradient(145deg, #060e1a 0%, #0a192f 50%, #0d233e 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px 16px;
            position: relative;
            overflow: hidden;
        }

        .login-wrapper::before {
            content: "";
            position: absolute;
            inset: 0;
            background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='%23ffffff' fill-opacity='0.02' fill-rule='evenodd'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/svg%3E");
            pointer-events: none;
        }

        .login-card {
            background: #ffffff;
            border-radius: 24px;
            box-shadow: 0 25px 60px -15px rgba(2, 6, 23, 0.4), 0 0 0 1px rgba(255, 255, 255, 0.1);
            max-width: 450px;
            width: 100%;
            padding: 36px 32px;
            position: relative;
            z-index: 1;
            animation: cardAppear 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        }

        @keyframes cardAppear {
            from { opacity: 0; transform: translateY(16px) scale(0.98); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }

        .brand-badge {
            width: 60px;
            height: 60px;
            border-radius: 18px;
            background: linear-gradient(135deg, #06b6d4 0%, #2563eb 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 14px;
            box-shadow: 0 8px 20px rgba(6, 182, 212, 0.35);
            color: #ffffff;
        }

        .brand-badge svg {
            width: 32px;
            height: 32px;
            stroke-width: 2.2;
        }

        .login-header {
            text-align: center;
            margin-bottom: 26px;
        }

        .login-header h1 {
            font-size: 1.45rem;
            color: #0f172a;
            font-weight: 800;
            letter-spacing: -0.02em;
        }

        .login-header p {
            font-size: 0.85rem;
            color: #64748b;
            margin-top: 4px;
            font-weight: 500;
        }

        .input-group {
            position: relative;
            margin-bottom: 18px;
        }

        .input-icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            pointer-events: none;
            width: 18px;
            height: 18px;
        }

        .input-with-icon {
            padding-left: 42px !important;
            height: 48px;
            border-radius: 12px;
            font-size: 0.92rem;
            border: 1.5px solid #e2e8f0;
            transition: all var(--transition-fast);
        }

        .input-with-icon:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.12);
        }

        .btn-submit {
            height: 48px;
            border-radius: 12px;
            font-size: 0.95rem;
            font-weight: 700;
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            color: #ffffff;
            box-shadow: 0 4px 14px rgba(37, 99, 235, 0.35);
            border: none;
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            cursor: pointer;
            transition: all var(--transition-fast);
        }

        .btn-submit:hover {
            box-shadow: 0 6px 18px rgba(37, 99, 235, 0.45);
            transform: translateY(-1px);
        }

        .btn-submit:active {
            transform: scale(0.98);
        }

        /* Role Quick Launcher Grid */
        .role-launcher {
            margin-top: 24px;
            padding-top: 20px;
            border-top: 1px solid #f1f5f9;
        }

        .role-launcher-title {
            font-size: 0.72rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #64748b;
            text-align: center;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }

        .role-buttons-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
        }

        .role-pill-btn {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 9px 12px;
            text-align: left;
            cursor: pointer;
            transition: all var(--transition-fast);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .role-pill-btn:hover {
            background: #eff6ff;
            border-color: #93c5fd;
            transform: translateY(-1px);
        }

        .role-pill-btn .role-emoji {
            font-size: 1.1rem;
            line-height: 1;
        }

        .role-pill-btn .role-meta {
            overflow: hidden;
        }

        .role-pill-btn .role-name {
            font-size: 0.82rem;
            font-weight: 700;
            color: #0f172a;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .role-pill-btn .role-desc {
            font-size: 0.68rem;
            color: #64748b;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .role-pill-btn.full-width {
            grid-column: span 2;
        }
    </style>
</head>
<body class="login-wrapper">
    <div class="login-card">
        <!-- Logo & Brand Header -->
        <div class="login-header">
            <div class="brand-badge">
                <i data-lucide="snowflake"></i>
            </div>
            <h1>AC SERVICE PRO</h1>
            <p>Integrated Air Conditioning Management System</p>
        </div>

        <!-- Flash & Error Alerts -->
        <?php if (!empty($error)): ?>
            <div style="background:#fef2f2; border:1px solid #fecaca; color:#991b1b; padding:12px 16px; border-radius:12px; font-size:0.85rem; margin-bottom:20px; display:flex; align-items:center; gap:10px;">
                <i data-lucide="alert-circle" style="width:18px; height:18px; flex-shrink:0;"></i>
                <span><?= htmlspecialchars($error) ?></span>
            </div>
        <?php endif; ?>

        <?php if (!empty($_SESSION['flash_success'])): ?>
            <div style="background:#f0fdf4; border:1px solid #bbf7d0; color:#166534; padding:12px 16px; border-radius:12px; font-size:0.85rem; margin-bottom:20px; display:flex; align-items:center; gap:10px;">
                <i data-lucide="check-circle" style="width:18px; height:18px; flex-shrink:0;"></i>
                <span><?= htmlspecialchars($_SESSION['flash_success']) ?></span>
            </div>
            <?php unset($_SESSION['flash_success']); ?>
        <?php endif; ?>

        <!-- Login Form -->
        <form method="POST" action="/login">
            <?= csrf_field() ?>
            
            <div class="input-group">
                <i data-lucide="mail" class="input-icon"></i>
                <input type="text" id="email" name="email" class="form-control input-with-icon" placeholder="Email atau Nomor WhatsApp" required value="<?= htmlspecialchars($_POST['email'] ?? 'owner@acservicepro.id') ?>" autocomplete="username">
            </div>

            <div class="input-group" style="margin-bottom:14px;">
                <i data-lucide="lock" class="input-icon"></i>
                <input type="password" id="password" name="password" class="form-control input-with-icon" placeholder="Kata Sandi" required value="Password123!" autocomplete="current-password">
            </div>

            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:22px; font-size:0.84rem;">
                <label style="display:flex; align-items:center; gap:7px; cursor:pointer; color:#475569; font-weight:500;">
                    <input type="checkbox" name="remember" value="1" checked style="accent-color:#2563eb; width:16px; height:16px;">
                    <span>Ingat saya</span>
                </label>
                <a href="#" onclick="alert('Silakan hubungi Administrator untuk mereset kata sandi Anda.')" style="color:#2563eb; font-weight:600;">Lupa Sandi?</a>
            </div>

            <button type="submit" class="btn-submit">
                <i data-lucide="log-in" style="width:19px; height:19px;"></i>
                <span>Masuk ke Dashboard</span>
            </button>
        </form>

        <div style="margin-top:18px; text-align:center; font-size:0.84rem; color:#64748b;">
            Pelanggan baru? <a href="/register" style="color:#2563eb; font-weight:700;">Daftar akun di sini</a>
        </div>

        <!-- Fast 1-Click Role Switcher Testing Grid -->
        <div class="role-launcher">
            <div class="role-launcher-title">
                <i data-lucide="zap" style="width:14px; height:14px; color:#f59e0b;"></i>
                <span>Masuk Cepat Sebagai Akun Contoh:</span>
            </div>
            <div class="role-buttons-grid">
                <button type="button" class="role-pill-btn" onclick="quickFill('owner@acservicepro.id')">
                    <span class="role-emoji">👑</span>
                    <div class="role-meta">
                        <div class="role-name">Owner</div>
                        <div class="role-desc">P&L & Kontrol Penuh</div>
                    </div>
                </button>
                <button type="button" class="role-pill-btn" onclick="quickFill('admin@acservicepro.id')">
                    <span class="role-emoji">⚙️</span>
                    <div class="role-meta">
                        <div class="role-name">Admin</div>
                        <div class="role-desc">Manajemen & SPK</div>
                    </div>
                </button>
                <button type="button" class="role-pill-btn" onclick="quickFill('cs@acservicepro.id')">
                    <span class="role-emoji">🎧</span>
                    <div class="role-meta">
                        <div class="role-name">CS</div>
                        <div class="role-desc">Pemesanan Servis</div>
                    </div>
                </button>
                <button type="button" class="role-pill-btn" onclick="quickFill('kasir@acservicepro.id')">
                    <span class="role-emoji">💰</span>
                    <div class="role-meta">
                        <div class="role-name">Kasir</div>
                        <div class="role-desc">Faktur & Kas Masuk</div>
                    </div>
                </button>
                <button type="button" class="role-pill-btn" onclick="quickFill('teknisi1@acservicepro.id')">
                    <span class="role-emoji">🔧</span>
                    <div class="role-meta">
                        <div class="role-name">Teknisi (Budi)</div>
                        <div class="role-desc">Mobile PWA Lapangan</div>
                    </div>
                </button>
                <button type="button" class="role-pill-btn" onclick="quickFill('gudang@acservicepro.id')">
                    <span class="role-emoji">📦</span>
                    <div class="role-meta">
                        <div class="role-name">Gudang</div>
                        <div class="role-desc">Inventori & PO</div>
                    </div>
                </button>
                <button type="button" class="role-pill-btn full-width" onclick="quickFill('pelanggan@gmail.com')">
                    <span class="role-emoji">👤</span>
                    <div class="role-meta">
                        <div class="role-name">Portal Pelanggan</div>
                        <div class="role-desc">Dr. Hendra Wijaya &bull; Tracking Real-Time</div>
                    </div>
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
