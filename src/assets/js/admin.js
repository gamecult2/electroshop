/* Shared admin feedback, navigation and accessible component behavior. */
window.AdminUI = (() => {
    let pendingConfirmation = false;
    const palette = () => {
        const css = getComputedStyle(document.body);
        return {
            brand: css.getPropertyValue('--admin-brand').trim(),
            success: css.getPropertyValue('--admin-success').trim(),
            warning: css.getPropertyValue('--admin-warning').trim(),
            info: css.getPropertyValue('--admin-info').trim(),
            muted: css.getPropertyValue('--admin-muted').trim(),
            border: css.getPropertyValue('--admin-border').trim(),
            surface: css.getPropertyValue('--admin-surface').trim()
        };
    };
    function notify(message, type = 'success') {
        const toast = document.getElementById('statusToast');
        if (!toast) return;
        toast.classList.remove('bg-success', 'bg-danger', 'bg-dark');
        toast.classList.add(['danger', 'error'].includes(type) ? 'bg-danger' : type === 'success' ? 'bg-success' : 'bg-dark');
        toast.setAttribute('role', ['danger', 'error'].includes(type) ? 'alert' : 'status');
        toast.setAttribute('aria-live', ['danger', 'error'].includes(type) ? 'assertive' : 'polite');
        document.getElementById('toastMessage').textContent = String(message);
        bootstrap.Toast.getOrCreateInstance(toast, {delay: type === 'success' ? 4000 : 8000}).show();
    }
    function confirmAction(message, label = 'Confirm') {
        if (pendingConfirmation) return Promise.resolve(false);
        pendingConfirmation = true;
        return new Promise(resolve => {
            const dialog = document.getElementById('adminConfirmModal');
            const accept = document.getElementById('adminConfirmAccept');
            const previous = document.activeElement;
            const modal = bootstrap.Modal.getOrCreateInstance(dialog);
            let confirmed = false;
            document.getElementById('adminConfirmMessage').textContent = message;
            const destructive = /delete|remove|permanent/i.test(message);
            accept.textContent = label === 'Confirm' && destructive ? 'Delete' : label;
            accept.className = 'btn ' + (destructive ? 'btn-danger' : 'btn-primary');
            const onAccept = () => { confirmed = true; modal.hide(); };
            accept.addEventListener('click', onAccept);
            dialog.addEventListener('shown.bs.modal', () => dialog.querySelector('[data-bs-dismiss]').focus(), {once: true});
            dialog.addEventListener('hidden.bs.modal', () => {
                accept.removeEventListener('click', onAccept);
                pendingConfirmation = false;
                previous?.focus();
                resolve(confirmed);
            }, {once: true});
            modal.show();
        });
    }
    function busy(control, active) {
        if (!control) return;
        control.disabled = active;
        control.setAttribute('aria-busy', String(active));
        control.classList.toggle('opacity-50', active);
    }
    function refresh(root = document) {
        // Give legacy and dynamically generated controls stable accessible names.
        root.querySelectorAll('label:not([for])').forEach(label => {
            if (label.querySelector('input,select,textarea')) return;
            const parent = label.parentElement;
            const controls = parent.querySelectorAll('input:not([type="hidden"]),select,textarea');
            if (controls.length !== 1) return;
            const control = controls[0];
            control.id ||= 'admin-field-' + (++refresh.counter);
            label.htmlFor = control.id;
        });
        root.querySelectorAll('input:not([type="hidden"]),select,textarea').forEach(control => {
            if (control.labels?.length || control.hasAttribute('aria-label') || control.hasAttribute('aria-labelledby')) return;
            const switchRow = control.closest('.form-check')?.parentElement;
            const rowLabel = switchRow?.classList.contains('d-flex') ? switchRow.querySelector('span.small') : null;
            const cell = control.closest('td');
            const heading = cell?.getAttribute('data-label') || cell?.closest('table')?.querySelectorAll('thead th')[cell.cellIndex]?.textContent.trim();
            const text = rowLabel?.textContent || heading || control.placeholder || control.name?.replace(/[\[\]_]+/g, ' ').trim();
            if (text) control.setAttribute('aria-label', text);
        });
        root.querySelectorAll('button[title],a[title]').forEach(control => {
            if (!control.hasAttribute('aria-label')) control.setAttribute('aria-label', control.title);
        });
        root.querySelectorAll('i[class*="fa-"]').forEach(icon => icon.setAttribute('aria-hidden', 'true'));
        root.querySelectorAll('canvas:not([aria-label])').forEach(canvas => {
            canvas.setAttribute('role', 'img');
            canvas.setAttribute('aria-label', canvas.closest('.card')?.querySelector('.card-header')?.textContent.trim() || 'Analytics chart');
        });
        root.querySelectorAll('.table-responsive').forEach(wrapper => {
            wrapper.tabIndex = 0;
            wrapper.setAttribute('role', 'region');
            wrapper.setAttribute('aria-label', 'Scrollable data table');
        });
        root.querySelectorAll('.table thead th').forEach(th => th.scope = 'col');
        root.querySelectorAll('.admin-mobile-table').forEach(table => {
            const labels = [...table.querySelectorAll('thead th')].map(th => th.textContent.trim() || 'Select');
            table.querySelectorAll('tbody tr').forEach(row => {
                if (row.cells.length !== labels.length) return;
                [...row.cells].forEach((cell, index) => cell.dataset.label = labels[index]);
            });
        });
        root.querySelectorAll('.modal:not([aria-labelledby]):not([aria-label])').forEach(modal => {
            const title = modal.querySelector('.modal-title');
            if (!title) return;
            title.id ||= 'admin-dialog-title-' + (++refresh.counter);
            modal.setAttribute('aria-labelledby', title.id);
        });
    }
    refresh.counter = 0;
    document.addEventListener('DOMContentLoaded', () => {
        refresh();
        new MutationObserver(records => {
            records.forEach(record => record.addedNodes.forEach(node => {
                if (node.nodeType === 1) refresh(node.parentElement || node);
            }));
        }).observe(document.body, {childList: true, subtree: true});
        document.addEventListener('click', async event => {
            const trigger = event.target.closest('[data-confirm]');
            if (!trigger || trigger.dataset.confirmed === 'true') return;
            event.preventDefault();
            event.stopImmediatePropagation();
            if (await confirmAction(trigger.dataset.confirm, trigger.dataset.confirmLabel || 'Confirm')) {
                trigger.dataset.confirmed = 'true';
                trigger.click();
                delete trigger.dataset.confirmed;
            }
        }, true);
        document.addEventListener('submit', async event => {
            const form = event.target;
            if (!form.dataset.confirm || form.dataset.confirmed === 'true') return;
            event.preventDefault();
            const submitter = event.submitter;
            if (await confirmAction(form.dataset.confirm)) {
                form.dataset.confirmed = 'true';
                form.requestSubmit(submitter);
                delete form.dataset.confirmed;
            }
        });
        document.addEventListener('submit', event => {
            const form = event.target;
            if (event.defaultPrevented || form.hasAttribute('onsubmit')) return;
            const button = event.submitter;
            if (!button) return;
            // Preserve the submitter's name/value in the request; block only duplicate clicks.
            button.setAttribute('aria-busy', 'true');
            button.addEventListener('click', e => e.preventDefault(), {once: true});
        });
    });
    const storage = {
        get(key) { try { return localStorage.getItem(key); } catch (_) { return null; } },
        set(key, value) { try { localStorage.setItem(key, value); } catch (_) {} }
    };
    function chartHasData(canvas, values) {
        if (values.some(value => Number(value) !== 0)) return true;
        canvas.hidden = true;
        const empty = document.createElement('div');
        empty.className = 'admin-empty-state';
        empty.setAttribute('role', 'status');
        empty.textContent = 'No sales recorded for this period. Try a different date range.';
        canvas.after(empty);
        return false;
    }
    return {notify, confirm: confirmAction, busy, refresh, palette, storage, chartHasData};
})();
window.showToast = AdminUI.notify;

