<?php

namespace Tests\Feature\Marketing;

use App\Enums\OpportunityStatus;
use App\Livewire\Marketing\OpportunityLibrary;
use App\Models\GrantOpportunity;
use App\Services\Grants\OpportunityLinks;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class OpportunityPresentationTest extends TestCase
{
    use RefreshDatabase;

    private function grant(string $slug, array $fields = []): GrantOpportunity
    {
        return GrantOpportunity::create(array_replace([
            'slug' => $slug, 'title' => $slug, 'funder' => 'Foundation', 'summary' => 'Summary',
            'status' => OpportunityStatus::Active, 'thematic_sectors' => ['Education'], 'eligible_countries' => ['Ghana'],
        ], $fields));
    }

    public function test_filter_options_only_include_countries_and_sectors_from_open_or_closed_opportunities(): void
    {
        $this->grant('open-grant');
        $this->grant('closed-grant', ['status' => OpportunityStatus::Closed, 'eligible_countries' => ['Kenya'], 'thematic_sectors' => ['Health']]);
        $this->grant('expired-grant', ['deadline' => today()->subDay(), 'eligible_countries' => ['Uganda'], 'thematic_sectors' => ['Climate']]);
        $this->grant('draft-grant', ['status' => OpportunityStatus::Draft, 'eligible_countries' => ['Draft country'], 'thematic_sectors' => ['Draft sector']]);
        $this->grant('archive-grant', ['status' => OpportunityStatus::Archived, 'eligible_countries' => ['Archived country'], 'thematic_sectors' => ['Archived sector']]);
        $this->grant('future-grant', ['open_date' => today()->addMonth(), 'eligible_countries' => ['Future country'], 'thematic_sectors' => ['Future sector']]);
        $this->grant('deleted-grant', ['eligible_countries' => ['Deleted country'], 'thematic_sectors' => ['Deleted sector']])->delete();
        Livewire::test(OpportunityLibrary::class)->assertSet('countries', ['Ghana', 'Kenya', 'Uganda'])
            ->assertSet('sectors', ['Climate', 'Education', 'Health'])
            ->assertSee('open-grant')->assertDontSee('closed-grant')
            ->set('status', 'closed')->assertSee('closed-grant')->assertSee('expired-grant')->assertDontSee('open-grant')
            ->set('status', 'all')->assertSee('open-grant')->assertSee('closed-grant')->assertDontSee('future-grant');
    }

    public function test_unknown_same_count_filter_values_do_not_bypass_filtering(): void
    {
        $this->grant('visible-grant');
        Livewire::test(OpportunityLibrary::class)->set('selectedCountries', ['Unknown'])
            ->assertDontSee('visible-grant')->call('selectAllCountries')->assertSee('visible-grant')
            ->set('selectedSectors', ['Unknown'])->assertDontSee('visible-grant')
            ->call('selectAllSectors')->assertSee('visible-grant')->call('clearSectors')->assertDontSee('visible-grant');
    }

    public function test_cards_and_details_present_every_sector(): void
    {
        $grant = $this->grant('multi-sector-grant', [
            'thematic_sectors' => ['Health', 'Climate Change', 'Gender Equality', 'Poverty Reduction'],
        ]);

        $card = view('components.marketing.opportunity-card', ['opportunity' => $grant])->render();
        $this->assertStringContainsString('Health', $card);
        $this->assertStringContainsString('Climate Change', $card);
        $this->assertStringContainsString('Gender Equality', $card);
        $this->assertStringContainsString('+1 more', $card);
        $this->assertStringNotContainsString('Poverty Reduction', $card);

        $this->get(route('opportunities.show', $grant))->assertOk()
            ->assertSeeInOrder(['Health', 'Climate Change', 'Gender Equality', 'Poverty Reduction'])
            ->assertSee('Health, Climate Change, Gender Equality, Poverty Reduction');
    }

    public function test_learn_more_is_primary_and_duplicate_empty_or_unsafe_links_are_hidden(): void
    {
        $grant = $this->grant('linked-grant', ['learn_more_url' => 'https://example.org/info', 'opportunity_url' => 'https://example.org/apply', 'funder_url' => 'https://example.org']);
        $links = app(OpportunityLinks::class);
        $this->assertSame([
            ['kind' => 'learn_more', 'label' => 'Learn more'], ['kind' => 'apply', 'label' => 'Apply on funder site'], ['kind' => 'funder', 'label' => 'Funder website'],
        ], $links->visible($grant));
        $this->get(route('opportunities.show', $grant))->assertSee('Learn more')->assertDontSee('Learn more about this grant');
        $grant->update(['opportunity_url' => 'https://example.org/info/', 'funder_url' => 'https://example.org/info']);
        $this->assertCount(1, $links->visible($grant));
        $grant->update(['learn_more_url' => null, 'opportunity_url' => 'https://example.org/apply', 'funder_url' => 'javascript:alert(1)']);
        $this->assertSame([['kind' => 'apply', 'label' => 'Learn more']], $links->visible($grant));
        $grant->update(['opportunity_url' => null]);
        $this->assertSame([], $links->visible($grant));
    }

    public function test_closed_grants_can_link_to_details_but_not_to_applications(): void
    {
        $grant = $this->grant('closed', ['status' => OpportunityStatus::Closed, 'learn_more_url' => 'https://example.org/info', 'opportunity_url' => 'https://example.org/apply']);
        $this->assertSame([['kind' => 'learn_more', 'label' => 'Learn more']], app(OpportunityLinks::class)->visible($grant));
        $this->get(route('opportunities.show', $grant))->assertSee('Learn more')->assertSee('Applications are closed.')->assertDontSee('Apply on funder site');
        $this->get(route('opportunities.visit', [$grant, 'apply']))->assertStatus(410);
        $this->get(route('opportunities.visit', [$grant, 'learn_more']))->assertRedirect('https://example.org/info');
    }
}
