/* @ds-bundle: {"format":4,"namespace":"AJL","components":[{"name":"Button"},{"name":"IconButton"},{"name":"Icon"},{"name":"SectionHeading"},{"name":"FeatureBadge"},{"name":"ProjectCard"},{"name":"StatCard"},{"name":"FeatureCard"},{"name":"CtaBanner"},{"name":"PaginationDots"},{"name":"NavBar"},{"name":"Footer"}]} */
(function () {
  var React = window.React;
  var h = React.createElement;

  function cx() {
    var out = [];
    for (var i = 0; i < arguments.length; i++) if (arguments[i]) out.push(arguments[i]);
    return out.join(' ');
  }

  /* Line icons: 24px grid, 2px stroke, round caps and joins (Lucide-style, ISC). */
  var PATHS = {
    'arrow-right': ['M5 12h14', 'm12 5 7 7-7 7'],
    'arrow-left': ['m12 19-7-7 7-7', 'M19 12H5'],
    'chevron-right': ['m9 18 6-6-6-6'],
    'house': ['M15 21v-8a1 1 0 0 0-1-1h-4a1 1 0 0 0-1 1v8', 'M3 10a2 2 0 0 1 .709-1.528l7-5.999a2 2 0 0 1 2.582 0l7 5.999A2 2 0 0 1 21 10v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z'],
    'layers': ['M12.83 2.18a2 2 0 0 0-1.66 0L2.6 6.08a1 1 0 0 0 0 1.83l8.58 3.91a2 2 0 0 0 1.66 0l8.58-3.9a1 1 0 0 0 0-1.83Z', 'm22 17.65-9.17 4.16a2 2 0 0 1-1.66 0L2 17.65', 'm22 12.65-9.17 4.16a2 2 0 0 1-1.66 0L2 12.65'],
    'trending-up': ['M22 7 13.5 15.5 8.5 10.5 2 17', 'M16 7h6v6'],
    'shield-check': ['M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z', 'm9 12 2 2 4-4'],
    'calendar': ['M8 2v4', 'M16 2v4', 'M5 4h14a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2z', 'M3 10h18'],
    'users': ['M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2', 'M9 3a4 4 0 1 0 0 8 4 4 0 0 0 0-8z', 'M22 21v-2a4 4 0 0 0-3-3.87', 'M16 3.13a4 4 0 0 1 0 7.75'],
    'map-pin': ['M20 10c0 4.993-5.539 10.193-7.399 11.799a1 1 0 0 1-1.202 0C9.539 20.193 4 14.993 4 10a8 8 0 0 1 16 0', 'M12 7a3 3 0 1 0 0 6 3 3 0 0 0 0-6z'],
    'headphones': ['M3 14h3a2 2 0 0 1 2 2v3a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-7a9 9 0 0 1 18 0v7a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3'],
    'phone': ['M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z'],
    'mail': ['M4 4h16a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2z', 'm22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7'],
    'clock': ['M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20z', 'M12 6v6l4 2'],
    'calculator': ['M6 2h12a2 2 0 0 1 2 2v16a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2z', 'M8 6h8', 'M16 14v4', 'M16 10h.01', 'M12 10h.01', 'M8 10h.01', 'M12 14h.01', 'M8 14h.01', 'M12 18h.01', 'M8 18h.01']
  };

  function Icon(props) {
    var d = PATHS[props.name] || [];
    var size = props.size || 24;
    return h('svg', {
      className: cx('ajl-icon', props.className),
      width: size, height: size, viewBox: '0 0 24 24', fill: 'none',
      stroke: 'currentColor', strokeWidth: props.strokeWidth || 2,
      strokeLinecap: 'round', strokeLinejoin: 'round',
      'aria-hidden': props.label ? undefined : 'true',
      role: props.label ? 'img' : undefined,
      'aria-label': props.label
    }, d.map(function (p, i) { return h('path', { key: i, d: p }); }));
  }

  function Button(props) {
    var variant = props.variant || 'primary';
    var size = props.size || 'md';
    var Tag = props.href ? 'a' : 'button';
    var trailing = props.trailingIcon === undefined ? 'arrow-right' : props.trailingIcon;
    return h(Tag, {
      className: cx('ajl-btn', 'ajl-btn--' + variant, 'ajl-btn--' + size, props.className),
      href: props.href, type: props.href ? undefined : (props.type || 'button'),
      onClick: props.onClick, disabled: props.disabled
    },
      props.leadingIcon ? h(Icon, { name: props.leadingIcon, size: size === 'sm' ? 16 : 20 }) : null,
      h('span', { className: 'ajl-btn__label' }, props.children),
      trailing ? h(Icon, { name: trailing, size: size === 'sm' ? 14 : 18 }) : null
    );
  }

  function IconButton(props) {
    var variant = props.variant || 'gold';
    return h(props.href ? 'a' : 'button', {
      className: cx('ajl-iconbtn', 'ajl-iconbtn--' + variant, 'ajl-iconbtn--' + (props.size || 'md'), props.className),
      href: props.href, type: props.href ? undefined : 'button',
      'aria-label': props.label, onClick: props.onClick, disabled: props.disabled
    }, h(Icon, { name: props.icon || 'arrow-right', size: props.size === 'sm' ? 16 : 20 }));
  }

  function SectionHeading(props) {
    var align = props.align || 'center';
    var Tag = 'h' + (props.level || 2);
    return h('div', { className: cx('ajl-heading', 'ajl-heading--' + align, props.className) },
      props.eyebrow ? h('p', { className: 'ajl-heading__eyebrow eyebrow' }, props.eyebrow) : null,
      h(Tag, { className: cx('ajl-heading__title', props.size === 'display' ? 'display' : props.size === 'sm' ? 'heading-2' : 'heading-1') },
        props.title,
        props.accent ? h(React.Fragment, null, props.stacked ? h('br') : ' ', h('span', { className: 'ajl-accent' }, props.accent)) : null
      ),
      props.subtitle ? h('p', { className: cx('ajl-heading__sub', props.size === 'display' ? 'body-lg' : 'body') }, props.subtitle) : null,
      props.children
    );
  }

  function FeatureBadge(props) {
    return h('div', { className: cx('ajl-badge', props.className) },
      h('span', { className: 'ajl-badge__tile' }, h(Icon, { name: props.icon || 'house', size: 26 })),
      h('span', { className: 'ajl-badge__label body-sm' }, props.label)
    );
  }

  function ProjectCard(props) {
    // With an href, the photo and the title open the project too (not only the arrow button).
    // The photo link is skipped by keyboard / screen readers so each card is one stop: the title link.
    var media = props.image ? h('img', { src: props.image, alt: props.imageAlt || '' }) : h('span', { className: 'ajl-project__placeholder' }, h(Icon, { name: 'house', size: 32 }));
    return h('article', { className: cx('ajl-project', props.href ? 'ajl-project--link' : null, props.className) },
      props.href
        ? h('a', { className: 'ajl-project__media', href: props.href, tabIndex: -1, 'aria-hidden': 'true' }, media)
        : h('div', { className: 'ajl-project__media' }, media),
      h('div', { className: 'ajl-project__body' },
        h('h3', { className: 'ajl-project__title title' }, props.href ? h('a', { className: 'ajl-project__link', href: props.href }, props.title) : props.title),
        props.description ? h('p', { className: 'ajl-project__desc body-sm' }, props.description) : null,
        h('div', { className: 'ajl-project__foot' },
          props.location ? h('span', { className: 'ajl-project__loc caption' }, h(Icon, { name: 'map-pin', size: 16 }), props.location) : h('span'),
          h(IconButton, { variant: 'ghost', size: 'sm', href: props.href, label: 'Lihat ' + (props.title || 'proyek'), onClick: props.onClick })
        )
      )
    );
  }

  function StatCard(props) {
    return h('div', { className: cx('ajl-stat', props.className) },
      h('span', { className: 'ajl-stat__icon' }, h(Icon, { name: props.icon || 'calendar', size: 28 })),
      h('div', null,
        h('div', { className: 'ajl-stat__value stat' }, props.value),
        h('div', { className: 'ajl-stat__label caption' }, props.label)
      )
    );
  }

  function FeatureCard(props) {
    return h('div', { className: cx('ajl-feature', props.className) },
      h('span', { className: 'ajl-feature__icon' }, h(Icon, { name: props.icon || 'shield-check', size: 32 })),
      h('div', { className: 'ajl-feature__text' },
        h('h3', { className: 'ajl-feature__title title' }, props.title),
        h('p', { className: 'ajl-feature__desc body-sm' }, props.description)
      )
    );
  }

  function CtaBanner(props) {
    return h('section', {
      className: cx('ajl-cta', props.className),
      style: props.image ? { '--ajl-cta-image': 'url("' + props.image + '")' } : undefined
    },
      props.image ? h('span', { className: 'ajl-cta__image', 'aria-hidden': 'true' }) : null,
      h('div', { className: 'ajl-cta__text' },
        h('p', { className: 'ajl-cta__line1 heading-2' }, props.title),
        h('p', { className: 'ajl-cta__line2 title-lg' }, props.accent)
      ),
      h(Button, { href: props.href, onClick: props.onClick }, props.actionLabel || 'Konsultasi Sekarang')
    );
  }

  function PaginationDots(props) {
    var count = props.count || 5, active = props.active || 0, out = [];
    for (var i = 0; i < count; i++) {
      out.push(h('button', {
        key: i, type: 'button', className: cx('ajl-dots__dot', i === active && 'is-active'),
        'aria-label': 'Slide ' + (i + 1), 'aria-current': i === active ? 'true' : undefined,
        onClick: props.onChange ? (function (n) { return function () { props.onChange(n); }; })(i) : undefined
      }));
    }
    return h('div', { className: cx('ajl-dots', props.className), role: 'group', 'aria-label': props.label || 'Pilih slide' }, out);
  }

  function NavBar(props) {
    var links = props.links || [];
    return h('header', { className: cx('ajl-nav', props.className) },
      h('a', { className: 'ajl-nav__brand', href: props.homeHref || '#' },
        props.logo ? h('img', { src: props.logo, alt: props.brandName || 'Amanah Jaya Land' }) : h('span', { className: 'ajl-nav__wordmark title' }, props.brandName || 'Amanah Jaya Land')
      ),
      h('nav', { className: 'ajl-nav__links', 'aria-label': 'Utama' },
        links.map(function (l, i) {
          return h('a', { key: i, href: l.href || '#', className: cx('nav', l.active && 'is-active'), 'aria-current': l.active ? 'page' : undefined }, l.label);
        })
      ),
      props.action ? h(Button, { size: 'sm', leadingIcon: 'calculator', trailingIcon: null, href: props.action.href }, props.action.label) : null
    );
  }

  function Footer(props) {
    var nav = props.nav || [], contact = props.contact || [], legal = props.legal || [];
    /* Optional extra link columns (e.g. Layanan), placed between the about block and Navigasi */
    var columns = props.columns || [];
    function linkCol(title, items, key) {
      return h('div', { className: 'ajl-footer__col', key: key },
        h('h4', { className: 'title' }, title),
        h('ul', null, (items || []).map(function (n, i) {
          return h('li', { key: i }, h('a', { href: n.href || '#', className: 'nav' }, h(Icon, { name: 'chevron-right', size: 16 }), n.label));
        }))
      );
    }
    return h('footer', { className: cx('ajl-footer', props.className) },
      h('div', { className: 'ajl-footer__cols' },
        h('div', { className: 'ajl-footer__about' },
          props.logo ? h('img', { className: 'ajl-footer__logo', src: props.logo, alt: props.brandName || 'Amanah Jaya Land' }) : h('p', { className: 'title' }, props.brandName || 'Amanah Jaya Land'),
          props.about ? h('p', { className: 'body-sm' }, props.about) : null,
          props.social ? h('div', { className: 'ajl-footer__social' }, props.social) : null
        ),
        columns.map(function (c, i) { return linkCol(c.title, c.items, 'x' + i); }),
        linkCol(props.navTitle || 'Navigasi', nav, 'nav'),
        h('div', { className: 'ajl-footer__col' },
          h('h4', { className: 'title' }, props.contactTitle || 'Kontak Kami'),
          h('ul', null, contact.map(function (c, i) {
            return h('li', { key: i, className: 'body-sm' }, h(Icon, { name: c.icon, size: 18 }), h('span', null, c.text));
          }))
        )
      ),
      h('div', { className: 'ajl-footer__bar caption' },
        h('span', null, props.copyright),
        h('span', { className: 'ajl-footer__legal' }, legal.map(function (l, i) { return h('a', { key: i, href: l.href || '#' }, l.label); }))
      )
    );
  }

  window.AJL = {
    Button: Button, IconButton: IconButton, Icon: Icon, SectionHeading: SectionHeading,
    FeatureBadge: FeatureBadge, ProjectCard: ProjectCard, StatCard: StatCard, FeatureCard: FeatureCard,
    CtaBanner: CtaBanner, PaginationDots: PaginationDots, NavBar: NavBar, Footer: Footer,
    icons: Object.keys(PATHS)
  };
})();
