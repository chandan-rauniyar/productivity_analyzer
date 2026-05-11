<?php
// ════════════════════════════════════════════════════════════════
// app/Services/ProductivityAnalyzer.php
// ════════════════════════════════════════════════════════════════
namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Collection;

class ProductivityAnalyzer
{
    private const WORK_START  = 9;
    private const WORK_END    = 18;
    private const WORK_MINS   = (self::WORK_END - self::WORK_START) * 60;

    // ── Email Analysis ─────────────────────────────────────────────────────

    public function analyzeEmails(array $inbox, array $sent): array
    {
        $hourly      = $this->buildHourlyDistribution($inbox, 'receivedDateTime');
        $peakHour    = ! empty($hourly) ? (int) array_search(max($hourly), $hourly) : null;
        $avgResponse = $this->calcAvgResponseHours($inbox, $sent);
        $afterHours  = $this->countAfterHours($inbox, 'receivedDateTime')
                     + $this->countAfterHours($sent,  'sentDateTime');

        return [
            'received_count'      => count($inbox),
            'sent_count'          => count($sent),
            'after_hours_count'   => $afterHours,
            'avg_response_hours'  => $avgResponse,
            'peak_hour'           => $peakHour,
            'hourly_distribution' => $hourly,
        ];
    }

    private function buildHourlyDistribution(array $messages, string $field): array
    {
        $dist = array_fill(0, 24, 0);
        foreach ($messages as $msg) {
            if (! empty($msg[$field])) {
                $dist[Carbon::parse($msg[$field])->hour]++;
            }
        }
        return $dist;
    }

    private function countAfterHours(array $messages, string $field): int
    {
        return collect($messages)->filter(function ($m) use ($field) {
            if (empty($m[$field])) return false;
            $h = Carbon::parse($m[$field])->hour;
            return $h < self::WORK_START || $h >= self::WORK_END;
        })->count();
    }

    private function calcAvgResponseHours(array $inbox, array $sent): float
    {
        if (empty($inbox) || empty($sent)) return 0.0;

        $sentTimes = collect($sent)
            ->filter(fn($m) => ! empty($m['sentDateTime']))
            ->map(fn($m)    => Carbon::parse($m['sentDateTime']))
            ->sort()->values();

        $total = 0; $count = 0;
        foreach ($inbox as $msg) {
            if (empty($msg['receivedDateTime'])) continue;
            $recv     = Carbon::parse($msg['receivedDateTime']);
            $nextSent = $sentTimes->first(fn($t) => $t->gt($recv));
            if ($nextSent) { $total += $recv->diffInMinutes($nextSent); $count++; }
        }

        return $count > 0 ? round($total / $count / 60, 1) : 0.0;
    }

    // ── Calendar Analysis ──────────────────────────────────────────────────

    public function analyzeCalendar(array $events): array
    {
        if (empty($events)) {
            return [
                'total_meetings'       => 0,
                'meeting_minutes'      => 0,
                'focus_time_minutes'   => self::WORK_MINS, // full day is focus if no meetings
                'back_to_back_count'   => 0,
                'after_hours_meetings' => 0,
                'meeting_slots'        => [],
            ];
        }

        $sorted = collect($events)
            ->filter(fn($e) => ! empty($e['start']['dateTime']) && ! empty($e['end']['dateTime']))
            ->sortBy(fn($e) => $e['start']['dateTime'])
            ->values();

        $slots = []; $totalMin = 0; $b2b = 0; $afterHours = 0;
        $prevEnd = null;

        foreach ($sorted as $event) {
            $start = Carbon::parse($event['start']['dateTime']);
            $end   = Carbon::parse($event['end']['dateTime']);
            $dur   = max(0, $start->diffInMinutes($end));

            $totalMin += $dur;
            $slots[]   = [
                'title'    => $event['subject'] ?? 'Meeting',
                'start'    => $start->format('H:i'),
                'end'      => $end->format('H:i'),
                'duration' => $dur,
                'online'   => $event['isOnlineMeeting'] ?? false,
            ];

            if ($prevEnd && $start->gt($prevEnd) && $prevEnd->diffInMinutes($start) < 5) {
                $b2b++;
            }

            if ($start->hour < self::WORK_START || $start->hour >= self::WORK_END) {
                $afterHours++;
            }

            $prevEnd = $end;
        }

        return [
            'total_meetings'       => $sorted->count(),
            'meeting_minutes'      => $totalMin,
            'focus_time_minutes'   => $this->calcFocusTime($sorted->toArray()),
            'back_to_back_count'   => $b2b,
            'after_hours_meetings' => $afterHours,
            'meeting_slots'        => $slots,
        ];
    }

