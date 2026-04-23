villa-- Database schema for kawanpuncak.com villa booking platform

SET FOREIGN_KEY_CHECKS=0;

-- =====================
-- USERS
-- =====================
CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  email VARCHAR(255) UNIQUE NOT NULL,
  password VARCHAR(255) NOT NULL,
  phone VARCHAR(20),
  role ENUM('user', 'admin') DEFAULT 'user',
  affiliate_code VARCHAR(10) UNIQUE,
  referral_code VARCHAR(10),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- =====================
-- AUTH TOKENS
-- =====================
CREATE TABLE refresh_tokens (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  token_hash VARCHAR(255) NOT NULL,
  expires_at DATETIME NOT NULL,
  revoked TINYINT(1) DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  UNIQUE KEY unique_token_hash (token_hash)
);

CREATE TABLE invalidated_tokens (
  id INT AUTO_INCREMENT PRIMARY KEY,
  jti VARCHAR(255) NOT NULL,
  expires_at DATETIME NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY unique_jti (jti)
);

-- =====================
-- VILLAS
-- =====================
CREATE TABLE villas (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  description TEXT,
  location VARCHAR(255),
  max_guests INT NOT NULL,
  bedrooms INT NOT NULL,
  weekday_price DECIMAL(10,2) NOT NULL,
  weekend_price DECIMAL(10,2) NOT NULL,
  facilities JSON,
  rules TEXT,
  images JSON,
  status ENUM('active', 'inactive') DEFAULT 'active',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE villa_images (
  id INT AUTO_INCREMENT PRIMARY KEY,
  villa_id INT NOT NULL,
  image_path VARCHAR(512) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (villa_id) REFERENCES villas(id) ON DELETE CASCADE
);

-- =====================
-- BOOKINGS
-- =====================
CREATE TABLE bookings (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  villa_id INT NOT NULL,
  checkin_date DATE NOT NULL,
  checkout_date DATE NOT NULL,
  status ENUM('pending', 'approved', 'waiting_payment', 'paid', 'completed', 'cancelled') DEFAULT 'pending',
  user_ip VARCHAR(45) NULL,
  total_price DECIMAL(10,2),
  markup_amount DECIMAL(10,2),
  affiliate_user_id INT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (villa_id) REFERENCES villas(id) ON DELETE CASCADE,
  FOREIGN KEY (affiliate_user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- =====================
-- BOOKING DATES (ANTI DOUBLE BOOKING)
-- =====================
CREATE TABLE booking_dates (
  id INT AUTO_INCREMENT PRIMARY KEY,
  booking_id INT NOT NULL,
  villa_id INT NOT NULL,
  date DATE NOT NULL,
  UNIQUE KEY unique_villa_date (villa_id, date),
  FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE,
  FOREIGN KEY (villa_id) REFERENCES villas(id) ON DELETE CASCADE
);

-- =====================
-- PAYMENTS
-- =====================
CREATE TABLE payments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  booking_id INT NOT NULL,
  amount DECIMAL(10,2) NOT NULL,
  payment_method VARCHAR(50) DEFAULT 'doku',
  doku_transaction_id VARCHAR(255) NOT NULL,
  status ENUM('pending', 'success', 'failed') DEFAULT 'pending',
  webhook_data JSON,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE,
  UNIQUE KEY unique_transaction_id (doku_transaction_id)
);

-- =====================
-- DOKU WEBHOOKS (PINDAH KE BAWAH)
-- =====================
CREATE TABLE doku_webhooks (
  id INT AUTO_INCREMENT PRIMARY KEY,
  booking_id INT NOT NULL,
  order_id VARCHAR(255) NOT NULL,
  transaction_id VARCHAR(255) NOT NULL,
  status ENUM('PENDING','SUCCESS','FAILED') NOT NULL,
  amount DECIMAL(10,2) NOT NULL,
  signature VARCHAR(255) NOT NULL,
  received_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY unique_order_status (order_id, status),
  FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE
);

-- =====================
-- REVIEWS
-- =====================
CREATE TABLE reviews (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  villa_id INT NOT NULL,
  booking_id INT NOT NULL,
  rating INT NOT NULL,
  comment TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (villa_id) REFERENCES villas(id) ON DELETE CASCADE,
  FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE
);

-- =====================
-- WISHLIST
-- =====================
CREATE TABLE wishlist (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  villa_id INT NOT NULL,
  UNIQUE KEY unique_user_villa (user_id, villa_id),
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (villa_id) REFERENCES villas(id) ON DELETE CASCADE
);

-- =====================
-- AFFILIATE
-- =====================
CREATE TABLE affiliate_commissions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  affiliate_user_id INT NOT NULL,
  booking_id INT NOT NULL,
  commission_amount DECIMAL(10,2) NOT NULL,
  status ENUM('pending', 'approved', 'paid') DEFAULT 'pending',
  eligible_at DATETIME NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (affiliate_user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE,
  UNIQUE KEY unique_booking_commission (booking_id)
);

-- =====================
-- AUDIT LOGS
-- =====================
CREATE TABLE audit_logs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT,
  action VARCHAR(255) NOT NULL,
  details JSON,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- =====================
-- INDEXES
-- =====================
CREATE INDEX idx_bookings_user_id ON bookings(user_id);
CREATE INDEX idx_bookings_villa_id ON bookings(villa_id);
CREATE INDEX idx_bookings_status ON bookings(status);
CREATE INDEX idx_booking_dates_date ON booking_dates(date);
CREATE INDEX idx_payments_booking_id ON payments(booking_id);
CREATE INDEX idx_reviews_villa_id ON reviews(villa_id);
CREATE INDEX idx_affiliate_commissions_affiliate_user_id ON affiliate_commissions(affiliate_user_id);
CREATE INDEX idx_audit_logs_user_id ON audit_logs(user_id);
CREATE INDEX idx_audit_logs_created_at ON audit_logs(created_at);

SET FOREIGN_KEY_CHECKS=1;