<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailMetric extends Model
{
    protected $fillable = [
        'user_id',
        'metric_date',
        'received_count',
        'sent_count',
        'after_hours_count',
        'avg_response_hours',
        'peak_hour',
        'hourly_distribution',
        'productivity_score',
    ];

    protected function casts(): array
    {
        return [
            'metric_date'         => 'date',
            'hourly_distribution' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}