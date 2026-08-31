<?php

namespace Tests\Feature\Admin;

use App\Actions\Grants\SaveOpportunity;
use App\Models\User;
use Aws\MockHandler;
use Aws\Result;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use League\Flysystem\UnableToWriteFile;
use Tests\Concerns\FakesOpportunityImages;
use Tests\TestCase;

class R2OpportunityStorageTest extends TestCase
{
    use FakesOpportunityImages, RefreshDatabase;

    private function fields(array $overrides = []): array
    {
        return array_replace(['title' => 'R2 draft', 'status' => 'draft', 'funding_type' => 'Grant', 'currency' => 'USD', 'image_source' => 'upload', 'image_alt' => 'Community garden'], $overrides);
    }

    public function test_existing_local_images_survive_disk_switch_and_replacements_use_r2(): void
    {
        $this->fakeOpportunityImages();
        Storage::fake('r2');
        $staff = User::factory()->admin()->create();
        $save = app(SaveOpportunity::class);
        $grant = $save->handle($staff, $this->fields(), image: UploadedFile::fake()->image('local.png'));
        $oldPath = $grant->image_path;
        config(['filesystems.opportunity_images' => 'r2']);
        $grant = $save->handle($staff, $this->fields(['title' => 'Edited']), $grant);
        $this->assertSame('public', $grant->image_disk);
        Storage::disk('public')->assertExists($oldPath);

        $grant = $save->handle($staff, $this->fields(), $grant, UploadedFile::fake()->image('r2.png'));
        $this->assertSame('r2', $grant->image_disk);
        Storage::disk('r2')->assertExists($grant->image_path);
        Storage::disk('public')->assertMissing($oldPath);
        $uploadedPath = $grant->image_path;

        $grant = $save->handle($staff, $this->fields(['image_source' => 'url', 'image_url' => 'https://images.example.test/photo']), $grant);
        $this->assertSame('r2', $grant->image_disk);
        $this->assertNull($grant->image_url);
        Storage::disk('r2')->assertExists($grant->image_path);
        Storage::disk('r2')->assertMissing($uploadedPath);
        $this->assertSame('private', Storage::disk('r2')->getVisibility($grant->image_path));
    }

    public function test_real_s3_adapter_writes_to_the_configured_bucket_and_generates_public_domain_urls(): void
    {
        $handler = new MockHandler([new Result(['ETag' => 'test-etag'])]);
        $disk = Storage::build(array_replace(config('filesystems.disks.r2'), [
            'key' => 'test-key', 'secret' => 'test-secret', 'bucket' => 'test-images',
            'endpoint' => 'https://account.r2.cloudflarestorage.com', 'url' => 'https://images.example.test',
            'handler' => $handler,
        ]));
        Storage::set('r2', $disk);
        config(['filesystems.opportunity_images' => 'r2']);
        $grant = app(SaveOpportunity::class)->handle(User::factory()->admin()->create(), $this->fields(), image: UploadedFile::fake()->image('photo.png'));
        $command = $handler->getLastCommand();
        $this->assertSame('PutObject', $command->getName());
        $this->assertSame('test-images', $command['Bucket']);
        $this->assertSame($grant->image_path, $command['Key']);
        $this->assertSame('private', $command['ACL']);
        $this->assertSame('image/png', $command['ContentType']);
        $this->assertSame('https://images.example.test/'.$grant->image_path, $grant->imageUrl());
    }

    public function test_failed_r2_upload_does_not_change_an_existing_grant_or_delete_its_image(): void
    {
        Storage::fake('public');
        $staff = User::factory()->admin()->create();
        $save = app(SaveOpportunity::class);
        $grant = $save->handle($staff, $this->fields(), image: UploadedFile::fake()->image('local.png'));
        $oldPath = $grant->image_path;
        config(['filesystems.opportunity_images' => 'r2']);
        $failed = \Mockery::mock(FilesystemAdapter::class);
        $failed->shouldReceive('putFileAs')->once()->andThrow(UnableToWriteFile::atLocation('opportunities/test.png', 'Private provider details'));
        Storage::set('r2', $failed);
        try {
            $save->handle($staff, $this->fields(['title' => 'Do not save']), $grant, UploadedFile::fake()->image('new.png'));
            $this->fail('Expected upload validation error.');
        } catch (ValidationException $exception) {
            $this->assertSame(['The image could not be saved. Please try again.'], $exception->errors()['image']);
        }
        $this->assertSame('R2 draft', $grant->fresh()->title);
        $this->assertSame('public', $grant->fresh()->image_disk);
        Storage::disk('public')->assertExists($oldPath);
    }
}
