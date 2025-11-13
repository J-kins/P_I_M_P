-- MySQL/migrations/012_create_communication_tables.sql
CREATE TABLE IF NOT EXISTS `messages` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `from_user_id` INT NOT NULL,
    `to_user_id` INT NOT NULL,
    `subject` VARCHAR(255) NOT NULL,
    `content` TEXT NOT NULL,
    `message_type` ENUM('email', 'system', 'notification') DEFAULT 'email',
    `is_read` BOOLEAN DEFAULT FALSE,
    `read_at` DATETIME,
    `parent_message_id` INT,
    `metadata` JSON,
    `created_at` DATETIME NOT NULL,
    `updated_at` DATETIME NOT NULL,
    INDEX `idx_from_user` (`from_user_id`),
    INDEX `idx_to_user` (`to_user_id`),
    INDEX `idx_message_type` (`message_type`),
    INDEX `idx_is_read` (`is_read`),
    INDEX `idx_created` (`created_at`),
    FOREIGN KEY (`from_user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`to_user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`parent_message_id`) REFERENCES `messages`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `notifications` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `user_id` INT NOT NULL,
    `type` ENUM('new_review', 'new_complaint', 'status_update', 'new_message', 'system_alert', 'promotional') NOT NULL,
    `title` VARCHAR(255) NOT NULL,
    `message` TEXT NOT NULL,
    `data` JSON,
    `status` ENUM('unread', 'read', 'dismissed') DEFAULT 'unread',
    `read_at` DATETIME,
    `action_url` VARCHAR(500),
    `expires_at` DATETIME,
    `created_at` DATETIME NOT NULL,
    INDEX `idx_user` (`user_id`),
    INDEX `idx_type` (`type`),
    INDEX `idx_status` (`status`),
    INDEX `idx_created` (`created_at`),
    INDEX `idx_expires` (`expires_at`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `chat_sessions` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `user_id_1` INT NOT NULL,
    `user_id_2` INT NOT NULL,
    `session_type` ENUM('direct', 'group', 'support') DEFAULT 'direct',
    `session_title` VARCHAR(255),
    `last_activity` DATETIME NOT NULL,
    `is_active` BOOLEAN DEFAULT TRUE,
    `metadata` JSON,
    `created_at` DATETIME NOT NULL,
    `updated_at` DATETIME NOT NULL,
    INDEX `idx_user1` (`user_id_1`),
    INDEX `idx_user2` (`user_id_2`),
    INDEX `idx_session_type` (`session_type`),
    INDEX `idx_last_activity` (`last_activity`),
    INDEX `idx_active` (`is_active`),
    UNIQUE KEY `unique_chat_session` (`user_id_1`, `user_id_2`, `session_type`),
    FOREIGN KEY (`user_id_1`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`user_id_2`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `chat_messages` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `chat_session_id` INT NOT NULL,
    `sender_id` INT NOT NULL,
    `message` TEXT NOT NULL,
    `message_type` ENUM('text', 'image', 'file', 'system') DEFAULT 'text',
    `is_read` BOOLEAN DEFAULT FALSE,
    `read_at` DATETIME,
    `metadata` JSON,
    `created_at` DATETIME NOT NULL,
    INDEX `idx_chat_session` (`chat_session_id`),
    INDEX `idx_sender` (`sender_id`),
    INDEX `idx_message_type` (`message_type`),
    INDEX `idx_is_read` (`is_read`),
    INDEX `idx_created` (`created_at`),
    FOREIGN KEY (`chat_session_id`) REFERENCES `chat_sessions`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`sender_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
