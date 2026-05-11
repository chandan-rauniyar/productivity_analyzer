<?php
// ════════════════════════════════════════════════════════════════
// app/Models/PasswordResetOtp.php
// ════════════════════════════════════════════════════════════════
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PasswordResetOtp extends Model
{
    protected $fillable = [
        'email', 'otp', 'token',
        'otp_used', 'token_used',
        'expires_at', 'attempts',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'otp_used'   => 'boolean',
            'token_used' => 'boolean',
        ];
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }
}