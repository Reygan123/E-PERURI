<?php

use App\Modules\Platform\Http\Controllers\AuditLogController;
use App\Modules\Platform\Http\Controllers\EmployeeController;
use App\Modules\Platform\Http\Controllers\NumberingFormatController;
use App\Modules\Platform\Http\Controllers\OrgUnitController;
use App\Modules\Platform\Http\Controllers\PositionController;
use App\Modules\Platform\Http\Controllers\UserRoleController;
use App\Modules\Platform\Http\Middleware\EnsurePlatformAdmin;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth', 'must.change.password', EnsurePlatformAdmin::class])->prefix('platform')->name('platform.')->group(function () {
    // 6.1 Unit Organisasi
    Route::get('/org-units', [OrgUnitController::class, 'index'])->name('org-units.index');
    Route::post('/org-units', [OrgUnitController::class, 'store'])->name('org-units.store');
    Route::put('/org-units/{orgUnit}', [OrgUnitController::class, 'update'])->name('org-units.update');
    Route::delete('/org-units/{orgUnit}', [OrgUnitController::class, 'destroy'])->name('org-units.destroy');

    // 6.2 Master Jabatan
    Route::get('/positions', [PositionController::class, 'index'])->name('positions.index');
    Route::post('/positions', [PositionController::class, 'store'])->name('positions.store');
    Route::put('/positions/{position}', [PositionController::class, 'update'])->name('positions.update');
    Route::delete('/positions/{position}', [PositionController::class, 'destroy'])->name('positions.destroy');

    // 6.3 Data Pegawai & Penugasan
    Route::get('/employees', [EmployeeController::class, 'index'])->name('employees.index');
    Route::post('/employees', [EmployeeController::class, 'store'])->name('employees.store');
    Route::get('/employees/{employee}', [EmployeeController::class, 'show'])->name('employees.show');
    Route::put('/employees/{employee}', [EmployeeController::class, 'update'])->name('employees.update');
    Route::post('/employees/{employee}/assign', [EmployeeController::class, 'assign'])->name('employees.assign');

    // 6.4 Pengguna & Peran Otorisasi
    Route::get('/users', [UserRoleController::class, 'index'])->name('users.index');
    Route::post('/users/{user}/roles', [UserRoleController::class, 'assignRole'])->name('users.roles.assign');
    Route::delete('/users/roles/{userRole}', [UserRoleController::class, 'revokeRole'])->name('users.roles.revoke');
    Route::post('/users/{user}/toggle-status', [UserRoleController::class, 'toggleStatus'])->name('users.toggle-status');
    Route::post('/users/{user}/reset-password', [UserRoleController::class, 'resetPassword'])->name('users.reset-password');

    // 6.5 Penomoran Dokumen
    Route::get('/numbering', [NumberingFormatController::class, 'index'])->name('numbering.index');
    Route::post('/numbering', [NumberingFormatController::class, 'store'])->name('numbering.store');
    Route::put('/numbering/{numberingFormat}', [NumberingFormatController::class, 'update'])->name('numbering.update');
    Route::post('/numbering/preview', [NumberingFormatController::class, 'preview'])->name('numbering.preview');

    // Audit Log Terpusat
    Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');
});
