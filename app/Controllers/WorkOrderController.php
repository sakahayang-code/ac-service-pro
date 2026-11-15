<?php

declare(strict_types=1);

namespace App\Controllers;

use PDO;
use App\Services\Auth;
use App\Services\Database;
use App\Services\DocumentSequenceService;

class WorkOrderController
{
    public function index(): void
    {
        $pdo = Database::getConnection();
        $status = trim($_GET['status'] ?? '');
        $search = trim($_GET['q'] ?? '');
        $date = trim($_GET['date'] ?? '');

        $sql = "SELECT wo.*, c.name as customer_name, c.phone as customer_phone,
                       u.brand as ac_brand, u.model_type as ac_model, u.capacity_pk,
                       t.full_name as lead_technician_name
                FROM work_orders wo
                JOIN customers c ON wo.customer_id = c.id
                LEFT JOIN ac_units u ON wo.ac_unit_id = u.id
                LEFT JOIN work_order_technicians wot ON wo.id = wot.work_order_id AND wot.is_lead = 1
                LEFT JOIN technicians t ON wot.technician_id = t.id
                WHERE wo.deleted_at IS NULL";
        $params = [];

        if ($status !== '') {
            $sql .= " AND wo.status = :status";
            $params[':status'] = $status;
        }

        if ($date !== '') {
            $sql .= " AND wo.scheduled_date = :date";
            $params[':date'] = $date;
        }

        if ($search !== '') {
            $sql .= " AND (wo.order_number LIKE :q OR c.name LIKE :q OR c.phone LIKE :q OR wo.complaints LIKE :q)";
            $params[':q'] = '%' . $search . '%';
        }

        $sql .= " ORDER BY wo.scheduled_date DESC, wo.id DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Counts per status
        $statusCounts = $pdo->query("
            SELECT status, count(*) as cnt
            FROM work_orders
            WHERE deleted_at IS NULL
            GROUP BY status
        ")->fetchAll(PDO::FETCH_KEY_PAIR) ?: [];

        view('admin.work_orders.index', [
            'title' => 'Manajemen Work Order Servis',
            'orders' => $orders,
            'currentStatus' => $status,
            'search' => $search,
            'date' => $date,
            'statusCounts' => $statusCounts,
        ]);
    }

    public function create(): void
    {
        $pdo = Database::getConnection();
        $customerId = isset($_GET['customer_id']) ? (int)$_GET['customer_id'] : null;
        $acUnitId = isset($_GET['ac_unit_id']) ? (int)$_GET['ac_unit_id'] : null;

        $customers = $pdo->query("SELECT id, customer_code, name, phone, address FROM customers WHERE deleted_at IS NULL ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
        $technicians = $pdo->query("SELECT id, code, full_name, work_area FROM technicians WHERE status = 'active' AND deleted_at IS NULL ORDER BY full_name ASC")->fetchAll(PDO::FETCH_ASSOC);
        $services = $pdo->query("SELECT p.*, c.name as category_name FROM service_products p JOIN service_categories c ON p.category_id = c.id WHERE p.is_active = 1 ORDER BY c.sort_order ASC, p.name ASC")->fetchAll(PDO::FETCH_ASSOC);

        // If customer selected, load their units
        $customerUnits = [];
        if ($customerId) {
            $stmtUnits = $pdo->prepare("SELECT * FROM ac_units WHERE customer_id = :cid AND deleted_at IS NULL ORDER BY id DESC");
            $stmtUnits->execute([':cid' => $customerId]);
            $customerUnits = $stmtUnits->fetchAll(PDO::FETCH_ASSOC);
        }

        view('admin.work_orders.create', [
            'title' => 'Buat Work Order Baru',
            'customers' => $customers,
            'technicians' => $technicians,
            'services' => $services,
            'selectedCustomerId' => $customerId,
            'selectedUnitId' => $acUnitId,
            'customerUnits' => $customerUnits,
        ]);
    }

    public function store(): void
    {
        $customerId = (int)($_POST['customer_id'] ?? 0);
        $acUnitId = !empty($_POST['ac_unit_id']) ? (int)$_POST['ac_unit_id'] : null;
        $orderSource = trim($_POST['order_source'] ?? 'admin');
        $priority = trim($_POST['priority'] ?? 'normal');
        $scheduledDate = trim($_POST['scheduled_date'] ?? date('Y-m-d'));
        $scheduledTime = trim($_POST['scheduled_time'] ?? '09:00');
        $addressText = trim($_POST['address_text'] ?? '');
        $complaints = trim($_POST['complaints'] ?? '');
        $leadTechnicianId = !empty($_POST['lead_technician_id']) ? (int)$_POST['lead_technician_id'] : null;
        $serviceIds = $_POST['services'] ?? [];

        if ($customerId <= 0 || empty($scheduledDate) || empty($complaints)) {
            $_SESSION['flash_error'] = 'Pelanggan, tanggal jadwal, dan keluhan pekerjaan wajib diisi.';
            redirect('work-orders/create');
            return;
        }

        $pdo = Database::getConnection();

        // If addressText empty, grab from customer
        if (empty($addressText)) {
            $stmtCust = $pdo->prepare("SELECT address FROM customers WHERE id = :id");
            $stmtCust->execute([':id' => $customerId]);
            $addressText = (string)$stmtCust->fetchColumn();
        }

        $orderNumber = DocumentSequenceService::generate('WO');
        $initialStatus = $leadTechnicianId ? 'DIJADWALKAN' : 'DIKONFIRMASI';

        Database::transaction(function (PDO $pdo) use (
            $orderNumber, $customerId, $acUnitId, $orderSource, $priority, $initialStatus,
            $scheduledDate, $scheduledTime, $addressText, $complaints, $leadTechnicianId, $serviceIds
        ) {
            $creatorId = Auth::id();

            // Insert Work Order
            $stmt = $pdo->prepare("
                INSERT INTO work_orders (
                    order_number, customer_id, ac_unit_id, order_source, priority, status,
                    scheduled_date, scheduled_time, address_text, complaints, created_by
                ) VALUES (
                    :num, :cid, :uid, :src, :prio, :status,
                    :date, :time, :addr, :comp, :by
                )
            ");
            $stmt->execute([
                ':num' => $orderNumber,
                ':cid' => $customerId,
                ':uid' => $acUnitId,
                ':src' => $orderSource,
                ':prio' => $priority,
                ':status' => $initialStatus,
                ':date' => $scheduledDate,
                ':time' => $scheduledTime,
                ':addr' => $addressText,
                ':comp' => $complaints,
                ':by' => $creatorId,
            ]);
            $workOrderId = (int)$pdo->lastInsertId();

            // Calculate estimated price from chosen services
            $totalEstimate = 0;
            if (!empty($serviceIds) && is_array($serviceIds)) {
                $stmtSrv = $pdo->prepare("SELECT id, name, base_price FROM service_products WHERE id = :id");
                $stmtInsertSrv = $pdo->prepare("
                    INSERT INTO work_order_services (work_order_id, service_product_id, service_name, quantity, unit_price, subtotal)
                    VALUES (:wo_id, :srv_id, :name, 1, :price, :price)
                ");

                foreach ($serviceIds as $sid) {
                    $stmtSrv->execute([':id' => (int)$sid]);
                    $srv = $stmtSrv->fetch(PDO::FETCH_ASSOC);
                    if ($srv) {
                        $stmtInsertSrv->execute([
                            ':wo_id' => $workOrderId,
                            ':srv_id' => $srv['id'],
                            ':name' => $srv['name'],
                            ':price' => (int)$srv['base_price'],
                        ]);
                        $totalEstimate += (int)$srv['base_price'];
                    }
                }
            }

            // Update estimated price
            $pdo->prepare("UPDATE work_orders SET estimated_price = :est WHERE id = :id")
                ->execute([':est' => $totalEstimate, ':id' => $workOrderId]);

            // Assign lead technician if selected
            if ($leadTechnicianId) {
                $pdo->prepare("
                    INSERT INTO work_order_technicians (work_order_id, technician_id, is_lead)
                    VALUES (:wo_id, :tid, 1)
                ")->execute([':wo_id' => $workOrderId, ':tid' => $leadTechnicianId]);
            }

            // Seed default checklist items for this work order
            $defaultChecklists = [
                'Pemeriksaan Arus Listrik (Ampere Normal)',
                'Pengecekan Tekanan Freon Standar Pabrik',
                'Pembersihan Filter Udara Indoor',
                'Pembersihan Evaporator & Talang Air (Drainase)',
                'Penyemprotan Bersih Kondensor Outdoor',
                'Uji Coba Fungsi Remote & Suhu Dingin',
            ];
            $stmtChk = $pdo->prepare("
                INSERT INTO service_checklists (work_order_id, checklist_item, status)
                VALUES (:wo_id, :item, 'ok')
            ");
            foreach ($defaultChecklists as $chkItem) {
                $stmtChk->execute([':wo_id' => $workOrderId, ':item' => $chkItem]);
            }

            audit_log('create', 'work_orders', $workOrderId, null, ['order_number' => $orderNumber, 'status' => $initialStatus]);
            $_SESSION['flash_success'] = "Work Order {$orderNumber} berhasil dibuat!";
            redirect('work-orders/' . $workOrderId);
        });
    }

    public function show(string $id): void
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            SELECT wo.*, c.name as customer_name, c.customer_code, c.phone as customer_phone, c.address as customer_full_address,
                   u.brand as ac_brand, u.model_type as ac_model, u.capacity_pk, u.refrigerant_type, u.room_location, u.serial_number as ac_serial
            FROM work_orders wo
            JOIN customers c ON wo.customer_id = c.id
            LEFT JOIN ac_units u ON wo.ac_unit_id = u.id
            WHERE wo.id = :id AND wo.deleted_at IS NULL
            LIMIT 1
        ");
        $stmt->execute([':id' => (int)$id]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$order) {
            redirect('work-orders');
            return;
        }

        // Assigned technicians
        $technicians = $pdo->prepare("
            SELECT wot.*, t.code, t.full_name, t.phone, t.rating, t.work_area
            FROM work_order_technicians wot
            JOIN technicians t ON wot.technician_id = t.id
            WHERE wot.work_order_id = :id
            ORDER BY wot.is_lead DESC
        ");
        $technicians->execute([':id' => (int)$id]);
        $assignedTechnicians = $technicians->fetchAll(PDO::FETCH_ASSOC);

        // Services
        $services = $pdo->prepare("SELECT * FROM work_order_services WHERE work_order_id = :id ORDER BY id ASC");
        $services->execute([':id' => (int)$id]);
        $orderServices = $services->fetchAll(PDO::FETCH_ASSOC);

        // Checklists
        $checklists = $pdo->prepare("SELECT * FROM service_checklists WHERE work_order_id = :id ORDER BY id ASC");
        $checklists->execute([':id' => (int)$id]);
        $orderChecklists = $checklists->fetchAll(PDO::FETCH_ASSOC);

        // Diagnostics
        $diag = $pdo->prepare("SELECT * FROM service_diagnostics WHERE work_order_id = :id ORDER BY id DESC LIMIT 1");
        $diag->execute([':id' => (int)$id]);
        $diagnostics = $diag->fetch(PDO::FETCH_ASSOC);

        // Materials / spareparts used
        $materials = $pdo->prepare("SELECT * FROM service_materials WHERE work_order_id = :id ORDER BY id ASC");
        $materials->execute([':id' => (int)$id]);
        $orderMaterials = $materials->fetchAll(PDO::FETCH_ASSOC);

        // Photos
        $photos = $pdo->prepare("SELECT * FROM service_photos WHERE work_order_id = :id ORDER BY uploaded_at ASC");
        $photos->execute([':id' => (int)$id]);
        $orderPhotos = $photos->fetchAll(PDO::FETCH_ASSOC);

        // Customer Signature
        $sig = $pdo->prepare("SELECT * FROM customer_signatures WHERE work_order_id = :id LIMIT 1");
        $sig->execute([':id' => (int)$id]);
        $signature = $sig->fetch(PDO::FETCH_ASSOC);

        // Available active technicians for assignment
        $availableTechnicians = $pdo->query("SELECT id, code, full_name, work_area FROM technicians WHERE status = 'active' AND deleted_at IS NULL ORDER BY full_name ASC")->fetchAll(PDO::FETCH_ASSOC);

        // Associated Invoice (if any)
        $inv = $pdo->prepare("SELECT id, invoice_number, total_amount, payment_status FROM invoices WHERE work_order_id = :id LIMIT 1");
        $inv->execute([':id' => (int)$id]);
        $invoice = $inv->fetch(PDO::FETCH_ASSOC);

        view('admin.work_orders.show', [
            'title' => 'Work Order - ' . $order['order_number'],
            'order' => $order,
            'assignedTechnicians' => $assignedTechnicians,
            'orderServices' => $orderServices,
            'orderChecklists' => $orderChecklists,
            'diagnostics' => $diagnostics,
            'orderMaterials' => $orderMaterials,
            'orderPhotos' => $orderPhotos,
            'signature' => $signature,
            'availableTechnicians' => $availableTechnicians,
            'invoice' => $invoice,
        ]);
    }

    public function edit(string $id): void
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM work_orders WHERE id = :id AND deleted_at IS NULL LIMIT 1");
        $stmt->execute([':id' => (int)$id]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$order) {
            redirect('work-orders');
            return;
        }

        view('admin.work_orders.edit', [
            'title' => 'Ubah Work Order - ' . $order['order_number'],
            'order' => $order,
        ]);
    }

    public function update(string $id): void
    {
        $pdo = Database::getConnection();
        $scheduledDate = trim($_POST['scheduled_date'] ?? date('Y-m-d'));
        $scheduledTime = trim($_POST['scheduled_time'] ?? '09:00');
        $priority = trim($_POST['priority'] ?? 'normal');
        $complaints = trim($_POST['complaints'] ?? '');
        $notes = trim($_POST['notes'] ?? '');

        $stmt = $pdo->prepare("
            UPDATE work_orders
            SET scheduled_date = :date, scheduled_time = :time, priority = :prio,
                complaints = :comp, notes = :notes, updated_at = datetime('now', 'localtime')
            WHERE id = :id
        ");
        $stmt->execute([
            ':date' => $scheduledDate,
            ':time' => $scheduledTime,
            ':prio' => $priority,
            ':comp' => $complaints,
            ':notes' => $notes ?: null,
            ':id' => (int)$id,
        ]);

        audit_log('update', 'work_orders', (int)$id);
        $_SESSION['flash_success'] = 'Jadwal / keluhan Work Order berhasil diperbarui!';
        redirect('work-orders/' . $id);
    }

    public function updateStatus(string $id): void
    {
        $pdo = Database::getConnection();
        $newStatus = trim($_POST['status'] ?? '');
        $notes = trim($_POST['notes'] ?? '');

        $allowedStatuses = [
            'BARU', 'DIKONFIRMASI', 'DIJADWALKAN', 'DALAM_PERJALANAN',
            'SEDANG_DIKERJAKAN', 'MENUNGGU_SPAREPART', 'SELESAI',
            'DITAGIHKAN', 'LUNAS', 'DITUNDA', 'DIBATALKAN', 'KUNJUNGAN_ULANG'
        ];

        if (!in_array($newStatus, $allowedStatuses, true)) {
            $_SESSION['flash_error'] = 'Status tidak valid.';
            redirect('work-orders/' . $id);
            return;
        }

        $stmt = $pdo->prepare("SELECT status FROM work_orders WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => (int)$id]);
        $oldStatus = $stmt->fetchColumn();

        Database::transaction(function (PDO $pdo) use ($id, $newStatus, $oldStatus, $notes) {
            $sql = "UPDATE work_orders SET status = :status, updated_at = datetime('now', 'localtime')";
            $params = [':status' => $newStatus, ':id' => (int)$id];

            if ($newStatus === 'SEDANG_DIKERJAKAN') {
                $sql .= ", checkin_at = COALESCE(checkin_at, datetime('now', 'localtime'))";
            } elseif ($newStatus === 'SELESAI') {
                $sql .= ", checkout_at = COALESCE(checkout_at, datetime('now', 'localtime'))";
            } elseif ($newStatus === 'DIBATALKAN') {
                $sql .= ", cancellation_reason = :notes";
                $params[':notes'] = $notes ?: 'Dibatalkan oleh admin/pelanggan';
            }

            $sql .= " WHERE id = :id";
            $stmtUpdate = $pdo->prepare($sql);
            $stmtUpdate->execute($params);

            audit_log('status_change', 'work_orders', (int)$id, ['status' => $oldStatus], ['status' => $newStatus, 'notes' => $notes]);
        });

        $_SESSION['flash_success'] = "Status Work Order berhasil diubah menjadi: " . str_replace('_', ' ', $newStatus);
        redirect('work-orders/' . $id);
    }

    public function assignTechnician(string $id): void
    {
        $pdo = Database::getConnection();
        $techId = (int)($_POST['technician_id'] ?? 0);
        $isLead = !empty($_POST['is_lead']) ? 1 : 0;

        if ($techId <= 0) {
            $_SESSION['flash_error'] = 'Pilih teknisi yang akan ditugaskan.';
            redirect('work-orders/' . $id);
            return;
        }

        // If making new lead, clear previous lead status
        if ($isLead) {
            $pdo->prepare("UPDATE work_order_technicians SET is_lead = 0 WHERE work_order_id = :wo_id")
                ->execute([':wo_id' => (int)$id]);
        }

        $stmt = $pdo->prepare("
            INSERT OR REPLACE INTO work_order_technicians (work_order_id, technician_id, is_lead, assigned_at)
            VALUES (:wo_id, :tid, :is_lead, datetime('now', 'localtime'))
        ");
        $stmt->execute([
            ':wo_id' => (int)$id,
            ':tid' => $techId,
            ':is_lead' => $isLead,
        ]);

        // Automatically change status to DIJADWALKAN if was BARU/DIKONFIRMASI
        $pdo->prepare("
            UPDATE work_orders
            SET status = CASE WHEN status IN ('BARU', 'DIKONFIRMASI') THEN 'DIJADWALKAN' ELSE status END,
                updated_at = datetime('now', 'localtime')
            WHERE id = :id
        ")->execute([':id' => (int)$id]);

        $_SESSION['flash_success'] = 'Teknisi berhasil ditugaskan ke Work Order ini!';
        redirect('work-orders/' . $id);
    }

    public function printSpk(string $id): void
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            SELECT wo.*, c.name as customer_name, c.customer_code, c.phone as customer_phone, c.address as customer_address,
                   u.brand as ac_brand, u.model_type as ac_model, u.capacity_pk, u.refrigerant_type, u.room_location, u.serial_number as ac_serial
            FROM work_orders wo
            JOIN customers c ON wo.customer_id = c.id
            LEFT JOIN ac_units u ON wo.ac_unit_id = u.id
            WHERE wo.id = :id AND wo.deleted_at IS NULL
            LIMIT 1
        ");
        $stmt->execute([':id' => (int)$id]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$order) {
            redirect('work-orders');
            return;
        }

        $technicians = $pdo->prepare("
            SELECT t.code, t.full_name, t.phone, wot.is_lead
            FROM work_order_technicians wot
            JOIN technicians t ON wot.technician_id = t.id
            WHERE wot.work_order_id = :id
            ORDER BY wot.is_lead DESC
        ");
        $technicians->execute([':id' => (int)$id]);
        $assignedTechnicians = $technicians->fetchAll(PDO::FETCH_ASSOC);

        $services = $pdo->prepare("SELECT * FROM work_order_services WHERE work_order_id = :id");
        $services->execute([':id' => (int)$id]);
        $orderServices = $services->fetchAll(PDO::FETCH_ASSOC);

        $checklists = $pdo->prepare("SELECT * FROM service_checklists WHERE work_order_id = :id");
        $checklists->execute([':id' => (int)$id]);
        $orderChecklists = $checklists->fetchAll(PDO::FETCH_ASSOC);

        view('admin.work_orders.spk', [
            'title' => 'Surat Perintah Kerja (SPK) - ' . $order['order_number'],
            'order' => $order,
            'technicians' => $assignedTechnicians,
            'services' => $orderServices,
            'checklists' => $orderChecklists,
        ]);
    }

    public function cancel(string $id): void
    {
        $pdo = Database::getConnection();
        $reason = trim($_POST['cancellation_reason'] ?? 'Dibatalkan oleh pihak pelanggan');

        $stmt = $pdo->prepare("
            UPDATE work_orders
            SET status = 'DIBATALKAN', cancellation_reason = :reason, updated_at = datetime('now', 'localtime')
            WHERE id = :id
        ");
        $stmt->execute([':reason' => $reason, ':id' => (int)$id]);

        audit_log('cancel', 'work_orders', (int)$id, null, ['reason' => $reason]);
        $_SESSION['flash_success'] = 'Work Order berhasil dibatalkan.';
        redirect('work-orders/' . $id);
    }
}
