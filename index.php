<?php
declare(strict_types=1);

session_start();
date_default_timezone_set('Asia/Kolkata');

require_once __DIR__ . '/includes/data.php';
require_once __DIR__ . '/includes/layout.php';

initialize_storage();
$csrfToken = ensure_csrf_token();

$allowedPages = ['home', 'post-job', 'dashboard', 'employer-login'];
$employerRestrictedPages = ['post-job', 'dashboard'];
$page = in_array((string) ($_GET['page'] ?? 'home'), $allowedPages, true) ? (string) ($_GET['page'] ?? 'home') : 'home';

$jobFormData = job_form_defaults();
$applicationFormData = application_form_defaults();
$employerLoginData = employer_login_defaults();
$jobErrors = [];
$applicationErrors = [];
$employerLoginErrors = [];
$editingJobId = trim((string) ($_GET['edit'] ?? ''));
$employerRedirectPage = normalize_employer_redirect_page((string) ($_POST['redirect'] ?? $_GET['redirect'] ?? 'post-job'));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = trim((string) ($_POST['action'] ?? ''));
    $token = trim((string) ($_POST['csrf_token'] ?? ''));
    $employerActionTargets = [
        'post_job' => 'post-job',
        'update_job' => 'post-job',
        'set_job_status' => 'post-job',
        'delete_job' => 'post-job'
    ];
    $csrfRedirect = 'index.php';

    if ($action === 'employer_login') {
        $csrfRedirect = employer_login_url($employerRedirectPage);
    } elseif (isset($employerActionTargets[$action])) {
        $csrfRedirect = employer_login_url((string) $employerActionTargets[$action]);
    }

    if (!is_valid_csrf($token)) {
        flash('error', 'Session expired. Please refresh the page and try again.');
        header('Location: ' . $csrfRedirect);
        exit;
    }

    if ($action === 'logout_employer') {
        logout_employer_session();
        flash('success', 'Employer session signed out successfully.');
        header('Location: index.php');
        exit;
    }

    if ($action === 'employer_login') {
        $page = 'employer-login';
        $employerLoginData = collect_employer_login_data($_POST);
        $employerLoginErrors = validate_employer_login($employerLoginData);

        if ($employerLoginErrors === [] && !authenticate_employer($employerLoginData)) {
            $employerLoginErrors[] = 'Incorrect employer email or password.';
        }

        if ($employerLoginErrors === []) {
            login_employer_session();
            flash('success', 'Employer login successful.');
            header('Location: ' . build_url(['page' => $employerRedirectPage]));
            exit;
        }
    }

    if (isset($employerActionTargets[$action]) && !employer_logged_in()) {
        flash('error', 'Employer login required to continue.');
        header('Location: ' . employer_login_url((string) $employerActionTargets[$action]));
        exit;
    }

    if ($action === 'post_job' || $action === 'update_job') {
        $page = 'post-job';
        $jobFormData = collect_job_form_data($_POST);
        $editingJobId = $jobFormData['job_id'];
        $jobErrors = validate_job_form($jobFormData);

        if ($jobErrors === []) {
            $isUpdatingJob = $action === 'update_job' && $jobFormData['job_id'] !== '';
            $saved = $isUpdatingJob ? update_job($jobFormData) : save_job($jobFormData);

            if ($saved) {
                flash('success', $isUpdatingJob ? 'The role has been updated successfully.' : 'The role is now live on your portal.');
                header('Location: ' . build_url($isUpdatingJob ? ['page' => 'post-job', 'edit' => $jobFormData['job_id']] : ['page' => 'post-job'], '#employer-form'));
                exit;
            }

            $jobErrors[] = $isUpdatingJob
                ? 'The role could not be updated. Please verify the job still exists and the data folder is writable.'
                : 'The job could not be saved. Please verify write permissions for the data folder.';
        }
    }

    if ($action === 'set_job_status') {
        $page = 'post-job';
        $statusJobId = trim((string) ($_POST['job_id'] ?? ''));
        $nextStatus = trim((string) ($_POST['next_status'] ?? ''));
        $returnEditJobId = trim((string) ($_POST['return_edit_job'] ?? ''));
        $redirectParams = ['page' => 'post-job'];

        if ($returnEditJobId !== '') {
            $redirectParams['edit'] = $returnEditJobId;
        }

        if (set_job_status($statusJobId, $nextStatus)) {
            flash('success', $nextStatus === 'active'
                ? 'The role is now active and visible on the public portal.'
                : 'The role has been deactivated and hidden from the public job feed.');
        } else {
            flash('error', 'The role status could not be updated.');
        }

        header('Location: ' . build_url($redirectParams, '#manage-jobs'));
        exit;
    }

    if ($action === 'delete_job') {
        $page = 'post-job';
        $deleteJobId = trim((string) ($_POST['job_id'] ?? ''));
        $returnEditJobId = trim((string) ($_POST['return_edit_job'] ?? ''));
        $redirectParams = ['page' => 'post-job'];

        if ($returnEditJobId !== '' && $returnEditJobId !== $deleteJobId) {
            $redirectParams['edit'] = $returnEditJobId;
        }

        if (delete_job($deleteJobId)) {
            flash('success', 'The role has been deleted from the employer panel and public portal.');
        } else {
            flash('error', 'The role could not be deleted.');
        }

        header('Location: ' . build_url($redirectParams, '#manage-jobs'));
        exit;
    }

    if ($action === 'apply_job') {
        $page = 'home';
        $applicationFormData = collect_application_form_data($_POST);
        $jobsForValidation = load_jobs();
        $applicationErrors = validate_application_form($applicationFormData, $jobsForValidation);

        if ($applicationErrors === []) {
            if (save_application($applicationFormData, $jobsForValidation)) {
                flash('success', 'Application submitted successfully.');
                header('Location: ' . build_url(['job' => $applicationFormData['job_id']], '#job-details'));
                exit;
            }

            $applicationErrors[] = 'The application could not be saved. Please try again.';
        }
    }
}

if ($page === 'employer-login' && employer_logged_in()) {
    header('Location: ' . build_url(['page' => $employerRedirectPage]));
    exit;
}

if (in_array($page, $employerRestrictedPages, true) && !employer_logged_in()) {
    flash('error', 'Please log in as an employer to access this page.');
    header('Location: ' . employer_login_url($page));
    exit;
}

$allJobs = load_jobs(true);
$jobs = filter_active_jobs($allJobs);
$applications = load_applications();
$employerLoggedIn = employer_logged_in();
$postJobUrl = build_url(['page' => 'post-job']);
$dashboardUrl = build_url(['page' => 'dashboard']);
$employerPostJobUrl = $employerLoggedIn ? $postJobUrl : employer_login_url('post-job');
$employerDashboardUrl = $employerLoggedIn ? $dashboardUrl : employer_login_url('dashboard');
$showDefaultEmployerCredentials = employer_uses_default_credentials();
$employerCredentials = employer_credentials();

if ($page === 'dashboard' && (string) ($_GET['export'] ?? '') === 'applications-xls') {
    output_applications_excel($applications);
    exit;
}

$filters = normalize_filters($_GET);
$filteredJobs = $page === 'home' ? filter_jobs($jobs, $filters) : $jobs;
$featuredJobs = featured_jobs($jobs, 3);
$dashboard = build_dashboard($jobs, $applications);
$requestedJobId = trim((string) ($_GET['job'] ?? ''));
$selectedJobId = trim((string) ($_GET['job'] ?? $applicationFormData['job_id']));

if ($selectedJobId === '' && $filteredJobs !== []) {
    $selectedJobId = (string) $filteredJobs[0]['id'];
}

$selectedJob = find_job_by_id($jobs, $selectedJobId);

if ($selectedJob === null && $jobs !== []) {
    $selectedJob = $jobs[0];
    $selectedJobId = (string) $selectedJob['id'];
}

