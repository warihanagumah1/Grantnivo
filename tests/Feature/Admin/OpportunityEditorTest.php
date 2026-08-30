<?php

namespace Tests\Feature\Admin;

use App\Actions\Grants\SaveOpportunity;
use App\Enums\OpportunityStatus;
use App\Livewire\Admin\OpportunityForm;
use App\Models\GrantOpportunity;
use App\Models\User;
use App\Services\Admin\AuditLog;
use App\Services\Grants\RichText;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use RuntimeException;
use Tests\TestCase;

class OpportunityEditorTest extends TestCase
{
    use RefreshDatabase;

    private function fields(array $overrides = []): array
    {
        return array_replace([
            'title' => 'Community opportunity', 'funder' => 'Community Foundation',
            'sector' => 'Education', 'countries' => 'Ghana, Kenya',
            'funding_type' => 'Grant', 'status' => 'active', 'currency' => 'USD',
            'summary' => '<p>Funding for <strong>local projects</strong>.</p>',
            'description' => '<h2>Eligibility</h2><ul><li>Registered NGOs</li></ul><h3>How to apply</h3><ol><li><em>Prepare your proposal</em></li></ol>',
            'image_url' => '', 'image_alt' => '',
        ], $overrides);
    }

    public function test_title_only_draft_can_be_saved_completed_and_published_later(): void
    {
        $staff = User::factory()->admin()->create();
        Livewire::actingAs($staff)->test(OpportunityForm::class)
            ->set('form.title', 'Private work in progress')->set('imageSource', 'upload')->call('saveDraft')
            ->assertHasNoErrors()->assertRedirect(route('admin.opportunities'));
        $grant = GrantOpportunity::query()->sole();
        $this->assertSame(OpportunityStatus::Draft, $grant->status);
        $this->get(route('opportunities.show', $grant))->assertNotFound();
        $this->get('/opportunities')->assertDontSee('Private work in progress');
        $this->get('/admin/opportunities?status=draft')->assertSee('Edit &amp; publish', false);

        $editor = Livewire::test(OpportunityForm::class, ['grantOpportunity' => $grant]);
        $editor->assertSet('savedStatus', 'draft')->assertSee('Publish opportunity')
            ->call('save')->assertHasErrors(['form.funder', 'form.sector', 'form.countries', 'form.summary']);
        $this->assertSame(OpportunityStatus::Draft, $grant->fresh()->status);
        $editor->set('form', $this->fields())->call('save')->assertHasNoErrors();
        $this->assertSame(OpportunityStatus::Active, $grant->fresh()->status);
        $this->assertSame('private-work-in-progress', $grant->fresh()->slug);
        $this->get(route('opportunities.show', $grant))->assertOk()
            ->assertSee('<strong>local projects</strong>', false)
            ->assertSee('<h2>Eligibility</h2>', false)
            ->assertSee('<ul><li>Registered NGOs</li></ul>', false)
            ->assertSee('<em>Prepare your proposal</em>', false);
    }

    public function test_drafts_need_a_title_and_published_grants_can_be_moved_back_to_draft(): void
    {
        $staff = User::factory()->admin()->create();
        Livewire::actingAs($staff)->test(OpportunityForm::class)->call('saveDraft')->assertHasErrors('form.title');
        $grant = app(SaveOpportunity::class)->handle($staff, $this->fields());
        Livewire::test(OpportunityForm::class, ['grantOpportunity' => $grant])->call('saveDraft')->assertHasNoErrors();
        $this->assertSame(OpportunityStatus::Draft, $grant->fresh()->status);
        $this->get(route('opportunities.show', $grant))->assertNotFound();
    }

    public function test_unsafe_html_and_attributes_are_removed_before_storage_and_display(): void
    {
        $staff = User::factory()->admin()->create();
        $unsafe = '<p onclick="alert(1)" style="position:fixed">Safe <strong>bold</strong></p><script>alert(1)</script><iframe src="https://evil.test"></iframe><img src=x onerror=alert(1)><a href="javascript:alert(1)">bad link</a>';
        $grant = app(SaveOpportunity::class)->handle($staff, $this->fields(['summary' => $unsafe]));
        foreach (['onclick', 'style=', '<script', '<iframe', '<img', 'javascript:'] as $fragment) {
            $this->assertStringNotContainsString($fragment, $grant->summary);
        }
        // Defense in depth: old/imported rows also pass through the sanitizer on output.
        $grant->update(['description' => $unsafe]);
        $this->get(route('opportunities.show', $grant))->assertOk()
            ->assertSee('<strong>bold</strong>', false)->assertDontSee('onclick="alert(1)"', false)
            ->assertDontSee('javascript:alert(1)', false)->assertDontSee('<iframe', false);
    }

