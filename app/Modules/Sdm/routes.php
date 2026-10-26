<?php

use App\Modules\Sdm\Http\Controllers\JobDescriptionController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])->prefix('sdm/jobdesk')->name('sdm.jobdesk.')->group(function () {
    Route::get('/', [JobDescriptionController::class, 'index'])->name('index');
    Route::get('/create', [JobDescriptionController::class, 'create'])->name('create');
    Route::post('/', [JobDescriptionController::class, 'store'])->name('store');
    Route::get('/{id}', [JobDescriptionController::class, 'show'])->name('show');
    Route::get('/{id}/edit', [JobDescriptionController::class, 'edit'])->name('edit');
    Route::put('/{id}', [JobDescriptionController::class, 'update'])->name('update');
    Route::post('/{id}/submit', [JobDescriptionController::class, 'submit'])->name('submit');
    Route::post('/{id}/approve', [JobDescriptionController::class, 'approve'])->name('approve');
    Route::get('/{id}/print', [JobDescriptionController::class, 'print'])->name('print');
});
