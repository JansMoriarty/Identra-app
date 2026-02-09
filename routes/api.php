<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AdminGuruController;
use App\Http\Controllers\Api\AttendanceController;

Route::post('/login', [AuthController::class, 'login']);
Route::post('/guru/login', [AuthController::class, 'loginGuru']);

Route::middleware(['auth:sanctum'])->group(function () {

    Route::post('/logout', [AuthController::class, 'logout']);

    // CRUD Guru (hanya admin)
    Route::middleware(['role:admin'])->group(function () {
        Route::get('/admin/guru', [AdminGuruController::class, 'index']);
        Route::post('/admin/guru', [AdminGuruController::class, 'store']);
        Route::get('/admin/guru/{id}', [AdminGuruController::class, 'show']);
        Route::put('/admin/guru/{id}', [AdminGuruController::class, 'update']);
        Route::delete('/admin/guru/{id}', [AdminGuruController::class, 'destroy']);

        // Bulk import guru dari API
        Route::post('/admin/guru/bulk-import', [AdminGuruController::class, 'bulkImport']);

        // Route untuk List Riwayat (Index)
        Route::get('/attendance', [AttendanceController::class, 'index']);

        // Route untuk Input Absen Masuk/Izin/Sakit (Store)
        Route::post('/attendance/store', [AttendanceController::class, 'store']);

        // Route untuk Absen Pulang (Checkout)
        Route::post('/attendance/checkout', [AttendanceController::class, 'checkout']);
    });
});
