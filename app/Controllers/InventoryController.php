<?php

declare(strict_types=1);

namespace App\Controllers;

use PDO;
use App\Services\Auth;
use App\Services\Database;
use App\Services\DocumentSequenceService;

class InventoryController
{
    public function index(): void
    {
        $pdo = Database::getConnection();
        $search = trim($_GET['q'] ?? '');
        $category = trim($_GET['category'] ?? '');
        $lowStock = !empty($_GET['low_stock']);

        $sql = "SELECT * FROM inventory_items WHERE deleted_at IS NULL";
        $params = [];

        if ($search !== '') {
            $sql .= " AND (name LIKE :q OR sku LIKE :q OR category LIKE :q)";
            $params[':q'] = '%' . $search . '%';
        }

        if ($category !== '') {
            $sql .= " AND category = :cat";
            $params[':cat'] = $category;
        }

        if ($lowStock) {
            $sql .= " AND current_stock <= min_stock";
        }

        $sql .= " ORDER BY (current_stock <= min_stock) DESC, name ASC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Valuation stats
        $totalItems = count($items);
        $totalValuation = 0;
        foreach ($items as $item) {
            $totalValuation += (int)($item['current_stock'] * $item['cost_price']);
        }

        $categories = $pdo->query("SELECT DISTINCT category FROM inventory_items WHERE deleted_at IS NULL ORDER BY category ASC")->fetchAll(PDO::FETCH_COLUMN);

        view('admin.inventory.index', [
            'title' => 'Inventori & Suku Cadang AC',
            'items' => $items,
            'categories' => $categories,
            'search' => $search,
            'category' => $category,
            'lowStock' => $lowStock,
            'totalItems' => $totalItems,
            'totalValuation' => $totalValuation,
        ]);
    }

    public function create(): void
    {
        $pdo = Database::getConnection();
        $suppliers = $pdo->query("SELECT id, name FROM suppliers WHERE status = 'active' ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);

        view('admin.inventory.create', [
            'title' => 'Tambah Barang / Sparepart Baru',
            'suppliers' => $suppliers,
        ]);
    }

    public function store(): void
    {
        $sku = trim($_POST['sku'] ?? '');
        $barcode = trim($_POST['barcode'] ?? '');
        $name = trim($_POST['name'] ?? '');
        $category = trim($_POST['category'] ?? 'Sparepart');
        $unit = trim($_POST['unit'] ?? 'Pcs');
        $costPrice = (int)($_POST['cost_price'] ?? 0);
        $sellingPrice = (int)($_POST['selling_price'] ?? 0);
        $minStock = (float)($_POST['min_stock'] ?? 5.0);
        $initialStock = (float)($_POST['initial_stock'] ?? 0.0);

        if (empty($name) || empty($sku)) {
            $_SESSION['flash_error'] = 'SKU dan nama barang wajib diisi.';
            redirect('inventory/create');
            return;
        }

        $pdo = Database::getConnection();

        // Check if SKU exists
        $check = $pdo->prepare("SELECT id FROM inventory_items WHERE sku = :sku");
        $check->execute([':sku' => $sku]);
        if ($check->fetch()) {
            $_SESSION['flash_error'] = "SKU {$sku} sudah digunakan oleh barang lain.";
            redirect('inventory/create');
            return;
        }

        Database::transaction(function (PDO $pdo) use ($sku, $barcode, $name, $category, $unit, $costPrice, $sellingPrice, $minStock, $initialStock) {
            $stmt = $pdo->prepare("
                INSERT INTO inventory_items (
                    sku, barcode, name, category, unit, cost_price, selling_price, min_stock, current_stock
                ) VALUES (
                    :sku, :barcode, :name, :cat, :unit, :cost, :sell, :min, :cur
                )
            ");
            $stmt->execute([
                ':sku' => $sku,
                ':barcode' => $barcode ?: null,
                ':name' => $name,
                ':cat' => $category,
                ':unit' => $unit,
                ':cost' => $costPrice,
                ':sell' => $sellingPrice,
                ':min' => $minStock,
                ':cur' => $initialStock,
            ]);
            $itemId = (int)$pdo->lastInsertId();

            // Record initial stock in warehouse
            $pdo->prepare("
                INSERT INTO inventory_stocks (inventory_item_id, location_type, quantity)
                VALUES (:item_id, 'warehouse', :qty)
            ")->execute([':item_id' => $itemId, ':qty' => $initialStock]);

            if ($initialStock > 0) {
                $pdo->prepare("
                    INSERT INTO stock_movements (inventory_item_id, movement_type, reference_type, quantity, unit_price, notes, created_by)
                    VALUES (:item_id, 'in', 'initial', :qty, :cost, 'Saldo awal pembukuan', :uid)
                ")->execute([
                    ':item_id' => $itemId,
                    ':qty' => $initialStock,
                    ':cost' => $costPrice,
                    ':uid' => Auth::id(),
                ]);
            }

            audit_log('create', 'inventory_items', $itemId, null, ['sku' => $sku, 'name' => $name]);
        });

        $_SESSION['flash_success'] = "Barang {$name} ({$sku}) berhasil disimpan!";
        redirect('inventory');
    }

    public function show(string $id): void
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM inventory_items WHERE id = :id AND deleted_at IS NULL LIMIT 1");
        $stmt->execute([':id' => (int)$id]);
        $item = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$item) {
            redirect('inventory');
            return;
        }

        // Kartu Stok (Stock Movements History)
        $stmtMove = $pdo->prepare("
            SELECT sm.*, u.name as created_by_name
            FROM stock_movements sm
            LEFT JOIN users u ON sm.created_by = u.id
            WHERE sm.inventory_item_id = :id
            ORDER BY sm.created_at DESC, sm.id DESC
            LIMIT 50
        ");
        $stmtMove->execute([':id' => (int)$id]);
        $movements = $stmtMove->fetchAll(PDO::FETCH_ASSOC);

        view('admin.inventory.show', [
            'title' => 'Kartu Stok - ' . $item['name'],
            'item' => $item,
            'movements' => $movements,
        ]);
    }

    public function edit(string $id): void
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM inventory_items WHERE id = :id AND deleted_at IS NULL LIMIT 1");
        $stmt->execute([':id' => (int)$id]);
        $item = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$item) {
            redirect('inventory');
            return;
        }

