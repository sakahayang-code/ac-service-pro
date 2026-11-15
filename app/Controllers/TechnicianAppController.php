<?php

declare(strict_types=1);

namespace App\Controllers;

use PDO;
use App\Services\Auth;
use App\Services\Database;
use App\Services\DocumentSequenceService;

class TechnicianAppController
{
    private function getTechnicianId(): int
    {
        $user = Auth::user();
        if (!empty($user['technician_id'])) {
            return (int)$user['technician_id'];
        }

        $pdo = Database::getConnection();
        $tid = $pdo->query("SELECT id FROM technicians WHERE user_id = " . (int)$user['id'])->fetchColumn();
        if ($tid) {
            return (int)$tid;
        }

        // Fallback for owner/admin reviewing technician app: use first technician
        return (int)$pdo->query("SELECT id FROM technicians ORDER BY id ASC LIMIT 1")->fetchColumn() ?: 1;
    }

    public function myTasks(): void
    {
        $techId = $this->getTechnicianId();
        $pdo = Database::getConnection();

        $stmt = $pdo->prepare("
            SELECT wo.*, c.name as customer_name, c.phone as customer_phone, c.address as customer_address,
                   u.brand as ac_brand, u.model_type as ac_model, u.capacity_pk, u.refrigerant_type, u.room_location,
                   wot.is_lead
            FROM work_orders wo
            JOIN work_order_technicians wot ON wo.id = wot.work_order_id
            JOIN customers c ON wo.customer_id = c.id
            LEFT JOIN ac_units u ON wo.ac_unit_id = u.id
            WHERE wot.technician_id = :tid
              AND wo.deleted_at IS NULL
              AND wo.status NOT IN ('LUNAS', 'DIBATALKAN')
            ORDER BY 
                CASE wo.status
                    WHEN 'SEDANG_DIKERJAKAN' THEN 1
                    WHEN 'DALAM_PERJALANAN' THEN 2
                    WHEN 'DIJADWALKAN' THEN 3
                    WHEN 'MENUNGGU_SPAREPART' THEN 4
                    WHEN 'SELESAI' THEN 5
                    ELSE 6
                END,
                wo.scheduled_date ASC
        ");
        $stmt->execute([':tid' => $techId]);
        $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Tech stats for header
        $tech = $pdo->query("SELECT * FROM technicians WHERE id = $techId")->fetch(PDO::FETCH_ASSOC);

        view('technician.tasks', [
            'title' => 'Tugas Servis Lapangan',
            'tasks' => $tasks,
            'technician' => $tech,
        ]);
    }

    public function executeTask(string $id): void
    {
        $techId = $this->getTechnicianId();
        $pdo = Database::getConnection();

        $stmt = $pdo->prepare("
            SELECT wo.*, c.name as customer_name, c.customer_code, c.phone as customer_phone, c.address as customer_address,
                   c.latitude, c.longitude,
                   u.brand as ac_brand, u.model_type as ac_model, u.capacity_pk, u.refrigerant_type, u.room_location, u.serial_number as ac_serial
            FROM work_orders wo
            JOIN work_order_technicians wot ON wo.id = wot.work_order_id
            JOIN customers c ON wo.customer_id = c.id
            LEFT JOIN ac_units u ON wo.ac_unit_id = u.id
            WHERE wo.id = :id AND (wot.technician_id = :tid OR :is_admin = 1)
            LIMIT 1
        ");
        $isAdmin = Auth::hasRole(['owner', 'admin']) ? 1 : 0;
        $stmt->execute([':id' => (int)$id, ':tid' => $techId, ':is_admin' => $isAdmin]);
        $task = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$task) {
            $_SESSION['flash_error'] = 'Pekerjaan tidak ditemukan atau Anda tidak memiliki akses ke order ini.';
            redirect('technician/tasks');
            return;
        }

        // Checklists
        $checklists = $pdo->prepare("SELECT * FROM service_checklists WHERE work_order_id = :id ORDER BY id ASC");
        $checklists->execute([':id' => (int)$id]);
        $orderChecklists = $checklists->fetchAll(PDO::FETCH_ASSOC);

        // Diagnostics
        $diag = $pdo->prepare("SELECT * FROM service_diagnostics WHERE work_order_id = :id ORDER BY id DESC LIMIT 1");
        $diag->execute([':id' => (int)$id]);
        $diagnostics = $diag->fetch(PDO::FETCH_ASSOC);

        // Materials used
        $materials = $pdo->prepare("SELECT * FROM service_materials WHERE work_order_id = :id ORDER BY id ASC");
        $materials->execute([':id' => (int)$id]);
        $orderMaterials = $materials->fetchAll(PDO::FETCH_ASSOC);

        // Available spareparts from inventory
        $inventoryItems = $pdo->query("SELECT id, sku, name, unit, selling_price, current_stock FROM inventory_items WHERE is_active = 1 AND deleted_at IS NULL ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);

        // Signature
        $sig = $pdo->prepare("SELECT * FROM customer_signatures WHERE work_order_id = :id LIMIT 1");
        $sig->execute([':id' => (int)$id]);
        $signature = $sig->fetch(PDO::FETCH_ASSOC);

        view('technician.execute', [
            'title' => 'Pengerjaan ' . $task['order_number'],
            'task' => $task,
            'checklists' => $orderChecklists,
            'diagnostics' => $diagnostics,
            'materials' => $orderMaterials,
            'inventoryItems' => $inventoryItems,
            'signature' => $signature,
        ]);
    }

    public function checkIn(string $id): void
    {
        $pdo = Database::getConnection();
        $pdo->prepare("
            UPDATE work_orders
            SET status = 'SEDANG_DIKERJAKAN',
                checkin_at = COALESCE(checkin_at, datetime('now', 'localtime')),
                updated_at = datetime('now', 'localtime')
            WHERE id = :id
        ")->execute([':id' => (int)$id]);

        $_SESSION['flash_success'] = 'Check-in berhasil! Waktu mulai servis telah tercatat.';
        redirect('technician/tasks/' . $id);
    }

    public function saveChecklist(string $id): void
    {
        $pdo = Database::getConnection();
        $items = $_POST['checklist'] ?? [];

        foreach ($items as $chkId => $status) {
            $notes = $_POST['notes'][$chkId] ?? null;
            $pdo->prepare("
                UPDATE service_checklists
                SET status = :status, notes = :notes, checked_at = datetime('now', 'localtime')
                WHERE id = :id AND work_order_id = :wo_id
            ")->execute([
                ':status' => $status,
                ':notes' => $notes,
                ':id' => (int)$chkId,
                ':wo_id' => (int)$id,
            ]);
        }

        $_SESSION['flash_success'] = 'Checklist pemeriksaan fisik berhasil diperbarui.';
        redirect('technician/tasks/' . $id);
    }

    public function saveDiagnostics(string $id): void
    {
        $pdo = Database::getConnection();
        $ampere = !empty($_POST['current_ampere']) ? (float)$_POST['current_ampere'] : null;
        $volt = !empty($_POST['voltage_volt']) ? (float)$_POST['voltage_volt'] : null;
        $psi = !empty($_POST['pressure_psi']) ? (float)$_POST['pressure_psi'] : null;
        $temp = !empty($_POST['temperature_celsius']) ? (float)$_POST['temperature_celsius'] : null;
        $result = trim($_POST['diagnostic_result'] ?? '');
        $notes = trim($_POST['technician_notes'] ?? '');

        // Check if existing diagnostics
        $existing = $pdo->prepare("SELECT id FROM service_diagnostics WHERE work_order_id = :id LIMIT 1");
        $existing->execute([':id' => (int)$id]);
        $diagId = $existing->fetchColumn();

        if ($diagId) {
            $stmt = $pdo->prepare("
                UPDATE service_diagnostics
                SET current_ampere = :amp, voltage_volt = :volt, pressure_psi = :psi,
                    temperature_celsius = :temp, diagnostic_result = :res, technician_notes = :notes,
                    recorded_at = datetime('now', 'localtime')
                WHERE id = :id
            ");
            $stmt->execute([
                ':amp' => $ampere,
                ':volt' => $volt,
                ':psi' => $psi,
                ':temp' => $temp,
                ':res' => $result ?: null,
                ':notes' => $notes ?: null,
                ':id' => $diagId,
            ]);
        } else {
            $stmt = $pdo->prepare("
                INSERT INTO service_diagnostics (
                    work_order_id, current_ampere, voltage_volt, pressure_psi, temperature_celsius,
                    diagnostic_result, technician_notes
                ) VALUES (
                    :wo_id, :amp, :volt, :psi, :temp, :res, :notes
                )
            ");
            $stmt->execute([
                ':wo_id' => (int)$id,
                ':amp' => $ampere,
                ':volt' => $volt,
                ':psi' => $psi,
                ':temp' => $temp,
                ':res' => $result ?: null,
                ':notes' => $notes ?: null,
            ]);
        }

        $_SESSION['flash_success'] = 'Hasil pengukuran diagnosis teknis berhasil disimpan.';
        redirect('technician/tasks/' . $id);
    }

    public function addMaterial(string $id): void
    {
        $pdo = Database::getConnection();
        $itemId = (int)($_POST['inventory_item_id'] ?? 0);
        $qty = (float)($_POST['quantity'] ?? 1.0);

        if ($itemId <= 0 || $qty <= 0) {
            $_SESSION['flash_error'] = 'Pilih barang sparepart dan jumlah yang valid.';
            redirect('technician/tasks/' . $id);
            return;
        }

        Database::transaction(function (PDO $pdo) use ($id, $itemId, $qty) {
            $stmtItem = $pdo->prepare("SELECT id, name, unit, selling_price, current_stock FROM inventory_items WHERE id = :id");
            $stmtItem->execute([':id' => $itemId]);
            $item = $stmtItem->fetch(PDO::FETCH_ASSOC);

            if (!$item) {
                throw new \RuntimeException('Barang tidak ditemukan');
            }

            $price = (int)$item['selling_price'];
            $subtotal = (int)round($price * $qty);

            // 1. Insert into service_materials
            $stmtMat = $pdo->prepare("
                INSERT INTO service_materials (work_order_id, inventory_item_id, item_name, quantity, unit, unit_price, subtotal)
                VALUES (:wo_id, :item_id, :name, :qty, :unit, :price, :subtotal)
            ");
            $stmtMat->execute([
                ':wo_id' => (int)$id,
                ':item_id' => $itemId,
                ':name' => $item['name'],
                ':qty' => $qty,
                ':unit' => $item['unit'],
                ':price' => $price,
                ':subtotal' => $subtotal,
            ]);

            // 2. Automatically deduct stock
            $pdo->prepare("UPDATE inventory_items SET current_stock = current_stock - :qty, updated_at = datetime('now', 'localtime') WHERE id = :id")
                ->execute([':qty' => $qty, ':id' => $itemId]);

            // 3. Record stock movement
            $stmtMove = $pdo->prepare("
                INSERT INTO stock_movements (inventory_item_id, movement_type, reference_type, reference_id, quantity, unit_price, notes, created_by)
                VALUES (:item_id, 'service_usage', 'work_order', :wo_id, :qty, :price, :notes, :uid)
            ");
            $stmtMove->execute([
                ':item_id' => $itemId,
                ':wo_id' => (int)$id,
                ':qty' => $qty,
                ':price' => $price,
                ':notes' => 'Pemakaian sparepart servis WO #' . $id,
                ':uid' => Auth::id(),
            ]);
        });

        $_SESSION['flash_success'] = 'Sparepart berhasil ditambahkan dan stok gudang otomatis terpotong.';
        redirect('technician/tasks/' . $id);
    }

    public function removeMaterial(string $id): void
    {
        $pdo = Database::getConnection();
        $matId = (int)($_POST['material_id'] ?? 0);

        Database::transaction(function (PDO $pdo) use ($id, $matId) {
            $stmtMat = $pdo->prepare("SELECT * FROM service_materials WHERE id = :id AND work_order_id = :wo_id");
            $stmtMat->execute([':id' => $matId, ':wo_id' => (int)$id]);
            $mat = $stmtMat->fetch(PDO::FETCH_ASSOC);

            if ($mat && !empty($mat['inventory_item_id'])) {
                // Restore stock
                $pdo->prepare("UPDATE inventory_items SET current_stock = current_stock + :qty WHERE id = :id")
                    ->execute([':qty' => (float)$mat['quantity'], ':id' => (int)$mat['inventory_item_id']]);

                // Delete material record
                $pdo->prepare("DELETE FROM service_materials WHERE id = :id")->execute([':id' => $matId]);
            }
        });

        $_SESSION['flash_success'] = 'Sparepart dihapus dan stok telah dikembalikan.';
        redirect('technician/tasks/' . $id);
    }

    public function finishTask(string $id): void
    {
        $pdo = Database::getConnection();
        $signatureData = $_POST['signature_data'] ?? '';
        $signerName = trim($_POST['signer_name'] ?? '');
        $rating = (int)($_POST['rating'] ?? 5);
        $customerNotes = trim($_POST['customer_notes'] ?? '');

        if (empty($signatureData) || empty($signerName)) {
            $_SESSION['flash_error'] = 'Nama dan tanda tangan digital pelanggan wajib dilengkapi sebelum menyelesaikan servis.';
            redirect('technician/tasks/' . $id);
            return;
        }

        Database::transaction(function (PDO $pdo) use ($id, $signatureData, $signerName, $rating, $customerNotes) {
            // 1. Save or update customer signature
            $stmtSig = $pdo->prepare("
                INSERT OR REPLACE INTO customer_signatures (work_order_id, signer_name, signature_data, rating, customer_notes, signed_at)
                VALUES (:wo_id, :name, :sig, :rating, :notes, datetime('now', 'localtime'))
            ");
            $stmtSig->execute([
                ':wo_id' => (int)$id,
                ':name' => $signerName,
                ':sig' => $signatureData,
                ':rating' => $rating,
                ':notes' => $customerNotes ?: null,
            ]);

            // 2. Calculate final totals from services & materials
            $srvTotal = (int)$pdo->query("SELECT COALESCE(SUM(subtotal), 0) FROM work_order_services WHERE work_order_id = " . (int)$id)->fetchColumn();
            $matTotal = (int)$pdo->query("SELECT COALESCE(SUM(subtotal), 0) FROM service_materials WHERE work_order_id = " . (int)$id)->fetchColumn();
            $grandTotal = $srvTotal + $matTotal;

            // 3. Mark work order as SELESAI with checkout timestamp
            $stmtWo = $pdo->prepare("
                UPDATE work_orders
                SET status = 'SELESAI', final_price = :final_price,
                    checkout_at = COALESCE(checkout_at, datetime('now', 'localtime')),
                    updated_at = datetime('now', 'localtime')
                WHERE id = :id
            ");
            $stmtWo->execute([
                ':final_price' => $grandTotal,
                ':id' => (int)$id,
            ]);

            // 4. Update technician jobs count & calculate commission
            $leadTech = $pdo->query("SELECT technician_id FROM work_order_technicians WHERE work_order_id = " . (int)$id . " AND is_lead = 1 LIMIT 1")->fetch(PDO::FETCH_ASSOC);
            if ($leadTech) {
                $techId = (int)$leadTech['technician_id'];
                $commPercent = (float)$pdo->query("SELECT commission_rate_percent FROM technicians WHERE id = $techId")->fetchColumn();
                // Commission is calculated on service revenue
                $commissionAmount = (int)round(($srvTotal * $commPercent) / 100);

                $pdo->prepare("UPDATE work_order_technicians SET commission_amount = :comm WHERE work_order_id = :wo_id AND technician_id = :tid")
                    ->execute([':comm' => $commissionAmount, ':wo_id' => (int)$id, ':tid' => $techId]);

                $pdo->prepare("UPDATE technicians SET total_jobs_done = total_jobs_done + 1 WHERE id = :tid")
                    ->execute([':tid' => $techId]);
            }

            // 5. Create Draft Invoice if not exists
            $existingInv = $pdo->query("SELECT id FROM invoices WHERE work_order_id = " . (int)$id)->fetchColumn();
            if (!$existingInv) {
                $woData = $pdo->query("SELECT * FROM work_orders WHERE id = " . (int)$id)->fetch(PDO::FETCH_ASSOC);
                $invNumber = DocumentSequenceService::generate('INV');
                $dueDate = date('Y-m-d', strtotime('+7 days'));

                $stmtInv = $pdo->prepare("
                    INSERT INTO invoices (
                        invoice_number, work_order_id, customer_id, issue_date, due_date,
                        subtotal_services, subtotal_materials, visit_fee, discount_amount,
                        tax_rate, tax_amount, total_amount, paid_amount, balance_due, payment_status, created_by
                    ) VALUES (
                        :num, :wo_id, :cid, date('now', 'localtime'), :due,
                        :srv_sub, :mat_sub, 0, 0,
                        0, 0, :total, 0, :total, 'unpaid', :by
                    )
                ");
                $stmtInv->execute([
                    ':num' => $invNumber,
                    ':wo_id' => (int)$id,
                    ':cid' => $woData['customer_id'],
                    ':due' => $dueDate,
                    ':srv_sub' => $srvTotal,
                    ':mat_sub' => $matTotal,
                    ':total' => $grandTotal,
                    ':by' => Auth::id(),
                ]);
                $invId = (int)$pdo->lastInsertId();

                // Copy items to invoice_items
                $orderServices = $pdo->query("SELECT * FROM work_order_services WHERE work_order_id = " . (int)$id)->fetchAll(PDO::FETCH_ASSOC);
                $stmtInvItem = $pdo->prepare("INSERT INTO invoice_items (invoice_id, item_type, reference_id, description, quantity, unit_price, subtotal) VALUES (:iid, 'service', :ref, :desc, :qty, :price, :sub)");
                foreach ($orderServices as $os) {
                    $stmtInvItem->execute([
                        ':iid' => $invId,
                        ':ref' => $os['service_product_id'],
                        ':desc' => $os['service_name'],
                        ':qty' => $os['quantity'],
                        ':price' => $os['unit_price'],
                        ':sub' => $os['subtotal'],
                    ]);
                }

                $orderMaterials = $pdo->query("SELECT * FROM service_materials WHERE work_order_id = " . (int)$id)->fetchAll(PDO::FETCH_ASSOC);
                $stmtMatItem = $pdo->prepare("INSERT INTO invoice_items (invoice_id, item_type, reference_id, description, quantity, unit_price, subtotal) VALUES (:iid, 'material', :ref, :desc, :qty, :price, :sub)");
                foreach ($orderMaterials as $om) {
                    $stmtMatItem->execute([
                        ':iid' => $invId,
                        ':ref' => $om['inventory_item_id'],
                        ':desc' => $om['item_name'],
                        ':qty' => $om['quantity'],
                        ':price' => $om['unit_price'],
                        ':sub' => $om['subtotal'],
                    ]);
                }
            }

            // 6. Schedule Next Service Reminder (90 Days)
            $woInfo = $pdo->query("SELECT customer_id, ac_unit_id FROM work_orders WHERE id = " . (int)$id)->fetch(PDO::FETCH_ASSOC);
            if ($woInfo && !empty($woInfo['ac_unit_id'])) {
                $nextDue = date('Y-m-d', strtotime('+90 days'));
                $pdo->prepare("
                    UPDATE ac_units
                    SET last_serviced_at = datetime('now', 'localtime'), next_service_due = :due
                    WHERE id = :id
                ")->execute([':due' => $nextDue, ':id' => $woInfo['ac_unit_id']]);

                $pdo->prepare("
                    INSERT INTO service_reminders (customer_id, ac_unit_id, last_service_date, due_date, reminder_type, status)
                    VALUES (:cid, :uid, date('now', 'localtime'), :due, 'routine_wash', 'pending')
                ")->execute([
                    ':cid' => $woInfo['customer_id'],
                    ':uid' => $woInfo['ac_unit_id'],
                    ':due' => $nextDue,
                ]);
            }

            audit_log('finish_service', 'work_orders', (int)$id, null, ['signer' => $signerName, 'rating' => $rating]);
        });

        $_SESSION['flash_success'] = 'Alhamdulillah! Pekerjaan servis berhasil diselesaikan secara profesional. Faktur dan jadwal servis berkala otomatis diterbitkan.';
        redirect('technician/tasks');
    }

    public function history(): void
    {
        $techId = $this->getTechnicianId();
        $pdo = Database::getConnection();

        $stmt = $pdo->prepare("
            SELECT wo.*, c.name as customer_name, c.phone as customer_phone,
                   u.brand as ac_brand, u.capacity_pk, wot.commission_amount
            FROM work_orders wo
            JOIN work_order_technicians wot ON wo.id = wot.work_order_id
            JOIN customers c ON wo.customer_id = c.id
            LEFT JOIN ac_units u ON wo.ac_unit_id = u.id
            WHERE wot.technician_id = :tid AND wo.status IN ('SELESAI', 'DITAGIHKAN', 'LUNAS')
            ORDER BY wo.checkout_at DESC, wo.id DESC
        ");
        $stmt->execute([':tid' => $techId]);
        $history = $stmt->fetchAll(PDO::FETCH_ASSOC);

        view('technician.history', [
            'title' => 'Riwayat Servis Selesai',
            'history' => $history,
        ]);
    }

    public function profile(): void
    {
        $techId = $this->getTechnicianId();
        $pdo = Database::getConnection();

        $stmt = $pdo->prepare("
            SELECT t.*, u.name as user_name, u.email as user_email
            FROM technicians t
            JOIN users u ON t.user_id = u.id
            WHERE t.id = :id
        ");
        $stmt->execute([':id' => $techId]);
        $technician = $stmt->fetch(PDO::FETCH_ASSOC);

        // Calculate total earnings / commission
        $totalCommission = (int)$pdo->query("
            SELECT COALESCE(SUM(commission_amount), 0)
            FROM work_order_technicians
            WHERE technician_id = $techId
        ")->fetchColumn();

        $thisMonthCommission = (int)$pdo->query("
            SELECT COALESCE(SUM(wot.commission_amount), 0)
            FROM work_order_technicians wot
            JOIN work_orders wo ON wot.work_order_id = wo.id
            WHERE wot.technician_id = $techId AND wo.scheduled_date LIKE '" . date('Y-m') . "%'
        ")->fetchColumn();

        view('technician.profile', [
            'title' => 'Profil & Penghasilan Teknisi',
            'technician' => $technician,
            'totalCommission' => $totalCommission,
            'thisMonthCommission' => $thisMonthCommission,
        ]);
    }
}
