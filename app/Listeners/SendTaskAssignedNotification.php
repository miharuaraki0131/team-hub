<?php

namespace App\Listeners;

use App\Events\TaskAssigned;
use App\Models\Notification; // Notificationモデルを使えるようにする
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Auth; // ログインユーザー情報を取得するために追加

class SendTaskAssignedNotification
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     * @param  \App\Events\TaskAssigned  $event
     * @return void
     */
    public function handle(TaskAssigned $event): void
    {
        $task = $event->task;

        // タスクに担当者が割り当てられている場合のみ通知を作成
        if ($task->user_id) {
            Notification::create([
                'user_id' => $task->user_id, // 通知を受け取るのは、タスクの担当者
                'type'    => 'task_assigned', // 通知の種類
                'data'    => [
                    'task_id'        => $task->id,
                    'task_title'     => $task->title,
                    'project_id'     => $task->project_id,
                    'from_user_id'   => Auth::id(), // タスクを割り当てた人（現在のログインユーザー）
                    'from_user_name' => Auth::user()->name,
                ],
            ]);
        }
    }
}
