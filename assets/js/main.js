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
 * CP10F adaptive submission flow. Section 03 remains category-adaptive while
 * Sections 04–07 manage their repeatable records and safe image previews.
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
    let repeatableIndex = Date.now();
    let personIndex = Date.now();
    let mentoringIndex = Date.now();
    let journeyIndex = Date.now();

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

    const mentorReferenceForRow = (row) => {
        const nameInput = row.querySelector('[name$="[full_name]"]');
        const idInput = row.querySelector('[name$="[id]"]');
        const keyMatch = nameInput?.name.match(/^mentors\[([^\]]+)\]/);
        if (!keyMatch) return '';
        return Number(idInput?.value) > 0 ? `id:${idInput.value}` : `ref:${keyMatch[1]}`;
    };

    const syncMentorOptions = () => {
        const mentors = Array.from(submissionForm.querySelectorAll('[data-person-row="mentor"]')).map((row) => ({
            reference: mentorReferenceForRow(row),
            name: row.querySelector('[name$="[full_name]"]')?.value.trim() || '',
        })).filter((mentor) => mentor.reference && mentor.name);
        submissionForm.querySelectorAll('[data-mentor-select]').forEach((select) => {
            const selected = select.value;
            const placeholder = select.options[0]?.textContent || 'Select mentor';
            select.replaceChildren();
            const emptyOption = document.createElement('option');
            emptyOption.value = '';
            emptyOption.textContent = placeholder;
            select.append(emptyOption);
            mentors.forEach((mentor) => {
                const option = document.createElement('option');
                option.value = mentor.reference;
                option.textContent = mentor.name;
                option.selected = mentor.reference === selected;
                select.append(option);
            });
        });
        const empty = submissionForm.querySelector('[data-mentoring-empty]');
        if (empty) empty.hidden = submissionForm.querySelectorAll('[data-mentoring-row]').length > 0;
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
        const repeatableReview = {
            metric: (row) => {
                const label = row.querySelector('[data-repeatable-field="label"]')?.value.trim() || '';
                const value = row.querySelector('[data-repeatable-field="value"]')?.value.trim() || row.querySelector('[data-repeatable-field="target"]')?.value.trim() || '';
                return label ? `${label}${value ? `: ${value}` : ''}` : '';
            },
            evidence: (row) => {
                const title = row.querySelector('[data-repeatable-field="title"]')?.value.trim() || '';
                const url = row.querySelector('[data-repeatable-field="url"]')?.value.trim() || '';
                return title ? `${title}${url ? ` — ${url}` : ''}` : '';
            },
            recognition: (row) => row.querySelector('[data-repeatable-field="title"]')?.value.trim() || '',
        };
        Object.entries(repeatableReview).forEach(([kind, formatRow]) => {
            const output = document.querySelector(`[data-review-repeatable="${kind}"]`);
            if (!output) return;
            output.replaceChildren();
            const values = Array.from(submissionForm.querySelectorAll(`[data-repeatable-row="${kind}"]`)).map(formatRow).filter(Boolean);
            values.forEach((value) => {
                const item = document.createElement('li');
                item.textContent = value;
                output.append(item);
            });
            if (!values.length) {
                const item = document.createElement('li');
                item.textContent = '—';
                output.append(item);
            }
        });
        document.querySelectorAll('[data-review-people]').forEach((output) => {
            const kind = output.dataset.reviewPeople;
            output.replaceChildren();
            const values = Array.from(submissionForm.querySelectorAll(`[data-person-row="${kind}"]`)).map((row) => {
                const value = (suffix) => row.querySelector(`[name$="[${suffix}]"]`)?.value.trim() || '';
                const name = value('full_name');
                if (!name) return '';
                if (kind === 'student') {
                    const leader = row.querySelector('[data-team-leader]')?.checked ? `${submissionForm.dataset.leaderLabel || 'Leader'}: ` : '';
                    return `${leader}${name} — ${value('class_name')} — ${value('role_title')}`;
                }
                return `${name} — ${value('position_title')} — ${value('role_title')}`;
            }).filter(Boolean);
            values.forEach((value) => {
                const item = document.createElement('li');
                item.textContent = value;
                output.append(item);
            });
            if (!values.length) {
                const item = document.createElement('li');
                item.textContent = '—';
                output.append(item);
            }
        });
        const mentoringOutput = document.querySelector('[data-review-mentoring]');
        if (mentoringOutput) {
            mentoringOutput.replaceChildren();
            const values = Array.from(submissionForm.querySelectorAll('[data-mentoring-row]')).map((row) => {
                const mentor = row.querySelector('[data-mentor-select]')?.selectedOptions[0]?.textContent.trim() || '';
                const focus = row.querySelector('[name$="[guidance_focus]"]')?.value.trim() || '';
                const outcome = row.querySelector('[name$="[guidance_outcome]"]')?.value.trim() || '';
                return mentor && (focus || outcome) ? `${mentor} — ${focus}${outcome ? ` → ${outcome}` : ''}` : '';
            }).filter(Boolean);
            values.forEach((value) => {
                const item = document.createElement('li');
                item.textContent = value;
                mentoringOutput.append(item);
            });
            if (!values.length) {
                const item = document.createElement('li');
                item.textContent = '—';
                mentoringOutput.append(item);
            }
        }
        const galleryCount = document.querySelector('[data-review-gallery-count]');
        if (galleryCount) {
            const gallery = submissionForm.querySelector('[data-media-collection="gallery"]')?.querySelectorAll('[data-media-row]').length || 0;
            galleryCount.textContent = `${gallery} / 10`;
        }
        document.querySelectorAll('[data-review-media-count]').forEach((output) => {
            output.textContent = String(submissionForm.querySelector(`[data-media-collection="${output.dataset.reviewMediaCount}"]`)?.querySelectorAll('[data-media-row]').length || 0);
        });
        const videoOutput = document.querySelector('[data-review-video]');
        if (videoOutput) videoOutput.textContent = submissionForm.querySelector('[data-video-url]')?.value.trim() || '—';
        const journeyOutput = document.querySelector('[data-review-journey]');
        if (journeyOutput) {
            journeyOutput.replaceChildren();
            const journeys = Array.from(submissionForm.querySelectorAll('[data-journey-row]')).map((row) => {
                const title = row.querySelector('[name$="[title]"]')?.value.trim() || '';
                const description = row.querySelector('[name$="[description]"]')?.value.trim() || '';
                return title ? `${title}${description ? ` — ${description}` : ''}` : '';
            }).filter(Boolean);
            journeys.forEach((value) => { const item = document.createElement('li'); item.textContent = value; journeyOutput.append(item); });
            if (!journeys.length) { const item = document.createElement('li'); item.textContent = '—'; journeyOutput.append(item); }
        }
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
        if (currentStep === 6) syncMentorOptions();
        if (focusPanel) {
            const panel = panels.find((item) => Number(item.dataset.submissionStep) === currentStep);
            panel?.scrollIntoView({ behavior: prefersReducedMotion ? 'auto' : 'smooth', block: 'start' });
        }
    };

    const validateImpactCollections = () => {
        const hasMetric = Array.from(submissionForm.querySelectorAll('[data-repeatable-row="metric"]')).some((row) => {
            const label = row.querySelector('[data-repeatable-field="label"]')?.value.trim();
            const value = row.querySelector('[data-repeatable-field="value"]')?.value.trim();
            const target = row.querySelector('[data-repeatable-field="target"]')?.value.trim();
            return label && (value || target);
        });
        const hasEvidence = Array.from(submissionForm.querySelectorAll('[data-repeatable-row="evidence"]')).some((row) => {
            const title = row.querySelector('[data-repeatable-field="title"]')?.value.trim();
            const url = row.querySelector('[data-repeatable-field="url"]')?.value.trim();
            return title && url;
        });
        return Boolean(hasMetric && hasEvidence);
    };

    const validateParticipants = () => submissionForm.querySelectorAll('[data-person-row="student"]').length > 0;

    const validateMedia = () => {
        const gallery = submissionForm.querySelector('[data-media-collection="gallery"]')?.querySelectorAll('[data-media-row]').length || 0;
        const solutionVisuals = submissionForm.querySelector('[data-media-collection="solution_visual"]')?.querySelectorAll('[data-media-row]').length || 0;
        const journeys = Array.from(submissionForm.querySelectorAll('[data-journey-row]'));
        return (gallery >= 1 || solutionVisuals >= 1) && journeys.length >= 1 && journeys.every((row) =>
            Boolean(row.querySelector('[name$="[title]"]')?.value.trim() && row.querySelector('[name$="[description]"]')?.value.trim())
        );
    };

    const validatePanel = (step) => {
        const panel = panels.find((item) => Number(item.dataset.submissionStep) === step);
        if (!panel) return true;
        if (step === 4 && !validateImpactCollections()) {
            showStep(step, true);
            if (formMessage) {
                formMessage.hidden = false;
                formMessage.className = 'form-message form-message--error';
                formMessage.textContent = submissionForm.dataset.impactMessage || 'Add an impact metric and evidence item.';
            }
            return false;
        }
        if (step === 5 && !validateParticipants()) {
            showStep(step, true);
            if (formMessage) {
                formMessage.hidden = false;
                formMessage.className = 'form-message form-message--error';
                formMessage.textContent = submissionForm.dataset.participantMessage || 'Add at least one student.';
            }
            return false;
        }
        if (step === 7 && !validateMedia()) {
            showStep(step, true);
            if (formMessage) {
                formMessage.hidden = false;
                formMessage.className = 'form-message form-message--error';
                formMessage.textContent = submissionForm.dataset.mediaMessage || 'Add at least one image and one complete journey record.';
            }
            return false;
        }
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
        if (validatePanel(1) && validatePanel(2) && validatePanel(3) && validatePanel(4) && validatePanel(5) && validatePanel(6) && validatePanel(7)) showStep(8, true);
    });
    stepButtons.forEach((button) => button.addEventListener('click', () => showStep(Number(button.dataset.stepTarget), true)));
    submissionForm.querySelectorAll('[data-go-to-step]').forEach((button) => button.addEventListener('click', () => showStep(Number(button.dataset.goToStep), true)));
    if (categorySelect instanceof HTMLSelectElement) categorySelect.addEventListener('change', syncAdaptiveCategory);
    submissionForm.querySelectorAll('[data-add-repeatable]').forEach((button) => {
        button.addEventListener('click', () => {
            const kind = button.dataset.addRepeatable;
            const template = submissionForm.querySelector(`[data-repeatable-template="${kind}"]`);
            const list = submissionForm.querySelector(`[data-repeatable-list="${kind}"]`);
            if (!(template instanceof HTMLTemplateElement) || !list || list.children.length >= 20) return;
            const holder = document.createElement('div');
            holder.innerHTML = template.innerHTML.replaceAll('__INDEX__', String(repeatableIndex++));
            const row = holder.firstElementChild;
            if (!row) return;
            list.append(row);
            row.querySelector('input, textarea, select')?.focus();
        });
    });
    submissionForm.querySelectorAll('[data-add-person]').forEach((button) => {
        button.addEventListener('click', () => {
            const kind = button.dataset.addPerson;
            const template = submissionForm.querySelector(`[data-person-template="${kind}"]`);
            const list = submissionForm.querySelector(`[data-person-list="${kind}"]`);
            if (!(template instanceof HTMLTemplateElement) || !list || list.children.length >= 30) return;
            const key = `${kind}-new-${personIndex++}`;
            const holder = document.createElement('div');
            holder.innerHTML = template.innerHTML.replaceAll('__KEY__', key);
            const row = holder.firstElementChild;
            if (!row) return;
            list.append(row);
            syncMentorOptions();
            row.querySelector('input:not([type="hidden"]), textarea, select')?.focus();
        });
    });
    const addMentoringButton = submissionForm.querySelector('[data-add-mentoring-record]');
    addMentoringButton?.addEventListener('click', () => {
        const template = submissionForm.querySelector('[data-mentoring-template]');
        const list = submissionForm.querySelector('[data-mentoring-list]');
        if (!(template instanceof HTMLTemplateElement) || !list || list.children.length >= 30) return;
        const holder = document.createElement('div');
        holder.innerHTML = template.innerHTML.replaceAll('__KEY__', `guidance-new-${mentoringIndex++}`);
        const row = holder.firstElementChild;
        if (!row) return;
        list.append(row);
        syncMentorOptions();
        row.querySelector('[data-mentor-select]')?.focus();
    });
    submissionForm.querySelector('[data-add-journey]')?.addEventListener('click', () => {
        const template = submissionForm.querySelector('[data-journey-template]');
        const list = submissionForm.querySelector('[data-journey-list]');
        if (!(template instanceof HTMLTemplateElement) || !list || list.children.length >= 20) return;
        const holder = document.createElement('div');
        holder.innerHTML = template.innerHTML.replaceAll('__KEY__', `journey-new-${journeyIndex++}`);
        const row = holder.firstElementChild;
        if (!row) return;
        list.append(row);
        const empty = submissionForm.querySelector('[data-journey-empty]');
        if (empty) empty.hidden = true;
        row.querySelector('input:not([type="hidden"])')?.focus();
    });
    submissionForm.addEventListener('click', (event) => {
        if (!(event.target instanceof Element)) return;
        const removeButton = event.target.closest('[data-remove-repeatable]');
        if (removeButton instanceof HTMLButtonElement) {
            removeButton.closest('[data-repeatable-row]')?.remove();
            return;
        }
        const removePerson = event.target.closest('[data-remove-person]');
        if (removePerson instanceof HTMLButtonElement) {
            const personRow = removePerson.closest('[data-person-row]');
            if (personRow?.dataset.personRow === 'mentor') {
                const reference = mentorReferenceForRow(personRow);
                submissionForm.querySelectorAll('[data-mentoring-row]').forEach((record) => {
                    if (record.querySelector('[data-mentor-select]')?.value === reference) record.remove();
                });
            }
            personRow?.remove();
            syncMentorOptions();
            return;
        }
        const removeMentoring = event.target.closest('[data-remove-mentoring]');
        if (removeMentoring instanceof HTMLButtonElement) {
            removeMentoring.closest('[data-mentoring-row]')?.remove();
            syncMentorOptions();
            return;
        }
        const removeMedia = event.target.closest('[data-remove-media]');
        if (removeMedia instanceof HTMLButtonElement) {
            const collection = removeMedia.closest('[data-media-collection]');
            removeMedia.closest('[data-media-row]')?.remove();
            const firstCover = submissionForm.querySelector('[data-media-collection="gallery"] [data-media-row] input[name="gallery_cover"]');
            if (firstCover instanceof HTMLInputElement && !submissionForm.querySelector('input[name="gallery_cover"]:checked')) firstCover.checked = true;
            const empty = collection?.querySelector('[data-media-empty]');
            if (empty) empty.hidden = Boolean(collection?.querySelector('[data-media-row]'));
            return;
        }
        const removeJourney = event.target.closest('[data-remove-journey]');
        if (removeJourney instanceof HTMLButtonElement) {
            removeJourney.closest('[data-journey-row]')?.remove();
            const empty = submissionForm.querySelector('[data-journey-empty]');
            if (empty) empty.hidden = submissionForm.querySelectorAll('[data-journey-row]').length > 0;
            return;
        }
        const moveButton = event.target.closest('[data-move-person]');
        if (!(moveButton instanceof HTMLButtonElement)) return;
        const row = moveButton.closest('[data-person-row]');
        if (!row) return;
        if (moveButton.dataset.movePerson === 'up' && row.previousElementSibling) row.parentElement?.insertBefore(row, row.previousElementSibling);
        if (moveButton.dataset.movePerson === 'down' && row.nextElementSibling) row.parentElement?.insertBefore(row.nextElementSibling, row);
    });
    submissionForm.addEventListener('change', (event) => {
        if (!(event.target instanceof HTMLInputElement)) return;
        if (event.target.matches('[data-team-leader]') && event.target.checked) {
            submissionForm.querySelectorAll('[data-team-leader]').forEach((checkbox) => {
                if (checkbox !== event.target) checkbox.checked = false;
            });
            const row = event.target.closest('[data-person-row="student"]');
            if (row) row.parentElement?.prepend(row);
        }
        if (event.target.matches('[data-media-input]')) {
            const input = event.target;
            const collection = input.closest('[data-media-collection]');
            const list = collection?.querySelector('[data-media-list]');
            if (!collection || !list || !input.files) return;
            list.querySelectorAll('[data-media-new]').forEach((row) => row.remove());
            const existingCount = list.querySelectorAll('[data-media-row]').length;
            const files = Array.from(input.files);
            const limit = Number(collection.dataset.mediaLimit) || 1;
            const mediaType = collection.dataset.mediaCollection || 'gallery';
            const invalid = files.some((file) => !['image/jpeg', 'image/png', 'image/webp'].includes(file.type) || file.size > 5 * 1024 * 1024) || existingCount + files.length > limit;
            input.setCustomValidity(invalid ? (submissionForm.dataset.galleryFileMessage || 'Choose valid images under 5MB within this section limit.') : '');
            if (invalid) { input.reportValidity(); return; }
            files.forEach((file, index) => {
                const reference = `new:${index}`;
                const row = document.createElement('article');
                row.className = 'gallery-editor-card'; row.draggable = true; row.dataset.mediaRow = ''; row.dataset.mediaNew = ''; row.dataset.mediaReference = reference;
                const order = document.createElement('input'); order.type = 'hidden'; order.name = `${mediaType}_order[]`; order.value = reference;
                const preview = document.createElement('div'); preview.className = 'gallery-editor-card__image';
                const objectUrl = URL.createObjectURL(file); preview.dataset.objectUrl = objectUrl;
                const image = document.createElement('img'); image.src = objectUrl; image.alt = ''; preview.append(image);
                const body = document.createElement('div'); body.className = 'gallery-editor-card__body';
                const handle = document.createElement('span'); handle.className = 'gallery-drag-handle'; handle.textContent = '↕';
                body.append(handle);
                if (mediaType === 'gallery') { const coverLabel = document.createElement('label'); coverLabel.className = 'form-check form-check--compact'; const cover = document.createElement('input'); cover.type = 'radio'; cover.name = 'gallery_cover'; cover.value = reference; const coverText = document.createElement('span'); coverText.textContent = 'Cover'; coverLabel.append(cover, coverText); body.append(coverLabel); }
                const remove = document.createElement('button'); remove.type = 'button'; remove.className = 'text-button text-button--danger'; remove.dataset.removeMedia = ''; remove.textContent = '×'; body.append(remove);
                const captionLabel = document.createElement('label'); captionLabel.className = 'form-field gallery-caption'; const captionText = document.createElement('span'); captionText.textContent = 'Caption'; const caption = document.createElement('input'); caption.type = 'text'; caption.name = `${mediaType}_captions[${reference}]`; caption.maxLength = 1000; captionLabel.append(captionText, caption);
                row.append(order, preview, body, captionLabel); list.append(row);
            });
            const firstCover = submissionForm.querySelector('[data-media-collection="gallery"] [data-media-row] input[name="gallery_cover"]');
            if (firstCover instanceof HTMLInputElement && !submissionForm.querySelector('input[name="gallery_cover"]:checked')) firstCover.checked = true;
            const empty = collection.querySelector('[data-media-empty]'); if (empty) empty.hidden = list.children.length > 0;
            return;
        }
        if (event.target.matches('[data-media-replacement]') && event.target.files?.[0]) {
            const file = event.target.files[0];
            const valid = ['image/jpeg', 'image/png', 'image/webp'].includes(file.type) && file.size <= 5 * 1024 * 1024;
            event.target.setCustomValidity(valid ? '' : (submissionForm.dataset.galleryFileMessage || 'Choose a valid image under 5MB.'));
            if (!valid) { event.target.reportValidity(); return; }
            const preview = event.target.closest('[data-media-row]')?.querySelector('[data-media-preview]');
            if (preview) { if (preview.dataset.objectUrl) URL.revokeObjectURL(preview.dataset.objectUrl); const url = URL.createObjectURL(file); preview.dataset.objectUrl = url; const image = document.createElement('img'); image.src = url; image.alt = ''; preview.replaceChildren(image); }
            return;
        }
        if (!event.target.matches('[data-photo-input]') || !event.target.files?.[0]) return;
        const file = event.target.files[0];
        if (!['image/jpeg', 'image/png', 'image/webp'].includes(file.type) || file.size > 5 * 1024 * 1024) {
            event.target.setCustomValidity(submissionForm.dataset.participantMessage || 'Choose a valid JPG, PNG or WebP image under 5MB.');
            event.target.reportValidity();
            return;
        }
        event.target.setCustomValidity('');
        const preview = event.target.closest('.profile-photo-editor')?.querySelector('[data-photo-preview]');
        if (!preview) return;
        if (preview.dataset.objectUrl) URL.revokeObjectURL(preview.dataset.objectUrl);
        const objectUrl = URL.createObjectURL(file);
        preview.dataset.objectUrl = objectUrl;
        const image = document.createElement('img');
        image.src = objectUrl;
        image.alt = '';
        preview.replaceChildren(image);
    });
    let draggedRow = null;
    submissionForm.addEventListener('dragstart', (event) => {
        if (!(event.target instanceof Element)) return;
        draggedRow = event.target.closest('[data-media-row], [data-journey-row]');
        if (draggedRow instanceof HTMLElement) { draggedRow.classList.add('is-dragging'); event.dataTransfer?.setData('text/plain', 'reorder'); }
    });
    submissionForm.addEventListener('dragover', (event) => {
        if (!(draggedRow instanceof HTMLElement) || !(event.target instanceof Element)) return;
        const target = event.target.closest(draggedRow.matches('[data-media-row]') ? '[data-media-row]' : '[data-journey-row]');
        if (!target || target === draggedRow || target.parentElement !== draggedRow.parentElement) return;
        event.preventDefault();
        const bounds = target.getBoundingClientRect();
        const sameGalleryRow = draggedRow.matches('[data-media-row]') && event.clientY >= bounds.top && event.clientY <= bounds.bottom;
        const insertBefore = sameGalleryRow ? event.clientX < bounds.left + bounds.width / 2 : event.clientY < bounds.top + bounds.height / 2;
        target.parentElement?.insertBefore(draggedRow, insertBefore ? target : target.nextSibling);
    });
    submissionForm.addEventListener('dragend', () => { if (draggedRow instanceof HTMLElement) draggedRow.classList.remove('is-dragging'); draggedRow = null; });
    submissionForm.addEventListener('input', (event) => {
        if (!(event.target instanceof Element)) return;
        if (event.target.matches('[data-person-row="mentor"] [name$="[full_name]"]')) syncMentorOptions();
    });

    submissionForm.addEventListener('submit', (event) => {
        const submitter = event.submitter;
        if (!(submitter instanceof HTMLButtonElement) || submitter.value !== 'submit_review') return;
        for (const step of [1, 2, 3, 4, 5, 6, 7, 8]) {
            if (!validatePanel(step)) {
                event.preventDefault();
                return;
            }
        }
    });

    syncAdaptiveCategory();
    syncMentorOptions();
    showStep(currentStep);
}

