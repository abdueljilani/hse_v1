<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HseReport extends Model
{
    protected $fillable = [
        'user_id',
        'report_number',
        'title',
        'report_type',
        'location',
        'description',
        'risk_level',
        'corrective_action',
        'status',
        'reported_at',
    ];

    protected function casts(): array
    {
        return [
            'reported_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
