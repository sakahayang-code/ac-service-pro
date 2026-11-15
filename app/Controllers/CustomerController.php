<?php

declare(strict_types=1);

namespace App\Controllers;

use PDO;
use App\Services\Auth;
use App\Services\Database;
use App\Services\DocumentSequenceService;

class CustomerController
{
    public function index(): void
    {
        $pdo = Database::getConnection();
        $search = trim($_GET['q'] ?? '');
        $category = trim($_GET['category'] ?? '');

        $sql = "SELECT c.*, COUNT(u.id) as unit_count
                FROM customers c
                LEFT JOIN ac_units u ON c.id = u.customer_id AND u.deleted_at IS NULL
                WHERE c.deleted_at IS NULL";
        $params = [];

        if ($search !== '') {
            $sql .= " AND (c.name LIKE :search OR c.customer_code LIKE :search OR c.phone LIKE :search OR c.address LIKE :search)";
            $params[':search'] = '%' . $search . '%';
        }

        if ($category !== '') {
            $sql .= " AND c.category = :category";
            $params[':category'] = $category;
        }

        $sql .= " GROUP BY c.id ORDER BY c.id DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);

        view('admin.customers.index', [
            'title' => 'Manajemen Pelanggan',
            'customers' => $customers,
            'search' => $search,
            'category' => $category,
        ]);
    }

    public function create(): void
    {
        view('admin.customers.create', [
            'title' => 'Tambah Pelanggan Baru',
        ]);
    }

    public function store(): void
    {
        $name = trim($_POST['name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $category = trim($_POST['category'] ?? 'household');
        $address = trim($_POST['address'] ?? '');
        $latitude = !empty($_POST['latitude']) ? (float)$_POST['latitude'] : null;
        $longitude = !empty($_POST['longitude']) ? (float)$_POST['longitude'] : null;
        $notes = trim($_POST['notes'] ?? '');

        if (empty($name) || empty($phone) || empty($address)) {
            $_SESSION['flash_error'] = 'Nama, nomor telepon, dan alamat wajib diisi.';
            redirect('customers/create');
            return;
        }

        $pdo = Database::getConnection();
        $customerCode = DocumentSequenceService::generate('CUST');

        $stmt = $pdo->prepare("
            INSERT INTO customers (customer_code, category, name, phone, email, address, latitude, longitude, notes, status)
            VALUES (:code, :cat, :name, :phone, :email, :addr, :lat, :lng, :notes, 'active')
        ");
        $stmt->execute([
            ':code' => $customerCode,
            ':cat' => $category,
            ':name' => $name,
            ':phone' => $phone,
            ':email' => $email ?: null,
            ':addr' => $address,
            ':lat' => $latitude,
            ':lng' => $longitude,
            ':notes' => $notes ?: null,
        ]);

        $customerId = (int)$pdo->lastInsertId();

        // Create default address
        $pdo->prepare("
            INSERT INTO customer_addresses (customer_id, label, recipient_name, phone, address, latitude, longitude, is_default)
            VALUES (:cid, 'Alamat Utama', :name, :phone, :addr, :lat, :lng, 1)
        ")->execute([
            ':cid' => $customerId,
            ':name' => $name,
            ':phone' => $phone,
            ':addr' => $address,
            ':lat' => $latitude,
            ':lng' => $longitude,
        ]);

        audit_log('create', 'customers', $customerId, null, ['name' => $name, 'code' => $customerCode]);
        $_SESSION['flash_success'] = "Pelanggan {$name} ({$customerCode}) berhasil ditambahkan!";
        redirect('customers/' . $customerId);
    }

    public function show(string $id): void
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM customers WHERE id = :id AND deleted_at IS NULL LIMIT 1");
        $stmt->execute([':id' => (int)$id]);
        $customer = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$customer) {
            redirect('customers');
            return;
        }

        // AC Units
        $stmtUnits = $pdo->prepare("SELECT * FROM ac_units WHERE customer_id = :cid AND deleted_at IS NULL ORDER BY id DESC");
        $stmtUnits->execute([':cid' => (int)$id]);
        $units = $stmtUnits->fetchAll(PDO::FETCH_ASSOC);

        // Work Orders History
        $stmtWo = $pdo->prepare("
            SELECT wo.*, u.brand, u.capacity_pk
            FROM work_orders wo
            LEFT JOIN ac_units u ON wo.ac_unit_id = u.id
            WHERE wo.customer_id = :cid AND wo.deleted_at IS NULL
            ORDER BY wo.id DESC
        ");
        $stmtWo->execute([':cid' => (int)$id]);
        $orders = $stmtWo->fetchAll(PDO::FETCH_ASSOC);

        // Invoices History
        $stmtInv = $pdo->prepare("SELECT * FROM invoices WHERE customer_id = :cid ORDER BY id DESC");
        $stmtInv->execute([':cid' => (int)$id]);
        $invoices = $stmtInv->fetchAll(PDO::FETCH_ASSOC);

        view('admin.customers.show', [
            'title' => 'Detail Pelanggan - ' . $customer['name'],
            'customer' => $customer,
            'units' => $units,
            'orders' => $orders,
            'invoices' => $invoices,
        ]);
    }

    public function edit(string $id): void
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM customers WHERE id = :id AND deleted_at IS NULL LIMIT 1");
        $stmt->execute([':id' => (int)$id]);
        $customer = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$customer) {
            redirect('customers');
            return;
        }

