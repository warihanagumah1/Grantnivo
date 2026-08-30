import { Editor } from '@tiptap/core';
import StarterKit from '@tiptap/starter-kit';

export function richTextEditor(field, limit) {
    // ProseMirror must stay outside Alpine's reactive proxy.
    let editor;
    return {
        field, limit, count: 0, active: {},
        init() {
            editor = new Editor({
                element: this.$refs.editor,
                extensions: [StarterKit.configure({
                    heading: { levels: [2, 3] }, code: false, codeBlock: false,
                    horizontalRule: false, strike: false,
                    link: { openOnClick: false, protocols: ['https', 'http', 'mailto'] },
                })],
                content: this.$wire.form[field] || '',
                editorProps: { attributes: {
                    id: field, class: 'grant-rich-text rich-editor-content', role: 'textbox',
                    'aria-multiline': 'true', 'aria-labelledby': `${field}-label`,
                    'aria-describedby': `${field}-hint form-${field}-error`,
                    'aria-required': String(this.$refs.validation.required),
                } },
                onCreate: () => this.sync(),
                onUpdate: () => this.sync(),
                onSelectionUpdate: () => this.updateToolbar(),
                onTransaction: () => this.updateToolbar(),
                onBlur: () => this.$refs.validation.dispatchEvent(new FocusEvent('focusout', { bubbles: true })),
            });
            this.$watch('messages', () => {
                editor.view.dom.setAttribute('aria-invalid', String(Boolean(this.messages[`form.${field}`])));
            });
        },
        sync() {
            if (!editor) return;
            const text = editor.getText({ blockSeparator: '' }).replace(/\u00a0/g, ' ').trim();
            this.count = [...text].length;
            this.$refs.validation.value = text;
            this.$wire.form[field] = editor.isEmpty ? '' : editor.getHTML();
            this.$refs.validation.dispatchEvent(new Event('input', { bubbles: true }));
            this.updateToolbar();
        },
        updateToolbar() {
            if (!editor) return;
            this.active = Object.fromEntries(['bold', 'italic', 'underline', 'bulletList', 'orderedList', 'blockquote'].map((name) => [name, editor.isActive(name)]));
            this.active.heading = editor.isActive('heading', { level: 2 });
            this.active.subheading = editor.isActive('heading', { level: 3 });
        },
        command(name) {
            const chain = editor.chain().focus();
            if (name === 'heading' || name === 'subheading') chain.toggleHeading({ level: name === 'heading' ? 2 : 3 }).run();
            else if (name === 'undo' || name === 'redo') chain[name]().run();
            else chain[`toggle${name[0].toUpperCase()}${name.slice(1)}`]().run();
        },
        destroy() { editor?.destroy(); },
    };
}

const registerEditor = () => window.Alpine.data('richTextEditor', richTextEditor);
if (window.Alpine) registerEditor();
else document.addEventListener('alpine:init', registerEditor, { once: true });
