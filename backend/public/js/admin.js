/*
 * CAGAD blog admin: small progressive enhancements. Every page still works
 * without this file; it only adds polish on top of plain forms and links.
 */
(() => {
    'use strict';

    const state = { isLeaving: false };

    // Flash messages arrive as toasts that hide themselves.
    document.querySelectorAll('.toast[data-autoshow]').forEach((element) => {
        bootstrap.Toast.getOrCreateInstance(element, { delay: 6000 }).show();
    });

    // Forms with data-confirm ask in a modal before submitting (used for deletes).
    const confirmModal = document.getElementById('confirm-modal');

    if (confirmModal) {
        const modal = bootstrap.Modal.getOrCreateInstance(confirmModal);
        const title = confirmModal.querySelector('[data-confirm-title]');
        const message = confirmModal.querySelector('[data-confirm-message]');
        const accept = confirmModal.querySelector('[data-confirm-accept]');
        let pendingForm = null;

        document.addEventListener('submit', (event) => {
            const form = event.target;

            if (!(form instanceof HTMLFormElement) || !form.dataset.confirm) {
                return;
            }

            event.preventDefault();
            pendingForm = form;
            title.textContent = form.dataset.confirmTitle || 'Are you sure?';
            message.textContent = form.dataset.confirm;
            accept.textContent = form.dataset.confirmButton || 'Delete';
            modal.show();
        }, true);

        accept.addEventListener('click', () => {
            if (!pendingForm) {
                return;
            }

            accept.disabled = true;
            state.isLeaving = true;
            pendingForm.submit();
        });

        confirmModal.addEventListener('hidden.bs.modal', () => {
            pendingForm = null;
            accept.disabled = false;
        });
    }

    // Warn before leaving a form with unsaved changes. Editors that don't fire
    // native events (TinyMCE) dispatch an "input" event on the form instead.
    let hasUnsavedChanges = false;

    document.querySelectorAll('form[data-unsaved-warning]').forEach((form) => {
        const markDirty = () => { hasUnsavedChanges = true; };

        form.addEventListener('input', markDirty);
        form.addEventListener('change', markDirty);
        form.addEventListener('submit', () => { state.isLeaving = true; });
    });

    window.addEventListener('beforeunload', (event) => {
        if (hasUnsavedChanges && !state.isLeaving) {
            event.preventDefault();
            event.returnValue = '';
        }
    });

    // Show / hide password fields.
    document.querySelectorAll('[data-password-toggle]').forEach((button) => {
        const input = document.getElementById(button.dataset.passwordToggle);
        const icon = button.querySelector('i');

        button.addEventListener('click', () => {
            const reveal = input.type === 'password';

            input.type = reveal ? 'text' : 'password';
            button.setAttribute('aria-pressed', String(reveal));
            button.setAttribute('aria-label', reveal ? 'Hide password' : 'Show password');
            icon.className = reveal ? 'fa-regular fa-eye-slash' : 'fa-regular fa-eye';
        });
    });

    // "120 / 500" counters for fields with a maxlength.
    document.querySelectorAll('[data-char-count]').forEach((field) => {
        const output = document.getElementById(field.dataset.charCount);
        const update = () => {
            output.textContent = `${field.value.length} / ${field.maxLength}`;
            output.classList.toggle('text-danger', field.maxLength - field.value.length <= 20);
        };

        field.addEventListener('input', update);
        update();
    });

    // Filters that apply as soon as an option is picked.
    document.querySelectorAll('[data-autosubmit]').forEach((field) => {
        field.addEventListener('change', () => field.form.requestSubmit());
    });
})();
