<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DeployController;
use App\Http\Controllers\RepositoryWebController;

Route::get('/', [DeployController::class, 'index'])->name('dashboard');
Route::post('/servers', [DeployController::class, 'store'])->name('servers.store');
Route::get('/servers/{server}', [DeployController::class, 'show'])->name('servers.show');
Route::post('/servers/{server}/config', [DeployController::class, 'updateConfig'])->name('servers.config.update');
Route::post('/servers/{server}/trigger', [DeployController::class, 'trigger'])->name('servers.trigger');
Route::get('/servers/{server}/simulate', [DeployController::class, 'simulate'])->name('servers.simulate');

// Repository Monitoring Routes
Route::prefix('repositories')->name('repositories.')->group(function () {
    Route::get('/', [RepositoryWebController::class, 'index'])->name('index');
    Route::post('/sync', [RepositoryWebController::class, 'sync'])->name('sync');
    Route::get('/{repoName}', [RepositoryWebController::class, 'show'])->name('show');
    Route::get('/{repoName}/structure', [RepositoryWebController::class, 'structure'])->name('structure');
    Route::get('/{repoName}/changes', [RepositoryWebController::class, 'changes'])->name('changes');
    Route::get('/{repoName}/notifications', [RepositoryWebController::class, 'notifications'])->name('notifications');
    Route::get('/{repoName}/logs', [RepositoryWebController::class, 'logs'])->name('logs');
});
