# Bogø Hallen Website

A modern, database-driven PHP website for Bogø Hallen (Bogø Idrætscenter) with a custom admin panel for managing content, images, sponsors, SEO metadata, and contact data.

## What is included

### Frontend
- Responsive multi-page website for a single facility website
- Homepage, board page, about page, contact page, bylaws page, and sponsor page
- Dynamic content from MySQL
- Google Maps embed and footer contact details
- SEO metadata, canonical URLs, Open Graph tags, `robots.txt`, and sitemap support
- Cookie consent banner for compliant basic cookie handling

### Admin panel
- Secure session-based login with role-based access
- Content editor for header/footer, page content, opening hours, SEO settings, and cookie text
- Upload manager for gallery and sponsor images
- Audit log for all admin actions
- CSRF protection, prepared statements, validation, and password hashing
- Hardened `.htaccess` security headers and HTTPS enforcement

## Technology stack

- Backend: PHP 8.x
- Database: MySQL 8 / MariaDB-compatible SQL
- Frontend: HTML5, CSS3, vanilla JavaScript
- Hosting: shared hosting / Apache-compatible hosting
- Dependencies: none (pure PHP)

## Local development

### Option 1: Docker Compose
```bash
docker compose up --build
```
Then open:
- Public site: http://localhost:8080
- MySQL: localhost:3307

The container uses `APP_ENV=development` and environment variables from `docker-compose.yml`.

### Option 2: Local PHP + MySQL
1. Copy `config.example.php` to `config.php`
2. Fill in your local database details
3. Create the database and import `INSTALL.sql`
4. Start PHP:
   ```bash
   php -S localhost:8000
   ```

## Production setup (Simply.com)

### 1) Configure credentials
1. Copy `config.example.php` to `config.php`
2. Update database and site URL values
3. Upload `config.php` to the project root over SFTP
4. Keep it out of Git and public repositories

### 2) Set permissions
```bash
mkdir -p admin/uploads logs
chmod 755 admin/uploads logs
```

### 3) Import database schema
Import `INSTALL.sql` into your MySQL database.

### 4) Access admin
Open:
- `https://yourdomain.dk/admin/login.php`

Default user:
- Username: `admin`
- Password: `admin123`

Important: change the password immediately after first login.

## Admin panel usage

### Dashboard
Overview of users, sponsor count, gallery count, and recent changes.

### Edit content
Update:
- Site title and description
- Contact details and Maps query
- Homepage hero text
- About page text
- Footer links
- SEO settings and cookie notice
- Opening hours

### Gallery and sponsors
Upload images, add captions, and set display order.

### Audit log
Review who changed what and when.

## Security notes

This project includes:
- HTTPS redirection
- Strict transport security
- Content Security Policy
- CSRF validation on forms
- Prepared SQL statements
- Password hashing with bcrypt
- Session cookies with HttpOnly and SameSite settings

## Project structure

```text
/
├── .htaccess
├── .gitignore
├── INSTALL.sql
├── README.md
├── SECURE-SETUP.md
├── robots.txt
├── sitemap.php
├── config.example.php
├── docker-compose.yml
├── admin/
│   ├── audit_log.php
│   ├── dashboard.php
│   ├── edit_content.php
│   ├── edit_gallery.php
│   ├── edit_sponsors.php
│   ├── login.php
│   ├── logout.php
│   └── includes/
├── includes/
│   ├── content-defaults.php
│   ├── footer.php
│   ├── functions.php
│   ├── header.php
│   └── security.php
├── css/
├── js/
├── logs/
├── admin/uploads/
├── index.php
├── om-os.php
├── kontakt.php
├── vedtaegt.php
├── bliv-sponsor.php
├── bestyrelsen.php
└── sitemap.php
```

## Notes

- This is designed as a single-site public website with an admin dashboard, not a multi-site platform.
- The code intentionally avoids external dependencies so it works well on shared hosting.
- The project includes site metadata and business settings in the database so the site can be maintained without changing code.

## Security Best Practices

✅ **Already Implemented**:
- Bcrypt password hashing (10 cost rounds)
- CSRF token protection on all forms
- Prepared SQL statements (prevent SQL injection)
- Input sanitization with `htmlspecialchars()`
- Secure session handling (HttpOnly, SameSite cookies)
- SSL/HTTPS forced in `.htaccess`
- Prevent script execution in upload directory

⚠️ **You Should Do**:
1. **Change default admin password** immediately after installation
2. **Set proper file permissions**: `chmod 644` for PHP files and `chmod 755` for directories
3. **Enable HTTPS** (SSL certificate)
4. **Keep PHP updated** to the latest secure version
5. **Monitor `/logs/errors.log`** regularly for issues

## Configuration Security

Database credentials are stored in the ignored root `config.php`, not in source control. Deploy it separately over SFTP, restrict file access where the hosting plan permits, and never expose credentials through a public setup form. PHP errors are written to `/logs/errors.log` when the host permits it.

## Customization

### Change Site Title
Edit in admin panel: Content editor → "Om Os" section

### Update Contact Email
Edit in admin panel: Content editor → Contact information section

### Modify Styling
Edit `/css/style.css` to customize colors, fonts, layout

### Add New Pages
1. Create new PHP file (e.g., `programs.php`)
2. Include header and footer templates
3. Add link to navigation in `/includes/header.php`

### Change Default Opening Hours
Edit `INSTALL.sql` before importing, or update through admin panel

## Troubleshooting

### "Database connection failed" on homepage

**Problem:** Cannot connect to database

**Solutions:**
1. Check that the root `config.php` exists and contains the correct database settings
2. Verify database credentials are correct (test in phpMyAdmin)
3. Verify database name, user, and host are correct
4. Check PHP error logs: `/logs/errors.log`
5. Contact hosting provider (for database access issues)

### Images not uploading
- Check `/admin/uploads/` folder permissions (should be 755)
- Verify PHP GD library is installed: `php -m | grep gd`
- Check PHP upload limits in `php.ini`

### Forms not submitting
- Verify CSRF token is included in form
- Check that `POST` method is set on form
- Review error log in `/logs/errors.log`

### Admin panel looks unstyled
- Clear browser cache
- Verify `/css/style.css` is accessible
- Check file permissions

### Email not sending
- Verify `contact_email` is set in admin panel
- Check server mail configuration
- Review error logs for mail errors

## Email Configuration

Contact form emails are sent using PHP `mail()` function. For Simply.com:

1. Emails are sent to the address configured in Admin Panel (Content Editor)
2. From address is the visitor's email
3. No external SMTP configuration needed for Simply.com shared hosting

If emails aren't arriving:
- Check spam/junk folder
- Verify recipient email is correct in admin panel
- Test with a Simply.com email address first

## Backup and Maintenance

### Regular Backups
1. **Database**: Export SQL through phpMyAdmin weekly
2. **Files**: Download entire `/bogo-hallen/` folder regularly
3. **Uploads**: Important images are in `/admin/uploads/`

### Database Maintenance
- Run `OPTIMIZE TABLE` on large tables monthly
- Archive old audit logs if they grow very large
- Verify backups are recoverable

### Update PHP
- Check Simply.com for available PHP versions
- Test on staging first if possible
- Update `.htaccess` if needed

## Support

For issues:
1. Check `/logs/errors.log` for PHP errors
2. Review `config.example.php` and the database setup steps above
3. Review troubleshooting section below
4. Contact hosting support (for hosting-level issues)

## License

This website was built for Bogø Hallen. All code is proprietary.

## Version History

- **v1.0** (May 2026): Initial release
  - Complete admin panel
  - Responsive frontend
  - Audit logging
  - Gallery and sponsor management
