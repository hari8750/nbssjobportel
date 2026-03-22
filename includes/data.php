<?php
declare(strict_types=1);

const STORAGE_JOBS = __DIR__ . '/../data/jobs.json';
const STORAGE_APPLICATIONS = __DIR__ . '/../data/applications.json';
const STORAGE_SITEMAP = __DIR__ . '/../sitemap.xml';
const STORAGE_ROBOTS = __DIR__ . '/../robots.txt';
const SITE_NAME = 'NBSS HR Services';
const SITE_MARK = 'NB';
const SITE_TAGLINE = 'Jobs for India';
const SITE_DEFAULT_URL = 'http://127.0.0.1:8000';
const SITE_DEFAULT_DESCRIPTION = 'Discover jobs across India, explore roles by category and city, and manage hiring workflows with NBSS HR Services.';
const EMPLOYER_DEFAULT_EMAIL = 'employer@nbsshrservices.in';
const EMPLOYER_DEFAULT_PASSWORD = 'Employer@123';
const EMPLOYER_DEFAULT_NAME = 'NBSS HR Services Employer';

function default_jobs(): array
{
    return [
        [
            'id' => 'job_hireloop_01',
            'title' => 'Growth Marketing Manager',
            'company' => 'OrbitHive',
            'location' => 'Bengaluru',
            'salary' => 'INR 12L - 18L',
            'type' => 'Hybrid',
            'category' => 'Marketing',
            'experience' => '3-5 years',
            'description' => 'Lead demand-generation campaigns, own funnel metrics, and partner closely with brand and product teams to scale acquisition.',
            'requirements' => [
                'Hands-on experience with paid campaigns and lifecycle funnels',
                'Strong reporting skills and comfort with attribution analysis',
                'Ability to ship experiments every week'
            ],
            'skills' => ['Performance Marketing', 'CRM', 'Analytics'],
            'featured' => true,
            'created_at' => '2026-03-22T09:15:00+05:30'
        ],
        [
            'id' => 'job_hireloop_02',
            'title' => 'Customer Support Specialist',
            'company' => 'SwiftPe',
            'location' => 'Remote',
            'salary' => 'INR 4.2L - 6L',
            'type' => 'Remote',
            'category' => 'Support',
            'experience' => '1-3 years',
            'description' => 'Resolve merchant queries across chat and email, manage escalations, and turn customer feedback into product-ready insights.',
            'requirements' => [
                'Excellent written English and Hindi',
                'Prior SaaS or fintech support experience',
                'Comfort with shift-based coverage'
            ],
            'skills' => ['Zendesk', 'Escalation Handling', 'CX Ops'],
            'featured' => true,
            'created_at' => '2026-03-22T07:40:00+05:30'
        ],
        [
            'id' => 'job_hireloop_03',
            'title' => 'UI/UX Designer',
            'company' => 'Northstar Studio',
            'location' => 'Pune',
            'salary' => 'INR 8L - 11L',
            'type' => 'Full-time',
            'category' => 'Design',
            'experience' => '2-4 years',
            'description' => 'Design clean product flows, clickable prototypes, and polished interface systems for mobile and web experiences.',
            'requirements' => [
                'Strong portfolio with shipped digital products',
                'Figma prototyping and design-system experience',
                'Ability to collaborate with engineers and PMs'
            ],
            'skills' => ['Figma', 'Design Systems', 'Wireframing'],
            'featured' => false,
            'created_at' => '2026-03-21T18:30:00+05:30'
        ],
        [
            'id' => 'job_hireloop_04',
            'title' => 'Business Development Executive',
            'company' => 'MarketLance',
            'location' => 'New Delhi',
            'salary' => 'INR 5L - 7.5L',
            'type' => 'Full-time',
            'category' => 'Sales',
            'experience' => '1-2 years',
            'description' => 'Prospect mid-market clients, qualify inbound leads, and help the partnerships team move opportunities through the pipeline.',
            'requirements' => [
                'Strong communication and lead-nurturing skills',
                'Comfort with outbound calling and demos',
                'Experience using a CRM to track activity'
            ],
            'skills' => ['Sales Funnel', 'Lead Generation', 'CRM'],
            'featured' => true,
            'created_at' => '2026-03-21T12:10:00+05:30'
        ],
        [
            'id' => 'job_hireloop_05',
            'title' => 'Laravel Developer',
            'company' => 'StackMint',
            'location' => 'Hyderabad',
            'salary' => 'INR 9L - 14L',
            'type' => 'Full-time',
            'category' => 'Engineering',
            'experience' => '2-5 years',
            'description' => 'Build backend APIs, optimize database queries, and contribute to a growing product platform using Laravel and modern PHP practices.',
            'requirements' => [
                'Production experience with Laravel and MySQL',
                'API design and authentication knowledge',
                'Good debugging and code-review habits'
            ],
            'skills' => ['PHP', 'Laravel', 'REST APIs'],
            'featured' => false,
            'created_at' => '2026-03-20T16:45:00+05:30'
        ],
        [
            'id' => 'job_hireloop_06',
            'title' => 'Operations Coordinator',
            'company' => 'UrbanBasket',
            'location' => 'Mumbai',
            'salary' => 'INR 4.8L - 6.5L',
            'type' => 'Shift-based',
            'category' => 'Operations',
            'experience' => '1-3 years',
            'description' => 'Coordinate city operations, monitor SLAs, and keep field teams aligned with real-time business priorities.',
            'requirements' => [
                'Strong spreadsheet and process-tracking skills',
                'Comfort with fast-moving operations environments',
                'Willingness to work rotational shifts'
            ],
            'skills' => ['Operations', 'SLA Tracking', 'Excel'],
            'featured' => false,
            'created_at' => '2026-03-20T09:20:00+05:30'
        ],
        [
            'id' => 'job_hireloop_07',
            'title' => 'Telecalling Executive',
            'company' => 'BlueNest Connect',
            'location' => 'Jaipur',
            'salary' => 'INR 2.8L - 4L',
            'type' => 'Full-time',
            'category' => 'Telecalling / BPO / Telesales',
            'experience' => '0-1 years',
            'description' => 'Call warm leads, explain plans clearly, and help the sales team convert interest into booked demos and follow-ups.',
            'requirements' => [
                'Confident Hindi communication',
                'Basic CRM data entry',
                'Comfort with daily call targets'
            ],
            'skills' => ['Telecalling', 'Lead Follow-up', 'CRM'],
            'featured' => true,
            'created_at' => '2026-03-22T10:05:00+05:30'
        ],
        [
            'id' => 'job_hireloop_08',
            'title' => 'Delivery Associate (Part-time)',
            'company' => 'GoFleet',
            'location' => 'Chennai',
            'salary' => 'INR 18,000 - 28,000 / month',
            'type' => 'Part-time',
            'category' => 'Delivery / Driver / Logistics',
            'experience' => '0-2 years',
            'description' => 'Deliver nearby orders in flexible shifts, manage proof-of-delivery updates, and maintain a strong on-time completion rate.',
            'requirements' => [
                'Valid driving license',
                'Own smartphone and bike',
                'Willing to work flexible shifts'
            ],
            'skills' => ['Delivery Ops', 'Route Handling', 'Customer Service'],
            'featured' => false,
            'created_at' => '2026-03-22T09:25:00+05:30'
        ],
        [
            'id' => 'job_hireloop_09',
            'title' => 'Back Office Executive',
            'company' => 'LedgerLane',
            'location' => 'Lucknow',
            'salary' => 'INR 2.6L - 3.8L',
            'type' => 'Full-time',
            'category' => 'Admin / Back Office / Computer Operator',
            'experience' => '0-2 years',
            'description' => 'Maintain records, support invoice processing, and keep backend data updated for the operations and finance teams.',
            'requirements' => [
                'Strong typing and spreadsheet skills',
                'Attention to detail',
                'Comfort with repetitive documentation tasks'
            ],
            'skills' => ['Data Entry', 'MS Excel', 'Documentation'],
            'featured' => false,
            'created_at' => '2026-03-22T08:55:00+05:30'
        ],
        [
            'id' => 'job_hireloop_10',
            'title' => 'Night Shift Customer Support',
            'company' => 'NovaCare',
            'location' => 'Gurugram',
            'salary' => 'INR 3.6L - 5.2L',
            'type' => 'Night Shift',
            'category' => 'Customer Support',
            'experience' => '1-3 years',
            'description' => 'Handle inbound night-shift support queues, solve customer issues, and coordinate with the day team for detailed escalations.',
            'requirements' => [
                'Prior customer support experience',
                'Comfort with night shift timings',
                'Clear spoken and written English'
            ],
            'skills' => ['Voice Support', 'Escalation Handling', 'Ticketing'],
            'featured' => true,
            'created_at' => '2026-03-22T08:20:00+05:30'
        ],
        [
            'id' => 'job_hireloop_11',
            'title' => 'Field Sales Executive',
            'company' => 'UrbanReach',
            'location' => 'Ahmedabad',
            'salary' => 'INR 3.8L - 6L',
            'type' => 'Full-time',
            'category' => 'Sales & BD',
            'experience' => '1-3 years',
            'description' => 'Meet local businesses, pitch subscription plans, and grow the pipeline through daily outbound and field activity.',
            'requirements' => [
                'Field sales experience preferred',
                'Strong persuasion skills',
                'Comfort with target-based incentives'
            ],
            'skills' => ['Field Sales', 'Prospecting', 'Negotiation'],
            'featured' => false,
            'created_at' => '2026-03-21T20:10:00+05:30'
        ],
        [
            'id' => 'job_hireloop_12',
            'title' => 'Software / Web Developer',
            'company' => 'DevHarbor',
            'location' => 'Noida',
            'salary' => 'INR 6.5L - 10L',
            'type' => 'Hybrid',
            'category' => 'Software / Web Developer',
            'experience' => '1-4 years',
            'description' => 'Build internal dashboards, ship product-facing UI, and support API integrations across web workflows and admin tools.',
            'requirements' => [
                'Strong HTML, CSS, JavaScript basics',
                'PHP or Laravel familiarity preferred',
                'Version control and debugging comfort'
            ],
            'skills' => ['PHP', 'JavaScript', 'Frontend'],
            'featured' => true,
            'created_at' => '2026-03-21T17:35:00+05:30'
        ]
    ];
}

