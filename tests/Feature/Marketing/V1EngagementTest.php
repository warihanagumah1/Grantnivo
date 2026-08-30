<?php

namespace Tests\Feature\Marketing;

use App\Enums\OpportunityStatus;
use App\Livewire\Marketing\Waitlist;
use App\Models\GrantOpportunity;
use App\Models\User;
use App\Services\Analytics\AdminMetrics;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

class V1EngagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_waitlist_normalizes_email_deduplicates_and_preserves_original_details(): void
    {
        Livewire::test(Waitlist::class)->set('name', 'First Person')->set('email', 'TEAM@EXAMPLE.ORG')->set('organisation', 'First Organisation')->call('join')->assertHasNoErrors()->assertSet('submitted', true);
        Livewire::test(Waitlist::class)->set('name', 'Other Person')->set('email', 'team@example.org')->set('organisation', 'Other Organisation')->call('join')->assertHasNoErrors();
        $this->assertDatabaseCount('waitlist_entries', 1);
        $this->assertDatabaseHas('waitlist_entries', ['email' => 'team@example.org', 'name' => 'First Person']);
        $this->actingAs(User::factory()->admin()->create())->get(route('admin.waitlist'))->assertOk()->assertSee('First Organisation');
    }

    public function test_waitlist_validates_rate_limits_and_ignores_honeypot(): void
    {
        Livewire::test(Waitlist::class)->set('email', 'invalid')->call('join')->assertHasErrors(['name', 'email', 'organisation']);
        Livewire::test(Waitlist::class)->set('website', 'spam')->call('join')->assertSet('submitted', true);
        $this->assertDatabaseCount('waitlist_entries', 0);
        for ($i = 0; $i < 5; $i++) {
            Livewire::test(Waitlist::class)->set('name', 'Test')->set('email', 'team@example.org')->set('organisation', 'Community')->call('join');
        }
        Livewire::test(Waitlist::class)->set('name', 'Test')->set('email', 'another@example.org')->set('organisation', 'Community')->call('join')->assertHasErrors('email');
        $this->assertDatabaseCount('waitlist_entries', 1);
    }

    public function test_visits_and_all_outbound_actions_are_persisted_and_deduplicated(): void
    {
        $opportunity = GrantOpportunity::query()->create(['title' => 'Public Grant', 'slug' => 'public-grant', 'funder' => 'Funder', 'summary' => 'Summary', 'status' => OpportunityStatus::Active, 'opportunity_url' => 'https://example.org/apply', 'funder_url' => 'https://example.org', 'learn_more_url' => 'https://example.org/info']);
        $this->withSession(['test-session' => true]);
        $this->withCookie(config('session.cookie'), session()->getId());
        $this->get(route('opportunities.show', $opportunity))->assertOk();
        $this->get(route('opportunities.show', $opportunity))->assertOk();
        foreach (['apply' => '/apply', 'funder' => '', 'learn_more' => '/info'] as $kind => $path) {
            $this->get(route('opportunities.visit', [$opportunity, $kind]))->assertRedirect('https://example.org'.$path);
            $this->get(route('opportunities.visit', [$opportunity, $kind]))->assertRedirect();
        }
        $this->assertDatabaseCount('site_visits', 1);
        $this->assertDatabaseCount('opportunity_events', 4);
        $metrics = app(AdminMetrics::class)->engagement();
        $this->assertSame(1, $metrics['Total views']);
        $this->assertSame(3, $metrics['Outbound clicks']);
        $this->assertEquals(100, $metrics['Click-through rate']);
        $this->assertNotNull(DB::table('site_visits')->value('visitor_hash'));
        $this->get(route('opportunities.visit', [$opportunity, 'evil']))->assertNotFound();
    }

    public function test_staff_privacy_opt_outs_and_bots_are_not_tracked(): void
    {
        $this->withHeaders(['DNT' => '1'])->get('/')->assertOk();
        $this->flushHeaders()->withHeaders(['Sec-GPC' => '1'])->get('/')->assertOk();
        $this->flushHeaders()->withHeaders(['User-Agent' => 'Googlebot'])->get('/')->assertOk();
        $this->flushHeaders()->actingAs(User::factory()->admin()->create())->get('/')->assertOk();
        $this->assertDatabaseCount('site_visits', 0);
    }

    public function test_unconfigured_or_unsafe_legacy_links_are_not_redirected(): void
    {
        $opportunity = GrantOpportunity::query()->create(['title' => 'Grant', 'slug' => 'grant', 'funder' => 'Funder', 'summary' => 'Summary', 'status' => OpportunityStatus::Active, 'opportunity_url' => 'javascript:alert(1)']);
        $this->get(route('opportunities.visit', [$opportunity, 'apply']))->assertNotFound();
        $this->get(route('opportunities.visit', [$opportunity, 'funder']))->assertNotFound();
        $this->assertDatabaseCount('opportunity_events', 0);
    }
}
