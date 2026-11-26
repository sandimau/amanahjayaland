(() => {
    const editor = document.querySelector('#cms-editor');
    if (!editor) return;
    const frame = document.querySelector('#cms-frame');
    const form = document.querySelector('#cms-form');
    const feedback = document.querySelector('#cms-feedback');
    const selectionLabel = document.querySelector('#cms-selection');
    const textField = document.querySelector('#cms-text-field');
    const linkField = document.querySelector('#cms-url-field');
    const fileField = document.querySelector('#cms-file-field');
    const textInput = document.querySelector('#cms-value');
    const linkInput = document.querySelector('#cms-url');
    const fileInput = document.querySelector('#cms-file');
    const button = document.querySelector('#cms-save');
    const csrf = document.querySelector('meta[name="csrf-token"]').content;
    let selected;

    const mode = () => document.querySelector('input[name="cms-mode"]:checked').value;
    document.querySelectorAll('input[name="cms-mode"]').forEach((radio) => radio.addEventListener('change', () => {
        selected = null;
        form.hidden = true;
        selectionLabel.textContent = 'Pilih bagian pada pratinjau untuk mode ' + mode() + '.';
        frame.contentWindow.postMessage({type: 'AJL_EDITOR_MODE', mode: mode()}, location.origin);
    }));
    frame.addEventListener('load', () => frame.contentWindow.postMessage({type: 'AJL_EDITOR_MODE', mode: mode()}, location.origin));

    window.addEventListener('message', (event) => {
        if (event.origin !== location.origin || event.source !== frame.contentWindow || event.data?.type !== 'AJL_EDITOR_SELECT') return;
        selected = event.data;
        selectionLabel.textContent = (selected.label || 'Elemen dipilih') + (selected.scope === 'global' ? ' · berlaku di semua halaman' : '');
        feedback.textContent = '';
        form.hidden = false;
        textField.hidden = !['text', 'placeholder'].includes(mode());
        linkField.hidden = mode() !== 'link';
        fileField.hidden = !['image', 'background'].includes(mode());
        textInput.value = ['text', 'placeholder'].includes(mode()) ? selected.value : '';
        linkInput.value = mode() === 'link' ? selected.value : '';
        fileInput.value = '';
    });

    async function send(url, options) {
        const response = await fetch(url, {...options, headers: {...options.headers, 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json'}});
        const data = await response.json();
        if (!response.ok) throw new Error(Object.values(data.errors || {}).flat()[0] || data.message || 'Tidak dapat menyimpan konten.');
        return data;
    }

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        if (!selected) return;
        button.disabled = true;
        feedback.textContent = 'Menyimpan...';
        try {
            let value = ['text', 'placeholder'].includes(mode()) ? textInput.value : linkInput.value.trim();
            if (['image', 'background'].includes(mode())) {
                if (!fileInput.files[0]) throw new Error('Pilih gambar terlebih dahulu.');
                const body = new FormData();
                body.append('image', fileInput.files[0]);
                value = (await send(editor.dataset.upload, {method: 'POST', body})).url;
            }
            await send(editor.dataset.save, {
                method: 'POST', headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({
                    page: selected.scope === 'global' ? 'global' : editor.dataset.page,
                    selector: selected.selector, kind: mode(),
                    node_index: selected.node_index, label: selected.label, value,
                }),
            });
            location.reload();
        } catch (error) {
            feedback.textContent = error.message;
            button.disabled = false;
        }
    });
})();
