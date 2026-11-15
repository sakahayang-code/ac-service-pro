<?php

declare(strict_types=1);

use App\Services\Router;
use App\Controllers\ApiController;

// API routes for mobile app & offline sync
Router::post('/api/sync/push', [ApiController::class, 'syncPush']);
Router::get('/api/sync/pull', [ApiController::class, 'syncPull']);
Router::get('/api/technician/my-tasks', [ApiController::class, 'technicianTasks']);
Router::post('/api/work-orders/{id}/status', [ApiController::class, 'updateOrderStatus']);
