<?php
// ════════════════════════════════════════════════════════════════
// app/Http/Controllers/SettingsController.php
// ════════════════════════════════════════════════════════════════
namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        return view('settings', compact('user'));
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'work_start'           => ['required', 'integer', 'between:0,23'],
            'work_end'             => ['required', 'integer', 'between:0,23'],
            'email_notifications'  => ['boolean'],
            'weekly_report'        => ['boolean'],
            'sync_frequency'       => ['required', 'in:daily,manual'],
            'timezone'             => ['required', 'timezone'],
        ]);

        // Store in user meta or a settings table
        // For now store as JSON in users.settings column
        $request->user()->update([
            'settings' => $validated,
        ]);

        return back()->with('status', 'Settings saved successfully.');
    }
}