@props([
    'variant' => 'primary',
])

<flux:button
    type="submit"
    :variant="$variant"
    disabled
    x-bind:disabled="blocked"
    x-bind:aria-disabled="blocked.toString()"
    {{ $attributes }}
>
    {{ $slot }}
</flux:button>
