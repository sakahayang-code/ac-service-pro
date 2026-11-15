<?php

declare(strict_types=1);

namespace App\Controllers;

use PDO;
use App\Services\Auth;
use App\Services\Database;
use App\Services\DocumentSequenceService;

class MaintenanceContractController
{
    public function index(): void
    {
        $pdo = Database::getConnection();
        $status = trim($_GET['status'] ?? '');
        $search = trim($_GET['q'] ?? '');

        $sql = "SELECT mc.*, c.name as customer_name, c.customer_code, c.phone as customer_phone
                FROM maintenance_contracts mc
                JOIN customers c ON mc.customer_id = c.id
                WHERE 1=1";
        $params = [];

        if ($status !== '') {
            $sql .= " AND mc.status = :status";
            $params[':status'] = $status;
        }

        if ($search !== '') {
            $sql .= " AND (mc.contract_number LIKE :q OR mc.title LIKE :q OR c.name LIKE :q)";
            $params[':q'] = '%' . $search . '%';
        }

        $sql .= " ORDER BY mc.id DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $contracts = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Summaries
        $activeCount = (int)$pdo->query("SELECT COUNT(*) FROM maintenance_contracts WHERE status = 'active'")->fetchColumn();
        $totalContractValue = (int)$pdo->query("SELECT COALESCE(SUM(contract_amount), 0) FROM maintenance_contracts WHERE status = 'active'")->fetchColumn();
        $expiringSoonCount = (int)$pdo->query("SELECT COUNT(*) FROM maintenance_contracts WHERE status = 'active' AND date(end_date) <= date('now', '+30 days')")->fetchColumn();

        view('admin.contracts.index', [
            'title' => 'Kontrak Maintenance Servis AC',
            'contracts' => $contracts,
            'currentStatus' => $status,
            'search' => $search,
            'activeCount' => $activeCount,
            'totalContractValue' => $totalContractValue,
            'expiringSoonCount' => $expiringSoonCount,
        ]);
    }

