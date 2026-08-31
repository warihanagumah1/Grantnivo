<main class="flex-1">
    @if($preview ?? false)
        <div class="border-b border-purple/20 bg-purple-tint px-6 py-4 text-center text-[14px] text-purple">
            <strong>Admin preview.</strong> {{ $grantOpportunity->isPublic() ? 'You are previewing this opportunity.' : 'This opportunity is not published and is only visible to admins.' }}
            <a href="{{ route('admin.opportunities.edit', $grantOpportunity) }}" class="ml-2 font-semibold underline">Edit opportunity</a>
        </div>
    @endif
    <section class="relative min-h-[340px] overflow-hidden bg-charcoal md:min-h-[420px]">
        @if ($grantOpportunity->imageUrl())<img src="{{ $grantOpportunity->imageUrl() }}" alt="{{ $grantOpportunity->image_alt ?: $grantOpportunity->title }}" class="absolute inset-0 h-full w-full object-cover" />@endif
        <div class="absolute inset-0 bg-gradient-to-t from-charcoal/95 via-charcoal/55 to-charcoal/25"></div>
        <div class="relative mx-auto flex min-h-[340px] w-full max-w-site flex-col justify-end px-6 py-10 md:min-h-[420px] md:px-8">
            <a href="{{ route(($preview ?? false) ? 'admin.opportunities' : 'opportunities.index') }}" class="mb-4 flex w-fit items-center gap-1.5 text-[13px] font-medium text-white/80 hover:text-white"><flux:icon.arrow-left class="size-4" />{{ __('All opportunities') }}</a>
            <div class="flex flex-wrap items-center gap-2"><span class="rounded-full bg-white/15 px-3 py-1 text-[12px] font-medium text-white backdrop-blur">{{ $grantOpportunity->thematic_sectors[0] ?? __('General') }}</span><span class="rounded-full bg-white/15 px-3 py-1 text-[12px] font-medium text-white backdrop-blur">{{ $grantOpportunity->funding_type ?: __('Grant') }}</span><span class="rounded-full bg-teal px-3 py-1 text-[12px] font-semibold text-white backdrop-blur">{{ $grantOpportunity->isOpen() ? __('Open for applications') : $grantOpportunity->publicStatusLabel() }}</span></div>
            <h1 class="mt-4 max-w-3xl text-[32px] font-extrabold leading-tight tracking-[-0.02em] text-white md:text-[44px]">{{ $grantOpportunity->title }}</h1>
            <p class="mt-2 font-mono text-[13px] uppercase tracking-wide text-white/70">{{ $grantOpportunity->funder }}</p>
        </div>
    </section>

    <section class="bg-white py-14 md:py-20">
        <div class="mx-auto grid w-full max-w-site gap-10 px-6 md:px-8 lg:grid-cols-[1.5fr_1fr]">
            <div>
                <h2 class="text-[20px] font-bold text-charcoal">{{ __('About this opportunity') }}</h2>
                <div class="grant-rich-text mt-3 text-[16px] text-charcoal/75">{!! app(\App\Services\Grants\RichText::class)->sanitize($grantOpportunity->summary) !!}</div>
                @if($grantOpportunity->description)<div class="grant-rich-text mt-6 text-[16px] text-charcoal/75">{!! app(\App\Services\Grants\RichText::class)->sanitize($grantOpportunity->description) !!}</div>@endif
                <h3 class="mt-10 text-[16px] font-bold text-charcoal">{{ __('At a glance') }}</h3>
                <div class="mt-4 grid gap-3 sm:grid-cols-2">
                    @foreach ([__('Focus area') => ($grantOpportunity->thematic_sectors[0] ?? __('General')), __('Funder') => $grantOpportunity->funder, __('Eligible countries') => implode(', ', $grantOpportunity->eligible_countries ?? []), __('Funding amount') => $grantOpportunity->displayAmount()] as $label => $value)
                        <div class="rounded-lg border border-hair px-4 py-3"><p class="font-mono text-[11px] uppercase tracking-wide text-charcoal/45">{{ $label }}</p><p class="mt-0.5 text-[14px] font-medium text-charcoal">{{ $value }}</p></div>
                    @endforeach
                </div>
            </div>
            <aside class="lg:sticky lg:top-24 lg:self-start">
                <div class="rounded-2xl border border-hair bg-offwhite/60 p-6">
                    <dl class="space-y-4 text-[14px]">
                        <div><dt class="font-mono text-[11px] uppercase tracking-wide text-charcoal/45">{{ __('Funder') }}</dt><dd class="mt-0.5 font-semibold text-charcoal">{{ $grantOpportunity->funder }}</dd></div>
                        <div><dt class="font-mono text-[11px] uppercase tracking-wide text-charcoal/45">{{ __('Funding amount') }}</dt><dd class="mt-0.5 font-mono text-[18px] font-bold text-charcoal">{{ $grantOpportunity->displayAmount() }}</dd></div>
                        <div><dt class="flex items-center gap-1.5 font-mono text-[11px] uppercase tracking-wide text-charcoal/45"><flux:icon.map-pin class="size-3.5" />{{ __('Eligible countries') }}</dt><dd class="mt-0.5 font-medium text-charcoal">{{ implode(', ', $grantOpportunity->eligible_countries ?? []) }}</dd></div>
                        <div><dt class="flex items-center gap-1.5 font-mono text-[11px] uppercase tracking-wide text-charcoal/45"><flux:icon.calendar-days class="size-3.5" />{{ __('Deadline') }}</dt><dd class="mt-0.5 font-mono font-semibold text-red">{{ $grantOpportunity->deadline?->format('d M Y') ?? __('Rolling') }}</dd></div>
                        <div><dt class="font-mono text-[11px] uppercase tracking-wide text-charcoal/45">{{ __('Type') }}</dt><dd class="mt-0.5 font-medium text-charcoal">{{ $grantOpportunity->funding_type ?: __('Grant') }}</dd></div>
                    </dl>
                    @if(! ($preview ?? false) && ! $grantOpportunity->isOpen())<p class="mt-6 rounded-xl bg-hair p-4 text-center text-[14px] font-medium">{{ $grantOpportunity->publicStatusLabel() === 'Opening soon' ? 'Applications have not opened yet.' : 'Applications are closed.' }}</p>@endif
                    @foreach($links as $link)
                        <a href="{{ route('opportunities.visit', [$grantOpportunity, $link['kind']]) }}" target="_blank" rel="noreferrer noopener" @class([
                            'mt-6 flex w-full items-center justify-center gap-2 rounded-full bg-red px-6 py-3 text-[15px] font-bold text-white transition-colors hover:bg-[#b31f15]' => $loop->first,
                            'mt-4 flex items-center justify-between border-t border-hair pt-4 text-[13.5px] font-medium text-charcoal/75 hover:text-purple' => !$loop->first,
                        ])>{{ __($link['label']) }}<flux:icon.arrow-right class="size-4" /></a>
                    @endforeach
                </div>
            </aside>
        </div>
    </section>
</main>
