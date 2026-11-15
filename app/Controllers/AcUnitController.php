<?php

declare(strict_types=1);

namespace App\Controllers;

use PDO;
use App\Services\Auth;
use App\Services\Database;
use App\Services\DocumentSequenceService;

class AcUnitController
{
    public function index(): void
    {
        $pdo = Database::getConnection();
        $search = trim($_GET['q'] ?? '');
        $brand = trim($_GET['brand'] ?? '');

        $sql = "SELECT u.*, c.name as customer_name, c.customer_code, c.phone as customer_phone
                FROM ac_units u
                JOIN customers c ON u.customer_id = c.id
                WHERE u.deleted_at IS NULL";
        $params = [];

        if ($search !== '') {
            $sql .= " AND (u.unit_code LIKE :search OR u.brand LIKE :search OR u.model_type LIKE :search OR u.room_location LIKE :search OR c.name LIKE :search)";
            $params[':search'] = '%' . $search . '%';
        }

        if ($brand !== '') {
            $sql .= " AND u.brand = :brand";
            $params[':brand'] = $brand;
        }

        $sql .= " ORDER BY u.id DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $units = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Get distinct brands for filter
        $brands = $pdo->query("SELECT DISTINCT brand FROM ac_units WHERE deleted_at IS NULL ORDER BY brand ASC")->fetchAll(PDO::FETCH_COLUMN);

        view('admin.ac_units.index', [
            'title' => 'Manajemen Unit AC',
            'units' => $units,
            'brands' => $brands,
            'search' => $search,
            'brand' => $brand,
        ]);
    }

    public function create(): void
    {
        $pdo = Database::getConnection();
        $customerId = isset($_GET['customer_id']) ? (int)$_GET['customer_id'] : null;

        $customers = $pdo->query("SELECT id, customer_code, name, phone FROM customers WHERE deleted_at IS NULL ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);

        view('admin.ac_units.create', [
            'title' => 'Daftarkan Unit AC Baru',
            'customers' => $customers,
            'selectedCustomerId' => $customerId,
        ]);
    }

    public function store(): void
    {
        $customerId = (int)($_POST['customer_id'] ?? 0);
        $brand = trim($_POST['brand'] ?? '');
        $modelType = trim($_POST['model_type'] ?? 'Split Wall');
        $capacityPk = trim($_POST['capacity_pk'] ?? '1 PK');
        $serialNumber = trim($_POST['serial_number'] ?? '');
        $refrigerantType = trim($_POST['refrigerant_type'] ?? 'R32');
        $roomLocation = trim($_POST['room_location'] ?? 'Ruang Utama');
        $installationDate = !empty($_POST['installation_date']) ? $_POST['installation_date'] : null;
        $warrantyStatus = trim($_POST['warranty_status'] ?? 'Garansi Toko');
        $condition = trim($_POST['condition'] ?? 'Normal');
        $notes = trim($_POST['notes'] ?? '');

        if ($customerId <= 0 || empty($brand)) {
            $_SESSION['flash_error'] = 'Pelanggan pemilik dan merek AC wajib diisi.';
            redirect('ac-units/create');
            return;
        }

        $pdo = Database::getConnection();
        $unitCode = DocumentSequenceService::generate('AC');

        // Calculate next service due (default 90 days from today)
        $nextServiceDue = date('Y-m-d', strtotime('+90 days'));

        $stmt = $pdo->prepare("
            INSERT INTO ac_units (
                customer_id, unit_code, brand, model_type, capacity_pk, serial_number,
                refrigerant_type, room_location, installation_date, warranty_status, condition,
                notes, next_service_due
            ) VALUES (
                :cid, :code, :brand, :model, :pk, :serial,
                :freon, :loc, :inst_date, :warr, :cond,
                :notes, :next_due
            )
        ");
        $stmt->execute([
            ':cid' => $customerId,
            ':code' => $unitCode,
            ':brand' => $brand,
            ':model' => $modelType,
            ':pk' => $capacityPk,
            ':serial' => $serialNumber ?: null,
            ':freon' => $refrigerantType,
            ':loc' => $roomLocation,
            ':inst_date' => $installationDate,
            ':warr' => $warrantyStatus,
            ':cond' => $condition,
            ':notes' => $notes ?: null,
            ':next_due' => $nextServiceDue,
        ]);

        $unitId = (int)$pdo->lastInsertId();

        // Create initial service reminder entry
        $pdo->prepare("
            INSERT INTO service_reminders (customer_id, ac_unit_id, due_date, reminder_type, status)
            VALUES (:cid, :uid, :due, 'routine_wash', 'pending')
        ")->execute([
            ':cid' => $customerId,
            ':uid' => $unitId,
            ':due' => $nextServiceDue,
        ]);

        audit_log('create', 'ac_units', $unitId, null, ['code' => $unitCode, 'brand' => $brand]);
        $_SESSION['flash_success'] = "Unit AC {$brand} ({$unitCode}) berhasil didaftarkan!";
        redirect('ac-units/' . $unitId);
    }

    public function show(string $id): void
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            SELECT u.*, c.name as customer_name, c.customer_code, c.phone as customer_phone, c.address as customer_address
            FROM ac_units u
            JOIN customers c ON u.customer_id = c.id
            WHERE u.id = :id AND u.deleted_at IS NULL
            LIMIT 1
        ");
        $stmt->execute([':id' => (int)$id]);
        $unit = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$unit) {
            redirect('ac-units');
            return;
        }

