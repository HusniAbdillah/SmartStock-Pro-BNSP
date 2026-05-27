<?php

use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\ServerResourceController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::get('/server-resources', [ServerResourceController::class, 'index'])
         ->name('api.server-resources');

    Route::get('/health', [ServerResourceController::class, 'health'])
         ->name('api.health');

    Route::get('/notifications/unread', [NotificationController::class, 'unread'])
         ->name('api.notifications.unread');

    Route::patch('/notifications/{id}/read', [NotificationController::class, 'markRead'])
         ->name('api.notifications.mark-read');

    Route::patch('/notifications/read-all', [NotificationController::class, 'markAllRead'])
         ->name('api.notifications.read-all');
});
