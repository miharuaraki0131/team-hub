<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    use HasFactory, SoftDeletes;

    // fillableの設定
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

    /**
     * このタスクが属するプロジェクトを取得する (多対1)
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * このタスクの担当ユーザーを取得する (多対1)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * このタスクの親タスクを取得する (多対1)
     * 自己参照リレーション
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Task::class, 'parent_id');
    }

    /**
     * このタスクの子タスク一覧を取得する (1対多)
     * 自己参照リレーション
     */
    public function children(): HasMany
    {
        return $this->hasMany(Task::class, 'parent_id');
    }

    /**
     * このタスクを作成したユーザーを取得する (多対1)
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
