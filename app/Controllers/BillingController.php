<?php

declare(strict_types=1);

namespace App\Controllers;

use PDO;
use App\Services\Auth;
use App\Services\Database;
use App\Services\DocumentSequenceService;

class BillingController
{
    public function index(): void
    {
        $pdo = Database::getConnection();
        $status = trim($_GET['status'] ?? '');
        $search = trim($_GET['q'] ?? '');

        $sql = "SELECT inv.*, c.name as customer_name, c.phone as customer_phone, wo.order_number
                FROM invoices inv
                JOIN customers c ON inv.customer_id = c.id
                LEFT JOIN work_orders wo ON inv.work_order_id = wo.id
                WHERE 1=1";
        $params = [];

        if ($status !== '') {
            $sql .= " AND inv.payment_status = :status";
            $params[':status'] = $status;
        }

        if ($search !== '') {
            $sql .= " AND (inv.invoice_number LIKE :q OR c.name LIKE :q OR wo.order_number LIKE :q)";
            $params[':q'] = '%' . $search . '%';
        }

        $sql .= " ORDER BY inv.id DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $invoices = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Summaries
        $totalReceivables = (int)$pdo->query("SELECT COALESCE(SUM(balance_due), 0) FROM invoices WHERE payment_status != 'paid'")->fetchColumn();
        $totalPaidMonth = (int)$pdo->query("SELECT COALESCE(SUM(amount), 0) FROM payments WHERE payment_date LIKE '" . date('Y-m') . "%'")->fetchColumn();

        view('admin.billing.index', [
            'title' => 'Faktur & Kasir Pembayaran',
            'invoices' => $invoices,
            'currentStatus' => $status,
            'search' => $search,
            'totalReceivables' => $totalReceivables,
            'totalPaidMonth' => $totalPaidMonth,
        ]);
    }