        // Full Work Order / Service History for this unit
        $stmtWo = $pdo->prepare("
            SELECT wo.*, u.name as technician_name
            FROM work_orders wo
            LEFT JOIN work_order_technicians wot ON wo.id = wot.work_order_id AND wot.is_lead = 1
            LEFT JOIN technicians t ON wot.technician_id = t.id
            LEFT JOIN users u ON t.user_id = u.id
            WHERE wo.ac_unit_id = :uid AND wo.deleted_at IS NULL
            ORDER BY wo.id DESC
        ");
        $stmtWo->execute([':uid' => (int)$id]);
        $serviceHistory = $stmtWo->fetchAll(PDO::FETCH_ASSOC);

        view('admin.ac_units.show', [
            'title' => 'Detail Unit AC - ' . $unit['unit_code'],
            'unit' => $unit,
            'serviceHistory' => $serviceHistory,
        ]);
    }

    public function edit(string $id): void
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM ac_units WHERE id = :id AND deleted_at IS NULL LIMIT 1");
        $stmt->execute([':id' => (int)$id]);
        $unit = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$unit) {
            redirect('ac-units');
            return;
        }

        $customers = $pdo->query("SELECT id, name FROM customers WHERE deleted_at IS NULL ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);

        view('admin.ac_units.edit', [
            'title' => 'Ubah Data Unit AC - ' . $unit['unit_code'],
            'unit' => $unit,
            'customers' => $customers,
        ]);
    }

    public function update(string $id): void
    {
        $pdo = Database::getConnection();
        $brand = trim($_POST['brand'] ?? '');
        $modelType = trim($_POST['model_type'] ?? 'Split Wall');
        $capacityPk = trim($_POST['capacity_pk'] ?? '1 PK');
        $serialNumber = trim($_POST['serial_number'] ?? '');
        $refrigerantType = trim($_POST['refrigerant_type'] ?? 'R32');
        $roomLocation = trim($_POST['room_location'] ?? 'Ruang Utama');
        $condition = trim($_POST['condition'] ?? 'Normal');
        $warrantyStatus = trim($_POST['warranty_status'] ?? '');
        $notes = trim($_POST['notes'] ?? '');

        if (empty($brand)) {
            $_SESSION['flash_error'] = 'Merek AC wajib diisi.';
            redirect('ac-units/' . $id . '/edit');
            return;
        }

        $stmt = $pdo->prepare("
            UPDATE ac_units
            SET brand = :brand, model_type = :model, capacity_pk = :pk, serial_number = :serial,
                refrigerant_type = :freon, room_location = :loc, condition = :cond,
                warranty_status = :warr, notes = :notes, updated_at = datetime('now', 'localtime')
            WHERE id = :id
        ");
        $stmt->execute([
            ':brand' => $brand,
            ':model' => $modelType,
            ':pk' => $capacityPk,
            ':serial' => $serialNumber ?: null,
            ':freon' => $refrigerantType,
            ':loc' => $roomLocation,
            ':cond' => $condition,
            ':warr' => $warrantyStatus,
            ':notes' => $notes ?: null,
            ':id' => (int)$id,
        ]);

        audit_log('update', 'ac_units', (int)$id, null, ['brand' => $brand]);
        $_SESSION['flash_success'] = 'Data unit AC berhasil diperbarui!';
        redirect('ac-units/' . $id);
    }

    public function destroy(string $id): void
    {
        $pdo = Database::getConnection();
        $pdo->prepare("UPDATE ac_units SET deleted_at = datetime('now', 'localtime') WHERE id = :id")
            ->execute([':id' => (int)$id]);

        audit_log('delete', 'ac_units', (int)$id);
        $_SESSION['flash_success'] = 'Unit AC berhasil dinonaktifkan.';
        redirect('ac-units');
    }
}
