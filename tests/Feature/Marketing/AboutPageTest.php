<?php

namespace Tests\Feature\Marketing;

use App\Enums\OpportunityStatus;
use App\Models\GrantOpportunity;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AboutPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_sector_count_reflects_distinct_published_sectors(): void
    {
        foreach ([
            ['grant', OpportunityStatus::Active, ['Education', ' Health ', '', null]],
            ['partnership', OpportunityStatus::Closed, ['education', 'Climate']],
            ['draft', OpportunityStatus::Draft, ['Private sector']],
            ['archived', OpportunityStatus::Archived, ['Archived sector']],
        ] as [$slug, $status, $sectors]) {
            GrantOpportunity::create([
                'slug' => $slug, 'title' => $slug, 'funder' => 'Test funder', 'summary' => 'Test opportunity',
                'status' => $status, 'currency' => 'USD', 'thematic_sectors' => $sectors,
                'funding_type' => $slug === 'partnership' ? 'Partnership' : 'Grant',
            ]);
        }

        $this->get(route('about'))->assertOk()->assertViewHas('sectorCount', 3);

        GrantOpportunity::where('slug', 'partnership')->firstOrFail()->delete();
        $this->get(route('about'))->assertOk()->assertViewHas('sectorCount', 2);
    }

    public function test_empty_catalogue_displays_zero_sectors(): void
    {
        $this->get(route('about'))->assertOk()->assertViewHas('sectorCount', 0);
    }
}