function default_applications(): array
{
    return [
        [
            'id' => 'app_hireloop_01',
            'job_id' => 'job_hireloop_02',
            'job_title' => 'Customer Support Specialist',
            'company' => 'SwiftPe',
            'candidate_name' => 'Ananya Sharma',
            'email' => 'ananya.sharma@example.com',
            'phone' => '9876543210',
            'city' => 'Jaipur',
            'experience' => '2 years in customer support',
            'summary' => 'Handled merchant support for a payments startup and consistently maintained high CSAT scores.',
            'status' => 'Reviewing',
            'applied_at' => '2026-03-22T08:25:00+05:30'
        ],
        [
            'id' => 'app_hireloop_02',
            'job_id' => 'job_hireloop_05',
            'job_title' => 'Laravel Developer',
            'company' => 'StackMint',
            'candidate_name' => 'Rahul Verma',
            'email' => 'rahul.verma@example.com',
            'phone' => '9123456780',
            'city' => 'Hyderabad',
            'experience' => '3 years building internal tools and API services',
            'summary' => 'Worked on Laravel dashboards, auth flows, and performance tuning for a logistics platform.',
            'status' => 'Shortlisted',
            'applied_at' => '2026-03-21T19:40:00+05:30'
        ],
        [
            'id' => 'app_hireloop_03',
            'job_id' => 'job_hireloop_01',
            'job_title' => 'Growth Marketing Manager',
            'company' => 'OrbitHive',
            'candidate_name' => 'Sneha Iyer',
            'email' => 'sneha.iyer@example.com',
            'phone' => '9988776655',
            'city' => 'Bengaluru',
            'experience' => '4 years across B2B demand generation and lifecycle campaigns',
            'summary' => 'Scaled paid, organic, and CRM campaigns with a strong focus on experimentation and reporting.',
            'status' => 'New',
            'applied_at' => '2026-03-21T10:15:00+05:30'
        ],
        [
            'id' => 'app_hireloop_04',
            'job_id' => 'job_hireloop_10',
            'job_title' => 'Night Shift Customer Support',
            'company' => 'NovaCare',
            'candidate_name' => 'Aakash Kumar',
            'email' => 'aakash.kumar@example.com',
            'phone' => '9001122334',
            'city' => 'Gurugram',
            'experience' => '18 months in customer support and ticket handling',
            'summary' => 'Comfortable with rotational and night shift support processes across voice and chat queues.',
            'status' => 'Reviewing',
            'applied_at' => '2026-03-22T09:40:00+05:30'
        ],
        [
            'id' => 'app_hireloop_05',
            'job_id' => 'job_hireloop_07',
            'job_title' => 'Telecalling Executive',
            'company' => 'BlueNest Connect',
            'candidate_name' => 'Priya Soni',
            'email' => 'priya.soni@example.com',
            'phone' => '9811223344',
            'city' => 'Jaipur',
            'experience' => '6 months of telesales and customer follow-up work',
            'summary' => 'Handled high-volume outbound calling and lead qualification for education and insurance campaigns.',
            'status' => 'Shortlisted',
            'applied_at' => '2026-03-22T09:10:00+05:30'
        ]
    ];
}