        view('admin.inventory.edit', [
            'title' => 'Ubah Barang - ' . $item['name'],
            'item' => $item,
        ]);
    }

    public function update(string $id): void
    {
        $pdo = Database::getConnection();
        $name = trim($_POST['name'] ?? '');
        $category = trim($_POST['category'] ?? 'Sparepart');
        $unit = trim($_POST['unit'] ?? 'Pcs');
        $costPrice = (int)($_POST['cost_price'] ?? 0);
        $sellingPrice = (int)($_POST['selling_price'] ?? 0);
        $minStock = (float)($_POST['min_stock'] ?? 5.0);

        $stmt = $pdo->prepare("
            UPDATE inventory_items
            SET name = :name, category = :cat, unit = :unit, cost_price = :cost,
                selling_price = :sell, min_stock = :min, updated_at = datetime('now', 'localtime')
            WHERE id = :id
        ");
        $stmt->execute([
            ':name' => $name,
            ':cat' => $category,
            ':unit' => $unit,
            ':cost' => $costPrice,
            ':sell' => $sellingPrice,
            ':min' => $minStock,
            ':id' => (int)$id,
        ]);

        audit_log('update', 'inventory_items', (int)$id);
        $_SESSION['flash_success'] = 'Data barang berhasil diperbarui!';
        redirect('inventory/' . $id);
    }

    public function storeMovement(): void
    {
        $itemId = (int)($_POST['inventory_item_id'] ?? 0);
        $type = trim($_POST['movement_type'] ?? 'in'); // in, out, adjustment
        $qty = (float)($_POST['quantity'] ?? 0);
        $notes = trim($_POST['notes'] ?? '');

        if ($itemId <= 0 || $qty <= 0) {
            $_SESSION['flash_error'] = 'Barang dan jumlah stok harus diisi dengan benar.';
            redirect('inventory');
            return;
        }

        $pdo = Database::getConnection();

        Database::transaction(function (PDO $pdo) use ($itemId, $type, $qty, $notes) {
            $item = $pdo->query("SELECT cost_price FROM inventory_items WHERE id = $itemId")->fetch(PDO::FETCH_ASSOC);
            $cost = (int)($item['cost_price'] ?? 0);

            if ($type === 'in') {
                $pdo->prepare("UPDATE inventory_items SET current_stock = current_stock + :qty, updated_at = datetime('now', 'localtime') WHERE id = :id")
                    ->execute([':qty' => $qty, ':id' => $itemId]);
            } elseif ($type === 'out') {
                $pdo->prepare("UPDATE inventory_items SET current_stock = current_stock - :qty, updated_at = datetime('now', 'localtime') WHERE id = :id")
                    ->execute([':qty' => $qty, ':id' => $itemId]);
            } elseif ($type === 'adjustment') {
                $pdo->prepare("UPDATE inventory_items SET current_stock = :qty, updated_at = datetime('now', 'localtime') WHERE id = :id")
                    ->execute([':qty' => $qty, ':id' => $itemId]);
            }

            $stmtMove = $pdo->prepare("
                INSERT INTO stock_movements (inventory_item_id, movement_type, reference_type, quantity, unit_price, notes, created_by)
                VALUES (:item_id, :type, 'manual', :qty, :cost, :notes, :uid)
            ");
            $stmtMove->execute([
                ':item_id' => $itemId,
                ':type' => $type,
                ':qty' => $qty,
                ':cost' => $cost,
                ':notes' => $notes ?: 'Mutasi manual',
                ':uid' => Auth::id(),
            ]);
        });

        $_SESSION['flash_success'] = 'Mutasi stok berhasil dicatat ke kartu stok!';
        redirect('inventory/' . $itemId);
    }

    // Suppliers Sub-module
    public function suppliersIndex(): void
    {
        $pdo = Database::getConnection();
        $suppliers = $pdo->query("SELECT * FROM suppliers ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);

        view('admin.inventory.suppliers', [
            'title' => 'Daftar Pemasok / Supplier',
            'suppliers' => $suppliers,
        ]);
    }

    public function storeSupplier(): void
    {
        $name = trim($_POST['name'] ?? '');
        $contactPerson = trim($_POST['contact_person'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $address = trim($_POST['address'] ?? '');

        if (empty($name) || empty($phone)) {
            $_SESSION['flash_error'] = 'Nama supplier dan nomor telepon wajib diisi.';
            redirect('suppliers');
            return;
        }

        $pdo = Database::getConnection();
        $nextNum = (int)$pdo->query("SELECT count(*) FROM suppliers")->fetchColumn() + 1;
        $code = 'SUP-' . str_pad((string)$nextNum, 3, '0', STR_PAD_LEFT);

        $stmt = $pdo->prepare("
            INSERT INTO suppliers (code, name, contact_person, phone, email, address)
            VALUES (:code, :name, :cp, :phone, :email, :addr)
        ");
        $stmt->execute([
            ':code' => $code,
            ':name' => $name,
            ':cp' => $contactPerson ?: null,
            ':phone' => $phone,
            ':email' => $email ?: null,
            ':addr' => $address ?: null,
        ]);

        $_SESSION['flash_success'] = "Supplier {$name} berhasil ditambahkan!";
        redirect('suppliers');
    }

    // Purchases (PO) Sub-module
    public function purchasesIndex(): void
    {
        $pdo = Database::getConnection();
        $purchases = $pdo->query("
            SELECT p.*, s.name as supplier_name, u.name as created_by_name
            FROM purchases p
            JOIN suppliers s ON p.supplier_id = s.id
            LEFT JOIN users u ON p.created_by = u.id
            ORDER BY p.id DESC
        ")->fetchAll(PDO::FETCH_ASSOC);

        view('admin.inventory.purchases', [
            'title' => 'Pembelian & Pengadaan (Purchase Order)',
            'purchases' => $purchases,
        ]);
    }

    public function createPurchase(): void
    {
        $pdo = Database::getConnection();
        $suppliers = $pdo->query("SELECT id, name FROM suppliers WHERE status = 'active' ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
        $items = $pdo->query("SELECT id, sku, name, unit, cost_price FROM inventory_items WHERE is_active = 1 AND deleted_at IS NULL ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);

        view('admin.inventory.purchase_create', [
            'title' => 'Buat Purchase Order (PO) Baru',
            'suppliers' => $suppliers,
            'items' => $items,
        ]);
    }

    public function storePurchase(): void
    {
        $supplierId = (int)($_POST['supplier_id'] ?? 0);
        $purchaseDate = trim($_POST['purchase_date'] ?? date('Y-m-d'));
        $notes = trim($_POST['notes'] ?? '');
        $itemIds = $_POST['items'] ?? [];
        $quantities = $_POST['quantities'] ?? [];
        $unitCosts = $_POST['unit_costs'] ?? [];

        if ($supplierId <= 0 || empty($itemIds)) {
            $_SESSION['flash_error'] = 'Pilih supplier dan minimal 1 barang belanjaan.';
            redirect('purchases/create');
            return;
        }

        $pdo = Database::getConnection();
        $poNumber = DocumentSequenceService::generate('PO');

        Database::transaction(function (PDO $pdo) use ($poNumber, $supplierId, $purchaseDate, $notes, $itemIds, $quantities, $unitCosts) {
            $totalAmount = 0;
            $itemsToInsert = [];

            foreach ($itemIds as $idx => $itemId) {
                $qty = (float)($quantities[$idx] ?? 1.0);
                $cost = (int)($unitCosts[$idx] ?? 0);
                $subtotal = (int)round($qty * $cost);
                $totalAmount += $subtotal;

                $itemsToInsert[] = [
                    'item_id' => (int)$itemId,
                    'qty' => $qty,
                    'cost' => $cost,
                    'subtotal' => $subtotal,
                ];
            }

            // Insert Purchase
            $stmtPo = $pdo->prepare("
                INSERT INTO purchases (purchase_number, supplier_id, status, purchase_date, total_amount, paid_amount, payment_status, notes, created_by)
                VALUES (:num, :sid, 'received', :date, :total, :total, 'paid', :notes, :uid)
            ");
            $stmtPo->execute([
                ':num' => $poNumber,
                ':sid' => $supplierId,
                ':date' => $purchaseDate,
                ':total' => $totalAmount,
                ':notes' => $notes ?: null,
                ':uid' => Auth::id(),
            ]);
            $purchaseId = (int)$pdo->lastInsertId();

            // Insert items, update inventory stocks & record movement
            $stmtPoItem = $pdo->prepare("
                INSERT INTO purchase_items (purchase_id, inventory_item_id, quantity, unit_cost, subtotal)
                VALUES (:pid, :item_id, :qty, :cost, :subtotal)
            ");

            $stmtStockUp = $pdo->prepare("
                UPDATE inventory_items
                SET current_stock = current_stock + :qty, cost_price = :cost, updated_at = datetime('now', 'localtime')
                WHERE id = :id
            ");

            $stmtMove = $pdo->prepare("
                INSERT INTO stock_movements (inventory_item_id, movement_type, reference_type, reference_id, quantity, unit_price, notes, created_by)
                VALUES (:item_id, 'in', 'purchase', :po_id, :qty, :cost, :notes, :uid)
            ");

            foreach ($itemsToInsert as $pi) {
                $stmtPoItem->execute([
                    ':pid' => $purchaseId,
                    ':item_id' => $pi['item_id'],
                    ':qty' => $pi['qty'],
                    ':cost' => $pi['cost'],
                    ':subtotal' => $pi['subtotal'],
                ]);

                $stmtStockUp->execute([
                    ':qty' => $pi['qty'],
                    ':cost' => $pi['cost'],
                    ':id' => $pi['item_id'],
                ]);

                $stmtMove->execute([
                    ':item_id' => $pi['item_id'],
                    ':po_id' => $purchaseId,
                    ':qty' => $pi['qty'],
                    ':cost' => $pi['cost'],
                    ':notes' => "Penerimaan PO #{$poNumber}",
                    ':uid' => Auth::id(),
                ]);
            }

            // Record cash expense for purchase
            $expNum = DocumentSequenceService::generate('EXP');
            $kasAccount = (int)$pdo->query("SELECT id FROM accounts WHERE code = '1001'")->fetchColumn();
            $pdo->prepare("
                INSERT INTO expenses (expense_number, expense_category, account_id, amount, expense_date, recipient, description, created_by)
                VALUES (:num, 'pembelian_sparepart', :acc, :amount, :date, :rec, :desc, :uid)
            ")->execute([
                ':num' => $expNum,
                ':acc' => $kasAccount,
                ':amount' => $totalAmount,
                ':date' => $purchaseDate,
                ':rec' => 'Supplier',
                ':desc' => "Pembelian suku cadang PO #{$poNumber}",
                ':uid' => Auth::id(),
            ]);

            // Deduct cash account
            $pdo->prepare("UPDATE accounts SET balance = balance - :amount WHERE id = :id")
                ->execute([':amount' => $totalAmount, ':id' => $kasAccount]);
        });

        $_SESSION['flash_success'] = "Purchase Order {$poNumber} berhasil disimpan dan stok gudang otomatis bertambah!";
        redirect('purchases');
    }

    public function showPurchase(string $id): void
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            SELECT p.*, s.name as supplier_name, s.phone as supplier_phone, s.address as supplier_address, u.name as created_by_name
            FROM purchases p
            JOIN suppliers s ON p.supplier_id = s.id
            LEFT JOIN users u ON p.created_by = u.id
            WHERE p.id = :id
            LIMIT 1
        ");
        $stmt->execute([':id' => (int)$id]);
        $purchase = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$purchase) {
            redirect('purchases');
            return;
        }

        $items = $pdo->prepare("
            SELECT pi.*, i.name as item_name, i.sku, i.unit
            FROM purchase_items pi
            JOIN inventory_items i ON pi.inventory_item_id = i.id
            WHERE pi.purchase_id = :id
        ");
        $items->execute([':id' => (int)$id]);
        $purchaseItems = $items->fetchAll(PDO::FETCH_ASSOC);

        view('admin.inventory.purchase_show', [
            'title' => 'Detail PO - ' . $purchase['purchase_number'],
            'purchase' => $purchase,
            'items' => $purchaseItems,
        ]);
    }
}
