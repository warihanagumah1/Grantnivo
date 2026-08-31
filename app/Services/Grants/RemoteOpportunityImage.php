<?php

namespace App\Services\Grants;

use GuzzleHttp\Psr7\Uri;
use GuzzleHttp\Psr7\UriResolver;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;
use Throwable;

class RemoteOpportunityImage
{
    private const MAX_BYTES = 5 * 1024 * 1024;

    public function store(string $url, string $disk): string
    {
        $temporary = tempnam(sys_get_temp_dir(), 'grant-image-');
        if ($temporary === false) {
            throw ValidationException::withMessages(['image_url' => 'The image could not be saved. Please try again.']);
        }
        try {
            for ($redirects = 0; $redirects <= 3; $redirects++) {
                [$host, $address] = $this->publicTarget($url);
                $response = Http::connectTimeout(5)->timeout(15)->withOptions([
                    // Pin the checked address to prevent DNS rebinding. Never use an environment proxy.
                    'proxy' => '',
                    'allow_redirects' => false,
                    'decode_content' => false,
                    'headers' => ['Accept-Encoding' => 'identity'],
                    'sink' => $temporary,
                    'curl' => [CURLOPT_RESOLVE => [$host.':443:'.$address], CURLOPT_PROTOCOLS => CURLPROTO_HTTPS],
                    'on_headers' => function (ResponseInterface $response): void {
                        if ((int) $response->getHeaderLine('Content-Length') > self::MAX_BYTES) {
                            throw new RuntimeException('Image exceeds size limit.');
                        }
                    },
                    'progress' => function ($total, $downloaded): void {
                        if ($total > self::MAX_BYTES || $downloaded > self::MAX_BYTES) {
                            throw new RuntimeException('Image exceeds size limit.');
                        }
                    },
                ])->get($url);
                if ($response->redirect()) {
                    $location = $response->header('Location');
                    if (! $location || $redirects === 3) {
                        throw new RuntimeException('Too many image redirects.');
                    }
                    $url = (string) UriResolver::resolve(new Uri($url), new Uri($location));

                    continue;
                }
                $response->throw();
                $contents = $response->body();
                if ($contents === '' || strlen($contents) > self::MAX_BYTES) {
                    throw new RuntimeException('Invalid image size.');
                }
                // Inspect the actual file, never trust its URL extension or Content-Type header.
                $info = @getimagesizefromstring($contents);
                $extension = match ($info['mime'] ?? '') {
                    'image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', default => null,
                };
                if (! $extension) {
                    throw new RuntimeException('Unsupported image format.');
                }
                file_put_contents($temporary, $contents);
                $image = new UploadedFile($temporary, 'image.'.$extension, test: true);
                Validator::make(['image_url' => $image], ['image_url' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120', 'dimensions:max_width=6000,max_height=6000']])->validate();
                $path = $image->store('opportunities', [
                    'disk' => $disk,
                    'visibility' => config("filesystems.disks.{$disk}.visibility", 'public'),
                ]);
                if (! $path) {
                    throw new RuntimeException('Image storage failed.');
                }

                return $path;
            }
            throw new RuntimeException('Image download failed.');
        } catch (Throwable $exception) {
            // Do not expose remote response bodies or private network details to the form.
            throw ValidationException::withMessages(['image_url' => 'We could not save this image. Use a public HTTPS link to a JPG, PNG or WebP image up to 5 MB and 6000 × 6000 pixels, or upload the image instead.']);
        } finally {
            unlink($temporary);
        }
    }

    /** @return array{string, string} */
    private function publicTarget(string $url): array
    {
        $parts = parse_url($url);
        $host = strtolower($parts['host'] ?? '');
        if (($parts['scheme'] ?? '') !== 'https' || $host === '' || isset($parts['user']) || isset($parts['pass']) || ($parts['port'] ?? 443) !== 443) {
            throw new RuntimeException('Only public HTTPS images are supported.');
        }
        $addresses = filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) ? [$host] : $this->resolveAddresses($host);
        if ($addresses === []) {
            throw new RuntimeException('Image host could not be resolved.');
        }
        foreach ($addresses as $address) {
            if (! filter_var($address, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_GLOBAL_RANGE) || (int) explode('.', $address)[0] >= 224) {
                throw new RuntimeException('Image host must have a public address.');
            }
        }

        return [$host, $addresses[0]];
    }

    /** @return list<string> */
    protected function resolveAddresses(string $host): array
    {
        return gethostbynamel($host) ?: [];
    }
}
