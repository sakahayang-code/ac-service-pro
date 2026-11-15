<?php

declare(strict_types=1);

namespace Database\Seeds;

use PDO;
use App\Services\Database;

class DatabaseSeeder
{
    private PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? Database::getConnection();
    }

    public function run(): void
    {
        Database::transaction(function (PDO $pdo) {
            $this->seedRolesAndPermissions($pdo);
            $this->seedUsers($pdo);
            $this->seedDocumentSequences($pdo);
            $this->seedSettings($pdo);
            $this->seedAccounts($pdo);
            $this->seedServiceCatalog($pdo);
            $this->seedInventoryAndSuppliers($pdo);
            $this->seedCustomersAndUnits($pdo);
            $this->seedWorkOrdersAndTransactions($pdo);
        });
    }

    private function seedRolesAndPermissions(PDO $pdo): void
    {
        $roles = [
            ['name' => 'owner', 'display_name' => 'Pemilik Usaha (Owner)', 'description' => 'Akses penuh ke seluruh sistem dan laporan finansial.'],
            ['name' => 'admin', 'display_name' => 'Administrator', 'description' => 'Mengelola operasional harian, data master, dan penugasan.'],
            ['name' => 'customer_service', 'display_name' => 'Customer Service', 'description' => 'Mengelola pesanan masuk, pendaftaran pelanggan, dan komplain.'],
            ['name' => 'cashier', 'display_name' => 'Kasir', 'description' => 'Mengelola faktur/invoice, penerimaan pembayaran, dan kas harian.'],
            ['name' => 'technician', 'display_name' => 'Teknisi Lapangan', 'description' => 'Akses aplikasi mobile teknisi untuk menyelesaikan work order.'],
            ['name' => 'inventory', 'display_name' => 'Petugas Gudang', 'description' => 'Mengelola stok sparepart, mutasi barang, dan pembelian.'],
            ['name' => 'customer', 'display_name' => 'Pelanggan', 'description' => 'Portal pelanggan untuk pesan servis, cek AC, dan riwayat invoice.'],
        ];

        $stmt = $pdo->prepare("INSERT OR IGNORE INTO roles (name, display_name, description, is_system) VALUES (:name, :display_name, :description, 1)");
        foreach ($roles as $r) {
            $stmt->execute($r);
        }

        $permissions = [
            ['code' => 'dashboard.view', 'module' => 'Dashboard', 'display_name' => 'Lihat Dashboard'],
            ['code' => 'dashboard.financial', 'module' => 'Dashboard', 'display_name' => 'Lihat Finansial & Profit'],
            ['code' => 'customer.manage', 'module' => 'Pelanggan', 'display_name' => 'Kelola Pelanggan'],
            ['code' => 'customer.view', 'module' => 'Pelanggan', 'display_name' => 'Lihat Data Pelanggan'],
            ['code' => 'ac_unit.manage', 'module' => 'Unit AC', 'display_name' => 'Kelola Data Unit AC'],
            ['code' => 'work_order.manage', 'module' => 'Work Order', 'display_name' => 'Kelola Work Order'],
            ['code' => 'work_order.assign', 'module' => 'Work Order', 'display_name' => 'Tugaskan Teknisi'],
            ['code' => 'service_execution.execute', 'module' => 'Teknisi Lapangan', 'display_name' => 'Eksekusi Servis Mobile'],
            ['code' => 'catalog.manage', 'module' => 'Katalog', 'display_name' => 'Kelola Layanan & Tarif'],
            ['code' => 'inventory.manage', 'module' => 'Inventori', 'display_name' => 'Kelola Stok & Pembelian'],
            ['code' => 'invoice.manage', 'module' => 'Kasir', 'display_name' => 'Kelola Faktur & Kasir'],
            ['code' => 'finance.manage', 'module' => 'Keuangan', 'display_name' => 'Kelola Keuangan & Arus Kas'],
            ['code' => 'contract.manage', 'module' => 'Kontrak', 'display_name' => 'Kelola Kontrak Maintenance'],
            ['code' => 'reports.view', 'module' => 'Laporan', 'display_name' => 'Lihat & Ekspor Laporan'],
            ['code' => 'settings.manage', 'module' => 'Pengaturan', 'display_name' => 'Kelola Pengaturan Sistem'],
        ];

        $stmtPerm = $pdo->prepare("INSERT OR IGNORE INTO permissions (code, module, display_name) VALUES (:code, :module, :display_name)");
        foreach ($permissions as $p) {
            $stmtPerm->execute($p);
        }

        // Assign all permissions to owner & admin
        $pdo->exec("
            INSERT OR IGNORE INTO role_permissions (role_id, permission_id)
            SELECT r.id, p.id FROM roles r CROSS JOIN permissions p WHERE r.name IN ('owner', 'admin');
        ");

        // Customer service permissions
        $pdo->exec("
            INSERT OR IGNORE INTO role_permissions (role_id, permission_id)
            SELECT r.id, p.id FROM roles r CROSS JOIN permissions p
            WHERE r.name = 'customer_service' AND p.code IN ('dashboard.view', 'customer.manage', 'customer.view', 'ac_unit.manage', 'work_order.manage', 'contract.manage');
        ");

        // Cashier permissions
        $pdo->exec("
            INSERT OR IGNORE INTO role_permissions (role_id, permission_id)
            SELECT r.id, p.id FROM roles r CROSS JOIN permissions p
            WHERE r.name = 'cashier' AND p.code IN ('dashboard.view', 'customer.view', 'invoice.manage', 'finance.manage');
        ");

        // Technician permissions
        $pdo->exec("
            INSERT OR IGNORE INTO role_permissions (role_id, permission_id)
            SELECT r.id, p.id FROM roles r CROSS JOIN permissions p
            WHERE r.name = 'technician' AND p.code IN ('dashboard.view', 'service_execution.execute');
        ");

        // Inventory permissions
        $pdo->exec("
            INSERT OR IGNORE INTO role_permissions (role_id, permission_id)
            SELECT r.id, p.id FROM roles r CROSS JOIN permissions p
            WHERE r.name = 'inventory' AND p.code IN ('dashboard.view', 'inventory.manage');
        ");
    }

    private function seedUsers(PDO $pdo): void
    {
        $passwordHash = password_hash('Password123!', PASSWORD_BCRYPT);
        $rolesStmt = $pdo->query("SELECT name, id FROM roles");
        $roles = $rolesStmt->fetchAll(PDO::FETCH_KEY_PAIR);

        $users = [
            ['name' => 'Ir. Hartono Wibowo (Owner)', 'email' => 'owner@acservicepro.id', 'phone' => '081234567890', 'role' => 'owner'],
            ['name' => 'Dewi Anggraini (Admin)', 'email' => 'admin@acservicepro.id', 'phone' => '081234567891', 'role' => 'admin'],
            ['name' => 'Maya Sari (Customer Service)', 'email' => 'cs@acservicepro.id', 'phone' => '081234567892', 'role' => 'customer_service'],
            ['name' => 'Rina Kartika (Kasir)', 'email' => 'kasir@acservicepro.id', 'phone' => '081234567893', 'role' => 'cashier'],
            ['name' => 'Budi Santoso (Teknisi Senior)', 'email' => 'teknisi1@acservicepro.id', 'phone' => '081234567894', 'role' => 'technician'],
            ['name' => 'Ahmad Rifai (Teknisi Lapangan)', 'email' => 'teknisi2@acservicepro.id', 'phone' => '081234567895', 'role' => 'technician'],
            ['name' => 'Guntur Prakoso (Petugas Gudang)', 'email' => 'gudang@acservicepro.id', 'phone' => '081234567896', 'role' => 'inventory'],
            ['name' => 'Dr. Hendra Wijaya (Pelanggan)', 'email' => 'pelanggan@gmail.com', 'phone' => '081299887766', 'role' => 'customer'],
        ];

        $stmt = $pdo->prepare("
            INSERT OR IGNORE INTO users (role_id, name, email, phone, password_hash, status)
            VALUES (:role_id, :name, :email, :phone, :password_hash, 'active')
        ");

        foreach ($users as $u) {
            $roleId = $roles[$u['role']] ?? 1;
            $stmt->execute([
                ':role_id' => $roleId,
                ':name' => $u['name'],
                ':email' => $u['email'],
                ':phone' => $u['phone'],
                ':password_hash' => $passwordHash,
            ]);
        }

        // Create Technicians entries for technician users
        $tech1User = $pdo->query("SELECT id FROM users WHERE email = 'teknisi1@acservicepro.id'")->fetchColumn();
        if ($tech1User) {
            $pdo->exec("
                INSERT OR IGNORE INTO technicians (user_id, code, full_name, phone, skills, certifications, work_area, commission_rate_percent, status, rating, total_jobs_done)
                VALUES ($tech1User, 'TEK-001', 'Budi Santoso', '081234567894', 'Instalasi Inverter, Perbaikan Kompresor, Flushing Freon', 'BNSP Teknisi AC Level 3', 'Jakarta Selatan, Depok', 20.0, 'active', 4.9, 142);
            ");
        }

        $tech2User = $pdo->query("SELECT id FROM users WHERE email = 'teknisi2@acservicepro.id'")->fetchColumn();
        if ($tech2User) {
            $pdo->exec("
                INSERT OR IGNORE INTO technicians (user_id, code, full_name, phone, skills, certifications, work_area, commission_rate_percent, status, rating, total_jobs_done)
                VALUES ($tech2User, 'TEK-002', 'Ahmad Rifai', '081234567895', 'Cuci Kimia, Ganti Sparepart, Pengisian Freon R32/R410A', 'Sertifikasi Asosiasi Tata Udara (ASISI)', 'Jakarta Timur, Bekasi', 20.0, 'active', 4.8, 98);
            ");
        }
    }

    private function seedDocumentSequences(PDO $pdo): void
    {
        $sequences = [
            ['sequence_key' => 'WO', 'prefix' => 'WO', 'current_number' => 10, 'padding' => 4, 'format_pattern' => '{PREFIX}-{YEAR}{MONTH}-{NUMBER}'],
            ['sequence_key' => 'INV', 'prefix' => 'INV', 'current_number' => 8, 'padding' => 4, 'format_pattern' => '{PREFIX}-{YEAR}{MONTH}-{NUMBER}'],
            ['sequence_key' => 'SPK', 'prefix' => 'SPK', 'current_number' => 10, 'padding' => 4, 'format_pattern' => '{PREFIX}-{YEAR}{MONTH}-{NUMBER}'],
            ['sequence_key' => 'PO', 'prefix' => 'PO', 'current_number' => 3, 'padding' => 4, 'format_pattern' => '{PREFIX}-{YEAR}{MONTH}-{NUMBER}'],
            ['sequence_key' => 'CUST', 'prefix' => 'CUST', 'current_number' => 5, 'padding' => 4, 'format_pattern' => '{PREFIX}-{NUMBER}'],
            ['sequence_key' => 'AC', 'prefix' => 'AC', 'current_number' => 8, 'padding' => 4, 'format_pattern' => '{PREFIX}-{NUMBER}'],
            ['sequence_key' => 'PAY', 'prefix' => 'PAY', 'current_number' => 8, 'padding' => 4, 'format_pattern' => '{PREFIX}-{YEAR}{MONTH}-{NUMBER}'],
            ['sequence_key' => 'EXP', 'prefix' => 'EXP', 'current_number' => 4, 'padding' => 4, 'format_pattern' => '{PREFIX}-{YEAR}{MONTH}-{NUMBER}'],
            ['sequence_key' => 'CONT', 'prefix' => 'MC', 'current_number' => 2, 'padding' => 4, 'format_pattern' => '{PREFIX}-{YEAR}-{NUMBER}'],
            ['sequence_key' => 'CMP', 'prefix' => 'KOM', 'current_number' => 1, 'padding' => 4, 'format_pattern' => '{PREFIX}-{YEAR}{MONTH}-{NUMBER}'],
            ['sequence_key' => 'CLM', 'prefix' => 'GAR', 'current_number' => 1, 'padding' => 4, 'format_pattern' => '{PREFIX}-{YEAR}{MONTH}-{NUMBER}'],
        ];

        $stmt = $pdo->prepare("
            INSERT OR IGNORE INTO document_sequences (sequence_key, prefix, current_number, padding, format_pattern)
            VALUES (:sequence_key, :prefix, :current_number, :padding, :format_pattern)
        ");
        foreach ($sequences as $s) {
            $stmt->execute($s);
        }
    }

    private function seedSettings(PDO $pdo): void
    {
        $settings = [
            ['setting_key' => 'company_name', 'setting_value' => 'AC Service Pro Indonesia', 'group_name' => 'company', 'description' => 'Nama Usaha / Perusahaan'],
            ['setting_key' => 'company_tagline', 'setting_value' => 'Solusi Cepat, Bersih & Profesional Servis AC', 'group_name' => 'company', 'description' => 'Tagline Perusahaan'],
            ['setting_key' => 'company_address', 'setting_value' => 'Jl. Merdeka Sejahtera No. 88, Jakarta Selatan 12430', 'group_name' => 'company', 'description' => 'Alamat Lengkap Operasional'],
            ['setting_key' => 'company_phone', 'setting_value' => '021-78901234', 'group_name' => 'company', 'description' => 'Nomor Telepon Kantor'],
            ['setting_key' => 'company_whatsapp', 'setting_value' => '0812-3456-7890', 'group_name' => 'company', 'description' => 'Nomor WhatsApp Resmi'],
            ['setting_key' => 'company_email', 'setting_value' => 'admin@acservicepro.id', 'group_name' => 'company', 'description' => 'Alamat Email Resmi'],
            ['setting_key' => 'tax_rate_percent', 'setting_value' => '0', 'group_name' => 'finance', 'description' => 'Tarif Pajak PPN (0 jika dinonaktifkan)'],
            ['setting_key' => 'default_warranty_days', 'setting_value' => '30', 'group_name' => 'operational', 'description' => 'Masa Garansi Servis Standar (Hari)'],
            ['setting_key' => 'service_reminder_days', 'setting_value' => '90', 'group_name' => 'crm', 'description' => 'Interval Pengingat Servis Berkala (Hari)'],
            ['setting_key' => 'operating_hours', 'setting_value' => 'Senin - Minggu: 08:00 - 18:00 WIB', 'group_name' => 'operational', 'description' => 'Jam Kerja Operasional'],
            ['setting_key' => 'invoice_footer_note', 'setting_value' => 'Terima kasih atas kepercayaan Anda. Garansi servis 30 hari untuk kerusakan yang sama.', 'group_name' => 'finance', 'description' => 'Catatan Kaki Faktur'],
            ['setting_key' => 'qris_merchant_name', 'setting_value' => 'AC SERVICE PRO JKT', 'group_name' => 'finance', 'description' => 'Nama Merchant QRIS'],
        ];

        $stmt = $pdo->prepare("
            INSERT OR IGNORE INTO settings (setting_key, setting_value, group_name, description)
            VALUES (:setting_key, :setting_value, :group_name, :description)
        ");
        foreach ($settings as $item) {
            $stmt->execute($item);
        }
    }

    private function seedAccounts(PDO $pdo): void
    {
        $accounts = [
            ['code' => '1001', 'name' => 'Kas Operasional Kantor', 'type' => 'asset', 'balance' => 5450000],
            ['code' => '1002', 'name' => 'Rekening Bank BCA', 'type' => 'asset', 'balance' => 28750000],
            ['code' => '1003', 'name' => 'Rekening Bank Mandiri', 'type' => 'asset', 'balance' => 14200000],
            ['code' => '1101', 'name' => 'Piutang Usaha Pelanggan', 'type' => 'asset', 'balance' => 850000],
            ['code' => '1201', 'name' => 'Persediaan Sparepart & Freon', 'type' => 'asset', 'balance' => 9350000],
            ['code' => '2001', 'name' => 'Hutang Dagang Supplier', 'type' => 'liability', 'balance' => 0],
            ['code' => '4001', 'name' => 'Pendapatan Jasa Servis AC', 'type' => 'revenue', 'balance' => 0],
            ['code' => '4002', 'name' => 'Pendapatan Penjualan Sparepart', 'type' => 'revenue', 'balance' => 0],
            ['code' => '5001', 'name' => 'Beban Operasional & Alat', 'type' => 'expense', 'balance' => 0],
            ['code' => '5002', 'name' => 'Beban BBM & Transportasi', 'type' => 'expense', 'balance' => 0],
            ['code' => '5003', 'name' => 'Beban Gaji & Komisi Teknisi', 'type' => 'expense', 'balance' => 0],
        ];

        $stmt = $pdo->prepare("
            INSERT OR IGNORE INTO accounts (code, name, type, balance)
            VALUES (:code, :name, :type, :balance)
        ");
        foreach ($accounts as $acc) {
            $stmt->execute($acc);
        }
    }

    private function seedServiceCatalog(PDO $pdo): void
    {
        $categories = [
            ['name' => 'Pembersihan & Cuci AC', 'slug' => 'cuci-ac', 'description' => 'Layanan pembersihan unit indoor dan outdoor', 'sort_order' => 1],
            ['name' => 'Freon & Refrigerant', 'slug' => 'freon', 'description' => 'Penambahan dan pengisian ulang refrigerant tekanan standar pabrik', 'sort_order' => 2],
            ['name' => 'Bongkar & Pasang', 'slug' => 'bongkar-pasang', 'description' => 'Relokasi dan instalasi unit AC baru atau lama', 'sort_order' => 3],
            ['name' => 'Perbaikan & Troubleshooting', 'slug' => 'perbaikan', 'description' => 'Diagnosis kebocoran, kelistrikan, dan penggantian sparepart', 'sort_order' => 4],
            ['name' => 'Paket Maintenance Berkala', 'slug' => 'maintenance', 'description' => 'Paket servis rutin 3 bulanan untuk rumah dan gedung', 'sort_order' => 5],
        ];

        $stmtCat = $pdo->prepare("
            INSERT OR IGNORE INTO service_categories (name, slug, description, sort_order)
            VALUES (:name, :slug, :description, :sort_order)
        ");
        foreach ($categories as $cat) {
            $stmtCat->execute($cat);
        }

        $catMap = $pdo->query("SELECT slug, id FROM service_categories")->fetchAll(PDO::FETCH_KEY_PAIR);

        $services = [
            ['category_id' => $catMap['cuci-ac'], 'code' => 'SRV-WASH-01', 'name' => 'Cuci AC Standar 0.5 - 1 PK', 'description' => 'Cuci indoor, filter, talang air, evaporator, dan outdoor', 'capacity_pk_filter' => '0.5 - 1 PK', 'base_price' => 75000, 'contract_price' => 65000, 'estimated_duration_minutes' => 45],
            ['category_id' => $catMap['cuci-ac'], 'code' => 'SRV-WASH-02', 'name' => 'Cuci AC Besar 1.5 - 2 PK', 'description' => 'Cuci menyeluruh untuk unit kapasitas besar', 'capacity_pk_filter' => '1.5 - 2 PK', 'base_price' => 90000, 'contract_price' => 80000, 'estimated_duration_minutes' => 60],
            ['category_id' => $catMap['cuci-ac'], 'code' => 'SRV-WASH-03', 'name' => 'Cuci AC Inverter & Disinfeksi', 'description' => 'Perawatan hati-hati modul inverter + penyemprotan antibakteri', 'capacity_pk_filter' => 'ALL', 'base_price' => 95000, 'contract_price' => 85000, 'estimated_duration_minutes' => 60],
            ['category_id' => $catMap['cuci-ac'], 'code' => 'SRV-WASH-OVERHAUL', 'name' => 'Cuci Overhaul (Turun Unit)', 'description' => 'Bongkar unit indoor untuk pembersihan kerak membandel', 'capacity_pk_filter' => 'ALL', 'base_price' => 250000, 'contract_price' => 220000, 'estimated_duration_minutes' => 120],
            ['category_id' => $catMap['freon'], 'code' => 'SRV-FR-R32-ADD', 'name' => 'Tambah Freon R32 / R410A (0.5 - 1 PK)', 'description' => 'Penambahan tekanan freon ramah lingkungan', 'capacity_pk_filter' => '0.5 - 1 PK', 'base_price' => 150000, 'contract_price' => 135000, 'estimated_duration_minutes' => 30],
            ['category_id' => $catMap['freon'], 'code' => 'SRV-FR-R32-FULL', 'name' => 'Isi Ulang Freon R32 / R410A Penuh + Vakum', 'description' => 'Pengisian dari kosong setelah perbaikan kebocoran', 'capacity_pk_filter' => 'ALL', 'base_price' => 275000, 'contract_price' => 240000, 'estimated_duration_minutes' => 60],
            ['category_id' => $catMap['freon'], 'code' => 'SRV-FR-R22-ADD', 'name' => 'Tambah Freon R22 (Unit Lama)', 'description' => 'Penambahan freon R22 konvensional', 'capacity_pk_filter' => 'ALL', 'base_price' => 135000, 'contract_price' => 120000, 'estimated_duration_minutes' => 30],
            ['category_id' => $catMap['bongkar-pasang'], 'code' => 'SRV-BP-BONGKAR', 'name' => 'Jasa Bongkar AC Standar', 'description' => 'Bongkar unit indoor & outdoor dengan pump-down freon', 'capacity_pk_filter' => '0.5 - 1 PK', 'base_price' => 150000, 'contract_price' => 130000, 'estimated_duration_minutes' => 60],
            ['category_id' => $catMap['bongkar-pasang'], 'code' => 'SRV-BP-PASANG', 'name' => 'Jasa Pasang AC Standar (0.5 - 1 PK)', 'description' => 'Instalasi bracket, pemipaan, dan vakum instalasi', 'capacity_pk_filter' => '0.5 - 1 PK', 'base_price' => 250000, 'contract_price' => 220000, 'estimated_duration_minutes' => 90],
            ['category_id' => $catMap['bongkar-pasang'], 'code' => 'SRV-BP-PAKET', 'name' => 'Paket Bongkar & Pasang Relokasi', 'description' => 'Pindahan AC ke lokasi baru termasuk pengecekan fungsi', 'capacity_pk_filter' => '0.5 - 1 PK', 'base_price' => 380000, 'contract_price' => 340000, 'estimated_duration_minutes' => 150],
            ['category_id' => $catMap['perbaikan'], 'code' => 'SRV-REP-LEAK', 'name' => 'Perbaikan Kebocoran Air Indoor / Drain', 'description' => 'Pembersihan saluran buang tersumbat dan perbaikan isolasi', 'capacity_pk_filter' => 'ALL', 'base_price' => 120000, 'contract_price' => 100000, 'estimated_duration_minutes' => 45],
            ['category_id' => $catMap['perbaikan'], 'code' => 'SRV-REP-CHECK', 'name' => 'Pengecekan Kerusakan & Diagnosis', 'description' => 'Pemeriksaan kelistrikan, amperemeter, kompresor (Free jika lanjut servis)', 'capacity_pk_filter' => 'ALL', 'base_price' => 50000, 'contract_price' => 0, 'estimated_duration_minutes' => 30],
        ];

        $stmtSrv = $pdo->prepare("
            INSERT OR IGNORE INTO service_products (category_id, code, name, description, capacity_pk_filter, base_price, contract_price, estimated_duration_minutes)
            VALUES (:category_id, :code, :name, :description, :capacity_pk_filter, :base_price, :contract_price, :estimated_duration_minutes)
        ");
        foreach ($services as $srv) {
            $stmtSrv->execute($srv);
        }
    }

    private function seedInventoryAndSuppliers(PDO $pdo): void
    {
        $suppliers = [
            ['code' => 'SUP-001', 'name' => 'PT Sumber Jaya Rezeki', 'contact_person' => 'Bpk. Gunawan', 'phone' => '021-55443322', 'email' => 'sales@sumberjayarezeki.com', 'address' => 'Kawasan Pergudangan Glodok Blok C No. 12, Jakarta Barat'],
            ['code' => 'SUP-002', 'name' => 'CV Sejuk Mandiri Parts', 'contact_person' => 'Ibu Lilis', 'phone' => '021-88997766', 'email' => 'order@sejukmandiri.com', 'address' => 'Jl. Pahlawan Revolusi No. 45, Duren Sawit, Jakarta Timur'],
        ];

        $stmtSup = $pdo->prepare("
            INSERT OR IGNORE INTO suppliers (code, name, contact_person, phone, email, address)
            VALUES (:code, :name, :contact_person, :phone, :email, :address)
        ");
        foreach ($suppliers as $sup) {
            $stmtSup->execute($sup);
        }

        $items = [
            ['sku' => 'PRT-CAP-25UF', 'name' => 'Kapasitor Kompresor 25 uF 450V', 'category' => 'Kapasitor', 'unit' => 'Pcs', 'cost_price' => 42000, 'selling_price' => 85000, 'min_stock' => 5.0, 'current_stock' => 18.0],
            ['sku' => 'PRT-CAP-35UF', 'name' => 'Kapasitor Kompresor 35 uF 450V', 'category' => 'Kapasitor', 'unit' => 'Pcs', 'cost_price' => 52000, 'selling_price' => 105000, 'min_stock' => 5.0, 'current_stock' => 14.0],
            ['sku' => 'PRT-FR-R32', 'name' => 'Freon R32 Tabung Segel 3 Kg', 'category' => 'Refrigerant', 'unit' => 'Tabung', 'cost_price' => 210000, 'selling_price' => 310000, 'min_stock' => 3.0, 'current_stock' => 7.0],
            ['sku' => 'PRT-FR-R410A', 'name' => 'Freon R410A Tabung Segel 3 Kg', 'category' => 'Refrigerant', 'unit' => 'Tabung', 'cost_price' => 220000, 'selling_price' => 330000, 'min_stock' => 3.0, 'current_stock' => 5.0],
            ['sku' => 'PRT-PIPE-023', 'name' => 'Pipa Tembaga AC Tateyama 1/4 + 3/8 Tebal 0.6mm', 'category' => 'Pipa & Isolasi', 'unit' => 'Meter', 'cost_price' => 62000, 'selling_price' => 95000, 'min_stock' => 20.0, 'current_stock' => 45.0],
            ['sku' => 'PRT-RMT-UNI', 'name' => 'Remote AC Universal LCD Backlight Chunghop', 'category' => 'Aksesoris', 'unit' => 'Pcs', 'cost_price' => 28000, 'selling_price' => 65000, 'min_stock' => 5.0, 'current_stock' => 22.0],
            ['sku' => 'PRT-SEN-THRM', 'name' => 'Sensor Suhu Thermistor Indoor Socket Kuning', 'category' => 'Elektrikal', 'unit' => 'Pcs', 'cost_price' => 22000, 'selling_price' => 55000, 'min_stock' => 4.0, 'current_stock' => 12.0],
            ['sku' => 'PRT-OVL-051', 'name' => 'Overload Kompresor AC 0.5 - 1 PK External', 'category' => 'Protektor', 'unit' => 'Pcs', 'cost_price' => 30000, 'selling_price' => 70000, 'min_stock' => 4.0, 'current_stock' => 8.0],
            ['sku' => 'PRT-BRK-OUT', 'name' => 'Bracket Outdoor Tebal 2mm + Dynabolt', 'category' => 'Bracket', 'unit' => 'Set', 'cost_price' => 45000, 'selling_price' => 85000, 'min_stock' => 5.0, 'current_stock' => 16.0],
            ['sku' => 'PRT-DRN-FLEX', 'name' => 'Selang Pembuangan Air Fleksibel Spiral 16mm', 'category' => 'Drainase', 'unit' => 'Meter', 'cost_price' => 5000, 'selling_price' => 15000, 'min_stock' => 20.0, 'current_stock' => 60.0],
        ];

        $stmtItem = $pdo->prepare("
            INSERT OR IGNORE INTO inventory_items (sku, name, category, unit, cost_price, selling_price, min_stock, current_stock)
            VALUES (:sku, :name, :category, :unit, :cost_price, :selling_price, :min_stock, :current_stock)
        ");
        foreach ($items as $item) {
            $stmtItem->execute($item);
        }

        // Add central warehouse stock balance
        $pdo->exec("
            INSERT OR IGNORE INTO inventory_stocks (inventory_item_id, location_type, location_ref_id, quantity)
            SELECT id, 'warehouse', NULL, current_stock FROM inventory_items;
        ");
    }

    private function seedCustomersAndUnits(PDO $pdo): void
    {
        $custUser = $pdo->query("SELECT id FROM users WHERE email = 'pelanggan@gmail.com'")->fetchColumn();

        $customers = [
            [
                'user_id' => $custUser ?: null,
                'customer_code' => 'CUST-0001',
                'category' => 'household',
                'name' => 'Dr. Hendra Wijaya',
                'phone' => '081299887766',
                'email' => 'pelanggan@gmail.com',
                'address' => 'Jl. Kemang Raya No. 18B, Bangka, Mampang Prapatan, Jakarta Selatan',
                'latitude' => -6.2738,
                'longitude' => 106.8152,
                'notes' => 'Rumah tingkat 2, parkir mudah, ada anjing jinak di dalam.',
                'total_orders' => 2,
                'total_spent' => 450000,
            ],
            [
                'user_id' => null,
                'customer_code' => 'CUST-0002',
                'category' => 'office',
                'name' => 'PT Mega Pratama Solusindo',
                'phone' => '081388776655',
                'email' => 'facility@megapratama.co.id',
                'address' => 'Gedung Wisma 46 Lt. 15, Jl. Jend. Sudirman Kav. 1, Jakarta Pusat',
                'latitude' => -6.2088,
                'longitude' => 106.8224,
                'notes' => 'Gedung perkantoran, wajib lapor security dan bawa KTP/ID badge.',
                'total_orders' => 1,
                'total_spent' => 950000,
            ],
            [
                'user_id' => null,
                'customer_code' => 'CUST-0003',
                'category' => 'household',
                'name' => 'Ibu Siti Rahmawati',
                'phone' => '081577665544',
                'email' => 'siti.rahma@yahoo.com',
                'address' => 'Perumahan Tebet Mas Indah Blok D No. 7, Tebet, Jakarta Selatan',
                'latitude' => -6.2361,
                'longitude' => 106.8528,
                'notes' => 'AC kamar anak menetes air ke kasur.',
                'total_orders' => 1,
                'total_spent' => 195000,
            ],
        ];

        $stmtCust = $pdo->prepare("
            INSERT OR IGNORE INTO customers (user_id, customer_code, category, name, phone, email, address, latitude, longitude, notes, total_orders, total_spent)
            VALUES (:user_id, :customer_code, :category, :name, :phone, :email, :address, :latitude, :longitude, :notes, :total_orders, :total_spent)
        ");
        foreach ($customers as $c) {
            $stmtCust->execute($c);
        }

        $c1 = $pdo->query("SELECT id FROM customers WHERE customer_code = 'CUST-0001'")->fetchColumn();
        $c2 = $pdo->query("SELECT id FROM customers WHERE customer_code = 'CUST-0002'")->fetchColumn();
        $c3 = $pdo->query("SELECT id FROM customers WHERE customer_code = 'CUST-0003'")->fetchColumn();

        if ($c1) {
            $pdo->exec("
                INSERT OR IGNORE INTO customer_addresses (customer_id, label, recipient_name, phone, address, latitude, longitude, is_default)
                VALUES ($c1, 'Rumah Utama', 'Dr. Hendra Wijaya', '081299887766', 'Jl. Kemang Raya No. 18B, Jakarta Selatan', -6.2738, 106.8152, 1);
            ");

            $pdo->exec("
                INSERT OR IGNORE INTO ac_units (customer_id, unit_code, brand, model_type, capacity_pk, serial_number, refrigerant_type, room_location, condition, next_service_due)
                VALUES 
                ($c1, 'AC-0001', 'Daikin', 'Inverter FTKQ', '1 PK', 'DKN-2023-8871', 'R32', 'Kamar Tidur Utama Lt.2', 'Normal Dingin', date('now', '+60 days')),
                ($c1, 'AC-0002', 'Panasonic', 'Standar CS-YN9', '1 PK', 'PNS-2022-4412', 'R32', 'Ruang Keluarga Lt.1', 'Kurang Dingin', date('now', '-5 days'));
            ");
        }

        if ($c2) {
            $pdo->exec("
                INSERT OR IGNORE INTO customer_addresses (customer_id, label, recipient_name, phone, address, latitude, longitude, is_default)
                VALUES ($c2, 'Kantor Pusat', 'Bpk. Rizky (GA Facility)', '081388776655', 'Gedung Wisma 46 Lt. 15, Jl. Jend. Sudirman Kav. 1, Jakarta Pusat', -6.2088, 106.8224, 1);
            ");

            $pdo->exec("
                INSERT OR IGNORE INTO ac_units (customer_id, unit_code, brand, model_type, capacity_pk, serial_number, refrigerant_type, room_location, condition, next_service_due)
                VALUES 
                ($c2, 'AC-0003', 'Mitsubishi Heavy Duty', 'Cassette 4-Way', '2.5 PK', 'MHD-2021-9921', 'R410A', 'Ruang Rapat Utama (Meeting Room A)', 'Normal', date('now', '+30 days')),
                ($c2, 'AC-0004', 'Daikin', 'Ceiling Suspended', '2 PK', 'DKN-2021-3312', 'R32', 'Ruang Server IT', 'Kritis / Perlu Cek', date('now', '+15 days'));
            ");
        }

        if ($c3) {
            $pdo->exec("
                INSERT OR IGNORE INTO ac_units (customer_id, unit_code, brand, model_type, capacity_pk, serial_number, refrigerant_type, room_location, condition, next_service_due)
                VALUES 
                ($c3, 'AC-0005', 'Sharp', 'Sayonara Panas AH-A5SEY', '0.5 PK', 'SHP-2020-1123', 'R32', 'Kamar Anak Depan', 'Bocor Air Menetes', date('now', 'localtime'));
            ");
        }
    }

    private function seedWorkOrdersAndTransactions(PDO $pdo): void
    {
        $c1 = $pdo->query("SELECT id FROM customers WHERE customer_code = 'CUST-0001'")->fetchColumn();
        $ac1 = $pdo->query("SELECT id FROM ac_units WHERE unit_code = 'AC-0001'")->fetchColumn();
        $ac2 = $pdo->query("SELECT id FROM ac_units WHERE unit_code = 'AC-0002'")->fetchColumn();
        $t1 = $pdo->query("SELECT id FROM technicians WHERE code = 'TEK-001'")->fetchColumn();
        $t2 = $pdo->query("SELECT id FROM technicians WHERE code = 'TEK-002'")->fetchColumn();
        $adminId = $pdo->query("SELECT id FROM users WHERE email = 'admin@acservicepro.id'")->fetchColumn();
        $cashierId = $pdo->query("SELECT id FROM users WHERE email = 'kasir@acservicepro.id'")->fetchColumn();
        $bankBca = $pdo->query("SELECT id FROM accounts WHERE code = '1002'")->fetchColumn();

        if ($c1 && $ac1 && $t1) {
            // WO 1: SELESAI & LUNAS
            $pdo->exec("
                INSERT OR IGNORE INTO work_orders (
                    id, order_number, customer_id, ac_unit_id, order_source, priority, status,
                    scheduled_date, scheduled_time, address_text, complaints, estimated_price, final_price,
                    created_by, created_at
                ) VALUES (
                    1, 'WO-202611-0001', $c1, $ac1, 'customer_portal', 'normal', 'LUNAS',
                    date('now', '-2 days'), '09:00', 'Jl. Kemang Raya No. 18B, Jakarta Selatan',
                    'Cuci AC berkala dan penambahan freon agar lebih sejuk', 225000, 225000,
                    $adminId, datetime('now', '-2 days')
                );

                INSERT OR IGNORE INTO work_order_technicians (work_order_id, technician_id, is_lead, commission_amount)
                VALUES (1, $t1, 1, 45000);

                INSERT OR IGNORE INTO work_order_services (work_order_id, service_name, quantity, unit_price, subtotal)
                VALUES 
                (1, 'Cuci AC Inverter & Disinfeksi', 1, 95000, 95000),
                (1, 'Tambah Freon R32 / R410A (0.5 - 1 PK)', 1, 130000, 130000);

                INSERT OR IGNORE INTO service_checklists (work_order_id, checklist_item, status, notes)
                VALUES 
                (1, 'Pemeriksaan Arus Listrik (Ampere)', 'ok', 'Ampere normal 3.2 A'),
                (1, 'Tekanan Freon Standar', 'ok', 'Tekanan 140 PSI'),
                (1, 'Pembersihan Filter Udara', 'ok', 'Filter bersih'),
                (1, 'Pembersihan Evaporator & Blower', 'ok', 'Lendir lumut bersih'),
                (1, 'Pembersihan Outdoor Condenser', 'ok', 'Outdoor disemprot lancar');

                INSERT OR IGNORE INTO service_diagnostics (work_order_id, current_ampere, voltage_volt, pressure_psi, temperature_celsius, diagnostic_result)
                VALUES (1, 3.2, 224.0, 140.0, 16.5, 'Unit Daikin Inverter normal dan dingin maksimal.');

                INSERT OR IGNORE INTO customer_signatures (work_order_id, signer_name, signature_data, rating, customer_notes)
                VALUES (1, 'Dr. Hendra Wijaya', 'data:image/svg+xml;utf8,<svg xmlns=\"http://www.w3.org/2000/svg\" width=\"200\" height=\"80\"><path d=\"M10,50 Q60,10 100,50 T190,40\" stroke=\"#1e3a8a\" stroke-width=\"3\" fill=\"none\"/></svg>', 5, 'Pengerjaan sangat rapi dan bersih. Teknisi pakai cover lantai.');

                INSERT OR IGNORE INTO invoices (
                    id, invoice_number, work_order_id, customer_id, issue_date, due_date,
                    subtotal_services, subtotal_materials, visit_fee, discount_amount, total_amount, paid_amount, balance_due, payment_status, created_by
                ) VALUES (
                    1, 'INV-202611-0001', 1, $c1, date('now', '-2 days'), date('now', '-2 days'),
                    225000, 0, 0, 0, 225000, 225000, 0, 'paid', $cashierId
                );

                INSERT OR IGNORE INTO invoice_items (invoice_id, item_type, description, quantity, unit_price, subtotal)
                VALUES 
                (1, 'service', 'Cuci AC Inverter & Disinfeksi', 1, 95000, 95000),
                (1, 'service', 'Tambah Freon R32 / R410A (0.5 - 1 PK)', 1, 130000, 130000);

                INSERT OR IGNORE INTO payments (
                    payment_number, invoice_id, amount, payment_method, account_id, payment_date, reference_number, notes, received_by
                ) VALUES (
                    'PAY-202611-0001', 1, 225000, 'bank_transfer', $bankBca, date('now', '-2 days'), 'TRF-BCA-887123', 'Lunas via Transfer BCA', $cashierId
                );
            ");
        }

        if ($c1 && $ac2 && $t2) {
            // WO 2: SEDANG_DIKERJAKAN (Hari ini)
            $pdo->exec("
                INSERT OR IGNORE INTO work_orders (
                    id, order_number, customer_id, ac_unit_id, order_source, priority, status,
                    scheduled_date, scheduled_time, address_text, complaints, estimated_price, created_by, created_at
                ) VALUES (
                    2, 'WO-202611-0002', $c1, $ac2, 'whatsapp', 'high', 'SEDANG_DIKERJAKAN',
                    date('now', 'localtime'), '10:00', 'Jl. Kemang Raya No. 18B, Jakarta Selatan',
                    'AC ruang keluarga kurang dingin, hanya hembus angin biasa', 250000, $adminId, datetime('now', 'localtime')
                );

                INSERT OR IGNORE INTO work_order_technicians (work_order_id, technician_id, is_lead)
                VALUES (2, $t2, 1);

                INSERT OR IGNORE INTO work_order_services (work_order_id, service_name, quantity, unit_price, subtotal)
                VALUES (2, 'Cuci AC Standar 0.5 - 1 PK', 1, 75000, 75000);
            ");
        }
    }
}
