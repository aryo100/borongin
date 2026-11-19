const feedback = document.querySelector('[data-page-loading]');

if (feedback) {
    const status = feedback.querySelector('[data-loading-status]');
    const restoredAttributes = new Map();
    const submittingForms = new Set();
    let recoveryTimer;

    const rememberAttribute = (element, name, value) => {
        if (!element) return;
        if (!restoredAttributes.has(element)) restoredAttributes.set(element, new Map());
        const attributes = restoredAttributes.get(element);
        if (!attributes.has(name)) attributes.set(name, element.getAttribute(name));
        element.setAttribute(name, value);
    };

    const reset = () => {
        clearTimeout(recoveryTimer);
        document.documentElement.removeAttribute('data-page-busy');
        feedback.hidden = true;
        status.textContent = '';
        for (const [element, attributes] of restoredAttributes) {
            for (const [name, value] of attributes) {
                if (value === null) element.removeAttribute(name);
                else element.setAttribute(name, value);
            }
        }
        restoredAttributes.clear();
        submittingForms.clear();
    };

    const start = (message) => {
        feedback.hidden = false;
        status.textContent = message;
        document.documentElement.setAttribute('data-page-busy', 'true');
        rememberAttribute(document.querySelector('main, [role="main"], .app-main'), 'aria-busy', 'true');
        // A stopped request has no cross-browser event; allow recovery if the old page remains.
        clearTimeout(recoveryTimer);
        recoveryTimer = setTimeout(reset, 120000);
    };

    const isCurrentTarget = (target) => !target || target.toLowerCase() === '_self';
    const isPageUrl = (url) => url.origin === window.location.origin
        && /^https?:$/.test(url.protocol)
        && !/\/invoice(?:\/|$)/i.test(url.pathname);

    window.addEventListener('click', (event) => {
        if (event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;
        const link = event.target instanceof Element ? event.target.closest('a[href]') : null;
        if (!link || link.hasAttribute('download') || link.hasAttribute('wire:navigate')
            || link.closest('[data-loading-ignore]') || link.getAttribute('href').startsWith('#')
            || !isCurrentTarget(link.getAttribute('target') ?? document.querySelector('base')?.target)) return;

        const url = new URL(link.href, window.location.href);
        if (!isPageUrl(url) || (url.hash && url.pathname === window.location.pathname
            && url.search === window.location.search)) return;

        queueMicrotask(() => {
            if (!event.defaultPrevented) start('Memuat halaman…');
        });
    });

    // Capture only repeat attempts; the first submit must reach validation and confirmation handlers.
    document.addEventListener('submit', (event) => {
        if (submittingForms.has(event.target)) {
            event.preventDefault();
            event.stopImmediatePropagation();
        }
    }, true);

    window.addEventListener('submit', (event) => {
        const form = event.target;
        if (!(form instanceof HTMLFormElement) || form.closest('[data-loading-ignore]')
            || form.hasAttribute('wire:submit')) return;
        const submitter = event.submitter;
        const target = submitter?.getAttribute('formtarget') ?? form.getAttribute('target')
            ?? document.querySelector('base')?.target;
        const method = submitter?.getAttribute('formmethod') ?? form.method;
        const action = submitter?.getAttribute('formaction') ?? form.action;
        if (!isCurrentTarget(target) || method.toLowerCase() === 'dialog'
            || !isPageUrl(new URL(action, window.location.href))) return;

        queueMicrotask(() => {
            if (event.defaultPrevented) return;
            submittingForms.add(form);
            rememberAttribute(form, 'aria-busy', 'true');
            // Native disabled controls would remove the chosen admin action from the request.
            for (const control of form.elements) {
                if ((control instanceof HTMLButtonElement && control.type === 'submit')
                    || (control instanceof HTMLInputElement && ['submit', 'image'].includes(control.type))) {
                    rememberAttribute(control, 'aria-disabled', 'true');
                }
            }
            start(method.toLowerCase() === 'get' ? 'Memuat hasil…' : 'Memproses permintaan…');
        });
    });

    window.addEventListener('pageshow', reset);
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') reset();
    });
    window.addEventListener('beforeunload', (event) => {
        queueMicrotask(() => {
            if (event.defaultPrevented || event.returnValue) reset();
        });
    });
    if (window.navigation) {
        window.navigation.addEventListener('navigate', (event) => {
            event.signal.addEventListener('abort', reset, { once: true });
        });
        window.navigation.addEventListener('navigateerror', reset);
        window.navigation.addEventListener('navigatesuccess', reset);
    }
}
