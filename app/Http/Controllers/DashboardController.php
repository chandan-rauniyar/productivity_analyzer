<?php

namespace App\Http\Controllers;

use App\Models\CalendarMetric;
use App\Models\EmailMetric;
use App\Models\ProductivityScore;
use App\Services\GraphApiService;
use App\Services\ProductivityAnalyzer;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        private readonly GraphApiService      $graph,
        private readonly ProductivityAnalyzer $analyzer,
    ) {}

    // ── Main dashboard ─────────────────────────────────────────────────────

    public function index(Request $request): View
    {
        $user  = $request->user();
        $token = $user->oauthToken;

        $todayEmail    = EmailMetric::where('user_id', $user->id)
            ->whereDate('metric_date', today())->first();

        $todayCalendar = CalendarMetric::where('user_id', $user->id)
            ->whereDate('metric_date', today())->first();

        $from = now()->subDays(6)->toDateString();
        $to   = today()->toDateString();

        $weekEmails = EmailMetric::where('user_id', $user->id)
            ->whereBetween('metric_date', [$from, $to])
            ->orderBy('metric_date')->get();

        $weekCalendars = CalendarMetric::where('user_id', $user->id)
            ->whereBetween('metric_date', [$from, $to])
            ->orderBy('metric_date')->get();

        $weekStart   = now()->startOfWeek()->toDateString();
        $weeklyScore = ProductivityScore::where('user_id', $user->id)
            ->where('week_start', $weekStart)->first();

        $scores   = [];
        $insights = [];

        if ($todayEmail && $todayCalendar) {
            $scores   = $this->analyzer->calculateScore($todayEmail->toArray(), $todayCalendar->toArray());
            $insights = $this->analyzer->generateInsights($todayEmail->toArray(), $todayCalendar->toArray(), $scores);
        }

        $score = $scores['score'] ?? ($todayEmail?->productivity_score ?? 0);

        $calArrays    = $weekCalendars->map(fn($c) => $c->toArray())->toArray();
        $focusWindows = $this->analyzer->recommendFocusWindows($calArrays);

        $combined = $weekEmails->map(function ($em) use ($weekCalendars) {
            $cal = $weekCalendars->first(
                fn($c) => $c->metric_date->toDateString() === $em->metric_date->toDateString(),
                null
            );
            return array_merge($em->toArray(), $cal ? $cal->toArray() : []);
        })->toArray();

        $burnout = $this->analyzer->detectBurnout($combined);

        $chartLabels      = $weekEmails->map(fn($e) => $e->metric_date->format('D d'))->values();
        $chartReceived    = $weekEmails->pluck('received_count')->values();
        $chartSent        = $weekEmails->pluck('sent_count')->values();
        $chartMeetingMins = $this->padWeekData($weekCalendars, 'meeting_minutes');
        $chartFocusMins   = $this->padWeekData($weekCalendars, 'focus_time_minutes');
        $chartScores      = $weekEmails->pluck('productivity_score')->values();
        $hourlyDist       = $todayEmail?->hourly_distribution ?? array_fill(0, 24, 0);

        $monthScores = ProductivityScore::where('user_id', $user->id)
            ->orderByDesc('week_start')->limit(4)->get()->reverse()->values();

        $trendLabels = $monthScores->map(fn($s) => 'Wk ' . $s->week_start->format('M d'));
        $trendScores = $monthScores->pluck('score');

        return view('dashboard', compact(
            'token', 'user',
            'todayEmail', 'todayCalendar',
            'score', 'scores', 'insights',
            'weeklyScore', 'focusWindows', 'burnout',
            'chartLabels', 'chartReceived', 'chartSent',
            'chartMeetingMins', 'chartFocusMins', 'chartScores',
            'hourlyDist', 'trendLabels', 'trendScores',
        ));
    }

    // ── Today sync ─────────────────────────────────────────────────────────
 // ADD THIS to your DashboardController.php
