<?php

declare(strict_types=1);

namespace App\Controllers;

use PDO;
use App\Services\Auth;
use App\Services\Database;
use App\Services\DocumentSequenceService;

class CustomerPortalController
{
    private function getCustomer(): ?array
    {
        $pdo = Database::getConnection();
        $user = Auth::user();
        $userId = $user['id'] ?? ($_SESSION['user_id'] ?? null);
        if (!$userId) {
            return null;
        }

        if (!$user) {
            $stmtU = $pdo->prepare("SELECT * FROM users WHERE id = :uid");
            $stmtU->execute([':uid' => $userId]);
            $user = $stmtU->fetch(PDO::FETCH_ASSOC);
        }

        // 1. Check by user_id
        $stmt = $pdo->prepare("SELECT * FROM customers WHERE user_id = :uid AND deleted_at IS NULL LIMIT 1");
        $stmt->execute([':uid' => $userId]);
        $customer = $stmt->fetch(PDO::FETCH_ASSOC);

        // 2. If not found by user_id, check by email or phone
        if (!$customer && $user) {
            $stmt = $pdo->prepare("SELECT * FROM customers WHERE (email = :email OR phone = :phone) AND deleted_at IS NULL LIMIT 1");
            $stmt->execute([
                ':email' => $user['email'],
                ':phone' => $user['phone'] ?? $user['email'],
            ]);
            $customer = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($customer) {
                // Link customer to this user
                $pdo->prepare("UPDATE customers SET user_id = :uid WHERE id = :cid")
                    ->execute([':uid' => $userId, ':cid' => $customer['id']]);
            }
        }

        // 3. If still not found, auto-create customer profile for this user
        if (!$customer && $user) {
            $custCode = DocumentSequenceService::generate('CUST');
            $stmt = $pdo->prepare("
                INSERT INTO customers (user_id, customer_code, category, name, phone, email, address, status)
                VALUES (:uid, :code, 'household', :name, :phone, :email, :addr, 'active')
            ");
            $phone = !empty($user['phone']) ? $user['phone'] : '08120000000';
            $stmt->execute([
                ':uid' => $userId,
                ':code' => $custCode,
                ':name' => $user['name'],
                ':phone' => $phone,
                ':email' => $user['email'],
                ':addr' => 'Alamat belum diatur',
            ]);
            $cid = (int)$pdo->lastInsertId();
            $customer = $pdo->query("SELECT * FROM customers WHERE id = $cid")->fetch(PDO::FETCH_ASSOC);
        }

        return $customer;
    }

    public function orders(): void
    {
        $customer = $this->getCustomer();
        $pdo = Database::getConnection();

        $stmt = $pdo->prepare("
            SELECT wo.*, t.full_name as technician_name, inv.id as invoice_id, inv.payment_status, inv.balance_due
            FROM work_orders wo
            LEFT JOIN work_order_technicians wot ON wo.id = wot.work_order_id AND wot.is_lead = 1
            LEFT JOIN technicians t ON wot.technician_id = t.id
            LEFT JOIN invoices inv ON inv.work_order_id = wo.id
            WHERE wo.customer_id = :cid AND wo.deleted_at IS NULL
            ORDER BY wo.id DESC
        ");
        $stmt->execute([':cid' => $customer['id']]);
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

        view('customer.orders', [
            'title' => 'Pesanan Servis Saya',
            'customer' => $customer,
            'orders' => $orders,
        ]);
    }

    public function showOrder(string $id): void
    {
        $customer = $this->getCustomer();
        $pdo = Database::getConnection();

        $stmt = $pdo->prepare("
            SELECT wo.*, t.id as technician_id, t.full_name as technician_name, t.phone as technician_phone, t.rating as tech_rating,
                   inv.id as invoice_id, inv.invoice_number, inv.total_amount as inv_total, inv.paid_amount, inv.balance_due, inv.payment_status
            FROM work_orders wo
            LEFT JOIN work_order_technicians wot ON wo.id = wot.work_order_id AND wot.is_lead = 1
            LEFT JOIN technicians t ON wot.technician_id = t.id
            LEFT JOIN invoices inv ON inv.work_order_id = wo.id
            WHERE wo.id = :id AND wo.customer_id = :cid
            LIMIT 1
        ");
        $stmt->execute([':id' => (int)$id, ':cid' => $customer['id']]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$order) {
            $_SESSION['flash_error'] = 'Pesanan tidak ditemukan atau bukan milik akun Anda.';
            redirect('customer-portal/orders');
            return;
        }

        // Order services
        $stmtSrv = $pdo->prepare("SELECT * FROM work_order_services WHERE work_order_id = :id");
        $stmtSrv->execute([':id' => (int)$id]);
        $services = $stmtSrv->fetchAll(PDO::FETCH_ASSOC);

        // Status logs from audit_logs
        $stmtLogs = $pdo->prepare("
            SELECT al.*, u.name as user_name
            FROM audit_logs al
            LEFT JOIN users u ON al.user_id = u.id
            WHERE al.entity_type = 'work_orders' AND al.entity_id = :id
            ORDER BY al.id ASC
        ");
        $stmtLogs->execute([':id' => (int)$id]);
        $logs = $stmtLogs->fetchAll(PDO::FETCH_ASSOC);

        // Existing review
        $stmtRev = $pdo->prepare("SELECT * FROM customer_reviews WHERE work_order_id = :id LIMIT 1");
        $stmtRev->execute([':id' => (int)$id]);
        $review = $stmtRev->fetch(PDO::FETCH_ASSOC);

        view('customer.show_order', [
            'title' => 'Detail Pesanan #' . $order['order_number'],
            'order' => $order,
            'services' => $services,
            'logs' => $logs,
            'review' => $review,
            'customer' => $customer,
        ]);
    }

    public function book(): void
    {
        $customer = $this->getCustomer();
        $pdo = Database::getConnection();

        // Customer's existing units
        $stmtUnits = $pdo->prepare("SELECT * FROM ac_units WHERE customer_id = :cid AND deleted_at IS NULL ORDER BY brand ASC");
        $stmtUnits->execute([':cid' => $customer['id']]);
        $units = $stmtUnits->fetchAll(PDO::FETCH_ASSOC);

        // Service catalog
        $services = $pdo->query("SELECT p.*, c.name as category_name FROM service_products p JOIN service_categories c ON p.category_id = c.id WHERE p.is_active = 1 ORDER BY c.sort_order ASC, p.base_price ASC")->fetchAll(PDO::FETCH_ASSOC);

        view('customer.book', [
            'title' => 'Pesan Servis AC Online',
            'customer' => $customer,
            'units' => $units,
            'services' => $services,
        ]);
    }

    public function storeBooking(): void
    {
        $customer = $this->getCustomer();
        $pdo = Database::getConnection();

        $scheduledDate = trim($_POST['scheduled_date'] ?? date('Y-m-d', strtotime('+1 day')));
        $scheduledTimeSlot = trim($_POST['scheduled_time_slot'] ?? 'morning_09_12');
        $serviceAddress = trim($_POST['service_address'] ?? $customer['address']);
        $complaints = trim($_POST['complaints'] ?? '');
        $serviceId = (int)($_POST['service_id'] ?? 0);
        $acUnitId = !empty($_POST['ac_unit_id']) ? (int)$_POST['ac_unit_id'] : null;

        // If user entered a new AC unit inline
        if (empty($acUnitId) && !empty($_POST['new_unit_brand'])) {
            $unitCode = DocumentSequenceService::generate('AC');
            $stmtUnit = $pdo->prepare("
                INSERT INTO ac_units (customer_id, unit_code, brand, model_type, capacity_pk, refrigerant_type, room_location, condition)
                VALUES (:cid, :code, :brand, :model, :pk, :freon, :room, 'good')
            ");
            $stmtUnit->execute([
                ':cid' => $customer['id'],
                ':code' => $unitCode,
                ':brand' => trim($_POST['new_unit_brand']),
                ':model' => trim($_POST['new_unit_model'] ?? 'Split Wall'),
                ':pk' => trim($_POST['new_unit_pk'] ?? '1 PK'),
                ':freon' => trim($_POST['new_unit_freon'] ?? 'R32'),
                ':room' => trim($_POST['new_unit_room'] ?? 'Ruang Tamu'),
            ]);
            $acUnitId = (int)$pdo->lastInsertId();
        }

        // Fetch service price estimate
        $estPrice = 0;
        $serviceName = 'Servis AC Rutin';
        if ($serviceId > 0) {
            $stmtS = $pdo->prepare("SELECT * FROM service_products WHERE id = :id");
            $stmtS->execute([':id' => $serviceId]);
            $svc = $stmtS->fetch(PDO::FETCH_ASSOC);
            if ($svc) {
                $estPrice = (int)$svc['base_price'];
                $serviceName = $svc['name'];
            }
        }

        $orderNumber = DocumentSequenceService::generate('WO');

        $woId = Database::transaction(function (PDO $pdo) use ($customer, $orderNumber, $acUnitId, $scheduledDate, $scheduledTimeSlot, $serviceAddress, $complaints, $estPrice, $serviceId, $serviceName) {
            // 1. Insert Work Order
            $stmtWo = $pdo->prepare("
                INSERT INTO work_orders (
                    order_number, customer_id, ac_unit_id, order_source, priority,
                    status, scheduled_date, scheduled_time, address_text,
                    complaints, estimated_price, created_by
                ) VALUES (
                    :num, :cid, :uid, 'customer_portal', 'normal',
                    'BARU', :date, :slot, :addr,
                    :complaint, :price, :by
                )
            ");
            $stmtWo->execute([
                ':num' => $orderNumber,
                ':cid' => $customer['id'],
                ':uid' => $acUnitId,
                ':date' => $scheduledDate,
                ':slot' => $scheduledTimeSlot,
                ':addr' => $serviceAddress,
                ':complaint' => $complaints ?: 'Pemesanan servis via Customer Portal',
                ':price' => $estPrice,
                ':by' => Auth::id(),
            ]);
            $woId = (int)$pdo->lastInsertId();

            // 2. Insert Service Item
            if ($serviceId > 0) {
                $stmtItem = $pdo->prepare("
                    INSERT INTO work_order_services (work_order_id, service_product_id, service_name, quantity, unit_price, subtotal)
                    VALUES (:wo_id, :sid, :sname, 1, :price, :sub)
                ");
                $stmtItem->execute([
                    ':wo_id' => $woId,
                    ':sid' => $serviceId,
                    ':sname' => $serviceName,
                    ':price' => $estPrice,
                    ':sub' => $estPrice,
                ]);
            }

            // 3. Update customer total orders
            $pdo->prepare("UPDATE customers SET total_orders = total_orders + 1 WHERE id = :cid")
                ->execute([':cid' => $customer['id']]);

            audit_log('create_booking', 'work_orders', $woId, null, ['order_number' => $orderNumber]);
            return $woId;
        });

        $_SESSION['flash_success'] = "Pesanan servis #{$orderNumber} berhasil dikirim! Tim Customer Service kami akan segera mengonfirmasi jadwal Anda.";
        redirect('customer-portal/orders/' . $woId);
    }

    public function units(): void
    {
        $customer = $this->getCustomer();
        $pdo = Database::getConnection();

        $stmt = $pdo->prepare("
            SELECT u.*, 
                   (SELECT COUNT(*) FROM work_orders WHERE ac_unit_id = u.id) as service_count,
                   (SELECT MAX(checkout_at) FROM work_orders WHERE ac_unit_id = u.id AND status = 'LUNAS') as last_service_date
            FROM ac_units u
            WHERE u.customer_id = :cid AND u.deleted_at IS NULL
            ORDER BY u.id DESC
        ");
        $stmt->execute([':cid' => $customer['id']]);
        $units = $stmt->fetchAll(PDO::FETCH_ASSOC);

        view('customer.units', [
            'title' => 'Unit AC Terdaftar',
            'customer' => $customer,
            'units' => $units,
        ]);
    }

    public function storeUnit(): void
    {
        $customer = $this->getCustomer();
        $pdo = Database::getConnection();

        $brand = trim($_POST['brand'] ?? '');
        $model = trim($_POST['model_type'] ?? 'Split Wall');
        $pk = trim($_POST['capacity_pk'] ?? '1 PK');
        $freon = trim($_POST['freon_type'] ?? 'R32');
        $room = trim($_POST['room_location'] ?? 'Ruang Tamu');
        $serial = trim($_POST['serial_number'] ?? '');
        $notes = trim($_POST['notes'] ?? '');

        if (empty($brand)) {
            $_SESSION['flash_error'] = 'Merek AC wajib diisi.';
            redirect('customer-portal/units');
            return;
        }

        $unitCode = DocumentSequenceService::generate('AC');

        $stmt = $pdo->prepare("
            INSERT INTO ac_units (customer_id, unit_code, brand, model_type, capacity_pk, refrigerant_type, serial_number, room_location, notes, condition)
            VALUES (:cid, :code, :brand, :model, :pk, :freon, :serial, :room, :notes, 'good')
        ");
        $stmt->execute([
            ':cid' => $customer['id'],
            ':code' => $unitCode,
            ':brand' => $brand,
            ':model' => $model,
            ':pk' => $pk,
            ':freon' => $freon,
            ':serial' => $serial ?: null,
            ':room' => $room,
            ':notes' => $notes ?: null,
        ]);

        $_SESSION['flash_success'] = "Unit AC {$brand} ({$unitCode}) berhasil didaftarkan!";
        redirect('customer-portal/units');
    }

    public function invoices(): void
    {
        $customer = $this->getCustomer();
        $pdo = Database::getConnection();

        $stmt = $pdo->prepare("
            SELECT inv.*, wo.order_number
            FROM invoices inv
            LEFT JOIN work_orders wo ON inv.work_order_id = wo.id
            WHERE inv.customer_id = :cid
            ORDER BY inv.id DESC
        ");
        $stmt->execute([':cid' => $customer['id']]);
        $invoices = $stmt->fetchAll(PDO::FETCH_ASSOC);

        view('customer.invoices', [
            'title' => 'Tagihan & Faktur Saya',
            'customer' => $customer,
            'invoices' => $invoices,
        ]);
    }

    public function complaints(): void
    {
        $customer = $this->getCustomer();
        $pdo = Database::getConnection();

        // Customer's past work orders
        $stmtWo = $pdo->prepare("SELECT id, order_number, scheduled_date FROM work_orders WHERE customer_id = :cid ORDER BY id DESC");
        $stmtWo->execute([':cid' => $customer['id']]);
        $orders = $stmtWo->fetchAll(PDO::FETCH_ASSOC);

        // Complaints list
        $stmtC = $pdo->prepare("
            SELECT c.*, wo.order_number
            FROM complaints c
            LEFT JOIN work_orders wo ON c.work_order_id = wo.id
            WHERE c.customer_id = :cid
            ORDER BY c.id DESC
        ");
        $stmtC->execute([':cid' => $customer['id']]);
        $complaints = $stmtC->fetchAll(PDO::FETCH_ASSOC);

        view('customer.complaints', [
            'title' => 'Keluhan & Klaim Garansi',
            'customer' => $customer,
            'orders' => $orders,
            'complaints' => $complaints,
        ]);
    }

    public function storeComplaint(): void
    {
        $customer = $this->getCustomer();
        $pdo = Database::getConnection();

        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $workOrderId = !empty($_POST['work_order_id']) ? (int)$_POST['work_order_id'] : null;
        $priority = trim($_POST['priority'] ?? 'normal');

        if (empty($title) || empty($description)) {
            $_SESSION['flash_error'] = 'Judul dan rincian keluhan wajib diisi.';
            redirect('customer-portal/complaints');
            return;
        }

        $complaintNumber = DocumentSequenceService::generate('KOM');

        $stmt = $pdo->prepare("
            INSERT INTO complaints (complaint_number, customer_id, work_order_id, title, description, priority, status)
            VALUES (:num, :cid, :wo_id, :title, :desc, :prio, 'open')
        ");
        $stmt->execute([
            ':num' => $complaintNumber,
            ':cid' => $customer['id'],
            ':wo_id' => $workOrderId,
            ':title' => $title,
            ':desc' => $description,
            ':prio' => $priority,
        ]);

        $_SESSION['flash_success'] = "Keluhan #{$complaintNumber} berhasil diajukan. Tim Customer Care kami akan segera menindaklanjuti.";
        redirect('customer-portal/complaints');
    }

    public function storeReview(string $id): void
    {
        $customer = $this->getCustomer();
        $pdo = Database::getConnection();

        $rating = (int)($_POST['rating'] ?? 5);
        $reviewText = trim($_POST['review_text'] ?? '');

        if ($rating < 1 || $rating > 5) {
            $rating = 5;
        }

        // Verify order belongs to customer
        $stmt = $pdo->prepare("SELECT * FROM work_orders WHERE id = :id AND customer_id = :cid");
        $stmt->execute([':id' => (int)$id, ':cid' => $customer['id']]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$order) {
            $_SESSION['flash_error'] = 'Pesanan tidak valid.';
            redirect('customer-portal/orders');
            return;
        }

        Database::transaction(function (PDO $pdo) use ($order, $customer, $rating, $reviewText) {
            $leadTechId = (int)$pdo->query("SELECT technician_id FROM work_order_technicians WHERE work_order_id = {$order['id']} AND is_lead = 1 LIMIT 1")->fetchColumn();

            // Check if review already exists
            $stmtCheck = $pdo->prepare("SELECT id FROM customer_reviews WHERE work_order_id = :wo_id");
            $stmtCheck->execute([':wo_id' => $order['id']]);
            $existing = $stmtCheck->fetchColumn();

            if ($existing) {
                $pdo->prepare("
                    UPDATE customer_reviews
                    SET rating = :rating, review_text = :text, technician_id = :tid, created_at = datetime('now', 'localtime')
                    WHERE id = :id
                ")->execute([':rating' => $rating, ':text' => $reviewText ?: null, ':tid' => ($leadTechId > 0 ? $leadTechId : null), ':id' => $existing]);
            } else {
                $pdo->prepare("
                    INSERT INTO customer_reviews (customer_id, work_order_id, technician_id, rating, review_text, is_published)
                    VALUES (:cid, :wo_id, :tid, :rating, :text, 1)
                ")->execute([
                    ':cid' => $customer['id'],
                    ':wo_id' => $order['id'],
                    ':tid' => ($leadTechId > 0 ? $leadTechId : null),
                    ':rating' => $rating,
                    ':text' => $reviewText ?: null,
                ]);
            }

            // Recalculate technician rating average
            if ($leadTechId > 0) {
                $avg = (float)$pdo->query("SELECT AVG(rating) FROM customer_reviews WHERE technician_id = {$leadTechId}")->fetchColumn();
                $count = (int)$pdo->query("SELECT COUNT(*) FROM customer_reviews WHERE technician_id = {$leadTechId}")->fetchColumn();
                $pdo->prepare("UPDATE technicians SET rating_average = :avg, total_reviews = :cnt WHERE id = :tid")
                    ->execute([':avg' => round($avg, 2), ':cnt' => $count, ':tid' => $leadTechId]);
            }
        });

        $_SESSION['flash_success'] = 'Terima kasih atas ulasan dan penilaian bintang Anda!';
        redirect('customer-portal/orders/' . $id);
    }
}
