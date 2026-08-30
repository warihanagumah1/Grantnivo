<main class="flex-1">
    <section class="bg-purple py-20 md:py-28">
        <div class="mx-auto grid w-full max-w-site items-center gap-14 px-6 md:px-8 lg:grid-cols-2">
            <div>
                <span class="font-mono text-[11px] font-medium uppercase tracking-[0.18em] text-teal">{{ __('Join the Waitlist') }}</span>
                <h1 class="mt-4 text-[34px] font-extrabold leading-[1.06] tracking-[-0.03em] text-white md:text-[46px]">{{ __('Ready to pursue funding smarter?') }}</h1>
                <p class="mt-5 max-w-[500px] text-[16px] leading-relaxed text-white/75">{{ __('Join the Grantnivo waitlist and be among the first organisations to experience smarter grant discovery and proposal development.') }}</p>
                <ul class="mt-8 space-y-3">
                    @foreach ([['shield-check', 'Early access to the platform'], ['eye', 'A source-grounded proposal workflow'], ['lock-closed', 'A private organisational workspace']] as [$icon, $label])
                        <li class="flex items-center gap-3 text-[15px] text-white/90"><span class="flex size-8 items-center justify-center rounded-lg bg-white/15 text-teal"><flux:icon :name="$icon" class="size-4" /></span>{{ $label }}</li>
                    @endforeach
                </ul>
            </div>
            <div class="rounded-2xl bg-white/5 p-6 ring-1 ring-white/15 md:p-8">
                @if ($submitted)
                    <div class="flex min-h-80 flex-col items-center justify-center text-center" role="status"><span class="flex size-14 items-center justify-center rounded-full bg-teal text-white"><flux:icon.check class="size-7" /></span><h2 class="mt-5 text-2xl font-bold text-white">{{ __("You're on the list.") }}</h2><p class="mt-2 max-w-sm text-[14px] leading-relaxed text-white/70">{{ __('We will send early access updates to :email.', ['email' => $email]) }}</p></div>
                @else
                    <x-ui.validated-form wire:submit="join" spacing="grid gap-3" class="validation-on-purple">
                        <div class="hidden" aria-hidden="true"><label>Leave this field blank<input wire:model="website" tabindex="-1" autocomplete="off" /></label></div>
                        <div class="grid gap-1.5"><label for="waitlist-name" class="text-[13px] font-medium text-white/80">{{ __('Name') }}</label><input id="waitlist-name" wire:model="name" maxlength="120" autocomplete="name" required placeholder="Amara Okafor" class="w-full rounded-xl border border-white/20 bg-white/10 px-4 py-3 text-[14px] text-white placeholder:text-white/50 outline-none transition focus:border-white/70 focus:ring-2 focus:ring-white/20 focus:bg-white/15" /><x-ui.field-error field="name" /></div>
                        <div class="grid gap-1.5"><label for="waitlist-email" class="text-[13px] font-medium text-white/80">{{ __('Work email') }}</label><input id="waitlist-email" wire:model="email" maxlength="255" autocomplete="email" required type="email" placeholder="amara@organisation.org" class="w-full rounded-xl border border-white/20 bg-white/10 px-4 py-3 text-[14px] text-white placeholder:text-white/50 outline-none transition focus:border-white/70 focus:ring-2 focus:ring-white/20 focus:bg-white/15" /><x-ui.field-error field="email" /></div>
                        <div class="grid gap-1.5"><label for="waitlist-organisation" class="text-[13px] font-medium text-white/80">{{ __('Organisation') }}</label><input id="waitlist-organisation" wire:model="organisation" maxlength="180" autocomplete="organization" required placeholder="Your organisation" class="w-full rounded-xl border border-white/20 bg-white/10 px-4 py-3 text-[14px] text-white placeholder:text-white/50 outline-none transition focus:border-white/70 focus:ring-2 focus:ring-white/20 focus:bg-white/15" /><x-ui.field-error field="organisation" /></div>
                        <button type="submit" class="mt-2 inline-flex items-center justify-center gap-3 rounded-full bg-red px-6 py-3.5 text-[15px] font-semibold text-white transition-colors hover:bg-[#b31f15]" disabled x-bind:disabled="blocked" x-bind:aria-disabled="blocked.toString()">{{ __('Join the Waitlist') }} <flux:icon.arrow-right class="size-5 shrink-0" aria-hidden="true" /></button>
                        <p class="mt-1 text-center text-[12px] text-white/55">{{ __('No spam. Early access invitations only.') }}</p>
                    </x-ui.validated-form>
                @endif
            </div>
        </div>
    </section>
</main>
