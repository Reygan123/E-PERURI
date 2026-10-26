<?php

declare(strict_types=1);

use App\Modules\Arsip\Http\Controllers\ApprovalController;
use App\Modules\Arsip\Http\Controllers\LetterPreviewController;
use App\Modules\Arsip\Http\Controllers\NaskahDinasController;
use App\Modules\Arsip\Http\Controllers\PublicVerificationController;
use App\Modules\Arsip\Http\Controllers\SpecialDocumentController;
use App\Modules\Arsip\Http\Controllers\SuratKeluarController;
use App\Modules\Arsip\Http\Controllers\SuratMasukController;
use Illuminate\Support\Facades\Route;

// Public Document Verification Portal (BSrE BSSN TTE)
Route::middleware(['web', 'throttle:60,1'])->group(function () {
    Route::get('verify/{hash}', [PublicVerificationController::class, 'verify'])->name('document.verify');
    Route::get('arsip/verify/{hash}', [PublicVerificationController::class, 'verify'])->name('document.verify.alias');
});

Route::middleware(['web', 'auth', 'must.change.password'])->prefix('arsip')->name('arsip.')->group(function () {
    // 1. Surat Keluar (Type Code: A)
    Route::get('surat-keluar', [SuratKeluarController::class, 'index'])->name('surat-keluar.index');
    Route::get('surat-keluar/create', [SuratKeluarController::class, 'create'])->name('surat-keluar.create');
    Route::post('surat-keluar', [SuratKeluarController::class, 'store'])->name('surat-keluar.store');
    Route::get('surat-keluar/{letter}', [SuratKeluarController::class, 'show'])->name('surat-keluar.show');
    Route::get('surat-keluar/{letter}/edit', [SuratKeluarController::class, 'edit'])->name('surat-keluar.edit');
    Route::put('surat-keluar/{letter}', [SuratKeluarController::class, 'update'])->name('surat-keluar.update');
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
    Route::get('naskah-dinas/{letter}/edit', [NaskahDinasController::class, 'edit'])->name('naskah-dinas.edit');
    Route::put('naskah-dinas/{letter}', [NaskahDinasController::class, 'update'])->name('naskah-dinas.update');
    Route::post('naskah-dinas/{letter}/submit', [NaskahDinasController::class, 'submit'])->name('naskah-dinas.submit');

    // 5. Formulir Khusus (SPPD, SPB, BP)
    Route::get('khusus', [SpecialDocumentController::class, 'index'])->name('khusus.index');
    Route::get('khusus/create', [SpecialDocumentController::class, 'create'])->name('khusus.create');
    Route::post('khusus', [SpecialDocumentController::class, 'store'])->name('khusus.store');
    Route::get('khusus/{letter}', [SpecialDocumentController::class, 'show'])->name('khusus.show');
    Route::get('khusus/{letter}/edit', [SpecialDocumentController::class, 'edit'])->name('khusus.edit');
    Route::put('khusus/{letter}', [SpecialDocumentController::class, 'update'])->name('khusus.update');
    Route::post('khusus/{letter}/submit', [SpecialDocumentController::class, 'submit'])->name('khusus.submit');

    // 6. Letter Preview & PDF Streams
    Route::get('letters/{letter}/preview', [LetterPreviewController::class, 'preview'])->name('letters.preview');
    Route::get('letters/{letter}/preview-html', [LetterPreviewController::class, 'previewHtml'])->name('letters.preview-html');
});
