<?php
// ════════════════════════════════════════════════════════════════
// app/Http/Controllers/Profile/ProfileController.php
// ════════════════════════════════════════════════════════════════
namespace App\Http\Controllers\Profile;

use App\Http\Controllers\Controller;
use App\Models\EmailMetric;
use App\Models\CalendarMetric;
use App\Models\OAuthToken;
use App\Models\ProductivityScore;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class ProfileController extends Controller
{
    // ── Show profile page ──────────────────────────────────────────────────
    public function show(Request $request): View
    {
        $user  = $request->user();
        $token = $user->oauthToken;

        // Stats summary for profile page
        $totalEmailDays    = EmailMetric::where('user_id', $user->id)->count();
        $totalEmailsRecv   = EmailMetric::where('user_id', $user->id)->sum('received_count');
        $avgScore          = EmailMetric::where('user_id', $user->id)->avg('productivity_score');
        $totalMeetingMins  = CalendarMetric::where('user_id', $user->id)->sum('meeting_minutes');

        return view('profile.show', compact(
            'user', 'token',
            'totalEmailDays', 'totalEmailsRecv',
            'avgScore', 'totalMeetingMins'
        ));
    }

    // ── Update name / organisation ─────────────────────────────────────────
    public function updateInfo(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'         => ['required', 'string', 'max:255'],
            'organisation' => ['nullable', 'string', 'max:255'],
        ]);

        $request->user()->update($validated);

        return back()->with('status', 'Profile updated successfully.');
    }

    // ── Change email ───────────────────────────────────────────────────────
    public function updateEmail(Request $request): RedirectResponse
    {
        $user = $request->user();

        $request->validate([
            'email'    => ['required', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'password' => ['required', function ($attr, $val, $fail) use ($user) {
                if (! Hash::check($val, $user->password ?? '')) {
                    $fail('Your current password is incorrect.');
                }
            }],
        ]);

        $user->update([
            'email'             => $request->email,
            'email_verified_at' => null,   // require re-verification if you add it
        ]);

        return back()->with('status', 'Email address updated.');
    }

    // ── Change password ────────────────────────────────────────────────────
    public function updatePassword(Request $request): RedirectResponse
    {
        $user = $request->user();

        $request->validate([
            'current_password' => ['required', function ($attr, $val, $fail) use ($user) {
                if (! Hash::check($val, $user->password ?? '')) {
                    $fail('Your current password is incorrect.');
                }
            }],
            'password' => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()],
        ]);

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return back()->with('status', 'Password changed successfully.');
    }

    // ── Disconnect Microsoft / Outlook ─────────────────────────────────────
    public function disconnectOutlook(Request $request): RedirectResponse
    {
        $user  = $request->user();
        $token = $user->oauthToken;

        if ($token) {
            // Revoke token with Microsoft (best effort)
            try {
                \Illuminate\Support\Facades\Http::asForm()->post(
                    'https://login.microsoftonline.com/' . config('services.azure.tenant') . '/oauth2/v2.0/logout',
                    ['token' => $token->getRawOriginal('access_token')]
                );
            } catch (\Throwable) { /* silent — we still delete locally */ }

            $token->delete();
        }

        $user->update(['ms_id' => null, 'avatar_url' => null]);

        return redirect()->route('profile.show')
            ->with('status', 'Microsoft account disconnected. Your analytics data is preserved.');
    }

    // ── Delete all analytics data (keep account) ───────────────────────────
    public function deleteData(Request $request): RedirectResponse
    {
        $request->validate([
            'confirm_delete' => ['required', 'in:DELETE'],
        ]);

        $userId = $request->user()->id;

        EmailMetric::where('user_id', $userId)->delete();
        CalendarMetric::where('user_id', $userId)->delete();
        ProductivityScore::where('user_id', $userId)->delete();

        // Clear cache
        \Illuminate\Support\Facades\Cache::flush();

        return back()->with('status', 'All analytics data deleted. Your account remains active.');
    }

    // ── Delete account entirely ────────────────────────────────────────────
    public function deleteAccount(Request $request): RedirectResponse
    {
        $user = $request->user();

        $request->validate([
            'password'       => ['required', function ($attr, $val, $fail) use ($user) {
                if ($user->password && ! Hash::check($val, $user->password)) {
                    $fail('Your password is incorrect.');
                }
            }],
            'confirm_delete' => ['required', 'in:DELETE MY ACCOUNT'],
        ]);

        Auth::logout();

        // Cascade deletes handle metrics via FK constraints
        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')->with('status', 'Your account has been permanently deleted.');
    }
}