    private function calcFocusTime(array $events): int
    {
        $focus      = 0;
        $workStart  = self::WORK_START * 60;
        $workEnd    = self::WORK_END   * 60;
        $cursor     = $workStart;

        foreach ($events as $event) {
            $s = Carbon::parse($event['start']['dateTime']);
            $e = Carbon::parse($event['end']['dateTime']);
            $sm = max($s->hour * 60 + $s->minute, $workStart);
            $em = min($e->hour * 60 + $e->minute, $workEnd);

            $gap = $sm - $cursor;
            if ($gap >= 30) $focus += $gap;

            $cursor = max($cursor, $em);
        }

        $remaining = $workEnd - $cursor;
        if ($remaining >= 30) $focus += $remaining;

        return max(0, $focus);
    }

    // ── Scoring ────────────────────────────────────────────────────────────

    public function calculateScore(array $em, array $cm): array
    {
        // Email sub-score (0-100)
        $emailScore = 100;
        $received   = $em['received_count'] ?? 0;
        if ($received > 80)      $emailScore -= 25;
        elseif ($received > 50)  $emailScore -= 12;
        $after = $em['after_hours_count'] ?? 0;
        if ($after > 10)     $emailScore -= 20;
        elseif ($after > 5)  $emailScore -= 10;
        $resp = $em['avg_response_hours'] ?? 0;
        if ($resp > 12)            $emailScore -= 15;
        elseif ($resp > 6)         $emailScore -= 7;
        elseif ($resp > 0 && $resp < 2) $emailScore += 5;

        // Calendar sub-score (0-100)
        $calScore    = 100;
        $meetingHrs  = ($cm['meeting_minutes'] ?? 0) / 60;
        if ($meetingHrs > 5)     $calScore -= 25;
        elseif ($meetingHrs > 3) $calScore -= 12;
        $b2b = $cm['back_to_back_count'] ?? 0;
        if ($b2b > 3)     $calScore -= 15;
        elseif ($b2b > 1) $calScore -= 7;
        $focusHrs = ($cm['focus_time_minutes'] ?? 0) / 60;
        if ($focusHrs > 3)                        $calScore += 10;
        elseif ($focusHrs < 1 && $meetingHrs > 0) $calScore -= 12;
        if (($cm['after_hours_meetings'] ?? 0) > 2) $calScore -= 10;

        // Balance sub-score (0-100)
        $balScore   = 100;
        $totalWork  = self::WORK_MINS;
        $meetPct    = $totalWork > 0 ? ($cm['meeting_minutes'] ?? 0) / $totalWork : 0;
        if ($meetPct > 0.75)     $balScore -= 25;
        elseif ($meetPct > 0.50) $balScore -= 12;
        if ($after > 5)          $balScore -= 20;
        if ($focusHrs > $meetingHrs && $meetingHrs > 0) $balScore += 10;

        // Clamp all sub-scores
        $emailScore = max(0, min(100, $emailScore));
        $calScore   = max(0, min(100, $calScore));
        $balScore   = max(0, min(100, $balScore));

        // Weighted overall
        $overall = (int) round($emailScore * 0.35 + $calScore * 0.40 + $balScore * 0.25);

        return [
            'score'          => max(0, min(100, $overall)),
            'email_score'    => $emailScore,
            'calendar_score' => $calScore,
            'balance_score'  => $balScore,
        ];
    }

    // ── Best / Worst Day ──────────────────────────────────────────────────

