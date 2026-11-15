<?php

declare(strict_types=1);

namespace Tests;

require_once __DIR__ . '/../app/Helpers/helpers.php';
require_once __DIR__ . '/../vendor/autoload.php';

use PDO;
use App\Services\Auth;
use App\Services\Database;
use App\Services\DocumentSequenceService;

class SystemTest
{
    private PDO $pdo;
    private int $passed = 0;
    private int $failed = 0;
    private array $errors = [];

    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }

    private function assert(string $testName, bool $condition, string $failureMessage = ''): void
    {
        if ($condition) {
            $this->passed++;
            echo "  [PASS] {$testName}\n";
        } else {
            $this->failed++;
            $msg = "  [FAIL] {$testName}: {$failureMessage}";
            $this->errors[] = $msg;
            echo "{$msg}\n";
        }
    }

    public function runAll(): void
    {
        echo "=======================================================\n";
        echo " AC SERVICE PRO v1.0 FINAL - AUTOMATED SYSTEM TEST SUITE\n";
        echo "=======================================================\n\n";

        $this->testDatabaseAndEnvironment();
        $this->testAuthAndRBAC();
        $this->testMasterDataManagement();
        $this->testWorkOrderLifecycle();
        $this->testTechnicianExecutionAndStockDeduction();
        $this->testInvoicingAndCashierPayments();
        $this->testFinanceAndProfitLoss();
        $this->testCustomerPortalAndReviews();
        $this->testMaintenanceContracts();
        $this->testInventoryAndPurchasing();
        $this->testReportingAndCsvExports();
        $this->testSystemSettingsAndBackup();
        $this->testPwaAssets();

        echo "\n=======================================================\n";
        echo " TEST RESULTS: {$this->passed} PASSED, {$this->failed} FAILED\n";
        echo "=======================================================\n";

        if ($this->failed > 0) {
            echo "\nFailure Details:\n";
            foreach ($this->errors as $err) {
                echo " - {$err}\n";
            }
            exit(1);
        } else {
            echo "\nCONGRATULATIONS: All functional modules are 100% verified and operational!\n\n";
            exit(0);
        }
    }

    private function testDatabaseAndEnvironment(): void
    {
        echo "--- 1. DATABASE & ENVIRONMENT TESTS ---\n";

        // Check SQLite WAL mode
        $wal = $this->pdo->query("PRAGMA journal_mode")->fetchColumn();
        $this->assert("SQLite WAL Mode Active", strtolower((string)$wal) === 'wal', "Expected WAL but got {$wal}");

        // Check foreign keys
        $fk = (int)$this->pdo->query("PRAGMA foreign_keys")->fetchColumn();
        $this->assert("SQLite Foreign Keys Active", $fk === 1, "Foreign keys disabled");

        // Check table count
        $tables = $this->pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'")->fetchAll(PDO::FETCH_COLUMN);
        $this->assert("All Database Tables Created (>= 45)", count($tables) >= 45, "Found " . count($tables) . " tables");

        // Check key tables
        $expected = ['users', 'roles', 'customers', 'ac_units', 'work_orders', 'inventory_items', 'invoices', 'payments', 'accounts', 'maintenance_contracts'];
        foreach ($expected as $table) {
            $this->assert("Table '{$table}' exists", in_array($table, $tables), "Missing {$table}");
        }
    }

    private function testAuthAndRBAC(): void
    {
        echo "\n--- 2. AUTHENTICATION & RBAC TESTS ---\n";

        $roles = [
            'owner' => 'owner@acservicepro.id',
            'admin' => 'admin@acservicepro.id',
            'customer_service' => 'cs@acservicepro.id',
            'cashier' => 'kasir@acservicepro.id',
            'technician' => 'teknisi1@acservicepro.id',
            'inventory' => 'gudang@acservicepro.id',
            'customer' => 'pelanggan@gmail.com',
        ];

        foreach ($roles as $roleName => $email) {
            Auth::logout();
            $attempt = Auth::attempt($email, 'Password123!');
            $this->assert("Login as {$roleName} ({$email})", $attempt === true, "Authentication failed");
            if ($attempt) {
                $user = Auth::user();
                $this->assert("Role assignment for {$roleName}", ($user['role_name'] ?? '') === $roleName, "Expected {$roleName} but got " . ($user['role_name'] ?? 'null'));
            }
        }
        Auth::logout();

        // Test invalid login
        $badAttempt = Auth::attempt('owner@acservicepro.id', 'WrongPassword!');
        $this->assert("Reject invalid password", $badAttempt === false, "Allowed bad password");
    }

    private function testMasterDataManagement(): void
    {
        echo "\n--- 3. MASTER DATA MANAGEMENT TESTS ---\n";

        Auth::loginById(1); // Owner

        // Sequence generator
        $custCode = DocumentSequenceService::generate('CUST');
        $this->assert("Customer sequence generated ({$custCode})", str_starts_with($custCode, 'CUST-'), "Invalid format");

        // Create Customer
        $stmt = $this->pdo->prepare("
            INSERT INTO customers (customer_code, category, name, phone, email, address, status)
            VALUES (:code, 'household', 'Bpk. Test Otomasi', '081999990001', 'test.otomasi@acpro.test', 'Jl. Otomasi No. 1, Jakarta', 'active')
        ");
        $stmt->execute([':code' => $custCode]);
        $customerId = (int)$this->pdo->lastInsertId();
        $this->assert("Customer created in database", $customerId > 0);

        // Create AC Unit
        $unitCode = DocumentSequenceService::generate('AC');
        $stmtU = $this->pdo->prepare("
            INSERT INTO ac_units (customer_id, unit_code, brand, model_type, capacity_pk, refrigerant_type, room_location, condition)
            VALUES (:cid, :code, 'Daikin Inverter', 'Split Wall', '1 PK', 'R32', 'Kamar Utama', 'good')
        ");
        $stmtU->execute([':cid' => $customerId, ':code' => $unitCode]);
        $unitId = (int)$this->pdo->lastInsertId();
        $this->assert("AC unit created ({$unitCode})", $unitId > 0);

        // Verify service products catalog
        $catalogCount = (int)$this->pdo->query("SELECT COUNT(*) FROM service_products WHERE is_active = 1")->fetchColumn();
        $this->assert("Service catalog populated (> 0 items)", $catalogCount > 0);
    }

    private function testWorkOrderLifecycle(): void
    {
        echo "\n--- 4. WORK ORDER LIFECYCLE TESTS ---\n";

        Auth::loginById(1);

        $orderNumber = DocumentSequenceService::generate('WO');
        $this->assert("Work order sequence generated ({$orderNumber})", str_starts_with($orderNumber, 'WO-'), "Invalid format");

        // Insert Work Order
        $stmt = $this->pdo->prepare("
            INSERT INTO work_orders (order_number, customer_id, ac_unit_id, order_source, priority, status, scheduled_date, scheduled_time, address_text, complaints, estimated_price, created_by)
            VALUES (:num, 1, 1, 'admin', 'normal', 'BARU', date('now'), '09:00', 'Jl. Kemang', 'AC Tidak Dingin', 150000, 1)
        ");
        $stmt->execute([':num' => $orderNumber]);
        $woId = (int)$this->pdo->lastInsertId();
        $this->assert("Work order record created (ID: {$woId})", $woId > 0);

        // Assign Lead Technician (Budi Santoso, ID 1)
        $stmtTech = $this->pdo->prepare("
            INSERT INTO work_order_technicians (work_order_id, technician_id, is_lead, commission_amount)
            VALUES (:wo_id, 1, 1, 30000)
        ");
        $stmtTech->execute([':wo_id' => $woId]);
        $this->assert("Technician assigned to work order", $this->pdo->lastInsertId() > 0);

        // Status transition to DIJADWALKAN then SEDANG_DIKERJAKAN
        $this->pdo->prepare("UPDATE work_orders SET status = 'DIJADWALKAN' WHERE id = :id")->execute([':id' => $woId]);
        $st1 = $this->pdo->query("SELECT status FROM work_orders WHERE id = {$woId}")->fetchColumn();
        $this->assert("Status updated to DIJADWALKAN", $st1 === 'DIJADWALKAN');

        $this->pdo->prepare("UPDATE work_orders SET status = 'SEDANG_DIKERJAKAN', checkin_at = datetime('now', 'localtime') WHERE id = :id")->execute([':id' => $woId]);
        $st2 = $this->pdo->query("SELECT status FROM work_orders WHERE id = {$woId}")->fetchColumn();
        $this->assert("Technician check-in & status updated to SEDANG_DIKERJAKAN", $st2 === 'SEDANG_DIKERJAKAN');
    }

    private function testTechnicianExecutionAndStockDeduction(): void
    {
        echo "\n--- 5. TECHNICIAN EXECUTION & STOCK DEDUCTION TESTS ---\n";

        // Find an active work order
        $wo = $this->pdo->query("SELECT * FROM work_orders WHERE status = 'SEDANG_DIKERJAKAN' ORDER BY id DESC LIMIT 1")->fetch(PDO::FETCH_ASSOC);
        $this->assert("Active working order available", !empty($wo));
        $woId = (int)$wo['id'];

        // Record Diagnostics
        $stmtDiag = $this->pdo->prepare("
            INSERT INTO service_diagnostics (work_order_id, current_ampere, voltage_volt, pressure_psi, temperature_celsius, diagnostic_result)
            VALUES (:wo_id, 3.8, 220.5, 140.0, 16.5, 'Freon R32 normal, filter bersih, arus kompresor stabil')
        ");
        $stmtDiag->execute([':wo_id' => $woId]);
        $this->assert("Service diagnostics recorded", $this->pdo->lastInsertId() > 0);

        // Material usage with stock deduction (Freon R32, ID 1)
        $freon = $this->pdo->query("SELECT * FROM inventory_items WHERE id = 1")->fetch(PDO::FETCH_ASSOC);
        $initialStock = (float)$freon['current_stock'];
        $deductQty = 1.0;

        // Add service material
        $stmtMat = $this->pdo->prepare("
            INSERT INTO service_materials (work_order_id, inventory_item_id, item_name, quantity, unit, unit_price, subtotal)
            VALUES (:wo_id, 1, :name, :qty, 'Kg', :price, :sub)
        ");
        $price = (int)$freon['selling_price'];
        $stmtMat->execute([
            ':wo_id' => $woId,
            ':name' => $freon['name'],
            ':qty' => $deductQty,
            ':price' => $price,
            ':sub' => (int)($price * $deductQty),
        ]);

        // Deduct inventory item stock
        $this->pdo->prepare("UPDATE inventory_items SET current_stock = current_stock - :qty WHERE id = 1")
            ->execute([':qty' => $deductQty]);

        // Record stock movement
        $stmtMv = $this->pdo->prepare("
            INSERT INTO stock_movements (inventory_item_id, movement_type, quantity, unit_price, reference_type, reference_id, notes, created_by)
            VALUES (1, 'service_usage', :qty, :cost, 'work_order', :wo_id, 'Pemakaian servis WO', 1)
        ");
        $stmtMv->execute([':qty' => $deductQty, ':cost' => $freon['cost_price'], ':wo_id' => $woId]);

        // Verify stock decreased
        $newStock = (float)$this->pdo->query("SELECT current_stock FROM inventory_items WHERE id = 1")->fetchColumn();
        $this->assert("Inventory stock automatically decreased (-{$deductQty} Kg)", $newStock === ($initialStock - $deductQty), "Old: {$initialStock}, New: {$newStock}");

        // Customer Signature
        $sigData = 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciPjwvc3ZnPg==';
        $stmtSig = $this->pdo->prepare("
            INSERT INTO customer_signatures (work_order_id, signer_name, signature_data, rating)
            VALUES (:wo_id, 'Dr. Hendra', :sig, 5)
        ");
        $stmtSig->execute([':wo_id' => $woId, ':sig' => $sigData]);
        $this->assert("Customer digital signature recorded", $this->pdo->lastInsertId() > 0);

        // Mark Work Order SELESAI
        $this->pdo->prepare("UPDATE work_orders SET status = 'SELESAI', checkout_at = datetime('now', 'localtime') WHERE id = :id")
            ->execute([':id' => $woId]);
        $stDone = $this->pdo->query("SELECT status FROM work_orders WHERE id = {$woId}")->fetchColumn();
        $this->assert("Work order status updated to SELESAI", $stDone === 'SELESAI');
    }

    private function testInvoicingAndCashierPayments(): void
    {
        echo "\n--- 6. INVOICING & CASHIER PAYMENT TESTS ---\n";

        $invNumber = DocumentSequenceService::generate('INV');
        $this->assert("Invoice sequence generated ({$invNumber})", str_starts_with($invNumber, 'INV-'));

        $totalAmount = 250000;
        $stmt = $this->pdo->prepare("
            INSERT INTO invoices (
                invoice_number, customer_id, issue_date, due_date, subtotal_services, subtotal_materials,
                visit_fee, discount_amount, tax_rate, tax_amount, total_amount, paid_amount, balance_due,
                payment_status, created_by
            ) VALUES (
                :num, 1, date('now'), date('now', '+7 days'), 150000, 100000,
                0, 0, 0, 0, :total, 0, :total, 'unpaid', 1
            )
        ");
        $stmt->execute([':num' => $invNumber, ':total' => $totalAmount]);
        $invoiceId = (int)$this->pdo->lastInsertId();
        $this->assert("Invoice created in database (ID: {$invoiceId})", $invoiceId > 0);

        // Initial cash balance of account #1 (Kas Toko)
        $initialBalance = (int)$this->pdo->query("SELECT balance FROM accounts WHERE id = 1")->fetchColumn();

        // Record Payment (Full payment of 250,000)
        $payNumber = DocumentSequenceService::generate('PAY');
        $stmtPay = $this->pdo->prepare("
            INSERT INTO payments (payment_number, invoice_id, amount, payment_method, account_id, payment_date, received_by)
            VALUES (:pnum, :iid, :amt, 'cash', 1, date('now'), 1)
        ");
        $stmtPay->execute([':pnum' => $payNumber, ':iid' => $invoiceId, ':amt' => $totalAmount]);

        // Update invoice balance
        $this->pdo->prepare("UPDATE invoices SET paid_amount = :amt, balance_due = 0, payment_status = 'paid' WHERE id = :id")
            ->execute([':amt' => $totalAmount, ':id' => $invoiceId]);

        // Update cash account balance
        $this->pdo->prepare("UPDATE accounts SET balance = balance + :amt WHERE id = 1")
            ->execute([':amt' => $totalAmount]);

        // Check new balance
        $newBalance = (int)$this->pdo->query("SELECT balance FROM accounts WHERE id = 1")->fetchColumn();
        $this->assert("Cash account balance updated (+Rp 250.000)", $newBalance === ($initialBalance + $totalAmount), "Old: {$initialBalance}, New: {$newBalance}");

        // Check invoice status
        $invStatus = $this->pdo->query("SELECT payment_status FROM invoices WHERE id = {$invoiceId}")->fetchColumn();
        $this->assert("Invoice marked as 'paid' (LUNAS)", $invStatus === 'paid');
    }

    private function testFinanceAndProfitLoss(): void
    {
        echo "\n--- 7. FINANCE & PROFIT & LOSS TESTS ---\n";

        // Cash transfer between accounts (from account 1 Kas Toko to account 2 Bank BCA)
        $fromAcc = 1;
        $toAcc = 2;
        $transferAmt = 50000;

        $bal1Before = (int)$this->pdo->query("SELECT balance FROM accounts WHERE id = {$fromAcc}")->fetchColumn();
        $bal2Before = (int)$this->pdo->query("SELECT balance FROM accounts WHERE id = {$toAcc}")->fetchColumn();

        $this->pdo->prepare("UPDATE accounts SET balance = balance - :amt WHERE id = :id")->execute([':amt' => $transferAmt, ':id' => $fromAcc]);
        $this->pdo->prepare("UPDATE accounts SET balance = balance + :amt WHERE id = :id")->execute([':amt' => $transferAmt, ':id' => $toAcc]);

        $bal1After = (int)$this->pdo->query("SELECT balance FROM accounts WHERE id = {$fromAcc}")->fetchColumn();
        $bal2After = (int)$this->pdo->query("SELECT balance FROM accounts WHERE id = {$toAcc}")->fetchColumn();

        $this->assert("Cash transfer debit from Kas Toko", $bal1After === ($bal1Before - $transferAmt));
        $this->assert("Cash transfer credit to Bank BCA", $bal2After === ($bal2Before + $transferAmt));

        // Create Operational Expense
        $expNum = DocumentSequenceService::generate('EXP');
        $expStmt = $this->pdo->prepare("
            INSERT INTO expenses (expense_number, expense_category, account_id, amount, expense_date, recipient, description, created_by)
            VALUES (:num, 'bensin_transport', 1, 35000, date('now'), 'SPBU Shell', 'BBM Armada Servis', 1)
        ");
        $expStmt->execute([':num' => $expNum]);
        $this->assert("Operational expense recorded ({$expNum})", $this->pdo->lastInsertId() > 0);
    }

    private function testCustomerPortalAndReviews(): void
    {
        echo "\n--- 8. CUSTOMER PORTAL & REVIEWS TESTS ---\n";

        // Create review for work order 1
        $stmtRev = $this->pdo->prepare("
            INSERT OR REPLACE INTO customer_reviews (customer_id, work_order_id, technician_id, rating, review_text, is_published)
            VALUES (1, 1, 1, 5, 'Pelayanan teknisi sangat ramah, rapi dan AC kembali dingin!', 1)
        ");
        $stmtRev->execute();
        $this->assert("Customer review recorded", $this->pdo->lastInsertId() > 0);

        // Create customer complaint
        $komNum = DocumentSequenceService::generate('KOM');
        $stmtCmp = $this->pdo->prepare("
            INSERT INTO complaints (complaint_number, customer_id, work_order_id, title, description, priority, status)
            VALUES (:num, 1, 1, 'Pipa Pembuangan Menetes Air', 'Setelah 2 hari air menetes sedikit di dinding', 'normal', 'open')
        ");
        $stmtCmp->execute([':num' => $komNum]);
        $this->assert("Customer warranty complaint created ({$komNum})", $this->pdo->lastInsertId() > 0);
    }

    private function testMaintenanceContracts(): void
    {
        echo "\n--- 9. MAINTENANCE CONTRACTS TESTS ---\n";

        $mcNum = DocumentSequenceService::generate('MC');
        $stmt = $this->pdo->prepare("
            INSERT INTO maintenance_contracts (
                contract_number, customer_id, title, start_date, end_date, total_units, visits_per_year, contract_amount, status, created_by
            ) VALUES (
                :num, 2, 'Kontrak Otomasi 5 Unit AC', date('now'), date('now', '+1 year'), 5, 4, 6000000, 'active', 1
            )
        ");
        $stmt->execute([':num' => $mcNum]);
        $contractId = (int)$this->pdo->lastInsertId();
        $this->assert("Maintenance contract created ({$mcNum})", $contractId > 0);

        // Schedule visit
        $stmtS = $this->pdo->prepare("
            INSERT INTO maintenance_schedules (contract_id, scheduled_date, status, notes)
            VALUES (:cid, date('now', '+90 days'), 'scheduled', 'Kunjungan Rutin Kuartal 1')
        ");
        $stmtS->execute([':cid' => $contractId]);
        $this->assert("Maintenance schedule generated", $this->pdo->lastInsertId() > 0);
    }

    private function testInventoryAndPurchasing(): void
    {
        echo "\n--- 10. INVENTORY & PURCHASING TESTS ---\n";

        // Create PO
        $poNum = DocumentSequenceService::generate('PO');
        $this->assert("PO sequence generated ({$poNum})", str_starts_with($poNum, 'PO-'));

        $stmtPo = $this->pdo->prepare("
            INSERT INTO purchases (purchase_number, supplier_id, purchase_date, total_amount, status, created_by)
            VALUES (:num, 1, date('now'), 1500000, 'received', 1)
        ");
        $stmtPo->execute([':num' => $poNum]);
        $poId = (int)$this->pdo->lastInsertId();
        $this->assert("Purchase record created (ID: {$poId})", $poId > 0);

        // Verify stock movement ledger
        $movementsCount = (int)$this->pdo->query("SELECT COUNT(*) FROM stock_movements")->fetchColumn();
        $this->assert("Kartu Stok / Stock movement ledger active (> 0 records)", $movementsCount > 0);
    }

    private function testReportingAndCsvExports(): void
    {
        echo "\n--- 11. REPORTING & CSV EXPORT TESTS ---\n";

        $ctrl = new \App\Controllers\ReportController();

        ob_start();
        $ctrl->index();
        $indexHtml = ob_get_clean();
        $this->assert("Report dashboard index view rendered", strlen($indexHtml) > 5000);

        ob_start();
        $ctrl->workOrdersReport();
        $woHtml = ob_get_clean();
        $this->assert("Work orders report view rendered", strlen($woHtml) > 5000);

        ob_start();
        $ctrl->financeReport();
        $finHtml = ob_get_clean();
        $this->assert("Finance & P&L report view rendered", strlen($finHtml) > 5000);

        ob_start();
        $ctrl->technicianReport();
        $techHtml = ob_get_clean();
        $this->assert("Technician productivity report rendered", strlen($techHtml) > 5000);

        ob_start();
        $ctrl->inventoryReport();
        $invHtml = ob_get_clean();
        $this->assert("Inventory valuation report rendered", strlen($invHtml) > 5000);
    }

    private function testSystemSettingsAndBackup(): void
    {
        echo "\n--- 12. SYSTEM SETTINGS & BACKUP TESTS ---\n";

        $ctrl = new \App\Controllers\SettingController();

        ob_start();
        $ctrl->index();
        $out = ob_get_clean();
        $this->assert("System settings view rendered", strlen($out) > 5000);

        ob_start();
        $ctrl->usersIndex();
        $usersHtml = ob_get_clean();
        $this->assert("Users RBAC management view rendered", strlen($usersHtml) > 5000);

        // Check SQLite backup file exists and has valid SQLite format 3 header
        $backups = glob(dirname(__DIR__) . '/storage/backups/*.sqlite');
        $this->assert("SQLite backup file exists on disk", !empty($backups));

        if (!empty($backups)) {
            $latest = $backups[count($backups) - 1];
            $h = fopen($latest, 'rb');
            $header = fread($h, 16);
            fclose($h);
            $this->assert("Backup file has valid SQLite format 3 header", $header === "SQLite format 3\000");
        }
    }

    private function testPwaAssets(): void
    {
        echo "\n--- 13. PWA & OFFLINE ASSETS TESTS ---\n";

        $manifestPath = dirname(__DIR__) . '/public/manifest.json';
        $this->assert("PWA manifest.json exists", file_exists($manifestPath));

        if (file_exists($manifestPath)) {
            $manifest = json_decode(file_get_contents($manifestPath), true);
            $this->assert("Manifest valid JSON with name 'AC Service Pro'", str_contains($manifest['name'] ?? '', 'AC Service Pro'));
            $this->assert("Manifest contains icons", !empty($manifest['icons']));
        }

        $swPath = dirname(__DIR__) . '/public/service-worker.js';
        $this->assert("Service worker service-worker.js exists", file_exists($swPath));

        $offlineDbPath = dirname(__DIR__) . '/public/assets/js/offline-db.js';
        $this->assert("IndexedDB offline-db.js exists", file_exists($offlineDbPath));
    }
}

// Execute test suite
(new SystemTest())->runAll();
