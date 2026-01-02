<?php
/**
 * P.I.M.P Application Configuration
 * 
 * Main configuration file for the PIMP application
 */

return [
    /**
     * Application Environment
     * Options: 'development', 'staging', 'production'
     */
    'environment' => getenv('APP_ENV') ?: 'development',

    /**
     * Application Name
     */
    'app_name' => 'PIMP - Business Repository Platform',

    /**
     * Application Version
     */
    'version' => '1.0.0',

    /**
     * Default Theme
     * Available themes: purple1, berry, coffee, grayscale, oceanic, slate
     */
    'theme' => 'purple1',

    /**
     * CDN URL (optional)
     * Leave empty to use local assets
     */
    'cdn_url' => '',

    /**
     * Database Configuration
     */
    'database' => [
        'driver' => 'mysql',
        'host' => getenv('DB_HOST') ?: 'localhost',
        'port' => getenv('DB_PORT') ?: '3306',
        'database' => getenv('DB_NAME') ?: 'pimp',
        'username' => getenv('DB_USER') ?: 'root',
        'password' => getenv('DB_PASS') ?: '',
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
        'prefix' => '',
    ],

    /**
     * Session Configuration
     */
    'session' => [
        'name' => 'PIMP_SESSION',
        'lifetime' => 7200, // 2 hours
        'path' => '/',
        'domain' => '',
        'secure' => false,
        'httponly' => true,
        'samesite' => 'Lax',
    ],

    /**
     * Security Configuration
     */
    'security' => [
        'csrf_protection' => true,
        'encryption_key' => getenv('APP_KEY') ?: 'your-secret-key-here',
        'password_min_length' => 8,
    ],

    /**
     * Upload Configuration
     */
    'uploads' => [
        'max_size' => 10485760, // 10MB in bytes
        'allowed_types' => ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx'],
        'path' => __DIR__ . '/../uploads',
    ],

    /**
     * Pagination Configuration
     */
    'pagination' => [
        'per_page' => 20,
        'max_per_page' => 100,
    ],

    /**
     * Cache Configuration
     */
    'cache' => [
        'enabled' => true,
        'driver' => 'file',
        'path' => __DIR__ . '/../tmp/cache',
        'lifetime' => 3600, // 1 hour
    ],

    /**
     * Mail Configuration
     */
    'mail' => [
        'driver' => 'smtp',
        'host' => getenv('MAIL_HOST') ?: 'localhost',
        'port' => getenv('MAIL_PORT') ?: 587,
        'username' => getenv('MAIL_USER') ?: '',
        'password' => getenv('MAIL_PASS') ?: '',
        'encryption' => 'tls',
        'from_address' => 'noreply@pimp.local',
        'from_name' => 'PIMP Platform',
    ],

    /**
     * Logging Configuration
     */
    'logging' => [
        'enabled' => true,
        'level' => 'debug', // debug, info, warning, error
        'path' => __DIR__ . '/../tmp/logs',
    ],

    /**
     * API Configuration
     */
    'api' => [
        'enabled' => true,
        'rate_limit' => 100, // requests per minute
        'version' => 'v1',
    ],

    /**
     * Social Media Links
     */
    'social' => [
        'facebook' => '',
        'twitter' => '',
        'linkedin' => '',
        'instagram' => '',
    ],

    /**
     * Feature Flags
     */
    'features' => [
        'reviews' => true,
        'comments' => true,
        'ratings' => true,
        'social_login' => false,
        'two_factor_auth' => false,
    ],

    /**
     * Locale Configuration
     */
    'locale' => [
        'default' => 'en',
        'available' => ['en', 'fr', 'es'],
        'timezone' => 'UTC',
    ],
];