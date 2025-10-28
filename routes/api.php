<?php

use App\Http\Controllers\CompanyController;
use Illuminate\Support\Facades\Route;

Route::middleware(['authed'])->group(function () {
    Route::prefix('companies')->group(function () {
        Route::get('/', [CompanyController::class, 'index']);
        Route::get('/activity/{activity}', [CompanyController::class, 'activityIndex']);
        Route::get('/nearest', [CompanyController::class, 'nearest']);
        Route::get('{company}', [CompanyController::class, 'show']);
    });
});
