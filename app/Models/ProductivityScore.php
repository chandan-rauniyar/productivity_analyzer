<?php
// ════════════════════════════════════════════════════════════════
// app/Models/ProductivityScore.php
// ════════════════════════════════════════════════════════════════
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductivityScore extends Model
{
    protected $fillable = [
        'user_id', 'week_start', 'score',
        'email_score', 'calendar_score', 'balance_score',
        'insights', 'best_day', 'worst_day',
    ];

    protected function casts(): array
    {
        return [
            'week_start' => 'date',
            'insights'   => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function label(): string
    {
        return match (true) {
            $this->score >= 80 => 'Excellent',
            $this->score >= 60 => 'Good',
            $this->score >= 40 => 'Fair',
            default            => 'Needs Attention',
        };
    }

    public function color(): string
    {
        return match (true) {
            $this->score >= 80 => 'emerald',
            $this->score >= 60 => 'blue',
            $this->score >= 40 => 'amber',
            default            => 'red',
        };
    }
}