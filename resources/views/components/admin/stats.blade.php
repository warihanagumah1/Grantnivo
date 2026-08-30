@props(['stats', 'icons' => ['eye', 'arrow-trending-up', 'square-3-stack-3d', 'bell']])
<div class="mb-8 grid grid-cols-2 gap-4 xl:grid-cols-4">
    @foreach($stats as $label => $value)
        <div class="rounded-2xl border border-hair bg-white p-6"><span class="mb-4 flex size-10 items-center justify-center rounded-xl bg-teal-tint text-teal"><flux:icon :name="$icons[$loop->index]" class="size-[18px]" /></span><p class="font-mono text-[30px] font-semibold leading-tight">{{ $label === 'Click-through rate' ? $value.'%' : number_format($value) }}</p><p class="mt-2 text-[13px] text-charcoal/50">{{ $label }}</p></div>
    @endforeach
</div>
