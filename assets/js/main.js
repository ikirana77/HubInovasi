/**
 * Interaksi navigasi mudah alih HubInovasi.
 */
const navToggle = document.querySelector('.nav-toggle');
const primaryNav = document.querySelector('.primary-nav');

if (navToggle && primaryNav) {
    const closeMenu = () => {
        navToggle.setAttribute('aria-expanded', 'false');
        navToggle.querySelector('.sr-only').textContent = 'Buka menu navigasi';
        document.body.classList.remove('nav-open');
    };

    navToggle.addEventListener('click', () => {
        const isOpen = navToggle.getAttribute('aria-expanded') === 'true';
        navToggle.setAttribute('aria-expanded', String(!isOpen));
        navToggle.querySelector('.sr-only').textContent = isOpen ? 'Buka menu navigasi' : 'Tutup menu navigasi';
        document.body.classList.toggle('nav-open', !isOpen);
    });

    primaryNav.addEventListener('click', (event) => {
        if (event.target.closest('a')) closeMenu();
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            closeMenu();
            navToggle.focus();
        }
    });
}

/**
 * Penapisan projek Teroka Inovasi.
 */
const projectGrid = document.getElementById('project-grid');
const searchInput = document.getElementById('project-search');
const filterButtons = Array.from(document.querySelectorAll('.filter-chip'));
const resultCount = document.getElementById('project-count');
const emptyState = document.getElementById('empty-state');

if (projectGrid && searchInput && filterButtons.length) {
    let activeCategory = 'Semua';

    const updateResults = () => {
        const query = searchInput.value.trim().toLowerCase();
        let visibleCount = 0;

        Array.from(projectGrid.children).forEach((card) => {
            const category = card.getAttribute('data-category') || '';
            const searchableText = (card.getAttribute('data-search') || '').toLowerCase();
            const matchesCategory = activeCategory === 'Semua' || category === activeCategory;
            const matchesSearch = !query || searchableText.includes(query);
            const isVisible = matchesCategory && matchesSearch;

            card.style.display = isVisible ? '' : 'none';
            if (isVisible) visibleCount += 1;
        });

        if (resultCount) {
            resultCount.textContent = `${visibleCount} projek dipaparkan`;
        }

        if (emptyState) {
            if (visibleCount === 0) {
                emptyState.hidden = false;
                projectGrid.setAttribute('aria-hidden', 'true');
            } else {
                emptyState.hidden = true;
                projectGrid.setAttribute('aria-hidden', 'false');
            }
        }
    };

    filterButtons.forEach((button) => {
        button.addEventListener('click', () => {
            activeCategory = button.getAttribute('data-category') || 'Semua';

            filterButtons.forEach((chip) => {
                const isActive = chip === button;
                chip.classList.toggle('is-active', isActive);
                chip.setAttribute('aria-pressed', String(isActive));
            });

            updateResults();
        });
    });

    searchInput.addEventListener('input', updateResults);
    updateResults();
}

/**
 * Animasi pendedahan lembut dan pelancaran ankar untuk halaman projek.
 */
const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
const revealTargets = Array.from(document.querySelectorAll('.hero, .home-feature, .journey, .home-areas, .people-impact, .competition-callout, .project-section, .project-hero, .facts-strip, .next-project'));

if (!prefersReducedMotion && revealTargets.length) {
    revealTargets.forEach((section, index) => {
        section.classList.add('reveal-target');
        section.style.transitionDelay = `${index * 60}ms`;
    });

    const revealObserver = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                entry.target.classList.add('is-visible');
                revealObserver.unobserve(entry.target);
            }
        });
    }, { threshold: 0.12 });

    revealTargets.forEach((section) => revealObserver.observe(section));
}

document.querySelectorAll('a[href^="#"]').forEach((anchor) => {
    anchor.addEventListener('click', (event) => {
        const targetId = anchor.getAttribute('href');
        if (!targetId || targetId === '#') return;

        const targetElement = document.querySelector(targetId);
        if (!targetElement) return;

        event.preventDefault();
        targetElement.scrollIntoView({ behavior: prefersReducedMotion ? 'auto' : 'smooth', block: 'start' });
    });
});

/**
 * Borang penghantaran projek ringkas.
 */
const submissionForm = document.getElementById('submission-form');
const formMessage = document.getElementById('form-message');
const previewPitchButton = document.getElementById('preview-pitch');

if (submissionForm && previewPitchButton) {
    previewPitchButton.addEventListener('click', () => {
        const formData = new FormData(submissionForm);
        const preview = document.getElementById('pitch-preview');
        const previewFields = {
            'preview-category': 'category',
            'preview-name': 'project_name',
            'preview-tagline': 'tagline',
            'preview-problem': 'problem',
            'preview-solution': 'solution',
            'preview-process': 'how_it_works',
            'preview-features': 'features',
            'preview-impact': 'impact',
            'preview-technologies': 'technologies',
            'preview-team': 'team',
            'preview-journey': 'journey',
            'preview-cta': 'call_to_action',
        };

        Object.entries(previewFields).forEach(([elementId, fieldName]) => {
            const element = document.getElementById(elementId);
            if (element) element.textContent = String(formData.get(fieldName) || '').trim();
        });

        document.querySelectorAll('[data-preview-optional]').forEach((section) => {
            const fieldName = section.getAttribute('data-preview-optional');
            section.hidden = !String(formData.get(fieldName) || '').trim();
        });

        const evidenceElement = document.getElementById('preview-evidence');
        if (evidenceElement) {
            const evidenceStatus = String(formData.get('evidence_status') || 'Belum diuji');
            const evidence = String(formData.get('evidence') || '').trim();
            evidenceElement.textContent = `Status bukti: ${evidenceStatus}${evidence ? ` — ${evidence}` : ''}`;
        }

        if (preview) {
            preview.hidden = false;
            preview.scrollIntoView({ behavior: prefersReducedMotion ? 'auto' : 'smooth', block: 'start' });
        }

        if (formMessage) {
            formMessage.hidden = false;
            formMessage.textContent = 'Pratonton dijana daripada borang semasa. Simpan draft untuk menyimpan perubahan ke MySQL.';
            formMessage.className = 'form-message';
        }
    });
}
