-- MySQL/migrations/002_create_business_categories_tags.sql
CREATE TABLE IF NOT EXISTS `business_categories` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `name` VARCHAR(255) NOT NULL,
    `slug` VARCHAR(255) UNIQUE NOT NULL,
    `description` TEXT,
    `parent_id` INT DEFAULT NULL,
    `icon` VARCHAR(100),
    `color` VARCHAR(7),
    `is_featured` BOOLEAN DEFAULT FALSE,
    `display_order` INT DEFAULT 0,
    `status` ENUM('active', 'inactive', 'pending', 'deleted') DEFAULT 'active',
    `metadata` JSON,
    `created_at` DATETIME NOT NULL,
    `updated_at` DATETIME NOT NULL,
    INDEX `idx_slug` (`slug`),
    INDEX `idx_parent` (`parent_id`),
    INDEX `idx_status` (`status`),
    INDEX `idx_featured` (`is_featured`),
    INDEX `idx_display_order` (`display_order`),
    FOREIGN KEY (`parent_id`) REFERENCES `business_categories`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `business_tags` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `name` VARCHAR(100) NOT NULL,
    `slug` VARCHAR(100) UNIQUE NOT NULL,
    `description` TEXT,
    `status` ENUM('active', 'inactive', 'deleted') DEFAULT 'active',
    `created_at` DATETIME NOT NULL,
    `updated_at` DATETIME NOT NULL,
    INDEX `idx_slug` (`slug`),
    INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `business_category_mapping` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `business_id` INT NOT NULL,
    `category_id` INT NOT NULL,
    `created_at` DATETIME NOT NULL,
    UNIQUE KEY `unique_business_category` (`business_id`, `category_id`),
    INDEX `idx_business` (`business_id`),
    INDEX `idx_category` (`category_id`),
    FOREIGN KEY (`business_id`) REFERENCES `business_profiles`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`category_id`) REFERENCES `business_categories`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `business_tags_mapping` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `business_id` INT NOT NULL,
    `tag_id` INT NOT NULL,
    `created_at` DATETIME NOT NULL,
    UNIQUE KEY `unique_business_tag` (`business_id`, `tag_id`),
    INDEX `idx_business` (`business_id`),
    INDEX `idx_tag` (`tag_id`),
    FOREIGN KEY (`business_id`) REFERENCES `business_profiles`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`tag_id`) REFERENCES `business_tags`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default categories
INSERT INTO `business_categories` (`name`, `slug`, `description`, `display_order`, `created_at`, `updated_at`) VALUES
('Restaurants & Food', 'restaurants-food', 'Restaurants, cafes, food trucks, and food services', 1, NOW(), NOW()),
('Retail & Shopping', 'retail-shopping', 'Stores, shops, and retail businesses', 2, NOW(), NOW()),
('Home Services', 'home-services', 'Plumbing, electrical, cleaning, and home maintenance', 3, NOW(), NOW()),
('Healthcare', 'healthcare', 'Doctors, dentists, hospitals, and medical services', 4, NOW(), NOW()),
('Automotive', 'automotive', 'Car dealers, repair shops, and automotive services', 5, NOW(), NOW()),
('Professional Services', 'professional-services', 'Legal, accounting, consulting, and business services', 6, NOW(), NOW()),
('Beauty & Personal Care', 'beauty-personal-care', 'Salons, spas, barbers, and beauty services', 7, NOW(), NOW()),
('Education', 'education', 'Schools, tutors, training centers, and educational services', 8, NOW(), NOW()),
('Technology', 'technology', 'IT services, software development, and tech support', 9, NOW(), NOW()),
('Real Estate', 'real-estate', 'Agents, brokers, property management, and real estate services', 10, NOW(), NOW());
