<x-layouts.marketing :title="__('Grantnivo — Funding your mission')">
    <main class="flex-1">
        <section class="bg-white">
            <div class="mx-auto grid w-full max-w-[1180px] grid-cols-1 items-center gap-12 px-6 py-16 md:px-8 md:py-24 lg:grid-cols-[1.05fr_1fr]">
                <div>
                    <span class="inline-flex items-center gap-2 rounded-full bg-purple-tint px-3 py-1 font-mono text-[11px] font-semibold uppercase tracking-[0.14em] text-purple"><span class="size-1.5 rounded-full bg-teal"></span>{{ __('Early access · Coming soon') }}</span>
                    <h1 class="mt-5 text-[40px] font-extrabold leading-[1.05] tracking-[-0.03em] text-charcoal md:text-[54px]">{{ __('The intelligent platform for') }}<br><span class="text-purple">{{ __('funding your mission.') }}</span></h1>
                    <p class="mt-6 max-w-[520px] text-[17px] leading-relaxed text-charcoal/70">{{ __('Grantnivo is building a smarter way for nonprofits, NGOs, and funding teams to discover grants, organise institutional knowledge, and write source-grounded proposals. Browse open opportunities today — and join the waitlist for early access to the full platform.') }}</p>
                    <div class="mt-8 flex flex-wrap items-center gap-3">
                        <a href="{{ route('waitlist') }}" class="inline-flex items-center justify-center rounded-full bg-red px-6 py-3 text-[15px] font-bold text-white shadow-sm shadow-red/20 transition-colors hover:bg-[#b31f15] gap-3">{{ __('Join the Waitlist') }} <flux:icon.arrow-right class="size-5 shrink-0" aria-hidden="true" /></a>
                        <a href="{{ route('opportunities.index') }}" class="inline-flex items-center justify-center gap-2 rounded-full border border-hair bg-white px-6 py-3 text-[15px] font-bold text-charcoal transition-colors hover:border-purple hover:text-purple">{{ __('Browse Opportunities') }}<flux:icon.arrow-right class="size-4" /></a>
                    </div>
                    <p class="mt-6 flex items-center gap-2 text-[13px] text-charcoal/55"><flux:icon.check class="size-4 text-teal" />{{ __('Built for nonprofits, NGOs and funding teams.') }}</p>
                </div>
                <div class="relative">
                    <div class="absolute -inset-6 -z-10 rounded-[32px] bg-gradient-to-br from-purple-tint via-teal-tint/40 to-transparent"></div>
                    <div class="overflow-hidden rounded-2xl border border-hair bg-white shadow-[0_24px_60px_-24px_rgba(36,55,70,0.28)]">
                        <div class="flex flex-wrap items-center gap-2 border-b border-hair bg-offwhite px-4 py-3"><span class="size-2.5 rounded-full bg-[#e2e5ea]"></span><span class="size-2.5 rounded-full bg-[#e2e5ea]"></span><span class="size-2.5 rounded-full bg-[#e2e5ea]"></span><span class="ml-3 font-mono text-[11px] text-charcoal/45">app.grantnivo.com</span><span class="ml-auto rounded-full bg-purple-tint px-2 py-0.5 font-mono text-[10px] font-semibold text-purple">{{ __('Product preview') }}</span></div>
                        <div class="p-4 text-[13px]">
                            <div class="mb-3 flex items-center justify-between"><p class="font-semibold text-charcoal">{{ __('Grant opportunities') }}</p><span class="font-mono text-[11px] text-charcoal/45">{{ __('24 matched') }}</span></div>
                            <div class="mb-3 flex items-center gap-3 rounded-xl border border-teal/25 bg-teal-tint/60 p-3"><div class="flex size-11 shrink-0 items-center justify-center rounded-full bg-white font-mono text-[13px] font-bold text-teal ring-2 ring-teal/30">92%</div><div class="min-w-0"><p class="truncate text-[12.5px] font-semibold text-charcoal">{{ __('Climate Resilience Fund') }}</p><p class="truncate text-[11px] text-charcoal/55">{{ __('Strong match · Grant match score') }}</p></div><span class="ml-auto shrink-0 rounded-full bg-teal px-2 py-0.5 text-[10px] font-semibold text-white">GO</span></div>
                            <p class="mb-2 mt-4 text-[11px] font-semibold uppercase tracking-wide text-charcoal/45">{{ __('Proposal pipeline') }}</p>
                            <div class="space-y-1.5">
                                @foreach ([['Global Health Initiative', 'Drafting', '60%', 'bg-purple'], ['Youth Education Grant', 'Review', '85%', 'bg-teal'], ['Water & Sanitation Fund', 'Submitted', '100%', 'bg-charcoal']] as [$name, $status, $progress, $colour])
                                    <div class="rounded-lg border border-hair p-2.5"><div class="mb-1.5 flex flex-wrap items-center justify-between gap-1"><span class="text-[12px] font-medium text-charcoal">{{ $name }}</span><span class="font-mono text-[10px] text-charcoal/50">{{ $status }}</span></div><div class="h-1.5 w-full overflow-hidden rounded-full bg-offwhite"><div class="h-full rounded-full {{ $colour }}" style="width: {{ $progress }}"></div></div></div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="bg-offwhite py-20 md:py-28">
            <div class="mx-auto w-full max-w-[1180px] px-6 md:px-8">
                <div class="max-w-2xl"><span class="font-mono text-[11px] font-medium uppercase tracking-[0.18em] text-teal">{{ __("What we're building") }}</span><h2 class="mt-4 text-[32px] font-extrabold leading-tight tracking-[-0.02em] text-charcoal md:text-[40px]">{{ __('Everything you need to pursue funding smarter.') }}</h2></div>
                <div class="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ([['magnifying-glass-plus', 'Available now', 'Grant Discovery', 'Find relevant funding opportunities without searching across dozens of sources.', true], ['document-text', 'Coming soon', 'Organisation Brain', 'Build a reusable knowledge base from your programmes, impact data, reports, and past proposals.', false], ['document-check', 'Coming soon', 'AI Proposal Drafting', 'Turn verified organisational knowledge into source-grounded responses tailored to each grant.', false]] as [$icon, $badge, $heading, $copy, $live])
                        <div class="rounded-2xl border border-hair bg-white p-7"><div class="flex items-center justify-between"><div class="flex size-12 items-center justify-center rounded-xl bg-teal-tint text-teal"><flux:icon :name="$icon" class="size-6" /></div><span @class(['rounded-full px-2.5 py-0.5 font-mono text-[10px] font-semibold', 'bg-teal text-white' => $live, 'bg-purple-tint text-purple' => ! $live])>{{ $badge }}</span></div><h3 class="mt-5 text-[19px] font-semibold text-charcoal">{{ $heading }}</h3><p class="mt-2.5 text-[14.5px] leading-relaxed text-charcoal/65">{{ $copy }}</p></div>
                    @endforeach
                </div>
                <div class="mt-10 flex justify-center"><a href="{{ route('platform') }}" class="inline-flex items-center justify-center gap-2 rounded-full border border-hair bg-white px-6 py-3 text-[15px] font-bold text-charcoal transition-colors hover:border-purple hover:text-purple">{{ __('See the full platform') }}<flux:icon.arrow-right class="size-4" /></a></div>
            </div>
        </section>

        <section class="bg-white py-20 md:py-28">
            <div class="mx-auto w-full max-w-[1180px] px-6 md:px-8">
                <div class="flex flex-wrap items-end justify-between gap-4"><div class="max-w-2xl"><span class="font-mono text-[11px] font-medium uppercase tracking-[0.18em] text-teal">{{ __('Available now') }}</span><h2 class="mt-4 text-[32px] font-extrabold leading-tight tracking-[-0.02em] text-charcoal md:text-[40px]">{{ __('Explore open funding opportunities.') }}</h2></div><a href="{{ route('opportunities.index') }}" class="inline-flex items-center justify-center gap-2 rounded-full border border-hair bg-white px-6 py-3 text-[15px] font-bold text-charcoal transition-colors hover:border-purple hover:text-purple">{{ __('View all') }}<flux:icon.arrow-right class="size-4" /></a></div>
                <div class="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">@foreach ($featuredOpportunities as $opportunity)<x-marketing.opportunity-card :$opportunity />@endforeach</div>
            </div>
        </section>

        <section class="bg-lilac py-20 md:py-28">
            <div class="mx-auto grid w-full max-w-[1180px] items-center gap-10 px-6 md:px-8 lg:grid-cols-[1fr_1.1fr]">
                <div><span class="font-mono text-[11px] font-medium uppercase tracking-[0.18em] text-teal">{{ __('Responsible by design') }}</span><h2 class="mt-4 text-[28px] font-bold leading-tight tracking-[-0.02em] text-charcoal md:text-[36px]">{{ __('Built for credible, responsible grant writing.') }}</h2><p class="mt-5 max-w-[480px] text-[16px] leading-relaxed text-charcoal/70">{{ __('When the full platform launches, every AI response will be grounded in your own verified documents — never invented — with human review in control at every step.') }}</p></div>
                <div class="grid gap-4 sm:grid-cols-2">@foreach (['Source-grounded responses', 'No invented organisational facts', 'Private organisational workspace', 'Human review stays in control'] as $item)<div class="flex items-start gap-3 rounded-xl border border-hair bg-white p-4"><span class="flex size-8 shrink-0 items-center justify-center rounded-lg bg-teal-tint text-teal"><flux:icon.shield-check class="size-4" /></span><p class="text-[14px] font-medium leading-snug text-charcoal">{{ __($item) }}</p></div>@endforeach</div>
            </div>
        </section>

        <section class="bg-purple py-20 md:py-24"><div class="mx-auto flex w-full max-w-[1180px] flex-col items-center gap-6 px-6 text-center md:px-8"><h2 class="max-w-2xl text-[30px] font-bold leading-tight tracking-[-0.02em] text-white md:text-[40px]">{{ __('Be first to pursue funding smarter.') }}</h2><p class="max-w-[540px] text-[16px] leading-relaxed text-white/75">{{ __('Join the Grantnivo waitlist and be among the first organisations to experience smarter grant discovery and proposal development when we launch.') }}</p><a href="{{ route('waitlist') }}" class="inline-flex items-center justify-center gap-2 rounded-full bg-red px-7 py-3.5 text-[15px] font-bold text-white shadow-sm shadow-red/20 transition-colors hover:bg-[#b31f15]">{{ __('Join the Waitlist') }}<flux:icon.arrow-right class="size-5 shrink-0" aria-hidden="true" /></a></div></section>
    </main>
</x-layouts.marketing>
