-- Create database and set character encoding
CREATE DATABASE IF NOT EXISTS lccl_ticketing CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE lccl_ticketing;

-- Users table (roles: customer, admin, super_admin)
CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(100) NOT NULL UNIQUE,
  email VARCHAR(255) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  profile_image VARCHAR(255) DEFAULT NULL,
  role ENUM('customer','admin','super_admin') NOT NULL DEFAULT 'customer',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Events table with price column
CREATE TABLE IF NOT EXISTS events (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(255) NOT NULL,
  description TEXT,
  date DATE,
  image VARCHAR(255) DEFAULT NULL,
  price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  capacity INT DEFAULT NULL,
  type ENUM('upcoming','past') DEFAULT 'upcoming',
  created_by INT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Messages/Feedback table
CREATE TABLE IF NOT EXISTS messages (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  subject VARCHAR(255) NOT NULL,
  message TEXT NOT NULL,
  status VARCHAR(20) DEFAULT 'new',
  target_roles VARCHAR(255) DEFAULT 'admin,super_admin',
  is_read TINYINT DEFAULT 0,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tickets table
CREATE TABLE IF NOT EXISTS tickets (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NULL,
  event_id INT NULL,
  quantity INT DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE RESTRICT,
  FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Shopping Cart table
CREATE TABLE IF NOT EXISTS cart (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  event_id INT NOT NULL,
  quantity INT DEFAULT 1,
  added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Payments table
CREATE TABLE IF NOT EXISTS payments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  amount DECIMAL(10,2) NOT NULL DEFAULT 0,
  method VARCHAR(50) NOT NULL,
  details JSON NULL,
  status VARCHAR(50) NOT NULL DEFAULT 'pending',
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default admin and superadmin accounts
INSERT INTO users (username, email, password, role) VALUES
('admin', 'admin@example.com', '$2y$10$lj.W5neEVVVuvqDFAlg4AeN/XNrFZSaCkqmBSQQpBOKrfnClUALi.', 'admin'),
('superadmin', 'superadmin@example.com', '$2y$10$CLcFENICUDyjH1zJzxdcu.KnqCqDAJ4jnK40/UPIXzjjkJw.HOhDG', 'super_admin');

-- Default login credentials:
-- Admin: username=admin, password=AdminPass2025!
-- SuperAdmin: username=superadmin, password=SuperAdminPass2025!

ALTER TABLE messages
  ADD COLUMN solution TEXT NULL,
  ADD COLUMN responded_by INT NULL,
  ADD COLUMN responded_at DATETIME NULL;  

-- System settings table
CREATE TABLE IF NOT EXISTS settings (
  name VARCHAR(100) PRIMARY KEY,
  value VARCHAR(255) NOT NULL
);

INSERT IGNORE INTO settings (name, value) VALUES ('site_name', 'LCCL Ticketing System');
-- Find the name of the foreign key constraint (if not known, use SHOW CREATE TABLE tickets)
ALTER TABLE tickets DROP FOREIGN KEY tickets_ibfk_2;

ALTER TABLE tickets
ADD CONSTRAINT tickets_ibfk_2
FOREIGN KEY (event_id) REFERENCES events(id)
ON DELETE CASCADE;