## Database Configuration

The site expects a private `config.php` in the project root. This file is intentionally excluded from Git and from automated deployment.

1. Copy `config.example.php` to `config.php`.
2. Enter the database host, database name, database username, and password supplied by Simply.com.
3. Upload `config.php` to the site root over SFTP. Do not expose credentials through a public setup page or commit them to Git.
4. Restrict file access to the hosting account where the hosting plan supports that setting.

The template enables PHP error logging to `logs/errors.log`, uses `utf8mb4`, and starts sessions with HttpOnly and SameSite cookies. HTTPS is enforced by `.htaccess`.
