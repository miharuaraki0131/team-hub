<?php

namespace App\Notifications;

use App\Models\Task; // Taskモデルを使用
use App\Models\User; // Userモデルも使用
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Auth; // Authファサードを使用

class TaskAssigned extends Notification implements ShouldQueue
{
    use Queueable;

    public Task $task;
    public User $assigner; // タスクを割り当てた人

    /**
     * Create a new notification instance.
     */
    public function __construct(Task $task)
    {
        $this->task = $task->loadMissing('project'); // 関連プロジェクトも読み込んでおく
        $this->assigner = Auth::user(); // タスクを割り当てたのは、現在のログインユーザー
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $projectName = $this->task->project->name;
        $url = route('projects.show', $this->task->project); // プロジェクトのWBS/ガント画面へのリンク

        return (new MailMessage)
                    ->subject("[TeamHub] {$this->assigner->name}さんがタスクを割り当てました")
                    ->greeting("{$notifiable->name}さん、新しいタスクが割り当てられました。")
                    ->line("プロジェクト: {$projectName}")
                    ->line("タスク名: {$this->task->title}")
                    ->line("担当者: {$notifiable->name} (あなた)")
                    ->line("割り当てた人: {$this->assigner->name}")
                    ->action('タスクを確認する', $url)
                    ->salutation('From TeamHub');
    }
}
