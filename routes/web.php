<?php

// use App\Http\Controllers\ProfileController; // Removed Breeze ProfileController
use App\Http\Controllers\DeployController;
use App\Http\Controllers\RepositoryWebController;
use Illuminate\Support\Facades\Route;


// Replaced default dashboard route with DeployController@index
Route::get('/', [DeployController::class, 'index'])
    ->middleware(['auth']) // Removed verified
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    // Profile Management - logic removed as it was Breeze specific. 
    // UI doesn't provide profile mgmt by default.



    Route::post('/servers', [DeployController::class, 'store'])->name('servers.store');
    Route::put('/servers/{server}', [DeployController::class, 'update'])->name('servers.update');
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

});

Auth::routes();


Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
