-- Bogø Hallen - Sample Data
-- Insert this after running INSTALL.sql to populate the database with test data

-- Sample Gallery Images (using placeholder URLs - replace with actual uploaded images)
INSERT INTO gallery_images (image_path, caption, sort_order) VALUES
('https://via.placeholder.com/400x300?text=Støttemedlem', 'Støttemedlem', 1),
('https://via.placeholder.com/400x300?text=Badminton', 'Badminton', 2),
('https://via.placeholder.com/400x300?text=Futsal', 'Futsal', 3),
('https://via.placeholder.com/400x300?text=Volleyball', 'Volleyball', 4);

-- Sample Sponsors (using placeholder logos - replace with actual uploaded images)
INSERT INTO sponsors (name, logo, link, sort_order) VALUES
('C Sports', 'https://via.placeholder.com/150x100?text=C+Sports', 'https://example.com', 1),
('Barfod ApS', 'https://via.placeholder.com/150x100?text=Barfod', 'https://example.com', 2),
('Bogø Bogø', 'https://via.placeholder.com/150x100?text=Bogø', 'https://example.com', 3),
('Local Bank', 'https://via.placeholder.com/150x100?text=Bank', 'https://example.com', 4);

-- Sample Contact Submissions
INSERT INTO contact_submissions (name, email, subject, message) VALUES
('Jens Hansen', 'jens@example.com', 'Medlemskab', 'Jeg er interesseret i at blive medlem. Hvad er omkostningerne?', 0),
('Maria Petersen', 'maria@example.com', 'Instruktør hold', 'Jeg vil gerne deltage i badminton holdet. Hvornår er de første træninger?', 0);

-- Note: Sample audit log entries will be created automatically as changes are made through the admin panel
