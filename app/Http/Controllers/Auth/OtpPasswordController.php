<?php
// ════════════════════════════════════════════════════════════════
// app/Http/Controllers/Auth/OtpPasswordController.php
// Handles: forgot → sends OTP + magic link email
//          verify OTP → show new password form
//          reset via OTP
//          reset via magic link token
// ════════════════════════════════════════════════════════════════
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\PasswordResetMail;
use App\Models\PasswordResetOtp;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class OtpPasswordController extends Controller
{
    // ── Step 1: Show forgot-password form ─────────────────────────────────
    public function showForgot(): View
    {
        return view('auth.forgot-password');
    }

    // ── Step 2: Send OTP + magic link ────────────────────────────────────
    // public function sendReset(Request $request): RedirectResponse
    // {
    //     $request->validate(['email' => ['required', 'email']]);

    //     // Rate limit: 3 requests per 10 minutes per email
    //     $throttleKey = 'pwd-reset:' . $request->email;
    //     if (RateLimiter::tooManyAttempts($throttleKey, 3)) {
    //         $seconds = RateLimiter::availableIn($throttleKey);
    //         return back()->withErrors([
    //             'email' => "Too many attempts. Please wait {$seconds} seconds."
    //         ]);
    //     }
    //     RateLimiter::hit($throttleKey, 600);

    //     $user = User::where('email', $request->email)->first();

    //     // Always return success even if no user (security: no user enumeration)
    //     if (! $user) {
    //         return back()->with('status',
    //             'If that email exists, we sent a 6-digit code and a magic link.'
    //         );
    //     }

    //     // Generate OTP and magic link token
    //     $otp   = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    //     $token = Str::random(64);

    //     // Delete old records for this email
    //     PasswordResetOtp::where('email', $request->email)->delete();

    //     // Save new record
    //     PasswordResetOtp::create([
    //         'email'      => $request->email,
    //         'otp'        => Hash::make($otp),       // store hashed
    //         'token'      => hash('sha256', $token),  // store hashed
    //         'expires_at' => now()->addMinutes(15),
    //     ]);

    //     // Send email with both OTP and magic link
    //     Mail::to($user->email)->send(new PasswordResetMail(
    //         user:       $user,
    //         otp:        $otp,
    //         magicLink:  route('password.reset-link', ['token' => $token, 'email' => $user->email]),
    //     ));

    //     return redirect()->route('password.otp-form')
    //         ->with('reset_email', $request->email)
    //         ->with('status', 'We sent a 6-digit code and a magic link to your email. Check your inbox.');
    // }

    public function sendReset(Request $request): RedirectResponse
{
    $request->validate([
        'email'  => ['required', 'email'],
        'method' => ['required', 'in:otp,link'],
    ]);

    $throttleKey = 'pwd-reset:' . $request->email;
    if (RateLimiter::tooManyAttempts($throttleKey, 3)) {
        $seconds = RateLimiter::availableIn($throttleKey);
        return back()->withErrors([
            'email' => "Too many attempts. Please wait {$seconds} seconds."
        ]);
    }
    RateLimiter::hit($throttleKey, 600);

    $user = User::where('email', $request->email)->first();

    // No user — return success anyway (prevents email enumeration)
    if (! $user) {
        if ($request->method === 'otp') {
            return redirect()->route('password.otp-form')
                ->with('reset_email', $request->email)
                ->with('status', 'If that email exists, we sent a 6-digit code.');
        }
        return back()->with('status', 'If that email exists, we sent a magic link.');
    }

    // Generate OTP and token
    $otp   = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    $token = Str::random(64);

    PasswordResetOtp::where('email', $request->email)->delete();

    PasswordResetOtp::create([
        'email'      => $request->email,
        'otp'        => Hash::make($otp),
        'token'      => hash('sha256', $token),
        'expires_at' => now()->addMinutes(15),
    ]);

    Mail::to($user->email)->send(new PasswordResetMail(
        user:      $user,
        otp:       $otp,
        magicLink: route('password.reset-link', [
            'token' => $token,
            'email' => $user->email,
        ]),
    ));

    // Route user to whichever flow they chose
    if ($request->method === 'otp') {
        return redirect()->route('password.otp-form')
            ->with('reset_email', $request->email)
            ->with('status', 'We sent a 6-digit code to your email. Enter it below.');
    }

    return back()->with('status', 'Magic link sent! Check your inbox and click the link to reset your password.');
}
    // ── Step 3a: Show OTP input form ──────────────────────────────────────
    public function showOtpForm(Request $request): View
    {
        return view('auth.otp-verify', [
            'email' => session('reset_email') ?? $request->query('email', ''),
        ]);
    }

    // ── Step 3b: Verify OTP → show password reset form ───────────────────
    public function verifyOtp(Request $request): View|RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
            'otp'   => ['required', 'digits:6'],
        ]);

        $throttleKey = 'otp-verify:' . $request->email;
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            return back()->withErrors(['otp' => 'Too many incorrect attempts. Request a new code.']);
        }

        $record = PasswordResetOtp::where('email', $request->email)
            ->where('otp_used', false)
            ->where('expires_at', '>', now())
            ->latest()
            ->first();

        if (! $record || ! Hash::check($request->otp, $record->otp)) {
            RateLimiter::hit($throttleKey, 300);
            return back()
                ->withInput()
                ->withErrors(['otp' => 'Incorrect code. Check your email and try again.']);
        }

        RateLimiter::clear($throttleKey);

        // Mark OTP as used
        $record->update(['otp_used' => true]);

        // Issue a short-lived verified session token to allow password reset
        session(['pwd_verified_email' => $request->email, 'pwd_verified_at' => now()->timestamp]);

        return view('auth.reset-password-otp', [
            'email' => $request->email,
        ]);
    }

    // ── Step 4a: Reset password via OTP flow ─────────────────────────────
    public function resetViaOtp(Request $request): RedirectResponse
    {
        $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()],
        ]);

        // Check session verification (valid for 15 minutes)
        $verifiedEmail = session('pwd_verified_email');
        $verifiedAt    = session('pwd_verified_at');

        if (! $verifiedEmail || $verifiedEmail !== $request->email ||
            ! $verifiedAt   || now()->timestamp - $verifiedAt > 900) {
            return redirect()->route('password.request')
                ->withErrors(['email' => 'Session expired. Please request a new code.']);
        }

        $user = User::where('email', $request->email)->first();

        if (! $user) {
            return redirect()->route('password.request')
                ->withErrors(['email' => 'Account not found.']);
        }

        $user->update(['password' => Hash::make($request->password)]);

        // Clear session
        session()->forget(['pwd_verified_email', 'pwd_verified_at', 'reset_email']);

        return redirect()->route('login')
            ->with('status', 'Password reset successfully. You can now sign in.');
    }

    // ── Step 4b: Reset password via magic link ────────────────────────────
    public function showResetLink(Request $request, string $token): View|RedirectResponse
    {
        $email = $request->query('email', '');

        $record = PasswordResetOtp::where('email', $email)
            ->where('token_used', false)
            ->where('expires_at', '>', now())
            ->latest()
            ->first();

        if (! $record || ! hash_equals($record->token, hash('sha256', $token))) {
            return redirect()->route('password.request')
                ->withErrors(['email' => 'This reset link is invalid or has expired.']);
        }

        return view('auth.reset-password-link', [
            'email' => $email,
            'token' => $token,
        ]);
    }

    public function resetViaLink(Request $request): RedirectResponse
    {
        $request->validate([
            'email'    => ['required', 'email'],
            'token'    => ['required'],
            'password' => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()],
        ]);

        $record = PasswordResetOtp::where('email', $request->email)
            ->where('token_used', false)
            ->where('expires_at', '>', now())
            ->latest()
            ->first();

        if (! $record || ! hash_equals($record->token, hash('sha256', $request->token))) {
            return back()->withErrors(['email' => 'Invalid or expired reset link.']);
        }

        $user = User::where('email', $request->email)->first();

        if (! $user) {
            return back()->withErrors(['email' => 'Account not found.']);
        }

        $user->update(['password' => Hash::make($request->password)]);
        $record->update(['token_used' => true]);

        return redirect()->route('login')
            ->with('status', 'Password reset successfully. You can now sign in.');
    }
}