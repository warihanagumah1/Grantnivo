<?php

namespace Tests\Feature\Admin;

use App\Actions\Grants\ImportOpportunities;
use App\Actions\Grants\SaveOpportunity;
use App\Models\User;
use App\Services\Admin\AuditLog;
use App\Services\Grants\RemoteOpportunityImage;
use GuzzleHttp\Psr7\Response;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Factory;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Tests\Concerns\FakesOpportunityImages;
use Tests\TestCase;

class RemoteOpportunityImageTest extends TestCase
{
    use FakesOpportunityImages, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->fakeOpportunityImages();
    }

    private function fields(array $overrides = []): array
    {
        return array_replace(['title' => 'Image draft', 'status' => 'draft', 'funding_type' => 'Grant', 'currency' => 'USD', 'image_source' => 'url', 'image_url' => 'https://images.example.test/photo', 'image_alt' => 'Community members'], $overrides);
    }

    public function test_url_images_are_downloaded_once_and_survive_later_edits(): void
    {
        $staff = User::factory()->admin()->create();
        $grant = app(SaveOpportunity::class)->handle($staff, $this->fields());
        $path = $grant->image_path;
        Storage::disk('public')->assertExists($path);
        $this->assertStringEndsWith('.png', $path);
        $this->assertNull($grant->image_url);
        $this->assertStringContainsString('/storage/', $grant->imageUrl());
        app(SaveOpportunity::class)->handle($staff, $this->fields(['image_source' => 'upload', 'image_url' => null]), $grant);
        $this->assertSame($path, $grant->fresh()->image_path);
        Http::assertSentCount(1);
    }

    public function test_invalid_remote_content_keeps_the_existing_image_and_grant_unchanged(): void
    {
        $staff = User::factory()->admin()->create();
        $grant = app(SaveOpportunity::class)->handle($staff, $this->fields());
        $path = $grant->image_path;
        $wide = UploadedFile::fake()->image('wide.png', 6001, 1);
        foreach ([['<html>Not an image</html>', 200], ['<svg xmlns="http://www.w3.org/2000/svg"></svg>', 200], ['', 404], [str_repeat('x', 5 * 1024 * 1024 + 1), 200], [file_get_contents($wide->getRealPath()), 200]] as [$body, $status]) {
            Http::swap(new Factory);
            Http::preventStrayRequests();
            Http::fake(['*' => Http::response($body, $status, ['Content-Type' => 'image/jpeg'])]);
            try {
                app(SaveOpportunity::class)->handle($staff, $this->fields(['title' => 'Do not save']), $grant);
                $this->fail('Unsafe download was accepted.');
            } catch (ValidationException $exception) {
                $this->assertArrayHasKey('image_url', $exception->errors());
            }
            $this->assertSame('Image draft', $grant->fresh()->title);
            Storage::disk('public')->assertExists($path);
            $this->assertCount(1, Storage::disk('public')->allFiles('opportunities'));
        }
    }

    public function test_private_targets_and_unsafe_redirects_are_never_requested(): void
    {
        foreach (['https://127.0.0.1/photo', 'https://10.0.0.1/photo', 'https://169.254.169.254/photo', 'https://100.64.0.1/photo', 'https://224.0.0.1/photo', 'https://user:password@example.test/photo', 'https://example.test:8443/photo', 'http://example.test/photo'] as $url) {
            try {
                app(RemoteOpportunityImage::class)->store($url, 'public');
                $this->fail('Private or unsafe target accepted.');
            } catch (ValidationException $exception) {
                $this->assertArrayHasKey('image_url', $exception->errors());
            }
        }
        Http::assertNothingSent();
        Http::swap(new Factory);
        Http::preventStrayRequests();
        Http::fake(['*' => Http::response('', 302, ['Location' => 'https://127.0.0.1/private'])]);
        try {
            app(RemoteOpportunityImage::class)->store('https://images.example.test/photo', 'public');
            $this->fail('Unsafe redirect accepted.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('image_url', $exception->errors());
        }
        Http::assertSentCount(1);
        $this->assertEmpty(Storage::disk('public')->allFiles());
    }

    public function test_hostname_resolving_to_private_addresses_is_blocked(): void
    {
        $this->fakeOpportunityImages(['93.184.215.14', '192.168.1.1']);
        try {
            app(RemoteOpportunityImage::class)->store('https://images.example.test/photo', 'public');
            $this->fail('Private DNS address accepted.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('image_url', $exception->errors());
        }
        Http::assertNothingSent();
    }

    public function test_public_redirects_are_followed_and_the_download_address_is_pinned(): void
    {
        $image = UploadedFile::fake()->image('photo.jpg');
        $contents = file_get_contents($image->getRealPath());
        Http::swap(new Factory);
        Http::preventStrayRequests();
        $sequence = Http::sequence()->push('', 302, ['Location' => '/final.jpg'])->push($contents);
        Http::fake(function ($request, $options) use ($sequence) {
            $this->assertSame(['images.example.test:443:93.184.215.14'], $options['curl'][CURLOPT_RESOLVE]);
            $this->assertSame(CURLPROTO_HTTPS, $options['curl'][CURLOPT_PROTOCOLS]);
            $this->assertFalse($options['allow_redirects']);
            $this->assertSame('', $options['proxy']);
            $this->assertSame(15, $options['timeout']);

            return $sequence($request);
        });
        $path = app(RemoteOpportunityImage::class)->store('https://images.example.test/photo', 'public');
        Storage::disk('public')->assertExists($path);
        Http::assertSentCount(2);
        Http::assertSent(fn ($request) => $request->url() === 'https://images.example.test/final.jpg');
    }

    public function test_download_limits_and_connection_failures_become_field_errors(): void
    {
        foreach (['headers', 'progress', 'connection'] as $failure) {
            Http::swap(new Factory);
            Http::preventStrayRequests();
            Http::fake(function ($request, $options) use ($failure) {
                if ($failure === 'headers') {
                    $options['on_headers'](new Response(200, ['Content-Length' => 6 * 1024 * 1024]));
                } elseif ($failure === 'progress') {
                    $options['progress'](0, 6 * 1024 * 1024);
                }

                return Http::failedConnection();
            });
            try {
                app(RemoteOpportunityImage::class)->store('https://images.example.test/photo', 'public');
                $this->fail('Failed download was accepted.');
            } catch (ValidationException $exception) {
                $this->assertArrayHasKey('image_url', $exception->errors());
            }
            $this->assertEmpty(Storage::disk('public')->allFiles());
        }
    }

    public function test_failed_database_write_and_failed_batch_remove_downloaded_files(): void
    {
        $staff = User::factory()->admin()->create();
        $csv = "title,image_url\nFirst,https://images.example.test/photo\n,\n";
        try {
            app(ImportOpportunities::class)->handle($staff, UploadedFile::fake()->createWithContent('grants.csv', $csv));
            $this->fail('Invalid batch accepted.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('rows.3', $exception->errors());
        }
        $this->assertDatabaseCount('grant_opportunities', 0);
        $this->assertEmpty(Storage::disk('public')->allFiles('opportunities'));
        $this->mock(AuditLog::class)->shouldReceive('record')->andThrow(new RuntimeException('Audit failed'));
        try {
            app(SaveOpportunity::class)->handle($staff, $this->fields());
            $this->fail('Expected database rollback.');
        } catch (RuntimeException $exception) {
            $this->assertSame('Audit failed', $exception->getMessage());
        }
        $this->assertDatabaseCount('grant_opportunities', 0);
        $this->assertEmpty(Storage::disk('public')->allFiles('opportunities'));
    }
}
