(() => {
    const name = document.querySelector('input[name="name"]');
    const preview = document.querySelector('#project-slug-preview');
    if (!name || !preview) return;

    const update = () => {
        preview.value = name.value.normalize('NFD').replace(/[\u0300-\u036f]/g, '')
            .toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '').slice(0, 240) || 'proyek';
    };
    name.addEventListener('input', update);
    update();
})();
