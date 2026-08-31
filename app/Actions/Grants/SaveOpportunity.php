<?php

namespace App\Actions\Grants;

use App\Enums\OpportunityStatus;
use App\Models\GrantOpportunity;
use App\Models\User;
use App\Services\Admin\AuditLog;
use App\Services\Grants\RemoteOpportunityImage;
use App\Services\Grants\RichText;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Throwable;

class SaveOpportunity
{
    public function __construct(private AuditLog $audit, private RichText $richText, private RemoteOpportunityImage $remoteImage) {}

    /** @param array<string, mixed> $input */
    public function handle(User $user, array $input, ?GrantOpportunity $opportunity = null, ?UploadedFile $image = null): GrantOpportunity
    {
        Gate::forUser($user)->authorize($opportunity ? 'update' : 'create', $opportunity ?? GrantOpportunity::class);
        foreach ($input as $key => $value) {
            if (is_string($value)) {
                $input[$key] = trim($value) === '' ? null : trim($value);
            }
        }
        $draft = ($input['status'] ?? null) === OpportunityStatus::Draft->value;
        $required = $draft ? 'nullable' : 'required';
        $source = $input['image_source'] ?? ($opportunity?->image_path ? 'upload' : 'url');
        $input['image_source'] = $source;
        $input['image'] = $image;
        if ($source === 'upload') {
            $input['image_url'] = null;
        }
        $data = Validator::make($input, [
            'title' => ['required', 'string', 'max:255'],
            'funder' => [$required, 'string', 'max:255'],
            'sector' => [$required, 'string', 'max:255'],
            'countries' => [$required, 'string', 'max:2000'],
            'funding_type' => ['required', Rule::in(['Grant', 'Partnership'])],
            'status' => ['required', Rule::in([OpportunityStatus::Draft->value, OpportunityStatus::Active->value, OpportunityStatus::Closed->value, OpportunityStatus::ClosingSoon->value])],
            'maximum_award' => ['nullable', 'integer', 'min:0', 'max:999999999999'],
            'currency' => ['required', Rule::in(['USD', 'EUR', 'GBP', 'GHS'])],
            'deadline' => ['nullable', 'date_format:Y-m-d'],
            'summary' => [$required, 'string', 'max:12000'],
            'description' => ['nullable', 'string', 'max:60000'],
            'image_source' => ['required', Rule::in(['url', 'upload'])],
            'image' => [Rule::requiredIf(! $draft && $source === 'upload' && ! $opportunity?->image_path), 'nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120', 'dimensions:max_width=6000,max_height=6000'],
            'image_url' => ['nullable', 'url:https', 'max:2048'],
            'image_alt' => ['nullable', 'string', 'max:255', Rule::requiredIf(! $draft && ($source === 'upload' || ! empty($input['image_url'])))],
            'funder_url' => ['nullable', 'url:https,http', 'max:2048'],
            'learn_more_url' => ['nullable', 'url:https,http', 'max:2048'],
            'opportunity_url' => ['nullable', 'url:https,http', 'max:2048'],
        ])->validate();
        foreach (['summary' => 2000, 'description' => 20000] as $field => $limit) {
            $data[$field] = $this->richText->sanitize($data[$field] ?? '');
            Validator::make([$field => $this->richText->text($data[$field])], [
                $field => [$field === 'summary' ? $required : 'nullable', 'string', 'max:'.$limit],
            ])->validate();
        }
        $data['funder'] ??= '';
        $data['thematic_sectors'] = empty($data['sector']) ? [] : [trim($data['sector'])];
        $data['eligible_countries'] = array_values(array_unique(array_filter(array_map('trim', explode(',', $data['countries'] ?? '')))));
        Validator::make(['countries' => $data['eligible_countries']], ['countries' => ['array', 'min:'.($draft ? 0 : 1), 'max:100'], 'countries.*' => ['string', 'max:120']])->validate();
        unset($data['sector'], $data['countries'], $data['image_source'], $data['image']);

        $oldPath = $opportunity?->image_path;
        $oldDisk = $opportunity->image_disk ?? 'public';
        $path = $source === 'upload' ? $oldPath : null;
        $disk = $source === 'upload' ? $oldDisk : null;
        if ($source === 'upload' && $image) {
            $disk = config('filesystems.opportunity_images', 'public');
            try {
                $path = $image->store('opportunities', [
                    'disk' => $disk,
                    'visibility' => config("filesystems.disks.{$disk}.visibility", 'public'),
                ]);
            } catch (Throwable $exception) {
                throw ValidationException::withMessages(['image' => 'The image could not be saved. Please try again.']);
            }
            if (! $path) {
                throw ValidationException::withMessages(['image' => 'The image could not be saved. Please try again.']);
            }
        }
        if ($source === 'url' && ! empty($data['image_url'])) {
            $disk = config('filesystems.opportunity_images', 'public');
            $path = $this->remoteImage->store($data['image_url'], $disk);
            $data['image_url'] = null;
        }

        try {
            $saved = DB::transaction(function () use ($user, $data, $opportunity, $path, $disk): GrantOpportunity {
                $creating = $opportunity === null;
                $opportunity ??= new GrantOpportunity;
                if ($creating) {
                    $base = Str::limit(Str::slug($data['title']), 220, '') ?: 'opportunity';
                    $slug = $base;
                    for ($suffix = 2; GrantOpportunity::withTrashed()->where('slug', $slug)->exists(); $suffix++) {
                        $slug = $base.'-'.$suffix;
                    }
                    $opportunity->slug = $slug;
                }
                $opportunity->fill($data);
                // Storage paths are generated by the server, never accepted from form input.
                $opportunity->forceFill(['image_path' => $path, 'image_disk' => $disk]);
                $changes = $opportunity->getDirty();
                $opportunity->save();
                $this->audit->record($user, $creating ? 'opportunity.created' : 'opportunity.updated', 'grant_opportunity', $opportunity->id, $changes);

                return $opportunity;
            });
        } catch (Throwable $exception) {
            if ($path && $path !== $oldPath) {
                try {
                    Storage::disk($disk)->delete($path);
                } catch (Throwable $cleanupException) {
                    report($cleanupException);
                }
            }
            throw $exception;
        }
        if ($oldPath && $oldPath !== $path) {
            try {
                Storage::disk($oldDisk)->delete($oldPath);
            } catch (Throwable $exception) {
                report($exception);
            }
        }

        return $saved;
    }
}
