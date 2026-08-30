<?php

namespace Tests\Feature\Marketing;

use App\Actions\Grants\SaveOpportunity;
use App\Enums\OpportunityStatus;
use App\Livewire\Admin\OpportunityForm;
use App\Models\GrantOpportunity;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\TestCase;

class OpportunityUuidTest extends TestCase
{
    // Exercise schema changes outside a test transaction, as SQLite migrations run in practice.
    use DatabaseMigrations;

    private function opportunity(array $attributes = []): GrantOpportunity
    {
        return GrantOpportunity::create(array_replace([
            'title' => 'Community solar', 'slug' => 'community-solar', 'funder' => 'Foundation',
            'summary' => 'Summary', 'status' => OpportunityStatus::Active,
            'opportunity_url' => 'https://example.org/apply',
        ], $attributes));
    }

    public function test_generated_links_use_uuids_and_slug_links_redirect(): void
    {
        $grant = $this->opportunity();
        $this->assertTrue(Str::isUuid($grant->uuid));
        $this->assertSame($grant->uuid, $grant->getRouteKey());
        $this->get('/opportunities/'.$grant->uuid)->assertOk()->assertSee('Community solar');
        $this->get('/opportunities/community-solar')->assertStatus(301)->assertRedirect(route('opportunities.show', $grant));
        $this->get('/opportunities/community-solar/visit/apply')->assertStatus(301)->assertRedirect(route('opportunities.visit', [$grant, 'apply']));
        $this->get(route('opportunities.visit', [$grant, 'apply']))->assertRedirect('https://example.org/apply');
        $this->get('/opportunities')->assertSee(route('opportunities.show', $grant), false)->assertDontSee('href="'.url('/opportunities/community-solar').'"', false);
        $this->get('/opportunities/'.Str::uuid7())->assertNotFound();
    }

    public function test_uuid_and_legacy_links_never_expose_private_grants(): void
    {
        foreach ([OpportunityStatus::Draft, OpportunityStatus::Archived] as $status) {
            $grant = $this->opportunity(['slug' => $status->value, 'status' => $status]);
            foreach ([$grant->uuid, $grant->slug] as $key) {
                $this->get('/opportunities/'.$key)->assertNotFound();
                $this->get('/opportunities/'.$key.'/visit/apply')->assertNotFound();
            }
        }
        $grant = $this->opportunity();
        $grant->delete();
        $this->get('/opportunities/'.$grant->uuid)->assertNotFound();
        $this->get('/opportunities/'.$grant->slug)->assertNotFound();
    }

    public function test_admin_links_use_uuids_and_old_numeric_links_remain_staff_only(): void
    {
        $grant = $this->opportunity();
        $url = route('admin.opportunities.edit', $grant);
        $this->assertStringContainsString('/'.$grant->uuid.'/edit', $url);
        $this->get($url)->assertRedirect(route('login'));
        $this->get('/admin/opportunities/'.$grant->id.'/edit')->assertRedirect(route('login'));
        $this->actingAs(User::factory()->admin()->create());
        $this->get($url)->assertOk();
        $this->get('/admin/opportunities/'.$grant->id.'/edit')->assertStatus(301)->assertRedirect($url);
    }

    public function test_duplicate_titles_and_edits_keep_distinct_stable_uuids(): void
    {
        $staff = User::factory()->admin()->create();
        $input = ['title' => 'Same title', 'status' => 'draft', 'currency' => 'USD', 'funding_type' => 'Grant'];
        $first = app(SaveOpportunity::class)->handle($staff, $input);
        $second = app(SaveOpportunity::class)->handle($staff, $input);
        $this->assertNotSame($first->uuid, $second->uuid);
        $this->assertNotSame(route('opportunities.show', $first), route('opportunities.show', $second));
        Livewire::actingAs($staff)->test(OpportunityForm::class, ['grantOpportunity' => $first])
            ->set('form.title', 'Renamed')->set('form.uuid', $second->uuid)->call('saveDraft')->assertHasNoErrors();
        $this->assertSame($first->uuid, $first->fresh()->uuid);
        $this->assertSame('Renamed', $first->fresh()->title);
    }

    public function test_migration_backfills_all_existing_rows_and_preserves_relationships(): void
    {
        $grant = $this->opportunity();
        $eventId = DB::table('opportunity_events')->insertGetId(['grant_opportunity_id' => $grant->id, 'session_hash' => str_repeat('a', 64), 'kind' => 'view', 'event_date' => today(), 'created_at' => now()]);
        $migration = require database_path('migrations/2026_08_30_150000_add_opportunity_uuids.php');
        $migration->down();
        $archivedId = DB::table('grant_opportunities')->insertGetId(['title' => 'Old archived grant', 'slug' => 'archived', 'funder' => 'Funder', 'summary' => '', 'status' => 'archived', 'deleted_at' => now()]);
        $migration->up();
        $rows = DB::table('grant_opportunities')->get();
        $this->assertCount(2, $rows);
        $this->assertCount(2, $rows->pluck('uuid')->unique());
        foreach ($rows as $row) {
            $this->assertTrue(Str::isUuid($row->uuid));
        }
        $this->assertDatabaseHas('grant_opportunities', ['id' => $archivedId, 'slug' => 'archived']);
        $this->assertDatabaseHas('opportunity_events', ['id' => $eventId, 'grant_opportunity_id' => $grant->id]);
    }

    public function test_database_enforces_uuid_uniqueness(): void
    {
        $first = $this->opportunity();
        $second = $this->opportunity(['slug' => 'second']);
        $this->expectException(QueryException::class);
        DB::table('grant_opportunities')->where('id', $second->id)->update(['uuid' => $first->uuid]);
    }
}
