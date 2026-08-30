@props(['field', 'label', 'limit', 'required' => false])
<div class="grid gap-1.5" x-data="richTextEditor(@js($field), {{ $limit }})">
    <label id="{{ $field }}-label" for="{{ $field }}" class="text-[13px] font-medium text-charcoal/75">{{ $label }} @if($required)<span class="text-red" aria-hidden="true">*</span>@endif</label>
    <div wire:ignore class="rich-editor" x-bind:class="{ 'rich-editor-invalid': messages['form.' + field] }">
        <div class="rich-toolbar" role="group" aria-label="{{ $label }} formatting">
            <button type="button" x-on:click="command('bold')" x-bind:aria-pressed="active.bold" title="Bold (Ctrl/Cmd+B)"><strong>B</strong><span class="sr-only">Bold</span></button>
            <button type="button" x-on:click="command('italic')" x-bind:aria-pressed="active.italic" title="Italic (Ctrl/Cmd+I)"><em>I</em><span class="sr-only">Italic</span></button>
            <button type="button" x-on:click="command('underline')" x-bind:aria-pressed="active.underline" title="Underline"><u>U</u><span class="sr-only">Underline</span></button>
            <span class="mx-1 h-5 border-l border-slate-300" aria-hidden="true"></span>
            <button type="button" x-on:click="command('heading')" x-bind:aria-pressed="active.heading" title="Section heading">H2<span class="sr-only">Section heading</span></button>
            <button type="button" x-on:click="command('subheading')" x-bind:aria-pressed="active.subheading" title="Subheading">H3<span class="sr-only">Subheading</span></button>
            <button type="button" x-on:click="command('bulletList')" x-bind:aria-pressed="active.bulletList" title="Bulleted list"><flux:icon.list-bullet class="size-4" /><span class="sr-only">Bulleted list</span></button>
            <button type="button" x-on:click="command('orderedList')" x-bind:aria-pressed="active.orderedList" title="Numbered list">1.<span class="sr-only">Numbered list</span></button>
            <button type="button" x-on:click="command('blockquote')" x-bind:aria-pressed="active.blockquote" title="Quote">❝<span class="sr-only">Quote</span></button>
            <button type="button" x-on:click="command('undo')" title="Undo">↶<span class="sr-only">Undo</span></button>
            <button type="button" x-on:click="command('redo')" title="Redo">↷<span class="sr-only">Redo</span></button>
        </div>
        <div x-ref="editor"></div>
        {{-- A plain-text validation proxy keeps the existing accessible form checks. --}}
        <textarea id="{{ $field }}-validation" name="form.{{ $field }}" x-ref="validation" class="sr-only" tabindex="-1" aria-hidden="true" @required($required) maxlength="{{ $limit }}" x-on:focus="$refs.editor.querySelector('[contenteditable]')?.focus()"></textarea>
    </div>
    <p class="text-[11px] text-charcoal/55" id="{{ $field }}-hint"><span x-text="count.toLocaleString()">0</span> / {{ number_format($limit) }} characters · Use headings to separate sections.</p>
    <x-ui.field-error :field="'form.'.$field" />
</div>
