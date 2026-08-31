<?php

namespace App\Services\Analytics;

use App\Models\GrantOpportunity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class RecordEngagement
{
    public function record(Request $request, Response $response, ?GrantOpportunity $opportunity = null, ?string $kind = null): void
    {
        if ($request->cookie('grantnivo_analytics') !== 'accepted' || $request->user()?->is_admin || $request->header('DNT') === '1' || $request->header('Sec-GPC') === '1'
            || preg_match('/bot|crawler|spider|headless|preview/i', $request->userAgent() ?? '')
            || ! $request->hasSession()) {
            return;
        }

        try {
            $visitor = $request->cookie('grantnivo_visitor');
            if (! is_string($visitor) || ! Str::isUuid($visitor)) {
                $visitor = (string) Str::uuid();
                $response->headers->setCookie(cookie('grantnivo_visitor', $visitor, 60 * 24 * 365, '/', null, $request->isSecure(), true, false, 'lax'));
            }
            $sessionHash = hash_hmac('sha256', $request->session()->getId(), config('app.key'));
            DB::table('site_visits')->insertOrIgnore([
                'visitor_hash' => hash_hmac('sha256', $visitor, config('app.key')),
                'session_hash' => $sessionHash,
                'created_at' => now(),
            ]);
            if ($opportunity && in_array($kind, ['view', 'funder', 'learn_more', 'apply'], true)) {
                DB::table('opportunity_events')->insertOrIgnore([
                    'grant_opportunity_id' => $opportunity->id,
                    'session_hash' => $sessionHash,
                    'kind' => $kind,
                    'event_date' => today()->toDateString(),
                    'created_at' => now(),
                ]);
            }
        } catch (Throwable $exception) {
            // Analytics must never prevent a visitor from reading or applying for a grant.
            report($exception);
        }
    }
}
