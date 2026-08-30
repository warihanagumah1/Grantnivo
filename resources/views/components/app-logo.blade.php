@props([
    'sidebar' => false,
    'href' => null,
])

<a
    href="{{ $href ?? route('home') }}"
    {{ $attributes->class([
        'inline-flex shrink-0 items-center focus-visible:rounded-lg focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary focus-visible:ring-offset-2',
        'max-w-40' => $sidebar,
        'max-w-44' => ! $sidebar,
    ]) }}
    aria-label="{{ config('app.name', 'Grantnivo') }}"
>
    <img
        src="{{ asset('images/grantnivo-logo.png') }}"
        alt="{{ config('app.name', 'Grantnivo') }}"
        width="5088"
        height="1299"
        class="h-11 w-auto object-contain"
    />
</a>
