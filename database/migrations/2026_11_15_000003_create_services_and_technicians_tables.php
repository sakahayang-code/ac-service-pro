<?php

declare(strict_types=1);

return new class {
    public function up(PDO $pdo): void
    {
        // 10. service_categories
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS service_categories (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name VARCHAR(100) NOT NULL,
                slug VARCHAR(100) NOT NULL UNIQUE,
                description TEXT DEFAULT NULL,
                sort_order INTEGER NOT NULL DEFAULT 0,
                is_active INTEGER NOT NULL DEFAULT 1,
                created_at DATETIME NOT NULL DEFAULT (datetime('now', 'localtime')),
                updated_at DATETIME NOT NULL DEFAULT (datetime('now', 'localtime'))
            );
        ");

        // 11. service_products
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS service_products (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                category_id INTEGER NOT NULL,
                code VARCHAR(50) NOT NULL UNIQUE,
                name VARCHAR(150) NOT NULL,
                description TEXT DEFAULT NULL,
                capacity_pk_filter VARCHAR(50) DEFAULT 'ALL',
                refrigerant_filter VARCHAR(50) DEFAULT 'ALL',
                base_price INTEGER NOT NULL DEFAULT 0,
                contract_price INTEGER NOT NULL DEFAULT 0,
                estimated_duration_minutes INTEGER NOT NULL DEFAULT 60,
                is_active INTEGER NOT NULL DEFAULT 1,
                created_at DATETIME NOT NULL DEFAULT (datetime('now', 'localtime')),
                updated_at DATETIME NOT NULL DEFAULT (datetime('now', 'localtime')),
                FOREIGN KEY (category_id) REFERENCES service_categories(id)
            );
            CREATE INDEX IF NOT EXISTS idx_services_category ON service_products(category_id);
            CREATE INDEX IF NOT EXISTS idx_services_code ON service_products(code);
        ");

        // 12. technicians
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS technicians (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER NOT NULL UNIQUE,
                code VARCHAR(50) NOT NULL UNIQUE,
                full_name VARCHAR(150) NOT NULL,
                phone VARCHAR(30) NOT NULL,
                skills TEXT DEFAULT NULL,
                certifications TEXT DEFAULT NULL,
                work_area VARCHAR(150) DEFAULT 'Jabodetabek',
                commission_rate_percent REAL NOT NULL DEFAULT 20.0,
                status VARCHAR(30) NOT NULL DEFAULT 'active',
                rating REAL NOT NULL DEFAULT 5.0,
                total_jobs_done INTEGER NOT NULL DEFAULT 0,
                photo VARCHAR(255) DEFAULT NULL,
                created_at DATETIME NOT NULL DEFAULT (datetime('now', 'localtime')),
                updated_at DATETIME NOT NULL DEFAULT (datetime('now', 'localtime')),
                deleted_at DATETIME DEFAULT NULL,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            );
            CREATE INDEX IF NOT EXISTS idx_technicians_status ON technicians(status);
        ");

        // 13. technician_schedules
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS technician_schedules (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                technician_id INTEGER NOT NULL,
                date DATE NOT NULL,
                shift VARCHAR(50) NOT NULL DEFAULT 'Pagi (08:00 - 16:00)',
                start_time TIME NOT NULL DEFAULT '08:00',
                end_time TIME NOT NULL DEFAULT '17:00',
                status VARCHAR(30) NOT NULL DEFAULT 'available',
                notes TEXT DEFAULT NULL,
                created_at DATETIME NOT NULL DEFAULT (datetime('now', 'localtime')),
                updated_at DATETIME NOT NULL DEFAULT (datetime('now', 'localtime')),
                FOREIGN KEY (technician_id) REFERENCES technicians(id) ON DELETE CASCADE
            );
            CREATE INDEX IF NOT EXISTS idx_tech_sched_date ON technician_schedules(technician_id, date);
        ");
    }
};
