<?php

namespace Tests\Concerns;

use App\Services\Grants\RemoteOpportunityImage;
use Illuminate\Http\Client\Factory;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Mockery;

trait FakesOpportunityImages
{
    protected function fakeOpportunityImages(array $addresses = ['93.184.215.14']): void
    {
        Storage::fake('public');
        Http::swap(new Factory);
        Http::preventStrayRequests();
        $image = UploadedFile::fake()->image('download.png', 640, 480);
        Http::fake(['*' => Http::response(file_get_contents($image->getRealPath()), 200, ['Content-Type' => 'image/png'])]);
        $service = Mockery::mock(RemoteOpportunityImage::class)->makePartial()->shouldAllowMockingProtectedMethods();
        $service->shouldReceive('resolveAddresses')->andReturn($addresses);
        $this->app->instance(RemoteOpportunityImage::class, $service);
    }
}
