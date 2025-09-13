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
use App\Http\Controllers\Admin\DivisionController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\NotificationController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// トップページはログインページへリダイレクト
Route::get('/', function () {
    return redirect()->route('login');
});

// ログアウト時のリダイレクト先
Route::get('/logout', function () {
    return redirect()->route('login');
});

// 認証必須エリア
Route::middleware('auth')->group(function () {

    // ダッシュボード
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // プロフィール管理
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // 週報 (Weekly Reports)
    Route::get('/weekly-reports/{user}/{year}/{week_number}', [WeeklyReportController::class, 'show'])
        ->name('weekly-reports.show');

    // 日報 (Daily Reports)
    Route::get('/daily-reports/{user}/{date}/edit', [DailyReportController::class, 'edit'])->name('daily-reports.edit');
    Route::post('/daily-reports/{user}/{date}', [DailyReportController::class, 'storeOrUpdate'])->name('daily-reports.storeOrUpdate');

    // 週の目標 (Weekly Goals)
    Route::get('/weekly-goals/{user}/{year}/{week_number}/edit', [WeeklyGoalController::class, 'edit'])->name('weekly-goals.edit');
    Route::post('/weekly-goals/{user}/{year}/{week_number}', [WeeklyGoalController::class, 'storeOrUpdate'])->name('weekly-goals.storeOrUpdate');

    // 共有事項 (Knowledges)
    Route::resource('knowledges', KnowledgeController::class);

    // カレンダー (Events)
    Route::get('/events/json', [EventController::class, 'getEvents'])->name('events.json');
    Route::resource('events', EventController::class);

    // プロジェクト (Projects)
    Route::resource('projects', ProjectController::class);

    // タスク管理 (Tasks) - プロジェクトにネストされたリソースとして定義
    Route::prefix('projects/{project}')->name('tasks.')->group(function () {
        // WBS/ガントチャート表示
        Route::get('/tasks', [TaskController::class, 'index'])->name('index');

        // API エンドポイント群
        Route::get('/gantt-data', [TaskController::class, 'getGanttData'])->name('ganttData');
        Route::get('/kanban-data', [TaskController::class, 'getAllTasksForKanban'])->name('kanbanData');
        Route::get('/summary', [TaskController::class, 'summary'])->name('summary');

        // タスクのCRUD操作（JSON API）
        Route::post('/tasks', [TaskController::class, 'store'])->name('store');
        Route::get('/tasks/{task}', [TaskController::class, 'show'])->name('show');
        Route::put('/tasks/{task}', [TaskController::class, 'update'])->name('update');
        Route::delete('/tasks/{task}', [TaskController::class, 'destroy'])->name('destroy');

        // その他の操作
        Route::put('/tasks-positions', [TaskController::class, 'updatePositions'])->name('updatePositions');
    });

    // 通知 (Notifications)
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        Route::get('/count', [NotificationController::class, 'count'])->name('count');
        Route::post('/mark-as-read', [NotificationController::class, 'markAsRead'])->name('markAsRead');
    });

    Route::get('/notifications-page', [NotificationController::class, 'showNotificationsPage'])->name('notifications.page');

    // 管理者専用エリア
    Route::prefix('admin')->name('admin.')->middleware('admin')->group(function () {
        Route::resource('users', UserController::class);
        Route::resource('divisions', DivisionController::class);
    });
});

// Laravel Breezeの認証ルート
require __DIR__ . '/auth.php';
