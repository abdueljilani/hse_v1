<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HseReportStatusHistory extends Model
{
    protected $fillable = [
        'hse_report_id',
        'report_number',
        'old_status',
        'new_status',
        'changed_by',
        'changed_by_name',
    ];

    public function report()
    {
        return $this->belongsTo(HseReport::class, 'hse_report_id');
    }

    public function actor()
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
