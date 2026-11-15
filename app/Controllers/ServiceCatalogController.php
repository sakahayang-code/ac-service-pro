<?php

declare(strict_types=1);

namespace App\Controllers;

use PDO;
use App\Services\Auth;
use App\Services\Database;

class ServiceCatalogController
{
    public function index(): void
    {
        $pdo = Database::getConnection();
        $categoryId = isset($_GET['category_id']) && $_GET['category_id'] !== '' ? (int)$_GET['category_id'] : null;

        $categories = $pdo->query("SELECT * FROM service_categories ORDER BY sort_order ASC")->fetchAll(PDO::FETCH_ASSOC);

        $sql = "SELECT p.*, c.name as category_name
                FROM service_products p
                JOIN service_categories c ON p.category_id = c.id";
        $params = [];

        if ($categoryId !== null) {
            $sql .= " WHERE p.category_id = :cat_id";
            $params[':cat_id'] = $categoryId;
        }

        $sql .= " ORDER BY c.sort_order ASC, p.base_price ASC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $services = $stmt->fetchAll(PDO::FETCH_ASSOC);

        view('admin.service_catalog.index', [
            'title' => 'Katalog Layanan & Tarif Servis',
            'categories' => $categories,
            'services' => $services,
            'selectedCategoryId' => $categoryId,
        ]);
    }

    public function create(): void
    {
        $pdo = Database::getConnection();
        $categories = $pdo->query("SELECT * FROM service_categories ORDER BY sort_order ASC")->fetchAll(PDO::FETCH_ASSOC);

        view('admin.service_catalog.create', [
            'title' => 'Tambah Layanan Baru',
            'categories' => $categories,
        ]);
    }

    public function store(): void
    {
        $categoryId = (int)($_POST['category_id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $code = trim($_POST['code'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $capacityPkFilter = trim($_POST['capacity_pk_filter'] ?? 'ALL');
        $refrigerantFilter = trim($_POST['refrigerant_filter'] ?? 'ALL');
        $basePrice = (int)($_POST['base_price'] ?? 0);
        $contractPrice = (int)($_POST['contract_price'] ?? 0);
        $durationMinutes = (int)($_POST['estimated_duration_minutes'] ?? 60);

        if ($categoryId <= 0 || empty($name) || empty($code)) {
            $_SESSION['flash_error'] = 'Kategori, nama layanan, dan kode layanan wajib diisi.';
            redirect('service-catalog/create');
            return;
        }

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            INSERT INTO service_products (
                category_id, code, name, description, capacity_pk_filter,
                refrigerant_filter, base_price, contract_price, estimated_duration_minutes, is_active
            ) VALUES (
                :cat_id, :code, :name, :desc, :pk,
                :freon, :base_price, :contract_price, :duration, 1
            )
        ");
        $stmt->execute([
            ':cat_id' => $categoryId,
            ':code' => $code,
            ':name' => $name,
            ':desc' => $description ?: null,
            ':pk' => $capacityPkFilter,
            ':freon' => $refrigerantFilter,
            ':base_price' => $basePrice,
            ':contract_price' => $contractPrice ?: $basePrice,
            ':duration' => $durationMinutes,
        ]);

        audit_log('create', 'service_products', (int)$pdo->lastInsertId(), null, ['name' => $name, 'price' => $basePrice]);
        $_SESSION['flash_success'] = "Layanan {$name} berhasil ditambahkan ke katalog!";
        redirect('service-catalog');
    }

    public function edit(string $id): void
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM service_products WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => (int)$id]);
        $service = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$service) {
            redirect('service-catalog');
            return;
        }

        $categories = $pdo->query("SELECT * FROM service_categories ORDER BY sort_order ASC")->fetchAll(PDO::FETCH_ASSOC);

        view('admin.service_catalog.edit', [
            'title' => 'Ubah Layanan - ' . $service['name'],
            'service' => $service,
            'categories' => $categories,
        ]);
    }

    public function update(string $id): void
    {
        $pdo = Database::getConnection();
        $categoryId = (int)($_POST['category_id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $code = trim($_POST['code'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $capacityPkFilter = trim($_POST['capacity_pk_filter'] ?? 'ALL');
        $refrigerantFilter = trim($_POST['refrigerant_filter'] ?? 'ALL');
        $basePrice = (int)($_POST['base_price'] ?? 0);
        $contractPrice = (int)($_POST['contract_price'] ?? 0);
        $durationMinutes = (int)($_POST['estimated_duration_minutes'] ?? 60);
        $isActive = !empty($_POST['is_active']) ? 1 : 0;

        $stmt = $pdo->prepare("
            UPDATE service_products
            SET category_id = :cat_id, code = :code, name = :name, description = :desc,
                capacity_pk_filter = :pk, refrigerant_filter = :freon, base_price = :base_price,
                contract_price = :contract_price, estimated_duration_minutes = :duration,
                is_active = :is_active, updated_at = datetime('now', 'localtime')
            WHERE id = :id
        ");
        $stmt->execute([
            ':cat_id' => $categoryId,
            ':code' => $code,
            ':name' => $name,
            ':desc' => $description ?: null,
            ':pk' => $capacityPkFilter,
            ':freon' => $refrigerantFilter,
            ':base_price' => $basePrice,
            ':contract_price' => $contractPrice ?: $basePrice,
            ':duration' => $durationMinutes,
            ':is_active' => $isActive,
            ':id' => (int)$id,
        ]);

        audit_log('update', 'service_products', (int)$id, null, ['name' => $name, 'price' => $basePrice]);
        $_SESSION['flash_success'] = 'Layanan berhasil diperbarui!';
        redirect('service-catalog');
    }

    public function toggleActive(string $id): void
    {
        $pdo = Database::getConnection();
        $pdo->prepare("
            UPDATE service_products
            SET is_active = CASE WHEN is_active = 1 THEN 0 ELSE 1 END, updated_at = datetime('now', 'localtime')
            WHERE id = :id
        ")->execute([':id' => (int)$id]);

        $_SESSION['flash_success'] = 'Status layanan berhasil diubah.';
        redirect('service-catalog');
    }
}
