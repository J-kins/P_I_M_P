-- Migration: 001_create_admin_tables.sql
-- Create administrative system tables

-- System logs table
CREATE TABLE IF NOT EXISTS system_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    level ENUM('info', 'warning', 'error', 'critical') NOT NULL DEFAULT 'info',
    message TEXT NOT NULL,
    context JSON,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_level (level),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Admin activities table
CREATE TABLE IF NOT EXISTS admin_activities (
    id INT PRIMARY KEY AUTO_INCREMENT,
    admin_id INT NOT NULL,
    activity_type ENUM(
        'user_management',
        'business_management', 
        'complaint_moderation',
        'review_moderation',
        'system_settings',
        'content_moderation'
    ) NOT NULL,
    description TEXT NOT NULL,
    ip_address VARCHAR(45),
    user_agent TEXT,
    metadata JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (admin_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_admin_id (admin_id),
    INDEX idx_activity_type (activity_type),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- System settings table
CREATE TABLE IF NOT EXISTS system_settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    setting_key VARCHAR(255) NOT NULL UNIQUE,
    setting_value TEXT NOT NULL,
    setting_type ENUM('string', 'integer', 'boolean', 'json') DEFAULT 'string',
    description TEXT,
    is_public BOOLEAN DEFAULT FALSE,
    created_by INT,
    updated_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_setting_key (setting_key),
    INDEX idx_is_public (is_public)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Admin sessions table
CREATE TABLE IF NOT EXISTS admin_sessions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    session_token VARCHAR(64) NOT NULL UNIQUE,
    ip_address VARCHAR(45),
    user_agent TEXT,
    expires_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_accessed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_session_token (session_token),
    INDEX idx_expires_at (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Flagged content table
CREATE TABLE IF NOT EXISTS flagged_content (
    id INT PRIMARY KEY AUTO_INCREMENT,
    content_id INT NOT NULL,
    content_type ENUM('review', 'complaint', 'business', 'user', 'message') NOT NULL,
    reason ENUM('spam', 'inappropriate', 'misinformation', 'fraud', 'harassment', 'other') NOT NULL,
    description TEXT,
    reporter_id INT NOT NULL,
    status ENUM('pending', 'resolved', 'dismissed') DEFAULT 'pending',
    resolution ENUM('approved', 'removed', 'edited', 'no_action'),
    resolution_notes TEXT,
    moderator_id INT,
    resolved_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (reporter_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (moderator_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_content_type (content_type),
    INDEX idx_status (status),
    INDEX idx_created_at (created_at),
    INDEX idx_reporter_id (reporter_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Spam detection table
CREATE TABLE IF NOT EXISTS spam_detection (
    id INT PRIMARY KEY AUTO_INCREMENT,
    content TEXT NOT NULL,
    content_type VARCHAR(50) NOT NULL,
    author_id INT NOT NULL,
    spam_score INT NOT NULL,
    confidence_level ENUM('low', 'medium', 'high', 'very_high') NOT NULL,
    detected_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    action_taken ENUM('flagged', 'removed', 'no_action') DEFAULT 'no_action',
    FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_author_id (author_id),
    INDEX idx_confidence_level (confidence_level),
    INDEX idx_detected_at (detected_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Fraud prevention table
CREATE TABLE IF NOT EXISTS fraud_prevention (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    activity_type VARCHAR(100) NOT NULL,
    fraud_score INT NOT NULL,
    risk_level ENUM('very_low', 'low', 'medium', 'high', 'very_high') NOT NULL,
    detected_patterns JSON,
    activity_data JSON,
    action_taken ENUM('monitor', 'flag', 'suspend', 'block') DEFAULT 'monitor',
    detected_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    resolved_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_risk_level (risk_level),
    INDEX idx_detected_at (detected_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Quality control table
CREATE TABLE IF NOT EXISTS quality_control (
    id INT PRIMARY KEY AUTO_INCREMENT,
    content_type VARCHAR(50) NOT NULL,
    content_id INT NOT NULL,
    quality_score INT NOT NULL,
    quality_rating ENUM('unacceptable', 'poor', 'fair', 'good', 'excellent') NOT NULL,
    issues_found JSON,
    improvement_suggestions JSON,
    checked_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_content_type (content_type),
    INDEX idx_quality_rating (quality_rating),
    INDEX idx_checked_at (checked_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- User warnings table
CREATE TABLE IF NOT EXISTS user_warnings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    admin_id INT NOT NULL,
    warning_type VARCHAR(100) NOT NULL,
    message TEXT NOT NULL,
    acknowledged BOOLEAN DEFAULT FALSE,
    acknowledged_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (admin_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_warning_type (warning_type),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Business accreditation workflows table
CREATE TABLE IF NOT EXISTS business_accreditation_workflows (
    id INT PRIMARY KEY AUTO_INCREMENT,
    business_id INT NOT NULL,
    workflow_type ENUM('initial', 'renewal', 'upgrade') DEFAULT 'initial',
    current_step VARCHAR(100) NOT NULL,
    steps_completed JSON,
    status ENUM('pending', 'in_progress', 'completed', 'cancelled') DEFAULT 'pending',
    assigned_to INT,
    due_date DATE,
    completed_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (business_id) REFERENCES business_profiles(id) ON DELETE CASCADE,
    FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_business_id (business_id),
    INDEX idx_status (status),
    INDEX idx_due_date (due_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Migration: 002_insert_default_settings.sql
-- Insert default system settings

INSERT IGNORE INTO system_settings (setting_key, setting_value, setting_type, description, is_public) VALUES
('system.name', 'P.I.M.P Business Repository', 'string', 'System name displayed throughout the platform', TRUE),
('system.version', '1.0.0', 'string', 'Current system version', TRUE),
('system.environment', 'production', 'string', 'System environment (development, staging, production)', FALSE),
('system.maintenance_mode', 'false', 'boolean', 'Whether system is in maintenance mode', TRUE),

('security.require_https', 'true', 'boolean', 'Require HTTPS for all connections', FALSE),
('security.password_min_length', '8', 'integer', 'Minimum password length', FALSE),
('security.max_login_attempts', '5', 'integer', 'Maximum failed login attempts before lockout', FALSE),
('security.session_timeout', '3600', 'integer', 'Session timeout in seconds', FALSE),

('email.from_address', 'noreply@pimp-platform.com', 'string', 'Default sender email address', FALSE),
('email.from_name', 'P.I.M.P Business Repository', 'string', 'Default sender name', FALSE),
('email.admin_notifications', 'true', 'boolean', 'Send email notifications to admins', FALSE),

('accreditation.auto_approve_threshold', '80', 'integer', 'Minimum score for auto-approval', FALSE),
('accreditation.review_period_days', '365', 'integer', 'Days between accreditation reviews', FALSE),
('accreditation.levels', '["basic", "verified", "premium", "elite"]', 'json', 'Available accreditation levels', TRUE),

('moderation.auto_flag_spam', 'true', 'boolean', 'Automatically flag content as spam', FALSE),
('moderation.spam_threshold', '70', 'integer', 'Spam score threshold for auto-flagging', FALSE),
('moderation.fraud_threshold', '50', 'integer', 'Fraud score threshold for action', FALSE),
('moderation.quality_threshold', '60', 'integer', 'Minimum quality score for content', FALSE),

('business.onboarding_required', 'true', 'boolean', 'Whether business onboarding is required', TRUE),
('business.auto_approve', 'false', 'boolean', 'Automatically approve new businesses', FALSE),
('business.verification_required', 'true', 'boolean', 'Require business verification', TRUE);

-- Migration: 003_create_admin_views.sql
-- Create useful admin views

CREATE OR REPLACE VIEW admin_dashboard_stats AS
SELECT 
    (SELECT COUNT(*) FROM users WHERE status = 'active') as total_users,
    (SELECT COUNT(*) FROM users WHERE DATE(created_at) = CURDATE()) as new_users_today,
    (SELECT COUNT(*) FROM business_profiles WHERE status = 'active') as total_businesses,
    (SELECT COUNT(*) FROM business_profiles WHERE status = 'pending') as pending_businesses,
    (SELECT COUNT(*) FROM complaints WHERE status IN ('new', 'under_review')) as open_complaints,
    (SELECT COUNT(*) FROM business_reviews WHERE status = 'pending') as pending_reviews,
    (SELECT COUNT(*) FROM flagged_content WHERE status = 'pending') as pending_flags,
    (SELECT COUNT(*) FROM admin_activities WHERE DATE(created_at) = CURDATE()) as admin_activities_today;

CREATE OR REPLACE VIEW admin_moderation_queue AS
SELECT 
    'review' as content_type,
    id as content_id,
    'Pending review moderation' as description,
    created_at
FROM business_reviews 
WHERE status = 'pending'

UNION ALL

SELECT 
    'complaint' as content_type,
    id as content_id,
    'Pending complaint moderation' as description,
    created_at
FROM complaints 
WHERE status IN ('new', 'under_review')

UNION ALL

SELECT 
    'flag' as content_type,
    id as content_id,
    CONCAT('Flagged content: ', reason) as description,
    created_at
FROM flagged_content 
WHERE status = 'pending'

ORDER BY created_at ASC;

CREATE OR REPLACE VIEW system_health_monitor AS
SELECT 
    'database' as component,
    (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE()) as metric_value,
    'table_count' as metric_name

UNION ALL

SELECT 
    'database' as component,
    ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) as metric_value,
    'size_mb' as metric_name
FROM information_schema.tables 
WHERE table_schema = DATABASE()

UNION ALL

SELECT 
    'system' as component,
    COUNT(*) as metric_value,
    'active_sessions' as metric_name
FROM admin_sessions 
WHERE expires_at > NOW()

UNION ALL

SELECT 
    'system' as component,
    COUNT(*) as metric_value,
    'errors_last_hour' as metric_name
FROM system_logs 
WHERE level IN ('error', 'critical') AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR);