    public function create(): void
    {
        $pdo = Database::getConnection();
        $customers = $pdo->query("SELECT id, customer_code, name, phone, address FROM customers WHERE deleted_at IS NULL ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);

        view('admin.contracts.create', [
            'title' => 'Buat Kontrak Maintenance Baru',
            'customers' => $customers,
        ]);
    }

    public function store(): void
    {
        $customerId = (int)($_POST['customer_id'] ?? 0);
        $title = trim($_POST['title'] ?? '');
        $startDate = trim($_POST['start_date'] ?? date('Y-m-d'));
        $endDate = trim($_POST['end_date'] ?? date('Y-m-d', strtotime('+1 year')));
        $totalUnits = (int)($_POST['total_units'] ?? 1);
        $visitsPerYear = (int)($_POST['visits_per_year'] ?? 4);
        $contractAmount = (int)($_POST['contract_amount'] ?? 0);
        $billingFrequency = trim($_POST['billing_frequency'] ?? 'quarterly');
        $slaHours = (int)($_POST['sla_hours'] ?? 24);
        $notes = trim($_POST['notes'] ?? '');

        if ($customerId <= 0 || empty($title) || empty($startDate) || empty($endDate) || $visitsPerYear <= 0) {
            $_SESSION['flash_error'] = 'Lengkapi formulir kontrak maintenance dengan benar.';
            redirect('contracts/create');
            return;
        }

        $pdo = Database::getConnection();
        $contractNumber = DocumentSequenceService::generate('MC');

        $contractId = Database::transaction(function (PDO $pdo) use (
            $contractNumber, $customerId, $title, $startDate, $endDate,
            $totalUnits, $visitsPerYear, $contractAmount, $billingFrequency, $slaHours, $notes
        ) {
            // 1. Insert Contract
            $stmt = $pdo->prepare("
                INSERT INTO maintenance_contracts (
                    contract_number, customer_id, title, start_date, end_date,
                    total_units, visits_per_year, contract_amount, billing_frequency,
                    sla_hours, status, notes, created_by
                ) VALUES (
                    :num, :cid, :title, :sdate, :edate,
                    :units, :visits, :amt, :freq,
                    :sla, 'active', :notes, :uid
                )
            ");
            $stmt->execute([
                ':num' => $contractNumber,
                ':cid' => $customerId,
                ':title' => $title,
                ':sdate' => $startDate,
                ':edate' => $endDate,
                ':units' => $totalUnits,
                ':visits' => $visitsPerYear,
                ':amt' => $contractAmount,
                ':freq' => $billingFrequency,
                ':sla' => $slaHours,
                ':notes' => $notes ?: null,
                ':uid' => Auth::id(),
            ]);
            $contractId = (int)$pdo->lastInsertId();

            // 2. Generate Periodic Schedules
            $startTs = strtotime($startDate);
            $endTs = strtotime($endDate);
            $durationSeconds = max(86400, $endTs - $startTs);
            $interval = (int)($durationSeconds / $visitsPerYear);

            $stmtSched = $pdo->prepare("
                INSERT INTO maintenance_schedules (contract_id, scheduled_date, status, notes)
                VALUES (:cid, :sdate, 'scheduled', :notes)
            ");

            for ($i = 0; $i < $visitsPerYear; $i++) {
                $visitDate = date('Y-m-d', $startTs + ($i * $interval) + (86400 * 7)); // Offset by 1 week from interval
                if (strtotime($visitDate) > $endTs) {
                    $visitDate = date('Y-m-d', $endTs);
                }
                $visitNum = $i + 1;
                $stmtSched->execute([
                    ':cid' => $contractId,
                    ':sdate' => $visitDate,
                    ':notes' => "Kunjungan Pemeliharaan Rutin #{$visitNum} ({$totalUnits} Unit AC)",
                ]);
            }

            audit_log('create', 'maintenance_contracts', $contractId, null, ['number' => $contractNumber, 'amount' => $contractAmount]);
            return $contractId;
        });

        $_SESSION['flash_success'] = "Kontrak {$contractNumber} ({$title}) berhasil dibuat beserta {$visitsPerYear} jadwal servis berkala!";
        redirect('contracts/' . $contractId);
    }

    public function show(string $id): void
    {
        $pdo = Database::getConnection();

        $stmt = $pdo->prepare("
            SELECT mc.*, c.name as customer_name, c.customer_code, c.phone as customer_phone, c.address as customer_address
            FROM maintenance_contracts mc
            JOIN customers c ON mc.customer_id = c.id
            WHERE mc.id = :id
            LIMIT 1
        ");
        $stmt->execute([':id' => (int)$id]);
        $contract = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$contract) {
            redirect('contracts');
            return;
        }

        // Schedules
        $stmtS = $pdo->prepare("
            SELECT ms.*, wo.order_number, wo.status as wo_status
            FROM maintenance_schedules ms
            LEFT JOIN work_orders wo ON ms.work_order_id = wo.id
            WHERE ms.contract_id = :id
            ORDER BY ms.scheduled_date ASC
        ");
        $stmtS->execute([':id' => (int)$id]);
        $schedules = $stmtS->fetchAll(PDO::FETCH_ASSOC);

        // Days remaining
        $daysRemaining = (int)ceil((strtotime($contract['end_date']) - time()) / 86400);

        view('admin.contracts.show', [
            'title' => 'Detail Kontrak - ' . $contract['contract_number'],
            'contract' => $contract,
            'schedules' => $schedules,
            'daysRemaining' => $daysRemaining,
        ]);
    }

    public function generateWorkOrder(string $id): void
    {
        $scheduleId = (int)($_POST['schedule_id'] ?? 0);
        $pdo = Database::getConnection();

        $stmt = $pdo->prepare("
            SELECT ms.*, mc.customer_id, mc.contract_number, mc.title as contract_title, mc.total_units, c.address as customer_address
            FROM maintenance_schedules ms
            JOIN maintenance_contracts mc ON ms.contract_id = mc.id
            JOIN customers c ON mc.customer_id = c.id
            WHERE ms.id = :sid AND ms.contract_id = :cid
            LIMIT 1
        ");
        $stmt->execute([':sid' => $scheduleId, ':cid' => (int)$id]);
        $schedule = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$schedule) {
            $_SESSION['flash_error'] = 'Jadwal pemeliharaan tidak ditemukan.';
            redirect('contracts/' . $id);
            return;
        }

        if (!empty($schedule['work_order_id'])) {
            $_SESSION['flash_error'] = 'Surat Perintah Kerja (SPK) untuk jadwal ini sudah dibuat sebelumnya.';
            redirect('contracts/' . $id);
            return;
        }

        $orderNumber = DocumentSequenceService::generate('WO');

        $woId = Database::transaction(function (PDO $pdo) use ($schedule, $id, $orderNumber) {
            // 1. Create Work Order
            $stmtWo = $pdo->prepare("
                INSERT INTO work_orders (
                    order_number, customer_id, order_source, priority, status,
                    scheduled_date, scheduled_time, address_text, complaints, created_by
                ) VALUES (
                    :num, :cid, 'contract', 'normal', 'DIJADWALKAN',
                    :date, '09:00', :addr, :comp, :uid
                )
            ");
            $stmtWo->execute([
                ':num' => $orderNumber,
                ':cid' => $schedule['customer_id'],
                ':date' => $schedule['scheduled_date'],
                ':addr' => $schedule['customer_address'],
                ':comp' => "Pemeliharaan Kontrak {$schedule['contract_number']}: {$schedule['notes']}",
                ':uid' => Auth::id(),
            ]);
            $woId = (int)$pdo->lastInsertId();

            // 2. Insert Service Product (Cuci AC Maintenance)
            $stmtItem = $pdo->prepare("
                INSERT INTO work_order_services (work_order_id, service_name, quantity, unit_price, subtotal)
                VALUES (:wo_id, :name, :qty, 0, 0)
            ");
            $stmtItem->execute([
                ':wo_id' => $woId,
                ':name' => "Maintenance Rutin Kontrak ({$schedule['total_units']} Unit)",
                ':qty' => (int)$schedule['total_units'],
            ]);

            // 3. Link Schedule to Work Order
            $pdo->prepare("UPDATE maintenance_schedules SET work_order_id = :wo_id, status = 'in_progress' WHERE id = :sid")
                ->execute([':wo_id' => $woId, ':sid' => $schedule['id']]);

            audit_log('create_wo_from_contract', 'work_orders', $woId, null, ['contract_id' => $id, 'order_number' => $orderNumber]);
            return $woId;
        });

        $_SESSION['flash_success'] = "Surat Perintah Kerja #{$orderNumber} berhasil diterbitkan dari jadwal kontrak!";
        redirect('work-orders/' . $woId);
    }
}
