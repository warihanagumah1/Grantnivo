@props([
    'spacing' => 'space-y-6',
])

<form
    data-validated-form
    x-data="validatedForm"
    x-on:input.capture="refreshValidity()"
    x-on:change.capture="refreshValidity()"
    x-on:focusout="handleBlur($event)"
    x-on:focusin="$nextTick(() => refreshValidity())"
    x-on:submit.capture="handleSubmit($event)"
    x-on:reset="$nextTick(() => refreshValidity())"
    x-on:pageshow.window="finishRequest()"
    x-on:validation-request-finished="finishRequest()"
    x-bind:aria-busy="submitting.toString()"
    {{ $attributes->class($spacing) }}
>
    {{ $slot }}
</form>
