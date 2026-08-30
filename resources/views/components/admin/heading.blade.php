@props(['eyebrow', 'title', 'description' => null])
<div class="mb-8 flex flex-wrap items-center justify-between gap-5">
    <div><p class="mb-3 font-mono text-[10px] font-semibold uppercase tracking-[0.18em] text-teal">{{ $eyebrow }}</p><h1 class="text-[28px] font-bold leading-tight tracking-tight">{{ $title }}</h1>@if($description)<p class="mt-2 text-[14px] text-charcoal/55">{{ $description }}</p>@endif</div>
    {{ $slot }}
</div>
