-- MySQL/migrations/003_create_accreditation_tables.sql
CREATE TABLE IF NOT EXISTS `business_accreditations` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `business_id` INT NOT NULL,
    `accreditation_level` ENUM('basic', 'premium', 'verified') NOT NULL,
    `status` ENUM('pending', 'approved', 'rejected', 'expired', 'suspended', 'renewal_pending') DEFAULT 'pending',
    `application_date` DATETIME NOT NULL,
    `approved_date` DATETIME,
    `expiry_date` DATETIME,
    `renewal_applied_date` DATETIME,
    `reviewed_by` INT,
    `reviewed_at` DATETIME,
    `review_notes` TEXT,
    `documents` JSON,
    `created_at` DATETIME NOT NULL,
    `updated_at` DATETIME NOT NULL,
    INDEX `idx_business` (`business_id`),
    INDEX `idx_status` (`status`),
    INDEX `idx_level` (`accreditation_level`),
    INDEX `idx_application_date` (`application_date`),
    INDEX `idx_expiry_date` (`expiry_date`),
    FOREIGN KEY (`business_id`) REFERENCES `business_profiles`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `accreditation_history` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `accreditation_id` INT NOT NULL,
    `status` ENUM('pending', 'approved', 'rejected', 'expired', 'suspended', 'renewal_pending') NOT NULL,
    `notes` TEXT,
    `created_at` DATETIME NOT NULL,
    INDEX `idx_accreditation` (`accreditation_id`),
    INDEX `idx_status` (`status`),
    INDEX `idx_created_at` (`created_at`),
    FOREIGN KEY (`accreditation_id`) REFERENCES `business_accreditations`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
