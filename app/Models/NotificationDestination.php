<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

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
