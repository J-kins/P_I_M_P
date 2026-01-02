-- MySQL/migrations/009_create_search_history_tables.sql
CREATE TABLE IF NOT EXISTS `user_search_history` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `user_id` INT NOT NULL,
    `search_query` VARCHAR(500) NOT NULL,
    `search_type` ENUM('business', 'category', 'location', 'review', 'complaint') DEFAULT 'business',
    `filters` JSON,
    `result_count` INT DEFAULT 0,
    `ip_address` VARCHAR(45),
    `user_agent` TEXT,
    `created_at` DATETIME NOT NULL,
    INDEX `idx_user` (`user_id`),
    INDEX `idx_query` (`search_query`(255)),
    INDEX `idx_type` (`search_type`),
    INDEX `idx_created` (`created_at`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `user_search_preferences` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `user_id` INT NOT NULL,
    `preference_key` VARCHAR(100) NOT NULL,
    `preference_value` TEXT,
    `created_at` DATETIME NOT NULL,
    `updated_at` DATETIME NOT NULL,
    UNIQUE KEY `unique_user_search_preference` (`user_id`, `preference_key`),
    INDEX `idx_user` (`user_id`),
    INDEX `idx_key` (`preference_key`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
