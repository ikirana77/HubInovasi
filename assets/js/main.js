/**
 * Interaksi navigasi mudah alih HubInovasi.
 */
const navToggle = document.querySelector('.nav-toggle');
const primaryNav = document.querySelector('.primary-nav');
const navOpenLabel = document.body.dataset.navOpenLabel || 'Buka menu navigasi';
const navCloseLabel = document.body.dataset.navCloseLabel || 'Tutup menu navigasi';

if (navToggle && primaryNav) {
    const closeMenu = () => {
        navToggle.setAttribute('aria-expanded', 'false');
        navToggle.querySelector('.sr-only').textContent = navOpenLabel;
        document.body.classList.remove('nav-open');
    };

    navToggle.addEventListener('click', () => {
        const isOpen = navToggle.getAttribute('aria-expanded') === 'true';
        navToggle.setAttribute('aria-expanded', String(!isOpen));
        navToggle.querySelector('.sr-only').textContent = isOpen ? navOpenLabel : navCloseLabel;
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
const areaFilter = document.getElementById('area-filter');
const programmeFilter = document.getElementById('programme-filter');

if (projectGrid && searchInput && filterButtons.length) {
    const allCategory = projectGrid.dataset.allCategory || 'Semua';
    const countTemplate = projectGrid.dataset.countTemplate || '{count} projek dipaparkan';
    let activeCategory = projectGrid.dataset.initialCategory || allCategory;

    const updateResults = () => {
        const query = searchInput.value.trim().toLowerCase();
        const activeProgramme = programmeFilter ? programmeFilter.value : '';
        let visibleCount = 0;

        Array.from(projectGrid.children).forEach((card) => {
            const category = card.getAttribute('data-category') || '';
            const searchableText = (card.getAttribute('data-search') || '').toLowerCase();
            const programmeCodes = (card.getAttribute('data-programmes') || '').split(/\s+/).filter(Boolean);
            const matchesCategory = activeCategory === allCategory || category === activeCategory;
            const matchesProgramme = !activeProgramme || programmeCodes.includes(activeProgramme);
            const matchesSearch = !query || searchableText.includes(query);
            const isVisible = matchesCategory && matchesProgramme && matchesSearch;

            card.style.display = isVisible ? '' : 'none';
            if (isVisible) visibleCount += 1;
        });

        if (resultCount) {
            resultCount.textContent = countTemplate.replace('{count}', String(visibleCount));
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
            activeCategory = button.getAttribute('data-category') || allCategory;

            filterButtons.forEach((chip) => {
                const isActive = chip === button;
                chip.classList.toggle('is-active', isActive);
                chip.setAttribute('aria-pressed', String(isActive));
            });

            if (areaFilter) areaFilter.value = activeCategory;

            updateResults();
        });
    });

    if (areaFilter) {
        areaFilter.addEventListener('change', () => {
            activeCategory = areaFilter.value || allCategory;
            filterButtons.forEach((chip) => {
                const isActive = chip.getAttribute('data-category') === activeCategory;
                chip.classList.toggle('is-active', isActive);
                chip.setAttribute('aria-pressed', String(isActive));
            });
            updateResults();
        });
    }

    if (programmeFilter) programmeFilter.addEventListener('change', updateResults);

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
            const evidenceStatus = String(formData.get('evidence_status') || '').trim();
            const evidence = String(formData.get('evidence') || '').trim();
            const evidencePrefix = submissionForm.dataset.evidencePrefix || 'Status bukti:';
            evidenceElement.textContent = `${evidencePrefix} ${evidenceStatus}${evidence ? ` — ${evidence}` : ''}`;
        }

        if (preview) {
            preview.hidden = false;
            preview.scrollIntoView({ behavior: prefersReducedMotion ? 'auto' : 'smooth', block: 'start' });
        }

        if (formMessage) {
            formMessage.hidden = false;
            formMessage.textContent = submissionForm.dataset.previewMessage || 'Pratonton dijana daripada borang semasa.';
            formMessage.className = 'form-message';
        }
    });
}

/**
 * CP10B adaptive submission flow. Section 03 enables only the allowlisted
 * field panel matching the solution area selected in Section 01.
 */
