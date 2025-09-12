<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property int $user_id
 * @property string $title タイトル
 * @property string $body 内容
 * @property bool $is_pinned ピン留め
 * @property \Illuminate\Support\Carbon|null $published_at 公開日時
 * @property \Illuminate\Support\Carbon|null $expired_at 期限日時
 * @property string|null $category カテゴリー
 * @property int $view_count
 * @property string $priority
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Knowledge byPriority()
 * @method static \Database\Factories\KnowledgeFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Knowledge newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Knowledge newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Knowledge onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Knowledge pinned()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Knowledge published()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Knowledge query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Knowledge whereBody($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Knowledge whereCategory($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Knowledge whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Knowledge whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Knowledge whereExpiredAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Knowledge whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Knowledge whereIsPinned($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Knowledge wherePriority($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Knowledge wherePublishedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Knowledge whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Knowledge whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Knowledge whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Knowledge whereViewCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Knowledge withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Knowledge withoutTrashed()
 * @mixin \Eloquent
 */
class Knowledge extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'knowledges';

    protected $fillable = [
        'user_id',
        'title',
        'body',
        'is_pinned',
        'published_at',
        'expired_at',
        // 将来の拡張用
        'category',
        'view_count',
        'tags',
        'priority',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'expired_at' => 'datetime',
        'is_pinned' => 'boolean',
        'tags' => 'array', // JSON配列として扱う
    ];

    /**
     * 投稿者との関係
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }


    /**
     * スコープ: 公開中の記事のみを取得
     */
    public function scopePublished($query)
    {
        $now = Carbon::now();

        return $query->where(function ($q) use ($now) {
            $q->whereNull('published_at')
                ->orWhere('published_at', '<=', $now);
        })
            ->where(function ($q) use ($now) {
                $q->whereNull('expired_at')
                    ->orWhere('expired_at', '>', $now);
            });
    }

    /**
     * スコープ: 固定表示の記事を取得
     */
    public function scopePinned($query)
    {
        return $query->where('is_pinned', true);
    }

    /**
     * スコープ: 重要度でソート
     */
    public function scopeByPriority($query)
    {
        return $query->orderByRaw("
            CASE priority
                WHEN 'high' THEN 1
                WHEN 'normal' THEN 2
                WHEN 'low' THEN 3
                ELSE 4
            END
        ");
    }

    /**
     * 公開状態の判定
     */
    public function isPublished(): bool
    {
        $now = Carbon::now();

        $publishedCheck = is_null($this->published_at) || $this->published_at <= $now;
        $expiredCheck = is_null($this->expired_at) || $this->expired_at > $now;

        return $publishedCheck && $expiredCheck;
    }

    /**
     * 閲覧数を増やす
     */
    public function incrementViewCount(): void
    {
        $this->increment('view_count');
    }

    /**
     * 本文の要約を取得する。
     * 指定した文字数より短い場合は、"..." を付けない。
     *
     * @param int $length
     * @return string
     */
    public function getExcerpt(int $length = 100): string
    {
        // strip_tags()でHTMLタグを取り除いた後、Str::limit()で要約を作成
        return Str::limit(strip_tags($this->body), $length);
    }
}
