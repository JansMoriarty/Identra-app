<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\DashboardController;

/*
|--------------------------------------------------------------------------
| PUBLIC ROUTES
|--------------------------------------------------------------------------
*/

// Login ADMIN
Route::middleware('guest')->group(function () {
    Route::get(
        '/signin',
        fn() =>
        view('pages.auth.signin', ['title' => 'Admin Sign In'])
    )->name('login');

    Route::post('/signin', [AuthController::class, 'login'])
        ->name('login.process');
});

// Logout
Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

/*
|--------------------------------------------------------------------------
| ADMIN ONLY (WEB DASHBOARD)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:admin'])->group(function () {

    Route::get('/', [DashboardController::class, 'index'])
        ->name('dashboard');

    Route::get(
        '/profile',
        fn() =>
        view('pages.profile', ['title' => 'Profile'])
    )->name('profile');

    // TailAdmin Pages
    Route::get(
        '/calendar',
        fn() =>
        view('pages.calender', ['title' => 'Calendar'])
    )->name('calendar');

    Route::get(
        '/form-elements',
        fn() =>
        view('pages.form.form-elements', ['title' => 'Form Elements'])
    )->name('form-elements');

    Route::get(
        '/blank',
        fn() =>
        view('pages.blank', ['title' => 'Face Recognize'])
    )->name('blank');

    Route::get('/manajemen-pengguna/guru', function () {
        return view('pages.manajemen_pengguna.guru.index', [
            'title' => 'Manajemen Guru'
        ]);
    })->name('guru.index');

    Route::get('/manajemen-pengguna/guru/create', function () {
        return view('pages.manajemen_pengguna.guru.create', [
            'title' => 'Tambah Guru'
        ]);
    })->name('guru.create');



    Route::get(
        '/line-chart',
        fn() =>
        view('pages.chart.line-chart', ['title' => 'Line Chart'])
    )->name('line-chart');

    Route::get(
        '/bar-chart',
        fn() =>
        view('pages.chart.bar-chart', ['title' => 'Bar Chart'])
    )->name('bar-chart');

    Route::prefix('ui')->group(function () {
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
Route::get(
    '/kiosk/face',
    fn() =>
    view('pages.kiosk.face-recognition', ['title' => 'Face Recognition'])
)->name('kiosk.face');
