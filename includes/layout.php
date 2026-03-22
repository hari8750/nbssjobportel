<?php
declare(strict_types=1);

require_once __DIR__ . '/data.php';

function nav_active_class(string $activePage, string $linkPage): string
{
    return $activePage === $linkPage ? 'is-active' : '';
}

function render_schema_markup(array $schemas): void
{
    foreach ($schemas as $schema) {
        if (!is_array($schema) || $schema === []) {
            continue;
        }

        $json = json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        if ($json === false) {
            continue;
        }
        ?>
    <script type="application/ld+json"><?php echo $json; ?></script>
        <?php
    }
}

function render_header(string $activePage, array $seo = []): void
{
    $title = (string) ($seo['title'] ?? (SITE_NAME . ' | Jobs in India and Hiring Portal'));
    $description = (string) ($seo['description'] ?? SITE_DEFAULT_DESCRIPTION);
    $canonical = (string) ($seo['canonical'] ?? absolute_url());
    $robots = (string) ($seo['robots'] ?? 'index,follow,max-image-preview:large,max-snippet:-1,max-video-preview:-1');
    $ogType = (string) ($seo['og_type'] ?? 'website');
    $image = (string) ($seo['image'] ?? absolute_url('assets/seo-share.svg'));
    $imageAlt = (string) ($seo['image_alt'] ?? (SITE_NAME . ' preview image'));
    $publishedTime = trim((string) ($seo['published_time'] ?? ''));
    $schemas = is_array($seo['schemas'] ?? null) ? $seo['schemas'] : [];
    $employer = current_employer();
    $employerLoggedIn = $employer !== null;
    $csrfToken = ensure_csrf_token();
    ?>
<!DOCTYPE html>
<html lang="en-IN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo h($title); ?></title>
    <meta name="description" content="<?php echo h($description); ?>">
    <meta name="robots" content="<?php echo h($robots); ?>">
    <meta name="theme-color" content="#1768ff">
    <meta name="application-name" content="<?php echo h(SITE_NAME); ?>">
    <link rel="canonical" href="<?php echo h($canonical); ?>">
    <meta property="og:locale" content="en_IN">
    <meta property="og:site_name" content="<?php echo h(SITE_NAME); ?>">
    <meta property="og:type" content="<?php echo h($ogType); ?>">
    <meta property="og:title" content="<?php echo h($title); ?>">
    <meta property="og:description" content="<?php echo h($description); ?>">
    <meta property="og:url" content="<?php echo h($canonical); ?>">
    <meta property="og:image" content="<?php echo h($image); ?>">
    <meta property="og:image:alt" content="<?php echo h($imageAlt); ?>">
    <meta property="og:image:type" content="image/svg+xml">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?php echo h($title); ?>">
    <meta name="twitter:description" content="<?php echo h($description); ?>">
    <meta name="twitter:image" content="<?php echo h($image); ?>">
    <meta name="twitter:image:alt" content="<?php echo h($imageAlt); ?>">
    <?php if ($publishedTime !== ''): ?>
    <meta property="article:published_time" content="<?php echo h($publishedTime); ?>">
    <?php endif; ?>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;700;800&family=Space+Grotesk:wght@500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/styles.css">
    <?php render_schema_markup($schemas); ?>
</head>
<body>
    <div class="page-shell">
        <header class="site-header">
            <div class="container header-inner">
                <a class="brand" href="index.php">
                    <span class="brand-mark">HL</span>
                    <span class="brand-copy">
                        <strong>HireLoop</strong>
                        <small>Jobs for India</small>
                    </span>
                </a>
                <nav class="site-nav" aria-label="Primary">
                    <a class="<?php echo nav_active_class($activePage, 'home'); ?>" href="index.php">Explore Jobs</a>
                    <?php if ($employerLoggedIn): ?>
                        <a class="<?php echo nav_active_class($activePage, 'post-job'); ?>" href="<?php echo h(build_url(['page' => 'post-job'])); ?>">Post a Job</a>
                        <a class="<?php echo nav_active_class($activePage, 'dashboard'); ?>" href="<?php echo h(build_url(['page' => 'dashboard'])); ?>">Dashboard</a>
                    <?php endif; ?>
                </nav>
                <div class="header-actions">
                    <a class="utility-link" href="index.php#job-feed">Find jobs</a>
                    <?php if ($employerLoggedIn): ?>
                        <span class="header-session"><?php echo h((string) ($employer['name'] ?? 'Employer')); ?></span>
                        <form method="post" class="action-form">
                            <input type="hidden" name="action" value="logout_employer">
                            <input type="hidden" name="csrf_token" value="<?php echo h($csrfToken); ?>">
                            <button class="button button-ghost button-sm" type="submit">Logout</button>
                        </form>
                    <?php else: ?>
                        <a class="button button-primary button-sm" href="<?php echo h(employer_login_url('post-job')); ?>">Employer Login</a>
                    <?php endif; ?>
                </div>
            </div>
            <div class="container subnav-shell">
                <div class="subnav-track">
                    <a href="index.php#jobs-by-type">Jobs By Type</a>
                    <a href="index.php#popular-searches">Popular Searches</a>
                    <a href="index.php#jobs-by-city">Jobs By City</a>
                    <a href="index.php#jobs-by-company">Jobs By Company</a>
                    <a href="index.php#career-tools">Career Tools</a>
                    <a href="index.php#download-app">Download App</a>
                </div>
            </div>
        </header>
    <?php
}

