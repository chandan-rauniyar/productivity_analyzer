<?php

use App\Models\User;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('token:diagnose {user_id?}', function ($userId = null) {
    $this->info('🔍 OAuth Token Diagnostic Tool');
    $this->line('');

    $diagnoseUser = function ($user) {
        $token = $user->oauthToken;

        if (!$token) {
            $this->warn("User {$user->email} has no OAuth token");
            return;
        }

        $this->info("User: {$user->email}");
        $this->line("  Token Provider: {$token->provider}");

        // Token existence
        $this->line("  Access Token Length: " . strlen($token->access_token ?? '') . " chars");
        $this->line("  Refresh Token: " . (!empty($token->refresh_token) ? '✓ YES' : '✗ NO'));

        // Expiry check
        $expiresAt = $token->expires_at;
        if ($expiresAt) {
            $now = now();
            $isExpired = $expiresAt->isPast();
            $isExpiringSoon = $expiresAt->diffInMinutes($now) < 5;

            $this->line("  Expires At: {$expiresAt->toDateTimeString()}");
            $this->line("  Status: " . ($isExpired ? "❌ EXPIRED" : ($isExpiringSoon ? "⚠️  EXPIRING SOON" : "✓ VALID")));
            $this->line("  Time Until Expiry: " . $expiresAt->diffForHumans($now));
        } else {
            $this->warn("  Expires At: NOT SET (will force refresh)");
        }

        // Scopes
        $scopes = $token->scopes ?? [];
        if (is_array($scopes) && !empty($scopes)) {
            $hasMailRead = in_array('Mail.Read', $scopes);
            $this->line("  Scopes Granted: " . implode(', ', $scopes));
            $this->line("  Has Mail.Read: " . ($hasMailRead ? '✓ YES' : '✗ NO (PROBLEM!)'));
        } else {
            $this->warn("  Scopes: EMPTY or INVALID FORMAT");
        }

        // Configuration check
        $this->line("\n  Configuration Check:");
        $this->line("    Client ID: " . (config('services.azure.client_id') ? '✓ SET' : '✗ MISSING'));
        $this->line("    Client Secret: " . (config('services.azure.client_secret') ? '✓ SET' : '✗ MISSING'));
        $this->line("    Redirect URI: " . config('services.azure.redirect'));
        $this->line("    Tenant: " . config('services.azure.tenant'));

        $configuredScopes = config('services.azure.scopes', []);
        $this->line("    Configured Scopes: " . implode(', ', $configuredScopes));
    };

    if ($userId) {
        $user = User::find($userId);
        if (!$user) {
            $this->error("User {$userId} not found");
            return;
        }
        $diagnoseUser($user);
    } else {
        $users = User::whereHas('oauthToken')->get();

        if ($users->isEmpty()) {
            $this->warn('No users with OAuth tokens found');
            return;
        }

        $this->info("Found {$users->count()} users with OAuth tokens:\n");
        foreach ($users as $user) {
            $diagnoseUser($user);
            $this->line('---');
        }
    }
})->purpose('Diagnose OAuth token configuration and validity');

Artisan::command('token:refresh {user_id?}', function ($userId = null) {
    $this->info('🔄 Token Refresh Tool');
    $this->line('');

    if ($userId) {
        $user = User::find($userId);
        if (!$user) {
            $this->error("User {$userId} not found");
            return;
        }
        $users = [$user];
    } else {
        $users = User::whereHas('oauthToken')->get();
        if ($users->isEmpty()) {
            $this->warn('No users with OAuth tokens found');
            return;
        }
    }

    $tokenManager = app(\App\Services\TokenManager::class);

    foreach ($users as $user) {
        $token = $user->oauthToken;
        if (!$token) {
            $this->warn("User {$user->email} has no OAuth token");
            continue;
        }

        $this->info("Refreshing token for: {$user->email}");

        try {
            $newToken = $tokenManager->refreshAccessToken($token);
            $this->info("  ✓ Token refreshed successfully");
            $this->line("  New token length: " . strlen($newToken) . " chars");
            $this->line("  Expires at: " . $token->expires_at->toDateTimeString());
        } catch (\Exception $e) {
            $this->error("  ✗ Refresh failed: " . $e->getMessage());
        }
    }
})->purpose('Manually refresh OAuth tokens');

