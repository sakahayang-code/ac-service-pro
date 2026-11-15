<?php

declare(strict_types=1);

return new class {
    public function up(PDO $pdo): void
    {
        // 7. customers
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS customers (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER DEFAULT NULL,
                customer_code VARCHAR(50) NOT NULL UNIQUE,
                category VARCHAR(50) NOT NULL DEFAULT 'household',
                name VARCHAR(150) NOT NULL,
                phone VARCHAR(30) NOT NULL,
                email VARCHAR(150) DEFAULT NULL,
                address TEXT NOT NULL,
                latitude REAL DEFAULT NULL,
                longitude REAL DEFAULT NULL,
                photo VARCHAR(255) DEFAULT NULL,
                notes TEXT DEFAULT NULL,
                status VARCHAR(20) NOT NULL DEFAULT 'active',
                total_orders INTEGER NOT NULL DEFAULT 0,
                total_spent INTEGER NOT NULL DEFAULT 0,
                created_at DATETIME NOT NULL DEFAULT (datetime('now', 'localtime')),
                updated_at DATETIME NOT NULL DEFAULT (datetime('now', 'localtime')),
                deleted_at DATETIME DEFAULT NULL,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
            );
            CREATE INDEX IF NOT EXISTS idx_customers_code ON customers(customer_code);
            CREATE INDEX IF NOT EXISTS idx_customers_phone ON customers(phone);
            CREATE INDEX IF NOT EXISTS idx_customers_category ON customers(category);
        ");

        // 8. customer_addresses
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS customer_addresses (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                customer_id INTEGER NOT NULL,
                label VARCHAR(100) NOT NULL DEFAULT 'Utama',
                recipient_name VARCHAR(150) NOT NULL,
                phone VARCHAR(30) NOT NULL,
                address TEXT NOT NULL,
                latitude REAL DEFAULT NULL,
                longitude REAL DEFAULT NULL,
                notes TEXT DEFAULT NULL,
                is_default INTEGER NOT NULL DEFAULT 0,
                created_at DATETIME NOT NULL DEFAULT (datetime('now', 'localtime')),
                updated_at DATETIME NOT NULL DEFAULT (datetime('now', 'localtime')),
                FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE
            );
            CREATE INDEX IF NOT EXISTS idx_cust_addr_cust ON customer_addresses(customer_id);
        ");

        // 9. ac_units
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS ac_units (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                customer_id INTEGER NOT NULL,
                address_id INTEGER DEFAULT NULL,
                unit_code VARCHAR(50) NOT NULL UNIQUE,
                brand VARCHAR(100) NOT NULL,
                model_type VARCHAR(100) NOT NULL DEFAULT 'Split Wall',
                capacity_pk VARCHAR(20) NOT NULL DEFAULT '1 PK',
                serial_number VARCHAR(100) DEFAULT NULL,
                refrigerant_type VARCHAR(30) NOT NULL DEFAULT 'R32',
                room_location VARCHAR(100) NOT NULL DEFAULT 'Kamar Utama',
                installation_date DATE DEFAULT NULL,
                warranty_status VARCHAR(50) NOT NULL DEFAULT 'Out of Warranty',
                warranty_expiry DATE DEFAULT NULL,
                condition VARCHAR(50) NOT NULL DEFAULT 'Normal',
                photo VARCHAR(255) DEFAULT NULL,
                notes TEXT DEFAULT NULL,
                last_serviced_at DATETIME DEFAULT NULL,
                next_service_due DATE DEFAULT NULL,
                created_at DATETIME NOT NULL DEFAULT (datetime('now', 'localtime')),
                updated_at DATETIME NOT NULL DEFAULT (datetime('now', 'localtime')),
                deleted_at DATETIME DEFAULT NULL,
                FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE,
                FOREIGN KEY (address_id) REFERENCES customer_addresses(id) ON DELETE SET NULL
            );
            CREATE INDEX IF NOT EXISTS idx_ac_units_cust ON ac_units(customer_id);
            CREATE INDEX IF NOT EXISTS idx_ac_units_code ON ac_units(unit_code);
            CREATE INDEX IF NOT EXISTS idx_ac_units_due ON ac_units(next_service_due);
        ");
    }
};
