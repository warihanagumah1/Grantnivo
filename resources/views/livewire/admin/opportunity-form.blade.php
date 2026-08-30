<div x-data="{ uploading: false, uploadFailed: false }"
    x-on:livewire-upload-start="uploading = true; uploadFailed = false"
    x-on:livewire-upload-finish="uploading = false"
    x-on:livewire-upload-error="uploading = false; uploadFailed = true"
    x-on:livewire-upload-cancel="uploading = false"
    x-on:submit.capture="if (uploading) { $event.preventDefault(); $event.stopImmediatePropagation() }">
    <a href="{{ route('admin.opportunities') }}" class="admin-text-link mb-5"><flux:icon.arrow-left class="size-4" />Opportunities</a>
    <h1 class="text-[28px] font-bold tracking-tight">{{ $opportunityId ? 'Edit opportunity' : 'New opportunity' }}</h1>
    <p class="mt-2 text-[14px] text-charcoal/65">Save your progress privately, or publish when it’s ready. Fields marked * are required to publish.</p>
    @if(!$opportunityId)<p class="mt-3 text-[13px] text-charcoal/65">Adding several grants? <a href="{{ route('admin.opportunities.import') }}" class="admin-text-link">Bulk upload a CSV</a> or <a href="{{ route('admin.opportunities.import-template') }}" class="admin-text-link">download the template</a>.</p>@endif
    @if($opportunityId && $savedStatus === 'draft')<p class="mt-3 inline-flex rounded-full bg-purple-tint px-3 py-1 text-[12px] font-semibold text-purple">Draft · Not visible on the website</p>@endif
    <x-ui.validated-form wire:submit="save" spacing="mt-8 grid gap-8 xl:grid-cols-[1.5fr_1fr]">
        <div class="grid gap-4">
            @if($errors->any())<div role="alert" class="rounded-xl border border-red/20 bg-red/5 p-4 text-[13px] text-red">Please correct the highlighted fields before saving.</div>@endif
            <x-admin.input label="Grant title" field="title" placeholder="Resilient Communities Fund" maxlength="255" required />
            <div class="grid gap-4 sm:grid-cols-2"><x-admin.input label="Funder" field="funder" placeholder="Wellspring Foundation" maxlength="255" required /><x-admin.input label="Sector" field="sector" placeholder="Climate & Livelihoods" maxlength="255" required /></div>
            <div class="grid gap-4 sm:grid-cols-2">
                <div class="grid gap-1.5"><label for="funding_type" class="text-[13px] font-medium text-charcoal/75">Type</label><select id="funding_type" wire:model="form.funding_type" class="admin-input"><option>Grant</option><option>Partnership</option></select><x-ui.field-error field="form.funding_type" /></div>
                <div class="grid gap-1.5"><label for="status" class="text-[13px] font-medium text-charcoal/75">Status when published</label><select id="status" wire:model="form.status" class="admin-input"><option value="active">Open</option><option value="closed">Closed</option><option value="closing_soon">Closing soon</option></select><x-ui.field-error field="form.status" /></div>
            </div>
            <div class="grid gap-4 sm:grid-cols-2"><div class="grid grid-cols-[1fr_90px] gap-2"><x-admin.input label="Funding amount" field="maximum_award" type="number" placeholder="250000" min="0" max="999999999999" step="1" /><div class="grid content-start gap-1.5"><label for="currency" class="text-[13px] font-medium text-charcoal/75">Currency</label><select id="currency" wire:model="form.currency" class="admin-input px-2!"><option>USD</option><option>EUR</option><option>GBP</option><option>GHS</option></select><x-ui.field-error field="form.currency" /></div></div><x-admin.input label="Deadline (blank for rolling)" field="deadline" type="date" /></div>
            <x-admin.input label="Eligible countries" field="countries" data-list-max="100" data-item-max="120" placeholder="Kenya, Uganda, Tanzania" maxlength="2000" required />
            <fieldset class="grid gap-3 rounded-xl border-2 border-slate-300 bg-white p-4">
                <legend class="px-1 text-[13px] font-semibold text-charcoal">Opportunity image</legend>
                <div class="flex flex-wrap gap-5 text-[14px]">
                    <label class="flex cursor-pointer items-center gap-2"><input type="radio" name="image-source" value="url" x-model="$wire.imageSource" class="accent-purple" />Image URL</label>
                    <label class="flex cursor-pointer items-center gap-2"><input type="radio" name="image-source" value="upload" x-model="$wire.imageSource" class="accent-purple" />Upload an image</label>
                </div>
                <div x-show="$wire.imageSource === 'url'">
                    <x-admin.input label="Image URL" field="image_url" type="url" data-protocols="https:" x-bind:disabled="$wire.imageSource !== 'url'" placeholder="https://images.unsplash.com/photo-…" maxlength="2048" />
                    <p class="mt-1 text-[11px] text-charcoal/55">Use a public HTTPS image link, or leave blank for no image.</p>
                </div>
                <div x-show="$wire.imageSource === 'upload'" x-cloak class="grid gap-2">
                    <label for="image" class="text-[13px] font-medium text-charcoal/75">Choose image</label>
                    <input id="image" wire:model="image" type="file" accept="image/jpeg,image/png,image/webp" x-bind:disabled="$wire.imageSource !== 'upload'" class="admin-input file:mr-3 file:rounded-lg file:border-0 file:bg-purple-tint file:px-3 file:py-1 file:font-medium file:text-purple" aria-describedby="image-hint image-error" />
                    <p id="image-hint" class="text-[11px] text-charcoal/55">JPG, PNG or WebP. Maximum 5 MB and 6,000 × 6,000 pixels.@if($savedImageUrl) Choose a file only to replace the current image.@endif</p>
                    <p x-show="uploading" x-cloak role="status" class="text-[12px] text-purple">Uploading image… Please wait before saving.</p>
                    <p x-show="uploadFailed" x-cloak role="alert" class="text-[12px] text-red">Upload failed. Check the file size and connection, then choose the image again.</p>
                    <x-ui.field-error field="image" />
                </div>
                <x-ui.field-error field="imageSource" />
                <x-admin.input label="Image description (alt text)" field="image_alt" x-bind:required="$wire.imageSource === 'upload' || Boolean($wire.form.image_url)" placeholder="Community members carrying water" maxlength="255" />
            </fieldset>
            <x-admin.rich-text field="summary" label="Summary" :limit="2000" required />
            <x-admin.rich-text field="description" label="Additional details (optional)" :limit="20000" />
            <fieldset class="grid gap-3 rounded-xl border-2 border-slate-300 p-4">
                <legend class="px-1 text-[13px] font-semibold">Website links (optional)</legend>
                <x-admin.input label="Learn more — main button" field="learn_more_url" type="url" placeholder="https://…/programme" maxlength="2048" />
                <p class="text-[12px] text-charcoal/60">Link to the full grant details. Add the links below only if they lead somewhere different. Empty or duplicate links are hidden.</p>
                <div class="grid gap-4 sm:grid-cols-2"><x-admin.input label="Application link" field="opportunity_url" type="url" placeholder="https://…/apply" maxlength="2048" /><x-admin.input label="Funder website" field="funder_url" type="url" placeholder="https://wellspringfoundation.org" maxlength="2048" /></div>
            </fieldset>
            <div class="mt-2 border-t border-hair pt-5">
                <div class="flex flex-wrap items-center gap-3">
                    <button type="submit" disabled x-bind:disabled="blocked || uploading" x-bind:aria-disabled="(blocked || uploading).toString()" wire:loading.attr="disabled" class="admin-primary"><span wire:loading.remove wire:target="save">{{ $savedStatus === 'draft' ? 'Publish opportunity' : 'Save changes' }}</span><span wire:loading wire:target="save">Saving…</span></button>
                    @if($savedStatus === 'draft')
                        <button type="button" wire:click="saveDraft" x-bind:disabled="uploading || submitting || !$wire.form.title.trim()" wire:loading.attr="disabled" class="admin-secondary"><span wire:loading.remove wire:target="saveDraft">Save as draft</span><span wire:loading wire:target="saveDraft">Saving draft…</span></button>
                    @else
                        <x-admin.confirm-action action="saveDraft()" title="Move opportunity to draft?" message="Your changes will be saved as a draft and this opportunity will no longer be visible on the public website. You can publish it again when ready." confirm-label="Move to draft" disabled-when="uploading || submitting || !$wire.form.title.trim()" class="admin-secondary">Move to draft</x-admin.confirm-action>
                    @endif
                    <a href="{{ route('admin.opportunities') }}" class="admin-text-link px-2">Cancel</a>
                </div>
                <p class="mt-3 text-[12px] text-charcoal/55">Only a title is needed to start a draft. Find it in Opportunities → Drafts, then edit and publish when ready.</p>
            </div>
        </div>
        <aside class="xl:sticky xl:top-8 xl:self-start" x-data="{ previewImage: $wire.form.image_url }" x-init="$watch('$wire.form.image_url', value => { clearTimeout($el._previewTimer); $el._previewTimer = setTimeout(() => previewImage = value, 500) })">
            <p class="font-mono text-[10px] font-semibold uppercase tracking-[0.16em] text-charcoal/40">Card preview</p>
            <div class="mt-3 overflow-hidden rounded-2xl border border-hair bg-white">
                <div class="aspect-[3/2] bg-purple-tint">
                    <template x-if="$wire.imageSource === 'url' && previewImage && previewImage.startsWith('https://')">
                        <img x-bind:src="previewImage" x-bind:alt="$wire.form.image_alt" class="h-full w-full object-cover" />
                    </template>
                    <template x-if="$wire.imageSource === 'url' && (!previewImage || !previewImage.startsWith('https://'))">
                        <div class="flex h-full items-center justify-center text-[12px] text-charcoal/55">Add an image URL or upload an image</div>
                    </template>
                    <div x-show="$wire.imageSource === 'upload'" class="h-full">
                        @if($image && !$errors->has('image'))
                            <img src="{{ $image->temporaryUrl() }}" x-bind:alt="$wire.form.image_alt" class="h-full w-full object-cover" />
                        @elseif($savedImageUrl)
                            <img src="{{ $savedImageUrl }}" x-bind:alt="$wire.form.image_alt" class="h-full w-full object-cover" />
                        @else
                            <div class="flex h-full items-center justify-center text-[12px] text-charcoal/55">Choose an image to preview it</div>
                        @endif
                    </div>
                </div>
                <div class="p-4">
                    <div class="flex gap-2 text-[10px] font-semibold">
                        <span class="rounded-full bg-purple-tint px-2 py-0.5 text-purple" x-text="$wire.form.funding_type"></span>
                        <span class="rounded-full bg-teal-tint px-2 py-0.5 text-teal" x-text="({ active: 'Open', closed: 'Closed', draft: 'Draft', closing_soon: 'Closing soon' })[$wire.form.status] || 'Draft'"></span>
                    </div>
                    <p class="mt-2 font-mono text-[11px] uppercase tracking-wide text-charcoal/45" x-text="$wire.form.sector || 'Sector'"></p>
                    <h3 class="mt-1.5 text-[16px] font-bold leading-snug" x-text="$wire.form.title || 'Grant title'"></h3>
                    <p class="mt-1 text-[12.5px] text-charcoal/55" x-text="$wire.form.funder || 'Funder'"></p>
                    <div class="mt-3 flex justify-between gap-3 border-t border-hair pt-3 font-mono">
                        <span class="text-[13px] font-bold" x-text="$wire.form.currency + ' ' + ($wire.form.maximum_award !== '' && $wire.form.maximum_award !== null && Number.isFinite(Number($wire.form.maximum_award)) ? new Intl.NumberFormat('en-US').format(Number($wire.form.maximum_award)) : '—')"></span>
                        <span class="text-[11px] text-red" x-text="$wire.form.deadline || 'Rolling deadline'"></span>
                    </div>
                </div>
            </div>
            <p class="mt-3 text-[12px] text-charcoal/45">Expired deadlines automatically display as closed.</p>
        </aside>
    </x-ui.validated-form>
</div>
