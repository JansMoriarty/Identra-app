<?php

use App\Http\Controllers\AttendanceWebController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PositionController;
use App\Http\Controllers\GuruPositionController;
use App\Http\Controllers\LeaveRequestWebController;
use App\Http\Controllers\AttendanceRuleController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\Api\AdminGuruController;
use App\Http\Controllers\ReportClassController;
use App\Http\Controllers\AssessmentCategoryWebController;
use App\Http\Controllers\AssessmentWebController;
use App\Http\Controllers\AssessmentPeriodWebController;
use App\Http\Controllers\AssessmentReportController;



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


    // Route untuk Master Jabatan (yang kita buat sebelumnya)
    Route::resource('positions', PositionController::class);

    // Route untuk Plotting Jabatan Guru
    // Kita gunakan nama 'guru-positions' agar URL-nya enak dibaca: /guru-positions
    Route::resource('guru-positions', GuruPositionController::class);

    Route::get('/attendance', [AttendanceWebController::class, 'index'])->name('admin.attendance.index');

    Route::get('/leave', [LeaveRequestWebController::class, 'index'])->name('leave.index');
    Route::patch('/leave/{id}/status', [LeaveRequestWebController::class, 'updateStatus'])->name('leave.status');

    Route::get('/attendance-rules', [AttendanceRuleController::class, 'index'])->name('attendance-rules.index');
    Route::put('/attendance-rules/update', [AttendanceRuleController::class, 'update'])->name('attendance-rules.update');

    Route::get('/report/personal/{guru_id}', [ReportController::class, 'downloadPersonalReport'])->name('report.personal');
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    // Pastikan name rutenya sesuai dengan yang dipanggil di JavaScript: reports.allPdf
    Route::get('/report/all-pdf', [ReportController::class, 'downloadAllReport'])->name('reports.allPdf');
    Route::get('/report/all-excel', [ReportController::class, 'allExcel'])->name('reports.allExcel');

    Route::resource('locations', LocationController::class);

    Route::get('/admin/guru/{id}/register-face', [AdminGuruController::class, 'registerFaceView'])->name('admin.guru.register-face');

    Route::get('/attendance-settings', [AttendanceRuleController::class, 'getSettings'])->name('attendance-settings');

    Route::get('/subjects', function () {
        // Sesuaikan dengan folder: page > subjects > index
        return view('pages.subjects.index');
    })->name('admin.subjects.index');

    Route::get('/classrooms', function () {
        return view('pages.classrooms.index'); // Sesuaikan dengan lokasi file blade kamu
    })->name('classrooms.index');

    Route::get('/schedules', function () {
        return view('pages.schedules.index'); // Sesuaikan dengan lokasi file blade kamu
    })->name('schedules.index');

    // Route untuk menampilkan halaman index (Tabel Alpine.js)
    Route::get('/assessment-categories', [AssessmentCategoryWebController::class, 'index'])->name('admin.categories.index');
    // Route untuk menyimpan kategori baru (dari Modal Tambah)
    Route::post('/assessment-categories', [AssessmentCategoryWebController::class, 'store'])->name('admin.categories.store');
    // Route untuk menampilkan halaman edit
    Route::get('/assessment-categories/{id}/edit', [AssessmentCategoryWebController::class, 'edit'])->name('admin.categories.edit');
    // Route untuk memproses update data
    Route::put('/assessment-categories/{id}', [AssessmentCategoryWebController::class, 'update'])->name('admin.categories.update');
    // Route untuk menghapus data (digunakan oleh confirmDelete di Alpine.js)
    Route::delete('/assessment-categories/{id}', [AssessmentCategoryWebController::class, 'destroy'])->name('admin.categories.destroy');

    Route::get('/report-class', [ReportClassController::class, 'index'])->name('report-class.index');


    Route::resource('assessment-periods', AssessmentPeriodWebController::class)->names([
        'index' => 'admin.periods.index',
        'store' => 'admin.periods.store',
        'update' => 'admin.periods.update',
        'destroy' => 'admin.periods.destroy',
    ]);

    Route::get('/assessments', [AssessmentWebController::class, 'index'])->name('admin.assessments.index');
    Route::post('/assessments', [AssessmentWebController::class, 'store'])->name('admin.assessments.store');

    // Mengubah nama route agar sesuai dengan pemanggilan di View
    Route::get('/report-assessments', [AssessmentReportController::class, 'index'])
        ->name('report-assessments.index');

    Route::get('/report-assessments/{id}', [AssessmentReportController::class, 'show'])
        ->name('report-assessments.show');

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
Route::get('/kiosk/face-recognition', fn() => view('pages.attendances.face', ['title' => 'Face Recognize']))->name('kiosk.face');
// 2. Halaman Absensi Manual
Route::get('/kiosk/manual', [AttendanceWebController::class, 'manual'])->name('attendances.manual');

Route::post('/attendances/store', [AttendanceWebController::class, 'store'])->name('attendances.store');
