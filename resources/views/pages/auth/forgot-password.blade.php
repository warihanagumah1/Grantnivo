<x-layouts::auth :title="__('Forgot password')">
    <div class="flex flex-col gap-6">
        <x-auth-header :title="__('Forgot password')" :description="__('Enter your email to receive a password reset link')" />

        <!-- Session Status -->
        <x-auth-session-status class="text-center" :status="session('status')" />

        <x-ui.validated-form method="POST" action="{{ route('password.email') }}" spacing="flex flex-col gap-6">
            @csrf

            <!-- Email Address -->
            <flux:field>
                <flux:label>{{ __('Email address') }}</flux:label>
                <flux:input
                    name="email"
                    type="email"
                    required
                    autofocus
                    placeholder="email@example.com"
                    maxlength="255"
                />
                <x-ui.field-error field="email" />
            </flux:field>

            <x-ui.submit-button class="w-full" data-test="email-password-reset-link-button">
                {{ __('Email password reset link') }}
            </x-ui.submit-button>
        </x-ui.validated-form>

        <div class="space-x-1 rtl:space-x-reverse text-center text-sm text-zinc-400">
            <span>{{ __('Or, return to') }}</span>
            <flux:link :href="route('login')" wire:navigate>{{ __('log in') }}</flux:link>
        </div>
    </div>
</x-layouts::auth>
