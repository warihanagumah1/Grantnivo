<x-layouts::auth :title="__('Confirm password')">
    <div class="flex flex-col gap-6">
        <x-auth-header
            :title="__('Confirm password')"
            :description="__('This is a secure area of the application. Please confirm your password before continuing.')"
        />

        <x-auth-session-status class="text-center" :status="session('status')" />

        <x-ui.validated-form method="POST" action="{{ route('password.confirm.store') }}" spacing="flex flex-col gap-6">
            @csrf

            <flux:field>
                <flux:label>{{ __('Password') }}</flux:label>
                <flux:input
                    name="password"
                    type="password"
                    required
                    autocomplete="current-password"
                    :placeholder="__('Password')"
                    viewable
                />
                <x-ui.field-error field="password" />
            </flux:field>

            <x-ui.submit-button class="w-full" data-test="confirm-password-button">
                {{ __('Confirm') }}
            </x-ui.submit-button>
        </x-ui.validated-form>
    </div>
</x-layouts::auth>
