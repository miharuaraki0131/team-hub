<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Project extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'created_by',
    ];

    /**
     * このプロジェクトに属するタスクを取得する (1対多)
     */
    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    /**
     * このプロジェクトを作成したユーザーを取得する (多対1)
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getGanttDataAttribute(): array
    {
        $parentTasks = $this->tasks()->parentTasks()->with('children.user', 'user')->get();

        return $parentTasks->map(function ($task) {
            $children = $task->children->map(function ($child) use ($task) {
                return [
                    'id' => $child->id,
                    'title' => $child->title,
                    'start' => $child->planned_start_date?->format('Y-m-d'),
                    'end' => $child->planned_end_date?->format('Y-m-d'),
                    'progress' => $child->progress_percentage,
                    'status' => $child->status,
                    'user' => $child->user?->name,
                    'is_child' => true,
                    'parent_id' => $task->id
                ];
            });

            return [
                'id' => $task->id,
                'title' => $task->title,
                'start' => $task->planned_start_date?->format('Y-m-d'),
                'end' => $task->planned_end_date?->format('Y-m-d'),
                'progress' => $task->progress_percentage,
                'status' => $task->status,
                'user' => $task->user?->name,
                'is_child' => false,
                'children' => $children
            ];
        })->values()->all(); // コレクションをプレーンな配列に変換
    }

    /**
     * [ここから追記]
     * プロジェクトの予定開始日を取得するアクセサ
     */
    public function getStartDateAttribute(): ?Carbon
    {
        $minDate = $this->tasks()->min('planned_start_date');
        return $minDate ? Carbon::parse($minDate) : null;
    }

    /**
     * プロジェクトの予定終了日を取得するアクセサ
     */
    public function getEndDateAttribute(): ?Carbon
    {
        $maxDate = $this->tasks()->max('planned_end_date');
        return $maxDate ? Carbon::parse($maxDate) : null;
    }

    /**
     * プロジェクトの実績開始日を取得するアクセサ
     */
    public function getActualStartDateAttribute(): ?Carbon
    {
        $minDate = $this->tasks()->min('actual_start_date');
        return $minDate ? Carbon::parse($minDate) : null;
    }

    /**
     * プロジェクトの実績終了日を取得するアクセサ
     */
    public function getActualEndDateAttribute(): ?Carbon
    {
        $maxDate = $this->tasks()->max('actual_end_date');
        return $maxDate ? Carbon::parse($maxDate) : null;
    }
}
