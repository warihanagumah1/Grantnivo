<div class="max-w-4xl">
    <x-admin.success-notice />
    <a href="{{ route('admin.opportunities') }}" class="admin-text-link mb-5"><flux:icon.arrow-left class="size-4" />Opportunities</a>
    <x-admin.heading eyebrow="Bulk import" title="Add opportunities from a CSV" description="Every imported opportunity is saved privately as a draft. Nothing is published automatically." />
    <div class="my-6 rounded-2xl border border-hair bg-white p-6">
        <h2 class="text-[17px] font-semibold">1. Prepare your spreadsheet</h2>
        <p class="mt-2 text-[14px] text-charcoal/70">Use the template in Excel, Google Sheets, or another spreadsheet app, then export it as <strong>CSV UTF-8 (comma-delimited)</strong>. Only the title is required for a draft.</p>
        <a href="{{ route('admin.opportunities.import-template') }}" class="admin-secondary mt-4">Download CSV template</a>
        <p class="mt-3 text-[13px] text-charcoal/65">The template includes every supported field and a formatted example. Replace or delete the example row before uploading.</p>
        <ul class="mt-5 list-disc space-y-2 pl-5 text-[13px] text-charcoal/70">
            <li>Up to 250 opportunities per file, maximum 2 MB. Each upload creates new drafts; it does not update existing opportunities.</li>
            <li>Use YYYY-MM-DD for deadlines; leave blank for rolling deadlines. Funding amount should be a whole number without currency symbols.</li>
            <li>Separate sectors and countries with commas inside their spreadsheet cells. Use Grant or Partnership, and USD, EUR, GBP, or GHS.</li>
            <li>Summary and details accept Markdown (recommended), supported HTML, or plain text. Use an HTTPS image URL, or upload images individually after import.</li>
            <li>Any status column is ignored: imported opportunities always start as drafts. Delete the example row before importing your own data.</li>
        </ul>
    </div>
    <details class="mb-6 rounded-2xl border border-hair bg-white p-6">
        <summary class="cursor-pointer text-[15px] font-semibold text-purple">How to format summary and additional details</summary>
        <p class="mt-3 text-[13px] text-charcoal/70">Use Markdown inside the <code>summary</code> and <code>description</code> cells: it is easier to read and becomes formatted text on the website. The template uses Markdown. Other fields should stay plain text.</p>
        <div class="mt-4 grid gap-4 md:grid-cols-2">
            <div class="min-w-0"><h3 class="mb-2 text-[12px] font-semibold text-charcoal/65">Write this in one cell</h3><pre class="overflow-x-auto rounded-xl bg-offwhite p-4 text-[12px] leading-relaxed"><code>## Eligibility

For **nonprofits** with *local experience*.

- Registered organisation

### How to apply

1. Send your proposal</code></pre></div>
            <div class="grant-rich-text rounded-xl border border-hair p-4 text-[14px]"><h2>Eligibility</h2><p>For <strong>nonprofits</strong> with <em>local experience</em>.</p><ul><li>Registered organisation</li></ul><h3>How to apply</h3><ol><li>Send your proposal</li></ol></div>
        </div>
        <p class="mt-4 text-[13px] text-charcoal/70">Start headings and list items on new lines <strong>inside the same spreadsheet cell</strong>. Put a space after <code>##</code>, <code>-</code>, or <code>1.</code>, and leave a blank line between sections. For links, use <code>[Programme guide](https://example.org)</code>; for a quote, start a line with <code>&gt; </code>.</p>
        <p class="mt-3 text-[13px] text-charcoal/70">HTML still works: <code>&lt;h2&gt;Eligibility&lt;/h2&gt;</code> and <code>## Eligibility</code> produce the same heading. You can use HTML in one cell and Markdown in another. When mixing them in one cell, keep HTML blocks separate from Markdown with a blank line.</p>
        <p class="mt-3 text-[12px] text-charcoal/60">CSV does not retain spreadsheet font styling. Export as CSV UTF-8 so commas, quotes, and line breaks stay inside their cells. Plain-text line breaks are preserved, and unsafe HTML and links are removed. After import, use the rich-text editor to polish each draft before publishing.</p>
    </details>
    <form wire:submit="import" x-data="{ uploading: false, uploadFailed: false }"
        x-on:livewire-upload-start="uploading = true; uploadFailed = false"
        x-on:livewire-upload-finish="uploading = false"
        x-on:livewire-upload-error="uploading = false; uploadFailed = true"
        x-on:livewire-upload-cancel="uploading = false"
        x-on:submit.capture="if (uploading) { $event.preventDefault(); $event.stopImmediatePropagation() }"
        class="rounded-2xl border border-hair bg-white p-6">
        <h2 class="text-[17px] font-semibold">2. Import as drafts</h2>
        <label for="import-file" class="mb-2 mt-4 block text-[13px] font-medium">CSV file</label>
        <input id="import-file" wire:model="file" type="file" accept=".csv,text/csv" required class="admin-input file:mr-3 file:rounded-lg file:border-0 file:bg-purple-tint file:px-3 file:py-1 file:text-purple" aria-describedby="import-errors" />
        <p x-show="uploading" x-cloak role="status" class="mt-2 text-[13px] text-purple">Uploading your CSV…</p>
        <p x-show="uploadFailed" x-cloak role="alert" class="mt-2 text-[13px] text-red">Upload failed. Check the file size and your connection, then choose the file again.</p>
        <div id="import-errors">
            @if($errors->any())<div role="alert" class="mt-4 rounded-xl border border-red/25 bg-red/5 p-4 text-[13px] text-red"><p class="font-semibold">Nothing was imported. Correct these issues and try again:</p><ul class="mt-2 max-h-72 list-disc space-y-1 overflow-auto pl-5">@foreach($errors->all() as $message)<li>{{ $message }}</li>@endforeach</ul></div>@endif
        </div>
        <div class="mt-5 flex flex-wrap items-center gap-4">
            <button type="submit" x-bind:disabled="uploading || !$wire.file" wire:loading.attr="disabled" class="admin-primary"><span wire:loading.remove wire:target="import">Import as drafts</span><span wire:loading wire:target="import">Validating and importing…</span></button>
            <a href="{{ route('admin.opportunities') }}" class="admin-secondary">Cancel</a>
        </div>
        <p class="mt-3 text-[12px] text-charcoal/55">The whole file must pass validation. If any row fails, no opportunities are imported.</p>
    </form>
</div>
