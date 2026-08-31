# Cloudflare R2 image storage

Grantnivo supports R2 through Laravel's S3 filesystem adapter. Both uploaded opportunity images and validated images fetched from URLs use `OPPORTUNITY_IMAGES_DISK`. This integration is configured and tested locally; a live bucket connection must be verified on your host.

## Cloudflare configuration

1. Create a dedicated bucket for public opportunity images. Do not put private documents, database exports, credentials, or waitlist data in it. Draft images also use this media bucket; their pages remain staff-only, but an image URL is not an access-controlled draft preview.
2. Connect a public custom domain to that bucket in Cloudflare, for example `images.grantnivo.com` if you choose that name. This exposes objects in this dedicated bucket. The `r2.dev` URL is for development, not production. Keep it disabled in production.
3. Create R2 S3 credentials with **Object Read & Write** permission scoped to this bucket. The application does not need account-wide bucket administration rights. Store the Access Key ID and Secret Access Key in your hosting provider's secret environment settings, not in source control or chat.
4. Copy the S3 API endpoint shown for the bucket. Jurisdiction-specific buckets use their corresponding endpoint; do not guess it from the public domain.

References: [Cloudflare authentication](https://developers.cloudflare.com/r2/api/tokens/), [public buckets and custom domains](https://developers.cloudflare.com/r2/buckets/public-buckets/), [PHP SDK configuration](https://developers.cloudflare.com/r2/examples/aws/aws-sdk-php/), [Laravel filesystem adapter](https://laravel.com/docs/13.x/filesystem#s3-driver-configuration).

## Hosting environment

```dotenv
FILESYSTEM_DISK=local
OPPORTUNITY_IMAGES_DISK=r2
R2_ACCESS_KEY_ID=your-access-key-id
R2_SECRET_ACCESS_KEY=your-secret-access-key
R2_BUCKET=your-public-image-bucket
R2_ENDPOINT=https://your-account-id.r2.cloudflarestorage.com
R2_PUBLIC_URL=https://your-connected-image-domain
```

`R2_PUBLIC_URL` is the public bucket custom domain, without a bucket name or `/opportunities` suffix. `R2_ENDPOINT` is the authenticated S3 endpoint and must not be used as the public image URL. The disk uses region `auto` and path-style S3 requests. Public access is controlled by Cloudflare's bucket/domain configuration; the app does not request public-read object ACLs.

Keep `FILESYSTEM_DISK=local`: private application files and Livewire temporary uploads must not accidentally go into a public image bucket. Temporary uploads still pass through the Laravel server, are validated, and are then stored in R2. No browser-to-R2 upload or broad CORS permission is needed for this flow. Preserve writable private storage for Livewire upload staging, CSV imports, cache and compiled views. A future private-document feature must use its own private bucket and access-controlled downloads.

Local development remains on `OPPORTUNITY_IMAGES_DISK=public` unless you deliberately configure a separate test bucket. R2 credentials do not belong in frontend `VITE_*` variables.

## GitHub deployment and verification

Commit `composer.json` and `composer.lock` along with the code. `composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction` installs the S3 adapter and AWS PHP SDK from the lock file. Set the environment values before `php artisan optimize` (or refresh with `php artisan config:cache` after changing them). See `DEPLOYMENT.md` for the full GitHub deployment sequence.

Using a non-sensitive test opportunity on the deployed host:

- Upload a valid JPG/PNG/WebP and confirm its public image loads over HTTPS from the configured custom domain.
- Save a public HTTPS image URL and confirm it is downloaded into the bucket rather than remaining an external reference.
- Replace an image and verify the previous object is removed; archive and restore the opportunity and confirm its retained image still works.
- Verify failed uploads leave the existing opportunity and image intact and show a field error. Check that credentials do not appear in page source.

Actual bucket permissions, custom-domain access and network connectivity cannot be proven by mocked tests. Complete these checks before opening traffic.

## Existing local images

Changing the disk setting affects new uploads/downloads only. Existing records retain their `image_disk` and continue to resolve on the original disk, including soft-deleted records. Editing text alone does not move an image.

Do not delete `storage/app/public` or remove its public storage link while records still use it. On a new GitHub deployment those files are not in the repository: transfer them with the database if you need existing images. To move them to R2, first back up the database/files, copy the referenced objects preserving their paths, verify their contents and public access, then update only those verified records to `image_disk=r2`. Retain the source copy for rollback. Do not blindly change all disk values or copy private storage into this bucket. No automatic migration or deletion of your existing files has been performed.

## Privacy and operations

Record Cloudflare as the chosen image-storage/delivery provider in the deployment inventory and confirm its actual region, contractual terms and retention/backup arrangements. Visitors requesting public images connect to Cloudflare, which receives the technical information needed to deliver them. R2 does not store the application's waitlist database through this integration. Keep database backups and media recovery procedures separate from the Git repository.
