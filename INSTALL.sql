-- Bogø Hallen Database Schema
-- MySQL 5.7+

-- Users table for admin authentication
CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) UNIQUE NOT NULL,
  password VARCHAR(255) NOT NULL,
  role ENUM('super_admin', 'editor') DEFAULT 'editor',
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX(username)
);

-- Content table for storing text blocks and settings
CREATE TABLE IF NOT EXISTS content (
  id INT AUTO_INCREMENT PRIMARY KEY,
  section VARCHAR(50) NOT NULL,
  `key` VARCHAR(50) NOT NULL,
  value LONGTEXT,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY unique_section_key (section, `key`)
);

-- Gallery images
CREATE TABLE IF NOT EXISTS gallery_images (
  id INT AUTO_INCREMENT PRIMARY KEY,
  image_path VARCHAR(255) NOT NULL,
  caption VARCHAR(100),
  sort_order INT DEFAULT 0,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX(sort_order)
);

-- Sponsors
CREATE TABLE IF NOT EXISTS sponsors (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  logo VARCHAR(255) NOT NULL,
  link VARCHAR(255),
  sort_order INT DEFAULT 0,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX(sort_order)
);

-- Contact form submissions
CREATE TABLE IF NOT EXISTS contact_submissions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(100) NOT NULL,
  subject VARCHAR(100) NOT NULL,
  message TEXT NOT NULL,
  submitted_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  read_status TINYINT(1) DEFAULT 0,
  INDEX(submitted_at)
);

-- Audit log for tracking all changes
CREATE TABLE IF NOT EXISTS audit_log (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT,
  action VARCHAR(50) NOT NULL,
  table_affected VARCHAR(50) NOT NULL,
  record_id INT,
  old_value LONGTEXT,
  new_value LONGTEXT,
  timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
  INDEX(user_id),
  INDEX(timestamp),
  INDEX(action)
);

-- Insert default admin user (username: admin, password: admin123)
-- Password hash for 'admin123' using bcrypt
INSERT INTO users (username, password, role) VALUES
('admin', '$2y$10$mOtZ9ZiI3YhYz.i.FPgFAuvVxZeqJcVHj.cKBRKFiZL/BEjRfEg66', 'super_admin');

-- Insert default content
INSERT INTO content (section, `key`, value) VALUES
('header', 'logo_alt', 'Bogø Hallen Logo'),
('header', 'site_title', 'Bogø Hallen'),
('main', 'about_title', 'Velkommen til Bogø Hallen'),
('main', 'about_text', 'Bogø Hallen er Danmarks moderne og bedst udstyret idrætscenter. Vi tilbyder faciliteter til alle typer sport og aktiviteter.'),
('footer', 'address', 'Bogø Idrætscenter, Bogø Idrætspark 1, 4773 Kalvebod'),
('footer', 'cvr', 'CVR: 12345678'),
('footer', 'contact_email', 'kontakt@bogohallen.dk'),
('footer', 'facebook_url', 'https://facebook.com/bogohallen'),
('footer', 'google_maps_embed', '<iframe src="https://www.google.com/maps/embed?pb=..." width="400" height="300" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>');

-- Insert opening hours
INSERT INTO content (section, `key`, value) VALUES
('opening_hours', 'monday', '08:00 - 22:00'),
('opening_hours', 'tuesday', '08:00 - 22:00'),
('opening_hours', 'wednesday', '08:00 - 22:00'),
('opening_hours', 'thursday', '08:00 - 22:00'),
('opening_hours', 'friday', '08:00 - 23:00'),
('opening_hours', 'saturday', '09:00 - 23:00'),
('opening_hours', 'sunday', '09:00 - 21:00');
