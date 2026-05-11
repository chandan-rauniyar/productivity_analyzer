<?php
// ════════════════════════════════════════════════════════════════
// app/Models/OAuthToken.php  — COMPLETE FILE
// Change: getAccessTokenAttribute and getRefreshTokenAttribute
// now catch DecryptException silently so demo fake tokens
// don't crash the dashboard.
// ════════════════════════════════════════════════════════════════
namespace App\Models;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Crypt;

class OAuthToken extends Model
{
    protected $table = 'oauth_tokens';

    protected $fillable = [
        'user_id',
        'provider',
        'access_token',
        'refresh_token',
        'expires_at',
        'scopes',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'scopes'     => 'array',
        ];
    }

    // ── Encrypt before saving ──────────────────────────────────────────────

    public function setAccessTokenAttribute(string $value): void
    {
        // Don't double-encrypt if already encrypted or is a demo token
        $this->attributes['access_token'] = $this->looksEncrypted($value)
            ? $value
            : Crypt::encryptString($value);
    }

    public function setRefreshTokenAttribute(?string $value): void
    {
        if ($value === null) {
            $this->attributes['refresh_token'] = null;
            return;
        }
        $this->attributes['refresh_token'] = $this->looksEncrypted($value)
            ? $value
            : Crypt::encryptString($value);
    }

    // ── Decrypt when reading ───────────────────────────────────────────────

    public function getAccessTokenAttribute(?string $value): string
    {
        if (! $value) return '';

        try {
            return Crypt::decryptString($value);
        } catch (DecryptException) {
            // Demo token or plain-text token — return as-is
            // Real sync will fail (expected for demo accounts)
            return $value;
        }
    }

    public function getRefreshTokenAttribute(?string $value): ?string
    {
        if (! $value) return null;

        try {
            return Crypt::decryptString($value);
        } catch (DecryptException) {
            return $value;
        }
    }

    // ── Helpers ────────────────────────────────────────────────────────────

    public function isExpired(): bool
    {
        if (! $this->expires_at) return true;
        return $this->expires_at->lessThanOrEqualTo(now()->addMinutes(5));
    }

    public function isDemoToken(): bool
    {
        $raw = $this->attributes['access_token'] ?? '';
        return str_starts_with($raw, 'demo-fake-token');
    }

    private function looksEncrypted(string $value): bool
    {
        // Laravel encrypted strings are base64 JSON starting with eyJ
        return str_starts_with($value, 'eyJ');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}