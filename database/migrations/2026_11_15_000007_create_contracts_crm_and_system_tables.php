<?php

declare(strict_types=1);

return new class {
    public function up(PDO $pdo): void
    {
        // 33. maintenance_contracts
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS maintenance_contracts (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                contract_number VARCHAR(50) NOT NULL UNIQUE,
                customer_id INTEGER NOT NULL,
                title VARCHAR(150) NOT NULL,
                start_date DATE NOT NULL,
                end_date DATE NOT NULL,
                total_units INTEGER NOT NULL DEFAULT 1,
                visits_per_year INTEGER NOT NULL DEFAULT 4,
                contract_amount INTEGER NOT NULL DEFAULT 0,
                billing_frequency VARCHAR(50) NOT NULL DEFAULT 'quarterly', -- monthly, quarterly, semi_annual, annual
                sla_hours INTEGER NOT NULL DEFAULT 24,
                status VARCHAR(30) NOT NULL DEFAULT 'active', -- draft, active, expired, terminated
                notes TEXT DEFAULT NULL,
                created_by INTEGER DEFAULT NULL,
                created_at DATETIME NOT NULL DEFAULT (datetime('now', 'localtime')),
                updated_at DATETIME NOT NULL DEFAULT (datetime('now', 'localtime')),
                FOREIGN KEY (customer_id) REFERENCES customers(id),
                FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
            );
            CREATE INDEX IF NOT EXISTS idx_contracts_num ON maintenance_contracts(contract_number);
            CREATE INDEX IF NOT EXISTS idx_contracts_cust ON maintenance_contracts(customer_id);
            CREATE INDEX IF NOT EXISTS idx_contracts_status ON maintenance_contracts(status);
        ");

        // 34. maintenance_schedules
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS maintenance_schedules (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                contract_id INTEGER NOT NULL,
                work_order_id INTEGER DEFAULT NULL,
                scheduled_date DATE NOT NULL,
                status VARCHAR(30) NOT NULL DEFAULT 'scheduled', -- scheduled, in_progress, completed, skipped
                notes TEXT DEFAULT NULL,
                completed_at DATETIME DEFAULT NULL,
                created_at DATETIME NOT NULL DEFAULT (datetime('now', 'localtime')),
                FOREIGN KEY (contract_id) REFERENCES maintenance_contracts(id) ON DELETE CASCADE,
                FOREIGN KEY (work_order_id) REFERENCES work_orders(id) ON DELETE SET NULL
            );
            CREATE INDEX IF NOT EXISTS idx_maint_sched_contract ON maintenance_schedules(contract_id);
            CREATE INDEX IF NOT EXISTS idx_maint_sched_date ON maintenance_schedules(scheduled_date);
        ");

        // 35. service_reminders
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS service_reminders (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                customer_id INTEGER NOT NULL,
                ac_unit_id INTEGER NOT NULL,
                last_service_date DATE DEFAULT NULL,
                due_date DATE NOT NULL,
                reminder_type VARCHAR(50) NOT NULL DEFAULT 'routine_wash', -- routine_wash, freon_check, general_maintenance
                status VARCHAR(30) NOT NULL DEFAULT 'pending', -- pending, sent, completed, dismissed
                sent_at DATETIME DEFAULT NULL,
                created_at DATETIME NOT NULL DEFAULT (datetime('now', 'localtime')),
                FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE,
                FOREIGN KEY (ac_unit_id) REFERENCES ac_units(id) ON DELETE CASCADE
            );
            CREATE INDEX IF NOT EXISTS idx_reminders_due ON service_reminders(due_date);
            CREATE INDEX IF NOT EXISTS idx_reminders_status ON service_reminders(status);
        ");

        // 36. customer_reviews
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS customer_reviews (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                customer_id INTEGER NOT NULL,
                work_order_id INTEGER NOT NULL UNIQUE,
                technician_id INTEGER DEFAULT NULL,
                rating INTEGER NOT NULL DEFAULT 5,
                review_text TEXT DEFAULT NULL,
                is_published INTEGER NOT NULL DEFAULT 1,
                created_at DATETIME NOT NULL DEFAULT (datetime('now', 'localtime')),
                FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE,
                FOREIGN KEY (work_order_id) REFERENCES work_orders(id) ON DELETE CASCADE,
                FOREIGN KEY (technician_id) REFERENCES technicians(id) ON DELETE SET NULL
            );
        ");

        // 37. promotions
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS promotions (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                code VARCHAR(50) NOT NULL UNIQUE,
                name VARCHAR(150) NOT NULL,
                discount_type VARCHAR(20) NOT NULL DEFAULT 'percentage', -- percentage, fixed
                discount_value INTEGER NOT NULL DEFAULT 10,
                min_order_amount INTEGER NOT NULL DEFAULT 0,
                max_discount_amount INTEGER NOT NULL DEFAULT 0,
                valid_from DATE NOT NULL,
                valid_until DATE NOT NULL,
                usage_limit INTEGER NOT NULL DEFAULT 100,
                times_used INTEGER NOT NULL DEFAULT 0,
                is_active INTEGER NOT NULL DEFAULT 1,
                created_at DATETIME NOT NULL DEFAULT (datetime('now', 'localtime'))
            );
            CREATE INDEX IF NOT EXISTS idx_promotions_code ON promotions(code);
        ");

        // 38. notifications
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS notifications (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER NOT NULL,
                title VARCHAR(150) NOT NULL,
                message TEXT NOT NULL,
                type VARCHAR(50) NOT NULL DEFAULT 'system', -- order, stock, payment, system
                is_read INTEGER NOT NULL DEFAULT 0,
                link_url VARCHAR(255) DEFAULT NULL,
                created_at DATETIME NOT NULL DEFAULT (datetime('now', 'localtime')),
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            );
            CREATE INDEX IF NOT EXISTS idx_notifications_user ON notifications(user_id, is_read);
        ");

        // 39. complaints
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS complaints (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                complaint_number VARCHAR(50) NOT NULL UNIQUE,
                customer_id INTEGER NOT NULL,
                work_order_id INTEGER DEFAULT NULL,
                title VARCHAR(150) NOT NULL,
                description TEXT NOT NULL,
                priority VARCHAR(20) NOT NULL DEFAULT 'normal', -- low, normal, urgent
                status VARCHAR(30) NOT NULL DEFAULT 'open', -- open, in_progress, resolved, closed
                resolution_notes TEXT DEFAULT NULL,
                resolved_at DATETIME DEFAULT NULL,
                created_at DATETIME NOT NULL DEFAULT (datetime('now', 'localtime')),
                updated_at DATETIME NOT NULL DEFAULT (datetime('now', 'localtime')),
                FOREIGN KEY (customer_id) REFERENCES customers(id),
                FOREIGN KEY (work_order_id) REFERENCES work_orders(id) ON DELETE SET NULL
            );
            CREATE INDEX IF NOT EXISTS idx_complaints_status ON complaints(status);
        ");

        // 40. warranty_claims
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS warranty_claims (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                claim_number VARCHAR(50) NOT NULL UNIQUE,
                work_order_id INTEGER NOT NULL,
                customer_id INTEGER NOT NULL,
                ac_unit_id INTEGER DEFAULT NULL,
                complaint_details TEXT NOT NULL,
                inspection_date DATE DEFAULT NULL,
                status VARCHAR(30) NOT NULL DEFAULT 'submitted', -- submitted, approved, rejected, completed
                resolution TEXT DEFAULT NULL,
                resolved_at DATETIME DEFAULT NULL,
                created_at DATETIME NOT NULL DEFAULT (datetime('now', 'localtime')),
                updated_at DATETIME NOT NULL DEFAULT (datetime('now', 'localtime')),
                FOREIGN KEY (work_order_id) REFERENCES work_orders(id),
                FOREIGN KEY (customer_id) REFERENCES customers(id),
                FOREIGN KEY (ac_unit_id) REFERENCES ac_units(id) ON DELETE SET NULL
            );
        ");

        // 41. document_sequences
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS document_sequences (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                sequence_key VARCHAR(50) NOT NULL UNIQUE, -- WO, INV, SPK, PO, CUST, AC, PAY, EXP, CONT, CMP, CLM
                prefix VARCHAR(20) NOT NULL,
                current_number INTEGER NOT NULL DEFAULT 0,
                padding INTEGER NOT NULL DEFAULT 4,
                format_pattern VARCHAR(50) NOT NULL DEFAULT '{PREFIX}-{YEAR}{MONTH}-{NUMBER}',
                updated_at DATETIME NOT NULL DEFAULT (datetime('now', 'localtime'))
            );
        ");

        // 42. settings
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS settings (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                setting_key VARCHAR(100) NOT NULL UNIQUE,
                setting_value TEXT NOT NULL,
                group_name VARCHAR(50) NOT NULL DEFAULT 'general',
                description VARCHAR(255) DEFAULT NULL,
                updated_at DATETIME NOT NULL DEFAULT (datetime('now', 'localtime'))
            );
        ");

        // 44. sync_queue (offline PWA)
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS sync_queue (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                uuid VARCHAR(64) NOT NULL UNIQUE,
                user_id INTEGER NOT NULL,
                action VARCHAR(100) NOT NULL,
                payload TEXT NOT NULL,
                status VARCHAR(20) NOT NULL DEFAULT 'pending', -- pending, synced, failed
                error_message TEXT DEFAULT NULL,
                retry_count INTEGER NOT NULL DEFAULT 0,
                created_at DATETIME NOT NULL DEFAULT (datetime('now', 'localtime')),
                synced_at DATETIME DEFAULT NULL,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            );
            CREATE INDEX IF NOT EXISTS idx_sync_status ON sync_queue(status);
        ");

        // 45. device_registrations
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS device_registrations (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER NOT NULL,
                device_uuid VARCHAR(100) NOT NULL UNIQUE,
                platform VARCHAR(50) NOT NULL DEFAULT 'android',
                browser VARCHAR(100) DEFAULT NULL,
                push_endpoint TEXT DEFAULT NULL,
                last_active_at DATETIME NOT NULL DEFAULT (datetime('now', 'localtime')),
                created_at DATETIME NOT NULL DEFAULT (datetime('now', 'localtime')),
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            );
        ");
    }
};
