<?php

declare(strict_types=1);

use App\Services\Router;
use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\CustomerController;
use App\Controllers\AcUnitController;
use App\Controllers\WorkOrderController;
use App\Controllers\TechnicianAppController;
use App\Controllers\TechnicianManagementController;
use App\Controllers\ServiceCatalogController;
use App\Controllers\InventoryController;
use App\Controllers\BillingController;
use App\Controllers\FinanceController;
use App\Controllers\MaintenanceContractController;
use App\Controllers\CustomerPortalController;
use App\Controllers\ReportController;
use App\Controllers\SettingController;

// Root redirect
Router::get('/', function() {
    redirect('dashboard');
});

// Authentication
Router::get('/login', [AuthController::class, 'showLoginForm']);
Router::post('/login', [AuthController::class, 'login']);
Router::get('/register', [AuthController::class, 'showRegisterForm']);
Router::post('/register', [AuthController::class, 'register']);
Router::get('/logout', [AuthController::class, 'logout']);
Router::get('/auth/switch-role', [AuthController::class, 'switchRole']);

// Main Dashboard
Router::get('/dashboard', [DashboardController::class, 'index'], ['auth']);
Router::get('/menu', [DashboardController::class, 'menu'], ['auth']);

// Customer Management (Modul 2)
Router::get('/customers', [CustomerController::class, 'index'], ['auth']);
Router::get('/customers/create', [CustomerController::class, 'create'], ['auth']);
Router::post('/customers', [CustomerController::class, 'store'], ['auth']);
Router::get('/customers/{id}', [CustomerController::class, 'show'], ['auth']);
Router::get('/customers/{id}/edit', [CustomerController::class, 'edit'], ['auth']);
Router::post('/customers/{id}/edit', [CustomerController::class, 'update'], ['auth']);
Router::post('/customers/{id}/delete', [CustomerController::class, 'destroy'], ['auth']);
Router::get('/customers/export/csv', [CustomerController::class, 'exportCsv'], ['auth']);

// AC Unit Management (Modul 3)
Router::get('/ac-units', [AcUnitController::class, 'index'], ['auth']);
Router::get('/ac-units/create', [AcUnitController::class, 'create'], ['auth']);
Router::post('/ac-units', [AcUnitController::class, 'store'], ['auth']);
Router::get('/ac-units/{id}', [AcUnitController::class, 'show'], ['auth']);
Router::get('/ac-units/{id}/edit', [AcUnitController::class, 'edit'], ['auth']);
Router::post('/ac-units/{id}/edit', [AcUnitController::class, 'update'], ['auth']);
Router::post('/ac-units/{id}/delete', [AcUnitController::class, 'destroy'], ['auth']);

// Work Order & Ordering (Modul 4)
Router::get('/work-orders', [WorkOrderController::class, 'index'], ['auth']);
Router::get('/work-orders/create', [WorkOrderController::class, 'create'], ['auth']);
Router::post('/work-orders', [WorkOrderController::class, 'store'], ['auth']);
Router::get('/work-orders/{id}', [WorkOrderController::class, 'show'], ['auth']);
Router::get('/work-orders/{id}/edit', [WorkOrderController::class, 'edit'], ['auth']);
Router::post('/work-orders/{id}/edit', [WorkOrderController::class, 'update'], ['auth']);
Router::post('/work-orders/{id}/status', [WorkOrderController::class, 'updateStatus'], ['auth']);
Router::post('/work-orders/{id}/assign', [WorkOrderController::class, 'assignTechnician'], ['auth']);
Router::get('/work-orders/{id}/spk', [WorkOrderController::class, 'printSpk'], ['auth']);
Router::post('/work-orders/{id}/cancel', [WorkOrderController::class, 'cancel'], ['auth']);

// Technician Management (Modul 5)
Router::get('/technicians', [TechnicianManagementController::class, 'index'], ['auth']);
Router::get('/technicians/create', [TechnicianManagementController::class, 'create'], ['auth']);
Router::post('/technicians', [TechnicianManagementController::class, 'store'], ['auth']);
Router::get('/technicians/{id}', [TechnicianManagementController::class, 'show'], ['auth']);

// Dedicated Technician Mobile App (Modul 6)
Router::get('/technician/tasks', [TechnicianAppController::class, 'myTasks'], ['auth']);
Router::get('/technician/tasks/{id}', [TechnicianAppController::class, 'executeTask'], ['auth']);
Router::post('/technician/tasks/{id}/checkin', [TechnicianAppController::class, 'checkIn'], ['auth']);
Router::post('/technician/tasks/{id}/checklist', [TechnicianAppController::class, 'saveChecklist'], ['auth']);
Router::post('/technician/tasks/{id}/diagnostics', [TechnicianAppController::class, 'saveDiagnostics'], ['auth']);
Router::post('/technician/tasks/{id}/material', [TechnicianAppController::class, 'addMaterial'], ['auth']);
Router::post('/technician/tasks/{id}/material/remove', [TechnicianAppController::class, 'removeMaterial'], ['auth']);
Router::post('/technician/tasks/{id}/finish', [TechnicianAppController::class, 'finishTask'], ['auth']);
Router::get('/technician/history', [TechnicianAppController::class, 'history'], ['auth']);
Router::get('/technician/profile', [TechnicianAppController::class, 'profile'], ['auth']);

// Service Catalog & Pricing (Modul 7)
Router::get('/service-catalog', [ServiceCatalogController::class, 'index'], ['auth']);
Router::get('/service-catalog/create', [ServiceCatalogController::class, 'create'], ['auth']);
Router::post('/service-catalog', [ServiceCatalogController::class, 'store'], ['auth']);
Router::get('/service-catalog/{id}/edit', [ServiceCatalogController::class, 'edit'], ['auth']);
Router::post('/service-catalog/{id}/edit', [ServiceCatalogController::class, 'update'], ['auth']);
Router::post('/service-catalog/{id}/toggle', [ServiceCatalogController::class, 'toggleActive'], ['auth']);

