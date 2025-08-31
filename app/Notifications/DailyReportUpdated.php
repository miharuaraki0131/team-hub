<?php

namespace App\Notifications;

use App\Models\DailyReport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class DailyReportUpdated extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * The daily report instance.
     *
     * @var \App\Models\DailyReport
     */
    public DailyReport $report;

    /**
     * Create a new notification instance.
     */
    public function __construct(DailyReport $report)
    {
        $this->report = $report->loadMissing('user');
    }

    /**
     * Get the notification's delivery channels.
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
        $userName = $this->report->user->name;
        $reportDate = $this->report->report_date->isoFormat('Y年M月D日 (ddd)');

        $url = route('weekly-reports.show', [
            'user' => $this->report->user,
            'year' => $this->report->report_date->year,
            'week_number' => $this->report->report_date->weekOfYear,
        ]);

        return (new MailMessage)
            ->subject("[TeamHub] {$userName}さんが日報を更新しました ({$reportDate})")
            ->greeting("{$userName}さんが日報を更新しました。")
            ->line("日付: {$reportDate}")
            ->line("今日やったことの要約: ")
            ->line(Str::limit(strip_tags($this->report->summary_today), 150))
            ->action('日報を詳しく見る', $url)
            ->salutation('From TeamHub');
    }
}
