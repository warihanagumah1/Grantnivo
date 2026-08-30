<?php

namespace App\Services\Grants;

use App\Models\GrantOpportunity;

class OpportunityLinks
{
    public function destination(GrantOpportunity $opportunity, string $kind): ?string
    {
        if ($kind === 'apply' && ! $opportunity->isOpen()) {
            return null;
        }
        $url = match ($kind) {
            'learn_more' => $opportunity->learn_more_url,
            'apply' => $opportunity->opportunity_url,
            'funder' => $opportunity->funder_url,
            default => null,
        };

        return is_string($url) && filter_var($url, FILTER_VALIDATE_URL) && in_array(parse_url($url, PHP_URL_SCHEME), ['https', 'http'], true) ? $url : null;
    }

    /** @return list<array{kind: string, label: string}> */
    public function visible(GrantOpportunity $opportunity): array
    {
        $links = [];
        $seen = [];
        foreach (['learn_more' => 'Learn more', 'apply' => 'Apply on funder site', 'funder' => 'Funder website'] as $kind => $label) {
            $url = $this->destination($opportunity, $kind);
            if ($url === null) {
                continue;
            }
            $parts = parse_url($url);
            if ($parts === false || ! isset($parts['scheme'], $parts['host'])) {
                continue;
            }
            $key = strtolower($parts['scheme'].'://'.$parts['host']).(isset($parts['port']) ? ':'.$parts['port'] : '').rtrim($parts['path'] ?? '', '/').(isset($parts['query']) ? '?'.$parts['query'] : '');
            if (in_array($key, $seen, true)) {
                continue;
            }
            $seen[] = $key;
            $links[] = ['kind' => $kind, 'label' => $links === [] ? 'Learn more' : $label];
        }

        return $links;
    }
}
