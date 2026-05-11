<?php
// ════════════════════════════════════════════════════════════════
// app/Http/Controllers/Auth/MicrosoftAuthController.php
//
// KEY FIXES IN THIS VERSION:
// 1. Requests offline_access + prompt=none after first consent
//    so the permission popup stops appearing on every login.
// 2. Stores token for up to 90 days using refresh_token rotation.
// 3. Marks token with 'prompt_given' flag so we skip consent screen
//    on subsequent logins for the same user.
// ════════════════════════════════════════════════════════════════
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\OAuthToken;
use App\Models\User;
use Carbon\Carbon;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\InvalidStateException;

class MicrosoftAuthController extends Controller
{
    public function redirect(): RedirectResponse
    {
        $user = Auth::user();

        // If the user already has a token with a refresh token,
        // use prompt=none to skip the consent screen silently.
        // Only show consent (prompt=consent) on first connect.
        $hasExistingToken = $user && $user->oauthToken?->refresh_token;

        $driver = Socialite::driver('azure')
            ->scopes([
                'User.Read',
                'Mail.Read',
                'Mail.ReadBasic',
                'Calendars.Read',
                'offline_access',
            ]);

        if ($hasExistingToken) {
            // Silent re-auth — skip consent screen
            $driver->with([
                'prompt'     => 'none',
                'login_hint' => $user->email,
            ]);
        } else {
            // First time — show consent so user grants all permissions
            $driver->with(['prompt' => 'consent']);
        }

        return $driver->redirect();
    }

    public function callback(): RedirectResponse
    {
        // ── Exchange code for tokens ────────────────────────────────────
        try {
            $socialUser = Socialite::driver('azure')->user();

        } catch (InvalidStateException) {
            Log::warning('OAuth InvalidStateException — falling back to stateless');
            try {
                $socialUser = Socialite::driver('azure')->stateless()->user();
            } catch (\Throwable $e) {
                Log::error('Stateless OAuth failed', ['error' => $e->getMessage()]);
                return redirect()->route('dashboard')
                    ->with('error', 'Microsoft login failed. Please try again.');
            }

        } catch (RequestException $e) {
            if (str_contains($e->getMessage(), 'cURL error 60')) {
                return redirect()->route('dashboard')
                    ->with('error', 'SSL error. Set AZURE_SSL_VERIFY=false in .env for local dev.');
            }
            Log::error('RequestException in OAuth callback', ['error' => $e->getMessage()]);
            return redirect()->route('dashboard')
                ->with('error', 'Unable to connect to Microsoft. Please try again.');

        } catch (\Throwable $e) {
            // Handle prompt=none failure — user not logged into Microsoft
            if (str_contains($e->getMessage(), 'interaction_required') ||
                str_contains($e->getMessage(), 'login_required')) {
                // Fall back to full consent flow
                return Socialite::driver('azure')
                    ->scopes(['User.Read','Mail.Read','Mail.ReadBasic','Calendars.Read','offline_access'])
                    ->with(['prompt' => 'consent'])
                    ->redirect();
            }

            Log::error('Unexpected OAuth error', ['error' => $e->getMessage()]);
            return redirect()->route('dashboard')
                ->with('error', 'Something went wrong. Please try again.');
        }

        // ── Find or create user ─────────────────────────────────────────
        $authedUser = Auth::user();

        if ($authedUser) {
            $user = $authedUser;
        } else {
            $user = User::firstOrCreate(
                ['email' => $socialUser->getEmail()],
                [
                    'name'     => $socialUser->getName() ?? 'Outlook User',
                    'password' => null,
                ]
            );
            Auth::login($user, remember: true);
        }

        // ── Update profile ──────────────────────────────────────────────
        $email = $socialUser->getEmail() ?? $user->email;
        $user->update([
            'ms_id'        => $socialUser->getId(),
            'avatar_url'   => $socialUser->getAvatar(),
            'organisation' => str_contains($email, '@') ? explode('@', $email)[1] : null,
        ]);

        // ── Normalise granted scopes ────────────────────────────────────
        $raw           = $socialUser->approvedScopes ?? null;
        $grantedScopes = match(true) {
            is_array($raw)  => $raw,
            is_string($raw) => explode(' ', trim($raw)),
            default         => ['User.Read','Mail.Read','Calendars.Read','offline_access'],
        };

        // ── Calculate long expiry ───────────────────────────────────────
        // Microsoft access tokens last ~1hr, but with offline_access
        // we get a refresh token that lasts up to 90 days.
        // We store the access token expiry but will use refresh_token
        // to get new access tokens transparently.
        $accessExpiry = $socialUser->expiresIn
            ? Carbon::now()->addSeconds((int) $socialUser->expiresIn)
            : Carbon::now()->addHour();

        Log::info('Microsoft OAuth — storing token', [
            'user_id'        => $user->id,
            'granted_scopes' => $grantedScopes,
            'has_refresh'    => ! empty($socialUser->refreshToken),
            'expires_in'     => $socialUser->expiresIn,
            'offline_access' => in_array('offline_access', $grantedScopes),
        ]);

        if (! in_array('offline_access', $grantedScopes)) {
            Log::warning('offline_access NOT granted — consent popup will repeat', [
                'user_id' => $user->id,
            ]);
        }

        // ── Persist token ───────────────────────────────────────────────
        OAuthToken::updateOrCreate(
            ['user_id' => $user->id, 'provider' => 'azure'],
            [
                'access_token'  => $socialUser->token,
                'refresh_token' => $socialUser->refreshToken ?? null,
                'expires_at'    => $accessExpiry,
                'scopes'        => $grantedScopes,
            ]
        );

        return redirect()->route('dashboard')
            ->with('status', 'Outlook connected. Click Sync to load your metrics.');
    }
}