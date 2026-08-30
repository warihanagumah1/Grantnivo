@props(['title' => null])
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>
    <body class="marketing-v1 min-h-screen">
        <div class="flex min-h-screen flex-col">
        <header class="sticky top-0 z-50 border-b border-hair/70 bg-white/85 backdrop-blur-md" x-data="{ open: false }" @click.outside="open = false" @keydown.escape="open = false; $refs.menuButton.focus()">
            <div class="mx-auto flex h-[92px] w-full max-w-[1180px] items-center justify-between px-6 md:px-8">
                <div class="flex items-center gap-10">
                    <x-app-logo href="{{ route('home') }}" class="w-auto! max-w-none!">
                    </x-app-logo>
                    <nav class="hidden items-center gap-8 lg:flex" aria-label="Primary navigation">
                        <a href="{{ route('opportunities.index') }}" @class(['text-[16px] font-semibold transition-colors', 'text-purple' => request()->routeIs('opportunities.*'), 'text-charcoal/80 hover:text-purple' => ! request()->routeIs('opportunities.*')])>{{ __('Opportunities') }}</a>
                        <a href="{{ route('platform') }}" @class(['text-[16px] font-semibold transition-colors', 'text-purple' => request()->routeIs('platform'), 'text-charcoal/80 hover:text-purple' => ! request()->routeIs('platform')])>{{ __('The Platform') }}</a>
                        <a href="{{ route('about') }}" @class(['text-[16px] font-semibold transition-colors', 'text-purple' => request()->routeIs('about'), 'text-charcoal/80 hover:text-purple' => ! request()->routeIs('about')])>{{ __('About') }}</a>
                    </nav>
                </div>
                <div class="hidden items-center lg:flex">
                    <a href="{{ route('waitlist') }}" class="inline-flex items-center justify-center rounded-full bg-red px-6 py-3 text-[15px] font-bold text-white shadow-sm shadow-red/20 transition-colors hover:bg-[#b31f15] gap-3">{{ __('Join the Waitlist') }} <flux:icon.arrow-right class="size-5 shrink-0" aria-hidden="true" /></a>
                </div>
                <button type="button" class="flex size-11 shrink-0 items-center justify-center rounded-lg border border-hair text-charcoal lg:hidden" @click="open = ! open" :aria-expanded="open" aria-label="{{ __('Open menu') }}" :aria-label="open ? @js(__('Close menu')) : @js(__('Open menu'))" aria-controls="marketing-mobile-navigation" x-ref="menuButton">
                    <span x-show="!open" class="space-y-1.5" aria-hidden="true"><span class="block h-0.5 w-5 bg-charcoal"></span><span class="block h-0.5 w-5 bg-charcoal"></span></span>
                    <flux:icon.x-mark x-cloak x-show="open" class="size-5" aria-hidden="true" />
                </button>
            </div>
            <div id="marketing-mobile-navigation" x-cloak x-show="open" x-transition class="absolute inset-x-4 top-[84px] max-h-[calc(100dvh-6rem)] overflow-y-auto rounded-2xl border border-hair bg-white p-5 shadow-xl lg:hidden">
                <nav class="grid gap-1 text-[15px] font-semibold" aria-label="Mobile navigation">
                    <a href="{{ route('opportunities.index') }}" class="rounded-lg px-3 py-2.5 hover:bg-lilac">{{ __('Opportunities') }}</a>
                    <a href="{{ route('platform') }}" class="rounded-lg px-3 py-2.5 hover:bg-lilac">{{ __('The Platform') }}</a>
                    <a href="{{ route('about') }}" class="rounded-lg px-3 py-2.5 hover:bg-lilac">{{ __('About') }}</a>
                    <a href="{{ route('waitlist') }}" class="inline-flex items-center justify-center mt-3 rounded-full bg-red px-5 py-3 text-center text-white gap-3">{{ __('Join the Waitlist') }} <flux:icon.arrow-right class="size-5 shrink-0" aria-hidden="true" /></a>
                </nav>
            </div>
        </header>

        <div class="flex flex-1 flex-col">{{ $slot }}</div>

        <footer class="border-t border-hair bg-white py-16">
            <div class="mx-auto w-full max-w-[1180px] px-6 md:px-8">
                <div class="grid gap-10 lg:grid-cols-[1.3fr_2fr]">
                    <div>
                        <x-app-logo href="{{ route('home') }}" class="w-auto! max-w-none! [&_img]:h-7 [&_img]:w-auto" />
                        <p class="mt-4 max-w-[280px] text-[14px] leading-relaxed text-charcoal/60">{{ __('Turn organisational knowledge into funding. Join the waitlist for early access.') }}</p>
                    </div>
                    <div class="grid grid-cols-2 gap-8 sm:grid-cols-3">
                        <div><p class="text-[12px] font-semibold uppercase tracking-wide text-charcoal/45">{{ __('Platform') }}</p><ul class="mt-3 space-y-2.5"><li><a class="text-[14px] text-charcoal/70 hover:text-purple" href="{{ route('platform') }}">{{ __('Grant Discovery') }}</a></li><li><a class="text-[14px] text-charcoal/70 hover:text-purple" href="{{ route('platform') }}">{{ __('Organisation Brain') }}</a></li><li><a class="text-[14px] text-charcoal/70 hover:text-purple" href="{{ route('platform') }}">{{ __('AI Proposal Drafting') }}</a></li></ul></div>
                        <div><p class="text-[12px] font-semibold uppercase tracking-wide text-charcoal/45">{{ __('Explore') }}</p><ul class="mt-3 space-y-2.5"><li><a class="text-[14px] text-charcoal/70 hover:text-purple" href="{{ route('opportunities.index') }}">{{ __('Opportunities') }}</a></li><li><a class="text-[14px] text-charcoal/70 hover:text-purple" href="{{ route('platform') }}">{{ __('The Platform') }}</a></li><li><a class="text-[14px] text-charcoal/70 hover:text-purple" href="{{ route('about') }}">{{ __('About') }}</a></li></ul></div>
                        <div><p class="text-[12px] font-semibold uppercase tracking-wide text-charcoal/45">{{ __('Get Started') }}</p><ul class="mt-3 space-y-2.5"><li><a class="text-[14px] text-charcoal/70 hover:text-purple" href="{{ route('waitlist') }}">{{ __('Join the Waitlist') }}</a></li><li><a class="text-[14px] text-charcoal/70 hover:text-purple" href="{{ route('opportunities.index') }}">{{ __('Opportunities') }}</a></li></ul></div>
                    </div>
                </div>
                <div class="mt-12 border-t border-hair pt-6 text-center text-[13px] text-charcoal/50">{{ __('Grantnivo — A WAMEER Product') }}</div>
            </div>
        </footer>
        </div>
        @fluxScripts
    </body>
</html>
