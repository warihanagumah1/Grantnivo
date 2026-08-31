<?php

namespace Tests\Feature\Admin;

use App\Actions\Grants\SaveOpportunity;
use App\Livewire\Admin\Opportunities;
use App\Livewire\Admin\OpportunityPreview;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class OpportunityPreviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_draft_previews_are_staff_only_and_never_publish_the_draft(): void
    {
        $staff = User::factory()->admin()->create();
        $grant = app(SaveOpportunity::class)->handle($staff, ['title' => 'Private draft preview', 'status' => 'draft', 'funding_type' => 'Grant', 'currency' => 'USD', 'summary' => '<p>Draft <strong>details</strong>.</p>']);
        $url = route('admin.opportunities.preview', $grant);
        $this->get($url)->assertRedirect(route('login'));
        $this->actingAs(User::factory()->create())->get($url)->assertForbidden();
        Livewire::test(OpportunityPreview::class, ['grantOpportunity' => $grant])->assertForbidden();
        $this->actingAs($staff)->get($url)->assertOk()->assertSee('Admin preview.')
            ->assertSee('Private draft preview')->assertSee('<strong>details</strong>', false)
            ->assertSee('only visible to admins')->assertDontSee('Applications are closed.');
        $this->get(route('admin.opportunities', ['status' => 'draft']))->assertOk()->assertSee($url, false)->assertSee('text-green-700', false);
        $this->get(route('opportunities.show', $grant))->assertNotFound();
        $this->get(route('opportunities.index'))->assertDontSee('Private draft preview');
        $this->assertSame('draft', $grant->fresh()->status->value);
        $this->assertDatabaseCount('opportunity_events', 0);
        $component = Livewire::test(OpportunityPreview::class, ['grantOpportunity' => $grant]);
        $staff->forceFill(['is_admin' => false])->save();
        $component->call('$refresh')->assertForbidden();
    }

    public function test_success_notices_render_after_livewire_actions_and_redirects(): void
    {
        $staff = User::factory()->admin()->create();
        $grant = app(SaveOpportunity::class)->handle($staff, ['title' => 'Draft', 'status' => 'draft', 'funding_type' => 'Grant', 'currency' => 'USD']);
        Livewire::actingAs($staff)->test(Opportunities::class)->call('archive', $grant->id)
            ->assertSee('Opportunity removed')->assertSee('Dismiss success message')->assertSee('60000', false);
        $this->withSession(['success' => 'Imported 2 drafts.'])->get(route('admin.opportunities'))
            ->assertOk()->assertSee('Imported 2 drafts.')->assertSee('Dismiss success message');
    }
}
