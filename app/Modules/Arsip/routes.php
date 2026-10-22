<?php

declare(strict_types=1);

use App\Modules\Arsip\Http\Controllers\ApprovalController;
use App\Modules\Arsip\Http\Controllers\NaskahDinasController;
use App\Modules\Arsip\Http\Controllers\SpecialDocumentController;
use App\Modules\Arsip\Http\Controllers\SuratKeluarController;
use App\Modules\Arsip\Http\Controllers\SuratMasukController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth', 'must.change.password'])->prefix('arsip')->name('arsip.')->group(function () {
    // 1. Surat Keluar (Type Code: A)
    Route::get('surat-keluar', [SuratKeluarController::class, 'index'])->name('surat-keluar.index');
    Route::get('surat-keluar/create', [SuratKeluarController::class, 'create'])->name('surat-keluar.create');
    Route::post('surat-keluar', [SuratKeluarController::class, 'store'])->name('surat-keluar.store');
    Route::get('surat-keluar/{letter}', [SuratKeluarController::class, 'show'])->name('surat-keluar.show');
    Route::post('surat-keluar/{letter}/submit', [SuratKeluarController::class, 'submit'])->name('surat-keluar.submit');
    Route::post('surat-keluar/{letter}/upload-signed', [SuratKeluarController::class, 'uploadSigned'])->name('surat-keluar.upload-signed');

    // 2. Surat Masuk (Type Code: B)
    Route::get('surat-masuk', [SuratMasukController::class, 'index'])->name('surat-masuk.index');
    Route::get('surat-masuk/create', [SuratMasukController::class, 'create'])->name('surat-masuk.create');
    Route::post('surat-masuk', [SuratMasukController::class, 'store'])->name('surat-masuk.store');
    Route::get('surat-masuk/{letter}', [SuratMasukController::class, 'show'])->name('surat-masuk.show');
    Route::post('surat-masuk/{letter}/disposisi', [SuratMasukController::class, 'createDisposition'])->name('surat-masuk.disposisi.store');

    // 3. Persetujuan / Approval Workbench
    Route::get('persetujuan', [ApprovalController::class, 'index'])->name('persetujuan.index');
    Route::post('persetujuan/{approval}/decide', [ApprovalController::class, 'decide'])->name('persetujuan.decide');

    // 4. Naskah Dinas (ND, E, ST, PENG, KPTS)
    Route::get('naskah-dinas', [NaskahDinasController::class, 'index'])->name('naskah-dinas.index');
    Route::get('naskah-dinas/create', [NaskahDinasController::class, 'create'])->name('naskah-dinas.create');
    Route::post('naskah-dinas', [NaskahDinasController::class, 'store'])->name('naskah-dinas.store');
    Route::get('naskah-dinas/{letter}', [NaskahDinasController::class, 'show'])->name('naskah-dinas.show');

    // 5. Formulir Khusus (SPPD, SPB, BP)
    Route::get('khusus', [SpecialDocumentController::class, 'index'])->name('khusus.index');
    Route::get('khusus/create', [SpecialDocumentController::class, 'create'])->name('khusus.create');
    Route::post('khusus', [SpecialDocumentController::class, 'store'])->name('khusus.store');
    Route::get('khusus/{letter}', [SpecialDocumentController::class, 'show'])->name('khusus.show');
});
