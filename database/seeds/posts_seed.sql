-- Seed data for posts table

INSERT INTO posts (title, slug, content, user_id, category_id, created_at, updated_at) VALUES
('Welcome to the Blog', 'welcome-to-the-blog', 'This is the first post on the blog!', 1, 1, NOW(), NOW()),
('Exploring Technology Trends', 'exploring-technology-trends', 'Let''s discuss the latest in tech.', 1, 1, NOW(), NOW()),
('Healthy Living Tips', 'healthy-living-tips', 'Tips for a healthier lifestyle.', 1, 2, NOW(), NOW()),
('Top Travel Destinations 2024', 'top-travel-destinations-2024', 'Where to go this year.', 1, 3, NOW(), NOW()); 