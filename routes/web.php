<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DeployController;

Route::get('/', [DeployController::class, 'index'])->name('dashboard');
Route::post('/servers', [DeployController::class, 'store'])->name('servers.store');
Route::get('/servers/{server}', [DeployController::class, 'show'])->name('servers.show');
Route::post('/servers/{server}/config', [DeployController::class, 'updateConfig'])->name('servers.config.update');
Route::post('/servers/{server}/trigger', [DeployController::class, 'trigger'])->name('servers.trigger');
Route::get('/servers/{server}/simulate', [DeployController::class, 'simulate'])->name('servers.simulate');
