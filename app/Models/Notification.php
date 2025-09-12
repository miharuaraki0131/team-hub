<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo; // ★追加

/**
 * @property int $id
 * @property int $user_id
 * @property string $type
 * @property array<array-key, mixed> $data
 * @property \Illuminate\Support\Carbon|null $read_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification whereData($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification whereReadAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification whereUserId($value)
 * @mixin \Eloquent
 */
class Notification extends Model
{
    use HasFactory;

    /**
     * マスアサインメント（一括代入）可能な属性
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'type',
        'data',
        'read_at',
    ];

    /**
     * ネイティブな型へのキャスト属性
     *
     * @var array<string, string>
     */
    protected $casts = [
        'data' => 'array',       // JSONカラムを配列として扱えるようにする
        'read_at' => 'datetime', // read_atをCarbonインスタンスとして扱えるようにする
    ];


    /**
     * この通知が属するユーザーを取得するリレーション (多対1)
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): BelongsTo
    {
        // Notificationモデルは、Userモデルに「属している(belongsTo)」
        return $this->belongsTo(User::class);
    }
}
