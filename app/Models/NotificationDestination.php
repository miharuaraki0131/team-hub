<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @property int $id
 * @property int $division_id
 * @property string $email
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Division $division
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotificationDestination newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotificationDestination newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotificationDestination query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotificationDestination whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotificationDestination whereDivisionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotificationDestination whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotificationDestination whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotificationDestination whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class NotificationDestination extends Model
{
    use HasFactory;
    protected $fillable = ['division_id', 'email'];


    /**
     * この通知先が所属する、親の部署を取得する
     */
    public function division()
    {
        return $this->belongsTo(Division::class);
    }
}
