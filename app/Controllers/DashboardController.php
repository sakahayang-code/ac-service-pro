<?php

declare(strict_types=1);

namespace App\Controllers;

use PDO;
use App\Services\Auth;
use App\Services\Database;

class DashboardController
{
    public function index(): void
    {
        $user = Auth::user();
        if (!$user) {
            redirect('login');
        }

        $role = $user['role_name'];

        // Delegate to specific dashboard views based on role
        if ($role === 'technician') {
            redirect('technician/tasks');
            return;
        }

        if ($role === 'customer') {
            redirect('customer-portal/orders');
            return;
        }

        $pdo = Database::getConnection();
        $today = date('Y-m-d');
        $thisMonth = date('Y-m');

        // Work Order Counters
        $woToday = (int)$pdo->query("SELECT count(*) FROM work_orders WHERE scheduled_date = '$today' AND deleted_at IS NULL")->fetchColumn();
        $woNew = (int)$pdo->query("SELECT count(*) FROM work_orders WHERE status = 'BARU' AND deleted_at IS NULL")->fetchColumn();
        $woConfirmed = (int)$pdo->query("SELECT count(*) FROM work_orders WHERE status = 'DIKONFIRMASI' AND deleted_at IS NULL")->fetchColumn();
        $woScheduled = (int)$pdo->query("SELECT count(*) FROM work_orders WHERE status = 'DIJADWALKAN' AND deleted_at IS NULL")->fetchColumn();
        $woInProgress = (int)$pdo->query("SELECT count(*) FROM work_orders WHERE status IN ('DALAM_PERJALANAN', 'SEDANG_DIKERJAKAN', 'MENUNGGU_SPAREPART') AND deleted_at IS NULL")->fetchColumn();
        $woCompleted = (int)$pdo->query("SELECT count(*) FROM work_orders WHERE status IN ('SELESAI', 'DITAGIHKAN', 'LUNAS') AND scheduled_date = '$today' AND deleted_at IS NULL")->fetchColumn();
        $woPending = (int)$pdo->query("SELECT count(*) FROM work_orders WHERE status = 'DITUNDA' AND deleted_at IS NULL")->fetchColumn();

        // Customer & Technician Counts
        $totalCustomers = (int)$pdo->query("SELECT count(*) FROM customers WHERE deleted_at IS NULL")->fetchColumn();
        $totalActiveTechnicians = (int)$pdo->query("SELECT count(*) FROM technicians WHERE status = 'active' AND deleted_at IS NULL")->fetchColumn();

        // Financial Metrics (Actual DB queries)
        $incomeToday = (int)$pdo->query("SELECT COALESCE(SUM(amount), 0) FROM payments WHERE payment_date = '$today'")->fetchColumn();
        $incomeMonth = (int)$pdo->query("SELECT COALESCE(SUM(amount), 0) FROM payments WHERE payment_date LIKE '$thisMonth%'")->fetchColumn();
        $expenseMonth = (int)$pdo->query("SELECT COALESCE(SUM(amount), 0) FROM expenses WHERE expense_date LIKE '$thisMonth%'")->fetchColumn();
        $totalReceivables = (int)$pdo->query("SELECT COALESCE(SUM(balance_due), 0) FROM invoices WHERE payment_status != 'paid'")->fetchColumn();

        // Low stock alerts
        $lowStockItems = $pdo->query("
            SELECT sku, name, current_stock, min_stock, unit
            FROM inventory_items
            WHERE current_stock <= min_stock AND is_active = 1 AND deleted_at IS NULL
            ORDER BY current_stock ASC
            LIMIT 5
        ")->fetchAll(PDO::FETCH_ASSOC);

        // Service reminders due (next 14 days or overdue)
        $serviceReminders = $pdo->query("
            SELECT r.*, c.name as customer_name, c.phone as customer_phone, u.brand, u.model_type, u.room_location
            FROM service_reminders r
            JOIN customers c ON r.customer_id = c.id
            JOIN ac_units u ON r.ac_unit_id = u.id
            WHERE r.status = 'pending' AND r.due_date <= date('now', '+14 days')
            ORDER BY r.due_date ASC
            LIMIT 5
        ")->fetchAll(PDO::FETCH_ASSOC);

        // Maintenance contracts expiring soon (next 30 days)
        $expiringContracts = $pdo->query("
            SELECT mc.*, c.name as customer_name, c.phone as customer_phone
            FROM maintenance_contracts mc
            JOIN customers c ON mc.customer_id = c.id
            WHERE mc.status = 'active' AND mc.end_date <= date('now', '+30 days')
            ORDER BY mc.end_date ASC
            LIMIT 5
        ")->fetchAll(PDO::FETCH_ASSOC);

        // Recent Work Orders
        $recentWorkOrders = $pdo->query("
            SELECT wo.*, c.name as customer_name, c.phone as customer_phone, u.brand as ac_brand, u.capacity_pk
            FROM work_orders wo
            JOIN customers c ON wo.customer_id = c.id
            LEFT JOIN ac_units u ON wo.ac_unit_id = u.id
            WHERE wo.deleted_at IS NULL
            ORDER BY wo.id DESC
            LIMIT 6
        ")->fetchAll(PDO::FETCH_ASSOC);

        // Technician Performance
        $technicianLeaderboard = $pdo->query("
            SELECT t.*, u.name as user_name
            FROM technicians t
            JOIN users u ON t.user_id = u.id
            WHERE t.deleted_at IS NULL
            ORDER BY t.total_jobs_done DESC, t.rating DESC
            LIMIT 5
        ")->fetchAll(PDO::FETCH_ASSOC);

        // Monthly Income Chart Data (Last 6 Months)
        $chartLabels = [];
        $chartIncome = [];
        $chartExpenses = [];
        for ($i = 5; $i >= 0; $i--) {
            $m = date('Y-m', strtotime("-$i months"));
            $label = date('M Y', strtotime("-$i months"));
            $inc = (int)$pdo->query("SELECT COALESCE(SUM(amount), 0) FROM payments WHERE payment_date LIKE '$m%'")->fetchColumn();
            $exp = (int)$pdo->query("SELECT COALESCE(SUM(amount), 0) FROM expenses WHERE expense_date LIKE '$m%'")->fetchColumn();
            $chartLabels[] = $label;
            $chartIncome[] = $inc;
            $chartExpenses[] = $exp;
        }

        view('admin.dashboard', [
            'title' => 'Dashboard Utama',
            'user' => $user,
            'role' => $role,
            'woToday' => $woToday,
            'woNew' => $woNew,
            'woConfirmed' => $woConfirmed,
            'woScheduled' => $woScheduled,
            'woInProgress' => $woInProgress,
            'woCompleted' => $woCompleted,
            'woPending' => $woPending,
            'totalCustomers' => $totalCustomers,
            'totalActiveTechnicians' => $totalActiveTechnicians,
            'incomeToday' => $incomeToday,
            'incomeMonth' => $incomeMonth,
            'expenseMonth' => $expenseMonth,
            'totalReceivables' => $totalReceivables,
            'lowStockItems' => $lowStockItems,
            'serviceReminders' => $serviceReminders,
            'expiringContracts' => $expiringContracts,
            'recentWorkOrders' => $recentWorkOrders,
            'technicianLeaderboard' => $technicianLeaderboard,
            'chartLabels' => $chartLabels,
            'chartIncome' => $chartIncome,
            'chartExpenses' => $chartExpenses,
        ]);
    }
}