if (submissionForm && submissionForm.classList.contains('adaptive-submission-form')) {
    const panels = Array.from(submissionForm.querySelectorAll('[data-submission-step]'));
    const stepButtons = Array.from(document.querySelectorAll('[data-step-target]'));
    const previousButton = document.getElementById('submission-previous');
    const nextButton = document.getElementById('submission-next');
    const reviewButton = document.getElementById('submission-review');
    const currentStepInput = document.getElementById('current-step-input');
    const currentStepNumber = document.getElementById('current-step-number');
    const categorySelect = submissionForm.elements.namedItem('category');
    const adaptiveCategoryPanels = Array.from(submissionForm.querySelectorAll('[data-adaptive-category]'));
    const adaptiveCategoryEmpty = submissionForm.querySelector('[data-adaptive-category-empty]');
    let currentStep = Math.max(1, Math.min(8, Number(submissionForm.dataset.initialStep) || 1));

    const syncAdaptiveCategory = () => {
        const selectedCategory = categorySelect instanceof HTMLSelectElement ? categorySelect.value : '';
        if (adaptiveCategoryEmpty) adaptiveCategoryEmpty.hidden = selectedCategory !== '';
        adaptiveCategoryPanels.forEach((panel) => {
            const isActive = panel.dataset.adaptiveCategory === selectedCategory;
            panel.hidden = !isActive;
            panel.querySelectorAll('[data-adaptive-input]').forEach((field) => {
                field.disabled = !isActive;
                field.required = isActive && field.dataset.required === '1';
            });
        });
        document.querySelectorAll('[data-review-category]').forEach((panel) => {
            panel.hidden = panel.dataset.reviewCategory !== selectedCategory;
        });
    };

    const updateReview = () => {
        document.querySelectorAll('[data-review-field]').forEach((output) => {
            const field = submissionForm.elements.namedItem(output.dataset.reviewField || '');
            if (!field || field instanceof RadioNodeList) return;
            let value = String(field.value || '').trim();
            if (field instanceof HTMLSelectElement && field.selectedIndex >= 0) {
                value = field.options[field.selectedIndex].text.trim();
            }
            output.textContent = value || '—';
        });
        document.querySelectorAll('[data-review-category-key]').forEach((output) => {
            const fieldName = `category_details[${output.dataset.reviewCategoryKey || ''}]`;
            const field = submissionForm.elements.namedItem(fieldName);
            output.textContent = field && !(field instanceof RadioNodeList) ? String(field.value || '').trim() || '—' : '—';
        });
        syncAdaptiveCategory();
    };

    const showStep = (step, focusPanel = false) => {
        currentStep = Math.max(1, Math.min(8, Number(step) || 1));
        panels.forEach((panel) => {
            panel.hidden = Number(panel.dataset.submissionStep) !== currentStep;
        });
        stepButtons.forEach((button) => {
            if (Number(button.dataset.stepTarget) === currentStep) button.setAttribute('aria-current', 'step');
            else button.removeAttribute('aria-current');
        });
        if (currentStepInput) currentStepInput.value = String(currentStep);
        if (currentStepNumber) currentStepNumber.textContent = String(currentStep);
        if (previousButton) previousButton.disabled = currentStep === 1;
        if (nextButton) nextButton.hidden = currentStep === 8;
        if (reviewButton) reviewButton.hidden = currentStep === 8;
        if (currentStep === 8) updateReview();
        if (focusPanel) {
            const panel = panels.find((item) => Number(item.dataset.submissionStep) === currentStep);
            panel?.scrollIntoView({ behavior: prefersReducedMotion ? 'auto' : 'smooth', block: 'start' });
        }
    };

    const validatePanel = (step) => {
        const panel = panels.find((item) => Number(item.dataset.submissionStep) === step);
        if (!panel) return true;
        const invalidField = Array.from(panel.querySelectorAll('input, select, textarea')).find((field) => !field.disabled && !field.checkValidity());
        if (!invalidField) return true;
        showStep(step, true);
        invalidField.reportValidity();
        if (formMessage) {
            formMessage.hidden = false;
            formMessage.className = 'form-message form-message--error';
            formMessage.textContent = submissionForm.dataset.requiredMessage || 'Complete the required fields before continuing.';
        }
        return false;
    };

    previousButton?.addEventListener('click', () => showStep(currentStep - 1, true));
    nextButton?.addEventListener('click', () => {
        if (validatePanel(currentStep)) showStep(currentStep + 1, true);
    });
    reviewButton?.addEventListener('click', () => {
        if (validatePanel(1) && validatePanel(2) && validatePanel(3)) showStep(8, true);
    });
    stepButtons.forEach((button) => button.addEventListener('click', () => showStep(Number(button.dataset.stepTarget), true)));
    submissionForm.querySelectorAll('[data-go-to-step]').forEach((button) => button.addEventListener('click', () => showStep(Number(button.dataset.goToStep), true)));
    if (categorySelect instanceof HTMLSelectElement) categorySelect.addEventListener('change', syncAdaptiveCategory);

    submissionForm.addEventListener('submit', (event) => {
        const submitter = event.submitter;
        if (!(submitter instanceof HTMLButtonElement) || submitter.value !== 'submit_review') return;
        for (const step of [1, 2, 3, 8]) {
            if (!validatePanel(step)) {
                event.preventDefault();
                return;
            }
        }
    });

    syncAdaptiveCategory();
    showStep(currentStep);
}
