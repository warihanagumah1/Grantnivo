@props(['opportunity'])

<a
    href="{{ route('opportunities.show', $opportunity) }}"
    {{ $attributes->class('group flex h-full flex-col overflow-hidden rounded-2xl border border-hair bg-white transition-shadow hover:shadow-[0_18px_44px_-24px_rgba(36,55,70,0.28)]') }}
>
    <div class="relative aspect-[16/10] overflow-hidden bg-offwhite">
        @if ($opportunity->imageUrl())<img src="{{ $opportunity->imageUrl() }}" alt="{{ $opportunity->image_alt ?: $opportunity->title }}" class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-[1.04]" loading="lazy" />@endif
        <div class="absolute right-3 top-3"><span class="rounded-full bg-teal px-2.5 py-1 font-mono text-[11px] font-semibold text-white backdrop-blur">{{ $opportunity->publicStatusLabel() }}</span></div>
    </div>
    <div class="flex flex-1 flex-col p-5">
        <span class="font-mono text-[11px] uppercase tracking-wide text-charcoal/45">{{ $opportunity->funder }}</span>
        <h3 class="mt-1.5 text-[18px] font-semibold text-charcoal">{{ $opportunity->title }}</h3>
        <div class="mt-2 flex flex-wrap items-center gap-1.5">
            <span class="inline-flex w-fit rounded-full bg-purple-tint px-2.5 py-0.5 text-[11px] font-medium text-purple">{{ $opportunity->thematic_sectors[0] ?? __('General') }}</span>
            <span class="inline-flex w-fit rounded-full border border-hair px-2.5 py-0.5 text-[11px] font-medium text-charcoal/60">{{ $opportunity->funding_type ?: __('Grant') }}</span>
        </div>
        <dl class="mt-4 space-y-2 border-t border-hair pt-4 text-[13px]">
            <div class="flex items-center gap-2 text-charcoal/70"><flux:icon.map-pin class="size-4 text-charcoal/40" /><span>{{ implode(', ', $opportunity->eligible_countries ?? []) }}</span></div>
            <div class="flex items-center justify-between gap-4"><dt class="text-charcoal/55">{{ __('Funding amount') }}</dt><dd class="font-mono font-semibold text-charcoal">{{ $opportunity->displayAmount() }}</dd></div>
            <div class="flex items-center justify-between gap-4"><dt class="flex items-center gap-1.5 text-charcoal/55"><flux:icon.calendar-days class="size-3.5 text-charcoal/40" />{{ __('Deadline') }}</dt><dd class="font-mono font-semibold text-red">{{ $opportunity->deadline?->format('d M Y') ?? __('Rolling') }}</dd></div>
        </dl>
    </div>
</a>
