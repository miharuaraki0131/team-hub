<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $user_id
 * @property \Illuminate\Support\Carbon $report_date 報告日
 * @property string|null $summary_today 今日やったこと
 * @property string|null $discrepancy 目標との差異
 * @property string|null $summary_tomorrow 明日やること
 * @property string|null $issues_thoughts 困っていることや感想
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $deleted_at
 * @method static \Database\Factories\DailyReportFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DailyReport newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DailyReport newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DailyReport query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DailyReport whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DailyReport whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DailyReport whereDiscrepancy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DailyReport whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DailyReport whereIssuesThoughts($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DailyReport whereReportDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DailyReport whereSummaryToday($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DailyReport whereSummaryTomorrow($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DailyReport whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DailyReport whereUserId($value)
 * @mixin \Eloquent
 */
class DailyReport extends Model
{
    use HasFactory;
     protected $fillable = [
        'user_id',
        'report_date',
        'summary_today',
        'discrepancy',
        'summary_tomorrow',
        'issues_thoughts',
    ];

     protected $casts = [
        'report_date' => 'date',
    ];
}
