<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'Terjadi Kesalahan') ?> - AC Service Pro</title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body style="background:var(--background); display:flex; align-items:center; justify-content:center; min-height:100vh; padding:20px;">
    <div class="card" style="max-width:480px; width:100%; text-align:center; padding:36px 24px;">
        <div style="width:64px; height:64px; border-radius:50%; background:#fee2e2; color:#dc2626; display:flex; align-items:center; justify-content:center; margin:0 auto 16px;">
            <i data-lucide="alert-triangle" style="width:32px; height:32px;"></i>
        </div>
        <h1 style="font-size:1.4rem; color:var(--text-main); margin-bottom:8px;"><?= htmlspecialchars($title ?? 'Terjadi Kesalahan') ?> (<?= (int)($code ?? 500) ?>)</h1>
        <p style="color:var(--text-muted); font-size:0.9rem; margin-bottom:24px; line-height:1.5;">
            <?= htmlspecialchars($message ?? 'Halaman yang Anda minta tidak dapat diproses saat ini.') ?>
        </p>
        <div style="display:flex; gap:12px; justify-content:center;">
            <a href="javascript:history.back()" class="btn btn-outline">Kembali</a>
            <a href="/dashboard" class="btn btn-primary">Ke Dashboard</a>
        </div>
    </div>
    <script>
        if (typeof lucide !== 'undefined') lucide.createIcons();
    </script>
</body>
</html>
