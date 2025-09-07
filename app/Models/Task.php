<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Task extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'project_id',
        'user_id',
        'parent_id',
        'title',
        'description',
        'status',
        'planned_start_date',
        'planned_end_date',
        'actual_start_date',
        'actual_end_date',
        'planned_effort',
        'actual_effort',
        'position',
        'created_by',
    ];

    protected $casts = [
        'planned_start_date' => 'date',
        'planned_end_date' => 'date',
        'actual_start_date' => 'date',
        'actual_end_date' => 'date',
        'planned_effort' => 'decimal:2',
        'actual_effort' => 'decimal:2',
    ];

    /**
     * このタスクが属するプロジェクト
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * このタスクの担当者
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * このタスクの親タスク
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Task::class, 'parent_id');
    }

    /**
     * このタスクの子タスク
     */
    public function children(): HasMany
    {
        return $this->hasMany(Task::class, 'parent_id')->orderBy('position');
    }

    /**
     * このタスクを作成したユーザー
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * ステータスのラベルを取得
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'todo' => '未着手',
            'in_progress' => '進行中',
            'done' => '完了',
            default => '不明',
        };
    }

    /**
     * ステータスのCSSクラスを取得（UI表示用）
     */
    public function getStatusClassAttribute(): string
    {
        return match ($this->status) {
            'todo' => 'bg-gray-100 text-gray-800',
            'in_progress' => 'bg-yellow-100 text-yellow-800',
            'done' => 'bg-green-100 text-green-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    /**
     * 進捗率を計算（子タスクがある場合は子タスクの完了率から算出）
     */
    public function getProgressPercentageAttribute(): float
    {
        // 子タスクがある場合は、子タスクの完了状況から計算
        if ($this->children()->count() > 0) {
            $totalChildren = $this->children()->count();
            $completedChildren = $this->children()->where('status', 'done')->count();
            return $totalChildren > 0 ? round(($completedChildren / $totalChildren) * 100, 1) : 0;
        }

        // 子タスクがない場合は、自身のステータスから判断
        return match ($this->status) {
            'todo' => 0,
            'in_progress' => 50, // デフォルトで50%（実装では細かい進捗入力も可能にする）
            'done' => 100,
            default => 0,
        };
    }

    /**
     * タスクの予定日数を計算
     */
    public function getPlannedDurationAttribute(): ?int
    {
        if ($this->planned_start_date && $this->planned_end_date) {
            return $this->planned_start_date->diffInDays($this->planned_end_date) + 1;
        }
        return null;
    }

    /**
     * タスクの実際の日数を計算
     */
    public function getActualDurationAttribute(): ?int
    {
        if ($this->actual_start_date && $this->actual_end_date) {
            return $this->actual_start_date->diffInDays($this->actual_end_date) + 1;
        }
        return null;
    }

    /**
     * タスクが遅れているかチェック
     */
    public function getIsDelayedAttribute(): bool
    {
        if (!$this->planned_end_date || $this->status === 'done') {
            return false;
        }

        return Carbon::now()->gt($this->planned_end_date);
    }

    /**
     * タスクがクリティカルパス上にあるかチェック（簡易版）
     */
    public function getIsCriticalAttribute(): bool
    {
        // より高度な実装では、プロジェクト全体のクリティカルパス計算が必要
        // ここでは簡易的に、終了日が近く、進捗が遅れているタスクをクリティカルとする
        if (!$this->planned_end_date || $this->status === 'done') {
            return false;
        }

        $daysUntilDeadline = Carbon::now()->diffInDays($this->planned_end_date, false);
        $progressPercentage = $this->progress_percentage;

        // 期限まで3日以内で、進捗50%未満の場合はクリティカル
        return $daysUntilDeadline <= 3 && $progressPercentage < 50;
    }

    /**
     * WBS番号を生成（例：1.2.3）
     */
    public function getWbsNumberAttribute(): string
    {
        $numbers = [];
        $current = $this;

        // 親をたどって階層を取得
        while ($current) {
            if ($current->parent_id) {
                $siblings = Task::where('parent_id', $current->parent_id)
                    ->orderBy('position')
                    ->pluck('id');
                $position = $siblings->search($current->id) + 1;
            } else {
                $siblings = Task::where('project_id', $current->project_id)
                    ->whereNull('parent_id')
                    ->orderBy('position')
                    ->pluck('id');
                $position = $siblings->search($current->id) + 1;
            }

            array_unshift($numbers, $position);
            $current = $current->parent;
        }

        return implode('.', $numbers);
    }

    /**
     * スコープ：親タスクのみ取得
     */
    public function scopeParentTasks($query)
    {
        return $query->whereNull('parent_id');
    }

    /**
     * スコープ：子タスクのみ取得
     */
    public function scopeChildTasks($query)
    {
        return $query->whereNotNull('parent_id');
    }

    /**
     * スコープ：特定のステータスのタスクを取得
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * スコープ：特定の担当者のタスクを取得
     */
    public function scopeAssignedTo($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * スコープ：期限が近いタスクを取得
     */
    public function scopeUpcoming($query, $days = 7)
    {
        return $query->where('planned_end_date', '<=', Carbon::now()->addDays($days))
            ->where('status', '!=', 'done');
    }


}
