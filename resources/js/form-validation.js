// Browser checks reduce invalid requests; server validation remains authoritative.
export function fieldMessage(field, fields) {
    const value = field.value ?? '';
    const find = (key) => fields.find((item) => item.name === key || item.id === key || Array.from(item.attributes || []).some((attribute) => attribute.name.startsWith('wire:model') && attribute.value === key));
    if (field.required && field.type === 'checkbox' && !field.checked) return 'Please tick this checkbox to continue.';
    if (field.required && !['checkbox', 'radio', 'file'].includes(field.type) && !value.trim()) {
        return 'Please complete this field.';
    }
    if (field.dataset.requiredWith && find(field.dataset.requiredWith)?.value.trim() && !value.trim()) {
        return 'Please complete this field.';
    }
    if (value && field.dataset.listMax) {
        const items = [...new Set(value.split(',').map((item) => item.trim()).filter(Boolean))];
        if (!items.length) return 'Enter at least one country.';
        if (items.length > Number(field.dataset.listMax)) return `Use no more than ${field.dataset.listMax} countries.`;
        if (items.some((item) => [...item].length > Number(field.dataset.itemMax))) return `Each country must be no longer than ${field.dataset.itemMax} characters.`;
    }
    if (value && field.dataset.protocols && !field.dataset.protocols.split(',').some((protocol) => value.toLowerCase().startsWith(`${protocol}//`))) {
        return 'Use an HTTPS URL.';
    }
    if (value && field.minLength > 0 && [...value].length < field.minLength) {
        return `Use at least ${field.minLength} characters.`;
    }
    if (field.maxLength >= 0 && [...value].length > field.maxLength) {
        return `Use no more than ${field.maxLength} characters.`;
    }
    const passwordRules = field.dataset.passwordRules;
    if (value && passwordRules) {
        const rules = JSON.parse(passwordRules);
        if ([...value].length < rules.min) return `Use at least ${rules.min} characters.`;
        if (rules.max && [...value].length > rules.max) return `Use no more than ${rules.max} characters.`;
        if (rules.mixedCase && (!/\p{Ll}/u.test(value) || !/\p{Lu}/u.test(value))) return 'Include uppercase and lowercase letters.';
        if (rules.letters && !/\p{L}/u.test(value)) return 'Include a letter.';
        if (rules.numbers && !/\p{N}/u.test(value)) return 'Include a number.';
        if (rules.symbols && !/[\p{Z}\p{S}\p{P}]/u.test(value)) return 'Include a symbol.';
    }
    if (value && field.dataset.matches && value !== find(field.dataset.matches)?.value) {
        return 'Passwords must match.';
    }
    if (value && field.type === 'url' && !/^https?:\/\//i.test(value)) {
        return 'Enter a URL starting with http:// or https://.';
    }
    return '';
}

export function validatedForm() {
    let observer;
    const touched = new Set();
    const fieldKey = (field) => Array.from(field.attributes || []).find((attribute) => attribute.name.startsWith('wire:model'))?.value || field.name || field.id;
    return {
        valid: false,
        submitting: false,
        messages: {},
        get blocked() { return !this.valid || this.submitting; },
        init() {
            this.$nextTick(() => this.refreshValidity());
            observer = new MutationObserver(() => this.$nextTick(() => this.refreshValidity()));
            observer.observe(this.$el, {
                subtree: true, childList: true, attributes: true,
                attributeFilter: ['value', 'required', 'min', 'max', 'minlength', 'maxlength', 'pattern', 'type'],
            });
        },
        destroy() { observer?.disconnect(); },
        refreshValidity() {
            // Livewire temporarily disables fields during requests; don't validate that transient state.
            if (this.submitting) return;
            const fields = Array.from(this.$el.elements).filter((field) => field.matches('input, select, textarea') && !field.disabled && field.type !== 'hidden');
            for (const field of fields) field.setCustomValidity(fieldMessage(field, fields));
            this.valid = fields.every((field) => field.validity.valid);
            const feedback = Array.from(this.$el.querySelectorAll('[data-error-for]'));
            for (const field of fields) {
                const key = fieldKey(field);
                const error = feedback.find((item) => item.dataset.errorFor === key);
                if (error) {
                    const describedBy = new Set((field.getAttribute('aria-describedby') || '').split(/\s+/).filter(Boolean));
                    describedBy.add(error.id);
                    field.setAttribute('aria-describedby', [...describedBy].join(' '));
                }
                if (touched.has(key)) {
                    this.messages[key] = field.validationMessage;
                    field.setAttribute('aria-invalid', String(!field.validity.valid));
                } else if (error?.dataset.serverMessage) {
                    field.setAttribute('aria-invalid', 'true');
                }
            }
        },
        handleBlur(event) {
            if (!event.target.matches('input, select, textarea')) return;
            touched.add(fieldKey(event.target));
            this.refreshValidity();
        },
        handleSubmit(event) {
            this.refreshValidity();
            if (this.blocked) {
                event.preventDefault();
                event.stopImmediatePropagation();
                this.$el.reportValidity();
                return;
            }
            this.submitting = true;
        },
        finishRequest() {
            this.submitting = false;
            // Let fresh server errors appear at their fields after Livewire morphs the form.
            touched.clear();
            this.messages = {};
            this.$nextTick(() => this.refreshValidity());
        },

    };
}
