<?php

namespace App\Http\Controllers;

use App\Models\GrantOpportunity;
use App\Services\Grants\OpportunityLinks;
use Illuminate\Http\RedirectResponse;

class LegacyOpportunityRedirectController
{
    public function show(GrantOpportunity $legacyOpportunity): RedirectResponse
    {
        abort_unless($legacyOpportunity->isPublic(), 404);

        return redirect()->route('opportunities.show', $legacyOpportunity, 301);
    }

    public function visit(GrantOpportunity $legacyOpportunity, string $kind, OpportunityLinks $links): RedirectResponse
    {
        abort_unless($legacyOpportunity->isPublic(), 404);
        abort_if($kind === 'apply' && ! $legacyOpportunity->isOpen(), 410);
        abort_if($links->destination($legacyOpportunity, $kind) === null, 404);

        return redirect()->route('opportunities.visit', [$legacyOpportunity, $kind], 301);
    }

    public function edit(GrantOpportunity $legacyOpportunity): RedirectResponse
    {
        return redirect()->route('admin.opportunities.edit', $legacyOpportunity, 301);
    }
}
