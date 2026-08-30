<?php

namespace Tests\Feature\Admin;

use App\Models\GrantOpportunity;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OpportunityConfirmationTest extends TestCase
{
    use RefreshDatabase;

    public function test_opportunity_actions_use_custom_dialogs_instead_of_browser_confirmations(): void
    {
        $grant = GrantOpportunity::create(['title' => 'Community grant', 'slug' => 'community-grant', 'status' => 'active', 'funder' => 'Example Foundation', 'funding_type' => 'Grant', 'currency' => 'USD', 'summary' => 'A community grant.']);
        $this->actingAs(User::factory()->admin()->create());
        $this->get(route('admin.opportunities'))->assertOk()
            ->assertSee('Delete opportunity?')->assertSee('confirmationDialog')
            ->assertSee('<dialog', false)->assertDontSee('wire:confirm', false);
        $this->get(route('admin.opportunities.edit', $grant))->assertOk()
            ->assertSee('Move opportunity to draft?')->assertSee('<dialog', false)
            ->assertDontSee('wire:confirm', false);
        $grant->delete();
        $this->get(route('admin.opportunities', ['status' => 'archived']))->assertOk()
            ->assertSee('Restore opportunity?')->assertSee('<dialog', false)
            ->assertDontSee('wire:confirm', false);
    }
}
