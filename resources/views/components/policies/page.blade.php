@props(['title', 'intro', 'sections' => []])
<x-layouts.marketing :title="$title">
    <main class="flex-1">
        <section class="border-b border-hair bg-lilac">
            <div class="mx-auto w-full max-w-site px-6 py-14 md:px-8 md:py-20">
                <!-- <p class="font-mono text-[11px] uppercase tracking-[0.18em] text-purple">Your information. Your choices.</p> -->
                <h1 class="mt-4 text-[36px] font-extrabold tracking-tight text-charcoal md:text-[48px]">{{ $title }}</h1>
                <p class="mt-5 max-w-3xl text-[17px] leading-relaxed text-charcoal/75">{{ $intro }}</p>
                <p class="mt-5 text-[13px] font-medium text-charcoal/60">Last updated: 31 August 2026 · Covers the Grantnivo website and waitlist</p>
            </div>
        </section>
        <div class="mx-auto grid w-full max-w-site gap-10 px-6 py-12 md:px-8 lg:grid-cols-[240px_minmax(0,1fr)]">
            <aside class="lg:sticky lg:top-28 lg:self-start">
                <nav aria-label="On this page" class="rounded-2xl border border-hair bg-offwhite p-5">
                    <p class="mb-3 text-[13px] font-bold text-charcoal">On this page</p>
                    <ul class="grid gap-3 text-[14px] text-charcoal/75">@foreach($sections as $id => $label)<li><a href="#{{ $id }}" class="hover:text-purple hover:underline">{{ $label }}</a></li>@endforeach</ul>
                </nav>
            </aside>
            <article class="min-w-0 max-w-4xl">
                <!-- @if(!config('legal.reviewed') || !config('legal.contact_email') || !config('legal.country') || !config('legal.address'))
                    <div role="note" class="mb-8 rounded-xl border border-amber-300 bg-amber-50 p-4 text-[14px] leading-relaxed text-charcoal"><strong>Draft for review.</strong> The operator’s details and legal review must be completed before these policies are used for a public launch.</div>
                @endif -->
                <div class="policy-copy">{{ $slot }}</div>
            </article>
        </div>
    </main>
</x-layouts.marketing>
