<?php

namespace App\Actions\Grants;

use App\Models\GrantOpportunity;
use App\Models\User;
use App\Services\Grants\RichText;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Throwable;

class ImportOpportunities
{
    public const COLUMNS = ['title', 'funder', 'sector', 'countries', 'funding_type', 'maximum_award', 'currency', 'deadline', 'summary', 'description', 'image_url', 'image_alt', 'learn_more_url', 'opportunity_url', 'funder_url', 'status'];

    public function __construct(private SaveOpportunity $save, private RichText $richText) {}

    public function handle(User $user, UploadedFile $file): int
    {
        Gate::forUser($user)->authorize('create', GrantOpportunity::class);
        Validator::make(['file' => $file], ['file' => ['required', 'file', 'mimes:csv,txt', 'extensions:csv', 'max:2048']])->validate();
        if (! mb_check_encoding($file->getContent(), 'UTF-8')) {
            throw ValidationException::withMessages(['file' => 'Save your spreadsheet as CSV UTF-8, then upload it again.']);
        }
        $stream = fopen($file->getRealPath(), 'r');
        if ($stream === false) {
            throw ValidationException::withMessages(['file' => 'The CSV could not be read. Please choose the file again.']);
        }
        try {
            $header = fgetcsv($stream, separator: ',', enclosure: '"', escape: '');
            if ($header === false) {
                throw ValidationException::withMessages(['file' => 'The CSV is empty. Start with the downloadable template.']);
            }
            $header = array_map(static fn (?string $column): string => strtolower(trim(ltrim($column ?? '', "\xEF\xBB\xBF"))), $header);
            if (! in_array('title', $header, true) || count(array_unique($header)) !== count($header) || array_diff($header, self::COLUMNS) !== []) {
                throw ValidationException::withMessages(['file' => 'Use the template column names, including title, without duplicate or unknown columns.']);
            }
            $rows = [];
            $record = 1;
            $errors = [];
            while (($values = fgetcsv($stream, separator: ',', enclosure: '"', escape: '')) !== false) {
                $record++;
                if ($values === [null]) {
                    continue;
                }
                if ($record > 251) {
                    throw ValidationException::withMessages(['file' => 'Import up to 250 rows at a time. Split this CSV into smaller files.']);
                }
                if (count($values) !== count($header)) {
                    $errors['rows.'.$record] = 'Row '.$record.': the number of cells does not match the header. Put comma-separated countries inside quotes.';

                    continue;
                }
                $rows[$record] = array_combine($header, $values);
            }
            if ($errors !== []) {
                throw ValidationException::withMessages($errors);
            }
            if ($rows === []) {
                throw ValidationException::withMessages(['file' => 'Add at least one opportunity below the header row.']);
            }
        } finally {
            fclose($stream);
        }

        // The entire batch rolls back if any row fails; successful rows are never left half-imported.
        $storedImages = [];
        try {
            return DB::transaction(function () use ($user, $rows, &$storedImages): int {
                $errors = [];
                foreach ($rows as $number => $row) {
                    $row['status'] = 'draft';
                    $row['funding_type'] = trim($row['funding_type'] ?? '') ?: 'Grant';
                    $row['currency'] = trim($row['currency'] ?? '') ?: 'USD';
                    $row['image_source'] = 'url';
                    try {
                        Validator::make($row, [
                            'summary' => ['nullable', 'string', 'max:12000'],
                            'description' => ['nullable', 'string', 'max:60000'],
                        ])->validate();
                        foreach (['summary', 'description'] as $field) {
                            $row[$field] = $this->richText->fromImport($row[$field] ?? '');
                        }
                        $saved = $this->save->handle($user, $row);
                        if ($saved->image_path) {
                            $storedImages[] = [$saved->image_disk, $saved->image_path];
                        }
                    } catch (ValidationException $exception) {
                        $errors['rows.'.$number] = 'Row '.$number.': '.implode(' ', $exception->validator->errors()->all());
                    }
                }
                if ($errors !== []) {
                    throw ValidationException::withMessages($errors);
                }

                return count($rows);
            });
        } catch (Throwable $exception) {
            // Database rollback cannot remove files from the storage disk.
            foreach ($storedImages as [$disk, $path]) {
                try {
                    Storage::disk($disk)->delete($path);
                } catch (Throwable $cleanupException) {
                    report($cleanupException);
                }
            }
            throw $exception;
        }
    }
}