        view('admin.customers.edit', [
            'title' => 'Ubah Data Pelanggan - ' . $customer['name'],
            'customer' => $customer,
        ]);
    }

    public function update(string $id): void
    {
        $pdo = Database::getConnection();
        $name = trim($_POST['name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $category = trim($_POST['category'] ?? 'household');
        $address = trim($_POST['address'] ?? '');
        $status = trim($_POST['status'] ?? 'active');
        $notes = trim($_POST['notes'] ?? '');

        if (empty($name) || empty($phone) || empty($address)) {
            $_SESSION['flash_error'] = 'Nama, nomor telepon, dan alamat wajib diisi.';
            redirect('customers/' . $id . '/edit');
            return;
        }

        $stmt = $pdo->prepare("
            UPDATE customers
            SET name = :name, phone = :phone, email = :email, category = :cat,
                address = :addr, status = :status, notes = :notes, updated_at = datetime('now', 'localtime')
            WHERE id = :id
        ");
        $stmt->execute([
            ':name' => $name,
            ':phone' => $phone,
            ':email' => $email ?: null,
            ':cat' => $category,
            ':addr' => $address,
            ':status' => $status,
            ':notes' => $notes ?: null,
            ':id' => (int)$id,
        ]);

        audit_log('update', 'customers', (int)$id, null, ['name' => $name]);
        $_SESSION['flash_success'] = 'Data pelanggan berhasil diperbarui!';
        redirect('customers/' . $id);
    }

    public function destroy(string $id): void
    {
        $pdo = Database::getConnection();
        $pdo->prepare("UPDATE customers SET deleted_at = datetime('now', 'localtime') WHERE id = :id")
            ->execute([':id' => (int)$id]);

        audit_log('delete', 'customers', (int)$id);
        $_SESSION['flash_success'] = 'Pelanggan berhasil dinonaktifkan.';
        redirect('customers');
    }

    public function exportCsv(): void
    {
        $pdo = Database::getConnection();
        $customers = $pdo->query("
            SELECT customer_code, name, category, phone, email, address, total_orders, total_spent, status, created_at
            FROM customers WHERE deleted_at IS NULL ORDER BY id DESC
        ")->fetchAll(PDO::FETCH_ASSOC);

        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="data-pelanggan-' . date('Ymd-His') . '.csv"');

        // Output UTF-8 BOM for Excel compatibility
        echo "\xEF\xBB\xBF";
        $out = fopen('php://output', 'w');
        fputcsv($out, ['Kode Pelanggan', 'Nama Lengkap', 'Kategori', 'Telepon / WA', 'Email', 'Alamat', 'Total Order', 'Total Pengeluaran (Rp)', 'Status', 'Tanggal Daftar']);

        foreach ($customers as $row) {
            fputcsv($out, [
                $row['customer_code'],
                $row['name'],
                $row['category'],
                $row['phone'],
                $row['email'] ?? '-',
                $row['address'],
                $row['total_orders'],
                $row['total_spent'],
                $row['status'],
                $row['created_at'],
            ]);
        }
        fclose($out);
        exit;
    }
}
