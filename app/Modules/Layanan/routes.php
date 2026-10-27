<?php

declare(strict_types=1);

use App\Modules\Layanan\Http\Controllers\WorkOrderController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])->prefix('layanan')->name('layanan.')->group(function () {
    Route::get('/work-orders', [WorkOrderController::class, 'index'])->name('work-orders.index');
    Route::get('/work-orders/create', [WorkOrderController::class, 'create'])->name('work-orders.create');
    Route::post('/work-orders', [WorkOrderController::class, 'store'])->name('work-orders.store');
    Route::get('/work-orders/{id}', [WorkOrderController::class, 'show'])->name('work-orders.show');
    Route::post('/work-orders/{id}/process', [WorkOrderController::class, 'process'])->name('work-orders.process');
    Route::get('/work-orders/{id}/print', [WorkOrderController::class, 'print'])->name('work-orders.print');
    Route::get('/work-orders/{id}/photo', [WorkOrderController::class, 'photo'])->name('work-orders.photo');
});