function render_footer(): void
{
    $jobs = load_jobs();
    $types = array_slice(unique_job_values($jobs, 'type'), 0, 5);
    $cities = array_slice(unique_job_values($jobs, 'location'), 0, 6);
    $companies = array_slice(unique_job_values($jobs, 'company'), 0, 6);
    ?>
        <footer class="site-footer">
            <div class="container footer-grid">
                <div class="footer-brand-block">
                    <p class="footer-brand">HireLoop</p>
                    <p class="footer-copy">A jobs-first PHP portal inspired by modern Indian job marketplaces, built for quick customisation and local deployment.</p>
                    <div class="footer-store-row">
                        <span>Quick apply</span>
                        <span>Employer posting</span>
                        <span>Dashboard insights</span>
                    </div>
                </div>
                <div class="footer-column">
                    <p class="footer-title">Jobs by type</p>
                    <div class="footer-links">
                        <?php foreach ($types as $type): ?>
                            <a href="<?php echo h(build_url(['type' => $type], '#job-feed')); ?>"><?php echo h($type); ?></a>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="footer-column">
                    <p class="footer-title">Jobs by city</p>
                    <div class="footer-links">
                        <?php foreach ($cities as $city): ?>
                            <a href="<?php echo h(build_url(['location' => $city], '#job-feed')); ?>"><?php echo h($city); ?></a>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="footer-column">
                    <p class="footer-title">Top companies</p>
                    <div class="footer-links">
                        <?php foreach ($companies as $company): ?>
                            <a href="<?php echo h(build_url(['keyword' => $company], '#job-feed')); ?>"><?php echo h($company); ?></a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <div class="container footer-meta">
                <span>Made with PHP + JSON storage</span>
                <span>Ready for MySQL, auth, and recruiter workflows</span>
            </div>
        </footer>
    </div>
</body>
</html>
    <?php
}

function render_flash_banner(?array $flash): void
{
    if ($flash === null) {
        return;
    }

    $type = ($flash['type'] ?? '') === 'error' ? 'is-error' : 'is-success';
    ?>
    <div class="container flash-wrap">
        <div class="flash-banner <?php echo $type; ?>">
            <?php echo h((string) ($flash['message'] ?? '')); ?>
        </div>
    </div>
    <?php
}

function render_error_list(array $errors): void
{
    if ($errors === []) {
        return;
    }

    ?>
    <div class="form-errors" role="alert">
        <p>Please fix the following:</p>
        <ul>
            <?php foreach ($errors as $error): ?>
                <li><?php echo h((string) $error); ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php
}