    public function create(): void
    {
        $pdo = Database::getConnection();
        $woId = isset($_GET['work_order_id']) ? (int)$_GET['work_order_id'] : null;

        $workOrders = $pdo->query("
            SELECT wo.id, wo.order_number, wo.customer_id, c.name as customer_name, wo.final_price, wo.estimated_price
            FROM work_orders wo
            JOIN customers c ON wo.customer_id = c.id
            WHERE wo.deleted_at IS NULL AND wo.status NOT IN ('LUNAS', 'DIBATALKAN')
            ORDER BY wo.id DESC
        ")->fetchAll(PDO::FETCH_ASSOC);

        $customers = $pdo->query("SELECT id, name, phone FROM customers WHERE deleted_at IS NULL ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);

        // If WO selected, grab services & materials
        $selectedWo = null;
        $woServices = [];
        $woMaterials = [];
        if ($woId) {
            $stmtWo = $pdo->prepare("SELECT wo.*, c.name as customer_name, c.phone as customer_phone FROM work_orders wo JOIN customers c ON wo.customer_id = c.id WHERE wo.id = :id");
            $stmtWo->execute([':id' => $woId]);
            $selectedWo = $stmtWo->fetch(PDO::FETCH_ASSOC);

            if ($selectedWo) {
                $stmtSrv = $pdo->prepare("SELECT * FROM work_order_services WHERE work_order_id = :id");
                $stmtSrv->execute([':id' => $woId]);
                $woServices = $stmtSrv->fetchAll(PDO::FETCH_ASSOC);

                $stmtMat = $pdo->prepare("SELECT * FROM service_materials WHERE work_order_id = :id");
                $stmtMat->execute([':id' => $woId]);
                $woMaterials = $stmtMat->fetchAll(PDO::FETCH_ASSOC);
            }
        }

        view('admin.billing.create', [
            'title' => 'Buat Faktur Tagihan Baru',
            'workOrders' => $workOrders,
            'customers' => $customers,
            'selectedWoId' => $woId,
            'selectedWo' => $selectedWo,
            'woServices' => $woServices,
            'woMaterials' => $woMaterials,
        ]);
    }

    public function store(): void
    {
        $customerId = (int)($_POST['customer_id'] ?? 0);
        $woId = !empty($_POST['work_order_id']) ? (int)$_POST['work_order_id'] : null;
        $issueDate = trim($_POST['issue_date'] ?? date('Y-m-d'));
        $dueDate = trim($_POST['due_date'] ?? date('Y-m-d', strtotime('+7 days')));
        $visitFee = (int)($_POST['visit_fee'] ?? 0);
        $discountAmount = (int)($_POST['discount_amount'] ?? 0);
        $notes = trim($_POST['notes'] ?? '');

        $itemDescriptions = $_POST['descriptions'] ?? [];
        $itemTypes = $_POST['types'] ?? [];
        $itemQuantities = $_POST['quantities'] ?? [];
        $itemPrices = $_POST['prices'] ?? [];

        if ($customerId <= 0 || empty($itemDescriptions)) {
            $_SESSION['flash_error'] = 'Pilih pelanggan dan sertakan minimal 1 baris item tagihan.';
            redirect('invoices/create');
            return;
        }

        $pdo = Database::getConnection();
        $invoiceNumber = DocumentSequenceService::generate('INV');

        $invoiceId = Database::transaction(function (PDO $pdo) use (
            $invoiceNumber, $woId, $customerId, $issueDate, $dueDate,
            $visitFee, $discountAmount, $notes, $itemDescriptions, $itemTypes, $itemQuantities, $itemPrices
        ) {
            $subtotalServices = 0;
            $subtotalMaterials = 0;
            $itemsToInsert = [];

            foreach ($itemDescriptions as $idx => $desc) {
                $qty = (float)($itemQuantities[$idx] ?? 1.0);
                $price = (int)($itemPrices[$idx] ?? 0);
                $sub = (int)round($qty * $price);
                $type = $itemTypes[$idx] ?? 'service';

                if ($type === 'service') {
                    $subtotalServices += $sub;
                } else {
                    $subtotalMaterials += $sub;
                }

                $itemsToInsert[] = [
                    'type' => $type,
                    'desc' => $desc,
                    'qty' => $qty,
                    'price' => $price,
                    'subtotal' => $sub,
                ];
            }

            $totalAmount = ($subtotalServices + $subtotalMaterials + $visitFee) - $discountAmount;
            if ($totalAmount < 0) $totalAmount = 0;

            $stmtInv = $pdo->prepare("
                INSERT INTO invoices (
                    invoice_number, work_order_id, customer_id, issue_date, due_date,
                    subtotal_services, subtotal_materials, visit_fee, discount_amount,
                    tax_rate, tax_amount, total_amount, paid_amount, balance_due, payment_status,
                    notes, created_by
                ) VALUES (
                    :num, :wo_id, :cid, :issue, :due,
                    :srv, :mat, :fee, :disc,
                    0, 0, :total, 0, :total, 'unpaid',
                    :notes, :by
                )
            ");
            $stmtInv->execute([
                ':num' => $invoiceNumber,
                ':wo_id' => $woId,
                ':cid' => $customerId,
                ':issue' => $issueDate,
                ':due' => $dueDate,
                ':srv' => $subtotalServices,
                ':mat' => $subtotalMaterials,
                ':fee' => $visitFee,
                ':disc' => $discountAmount,
                ':total' => $totalAmount,
                ':notes' => $notes ?: null,
                ':by' => Auth::id(),
            ]);
            $invoiceId = (int)$pdo->lastInsertId();

            // Insert invoice items
            $stmtItem = $pdo->prepare("
                INSERT INTO invoice_items (invoice_id, item_type, description, quantity, unit_price, subtotal)
                VALUES (:iid, :type, :desc, :qty, :price, :sub)
            ");
            foreach ($itemsToInsert as $it) {
                $stmtItem->execute([
                    ':iid' => $invoiceId,
                    ':type' => $it['type'],
                    ':desc' => $it['desc'],
                    ':qty' => $it['qty'],
                    ':price' => $it['price'],
                    ':sub' => $it['subtotal'],
                ]);
            }

            // Update WO status to DITAGIHKAN if linked
            if ($woId) {
                $pdo->prepare("UPDATE work_orders SET status = 'DITAGIHKAN', updated_at = datetime('now', 'localtime') WHERE id = :wo_id")
                    ->execute([':wo_id' => $woId]);
            }

            audit_log('create', 'invoices', $invoiceId, null, ['number' => $invoiceNumber, 'total' => $totalAmount]);
            return $invoiceId;
        });

        $_SESSION['flash_success'] = "Faktur {$invoiceNumber} berhasil diterbitkan!";
        redirect('invoices/' . $invoiceId);
    }

    public function show(string $id): void
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            SELECT inv.*, c.name as customer_name, c.customer_code, c.phone as customer_phone, c.address as customer_address,
                   wo.order_number, wo.scheduled_date as wo_date
            FROM invoices inv
            JOIN customers c ON inv.customer_id = c.id
            LEFT JOIN work_orders wo ON inv.work_order_id = wo.id
            WHERE inv.id = :id
            LIMIT 1
        ");
        $stmt->execute([':id' => (int)$id]);
        $invoice = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$invoice) {
            redirect('invoices');
            return;
        }

        $items = $pdo->prepare("SELECT * FROM invoice_items WHERE invoice_id = :id");
        $items->execute([':id' => (int)$id]);
        $invoiceItems = $items->fetchAll(PDO::FETCH_ASSOC);

        $payments = $pdo->prepare("
            SELECT p.*, a.name as account_name, u.name as received_by_name
            FROM payments p
            LEFT JOIN accounts a ON p.account_id = a.id
            LEFT JOIN users u ON p.received_by = u.id
            WHERE p.invoice_id = :id
            ORDER BY p.id DESC
        ");
        $payments->execute([':id' => (int)$id]);
        $paymentHistory = $payments->fetchAll(PDO::FETCH_ASSOC);

        $accounts = $pdo->query("SELECT id, code, name FROM accounts WHERE type = 'asset' AND is_active = 1 ORDER BY code ASC")->fetchAll(PDO::FETCH_ASSOC);

        view('admin.billing.show', [
            'title' => 'Faktur - ' . $invoice['invoice_number'],
            'invoice' => $invoice,
            'items' => $invoiceItems,
            'payments' => $paymentHistory,
            'accounts' => $accounts,
        ]);
    }

    public function recordPayment(string $id): void
    {
        $pdo = Database::getConnection();
        $amount = (int)($_POST['amount'] ?? 0);
        $method = trim($_POST['payment_method'] ?? 'cash');
        $accountId = (int)($_POST['account_id'] ?? 0);
        $paymentDate = trim($_POST['payment_date'] ?? date('Y-m-d'));
        $refNumber = trim($_POST['reference_number'] ?? '');
        $notes = trim($_POST['notes'] ?? '');

        if ($amount <= 0 || $accountId <= 0) {
            $_SESSION['flash_error'] = 'Jumlah pembayaran dan rekening penerima kas wajib diisi.';
            redirect('invoices/' . $id);
            return;
        }

        Database::transaction(function (PDO $pdo) use ($id, $amount, $method, $accountId, $paymentDate, $refNumber, $notes) {
            $stmtInv = $pdo->prepare("SELECT * FROM invoices WHERE id = :id");
            $stmtInv->execute([':id' => (int)$id]);
            $inv = $stmtInv->fetch(PDO::FETCH_ASSOC);

            if (!$inv) {
                throw new \RuntimeException('Invoice tidak ditemukan');
            }

            $paymentNumber = DocumentSequenceService::generate('PAY');

            // 1. Insert Payment
            $stmtPay = $pdo->prepare("
                INSERT INTO payments (
                    payment_number, invoice_id, amount, payment_method, account_id,
                    payment_date, reference_number, notes, received_by
                ) VALUES (
                    :pnum, :iid, :amount, :method, :acc,
                    :date, :ref, :notes, :uid
                )
            ");
            $stmtPay->execute([
                ':pnum' => $paymentNumber,
                ':iid' => (int)$id,
                ':amount' => $amount,
                ':method' => $method,
                ':acc' => $accountId,
                ':date' => $paymentDate,
                ':ref' => $refNumber ?: null,
                ':notes' => $notes ?: null,
                ':uid' => Auth::id(),
            ]);

            // 2. Update Invoice paid amount & balance due
            $newPaid = (int)$inv['paid_amount'] + $amount;
            $newBalance = (int)$inv['total_amount'] - $newPaid;
            $newStatus = ($newBalance <= 0) ? 'paid' : 'partial';

            $pdo->prepare("
                UPDATE invoices
                SET paid_amount = :paid, balance_due = :bal, payment_status = :status, updated_at = datetime('now', 'localtime')
                WHERE id = :id
            ")->execute([
                ':paid' => $newPaid,
                ':bal' => max(0, $newBalance),
                ':status' => $newStatus,
                ':id' => (int)$id,
            ]);

            // 3. Update Account Balance
            $pdo->prepare("UPDATE accounts SET balance = balance + :amount, updated_at = datetime('now', 'localtime') WHERE id = :id")
                ->execute([':amount' => $amount, ':id' => $accountId]);

            // 4. Record Cash Transaction
            $cashTrxNum = DocumentSequenceService::generate('PAY');
            $pdo->prepare("
                INSERT INTO cash_transactions (transaction_number, account_id, type, amount, reference_type, reference_id, description, created_by)
                VALUES (:num, :acc, 'in', :amount, 'payment', :pay_id, :desc, :uid)
            ")->execute([
                ':num' => $cashTrxNum,
                ':acc' => $accountId,
                ':amount' => $amount,
                ':pay_id' => (int)$pdo->lastInsertId(),
                ':desc' => "Penerimaan pembayaran Faktur #{$inv['invoice_number']} ({$method})",
                ':uid' => Auth::id(),
            ]);

            // 5. If fully paid and linked to Work Order, update WO to LUNAS
            if ($newStatus === 'paid' && !empty($inv['work_order_id'])) {
                $pdo->prepare("UPDATE work_orders SET status = 'LUNAS', updated_at = datetime('now', 'localtime') WHERE id = :wo_id")
                    ->execute([':wo_id' => $inv['work_order_id']]);
            }

            audit_log('record_payment', 'invoices', (int)$id, null, ['amount' => $amount, 'payment_number' => $paymentNumber]);
        });

        $_SESSION['flash_success'] = 'Pembayaran berhasil dicatat dan saldo kas kasir otomatis terupdate!';
        redirect('invoices/' . $id);
    }

    public function printInvoice(string $id): void
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            SELECT inv.*, c.name as customer_name, c.customer_code, c.phone as customer_phone, c.address as customer_address,
                   wo.order_number, wo.scheduled_date
            FROM invoices inv
            JOIN customers c ON inv.customer_id = c.id
            LEFT JOIN work_orders wo ON inv.work_order_id = wo.id
            WHERE inv.id = :id
            LIMIT 1
        ");
        $stmt->execute([':id' => (int)$id]);
        $invoice = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$invoice) {
            redirect('invoices');
            return;
        }

        $items = $pdo->prepare("SELECT * FROM invoice_items WHERE invoice_id = :id");
        $items->execute([':id' => (int)$id]);
        $invoiceItems = $items->fetchAll(PDO::FETCH_ASSOC);

        $payments = $pdo->prepare("SELECT * FROM payments WHERE invoice_id = :id");
        $payments->execute([':id' => (int)$id]);
        $paymentHistory = $payments->fetchAll(PDO::FETCH_ASSOC);

        view('admin.billing.print', [
            'title' => 'Faktur - ' . $invoice['invoice_number'],
            'invoice' => $invoice,
            'items' => $invoiceItems,
            'payments' => $paymentHistory,
        ]);
    }

    public function paymentsList(): void
    {
        $pdo = Database::getConnection();
        $payments = $pdo->query("
            SELECT p.*, inv.invoice_number, c.name as customer_name, a.name as account_name, u.name as received_by_name
            FROM payments p
            JOIN invoices inv ON p.invoice_id = inv.id
            JOIN customers c ON inv.customer_id = c.id
            LEFT JOIN accounts a ON p.account_id = a.id
            LEFT JOIN users u ON p.received_by = u.id
            ORDER BY p.id DESC
        ")->fetchAll(PDO::FETCH_ASSOC);

        view('admin.billing.payments', [
            'title' => 'Riwayat Pembayaran Kasir',
            'payments' => $payments,
        ]);
    }
}
