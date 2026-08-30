<?php

namespace App\Http\Controllers;

use App\Models\GrantOpportunity;
use App\Services\Analytics\RecordEngagement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class OpportunityLinkController
{
    public function __invoke(Request $request, GrantOpportunity $grantOpportunity, string $kind, RecordEngagement $engagement): RedirectResponse
    {
        abort_unless($grantOpportunity->isPublic(), 404);
        abort_if($kind === 'apply' && ! $grantOpportunity->isOpen(), 410, 'Applications for this opportunity are closed.');
        $url = match ($kind) {
            'apply' => $grantOpportunity->opportunity_url,
            'funder' => $grantOpportunity->funder_url,
            'learn_more' => $grantOpportunity->learn_more_url,
            default => null,
        };
        abort_unless(is_string($url) && filter_var($url, FILTER_VALIDATE_URL) && in_array(parse_url($url, PHP_URL_SCHEME), ['http', 'https'], true), 404);
        $response = redirect()->away($url);
        $engagement->record($request, $response, $grantOpportunity, $kind);

        return $response;
    }
}
