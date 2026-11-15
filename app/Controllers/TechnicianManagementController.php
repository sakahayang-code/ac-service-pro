<?php

declare(strict_types=1);

namespace App\Controllers;

use PDO;
use App\Services\Auth;
use App\Services\Database;
use App\Services\DocumentSequenceService;

class TechnicianManagementController
{
    public function index(): void
    {
        $pdo = Database::getConnection();
        $technicians = $pdo->query("
            SELECT t.*, u.name as user_name, u.email as user_email
            FROM technicians t
            JOIN users u ON t.user_id = u.id
            WHERE t.deleted_at IS NULL
            ORDER BY t.rating DESC, t.total_jobs_done DESC
        ")->fetchAll(PDO::FETCH_ASSOC);

        view('admin.technicians.index', [
            'title' => 'Manajemen Teknisi & Jadwal',
            'technicians' => $technicians,
        ]);
    }

    public function create(): void
    {
        view('admin.technicians.create', [
            'title' => 'Tambah Teknisi Baru',
        ]);
    }

    public function store(): void
    {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $password = (string)($_POST['password'] ?? 'Password123!');
        $skills = trim($_POST['skills'] ?? '');
        $certifications = trim($_POST['certifications'] ?? '');
        $workArea = trim($_POST['work_area'] ?? 'Jabodetabek');
        $commissionRate = (float)($_POST['commission_rate_percent'] ?? 20.0);

        if (empty($name) || empty($email) || empty($phone)) {
            $_SESSION['flash_error'] = 'Nama, email, dan nomor telepon wajib diisi.';
            redirect('technicians/create');
            return;
        }

        $pdo = Database::getConnection();

        // Check user existence
        $check = $pdo->prepare("SELECT id FROM users WHERE email = :e OR phone = :p");
        $check->execute([':e' => $email, ':p' => $phone]);
        if ($check->fetch()) {
            $_SESSION['flash_error'] = 'Email atau Nomor HP sudah terdaftar di sistem.';
            redirect('technicians/create');
            return;
        }

        Database::transaction(function (PDO $pdo) use ($name, $email, $phone, $password, $skills, $certifications, $workArea, $commissionRate) {
            $techRoleId = (int)$pdo->query("SELECT id FROM roles WHERE name = 'technician'")->fetchColumn();
            $hash = password_hash($password, PASSWORD_BCRYPT);

            $stmtUser = $pdo->prepare("
                INSERT INTO users (role_id, name, email, phone, password_hash, status)
                VALUES (:rid, :name, :email, :phone, :hash, 'active')
            ");
            $stmtUser->execute([
                ':rid' => $techRoleId,
                ':name' => $name,
                ':email' => $email,
                ':phone' => $phone,
                ':hash' => $hash,
            ]);
            $userId = (int)$pdo->lastInsertId();

            $nextTechNum = (int)$pdo->query("SELECT count(*) FROM technicians")->fetchColumn() + 1;
            $code = 'TEK-' . str_pad((string)$nextTechNum, 3, '0', STR_PAD_LEFT);

            $stmtTech = $pdo->prepare("
                INSERT INTO technicians (user_id, code, full_name, phone, skills, certifications, work_area, commission_rate_percent, status)
                VALUES (:uid, :code, :name, :phone, :skills, :certs, :area, :comm, 'active')
            ");
            $stmtTech->execute([
                ':uid' => $userId,
                ':code' => $code,
                ':name' => $name,
                ':phone' => $phone,
                ':skills' => $skills ?: null,
                ':certs' => $certifications ?: null,
                ':area' => $workArea,
                ':comm' => $commissionRate,
            ]);
        });

        $_SESSION['flash_success'] = "Teknisi {$name} berhasil ditambahkan!";
        redirect('technicians');
    }

    public function show(string $id): void
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            SELECT t.*, u.name as user_name, u.email as user_email
            FROM technicians t
            JOIN users u ON t.user_id = u.id
            WHERE t.id = :id
            LIMIT 1
        ");
        $stmt->execute([':id' => (int)$id]);
        $technician = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$technician) {
            redirect('technicians');
            return;
        }

        // Assigned Work Orders
        $stmtWo = $pdo->prepare("
            SELECT wo.*, c.name as customer_name, c.phone as customer_phone, wot.is_lead, wot.commission_amount
            FROM work_orders wo
            JOIN work_order_technicians wot ON wo.id = wot.work_order_id
            JOIN customers c ON wo.customer_id = c.id
            WHERE wot.technician_id = :tid AND wo.deleted_at IS NULL
            ORDER BY wo.scheduled_date DESC
            LIMIT 20
        ");
        $stmtWo->execute([':tid' => (int)$id]);
        $orders = $stmtWo->fetchAll(PDO::FETCH_ASSOC);

        // Reviews for this technician
        $stmtRev = $pdo->prepare("
            SELECT r.*, c.name as customer_name, wo.order_number
            FROM customer_reviews r
            JOIN customers c ON r.customer_id = c.id
            JOIN work_orders wo ON r.work_order_id = wo.id
            WHERE r.technician_id = :tid
            ORDER BY r.id DESC
        ");
        $stmtRev->execute([':tid' => (int)$id]);
        $reviews = $stmtRev->fetchAll(PDO::FETCH_ASSOC);

        view('admin.technicians.show', [
            'title' => 'Profil Teknisi - ' . $technician['full_name'],
            'technician' => $technician,
            'orders' => $orders,
            'reviews' => $reviews,
        ]);
    }
}
