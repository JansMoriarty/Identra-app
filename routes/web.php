<?php

use App\Http\Controllers\AttendanceWebController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PositionController;
use App\Http\Controllers\GuruPositionController;
use App\Models\User;

/*
|--------------------------------------------------------------------------
| PUBLIC & AUTH ROUTES
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {
    Route::get('/signin', fn() => view('pages.auth.signin', ['title' => 'Admin Sign In']))->name('login');
    Route::post('/signin', [AuthController::class, 'login'])->name('login.process');
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

/*
|--------------------------------------------------------------------------
| ADMIN ONLY (WEB DASHBOARD)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:admin'])->group(function () {

    // Main Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/profile', fn() => view('pages.profile', ['title' => 'Profile']))->name('profile');

    // Master Data - Positions (Menggunakan Resource agar otomatis create, index, dll terdaftar)
    Route::resource('positions', PositionController::class);

    // Manajemen Pengguna (Guru)
    Route::prefix('manajemen-pengguna')->group(function () {
        Route::get('/guru', fn() => view('pages.manajemen_pengguna.guru.index', ['title' => 'Manajemen Guru']))->name('guru.index');
        Route::get('/guru/create', fn() => view('pages.manajemen_pengguna.guru.create', ['title' => 'Tambah Guru']))->name('guru.create');
        Route::get('/guru/{guru_id}/edit', function ($guru_id) {
            $exists = User::where('role', 'guru')->where('guru_id', $guru_id)->exists();
            return view('pages.manajemen_pengguna.guru.edit', [
                'title' => 'Edit Guru',
                'guru_id' => $exists ? $guru_id : null
            ]);
        })->name('guru.edit');
    });

    // TailAdmin Pages & UI Elements
    Route::get('/calendar', fn() => view('pages.calender', ['title' => 'Calendar']))->name('calendar');
    Route::get('/form-elements', fn() => view('pages.form.form-elements', ['title' => 'Form Elements']))->name('form-elements');
    Route::get('/blank', fn() => view('pages.blank', ['title' => 'Face Recognize']))->name('blank');

    // Route untuk Master Jabatan (yang kita buat sebelumnya)
    Route::resource('positions', PositionController::class);

    // Route untuk Plotting Jabatan Guru
    // Kita gunakan nama 'guru-positions' agar URL-nya enak dibaca: /guru-positions
    Route::resource('guru-positions', GuruPositionController::class);

    Route::get('/attendance', [AttendanceWebController::class, 'index'])->name('admin.attendance.index');

    // Charts
    Route::get('/line-chart', fn() => view('pages.chart.line-chart', ['title' => 'Line Chart']))->name('line-chart');
    Route::get('/bar-chart', fn() => view('pages.chart.bar-chart', ['title' => 'Bar Chart']))->name('bar-chart');

    // UI Kit Prefix
    Route::prefix('ui')->name('ui.')->group(function () {
        Route::get('/alerts', fn() => view('pages.ui-elements.alerts', ['title' => 'Alerts']))->name('alerts');
        Route::get('/avatars', fn() => view('pages.ui-elements.avatars', ['title' => 'Avatars']))->name('avatars');
        Route::get('/badge', fn() => view('pages.ui-elements.badges', ['title' => 'Badges']))->name('badges');
        Route::get('/buttons', fn() => view('pages.ui-elements.buttons', ['title' => 'Buttons']))->name('buttons');
        Route::get('/image', fn() => view('pages.ui-elements.images', ['title' => 'Images']))->name('images');
        Route::get('/videos', fn() => view('pages.ui-elements.videos', ['title' => 'Videos']))->name('videos');
    });
});

/*
|--------------------------------------------------------------------------
| KIOSK (NO LOGIN WEB)
|--------------------------------------------------------------------------
*/
Route::get('/kiosk/face', fn() => view('pages.kiosk.face-recognition', ['title' => 'Face Recognition']))->name('kiosk.face');
