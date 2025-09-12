<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property string $title
 * @property string|null $body
 * @property \Illuminate\Support\Carbon $start_datetime
 * @property \Illuminate\Support\Carbon $end_datetime
 * @property bool $is_all_day
 * @property string|null $category
 * @property string $visibility
 * @property string $color
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \App\Models\User $user
 * @method static \Database\Factories\EventFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event inMonth(int $year, int $month)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event public()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event visibleTo($userId = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event whereBody($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event whereCategory($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event whereColor($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event whereEndDatetime($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event whereIsAllDay($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event whereStartDatetime($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event whereVisibility($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event withoutTrashed()
 * @mixin \Eloquent
 */
class Event extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'title',
        'body',
        'start_datetime',
        'end_datetime',
        'is_all_day',
        'category',
        'visibility',
        'color',
    ];

    protected $casts = [
        'start_datetime' => 'datetime',
        'end_datetime' => 'datetime',
        'is_all_day' => 'boolean',
    ];

    /**
     * 投稿者との関係
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * スコープ: 公開イベントのみ
     */
    public function scopePublic($query)
    {
        return $query->where('visibility', 'public');
    }

    /**
     * スコープ: 指定された月のイベントを取得
     */
    public function scopeInMonth($query, int $year, int $month)
    {
        $startOfMonth = Carbon::create($year, $month, 1)->startOfMonth();
        $endOfMonth = $startOfMonth->copy()->endOfMonth();

        return $query->where(function ($q) use ($startOfMonth, $endOfMonth) {
            $q->whereBetween('start_datetime', [$startOfMonth, $endOfMonth])
              ->orWhereBetween('end_datetime', [$startOfMonth, $endOfMonth])
              ->orWhere(function ($qq) use ($startOfMonth, $endOfMonth) {
                  $qq->where('start_datetime', '<=', $startOfMonth)
                     ->where('end_datetime', '>=', $endOfMonth);
              });
        });
    }

    /**
     * スコープ: 指定されたユーザーが見れるイベントのみ
     */
    public function scopeVisibleTo($query, $userId = null)
    {
        return $query->where(function ($q) use ($userId) {
            $q->where('visibility', 'public');
            if ($userId) {
                $q->orWhere('user_id', $userId);
            }
        });
    }

    /**
     * FullCalendar用のJSONフォーマット
     */
    public function toFullCalendarArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'start' => $this->is_all_day ? $this->start_datetime->format('Y-m-d') : $this->start_datetime->toISOString(),
            'end' => $this->is_all_day ? $this->end_datetime->format('Y-m-d') : $this->end_datetime->toISOString(),
            'allDay' => $this->is_all_day,
            'backgroundColor' => $this->color,
            'borderColor' => $this->color,
            'extendedProps' => [
                'user_name' => $this->user->name,
                'category' => $this->category,
                'body' => $this->body,
                'visibility' => $this->visibility,
            ],
        ];
    }

    /**
     * 期間を人間が読みやすい形式で取得
     */
    public function getFormattedDuration(): string
    {
        if ($this->is_all_day) {
            if ($this->start_datetime->format('Y-m-d') === $this->end_datetime->format('Y-m-d')) {
                return $this->start_datetime->format('Y/m/d') . ' (終日)';
            } else {
                return $this->start_datetime->format('Y/m/d') . ' ～ ' . $this->end_datetime->format('Y/m/d') . ' (終日)';
            }
        } else {
            return $this->start_datetime->format('Y/m/d H:i') . ' ～ ' . $this->end_datetime->format('H:i');
        }
    }

    /**
     * イベントが現在進行中かどうか
     */
    public function isOngoing(): bool
    {
        $now = now();
        return $this->start_datetime <= $now && $this->end_datetime >= $now;
    }

    /**
     * イベントが過去のものかどうか
     */
    public function isPast(): bool
    {
        return $this->end_datetime < now();
    }
}
