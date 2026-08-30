import { validatedForm } from './form-validation';
import { confirmationDialog } from './confirmation-dialog';

const registerForms = () => {
    window.Alpine.data('validatedForm', validatedForm);
    window.Alpine.data('confirmationDialog', confirmationDialog);
};
if (window.Alpine) registerForms();
else document.addEventListener('alpine:init', registerForms, { once: true });

const registerLivewire = () => {
    window.Livewire.hook('commit', ({ component, respond }) => {
        respond(() => setTimeout(() => {
            component.el.querySelectorAll('[data-validated-form]').forEach((form) => {
                form.dispatchEvent(new Event('validation-request-finished'));
            });
            if (component.el.matches('[data-validated-form]')) {
                component.el.dispatchEvent(new Event('validation-request-finished'));
            }
        }, 0));
    });
};
if (window.Livewire) registerLivewire();
else document.addEventListener('livewire:init', registerLivewire, { once: true });