    public function findBestAndWorstDay(array $weekEmailMetrics, array $weekCalendarMetrics): array
    {
        $days = [];
        foreach ($weekEmailMetrics as $em) {
            $date = is_string($em['metric_date']) ? $em['metric_date'] : $em['metric_date']->toDateString();
            $cm   = collect($weekCalendarMetrics)->first(
                fn($c) => (is_string($c['metric_date']) ? $c['metric_date'] : $c['metric_date']->toDateString()) === $date,
                []
            );

            $scores      = $this->calculateScore($em, $cm ?: []);
            $days[$date] = [
                'day'   => Carbon::parse($date)->format('l'),
                'score' => $scores['score'],
            ];
        }

        if (empty($days)) return ['best_day' => null, 'worst_day' => null];

        arsort($days);
        $best  = reset($days)['day'];
        asort($days);
        $worst = reset($days)['day'];

        return ['best_day' => $best, 'worst_day' => $worst];
    }

    // ── Burnout Detection ─────────────────────────────────────────────────

    public function detectBurnout(array $recentDayMetrics): ?array
    {
        $heavyDays = 0;
        $afterHoursTotal = 0;

        foreach ($recentDayMetrics as $day) {
            $meetingHrs      = ($day['meeting_minutes'] ?? 0) / 60;
            $afterHoursEmails = $day['after_hours_count'] ?? 0;

            if ($meetingHrs > 4 || $afterHoursEmails > 8) {
                $heavyDays++;
            }
            $afterHoursTotal += $afterHoursEmails;
        }

        if ($heavyDays >= 3) {
            return [
                'severity' => $heavyDays >= 4 ? 3 : 2,
                'reason'   => "{$heavyDays} consecutive heavy days detected — consider reducing meeting load.",
            ];
        }

        if ($afterHoursTotal > 30) {
            return [
                'severity' => 2,
                'reason'   => "High after-hours email activity ({$afterHoursTotal} emails) — work-life balance at risk.",
            ];
        }

        return null;
    }

    // ── Focus Window Recommendation ────────────────────────────────────────

    public function recommendFocusWindows(array $weekCalendarMetrics): array
    {
        $windows     = [];
        $workHours   = range(self::WORK_START, self::WORK_END - 1);
        $hourCounts  = array_fill(self::WORK_START, self::WORK_END - self::WORK_START, 0);

        foreach ($weekCalendarMetrics as $dayMetrics) {
            foreach ($dayMetrics['meeting_slots'] ?? [] as $slot) {
                $startH = (int) explode(':', $slot['start'])[0];
                $endH   = (int) explode(':', $slot['end'])[0];
                for ($h = $startH; $h < $endH && $h < self::WORK_END; $h++) {
                    if (isset($hourCounts[$h])) $hourCounts[$h]++;
                }
            }
        }

        // Find 2-hour blocks with fewest meetings
        $blocks = [];
        for ($h = self::WORK_START; $h <= self::WORK_END - 2; $h++) {
            $load         = ($hourCounts[$h] ?? 0) + ($hourCounts[$h + 1] ?? 0);
            $blocks[$h]   = $load;
        }

        asort($blocks);
        $top = array_slice(array_keys($blocks), 0, 2, true);

        foreach ($top as $startH) {
            $windows[] = sprintf(
                '%s–%s',
                Carbon::today()->setHour($startH)->format('g A'),
                Carbon::today()->setHour($startH + 2)->format('g A')
            );
        }

        return $windows;
    }

    // ── Plain-English Insights ────────────────────────────────────────────

