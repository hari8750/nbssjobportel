# GoDaddy Deployment Guide

Domain from your screenshot: `nbsshrservices.in`

## Important

The GoDaddy screen in your screenshot is `Websites + Marketing` / Airo Website Builder.
That product can embed custom `HTML`, `CSS`, and `JavaScript`, but it does not run a custom PHP app.

For this PHP job portal, use one of these paths:

1. Move the domain to `GoDaddy Web Hosting (cPanel)` and upload this project there.
2. Host the PHP site on another provider and point the domain DNS to that server.

## Best option on GoDaddy

Use `Web Hosting (cPanel)`.

Official GoDaddy help shows:

- `Websites + Marketing` supports custom code embeds for HTML/CSS/JS sections only.
- `Web Hosting (cPanel)` supports PHP and file uploads through File Manager.

## If you get cPanel hosting

1. In GoDaddy, open `My Products`.
2. Under `Web Hosting`, select `Manage`.
3. Open `File Manager` for the domain.
4. Upload the deployment ZIP from this project.
5. Extract it inside the website root directory for `nbsshrservices.in`.
6. Make sure these files are in the domain root:
   - `index.php`
   - `includes/`
   - `assets/`
   - `data/`
7. In hosting settings, set PHP to a modern version such as `8.1+`.
8. Open `https://nbsshrservices.in` and verify the site loads.

## GitHub auto-deploy option

This repo now includes a GitHub Actions workflow at:

- `.github/workflows/deploy-ftp.yml`

Add these GitHub repository secrets before running it:

- `FTP_HOST`
- `FTP_PORT` (optional, defaults to `21`)
- `FTP_USERNAME`
- `FTP_PASSWORD`
- `FTP_SERVER_DIR` (optional, defaults to `public_html`)

Once those secrets are added, every push to `main` can deploy the portal to your hosting account over FTP.

## If you host somewhere else

Update DNS in GoDaddy:

1. Open the domain DNS settings.
2. Set the root `A` record (`@`) to your server IP.
3. Set `www` as a `CNAME` to `@` or to the host your provider gives you.
4. Wait for DNS propagation.

## App URL note

This portal auto-detects the live host from the request, so once it is served from `nbsshrservices.in`, canonical URLs and SEO links will use that domain automatically.

## Demo employer login

- Email: `employer@hireloop.in`
- Password: `Employer@123`
