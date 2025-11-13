-- MySQL/migrations/008_create_users_auth_tables.sql
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `user_id` VARCHAR(50) UNIQUE NOT NULL,
    `email` VARCHAR(255) UNIQUE NOT NULL,
    `password` VARCHAR(255) NOT NULL,
    `first_name` VARCHAR(100) NOT NULL,
    `last_name` VARCHAR(100) NOT NULL,
    `phone` VARCHAR(50),
    `avatar_url` VARCHAR(500),
    `user_type` ENUM('consumer', 'business_owner', 'business_representative', 'admin', 'moderator') NOT NULL,
    `status` ENUM('active', 'inactive', 'suspended', 'pending', 'verified') DEFAULT 'active',
    `verification_level` ENUM('none', 'basic', 'full') DEFAULT 'none',
    `email_verified` BOOLEAN DEFAULT FALSE,
    `last_login` DATETIME,
    `login_count` INT DEFAULT 0,
    `status_reason` TEXT,
    `metadata` JSON,
    `created_at` DATETIME NOT NULL,
    `updated_at` DATETIME NOT NULL,
    INDEX `idx_user_id` (`user_id`),
    INDEX `idx_email` (`email`),
    INDEX `idx_user_type` (`user_type`),
    INDEX `idx_status` (`status`),
    INDEX `idx_verification` (`verification_level`),
    INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `user_preferences` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `user_id` INT NOT NULL,
    `preference_key` VARCHAR(100) NOT NULL,
    `preference_value` TEXT,
    `created_at` DATETIME NOT NULL,
    `updated_at` DATETIME NOT NULL,
    UNIQUE KEY `unique_user_preference` (`user_id`, `preference_key`),
    INDEX `idx_user` (`user_id`),
    INDEX `idx_key` (`preference_key`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `user_saved_items` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `user_id` INT NOT NULL,
    `business_id` INT,
    `complaint_id` INT,
    `item_type` ENUM('business', 'complaint', 'review') NOT NULL,
    `category` VARCHAR(100) DEFAULT 'favorites',
    `notes` TEXT,
    `created_at` DATETIME NOT NULL,
    INDEX `idx_user` (`user_id`),
    INDEX `idx_business` (`business_id`),
    INDEX `idx_complaint` (`complaint_id`),
    INDEX `idx_type` (`item_type`),
    INDEX `idx_category` (`category`),
    UNIQUE KEY `unique_saved_item` (`user_id`, `business_id`, `complaint_id`, `item_type`, `category`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`business_id`) REFERENCES `business_profiles`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `user_sessions` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `user_id` INT NOT NULL,
    `session_token` VARCHAR(64) UNIQUE NOT NULL,
    `ip_address` VARCHAR(45),
    `user_agent` TEXT,
    `expires_at` DATETIME NOT NULL,
    `created_at` DATETIME NOT NULL,
    INDEX `idx_user` (`user_id`),
    INDEX `idx_token` (`session_token`),
    INDEX `idx_expires` (`expires_at`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `password_resets` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `user_id` INT NOT NULL,
    `reset_token` VARCHAR(64) UNIQUE NOT NULL,
    `expires_at` DATETIME NOT NULL,
    `created_at` DATETIME NOT NULL,
    INDEX `idx_user` (`user_id`),
    INDEX `idx_token` (`reset_token`),
    INDEX `idx_expires` (`expires_at`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `user_permissions` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `user_id` INT NOT NULL,
    `permission` VARCHAR(100) NOT NULL,
    `granted` BOOLEAN DEFAULT TRUE,
    `created_at` DATETIME NOT NULL,
    `updated_at` DATETIME NOT NULL,
    UNIQUE KEY `unique_user_permission` (`user_id`, `permission`),
    INDEX `idx_user` (`user_id`),
    INDEX `idx_permission` (`permission`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `login_attempts` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `email` VARCHAR(255) NOT NULL,
    `ip_address` VARCHAR(45),
    `user_agent` TEXT,
    `attempt_time` DATETIME NOT NULL,
    INDEX `idx_email` (`email`),
    INDEX `idx_ip` (`ip_address`),
    INDEX `idx_time` (`attempt_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
