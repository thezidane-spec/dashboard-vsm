<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WeeklyReportPhoto extends Model
{
    protected $guarded = [];

    public function report()
    {
        return $this->belongsTo(WeeklyReport::class, 'weekly_report_id');
    }
}