<div>
    <x-admin.success-notice />
    <x-admin.heading eyebrow="Overview" :title="'Welcome back, '.str(auth()->user()->name)->before(' ').'.'"><div class="flex flex-wrap gap-3"><a href="{{ route('admin.opportunities.import') }}" class="admin-secondary">Bulk upload CSV</a><a href="{{ route('admin.opportunities.create') }}" class="admin-primary"><flux:icon.plus class="size-4" />New opportunity</a></div></x-admin.heading>
    <x-admin.stats :stats="$stats" />
    <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
        <section class="rounded-2xl border border-hair bg-white p-6"><div class="mb-5 flex flex-wrap items-center justify-between gap-3"><h2 class="text-[15px] font-semibold">Recent opportunities</h2><a href="{{ route('admin.opportunities') }}" class="admin-text-link">Manage <flux:icon.arrow-right class="size-3.5" /></a></div>
            <div class="divide-y divide-hair">@forelse($opportunities as $opportunity)<a href="{{ route('admin.opportunities.edit', $opportunity) }}" class="flex flex-col items-start justify-between gap-2 py-3 sm:flex-row sm:items-center sm:gap-4 hover:text-purple"><div class="min-w-0 max-w-full"><p class="truncate text-[14px] font-medium">{{ $opportunity->title }}</p><p class="truncate text-[12px] text-charcoal/45">{{ $opportunity->funder }} · {{ $opportunity->thematic_sectors[0] ?? 'General' }}</p></div><span class="shrink-0 font-mono text-[12px] text-charcoal/45">{{ $opportunity->displayAmount() }}</span></a>@empty<p class="py-10 text-center text-[13px] text-charcoal/45">No published opportunities yet.</p>@endforelse</div>
        </section>
        <section class="rounded-2xl border border-hair bg-white p-6"><div class="mb-5 flex flex-wrap items-center justify-between gap-3"><h2 class="text-[15px] font-semibold">Latest waitlist signups</h2><a href="{{ route('admin.waitlist') }}" class="admin-text-link">View all <flux:icon.arrow-right class="size-3.5" /></a></div>
            <div class="divide-y divide-hair">@forelse($entries as $entry)<div class="flex flex-wrap justify-between gap-2 py-3"><div><p class="text-[14px] font-medium">{{ $entry->name }}</p><p class="text-[12px] text-charcoal/45">{{ $entry->organisation }}</p></div><span class="text-[12px] text-charcoal/45">{{ $entry->created_at->format('d M Y') }}</span></div>@empty<p class="py-10 text-center text-[13px] text-charcoal/45">No signups yet.</p>@endforelse</div>
        </section>
    </div>
</div>
