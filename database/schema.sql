-- NexusLedger Database Schema
-- Financial Portfolio Management System

CREATE DATABASE IF NOT EXISTS nexusledger CHARACTER SET utf8mb4;
USE nexusledger;

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(64) NOT NULL,
    email VARCHAR(100),
    full_name VARCHAR(100),
    role ENUM('admin','manager','user') DEFAULT 'user',
    balance DECIMAL(15,2) DEFAULT 0.00,
    currency VARCHAR(3) DEFAULT 'USD',
    session_token VARCHAR(64),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL
) ENGINE=InnoDB;

-- Transactions
CREATE TABLE IF NOT EXISTS transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    from_account VARCHAR(50),
    to_account VARCHAR(50),
    amount DECIMAL(15,2),
    type ENUM('deposit','withdrawal','transfer','payment','fee') DEFAULT 'transfer',
    status ENUM('pending','completed','failed','cancelled') DEFAULT 'pending',
    description TEXT,
    ref_number VARCHAR(32),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB;

-- Documents (uploads)
CREATE TABLE IF NOT EXISTS documents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    filename VARCHAR(255),
    original_name VARCHAR(255),
    file_type VARCHAR(50),
    file_size INT,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB;

-- Reports
CREATE TABLE IF NOT EXISTS reports (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    title VARCHAR(200),
    template VARCHAR(100),
    parameters TEXT,
    generated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB;

-- Comments (for transaction notes / XSS stored)
CREATE TABLE IF NOT EXISTS comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    username VARCHAR(50),
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB;

-- API tokens
CREATE TABLE IF NOT EXISTS api_tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    token VARCHAR(128),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB;

-- Audit logs
CREATE TABLE IF NOT EXISTS audit_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    action VARCHAR(100),
    details TEXT,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Insert sample data
INSERT INTO users (username, password, email, full_name, role, balance) VALUES
('admin', MD5('admin123'), 'admin@nexusledger.com', 'System Administrator', 'admin', 500000.00),
('john.doe', MD5('password123'), 'john@corp.com', 'John Doe', 'manager', 125000.50),
('jane.smith', MD5('password123'), 'jane@corp.com', 'Jane Smith', 'user', 87500.25),
('bob.wilson', MD5('password123'), 'bob@corp.com', 'Bob Wilson', 'user', 42000.00),
('alice.brown', MD5('password123'), 'alice@corp.com', 'Alice Brown', 'manager', 250000.75);

INSERT INTO transactions (user_id, from_account, to_account, amount, type, status, description, ref_number) VALUES
(2, 'ACC-1001', 'ACC-2005', 15000.00, 'transfer', 'completed', 'Quarterly investment transfer', 'TXN-001'),
(2, 'ACC-1001', 'ACC-3010', 500.00, 'payment', 'completed', 'Service fee payment', 'TXN-002'),
(3, 'ACC-2005', 'ACC-1001', 2500.00, 'transfer', 'completed', 'Invoice #INV-2024-089', 'TXN-003'),
(3, 'ACC-2005', 'EXT-9012', 1200.00, 'withdrawal', 'pending', 'Withdrawal request - pending approval', 'TXN-004'),
(4, 'ACC-3010', 'ACC-1001', 850.00, 'payment', 'completed', 'Consulting fees March 2024', 'TXN-005'),
(5, 'EXT-5000', 'ACC-4015', 50000.00, 'deposit', 'completed', 'Investment portfolio funding', 'TXN-006'),
(2, 'ACC-1001', 'ACC-4015', 30000.00, 'transfer', 'failed', 'Transfer failed - insufficient funds', 'TXN-007');

INSERT INTO comments (user_id, username, comment) VALUES
(2, 'john.doe', 'Great platform! The portfolio tracking is excellent.'),
(3, 'jane.smith', 'Requesting feature: automated tax reporting for Q3.'),
(4, 'bob.wilson', 'System maintenance scheduled for weekend.');
