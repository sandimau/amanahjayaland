/* Navigation and data bridge for the original exported Design Canvas pages. */
(() => {
    // The source export references an absent /_blob logo. Keep the brand visible
    // until an administrator replaces it with the original image in the CMS.
    const restoreBrand = () => {
        document.querySelectorAll('.ajl-nav__brand img, .ajl-footer__logo').forEach((image) => {
            if (image.getAttribute('src') === '/original-assets/unavailable.svg') {
                image.src = '/original-assets/brand-fallback.svg';
            }
        });
    };
    restoreBrand();
    new MutationObserver(restoreBrand).observe(document.documentElement, {
        subtree: true, childList: true, attributes: true, attributeFilter: ['src'],
    });

    const pages = {
        'Main.dc.html': '/',
        'Tentang.dc.html': '/tentang-kami',
        'Services.dc.html': '/layanan',
        'Galeri.dc.html': '/galeri',
        'Kontak.dc.html': '/kontak',
        'ProyekDetail.dc.html': '/galeri',
    };

    document.addEventListener('click', (event) => {
        const tile = event.target.closest('.gl-tile[data-project-url]');
        if (tile) {
            event.preventDefault();
            event.stopImmediatePropagation();
            window.location.assign(tile.dataset.projectUrl);
            return;
        }
        const thumb = event.target.closest('.pd-thumb[data-photo-index]');
        if (thumb) {
            event.preventDefault();
            event.stopImmediatePropagation();
            const main = document.querySelector('.pd-main');
            const photo = window.ajlProjectPhotos?.[Number(thumb.dataset.photoIndex)];
            if (main && photo) {
                main.querySelector('img').src = photo.src;
                main.querySelector('img').alt = photo.caption;
                main.querySelector('.pd-main__cap .sc-interp').textContent = photo.caption;
                main.querySelector('.pd-count').textContent = `Foto ${Number(thumb.dataset.photoIndex) + 1} dari ${window.ajlProjectPhotos.length}`;
                main.dataset.currentPhoto = photo.src;
                document.querySelectorAll('.pd-thumb').forEach((element) => {
                    element.dataset.active = String(element === thumb);
                    element.setAttribute('aria-pressed', String(element === thumb));
                });
            }
            return;
        }
        const main = event.target.closest('.pd-main[data-current-photo]');
        if (main) {
            event.preventDefault();
            event.stopImmediatePropagation();
            window.open(main.dataset.currentPhoto, '_blank', 'noopener');
            return;
        }
        const link = event.target.closest('a[href]');
        if (!link) return;
        const raw = link.getAttribute('href');
        const path = raw.split('?')[0];
        if (!Object.hasOwn(pages, path)) return;
        event.preventDefault();
        event.stopImmediatePropagation();
        window.location.assign(pages[path]);
    }, true);

    document.addEventListener('submit', async (event) => {
        const form = event.target;
        if (!form.matches('.fm-form')) return;
        event.preventDefault();
        event.stopImmediatePropagation();

        const data = new FormData(form);
        const message = form.querySelector('.fm-note');
        const button = form.querySelector('[type="submit"]');
        button.disabled = true;
        if (message) message.dataset.show = 'false';

        try {
            const response = await fetch('/api/contact', {
                method: 'POST',
                headers: {'Accept': 'application/json', 'Content-Type': 'application/json'},
                body: JSON.stringify({
                    name: data.get('nama'),
                    phone: data.get('wa'),
                    email: data.get('email'),
                    subject: data.get('layanan'),
                    message: data.get('pesan'),
                }),
            });
            if (!response.ok) {
                const result = await response.json();
                throw new Error(Object.values(result.errors || {}).flat()[0] || 'Pesan gagal dikirim. Silakan coba kembali.');
            }
            form.reset();
            if (message) message.textContent = 'Terima kasih, pesan Anda berhasil dikirim.';
        } catch (error) {
            if (message) message.textContent = error.message;
        } finally {
            if (message) message.dataset.show = 'true';
            button.disabled = false;
        }
    }, true);

    function whenRendered(selector, callback, attempts = 20) {
        const elements = document.querySelectorAll(selector);
        if (elements.length) return callback(elements);
        if (attempts > 0) setTimeout(() => whenRendered(selector, callback, attempts - 1), 150);
    }

    function showStatus(container, text) {
        if (!container) return;
        const message = document.createElement('p');
        message.className = 'ajl-data-status';
        message.setAttribute('role', 'status');
        message.textContent = text;
        container.append(message);
    }

    function fillCard(card, project) {
        card.style.display = project ? '' : 'none';
        if (!project) return;
        card.style.visibility = 'visible';
        const href = '/proyek/' + encodeURIComponent(project.slug);
        const title = card.querySelector('.ajl-project__link');
        if (title) title.textContent = project.name;
        const description = card.querySelector('.ajl-project__desc');
        if (description) description.textContent = project.summary || '';
        const location = card.querySelector('.ajl-project__loc');
        if (location) location.lastChild.textContent = project.location || '';
        card.querySelectorAll('a[href]').forEach((link) => {
            link.href = href;
            if (link.hasAttribute('aria-label')) link.setAttribute('aria-label', 'Lihat ' + project.name);
        });
        const image = card.querySelector('img');
        if (image) {
            image.src = project.cover_image ? '/storage/' + project.cover_image : '/original-assets/unavailable.svg';
            image.alt = project.cover_image ? project.name : 'Foto proyek belum tersedia';
            image.loading = 'lazy';
        }
    }

    function pageControls(container, lastPage, changePage, label = 'Halaman proyek') {
        if (!container || lastPage < 2) return;
        const controls = document.createElement('nav');
        controls.className = 'ajl-data-pages';
        controls.setAttribute('aria-label', label);
        const previous = document.createElement('button');
        const indicator = document.createElement('span');
        const next = document.createElement('button');
        previous.type = next.type = 'button';
        previous.textContent = '← Sebelumnya';
        next.textContent = 'Berikutnya →';
        controls.append(previous, indicator, next);
        container.append(controls);
        return (page) => {
            indicator.textContent = `${page} / ${lastPage}`;
            previous.disabled = page === 1;
            next.disabled = page === lastPage;
            previous.onclick = () => changePage(page - 1);
            next.onclick = () => changePage(page + 1);
        };
    }

    function loadProjects() {
        const isGallery = location.pathname === '/galeri';
        const count = isGallery ? 8 : 7;
        const url = '/api/projects?per_page=' + count;
        let updateControls;
        const load = (page) => fetch(url + '&page=' + page, {headers: {'Accept': 'application/json'}})
            .then((response) => { if (!response.ok) throw new Error('Gagal memuat proyek.'); return response.json(); })
            .then((result) => whenRendered('#proyek .ajl-project', (cards) => {
                const root = document.querySelector('#proyek');
                const container = root.querySelector(isGallery ? '.pf' : '.wrap');
                cards.forEach((card, index) => fillCard(card, result.data[index]));
                if (result.total) {
                    root.querySelector('.ajl-data-status')?.remove();
                    if (!isGallery) root.querySelectorAll('.carousel, .dots').forEach((element) => element.style.display = '');
                }
                if (!result.total) {
                    if (!root.querySelector('.ajl-data-status')) showStatus(container, 'Proyek segera hadir. Silakan kembali lagi untuk melihat karya terbaru kami.');
                    if (!isGallery) root.querySelectorAll('.carousel, .dots').forEach((element) => element.style.display = 'none');
                }
                if (!updateControls) updateControls = pageControls(container, result.last_page, load);
                updateControls?.(page);
            }))
            .catch(() => {
                whenRendered('#proyek .ajl-project', (cards) => {
                    const root = document.querySelector('#proyek');
                    const container = root.querySelector(isGallery ? '.pf' : '.wrap');
                    cards.forEach((card) => card.style.display = 'none');
                    if (!root.querySelector('.ajl-data-status')) showStatus(container, 'Proyek belum dapat dimuat. Silakan coba lagi nanti.');
                });
            });
        load(1);
    }

    if (location.pathname === '/' || location.pathname === '/galeri') loadProjects();

    if (location.pathname === '/galeri') {
        let updateControls;
        const load = (page) => fetch('/api/gallery?per_page=13&page=' + page, {headers: {'Accept': 'application/json'}})
            .then((response) => { if (!response.ok) throw new Error('Gagal memuat galeri.'); return response.json(); })
            .then((result) => whenRendered('.gl-tile', (tiles) => {
                const section = document.querySelector('#galeri');
                section.querySelector('.gl-tabs').style.display = 'none'; // Categories are not stored in the database.
                tiles.forEach((tile, index) => {
                    const photo = result.data[index];
                    tile.style.display = photo ? '' : 'none';
                    if (!photo) return;
                    tile.style.visibility = 'visible';
                    tile.dataset.projectUrl = '/proyek/' + encodeURIComponent(photo.project.slug);
                    tile.setAttribute('aria-label', 'Lihat proyek ' + photo.project.name);
                    tile.querySelector('img').src = '/storage/' + photo.image_path;
                    tile.querySelector('img').alt = photo.caption || photo.project.name;
                    tile.querySelector('.gl-tile__cat').textContent = photo.project.name;
                    tile.querySelector('.gl-tile__cap .sc-interp:last-child').textContent = photo.caption || 'Lihat proyek';
                });
                if (result.total) section.querySelector('.ajl-data-status')?.remove();
                if (!result.total) showStatus(section.querySelector('.wrap'), 'Foto proyek akan ditampilkan di sini setelah diunggah oleh admin.');
                if (!updateControls) updateControls = pageControls(section.querySelector('.wrap'), result.last_page, load, 'Halaman galeri foto');
                updateControls?.(page);
            })).catch(() => whenRendered('.gl-tile', (tiles) => {
                tiles.forEach((tile) => tile.style.display = 'none');
                const section = document.querySelector('#galeri');
                section.querySelector('.gl-tabs').style.display = 'none';
                showStatus(section.querySelector('.wrap'), 'Galeri belum dapat dimuat. Silakan coba lagi nanti.');
            }));
        load(1);
    }

    if (location.pathname.startsWith('/proyek/')) {
        fetch('/api/projects/' + encodeURIComponent(decodeURIComponent(location.pathname.split('/').pop())))
            .then((response) => response.json()).then((project) => {
                if (!project?.name) return;
                whenRendered('.pd-title', () => {
                    document.title = project.name + ' — Amanah Jaya Land';
                    document.querySelectorAll('.pd-title, .hero-title').forEach((el) => el.textContent = project.name);
                    const description = document.querySelector('.pd-desc');
                    if (description) description.textContent = project.description || project.summary || '';
                    const location = document.querySelector('.pd-loc');
                    if (location) location.textContent = project.location || '';
                    const breadcrumb = document.querySelector('.crumbs__cur');
                    if (breadcrumb) breadcrumb.textContent = project.name;
                    const hero = document.querySelector('.hero');
                    if (hero) hero.setAttribute('aria-label', 'Detail proyek ' + project.name);
                    const heroPhoto = document.querySelector('.hero-photo');
                    if (heroPhoto && project.cover_image) heroPhoto.style.backgroundImage = `url("/storage/${project.cover_image}")`;
                    // Mockup specifications do not exist in the project database.
                    document.querySelectorAll('.pd-specs, .pd-badges').forEach((el) => el.style.display = 'none');
                    // The sample price/KPR and suggested projects are not database-backed.
                    document.querySelectorAll('.pd-kpr, .nx-sec, a[href="#kpr"]').forEach((el) => el.style.display = 'none');
                    const pictures = [
                        ...(project.cover_image ? [{src: '/storage/' + project.cover_image, caption: project.name}] : []),
                        ...(project.images || []).map((image) => ({src: '/storage/' + image.image_path, caption: image.caption || project.name})),
                    ];
                    window.ajlProjectPhotos = pictures;
                    if (!pictures.length) {
                        document.querySelector('.pd-gallery').style.display = 'none';
                        document.querySelector('.pd').style.gridTemplateColumns = '1fr';
                        return;
                    }
                    const main = document.querySelector('.pd-main img');
                    if (main) { main.src = pictures[0].src; main.alt = pictures[0].caption; }
                    const mainButton = document.querySelector('.pd-main');
                    mainButton.dataset.currentPhoto = pictures[0].src;
                    mainButton.setAttribute('aria-label', 'Perbesar foto: ' + pictures[0].caption);
                    mainButton.querySelector('.pd-main__cap .sc-interp').textContent = pictures[0].caption;
                    mainButton.querySelector('.pd-count').textContent = `Foto 1 dari ${pictures.length}`;
                    const thumbnails = document.querySelector('.pd-thumbs');
                    const example = thumbnails.querySelector('.pd-thumb');
                    for (let index = thumbnails.querySelectorAll('.pd-thumb').length; index < pictures.length; index++) {
                        thumbnails.append(example.cloneNode(true));
                    }
                    document.querySelectorAll('.pd-thumbs img').forEach((image, index) => {
                        if (!pictures[index]) { image.closest('.pd-thumb').style.display = 'none'; return; }
                        image.src = pictures[index].src;
                        image.alt = pictures[index].caption;
                        image.closest('.pd-thumb').dataset.photoIndex = index;
                        image.closest('.pd-thumb').setAttribute('aria-label', 'Lihat foto: ' + pictures[index].caption);
                    });
                });
            }).catch(() => {});
    }
})();