function initialize_storage(): void
{
    if (!is_dir(dirname(STORAGE_JOBS))) {
        mkdir(dirname(STORAGE_JOBS), 0777, true);
    }

    if (!file_exists(STORAGE_JOBS)) {
        write_json_file(STORAGE_JOBS, default_jobs());
    }

    if (!file_exists(STORAGE_APPLICATIONS)) {
        write_json_file(STORAGE_APPLICATIONS, default_applications());
    }

    refresh_seo_assets(load_jobs());
}

function read_json_file(string $path, array $fallback): array
{
    if (!file_exists($path)) {
        return $fallback;
    }

    $raw = file_get_contents($path);

    if ($raw === false || trim($raw) === '') {
        return $fallback;
    }

    $decoded = json_decode($raw, true);

    return is_array($decoded) ? $decoded : $fallback;
}

function write_json_file(string $path, array $records): bool
{
    $encoded = json_encode($records, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

    if ($encoded === false) {
        return false;
    }

    return file_put_contents($path, $encoded . PHP_EOL, LOCK_EX) !== false;
}

function sort_records_by_date(array $records, string $field): array
{
    usort($records, function (array $left, array $right) use ($field): int {
        return strtotime((string) ($right[$field] ?? '')) <=> strtotime((string) ($left[$field] ?? ''));
    });

    return $records;
}

function normalize_job_record(array $job): array
{
    $status = strtolower(trim((string) ($job['status'] ?? 'active')));

    if (!in_array($status, ['active', 'inactive'], true)) {
        $status = 'active';
    }

    $job['status'] = $status;

    return $job;
}

function job_status(array $job): string
{
    return normalize_job_record($job)['status'];
}

function is_job_active(array $job): bool
{
    return job_status($job) === 'active';
}

function filter_active_jobs(array $jobs): array
{
    $active = [];

    foreach ($jobs as $job) {
        $record = normalize_job_record($job);

        if (is_job_active($record)) {
            $active[] = $record;
        }
    }

    return array_values($active);
}

function load_jobs(bool $includeInactive = false): array
{
    $records = read_json_file(STORAGE_JOBS, default_jobs());
    $normalized = [];

    foreach ($records as $record) {
        if (is_array($record)) {
            $normalized[] = normalize_job_record($record);
        }
    }

    $sorted = sort_records_by_date($normalized, 'created_at');

    return $includeInactive ? $sorted : filter_active_jobs($sorted);
}

function load_applications(): array
{
    return sort_records_by_date(read_json_file(STORAGE_APPLICATIONS, default_applications()), 'applied_at');
}

function job_form_defaults(): array
{
    return [
        'job_id' => '',
        'title' => '',
        'company' => '',
        'location' => '',
        'salary' => '',
        'type' => '',
        'category' => '',
        'experience' => '',
        'description' => '',
        'requirements' => '',
        'skills' => ''
    ];
}

function application_form_defaults(): array
{
    return [
        'job_id' => '',
        'name' => '',
        'email' => '',
        'phone' => '',
        'city' => '',
        'experience' => '',
        'summary' => ''
    ];
}

function employer_login_defaults(): array
{
    return [
        'email' => '',
        'password' => ''
    ];
}

function collect_job_form_data(array $source): array
{
    $data = job_form_defaults();

    foreach (array_keys($data) as $key) {
        $data[$key] = trim((string) ($source[$key] ?? ''));
    }

    return $data;
}

function stringify_list(array $items, string $separator = PHP_EOL): string
{
    $clean = [];

    foreach ($items as $item) {
        $value = trim((string) $item);

        if ($value !== '') {
            $clean[] = $value;
        }
    }

    return implode($separator, $clean);
}

function job_form_data_from_job(array $job): array
{
    return [
        'job_id' => (string) ($job['id'] ?? ''),
        'title' => (string) ($job['title'] ?? ''),
        'company' => (string) ($job['company'] ?? ''),
        'location' => (string) ($job['location'] ?? ''),
        'salary' => (string) ($job['salary'] ?? ''),
        'type' => (string) ($job['type'] ?? ''),
        'category' => (string) ($job['category'] ?? ''),
        'experience' => (string) ($job['experience'] ?? ''),
        'description' => (string) ($job['description'] ?? ''),
        'requirements' => stringify_list($job['requirements'] ?? []),
        'skills' => stringify_list($job['skills'] ?? [], ', ')
    ];
}

function collect_application_form_data(array $source): array
{
    $data = application_form_defaults();

    foreach (array_keys($data) as $key) {
        $data[$key] = trim((string) ($source[$key] ?? ''));
    }

    return $data;
}

function collect_employer_login_data(array $source): array
{
    $data = employer_login_defaults();
    $data['email'] = strtolower(trim((string) ($source['email'] ?? '')));
    $data['password'] = trim((string) ($source['password'] ?? ''));

    return $data;
}

function parse_list(string $value): array
{
    $parts = preg_split('/\r\n|\r|\n|,/', $value) ?: [];
    $clean = [];

    foreach ($parts as $part) {
        $item = trim((string) $part);

        if ($item !== '') {
            $clean[] = $item;
        }
    }

    return array_values(array_unique($clean));
}

function employer_credentials(): array
{
    $email = trim((string) getenv('EMPLOYER_EMAIL'));
    $password = trim((string) getenv('EMPLOYER_PASSWORD'));
    $name = trim((string) getenv('EMPLOYER_NAME'));

    return [
        'email' => $email !== '' ? strtolower($email) : EMPLOYER_DEFAULT_EMAIL,
        'password' => $password !== '' ? $password : EMPLOYER_DEFAULT_PASSWORD,
        'name' => $name !== '' ? $name : EMPLOYER_DEFAULT_NAME
    ];
}

function employer_uses_default_credentials(): bool
{
    return trim((string) getenv('EMPLOYER_EMAIL')) === '' && trim((string) getenv('EMPLOYER_PASSWORD')) === '';
}

function normalize_employer_redirect_page(string $page): string
{
    return in_array($page, ['post-job', 'dashboard'], true) ? $page : 'post-job';
}

function employer_login_url(string $redirectPage = 'post-job'): string
{
    return build_url([
        'page' => 'employer-login',
        'redirect' => normalize_employer_redirect_page($redirectPage)
    ]);
}

function validate_employer_login(array $input): array
{
    $errors = [];

    if (!filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Enter a valid employer email address.';
    }

    if ($input['password'] === '') {
        $errors[] = 'Password is required.';
    }

    return $errors;
}

function employer_logged_in(): bool
{
    return isset($_SESSION['employer']) && is_array($_SESSION['employer']);
}

function current_employer(): ?array
{
    return employer_logged_in() ? $_SESSION['employer'] : null;
}

function authenticate_employer(array $input): bool
{
    $credentials = employer_credentials();

    return strtolower($input['email']) === strtolower((string) $credentials['email'])
        && hash_equals((string) $credentials['password'], (string) $input['password']);
}

function login_employer_session(): void
{
    $credentials = employer_credentials();
    $_SESSION['employer'] = [
        'name' => (string) $credentials['name'],
        'email' => (string) $credentials['email']
    ];
}

function logout_employer_session(): void
{
    unset($_SESSION['employer']);
}

function validate_job_form(array $input): array
{
    $errors = [];

    if ($input['title'] === '') {
        $errors[] = 'Job title is required.';
    }

    if ($input['company'] === '') {
        $errors[] = 'Company name is required.';
    }

    if ($input['location'] === '') {
        $errors[] = 'Location is required.';
    }

    if ($input['salary'] === '') {
        $errors[] = 'Salary range is required.';
    }

    if ($input['type'] === '') {
        $errors[] = 'Select a job type.';
    }

    if ($input['category'] === '') {
        $errors[] = 'Category is required.';
    }

    if ($input['experience'] === '') {
        $errors[] = 'Experience band is required.';
    }

    if (strlen($input['description']) < 40) {
        $errors[] = 'Description should be at least 40 characters.';
    }

    if (count(parse_list($input['requirements'])) === 0) {
        $errors[] = 'Add at least one requirement.';
    }

    return $errors;
}

function validate_application_form(array $input, array $jobs): array
{
    $errors = [];

    if ($input['job_id'] === '' || find_job_by_id($jobs, $input['job_id']) === null) {
        $errors[] = 'Select a valid job before applying.';
    }

    if ($input['name'] === '') {
        $errors[] = 'Your name is required.';
    }

    if (strlen(preg_replace('/\D+/', '', $input['phone'])) < 10) {
        $errors[] = 'Enter a valid phone number.';
    }

    if ($input['city'] === '') {
        $errors[] = 'City is required.';
    }

    return $errors;
}

function find_job_by_id(array $jobs, string $jobId): ?array
{
    foreach ($jobs as $job) {
        if (($job['id'] ?? '') === $jobId) {
            return $job;
        }
    }

    return null;
}

function save_job(array $input): bool
{
    $jobs = load_jobs(true);
    array_unshift($jobs, [
        'id' => 'job_' . bin2hex(random_bytes(5)),
        'title' => $input['title'],
        'company' => $input['company'],
        'location' => $input['location'],
        'salary' => $input['salary'],
        'type' => $input['type'],
        'category' => $input['category'],
        'experience' => $input['experience'],
        'description' => $input['description'],
        'requirements' => parse_list($input['requirements']),
        'skills' => parse_list($input['skills']),
        'status' => 'active',
        'featured' => false,
        'created_at' => date(DATE_ATOM)
    ]);

    $saved = write_json_file(STORAGE_JOBS, $jobs);

    if ($saved) {
        refresh_seo_assets($jobs);
    }

    return $saved;
}

function update_job(array $input): bool
{
    $jobs = load_jobs(true);
    $updated = false;

    foreach ($jobs as $index => $job) {
        if (($job['id'] ?? '') !== $input['job_id']) {
            continue;
        }

        $jobs[$index] = array_merge($job, [
            'title' => $input['title'],
            'company' => $input['company'],
            'location' => $input['location'],
            'salary' => $input['salary'],
            'type' => $input['type'],
            'category' => $input['category'],
            'experience' => $input['experience'],
            'description' => $input['description'],
            'requirements' => parse_list($input['requirements']),
            'skills' => parse_list($input['skills']),
            'updated_at' => date(DATE_ATOM)
        ]);
        $updated = true;
        break;
    }

    if (!$updated) {
        return false;
    }

    $saved = write_json_file(STORAGE_JOBS, $jobs);

    if ($saved) {
        refresh_seo_assets($jobs);
    }

    return $saved;
}

function set_job_status(string $jobId, string $status): bool
{
    $normalizedStatus = strtolower(trim($status));

    if (!in_array($normalizedStatus, ['active', 'inactive'], true)) {
        return false;
    }

    $jobs = load_jobs(true);
    $updated = false;

    foreach ($jobs as $index => $job) {
        if (($job['id'] ?? '') !== $jobId) {
            continue;
        }

        $jobs[$index] = array_merge($job, [
            'status' => $normalizedStatus,
            'updated_at' => date(DATE_ATOM)
        ]);
        $updated = true;
        break;
    }

    if (!$updated) {
        return false;
    }

    $saved = write_json_file(STORAGE_JOBS, $jobs);

    if ($saved) {
        refresh_seo_assets($jobs);
    }

    return $saved;
}

function delete_job(string $jobId): bool
{
    $jobs = load_jobs(true);
    $remaining = array_values(array_filter($jobs, function (array $job) use ($jobId): bool {
        return ($job['id'] ?? '') !== $jobId;
    }));

    if (count($remaining) === count($jobs)) {
        return false;
    }

    $saved = write_json_file(STORAGE_JOBS, $remaining);

    if ($saved) {
        refresh_seo_assets($remaining);
    }

    return $saved;
}

function save_application(array $input, array $jobs): bool
{
    $job = find_job_by_id($jobs, $input['job_id']);

    if ($job === null) {
        return false;
    }

    $applications = load_applications();
    array_unshift($applications, [
        'id' => 'app_' . bin2hex(random_bytes(5)),
        'job_id' => $job['id'],
        'job_title' => $job['title'],
        'company' => $job['company'],
        'candidate_name' => $input['name'],
        'email' => $input['email'],
        'phone' => $input['phone'],
        'city' => $input['city'],
        'experience' => $input['experience'],
        'summary' => $input['summary'],
        'status' => 'New',
        'applied_at' => date(DATE_ATOM)
    ]);

    return write_json_file(STORAGE_APPLICATIONS, $applications);
}

function normalize_filters(array $source): array
{
    return [
        'keyword' => trim((string) ($source['keyword'] ?? '')),
        'location' => trim((string) ($source['location'] ?? '')),
        'category' => trim((string) ($source['category'] ?? '')),
        'type' => trim((string) ($source['type'] ?? ''))
    ];
}

function job_matches_keyword(array $job, string $keyword): bool
{
    if ($keyword === '') {
        return true;
    }

    $haystack = strtolower(implode(' ', [
        (string) ($job['title'] ?? ''),
        (string) ($job['company'] ?? ''),
        (string) ($job['location'] ?? ''),
        (string) ($job['description'] ?? ''),
        (string) ($job['category'] ?? ''),
        implode(' ', $job['requirements'] ?? []),
        implode(' ', $job['skills'] ?? [])
    ]));

    return strpos($haystack, strtolower($keyword)) !== false;
}

function filter_jobs(array $jobs, array $filters): array
{
    return array_values(array_filter($jobs, function (array $job) use ($filters): bool {
        if (!job_matches_keyword($job, $filters['keyword'])) {
            return false;
        }

        if ($filters['location'] !== '' && strtolower((string) ($job['location'] ?? '')) !== strtolower($filters['location'])) {
            return false;
        }

        if ($filters['category'] !== '' && strtolower((string) ($job['category'] ?? '')) !== strtolower($filters['category'])) {
            return false;
        }

        if ($filters['type'] !== '' && strtolower((string) ($job['type'] ?? '')) !== strtolower($filters['type'])) {
            return false;
        }

        return true;
    }));
}

function featured_jobs(array $jobs, int $limit = 3): array
{
    $featured = array_values(array_filter($jobs, function (array $job): bool {
        return !empty($job['featured']);
    }));

    if ($featured === []) {
        $featured = $jobs;
    }

    return array_slice($featured, 0, $limit);
}

function unique_job_values(array $jobs, string $field): array
{
    $values = [];

    foreach ($jobs as $job) {
        $value = trim((string) ($job[$field] ?? ''));

        if ($value !== '') {
            $values[] = $value;
        }
    }

    $values = array_values(array_unique($values));
    sort($values);

    return $values;
}

function value_breakdown(array $records, string $field): array
{
    $counts = [];

    foreach ($records as $record) {
        $label = trim((string) ($record[$field] ?? ''));

        if ($label === '') {
            continue;
        }

        if (!isset($counts[$label])) {
            $counts[$label] = 0;
        }

        $counts[$label]++;
    }

    arsort($counts);

    $result = [];
    foreach ($counts as $label => $count) {
        $result[] = [
            'label' => $label,
            'count' => $count
        ];
    }

    return $result;
}

function build_dashboard(array $jobs, array $applications): array
{
    $companies = unique_job_values($jobs, 'company');
    $remoteCount = 0;

    foreach ($jobs as $job) {
        $type = strtolower((string) ($job['type'] ?? ''));
        $location = strtolower((string) ($job['location'] ?? ''));

        if ($type === 'remote' || $location === 'remote') {
            $remoteCount++;
        }
    }

    $categories = value_breakdown($jobs, 'category');
    $types = value_breakdown($jobs, 'type');

    return [
        'jobs_count' => count($jobs),
        'applications_count' => count($applications),
        'companies_count' => count($companies),
        'remote_count' => $remoteCount,
        'featured_count' => count(array_filter($jobs, function (array $job): bool {
            return !empty($job['featured']);
        })),
        'apply_per_role' => count($jobs) > 0 ? number_format(count($applications) / count($jobs), 1) : '0.0',
        'hottest_category' => $categories[0]['label'] ?? 'None yet',
        'categories' => $categories,
        'types' => $types,
        'recent_jobs' => array_slice($jobs, 0, 5),
        'recent_applications' => array_slice($applications, 0, 5)
    ];
}

function applications_for_job(array $applications, string $jobId): int
{
    $count = 0;

    foreach ($applications as $application) {
        if (($application['job_id'] ?? '') === $jobId) {
            $count++;
        }
    }

    return $count;
}

function human_time_diff(string $dateTime): string
{
    $timestamp = strtotime($dateTime);

    if ($timestamp === false) {
        return 'Recently';
    }

    $diff = max(0, time() - $timestamp);

    if ($diff < 3600) {
        return max(1, (int) floor($diff / 60)) . 'm ago';
    }

    if ($diff < 86400) {
        return (int) floor($diff / 3600) . 'h ago';
    }

    if ($diff < 604800) {
        return (int) floor($diff / 86400) . 'd ago';
    }

    return date('d M Y', $timestamp);
}

function format_portal_datetime(string $dateTime): string
{
    $timestamp = strtotime($dateTime);

    if ($timestamp === false) {
        return 'Recently';
    }

    return date('d M Y, h:i A', $timestamp);
}

function excel_cell(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function output_applications_excel(array $applications): void
{
    $filename = 'nbss-hr-services-applications-' . date('Y-m-d-H-i') . '.xls';

    header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Pragma: no-cache');
    header('Expires: 0');

    echo "\xEF\xBB\xBF";
    ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>NBSS HR Services Applications Export</title>
</head>
<body>
    <table border="1">
        <thead>
            <tr>
                <th>Candidate Name</th>
                <th>Phone</th>
                <th>Email</th>
                <th>City</th>
                <th>Job Title</th>
                <th>Company</th>
                <th>Status</th>
                <th>Experience</th>
                <th>Summary</th>
                <th>Applied At</th>
                <th>Job ID</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($applications as $application): ?>
                <tr>
                    <td><?php echo excel_cell((string) ($application['candidate_name'] ?? '')); ?></td>
                    <td><?php echo excel_cell((string) ($application['phone'] ?? '')); ?></td>
                    <td><?php echo excel_cell(!empty($application['email']) ? (string) $application['email'] : 'Email not collected'); ?></td>
                    <td><?php echo excel_cell((string) ($application['city'] ?? '')); ?></td>
                    <td><?php echo excel_cell((string) ($application['job_title'] ?? '')); ?></td>
                    <td><?php echo excel_cell((string) ($application['company'] ?? '')); ?></td>
                    <td><?php echo excel_cell((string) ($application['status'] ?? '')); ?></td>
                    <td><?php echo excel_cell((string) ($application['experience'] ?? '')); ?></td>
                    <td><?php echo excel_cell((string) ($application['summary'] ?? '')); ?></td>
                    <td><?php echo excel_cell(format_portal_datetime((string) ($application['applied_at'] ?? ''))); ?></td>
                    <td><?php echo excel_cell((string) ($application['job_id'] ?? '')); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>
    <?php
}

function site_base_url(): string
{
    $appUrl = trim((string) getenv('APP_URL'));

    if ($appUrl !== '' && filter_var($appUrl, FILTER_VALIDATE_URL)) {
        return rtrim($appUrl, '/');
    }

    $host = trim((string) ($_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? ''));

    if ($host !== '') {
        $isHttps = (!empty($_SERVER['HTTPS']) && strtolower((string) $_SERVER['HTTPS']) !== 'off')
            || (isset($_SERVER['REQUEST_SCHEME']) && strtolower((string) $_SERVER['REQUEST_SCHEME']) === 'https')
            || (isset($_SERVER['SERVER_PORT']) && (string) $_SERVER['SERVER_PORT'] === '443');

        return ($isHttps ? 'https' : 'http') . '://' . $host;
    }

    return SITE_DEFAULT_URL;
}

function absolute_url(string $path = '', array $params = [], string $anchor = ''): string
{
    $base = rtrim(site_base_url(), '/');
    $url = ($path === '' || $path === '/') ? $base . '/' : $base . '/' . ltrim($path, '/');
    $filtered = [];

    foreach ($params as $key => $value) {
        if ($value !== '' && $value !== null) {
            $filtered[$key] = $value;
        }
    }

    $query = http_build_query($filtered);

    if ($query !== '') {
        $url .= '?' . $query;
    }

    if ($anchor !== '') {
        $url .= $anchor;
    }

    return $url;
}

function seo_text(string $text, int $limit = 160): string
{
    $clean = trim(preg_replace('/\s+/', ' ', strip_tags($text)) ?? '');

    if ($clean === '') {
        return '';
    }

    if (function_exists('mb_strlen') && function_exists('mb_substr')) {
        if (mb_strlen($clean) <= $limit) {
            return $clean;
        }

        return rtrim(mb_substr($clean, 0, max(0, $limit - 1))) . '…';
    }

    if (strlen($clean) <= $limit) {
        return $clean;
    }

    return rtrim(substr($clean, 0, max(0, $limit - 1))) . '…';
}

function has_active_filters(array $filters): bool
{
    foreach ($filters as $value) {
        if (trim((string) $value) !== '') {
            return true;
        }
    }

    return false;
}

function employment_type_schema_value(string $type): ?string
{
    switch (strtolower(trim($type))) {
        case 'full-time':
            return 'FULL_TIME';
        case 'part-time':
            return 'PART_TIME';
        case 'contract':
            return 'CONTRACTOR';
        case 'internship':
            return 'INTERN';
        case 'temporary':
            return 'TEMPORARY';
        default:
            return null;
    }
}

function organization_schema(): array
{
    return [
        '@context' => 'https://schema.org',
        '@type' => 'Organization',
        'name' => SITE_NAME,
        'url' => absolute_url(),
        'logo' => absolute_url('assets/seo-share.svg'),
        'description' => SITE_DEFAULT_DESCRIPTION
    ];
}

function website_schema(): array
{
    return [
        '@context' => 'https://schema.org',
        '@type' => 'WebSite',
        'name' => SITE_NAME,
        'url' => absolute_url(),
        'description' => SITE_DEFAULT_DESCRIPTION,
        'potentialAction' => [
            '@type' => 'SearchAction',
            'target' => absolute_url('index.php', ['keyword' => '{search_term_string}']),
            'query-input' => 'required name=search_term_string'
        ]
    ];
}

function webpage_schema(string $title, string $description, string $url): array
{
    return [
        '@context' => 'https://schema.org',
        '@type' => 'WebPage',
        'name' => $title,
        'description' => $description,
        'url' => $url,
        'isPartOf' => [
            '@type' => 'WebSite',
            'name' => SITE_NAME,
            'url' => absolute_url()
        ]
    ];
}

function item_list_schema(array $jobs, string $name): array
{
    $items = [];
    $position = 1;

    foreach (array_slice($jobs, 0, 10) as $job) {
        $items[] = [
            '@type' => 'ListItem',
            'position' => $position,
            'name' => trim((string) ($job['title'] ?? '')) . ' at ' . trim((string) ($job['company'] ?? SITE_NAME)),
            'url' => absolute_url('index.php', ['job' => (string) ($job['id'] ?? '')])
        ];
        $position++;
    }

    return [
        '@context' => 'https://schema.org',
        '@type' => 'ItemList',
        'name' => $name,
        'itemListElement' => $items
    ];
}

function job_posting_schema(array $job): array
{
    $schema = [
        '@context' => 'https://schema.org',
        '@type' => 'JobPosting',
        'title' => (string) ($job['title'] ?? ''),
        'description' => seo_text((string) ($job['description'] ?? ''), 500),
        'datePosted' => (string) ($job['created_at'] ?? date(DATE_ATOM)),
        'identifier' => [
            '@type' => 'PropertyValue',
            'name' => SITE_NAME,
            'value' => (string) ($job['id'] ?? '')
        ],
        'hiringOrganization' => [
            '@type' => 'Organization',
            'name' => (string) ($job['company'] ?? SITE_NAME),
            'sameAs' => absolute_url(),
            'logo' => absolute_url('assets/seo-share.svg')
        ],
        'industry' => (string) ($job['category'] ?? ''),
        'skills' => implode(', ', $job['skills'] ?? []),
        'qualifications' => implode('. ', $job['requirements'] ?? []),
        'directApply' => true,
        'url' => absolute_url('index.php', ['job' => (string) ($job['id'] ?? '')])
    ];

    $employmentType = employment_type_schema_value((string) ($job['type'] ?? ''));

    if ($employmentType !== null) {
        $schema['employmentType'] = $employmentType;
    }

    $location = trim((string) ($job['location'] ?? ''));

    if (strtolower($location) === 'remote') {
        $schema['jobLocationType'] = 'TELECOMMUTE';
        $schema['applicantLocationRequirements'] = [
            '@type' => 'Country',
            'name' => 'India'
        ];
    } elseif ($location !== '') {
        $schema['jobLocation'] = [
            '@type' => 'Place',
            'address' => [
                '@type' => 'PostalAddress',
                'addressLocality' => $location,
                'addressCountry' => 'IN'
            ]
        ];
    }

    return $schema;
}

function filter_label(array $filters): string
{
    $parts = [];

    foreach (['keyword', 'category', 'type', 'location'] as $key) {
        $value = trim((string) ($filters[$key] ?? ''));

        if ($value !== '') {
            $parts[] = $value;
        }
    }

    return implode(' · ', $parts);
}

function build_seo_metadata(string $page, array $context = []): array
{
    $requestedJob = $context['requested_job'] ?? null;
    $modalJob = $context['modal_job'] ?? null;
    $filteredJobs = $context['filtered_jobs'] ?? [];
    $filters = $context['filters'] ?? [];
    $dashboard = $context['dashboard'] ?? [];
    $applyRequested = !empty($context['apply_requested']);
    $hasFilters = has_active_filters($filters);
    $image = absolute_url('assets/seo-share.svg');
    $title = SITE_NAME . ' | Jobs in India and Hiring Portal';
    $description = SITE_DEFAULT_DESCRIPTION;
    $canonical = absolute_url();
    $robots = 'index,follow,max-image-preview:large,max-snippet:-1,max-video-preview:-1';
    $ogType = 'website';
    $publishedTime = '';
    $schemas = [
        organization_schema(),
        website_schema()
    ];

    if ($page === 'employer-login') {
        $title = 'Employer Login | ' . SITE_NAME;
        $description = 'Sign in as an employer to access the NBSS HR Services job posting page and dashboard.';
        $canonical = absolute_url('index.php', ['page' => 'employer-login']);
        $robots = 'noindex,follow,max-image-preview:large,max-snippet:-1,max-video-preview:-1';
        $schemas[] = webpage_schema($title, $description, $canonical);
    } elseif ($page === 'post-job') {
        $title = 'Post a Job on ' . SITE_NAME;
        $description = 'Publish roles quickly on NBSS HR Services and keep your hiring workflow simple with the built-in employer posting form.';
        $canonical = absolute_url('index.php', ['page' => 'post-job']);
        $robots = 'noindex,follow,max-image-preview:large,max-snippet:-1,max-video-preview:-1';
        $schemas[] = webpage_schema($title, $description, $canonical);
    } elseif ($page === 'dashboard') {
        $title = 'Hiring Dashboard | ' . SITE_NAME;
        $description = 'Track job openings, application volume, and hiring activity from the NBSS HR Services dashboard.';
        $canonical = absolute_url('index.php', ['page' => 'dashboard']);
        $robots = 'noindex,follow,max-image-preview:large,max-snippet:-1,max-video-preview:-1';
        $schemas[] = webpage_schema($title, $description, $canonical);
    } elseif (is_array($requestedJob)) {
        $title = trim((string) $requestedJob['title']) . ' at ' . trim((string) $requestedJob['company']) . ' in ' . trim((string) $requestedJob['location']) . ' | ' . SITE_NAME;
        $description = seo_text(
            'Apply for ' . trim((string) $requestedJob['title']) . ' at ' . trim((string) $requestedJob['company']) . ' in ' . trim((string) $requestedJob['location']) . '. ' . (string) ($requestedJob['description'] ?? ''),
            160
        );
        $canonical = absolute_url('index.php', ['job' => (string) $requestedJob['id']]);
        $ogType = 'article';
        $publishedTime = (string) ($requestedJob['created_at'] ?? '');
        $schemas[] = webpage_schema($title, $description, $canonical);
        $schemas[] = job_posting_schema($requestedJob);
    } elseif ($hasFilters) {
        $filterLabel = filter_label($filters);
        $count = count($filteredJobs);
        $title = ($count > 0 ? $count . ' ' : '') . 'Filtered Jobs' . ($filterLabel !== '' ? ' for ' . $filterLabel : '') . ' | ' . SITE_NAME;
        $description = seo_text('Explore filtered jobs on NBSS HR Services' . ($filterLabel !== '' ? ' for ' . $filterLabel : '') . '. Browse current openings and switch filters to discover more roles across India.', 160);
        $canonical = absolute_url();
        $robots = 'noindex,follow,max-image-preview:large,max-snippet:-1,max-video-preview:-1';
        $schemas[] = webpage_schema($title, $description, $canonical);

        if ($filteredJobs !== []) {
            $schemas[] = item_list_schema($filteredJobs, 'Filtered jobs on ' . SITE_NAME);
        }
    } else {
        $jobsCount = (string) ($dashboard['jobs_count'] ?? count($filteredJobs));
        $title = 'Jobs in India | ' . SITE_NAME . ' Job Search Portal';
        $description = seo_text('Discover ' . $jobsCount . ' active job openings across support, sales, logistics, marketing, design, and software teams on NBSS HR Services.', 160);
        $canonical = absolute_url();
        $schemas[] = webpage_schema($title, $description, $canonical);

        if ($filteredJobs !== []) {
            $schemas[] = item_list_schema($filteredJobs, 'Latest jobs on ' . SITE_NAME);
        }
    }

    if ($applyRequested) {
        $canonicalJob = is_array($requestedJob) ? $requestedJob : (is_array($modalJob) ? $modalJob : null);
        $canonical = is_array($canonicalJob)
            ? absolute_url('index.php', ['job' => (string) $canonicalJob['id']])
            : absolute_url();
        $robots = 'noindex,follow,max-image-preview:large,max-snippet:-1,max-video-preview:-1';
    }

    return [
        'title' => $title,
        'description' => $description,
        'canonical' => $canonical,
        'robots' => $robots,
        'og_type' => $ogType,
        'image' => $image,
        'image_alt' => SITE_NAME . ' job portal preview',
        'published_time' => $publishedTime,
        'schemas' => array_values(array_filter($schemas, function ($schema): bool {
            return is_array($schema) && $schema !== [];
        }))
    ];
}

function job_last_modified(array $job): string
{
    return (string) ($job['updated_at'] ?? $job['created_at'] ?? date(DATE_ATOM));
}

function generate_sitemap_xml(array $jobs): string
{
    $entries = [
        [
            'loc' => absolute_url(),
            'lastmod' => isset($jobs[0]) ? job_last_modified($jobs[0]) : date(DATE_ATOM)
        ]
    ];

    foreach ($jobs as $job) {
        $entries[] = [
            'loc' => absolute_url('index.php', ['job' => (string) ($job['id'] ?? '')]),
            'lastmod' => job_last_modified($job)
        ];
    }

    $xml = ['<?xml version="1.0" encoding="UTF-8"?>'];
    $xml[] = '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

    foreach ($entries as $entry) {
        $xml[] = '  <url>';
        $xml[] = '    <loc>' . htmlspecialchars((string) $entry['loc'], ENT_XML1 | ENT_QUOTES, 'UTF-8') . '</loc>';
        $xml[] = '    <lastmod>' . htmlspecialchars((string) $entry['lastmod'], ENT_XML1 | ENT_QUOTES, 'UTF-8') . '</lastmod>';
        $xml[] = '  </url>';
    }

    $xml[] = '</urlset>';

    return implode(PHP_EOL, $xml) . PHP_EOL;
}

function generate_robots_txt(): string
{
    $lines = [
        'User-agent: *',
        'Allow: /',
        'Disallow: /index.php?page=employer-login',
        'Disallow: /index.php?page=dashboard',
        'Disallow: /index.php?page=post-job',
        'Disallow: /*apply=1',
        'Sitemap: ' . absolute_url('sitemap.xml')
    ];

    return implode(PHP_EOL, $lines) . PHP_EOL;
}

function refresh_seo_assets(?array $jobs = null): void
{
    $records = $jobs ?? load_jobs(true);
    $activeJobs = filter_active_jobs($records);

    file_put_contents(STORAGE_SITEMAP, generate_sitemap_xml($activeJobs), LOCK_EX);
    file_put_contents(STORAGE_ROBOTS, generate_robots_txt(), LOCK_EX);
}

function ensure_csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
    }

    return (string) $_SESSION['csrf_token'];
}

function is_valid_csrf(string $token): bool
{
    $stored = (string) ($_SESSION['csrf_token'] ?? '');

    return $stored !== '' && $token !== '' && hash_equals($stored, $token);
}

function flash(string $type, string $message): void
{
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message
    ];
}

function pull_flash(): ?array
{
    if (!isset($_SESSION['flash']) || !is_array($_SESSION['flash'])) {
        return null;
    }

    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);

    return $flash;
}

function build_url(array $params = [], string $anchor = ''): string
{
    $filtered = [];

    foreach ($params as $key => $value) {
        if ($value !== '' && $value !== null) {
            $filtered[$key] = $value;
        }
    }

    $query = http_build_query($filtered);

    return 'index.php' . ($query !== '' ? '?' . $query : '') . $anchor;
}

function h(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}