// Replace ONLY the sync() and syncHistorical() methods
// Everything else stays exactly the same
// ════════════════════════════════════════════════════════════════

    // ── Today sync ─────────────────────────────────────────────────────────
    public function sync(Request $request): RedirectResponse
    {
        $user  = $request->user();
        $token = $user->oauthToken;

        if (! $token) {
            return back()->with('error', 'Please connect your Outlook account first.');
        }

        // Block sync for demo accounts — they have fake tokens
        if ($token->isDemoToken()) {
            return back()->with('error',
                'This is a demo account — sync is disabled. Log in with a real Microsoft account to sync live data.'
            );
        }

        try {
            $emailData    = $this->graph->getTodayEmailMetrics($token);
            $calendarData = $this->graph->getTodayCalendarEvents($token);

            $em     = $this->analyzer->analyzeEmails($emailData['inbox'], $emailData['sent']);
            $cm     = $this->analyzer->analyzeCalendar($calendarData);
            $scores = $this->analyzer->calculateScore($em, $cm);

            EmailMetric::updateOrCreate(
                ['user_id' => $user->id, 'metric_date' => today()->toDateString()],
                [
                    'received_count'      => $em['received_count'],
                    'sent_count'          => $em['sent_count'],
                    'after_hours_count'   => $em['after_hours_count'],
                    'avg_response_hours'  => $em['avg_response_hours'],
                    'peak_hour'           => $em['peak_hour'],
                    'hourly_distribution' => $em['hourly_distribution'],
                    'productivity_score'  => $scores['score'],
                ]
            );

            CalendarMetric::updateOrCreate(
                ['user_id' => $user->id, 'metric_date' => today()->toDateString()],
                [
                    'total_meetings'       => $cm['total_meetings'],
                    'meeting_minutes'      => $cm['meeting_minutes'],
                    'focus_time_minutes'   => $cm['focus_time_minutes'],
                    'back_to_back_count'   => $cm['back_to_back_count'],
                    'after_hours_meetings' => $cm['after_hours_meetings'],
                    'meeting_slots'        => $cm['meeting_slots'],
                ]
            );

            $this->updateWeeklyScore($user->id);
            $this->bustCache($user->id);
            $user->update(['last_synced_at' => now()]);

        } catch (\Throwable $e) {
            $msg = app()->isLocal() ? $e->getMessage() : 'Sync failed. Please try again.';
            return back()->with('error', $msg);
        }

        return back()->with('status', 'Synced! Dashboard updated with latest data.');
    }

    // ── Historical sync ─────────────────────────────────────────────────────
    public function syncHistorical(Request $request): RedirectResponse
    {
        set_time_limit(300);
        ini_set('max_execution_time', '300');

        $request->validate([
            'from_date' => ['required', 'date', 'before:today'],
            'to_date'   => ['required', 'date', 'before_or_equal:today', 'after_or_equal:from_date'],
        ]);

        $user  = $request->user();
        $token = $user->oauthToken;

        if (! $token) {
            return back()->with('error', 'Please connect your Outlook account first.');
        }

        // Block sync for demo accounts
        if ($token->isDemoToken()) {
            return back()->with('error',
                'This is a demo account — sync is disabled. Log in with a real Microsoft account to sync live data.'
            );
        }

        $from = \Carbon\Carbon::parse($request->from_date)->startOfDay();
        $to   = \Carbon\Carbon::parse($request->to_date)->endOfDay();

        if ($from->diffInDays($to) > 90) {
            return back()->with('error', 'Maximum range is 90 days. Please select a shorter period.');
        }

        $synced  = 0;
        $skipped = 0;
        $current = $from->copy()->startOfDay();

        while ($current->lte($to)) {
            try {
                $dayStart = $current->copy()->startOfDay()->utc();
                $dayEnd   = $current->copy()->endOfDay()->utc();

                $inbox  = $this->graph->fetchDayMessages($token, 'inbox',     'receivedDateTime', $dayStart, $dayEnd);
                $sent   = $this->graph->fetchDayMessages($token, 'sentitems', 'sentDateTime',     $dayStart, $dayEnd);
                $events = $this->graph->fetchDayEvents($token, $dayStart, $dayEnd);

                $em     = $this->analyzer->analyzeEmails($inbox, $sent);
                $cm     = $this->analyzer->analyzeCalendar($events);
                $scores = $this->analyzer->calculateScore($em, $cm);

                $hasData = $em['received_count'] > 0
                    || $em['sent_count'] > 0
                    || $cm['total_meetings'] > 0;

                if ($hasData) {
                    \App\Models\EmailMetric::updateOrCreate(
                        ['user_id' => $user->id, 'metric_date' => $current->toDateString()],
                        [
                            'received_count'      => $em['received_count'],
                            'sent_count'          => $em['sent_count'],
                            'after_hours_count'   => $em['after_hours_count'],
                            'avg_response_hours'  => $em['avg_response_hours'],
                            'peak_hour'           => $em['peak_hour'],
                            'hourly_distribution' => $em['hourly_distribution'],
                            'productivity_score'  => $scores['score'],
                        ]
                    );

                    \App\Models\CalendarMetric::updateOrCreate(
                        ['user_id' => $user->id, 'metric_date' => $current->toDateString()],
                        [
                            'total_meetings'       => $cm['total_meetings'],
                            'meeting_minutes'      => $cm['meeting_minutes'],
                            'focus_time_minutes'   => $cm['focus_time_minutes'],
                            'back_to_back_count'   => $cm['back_to_back_count'],
                            'after_hours_meetings' => $cm['after_hours_meetings'],
                            'meeting_slots'        => $cm['meeting_slots'],
                        ]
                    );

                    $synced++;
                } else {
                    $skipped++;
                }

                usleep(300000);

            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('Historical sync failed for day', [
                    'user_id' => $user->id,
                    'date'    => $current->toDateString(),
                    'error'   => $e->getMessage(),
                ]);
                $skipped++;
            }

            $current->addDay();
        }

        $this->rebuildWeeklyScores($user->id, $from, $to);
        $this->bustCache($user->id);
        $user->update(['last_synced_at' => now()]);

        return back()->with('status',
            "Historical sync complete — {$synced} days synced, {$skipped} empty days skipped."
        );
    }

    // public function sync(Request $request): RedirectResponse
    // {
    //     $user  = $request->user();
    //     $token = $user->oauthToken;

    //     if (! $token) {
    //         return back()->with('error', 'Please connect your Outlook account first.');
    //     }

    //     try {
    //         $emailData    = $this->graph->getTodayEmailMetrics($token);
    //         $calendarData = $this->graph->getTodayCalendarEvents($token);

    //         $em     = $this->analyzer->analyzeEmails($emailData['inbox'], $emailData['sent']);
    //         $cm     = $this->analyzer->analyzeCalendar($calendarData);
    //         $scores = $this->analyzer->calculateScore($em, $cm);

    //         EmailMetric::updateOrCreate(
    //             ['user_id' => $user->id, 'metric_date' => today()->toDateString()],
    //             [
    //                 'received_count'      => $em['received_count'],
    //                 'sent_count'          => $em['sent_count'],
    //                 'after_hours_count'   => $em['after_hours_count'],
    //                 'avg_response_hours'  => $em['avg_response_hours'],
    //                 'peak_hour'           => $em['peak_hour'],
    //                 'hourly_distribution' => $em['hourly_distribution'],
    //                 'productivity_score'  => $scores['score'],
    //             ]
    //         );

    //         CalendarMetric::updateOrCreate(
    //             ['user_id' => $user->id, 'metric_date' => today()->toDateString()],
    //             [
    //                 'total_meetings'       => $cm['total_meetings'],
    //                 'meeting_minutes'      => $cm['meeting_minutes'],
    //                 'focus_time_minutes'   => $cm['focus_time_minutes'],
    //                 'back_to_back_count'   => $cm['back_to_back_count'],
    //                 'after_hours_meetings' => $cm['after_hours_meetings'],
    //                 'meeting_slots'        => $cm['meeting_slots'],
    //             ]
    //         );

    //         $this->updateWeeklyScore($user->id);
    //         $this->bustCache($user->id);
    //         $user->update(['last_synced_at' => now()]);

    //     } catch (\Throwable $e) {
    //         $msg = app()->isLocal() ? $e->getMessage() : 'Sync failed. Please try again.';
    //         return back()->with('error', $msg);
    //     }

    //     return back()->with('status', 'Synced! Dashboard updated with latest data.');
    // }

    // // ── Historical sync ────────────────────────────────────────────────────

    // public function syncHistorical(Request $request): RedirectResponse
    // {
    //     set_time_limit(300);
    //     ini_set('max_execution_time', '300');

    //     $request->validate([
    //         'from_date' => ['required', 'date', 'before:today'],
    //         'to_date'   => ['required', 'date', 'before_or_equal:today', 'after_or_equal:from_date'],
    //     ]);

    //     $user  = $request->user();
    //     $token = $user->oauthToken;

    //     if (! $token) {
    //         return back()->with('error', 'Please connect your Outlook account first.');
    //     }

    //     $from = Carbon::parse($request->from_date)->startOfDay();
    //     $to   = Carbon::parse($request->to_date)->endOfDay();

    //     if ($from->diffInDays($to) > 90) {
    //         return back()->with('error', 'Maximum range is 90 days. Please select a shorter period.');
    //     }

    //     $synced  = 0;
    //     $skipped = 0;
    //     $current = $from->copy()->startOfDay();

    //     while ($current->lte($to)) {
    //         try {
    //             $dayStart = $current->copy()->startOfDay()->utc();
    //             $dayEnd   = $current->copy()->endOfDay()->utc();

    //             $inbox  = $this->graph->fetchDayMessages($token, 'inbox',     'receivedDateTime', $dayStart, $dayEnd);
    //             $sent   = $this->graph->fetchDayMessages($token, 'sentitems', 'sentDateTime',     $dayStart, $dayEnd);
    //             $events = $this->graph->fetchDayEvents($token, $dayStart, $dayEnd);

    //             $em     = $this->analyzer->analyzeEmails($inbox, $sent);
    //             $cm     = $this->analyzer->analyzeCalendar($events);
    //             $scores = $this->analyzer->calculateScore($em, $cm);

    //             $hasData = $em['received_count'] > 0
    //                 || $em['sent_count'] > 0
    //                 || $cm['total_meetings'] > 0;

    //             if ($hasData) {
    //                 EmailMetric::updateOrCreate(
    //                     ['user_id' => $user->id, 'metric_date' => $current->toDateString()],
    //                     [
    //                         'received_count'      => $em['received_count'],
    //                         'sent_count'          => $em['sent_count'],
    //                         'after_hours_count'   => $em['after_hours_count'],
    //                         'avg_response_hours'  => $em['avg_response_hours'],
    //                         'peak_hour'           => $em['peak_hour'],
    //                         'hourly_distribution' => $em['hourly_distribution'],
    //                         'productivity_score'  => $scores['score'],
    //                     ]
    //                 );

    //                 CalendarMetric::updateOrCreate(
    //                     ['user_id' => $user->id, 'metric_date' => $current->toDateString()],
    //                     [
    //                         'total_meetings'       => $cm['total_meetings'],
    //                         'meeting_minutes'      => $cm['meeting_minutes'],
    //                         'focus_time_minutes'   => $cm['focus_time_minutes'],
    //                         'back_to_back_count'   => $cm['back_to_back_count'],
    //                         'after_hours_meetings' => $cm['after_hours_meetings'],
    //                         'meeting_slots'        => $cm['meeting_slots'],
    //                     ]
    //                 );

    //                 $synced++;
    //             } else {
    //                 $skipped++;
    //             }

    //             usleep(300000); // 300ms pause per day — avoids Graph API rate limits

    //         } catch (\Throwable $e) {
    //             Log::warning('Historical sync failed for day', [
    //                 'user_id' => $user->id,
    //                 'date'    => $current->toDateString(),
    //                 'error'   => $e->getMessage(),
    //             ]);
    //             $skipped++;
    //         }

    //         $current->addDay();
    //     }

    //     $this->rebuildWeeklyScores($user->id, $from, $to);
    //     $this->bustCache($user->id);
    //     $user->update(['last_synced_at' => now()]);

    //     return back()->with('status',
    //         "Historical sync complete — {$synced} days synced, {$skipped} empty days skipped."
    //     );
    // }

    // ── Private helpers ────────────────────────────────────────────────────

    private function updateWeeklyScore(int $userId): void
    {
        $weekStart     = now()->startOfWeek()->toDateString();
        $weekEmails    = EmailMetric::where('user_id', $userId)
            ->whereBetween('metric_date', [$weekStart, today()->toDateString()])->get();
        $weekCalendars = CalendarMetric::where('user_id', $userId)
            ->whereBetween('metric_date', [$weekStart, today()->toDateString()])->get();

        if ($weekEmails->isEmpty()) return;

        $avgScore  = (int) $weekEmails->avg('productivity_score');
        $bestWorst = $this->analyzer->findBestAndWorstDay(
            $weekEmails->map->toArray()->toArray(),
            $weekCalendars->map->toArray()->toArray()
        );

        $latestEm  = $weekEmails->last()->toArray();
        $latestCm  = $weekCalendars->last()?->toArray() ?? [];
        $scores    = $this->analyzer->calculateScore($latestEm, $latestCm);
        $insights  = $this->analyzer->generateInsights($latestEm, $latestCm, $scores);

        ProductivityScore::updateOrCreate(
            ['user_id' => $userId, 'week_start' => $weekStart],
            [
                'score'          => $avgScore,
                'email_score'    => $scores['email_score'],
                'calendar_score' => $scores['calendar_score'],
                'balance_score'  => $scores['balance_score'],
                'insights'       => array_column($insights, 'text'),
                'best_day'       => $bestWorst['best_day'],
                'worst_day'      => $bestWorst['worst_day'],
            ]
        );
    }

    private function rebuildWeeklyScores(int $userId, Carbon $from, Carbon $to): void
    {
        $monday = $from->copy()->startOfWeek();

        while ($monday->lte($to)) {
            $weekEnd = $monday->copy()->endOfWeek();

            $weekEmails    = EmailMetric::where('user_id', $userId)
                ->whereBetween('metric_date', [$monday->toDateString(), $weekEnd->toDateString()])
                ->get();

            $weekCalendars = CalendarMetric::where('user_id', $userId)
                ->whereBetween('metric_date', [$monday->toDateString(), $weekEnd->toDateString()])
                ->get();

            if ($weekEmails->isNotEmpty()) {
                $avgScore  = (int) $weekEmails->avg('productivity_score');
                $bestWorst = $this->analyzer->findBestAndWorstDay(
                    $weekEmails->map->toArray()->toArray(),
                    $weekCalendars->map->toArray()->toArray()
                );

                $latestEm  = $weekEmails->last()->toArray();
                $latestCm  = $weekCalendars->last()?->toArray() ?? [];
                $scores    = $this->analyzer->calculateScore($latestEm, $latestCm);
                $insights  = $this->analyzer->generateInsights($latestEm, $latestCm, $scores);

                ProductivityScore::updateOrCreate(
                    ['user_id' => $userId, 'week_start' => $monday->toDateString()],
                    [
                        'score'          => $avgScore,
                        'email_score'    => $scores['email_score'],
                        'calendar_score' => $scores['calendar_score'],
                        'balance_score'  => $scores['balance_score'],
                        'insights'       => array_column($insights, 'text'),
                        'best_day'       => $bestWorst['best_day'],
                        'worst_day'      => $bestWorst['worst_day'],
                    ]
                );
            }

            $monday->addWeek();
        }
    }

    private function bustCache(int $userId): void
    {
        $date = today()->toDateString();
        Cache::forget("graph:email-today:{$userId}:{$date}");
        Cache::forget("graph:cal-today:{$userId}:{$date}");
        Cache::forget("graph:email-week:{$userId}:{$date}");
        Cache::forget("graph:cal-week:{$userId}:{$date}");
    }

    private function padWeekData($collection, string $field): \Illuminate\Support\Collection
    {
        $map = $collection->keyBy(fn($c) => $c->metric_date->toDateString());
        return collect(range(6, 0))->map(function ($daysAgo) use ($map, $field) {
            $date = now()->subDays($daysAgo)->toDateString();
            return $map->get($date)?->{$field} ?? 0;
        })->values();
    }
}