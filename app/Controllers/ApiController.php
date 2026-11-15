<?php

declare(strict_types=1);

namespace App\Controllers;

use PDO;
use App\Services\Auth;
use App\Services\Database;

class ApiController
{
    public function syncPush(): void
    {
        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true);

        if (!$data || empty($data['action'])) {
            json_response(['success' => false, 'message' => 'Invalid sync payload'], 400);
        }

        $action = $data['action'];
        $payload = $data['payload'] ?? [];
        $userId = Auth::id() ?? 1;

        $pdo = Database::getConnection();

        try {
            Database::transaction(function (PDO $pdo) use ($action, $payload, $userId) {
                // Record in sync_queue table for audit
                $uuid = $payload['uuid'] ?? uuid_v4();
                $stmt = $pdo->prepare("
                    INSERT OR REPLACE INTO sync_queue (uuid, user_id, action, payload, status, synced_at)
                    VALUES (:uuid, :user_id, :action, :payload, 'synced', datetime('now', 'localtime'))
                ");
                $stmt->execute([
                    ':uuid' => $uuid,
                    ':user_id' => $userId,
                    ':action' => $action,
                    ':payload' => json_encode($payload, JSON_UNESCAPED_UNICODE),
                ]);

                // Dispatch specific sync action
                switch ($action) {
                    case 'update_status':
                        $woId = (int)($payload['work_order_id'] ?? 0);
                        $status = (string)($payload['status'] ?? '');
                        if ($woId > 0 && !empty($status)) {
                            $pdo->prepare("UPDATE work_orders SET status = :status, updated_at = datetime('now', 'localtime') WHERE id = :id")
                                ->execute([':status' => $status, ':id' => $woId]);
                        }
                        break;

                    case 'save_checklist':
                        $woId = (int)($payload['work_order_id'] ?? 0);
                        $items = $payload['items'] ?? [];
                        if ($woId > 0 && is_array($items)) {
                            foreach ($items as $item) {
                                $stmtChk = $pdo->prepare("
                                    INSERT INTO service_checklists (work_order_id, checklist_item, status, notes)
                                    VALUES (:wo_id, :item, :status, :notes)
                                ");
                                $stmtChk->execute([
                                    ':wo_id' => $woId,
                                    ':item' => $item['item'] ?? 'Pemeriksaan',
                                    ':status' => $item['status'] ?? 'ok',
                                    ':notes' => $item['notes'] ?? null,
                                ]);
                            }
                        }
                        break;

                    case 'save_diagnostics':
                        $woId = (int)($payload['work_order_id'] ?? 0);
                        if ($woId > 0) {
                            $stmtDiag = $pdo->prepare("
                                INSERT INTO service_diagnostics (work_order_id, current_ampere, voltage_volt, pressure_psi, temperature_celsius, diagnostic_result, technician_notes)
                                VALUES (:wo_id, :ampere, :volt, :psi, :temp, :result, :notes)
                            ");
                            $stmtDiag->execute([
                                ':wo_id' => $woId,
                                ':ampere' => $payload['current_ampere'] ?? null,
                                ':volt' => $payload['voltage_volt'] ?? null,
                                ':psi' => $payload['pressure_psi'] ?? null,
                                ':temp' => $payload['temperature_celsius'] ?? null,
                                ':result' => $payload['diagnostic_result'] ?? null,
                                ':notes' => $payload['technician_notes'] ?? null,
                            ]);
                        }
                        break;

                    case 'save_signature':
                        $woId = (int)($payload['work_order_id'] ?? 0);
                        if ($woId > 0 && !empty($payload['signature_data'])) {
                            $stmtSig = $pdo->prepare("
                                INSERT OR REPLACE INTO customer_signatures (work_order_id, signer_name, signature_data, rating, customer_notes)
                                VALUES (:wo_id, :name, :sig, :rating, :notes)
                            ");
                            $stmtSig->execute([
                                ':wo_id' => $woId,
                                ':name' => $payload['signer_name'] ?? 'Pelanggan',
                                ':sig' => $payload['signature_data'],
                                ':rating' => (int)($payload['rating'] ?? 5),
                                ':notes' => $payload['customer_notes'] ?? null,
                            ]);
                        }
                        break;
                }
            });

            json_response(['success' => true, 'message' => 'Action synced successfully']);
        } catch (\Throwable $e) {
            error_log('Sync push error: ' . $e->getMessage());
            json_response(['success' => false, 'message' => 'Sync failed: ' . $e->getMessage()], 500);
        }
    }

    public function syncPull(): void
    {
        $user = Auth::user();
        $pdo = Database::getConnection();

        // Return latest work orders for local cache
        $query = "
            SELECT wo.*, c.name as customer_name, c.phone as customer_phone, c.address as customer_address,
                   u.brand as ac_brand, u.model_type as ac_model, u.capacity_pk, u.refrigerant_type, u.room_location
            FROM work_orders wo
            JOIN customers c ON wo.customer_id = c.id
            LEFT JOIN ac_units u ON wo.ac_unit_id = u.id
            WHERE wo.deleted_at IS NULL AND wo.status NOT IN ('LUNAS', 'DIBATALKAN')
            ORDER BY wo.scheduled_date ASC
            LIMIT 50
        ";

        $orders = $pdo->query($query)->fetchAll(PDO::FETCH_ASSOC);
        json_response(['success' => true, 'data' => $orders, 'timestamp' => time()]);
    }

    public function technicianTasks(): void
    {
        $user = Auth::user();
        $techId = $user['technician_id'] ?? 1;
        $pdo = Database::getConnection();

        $stmt = $pdo->prepare("
            SELECT wo.*, c.name as customer_name, c.phone as customer_phone, c.address as customer_address,
                   u.brand as ac_brand, u.capacity_pk, u.refrigerant_type, u.room_location
            FROM work_orders wo
            JOIN work_order_technicians wot ON wo.id = wot.work_order_id
            JOIN customers c ON wo.customer_id = c.id
            LEFT JOIN ac_units u ON wo.ac_unit_id = u.id
            WHERE wot.technician_id = :tech_id AND wo.deleted_at IS NULL
            ORDER BY wo.scheduled_date ASC
        ");
        $stmt->execute([':tech_id' => $techId]);
        $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

        json_response(['success' => true, 'tasks' => $tasks]);
    }

    public function updateOrderStatus(string $id): void
    {
        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true) ?: $_POST;
        $status = $data['status'] ?? '';

        if (empty($status)) {
            json_response(['success' => false, 'message' => 'Status is required'], 400);
        }

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("UPDATE work_orders SET status = :status, updated_at = datetime('now', 'localtime') WHERE id = :id");
        $stmt->execute([':status' => $status, ':id' => (int)$id]);

        json_response(['success' => true, 'message' => 'Status updated to ' . $status]);
    }
}
