<?php
// ════════════════════════════════════════════════════════════════
// app/Models/CalendarMetric.php
// ════════════════════════════════════════════════════════════════
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CalendarMetric extends Model
{
    protected $fillable = [
        'user_id', 'metric_date',
        'total_meetings', 'meeting_minutes',
        'focus_time_minutes', 'back_to_back_count',
        'after_hours_meetings', 'meeting_slots',
    ];

    protected function casts(): array
    {
        return [
            'metric_date'   => 'date',
            'meeting_slots' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}