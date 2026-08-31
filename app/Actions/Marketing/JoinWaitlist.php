<?php

namespace App\Actions\Marketing;

use App\Models\WaitlistEntry;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class JoinWaitlist
{
    /** @param array<string, mixed> $input */
    public function handle(array $input, string $clientKey): void
    {
        $key = 'waitlist:'.hash('sha256', $clientKey);
        if (RateLimiter::tooManyAttempts($key, 5)) {
            throw ValidationException::withMessages(['email' => 'Too many attempts. Please try again in a minute.']);
        }
        RateLimiter::hit($key, 60);
        $input['email'] = mb_strtolower(trim((string) ($input['email'] ?? '')));
        $validated = Validator::make($input, [
            'acceptPolicies' => ['accepted'],
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email:rfc', 'max:255'],
            'organisation' => ['required', 'string', 'max:180'],
        ], ['acceptPolicies.accepted' => 'Please agree to the Terms and acknowledge the Privacy Policy to join the waitlist.'])->validate();

        // The unique email index also protects against simultaneous signups.
        $entry = WaitlistEntry::query()->firstOrCreate(['email' => $validated['email']], [
            'name' => trim($validated['name']),
            'organisation' => trim($validated['organisation']),
            'source' => 'waitlist',
            'subscribed_at' => now(),
            'consented_at' => now(),
            'privacy_policy_version' => config('legal.privacy_version'),
            'terms_version' => config('legal.terms_version'),
            'consent_version' => config('legal.consent_version'),
        ]);

        if (! $entry->wasRecentlyCreated) {
            throw ValidationException::withMessages(['email' => 'This email is already on the waitlist.']);
        }
    }
}
