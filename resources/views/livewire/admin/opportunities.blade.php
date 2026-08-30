<div>
    <x-admin.heading eyebrow="Opportunities" title="Manage opportunities" :description="$liveCount.' live on the public site.'"><a href="{{ route('admin.opportunities.create') }}" class="admin-primary"><flux:icon.plus class="size-4" />New opportunity</a></x-admin.heading>
    <div class="mb-5 flex flex-col gap-3 sm:flex-row"><label class="flex-1"><span class="sr-only">Search opportunities</span><input wire:model.live.debounce.300ms="search" type="search" maxlength="200" placeholder="Search by title, funder, or country…" class="admin-input" /></label><label><span class="sr-only">Filter status</span><select wire:model.live="status" class="admin-input sm:w-44"><option value="all">All opportunities</option><option value="active">Open</option><option value="closed">Closed</option><option value="draft">Drafts</option><option value="archived">Archived</option></select></label></div>
    <div class="divide-y divide-hair overflow-hidden rounded-2xl border border-hair bg-white" wire:loading.class="opacity-60">
        @forelse($opportunities as $opportunity)
            <article wire:key="opportunity-{{ $opportunity->id }}" class="flex flex-wrap items-center gap-4 p-5">
                @if($opportunity->imageUrl())<img src="{{ $opportunity->imageUrl() }}" alt="" loading="lazy" class="size-16 rounded-xl object-cover" />@else<div class="flex size-16 items-center justify-center rounded-xl bg-purple-tint text-purple"><flux:icon.square-3-stack-3d class="size-6" /></div>@endif
                <div class="min-w-0 flex-1"><p class="text-[15px] font-semibold">{{ $opportunity->title }}</p><p class="mt-1 text-[12px] text-charcoal/55">{{ $opportunity->funder }} · {{ $opportunity->thematic_sectors[0] ?? 'General' }} · {{ $opportunity->displayAmount() }}</p><p class="mt-2 text-[11px]"><span class="rounded-full bg-purple-tint px-2 py-1 text-purple">{{ ($opportunity->trashed() || $opportunity->status->value === 'archived') ? 'Archived' : $opportunity->publicStatusLabel() }}</span><span class="ml-2 font-mono text-charcoal/50">{{ $opportunity->deadline?->format('d M Y') ?? 'Rolling deadline' }}</span></p></div>
                <div class="flex items-center gap-3 text-[13px] font-medium">
                    @if(($opportunity->trashed() || $opportunity->status->value === 'archived'))<button wire:click="restore({{ $opportunity->id }})" wire:confirm="Restore this opportunity? Its previous publication status will be used." class="admin-text-link">Restore</button>@else
                    @if($opportunity->isPublic())<a href="{{ route('opportunities.show', $opportunity->slug) }}" class="text-charcoal/55 hover:text-purple" target="_blank" rel="noopener">View</a>@endif
                    <a href="{{ route('admin.opportunities.edit', $opportunity) }}" class="text-purple">{{ $opportunity->status->value === 'draft' ? 'Edit & publish' : 'Edit' }}</a><button wire:click="archive({{ $opportunity->id }})" wire:confirm="Remove this opportunity from the public site? You can restore it from Archived." wire:loading.attr="disabled" class="text-red hover:underline disabled:opacity-50">Delete</button>@endif
                </div>
            </article>
        @empty<div class="px-6 py-16 text-center"><h2 class="text-[16px] font-semibold">No opportunities found</h2><p class="mt-2 text-[14px] text-charcoal/50">Try a different search or create your first opportunity.</p></div>@endforelse
    </div>
    <div class="mt-5">{{ $opportunities->links() }}</div>
    <a href="{{ route('admin.overview') }}" class="admin-text-link mt-6"><flux:icon.arrow-left class="size-4" />Back to overview</a>
</div>
