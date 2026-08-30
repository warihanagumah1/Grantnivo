<x-layouts.marketing title="Page not found">
    <main class="mx-auto flex w-full max-w-[1180px] flex-1 flex-col items-center justify-center px-6 py-28 text-center">
        <p class="font-mono text-[12px] uppercase tracking-[0.18em] text-teal">404 · Page not found</p>
        <h1 class="mt-4 text-[36px] font-bold tracking-tight">This page is no longer here.</h1>
        <p class="mt-4 max-w-md text-[16px] text-charcoal/60">The link may have changed, or this opportunity is no longer published.</p>
        <a href="{{ route('opportunities.index') }}" class="admin-primary mt-8">Explore opportunities <flux:icon.arrow-right class="size-4" /></a>
    </main>
</x-layouts.marketing>
