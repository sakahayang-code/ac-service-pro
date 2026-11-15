<?php

declare(strict_types=1);

namespace App\Controllers;

use PDO;
use App\Services\Database;

class ReportController
{
    public function index(): void
    {
        $pdo = Database::getConnection();
        $thisMonth = date('Y-m');

        // Overview metrics
        $totalOrdersThisMonth = (int)$pdo->query("SELECT COUNT(*) FROM work_orders WHERE scheduled_date LIKE '{$thisMonth}%' AND deleted_at IS NULL")->fetchColumn();
        $completedOrdersThisMonth = (int)$pdo->query("SELECT COUNT(*) FROM work_orders WHERE scheduled_date LIKE '{$thisMonth}%' AND status IN ('SELESAI', 'DITAGIHKAN', 'LUNAS') AND deleted_at IS NULL")->fetchColumn();

        $revenueThisMonth = (int)$pdo->query("
            SELECT COALESCE(SUM(total_amount), 0)
            FROM invoices
            WHERE issue_date LIKE '{$thisMonth}%'
        ")->fetchColumn();

        $cogsThisMonth = (int)$pdo->query("
            SELECT COALESCE(SUM(quantity * unit_price), 0)
            FROM stock_movements
            WHERE movement_type = 'service_usage' AND created_at LIKE '{$thisMonth}%'
        ")->fetchColumn();

        $expensesThisMonth = (int)$pdo->query("
            SELECT COALESCE(SUM(amount), 0)
            FROM expenses
            WHERE expense_date LIKE '{$thisMonth}%' AND expense_category != 'pembelian_sparepart'
        ")->fetchColumn();

        $netProfit = $revenueThisMonth - $cogsThisMonth - $expensesThisMonth;

        $inventoryValuation = (int)$pdo->query("
            SELECT COALESCE(SUM(current_stock * cost_price), 0)
            FROM inventory_items
            WHERE deleted_at IS NULL
        ")->fetchColumn();

        view('admin.reports.index', [
            'title' => 'Pusat Laporan & Analisis Bisnis',
            'thisMonth' => $thisMonth,
            'totalOrders' => $totalOrdersThisMonth,
            'completedOrders' => $completedOrdersThisMonth,
            'revenue' => $revenueThisMonth,
            'netProfit' => $netProfit,
            'inventoryValuation' => $inventoryValuation,
        ]);
    }

    public function workOrdersReport(): void
    {
        $pdo = Database::getConnection();
        $startDate = trim($_GET['start_date'] ?? date('Y-m-01'));
        $endDate = trim($_GET['end_date'] ?? date('Y-m-t'));
        $status = trim($_GET['status'] ?? '');
        $export = trim($_GET['export'] ?? '');

        $sql = "SELECT wo.*, c.name as customer_name, c.customer_code, c.category as customer_category,
                       t.full_name as technician_name, inv.total_amount, inv.payment_status
                FROM work_orders wo
                JOIN customers c ON wo.customer_id = c.id
                LEFT JOIN work_order_technicians wot ON wo.id = wot.work_order_id AND wot.is_lead = 1
                LEFT JOIN technicians t ON wot.technician_id = t.id
                LEFT JOIN invoices inv ON inv.work_order_id = wo.id
                WHERE wo.scheduled_date BETWEEN :sdate AND :edate AND wo.deleted_at IS NULL";
        $params = [':sdate' => $startDate, ':edate' => $endDate];

        if ($status !== '') {
            $sql .= " AND wo.status = :status";
            $params[':status'] = $status;
        }

        $sql .= " ORDER BY wo.scheduled_date DESC, wo.id DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($export === 'csv') {
            header('Content-Type: text/csv; charset=UTF-8');
            header('Content-Disposition: attachment; filename="laporan_work_orders_' . $startDate . '_' . $endDate . '.csv"');
            $out = fopen('php://output', 'w');
            fputcsv($out, ['No. SPK', 'Tanggal', 'Pelanggan', 'Kategori', 'Teknisi', 'Keluhan', 'Status', 'Nominal Tagihan', 'Status Bayar']);
            foreach ($orders as $o) {
                fputcsv($out, [
                    $o['order_number'],
                    $o['scheduled_date'],
                    $o['customer_name'],
                    $o['customer_category'],
                    $o['technician_name'] ?? '-',
                    $o['complaints'],
                    $o['status'],
                    $o['total_amount'] ?? 0,
                    $o['payment_status'] ?? 'unbilled'
                ]);
            }
            fclose($out);
            exit;
        }

        view('admin.reports.work_orders', [
            'title' => 'Laporan Surat Perintah Kerja (SPK)',
            'orders' => $orders,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'selectedStatus' => $status,
        ]);
    }

    public function financeReport(): void
    {
        $pdo = Database::getConnection();
        $startDate = trim($_GET['start_date'] ?? date('Y-m-01'));
        $endDate = trim($_GET['end_date'] ?? date('Y-m-t'));
        $export = trim($_GET['export'] ?? '');

        // Invoices in range
        $stmtInv = $pdo->prepare("
            SELECT inv.*, c.name as customer_name
            FROM invoices inv
            JOIN customers c ON inv.customer_id = c.id
            WHERE inv.issue_date BETWEEN :sdate AND :edate
            ORDER BY inv.issue_date DESC
        ");
        $stmtInv->execute([':sdate' => $startDate, ':edate' => $endDate]);
        $invoices = $stmtInv->fetchAll(PDO::FETCH_ASSOC);

        // Revenue breakdown
        $stmtRev = $pdo->prepare("
            SELECT ii.item_type, COALESCE(SUM(ii.subtotal), 0) as total
            FROM invoice_items ii
            JOIN invoices inv ON ii.invoice_id = inv.id
            WHERE inv.issue_date BETWEEN :sdate AND :edate
            GROUP BY ii.item_type
        ");
        $stmtRev->execute([':sdate' => $startDate, ':edate' => $endDate]);
        $revRows = $stmtRev->fetchAll(PDO::FETCH_KEY_PAIR);
        $serviceRevenue = (int)($revRows['service'] ?? 0);
        $materialRevenue = (int)($revRows['material'] ?? 0);
        $totalRevenue = $serviceRevenue + $materialRevenue;

        // COGS
        $stmtCogs = $pdo->prepare("
            SELECT COALESCE(SUM(quantity * unit_price), 0)
            FROM stock_movements
            WHERE movement_type = 'service_usage' AND date(created_at) BETWEEN :sdate AND :edate
        ");
        $stmtCogs->execute([':sdate' => $startDate, ':edate' => $endDate]);
        $cogs = (int)$stmtCogs->fetchColumn();

        // Expenses
        $stmtExp = $pdo->prepare("
            SELECT e.*, a.name as account_name
            FROM expenses e
            LEFT JOIN accounts a ON e.account_id = a.id
            WHERE e.expense_date BETWEEN :sdate AND :edate
            ORDER BY e.expense_date DESC
        ");
        $stmtExp->execute([':sdate' => $startDate, ':edate' => $endDate]);
        $expenses = $stmtExp->fetchAll(PDO::FETCH_ASSOC);
        $totalExpenses = 0;
        foreach ($expenses as $ex) {
            $totalExpenses += (int)$ex['amount'];
        }

        $grossProfit = $totalRevenue - $cogs;
        $netProfit = $grossProfit - $totalExpenses;

        if ($export === 'csv') {
            header('Content-Type: text/csv; charset=UTF-8');
            header('Content-Disposition: attachment; filename="laporan_keuangan_' . $startDate . '_' . $endDate . '.csv"');
            $out = fopen('php://output', 'w');
            fputcsv($out, ['LAPORAN KEUANGAN & ARUS KAS', $startDate . ' s/d ' . $endDate]);
            fputcsv($out, []);
            fputcsv($out, ['RINGKASAN LABA RUGI', 'NOMINAL (IDR)']);
            fputcsv($out, ['Pendapatan Jasa Servis', $serviceRevenue]);
            fputcsv($out, ['Pendapatan Sparepart / Material', $materialRevenue]);
            fputcsv($out, ['TOTAL PENDAPATAN', $totalRevenue]);
            fputcsv($out, ['Beban Pokok Material / HPP (COGS)', -$cogs]);
            fputcsv($out, ['LABA KOTOR (GROSS PROFIT)', $grossProfit]);
            fputcsv($out, ['Beban Operasional Usaha', -$totalExpenses]);
            fputcsv($out, ['LABA BERSIH (NET PROFIT)', $netProfit]);
            fputcsv($out, []);
            fputcsv($out, ['RINCIAN FAKTUR TAGIHAN']);
            fputcsv($out, ['No. Faktur', 'Tanggal', 'Pelanggan', 'Subtotal Jasa', 'Subtotal Part', 'Total', 'Dibayar', 'Sisa', 'Status']);
            foreach ($invoices as $inv) {
                fputcsv($out, [
                    $inv['invoice_number'],
                    $inv['issue_date'],
                    $inv['customer_name'],
                    $inv['subtotal_services'],
                    $inv['subtotal_materials'],
                    $inv['total_amount'],
                    $inv['paid_amount'],
                    $inv['balance_due'],
                    $inv['payment_status']
                ]);
            }
            fclose($out);
            exit;
        }

        view('admin.reports.finance', [
            'title' => 'Laporan Keuangan & Laba Rugi',
            'invoices' => $invoices,
            'expenses' => $expenses,
            'serviceRevenue' => $serviceRevenue,
            'materialRevenue' => $materialRevenue,
            'totalRevenue' => $totalRevenue,
            'cogs' => $cogs,
            'grossProfit' => $grossProfit,
            'totalExpenses' => $totalExpenses,
            'netProfit' => $netProfit,
            'startDate' => $startDate,
            'endDate' => $endDate,
        ]);
    }

    public function technicianReport(): void
    {
        $pdo = Database::getConnection();
        $export = trim($_GET['export'] ?? '');

        $technicians = $pdo->query("
            SELECT t.*, u.email,
                   (SELECT COUNT(*) FROM work_order_technicians wot JOIN work_orders wo ON wot.work_order_id = wo.id WHERE wot.technician_id = t.id AND wo.deleted_at IS NULL) as total_assigned,
                   (SELECT COUNT(*) FROM work_order_technicians wot JOIN work_orders wo ON wot.work_order_id = wo.id WHERE wot.technician_id = t.id AND wo.status IN ('SELESAI', 'DITAGIHKAN', 'LUNAS') AND wo.deleted_at IS NULL) as total_completed,
                   (SELECT COALESCE(SUM(commission_amount), 0) FROM work_order_technicians WHERE technician_id = t.id) as total_commissions
            FROM technicians t
            JOIN users u ON t.user_id = u.id
            ORDER BY total_completed DESC, t.rating DESC
        ")->fetchAll(PDO::FETCH_ASSOC);

        if ($export === 'csv') {
            header('Content-Type: text/csv; charset=UTF-8');
            header('Content-Disposition: attachment; filename="laporan_produktivitas_teknisi_' . date('Y-m-d') . '.csv"');
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Kode', 'Nama Teknisi', 'No. HP', 'Area Kerja', 'Rating', 'Tugas Diberikan', 'Tugas Selesai', 'Rasio Penyelesaian (%)', 'Total Komisi (IDR)']);
            foreach ($technicians as $t) {
                $rate = $t['total_assigned'] > 0 ? round(($t['total_completed'] / $t['total_assigned']) * 100, 1) : 0;
                fputcsv($out, [
                    $t['code'],
                    $t['full_name'],
                    $t['phone'],
                    $t['work_area'],
                    $t['rating'],
                    $t['total_assigned'],
                    $t['total_completed'],
                    $rate . '%',
                    $t['total_commissions']
                ]);
            }
            fclose($out);
            exit;
        }

        view('admin.reports.technician', [
            'title' => 'Laporan Produktivitas & Komisi Teknisi',
            'technicians' => $technicians,
        ]);
    }

    public function inventoryReport(): void
    {
        $pdo = Database::getConnection();
        $export = trim($_GET['export'] ?? '');

        $items = $pdo->query("
            SELECT i.*, 
                   (i.current_stock * i.cost_price) as valuation_purchase,
                   (i.current_stock * i.selling_price) as valuation_selling,
                   (SELECT COALESCE(SUM(quantity), 0) FROM stock_movements WHERE inventory_item_id = i.id AND movement_type = 'service_usage') as total_used
            FROM inventory_items i
            WHERE i.deleted_at IS NULL
            ORDER BY i.category ASC, i.name ASC
        ")->fetchAll(PDO::FETCH_ASSOC);

        $totalValuation = 0;
        $totalItemsCount = 0;
        $lowStockCount = 0;

        foreach ($items as $it) {
            $totalValuation += (int)$it['valuation_purchase'];
            $totalItemsCount += (int)$it['current_stock'];
            if ((int)$it['current_stock'] <= (int)$it['min_stock']) {
                $lowStockCount++;
            }
        }

        if ($export === 'csv') {
            header('Content-Type: text/csv; charset=UTF-8');
            header('Content-Disposition: attachment; filename="laporan_stok_inventori_' . date('Y-m-d') . '.csv"');
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Kode SKU', 'Nama Sparepart / Material', 'Kategori', 'Satuan', 'Stok Saat Ini', 'Min. Stok', 'Harga Beli (HPP)', 'Harga Jual', 'Total Nilai Aset (HPP)', 'Terpakai di WO', 'Status Stok']);
            foreach ($items as $it) {
                fputcsv($out, [
                    $it['sku'],
                    $it['name'],
                    $it['category'],
                    $it['unit'],
                    $it['current_stock'],
                    $it['min_stock'],
                    $it['cost_price'],
                    $it['selling_price'],
                    $it['valuation_purchase'],
                    $it['total_used'],
                    $it['current_stock'] <= $it['min_stock'] ? 'MENIPIS' : 'AMAN'
                ]);
            }
            fclose($out);
            exit;
        }

        view('admin.reports.inventory', [
            'title' => 'Laporan Nilai Stok & Pemakaian Sparepart',
            'items' => $items,
            'totalValuation' => $totalValuation,
            'totalItemsCount' => $totalItemsCount,
            'lowStockCount' => $lowStockCount,
        ]);
    }
}
