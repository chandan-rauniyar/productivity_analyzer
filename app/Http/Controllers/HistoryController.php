<?php
// ════════════════════════════════════════════════════════════════
// app/Http/Controllers/HistoryController.php
// ════════════════════════════════════════════════════════════════
namespace App\Http\Controllers;

use App\Models\CalendarMetric;
use App\Models\EmailMetric;
use App\Models\ProductivityScore;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HistoryController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        // Last 30 days of email metrics
        $emailMetrics = EmailMetric::where('user_id', $user->id)
            ->where('metric_date', '>=', now()->subDays(30)->toDateString())
            ->orderBy('metric_date')
            ->get();

        // Last 30 days of calendar metrics
        $calendarMetrics = CalendarMetric::where('user_id', $user->id)
            ->where('metric_date', '>=', now()->subDays(30)->toDateString())
            ->orderBy('metric_date')
            ->get();

        // Last 8 weekly scores
        $weeklyScores = ProductivityScore::where('user_id', $user->id)
            ->orderByDesc('week_start')
            ->limit(8)
            ->get()
            ->reverse()
            ->values();

        // Chart data
        $chartLabels       = $emailMetrics->map(fn($e) => $e->metric_date->format('M d'))->values();
        $chartReceived     = $emailMetrics->pluck('received_count')->values();
        $chartScores       = $emailMetrics->pluck('productivity_score')->values();
        $chartMeetingMins  = $calendarMetrics->map(function($c) use ($emailMetrics) {
            return $c->meeting_minutes;
        })->values();

        $weekLabels  = $weeklyScores->map(fn($s) => 'Wk ' . $s->week_start->format('M d'));
        $weekScores  = $weeklyScores->pluck('score');

        // Summaries
        $avgScore        = round($emailMetrics->avg('productivity_score') ?? 0);
        $totalReceived   = $emailMetrics->sum('received_count');
        $totalMeetingHrs = round($calendarMetrics->sum('meeting_minutes') / 60, 1);
        $bestScore       = $emailMetrics->max('productivity_score') ?? 0;

        return view('history', compact(
            'chartLabels', 'chartReceived', 'chartScores',
            'chartMeetingMins', 'weekLabels', 'weekScores',
            'avgScore', 'totalReceived', 'totalMeetingHrs', 'bestScore',
            'emailMetrics', 'weeklyScores'
        ));
    }
}