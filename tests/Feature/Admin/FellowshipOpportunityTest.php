<?php

namespace Tests\Feature\Admin;

use App\Actions\Grants\ImportOpportunities;
use App\Enums\OpportunityStatus;
use App\Livewire\Admin\OpportunityForm;
use App\Livewire\Marketing\OpportunityLibrary;
use App\Models\GrantOpportunity;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Livewire\Livewire;
use Tests\TestCase;

class FellowshipOpportunityTest extends TestCase
{
    use RefreshDatabase;

    public function test_fellowship_can_be_created_and_published_from_the_form(): void
    {
        Livewire::actingAs(User::factory()->admin()->create())->test(OpportunityForm::class)
            ->assertSee('<option>Fellowship</option>', false)
            ->set('form.title', 'Leadership fellowship')->set('form.funder', 'Foundation')
            ->set('form.sector', 'Education')->set('form.countries', 'Ghana')
            ->set('form.summary', '<p>Leadership programme.</p>')->set('form.funding_type', 'Fellowship')
            ->call('save')->assertHasNoErrors();
        $grant = GrantOpportunity::sole();
        $this->assertSame('Fellowship', $grant->funding_type);
        $this->assertSame(OpportunityStatus::Active, $grant->status);
        $this->get(route('opportunities.show', $grant))->assertOk()->assertSee('Fellowship');
    }

    public function test_bulk_upload_accepts_all_three_types_and_keeps_them_as_drafts(): void
    {
        $csv = "title,funding_type\nResearch fellowship,Fellowship\nCommunity grant,Grant\nCommunity partnership,Partnership\n";
        $this->assertSame(3, app(ImportOpportunities::class)->handle(User::factory()->admin()->create(), UploadedFile::fake()->createWithContent('opportunities.csv', $csv)));
        $this->assertSame(['Fellowship', 'Grant', 'Partnership'], GrantOpportunity::orderBy('id')->pluck('funding_type')->all());
        foreach (GrantOpportunity::all() as $grant) {
            $this->assertSame(OpportunityStatus::Draft, $grant->status);
        }
    }

    public function test_public_filters_distinguish_fellowships_grants_and_partnerships(): void
    {
        foreach (['Grant', 'Partnership', 'Fellowship'] as $type) {
            GrantOpportunity::create(['title' => $type.' example', 'slug' => strtolower($type), 'funder' => 'Foundation', 'summary' => 'Summary', 'status' => 'active', 'funding_type' => $type]);
        }
        $library = Livewire::test(OpportunityLibrary::class);
        foreach (['Grant', 'Partnership', 'Fellowship'] as $type) {
            $library->set('type', strtolower($type))->assertSee($type.' example');
            foreach (array_diff(['Grant', 'Partnership', 'Fellowship'], [$type]) as $other) {
                $library->assertDontSee($other.' example');
            }
        }
        $library->set('type', 'all')->assertSee('Grant example')->assertSee('Partnership example')->assertSee('Fellowship example');
    }
}
