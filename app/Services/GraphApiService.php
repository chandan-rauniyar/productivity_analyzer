<?php

namespace App\Services;

use App\Models\OAuthToken;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class GraphApiService
{
    private const BASE = 'https://graph.microsoft.com/v1.0';

    public function __construct(private readonly TokenManager $tokenManager) {}

    // ── Today email (cached) ───────────────────────────────────────────────

    public function getTodayEmailMetrics(OAuthToken $token): array
    {
        return Cache::remember(
            "graph:email-today:{$token->user_id}:" . today()->toDateString(),
            now()->addMinutes(15),
            function () use ($token): array {
                $start = Carbon::today('UTC');
                $end   = Carbon::tomorrow('UTC');
                return [
                    'inbox' => $this->fetchMessages($token, 'inbox',     'receivedDateTime', $start, $end),
                    'sent'  => $this->fetchMessages($token, 'sentitems', 'sentDateTime',     $start, $end),
                ];
            }
        );
    }

    // ── Week email (cached) ────────────────────────────────────────────────

    public function getWeekEmailMetrics(OAuthToken $token): array
    {
        return Cache::remember(
            "graph:email-week:{$token->user_id}:" . today()->toDateString(),
            now()->addMinutes(30),
            function () use ($token): array {
                $start = Carbon::now()->startOfWeek()->utc();
                $end   = Carbon::now()->endOfDay()->utc();
                return [
                    'inbox' => $this->fetchMessages($token, 'inbox',     'receivedDateTime', $start, $end),
                    'sent'  => $this->fetchMessages($token, 'sentitems', 'sentDateTime',     $start, $end),
                ];
            }
        );
    }

    // ── Today calendar (cached) ────────────────────────────────────────────

    public function getTodayCalendarEvents(OAuthToken $token): array
    {
        return Cache::remember(
            "graph:cal-today:{$token->user_id}:" . today()->toDateString(),
            now()->addMinutes(15),
            fn() => $this->fetchEvents($token, Carbon::today('UTC'), Carbon::tomorrow('UTC'))
        );
    }

    // ── Week calendar (cached) ─────────────────────────────────────────────

    public function getWeekCalendarEvents(OAuthToken $token): array
    {
        return Cache::remember(
            "graph:cal-week:{$token->user_id}:" . today()->toDateString(),
            now()->addMinutes(30),
            fn() => $this->fetchEvents(
                $token,
                Carbon::now()->startOfWeek()->utc(),
                Carbon::now()->endOfWeek()->endOfDay()->utc()
            )
        );
    }

    // ── PUBLIC: single-day fetchers for historical sync ────────────────────

    public function fetchDayMessages(
        OAuthToken $token,
        string $folder,
        string $dateField,
        CarbonInterface $start,
        CarbonInterface $end
    ): array {
        return $this->fetchMessages($token, $folder, $dateField, $start, $end);
    }

    public function fetchDayEvents(
        OAuthToken $token,
        CarbonInterface $start,
        CarbonInterface $end
    ): array {
        return $this->fetchEvents($token, $start, $end);
    }

    // ── User profile ───────────────────────────────────────────────────────

    public function getUserProfile(OAuthToken $token): array
    {
        $accessToken = $this->tokenManager->getValidAccessToken($token);
        $response    = $this->graphGet($accessToken, self::BASE . '/me', [
            '$select' => 'id,displayName,mail,jobTitle,officeLocation,department',
        ]);
        return $response->successful() ? $response->json() : [];
    }

    // ── PROTECTED: core message fetcher ───────────────────────────────────
    // protected (not private) so fetchDayMessages public wrapper works

    protected function fetchMessages(
        OAuthToken $token,
        string $folder,
        string $dateField,
        CarbonInterface $start,
        CarbonInterface $end
    ): array {
        $filter      = "{$dateField} ge {$start->format('Y-m-d\TH:i:s\Z')} and {$dateField} lt {$end->format('Y-m-d\TH:i:s\Z')}";
        $url         = self::BASE . "/me/mailFolders/{$folder}/messages";
        $accessToken = $this->tokenManager->getValidAccessToken($token);

        $response = $this->graphGet($accessToken, $url, [
            '$filter' => $filter,
            '$select' => "{$dateField},id,from,toRecipients,isRead",
            '$top'    => 200,
        ]);

        if ($response->status() === 401) {
            Log::warning('Graph 401 — force refresh', [
                'user_id' => $token->user_id,
                'folder'  => $folder,
            ]);
            $fresh    = $this->tokenManager->refreshAccessToken($token);
            $response = $this->graphGet($fresh, $url, [
                '$filter' => $filter,
                '$select' => "{$dateField},id,from,toRecipients,isRead",
                '$top'    => 200,
            ]);
        }

        $this->assertSuccessful($response, $folder);
        return $response->json('value', []);
    }

    // ── PROTECTED: core event fetcher ─────────────────────────────────────
    // protected (not private) so fetchDayEvents public wrapper works

    protected function fetchEvents(
        OAuthToken $token,
        CarbonInterface $start,
        CarbonInterface $end
    ): array {
        $url         = self::BASE . '/me/calendarView';
        $accessToken = $this->tokenManager->getValidAccessToken($token);
        $params      = [
            'startDateTime' => $start->format('Y-m-d\TH:i:s\Z'),
            'endDateTime'   => $end->format('Y-m-d\TH:i:s\Z'),
            '$select'       => 'id,subject,start,end,isAllDay,showAs,organizer,attendees,isOnlineMeeting',
            '$top'          => 100,
            '$orderby'      => 'start/dateTime asc',
        ];

        $response = $this->graphGet($accessToken, $url, $params);

        if ($response->status() === 401) {
            $fresh    = $this->tokenManager->refreshAccessToken($token);
            $response = $this->graphGet($fresh, $url, $params);
        }

        $this->assertSuccessful($response, 'calendarView');

        return collect($response->json('value', []))
            ->filter(fn($e) => ! ($e['isAllDay'] ?? false) && ($e['showAs'] ?? '') !== 'free')
            ->values()
            ->toArray();
    }

    // ── Helpers ────────────────────────────────────────────────────────────

    private function graphGet(string $token, string $url, array $params = [])
    {
        return Http::withToken($token)
            ->withHeaders(['Accept' => 'application/json'])
            ->timeout(30)
            ->get($url, $params);
    }

    private function assertSuccessful($response, string $context): void
    {
        if (! $response->successful()) {
            throw new RuntimeException(sprintf(
                'Microsoft Graph request failed [%s]: status=%d; reason=%s; body=%s',
                $context,
                $response->status(),
                $response->reason() ?: 'n/a',
                $response->body() ?: 'empty'
            ));
        }
    }
}