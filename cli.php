<?php

declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';

use App\Services\Database;
use App\Services\MigrationRunner;
use Database\Seeds\DatabaseSeeder;

$action = $argv[1] ?? 'help';

echo "=== AC SERVICE PRO CLI TOOL ===" . PHP_EOL;

switch ($action) {
    case 'migrate':
        echo "Running database migrations..." . PHP_EOL;
        $runner = new MigrationRunner();
        $applied = $runner->run();
        if (empty($applied)) {
            echo "No new migrations to run. Database is up to date." . PHP_EOL;
        } else {
            foreach ($applied as $m) {
                echo " [OK] Applied: {$m}" . PHP_EOL;
            }
            echo "Successfully applied " . count($applied) . " migrations." . PHP_EOL;
        }
        break;

    case 'seed':
        echo "Running database seeders..." . PHP_EOL;
        $seeder = new DatabaseSeeder();
        $seeder->run();
        echo "Database seeding completed successfully!" . PHP_EOL;
        break;

    case 'migrate:fresh':
        echo "Dropping existing database and running fresh migrations..." . PHP_EOL;
        $dbPath = app_config('database.path');
        if (file_exists($dbPath)) {
            unlink($dbPath);
            echo "Old database removed." . PHP_EOL;
        }
        Database::reset();
        $runner = new MigrationRunner();
        $applied = $runner->run();
        foreach ($applied as $m) {
            echo " [OK] Applied: {$m}" . PHP_EOL;
        }
        echo "Seeding fresh database..." . PHP_EOL;
        $seeder = new DatabaseSeeder();
        $seeder->run();
        echo "Fresh database setup completed!" . PHP_EOL;
        break;

    case 'status':
        $runner = new MigrationRunner();
        $applied = $runner->getExecutedMigrations();
        echo "Applied migrations (" . count($applied) . "):" . PHP_EOL;
        foreach ($applied as $m) {
            echo " - {$m}" . PHP_EOL;
        }
        break;

    default:
        echo "Usage: php cli.php [migrate|seed|migrate:fresh|status]" . PHP_EOL;
        break;
}
