<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::middleware('web', 'SetSessionData', 'auth', 'language', 'timezone', 'AdminSidebarMenu')->prefix('constructions')->group(function () {
    Route::get('/', [\Modules\Constructions\Http\Controllers\ConstructionsController::class, 'index']);
    
    // Projects routes
    Route::resource('projects', \Modules\Constructions\Http\Controllers\ProjectsController::class)->names([
        'index' => 'constructions.projects.index',
        'create' => 'constructions.projects.create',
        'store' => 'constructions.projects.store',
        'show' => 'constructions.projects.show',
        'edit' => 'constructions.projects.edit',
        'update' => 'constructions.projects.update',
        'destroy' => 'constructions.projects.destroy',
    ]);
    
    // Install routes
    Route::get('install', [\Modules\Constructions\Http\Controllers\InstallController::class, 'index']);
    Route::post('install', [\Modules\Constructions\Http\Controllers\InstallController::class, 'install']);
    Route::get('install/uninstall', [\Modules\Constructions\Http\Controllers\InstallController::class, 'uninstall']);
    Route::get('install/update', [\Modules\Constructions\Http\Controllers\InstallController::class, 'update']);

    // Work Certificates routes
    Route::resource('work-certificates', \Modules\Constructions\Http\Controllers\WorkCertificateController::class)->names([
        'index' => 'constructions.work-certificates.index',
        'create' => 'constructions.work-certificates.create',
        'store' => 'constructions.work-certificates.store',
        'show' => 'constructions.work-certificates.show',
        'edit' => 'constructions.work-certificates.edit',
        'update' => 'constructions.work-certificates.update',
        'destroy' => 'constructions.work-certificates.destroy',
    ]);
    Route::get('work-certificates/{id}/print', [\Modules\Constructions\Http\Controllers\WorkCertificateController::class, 'printCertificate'])->name('constructions.work-certificates.print');
    Route::get('work-certificates/update-status', [\Modules\Constructions\Http\Controllers\WorkCertificateController::class, 'getUpdateStatus']);
    Route::post('work-certificates/update-status', [\Modules\Constructions\Http\Controllers\WorkCertificateController::class, 'updateStatus'])->name('constructions.work-certificates.update-status');
});
