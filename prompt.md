### **📋 Prompt: Build Bogø Hallen (Bogø Idrætscenter) PHP Website**

**Goal:**
Create a **modular, database-driven PHP website** for **Bogø Hallen** with a **custom admin panel** for non-technical administrators. The site must run on **Simply.com shared PHP hosting** and include all sections from the provided wireframe.

---

---

## **🎯 Requirements**

### **1. Technology**
- **Backend:** PHP 7.4–8.x (set via `.htaccess` for Simply.com).
- **Database:** MySQL (use `mysqli` or `PDO` with prepared statements).
- **Frontend:** HTML5, CSS3 (Flexbox/Grid), vanilla JavaScript.
- **Hosting:** Simply.com shared hosting (no Composer, limited extensions).
- **Security:** SSL (HTTPS), input sanitization, CSRF protection, password hashing.

---

### **2. Website Structure**
Implement **all** sections from the wireframe:

| **Section**         | **Details**                                                                                     | **Dynamic?** | **Admin-Editable**                          |
|---------------------|-------------------------------------------------------------------------------------------------|--------------|---------------------------------------------|
| **Header**          | Logo + navigation (Forside, Bestyrelsen, Om os, Kontakt).                                      | No           | Logo, nav links.                            |
| **Image Gallery**   | 4 images (e.g., "Støttemedlem," "Bøf," "Bif," "Fisk").                                           | No           | Images, captions.                           |
| **Main Content**    | 2-column layout: (1) Text description of Bogø Idrætscenter, (2) Opening hours.                 | No           | Text, opening hours (structured by day).    |
| **Sponsors**        | Horizontal row of sponsor logos (e.g., "C," "Barfod," "BOGØ BOGØ").                            | No           | Logos, links.                               |
| **Footer**          | Links to "Vedtægt," "Bliv sponsor," Google Maps, address, CVR, Facebook.                       | No           | All text, links, social URLs.               |
| **Contact Page**    | Form for inquiries (implied by "Kontakt" in nav).                                               | **Yes**      | Form fields, recipient email.              |
| **Google Maps**     | Embedded map (implied by footer link).                                                         | **Yes**      | Map location (address or lat/long).         |

---

### **3. Admin Panel**
#### **Features**
- **Multi-User Authentication:**
  - Users table: `id`, `username`, `password` (hashed), `role` (`super_admin`/`editor`).
  - Login/logout with sessions.
- **Audit Log:**
  - Track **who**, **what**, and **when** changes were made (store in `audit_log` table).
- **Content Management:**
  - **WYSIWYG Editor** (e.g., TinyMCE) for text fields.
  - **Image Uploads:**
    - Support `.jpg`, `.png`, `.svg`.
    - Store in `/uploads/` with random filenames.
    - Auto-resize to fit layout (use PHP GD library).
  - **Structured Data:**
    - Opening hours: Repeatable fields for `day` + `time`.
    - Sponsors: Fields for `logo` (file upload), `name`, `link`.

#### **Security**
- Restrict `/admin/` to logged-in users.
- Sanitize all inputs (use `htmlspecialchars`, `filter_var`).
- Use prepared statements for MySQL queries.

---
---
### **4. Database Schema (MySQL)**
```sql
-- Users
CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) UNIQUE NOT NULL,
  password VARCHAR(255) NOT NULL,
  role ENUM('super_admin', 'editor') DEFAULT 'editor',
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Content (for text blocks)
CREATE TABLE content (
  id INT AUTO_INCREMENT PRIMARY KEY,
  section VARCHAR(50) NOT NULL,  -- e.g., "header", "footer"
  key VARCHAR(50) NOT NULL,      -- e.g., "logo", "address"
  value TEXT,
  UNIQUE KEY (section, key)
);

-- Sponsors
CREATE TABLE sponsors (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  logo VARCHAR(255) NOT NULL,    -- Path to uploaded image
  link VARCHAR(255),
  sort_order INT DEFAULT 0
);

-- Gallery Images
CREATE TABLE gallery_images (
  id INT AUTO_INCREMENT PRIMARY KEY,
  image_path VARCHAR(255) NOT NULL,
  caption VARCHAR(100),
  sort_order INT DEFAULT 0
);

-- Audit Log
CREATE TABLE audit_log (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT,
  action VARCHAR(50) NOT NULL,  -- e.g., "updated_content", "uploaded_image"
  table_affected VARCHAR(50) NOT NULL,
  record_id INT,
  old_value TEXT,
  new_value TEXT,
  timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id)
);
```

---
---
### **5. Folder Structure**
```
/bogo-hallen/
├── /admin/
│   ├── /includes/        # Admin templates (header, footer, etc.)
│   ├── /uploads/         # Uploaded images (sponsors, gallery)
│   ├── dashboard.php     # Admin dashboard
│   ├── login.php         # Login page
│   ├── logout.php        # Logout script
│   ├── edit_content.php  # Edit text content
│   ├── edit_sponsors.php  # Manage sponsors
│   ├── edit_gallery.php  # Manage gallery
│   └── audit_log.php     # View change history
├── /includes/            # Frontend templates (header.php, footer.php, etc.)
├── /uploads/             # Public uploads (symlink to admin/uploads if needed)
├── index.php             # Homepage
├── om-os.php             # "Om os" page
├── kontakt.php           # Contact page with form
├── vedtaegt.php          # "Vedtægt" page
├── bliv-sponsor.php      # "Bliv sponsor" page
├── config.php            # Database credentials (exclude from Git)
└── .htaccess             # PHP version, HTTPS redirect, security rules
```

---
---
### **6. Simply.com Hosting Setup**
- **PHP Version:** Set in `.htaccess`:
  ```apache
  AddHandler application/x-httpd-php81 .php
  ```
- **HTTPS:** Force SSL in `.htaccess`:
  ```apache
  RewriteEngine On
  RewriteCond %{HTTPS} off
  RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
  ```
- **Permissions:** Ensure `/uploads/` has **755** permissions.

---
---
### **7. Deliverables**
1. **Full Website Code:**
   - All wireframe sections implemented.
   - Responsive design (mobile-friendly).
   - Dynamic features (contact form, Google Maps embed).

2. **Admin Panel:**
   - Multi-user login with roles.
   - Audit log for all changes.
   - WYSIWYG editor for text content.
   - Image upload/management.

3. **Database:**
   - MySQL schema (`INSTALL.sql`).
   - Sample data for testing.

4. **Documentation:**
   - `README.md` with:
     - Installation steps (Simply.com-specific).
     - Admin panel usage guide.
     - Folder structure overview.

---
---
### **8. Notes for the AI**
- **No Composer:** Use raw PHP (no dependencies).
- **Placeholders:** Use placeholder images/text (assets will be provided later).
- **Design:** Clean, minimalist, and responsive (match wireframe layout).
- **Language:** All frontend text must be in **Danish** (use wireframe labels).
- **Error Handling:** Log errors to a file (e.g., `/logs/errors.log`).