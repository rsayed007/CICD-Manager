<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ServerController;
use App\Http\Controllers\DeployController;
use App\Http\Controllers\WorkflowController;


Route::get('/servers', [ServerController::class, 'index']);
Route::post('/servers', [ServerController::class, 'store']);

Route::get('/servers/{server}/config', [ServerController::class, 'viewConfig']);
Route::post('/servers/{server}/config', [ServerController::class, 'updateConfig']);

Route::post('/servers/{server}/deploy', [DeployController::class, 'trigger']);

Route::post('/workflow/logs', [WorkflowController::class, 'storeWebhook']);
Route::get('/workflow/{run_id}', [WorkflowController::class, 'getLogs']);
