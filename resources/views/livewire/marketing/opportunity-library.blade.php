<main class="flex-1">
    <section class="border-b border-hair bg-lilac">
        <div class="mx-auto w-full max-w-site px-6 py-16 md:px-8 md:py-20">
            <div class="max-w-3xl"><span class="font-mono text-[11px] font-medium uppercase tracking-[0.18em] text-teal">{{ __('Opportunities') }}</span><h1 class="mt-4 text-[38px] font-extrabold leading-[1.04] tracking-[-0.03em] text-charcoal md:text-[52px]">{{ __('Discover funding opportunities that match your work.') }}</h1><p class="mt-5 max-w-[620px] text-[17px] leading-relaxed text-charcoal/70">{{ __('Grantnivo continuously scans hundreds of funders and surfaces grants and partnerships your organisation can pursue. Filter to find exactly what fits.') }}</p></div>
        </div>
    </section>

    <section class="bg-white py-12 md:py-16">
        <div class="mx-auto w-full max-w-site px-6 md:px-8">
            <div class="relative w-full"><flux:icon.magnifying-glass class="pointer-events-none absolute left-4 top-1/2 size-5 -translate-y-1/2 text-purple/70" /><input wire:model.live.debounce.500ms="search" type="search" placeholder="{{ __('Search by title, funder, sector or country…') }}" aria-label="{{ __('Search by title, funder, sector or country…') }}" class="w-full rounded-2xl border-2 border-purple/35 bg-white py-4 pl-12 pr-4 text-[16px] font-semibold text-charcoal placeholder:text-charcoal/65 shadow-sm outline-none transition focus:border-purple focus:bg-white focus:ring-2 focus:ring-purple/15" /></div>

            <div class="opportunity-filters mt-4 w-full">
                <div class="flex min-w-0 items-stretch rounded-xl border-2 border-purple/30 bg-white p-1" role="group" aria-label="{{ __('Opportunity status') }}">@foreach (['open' => 'Open', 'closed' => 'Closed', 'all' => 'All'] as $value => $label)<button type="button" aria-pressed="{{ $status === $value ? 'true' : 'false' }}" wire:click="$set('status', '{{ $value }}')" @class(['flex-1 rounded-lg px-3 py-2 text-[14px] font-semibold transition-colors focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-purple', 'bg-purple text-white' => $status === $value, 'text-charcoal/80 hover:bg-lilac hover:text-purple' => $status !== $value])>{{ __($label) }}</button>@endforeach</div>
                <div class="flex min-w-0 flex-wrap items-stretch rounded-xl border-2 border-purple/30 bg-white p-1" role="group" aria-label="{{ __('Funding type') }}">@foreach (['all' => 'All', 'grant' => 'Grant', 'partnership' => 'Partnership', 'fellowship' => 'Fellowship'] as $value => $label)<button type="button" aria-pressed="{{ $type === $value ? 'true' : 'false' }}" wire:click="$set('type', '{{ $value }}')" @class(['flex-1 rounded-lg px-3 py-2 text-[14px] font-semibold transition-colors focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-purple', 'bg-purple text-white' => $type === $value, 'text-charcoal/80 hover:bg-lilac hover:text-purple' => $type !== $value])>{{ __($label) }}</button>@endforeach</div>

                <div class="relative min-w-0" x-data="{ open: false, search: '' }" @click.outside="open = false" @keydown.escape.window="open = false">
                    <button type="button" @click="open = !open" x-bind:aria-expanded="open.toString()" @class(['flex h-full min-h-12 w-full items-center justify-between gap-2 rounded-xl border-2 bg-white px-3 py-2.5 text-[14px] font-semibold transition focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-purple', 'border-purple/30 text-charcoal' => count($selectedSectors) === count($sectors), 'border-purple text-purple' => count($selectedSectors) !== count($sectors)])>
                        {{ count($selectedSectors) === count($sectors) ? __('All sectors') : (count($selectedSectors) === 1 ? $selectedSectors[0] : trans_choice(':count sector|:count sectors', count($selectedSectors), ['count' => count($selectedSectors)])) }}
                        <flux:icon.chevron-down class="size-4 shrink-0 transition-transform" x-bind:class="open && 'rotate-180'" />
                    </button>
                    <div x-cloak x-show="open" x-transition class="absolute left-0 z-30 mt-2 w-72 max-w-[calc(100vw-3rem)] rounded-2xl border border-hair bg-white p-3 shadow-xl">
                        <input x-model="search" type="search" aria-label="{{ __('Search sectors') }}" placeholder="{{ __('Search sectors…') }}" class="w-full rounded-xl border-2 border-purple/30 px-3 py-2.5 text-[14px] font-semibold placeholder:text-charcoal/65 outline-none focus:border-purple focus:ring-2 focus:ring-purple/15" />
                        <div class="mt-2 flex items-center justify-between border-b border-hair pb-2 text-[12px]"><button type="button" wire:click="selectAllSectors" class="font-semibold text-purple">{{ __('Select all') }}</button><button type="button" wire:click="clearSectors" class="text-charcoal/55 hover:text-red">{{ __('Clear') }}</button></div>
                        <div class="mt-2 max-h-56 space-y-1 overflow-auto">@foreach ($sectors as $option)<label x-show="@js(strtolower($option)).includes(search.toLowerCase())" class="flex cursor-pointer items-center gap-2 rounded-lg px-2 py-2 text-[13px] hover:bg-lilac"><input wire:model.live="selectedSectors" type="checkbox" value="{{ $option }}" class="rounded border-hair text-purple focus:ring-purple" /><span>{{ $option }}</span></label>@endforeach</div>
                    </div>
                </div>

                <div class="relative min-w-0" x-data="{ open: false, search: '' }" @click.outside="open = false" @keydown.escape.window="open = false">
                    <button type="button" @click="open = !open" x-bind:aria-expanded="open.toString()" @class(['flex h-full min-h-12 w-full items-center justify-between gap-2 rounded-xl border-2 bg-white px-3 py-2.5 text-[14px] font-semibold transition focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-purple', 'border-purple/30 text-charcoal' => count($selectedCountries) === count($countries), 'border-purple text-purple' => count($selectedCountries) !== count($countries)])>
                        {{ count($selectedCountries) === count($countries) ? __('All countries') : (count($selectedCountries) === 1 ? $selectedCountries[0] : trans_choice(':count country|:count countries', count($selectedCountries), ['count' => count($selectedCountries)])) }}
                        <flux:icon.chevron-down class="size-4 shrink-0 transition-transform" x-bind:class="open && 'rotate-180'" />
                    </button>
                    <div x-cloak x-show="open" x-transition class="absolute left-0 z-30 mt-2 w-72 max-w-[calc(100vw-3rem)] rounded-2xl border border-hair bg-white p-3 shadow-xl">
                        <input x-model="search" type="search" aria-label="{{ __('Search countries') }}" placeholder="{{ __('Search countries…') }}" class="w-full rounded-xl border-2 border-purple/30 px-3 py-2.5 text-[14px] font-semibold placeholder:text-charcoal/65 outline-none focus:border-purple focus:ring-2 focus:ring-purple/15" />
                        <div class="mt-2 flex items-center justify-between border-b border-hair pb-2 text-[12px]"><button type="button" wire:click="selectAllCountries" class="font-semibold text-purple">{{ __('Select all') }}</button><button type="button" wire:click="clearCountries" class="text-charcoal/55 hover:text-red">{{ __('Clear') }}</button></div>
                        <div class="mt-2 max-h-56 space-y-1 overflow-auto">@foreach ($countries as $option)<label x-show="@js(strtolower($option)).includes(search.toLowerCase())" class="flex cursor-pointer items-center gap-2 rounded-lg px-2 py-2 text-[13px] hover:bg-lilac"><input wire:model.live="selectedCountries" type="checkbox" value="{{ $option }}" class="rounded border-hair text-purple focus:ring-purple" /><span>{{ $option }}</span></label>@endforeach</div>
                    </div>
                </div>

                <select wire:model.live="sort" class="min-h-12 w-full min-w-0 rounded-xl border-2 border-purple/30 bg-white px-3 py-2.5 text-[14px] font-semibold text-charcoal sm:col-span-2 lg:col-span-1 outline-none transition focus:border-purple focus:ring-2 focus:ring-purple/15" aria-label="{{ __('Sort by') }}"><option value="deadline">{{ __('Deadline · soonest') }}</option><option value="amount-desc">{{ __('Amount · high to low') }}</option><option value="amount-asc">{{ __('Amount · low to high') }}</option><option value="az">{{ __('Title · A–Z') }}</option></select>
            </div>

            <div class="mt-6 flex items-center justify-between border-t border-hair pt-4"><span class="font-mono text-[12px] text-charcoal/45">{{ trans_choice(':count opportunity|:count opportunities', $opportunities->total(), ['count' => $opportunities->total()]) }}</span><span wire:loading class="font-mono text-[11px] text-purple">{{ __('Updating…') }}</span></div>
            <div class="mt-6 grid gap-6 md:grid-cols-2 lg:grid-cols-3">@forelse ($opportunities as $opportunity)<x-marketing.opportunity-card :$opportunity wire:key="opportunity-{{ $opportunity->id }}" />@empty<div class="rounded-2xl border border-hair bg-lilac p-8 text-center md:col-span-2 lg:col-span-3"><h2 class="text-lg font-bold text-charcoal">{{ __('No opportunities match') }}</h2><p class="mt-2 text-sm text-charcoal/60">{{ __('Try another search or select more sectors and countries.') }}</p></div>@endforelse</div>
            @if ($opportunities->hasPages())<div class="mt-10">{{ $opportunities->links() }}</div>@endif
        </div>
    </section>
</main>