// Inventory & Spareparts (Modul 8)
Router::get('/inventory', [InventoryController::class, 'index'], ['auth']);
Router::get('/inventory/create', [InventoryController::class, 'create'], ['auth']);
Router::post('/inventory', [InventoryController::class, 'store'], ['auth']);
Router::get('/inventory/{id}', [InventoryController::class, 'show'], ['auth']);
Router::get('/inventory/{id}/edit', [InventoryController::class, 'edit'], ['auth']);
Router::post('/inventory/{id}/edit', [InventoryController::class, 'update'], ['auth']);
Router::post('/inventory/movement', [InventoryController::class, 'storeMovement'], ['auth']);
Router::get('/suppliers', [InventoryController::class, 'suppliersIndex'], ['auth']);
Router::post('/suppliers', [InventoryController::class, 'storeSupplier'], ['auth']);
Router::get('/purchases', [InventoryController::class, 'purchasesIndex'], ['auth']);
Router::get('/purchases/create', [InventoryController::class, 'createPurchase'], ['auth']);
Router::post('/purchases', [InventoryController::class, 'storePurchase'], ['auth']);
Router::get('/purchases/{id}', [InventoryController::class, 'showPurchase'], ['auth']);

// Cashier, Invoicing & Payments (Modul 9)
Router::get('/invoices', [BillingController::class, 'index'], ['auth']);
Router::get('/invoices/create', [BillingController::class, 'create'], ['auth']);
Router::post('/invoices', [BillingController::class, 'store'], ['auth']);
Router::get('/invoices/{id}', [BillingController::class, 'show'], ['auth']);
Router::get('/invoices/{id}/print', [BillingController::class, 'printInvoice'], ['auth']);
Router::post('/invoices/{id}/payment', [BillingController::class, 'recordPayment'], ['auth']);
Router::get('/payments', [BillingController::class, 'paymentsList'], ['auth']);

// Finance & Expenses (Modul 10)
Router::get('/finance', [FinanceController::class, 'index'], ['auth']);
Router::get('/expenses', [FinanceController::class, 'expensesIndex'], ['auth']);
Router::get('/expenses/create', [FinanceController::class, 'createExpense'], ['auth']);
Router::post('/expenses', [FinanceController::class, 'storeExpense'], ['auth']);
Router::post('/finance/transfer', [FinanceController::class, 'transferCash'], ['auth']);

// Maintenance Contracts (Modul 11)
Router::get('/contracts', [MaintenanceContractController::class, 'index'], ['auth']);
Router::get('/contracts/create', [MaintenanceContractController::class, 'create'], ['auth']);
Router::post('/contracts', [MaintenanceContractController::class, 'store'], ['auth']);
Router::get('/contracts/{id}', [MaintenanceContractController::class, 'show'], ['auth']);
Router::post('/contracts/{id}/generate-wo', [MaintenanceContractController::class, 'generateWorkOrder'], ['auth']);

// Customer Portal (Modul 12)
Router::get('/customer-portal/book', [CustomerPortalController::class, 'book'], ['auth']);
Router::post('/customer-portal/book', [CustomerPortalController::class, 'storeBooking'], ['auth']);
Router::get('/customer-portal/units', [CustomerPortalController::class, 'units'], ['auth']);
Router::post('/customer-portal/units', [CustomerPortalController::class, 'storeUnit'], ['auth']);
Router::get('/customer-portal/orders', [CustomerPortalController::class, 'orders'], ['auth']);
Router::get('/customer-portal/orders/{id}', [CustomerPortalController::class, 'showOrder'], ['auth']);
Router::get('/customer-portal/invoices', [CustomerPortalController::class, 'invoices'], ['auth']);
Router::get('/customer-portal/complaints', [CustomerPortalController::class, 'complaints'], ['auth']);
Router::post('/customer-portal/complaints', [CustomerPortalController::class, 'storeComplaint'], ['auth']);
Router::post('/customer-portal/orders/{id}/review', [CustomerPortalController::class, 'storeReview'], ['auth']);

// Reporting (Modul 14)
Router::get('/reports', [ReportController::class, 'index'], ['auth']);
Router::get('/reports/work-orders', [ReportController::class, 'workOrdersReport'], ['auth']);
Router::get('/reports/finance', [ReportController::class, 'financeReport'], ['auth']);
Router::get('/reports/technician', [ReportController::class, 'technicianReport'], ['auth']);
Router::get('/reports/inventory', [ReportController::class, 'inventoryReport'], ['auth']);

// Settings & Users (Modul 15 & Role 1)
Router::get('/settings', [SettingController::class, 'index'], ['auth', 'role:owner,admin']);
Router::post('/settings', [SettingController::class, 'update'], ['auth', 'role:owner,admin']);
Router::get('/users', [SettingController::class, 'usersIndex'], ['auth', 'role:owner,admin']);
Router::post('/users', [SettingController::class, 'storeUser'], ['auth', 'role:owner']);
Router::post('/users/{id}/toggle', [SettingController::class, 'toggleUserStatus'], ['auth', 'role:owner']);
Router::get('/settings/backup', [SettingController::class, 'backupDb'], ['auth', 'role:owner']);
Router::post('/settings/restore', [SettingController::class, 'restoreDb'], ['auth', 'role:owner']);
Router::get('/notifications', [SettingController::class, 'notificationsIndex'], ['auth']);
Router::post('/notifications/{id}/read', [SettingController::class, 'markNotificationRead'], ['auth']);