    public function generateInsights(array $em, array $cm, array $scores): array
    {
        $insights    = [];
        $score       = $scores['score'];
        $meetingHrs  = round(($cm['meeting_minutes'] ?? 0) / 60, 1);
        $focusHrs    = round(($cm['focus_time_minutes'] ?? 0) / 60, 1);
        $received    = $em['received_count'] ?? 0;
        $afterHours  = $em['after_hours_count'] ?? 0;
        $resp        = $em['avg_response_hours'] ?? 0;
        $b2b         = $cm['back_to_back_count'] ?? 0;
        $peak        = $em['peak_hour'] ?? null;

        // Overall score
        $insights[] = match (true) {
            $score >= 80 => ['type' => 'success', 'icon' => '🏆', 'text' => "Excellent productivity score: {$score}/100. Keep it up!"],
            $score >= 60 => ['type' => 'info',    'icon' => '👍', 'text' => "Good score: {$score}/100. A few tweaks could push you higher."],
            $score >= 40 => ['type' => 'warning',  'icon' => '⚡', 'text' => "Fair score: {$score}/100. Review your meeting load and email habits."],
            default      => ['type' => 'danger',   'icon' => '🚨', 'text' => "Low score: {$score}/100. Consider blocking focus time and reducing after-hours activity."],
        };

        // Meeting load
        if ($meetingHrs > 5)       $insights[] = ['type'=>'warning', 'icon'=>'📅', 'text'=>"Heavy meeting day — {$meetingHrs}hrs in meetings. Try blocking focus time tomorrow."];
        elseif ($meetingHrs > 2)   $insights[] = ['type'=>'info',    'icon'=>'📅', 'text'=>"Moderate meeting load — {$meetingHrs}hrs today."];
        elseif ($meetingHrs === 0.0) $insights[] = ['type'=>'success', 'icon'=>'📅', 'text'=>"Meeting-free day — great opportunity for deep work!"];

        // Back-to-back
        if ($b2b > 3)      $insights[] = ['type'=>'warning', 'icon'=>'⏱', 'text'=>"{$b2b} back-to-back meetings — no recovery time. Add 5-min buffers in your calendar."];
        elseif ($b2b > 1)  $insights[] = ['type'=>'info',    'icon'=>'⏱', 'text'=>"{$b2b} back-to-back meetings today."];

        // Focus time
        if ($focusHrs >= 3)                       $insights[] = ['type'=>'success', 'icon'=>'🎯', 'text'=>"Great — {$focusHrs}hrs of uninterrupted focus time today."];
        elseif ($focusHrs < 1 && $meetingHrs > 0) $insights[] = ['type'=>'warning', 'icon'=>'🎯', 'text'=>"Less than 1hr of focus time. Block time on your calendar before tomorrow's meetings fill up."];

        // Email volume
        if ($received > 60)      $insights[] = ['type'=>'warning', 'icon'=>'📬', 'text'=>"High email volume — {$received} received. Consider inbox rules or batching replies."];
        elseif ($received > 30)  $insights[] = ['type'=>'info',    'icon'=>'📬', 'text'=>"{$received} emails received today."];
        elseif ($received === 0) $insights[] = ['type'=>'info',    'icon'=>'📬', 'text'=>"No emails received yet — try syncing again later today."];

        // After hours
        if ($afterHours > 10)    $insights[] = ['type'=>'danger',  'icon'=>'🌙', 'text'=>"{$afterHours} emails outside work hours — strong burnout signal. Set boundaries."];
        elseif ($afterHours > 4) $insights[] = ['type'=>'warning', 'icon'=>'🌙', 'text'=>"{$afterHours} after-hours emails. Try disconnecting after {$this->formatHour(self::WORK_END)}."];

        // Response time
        if ($resp > 0 && $resp < 2)  $insights[] = ['type'=>'success', 'icon'=>'⚡', 'text'=>"Fast responder — average reply time under 2hrs."];
        elseif ($resp > 8)           $insights[] = ['type'=>'info',    'icon'=>'⚡', 'text'=>"Average response time is {$resp}hrs. Batch replies to improve this."];

        // Peak hour
        if ($peak !== null) {
            $insights[] = ['type'=>'info', 'icon'=>'📊', 'text'=>"Your busiest inbox hour is {$this->formatHour($peak)}."];
        }

        // Sub-score tips
        if ($scores['email_score'] < 50)    $insights[] = ['type'=>'tip', 'icon'=>'💡', 'text'=>"Email tip: Try a \"no-email before 9am\" rule to protect your mornings."];
        if ($scores['calendar_score'] < 50) $insights[] = ['type'=>'tip', 'icon'=>'💡', 'text'=>"Calendar tip: Aim for no more than 3hrs of meetings per day."];
        if ($scores['balance_score'] < 50)  $insights[] = ['type'=>'tip', 'icon'=>'💡', 'text'=>"Balance tip: Schedule one full focus block each day before checking email."];

        return $insights;
    }

    private function formatHour(int $hour): string
    {
        return Carbon::today()->setHour($hour)->format('g A');
    }
}