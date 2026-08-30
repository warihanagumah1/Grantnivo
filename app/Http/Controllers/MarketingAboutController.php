<?php

namespace App\Http\Controllers;

use App\Models\GrantOpportunity;
use Illuminate\Contracts\View\View;

class MarketingAboutController extends Controller
{
    public function __invoke(): View
    {
        $sectorCount = GrantOpportunity::query()
            ->published()
            ->pluck('thematic_sectors')
            ->flatten()
            ->filter(fn (mixed $sector): bool => is_string($sector) && trim($sector) !== '')
            ->map(fn (string $sector): string => trim($sector))
            ->unique(fn (string $sector): string => mb_strtolower($sector))
            ->count();

        return view('pages.marketing.about', compact('sectorCount'));
    }
}
