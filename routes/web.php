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

    //WBS関連
    Route::resource('projects', ProjectController::class);
    Route::resource('tasks', TaskController::class);

    // タスク管理のルート（プロジェクトのネストリソース）
    Route::prefix('projects/{project}')->group(function () {

        // WBS/ガントチャート表示
        Route::get('/tasks', [TaskController::class, 'index'])->name('tasks.index');

        // タスクのCRUD操作（JSON API）
        Route::post('/tasks', [TaskController::class, 'store'])->name('tasks.store');
        Route::get('/tasks/{task}', [TaskController::class, 'show'])->name('tasks.show');
        Route::put('/tasks/{task}', [TaskController::class, 'update'])->name('tasks.update');
        Route::delete('/tasks/{task}', [TaskController::class, 'destroy'])->name('tasks.destroy');

        // タスクの並び順一括更新
        Route::put('/tasks-positions', [TaskController::class, 'updatePositions'])->name('tasks.updatePositions');

        // プロジェクト進捗サマリー取得
        Route::get('/summary', [TaskController::class, 'getProjectSummary'])->name('tasks.summary');
    });


    // API用のルート（必要に応じて）
    Route::prefix('api')->middleware(['auth'])->group(function () {

        // ガントチャート用のJSONデータ取得
        Route::get('/projects/{project}/gantt-data', [TaskController::class, 'getGanttData'])->name('api.gantt.data');

        // タスクの一括更新（ガントチャートでのドラッグ&ドロップ対応）
        Route::put('/projects/{project}/tasks/bulk-update', [TaskController::class, 'bulkUpdate'])->name('api.tasks.bulkUpdate');


        // 管理者用ルート (Admin Routes)
        Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
            Route::resource('users', UserController::class);
            Route::resource('divisions', DivisionController::class);
        });
    });
});

require __DIR__ . '/auth.php';
