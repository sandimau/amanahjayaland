/* Click-to-edit helper, loaded only inside the admin's preview iframe. */
(() => {
    let mode = 'text';
    let previous;
    const hint = document.createElement('div');
    hint.textContent = 'Mode edit aktif · pilih elemen pada halaman';
    hint.style.cssText = 'position:fixed;left:14px;bottom:14px;z-index:2147483647;background:#f6c40e;color:#01173c;padding:8px 14px;border-radius:8px;font:600 13px Arial;pointer-events:none';
    document.body.append(hint);

    window.addEventListener('message', (event) => {
        if (event.origin === location.origin && event.data?.type === 'AJL_EDITOR_MODE') mode = event.data.mode;
    });

    function selector(element, shared) {
        const parts = [];
        for (let node = element; node && node !== document.body && node !== shared; node = node.parentElement) {
            const siblings = [...node.parentElement.children].filter((child) => child.tagName === node.tagName);
            parts.unshift(node.tagName.toLowerCase() + ':nth-of-type(' + (siblings.indexOf(node) + 1) + ')');
        }
        if (shared) return '.' + shared.classList[0] + (parts.length ? ' > ' + parts.join(' > ') : '');
        return 'body > ' + parts.join(' > ');
    }

    function textTarget(start) {
        let element = start;
        while (element && element !== document.body) {
            const index = [...element.childNodes].findIndex((node) => node.nodeType === Node.TEXT_NODE && node.nodeValue.trim());
            if (index >= 0) return {element, index, value: element.childNodes[index].nodeValue};
            const nested = [...element.querySelectorAll('span, strong, em, b, i')].find((node) =>
                [...node.childNodes].some((child) => child.nodeType === Node.TEXT_NODE && child.nodeValue.trim()));
            if (nested) return textTarget(nested);
            element = element.parentElement;
        }
        return null;
    }

    window.addEventListener('click', (event) => {
        event.preventDefault();
        event.stopImmediatePropagation();
        if (!(event.target instanceof Element)) return;
        if (event.target.closest('.ajl-project, .gl-tile, .pd-info, .pd-gallery, .ajl-data-status, .ajl-data-pages')) return;

        let element, nodeIndex = 0, value;
        if (mode === 'text') {
            const target = textTarget(event.target);
            if (!target) return;
            ({element, value} = target);
            nodeIndex = target.index;
        } else if (mode === 'placeholder') {
            element = event.target.closest('input, textarea');
            value = element?.getAttribute('placeholder') || '';
        } else if (mode === 'image') {
            element = event.target.closest('img');
            value = element?.getAttribute('src');
        } else if (mode === 'link') {
            element = event.target.closest('a');
            value = element?.getAttribute('href');
        } else if (mode === 'background') {
            element = event.target;
            while (element && element !== document.body && getComputedStyle(element).backgroundImage === 'none') {
                element = element.parentElement;
            }
            value = element && getComputedStyle(element).backgroundImage;
            if (value && value !== 'none') value = value.replace(/^url\(["']?/, '').replace(/["']?\)$/, '');
        }
        if (!element || value == null || value === 'none') return;
        if (previous) previous.style.outline = '';
        previous = element;
        element.style.outline = '3px solid #f6c40e';
        const shared = element.closest('.ajl-nav, .ajl-footer');
        window.parent.postMessage({
            type: 'AJL_EDITOR_SELECT', selector: selector(element, shared), scope: shared ? 'global' : 'page', kind: mode,
            node_index: nodeIndex, label: element.textContent.trim().slice(0, 100) || element.tagName,
            value: ['text', 'placeholder'].includes(mode) ? value : value.trim(),
        }, location.origin);
    }, true);

    window.addEventListener('submit', (event) => { event.preventDefault(); event.stopImmediatePropagation(); }, true);
})();
