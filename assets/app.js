(function () {
    'use strict';

    const STORAGE_KEYS = {
        jobs: 'nbss_hr_services_jobs_html_v1',
        applications: 'nbss_hr_services_applications_html_v1',
        employer: 'nbss_hr_services_employer_html_v1'
    };

    const EMPLOYER_ACCOUNT = {
        name: 'NBSS HR Services Employer',
        email: 'employer@nbsshrservices.in',
        password: 'Employer@123'
    };

    const JOB_TYPE_CHOICES = [
        'Work From Home',
        'Part Time',
        'Full Time',
        'Part Time / Full Time Both',
        'Hybrid',
        'Remote',
        'Contract',
        'Shift-based',
        'Night Shift',
        'Full-time',
        'Part-time'
    ];

    const state = {
        jobs: [],
        applications: [],
        employer: null,
        currentView: 'home',
        pendingView: 'post-job',
        selectedJobId: '',
        editingJobId: '',
        modalJobId: '',
        filters: {
            keyword: '',
            location: '',
            category: '',
            type: ''
        },
        loginErrors: [],
        jobErrors: [],
        applyErrors: [],
        flash: null,
        jobDraft: null
    };

    const popularSearches = [
        {
            rank: 'Trending at #1',
            title: 'Jobs for Freshers',
            copy: 'Back office, telecalling, and entry-level operations roles.',
            key: 'category',
            value: 'Telecalling / BPO / Telesales'
        },
        {
            rank: 'Trending at #2',
            title: 'Work from home jobs',
            copy: 'Remote-friendly roles for support, content, and software teams.',
            key: 'type',
            value: 'Remote'
        },
        {
            rank: 'Trending at #3',
            title: 'Part time jobs',
            copy: 'Flexible delivery and shift-based work across fast-moving teams.',
            key: 'type',
            value: 'Part-time'
        },
        {
            rank: 'Trending at #4',
            title: 'Night shift jobs',
            copy: 'Customer care and operations roles with late-hour hiring needs.',
            key: 'type',
            value: 'Night Shift'
        }
    ];

    document.addEventListener('DOMContentLoaded', init);

    async function init() {
        bindEvents();
        await seedStorage();
        hydrateState();
        renderApp();
    }

    async function seedStorage() {
        const seedJobs = await fetchJson('data/jobs.json');
        const seedApplications = await fetchJson('data/applications.json');

        if (!Array.isArray(readJson(STORAGE_KEYS.jobs, null)) && seedJobs.length > 0) {
            writeJson(STORAGE_KEYS.jobs, seedJobs.map(normalizeJob));
        }

        if (!Array.isArray(readJson(STORAGE_KEYS.applications, null)) && seedApplications.length > 0) {
            writeJson(STORAGE_KEYS.applications, seedApplications.map(normalizeApplication));
        }
    }

    function hydrateState() {
        state.jobs = ensureJobArray(readJson(STORAGE_KEYS.jobs, []));
        state.applications = ensureApplicationArray(readJson(STORAGE_KEYS.applications, []));
        state.employer = readJson(STORAGE_KEYS.employer, null);

        if (!isEmployerLoggedIn()) {
            state.employer = null;
        }

        const activeJobs = getActiveJobs();
        if (activeJobs.length > 0 && findJobById(state.selectedJobId) === null) {
            state.selectedJobId = activeJobs[0].id;
        }
    }

    function bindEvents() {
        document.addEventListener('click', handleClick);
        document.addEventListener('submit', handleSubmit);
    }

    function handleClick(event) {
        const viewLink = event.target.closest('[data-view-link]');
        if (viewLink) {
            event.preventDefault();
            openView(viewLink.getAttribute('data-view-link') || 'home');
            return;
        }

        const goHome = event.target.closest('[data-go-home]');
        if (goHome) {
            event.preventDefault();
            openView('home');
            const href = goHome.getAttribute('href') || '#job-feed';
            requestAnimationFrame(function () {
                scrollToSection(href);
            });
            return;
        }

        const filterLink = event.target.closest('[data-filter-key]');
        if (filterLink) {
            event.preventDefault();
            const key = filterLink.getAttribute('data-filter-key') || '';
            const value = filterLink.getAttribute('data-filter-value') || '';
            if (key !== '') {
                state.filters[key] = value;
                syncFilterFields();
                openView('home');
                requestAnimationFrame(function () {
                    scrollToSection('#job-feed');
                });
            }
            return;
        }

        const jobSelect = event.target.closest('[data-job-select]');
        if (jobSelect) {
            event.preventDefault();
            const jobId = jobSelect.getAttribute('data-job-select') || '';
            if (jobId !== '') {
                state.selectedJobId = jobId;
                renderHome();
                scrollToSection('#job-details');
            }
            return;
        }

        const applyTrigger = event.target.closest('[data-job-apply]');
        if (applyTrigger) {
            event.preventDefault();
            openApplyModal(applyTrigger.getAttribute('data-job-apply') || '');
            return;
        }

        const editJob = event.target.closest('[data-edit-job]');
        if (editJob) {
            event.preventDefault();
            state.editingJobId = editJob.getAttribute('data-edit-job') || '';
            state.jobDraft = null;
            state.jobErrors = [];
            openProtectedView('post-job');
            return;
        }

        const toggleJob = event.target.closest('[data-toggle-job]');
        if (toggleJob) {
            event.preventDefault();
            toggleJobStatus(toggleJob.getAttribute('data-toggle-job') || '');
            return;
        }

        const deleteJob = event.target.closest('[data-delete-job]');
        if (deleteJob) {
            event.preventDefault();
            const jobId = deleteJob.getAttribute('data-delete-job') || '';
            if (jobId !== '' && window.confirm('Delete this role permanently?')) {
                removeJob(jobId);
            }
            return;
        }

        const clearEdit = event.target.closest('[data-clear-edit]');
        if (clearEdit) {
            event.preventDefault();
            clearEditingState();
            return;
        }

        if (event.target.id === 'login-button') {
            event.preventDefault();
            state.pendingView = 'post-job';
            state.loginErrors = [];
            state.currentView = 'employer-login';
            renderApp();
            return;
        }

        if (event.target.id === 'logout-button') {
            event.preventDefault();
            logoutEmployer();
            return;
        }

        if (event.target.id === 'results-employer-button') {
            event.preventDefault();
            openProtectedView('dashboard');
            return;
        }

        if (event.target.id === 'download-cta-button') {
            event.preventDefault();
            openProtectedView('dashboard');
            return;
        }

        if (event.target.id === 'employer-cta-primary') {
            event.preventDefault();
            openProtectedView('post-job');
            return;
        }

        if (event.target.id === 'employer-cta-secondary') {
            event.preventDefault();
            openView('home');
            requestAnimationFrame(function () {
                scrollToSection('#job-feed');
            });
            return;
        }

        if (event.target.id === 'reset-filters-button') {
            event.preventDefault();
            resetFilters();
            return;
        }

        if (event.target.id === 'apply-modal-backdrop' || event.target.id === 'apply-modal-close' || event.target.id === 'apply-cancel-button') {
            event.preventDefault();
            closeApplyModal();
            return;
        }

        if (event.target.closest('[data-view-live-job]')) {
            event.preventDefault();
            const jobId = event.target.closest('[data-view-live-job]').getAttribute('data-view-live-job') || '';
            state.selectedJobId = jobId;
            openView('home');
            requestAnimationFrame(function () {
                scrollToSection('#job-details');
            });
            return;
        }

        if (event.target.closest('[data-export-applications]')) {
            event.preventDefault();
            exportApplicationsToExcel();
        }
    }

    function handleSubmit(event) {
        if (event.target.id === 'hero-search-form') {
            event.preventDefault();
            const formData = new FormData(event.target);
            state.filters.keyword = clean(formData.get('keyword'));
            state.filters.location = clean(formData.get('location'));
            state.filters.category = '';
            state.filters.type = '';
            openView('home');
            syncFilterFields();
            requestAnimationFrame(function () {
                scrollToSection('#job-feed');
            });
            return;
        }

        if (event.target.id === 'jobs-filter-form') {
            event.preventDefault();
            const formData = new FormData(event.target);
            state.filters.keyword = clean(formData.get('keyword'));
            state.filters.location = clean(formData.get('location'));
            state.filters.category = clean(formData.get('category'));
            state.filters.type = clean(formData.get('type'));
            state.selectedJobId = '';
            renderHome();
            return;
        }

        if (event.target.id === 'employer-login-form') {
            event.preventDefault();
            submitEmployerLogin(event.target);
            return;
        }

        if (event.target.id === 'employer-job-form') {
            event.preventDefault();
            submitEmployerJob(event.target);
            return;
        }

        if (event.target.id === 'apply-job-form') {
            event.preventDefault();
            submitApplication(event.target);
        }
    }

    function openView(view) {
        if ((view === 'post-job' || view === 'dashboard') && !isEmployerLoggedIn()) {
            openProtectedView(view);
            return;
        }

        state.currentView = view;
        if (view === 'home') {
            clearFlash();
        }
        renderApp();
    }

    function openProtectedView(view) {
        if (isEmployerLoggedIn()) {
            state.currentView = view;
            renderApp();
            return;
        }

        state.pendingView = view;
        state.currentView = 'employer-login';
        showFlash('error', 'Please log in as an employer to access this page.');
        renderApp();
    }

    function renderApp() {
        renderHeader();
        renderFlash();
        renderHome();
        renderEmployerLoginView();
        renderPostJobView();
        renderDashboardView();
        updateViewVisibility();
        updateDocumentTitle();
    }

    function renderHeader() {
        const protectedLinks = document.querySelectorAll('[data-protected-link]');
        const viewLinks = document.querySelectorAll('[data-view-link]');
        const loginButton = document.getElementById('login-button');
        const logoutButton = document.getElementById('logout-button');
        const sessionLabel = document.getElementById('header-session');

        protectedLinks.forEach(function (link) {
            link.hidden = !isEmployerLoggedIn();
        });

        viewLinks.forEach(function (link) {
            link.classList.toggle('is-active', link.getAttribute('data-view-link') === state.currentView);
        });

        if (isEmployerLoggedIn()) {
            sessionLabel.hidden = false;
            sessionLabel.textContent = state.employer.name || EMPLOYER_ACCOUNT.name;
            logoutButton.hidden = false;
            loginButton.hidden = true;
        } else {
            sessionLabel.hidden = true;
            sessionLabel.textContent = '';
            logoutButton.hidden = true;
            loginButton.hidden = false;
        }
    }

    function renderFlash() {
        const wrap = document.getElementById('flash-wrap');
        const banner = document.getElementById('flash-banner');

        if (state.flash === null) {
            wrap.hidden = true;
            banner.className = 'flash-banner';
            banner.textContent = '';
            return;
        }

        wrap.hidden = false;
        banner.className = 'flash-banner ' + (state.flash.type === 'error' ? 'is-error' : 'is-success');
        banner.textContent = state.flash.message;
    }

    function renderHome() {
        renderHeroMetrics();
        renderQuickLinks();
        renderTrustedEmployers();
        renderFeaturedShowcase();
        renderPopularSearchCards();
        renderDiscoverySections();
        renderFooter();
        renderFilterOptions();
        renderJobsFeed();
        updateEmployerCtas();
    }

    function renderHeroMetrics() {
        const activeJobs = getActiveJobs();
        const companies = uniqueValues(activeJobs, 'company');
        const remoteCount = activeJobs.filter(function (job) {
            const type = String(job.type || '').toLowerCase();
            const location = String(job.location || '').toLowerCase();
            return type === 'remote' || type === 'work from home' || location === 'remote';
        }).length;

        text('stat-jobs-count', String(activeJobs.length));
        text('stat-companies-count', String(companies.length));
        text('stat-applications-count', String(state.applications.length));
        text('remote-count-pill', remoteCount + ' remote');
        text('download-applications', String(state.applications.length));
        text('download-jobs', String(activeJobs.length));

        setOptions('hero-location', uniqueValues(activeJobs, 'location'));
    }

    function renderQuickLinks() {
        const container = document.getElementById('home-quick-links');
        const links = [
            ['Work From Home Jobs', 'type', 'Remote'],
            ['Part Time Jobs', 'type', 'Part-time'],
            ['Full Time Jobs', 'type', 'Full-time'],
            ['Night Shift Jobs', 'type', 'Night Shift'],
            ['Hybrid Jobs', 'type', 'Hybrid'],
            ['Sales Jobs', 'category', 'Sales & BD']
        ];

        container.innerHTML = links.map(function (item) {
            return '<a class="quick-link-pill" href="#job-feed" data-filter-key="' + escapeHtml(item[1]) + '" data-filter-value="' + escapeHtml(item[2]) + '">' + escapeHtml(item[0]) + '</a>';
        }).join('');
    }

    function renderTrustedEmployers() {
        const container = document.getElementById('trusted-employers');
        const companies = uniqueValues(getActiveJobs(), 'company').slice(0, 6);

        container.innerHTML = companies.map(function (company) {
            return '<div class="trusted-pill"><span class="company-badge">' + escapeHtml(companyMonogram(company)) + '</span><span>' + escapeHtml(company) + '</span></div>';
        }).join('');
    }

    function renderFeaturedShowcase() {
        const container = document.getElementById('featured-showcase');
        const activeJobs = getActiveJobs();
        const featured = activeJobs.find(function (job) {
            return !!job.featured;
        }) || activeJobs[0];

        if (!featured) {
            container.innerHTML = '<div class="empty-card"><h3>No jobs available yet.</h3><p>Employer posted roles will appear here automatically.</p></div>';
            return;
        }

        const topCategories = breakdown(getActiveJobs(), 'category').slice(0, 6);

        container.innerHTML = '' +
            '<div class="showcase-phone">' +
                '<span class="micro-pill is-featured">Hot role</span>' +
                '<h4>' + escapeHtml(featured.title) + '</h4>' +
                '<p>' + escapeHtml(featured.company) + ' · ' + escapeHtml(featured.location) + '</p>' +
                '<div class="tag-row">' +
                    '<span>' + escapeHtml(featured.type) + '</span>' +
                    '<span>' + escapeHtml(featured.experience) + '</span>' +
                '</div>' +
                '<button class="button button-primary button-wide" type="button" data-job-select="' + escapeHtml(featured.id) + '">View hot role</button>' +
            '</div>' +
            '<div class="showcase-side-stats">' +
                '<article><strong>' + escapeHtml(topCategories[0] ? topCategories[0].label : 'Jobs') + '</strong><span>top hiring lane</span></article>' +
                '<article><strong>' + escapeHtml(averageApplicationsPerRole()) + '</strong><span>avg applications per role</span></article>' +
                '<article><strong>' + escapeHtml(String(topCategories.length)) + '</strong><span>popular departments</span></article>' +
            '</div>';
    }

    function renderPopularSearchCards() {
        const container = document.getElementById('popular-search-cards');
        container.innerHTML = popularSearches.map(function (item) {
            return '' +
                '<a class="popular-card" href="#job-feed" data-filter-key="' + escapeHtml(item.key) + '" data-filter-value="' + escapeHtml(item.value) + '">' +
                    '<p class="popular-rank">' + escapeHtml(item.rank) + '</p>' +
                    '<h3>' + escapeHtml(item.title) + '</h3>' +
                    '<p>' + escapeHtml(item.copy) + '</p>' +
                    '<span class="text-link">View all</span>' +
                '</a>';
        }).join('');
    }

    function renderDiscoverySections() {
        renderBreakdownLinks('jobs-by-type-grid', breakdown(getActiveJobs(), 'type').slice(0, 6), 'type', 'openings');
        renderBreakdownLinks('jobs-by-category-grid', breakdown(getActiveJobs(), 'category').slice(0, 6), 'category', 'openings');
        renderFeaturedJobs();
        renderBreakdownLinks('jobs-by-city-grid', breakdown(getActiveJobs(), 'location').slice(0, 8), 'location', '');
        renderBreakdownLinks('jobs-by-company-grid', breakdown(getActiveJobs(), 'company').slice(0, 8), 'company', '', 'keyword');
    }

    function renderBreakdownLinks(id, items, field, suffix, filterKey) {
        const container = document.getElementById(id);
        const key = filterKey || field;
        const className = id.indexOf('city') >= 0 || id.indexOf('company') >= 0 ? 'list-pill' : 'cloud-pill';

        container.innerHTML = items.map(function (item) {
            return '<a class="' + className + '" href="#job-feed" data-filter-key="' + escapeHtml(key) + '" data-filter-value="' + escapeHtml(item.label) + '">' +
                '<span>' + escapeHtml(item.label) + '</span>' +
                '<strong>' + escapeHtml(String(item.count)) + (suffix !== '' ? ' ' + escapeHtml(suffix) : '') + '</strong>' +
            '</a>';
        }).join('');
    }

    function renderFeaturedJobs() {
        const container = document.getElementById('featured-jobs-grid');
        const activeJobs = getActiveJobs();
        const featured = activeJobs.filter(function (job) {
            return !!job.featured;
        });
        const cards = (featured.length > 0 ? featured : activeJobs).slice(0, 4);

        container.innerHTML = cards.map(function (job) {
            return '' +
                '<article class="featured-card">' +
                    '<div class="featured-card-top">' +
                        '<span class="company-badge">' + escapeHtml(companyMonogram(job.company)) + '</span>' +
                        '<span class="micro-pill is-featured">Featured</span>' +
                    '</div>' +
                    '<p class="eyebrow">' + escapeHtml(job.category) + '</p>' +
                    '<h3>' + escapeHtml(job.title) + '</h3>' +
                    '<p>' + escapeHtml(job.company) + ' · ' + escapeHtml(job.location) + '</p>' +
                    '<div class="tag-row">' +
                        '<span>' + escapeHtml(job.type) + '</span>' +
                        '<span>' + escapeHtml(job.experience) + '</span>' +
                    '</div>' +
                    '<div class="featured-card-footer">' +
                        '<span>' + escapeHtml(humanTimeDiff(job.created_at)) + '</span>' +
                        '<button class="text-link" type="button" data-job-select="' + escapeHtml(job.id) + '">Open role</button>' +
                    '</div>' +
                '</article>';
        }).join('');
    }

    function renderFooter() {
        renderFooterLinks('footer-types', uniqueValues(getActiveJobs(), 'type').slice(0, 5), 'type');
        renderFooterLinks('footer-cities', uniqueValues(getActiveJobs(), 'location').slice(0, 6), 'location');
        renderFooterLinks('footer-companies', uniqueValues(getActiveJobs(), 'company').slice(0, 6), 'keyword');
    }

    function renderFooterLinks(id, items, key) {
        const container = document.getElementById(id);
        container.innerHTML = items.map(function (value) {
            return '<a href="#job-feed" data-filter-key="' + escapeHtml(key) + '" data-filter-value="' + escapeHtml(value) + '">' + escapeHtml(value) + '</a>';
        }).join('');
    }

    function renderFilterOptions() {
        const activeJobs = getActiveJobs();
        setOptions('filter-location', uniqueValues(activeJobs, 'location'));
        setOptions('filter-category', uniqueValues(activeJobs, 'category'));
        setOptions('filter-type', uniqueValues(activeJobs, 'type'));
        syncFilterFields();
    }

    function syncFilterFields() {
        value('hero-keyword', state.filters.keyword);
        value('hero-location', state.filters.location);
        value('filter-keyword', state.filters.keyword);
        value('filter-location', state.filters.location);
        value('filter-category', state.filters.category);
        value('filter-type', state.filters.type);
    }

    function renderJobsFeed() {
        const activeJobs = getActiveJobs();
        const filteredJobs = filterJobs(activeJobs, state.filters);
        const topCategories = breakdown(activeJobs, 'category').slice(0, 4);
        const jobsContainer = document.getElementById('jobs-stack');
        const selectedJob = pickSelectedJob(filteredJobs, activeJobs);

        text('results-count-label', filteredJobs.length + ' matching opportunities');
        document.getElementById('result-pills').innerHTML = topCategories.map(function (item) {
            return '<span class="results-pill">' + escapeHtml(item.label) + ' · ' + escapeHtml(String(item.count)) + '</span>';
        }).join('');

        if (filteredJobs.length === 0) {
            jobsContainer.innerHTML = '<div class="empty-card"><h3>No roles match your current filters.</h3><p>Try broadening the keyword or clearing one of the filters.</p></div>';
        } else {
            jobsContainer.innerHTML = filteredJobs.map(function (job) {
                const selectedClass = selectedJob && selectedJob.id === job.id ? ' is-selected' : '';
                return '' +
                    '<article class="job-card' + selectedClass + '">' +
                        '<div class="job-card-brand">' +
                            '<span class="company-badge">' + escapeHtml(companyMonogram(job.company)) + '</span>' +
                            '<div class="job-card-brand-copy">' +
                                '<div class="job-card-head">' +
                                    '<span class="eyebrow">' + escapeHtml(job.category) + '</span>' +
                                    '<span class="micro-pill ' + (job.featured ? 'is-featured' : '') + '">' + escapeHtml(job.featured ? 'Featured' : humanTimeDiff(job.created_at)) + '</span>' +
                                '</div>' +
                                '<h3>' + escapeHtml(job.title) + '</h3>' +
                                '<p class="job-company">' + escapeHtml(job.company) + ' · ' + escapeHtml(job.location) + '</p>' +
                            '</div>' +
                        '</div>' +
                        '<p class="job-salary">' + escapeHtml(job.salary) + '</p>' +
                        '<div class="tag-row">' +
                            '<span>' + escapeHtml(job.type) + '</span>' +
                            '<span>' + escapeHtml(job.experience) + '</span>' +
                            (job.skills[0] ? '<span>' + escapeHtml(job.skills[0]) + '</span>' : '') +
                        '</div>' +
                        '<p class="job-excerpt">' + escapeHtml(job.description) + '</p>' +
                        '<div class="card-actions">' +
                            '<button class="button button-ghost" type="button" data-job-select="' + escapeHtml(job.id) + '">View details</button>' +
                            '<button class="button button-primary" type="button" data-job-apply="' + escapeHtml(job.id) + '">Apply now</button>' +
                        '</div>' +
                    '</article>';
            }).join('');
        }

        renderSelectedJob(selectedJob);
    }

    function renderSelectedJob(selectedJob) {
        const details = document.getElementById('job-details');
        const applyCard = document.getElementById('apply-form-card');

        if (!selectedJob) {
            details.innerHTML = '<div class="empty-card"><h3>No active role selected.</h3><p>Select a job card to view more information here.</p></div>';
            applyCard.innerHTML = '<div class="empty-card"><h3>Application form unavailable.</h3><p>Choose a live job from the list to open the popup form.</p></div>';
            return;
        }

        const applicationsCount = state.applications.filter(function (application) {
            return application.job_id === selectedJob.id;
        }).length;

        details.innerHTML = '' +
            '<div class="job-card-head">' +
                '<span class="eyebrow">' + escapeHtml(selectedJob.category) + '</span>' +
                '<span class="micro-pill">' + escapeHtml(humanTimeDiff(selectedJob.created_at)) + '</span>' +
            '</div>' +
            '<div class="detail-header-block">' +
                '<span class="company-badge is-large">' + escapeHtml(companyMonogram(selectedJob.company)) + '</span>' +
                '<div>' +
                    '<h2>' + escapeHtml(selectedJob.title) + '</h2>' +
                    '<p class="job-company">' + escapeHtml(selectedJob.company) + ' · ' + escapeHtml(selectedJob.location) + '</p>' +
                '</div>' +
            '</div>' +
            '<p class="job-salary">' + escapeHtml(selectedJob.salary) + '</p>' +
            '<div class="detail-matrix">' +
                '<article class="detail-stat"><span>Work mode</span><strong>' + escapeHtml(selectedJob.type) + '</strong></article>' +
                '<article class="detail-stat"><span>Experience</span><strong>' + escapeHtml(selectedJob.experience) + '</strong></article>' +
                '<article class="detail-stat"><span>Applicants</span><strong>' + escapeHtml(String(applicationsCount)) + '</strong></article>' +
            '</div>' +
            '<div class="tag-row">' + selectedJob.skills.map(function (skill) {
                return '<span>' + escapeHtml(skill) + '</span>';
            }).join('') + '</div>' +
            '<p class="detail-copy">' + escapeHtml(selectedJob.description) + '</p>' +
            '<div class="detail-block"><h3>Requirements</h3><ul class="clean-list">' + selectedJob.requirements.map(function (requirement) {
                return '<li>' + escapeHtml(requirement) + '</li>';
            }).join('') + '</ul></div>' +
            '<div class="detail-block"><h3>Why this role stands out</h3><ul class="clean-list"><li>Clear compensation and work-mode visibility for faster decisions.</li><li>Focused requirements so candidates can quickly self-qualify.</li><li>Quick-apply popup keeps the form on the same page.</li></ul></div>';

        applyCard.innerHTML = '' +
            '<div class="section-heading">' +
                '<p class="eyebrow">Quick apply popup</p>' +
                '<h2>Open the form in a popup</h2>' +
            '</div>' +
            '<p class="form-note">Apply now दबाते ही form इसी page पर popup में खुलेगा, ताकि candidate बिना page छोड़े apply कर सके.</p>' +
            '<div class="apply-preview-card">' +
                '<span class="company-badge is-large">' + escapeHtml(companyMonogram(selectedJob.company)) + '</span>' +
                '<div><strong>' + escapeHtml(selectedJob.title) + '</strong><span>' + escapeHtml(selectedJob.company) + ' · ' + escapeHtml(selectedJob.location) + '</span></div>' +
            '</div>' +
            '<div class="form-actions">' +
                '<button class="button button-primary" type="button" data-job-apply="' + escapeHtml(selectedJob.id) + '">Open application form</button>' +
                '<button class="button button-ghost" type="button" data-view-link="home">Browse more jobs</button>' +
            '</div>' +
            '<p class="popup-hint">Popup form keeps the selected job, so the candidate can submit faster.</p>';
    }

    function renderEmployerLoginView() {
        const container = document.getElementById('view-employer-login');
        const targetLabel = state.pendingView === 'dashboard' ? 'dashboard' : 'job posting page';

        container.innerHTML = '' +
            '<section class="hero hero-compact">' +
                '<div class="container hero-grid">' +
                    '<div class="hero-copy">' +
                        '<p class="eyebrow">Employer access</p>' +
                        '<div class="hero-chip-row"><span class="hero-chip">Protected HR panel</span><span class="hero-chip">Post jobs securely</span><span class="hero-chip">Dashboard after login</span></div>' +
                        '<h1>Employer login required for posting and dashboard access.</h1>' +
                        '<p class="hero-text">Sign in to open the HR panel, publish jobs, edit roles, download candidate data, and manage hiring activity from one place.</p>' +
                    '</div>' +
                    '<div class="hero-panel stack-panel">' +
                        '<div class="hero-panel-top"><div><p class="panel-kicker">Redirect after login</p><h3>' + escapeHtml(targetLabel.charAt(0).toUpperCase() + targetLabel.slice(1)) + '</h3></div><span class="panel-badge">Employer only</span></div>' +
                        '<div class="panel-line"><strong>' + escapeHtml(String(getActiveJobs().length)) + '</strong><span>active roles can be managed after sign in</span></div>' +
                        '<div class="panel-line"><strong>' + escapeHtml(String(state.applications.length)) + '</strong><span>candidate records available in the dashboard</span></div>' +
                        '<div class="panel-line"><strong>' + escapeHtml(String(uniqueValues(getActiveJobs(), 'company').length)) + '</strong><span>companies already represented in this portal demo</span></div>' +
                    '</div>' +
                '</div>' +
            '</section>' +
            '<section class="section">' +
                '<div class="container split-layout">' +
                    '<div class="form-panel">' +
                        '<div class="section-heading"><p class="eyebrow">Sign in</p><h2>Open the employer panel</h2></div>' +
                        '<p class="form-note">Enter employer credentials to continue to the ' + escapeHtml(targetLabel) + '.</p>' +
                        renderErrorBox(state.loginErrors) +
                        '<form class="portal-form" id="employer-login-form">' +
                            '<label>Employer email<input type="email" name="email" placeholder="' + escapeHtml(EMPLOYER_ACCOUNT.email) + '" required></label>' +
                            '<label>Password<input type="password" name="password" placeholder="Enter password" required></label>' +
                            '<div class="form-actions"><button class="button button-primary" type="submit">Login as employer</button><button class="button button-ghost" type="button" data-view-link="home">Back to jobs</button></div>' +
                        '</form>' +
                    '</div>' +
                    '<aside class="side-panel">' +
                        '<div class="side-card accent-card"><p class="eyebrow">Access rules</p><h3>What unlocks after login</h3><ul class="clean-list"><li>Post a new job and edit existing roles.</li><li>Activate, deactivate, or delete posted jobs.</li><li>Open the dashboard and export all applications in Excel format.</li></ul></div>' +
                        '<div class="side-card"><p class="eyebrow">Demo credentials</p><h3>Use the starter employer account</h3><ul class="clean-list"><li>Email: ' + escapeHtml(EMPLOYER_ACCOUNT.email) + '</li><li>Password: ' + escapeHtml(EMPLOYER_ACCOUNT.password) + '</li></ul><p>This HTML version uses browser storage for demo data.</p></div>' +
                    '</aside>' +
                '</div>' +
            '</section>';
    }

    function renderPostJobView() {
        const container = document.getElementById('view-post-job');
        const allJobs = sortJobs(state.jobs.slice());
        const editingJob = state.editingJobId ? findJobById(state.editingJobId) : null;
        const draft = state.jobDraft || formDataFromJob(editingJob);

        container.innerHTML = '' +
            '<section class="hero hero-compact">' +
                '<div class="container hero-grid">' +
                    '<div class="hero-copy">' +
                        '<p class="eyebrow">For employers</p>' +
                        '<div class="hero-chip-row"><span class="hero-chip">Instant publish</span><span class="hero-chip">Edit live roles</span><span class="hero-chip">HTML hosted</span><span class="hero-chip">Browser data demo</span></div>' +
                        '<h1>Publish and manage hiring roles from one page.</h1>' +
                        '<p class="hero-text">HR teams can post fresh jobs, edit existing roles, and keep the live portal updated in a static HTML deployment using local storage.</p>' +
                    '</div>' +
                    '<div class="hero-panel stack-panel">' +
                        '<div class="hero-panel-top"><div><p class="panel-kicker">Employer workflow</p><h3>Post, edit, move fast.</h3></div><span class="panel-badge">Live sync</span></div>' +
                        '<div class="panel-line"><strong>' + escapeHtml(String(getActiveJobs().length)) + '</strong><span>active roles already live</span></div>' +
                        '<div class="panel-line"><strong>' + escapeHtml(String(state.applications.length)) + '</strong><span>applications tracked in browser storage</span></div>' +
                        '<div class="panel-line"><strong>' + escapeHtml(topBreakdownLabel(getActiveJobs(), 'category')) + '</strong><span>currently the strongest hiring category</span></div>' +
                    '</div>' +
                '</div>' +
            '</section>' +
            '<section class="section">' +
                '<div class="container split-layout">' +
                    '<div class="form-panel" id="employer-form">' +
                        '<div class="section-heading"><p class="eyebrow">Employer console</p><h2>' + escapeHtml(editingJob ? 'Edit live role' : 'Add a new role') + '</h2></div>' +
                        '<p class="form-note">' + escapeHtml(editingJob ? 'Update the selected role and save changes to push them live on the portal.' : 'Fill in the job details below and publish a new role instantly.') + '</p>' +
                        renderErrorBox(state.jobErrors) +
                        '<form class="portal-form" id="employer-job-form">' +
                            '<div class="form-grid">' +
                                renderInput('Job title', 'title', draft.title, 'Senior HTML Developer') +
                                renderInput('Company', 'company', draft.company, 'NBSS HR Services') +
                                renderInput('Location', 'location', draft.location, 'Delhi or Remote') +
                                renderInput('Salary range', 'salary', draft.salary, 'INR 8L - 12L') +
                                renderSelect('Job type', 'type', JOB_TYPE_CHOICES, draft.type) +
                                renderInput('Category', 'category', draft.category, 'Engineering, Sales, Operations') +
                                renderInput('Experience band', 'experience', draft.experience, '2-4 years', true) +
                                renderTextarea('Job description', 'description', draft.description, 'Describe the role, team, and impact.', 5, true) +
                                renderTextarea('Requirements', 'requirements', draft.requirements, 'One requirement per line', 4, true) +
                                renderTextarea('Skills or tags', 'skills', draft.skills, 'Comma separated skills', 3, true) +
                            '</div>' +
                            '<div class="form-actions">' +
                                '<button class="button button-primary" type="submit">' + escapeHtml(editingJob ? 'Save changes' : 'Publish role') + '</button>' +
                                (editingJob ? '<button class="button button-ghost" type="button" data-clear-edit>Create new role</button>' : '') +
                                '<button class="button button-ghost" type="button" data-view-link="home">Back to portal</button>' +
                            '</div>' +
                        '</form>' +
                    '</div>' +
                    '<aside class="side-panel">' +
                        '<div class="side-card"><p class="eyebrow">What this page does</p><h3>HR posting and editing flow</h3><p>Every successful post or edit is written to local browser storage, then surfaced instantly on the jobs feed and dashboard.</p></div>' +
                        '<div class="side-card"><p class="eyebrow">Recommended fields</p><ul class="clean-list"><li>Keep the job title specific and searchable.</li><li>Use a realistic salary range to improve applications.</li><li>Add 3-5 concrete requirements so candidates self-select better.</li></ul></div>' +
                        '<div class="side-card accent-card"><p class="eyebrow">Editing status</p>' +
                            (editingJob ? '<h3>' + escapeHtml(editingJob.title) + '</h3><p>' + escapeHtml(editingJob.company) + ' · ' + escapeHtml(editingJob.location) + '</p><div class="tag-row"><span class="micro-pill ' + (jobStatus(editingJob) === 'active' ? 'is-active' : 'is-inactive') + '">' + escapeHtml(capitalize(jobStatus(editingJob))) + '</span><span>' + escapeHtml(editingJob.type) + '</span><span>' + escapeHtml(editingJob.experience) + '</span></div>' : '<h3>Pick a live role to edit</h3><p>Select any job from the management list below and the form will load that role for editing.</p>') +
                        '</div>' +
                    '</aside>' +
                '</div>' +
            '</section>' +
            '<section class="section section-tight" id="manage-jobs"><div class="container"><div class="dashboard-card manage-jobs-panel"><div class="section-heading section-heading-split"><div><p class="eyebrow">Manage live roles</p><h2>Edit current job postings</h2></div><div class="hero-chip-row"><span class="hero-chip">' + escapeHtml(String(getActiveJobs().length)) + ' active</span><span class="hero-chip">' + escapeHtml(String(allJobs.length - getActiveJobs().length)) + ' inactive</span></div></div><div class="manage-jobs-grid">' +
                allJobs.map(renderManageJobCard).join('') +
            '</div></div></div></section>';
    }

    function renderDashboardView() {
        const container = document.getElementById('view-dashboard');
        const activeJobs = getActiveJobs();
        const recentJobs = activeJobs.slice(0, 5);
        const recentApplications = sortApplications(state.applications.slice()).slice(0, 5);
        const categoryBreakdown = breakdown(activeJobs, 'category');
        const typeBreakdown = breakdown(activeJobs, 'type');
        const applications = sortApplications(state.applications.slice());

        container.innerHTML = '' +
            '<section class="hero hero-compact">' +
                '<div class="container hero-grid">' +
                    '<div class="hero-copy"><p class="eyebrow">Hiring overview</p><div class="hero-chip-row"><span class="hero-chip">Portal analytics</span><span class="hero-chip">Recent applicant feed</span><span class="hero-chip">Category heatmap</span></div><h1>Track portal activity at a glance.</h1><p class="hero-text">This HTML dashboard reads from the same browser storage as the job and application forms, giving you a lightweight control room without a backend.</p></div>' +
                    '<div class="hero-panel"><div class="hero-panel-top"><div><p class="panel-kicker">Hiring pulse</p><h3>' + escapeHtml(topBreakdownLabel(activeJobs, 'category')) + '</h3></div><span class="panel-badge">' + escapeHtml(averageApplicationsPerRole()) + ' avg</span></div><p>The busiest category in the current data, with applications syncing directly from the candidate form.</p><div class="radar-cluster">' + typeBreakdown.slice(0, 5).map(function (item) {
                        return '<span class="radar-chip">' + escapeHtml(item.label) + ' · ' + escapeHtml(String(item.count)) + '</span>';
                    }).join('') + '</div></div>' +
                '</div>' +
            '</section>' +
            '<section class="section"><div class="container metrics-grid">' +
                renderMetricCard(activeJobs.length, 'Active roles', 'Live jobs currently available on the portal.') +
                renderMetricCard(applications.length, 'Applications', 'Candidate submissions stored in the browser.') +
                renderMetricCard(uniqueValues(activeJobs, 'company').length, 'Hiring companies', 'Unique employers represented in the feed.') +
                renderMetricCard(averageApplicationsPerRole(), 'Apps per role', 'Average application load across open positions.') +
            '</div></section>' +
            '<section class="section"><div class="container dashboard-grid">' +
                '<div class="dashboard-card"><div class="section-heading"><p class="eyebrow">Category mix</p><h2>Where the demand is strongest</h2></div>' + categoryBreakdown.map(function (item) {
                    const max = categoryBreakdown[0] ? categoryBreakdown[0].count : 1;
                    const width = Math.max(12, Math.round((item.count / max) * 100));
                    return '<div class="bar-row"><div class="bar-copy"><span>' + escapeHtml(item.label) + '</span><strong>' + escapeHtml(String(item.count)) + ' roles</strong></div><div class="bar-track"><span style="width:' + width + '%"></span></div></div>';
                }).join('') + '</div>' +
                '<div class="dashboard-card"><div class="section-heading"><p class="eyebrow">Work mode</p><h2>Role format distribution</h2></div><div class="type-stack">' + typeBreakdown.map(function (item) {
                    return '<article class="type-card"><strong>' + escapeHtml(String(item.count)) + '</strong><span>' + escapeHtml(item.label) + '</span></article>';
                }).join('') + '</div><div class="mini-insight"><p><strong>' + escapeHtml(String(activeJobs.filter(function (job) { return String(job.location).toLowerCase() === 'remote' || String(job.type).toLowerCase() === 'remote'; }).length)) + '</strong> remote-friendly roles are currently visible.</p><p><strong>' + escapeHtml(String(activeJobs.filter(function (job) { return !!job.featured; }).length)) + '</strong> roles are highlighted as featured on the homepage.</p></div></div>' +
            '</div></section>' +
            '<section class="section"><div class="container dashboard-grid">' +
                '<div class="dashboard-card"><div class="section-heading"><p class="eyebrow">Recent jobs</p><h2>Latest published roles</h2></div><div class="list-stack">' + recentJobs.map(function (job) {
                    return '<article class="list-card"><div><h3>' + escapeHtml(job.title) + '</h3><p>' + escapeHtml(job.company) + ' · ' + escapeHtml(job.location) + '</p></div><span>' + escapeHtml(humanTimeDiff(job.created_at)) + '</span></article>';
                }).join('') + '</div></div>' +
                '<div class="dashboard-card"><div class="section-heading"><p class="eyebrow">Recent applications</p><h2>Latest candidate activity</h2></div><div class="list-stack">' + recentApplications.map(function (application) {
                    return '<article class="list-card application-card"><div><h3>' + escapeHtml(application.candidate_name) + '</h3><p>' + escapeHtml(application.job_title) + ' · ' + escapeHtml(application.company) + '</p></div><span class="status-pill ' + statusClass(application.status) + '">' + escapeHtml(application.status) + '</span></article>';
                }).join('') + '</div></div>' +
            '</div></section>' +
            '<section class="section"><div class="container"><div class="dashboard-card"><div class="section-heading section-heading-split"><div><p class="eyebrow">All candidate data</p><h2>Every application submitted on the portal</h2></div><div class="dashboard-actions"><span class="panel-badge">' + escapeHtml(String(applications.length)) + ' records</span><button class="button button-primary button-sm" type="button" data-export-applications>Download Excel</button></div></div>' +
                (applications.length === 0 ? '<div class="empty-card"><h3>No applications yet.</h3><p>As soon as candidates start applying, all their submitted data will appear here on the dashboard.</p></div>' : renderApplicationsTable(applications)) +
            '</div></div></section>';
    }

    function updateViewVisibility() {
        document.querySelectorAll('.app-view').forEach(function (view) {
            view.hidden = view.id !== 'view-' + state.currentView;
        });
    }

    function updateEmployerCtas() {
        const loggedIn = isEmployerLoggedIn();
        const resultsEmployerButton = document.getElementById('results-employer-button');
        const downloadButton = document.getElementById('download-cta-button');
        const primaryCta = document.getElementById('employer-cta-primary');
        const secondaryCta = document.getElementById('employer-cta-secondary');

        resultsEmployerButton.textContent = loggedIn ? 'View dashboard' : 'Employer login';
        downloadButton.textContent = loggedIn ? 'Track activity' : 'Employer login';
        primaryCta.textContent = loggedIn ? 'Post job' : 'Employer login';
        secondaryCta.textContent = loggedIn ? 'See insights' : 'Explore jobs';
    }

    function submitEmployerLogin(form) {
        const formData = new FormData(form);
        const email = clean(formData.get('email')).toLowerCase();
        const password = clean(formData.get('password'));
        const errors = [];

        if (!/\S+@\S+\.\S+/.test(email)) {
            errors.push('Enter a valid employer email address.');
        }
        if (password === '') {
            errors.push('Password is required.');
        }
        if (errors.length === 0 && (email !== EMPLOYER_ACCOUNT.email || password !== EMPLOYER_ACCOUNT.password)) {
            errors.push('Incorrect employer email or password.');
        }

        state.loginErrors = errors;

        if (errors.length > 0) {
            renderEmployerLoginView();
            return;
        }

        state.employer = {
            name: EMPLOYER_ACCOUNT.name,
            email: EMPLOYER_ACCOUNT.email
        };
        writeJson(STORAGE_KEYS.employer, state.employer);
        state.loginErrors = [];
        showFlash('success', 'Employer login successful.');
        state.currentView = state.pendingView || 'post-job';
        renderApp();
    }

    function submitEmployerJob(form) {
        const formData = new FormData(form);
        const payload = {
            title: clean(formData.get('title')),
            company: clean(formData.get('company')),
            location: clean(formData.get('location')),
            salary: clean(formData.get('salary')),
            type: clean(formData.get('type')),
            category: clean(formData.get('category')),
            experience: clean(formData.get('experience')),
            description: clean(formData.get('description')),
            requirements: clean(formData.get('requirements')),
            skills: clean(formData.get('skills'))
        };
        const errors = validateJobPayload(payload);

        state.jobErrors = errors;
        state.jobDraft = payload;

        if (errors.length > 0) {
            renderPostJobView();
            return;
        }

        if (state.editingJobId !== '') {
            state.jobs = state.jobs.map(function (job) {
                if (job.id !== state.editingJobId) {
                    return job;
                }
                return normalizeJob({
                    id: job.id,
                    title: payload.title,
                    company: payload.company,
                    location: payload.location,
                    salary: payload.salary,
                    type: payload.type,
                    category: payload.category,
                    experience: payload.experience,
                    description: payload.description,
                    requirements: parseList(payload.requirements),
                    skills: parseList(payload.skills),
                    status: job.status || 'active',
                    featured: !!job.featured,
                    created_at: job.created_at,
                    updated_at: new Date().toISOString()
                });
            });
            showFlash('success', 'The role has been updated successfully.');
        } else {
            state.jobs.unshift(normalizeJob({
                id: 'job_html_' + Date.now().toString(36),
                title: payload.title,
                company: payload.company,
                location: payload.location,
                salary: payload.salary,
                type: payload.type,
                category: payload.category,
                experience: payload.experience,
                description: payload.description,
                requirements: parseList(payload.requirements),
                skills: parseList(payload.skills),
                status: 'active',
                featured: false,
                created_at: new Date().toISOString()
            }));
            showFlash('success', 'The role is now live on your portal.');
        }

        saveJobs();
        clearEditingState(false);
        state.selectedJobId = getActiveJobs()[0] ? getActiveJobs()[0].id : '';
        renderApp();
    }

    function submitApplication(form) {
        const formData = new FormData(form);
        const payload = {
            name: clean(formData.get('name')),
            phone: clean(formData.get('phone')),
            city: clean(formData.get('city'))
        };
        const job = findJobById(state.modalJobId);
        const errors = [];

        if (!job || jobStatus(job) !== 'active') {
            errors.push('Select a valid active job before applying.');
        }
        if (payload.name === '') {
            errors.push('Your name is required.');
        }
        if (payload.phone.replace(/\D+/g, '').length < 10) {
            errors.push('Enter a valid phone number.');
        }
        if (payload.city === '') {
            errors.push('City is required.');
        }

        state.applyErrors = errors;

        if (errors.length > 0) {
            renderApplyModal();
            return;
        }

        state.applications.unshift(normalizeApplication({
            id: 'app_html_' + Date.now().toString(36),
            job_id: job.id,
            job_title: job.title,
            company: job.company,
            candidate_name: payload.name,
            email: '',
            phone: payload.phone,
            city: payload.city,
            experience: '',
            summary: '',
            status: 'New',
            applied_at: new Date().toISOString()
        }));

        saveApplications();
        state.applyErrors = [];
        form.reset();
        closeApplyModal();
        showFlash('success', 'Application submitted successfully.');
        renderApp();
    }

    function openApplyModal(jobId) {
        const job = findJobById(jobId);
        if (!job || jobStatus(job) !== 'active') {
            showFlash('error', 'The selected role is not available for applications.');
            renderFlash();
            return;
        }

        state.modalJobId = jobId;
        state.applyErrors = [];
        renderApplyModal();
        document.getElementById('apply-modal').hidden = false;
        document.body.classList.add('has-modal-open');
    }

    function closeApplyModal() {
        state.modalJobId = '';
        state.applyErrors = [];
        document.getElementById('apply-modal').hidden = true;
        document.body.classList.remove('has-modal-open');
    }

    function renderApplyModal() {
        const job = findJobById(state.modalJobId);
        const modal = document.getElementById('apply-modal');
        const jobPanel = document.getElementById('apply-modal-job');
        const selectedRole = document.getElementById('apply-selected-role');
        const errorBox = document.getElementById('apply-form-errors');

        if (!job) {
            modal.hidden = true;
            return;
        }

        jobPanel.innerHTML = '' +
            '<div class="detail-header-block">' +
                '<span class="company-badge is-large">' + escapeHtml(companyMonogram(job.company)) + '</span>' +
                '<div><h3>' + escapeHtml(job.title) + '</h3><p class="job-company">' + escapeHtml(job.company) + ' · ' + escapeHtml(job.location) + '</p></div>' +
            '</div>' +
            '<div class="tag-row"><span>' + escapeHtml(job.type) + '</span><span>' + escapeHtml(job.experience) + '</span><span>' + escapeHtml(job.salary) + '</span></div>';

        selectedRole.innerHTML = '<span class="signal-label">Applying for</span><strong>' + escapeHtml(job.title) + '</strong><span>' + escapeHtml(job.company) + ' · ' + escapeHtml(job.location) + '</span>';

        if (state.applyErrors.length > 0) {
            errorBox.hidden = false;
            errorBox.className = 'form-errors';
            errorBox.innerHTML = '<p>Please fix the following:</p><ul>' + state.applyErrors.map(function (error) {
                return '<li>' + escapeHtml(error) + '</li>';
            }).join('') + '</ul>';
        } else {
            errorBox.hidden = true;
            errorBox.innerHTML = '';
        }
    }

    function renderManageJobCard(job) {
        const isEditing = state.editingJobId === job.id;
        const active = jobStatus(job) === 'active';
        return '' +
            '<article class="manage-job-card' + (isEditing ? ' is-editing' : '') + '">' +
                '<div class="manage-job-head">' +
                    '<div><p class="eyebrow">' + escapeHtml(job.category) + '</p><h3>' + escapeHtml(job.title) + '</h3><p class="job-company">' + escapeHtml(job.company) + ' · ' + escapeHtml(job.location) + '</p></div>' +
                    '<div class="manage-job-badges"><span class="micro-pill ' + (active ? 'is-active' : 'is-inactive') + '">' + escapeHtml(active ? 'Active' : 'Inactive') + '</span><span class="micro-pill ' + (job.featured ? 'is-featured' : '') + '">' + escapeHtml(job.featured ? 'Featured' : humanTimeDiff(job.created_at)) + '</span></div>' +
                '</div>' +
                '<p class="job-salary">' + escapeHtml(job.salary) + '</p>' +
                '<div class="tag-row"><span>' + escapeHtml(job.type) + '</span><span>' + escapeHtml(job.experience) + '</span></div>' +
                '<div class="manage-job-actions">' +
                    '<button class="button button-primary button-sm" type="button" data-edit-job="' + escapeHtml(job.id) + '">Edit role</button>' +
                    '<button class="button button-ghost button-sm" type="button" data-toggle-job="' + escapeHtml(job.id) + '">' + escapeHtml(active ? 'Deactivate' : 'Activate') + '</button>' +
                    '<button class="button button-danger button-sm" type="button" data-delete-job="' + escapeHtml(job.id) + '">Delete</button>' +
                    (active ? '<button class="button button-ghost button-sm" type="button" data-view-live-job="' + escapeHtml(job.id) + '">View live</button>' : '<span class="button button-muted button-sm">Hidden from portal</span>') +
                '</div>' +
            '</article>';
    }

    function renderApplicationsTable(applications) {
        return '<div class="applications-table-wrap"><table class="applications-table"><thead><tr><th>Candidate</th><th>Contact</th><th>City</th><th>Applied for</th><th>Status</th><th>Applied on</th></tr></thead><tbody>' +
            applications.map(function (application) {
                return '<tr>' +
                    '<td><div class="table-primary">' + escapeHtml(application.candidate_name) + '</div>' + (application.experience ? '<div class="table-secondary">' + escapeHtml(application.experience) + '</div>' : '') + '</td>' +
                    '<td><div class="table-primary">' + escapeHtml(application.phone) + '</div><div class="table-secondary">' + escapeHtml(application.email || 'Email not collected') + '</div></td>' +
                    '<td><div class="table-primary">' + escapeHtml(application.city || '-') + '</div></td>' +
                    '<td><div class="table-primary">' + escapeHtml(application.job_title) + '</div><div class="table-secondary">' + escapeHtml(application.company) + '</div>' + (application.summary ? '<div class="table-note">' + escapeHtml(application.summary) + '</div>' : '') + '</td>' +
                    '<td><span class="status-pill ' + statusClass(application.status) + '">' + escapeHtml(application.status) + '</span></td>' +
                    '<td><div class="table-primary">' + escapeHtml(formatPortalDate(application.applied_at)) + '</div><div class="table-secondary">' + escapeHtml(humanTimeDiff(application.applied_at)) + '</div></td>' +
                '</tr>';
            }).join('') + '</tbody></table></div>';
    }

    function renderMetricCard(value, label, copy) {
        return '<article class="metric-card"><p class="metric-value">' + escapeHtml(String(value)) + '</p><h3>' + escapeHtml(label) + '</h3><p>' + escapeHtml(copy) + '</p></article>';
    }

    function renderErrorBox(errors) {
        if (!errors || errors.length === 0) {
            return '';
        }

        return '<div class="form-errors"><p>Please fix the following:</p><ul>' + errors.map(function (error) {
            return '<li>' + escapeHtml(error) + '</li>';
        }).join('') + '</ul></div>';
    }

    function renderInput(label, name, currentValue, placeholder, fullSpan) {
        return '<label' + (fullSpan ? ' class="full-span"' : '') + '>' + escapeHtml(label) + '<input type="text" name="' + escapeHtml(name) + '" value="' + escapeHtml(currentValue || '') + '" placeholder="' + escapeHtml(placeholder) + '"></label>';
    }

    function renderTextarea(label, name, currentValue, placeholder, rows, fullSpan) {
        return '<label' + (fullSpan ? ' class="full-span"' : '') + '>' + escapeHtml(label) + '<textarea name="' + escapeHtml(name) + '" rows="' + escapeHtml(String(rows)) + '" placeholder="' + escapeHtml(placeholder) + '">' + escapeHtml(currentValue || '') + '</textarea></label>';
    }

    function renderSelect(label, name, options, currentValue) {
        return '<label>' + escapeHtml(label) + '<select name="' + escapeHtml(name) + '"><option value="">Select type</option>' + options.map(function (option) {
            return '<option value="' + escapeHtml(option) + '"' + (option === currentValue ? ' selected' : '') + '>' + escapeHtml(option) + '</option>';
        }).join('') + '</select></label>';
    }

    function formDataFromJob(job) {
        if (!job) {
            return {
                title: '',
                company: '',
                location: '',
                salary: '',
                type: '',
                category: '',
                experience: '',
                description: '',
                requirements: '',
                skills: ''
            };
        }

        return {
            title: job.title || '',
            company: job.company || '',
            location: job.location || '',
            salary: job.salary || '',
            type: job.type || '',
            category: job.category || '',
            experience: job.experience || '',
            description: job.description || '',
            requirements: (job.requirements || []).join('\n'),
            skills: (job.skills || []).join(', ')
        };
    }

    function validateJobPayload(payload) {
        const errors = [];

        if (payload.title === '') {
            errors.push('Job title is required.');
        }
        if (payload.company === '') {
            errors.push('Company name is required.');
        }
        if (payload.location === '') {
            errors.push('Location is required.');
        }
        if (payload.salary === '') {
            errors.push('Salary range is required.');
        }
        if (payload.type === '') {
            errors.push('Select a job type.');
        }
        if (payload.category === '') {
            errors.push('Category is required.');
        }
        if (payload.experience === '') {
            errors.push('Experience band is required.');
        }
        if (payload.description.length < 40) {
            errors.push('Description should be at least 40 characters.');
        }
        if (parseList(payload.requirements).length === 0) {
            errors.push('Add at least one requirement.');
        }

        return errors;
    }

    function toggleJobStatus(jobId) {
        state.jobs = state.jobs.map(function (job) {
            if (job.id !== jobId) {
                return job;
            }

            return normalizeJob({
                id: job.id,
                title: job.title,
                company: job.company,
                location: job.location,
                salary: job.salary,
                type: job.type,
                category: job.category,
                experience: job.experience,
                description: job.description,
                requirements: job.requirements,
                skills: job.skills,
                status: jobStatus(job) === 'active' ? 'inactive' : 'active',
                featured: !!job.featured,
                created_at: job.created_at,
                updated_at: new Date().toISOString()
            });
        });

        saveJobs();
        state.selectedJobId = getActiveJobs()[0] ? getActiveJobs()[0].id : '';
        showFlash('success', 'The role status has been updated.');
        renderApp();
    }

    function removeJob(jobId) {
        state.jobs = state.jobs.filter(function (job) {
            return job.id !== jobId;
        });

        if (state.editingJobId === jobId) {
            clearEditingState(false);
        }

        saveJobs();
        state.selectedJobId = getActiveJobs()[0] ? getActiveJobs()[0].id : '';
        showFlash('success', 'The role has been deleted from the employer panel.');
        renderApp();
    }

    function clearEditingState(shouldRender) {
        state.editingJobId = '';
        state.jobDraft = null;
        state.jobErrors = [];

        if (shouldRender !== false) {
            renderApp();
        }
    }

    function exportApplicationsToExcel() {
        if (!isEmployerLoggedIn()) {
            openProtectedView('dashboard');
            return;
        }

        const rows = sortApplications(state.applications.slice()).map(function (application) {
            return '<tr>' +
                '<td>' + escapeHtml(application.candidate_name) + '</td>' +
                '<td>' + escapeHtml(application.phone) + '</td>' +
                '<td>' + escapeHtml(application.email || 'Email not collected') + '</td>' +
                '<td>' + escapeHtml(application.city) + '</td>' +
                '<td>' + escapeHtml(application.job_title) + '</td>' +
                '<td>' + escapeHtml(application.company) + '</td>' +
                '<td>' + escapeHtml(application.status) + '</td>' +
                '<td>' + escapeHtml(application.experience) + '</td>' +
                '<td>' + escapeHtml(application.summary) + '</td>' +
                '<td>' + escapeHtml(formatPortalDate(application.applied_at)) + '</td>' +
                '<td>' + escapeHtml(application.job_id) + '</td>' +
            '</tr>';
        }).join('');

        const content = '<html><head><meta charset="UTF-8"><title>NBSS HR Services Applications Export</title></head><body><table border="1"><thead><tr><th>Candidate Name</th><th>Phone</th><th>Email</th><th>City</th><th>Job Title</th><th>Company</th><th>Status</th><th>Experience</th><th>Summary</th><th>Applied At</th><th>Job ID</th></tr></thead><tbody>' + rows + '</tbody></table></body></html>';
        const blob = new Blob(['\ufeff', content], { type: 'application/vnd.ms-excel;charset=utf-8;' });
        const url = URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = url;
        link.download = 'nbss-hr-services-applications.xls';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        URL.revokeObjectURL(url);
    }

    function logoutEmployer() {
        localStorage.removeItem(STORAGE_KEYS.employer);
        state.employer = null;
        state.currentView = 'home';
        showFlash('success', 'Employer session signed out successfully.');
        renderApp();
    }

    function updateDocumentTitle() {
        const titles = {
            home: 'Jobs in India | NBSS HR Services Job Search Portal',
            'employer-login': 'Employer Login | NBSS HR Services',
            'post-job': 'Post a Job | NBSS HR Services',
            dashboard: 'Hiring Dashboard | NBSS HR Services'
        };

        document.title = titles[state.currentView] || titles.home;
    }

    function getActiveJobs() {
        return sortJobs(state.jobs.filter(function (job) {
            return jobStatus(job) === 'active';
        }));
    }

    function pickSelectedJob(filteredJobs, activeJobs) {
        const selectedFromFiltered = filteredJobs.find(function (job) {
            return job.id === state.selectedJobId;
        });
        const selected = selectedFromFiltered || filteredJobs[0] || activeJobs[0] || null;

        state.selectedJobId = selected ? selected.id : '';
        return selected;
    }

    function filterJobs(jobs, filters) {
        return jobs.filter(function (job) {
            const haystack = [job.title, job.company, job.location, job.description, job.category].join(' ').toLowerCase();
            if (filters.keyword && haystack.indexOf(filters.keyword.toLowerCase()) === -1) {
                return false;
            }
            if (filters.location && String(job.location).toLowerCase() !== filters.location.toLowerCase()) {
                return false;
            }
            if (filters.category && String(job.category).toLowerCase() !== filters.category.toLowerCase()) {
                return false;
            }
            if (filters.type && String(job.type).toLowerCase() !== filters.type.toLowerCase()) {
                return false;
            }
            return true;
        });
    }

    function normalizeJob(job) {
        return {
            id: String(job.id || ''),
            title: clean(job.title),
            company: clean(job.company),
            location: clean(job.location),
            salary: clean(job.salary),
            type: clean(job.type),
            category: clean(job.category),
            experience: clean(job.experience),
            description: clean(job.description),
            requirements: Array.isArray(job.requirements) ? job.requirements.map(clean).filter(Boolean) : parseList(clean(job.requirements)),
            skills: Array.isArray(job.skills) ? job.skills.map(clean).filter(Boolean) : parseList(clean(job.skills)),
            status: clean(job.status).toLowerCase() === 'inactive' ? 'inactive' : 'active',
            featured: !!job.featured,
            created_at: clean(job.created_at) || new Date().toISOString(),
            updated_at: clean(job.updated_at)
        };
    }

    function normalizeApplication(application) {
        return {
            id: clean(application.id) || ('app_' + Date.now().toString(36)),
            job_id: clean(application.job_id),
            job_title: clean(application.job_title),
            company: clean(application.company),
            candidate_name: clean(application.candidate_name),
            email: clean(application.email),
            phone: clean(application.phone),
            city: clean(application.city),
            experience: clean(application.experience),
            summary: clean(application.summary),
            status: clean(application.status) || 'New',
            applied_at: clean(application.applied_at) || new Date().toISOString()
        };
    }

    function ensureJobArray(input) {
        return Array.isArray(input) ? input.map(normalizeJob) : [];
    }

    function ensureApplicationArray(input) {
        return Array.isArray(input) ? input.map(normalizeApplication) : [];
    }

    function findJobById(jobId) {
        return state.jobs.find(function (job) {
            return job.id === jobId;
        }) || null;
    }

    function saveJobs() {
        writeJson(STORAGE_KEYS.jobs, state.jobs);
    }

    function saveApplications() {
        writeJson(STORAGE_KEYS.applications, state.applications);
    }

    function isEmployerLoggedIn() {
        return !!(state.employer && state.employer.email);
    }

    function sortJobs(jobs) {
        return jobs.sort(function (left, right) {
            return new Date(right.created_at).getTime() - new Date(left.created_at).getTime();
        });
    }

    function sortApplications(applications) {
        return applications.sort(function (left, right) {
            return new Date(right.applied_at).getTime() - new Date(left.applied_at).getTime();
        });
    }

    function breakdown(records, field) {
        const counts = {};
        records.forEach(function (record) {
            const label = clean(record[field]);
            if (!label) {
                return;
            }
            counts[label] = (counts[label] || 0) + 1;
        });
        return Object.keys(counts).sort(function (left, right) {
            return counts[right] - counts[left];
        }).map(function (label) {
            return { label: label, count: counts[label] };
        });
    }

    function uniqueValues(records, field) {
        return Array.from(new Set(records.map(function (record) {
            return clean(record[field]);
        }).filter(Boolean))).sort();
    }

    function topBreakdownLabel(records, field) {
        const items = breakdown(records, field);
        return items[0] ? items[0].label : 'None yet';
    }

    function averageApplicationsPerRole() {
        const activeJobs = getActiveJobs();
        if (activeJobs.length === 0) {
            return '0.0';
        }
        return (state.applications.length / activeJobs.length).toFixed(1);
    }

    function jobStatus(job) {
        return String(job.status || 'active').toLowerCase() === 'inactive' ? 'inactive' : 'active';
    }

    function humanTimeDiff(value) {
        const timestamp = new Date(value).getTime();
        if (!timestamp) {
            return 'Recently';
        }
        const diff = Math.max(0, Date.now() - timestamp);
        if (diff < 3600000) {
            return Math.max(1, Math.floor(diff / 60000)) + 'm ago';
        }
        if (diff < 86400000) {
            return Math.floor(diff / 3600000) + 'h ago';
        }
        if (diff < 604800000) {
            return Math.floor(diff / 86400000) + 'd ago';
        }
        return new Date(timestamp).toLocaleDateString('en-IN', { day: '2-digit', month: 'short', year: 'numeric' });
    }

    function formatPortalDate(value) {
        const date = new Date(value);
        if (Number.isNaN(date.getTime())) {
            return 'Recently';
        }
        return date.toLocaleString('en-IN', {
            day: '2-digit',
            month: 'short',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
            hour12: true
        });
    }

    function companyMonogram(name) {
        const parts = clean(name).split(/\s+/).filter(Boolean);
        if (parts.length === 0) {
            return 'NB';
        }
        if (parts.length === 1) {
            return parts[0].slice(0, 2).toUpperCase();
        }
        return (parts[0][0] + parts[1][0]).toUpperCase();
    }

    function statusClass(status) {
        const normalized = clean(status).toLowerCase();
        if (normalized === 'shortlisted') {
            return 'is-shortlisted';
        }
        if (normalized === 'reviewing') {
            return 'is-reviewing';
        }
        return 'is-new';
    }

    function parseList(value) {
        return String(value || '').split(/\r\n|\r|\n|,/).map(clean).filter(Boolean);
    }

    function showFlash(type, message) {
        state.flash = {
            type: type,
            message: message
        };
    }

    function clearFlash() {
        state.flash = null;
    }

    function setOptions(id, items) {
        const select = document.getElementById(id);
        if (!select) {
            return;
        }

        const firstOption = select.querySelector('option');
        const defaultLabel = firstOption ? firstOption.textContent : 'Select';
        const defaultValue = firstOption ? firstOption.value : '';
        select.innerHTML = '<option value="' + escapeHtml(defaultValue) + '">' + escapeHtml(defaultLabel) + '</option>' +
            items.map(function (item) {
                return '<option value="' + escapeHtml(item) + '">' + escapeHtml(item) + '</option>';
            }).join('');
    }

    function text(id, content) {
        const node = document.getElementById(id);
        if (node) {
            node.textContent = content;
        }
    }

    function value(id, content) {
        const node = document.getElementById(id);
        if (node) {
            node.value = content;
        }
    }

    function readJson(key, fallback) {
        try {
            const raw = localStorage.getItem(key);
            return raw ? JSON.parse(raw) : fallback;
        } catch (error) {
            return fallback;
        }
    }

    function writeJson(key, value) {
        localStorage.setItem(key, JSON.stringify(value));
    }

    async function fetchJson(path) {
        try {
            const response = await fetch(path);
            if (!response.ok) {
                return [];
            }
            const payload = await response.json();
            return Array.isArray(payload) ? payload : [];
        } catch (error) {
            return [];
        }
    }

    function scrollToSection(hash) {
        const id = hash.charAt(0) === '#' ? hash.slice(1) : hash;
        const target = document.getElementById(id);
        if (target) {
            target.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    }

    function capitalize(value) {
        const text = clean(value);
        return text ? text.charAt(0).toUpperCase() + text.slice(1) : '';
    }

    function clean(value) {
        return String(value || '').trim();
    }

    function escapeHtml(value) {
        return String(value || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }
}());
