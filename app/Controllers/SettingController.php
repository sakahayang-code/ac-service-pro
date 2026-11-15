<?php

declare(strict_types=1);

namespace App\Controllers;

use PDO;
use App\Services\Auth;
use App\Services\Database;
use App\Services\DocumentSequenceService;

class SettingController
{
    public function index(): void
    {
        $pdo = Database::getConnection();
        $settingsRaw = $pdo->query("SELECT * FROM settings ORDER BY group_name ASC, id ASC")->fetchAll(PDO::FETCH_ASSOC);

        $settings = [];
        foreach ($settingsRaw as $s) {
            $settings[$s['group_name']][] = $s;
        }

        // Database stats
        $dbPath = app_config('database.sqlite_path', dirname(__DIR__, 2) . '/storage/database/ac_service_pro.db');
        $dbSize = file_exists($dbPath) ? filesize($dbPath) : 0;
        $walPath = $dbPath . '-wal';
        $walSize = file_exists($walPath) ? filesize($walPath) : 0;

        $journalMode = $pdo->query("PRAGMA journal_mode")->fetchColumn();
        $foreignKeys = $pdo->query("PRAGMA foreign_keys")->fetchColumn();
        $busyTimeout = $pdo->query("PRAGMA busy_timeout")->fetchColumn();

        // Backups list
        $backupDir = dirname(__DIR__, 2) . '/storage/backups';
        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }
        $backupFiles = [];
        $files = glob($backupDir . '/*.sqlite');
        if ($files) {
            foreach ($files as $f) {
                $backupFiles[] = [
                    'filename' => basename($f),
                    'path' => $f,
                    'size' => filesize($f),
                    'created_at' => date('Y-m-d H:i:s', filemtime($f)),
                ];
            }
            usort($backupFiles, fn($a, $b) => strcmp($b['created_at'], $a['created_at']));
        }

