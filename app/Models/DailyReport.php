<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
