<?php

declare(strict_types=1);

namespace App\Services;

use PDO;
use Throwable;
use RuntimeException;

class MigrationRunner
{
    private PDO $pdo;
    private string $migrationPath;

    public function __construct(?PDO $pdo = null, ?string $migrationPath = null)
    {
        $this->pdo = $pdo ?? Database::getConnection();
        $this->migrationPath = $migrationPath ?? (dirname(__DIR__, 2) . '/database/migrations');
    }

    public function ensureMigrationTable(): void
    {
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS migrations (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                migration VARCHAR(255) NOT NULL UNIQUE,
                batch INTEGER NOT NULL,
                executed_at DATETIME NOT NULL DEFAULT (datetime('now', 'localtime'))
            );
        ");
    }

    public function getExecutedMigrations(): array
    {
        $this->ensureMigrationTable();
        $stmt = $this->pdo->query("SELECT migration FROM migrations ORDER BY id ASC");
        return $stmt->fetchAll(PDO::FETCH_COLUMN) ?: [];
    }

    public function getNextBatchNumber(): int
    {
        $this->ensureMigrationTable();
        $stmt = $this->pdo->query("SELECT MAX(batch) FROM migrations");
        $max = $stmt->fetchColumn();
        return ($max !== false && $max !== null) ? ((int)$max + 1) : 1;
    }

    public function run(): array
    {
        $this->ensureMigrationTable();
        $executed = $this->getExecutedMigrations();
        $files = glob($this->migrationPath . '/*.php');
        sort($files);

        $applied = [];
        $batch = $this->getNextBatchNumber();

        foreach ($files as $filePath) {
            $migrationName = basename($filePath, '.php');
            if (in_array($migrationName, $executed, true)) {
                continue;
            }

            $migrationClass = require $filePath;
            if (!is_object($migrationClass) || !method_exists($migrationClass, 'up')) {
                throw new RuntimeException("Migration file {$filePath} must return an object with an up(PDO) method.");
            }

            Database::transaction(function (PDO $pdo) use ($migrationClass, $migrationName, $batch) {
                $migrationClass->up($pdo);
                $stmt = $pdo->prepare("INSERT INTO migrations (migration, batch) VALUES (:migration, :batch)");
                $stmt->execute([
                    ':migration' => $migrationName,
                    ':batch' => $batch,
                ]);
            });

            $applied[] = $migrationName;
        }

        return $applied;
    }
}