function render_metric_card(string $value, string $label, string $copy): void
{
    ?>
    <article class="metric-card">
        <p class="metric-value"><?php echo h($value); ?></p>
        <h3><?php echo h($label); ?></h3>
        <p><?php echo h($copy); ?></p>
    </article>
    <?php
}

function company_monogram(string $name): string
{
    $words = preg_split('/\s+/', trim($name)) ?: [];
    $letters = '';

    if (count($words) === 1 && isset($words[0]) && strlen($words[0]) > 1) {
        return strtoupper(substr($words[0], 0, 2));
    }

    foreach (array_slice($words, 0, 2) as $word) {
        if ($word !== '') {
            $letters .= strtoupper(substr($word, 0, 1));
        }
    }

    if ($letters === '') {
        $letters = strtoupper(substr($name, 0, 2));
    }

    return substr($letters, 0, 2);
}

function render_job_card(array $job, array $filters, string $selectedJobId): void
{
    $jobId = (string) ($job['id'] ?? '');
    $params = array_merge($filters, ['job' => $jobId]);
    $selectedClass = $jobId === $selectedJobId ? 'is-selected' : '';
    $applyUrl = build_url(array_merge($filters, ['job' => $jobId, 'apply' => '1']), '#apply-modal');
    $company = (string) ($job['company'] ?? '');
    ?>
    <article class="job-card <?php echo $selectedClass; ?>">
        <div class="job-card-brand">
            <span class="company-badge"><?php echo h(company_monogram($company !== '' ? $company : 'HireLoop')); ?></span>
            <div class="job-card-brand-copy">
                <div class="job-card-head">
                    <span class="eyebrow"><?php echo h((string) ($job['category'] ?? '')); ?></span>
                    <span class="micro-pill <?php echo !empty($job['featured']) ? 'is-featured' : ''; ?>">
                        <?php echo !empty($job['featured']) ? 'Featured' : h(human_time_diff((string) ($job['created_at'] ?? ''))); ?>
                    </span>
                </div>
                <h3><?php echo h((string) ($job['title'] ?? '')); ?></h3>
                <p class="job-company"><?php echo h((string) ($job['company'] ?? '')); ?> · <?php echo h((string) ($job['location'] ?? '')); ?></p>
            </div>
        </div>
        <p class="job-salary"><?php echo h((string) ($job['salary'] ?? '')); ?></p>
        <div class="tag-row">
            <span><?php echo h((string) ($job['type'] ?? '')); ?></span>
            <span><?php echo h((string) ($job['experience'] ?? '')); ?></span>
            <?php if (!empty($job['skills'][0])): ?>
                <span><?php echo h((string) $job['skills'][0]); ?></span>
            <?php endif; ?>
        </div>
        <p class="job-excerpt"><?php echo h((string) ($job['description'] ?? '')); ?></p>
        <div class="card-actions">
            <a class="button button-ghost" href="<?php echo h(build_url($params, '#job-details')); ?>">View details</a>
            <a
                class="button button-primary"
                href="<?php echo h($applyUrl); ?>"
                data-apply-trigger
                data-job-id="<?php echo h($jobId); ?>"
                data-job-title="<?php echo h((string) ($job['title'] ?? '')); ?>"
                data-job-company="<?php echo h($company); ?>"
                data-job-location="<?php echo h((string) ($job['location'] ?? '')); ?>"
                data-job-type="<?php echo h((string) ($job['type'] ?? '')); ?>"
                data-job-experience="<?php echo h((string) ($job['experience'] ?? '')); ?>"
                data-job-salary="<?php echo h((string) ($job['salary'] ?? '')); ?>"
                data-job-badge="<?php echo h(company_monogram($company !== '' ? $company : 'HireLoop')); ?>"
            >Apply now</a>
        </div>
    </article>
    <?php
}

function status_class(string $status): string
{
    switch (strtolower($status)) {
        case 'shortlisted':
            return 'is-shortlisted';
        case 'reviewing':
            return 'is-reviewing';
        default:
            return 'is-new';
    }
}
