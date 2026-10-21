<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])->prefix('platform')->name('platform.')->group(function () {
    // Platform management routes will be registered in Fase 6
});
