@props(['field'])
@php($serverMessage = $errors->first($field))
<p
    id="{{ str_replace('.', '-', $field) }}-error"
    data-error-for="{{ $field }}"
    data-server-message="{{ $serverMessage }}"
    x-show="Boolean(messages[@js($field)] ?? @js($serverMessage))"
    x-text="messages[@js($field)] ?? @js($serverMessage)"
    @if (! $serverMessage) style="display: none" @endif
    aria-live="polite"
    {{ $attributes->class('form-validation-feedback') }}
>{{ $serverMessage }}</p>