Artisan::command('test:sync {user_id?}', function ($userId = null) {
    $this->info('🧪 Testing Email Sync');
    $this->line('');

    if ($userId) {
        $user = User::find($userId);
        if (!$user) {
            $this->error("User {$userId} not found");
            return;
        }
    } else {
        $user = User::first();
        if (!$user) {
            $this->error('No users found');
            return;
        }
    }

    $token = $user->oauthToken;
    if (!$token) {
        $this->error("User {$user->email} has no OAuth token");
        return;
    }

    $this->info("Testing sync for: {$user->email}");
    $this->line("");

    try {
        $graphApiService = app(\App\Services\GraphApiService::class);
        $metrics = $graphApiService->getTodayEmailMetrics($token);

        $this->info("✓ Sync successful!");
        $this->line("");
        $this->info("📊 Today's Metrics:");
        $this->line("  Received:     {$metrics['received']} emails");
        $this->line("  Sent:         {$metrics['sent']} emails");
        $this->line("  After Hours:  {$metrics['after_hours']} emails");
    } catch (\Exception $e) {
        $this->error("✗ Sync failed");
        $this->error("Error: " . $e->getMessage());
        $this->line("");
        $this->info("Debug Info:");
        $this->line("  Token expires at: " . $token->expires_at?->toDateTimeString());
        $this->line("  Token scopes: " . implode(', ', $token->scopes ?? []));
    }
})->purpose('Test email metrics sync');

Artisan::command('test:graph-api {user_id?}', function ($userId = null) {
    $this->info('🔍 Testing Microsoft Graph API Directly');
    $this->line('');

    if ($userId) {
        $user = User::find($userId);
        if (!$user) {
            $this->error("User {$userId} not found");
            return;
        }
    } else {
        $user = User::first();
        if (!$user) {
            $this->error('No users found');
            return;
        }
    }

    $token = $user->oauthToken;
    if (!$token) {
        $this->error("User {$user->email} has no OAuth token");
        return;
    }

    $this->info("Testing Graph API for: {$user->email}");
    $this->line("");

    // Test 1: Try with current token
    $this->info("Test 1: Direct Graph API call with current token");
    $this->line("  Token expires at: " . $token->expires_at?->toDateTimeString());
    $this->line("  Token scopes: " . implode(', ', $token->scopes ?? []));

    $guzzleConfig = config('services.azure.guzzle', []);
    $response = \Illuminate\Support\Facades\Http::withOptions($guzzleConfig)
        ->withToken($token->access_token)
        ->acceptJson()
        ->get('https://graph.microsoft.com/v1.0/me');

    $this->line("  Response: " . $response->status() . " " . $response->reason());
    if (!$response->successful()) {
        $this->error("  Error: " . $response->body());
    } else {
        $this->info("  ✓ Success! Got user info:");
        $data = $response->json();
        $this->line("    User: " . ($data['displayName'] ?? 'N/A'));
    }

    // Test 2: Try mailFolders endpoint
    $this->line("");
    $this->info("Test 2: Mail Folders endpoint");
    $response2 = \Illuminate\Support\Facades\Http::withOptions($guzzleConfig)
        ->withToken($token->access_token)
        ->acceptJson()
        ->get('https://graph.microsoft.com/v1.0/me/mailFolders');

    $this->line("  Response: " . $response2->status() . " " . $response2->reason());
    if (!$response2->successful()) {
        $this->error("  Error: " . ($response2->json('error.message') ?? $response2->body()));
    } else {
        $this->info("  ✓ Success! Found " . count($response2->json('value', [])) . " mail folders");
    }

    // Test 3: Token refresh and retry
    if (!$response->successful() || !$response2->successful()) {
        $this->line("");
        $this->info("Test 3: Refreshing token and retrying...");
        $tokenManager = app(\App\Services\TokenManager::class);
        try {
            $freshToken = $tokenManager->refreshAccessToken($token);
            $this->info("  ✓ Token refreshed");

            $response3 = \Illuminate\Support\Facades\Http::withOptions($guzzleConfig)
                ->withToken($freshToken)
                ->acceptJson()
                ->get('https://graph.microsoft.com/v1.0/me');

            $this->line("  Response after refresh: " . $response3->status() . " " . $response3->reason());
            if ($response3->successful()) {
                $this->info("  ✓ Success after refresh!");
            } else {
                $this->error("  Still getting error: " . ($response3->json('error.message') ?? $response3->body()));
            }
        } catch (\Exception $e) {
            $this->error("  Refresh failed: " . $e->getMessage());
        }
    }
})->purpose('Test Microsoft Graph API directly');
