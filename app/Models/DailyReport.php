<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DailyReport extends Model
{
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