/* UX patch: guided submit project form */
(() => {
    const form = document.getElementById('submission-form');
    if (!form) return;

    const fillButtons = form.querySelectorAll('[data-fill-target][data-fill-text]');
    fillButtons.forEach((button) => {
        button.addEventListener('click', () => {
            const target = form.querySelector(`[name="${button.dataset.fillTarget}"]`);
            if (!target) return;
            const text = String(button.dataset.fillText || '').trim();
            const current = String(target.value || '').trim();
            target.value = current ? `${current}\n${text}` : text;
            target.dispatchEvent(new Event('input', { bubbles: true }));
            target.focus();
        });
    });

    const stepItems = document.querySelectorAll('.pitch-guide [data-step-fields]');
    const progressCount = document.querySelector('[data-progress-count]');
    const progressMeter = document.querySelector('[data-progress-meter]');
    const requiredCount = document.querySelector('[data-required-count]');
    const requiredFields = Array.from(form.querySelectorAll('[required]'));

    const fieldHasValue = (fieldName) => {
        return Array.from(form.querySelectorAll(`[name="${fieldName}"]`)).some((field) => {
            if (field.type === 'checkbox' || field.type === 'radio') return field.checked;
            return String(field.value || '').trim() !== '';
        });
    };

    const updateProgress = () => {
        let started = 0;
        stepItems.forEach((item) => {
            const fields = String(item.dataset.stepFields || '').split(',').map((x) => x.trim()).filter(Boolean);
            const isStarted = fields.some(fieldHasValue);
            item.classList.toggle('is-started', isStarted);
            if (isStarted) started += 1;
        });

        if (progressCount) progressCount.textContent = `${started}/${stepItems.length || 6}`;
        if (progressMeter) progressMeter.value = started;

        if (requiredCount) {
            const remaining = requiredFields.filter((field) => {
                if (field.disabled || field.readOnly) return false;
                if (field.type === 'checkbox' || field.type === 'radio') {
                    const group = field.name ? requiredFields.filter((other) => other.name === field.name) : [field];
                    return !group.some((other) => other.checked);
                }
                return String(field.value || '').trim() === '';
            }).filter((field, index, fields) => fields.findIndex((other) => other.name === field.name) === index).length;

            requiredCount.textContent = remaining === 0
                ? (form.dataset.requiredComplete || 'Semua medan wajib sudah diisi. Anda boleh hantar untuk semakan.')
                : (form.dataset.requiredRemaining || '{count} medan wajib belum diisi sebelum boleh hantar semakan.').replace('{count}', String(remaining));
        }
    };

    form.addEventListener('input', updateProgress);
    form.addEventListener('change', updateProgress);
    updateProgress();
})();
