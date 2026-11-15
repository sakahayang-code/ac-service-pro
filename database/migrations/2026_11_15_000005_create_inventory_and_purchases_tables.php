<?php

declare(strict_types=1);

return new class {
    public function up(PDO $pdo): void
    {
        // 22. suppliers
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS suppliers (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                code VARCHAR(50) NOT NULL UNIQUE,
                name VARCHAR(150) NOT NULL,
                contact_person VARCHAR(100) DEFAULT NULL,
                phone VARCHAR(30) NOT NULL,
                email VARCHAR(150) DEFAULT NULL,
                address TEXT DEFAULT NULL,
                status VARCHAR(20) NOT NULL DEFAULT 'active',
                created_at DATETIME NOT NULL DEFAULT (datetime('now', 'localtime')),
                updated_at DATETIME NOT NULL DEFAULT (datetime('now', 'localtime'))
            );
        ");

        // 23. inventory_items
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS inventory_items (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                sku VARCHAR(50) NOT NULL UNIQUE,
                barcode VARCHAR(50) DEFAULT NULL,
                name VARCHAR(150) NOT NULL,
                category VARCHAR(50) NOT NULL DEFAULT 'Sparepart',
                unit VARCHAR(30) NOT NULL DEFAULT 'Pcs',
                cost_price INTEGER NOT NULL DEFAULT 0,
                selling_price INTEGER NOT NULL DEFAULT 0,
                min_stock REAL NOT NULL DEFAULT 5.0,
                current_stock REAL NOT NULL DEFAULT 0.0,
                is_active INTEGER NOT NULL DEFAULT 1,
                created_at DATETIME NOT NULL DEFAULT (datetime('now', 'localtime')),
                updated_at DATETIME NOT NULL DEFAULT (datetime('now', 'localtime')),
                deleted_at DATETIME DEFAULT NULL
            );
            CREATE INDEX IF NOT EXISTS idx_inv_sku ON inventory_items(sku);
            CREATE INDEX IF NOT EXISTS idx_inv_category ON inventory_items(category);
        ");

        // 24. inventory_stocks
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS inventory_stocks (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                inventory_item_id INTEGER NOT NULL,
                location_type VARCHAR(30) NOT NULL DEFAULT 'warehouse', -- warehouse, vehicle
                location_ref_id INTEGER DEFAULT NULL, -- NULL for central warehouse, or technician_id for vehicle
                quantity REAL NOT NULL DEFAULT 0.0,
                updated_at DATETIME NOT NULL DEFAULT (datetime('now', 'localtime')),
                FOREIGN KEY (inventory_item_id) REFERENCES inventory_items(id) ON DELETE CASCADE
            );
            CREATE UNIQUE INDEX IF NOT EXISTS idx_inv_loc ON inventory_stocks(inventory_item_id, location_type, location_ref_id);
        ");

        // 25. stock_movements
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS stock_movements (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                inventory_item_id INTEGER NOT NULL,
                movement_type VARCHAR(30) NOT NULL, -- in, out, transfer, adjustment, service_usage
                reference_type VARCHAR(50) DEFAULT NULL, -- purchase, work_order, adjustment, transfer
                reference_id INTEGER DEFAULT NULL,
                from_location VARCHAR(50) DEFAULT NULL,
                to_location VARCHAR(50) DEFAULT NULL,
                quantity REAL NOT NULL,
                unit_price INTEGER NOT NULL DEFAULT 0,
                notes TEXT DEFAULT NULL,
                created_by INTEGER DEFAULT NULL,
                created_at DATETIME NOT NULL DEFAULT (datetime('now', 'localtime')),
                FOREIGN KEY (inventory_item_id) REFERENCES inventory_items(id) ON DELETE CASCADE,
                FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
            );
            CREATE INDEX IF NOT EXISTS idx_movements_item ON stock_movements(inventory_item_id);
            CREATE INDEX IF NOT EXISTS idx_movements_ref ON stock_movements(reference_type, reference_id);
        ");

        // 26. purchases
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS purchases (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                purchase_number VARCHAR(50) NOT NULL UNIQUE,
                supplier_id INTEGER NOT NULL,
                status VARCHAR(30) NOT NULL DEFAULT 'received', -- pending, received, cancelled
                purchase_date DATE NOT NULL,
                total_amount INTEGER NOT NULL DEFAULT 0,
                paid_amount INTEGER NOT NULL DEFAULT 0,
                payment_status VARCHAR(30) NOT NULL DEFAULT 'unpaid', -- unpaid, partial, paid
                notes TEXT DEFAULT NULL,
                created_by INTEGER DEFAULT NULL,
                created_at DATETIME NOT NULL DEFAULT (datetime('now', 'localtime')),
                updated_at DATETIME NOT NULL DEFAULT (datetime('now', 'localtime')),
                FOREIGN KEY (supplier_id) REFERENCES suppliers(id),
                FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
            );
            CREATE INDEX IF NOT EXISTS idx_purchases_num ON purchases(purchase_number);
        ");

        // 27. purchase_items
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS purchase_items (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                purchase_id INTEGER NOT NULL,
                inventory_item_id INTEGER NOT NULL,
                quantity REAL NOT NULL DEFAULT 1.0,
                unit_cost INTEGER NOT NULL DEFAULT 0,
                subtotal INTEGER NOT NULL DEFAULT 0,
                FOREIGN KEY (purchase_id) REFERENCES purchases(id) ON DELETE CASCADE,
                FOREIGN KEY (inventory_item_id) REFERENCES inventory_items(id)
            );
            CREATE INDEX IF NOT EXISTS idx_purch_items_purch ON purchase_items(purchase_id);
        ");
    }
};
