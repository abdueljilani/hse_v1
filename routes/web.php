<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HseReportController;
use App\Http\Controllers\JoinRequestController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StatusHistoryController;
use App\Http\Controllers\UserController;

// الصفحة الرئيسية
Route::get('/', function () {
    return redirect()->route('dashboard');
});

// البلاغات والتحليلات: تتطلب تسجيل الدخول
Route::middleware('auth')->group(function () {

    Route::get('/hse-reports', [HseReportController::class, 'index'])
        ->name('hse-reports.index');

    Route::get('/notifications/unread-reports', [HseReportController::class, 'unreadReportNotificationCount'])
        ->name('notifications.unread-reports');

    Route::get('/hse-reports/create', [HseReportController::class, 'create'])
        ->name('hse-reports.create');

    Route::post('/hse-reports', [HseReportController::class, 'store'])
        ->name('hse-reports.store');

    Route::get('/hse-reports/export/excel', [HseReportController::class, 'exportExcel'])
        ->name('hse-reports.export.excel');

    Route::get('/hse-reports/export/pdf/data', [HseReportController::class, 'exportPdfData'])
        ->name('hse-reports.export.pdf.data');

    Route::get('/hse-reports/export/pdf', [HseReportController::class, 'exportPdf'])
        ->name('hse-reports.export.pdf');

    Route::get('/hse-reports/{hseReport}/export/excel', [HseReportController::class, 'exportReportExcel'])
        ->name('hse-reports.report.export.excel');

    Route::get('/hse-reports/{hseReport}/export/pdf', [HseReportController::class, 'exportReportPdf'])
        ->name('hse-reports.report.export.pdf');

    Route::get('/hse-reports/{hseReport}/edit', [HseReportController::class, 'edit'])
        ->name('hse-reports.edit');

    Route::put('/hse-reports/{hseReport}', [HseReportController::class, 'update'])
        ->name('hse-reports.update');

    Route::delete('/hse-reports/{hseReport}', [HseReportController::class, 'destroy'])
        ->name('hse-reports.destroy');

    Route::get('/hse-reports/{hseReport}', [HseReportController::class, 'show'])
        ->name('hse-reports.show');

    Route::get('/analytics', [HseReportController::class, 'analytics'])
        ->name('analytics.index');

    // إدارة المستخدمين (Admin only)
    Route::resource('admin/users', UserController::class)
        ->except('show')
        ->names('admin.users');

    Route::post('admin/users/{user}/reset-password', [UserController::class, 'resetPassword'])
        ->name('admin.users.reset-password');

    Route::get('/admin/join-requests', [JoinRequestController::class, 'index'])
        ->name('admin.join-requests.index');
    Route::post('/admin/join-requests/{user}/approve', [JoinRequestController::class, 'approve'])
        ->name('admin.join-requests.approve');
    Route::post('/admin/join-requests/{user}/deny', [JoinRequestController::class, 'deny'])
        ->name('admin.join-requests.deny');

    Route::get('/admin/status-history', [StatusHistoryController::class, 'index'])
        ->name('admin.status-history.index');

    // الملف الشخصي
    Route::get('/profile', [ProfileController::class, 'edit'])
        ->name('profile.edit');

    Route::patch('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');

});

// تبديل اللغة
Route::get('/language/{locale}', [LanguageController::class, 'switch'])
    ->where('locale', 'en|ar')
    ->name('language.switch');

// لوحة التحكم
Route::get('/dashboard', [HseReportController::class, 'dashboard'])
    ->middleware(['auth', 'verified'])->name('dashboard');

// تسجيل الدخول والخروج
require __DIR__.'/auth.php';
