@props(['title' => null])
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>@include('partials.head')</head>
<body class="marketing-v1 bg-offwhite! min-h-screen" x-data="{ menuOpen: false, desktop: window.matchMedia('(min-width: 1024px)').matches }" @resize.window="desktop = window.matchMedia('(min-width: 1024px)').matches">
    <a href="#main-content" class="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-[100] focus:rounded-lg focus:bg-white focus:p-3">Skip to content</a>
    <header class="flex h-20 items-center justify-between border-b border-hair bg-white px-6 lg:hidden">
        <x-app-logo class="[&_img]:h-7" />
        <button type="button" @click="menuOpen = !menuOpen" :aria-expanded="menuOpen" aria-controls="admin-navigation" aria-label="Toggle navigation" class="rounded-lg p-2 hover:bg-purple-tint"><flux:icon.bars-3 class="size-5" /></button>
    </header>
    <div x-cloak x-show="menuOpen" @click="menuOpen = false" class="fixed inset-0 z-40 bg-charcoal/30 lg:hidden"></div>
    <aside id="admin-navigation" class="fixed inset-y-0 left-0 z-50 flex w-[248px] max-w-[calc(100vw-2rem)] flex-col overflow-y-auto border-r border-hair bg-white p-5 transition-transform lg:translate-x-0!" :class="menuOpen ? 'translate-x-0' : '-translate-x-full'" :inert="!menuOpen && !desktop" @keydown.escape.window="menuOpen = false">
        <x-app-logo class="mb-8 mt-1 [&_img]:h-7" />
        <p class="mb-3 px-2 font-mono text-[10px] uppercase tracking-[0.16em] text-charcoal/40">Admin console</p>
        <nav aria-label="Admin navigation" class="grid gap-1">
            @foreach (['admin.overview' => ['Overview', 'squares-2x2'], 'admin.opportunities' => ['Opportunities', 'square-3-stack-3d'], 'admin.analytics' => ['Analytics', 'arrow-trending-up'], 'admin.waitlist' => ['Waitlist', 'bell']] as $route => [$label, $icon])
                @php($active = request()->routeIs($route, $route.'.*'))
                <a href="{{ route($route) }}" @class(['flex items-center gap-3 rounded-xl px-3 py-3 text-[14px] font-medium transition-colors', 'bg-purple text-white' => $active, 'text-charcoal/70 hover:bg-purple-tint hover:text-purple' => !$active]) @if($active) aria-current="page" @endif><flux:icon :name="$icon" class="size-[18px]" />{{ $label }}</a>
            @endforeach
        </nav>
        <div class="mt-auto pt-10">
            <a href="{{ route('home') }}" class="mb-3 flex items-center gap-2 px-3 py-2 text-[13px] text-charcoal/55 hover:text-purple"><flux:icon.arrow-left class="size-4" />View live site</a>
            <div class="flex items-center gap-3 rounded-xl border border-hair p-3">
                <span class="flex size-9 shrink-0 items-center justify-center rounded-full bg-purple font-mono text-[11px] text-white">{{ auth()->user()->initials() }}</span>
                <div class="min-w-0 flex-1"><p class="truncate text-[13px] font-semibold">{{ auth()->user()->name }}</p><p class="text-[11px] text-charcoal/45">Administrator</p></div>
                <form method="POST" action="{{ route('logout') }}">@csrf<button type="submit" aria-label="Sign out" title="Sign out" class="rounded p-1 text-charcoal/50 hover:text-purple"><flux:icon.arrow-right-start-on-rectangle class="size-4" /></button></form>
            </div>
        </div>
    </aside>
    <main id="main-content" class="min-w-0 p-6 md:p-10 lg:ml-[248px]">
        <div class="mx-auto max-w-site">
            {{ $slot }}
        </div>
    </main>
    @fluxScripts
</body>
</html>
