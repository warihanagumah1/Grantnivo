@if(session('success'))
    <div wire:key="success-{{ md5(session('success')) }}" x-data="{ visible: true, timer: null, init() { this.timer = setTimeout(() => this.visible = false, 60000) }, destroy() { clearTimeout(this.timer) } }" x-show="visible" x-transition.opacity role="status" class="mb-6 flex items-start gap-3 rounded-xl border border-teal/20 bg-teal-tint px-4 py-3 text-[14px] text-charcoal">
        <flux:icon.check-circle class="mt-0.5 size-5 shrink-0 text-teal" aria-hidden="true" />
        <p class="min-w-0 flex-1">{{ session('success') }}</p>
        <button type="button" @click="visible = false; clearTimeout(timer)" aria-label="Dismiss success message" class="-my-2 -mr-2 flex size-10 shrink-0 items-center justify-center rounded-lg text-charcoal/65 hover:bg-white/70 hover:text-charcoal focus-visible:outline-2 focus-visible:outline-purple"><flux:icon.x-mark class="size-5" aria-hidden="true" /></button>
    </div>
@endif
