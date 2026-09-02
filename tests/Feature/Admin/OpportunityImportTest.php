<?php

namespace Tests\Feature\Admin;

use App\Actions\Grants\ImportOpportunities;
use App\Enums\OpportunityStatus;
use App\Livewire\Admin\OpportunityForm;
use App\Livewire\Admin\OpportunityImport;
use App\Models\GrantOpportunity;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\TestCase;

class OpportunityImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_import_and_template_are_staff_only(): void
    {
        foreach (['admin.opportunities.import', 'admin.opportunities.import-template'] as $route) {
            $this->get(route($route))->assertRedirect(route('login'));
        }
        $this->actingAs(User::factory()->create());
        foreach (['admin.opportunities.import', 'admin.opportunities.import-template'] as $route) {
            $this->get(route($route))->assertForbidden();
        }
        Livewire::test(OpportunityImport::class)->assertForbidden();
        $this->actingAs(User::factory()->admin()->create());
        $this->get(route('admin.opportunities.import'))->assertOk();
        $this->get(route('admin.opportunities.import-template'))->assertDownload('grantnivo-opportunities-template.csv');
    }

    public function test_bulk_import_always_creates_drafts_and_accepts_bom_quoted_lists_and_multiline_text(): void
    {
        $csv = "\xEF\xBB\xBFtitle,sector,countries,summary,status\nFirst grant,\"Education, Health\",\"Ghana, Kenya\",\"First paragraph\nSecond paragraph\",active\nSecond grant,Climate,Kenya,<p><strong>Formatted</strong></p>,closed\n";
        Livewire::actingAs(User::factory()->admin()->create())->test(OpportunityImport::class)
            ->set('file', UploadedFile::fake()->createWithContent('grants.csv', $csv))
            ->call('import')->assertHasNoErrors()->assertRedirect(route('admin.opportunities', ['status' => 'draft']));
        $this->assertDatabaseCount('grant_opportunities', 2);
        $this->assertDatabaseCount('admin_audit_logs', 2);
        foreach (GrantOpportunity::all() as $grant) {
            $this->assertSame(OpportunityStatus::Draft, $grant->status);
            $this->get(route('opportunities.show', $grant))->assertNotFound();
        }
        $this->assertSame(['Education', 'Health'], GrantOpportunity::first()->thematic_sectors);
        $this->assertSame(['Ghana', 'Kenya'], GrantOpportunity::first()->eligible_countries);
        $this->assertStringContainsString('<br', GrantOpportunity::first()->summary);
        $this->assertStringContainsString('<strong>Formatted</strong>', GrantOpportunity::latest('id')->first()->summary);
    }

    public function test_invalid_rows_roll_back_all_grants_and_audit_records_with_row_errors(): void
    {
        $csv = "title,maximum_award,image_url\nValid grant,500,\nBad amount,-1,\nBad image,,javascript:alert(1)\n";
        Livewire::actingAs(User::factory()->admin()->create())->test(OpportunityImport::class)
            ->set('file', UploadedFile::fake()->createWithContent('grants.csv', $csv))
            ->call('import')->assertHasErrors(['rows.3', 'rows.4'])->assertSee('Nothing was imported.');
        $this->assertDatabaseCount('grant_opportunities', 0);
        $this->assertDatabaseCount('admin_audit_logs', 0);
    }

    public function test_csv_schema_and_batch_limits_are_enforced(): void
    {
        $staff = User::factory()->admin()->create();
        $examples = [
            '', "funder\nMissing title", "title,title\nDuplicate,Duplicate", "title,image_path\nSecret,/private/key",
            "title,countries\nUnquoted,Ghana,Kenya", "title\n", "title\n".str_repeat("Grant\n", 251),
            "title\nBad UTF8 \xFF",
        ];
        foreach ($examples as $csv) {
            try {
                app(ImportOpportunities::class)->handle($staff, UploadedFile::fake()->createWithContent('grants.csv', $csv));
                $this->fail('Invalid CSV accepted.');
            } catch (ValidationException $exception) {
                $this->assertNotEmpty($exception->errors());
            }
        }
        $this->assertDatabaseCount('grant_opportunities', 0);
    }

    public function test_minimal_csv_works_and_domain_action_rejects_non_staff(): void
    {
        $file = UploadedFile::fake()->createWithContent('grants.csv', "title\nA draft title\n");
        $this->assertSame(1, app(ImportOpportunities::class)->handle(User::factory()->admin()->create(), $file));
        $this->expectException(AuthorizationException::class);
        app(ImportOpportunities::class)->handle(User::factory()->create(), $file);
    }

    public function test_bulk_import_is_nested_under_opportunities_without_a_separate_navigation_item(): void
    {
        $response = $this->actingAs(User::factory()->admin()->create())
            ->get(route('admin.opportunities.import'))
            ->assertOk();

        $document = new \DOMDocument;
        @$document->loadHTML($response->getContent());
        $xpath = new \DOMXPath($document);
        $navigation = '//nav[@aria-label="Admin navigation"]';

        $this->assertSame(0, $xpath->query($navigation.'//a[@href="'.route('admin.opportunities.import').'"]')->length);
        $this->assertSame(1, $xpath->query($navigation.'//a[@href="'.route('admin.opportunities').'" and @aria-current="page"]')->length);
    }

    public function test_bulk_import_is_discoverable_and_template_formatting_survives_publication(): void
    {
        $staff = User::factory()->admin()->create();
        $this->actingAs($staff);
        foreach (['admin.overview', 'admin.opportunities', 'admin.opportunities.create'] as $route) {
            $this->get(route($route))->assertOk()->assertSee('Bulk upload')->assertSee(route('admin.opportunities.import'), false);
        }
        $this->get(route('admin.opportunities.import'))->assertSee('How to format summary and additional details');
        $csv = file_get_contents(resource_path('examples/opportunities.csv'));
        $this->assertStringContainsString('## Who can apply', $csv);
        app(ImportOpportunities::class)->handle($staff, UploadedFile::fake()->createWithContent('template.csv', $csv));
        $grant = GrantOpportunity::sole();
        $this->assertTrue(Str::isUuid($grant->uuid));
        $this->assertSame(OpportunityStatus::Draft, $grant->status);
        Livewire::test(OpportunityForm::class, ['grantOpportunity' => $grant])->call('save')->assertHasNoErrors();
        $this->get(route('opportunities.show', $grant))->assertOk()
            ->assertSee('<strong>community-led education</strong>', false)
            ->assertSee('<em>locally led organisations</em>', false)
            ->assertSee('<h2>Who can apply</h2>', false)
            ->assertSee('<h3>How to apply</h3>', false)
            ->assertSee('<ol>', false)
            ->assertSee('<li>Read the programme guidance.</li>', false);
    }

    public function test_markdown_content_limits_roll_back_the_whole_batch(): void
    {
        $csv = "title,summary\nValid,**Good**\nToo long,".str_repeat('a', 2001)."\n";
        Livewire::actingAs(User::factory()->admin()->create())->test(OpportunityImport::class)
            ->set('file', UploadedFile::fake()->createWithContent('grants.csv', $csv))
            ->call('import')->assertHasErrors(['rows.3']);
        $this->assertDatabaseCount('grant_opportunities', 0);
        $this->assertDatabaseCount('admin_audit_logs', 0);
    }
}
