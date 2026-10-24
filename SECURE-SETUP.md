## Database configuration

The site expects a private `config.php` in the project root. This file is intentionally excluded from Git and from automated deployment.

1. Copy `config.example.php` to `config.php`.
2. Enter the database host, database name, database username, and password supplied by your host.
3. For local development you can also set environment variables instead of hardcoding credentials.
4. Upload `config.php` to the site root over SFTP. Do not expose credentials through a public setup page or commit them to Git.
5. Restrict file access to the hosting account where the hosting plan supports that setting.

The template enables PHP error logging to `logs/errors.log`, uses `utf8mb4`, starts sessions with HttpOnly and SameSite cookies, and supports HTTPS enforcement and hardened headers in `.htaccess`.

## Security baseline

The project includes:
- HTTPS redirection
- HSTS on secure connections
- X-Frame-Options and X-Content-Type-Options
- Referrer and permission policies
- CSP for self-hosted assets
- login sessions with secure cookie settings
- CSRF tokens on admin and contact forms
- password hashing with bcrypt

## SEO and privacy settings

The website supports:
- `robots.txt`
- sitemap generation via `sitemap.php`
- SEO fields in the admin panel
- cookie consent banner text in the admin panel

These values are stored in the database and can be adjusted without changing code.