    public function test_rich_text_limits_count_visible_text_and_empty_markup_cannot_publish(): void
    {
        $staff = User::factory()->admin()->create();
        $editor = Livewire::actingAs($staff)->test(OpportunityForm::class)->set('form', $this->fields());
        foreach (['<p><br></p>', '<p>&nbsp;</p>', '<script>alert(1)</script>', '<p>'.str_repeat('a', 2001).'</p>'] as $summary) {
            $editor->set('form.summary', $summary)->call('save')->assertHasErrors('form.summary');
        }
        $editor->set('form.summary', '<p><strong>'.str_repeat('a', 2000).'</strong></p>')->call('save')->assertHasNoErrors();
    }

    public function test_plain_text_and_safe_links_keep_readable_formatting(): void
    {
        $text = app(RichText::class);
        $this->assertSame('<p>First line<br />'."\n".'Second &amp; third</p>', $text->sanitize("First line\nSecond & third"));
        $safe = $text->sanitize('<p><a href="https://example.org/apply">Apply</a></p>');
        $this->assertStringContainsString('href="https://example.org/apply"', $safe);
        $this->assertSame($safe, $text->sanitize($safe));
    }

    public function test_images_upload_persist_on_edit_and_can_be_replaced_by_upload_or_url(): void
    {
        Storage::fake('public');
        $staff = User::factory()->admin()->create();
        Livewire::actingAs($staff)->test(OpportunityForm::class)->set('form', $this->fields(['image_alt' => 'Community garden']))
            ->set('imageSource', 'upload')->set('image', UploadedFile::fake()->image('photo.jpg', 640, 480))
            ->call('save')->assertHasNoErrors();
        $grant = GrantOpportunity::query()->sole();
        $firstPath = $grant->image_path;
        $this->assertNull($grant->image_url);
        Storage::disk('public')->assertExists($firstPath);
        $this->get(route('opportunities.show', $grant))->assertSee($grant->imageUrl());

        Livewire::test(OpportunityForm::class, ['grantOpportunity' => $grant])
            ->assertSet('imageSource', 'upload')->set('form.title', 'Updated title')->call('save')->assertHasNoErrors();
        $this->assertSame($firstPath, $grant->fresh()->image_path);
        Livewire::test(OpportunityForm::class, ['grantOpportunity' => $grant->fresh()])
            ->set('image', UploadedFile::fake()->image('replacement.png'))->call('save')->assertHasNoErrors();
        $secondPath = $grant->fresh()->image_path;
        $this->assertNotSame($firstPath, $secondPath);
        Storage::disk('public')->assertMissing($firstPath);
        Storage::disk('public')->assertExists($secondPath);

        Livewire::test(OpportunityForm::class, ['grantOpportunity' => $grant->fresh()])
            ->set('imageSource', 'url')->set('form.image_url', 'https://example.org/photo.jpg')->call('save')->assertHasNoErrors();
        $this->assertNull($grant->fresh()->image_path);
        $this->assertSame('https://example.org/photo.jpg', $grant->fresh()->imageUrl());
        Storage::disk('public')->assertMissing($secondPath);
    }

    public function test_invalid_uploads_and_missing_alt_text_are_rejected_without_storing_files(): void
    {
        Storage::fake('public');
        $staff = User::factory()->admin()->create();
        foreach ([UploadedFile::fake()->create('not-an-image.jpg', 100, 'text/plain'), UploadedFile::fake()->create('vector.svg', 10, 'image/svg+xml'), UploadedFile::fake()->image('large.jpg')->size(5121), UploadedFile::fake()->image('wide.jpg', 6001, 10)] as $image) {
            try {
                app(SaveOpportunity::class)->handle($staff, $this->fields(['image_source' => 'upload', 'image_alt' => 'Photo']), image: $image);
                $this->fail('Invalid image accepted.');
            } catch (ValidationException $exception) {
                $this->assertArrayHasKey('image', $exception->errors());
            }
        }
        Livewire::actingAs($staff)->test(OpportunityForm::class)->set('form', $this->fields())
            ->set('imageSource', 'upload')->set('image', UploadedFile::fake()->image('photo.jpg'))
            ->call('save')->assertHasErrors('form.image_alt');
        $this->assertCount(0, Storage::disk('public')->allFiles('opportunities'));
        $this->assertDatabaseCount('grant_opportunities', 0);
    }

    public function test_forged_storage_paths_are_ignored_and_failed_transactions_clean_new_uploads(): void
    {
        Storage::fake('public');
        $staff = User::factory()->admin()->create();
        $grant = app(SaveOpportunity::class)->handle($staff, $this->fields(['image_path' => '../../secret', 'image_disk' => 'local']));
        $this->assertNull($grant->image_path);
        $this->mock(AuditLog::class)->shouldReceive('record')->andThrow(new RuntimeException('Audit failed'));
        try {
            app(SaveOpportunity::class)->handle($staff, $this->fields(['image_source' => 'upload', 'image_alt' => 'Photo']), image: UploadedFile::fake()->image('photo.jpg'));
            $this->fail('Expected transaction failure.');
        } catch (RuntimeException $exception) {
            $this->assertSame('Audit failed', $exception->getMessage());
        }
        $this->assertCount(0, Storage::disk('public')->allFiles('opportunities'));
        $this->assertDatabaseCount('grant_opportunities', 1);
    }
}
