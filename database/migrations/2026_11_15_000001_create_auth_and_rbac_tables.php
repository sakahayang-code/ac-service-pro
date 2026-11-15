<?php

declare(strict_types=1);

return new class {
    public function up(PDO $pdo): void
    {
        // 1. roles
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS roles (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name VARCHAR(50) NOT NULL UNIQUE,
                display_name VARCHAR(100) NOT NULL,
                description TEXT,
                is_system INTEGER NOT NULL DEFAULT 1,
                created_at DATETIME NOT NULL DEFAULT (datetime('now', 'localtime')),
                updated_at DATETIME NOT NULL DEFAULT (datetime('now', 'localtime'))
            );
        ");

        // 2. permissions
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS permissions (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                code VARCHAR(100) NOT NULL UNIQUE,
                module VARCHAR(50) NOT NULL,
                display_name VARCHAR(100) NOT NULL,
                description TEXT,
                created_at DATETIME NOT NULL DEFAULT (datetime('now', 'localtime'))
            );
        ");

        // 3. role_permissions
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS role_permissions (
                role_id INTEGER NOT NULL,
                permission_id INTEGER NOT NULL,
                PRIMARY KEY (role_id, permission_id),
                FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
                FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE
            );
        ");

        // 4. users
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                role_id INTEGER NOT NULL,
                name VARCHAR(150) NOT NULL,
                email VARCHAR(150) NOT NULL UNIQUE,
                phone VARCHAR(30) UNIQUE,
                password_hash VARCHAR(255) NOT NULL,
                avatar VARCHAR(255) DEFAULT NULL,
                status VARCHAR(20) NOT NULL DEFAULT 'active',
                last_login_at DATETIME DEFAULT NULL,
                created_at DATETIME NOT NULL DEFAULT (datetime('now', 'localtime')),
                updated_at DATETIME NOT NULL DEFAULT (datetime('now', 'localtime')),
                deleted_at DATETIME DEFAULT NULL,
                FOREIGN KEY (role_id) REFERENCES roles(id)
            );
            CREATE INDEX IF NOT EXISTS idx_users_role ON users(role_id);
            CREATE INDEX IF NOT EXISTS idx_users_status ON users(status);
        ");

        // 5. user_sessions
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS user_sessions (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER NOT NULL,
                session_token VARCHAR(128) NOT NULL UNIQUE,
                ip_address VARCHAR(45) NOT NULL,
                user_agent VARCHAR(255),
                payload TEXT,
                last_activity INTEGER NOT NULL,
                expires_at DATETIME NOT NULL,
                created_at DATETIME NOT NULL DEFAULT (datetime('now', 'localtime')),
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            );
            CREATE INDEX IF NOT EXISTS idx_user_sessions_token ON user_sessions(session_token);
        ");

        // 6. audit_logs
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS audit_logs (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER DEFAULT NULL,
                action VARCHAR(100) NOT NULL,
                entity_type VARCHAR(100) NOT NULL,
                entity_id INTEGER DEFAULT NULL,
                old_values TEXT DEFAULT NULL,
                new_values TEXT DEFAULT NULL,
                ip_address VARCHAR(45) DEFAULT NULL,
                user_agent VARCHAR(255) DEFAULT NULL,
                created_at DATETIME NOT NULL DEFAULT (datetime('now', 'localtime')),
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
            );
            CREATE INDEX IF NOT EXISTS idx_audit_logs_action ON audit_logs(action);
            CREATE INDEX IF NOT EXISTS idx_audit_logs_entity ON audit_logs(entity_type, entity_id);
        ");
    }
};
