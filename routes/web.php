<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DailyReportController;
use App\Http\Controllers\WeeklyReportController;
use App\Http\Controllers\WeeklyGoalController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KnowledgeController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\Admin\UserController;



// ログインページに遷移
Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/logout', function () {
    return redirect()->route('login');
});

Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // 週報 (Weekly Reports)
    Route::prefix('/weekly-reports')->name('weekly-reports.')->group(function () {
        // 週報の表示
        Route::get('/{user}/{year}/{week_number}', [WeeklyReportController::class, 'show'])->name('show');
    });

    // 日報 (Daily Reports)
    Route::prefix('/daily-reports')->name('daily-reports.')->group(function () {
        // 日報の編集画面
        Route::get('/{user}/{date}/edit', [DailyReportController::class, 'edit'])->name('edit');
        // 日報の保存・更新処理
        Route::post('/{user}/{date}', [DailyReportController::class, 'storeOrUpdate'])->name('storeOrUpdate');
    });

    // 週の目標 (Weekly Goals)
    Route::prefix('/weekly-goals')->name('weekly-goals.')->group(function () {
        // 週の目標の編集画面
        Route::get('/{user}/{year}/{week_number}/edit', [WeeklyGoalController::class, 'edit'])->name('edit');
        // 週の目標の保存・更新処理
        Route::post('/{user}/{year}/{week_number}', [WeeklyGoalController::class, 'storeOrUpdate'])->name('storeOrUpdate');
    });

    // 共有事項 (Knowledges)
    Route::resource('knowledges', KnowledgeController::class);

    // カレンダー表示 (Events)
    Route::get('/events/json', [EventController::class, 'getEvents'])->name('events.json');
    Route::resource('events', EventController::class);

    Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {

        // ユーザー管理のCRUDルートを、一行で定義
        Route::resource('users', UserController::class);

    });
});

require __DIR__ . '/auth.php';