if ($applicationFormData['job_id'] === '' && $selectedJob !== null) {
    $applicationFormData['job_id'] = (string) $selectedJob['id'];
}

$openApplyRequest = $page === 'home' && (string) ($_GET['apply'] ?? '') === '1';
$shouldOpenApplyModal = $page === 'home' && ($openApplyRequest || $applicationErrors !== []);
$modalJob = find_job_by_id($jobs, $applicationFormData['job_id']) ?? $selectedJob;
$modalJobId = $modalJob !== null ? (string) $modalJob['id'] : '';
$closeApplyModalUrl = build_url($modalJobId !== '' ? array_merge($filters, ['job' => $modalJobId]) : $filters, '#job-details');
$requestedJob = $requestedJobId !== '' ? find_job_by_id($jobs, $requestedJobId) : null;
$editingJob = null;

if ($page === 'post-job' && $editingJobId !== '') {
    $editingJob = find_job_by_id($allJobs, $editingJobId);

    if ($editingJob === null && $_SERVER['REQUEST_METHOD'] !== 'POST') {
        flash('error', 'The selected role could not be found for editing.');
        header('Location: ' . build_url(['page' => 'post-job']));
        exit;
    }

    if ($editingJob !== null && $_SERVER['REQUEST_METHOD'] !== 'POST') {
        $jobFormData = job_form_data_from_job($editingJob);
    }
}

