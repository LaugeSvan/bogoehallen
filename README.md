# Bogø Hallen Website

A modern, database-driven PHP website for Bogø Hallen (Bogø Idrætscenter) with a custom admin panel for managing content, images, and sponsors.

## Features

### Frontend
- **Responsive Design**: Mobile-first responsive layout using CSS Grid/Flexbox
- **Dynamic Content**: Homepage displays gallery, about text, opening hours, and sponsors from database
- **Public Pages**:
  - Forside (Homepage)
  - Om os (About)
  - Kontakt (Contact form with email submission)
  - Vedtægt (Constitution/Bylaws)
  - Bliv sponsor (Become Sponsor)

### Admin Panel
- **Secure Authentication**: Session-based login with bcrypt password hashing
- **Content Management**:
  - Edit main content blocks (about text, opening hours)
  - Manage gallery images with auto-resize
  - Manage sponsor logos and links
- **Audit Log**: Track all changes with user, timestamp, and action details
- **Role-Based Access**: Super Admin and Editor roles
- **Security**: CSRF protection, prepared SQL statements, input sanitization

## Technology Stack

- **Backend**: PHP 7.4+ (8.x recommended)
- **Database**: MySQL 5.7+ (with InnoDB)
- **Frontend**: HTML5, CSS3 (Flexbox/Grid), Vanilla JavaScript
- **Hosting**: Simply.com shared hosting (or any PHP hosting)
- **Libraries**: None - pure PHP, no dependencies

## Installation

### Prerequisites
- PHP 7.4 or higher
- MySQL 5.7 or higher
- FTP/SSH access to Simply.com hosting
- MySQL database and user created

### Step 1: Upload Files
1. Download all project files
2. Upload via FTP to your Simply.com hosting (typically to `public_html/`)
3. Ensure `.htaccess` file is uploaded (may be hidden)

### Step 2: Setup Encrypted Database Credentials

⚠️ **Database credentials are now encrypted for security!**

1. Upload all files to Simply.com (including `/config/` directory)

2. Navigate to setup script:
   ```
   https://yoursite.dk/config/setup-credentials.php
   ```

3. Enter your Simply.com database details:
   - Database Host (e.g., `mysql.simply.com`)
   - Database User
   - Database Password
   - Database Name
   - Choose a strong Master Password (12+ characters)

4. Click "Encrypt & Save Credentials"

5. **DELETE** `/config/setup-credentials.php` immediately

6. Save your master password in a secure location (password manager)

📖 **Full instructions:** See `SECURE-SETUP.md`

### Step 3: Create Database Schema

1. Open MySQL administration panel (phpMyAdmin on Simply.com)
2. Create a new database (use the name from setup)
3. Import `INSTALL.sql`:
   - Copy entire contents of `INSTALL.sql`
   - Paste into SQL query window in phpMyAdmin
   - Execute

### Step 4: Set Permissions
1. Set `/admin/uploads/` folder permissions to 755:
   ```bash
   chmod 755 admin/uploads
   ```

2. Ensure `/logs/` directory is writable:
   ```bash
   chmod 755 logs
   mkdir -p logs
   ```

3. Ensure encryption key file is secure:
   ```bash
   chmod 0600 config/.encryption-key
   chmod 0600 config/credentials.enc
   ```

### Step 5: Access Admin Panel
1. Navigate to `https://yoursite.dk/admin/login.php`
2. Login with default credentials:
   - **Username**: `admin`
   - **Password**: `admin123`
3. **⚠️ IMPORTANT**: Change the admin password immediately!

## Admin Panel Usage

### Dashboard (`/admin/dashboard.php`)
Overview of system statistics and recent changes.

### Edit Content (`/admin/edit_content.php`)
- Update main about text and title
- Edit footer information (address, CVR, contact email)
- Manage opening hours for each day

### Gallery Manager (`/admin/edit_gallery.php`)
- Upload gallery images (JPG, PNG, SVG supported)
- Add captions to images
- Delete images
- Reorder gallery items

### Sponsor Manager (`/admin/edit_sponsors.php`)
- Add new sponsors with logo and link
- Edit sponsor information
- Delete sponsors
- Manage sponsor order

### Audit Log (`/admin/audit_log.php`)
- View all changes made in the system
- See who made the change and when
- Track modifications to content, images, and sponsors

## Folder Structure