(() => {
    let previousFocus;
    const sidebar = () => document.getElementById('adminSidebar');
    const mobile = () => window.innerWidth < 992;
    function sync() {
        if (!sidebar()) return;
        const open = mobile() ? document.body.classList.contains('sidebar-show') : !document.body.classList.contains('sidebar-collapsed');
        sidebar().inert = !open;
        document.getElementById('toggle-sidebar-btn')?.setAttribute('aria-expanded', String(open));
        const main = document.getElementById('mainContent');
        main.inert = mobile() && open;
        if (!mobile()) document.body.classList.remove('sidebar-show');
    }
    window.toggleSidebar = () => {
        if (mobile()) {
            const opening = !document.body.classList.contains('sidebar-show');
            if (opening) previousFocus = document.activeElement;
            document.body.classList.toggle('sidebar-show');
            sync();
            if (opening) sidebar().querySelector('button,a')?.focus();
            else previousFocus?.focus();
        } else {
            document.body.classList.toggle('sidebar-collapsed');
            try { localStorage.setItem('admin_sidebar_collapsed', document.body.classList.contains('sidebar-collapsed')); } catch (_) {}
            sync();
        }
    };
    document.addEventListener('DOMContentLoaded', () => {
        try { document.body.classList.toggle('sidebar-collapsed', localStorage.getItem('admin_sidebar_collapsed') === 'true'); } catch (_) {}
        sync();
        window.addEventListener('resize', sync);
        document.addEventListener('keydown', event => {
            if (!mobile() || !document.body.classList.contains('sidebar-show')) return;
            if (event.key === 'Escape') toggleSidebar();
            if (event.key !== 'Tab') return;
            const nodes = [...sidebar().querySelectorAll('a[href],button:not([disabled]),[tabindex="0"]')].filter(el => el.getClientRects().length);
            const first = nodes[0], last = nodes[nodes.length - 1];
            if (event.shiftKey && document.activeElement === first) { event.preventDefault(); last.focus(); }
            else if (!event.shiftKey && document.activeElement === last) { event.preventDefault(); first.focus(); }
        });
    });
})();
