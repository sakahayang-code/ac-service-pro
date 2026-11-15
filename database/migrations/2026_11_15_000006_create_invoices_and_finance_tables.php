<?php

declare(strict_types=1);

return new class {
    public function up(PDO $pdo): void
    {
        // 28. accounts
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS accounts (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                code VARCHAR(50) NOT NULL UNIQUE,
                name VARCHAR(100) NOT NULL,
                type VARCHAR(50) NOT NULL, -- asset, liability, equity, revenue, expense
                balance INTEGER NOT NULL DEFAULT 0,
                is_active INTEGER NOT NULL DEFAULT 1,
                created_at DATETIME NOT NULL DEFAULT (datetime('now', 'localtime')),
                updated_at DATETIME NOT NULL DEFAULT (datetime('now', 'localtime'))
            );
        ");

        // 29. invoices
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS invoices (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                invoice_number VARCHAR(50) NOT NULL UNIQUE,
                work_order_id INTEGER DEFAULT NULL,
                customer_id INTEGER NOT NULL,
                issue_date DATE NOT NULL,
                due_date DATE NOT NULL,
                subtotal_services INTEGER NOT NULL DEFAULT 0,
                subtotal_materials INTEGER NOT NULL DEFAULT 0,
                visit_fee INTEGER NOT NULL DEFAULT 0,
                discount_amount INTEGER NOT NULL DEFAULT 0,
                tax_rate REAL NOT NULL DEFAULT 0.0,
                tax_amount INTEGER NOT NULL DEFAULT 0,
                total_amount INTEGER NOT NULL DEFAULT 0,
                paid_amount INTEGER NOT NULL DEFAULT 0,
                balance_due INTEGER NOT NULL DEFAULT 0,
                payment_status VARCHAR(30) NOT NULL DEFAULT 'unpaid', -- unpaid, partial, paid, cancelled
                notes TEXT DEFAULT NULL,
                created_by INTEGER DEFAULT NULL,
                created_at DATETIME NOT NULL DEFAULT (datetime('now', 'localtime')),
                updated_at DATETIME NOT NULL DEFAULT (datetime('now', 'localtime')),
                FOREIGN KEY (work_order_id) REFERENCES work_orders(id) ON DELETE SET NULL,
                FOREIGN KEY (customer_id) REFERENCES customers(id),
                FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
            );
            CREATE INDEX IF NOT EXISTS idx_invoices_number ON invoices(invoice_number);
            CREATE INDEX IF NOT EXISTS idx_invoices_status ON invoices(payment_status);
            CREATE INDEX IF NOT EXISTS idx_invoices_wo ON invoices(work_order_id);
            CREATE INDEX IF NOT EXISTS idx_invoices_cust ON invoices(customer_id);
        ");

        // 30. invoice_items
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS invoice_items (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                invoice_id INTEGER NOT NULL,
                item_type VARCHAR(30) NOT NULL DEFAULT 'service', -- service, material, fee, discount
                reference_id INTEGER DEFAULT NULL,
                description VARCHAR(255) NOT NULL,
                quantity REAL NOT NULL DEFAULT 1.0,
                unit_price INTEGER NOT NULL DEFAULT 0,
                subtotal INTEGER NOT NULL DEFAULT 0,
                FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON DELETE CASCADE
            );
            CREATE INDEX IF NOT EXISTS idx_inv_items_inv ON invoice_items(invoice_id);
        ");

        // 31. payments
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS payments (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                payment_number VARCHAR(50) NOT NULL UNIQUE,
                invoice_id INTEGER NOT NULL,
                amount INTEGER NOT NULL,
                payment_method VARCHAR(50) NOT NULL DEFAULT 'cash', -- cash, bank_transfer, qris
                account_id INTEGER DEFAULT NULL,
                payment_date DATE NOT NULL,
                reference_number VARCHAR(100) DEFAULT NULL,
                proof_image VARCHAR(255) DEFAULT NULL,
                notes TEXT DEFAULT NULL,
                received_by INTEGER DEFAULT NULL,
                created_at DATETIME NOT NULL DEFAULT (datetime('now', 'localtime')),
                FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON DELETE CASCADE,
                FOREIGN KEY (account_id) REFERENCES accounts(id) ON DELETE SET NULL,
                FOREIGN KEY (received_by) REFERENCES users(id) ON DELETE SET NULL
            );
            CREATE INDEX IF NOT EXISTS idx_payments_inv ON payments(invoice_id);
            CREATE INDEX IF NOT EXISTS idx_payments_date ON payments(payment_date);
        ");

        // 32. expenses
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS expenses (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                expense_number VARCHAR(50) NOT NULL UNIQUE,
                expense_category VARCHAR(100) NOT NULL, -- operasional, transportasi, gaji, komisi, pembelian_alat, lainnya
                account_id INTEGER DEFAULT NULL,
                amount INTEGER NOT NULL,
                expense_date DATE NOT NULL,
                recipient VARCHAR(150) NOT NULL,
                description TEXT NOT NULL,
                receipt_photo VARCHAR(255) DEFAULT NULL,
                created_by INTEGER DEFAULT NULL,
                created_at DATETIME NOT NULL DEFAULT (datetime('now', 'localtime')),
                FOREIGN KEY (account_id) REFERENCES accounts(id) ON DELETE SET NULL,
                FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
            );
            CREATE INDEX IF NOT EXISTS idx_expenses_date ON expenses(expense_date);
        ");

        // 33. cash_transactions
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS cash_transactions (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                transaction_number VARCHAR(50) NOT NULL UNIQUE,
                account_id INTEGER NOT NULL,
                type VARCHAR(20) NOT NULL, -- in, out
                amount INTEGER NOT NULL,
                reference_type VARCHAR(50) DEFAULT NULL, -- payment, expense, transfer, initial
                reference_id INTEGER DEFAULT NULL,
                description TEXT NOT NULL,
                transaction_date DATETIME NOT NULL DEFAULT (datetime('now', 'localtime')),
                created_by INTEGER DEFAULT NULL,
                created_at DATETIME NOT NULL DEFAULT (datetime('now', 'localtime')),
                FOREIGN KEY (account_id) REFERENCES accounts(id) ON DELETE CASCADE,
                FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
            );
            CREATE INDEX IF NOT EXISTS idx_cash_trx_account ON cash_transactions(account_id);
            CREATE INDEX IF NOT EXISTS idx_cash_trx_date ON cash_transactions(transaction_date);
        ");
    }
};
