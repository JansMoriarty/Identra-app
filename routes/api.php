<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AdminGuruController;
use App\Http\Controllers\Api\AttendanceController;
use App\Http\Controllers\Api\FaceRecognitionController;
use App\Http\Controllers\Api\LeaveRequestController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Public Routes (Tanpa Login)
Route::post('/login', [AuthController::class, 'login']);
Route::post('/guru/login', [AuthController::class, 'loginGuru']);

Route::post('/attendance/store-face', [FaceRecognitionController::class, 'registerFace']);

Route::get('/face-profiles', [FaceRecognitionController::class, 'getAllFaceProfiles']);

// Mengambil detail nama guru berdasarkan ID setelah wajah cocok
Route::get('/guru-detail/{id}', [AdminGuruController::class, 'show']);

Route::post('/attendance/scan-face', [App\Http\Controllers\Api\AttendanceController::class, 'scanFace']);


// Protected Routes (Harus Login Sanctum)
Route::middleware(['auth:sanctum'])->group(function () {

    Route::post('/logout', [AuthController::class, 'logout']);

    /** * ROLE: GURU
     * Hanya guru yang bisa mendaftarkan wajah dan melakukan absensi
     */
    Route::middleware(['role:guru'])->group(function () {

        // Cek Kehadiran Hari Ini
        Route::get('/attendance/today/{guru_id}', [AttendanceController::class, 'getAttendanceToday']);

        // Verifikasi Wajah (Proses Absensi) - Kita akan buat method ini selanjutnya
        Route::post('/attendance/verify', [FaceRecognitionController::class, 'verifyFace']);

        Route::middleware('auth:sanctum')->post('/leave-request', [LeaveRequestController::class, 'store']);
    });


    /** * ROLE: ADMIN
     * Hanya admin yang bisa mengelola data master guru dan rekap absensi
     */
    Route::middleware(['role:admin'])->group(function () {

        // CRUD Guru
        Route::get('/admin/guru', [AdminGuruController::class, 'index']);
        Route::post('/admin/guru', [AdminGuruController::class, 'store']);
        Route::get('/admin/guru/{id}', [AdminGuruController::class, 'show']);
        Route::put('/admin/guru/{id}', [AdminGuruController::class, 'update']);
        Route::delete('/admin/guru/{id}', [AdminGuruController::class, 'destroy']);

        // Fitur Tambahan Admin
        Route::post('/admin/guru/bulk-import', [AdminGuruController::class, 'bulkImport']);
        Route::get('/attendance', [AttendanceController::class, 'index']);
        Route::post('/attendance/store', [AttendanceController::class, 'store']);
        Route::post('/attendance/checkout', [AttendanceController::class, 'checkout']);
    });
});
