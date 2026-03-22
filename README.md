# HireLoop PHP Job Portal

A lightweight PHP job portal starter with:

- Job browsing with keyword and dropdown filters
- Featured jobs section
- Employer login + protected job posting form
- Candidate application form
- Protected dashboard with category and application insights
- JSON-based persistence for quick local demos
- SEO-ready metadata, structured data, robots.txt, and sitemap.xml
- GitHub Actions FTP deployment workflow for hosting

## Structure

```text
php-job-portal/
  assets/
    styles.css
  data/
    jobs.json
    applications.json
  includes/
    data.php
    layout.php
  index.php
```

## Run Locally

You need PHP installed on your machine. Then run:

```bash
cd "/Users/harihaldar/Documents/New project/php-job-portal"
php -S localhost:8000
```

Open `http://localhost:8000`.

## Notes

- New jobs are saved to `data/jobs.json`
- New applications are saved to `data/applications.json`
- `robots.txt` and `sitemap.xml` are generated automatically from the current jobs
- Set `APP_URL` in your environment for production so canonical URLs and sitemap links use your real domain
- Default employer login:
  - Email: `employer@hireloop.in`
  - Password: `Employer@123`
- Override employer access with `EMPLOYER_EMAIL`, `EMPLOYER_PASSWORD`, and `EMPLOYER_NAME`
- For GitHub-to-hosting deploys, add repository secrets:
  - `FTP_HOST`
  - `FTP_PORT` (optional, default `21`)
  - `FTP_USERNAME`
  - `FTP_PASSWORD`
  - `FTP_SERVER_DIR` (optional, default `public_html`)
- No database is required for this starter version
- This can be extended later with MySQL, login, admin roles, and recruiter dashboards
