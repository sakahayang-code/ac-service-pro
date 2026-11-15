<?php

declare(strict_types=1);

return new class {
    public function up(PDO $pdo): void
    {
        // 14. work_orders
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS work_orders (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                order_number VARCHAR(50) NOT NULL UNIQUE,
                customer_id INTEGER NOT NULL,
                ac_unit_id INTEGER DEFAULT NULL,
                order_source VARCHAR(50) NOT NULL DEFAULT 'admin', -- admin, whatsapp, customer_portal, contract
                priority VARCHAR(20) NOT NULL DEFAULT 'normal', -- low, normal, high, emergency
                status VARCHAR(50) NOT NULL DEFAULT 'BARU',
                -- Status list: BARU, DIKONFIRMASI, DIJADWALKAN, DALAM_PERJALANAN, SEDANG_DIKERJAKAN, MENUNGGU_SPAREPART, SELESAI, DITAGIHKAN, LUNAS, DITUNDA, DIBATALKAN, KUNJUNGAN_ULANG
                scheduled_date DATE NOT NULL,
                scheduled_time VARCHAR(20) NOT NULL DEFAULT '09:00',
                address_text TEXT NOT NULL,
                latitude REAL DEFAULT NULL,
                longitude REAL DEFAULT NULL,
                complaints TEXT NOT NULL,
                damage_photo VARCHAR(255) DEFAULT NULL,
                estimated_price INTEGER NOT NULL DEFAULT 0,
                final_price INTEGER NOT NULL DEFAULT 0,
                checkin_at DATETIME DEFAULT NULL,
                checkout_at DATETIME DEFAULT NULL,
                cancellation_reason TEXT DEFAULT NULL,
                created_by INTEGER DEFAULT NULL,
                notes TEXT DEFAULT NULL,
                created_at DATETIME NOT NULL DEFAULT (datetime('now', 'localtime')),
                updated_at DATETIME NOT NULL DEFAULT (datetime('now', 'localtime')),
                deleted_at DATETIME DEFAULT NULL,
                FOREIGN KEY (customer_id) REFERENCES customers(id),
                FOREIGN KEY (ac_unit_id) REFERENCES ac_units(id) ON DELETE SET NULL,
                FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
            );
            CREATE INDEX IF NOT EXISTS idx_wo_number ON work_orders(order_number);
            CREATE INDEX IF NOT EXISTS idx_wo_status ON work_orders(status);
            CREATE INDEX IF NOT EXISTS idx_wo_date ON work_orders(scheduled_date);
            CREATE INDEX IF NOT EXISTS idx_wo_customer ON work_orders(customer_id);
        ");

        // 15. work_order_technicians
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS work_order_technicians (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                work_order_id INTEGER NOT NULL,
                technician_id INTEGER NOT NULL,
                is_lead INTEGER NOT NULL DEFAULT 1,
                commission_amount INTEGER NOT NULL DEFAULT 0,
                assigned_at DATETIME NOT NULL DEFAULT (datetime('now', 'localtime')),
                FOREIGN KEY (work_order_id) REFERENCES work_orders(id) ON DELETE CASCADE,
                FOREIGN KEY (technician_id) REFERENCES technicians(id) ON DELETE CASCADE
            );
            CREATE INDEX IF NOT EXISTS idx_wo_tech_wo ON work_order_technicians(work_order_id);
            CREATE INDEX IF NOT EXISTS idx_wo_tech_tech ON work_order_technicians(technician_id);
        ");

        // 16. work_order_services
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS work_order_services (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                work_order_id INTEGER NOT NULL,
                service_product_id INTEGER DEFAULT NULL,
                service_name VARCHAR(150) NOT NULL,
                quantity INTEGER NOT NULL DEFAULT 1,
                unit_price INTEGER NOT NULL DEFAULT 0,
                subtotal INTEGER NOT NULL DEFAULT 0,
                FOREIGN KEY (work_order_id) REFERENCES work_orders(id) ON DELETE CASCADE,
                FOREIGN KEY (service_product_id) REFERENCES service_products(id) ON DELETE SET NULL
            );
            CREATE INDEX IF NOT EXISTS idx_wo_services_wo ON work_order_services(work_order_id);
        ");

        // 17. service_checklists
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS service_checklists (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                work_order_id INTEGER NOT NULL,
                checklist_item VARCHAR(150) NOT NULL,
                status VARCHAR(30) NOT NULL DEFAULT 'ok', -- ok, perbaikan, perlu_ganti, skip
                notes TEXT DEFAULT NULL,
                checked_at DATETIME NOT NULL DEFAULT (datetime('now', 'localtime')),
                FOREIGN KEY (work_order_id) REFERENCES work_orders(id) ON DELETE CASCADE
            );
            CREATE INDEX IF NOT EXISTS idx_wo_checklists_wo ON service_checklists(work_order_id);
        ");

        // 18. service_diagnostics
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS service_diagnostics (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                work_order_id INTEGER NOT NULL,
                current_ampere REAL DEFAULT NULL,
                voltage_volt REAL DEFAULT NULL,
                pressure_psi REAL DEFAULT NULL,
                temperature_celsius REAL DEFAULT NULL,
                diagnostic_result TEXT DEFAULT NULL,
                technician_notes TEXT DEFAULT NULL,
                recorded_at DATETIME NOT NULL DEFAULT (datetime('now', 'localtime')),
                FOREIGN KEY (work_order_id) REFERENCES work_orders(id) ON DELETE CASCADE
            );
            CREATE INDEX IF NOT EXISTS idx_wo_diagnostics_wo ON service_diagnostics(work_order_id);
        ");

        // 19. service_photos
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS service_photos (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                work_order_id INTEGER NOT NULL,
                photo_type VARCHAR(30) NOT NULL DEFAULT 'before', -- before, process, after
                photo_path VARCHAR(255) NOT NULL,
                caption VARCHAR(255) DEFAULT NULL,
                uploaded_at DATETIME NOT NULL DEFAULT (datetime('now', 'localtime')),
                FOREIGN KEY (work_order_id) REFERENCES work_orders(id) ON DELETE CASCADE
            );
            CREATE INDEX IF NOT EXISTS idx_wo_photos_wo ON service_photos(work_order_id);
        ");

        // 20. service_materials
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS service_materials (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                work_order_id INTEGER NOT NULL,
                inventory_item_id INTEGER DEFAULT NULL,
                item_name VARCHAR(150) NOT NULL,
                quantity REAL NOT NULL DEFAULT 1.0,
                unit VARCHAR(30) NOT NULL DEFAULT 'Pcs',
                unit_price INTEGER NOT NULL DEFAULT 0,
                subtotal INTEGER NOT NULL DEFAULT 0,
                is_billed INTEGER NOT NULL DEFAULT 1,
                created_at DATETIME NOT NULL DEFAULT (datetime('now', 'localtime')),
                FOREIGN KEY (work_order_id) REFERENCES work_orders(id) ON DELETE CASCADE
            );
            CREATE INDEX IF NOT EXISTS idx_wo_materials_wo ON service_materials(work_order_id);
        ");

        // 21. customer_signatures
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS customer_signatures (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                work_order_id INTEGER NOT NULL UNIQUE,
                signer_name VARCHAR(150) NOT NULL,
                signature_data TEXT NOT NULL, -- base64 SVG/PNG data URI
                rating INTEGER NOT NULL DEFAULT 5,
                customer_notes TEXT DEFAULT NULL,
                signed_at DATETIME NOT NULL DEFAULT (datetime('now', 'localtime')),
                FOREIGN KEY (work_order_id) REFERENCES work_orders(id) ON DELETE CASCADE
            );
        ");
    }
};
