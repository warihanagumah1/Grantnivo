<?php

namespace Tests\Feature\Admin;

use App\Actions\Grants\SaveOpportunity;
use App\Enums\OpportunityStatus;
use App\Livewire\Admin\Opportunities;
use App\Livewire\Admin\OpportunityForm;
use App\Livewire\Admin\Overview;
use App\Models\GrantOpportunity;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class V1AdminTest extends TestCase
{
    use RefreshDatabase;

    /** @return array<string, mixed> */
    private function fields(): array
    {
        return ['title' => 'Community Fund', 'funder' => 'Example Foundation', 'sector' => 'Education',
            'countries' => 'Ghana, Kenya, Ghana', 'funding_type' => 'Grant', 'status' => 'active',
            'maximum_award' => 250000, 'currency' => 'USD', 'deadline' => today()->addMonth()->format('Y-m-d'),
            'summary' => 'Support for community programmes.', 'description' => 'Read the eligibility requirements.',
            'image_url' => 'https://example.org/image.jpg', 'image_alt' => 'Community programme',
            'funder_url' => 'https://example.org', 'learn_more_url' => 'https://example.org/grants',
            'opportunity_url' => 'https://example.org/apply'];
    }

    public function test_only_staff_can_access_every_admin_page_and_component(): void
    {
        foreach (['admin.overview', 'admin.opportunities', 'admin.analytics', 'admin.waitlist', 'admin.opportunities.create'] as $route) {
            $this->get(route($route))->assertRedirect(route('login'));
        }
        $ordinaryUser = User::factory()->create();
        $this->actingAs($ordinaryUser);
        foreach (['admin.overview', 'admin.opportunities', 'admin.analytics', 'admin.waitlist', 'admin.opportunities.create'] as $route) {
            $this->get(route($route))->assertForbidden();
        }
        Livewire::test(Overview::class)->assertForbidden();
        $this->actingAs(User::factory()->admin()->create());
        foreach (['admin.overview', 'admin.opportunities', 'admin.analytics', 'admin.waitlist', 'admin.opportunities.create'] as $route) {
            $this->get(route($route))->assertOk();
        }
    }

    public function test_staff_can_publish_edit_archive_and_restore_an_opportunity(): void
    {
        $staff = User::factory()->admin()->create();
        Livewire::actingAs($staff)->test(OpportunityForm::class)->set('form', $this->fields())
            ->call('save')->assertHasNoErrors()->assertRedirect(route('admin.opportunities'));
        $opportunity = GrantOpportunity::query()->sole();
        $this->assertSame(['Ghana', 'Kenya'], $opportunity->eligible_countries);
        $this->assertDatabaseHas('admin_audit_logs', ['action' => 'opportunity.created', 'user_id' => $staff->id]);
        $this->get(route('opportunities.show', $opportunity->slug))->assertOk()->assertSee('Community Fund');
        Livewire::test(OpportunityForm::class, ['grantOpportunity' => $opportunity])
            ->set('form.title', 'Updated fund')->call('save')->assertHasNoErrors();
        $this->assertSame('community-fund', $opportunity->fresh()->slug);
        $this->assertSame('Updated fund', $opportunity->fresh()->title);
        Livewire::test(Opportunities::class)->call('archive', $opportunity->id)->assertHasNoErrors();
        $this->assertSoftDeleted($opportunity);
        $this->get(route('opportunities.show', $opportunity->slug))->assertNotFound();
        Livewire::test(Opportunities::class)->call('restore', $opportunity->id)->assertHasNoErrors();
        $this->get(route('opportunities.show', $opportunity->slug))->assertOk();
        $this->assertDatabaseHas('admin_audit_logs', ['action' => 'opportunity.restored']);
    }

    public function test_invalid_data_and_unsafe_urls_cannot_be_published(): void
    {
        Livewire::actingAs(User::factory()->admin()->create())->test(OpportunityForm::class)
            ->set('form', array_replace($this->fields(), ['title' => '', 'maximum_award' => -1, 'deadline' => 'not-a-date', 'opportunity_url' => 'javascript:alert(1)', 'image_url' => 'http://example.org/photo.jpg']))
            ->call('save')->assertHasErrors(['form.title', 'form.maximum_award', 'form.deadline', 'form.opportunity_url', 'form.image_url']);
        $this->assertDatabaseCount('grant_opportunities', 0);
    }

    public function test_optional_fields_can_be_blank_and_duplicate_titles_get_distinct_slugs(): void
    {
        $staff = User::factory()->admin()->create();
        $data = array_replace($this->fields(), ['maximum_award' => '', 'deadline' => '', 'image_url' => '', 'image_alt' => '']);
        $first = app(SaveOpportunity::class)->handle($staff, $data);
        $second = app(SaveOpportunity::class)->handle($staff, $data);
        $this->assertNull($first->maximum_award);
        $this->assertNull($first->deadline);
        $this->assertNotSame($first->slug, $second->slug);
    }

    public function test_domain_action_denies_non_admin_even_without_http_middleware(): void
    {
        $this->expectException(AuthorizationException::class);
        app(SaveOpportunity::class)->handle(User::factory()->create(), $this->fields());
    }

    public function test_revoked_staff_cannot_mutate_a_previously_open_component(): void
    {
        $staff = User::factory()->admin()->create();
        $component = Livewire::actingAs($staff)->test(OpportunityForm::class)->set('form', $this->fields());
        $staff->forceFill(['is_admin' => false])->save();
        $component->call('save')->assertForbidden();
        $this->assertDatabaseCount('grant_opportunities', 0);
    }

    public function test_non_admin_cannot_sign_in_or_register_or_mass_assign_staff_access(): void
    {
        $user = User::factory()->create();
        $this->post(route('login.store'), ['email' => $user->email, 'password' => 'password'])->assertSessionHasErrors('email');
        $this->assertGuest();
        $user->fill(['is_admin' => true])->save();
        $this->assertFalse($user->fresh()->is_admin);
        $this->get('/register')->assertNotFound();
        $this->post('/admin/register', [])->assertNotFound();
        foreach (['/v2', '/dashboard', '/brain/documents', '/organisation/settings'] as $path) {
            $this->get($path)->assertNotFound();
        }
    }

    public function test_drafts_never_leak_through_invalid_public_filters(): void
    {
        $opportunity = app(SaveOpportunity::class)->handle(User::factory()->admin()->create(), array_replace($this->fields(), ['status' => 'draft', 'title' => 'Secret draft']));
        $this->get('/opportunities?status=unexpected')->assertOk()->assertDontSee('Secret draft');
        $this->get(route('opportunities.show', $opportunity->slug))->assertNotFound();
        $this->get(route('opportunities.visit', [$opportunity->slug, 'apply']))->assertNotFound();
    }

    public function test_closed_and_expired_grants_remain_readable_but_cannot_accept_applications(): void
    {
        $staff = User::factory()->admin()->create();
        foreach ([['status' => OpportunityStatus::Closed->value], ['deadline' => today()->subDay()->format('Y-m-d')]] as $overrides) {
            $opportunity = app(SaveOpportunity::class)->handle($staff, array_replace($this->fields(), $overrides));
            $this->get(route('opportunities.show', $opportunity->slug))->assertOk()->assertSee('Applications are closed.')->assertDontSee('Apply on funder site');
            $this->get(route('opportunities.visit', [$opportunity->slug, 'apply']))->assertStatus(410);
        }
        $this->assertSame(0, GrantOpportunity::query()->open()->count());
        $this->assertSame(2, GrantOpportunity::query()->closed()->count());
    }
}
