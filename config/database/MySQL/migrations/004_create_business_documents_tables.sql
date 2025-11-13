-- MySQL/migrations/004_create_business_documents_tables.sql
CREATE TABLE IF NOT EXISTS `document_types` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `name` VARCHAR(255) NOT NULL,
    `slug` VARCHAR(255) UNIQUE NOT NULL,
    `description` TEXT,
    `category` ENUM('identification', 'business_registration', 'tax_documents', 'insurance', 'certificates', 'accreditation', 'other') NOT NULL,
    `required_for_accreditation` BOOLEAN DEFAULT FALSE,
    `accreditation_level` ENUM('none', 'basic', 'premium', 'verified') DEFAULT 'none',
    `requires_expiry` BOOLEAN DEFAULT FALSE,
    `max_file_size` INT DEFAULT 10485760, -- 10MB in bytes
    `allowed_extensions` JSON,
    `display_order` INT DEFAULT 0,
    `status` ENUM('active', 'inactive', 'deleted') DEFAULT 'active',
    `created_at` DATETIME NOT NULL,
    `updated_at` DATETIME NOT NULL,
    INDEX `idx_slug` (`slug`),
    INDEX `idx_category` (`category`),
    INDEX `idx_accreditation` (`required_for_accreditation`, `accreditation_level`),
    INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `business_documents` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `business_id` INT NOT NULL,
    `document_type` INT NOT NULL,
    `file_name` VARCHAR(255) NOT NULL,
    `file_path` VARCHAR(500) NOT NULL,
    `file_size` INT NOT NULL,
    `file_extension` VARCHAR(10),
    `mime_type` VARCHAR(100),
    `status` ENUM('pending', 'approved', 'rejected', 'expired') DEFAULT 'pending',
    `uploaded_at` DATETIME NOT NULL,
    `expiry_date` DATETIME,
    `reviewed_by` INT,
    `reviewed_at` DATETIME,
    `review_notes` TEXT,
    `metadata` JSON,
    `created_at` DATETIME NOT NULL,
    `updated_at` DATETIME NOT NULL,
    INDEX `idx_business` (`business_id`),
    INDEX `idx_document_type` (`document_type`),
    INDEX `idx_status` (`status`),
    INDEX `idx_uploaded` (`uploaded_at`),
    INDEX `idx_expiry` (`expiry_date`),
    FOREIGN KEY (`business_id`) REFERENCES `business_profiles`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`document_type`) REFERENCES `document_types`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default document types
INSERT INTO `document_types` (`name`, `slug`, `description`, `category`, `required_for_accreditation`, `accreditation_level`, `requires_expiry`, `allowed_extensions`, `display_order`, `created_at`, `updated_at`) VALUES
('Business License', 'business-license', 'Official business license or permit', 'business_registration', TRUE, 'basic', TRUE, '["pdf", "jpg", "jpeg", "png"]', 1, NOW(), NOW()),
('Tax Identification Number', 'tax-id', 'Business tax identification document', 'tax_documents', TRUE, 'basic', FALSE, '["pdf", "jpg", "jpeg", "png"]', 2, NOW(), NOW()),
('Proof of Address', 'proof-of-address', 'Utility bill or lease agreement showing business address', 'identification', TRUE, 'basic', FALSE, '["pdf", "jpg", "jpeg", "png"]', 3, NOW(), NOW()),
('Owner Identification', 'owner-id', 'Government-issued ID of business owner', 'identification', TRUE, 'basic', TRUE, '["pdf", "jpg", "jpeg", "png"]', 4, NOW(), NOW()),
('Insurance Certificate', 'insurance-certificate', 'Business liability insurance certificate', 'insurance', TRUE, 'premium', TRUE, '["pdf", "jpg", "jpeg", "png"]', 5, NOW(), NOW()),
('Professional Certifications', 'professional-certs', 'Industry-specific certifications or licenses', 'certificates', FALSE, 'premium', TRUE, '["pdf", "jpg", "jpeg", "png"]', 6, NOW(), NOW()),
('Business Registration', 'business-registration', 'Certificate of incorporation or business registration', 'business_registration', TRUE, 'basic', FALSE, '["pdf", "jpg", "jpeg", "png"]', 7, NOW(), NOW()),
('Bank Statement', 'bank-statement', 'Recent business bank statement', 'other', FALSE, 'verified', FALSE, '["pdf", "jpg", "jpeg", "png"]', 8, NOW(), NOW());