        view('admin.settings.index', [
            'title' => 'Pengaturan Sistem & Database',
            'settings' => $settings,
            'dbSize' => $dbSize,
            'walSize' => $walSize,
            'journalMode' => $journalMode,
            'foreignKeys' => $foreignKeys,
            'busyTimeout' => $busyTimeout,
            'backupFiles' => $backupFiles,
        ]);
    }

    public function update(): void
    {
        $pdo = Database::getConnection();
        $postedSettings = $_POST['settings'] ?? [];

        if (!empty($postedSettings) && is_array($postedSettings)) {
            $stmt = $pdo->prepare("UPDATE settings SET setting_value = :val, updated_at = datetime('now', 'localtime') WHERE setting_key = :key");
            foreach ($postedSettings as $key => $val) {
                $stmt->execute([':val' => trim((string)$val), ':key' => $key]);
            }
            audit_log('update', 'settings', 0, null, ['updated_keys' => array_keys($postedSettings)]);
        }

        $_SESSION['flash_success'] = 'Konfigurasi sistem AC Service Pro berhasil disimpan!';
        redirect('settings');
    }

    public function usersIndex(): void
    {
        $pdo = Database::getConnection();
        $users = $pdo->query("
            SELECT u.*, r.name as role_name, r.display_name as role_display_name
            FROM users u
            JOIN roles r ON u.role_id = r.id
            WHERE u.deleted_at IS NULL
            ORDER BY u.id ASC
        ")->fetchAll(PDO::FETCH_ASSOC);

        $roles = $pdo->query("SELECT * FROM roles ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);

        view('admin.settings.users', [
            'title' => 'Manajemen Pengguna & Hak Akses (RBAC)',
            'users' => $users,
            'roles' => $roles,
        ]);
    }

    public function storeUser(): void
    {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $roleId = (int)($_POST['role_id'] ?? 0);
        $password = trim($_POST['password'] ?? '');

        if (empty($name) || empty($email) || empty($phone) || $roleId <= 0 || empty($password)) {
            $_SESSION['flash_error'] = 'Lengkapi seluruh data pengguna dengan benar.';
            redirect('users');
            return;
        }

        $pdo = Database::getConnection();

        // Check email duplicate
        $exists = $pdo->prepare("SELECT id FROM users WHERE email = :email AND deleted_at IS NULL");
        $exists->execute([':email' => $email]);
        if ($exists->fetchColumn()) {
            $_SESSION['flash_error'] = "Email {$email} sudah digunakan oleh pengguna lain.";
            redirect('users');
            return;
        }

        Database::transaction(function (PDO $pdo) use ($name, $email, $phone, $roleId, $password) {
            $passwordHash = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $pdo->prepare("
                INSERT INTO users (role_id, name, email, phone, password_hash, status)
                VALUES (:rid, :name, :email, :phone, :hash, 'active')
            ");
            $stmt->execute([
                ':rid' => $roleId,
                ':name' => $name,
                ':email' => $email,
                ':phone' => $phone,
                ':hash' => $passwordHash,
            ]);
            $userId = (int)$pdo->lastInsertId();

            // Check if role is technician
            $roleName = $pdo->query("SELECT name FROM roles WHERE id = $roleId")->fetchColumn();
            if ($roleName === 'technician') {
                $techCode = 'T-' . str_pad((string)$userId, 3, '0', STR_PAD_LEFT);
                $stmtTech = $pdo->prepare("
                    INSERT INTO technicians (user_id, code, full_name, phone, work_area, rating, status)
                    VALUES (:uid, :code, :name, :phone, 'Jabodetabek', 5.0, 'active')
                ");
                $stmtTech->execute([
                    ':uid' => $userId,
                    ':code' => $techCode,
                    ':name' => $name,
                    ':phone' => $phone,
                ]);
            }

            audit_log('create', 'users', $userId, null, ['email' => $email, 'role_id' => $roleId]);
        });

        $_SESSION['flash_success'] = "Pengguna {$name} ({$email}) berhasil ditambahkan ke sistem!";
        redirect('users');
    }

    public function toggleUserStatus(string $id): void
    {
        $userId = (int)$id;
        if ($userId === (int)Auth::id()) {
            $_SESSION['flash_error'] = 'Anda tidak dapat menonaktifkan akun yang sedang digunakan.';
            redirect('users');
            return;
        }

        $pdo = Database::getConnection();
        $user = $pdo->query("SELECT id, name, status FROM users WHERE id = $userId")->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            $newStatus = ($user['status'] === 'active') ? 'inactive' : 'active';
            $pdo->prepare("UPDATE users SET status = :st, updated_at = datetime('now', 'localtime') WHERE id = :id")
                ->execute([':st' => $newStatus, ':id' => $userId]);

            audit_log('toggle_status', 'users', $userId, ['status' => $user['status']], ['status' => $newStatus]);
            $_SESSION['flash_success'] = "Status pengguna {$user['name']} berhasil diubah menjadi " . strtoupper($newStatus);
        }

        redirect('users');
    }

    public function backupDb(): void
    {
        $dbPath = app_config('database.sqlite_path', dirname(__DIR__, 2) . '/storage/database/ac_service_pro.db');
        $backupDir = dirname(__DIR__, 2) . '/storage/backups';
        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }

        $backupFileName = 'ac_service_pro_backup_' . date('Ymd_His') . '.sqlite';
        $backupFullPath = $backupDir . '/' . $backupFileName;

        // Perform safe WAL checkpoint then copy
        $pdo = Database::getConnection();
        $pdo->exec("PRAGMA wal_checkpoint(TRUNCATE)");

        // Vacuum into or copy
        if (!copy($dbPath, $backupFullPath)) {
            $_SESSION['flash_error'] = 'Gagal membuat cadangan database.';
            redirect('settings');
            return;
        }

        audit_log('backup_database', 'system', 0, null, ['file' => $backupFileName]);

        if (isset($_GET['download']) && $_GET['download'] === '1') {
            header('Content-Type: application/x-sqlite3');
            header('Content-Disposition: attachment; filename="' . $backupFileName . '"');
            header('Content-Length: ' . filesize($backupFullPath));
            readfile($backupFullPath);
            exit;
        }

        $_SESSION['flash_success'] = "Cadangan database {$backupFileName} (" . round(filesize($backupFullPath) / 1024, 1) . " KB) berhasil dibuat!";
        redirect('settings');
    }

    public function restoreDb(): void
    {
        if (empty($_FILES['backup_file']['tmp_name'])) {
            $_SESSION['flash_error'] = 'Pilih file backup SQLite (.sqlite / .db) yang valid.';
            redirect('settings');
            return;
        }

        $tmpPath = $_FILES['backup_file']['tmp_name'];
        $handle = fopen($tmpPath, 'rb');
        $header = fread($handle, 16);
        fclose($handle);

        if ($header !== "SQLite format 3\000") {
            $_SESSION['flash_error'] = 'File yang diunggah bukan file SQLite yang valid.';
            redirect('settings');
            return;
        }

        $dbPath = app_config('database.sqlite_path', dirname(__DIR__, 2) . '/storage/database/ac_service_pro.db');
        
        // Checkpoint existing and replace
        $pdo = Database::getConnection();
        $pdo->exec("PRAGMA wal_checkpoint(TRUNCATE)");

        if (copy($tmpPath, $dbPath)) {
            // Remove lingering WAL and SHM
            @unlink($dbPath . '-wal');
            @unlink($dbPath . '-shm');
            audit_log('restore_database', 'system', 0, null, ['uploaded_file' => $_FILES['backup_file']['name']]);
            $_SESSION['flash_success'] = 'Database SQLite berhasil dipulihkan dari cadangan!';
        } else {
            $_SESSION['flash_error'] = 'Gagal mengganti file database utama.';
        }

        redirect('settings');
    }

    public function notificationsIndex(): void
    {
        $pdo = Database::getConnection();
        $userId = Auth::id() ?? 1;

        $notifications = $pdo->prepare("
            SELECT * FROM notifications
            WHERE user_id = :uid
            ORDER BY is_read ASC, id DESC
            LIMIT 50
        ");
        $notifications->execute([':uid' => $userId]);
        $items = $notifications->fetchAll(PDO::FETCH_ASSOC);

        view('admin.settings.notifications', [
            'title' => 'Pusat Notifikasi & Pengingat',
            'notifications' => $items,
        ]);
    }

    public function markNotificationRead(string $id): void
    {
        $pdo = Database::getConnection();
        $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE id = :id AND user_id = :uid")
            ->execute([':id' => (int)$id, ':uid' => Auth::id()]);

        $redirectUrl = $_POST['redirect_url'] ?? 'notifications';
        redirect($redirectUrl);
    }
}
