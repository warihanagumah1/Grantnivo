<?php

namespace Tests\Feature\Marketing;

use App\Actions\Marketing\JoinWaitlist;
use App\Livewire\Marketing\Waitlist;
use App\Models\WaitlistEntry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\TestCase;

class PolicyConsentTest extends TestCase
{
    use RefreshDatabase;

    public function test_policies_and_footer_links_are_public_and_incomplete_details_are_flagged(): void
    {
        config(['legal.reviewed' => false]);
        foreach (['policies.privacy', 'policies.terms', 'policies.cookies'] as $route) {
            $this->get(route($route))->assertOk()->assertSee('Draft for review.');
            $this->get('/')->assertSee(route($route), false);
        }
        $this->get(route('waitlist'))->assertOk()->assertSee('waitlist-policies')->assertSee('required', false);
        $this->assertDatabaseCount('site_visits', 0);
    }

    public function test_waitlist_requires_explicit_acceptance_even_if_client_validation_is_bypassed(): void
    {
        Livewire::test(Waitlist::class)
            ->assertSet('acceptPolicies', false)
            ->set('name', 'Test Person')->set('email', 'test@example.org')->set('organisation', 'Test Organisation')
            ->call('join')->assertHasErrors('acceptPolicies')->assertSet('submitted', false);

        try {
            app(JoinWaitlist::class)->handle(['name' => 'Test', 'email' => 'test@example.org', 'organisation' => 'Test'], 'test');
            $this->fail('Missing consent must not create a signup.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('acceptPolicies', $exception->errors());
        }
        $this->assertDatabaseCount('waitlist_entries', 0);
    }

    public function test_consent_records_server_time_and_versions_without_enabling_analytics(): void
    {
        $this->travelTo(now()->startOfSecond());
        config(['legal.privacy_version' => 'privacy-test', 'legal.terms_version' => 'terms-test', 'legal.consent_version' => 'consent-test']);
        app(JoinWaitlist::class)->handle([
            'name' => 'Test', 'email' => 'TEST@example.org', 'organisation' => 'Test', 'acceptPolicies' => true,
            'consented_at' => '2000-01-01', 'privacy_policy_version' => 'forged',
        ], 'test');
        $entry = WaitlistEntry::query()->sole();
        $this->assertTrue($entry->consented_at->equalTo(now()));
        $this->assertSame('privacy-test', $entry->privacy_policy_version);
        $this->assertSame('terms-test', $entry->terms_version);
        $this->assertSame('consent-test', $entry->consent_version);
        $this->get('/')->assertCookieMissing('grantnivo_visitor')->assertCookieMissing('grantnivo_analytics');
        $this->assertDatabaseCount('site_visits', 0);
    }

    public function test_duplicate_signup_does_not_rewrite_old_consent_or_invent_consent_for_legacy_entries(): void
    {
        foreach ([null, '2025-01-01'] as $version) {
            $email = $version ? 'existing@example.org' : 'legacy@example.org';
            $entry = WaitlistEntry::query()->create([
                'name' => 'Original', 'email' => $email, 'organisation' => 'Original', 'subscribed_at' => now(),
                'consented_at' => $version, 'privacy_policy_version' => $version, 'terms_version' => $version, 'consent_version' => $version,
            ]);
            $before = $entry->fresh()->getAttributes();
            Livewire::test(Waitlist::class)->set('name', 'Changed')->set('email', strtoupper($email))
                ->set('organisation', 'Changed')->set('acceptPolicies', true)
                ->call('join')->assertHasErrors('email')->assertSet('submitted', false);
            $this->assertSame($before, $entry->fresh()->getAttributes());
        }
    }

    public function test_analytics_is_off_without_an_accepted_preference(): void
    {
        $this->get('/')->assertOk()->assertCookieMissing('grantnivo_visitor');
        foreach (['declined', 'invalid'] as $choice) {
            $this->withCookie('grantnivo_analytics', $choice)->get('/')->assertOk()->assertCookieMissing('grantnivo_visitor');
        }
        $this->assertDatabaseCount('site_visits', 0);
        $this->withCookie('grantnivo_analytics', 'accepted')->get('/')->assertOk()->assertCookie('grantnivo_visitor');
        $this->assertDatabaseCount('site_visits', 1);
    }

    public function test_cookie_preferences_can_be_enabled_and_withdrawn_and_reject_invalid_choices(): void
    {
        $accepted = $this->post(route('policies.cookies.save'), ['analytics' => 'accepted'])
            ->assertRedirect(route('policies.cookies').'#preferences')->assertCookie('grantnivo_analytics', 'accepted');
        $cookie = $accepted->getCookie('grantnivo_analytics');
        $this->assertTrue($cookie->isHttpOnly());
        $this->assertSame('lax', $cookie->getSameSite());
        $this->assertGreaterThan(now()->addDays(364)->timestamp, $cookie->getExpiresTime());
        $this->withCookie('grantnivo_analytics', 'accepted')->get(route('policies.cookies'))
            ->assertOk()->assertSee('Optional analytics enabled');
        $this->post(route('policies.cookies.save'), ['analytics' => 'declined'])
            ->assertCookie('grantnivo_analytics', 'declined')->assertCookieExpired('grantnivo_visitor');
        $this->post(route('policies.cookies.save'), ['analytics' => 'arbitrary'])
            ->assertSessionHasErrors('analytics')->assertCookieMissing('grantnivo_analytics');
        $this->assertDatabaseCount('site_visits', 0);
    }
}
