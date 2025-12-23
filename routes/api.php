<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ServerController;
use App\Http\Controllers\DeployController;
use App\Http\Controllers\WorkflowController;
use App\Http\Controllers\RepositoryController;


Route::get('/servers', [ServerController::class, 'index']);
Route::post('/servers', [ServerController::class, 'store']);

Route::get('/servers/{server}/config', [ServerController::class, 'viewConfig']);
Route::post('/servers/{server}/config', [ServerController::class, 'updateConfig']);

Route::post('/servers/{server}/deploy', [DeployController::class, 'trigger']);

Route::post('/workflow/logs', [WorkflowController::class, 'storeWebhook']);
Route::get('/workflow/{run_id}', [WorkflowController::class, 'getLogs']);

// Repository Management Routes
Route::prefix('repositories')->group(function () {
    // Sync repository and detect changes
    Route::post('/sync', [RepositoryController::class, 'sync']);
    
    // Get repository structure
    Route::get('/{repoName}/structure', [RepositoryController::class, 'getStructure']);
    
    // Get changes
    Route::get('/{repoName}/changes', [RepositoryController::class, 'getChanges']);
    
    // Get notifications
    Route::get('/{repoName}/notifications', [RepositoryController::class, 'getNotifications']);
    
    // Get sync logs
    Route::get('/{repoName}/sync-logs', [RepositoryController::class, 'getSyncLogs']);
    
    // Get latest sync status
    Route::get('/{repoName}/latest-sync', [RepositoryController::class, 'getLatestSync']);
    
    // Mark notification as sent
    Route::patch('/notifications/{notificationId}/mark-sent', [RepositoryController::class, 'markNotificationSent']);
});