```
/bogo-hallen/
├── config.php              # Database configuration (EDIT THIS)
├── INSTALL.sql             # Database schema
├── sample-data.sql         # Optional sample data
├── README.md               # This file
├── .htaccess               # Server configuration
├── .gitignore              # Git configuration
│
├── /admin/
│   ├── login.php           # Admin login page
│   ├── logout.php          # Logout handler
│   ├── dashboard.php       # Admin dashboard
│   ├── edit_content.php    # Content editor
│   ├── edit_gallery.php    # Gallery manager
│   ├── edit_sponsors.php   # Sponsor manager
│   ├── audit_log.php       # Audit log viewer
│   │
│   ├── /includes/
│   │   ├── header.php      # Admin template header
│   │   ├── footer.php      # Admin template footer
│   │   ├── admin-functions.php
│   │   └── image-handler.php
│   │
│   └── /uploads/           # User-uploaded images (auto-created)
│
├── /includes/
│   ├── header.php          # Frontend header template
│   ├── footer.php          # Frontend footer template
│   ├── nav.php             # Navigation menu
│   └── security.php        # Security utilities
│
├── /css/
│   └── style.css           # Main stylesheet
│
├── /js/
│   └── main.js             # Frontend JavaScript
│
├── /logs/
│   └── errors.log          # Error log (auto-created)
│
├── Public Pages:
│   ├── index.php           # Homepage
│   ├── om-os.php           # About page
│   ├── kontakt.php         # Contact page
│   ├── vedtaegt.php        # Constitution page
│   └── bliv-sponsor.php    # Become sponsor page
```

## Database Schema

### users
- `id` (INT, PK)
- `username` (VARCHAR 50, UNIQUE)
- `password` (VARCHAR 255, bcrypt hashed)
- `role` (ENUM: 'super_admin', 'editor')
- `created_at` (DATETIME)

### content
- `id` (INT, PK)
- `section` (VARCHAR 50)
- `key` (VARCHAR 50)
- `value` (LONGTEXT)
- `updated_at` (DATETIME)

### gallery_images
- `id` (INT, PK)
- `image_path` (VARCHAR 255)
- `caption` (VARCHAR 100)
- `sort_order` (INT)
- `created_at` (DATETIME)

### sponsors
- `id` (INT, PK)
- `name` (VARCHAR 100)
- `logo` (VARCHAR 255)
- `link` (VARCHAR 255)
- `sort_order` (INT)
- `created_at` (DATETIME)

### contact_submissions
- `id` (INT, PK)
- `name` (VARCHAR 100)
- `email` (VARCHAR 100)
- `subject` (VARCHAR 100)
- `message` (TEXT)
- `submitted_at` (DATETIME)
- `read_status` (TINYINT)

### audit_log
- `id` (INT, PK)
- `user_id` (INT, FK to users)
- `action` (VARCHAR 50)
- `table_affected` (VARCHAR 50)
- `record_id` (INT)
- `old_value` (LONGTEXT, JSON)
- `new_value` (LONGTEXT, JSON)
- `timestamp` (DATETIME)

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
2. **Update `SALT` constant** in `config.php` with a random string
3. **Set proper file permissions**: `chmod 644` for PHP files, `chmod 755` for directories
4. **Enable HTTPS** (SSL certificate)
5. **Keep PHP updated** to the latest secure version
6. **Monitor `/logs/errors.log`** regularly for issues

## Encrypted Credentials System

**Your database credentials are encrypted** for maximum security:

- 🔐 **AES-256-CBC encryption** (military-grade)
- 🔑 **Master password protection** (user-provided)
- 📁 **Separate `/config/` directory** (inaccessible from web)
- 🛡️ **0600 file permissions** (PHP-only read access)

### Files Created by Setup

- `/config/credentials.enc` - Encrypted database credentials
- `/config/.encryption-key` - Encryption key (0600 permissions)
- `config.php` - Updated to load encrypted credentials

### Security Features

✅ Credentials never stored in plain text
✅ Encryption key separate from encrypted data
✅ Master password never stored (only hash)
✅ Random IV for each encryption
✅ Web server cannot access encryption files

📖 **Full documentation:** See `SECURE-SETUP.md`

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
1. Check that `/config/credentials.enc` and `/config/.encryption-key` both exist
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
2. Review `SECURE-SETUP.md` for credential/encryption issues
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
