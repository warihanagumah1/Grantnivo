<?php

namespace App\Http\Controllers;

use App\Models\GrantOpportunity;
use App\Services\Analytics\RecordEngagement;
use App\Services\Grants\OpportunityLinks;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class OpportunityLinkController
{
    public function __invoke(Request $request, GrantOpportunity $grantOpportunity, string $kind, RecordEngagement $engagement, OpportunityLinks $links): RedirectResponse
    {
        abort_unless($grantOpportunity->isPublic(), 404);
        abort_if($kind === 'apply' && ! $grantOpportunity->isOpen(), 410, 'Applications for this opportunity are closed.');
        $url = $links->destination($grantOpportunity, $kind);
        abort_if($url === null, 404);
        $response = redirect()->away($url);
        $engagement->record($request, $response, $grantOpportunity, $kind);

        return $response;
    }
}
