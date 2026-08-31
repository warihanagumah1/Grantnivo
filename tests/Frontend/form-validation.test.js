import test from 'node:test';
import assert from 'node:assert/strict';
import { fieldMessage, validatedForm } from '../../resources/js/form-validation.js';

const field = (overrides = {}) => ({
    value: '', type: 'text', required: false, minLength: -1, maxLength: -1,
    dataset: {}, getAttribute: () => null, ...overrides,
});
const message = (overrides, fields = []) => fieldMessage(field(overrides), fields);

test('required text rejects whitespace but optional text may be empty', () => {
    assert.ok(message({ required: true, value: '  \t ' }));
    assert.equal(message({ value: '' }), '');
    assert.equal(message({ required: true, value: 'Amara' }), '');
});

test('length checks also apply to prefilled values and count Unicode characters', () => {
    assert.ok(message({ value: 'ab', minLength: 3 }));
    assert.ok(message({ value: 'abcd', maxLength: 3 }));
    assert.equal(message({ value: '🌍', maxLength: 1 }), '');
});

test('password confirmation is checked against the current password value', () => {
    const password = field({ name: 'password', value: 'first-password' });
    const confirmation = { value: 'first-password', dataset: { matches: 'password' } };
    assert.equal(message(confirmation, [password]), '');
    password.value = 'changed-password';
    assert.ok(message(confirmation, [password]));
});

test('production password requirements reject weak values and accept Unicode letters', () => {
    const dataset = { passwordRules: JSON.stringify({ min: 12, mixedCase: true, letters: true, numbers: true, symbols: true }) };
    for (const value of ['Short1!', 'alllowercase1!', 'ALLUPPERCASE1!', 'NoNumbersHere!', 'NoSymbolsHere1']) {
        assert.ok(message({ value, dataset }), value);
    }
    assert.equal(message({ value: 'ÉléphantLong9!', dataset }), '');
});



test('website fields only allow supported HTTP protocols', () => {
    assert.ok(message({ type: 'url', value: 'ftp://example.org' }));
    assert.equal(message({ type: 'url', value: 'https://example.org' }), '');
});



test('invalid submissions and duplicate submissions are stopped before Livewire', () => {
    const controller = validatedForm();
    controller.refreshValidity = () => {};
    controller.$el = { reportValidity: () => {} };
    let prevented = 0;
    let stopped = 0;
    const event = { preventDefault: () => prevented++, stopImmediatePropagation: () => stopped++ };
    controller.handleSubmit(event);
    assert.equal(prevented, 1);
    assert.equal(stopped, 1);
    controller.valid = true;
    controller.handleSubmit(event);
    assert.equal(controller.submitting, true);
    controller.handleSubmit(event);
    assert.equal(prevented, 2);
    controller.$nextTick = (callback) => callback();
    controller.finishRequest();
    assert.equal(controller.blocked, false);
});





test('dependent image description becomes optional again when the URL is cleared', () => {
    const image = field({ value: 'https://example.org/image.jpg', attributes: [{ name: 'wire:model', value: 'form.image_url' }] });
    const alt = { dataset: { requiredWith: 'form.image_url' } };
    assert.ok(message(alt, [image]));
    assert.equal(message({ ...alt, value: 'Community garden' }, [image]), '');
    image.value = '';
    assert.equal(message(alt, [image]), '');
});

test('admin image URLs require HTTPS and country lists enforce server limits', () => {
    assert.ok(message({ value: 'http://example.org/image.jpg', dataset: { protocols: 'https:' } }));
    assert.equal(message({ value: 'https://example.org/image.jpg', dataset: { protocols: 'https:' } }), '');
    const dataset = { listMax: '100', itemMax: '120' };
    assert.ok(message({ value: ', ,', dataset }));
    assert.ok(message({ value: 'a'.repeat(121), dataset }));
    assert.ok(message({ value: Array.from({length:101}, (_,i)=>`Country ${i}`).join(','), dataset }));
    assert.equal(message({ value: 'Ghana, Kenya, Ghana', dataset }), '');
});

test('each touched field keeps its own error and accessible description until corrected', () => {
    const makeField = (name) => {
        const attributes = new Map();
        return field({
            name, required: true, validity: { valid: false }, validationMessage: '',
            matches: () => true,
            setCustomValidity(message) { this.validationMessage = message; this.validity.valid = !message; },
            getAttribute: (key) => attributes.get(key),
            setAttribute: (key, value) => attributes.set(key, value),
        });
    };
    const name = makeField('name');
    const organisation = makeField('organisation');
    const errors = ['name', 'organisation'].map((key) => ({ id: `${key}-error`, dataset: { errorFor: key } }));
    const controller = validatedForm();
    controller.$el = { elements: [name, organisation], querySelectorAll: () => errors };
    controller.$nextTick = (callback) => callback();
    controller.handleBlur({ target: name });
    controller.handleBlur({ target: organisation });
    assert.ok(controller.messages.name);
    assert.ok(controller.messages.organisation);
    assert.equal(name.getAttribute('aria-describedby'), 'name-error');
    assert.equal(organisation.getAttribute('aria-describedby'), 'organisation-error');
    name.value = 'Amara';
    controller.refreshValidity();
    assert.equal(controller.messages.name, '');
    assert.ok(controller.messages.organisation);
    assert.equal(controller.blocked, true);
    organisation.value = 'Community Trust';
    controller.refreshValidity();
    assert.equal(controller.blocked, false);
    errors[1].dataset.serverMessage = 'Please check this organisation.';
    controller.finishRequest();
    assert.deepEqual(controller.messages, {});
    assert.equal(organisation.getAttribute('aria-invalid'), 'true');
});

test('required policy checkbox blocks submission until checked and blocks again when unchecked', () => {
    const checkbox = field({ type: 'checkbox', required: true, checked: false, value: 'on', name: 'acceptPolicies',
        validity: { valid: false }, matches: () => true,
        setCustomValidity(message) { this.validationMessage = message; this.validity.valid = !message; },
    });
    const controller = validatedForm();
    controller.$el = { elements: [checkbox], querySelectorAll: () => [], reportValidity: () => {} };
    controller.refreshValidity();
    assert.equal(controller.blocked, true);
    let prevented = false;
    controller.handleSubmit({ preventDefault: () => prevented = true, stopImmediatePropagation: () => {} });
    assert.equal(prevented, true);
    checkbox.checked = true;
    controller.refreshValidity();
    assert.equal(controller.blocked, false);
    checkbox.checked = false;
    controller.refreshValidity();
    assert.equal(controller.blocked, true);
});
