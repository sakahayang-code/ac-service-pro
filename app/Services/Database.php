<?php

declare(strict_types=1);

namespace App\Services;

use PDO;
use PDOException;
use RuntimeException;

class Database
{
    private static ?PDO $instance = null;

    public static function getConnection(): PDO
    {
        if (self::$instance === null) {
            $config = app_config('database');
            $dbPath = $config['path'] ?? (dirname(__DIR__, 2) . '/storage/database/ac_service_pro.db');
            $dbDir = dirname($dbPath);

            if (!is_dir($dbDir)) {
                mkdir($dbDir, 0755, true);
            }

            try {
                $dsn = 'sqlite:' . $dbPath;
                $pdo = new PDO($dsn, null, null, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]);

                // Enforce SQLite optimizations and constraints
                $pdo->exec('PRAGMA foreign_keys = ON;');
                $pdo->exec('PRAGMA journal_mode = WAL;');
                $pdo->exec('PRAGMA busy_timeout = 5000;');
                $pdo->exec('PRAGMA synchronous = NORMAL;');

                self::$instance = $pdo;
            } catch (PDOException $e) {
                throw new RuntimeException('Database connection failed: ' . $e->getMessage(), (int)$e->getCode(), $e);
            }
        }

        return self::$instance;
    }

    public static function transaction(callable $callback): mixed
    {
        $pdo = self::getConnection();
        $isAlreadyInTransaction = $pdo->inTransaction();

        if (!$isAlreadyInTransaction) {
            $pdo->beginTransaction();
        }

        try {
            $result = $callback($pdo);
            if (!$isAlreadyInTransaction) {
                $pdo->commit();
            }
            return $result;
        } catch (\Throwable $e) {
            if (!$isAlreadyInTransaction && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }
    }

    public static function reset(): void
    {
        self::$instance = null;
    }
}
