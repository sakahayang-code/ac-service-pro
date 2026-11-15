<?php

declare(strict_types=1);

namespace App\Controllers;

use PDO;
use App\Services\Auth;
use App\Services\Database;
use App\Services\DocumentSequenceService;

class FinanceController
{
    public function index(): void
    {
        $pdo = Database::getConnection();
        $thisMonth = date('Y-m');

        // Accounts list & balances
        $accounts = $pdo->query("SELECT * FROM accounts WHERE is_active = 1 ORDER BY code ASC")->fetchAll(PDO::FETCH_ASSOC);

        // Recent cash transactions
        $transactions = $pdo->query("
            SELECT ct.*, a.name as account_name, a.code as account_code, u.name as created_by_name
            FROM cash_transactions ct
            JOIN accounts a ON ct.account_id = a.id
            LEFT JOIN users u ON ct.created_by = u.id
            ORDER BY ct.id DESC
            LIMIT 30
        ")->fetchAll(PDO::FETCH_ASSOC);

        // Profit & Loss calculation for this month
        $revenueServices = (int)$pdo->query("
            SELECT COALESCE(SUM(ii.subtotal), 0)
            FROM invoice_items ii
            JOIN invoices inv ON ii.invoice_id = inv.id
            WHERE ii.item_type = 'service' AND inv.issue_date LIKE '$thisMonth%'
        ")->fetchColumn();

        $revenueMaterials = (int)$pdo->query("
            SELECT COALESCE(SUM(ii.subtotal), 0)
            FROM invoice_items ii
            JOIN invoices inv ON ii.invoice_id = inv.id
            WHERE ii.item_type = 'material' AND inv.issue_date LIKE '$thisMonth%'
        ")->fetchColumn();

        $totalRevenue = $revenueServices + $revenueMaterials;

        $cogs = (int)$pdo->query("
            SELECT COALESCE(SUM(sm.quantity * sm.unit_price), 0)
            FROM stock_movements sm
            WHERE sm.movement_type = 'service_usage' AND sm.created_at LIKE '$thisMonth%'
        ")->fetchColumn();

        $grossProfit = $totalRevenue - $cogs;

        $operatingExpenses = (int)$pdo->query("
            SELECT COALESCE(SUM(amount), 0)
            FROM expenses
            WHERE expense_date LIKE '$thisMonth%' AND expense_category != 'pembelian_sparepart'
        ")->fetchColumn();

        $netProfit = $grossProfit - $operatingExpenses;

        view('admin.finance.index', [
            'title' => 'Buku Kas & Laporan Laba Rugi',
            'accounts' => $accounts,
            'transactions' => $transactions,
            'thisMonth' => $thisMonth,
            'revenueServices' => $revenueServices,
            'revenueMaterials' => $revenueMaterials,
            'totalRevenue' => $totalRevenue,
            'cogs' => $cogs,
            'grossProfit' => $grossProfit,
            'operatingExpenses' => $operatingExpenses,
            'netProfit' => $netProfit,
        ]);
    }

    public function expensesIndex(): void
    {
        $pdo = Database::getConnection();
        $expenses = $pdo->query("
            SELECT e.*, a.name as account_name, u.name as created_by_name
            FROM expenses e
            LEFT JOIN accounts a ON e.account_id = a.id
            LEFT JOIN users u ON e.created_by = u.id
            ORDER BY e.id DESC
        ")->fetchAll(PDO::FETCH_ASSOC);

        $accounts = $pdo->query("SELECT id, code, name, balance FROM accounts WHERE type = 'asset' AND is_active = 1 ORDER BY code ASC")->fetchAll(PDO::FETCH_ASSOC);

        view('admin.finance.expenses', [
            'title' => 'Pengeluaran Kas Operasional',
            'expenses' => $expenses,
            'accounts' => $accounts,
        ]);
    }

    public function createExpense(): void
    {
        $pdo = Database::getConnection();
        $accounts = $pdo->query("SELECT id, code, name, balance FROM accounts WHERE type = 'asset' AND is_active = 1 ORDER BY code ASC")->fetchAll(PDO::FETCH_ASSOC);

        view('admin.finance.expense_create', [
            'title' => 'Catat Pengeluaran Kas Baru',
            'accounts' => $accounts,
        ]);
    }

    public function storeExpense(): void
    {
        $category = trim($_POST['expense_category'] ?? 'operasional');
        $accountId = (int)($_POST['account_id'] ?? 0);
        $amount = (int)($_POST['amount'] ?? 0);
        $expenseDate = trim($_POST['expense_date'] ?? date('Y-m-d'));
        $recipient = trim($_POST['recipient'] ?? '');
        $description = trim($_POST['description'] ?? '');

        if ($accountId <= 0 || $amount <= 0 || empty($recipient) || empty($description)) {
            $_SESSION['flash_error'] = 'Lengkapi semua kolom pengeluaran secara valid.';
            redirect('expenses/create');
            return;
        }

        $pdo = Database::getConnection();
        $expNum = DocumentSequenceService::generate('EXP');

        Database::transaction(function (PDO $pdo) use ($expNum, $category, $accountId, $amount, $expenseDate, $recipient, $description) {
            $stmt = $pdo->prepare("
                INSERT INTO expenses (expense_number, expense_category, account_id, amount, expense_date, recipient, description, created_by)
                VALUES (:num, :cat, :acc, :amount, :date, :rec, :desc, :uid)
            ");
            $stmt->execute([
                ':num' => $expNum,
                ':cat' => $category,
                ':acc' => $accountId,
                ':amount' => $amount,
                ':date' => $expenseDate,
                ':rec' => $recipient,
                ':desc' => $description,
                ':uid' => Auth::id(),
            ]);
            $expId = (int)$pdo->lastInsertId();

            // Deduct account balance
            $pdo->prepare("UPDATE accounts SET balance = balance - :amount, updated_at = datetime('now', 'localtime') WHERE id = :id")
                ->execute([':amount' => $amount, ':id' => $accountId]);

            // Record cash transaction
            $pdo->prepare("
                INSERT INTO cash_transactions (transaction_number, account_id, type, amount, reference_type, reference_id, description, created_by)
                VALUES (:num, :acc, 'out', :amount, 'expense', :exp_id, :desc, :uid)
            ")->execute([
                ':num' => $expNum,
                ':acc' => $accountId,
                ':amount' => $amount,
                ':exp_id' => $expId,
                ':desc' => "Pengeluaran {$category} kepada {$recipient}: {$description}",
                ':uid' => Auth::id(),
            ]);

            audit_log('create', 'expenses', $expId, null, ['amount' => $amount, 'category' => $category]);
        });

        $_SESSION['flash_success'] = "Pengeluaran kas {$expNum} sebesar " . format_rupiah($amount) . " berhasil dicatat!";
        redirect('expenses');
    }

    public function transferCash(): void
    {
        $fromAcc = (int)($_POST['from_account_id'] ?? 0);
        $toAcc = (int)($_POST['to_account_id'] ?? 0);
        $amount = (int)($_POST['amount'] ?? 0);
        $notes = trim($_POST['notes'] ?? 'Transfer antar rekening');

        if ($fromAcc <= 0 || $toAcc <= 0 || $fromAcc === $toAcc || $amount <= 0) {
            $_SESSION['flash_error'] = 'Pilih rekening asal dan tujuan yang berbeda serta jumlah transfer yang valid.';
            redirect('finance');
            return;
        }

        $pdo = Database::getConnection();

        Database::transaction(function (PDO $pdo) use ($fromAcc, $toAcc, $amount, $notes) {
            $fromName = $pdo->query("SELECT name FROM accounts WHERE id = $fromAcc")->fetchColumn();
            $toName = $pdo->query("SELECT name FROM accounts WHERE id = $toAcc")->fetchColumn();

            // Deduct from
            $pdo->prepare("UPDATE accounts SET balance = balance - :amount WHERE id = :id")->execute([':amount' => $amount, ':id' => $fromAcc]);
            // Add to
            $pdo->prepare("UPDATE accounts SET balance = balance + :amount WHERE id = :id")->execute([':amount' => $amount, ':id' => $toAcc]);

            // Trx records
            $num1 = DocumentSequenceService::generate('EXP');
            $pdo->prepare("INSERT INTO cash_transactions (transaction_number, account_id, type, amount, reference_type, description, created_by) VALUES (:num, :acc, 'out', :amount, 'transfer', :desc, :uid)")
                ->execute([':num' => $num1, ':acc' => $fromAcc, ':amount' => $amount, ':desc' => "Transfer keluar ke {$toName}: {$notes}", ':uid' => Auth::id()]);

            $num2 = DocumentSequenceService::generate('PAY');
            $pdo->prepare("INSERT INTO cash_transactions (transaction_number, account_id, type, amount, reference_type, description, created_by) VALUES (:num, :acc, 'in', :amount, 'transfer', :desc, :uid)")
                ->execute([':num' => $num2, ':acc' => $toAcc, ':amount' => $amount, ':desc' => "Transfer masuk dari {$fromName}: {$notes}", ':uid' => Auth::id()]);
        });

        $_SESSION['flash_success'] = 'Transfer dana antar rekening kas/bank berhasil!';
        redirect('finance');
    }
}
