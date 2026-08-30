<?php

namespace Tests\Feature\Marketing;

use App\Enums\OpportunityStatus;
use App\Livewire\Marketing\Waitlist;
use App\Models\GrantOpportunity;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class HomepageTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_presents_the_platform_and_opportunity_previews(): void
    {
        GrantOpportunity::create([
            'slug' => 'community-climate-fund',
            'title' => 'Community Climate Fund',
            'funder' => 'Climate Trust',
            'summary' => 'Locally led climate resilience funding.',
            'status' => OpportunityStatus::Active,
            'deadline' => today()->addMonth(),
            'currency' => 'USD',
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('The intelligent platform for')
            ->assertSee('funding your mission.')
            ->assertSee('Organisation Brain')
            ->assertSee('Explore open funding opportunities.')
            ->assertSee('Community Climate Fund')
            ->assertSee('Responsible by design');
    }

    public function test_visitor_can_join_the_waitlist_with_organisation_details(): void
    {
        Livewire::test(Waitlist::class)
            ->set('name', 'Amara Okafor')
            ->set('email', 'amara@example.org')
            ->set('organisation', 'Community Action Network')
            ->call('join')
            ->assertHasNoErrors()
            ->assertSet('submitted', true);

        $this->assertDatabaseHas('waitlist_entries', [
            'email' => 'amara@example.org',
            'name' => 'Amara Okafor',
            'organisation' => 'Community Action Network',
            'source' => 'waitlist',
        ]);
    }
}
