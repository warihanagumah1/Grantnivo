import test from 'node:test';
import assert from 'node:assert/strict';
import { confirmationDialog } from '../../resources/js/confirmation-dialog.js';

function fixture() {
    const controller = confirmationDialog();
    controller.$refs = {
        dialog: { open: false, showModal() { this.open = true; }, close() { this.open = false; } },
        cancel: { focused: false, focus() { this.focused = true; } },
    };
    return controller;
}

test('opening focuses Cancel, and cancellation never runs the action', () => {
    const dialog = fixture();
    dialog.open();
    assert.equal(dialog.$refs.dialog.open, true);
    assert.equal(dialog.$refs.cancel.focused, true);
    dialog.cancel();
    assert.equal(dialog.$refs.dialog.open, false);
});

test('only confirmation runs the action, once, and blocks dismissal while saving', async () => {
    const dialog = fixture();
    let calls = 0;
    let finish;
    const action = () => { calls++; return new Promise(resolve => { finish = resolve; }); };
    await dialog.confirm(action);
    assert.equal(calls, 0);
    dialog.open();
    const pending = dialog.confirm(action);
    await dialog.confirm(action);
    dialog.cancel();
    assert.equal(calls, 1);
    assert.equal(dialog.busy, true);
    assert.equal(dialog.$refs.dialog.open, true);
    finish();
    await pending;
    assert.equal(dialog.busy, false);
    assert.equal(dialog.$refs.dialog.open, false);
});

test('failed actions keep the dialog open with an error and allow retry', async () => {
    const dialog = fixture();
    dialog.open();
    await dialog.confirm(() => Promise.reject(new Error('Network error')));
    assert.equal(dialog.$refs.dialog.open, true);
    assert.equal(dialog.busy, false);
    assert.ok(dialog.error);
    await dialog.confirm(() => Promise.resolve());
    assert.equal(dialog.error, '');
    assert.equal(dialog.$refs.dialog.open, false);
});
