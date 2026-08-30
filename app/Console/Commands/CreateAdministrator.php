<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\Admin\AuditLog;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class CreateAdministrator extends Command
{
    protected $signature = 'v1:admin {email : Staff email address} {--revoke : Revoke staff access instead}';

    protected $description = 'Create a V1 administrator or explicitly grant/revoke access for an existing account';

    public function handle(AuditLog $audit): int
    {
        $email = mb_strtolower(trim((string) $this->argument('email')));
        $validator = Validator::make(['email' => $email], ['email' => ['required', 'email:rfc', 'max:255']]);
        if ($validator->fails()) {
            $this->error('Enter a valid staff email address.');

            return self::FAILURE;
        }
        $user = User::query()->where('email', $email)->first();
        if ($this->option('revoke')) {
            if (! $user || ! $this->confirm('Revoke administrator access for '.$email.'?', false)) {
                return self::FAILURE;
            }
            $user->forceFill(['is_admin' => false])->save();
            $audit->record($user, 'admin.revoked.cli', 'user', $user->id);
            $this->info('Administrator access revoked.');

            return self::SUCCESS;
        }
        if ($user) {
            if (! $this->confirm('Grant administrator access to '.$email.'?', false)) {
                return self::FAILURE;
            }
        } else {
            $name = $this->ask('Staff name');
            $password = $this->secret('Choose a password (at least 12 characters, upper/lowercase, and numbers)');
            $validated = Validator::make(['name' => $name, 'password' => $password], [
                'name' => ['required', 'string', 'max:120'],
                'password' => ['required', Password::min(12)->mixedCase()->numbers()],
            ]);
            if ($validated->fails()) {
                foreach ($validated->errors()->all() as $message) {
                    $this->error($message);
                }

                return self::FAILURE;
            }
            if ($password !== $this->secret('Confirm password')) {
                $this->error('Passwords do not match.');

                return self::FAILURE;
            }
            $user = User::query()->create(['name' => $name, 'email' => $email, 'password' => $password]);
        }
        $user->forceFill(['is_admin' => true])->save();
        $audit->record($user, 'admin.granted.cli', 'user', $user->id);
        $this->info('Administrator ready. Sign in at /admin/login.');

        return self::SUCCESS;
    }
}
