<?php

namespace App\Services\Analytics;

use App\Models\GrantOpportunity;
use App\Models\WaitlistEntry;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class AdminMetrics
{
    /** @return array<string, int> */
    public function overview(): array
    {
        return [
            'Site visitors' => DB::table('site_visits')->distinct()->count('visitor_hash'),
            'Total visits' => DB::table('site_visits')->count(),
            'Live opportunities' => GrantOpportunity::query()->published()->count(),
            'Waitlist signups' => WaitlistEntry::query()->count(),
        ];
    }

    /** @return array<string, int|float> */
    public function engagement(): array
    {
        $counts = DB::table('opportunity_events')->selectRaw('kind, count(*) as total')->groupBy('kind')->pluck('total', 'kind');
        $views = (int) ($counts['view'] ?? 0);
        $outbound = (int) ($counts['funder'] ?? 0) + (int) ($counts['learn_more'] ?? 0) + (int) ($counts['apply'] ?? 0);
        $clickedSessions = DB::table('opportunity_events')->where('kind', 'view')->whereExists(function ($query): void {
            $query->selectRaw('1')->from('opportunity_events as clicks')
                ->whereColumn('clicks.grant_opportunity_id', 'opportunity_events.grant_opportunity_id')
                ->whereColumn('clicks.session_hash', 'opportunity_events.session_hash')
                ->whereColumn('clicks.event_date', 'opportunity_events.event_date')->where('clicks.kind', '!=', 'view');
        })->count();

        return ['Total views' => $views, 'Outbound clicks' => $outbound, 'Application clicks' => (int) ($counts['apply'] ?? 0), 'Click-through rate' => $views ? round($clickedSessions / $views * 100, 1) : 0];
    }

    /** @return Builder<GrantOpportunity> */
    public function opportunities(): Builder
    {
        return GrantOpportunity::query()->published()->withCount([
            'events as views_count' => fn ($query) => $query->where('kind', 'view'),
            'events as funder_count' => fn ($query) => $query->where('kind', 'funder'),
            'events as learn_more_count' => fn ($query) => $query->where('kind', 'learn_more'),
            'events as apply_count' => fn ($query) => $query->where('kind', 'apply'),
        ]);
    }
}