$flash = pull_flash();
$locationOptions = unique_job_values($jobs, 'location');
$categoryOptions = unique_job_values($jobs, 'category');
$typeOptions = unique_job_values($jobs, 'type');
$topCategoryHighlights = array_slice($dashboard['categories'], 0, 6);
$topTypeHighlights = array_slice($dashboard['types'], 0, 5);
$recentTalentPulse = array_slice($applications, 0, 4);
$selectedJobApplications = $selectedJob !== null ? applications_for_job($applications, (string) $selectedJob['id']) : 0;
$cityHighlights = array_slice(value_breakdown($jobs, 'location'), 0, 8);
$companyHighlights = array_slice(value_breakdown($jobs, 'company'), 0, 8);
$trustedEmployers = array_slice(unique_job_values($jobs, 'company'), 0, 6);
$careerTools = [
    [
        'title' => 'Resume Builder',
        'copy' => 'Shape a stronger candidate profile before you start applying.',
        'tag' => 'Career tool'
    ],
    [
        'title' => 'Interview Prep',
        'copy' => 'Prepare answers for support, sales, delivery, and tech roles.',
        'tag' => 'Job prep'
    ],
    [
        'title' => 'Application Tracker',
        'copy' => 'Keep an eye on shortlisted, reviewing, and newly applied roles.',
        'tag' => 'Workflow'
    ]
];
$jobTypeChoices = [
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
$jobsByTypeLinks = [
    ['title' => 'Work From Home Jobs', 'params' => ['type' => 'Remote']],
    ['title' => 'Part Time Jobs', 'params' => ['type' => 'Part-time']],
    ['title' => 'Full Time Jobs', 'params' => ['type' => 'Full-time']],
    ['title' => 'Night Shift Jobs', 'params' => ['type' => 'Night Shift']],
    ['title' => 'Hybrid Jobs', 'params' => ['type' => 'Hybrid']],
    ['title' => 'Sales Jobs', 'params' => ['category' => 'Sales & BD']]
];
$popularSearches = [
    [
        'rank' => 'Trending at #1',
        'title' => 'Jobs for Freshers',
        'copy' => 'Back office, telecalling, and entry-level operations roles.',
        'url' => build_url(['category' => 'Telecalling / BPO / Telesales'], '#job-feed')
    ],
    [
        'rank' => 'Trending at #2',
        'title' => 'Work from home jobs',
        'copy' => 'Remote-friendly roles for support, content, and software teams.',
        'url' => build_url(['type' => 'Remote'], '#job-feed')
    ],
    [
        'rank' => 'Trending at #3',
        'title' => 'Part time jobs',
        'copy' => 'Flexible delivery and shift-based work across fast-moving teams.',
        'url' => build_url(['type' => 'Part-time'], '#job-feed')
    ],
    [
        'rank' => 'Trending at #4',
        'title' => 'Night shift jobs',
        'copy' => 'Customer care and operations roles with late-hour hiring needs.',
        'url' => build_url(['type' => 'Night Shift'], '#job-feed')
    ]
];
$featuredLead = $featuredJobs[0] ?? null;
$managedJobs = array_slice($allJobs, 0, 10);
$activeJobsCount = count(filter_active_jobs($allJobs));
$inactiveJobsCount = max(0, count($allJobs) - $activeJobsCount);
$isEditingJob = $page === 'post-job' && $editingJob !== null;
$seo = build_seo_metadata($page, [
    'requested_job' => $requestedJob,
    'modal_job' => $modalJob,
    'filtered_jobs' => $filteredJobs,
    'filters' => $filters,
    'dashboard' => $dashboard,
    'apply_requested' => $shouldOpenApplyModal
]);

render_header($page, $seo);
render_flash_banner($flash);
?>

<?php if ($page === 'post-job'): ?>
    <main>
        <section class="hero hero-compact">
            <div class="container hero-grid">
                <div class="hero-copy">
                    <p class="eyebrow">For employers</p>
                    <div class="hero-chip-row">
                        <span class="hero-chip">Instant publish</span>
                        <span class="hero-chip">Edit live roles</span>
                        <span class="hero-chip">No DB required</span>
                        <span class="hero-chip">Recruiter-ready base</span>
                    </div>
                    <h1>Publish and manage hiring roles from one page.</h1>
                    <p class="hero-text">HR teams can post fresh jobs, open an existing role for editing, and keep the live portal updated without a database dependency.</p>
                </div>
                    <div class="hero-panel stack-panel">
                        <div class="hero-panel-top">
                            <div>
                                <p class="panel-kicker">Employer workflow</p>
                                <h3>Post, edit, move fast.</h3>
                        </div>
                        <span class="panel-badge">Live sync</span>
                    </div>
                    <div class="panel-line">
                        <strong><?php echo h((string) $dashboard['jobs_count']); ?></strong>
                        <span>active roles already live</span>
                    </div>
                    <div class="panel-line">
                        <strong><?php echo h((string) $dashboard['applications_count']); ?></strong>
                        <span>applications tracked in JSON storage</span>
                    </div>
                    <div class="panel-line">
                        <strong><?php echo h((string) $dashboard['hottest_category']); ?></strong>
                        <span>currently the strongest hiring category</span>
                    </div>
                    <div class="radar-cluster">
                        <?php foreach ($topTypeHighlights as $item): ?>
                            <span class="radar-chip"><?php echo h((string) $item['label']); ?> · <?php echo h((string) $item['count']); ?></span>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </section>

        <section class="section">
            <div class="container split-layout">
                <div class="form-panel" id="employer-form">
                    <div class="section-heading">
                        <p class="eyebrow">Employer console</p>
                        <h2><?php echo $isEditingJob ? 'Edit live role' : 'Add a new role'; ?></h2>
                    </div>
                    <p class="form-note">
                        <?php echo $isEditingJob
                            ? 'Update the selected role and save changes to push them live on the portal.'
                            : 'Fill in the job details below and publish a new role instantly.'; ?>
                    </p>
                    <?php render_error_list($jobErrors); ?>
                    <form method="post" class="portal-form">
                        <input type="hidden" name="action" value="<?php echo $isEditingJob ? 'update_job' : 'post_job'; ?>">
                        <input type="hidden" name="csrf_token" value="<?php echo h($csrfToken); ?>">
                        <input type="hidden" name="job_id" value="<?php echo h($jobFormData['job_id']); ?>">

                        <div class="form-grid">
                            <label>
                                Job title
                                <input type="text" name="title" value="<?php echo h($jobFormData['title']); ?>" placeholder="Senior PHP Developer">
                            </label>
                            <label>
                                Company
                                <input type="text" name="company" value="<?php echo h($jobFormData['company']); ?>" placeholder="Acme Labs">
                            </label>
                            <label>
                                Location
                                <input type="text" name="location" value="<?php echo h($jobFormData['location']); ?>" placeholder="Gurugram or Remote">
                            </label>
                            <label>
                                Salary range
                                <input type="text" name="salary" value="<?php echo h($jobFormData['salary']); ?>" placeholder="INR 8L - 12L">
                            </label>
                            <label>
                                Job type
                                <select name="type">
                                    <option value="">Select type</option>
                                    <?php foreach ($jobTypeChoices as $type): ?>
                                        <option value="<?php echo h($type); ?>" <?php echo $jobFormData['type'] === $type ? 'selected' : ''; ?>><?php echo h($type); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </label>
                            <label>
                                Category
                                <input type="text" name="category" value="<?php echo h($jobFormData['category']); ?>" placeholder="Engineering, Sales, Operations">
                            </label>
                            <label class="full-span">
                                Experience band
                                <input type="text" name="experience" value="<?php echo h($jobFormData['experience']); ?>" placeholder="2-4 years">
                            </label>
                            <label class="full-span">
                                Job description
                                <textarea name="description" rows="5" placeholder="Describe the role, team, and impact."><?php echo h($jobFormData['description']); ?></textarea>
                            </label>
                            <label class="full-span">
                                Requirements
                                <textarea name="requirements" rows="4" placeholder="One requirement per line"><?php echo h($jobFormData['requirements']); ?></textarea>
                            </label>
                            <label class="full-span">
                                Skills or tags
                                <textarea name="skills" rows="3" placeholder="Comma separated skills"><?php echo h($jobFormData['skills']); ?></textarea>
                            </label>
                        </div>

                        <div class="form-actions">
                            <button class="button button-primary" type="submit"><?php echo $isEditingJob ? 'Save changes' : 'Publish role'; ?></button>
                            <?php if ($isEditingJob): ?>
                                <a class="button button-ghost" href="<?php echo h(build_url(['page' => 'post-job'], '#employer-form')); ?>">Create new role</a>
                            <?php endif; ?>
                            <a class="button button-ghost" href="index.php">Back to portal</a>
                        </div>
                    </form>
                </div>

                <aside class="side-panel">
                    <div class="side-card">
                        <p class="eyebrow">What this page does</p>
                        <h3>HR posting and editing flow</h3>
                        <p>Every successful post or edit is written to `data/jobs.json`, then surfaced instantly on the jobs feed and dashboard.</p>
                    </div>
                    <div class="side-card">
                        <p class="eyebrow">Recommended fields</p>
                        <ul class="clean-list">
                            <li>Keep the job title specific and searchable.</li>
                            <li>Use a realistic salary range to improve applications.</li>
                            <li>Add 3-5 concrete requirements so candidates self-select better.</li>
                        </ul>
                    </div>
                    <div class="side-card accent-card">
                        <p class="eyebrow">Editing status</p>
                        <?php if ($isEditingJob): ?>
                            <h3><?php echo h((string) $editingJob['title']); ?></h3>
                            <p><?php echo h((string) $editingJob['company']); ?> · <?php echo h((string) $editingJob['location']); ?></p>
                            <div class="tag-row">
                                <span class="micro-pill <?php echo is_job_active($editingJob) ? 'is-active' : 'is-inactive'; ?>">
                                    <?php echo is_job_active($editingJob) ? 'Active' : 'Inactive'; ?>
                                </span>
                                <span><?php echo h((string) $editingJob['type']); ?></span>
                                <span><?php echo h((string) $editingJob['experience']); ?></span>
                            </div>
                        <?php else: ?>
                            <h3>Pick a live role to edit</h3>
                            <p>Select any job from the management list below and the form will load that role for editing.</p>
                        <?php endif; ?>
                    </div>
                </aside>
            </div>
        </section>

        <section class="section section-tight" id="manage-jobs">
            <div class="container">
                    <div class="dashboard-card manage-jobs-panel">
                        <div class="section-heading section-heading-split">
                            <div>
                                <p class="eyebrow">Manage live roles</p>
                                <h2>Edit current job postings</h2>
                            </div>
                        <div class="hero-chip-row">
                            <span class="hero-chip"><?php echo h((string) $activeJobsCount); ?> active</span>
                            <span class="hero-chip"><?php echo h((string) $inactiveJobsCount); ?> inactive</span>
                        </div>
                    </div>

                    <div class="manage-jobs-grid">
                        <?php foreach ($managedJobs as $managedJob): ?>
                            <?php $isManagedEditing = $isEditingJob && $jobFormData['job_id'] === (string) $managedJob['id']; ?>
                            <article class="manage-job-card <?php echo $isManagedEditing ? 'is-editing' : ''; ?>">
                                <div class="manage-job-head">
                                    <div>
                                        <p class="eyebrow"><?php echo h((string) $managedJob['category']); ?></p>
                                        <h3><?php echo h((string) $managedJob['title']); ?></h3>
                                        <p class="job-company"><?php echo h((string) $managedJob['company']); ?> · <?php echo h((string) $managedJob['location']); ?></p>
                                    </div>
                                    <div class="manage-job-badges">
                                        <span class="micro-pill <?php echo is_job_active($managedJob) ? 'is-active' : 'is-inactive'; ?>">
                                            <?php echo is_job_active($managedJob) ? 'Active' : 'Inactive'; ?>
                                        </span>
                                        <span class="micro-pill <?php echo !empty($managedJob['featured']) ? 'is-featured' : ''; ?>">
                                            <?php echo !empty($managedJob['featured']) ? 'Featured' : h(human_time_diff((string) ($managedJob['created_at'] ?? ''))); ?>
                                        </span>
                                    </div>
                                </div>
                                <p class="job-salary"><?php echo h((string) $managedJob['salary']); ?></p>
                                <div class="tag-row">
                                    <span><?php echo h((string) $managedJob['type']); ?></span>
                                    <span><?php echo h((string) $managedJob['experience']); ?></span>
                                </div>
                                <div class="manage-job-actions">
                                    <a class="button button-primary button-sm" href="<?php echo h(build_url(['page' => 'post-job', 'edit' => (string) $managedJob['id']], '#employer-form')); ?>">Edit role</a>
                                    <form method="post" class="action-form">
                                        <input type="hidden" name="action" value="set_job_status">
                                        <input type="hidden" name="csrf_token" value="<?php echo h($csrfToken); ?>">
                                        <input type="hidden" name="job_id" value="<?php echo h((string) $managedJob['id']); ?>">
                                        <input type="hidden" name="next_status" value="<?php echo is_job_active($managedJob) ? 'inactive' : 'active'; ?>">
                                        <input type="hidden" name="return_edit_job" value="<?php echo h($jobFormData['job_id']); ?>">
                                        <button class="button button-ghost button-sm" type="submit">
                                            <?php echo is_job_active($managedJob) ? 'Deactivate' : 'Activate'; ?>
                                        </button>
                                    </form>
                                    <form method="post" class="action-form">
                                        <input type="hidden" name="action" value="delete_job">
                                        <input type="hidden" name="csrf_token" value="<?php echo h($csrfToken); ?>">
                                        <input type="hidden" name="job_id" value="<?php echo h((string) $managedJob['id']); ?>">
                                        <input type="hidden" name="return_edit_job" value="<?php echo h($jobFormData['job_id']); ?>">
                                        <button class="button button-danger button-sm" type="submit" onclick="return confirm('Delete this role permanently?');">Delete</button>
                                    </form>
                                    <?php if (is_job_active($managedJob)): ?>
                                        <a class="button button-ghost button-sm" href="<?php echo h(build_url(['job' => (string) $managedJob['id']], '#job-details')); ?>">View live</a>
                                    <?php else: ?>
                                        <span class="button button-muted button-sm">Hidden from portal</span>
                                    <?php endif; ?>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </section>
    </main>
<?php elseif ($page === 'dashboard'): ?>
    <main>
        <section class="hero hero-compact">
            <div class="container hero-grid">
                <div class="hero-copy">
                    <p class="eyebrow">Hiring overview</p>
                    <div class="hero-chip-row">
                        <span class="hero-chip">Portal analytics</span>
                        <span class="hero-chip">Recent applicant feed</span>
                        <span class="hero-chip">Category heatmap</span>
                    </div>
                    <h1>Track portal activity at a glance.</h1>
                    <p class="hero-text">This lightweight dashboard reads from the same job and application JSON files, giving you a simple control room without any external services.</p>
                </div>
                <div class="hero-panel">
                    <div class="hero-panel-top">
                        <div>
                            <p class="panel-kicker">Hiring pulse</p>
                            <h3><?php echo h((string) $dashboard['hottest_category']); ?></h3>
                        </div>
                        <span class="panel-badge"><?php echo h((string) $dashboard['apply_per_role']); ?> avg</span>
                    </div>
                    <p>The busiest category in the current sample data, with applications syncing directly from the candidate form.</p>
                    <div class="radar-cluster">
                        <?php foreach ($topTypeHighlights as $item): ?>
                            <span class="radar-chip"><?php echo h((string) $item['label']); ?> · <?php echo h((string) $item['count']); ?></span>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </section>

        <section class="section">
            <div class="container metrics-grid">
                <?php render_metric_card((string) $dashboard['jobs_count'], 'Active roles', 'Live jobs currently available on the portal.'); ?>
                <?php render_metric_card((string) $dashboard['applications_count'], 'Applications', 'Candidate submissions stored in the portal.'); ?>
                <?php render_metric_card((string) $dashboard['companies_count'], 'Hiring companies', 'Unique employers represented in the feed.'); ?>
                <?php render_metric_card((string) $dashboard['apply_per_role'], 'Apps per role', 'Average application load across open positions.'); ?>
            </div>
        </section>

        <section class="section">
            <div class="container dashboard-grid">
                <div class="dashboard-card">
                    <div class="section-heading">
                        <p class="eyebrow">Category mix</p>
                        <h2>Where the demand is strongest</h2>
                    </div>
                    <?php
                    $maxCategory = $dashboard['categories'][0]['count'] ?? 1;
                    foreach ($dashboard['categories'] as $item):
                        $width = max(12, (int) round(($item['count'] / $maxCategory) * 100));
                    ?>
                        <div class="bar-row">
                            <div class="bar-copy">
                                <span><?php echo h((string) $item['label']); ?></span>
                                <strong><?php echo h((string) $item['count']); ?> roles</strong>
                            </div>
                            <div class="bar-track">
                                <span style="width: <?php echo $width; ?>%"></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="dashboard-card">
                    <div class="section-heading">
                        <p class="eyebrow">Work mode</p>
                        <h2>Role format distribution</h2>
                    </div>
                    <div class="type-stack">
                        <?php foreach ($dashboard['types'] as $item): ?>
                            <article class="type-card">
                                <strong><?php echo h((string) $item['count']); ?></strong>
                                <span><?php echo h((string) $item['label']); ?></span>
                            </article>
                        <?php endforeach; ?>
                    </div>
                    <div class="mini-insight">
                        <p><strong><?php echo h((string) $dashboard['remote_count']); ?></strong> remote-friendly roles are currently visible.</p>
                        <p><strong><?php echo h((string) $dashboard['featured_count']); ?></strong> roles are highlighted as featured on the homepage.</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="section">
            <div class="container dashboard-grid">
                <div class="dashboard-card">
                    <div class="section-heading">
                        <p class="eyebrow">Recent jobs</p>
                        <h2>Latest published roles</h2>
                    </div>
                    <div class="list-stack">
                        <?php foreach ($dashboard['recent_jobs'] as $job): ?>
                            <article class="list-card">
                                <div>
                                    <h3><?php echo h((string) $job['title']); ?></h3>
                                    <p><?php echo h((string) $job['company']); ?> · <?php echo h((string) $job['location']); ?></p>
                                </div>
                                <span><?php echo h(human_time_diff((string) $job['created_at'])); ?></span>
                            </article>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="dashboard-card">
                    <div class="section-heading">
                        <p class="eyebrow">Recent applications</p>
                        <h2>Latest candidate activity</h2>
                    </div>
                    <div class="list-stack">
                        <?php foreach ($dashboard['recent_applications'] as $application): ?>
                            <article class="list-card application-card">
                                <div>
                                    <h3><?php echo h((string) $application['candidate_name']); ?></h3>
                                    <p><?php echo h((string) $application['job_title']); ?> · <?php echo h((string) $application['company']); ?></p>
                                </div>
                                <span class="status-pill <?php echo status_class((string) $application['status']); ?>">
                                    <?php echo h((string) $application['status']); ?>
                                </span>
                            </article>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </section>

        <section class="section">
            <div class="container">
                <div class="dashboard-card">
                    <div class="section-heading section-heading-split">
                        <div>
                            <p class="eyebrow">All candidate data</p>
                            <h2>Every application submitted on the portal</h2>
                        </div>
                        <div class="dashboard-actions">
                            <span class="panel-badge"><?php echo h((string) count($applications)); ?> records</span>
                            <a class="button button-primary button-sm" href="<?php echo h(build_url(['page' => 'dashboard', 'export' => 'applications-xls'])); ?>">Download Excel</a>
                        </div>
                    </div>

                    <?php if ($applications === []): ?>
                        <div class="empty-card">
                            <h3>No applications yet.</h3>
                            <p>As soon as candidates start applying, all their submitted data will appear here on the dashboard.</p>
                        </div>
                    <?php else: ?>
                        <div class="applications-table-wrap">
                            <table class="applications-table">
                                <thead>
                                    <tr>
                                        <th>Candidate</th>
                                        <th>Contact</th>
                                        <th>City</th>
                                        <th>Applied for</th>
                                        <th>Status</th>
                                        <th>Applied on</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($applications as $application): ?>
                                        <tr>
                                            <td>
                                                <div class="table-primary"><?php echo h((string) $application['candidate_name']); ?></div>
                                                <?php if (!empty($application['experience'])): ?>
                                                    <div class="table-secondary"><?php echo h((string) $application['experience']); ?></div>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="table-primary"><?php echo h((string) ($application['phone'] ?? '')); ?></div>
                                                <div class="table-secondary">
                                                    <?php echo !empty($application['email']) ? h((string) $application['email']) : 'Email not collected'; ?>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="table-primary"><?php echo h((string) ($application['city'] ?? '-')); ?></div>
                                            </td>
                                            <td>
                                                <div class="table-primary"><?php echo h((string) $application['job_title']); ?></div>
                                                <div class="table-secondary"><?php echo h((string) $application['company']); ?></div>
                                                <?php if (!empty($application['summary'])): ?>
                                                    <div class="table-note"><?php echo h((string) $application['summary']); ?></div>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="status-pill <?php echo status_class((string) $application['status']); ?>">
                                                    <?php echo h((string) $application['status']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="table-primary"><?php echo h(format_portal_datetime((string) $application['applied_at'])); ?></div>
                                                <div class="table-secondary"><?php echo h(human_time_diff((string) $application['applied_at'])); ?></div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>
<?php elseif ($page === 'employer-login'): ?>
    <main>
        <section class="hero hero-compact">
            <div class="container hero-grid">
                <div class="hero-copy">
                    <p class="eyebrow">Employer access</p>
                    <div class="hero-chip-row">
                        <span class="hero-chip">Protected HR panel</span>
                        <span class="hero-chip">Post jobs securely</span>
                        <span class="hero-chip">Dashboard after login</span>
                    </div>
                    <h1>Employer login required for job posting and dashboard access.</h1>
                    <p class="hero-text">Sign in to open the HR panel, publish jobs, edit live roles, download candidate data, and manage hiring activity from one place.</p>
                </div>
                <div class="hero-panel stack-panel">
                    <div class="hero-panel-top">
                        <div>
                            <p class="panel-kicker">Redirect after login</p>
                            <h3><?php echo $employerRedirectPage === 'dashboard' ? 'Dashboard access' : 'Job posting access'; ?></h3>
                        </div>
                        <span class="panel-badge">Employer only</span>
                    </div>
                    <div class="panel-line">
                        <strong><?php echo h((string) $dashboard['jobs_count']); ?></strong>
                        <span>active roles can be managed after sign in</span>
                    </div>
                    <div class="panel-line">
                        <strong><?php echo h((string) $dashboard['applications_count']); ?></strong>
                        <span>candidate records available in the employer dashboard</span>
                    </div>
                    <div class="panel-line">
                        <strong><?php echo h((string) $dashboard['companies_count']); ?></strong>
                        <span>companies already represented in this portal demo</span>
                    </div>
                </div>
            </div>
        </section>

        <section class="section">
            <div class="container split-layout">
                <div class="form-panel" id="employer-login-form">
                    <div class="section-heading">
                        <p class="eyebrow">Sign in</p>
                        <h2>Open the employer panel</h2>
                    </div>
                    <p class="form-note">Enter employer credentials to continue to the <?php echo $employerRedirectPage === 'dashboard' ? 'dashboard' : 'job posting page'; ?>.</p>
                    <?php render_error_list($employerLoginErrors); ?>
                    <form method="post" class="portal-form">
                        <input type="hidden" name="action" value="employer_login">
                        <input type="hidden" name="csrf_token" value="<?php echo h($csrfToken); ?>">
                        <input type="hidden" name="redirect" value="<?php echo h($employerRedirectPage); ?>">

                        <label>
                            Employer email
                            <input type="email" name="email" value="<?php echo h($employerLoginData['email']); ?>" placeholder="employer@hireloop.in" autocomplete="username">
                        </label>

                        <label>
                            Password
                            <input type="password" name="password" placeholder="Enter password" autocomplete="current-password">
                        </label>

                        <div class="form-actions">
                            <button class="button button-primary" type="submit">Login as employer</button>
                            <a class="button button-ghost" href="index.php">Back to jobs</a>
                        </div>
                    </form>
                </div>

                <aside class="side-panel">
                    <div class="side-card accent-card">
                        <p class="eyebrow">Access rules</p>
                        <h3>What unlocks after login</h3>
                        <ul class="clean-list">
                            <li>Post a new job and edit existing roles.</li>
                            <li>Activate, deactivate, or delete posted jobs.</li>
                            <li>Open the dashboard and export all applications in Excel format.</li>
                        </ul>
                    </div>
                    <div class="side-card">
                        <p class="eyebrow">Demo credentials</p>
                        <?php if ($showDefaultEmployerCredentials): ?>
                            <h3>Use the starter employer account</h3>
                            <ul class="clean-list">
                                <li>Email: <?php echo h((string) $employerCredentials['email']); ?></li>
                                <li>Password: <?php echo h((string) $employerCredentials['password']); ?></li>
                            </ul>
                            <p>This can be changed later with `EMPLOYER_EMAIL`, `EMPLOYER_PASSWORD`, and `EMPLOYER_NAME` environment variables.</p>
                        <?php else: ?>
                            <h3>Custom employer login is active</h3>
                            <p>Use the employer email and password configured in the current environment to continue.</p>
                        <?php endif; ?>
                    </div>
                </aside>
            </div>
        </section>
    </main>
<?php else: ?>
    <main>
        <section class="hero portal-hero">
            <div class="container hero-grid hero-grid-wide">
                <div class="hero-copy portal-hero-copy">
                    <p class="eyebrow">India's smart hiring portal</p>
                    <h1>Your job search ends here.</h1>
                    <p class="hero-text">Discover fresh openings across sales, support, back office, logistics, and software teams. This PHP portal is shaped around the same jobs-first discovery feel that large Indian hiring platforms use.</p>

                    <form method="get" action="index.php#job-feed" class="hero-search">
                        <label>
                            Job title or keyword
                            <input type="text" name="keyword" value="<?php echo h($filters['keyword']); ?>" placeholder="Search for sales, support, PHP, delivery">
                        </label>
                        <label>
                            City
                            <select name="location">
                                <option value="">All cities</option>
                                <?php foreach ($locationOptions as $location): ?>
                                    <option value="<?php echo h($location); ?>" <?php echo $filters['location'] === $location ? 'selected' : ''; ?>><?php echo h($location); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </label>
                        <button class="button button-primary" type="submit">Search jobs</button>
                    </form>

                    <div class="quick-link-row">
                        <?php foreach ($jobsByTypeLinks as $link): ?>
                            <a class="quick-link-pill" href="<?php echo h(build_url($link['params'], '#job-feed')); ?>"><?php echo h($link['title']); ?></a>
                        <?php endforeach; ?>
                    </div>

                    <div class="hero-stat-row">
                        <article class="hero-stat">
                            <strong><?php echo h((string) $dashboard['jobs_count']); ?></strong>
                            <span>active job openings</span>
                        </article>
                        <article class="hero-stat">
                            <strong><?php echo h((string) $dashboard['companies_count']); ?></strong>
                            <span>hiring companies</span>
                        </article>
                        <article class="hero-stat">
                            <strong><?php echo h((string) $dashboard['applications_count']); ?></strong>
                            <span>candidate applications</span>
                        </article>
                    </div>
                </div>

                <div class="hero-stack">
                    <article class="hero-card hero-card-brand">
                        <div class="hero-card-head">
                            <div>
                                <p class="panel-kicker">Trusted by growing teams</p>
                                <h3>Hiring across India with a clean, fast workflow.</h3>
                            </div>
                            <span class="panel-badge">Live jobs</span>
                        </div>
                        <div class="trusted-grid">
                            <?php foreach ($trustedEmployers as $company): ?>
                                <div class="trusted-pill">
                                    <span class="company-badge"><?php echo h(company_monogram($company)); ?></span>
                                    <span><?php echo h($company); ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </article>

                    <article class="hero-card hero-card-showcase">
                        <div class="hero-card-head">
                            <div>
                                <p class="panel-kicker">Portal snapshot</p>
                                <h3>Job discovery, employer posting, and candidate tracking in one place.</h3>
                            </div>
                            <span class="panel-badge"><?php echo h((string) $dashboard['remote_count']); ?> remote</span>
                        </div>

                        <?php if ($featuredLead !== null): ?>
                            <div class="showcase-role">
                                <div class="showcase-phone">
                                    <span class="micro-pill is-featured">Hot role</span>
                                    <h4><?php echo h((string) $featuredLead['title']); ?></h4>
                                    <p><?php echo h((string) $featuredLead['company']); ?> · <?php echo h((string) $featuredLead['location']); ?></p>
                                    <div class="tag-row">
                                        <span><?php echo h((string) $featuredLead['type']); ?></span>
                                        <span><?php echo h((string) $featuredLead['experience']); ?></span>
                                    </div>
                                    <a class="button button-primary button-wide" href="<?php echo h(build_url(['job' => (string) $featuredLead['id']], '#job-details')); ?>">View hot role</a>
                                </div>
                                <div class="showcase-side-stats">
                                    <article>
                                        <strong><?php echo h((string) $dashboard['hottest_category']); ?></strong>
                                        <span>top hiring lane</span>
                                    </article>
                                    <article>
                                        <strong><?php echo h((string) $dashboard['apply_per_role']); ?></strong>
                                        <span>avg applications per role</span>
                                    </article>
                                    <article>
                                        <strong><?php echo h((string) count($topCategoryHighlights)); ?></strong>
                                        <span>popular departments</span>
                                    </article>
                                </div>
                            </div>
                        <?php endif; ?>
                    </article>
                </div>
            </div>
        </section>

        <section class="section section-tight">
            <div class="container trust-banner">
                <span class="trust-banner-title">Proud to support</span>
                <div class="trust-pill-row">
                    <span>Fast hiring for MSMEs</span>
                    <span>Candidate-friendly quick apply</span>
                    <span>Post jobs in minutes</span>
                    <span>Track applications clearly</span>
                </div>
            </div>
        </section>

        <section class="section" id="popular-searches">
            <div class="container">
                <div class="section-heading section-heading-split">
                    <div>
                        <p class="eyebrow">Popular searches</p>
                        <h2>Trending searches on HireLoop</h2>
                    </div>
                    <a class="text-link" href="#job-feed">Explore all jobs</a>
                </div>
                <div class="popular-grid">
                    <?php foreach ($popularSearches as $item): ?>
                        <a class="popular-card" href="<?php echo h($item['url']); ?>">
                            <p class="popular-rank"><?php echo h($item['rank']); ?></p>
                            <h3><?php echo h($item['title']); ?></h3>
                            <p><?php echo h($item['copy']); ?></p>
                            <span class="text-link">View all</span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <section class="section" id="jobs-by-type">
            <div class="container discovery-grid">
                <article class="discovery-card">
                    <div class="section-heading">
                        <p class="eyebrow">Jobs by type</p>
                        <h2>Find roles that fit your schedule</h2>
                    </div>
                    <div class="chip-cloud">
                        <?php foreach ($topTypeHighlights as $item): ?>
                            <a class="cloud-pill" href="<?php echo h(build_url(['type' => (string) $item['label']], '#job-feed')); ?>">
                                <span><?php echo h((string) $item['label']); ?></span>
                                <strong><?php echo h((string) $item['count']); ?> openings</strong>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </article>

                <article class="discovery-card">
                    <div class="section-heading">
                        <p class="eyebrow">Trending job roles</p>
                        <h2>Departments hiring right now</h2>
                    </div>
                    <div class="chip-cloud">
                        <?php foreach ($topCategoryHighlights as $item): ?>
                            <a class="cloud-pill" href="<?php echo h(build_url(['category' => (string) $item['label']], '#job-feed')); ?>">
                                <span><?php echo h((string) $item['label']); ?></span>
                                <strong><?php echo h((string) $item['count']); ?> openings</strong>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </article>
            </div>
        </section>

        <section class="section">
            <div class="container">
                <div class="section-heading">
                    <p class="eyebrow">Featured jobs</p>
                    <h2>Roles job seekers are opening first</h2>
                </div>
                <div class="featured-grid">
                    <?php foreach ($featuredJobs as $job): ?>
                        <article class="featured-card">
                            <div class="featured-card-top">
                                <span class="company-badge"><?php echo h(company_monogram((string) $job['company'])); ?></span>
                                <span class="micro-pill is-featured">Featured</span>
                            </div>
                            <p class="eyebrow"><?php echo h((string) $job['category']); ?></p>
                            <h3><?php echo h((string) $job['title']); ?></h3>
                            <p><?php echo h((string) $job['company']); ?> · <?php echo h((string) $job['location']); ?></p>
                            <div class="tag-row">
                                <span><?php echo h((string) $job['type']); ?></span>
                                <span><?php echo h((string) $job['experience']); ?></span>
                            </div>
                            <div class="featured-card-footer">
                                <span><?php echo h(human_time_diff((string) $job['created_at'])); ?></span>
                                <a class="text-link" href="<?php echo h(build_url(['job' => (string) $job['id']], '#job-details')); ?>">Open role</a>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <section class="section" id="jobs-by-city">
            <div class="container discovery-board">
                <article class="discovery-panel">
                    <div class="section-heading">
                        <p class="eyebrow">Jobs by city</p>
                        <h2>Explore hiring hotspots</h2>
                    </div>
                    <div class="city-list-grid">
                        <?php foreach ($cityHighlights as $item): ?>
                            <a class="list-pill" href="<?php echo h(build_url(['location' => (string) $item['label']], '#job-feed')); ?>">
                                <span><?php echo h((string) $item['label']); ?></span>
                                <strong><?php echo h((string) $item['count']); ?></strong>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </article>

                <article class="discovery-panel" id="jobs-by-company">
                    <div class="section-heading">
                        <p class="eyebrow">Jobs by company</p>
                        <h2>Popular hiring brands on the portal</h2>
                    </div>
                    <div class="city-list-grid">
                        <?php foreach ($companyHighlights as $item): ?>
                            <a class="list-pill" href="<?php echo h(build_url(['keyword' => (string) $item['label']], '#job-feed')); ?>">
                                <span><?php echo h((string) $item['label']); ?></span>
                                <strong><?php echo h((string) $item['count']); ?></strong>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </article>

                <article class="discovery-panel" id="career-tools">
                    <div class="section-heading">
                        <p class="eyebrow">Career tools</p>
                        <h2>Stay job-ready beyond the first click</h2>
                    </div>
                    <div class="tool-stack">
                        <?php foreach ($careerTools as $tool): ?>
                            <article class="tool-card">
                                <p class="signal-label"><?php echo h($tool['tag']); ?></p>
                                <h3><?php echo h($tool['title']); ?></h3>
                                <p><?php echo h($tool['copy']); ?></p>
                            </article>
                        <?php endforeach; ?>
                    </div>
                </article>
            </div>
        </section>

        <section class="section" id="job-feed">
            <div class="container">
                <div class="filter-card">
                    <div class="section-heading">
                        <p class="eyebrow">Search</p>
                        <h2>Browse jobs and apply in one flow</h2>
                    </div>
                    <p class="filter-intro">Use quick filters to narrow down by keyword, city, category, or work mode, then compare the selected role and apply from the same page.</p>
                    <form method="get" class="filters-grid">
                        <label>
                            Keyword
                            <input type="text" name="keyword" value="<?php echo h($filters['keyword']); ?>" placeholder="PHP, support, design">
                        </label>
                        <label>
                            Location
                            <select name="location">
                                <option value="">All locations</option>
                                <?php foreach ($locationOptions as $location): ?>
                                    <option value="<?php echo h($location); ?>" <?php echo $filters['location'] === $location ? 'selected' : ''; ?>><?php echo h($location); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </label>
                        <label>
                            Category
                            <select name="category">
                                <option value="">All categories</option>
                                <?php foreach ($categoryOptions as $category): ?>
                                    <option value="<?php echo h($category); ?>" <?php echo $filters['category'] === $category ? 'selected' : ''; ?>><?php echo h($category); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </label>
                        <label>
                            Type
                            <select name="type">
                                <option value="">All types</option>
                                <?php foreach ($typeOptions as $type): ?>
                                    <option value="<?php echo h($type); ?>" <?php echo $filters['type'] === $type ? 'selected' : ''; ?>><?php echo h($type); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </label>
                        <div class="filter-actions">
                            <button class="button button-primary" type="submit">Apply filters</button>
                            <a class="button button-ghost" href="index.php#job-feed">Reset</a>
                        </div>
                    </form>
                </div>
            </div>
        </section>

        <section class="section">
            <div class="container portal-layout">
                <div>
                    <div class="results-header">
                        <div>
                            <p class="eyebrow">Results</p>
                            <h2><?php echo h((string) count($filteredJobs)); ?> matching opportunities</h2>
                    <div class="results-pills">
                                <?php foreach (array_slice($topCategoryHighlights, 0, 4) as $item): ?>
                                    <span class="results-pill"><?php echo h((string) $item['label']); ?> · <?php echo h((string) $item['count']); ?></span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php if ($employerLoggedIn): ?>
                            <a class="text-link" href="<?php echo h($dashboardUrl); ?>">View dashboard</a>
                        <?php else: ?>
                            <a class="text-link" href="<?php echo h($employerDashboardUrl); ?>">Employer login</a>
                        <?php endif; ?>
                    </div>

                    <?php if ($filteredJobs === []): ?>
                        <div class="empty-card">
                            <h3>No roles match your current filters.</h3>
                            <p>Try broadening the keyword or clearing one of the dropdown filters.</p>
                            <a class="button button-primary" href="index.php#job-feed">Show all jobs</a>
                        </div>
                    <?php else: ?>
                        <div class="jobs-stack">
                            <?php foreach ($filteredJobs as $job): ?>
                                <?php render_job_card($job, $filters, $selectedJobId); ?>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <aside class="sticky-column">
                    <?php if ($selectedJob !== null): ?>
                        <div class="detail-card" id="job-details">
                            <div class="job-card-head">
                                <span class="eyebrow"><?php echo h((string) $selectedJob['category']); ?></span>
                                <span class="micro-pill"><?php echo h(human_time_diff((string) $selectedJob['created_at'])); ?></span>
                            </div>
                            <div class="detail-header-block">
                                <span class="company-badge is-large"><?php echo h(company_monogram((string) $selectedJob['company'])); ?></span>
                                <div>
                                    <h2><?php echo h((string) $selectedJob['title']); ?></h2>
                                    <p class="job-company"><?php echo h((string) $selectedJob['company']); ?> · <?php echo h((string) $selectedJob['location']); ?></p>
                                </div>
                            </div>
                            <p class="job-salary"><?php echo h((string) $selectedJob['salary']); ?></p>
                            <div class="detail-matrix">
                                <article class="detail-stat">
                                    <span>Work mode</span>
                                    <strong><?php echo h((string) $selectedJob['type']); ?></strong>
                                </article>
                                <article class="detail-stat">
                                    <span>Experience</span>
                                    <strong><?php echo h((string) $selectedJob['experience']); ?></strong>
                                </article>
                                <article class="detail-stat">
                                    <span>Applicants</span>
                                    <strong><?php echo h((string) $selectedJobApplications); ?></strong>
                                </article>
                            </div>
                            <div class="tag-row">
                                <?php foreach ($selectedJob['skills'] as $skill): ?>
                                    <span><?php echo h((string) $skill); ?></span>
                                <?php endforeach; ?>
                            </div>
                            <p class="detail-copy"><?php echo h((string) $selectedJob['description']); ?></p>

                            <div class="detail-block">
                                <h3>Requirements</h3>
                                <ul class="clean-list">
                                    <?php foreach ($selectedJob['requirements'] as $requirement): ?>
                                        <li><?php echo h((string) $requirement); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>

                            <div class="detail-block">
                                <h3>Why this role stands out</h3>
                                <ul class="clean-list">
                                    <li>Clear compensation and work-mode visibility for faster decisions.</li>
                                    <li>Focused requirements so candidates can quickly self-qualify.</li>
                                    <li>Quick-apply popup keeps the form on the same page.</li>
                                </ul>
                            </div>
                        </div>

                        <?php
                        $selectedApplyUrl = build_url(array_merge($filters, ['job' => (string) $selectedJob['id'], 'apply' => '1']), '#apply-modal');
                        ?>
                        <div class="detail-card apply-prompt-card" id="apply-form">
                            <div class="section-heading">
                                <p class="eyebrow">Quick apply popup</p>
                                <h2>Open the form in a popup</h2>
                            </div>
                            <p class="form-note">`Apply now` दबाते ही form इसी page पर popup में खुलेगा, ताकि candidate बिना page छोड़े apply कर सके.</p>
                            <div class="apply-preview-card">
                                <span class="company-badge is-large"><?php echo h(company_monogram((string) $selectedJob['company'])); ?></span>
                                <div>
                                    <strong><?php echo h((string) $selectedJob['title']); ?></strong>
                                    <span><?php echo h((string) $selectedJob['company']); ?> · <?php echo h((string) $selectedJob['location']); ?></span>
                                </div>
                            </div>
                            <div class="form-actions">
                                <a
                                    class="button button-primary"
                                    href="<?php echo h($selectedApplyUrl); ?>"
                                    data-apply-trigger
                                    data-job-id="<?php echo h((string) $selectedJob['id']); ?>"
                                    data-job-title="<?php echo h((string) $selectedJob['title']); ?>"
                                    data-job-company="<?php echo h((string) $selectedJob['company']); ?>"
                                    data-job-location="<?php echo h((string) $selectedJob['location']); ?>"
                                    data-job-type="<?php echo h((string) $selectedJob['type']); ?>"
                                    data-job-experience="<?php echo h((string) $selectedJob['experience']); ?>"
                                    data-job-salary="<?php echo h((string) $selectedJob['salary']); ?>"
                                    data-job-badge="<?php echo h(company_monogram((string) $selectedJob['company'])); ?>"
                                >Open application form</a>
                                <a class="button button-ghost" href="#job-feed">Browse more jobs</a>
                            </div>
                            <p class="popup-hint">Popup form keeps the selected job, so the candidate can submit faster.</p>
                        </div>
                    <?php endif; ?>
                </aside>
            </div>
        </section>

        <section class="section" id="download-app">
            <div class="container download-band">
                <div>
                    <p class="eyebrow">Download app section</p>
                    <h2>Apply faster, get recruiter responses, and track progress clearly.</h2>
                    <p>This demo stays web-based, but the section is designed in the same style as app-focused job platforms: clear value props, application momentum, and strong next-step prompts.</p>
                    <ul class="clean-list">
                        <li>Unlimited local demo applications</li>
                        <li>Recruiter-friendly profile flow</li>
                        <li>Dashboard support for tracking activity</li>
                    </ul>
                </div>
                <div class="download-card">
                    <div class="download-phone">
                        <span class="micro-pill is-featured">Quick apply</span>
                        <h3>HireLoop Mobile Experience</h3>
                        <p>Track applications, open saved roles, and get a clearer sense of what is moving in your funnel.</p>
                        <div class="download-metrics">
                            <div>
                                <strong><?php echo h((string) $dashboard['applications_count']); ?></strong>
                                <span>applications</span>
                            </div>
                            <div>
                                <strong><?php echo h((string) $dashboard['jobs_count']); ?></strong>
                                <span>live jobs</span>
                            </div>
                        </div>
                    </div>
                    <div class="hero-actions">
                        <a class="button button-primary" href="#job-feed">Start exploring</a>
                        <?php if ($employerLoggedIn): ?>
                            <a class="button button-ghost" href="<?php echo h($dashboardUrl); ?>">Track activity</a>
                        <?php else: ?>
                            <a class="button button-ghost" href="<?php echo h($employerDashboardUrl); ?>">Employer login</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </section>

        <section class="section">
            <div class="container cta-band employer-band">
                <div>
                    <p class="eyebrow">HireLoop for employers</p>
                    <h2>Want to hire? Post a job and start getting candidates faster.</h2>
                    <p>Use the built-in posting form to add fresh openings instantly, then monitor application flow from the dashboard page.</p>
                </div>
                <div class="hero-actions">
                    <?php if ($employerLoggedIn): ?>
                        <a class="button button-primary" href="<?php echo h($postJobUrl); ?>">Post job</a>
                        <a class="button button-ghost" href="<?php echo h($dashboardUrl); ?>">See insights</a>
                    <?php else: ?>
                        <a class="button button-primary" href="<?php echo h($employerPostJobUrl); ?>">Employer login</a>
                        <a class="button button-ghost" href="index.php#job-feed">Explore jobs</a>
                    <?php endif; ?>
                </div>
            </div>
        </section>
    </main>
    <?php if ($modalJob !== null): ?>
        <div class="apply-modal <?php echo $shouldOpenApplyModal ? 'is-visible' : ''; ?>" data-apply-modal <?php echo $shouldOpenApplyModal ? '' : 'hidden'; ?>>
            <a class="apply-modal-backdrop" href="<?php echo h($closeApplyModalUrl); ?>" data-apply-close aria-label="Close application form"></a>
            <div class="apply-modal-dialog" role="dialog" aria-modal="true" aria-labelledby="apply-modal-title">
                <a class="apply-modal-close" href="<?php echo h($closeApplyModalUrl); ?>" data-apply-close aria-label="Close application form">Close</a>
                <div class="apply-modal-grid">
                    <div class="apply-modal-copy">
                        <p class="eyebrow">Candidate quick apply</p>
                        <h2 id="apply-modal-title">Apply without leaving the page.</h2>
                        <p class="hero-text">Candidate ka form popup में खुलेगा, selected role पहले से ready रहेगा, और submit करते ही application सीधे portal data में save हो जाएगी.</p>
                        <div class="apply-modal-job">
                            <div class="detail-header-block">
                                <span class="company-badge is-large" data-apply-job-badge><?php echo h(company_monogram((string) $modalJob['company'])); ?></span>
                                <div>
                                    <h3 data-apply-job-title><?php echo h((string) $modalJob['title']); ?></h3>
                                    <p class="job-company">
                                        <span data-apply-job-company><?php echo h((string) $modalJob['company']); ?></span>
                                        ·
                                        <span data-apply-job-location><?php echo h((string) $modalJob['location']); ?></span>
                                    </p>
                                </div>
                            </div>
                            <div class="tag-row">
                                <span data-apply-job-type><?php echo h((string) $modalJob['type']); ?></span>
                                <span data-apply-job-experience><?php echo h((string) $modalJob['experience']); ?></span>
                                <span data-apply-job-salary><?php echo h((string) $modalJob['salary']); ?></span>
                            </div>
                        </div>
                        <ul class="clean-list">
                            <li>Form opens as a popup over the current job portal screen.</li>
                            <li>Name, phone, and city can be filled in one go.</li>
                            <li>If there is any validation error, the popup stays open with the typed data.</li>
                        </ul>
                    </div>

                    <div class="form-panel modal-form-panel">
                        <div class="section-heading">
                            <p class="eyebrow">Application form</p>
                            <h2>Complete candidate details</h2>
                        </div>
                        <p class="form-note">Add your basic details and submit the application quickly.</p>
                        <?php render_error_list($applicationErrors); ?>
                        <form method="post" class="portal-form" id="apply-popup-form">
                            <input type="hidden" name="action" value="apply_job">
                            <input type="hidden" name="csrf_token" value="<?php echo h($csrfToken); ?>">
                            <input type="hidden" name="job_id" value="<?php echo h($modalJobId); ?>" data-apply-job-input>

                            <div class="modal-selected-role">
                                <span class="signal-label">Applying for</span>
                                <strong data-apply-job-title-inline><?php echo h((string) $modalJob['title']); ?></strong>
                                <span>
                                    <span data-apply-job-company-inline><?php echo h((string) $modalJob['company']); ?></span>
                                    ·
                                    <span data-apply-job-location-inline><?php echo h((string) $modalJob['location']); ?></span>
                                </span>
                            </div>

                            <label>
                                Full name
                                <input type="text" name="name" value="<?php echo h($applicationFormData['name']); ?>" placeholder="Your full name">
                            </label>

                            <label>
                                Phone
                                <input type="tel" name="phone" value="<?php echo h($applicationFormData['phone']); ?>" placeholder="9876543210">
                            </label>

                            <label>
                                City
                                <input type="text" name="city" value="<?php echo h($applicationFormData['city']); ?>" placeholder="Current city">
                            </label>

                            <div class="form-actions">
                                <button class="button button-primary" type="submit">Submit application</button>
                                <a class="button button-ghost" href="<?php echo h($closeApplyModalUrl); ?>" data-apply-close>Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <script>
        (function () {
            var modal = document.querySelector('[data-apply-modal]');

            if (!modal) {
                return;
            }

            var body = document.body;
            var jobInput = modal.querySelector('[data-apply-job-input]');
            var closeLinks = modal.querySelectorAll('[data-apply-close]');
            var titleTargets = modal.querySelectorAll('[data-apply-job-title], [data-apply-job-title-inline]');
            var companyTargets = modal.querySelectorAll('[data-apply-job-company], [data-apply-job-company-inline]');
            var locationTargets = modal.querySelectorAll('[data-apply-job-location], [data-apply-job-location-inline]');
            var typeTarget = modal.querySelector('[data-apply-job-type]');
            var experienceTarget = modal.querySelector('[data-apply-job-experience]');
            var salaryTarget = modal.querySelector('[data-apply-job-salary]');
            var badgeTarget = modal.querySelector('[data-apply-job-badge]');
            var firstField = modal.querySelector('input[name="name"]');

            function setText(nodes, value) {
                nodes.forEach(function (node) {
                    node.textContent = value || '';
                });
            }

            function buildCloseUrl(jobId) {
                var url = new URL(window.location.href);
                url.searchParams.delete('apply');

                if (jobId) {
                    url.searchParams.set('job', jobId);
                }

                url.hash = 'job-details';
                return url.toString();
            }

            function syncJob(trigger) {
                var jobId = trigger.getAttribute('data-job-id') || '';
                var jobTitle = trigger.getAttribute('data-job-title') || '';
                var jobCompany = trigger.getAttribute('data-job-company') || '';
                var jobLocation = trigger.getAttribute('data-job-location') || '';
                var jobType = trigger.getAttribute('data-job-type') || '';
                var jobExperience = trigger.getAttribute('data-job-experience') || '';
                var jobSalary = trigger.getAttribute('data-job-salary') || '';
                var jobBadge = trigger.getAttribute('data-job-badge') || '';
                var closeUrl = buildCloseUrl(jobId);

                if (jobInput) {
                    jobInput.value = jobId;
                }

                setText(titleTargets, jobTitle);
                setText(companyTargets, jobCompany);
                setText(locationTargets, jobLocation);

                if (typeTarget) {
                    typeTarget.textContent = jobType;
                }

                if (experienceTarget) {
                    experienceTarget.textContent = jobExperience;
                }

                if (salaryTarget) {
                    salaryTarget.textContent = jobSalary;
                }

                if (badgeTarget) {
                    badgeTarget.textContent = jobBadge;
                }

                closeLinks.forEach(function (link) {
                    link.setAttribute('href', closeUrl);
                });
            }

            function openModal() {
                modal.hidden = false;
                modal.classList.add('is-visible');
                body.classList.add('has-modal-open');

                if (firstField && firstField.value.trim() === '') {
                    firstField.focus();
                }
            }

            function closeModal(event) {
                if (event) {
                    event.preventDefault();
                }

                modal.classList.remove('is-visible');
                modal.hidden = true;
                body.classList.remove('has-modal-open');

                if (window.history && window.history.replaceState && jobInput) {
                    window.history.replaceState({}, '', buildCloseUrl(jobInput.value));
                }
            }

            document.querySelectorAll('[data-apply-trigger]').forEach(function (trigger) {
                trigger.addEventListener('click', function (event) {
                    event.preventDefault();
                    syncJob(trigger);
                    openModal();
                });
            });

            closeLinks.forEach(function (link) {
                link.addEventListener('click', closeModal);
            });

            modal.addEventListener('click', function (event) {
                if (event.target === modal) {
                    closeModal(event);
                }
            });

            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape' && modal.classList.contains('is-visible')) {
                    closeModal(event);
                }
            });

            if (modal.classList.contains('is-visible')) {
                body.classList.add('has-modal-open');
            }
        }());
        </script>
    <?php endif; ?>
<?php endif; ?>

<?php
render_footer();
