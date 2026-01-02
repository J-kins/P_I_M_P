-- MySQL/migrations/010_create_complaints_tables.sql
CREATE TABLE IF NOT EXISTS `complaints` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `complaint_id` VARCHAR(50) UNIQUE NOT NULL,
    `user_id` INT NOT NULL,
    `business_id` INT NOT NULL,
    `title` VARCHAR(255) NOT NULL,
    `description` TEXT NOT NULL,
    `complaint_type` ENUM('service_quality', 'product_issue', 'billing', 'fraud', 'misrepresentation', 'safety', 'other') NOT NULL,
    `status` ENUM('new', 'in_progress', 'under_review', 'resolved', 'closed', 'rejected', 'escalated') DEFAULT 'new',
    `priority` ENUM('low', 'medium', 'high', 'urgent') DEFAULT 'medium',
    `amount_involved` DECIMAL(10, 2),
    `desired_resolution` TEXT,
    `resolved_at` DATETIME,
    `escalated_at` DATETIME,
    `escalated_by` INT,
    `escalation_reason` TEXT,
    `updated_by` INT,
    `metadata` JSON,
    `created_at` DATETIME NOT NULL,
    `updated_at` DATETIME NOT NULL,
    INDEX `idx_complaint_id` (`complaint_id`),
    INDEX `idx_user` (`user_id`),
    INDEX `idx_business` (`business_id`),
    INDEX `idx_status` (`status`),
    INDEX `idx_priority` (`priority`),
    INDEX `idx_type` (`complaint_type`),
    INDEX `idx_created` (`created_at`),
    INDEX `idx_resolved` (`resolved_at`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`business_id`) REFERENCES `business_profiles`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`escalated_by`) REFERENCES `users`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`updated_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `complaint_threads` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `complaint_id` INT NOT NULL,
    `user_id` INT NOT NULL,
    `message` TEXT NOT NULL,
    `message_type` ENUM('message', 'status_update', 'priority_update', 'resolution_note', 'internal_note') DEFAULT 'message',
    `is_internal` BOOLEAN DEFAULT FALSE,
    `created_at` DATETIME NOT NULL,
    INDEX `idx_complaint` (`complaint_id`),
    INDEX `idx_user` (`user_id`),
    INDEX `idx_type` (`message_type`),
    INDEX `idx_internal` (`is_internal`),
    INDEX `idx_created` (`created_at`),
    FOREIGN KEY (`complaint_id`) REFERENCES `complaints`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `complaint_evidence` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `complaint_id` INT NOT NULL,
    `uploaded_by` INT NOT NULL,
    `file_name` VARCHAR(255) NOT NULL,
    `file_path` VARCHAR(500) NOT NULL,
    `file_type` VARCHAR(100) NOT NULL,
    `file_size` INT,
    `description` TEXT,
    `metadata` JSON,
    `created_at` DATETIME NOT NULL,
    INDEX `idx_complaint` (`complaint_id`),
    INDEX `idx_uploaded_by` (`uploaded_by`),
    INDEX `idx_file_type` (`file_type`),
    FOREIGN KEY (`complaint_id`) REFERENCES `complaints`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`uploaded_by`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `complaint_categories` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `name` VARCHAR(255) NOT NULL,
    `slug` VARCHAR(255) UNIQUE NOT NULL,
    `description` TEXT,
    `is_active` BOOLEAN DEFAULT TRUE,
    `display_order` INT DEFAULT 0,
    `created_at` DATETIME NOT NULL,
    `updated_at` DATETIME NOT NULL,
    INDEX `idx_slug` (`slug`),
    INDEX `idx_active` (`is_active`),
    INDEX `idx_display_order` (`display_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default complaint categories
INSERT INTO `complaint_categories` (`name`, `slug`, `description`, `display_order`, `created_at`, `updated_at`) VALUES
('Service Quality', 'service-quality', 'Issues related to service delivery and quality', 1, NOW(), NOW()),
('Product Issues', 'product-issues', 'Problems with products or merchandise', 2, NOW(), NOW()),
('Billing & Payments', 'billing-payments', 'Disputes related to billing and payment processing', 3, NOW(), NOW()),
('Fraud & Scams', 'fraud-scams', 'Reports of fraudulent activities or scams', 4, NOW(), NOW()),
('Misrepresentation', 'misrepresentation', 'False advertising or misrepresentation of services', 5, NOW(), NOW()),
('Safety Concerns', 'safety-concerns', 'Health and safety related issues', 6, NOW(), NOW()),
('Other Issues', 'other-issues', 'Other types of complaints not categorized', 7, NOW(), NOW());
