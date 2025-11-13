-- Core sequence management for distributed systems
CREATE TABLE sequences (
    sequence_name VARCHAR(100) PRIMARY KEY,
    current_value BIGINT NOT NULL DEFAULT 0,
    increment_by INT NOT NULL DEFAULT 1,
    max_value BIGINT,
    cycle BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Core users table with flexible name structure
CREATE TABLE users (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    uuid CHAR(36) NOT NULL UNIQUE DEFAULT (UUID()),
    username VARCHAR(150) NOT NULL UNIQUE,
    name_json JSON NOT NULL, -- Flexible name structure
    email VARCHAR(255) NOT NULL UNIQUE,
    email_verified BOOLEAN DEFAULT FALSE,
    phone_json JSON, -- { "primary": "+1234567890", "secondary": "+0987654321", "verified": true }
    avatar_url VARCHAR(500),
    date_of_birth DATE,
    gender ENUM('male', 'female', 'non_binary', 'prefer_not_to_say', 'other'),
    timezone VARCHAR(50) DEFAULT 'UTC',
    locale VARCHAR(10) DEFAULT 'en_US',
    status ENUM('active', 'inactive', 'suspended', 'pending_verification') DEFAULT 'pending_verification',
    last_login_at TIMESTAMP NULL,
    login_attempts INT DEFAULT 0,
    password_changed_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    
    INDEX idx_username (username),
    INDEX idx_email (email),
    INDEX idx_status (status),
    INDEX idx_created_at (created_at),
    INDEX idx_deleted_at (deleted_at)
);

-- User authentication credentials
CREATE TABLE user_credentials (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT NOT NULL,
    credential_type ENUM('password', 'oauth_google', 'oauth_facebook', 'sso') DEFAULT 'password',
    credential_value VARCHAR(500), -- Hashed password or OAuth token
    salt VARCHAR(100),
    expires_at TIMESTAMP NULL,
    is_primary BOOLEAN DEFAULT TRUE,
    metadata_json JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_credential (user_id, credential_type)
);

-- Flexible address system with GeoJSON support
CREATE TABLE addresses (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    uuid CHAR(36) NOT NULL UNIQUE DEFAULT (UUID()),
    addressable_type ENUM('user', 'business', 'branch') NOT NULL,
    addressable_id BIGINT NOT NULL,
    address_type ENUM('home', 'work', 'billing', 'shipping', 'primary', 'branch') DEFAULT 'primary',
    address_json JSON NOT NULL, -- GeoJSON format with spatial data
    is_primary BOOLEAN DEFAULT FALSE,
    verification_status ENUM('pending', 'verified', 'failed') DEFAULT 'pending',
    verified_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Spatial index for GeoJSON data (MySQL 5.7+)
    SPATIAL INDEX idx_address_geo ( (ST_GeomFromGeoJSON(address_json->'$.geometry')) ),
    INDEX idx_addressable (addressable_type, addressable_id),
    INDEX idx_primary (is_primary),
    INDEX idx_verification_status (verification_status)
);

-- User roles and permissions
CREATE TABLE roles (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    permissions_json JSON NOT NULL, -- Flexible permission structure
    is_system_role BOOLEAN DEFAULT FALSE,
    hierarchy_level INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_hierarchy (hierarchy_level)
);

-- User-role assignments
CREATE TABLE user_roles (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT NOT NULL,
    role_id BIGINT NOT NULL,
    assigned_by BIGINT NOT NULL,
    effective_from TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    effective_until TIMESTAMP NULL,
    metadata_json JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
    FOREIGN KEY (assigned_by) REFERENCES users(id),
    UNIQUE KEY unique_user_role (user_id, role_id, effective_from),
    INDEX idx_effective_dates (effective_from, effective_until)
);

-- System settings (global configuration)
CREATE TABLE system_settings (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    setting_key VARCHAR(255) NOT NULL UNIQUE,
    setting_value JSON NOT NULL,
    data_type ENUM('string', 'number', 'boolean', 'array', 'object') DEFAULT 'string',
    category VARCHAR(100) DEFAULT 'general',
    description TEXT,
    is_public BOOLEAN DEFAULT FALSE,
    min_value JSON,
    max_value JSON,
    options_json JSON, -- Available options for select fields
    updated_by BIGINT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (updated_by) REFERENCES users(id),
    INDEX idx_category (category),
    INDEX idx_public (is_public)
);

-- User-specific settings
CREATE TABLE user_settings (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT NOT NULL,
    setting_key VARCHAR(255) NOT NULL,
    setting_value JSON NOT NULL,
    data_type ENUM('string', 'number', 'boolean', 'array', 'object') DEFAULT 'string',
    category VARCHAR(100) DEFAULT 'preferences',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_setting (user_id, setting_key),
    INDEX idx_user_category (user_id, category)
);

-- Business categories and taxonomy
CREATE TABLE business_categories (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    uuid CHAR(36) NOT NULL UNIQUE DEFAULT (UUID()),
    name VARCHAR(255) NOT NULL,
    description TEXT,
    parent_id BIGINT NULL,
    path VARCHAR(1000), -- Materialized path for hierarchy
    depth INT DEFAULT 0,
    icon_url VARCHAR(500),
    metadata_json JSON,
    is_active BOOLEAN DEFAULT TRUE,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (parent_id) REFERENCES business_categories(id),
    INDEX idx_parent (parent_id),
    INDEX idx_path (path(255)),
    INDEX idx_sort (sort_order)
);

-- Core businesses table
CREATE TABLE businesses (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    uuid CHAR(36) NOT NULL UNIQUE DEFAULT (UUID()),
    legal_name VARCHAR(500) NOT NULL,
    trading_name VARCHAR(500),
    description TEXT,
    business_type ENUM('sole_proprietorship', 'partnership', 'corporation', 'llc', 'non_profit', 'government', 'other') DEFAULT 'corporation',
    industry_sector VARCHAR(255),
    category_id BIGINT,
    registration_number VARCHAR(100),
    tax_id VARCHAR(100),
    founding_date DATE,
    website_url VARCHAR(500),
    logo_url VARCHAR(500),
    cover_image_url VARCHAR(500),
    contact_info_json JSON NOT NULL, -- Structured contact information
    social_media_json JSON, -- Social media links
    operating_hours_json JSON, -- Business hours
    payment_methods_json JSON, -- Accepted payment methods
    status ENUM('pending', 'active', 'suspended', 'verified', 'rejected') DEFAULT 'pending',
    verification_level ENUM('unverified', 'basic', 'enhanced', 'premium') DEFAULT 'unverified',
    trust_score DECIMAL(3,2) DEFAULT 0.00, -- 0.00 to 5.00
    total_reviews INT DEFAULT 0,
    average_rating DECIMAL(2,1) DEFAULT 0.0,
    owner_user_id BIGINT NOT NULL,
    verified_at TIMESTAMP NULL,
    verified_by BIGINT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    
    FOREIGN KEY (category_id) REFERENCES business_categories(id),
    FOREIGN KEY (owner_user_id) REFERENCES users(id),
    FOREIGN KEY (verified_by) REFERENCES users(id),
    INDEX idx_legal_name (legal_name),
    INDEX idx_trading_name (trading_name),
    INDEX idx_category (category_id),
    INDEX idx_status (status),
    INDEX idx_trust_score (trust_score),
    INDEX idx_verified (verified_at)
);

-- Business branches/locations
CREATE TABLE business_branches (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    uuid CHAR(36) NOT NULL UNIQUE DEFAULT (UUID()),
    business_id BIGINT NOT NULL,
    branch_name VARCHAR(255),
    is_headquarters BOOLEAN DEFAULT FALSE,
    manager_user_id BIGINT,
    contact_info_json JSON,
    status ENUM('active', 'inactive', 'closed') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (business_id) REFERENCES businesses(id) ON DELETE CASCADE,
    FOREIGN KEY (manager_user_id) REFERENCES users(id),
    INDEX idx_business (business_id),
    INDEX idx_headquarters (is_headquarters)
);

-- Business accreditation and certifications
CREATE TABLE accreditations (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    uuid CHAR(36) NOT NULL UNIQUE DEFAULT (UUID()),
    business_id BIGINT NOT NULL,
    accreditation_type ENUM('bbb', 'iso', 'industry', 'government', 'other') NOT NULL,
    accrediting_body VARCHAR(255) NOT NULL,
    certificate_number VARCHAR(100),
    accreditation_level VARCHAR(100),
    issue_date DATE NOT NULL,
    expiry_date DATE,
    verification_status ENUM('pending', 'verified', 'expired', 'revoked') DEFAULT 'pending',
    supporting_documents_json JSON, -- Document references
    verified_at TIMESTAMP NULL,
    verified_by BIGINT NULL,
    metadata_json JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (business_id) REFERENCES businesses(id) ON DELETE CASCADE,
    FOREIGN KEY (verified_by) REFERENCES users(id),
    INDEX idx_business (business_id),
    INDEX idx_type (accreditation_type),
    INDEX idx_expiry (expiry_date),
    INDEX idx_status (verification_status)
);

-- Customer reviews and ratings
CREATE TABLE business_reviews (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    uuid CHAR(36) NOT NULL UNIQUE DEFAULT (UUID()),
    business_id BIGINT NOT NULL,
    reviewer_user_id BIGINT NOT NULL,
    rating TINYINT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    title VARCHAR(255),
    review_text TEXT,
    review_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    experience_date DATE, -- When the experience occurred
    is_verified_purchase BOOLEAN DEFAULT FALSE,
    status ENUM('pending', 'approved', 'rejected', 'flagged') DEFAULT 'pending',
    helpful_votes INT DEFAULT 0,
    total_votes INT DEFAULT 0,
    moderator_notes TEXT,
    moderated_at TIMESTAMP NULL,
    moderated_by BIGINT NULL,
    response_from_business TEXT,
    responded_at TIMESTAMP NULL,
    metadata_json JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (business_id) REFERENCES businesses(id) ON DELETE CASCADE,
    FOREIGN KEY (reviewer_user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (moderated_by) REFERENCES users(id),
    INDEX idx_business_rating (business_id, rating),
    INDEX idx_reviewer (reviewer_user_id),
    INDEX idx_status (status),
    INDEX idx_experience_date (experience_date)
);

-- Review rating categories (specific aspects)
CREATE TABLE review_ratings (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    review_id BIGINT NOT NULL,
    rating_category ENUM('quality', 'service', 'value', 'communication', 'reliability') NOT NULL,
    rating TINYINT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (review_id) REFERENCES business_reviews(id) ON DELETE CASCADE,
    UNIQUE KEY unique_review_category (review_id, rating_category),
    INDEX idx_category_rating (rating_category, rating)
);

-- Complaints system
CREATE TABLE complaints (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    uuid CHAR(36) NOT NULL UNIQUE DEFAULT (UUID()),
    complaint_type ENUM('service', 'product', 'billing', 'delivery', 'safety', 'fraud', 'other') NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    complainant_user_id BIGINT NOT NULL,
    business_id BIGINT NOT NULL,
    complaint_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    desired_resolution TEXT,
    status ENUM('open', 'in_progress', 'resolved', 'closed', 'escalated') DEFAULT 'open',
    priority ENUM('low', 'medium', 'high', 'critical') DEFAULT 'medium',
    assigned_to BIGINT NULL,
    resolution_details TEXT,
    resolved_at TIMESTAMP NULL,
    resolved_by BIGINT NULL,
    complainant_satisfaction TINYINT CHECK (satisfaction >= 1 AND satisfaction <= 5),
    metadata_json JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (complainant_user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (business_id) REFERENCES businesses(id) ON DELETE CASCADE,
    FOREIGN KEY (assigned_to) REFERENCES users(id),
    FOREIGN KEY (resolved_by) REFERENCES users(id),
    INDEX idx_business_status (business_id, status),
    INDEX idx_complainant (complainant_user_id),
    INDEX idx_priority (priority),
    INDEX idx_complaint_date (complaint_date)
);

-- User sessions with detailed activity tracking
CREATE TABLE user_sessions (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT NOT NULL,
    session_token VARCHAR(500) NOT NULL UNIQUE,
    ip_address VARCHAR(45),
    user_agent TEXT,
    device_info_json JSON, -- Device fingerprinting data
    location_json JSON, -- Geo location data
    login_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_activity_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    logout_time TIMESTAMP NULL,
    logout_reason ENUM('user', 'timeout', 'system', 'security') NULL,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_active (user_id, is_active),
    INDEX idx_token (session_token),
    INDEX idx_expires (expires_at)
);

-- Comprehensive audit trail
CREATE TABLE audit_logs (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    event_timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    event_type VARCHAR(100) NOT NULL,
    user_id BIGINT NULL, -- NULL for system events
    entity_type VARCHAR(100) NOT NULL, -- 'user', 'business', 'review', etc.
    entity_id BIGINT NOT NULL,
    action VARCHAR(100) NOT NULL, -- 'create', 'update', 'delete', 'login', etc.
    old_values_json JSON, -- Previous state
    new_values_json JSON, -- New state
    changes_json JSON, -- What actually changed
    ip_address VARCHAR(45),
    user_agent TEXT,
    request_id VARCHAR(100), -- For tracing requests across systems
    metadata_json JSON,
    
    INDEX idx_event_timestamp (event_timestamp),
    INDEX idx_user_entity (user_id, entity_type, entity_id),
    INDEX idx_event_type (event_type),
    INDEX idx_request (request_id)
);

-- File attachments storage
CREATE TABLE attachments (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    uuid CHAR(36) NOT NULL UNIQUE DEFAULT (UUID()),
    attachable_type VARCHAR(100) NOT NULL, -- Polymorphic association
    attachable_id BIGINT NOT NULL,
    file_name VARCHAR(500) NOT NULL,
    file_size BIGINT NOT NULL,
    mime_type VARCHAR(100) NOT NULL,
    file_data LONGBLOB NOT NULL, -- Actual file content
    thumbnail_data LONGBLOB, -- Thumbnail for images
    description TEXT,
    uploader_user_id BIGINT NOT NULL,
    is_public BOOLEAN DEFAULT FALSE,
    metadata_json JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (uploader_user_id) REFERENCES users(id),
    INDEX idx_attachable (attachable_type, attachable_id),
    INDEX idx_mime_type (mime_type),
    INDEX idx_public (is_public)
);

-- Time-series data for analytics
CREATE TABLE time_series_data (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    metric_name VARCHAR(255) NOT NULL,
    metric_value DECIMAL(15,4) NOT NULL,
    timestamp TIMESTAMP NOT NULL,
    entity_type VARCHAR(100),
    entity_id BIGINT,
    dimensions_json JSON, -- Additional dimensions for the metric
    tags_json JSON, -- Key-value tags
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_metric_timestamp (metric_name, timestamp),
    INDEX idx_entity (entity_type, entity_id),
    INDEX idx_timestamp (timestamp)
);

-- Financial ledger for transactions
CREATE TABLE ledger_entries (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    entry_type ENUM('debit', 'credit') NOT NULL,
    account_type VARCHAR(100) NOT NULL,
    amount DECIMAL(15,2) NOT NULL,
    currency VARCHAR(3) DEFAULT 'USD',
    reference_type VARCHAR(100), -- 'subscription', 'refund', 'purchase', etc.
    reference_id BIGINT,
    description TEXT,
    effective_date DATE NOT NULL,
    posted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    metadata_json JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_account_type (account_type),
    INDEX idx_effective_date (effective_date),
    INDEX idx_reference (reference_type, reference_id)
);

-- Vector data for spatial analytics
CREATE TABLE vector_data (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    vector_type VARCHAR(100) NOT NULL,
    geometry_data JSON NOT NULL, -- GeoJSON geometry
    properties_json JSON NOT NULL, -- GeoJSON properties
    bounding_box JSON, -- Pre-calculated bounding box
    business_id BIGINT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (business_id) REFERENCES businesses(id) ON DELETE SET NULL,
    SPATIAL INDEX idx_geometry ( (ST_GeomFromGeoJSON(geometry_data)) ),
    INDEX idx_vector_type (vector_type),
    INDEX idx_business (business_id)
);

-- Custom entity definitions for user-defined tables
CREATE TABLE entity_definitions (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL UNIQUE,
    description TEXT,
    schema_json JSON NOT NULL, -- JSON Schema for validation
    owner_user_id BIGINT NOT NULL,
    business_id BIGINT, -- NULL for system-wide entities
    is_public BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (owner_user_id) REFERENCES users(id),
    FOREIGN KEY (business_id) REFERENCES businesses(id) ON DELETE CASCADE,
    INDEX idx_owner (owner_user_id),
    INDEX idx_business (business_id)
);

-- Dynamic entity instances
CREATE TABLE entity_instances (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    entity_definition_id BIGINT NOT NULL,
    data_json JSON NOT NULL, -- Actual entity data
    owner_user_id BIGINT NOT NULL,
    business_id BIGINT,
    status ENUM('draft', 'active', 'archived') DEFAULT 'draft',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (entity_definition_id) REFERENCES entity_definitions(id) ON DELETE CASCADE,
    FOREIGN KEY (owner_user_id) REFERENCES users(id),
    FOREIGN KEY (business_id) REFERENCES businesses(id) ON DELETE CASCADE,
    INDEX idx_definition (entity_definition_id),
    INDEX idx_owner (owner_user_id),
    INDEX idx_business (business_id)
);

-- Insert initial system roles
INSERT INTO roles (name, description, permissions_json, is_system_role, hierarchy_level) VALUES
('super_admin', 'Full system access', '["*"]', TRUE, 100),
('admin', 'Administrative access', '["users.manage", "businesses.manage", "reviews.moderate", "complaints.manage", "system.settings.read"]', TRUE, 90),
('business_owner', 'Business owner privileges', '["business.manage", "reviews.respond", "analytics.view"]', FALSE, 50),
('verified_user', 'Trusted user with verification', '["reviews.create", "complaints.create", "businesses.view"]', FALSE, 30),
('user', 'Basic user account', '["reviews.create", "businesses.view"]', FALSE, 10);

-- Insert essential system settings
INSERT INTO system_settings (setting_key, setting_value, data_type, category, description, is_public, updated_by) VALUES
('system.name', '"PIMP Business Repository"', 'string', 'general', 'Platform display name', TRUE, 1),
('system.description', '"Trusted Business Directory Platform"', 'string', 'general', 'Platform description', TRUE, 1),
('user.registration.enabled', 'true', 'boolean', 'security', 'Allow new user registrations', FALSE, 1),
('business.verification.required', 'true', 'boolean', 'business', 'Require business verification', FALSE, 1),
('review.moderation.required', 'true', 'boolean', 'reviews', 'Require review moderation', FALSE, 1);