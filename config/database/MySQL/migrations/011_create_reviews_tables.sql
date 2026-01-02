-- MySQL/migrations/011_create_reviews_tables.sql
CREATE TABLE IF NOT EXISTS `business_reviews` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `review_id` VARCHAR(50) UNIQUE NOT NULL,
    `user_id` INT NOT NULL,
    `business_id` INT NOT NULL,
    `title` VARCHAR(255) NOT NULL,
    `content` TEXT NOT NULL,
    `rating` DECIMAL(2,1) NOT NULL,
    `rating_breakdown` JSON,
    `status` ENUM('pending', 'approved', 'rejected', 'flagged', 'edited') DEFAULT 'pending',
    `status_reason` TEXT,
    `is_verified` BOOLEAN DEFAULT FALSE,
    `visit_date` DATE,
    `visit_type` ENUM('in_person', 'online', 'phone', 'other') DEFAULT 'in_person',
    `would_recommend` BOOLEAN,
    `updated_by` INT,
    `metadata` JSON,
    `created_at` DATETIME NOT NULL,
    `updated_at` DATETIME NOT NULL,
    INDEX `idx_review_id` (`review_id`),
    INDEX `idx_user` (`user_id`),
    INDEX `idx_business` (`business_id`),
    INDEX `idx_rating` (`rating`),
    INDEX `idx_status` (`status`),
    INDEX `idx_verified` (`is_verified`),
    INDEX `idx_created` (`created_at`),
    UNIQUE KEY `unique_user_business_review` (`user_id`, `business_id`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`business_id`) REFERENCES `business_profiles`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`updated_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `review_responses` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `review_id` INT NOT NULL,
    `respondent_id` INT NOT NULL,
    `response` TEXT NOT NULL,
    `created_at` DATETIME NOT NULL,
    `updated_at` DATETIME NOT NULL,
    UNIQUE KEY `unique_review_response` (`review_id`),
    INDEX `idx_review` (`review_id`),
    INDEX `idx_respondent` (`respondent_id`),
    INDEX `idx_created` (`created_at`),
    FOREIGN KEY (`review_id`) REFERENCES `business_reviews`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`respondent_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `review_media` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `review_id` INT NOT NULL,
    `file_name` VARCHAR(255) NOT NULL,
    `file_path` VARCHAR(500) NOT NULL,
    `file_type` VARCHAR(100) NOT NULL,
    `file_size` INT,
    `caption` VARCHAR(500),
    `display_order` INT DEFAULT 0,
    `created_at` DATETIME NOT NULL,
    INDEX `idx_review` (`review_id`),
    INDEX `idx_file_type` (`file_type`),
    INDEX `idx_display_order` (`display_order`),
    FOREIGN KEY (`review_id`) REFERENCES `business_reviews`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `review_votes` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `review_id` INT NOT NULL,
    `user_id` INT NOT NULL,
    `vote_type` ENUM('helpful', 'not_helpful') NOT NULL,
    `created_at` DATETIME NOT NULL,
    `updated_at` DATETIME NOT NULL,
    UNIQUE KEY `unique_review_user_vote` (`review_id`, `user_id`),
    INDEX `idx_review` (`review_id`),
    INDEX `idx_user` (`user_id`),
    INDEX `idx_vote_type` (`vote_type`),
    FOREIGN KEY (`review_id`) REFERENCES `business_reviews`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
