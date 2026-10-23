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
('admin', '$2y$10$2dFG5SzHsIAx7KfXdgXlnO.Y/1qfoL7bVjcB0bkFu.qKBIFp0jtXq', 'super_admin');

-- Insert default content
INSERT INTO content (section, `key`, value) VALUES
('header', 'logo_alt', 'Bogø Hallen Logo'),
('header', 'site_title', 'Bogø Hallen'),
('header', 'nav_home_label', 'Forside'),
('header', 'nav_home_url', '/'),
('header', 'nav_board_label', 'Bestyrelsen'),
('header', 'nav_board_url', '/bestyrelsen.php'),
('header', 'nav_about_label', 'Om os'),
('header', 'nav_about_url', '/om-os.php'),
('header', 'nav_contact_label', 'Kontakt'),
('header', 'nav_contact_url', '/kontakt.php'),
('header', 'nav_booking_label', 'Booking'),
('header', 'nav_booking_url', 'https://bogoe-ic.halbooking.dk'),
('main', 'about_title', 'Velkommen til Bogø Hallen'),
('main', 'about_text', 'Bogø Hallen er Danmarks moderne og bedst udstyret idrætscenter. Vi tilbyder faciliteter til alle typer sport og aktiviteter.'),
('home_notice', 'title', 'Praktisk information'),
('home_notice', 'content', ''),
('about_page', 'content', '<p>Bogø Hallen (Bogø Idrætscenter) er Danmarks moderne og bedst udstyrede idrætscenter. Vi tilbyder faciliteter til alle typer sport og aktiviteter for børn, unge og voksne.</p><p>Vores mål er at fremme sundhed, trivsel og fællesskab gennem sport og fysisk aktivitet. Vi tilbyder undervisning, træning og arrangementer for alle niveauer.</p><p>Centeret har flere moderne haller med mulighed for:</p><ul><li>Badminton</li><li>Futsal/Fodbold</li><li>Volleyball</li><li>Basketball</li><li>Håndbold</li><li>Gymnastik og fitness</li><li>Diverse arrangementer og hold</li></ul><p>Vores erfarne instruktører og trænere er klar til at hjælpe dig med at nå dine mål. Vi tilbyder både private sessions, gruppetimer og organiserede turneringer.</p><p>Besøg os eller kontakt os for mere information om medlemskab, aktiviteter og åbningstider.</p><p><strong>Fakta:</strong><br>Etableret: 1985<br>Placering: Bogø, Danmark<br>Ledelse: Bogø Idrætscenter bestyrelse</p>'),
('board', 'title', 'Bestyrelsen'),
('board', 'text', 'Bestyrelsen varetager Bogø Hallens daglige drift og udvikling. Kontakt os gerne via kontaktsiden.'),
('bylaws', 'title', 'Vedtægter'),
('bylaws', 'content', '<h3>Bogø Idrætscenter - Vedtægter</h3><h3>§1 - Navn og Formål</h3><p>Bogø Idrætscenter er en frivillig organisation, hvis formål er at fremme motion, idræt og sundhed blandt børn, unge og voksne i Bogø og omegn.</p><h3>§2 - Medlemskab</h3><p>Medlemskab er åbent for alle uden aldersgrænser. Medlemmer betaler et årligt medlemsgebyr fastsat af bestyrelsen. Medlemskab kan opsiges med 4 ugers varsel.</p><h3>§3 - Bestyrelse</h3><p>Centeret ledes af en bestyrelse bestående af mindst 3 og højst 7 medlemmer valgt på medlemsmøde for et år ad gangen. Bestyrelsen vælger blandt sine medlemmer en formand, kasserer og sekretær.</p><h3>§4 - Økonomisk</h3><p>Centeret finansieres gennem medlemsgebyr, tilskud, fundraising og sponsorbidrag. Regnskab aflægges årligt og revideres af medlemmerne.</p><h3>§5 - Møder</h3><p>Ordinært medlemsmøde afholdes årligt. Ekstraordinære møder kan indkaldes efter behov. Alle medlemmer er velkomne til at deltage.</p><h3>§6 - Vedtægtsændringer</h3><p>Ændringer til vedtægterne kræver medlemsafstemning og 2/3 flertal blandt tilstedeværende medlemmer.</p><h3>§7 - Opløsning</h3><p>Ved opløsning af foreningen går formuen til foreninger med lignende formål, valgt af medlemmerne på det møde, hvor opløsning vedtages.</p><p><strong>Sidst opdateret:</strong> Januar 2024<br><strong>Version:</strong> 1.0</p>'),
('contact_form', 'name_label', 'Navn'),
('contact_form', 'email_label', 'Email'),
('contact_form', 'subject_label', 'Emne'),
('contact_form', 'message_label', 'Besked'),
('contact_form', 'submit_label', 'Send besked'),
('footer', 'address', 'Bogø Idrætscenter, Bogø Idrætspark 1, 4773 Kalvebod'),
('footer', 'cvr', 'CVR: 12345678'),
('footer', 'contact_email', 'kontakt@bogoehallen.dk'),
('footer', 'facebook_url', 'https://facebook.com/share/18iZZarv8b/?mibextid=wwXlfr'),
('footer', 'contact_url', '/kontakt.php'),
('footer', 'bylaws_url', '/vedtaegt.php'),
('footer', 'sponsor_url', '/bliv-sponsor.php'),
('footer', 'google_maps_query', 'Bogø Idrætscenter, Bogø Idrætspark 1, 4773 Kalvebod');

-- Insert opening hours
INSERT INTO content (section, `key`, value) VALUES
('opening_hours', 'monday', '08:00 - 22:00'),
('opening_hours', 'tuesday', '08:00 - 22:00'),
('opening_hours', 'wednesday', '08:00 - 22:00'),
('opening_hours', 'thursday', '08:00 - 22:00'),
('opening_hours', 'friday', '08:00 - 23:00'),
('opening_hours', 'saturday', '09:00 - 23:00'),
('opening_hours', 'sunday', '09:00 - 21:00');
