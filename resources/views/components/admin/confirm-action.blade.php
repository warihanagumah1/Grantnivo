@props(['action', 'title', 'message', 'confirmLabel', 'danger' => false, 'disabledWhen' => 'false'])

<div x-data="confirmationDialog" x-id="['confirmation-title', 'confirmation-description']" class="inline-flex">
    <button type="button" x-ref="trigger" @click="open()" x-bind:disabled="busy || ({{ $disabledWhen }})" wire:loading.attr="disabled" {{ $attributes }}>{{ $slot }}</button>
    <dialog x-ref="dialog" wire:ignore.self x-bind:aria-labelledby="$id('confirmation-title')" x-bind:aria-describedby="$id('confirmation-description')"
        @cancel.prevent="cancel()" @keydown.escape.prevent.stop="cancel()" @click.self="cancel()" @close="if ($refs.trigger.isConnected) $refs.trigger.focus()"
        class="m-auto w-[calc(100%-2rem)] max-w-md rounded-2xl border border-hair bg-white p-0 text-charcoal shadow-2xl backdrop:bg-charcoal/45">
        <div class="p-6 sm:p-7">
            <div @class(['mb-5 flex size-11 items-center justify-center rounded-full', 'bg-red/10 text-red' => $danger, 'bg-purple-tint text-purple' => !$danger])>
                <flux:icon.exclamation-triangle class="size-5" />
            </div>
            <h2 x-bind:id="$id('confirmation-title')" class="text-[20px] font-semibold leading-tight">{{ $title }}</h2>
            <p x-bind:id="$id('confirmation-description')" class="mt-3 text-[14px] font-normal leading-relaxed text-charcoal/65">{{ $message }}</p>
            <p x-show="error" x-cloak x-text="error" role="alert" class="mt-4 text-[13px] text-red"></p>
            <div class="mt-7 flex flex-wrap justify-end gap-3">
                <button type="button" x-ref="cancel" autofocus @click="cancel()" :disabled="busy" class="admin-secondary">Cancel</button>
                <button type="button" @click="confirm(() => $wire.{{ $action }})" :disabled="busy" :aria-busy="busy" @class(['admin-primary', 'bg-red! hover:bg-red/90!' => $danger])>
                    <span x-show="!busy">{{ $confirmLabel }}</span><span x-show="busy" x-cloak role="status">Working…</span>
                </button>
            </div>
        </div>
    </dialog>
</div>
