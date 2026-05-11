<?php

namespace App\Services;

use App\Models\OAuthToken;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class TokenManager
{
    // Short-form scopes — correct for personal Microsoft accounts
    private const GRAPH_SCOPES = 'User.Read Mail.Read Mail.ReadBasic Calendars.Read offline_access';

    public function getValidAccessToken(OAuthToken $token): string
    {
        if ($this->isExpiredOrExpiringSoon($token)) {
            return $this->refreshAccessToken($token);
        }

        return $token->access_token;
    }

    private function isExpiredOrExpiringSoon(OAuthToken $token): bool
    {
        if ($token->expires_at === null) {
            return true;
        }
        return $token->expires_at->lessThanOrEqualTo(now()->addMinutes(5));
    }

    public function refreshAccessToken(OAuthToken $token): string
    {
        if (empty($token->refresh_token)) {
            throw new RuntimeException('No refresh token. Please reconnect your Outlook account.');
        }

        $tenant   = config('services.azure.tenant', 'consumers');
        $tokenUrl = "https://login.microsoftonline.com/{$tenant}/oauth2/v2.0/token";

        Log::debug('Refreshing token', [
            'user_id'    => $token->user_id,
            'tenant'     => $tenant,
            'token_url'  => $tokenUrl,
            'scopes'     => self::GRAPH_SCOPES,
        ]);

        $response = Http::asForm()->post($tokenUrl, [
            'client_id'     => config('services.azure.client_id'),
            'client_secret' => config('services.azure.client_secret'),
            'grant_type'    => 'refresh_token',
            'refresh_token' => $token->refresh_token,
            'redirect_uri'  => config('services.azure.redirect'),
            'scope'         => self::GRAPH_SCOPES,
        ]);

        if (! $response->successful()) {
            Log::error('Token refresh HTTP failed', [
                'user_id' => $token->user_id,
                'status'  => $response->status(),
                'body'    => $response->body(),
            ]);
            throw new RuntimeException('Token refresh failed. Please reconnect your Outlook account.');
        }

        $payload = $response->json();

        if (empty($payload['access_token'])) {
            Log::error('Token refresh returned no access_token', [
                'user_id'  => $token->user_id,
                'response' => $payload,
            ]);
            throw new RuntimeException('Token refresh returned invalid data. Please reconnect.');
        }

        $token->update([
            'access_token'  => $payload['access_token'],
            'refresh_token' => $payload['refresh_token'] ?? $token->getRawOriginal('refresh_token'),
            'expires_at'    => Carbon::now()->addSeconds((int) ($payload['expires_in'] ?? 3600)),
        ]);

        $token->refresh();

        return $token->access_token;
    }
}