CREATE DATABASE IF NOT EXISTS referral_system;
USE referral_system;

CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    current_points INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_created_at (created_at)
);

CREATE TABLE binary_tree_structure (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    parent_id INT NULL,
    position ENUM('root', 'left', 'right') NOT NULL,
    level INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (parent_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user (user_id),
    INDEX idx_parent_position (parent_id, position),
    INDEX idx_level (level)
);

CREATE TABLE points_history (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    points INT NOT NULL,
    operation ENUM('add', 'set', 'subtract') NOT NULL,
    description VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_created (user_id, created_at)
);

CREATE TABLE referrals (
    id INT PRIMARY KEY AUTO_INCREMENT,
    referrer_id INT NOT NULL,
    referred_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (referrer_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (referred_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_referral (referred_id),
    INDEX idx_referrer (referrer_id),
    INDEX idx_created_at (created_at)
);


INSERT INTO users (id, name, current_points) VALUES 
(1, 'User 1', 0),
(2, 'User 2', 200), 
(3, 'User 3', 100);

INSERT INTO binary_tree_structure (user_id, parent_id, position, level) VALUES
(1, NULL, 'root', 0),
(2, 1, 'left', 1),
(3, 1, 'right', 1);

INSERT INTO referrals (referrer_id, referred_id) VALUES
(1, 2),
(1, 3);

INSERT INTO points_history (user_id, points, operation, description) VALUES
(2, 200, 'set', 'Initial Points'),
(3, 100, 'set', 'Initial Points');