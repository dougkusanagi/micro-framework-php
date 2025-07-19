-- Seed data for users table
-- Password for the user is 'password' (hashed)

DELETE FROM users WHERE email = 'test@example.com';

INSERT INTO users (name, email, password, created_at, updated_at) VALUES 
('Test User', 'test@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NOW(), NOW());
