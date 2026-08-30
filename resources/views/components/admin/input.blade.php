@props(['label', 'field', 'type' => 'text', 'placeholder' => '', 'required' => false])
<div class="grid gap-1.5">
    <label for="{{ $field }}" class="text-[13px] font-medium text-charcoal/75">{{ $label }}@if($required)<span class="text-red" aria-hidden="true"> *</span>@endif</label>
    <input id="{{ $field }}" wire:model="form.{{ $field }}" type="{{ $type }}" placeholder="{{ $placeholder }}" @required($required) {{ $attributes->class(['admin-input']) }} aria-invalid="{{ $errors->has('form.'.$field) ? 'true' : 'false' }}" aria-describedby="form-{{ $field }}-error" />
    <x-ui.field-error :field="'form.'.$field" />
</div>
