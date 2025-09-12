<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property string $name
 * @property string|null $logo_path
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read string|null $logo_url
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\NotificationDestination> $notificationDestinations
 * @property-read int|null $notification_destinations_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $users
 * @property-read int|null $users_count
 * @method static \Database\Factories\DivisionFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Division newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Division newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Division onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Division query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Division whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Division whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Division whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Division whereLogoPath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Division whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Division whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Division withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Division withoutTrashed()
 * @mixin \Eloquent
 */
class Division extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'logo_path',
    ];


    /**
     * Users belonging to this division.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function notificationDestinations(): HasMany
    {
        return $this->hasMany(NotificationDestination::class);
    }

    public function getLogoUrlAttribute(): ?string
    {
        if ($this->logo_path) {
            return Storage::url($this->logo_path);
        }

        return asset('images/team-hub-logo.png');
    }

    /**
     * [追加] モデルのブートメソッド
     * モデルが初期化される際に、このメソッドが一度だけ呼ばれます。
     */
    protected static function boot()
    {
        parent::boot();

        // [追加] "deleting" (削除処理が開始される直前) のイベントを監視します。
        // 論理削除の場合も、このイベントは発火します。
        static::deleting(function ($division) {
            // この部署に関連する全ての通知先を、物理的に削除します。
            $division->notificationDestinations()->delete();
        });
    }
}
