/* Apply admin-managed copy and media to the original exported layout. */
(() => {
    const path = location.pathname;
    const page = path === '/' ? 'home'
        : path === '/layanan' ? 'services'
        : path === '/tentang-kami' ? 'about'
        : path === '/kontak' ? 'contact'
        : path === '/galeri' ? 'gallery'
        : path.startsWith('/proyek/') || path.includes('project-standalone.html') ? 'project' : null;
    if (!page) return;

    let entries = [];
    let frame = 0;
    const update = () => {
        frame = 0;
        for (const entry of entries) {
            let element;
            try { element = document.querySelector(entry.selector); } catch { continue; }
            if (!element) continue;
            if (entry.kind === 'text') {
                const node = element.childNodes[entry.node_index];
                if (node?.nodeType === Node.TEXT_NODE && node.nodeValue !== entry.value) node.nodeValue = entry.value;
            } else if (entry.kind === 'placeholder' && element.matches('input, textarea')) {
                if (element.getAttribute('placeholder') !== entry.value) element.setAttribute('placeholder', entry.value);
            } else if (entry.kind === 'link' && element.matches('a')) {
                if (element.getAttribute('href') !== entry.value) element.setAttribute('href', entry.value);
            } else if (entry.kind === 'image' && element.matches('img')) {
                if (element.getAttribute('src') !== entry.value) element.setAttribute('src', entry.value);
            } else if (entry.kind === 'background') {
                const background = `url("${entry.value.replaceAll('"', '%22')}")`;
                if (element.dataset.ajlCmsBackground !== entry.value) {
                    element.dataset.ajlCmsBackground = entry.value;
                    element.style.backgroundImage = background;
                }
            }
        }
    };
    const schedule = () => { if (!frame) frame = requestAnimationFrame(update); };
    fetch('/api/site-content?page=' + encodeURIComponent(page))
        .then((response) => response.ok ? response.json() : [])
        .then((data) => {
            entries = data;
            if (!entries.length) return;
            schedule();
            new MutationObserver(schedule).observe(document.documentElement, {subtree: true, childList: true, characterData: true});
        }).catch(() => {});

    if (page !== 'project') {
        fetch('/api/site-settings').then((response) => response.json()).then((settings) => {
            if (page === 'contact' && settings.contact_services) {
                const updateServices = (remaining = 20) => {
                    const select = document.querySelector('select[name="layanan"]');
                    if (!select) {
                        if (remaining) setTimeout(() => updateServices(remaining - 1), 150);
                        return;
                    }
                    const services = settings.contact_services.split('\n').map((item) => item.trim()).filter(Boolean);
                    if (services.join('|') !== [...select.options].map((option) => option.textContent).join('|')) {
                        select.replaceChildren(...services.map((label) => new Option(label, label)));
                    }
                };
                if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', updateServices, {once: true});
                else updateServices();
            }
            const applyKpr = (remaining = 20) => {
                const inputs = document.querySelectorAll('.kpr-field__input');
                if (inputs.length < 3) {
                    if (remaining) setTimeout(() => applyKpr(remaining - 1), 150);
                    return;
                }
                const values = [settings.kpr_price, settings.kpr_down_payment, String(settings.kpr_interest).replace('.', ',')];
                values.forEach((value, index) => {
                    const input = document.querySelectorAll('.kpr-field__input')[index];
                    if (!input || !value) return;
                    const setter = Object.getOwnPropertyDescriptor(HTMLInputElement.prototype, 'value').set;
                    setter.call(input, String(value));
                    input.dispatchEvent(new Event('input', {bubbles: true}));
                    input.dispatchEvent(new Event('change', {bubbles: true}));
                });
                [...document.querySelectorAll('.kpr-pill')].find((button) => button.textContent.trim() === settings.kpr_term + ' th')?.click();
            };
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', () => requestAnimationFrame(() => applyKpr()), {once: true});
            } else requestAnimationFrame(() => applyKpr());
        }).catch(() => {});
    }

    if (new URLSearchParams(location.search).has('cms-editor') && window.parent !== window) {
        const script = document.createElement('script');
        script.src = '/js/site-editor.js';
        document.head.append(script);
    }
})();
