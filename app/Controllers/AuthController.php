<?php

declare(strict_types=1);

namespace App\Controllers;

use PDO;
use App\Services\Auth;
use App\Services\Database;
use App\Services\DocumentSequenceService;

class AuthController
{
    public function showLoginForm(): void
    {
        if (Auth::check()) {
            redirect('dashboard');
        }
        view('auth.login');
    }

    public function login(): void
    {
        $token = $_POST['_csrf_token'] ?? '';
        if (!validate_csrf($token)) {
            view('auth.login', ['error' => 'Sesi kedaluwarsa atau token keamanan tidak valid. Silakan coba lagi.']);
            return;
        }

        $email = trim($_POST['email'] ?? '');
        $password = (string)($_POST['password'] ?? '');
        $remember = !empty($_POST['remember']);

        if (empty($email) || empty($password)) {
            view('auth.login', ['error' => 'Email/nomor telepon dan kata sandi wajib diisi.']);
            return;
        }

        if (Auth::attempt($email, $password, $remember)) {
            $user = Auth::user();
            $_SESSION['flash_success'] = 'Selamat datang kembali, ' . ($user['name'] ?? 'Pengguna') . '!';
            
            $intended = $_SESSION['intended_url'] ?? null;
            unset($_SESSION['intended_url']);

            if ($intended && !str_contains($intended, 'login')) {
                header('Location: ' . $intended);
                exit;
            }

            // Redirect based on role
            if ($user['role_name'] === 'technician') {
                redirect('technician/tasks');
            } elseif ($user['role_name'] === 'customer') {
                redirect('customer-portal/orders');
            } else {
                redirect('dashboard');
            }
        }

        view('auth.login', ['error' => 'Kombinasi email/nomor HP dan kata sandi tidak cocok.']);
    }

    public function showRegisterForm(): void
    {
        if (Auth::check()) {
            redirect('dashboard');
        }
        view('auth.register');
    }

    public function register(): void
    {
        $token = $_POST['_csrf_token'] ?? '';
        if (!validate_csrf($token)) {
            view('auth.register', ['error' => 'Token keamanan tidak valid.']);
            return;
        }

        $name = trim($_POST['name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = (string)($_POST['password'] ?? '');
        $passwordConfirm = (string)($_POST['password_confirmation'] ?? '');
        $category = trim($_POST['category'] ?? 'household');
        $address = trim($_POST['address'] ?? '');

        if (empty($name) || empty($phone) || empty($email) || empty($password) || empty($address)) {
            view('auth.register', ['error' => 'Mohon lengkapi semua kolom wajib bertanda bintang (*).']);
            return;
        }

        if ($password !== $passwordConfirm) {
            view('auth.register', ['error' => 'Konfirmasi kata sandi tidak cocok.']);
            return;
        }

        if (strlen($password) < 6) {
            view('auth.register', ['error' => 'Kata sandi minimal 6 karakter.']);
            return;
        }

        $pdo = Database::getConnection();

        // Check if email or phone already exists
        $checkStmt = $pdo->prepare("SELECT id FROM users WHERE email = :email OR phone = :phone");
        $checkStmt->execute([':email' => $email, ':phone' => $phone]);
        if ($checkStmt->fetch()) {
            view('auth.register', ['error' => 'Email atau Nomor HP sudah terdaftar. Silakan langsung masuk.']);
            return;
        }

        try {
            $customerRoleId = (int)$pdo->query("SELECT id FROM roles WHERE name = 'customer'")->fetchColumn();
            $customerCode = DocumentSequenceService::generate('CUST');

            Database::transaction(function (PDO $pdo) use ($customerRoleId, $name, $email, $phone, $password, $customerCode, $category, $address) {
                $pwdHash = password_hash($password, PASSWORD_BCRYPT);
                $stmtUser = $pdo->prepare("
                    INSERT INTO users (role_id, name, email, phone, password_hash, status)
                    VALUES (:role_id, :name, :email, :phone, :hash, 'active')
                ");
                $stmtUser->execute([
                    ':role_id' => $customerRoleId,
                    ':name' => $name,
                    ':email' => $email,
                    ':phone' => $phone,
                    ':hash' => $pwdHash,
                ]);
                $userId = (int)$pdo->lastInsertId();

                $stmtCust = $pdo->prepare("
                    INSERT INTO customers (user_id, customer_code, category, name, phone, email, address, status)
                    VALUES (:user_id, :code, :category, :name, :phone, :email, :address, 'active')
                ");
                $stmtCust->execute([
                    ':user_id' => $userId,
                    ':code' => $customerCode,
                    ':category' => $category,
                    ':name' => $name,
                    ':phone' => $phone,
                    ':email' => $email,
                    ':address' => $address,
                ]);
                $custId = (int)$pdo->lastInsertId();

                $stmtAddr = $pdo->prepare("
                    INSERT INTO customer_addresses (customer_id, label, recipient_name, phone, address, is_default)
                    VALUES (:cust_id, 'Alamat Utama', :name, :phone, :address, 1)
                ");
                $stmtAddr->execute([
                    ':cust_id' => $custId,
                    ':name' => $name,
                    ':phone' => $phone,
                    ':address' => $address,
                ]);

                Auth::loginById($userId);
            });

            $_SESSION['flash_success'] = 'Pendaftaran berhasil! Selamat datang di Portal Pelanggan AC Service Pro.';
            redirect('customer-portal/orders');
        } catch (\Throwable $e) {
            error_log('Register error: ' . $e->getMessage());
            view('auth.register', ['error' => 'Terjadi kendala saat mendaftarkan akun: ' . $e->getMessage()]);
        }
    }

    public function logout(): void
    {
        Auth::logout();
        redirect('login');
    }

    public function switchRole(): void
    {
        $role = trim($_GET['role'] ?? '');
        $roleEmailMap = [
            'owner' => 'owner@acservicepro.id',
            'admin' => 'admin@acservicepro.id',
            'customer_service' => 'cs@acservicepro.id',
            'cashier' => 'kasir@acservicepro.id',
            'technician' => 'teknisi1@acservicepro.id',
            'inventory' => 'gudang@acservicepro.id',
            'customer' => 'pelanggan@gmail.com',
        ];

        if (!isset($roleEmailMap[$role])) {
            redirect('dashboard');
        }

        $email = $roleEmailMap[$role];
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            SELECT u.*, r.name as role_name, r.display_name as role_display_name
            FROM users u
            JOIN roles r ON u.role_id = r.id
            WHERE u.email = :email
            LIMIT 1
        ");
        $stmt->execute([':email' => $email]);
        $targetUser = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($targetUser) {
            Auth::login($targetUser);
            $_SESSION['flash_success'] = "Beralih peran aktif ke: {$targetUser['role_display_name']}";
        }

        if ($role === 'technician') {
            redirect('technician/tasks');
        } elseif ($role === 'customer') {
            redirect('customer-portal/orders');
        } else {
            redirect('dashboard');
        }
    }
}
