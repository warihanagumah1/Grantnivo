<x-layouts::auth :title="__('Reset password')">
    <div class="flex flex-col gap-6">
        <x-auth-header :title="__('Reset password')" :description="__('Please enter your new password below')" />

        <!-- Session Status -->
        <x-auth-session-status class="text-center" :status="session('status')" />

        <x-ui.validated-form method="POST" action="{{ route('password.update') }}" spacing="flex flex-col gap-6">
            @csrf
            <!-- Token -->
            <input type="hidden" name="token" value="{{ request()->route('token') }}">

            <!-- Email Address -->
            <flux:field>
                <flux:label>{{ __('Email') }}</flux:label>
                <flux:input
                    name="email"
                    value="{{ request('email') }}"
                    type="email"
                    required
                    autocomplete="email"
                    maxlength="255"
                />
                <x-ui.field-error field="email" />
            </flux:field>

            <!-- Password -->
            <flux:field>
                <flux:label>{{ __('Password') }}</flux:label>
                <flux:input
                    name="password"
                    type="password"
                    required
                    autocomplete="new-password"
                    data-password-rules="{{ json_encode(\Illuminate\Validation\Rules\Password::defaults()->appliedRules()) }}"
                    :placeholder="__('Password')"
                    passwordrules="{{ \Illuminate\Validation\Rules\Password::defaults()->toPasswordRulesString() }}"
                    viewable
                />
                <x-ui.field-error field="password" />
            </flux:field>

            <!-- Confirm Password -->
            <flux:field>
                <flux:label>{{ __('Confirm password') }}</flux:label>
                <flux:input
                    name="password_confirmation"
                    data-matches="password"
                    type="password"
                    required
                    autocomplete="new-password"
                    data-password-rules="{{ json_encode(\Illuminate\Validation\Rules\Password::defaults()->appliedRules()) }}"
                    :placeholder="__('Confirm password')"
                    passwordrules="{{ \Illuminate\Validation\Rules\Password::defaults()->toPasswordRulesString() }}"
                    viewable
                />
                <x-ui.field-error field="password_confirmation" />
            </flux:field>

            <div class="flex items-center justify-end">
                <x-ui.submit-button class="w-full" data-test="reset-password-button">
                    {{ __('Reset password') }}
                </x-ui.submit-button>
            </div>
        </x-ui.validated-form>
    </div>
</x-layouts::auth>
