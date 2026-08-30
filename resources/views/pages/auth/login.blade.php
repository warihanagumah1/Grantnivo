<x-layouts::auth :title="__('Admin sign in')">
    <h1 class="text-[28px] font-bold tracking-tight">Admin sign in</h1>
    <p class="mt-2 text-[14px] text-charcoal/55">Manage opportunities and waitlist access.</p>
    <x-auth-session-status class="mt-4" :status="session('status')" />
    <x-ui.validated-form method="POST" action="{{ route('login.store') }}" spacing="mt-8 grid gap-5">
        @csrf
        <div class="grid gap-1.5"><label for="email" class="text-[13px] font-medium text-charcoal/75">Email</label><input id="email" name="email" value="{{ old('email') }}" type="email" required autofocus autocomplete="username" maxlength="255" placeholder="you@grantnivo.com" class="admin-input" /><x-ui.field-error field="email" /></div>
        <div class="grid gap-1.5"><label for="password" class="text-[13px] font-medium text-charcoal/75">Password</label><input id="password" name="password" type="password" required autocomplete="current-password" placeholder="••••••••" class="admin-input" /><x-ui.field-error field="password" /></div>
        <button type="submit" class="admin-primary w-full" disabled x-bind:disabled="blocked" x-bind:aria-disabled="blocked.toString()">Sign in <flux:icon.arrow-right class="size-4" /></button>
    </x-ui.validated-form>
    <a href="{{ route('password.request') }}" class="mt-5 block text-center text-[13px] text-purple hover:underline">Forgot your password?</a>
    <p class="mt-6 text-center text-[12px] text-charcoal/45">Staff access only. Looking for early access? <a href="{{ route('waitlist') }}" class="text-purple hover:underline">Join the waitlist.</a></p>
</x-layouts::auth>
