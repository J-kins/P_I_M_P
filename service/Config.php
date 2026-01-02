<?php
/**
 * P.I.M.P Configuration and URL handling
 * 
 * Provides configuration management and URL/asset path resolution
 */

namespace PIMP\Core;

class Config
{
    /**
     * @var array Configuration settings
     */
    private static $config = [];

    /**
     * @var string Base URL
     */
    private static $baseUrl = null;

    /**
     * @var string Base path
     */
    private static $basePath = null;

    /**
     * @var bool Initialization status
     */
    private static $initialized = false;

    /**
     * Initialize configuration
     */
    public static function init(): void
    {
        if (self::$initialized) {
            return;
        }

        self::detectBaseUrl();
        self::loadConfiguration();
        self::$initialized = true;
    }

    /**
     * Ensure initialization before any operation
     */
    private static function ensureInitialized(): void
    {
        if (!self::$initialized) {
            self::init();
        }
    }

    /**
     * Detect and set base URL
     */
    private static function detectBaseUrl(): void
    {
        // Check if running in CLI mode
        if (php_sapi_name() === 'cli') {
            self::$baseUrl = 'http://localhost/';
            self::$basePath = '/';
            return;
        }

        // Strict environment checks
        if (!isset($_SERVER['HTTP_HOST'], $_SERVER['REQUEST_URI'])) {
            // Fallback for edge cases
            self::$baseUrl = 'http://localhost/';
            self::$basePath = '/';
            return;
        }

        // Protocol detection with fallbacks
        $protocol = 'http://';
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
            $protocol = 'https://';
        } elseif (isset($_SERVER['REQUEST_SCHEME'])) {
            $protocol = $_SERVER['REQUEST_SCHEME'] . '://';
        } elseif (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443) {
            $protocol = 'https://';
        }

        // Host with port normalization
        $host = $_SERVER['HTTP_HOST'];
        
        // Only remove default ports
        if (strpos($host, ':80') !== false && $protocol === 'http://') {
            $host = str_replace(':80', '', $host);
        } elseif (strpos($host, ':443') !== false && $protocol === 'https://') {
            $host = str_replace(':443', '', $host);
        }

        // Subdirectory detection
        $script_name = isset($_SERVER['SCRIPT_NAME']) ? $_SERVER['SCRIPT_NAME'] : '';
        $script_name = str_replace('\\', '/', dirname($script_name));
        $base_path = $script_name === '/' ? '' : $script_name;

        // Final BASE_URL construction
        self::$baseUrl = rtrim($protocol . $host . $base_path, '/') . '/';
        self::$basePath = $base_path;
    }

    /**
     * Load configuration from files
     */
    private static function loadConfiguration(): void
    {
        // Load main app configuration
        $appConfigFile = __DIR__ . '/../config/app.php';
        if (file_exists($appConfigFile)) {
            $config = require $appConfigFile;
            if (is_array($config)) {
                self::$config = $config;
            }
        }

        // Set default theme
        self::$config['theme'] = self::$config['theme'] ?? 'purple1';
        self::$config['cdn_url'] = self::$config['cdn_url'] ?? '';
    }

    /**
     * Get configuration value
     * 
     * @param string $key Configuration key
     * @param mixed $default Default value if key doesn't exist
     * @return mixed
     */
    public static function get(string $key, $default = null)
    {
        self::ensureInitialized();
        return self::$config[$key] ?? $default;
    }

    /**
     * Set configuration value
     * 
     * @param string $key Configuration key
     * @param mixed $value Configuration value
     */
    public static function set(string $key, $value): void
    {
        self::ensureInitialized();
        self::$config[$key] = $value;
    }

    /**
     * Generates a full URL for the given path
     * 
     * @param string $path Path relative to base URL
     * @return string Full URL
     */
    public static function url(string $path = ''): string
    {
        self::ensureInitialized();
        
        // If path is already a full URL, return as-is
        if (preg_match('/^https?:\/\//', $path)) {
            return $path;
        }
        
        return self::$baseUrl . ltrim($path, '/');
    }

    /**
     * Generates URL for asset files with optional CDN support
     * 
     * @param string $path Path relative to assets directory
     * @param bool $useCdn Whether to use CDN (if configured)
     * @return string Full asset URL
     */
    public static function assetUrl(string $path = '', bool $useCdn = false): string
    {
        self::ensureInitialized();
        
        $cdnUrl = self::get('cdn_url');
        
        if ($useCdn && !empty($cdnUrl)) {
            return rtrim($cdnUrl, '/') . '/' . ltrim($path, '/');
        }
        
        return self::url(ltrim($path, '/'));
    }

    /**
     * Generates URL for CSS files in the styles directory
     * 
     * @param string $path Path relative to styles directory
     * @param bool $useCdn Whether to use CDN (if configured)
     * @return string Full CSS URL
     */
    public static function styleUrl(string $path = '', bool $useCdn = false): string
    {
        return self::assetUrl('styles/' . ltrim($path, '/'), $useCdn);
    }

    /**
     * Generates URL for JavaScript files
     * 
     * @param string $path Path relative to js directory
     * @param bool $useCdn Whether to use CDN (if configured)
     * @return string Full JS URL
     */
    public static function scriptUrl(string $path = '', bool $useCdn = false): string
    {
        return self::assetUrl('js/' . ltrim($path, '/'), $useCdn);
    }

    /**
     * Generates URL for image files
     * 
     * @param string $path Path relative to images directory
     * @param bool $useCdn Whether to use CDN (if configured)
     * @return string Full image URL
     */
    public static function imageUrl(string $path = '', bool $useCdn = false): string
    {
        return self::assetUrl('images/' . ltrim($path, '/'), $useCdn);
    }

    /**
     * Gets the current active theme
     * 
     * @return string Theme name
     */
    public static function getActiveTheme(): string
    {
        self::ensureInitialized();
        
        // Start session if not already started
        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }
        
        return $_SESSION['user_theme'] ?? self::get('theme', 'purple1');
    }

    /**
     * Set the active theme
     * 
     * @param string $theme Theme name
     */
    public static function setActiveTheme(string $theme): void
    {
        self::ensureInitialized();
        
        // Start session if not already started
        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }
        
        $_SESSION['user_theme'] = $theme;
    }

    /**
     * Get base URL
     * 
     * @return string Base URL
     */
    public static function getBaseUrl(): string
    {
        self::ensureInitialized();
        return self::$baseUrl ?? 'http://localhost/';
    }

    /**
     * Get base path
     * 
     * @return string Base path
     */
    public static function getBasePath(): string
    {
        self::ensureInitialized();
        return self::$basePath ?? '/';
    }

    /**
     * Check if running in development environment
     * 
     * @return bool
     */
    public static function isDevelopment(): bool
    {
        self::ensureInitialized();
        return self::get('environment', 'production') === 'development';
    }

    /**
     * Check if running in production environment
     * 
     * @return bool
     */
    public static function isProduction(): bool
    {
        self::ensureInitialized();
        return self::get('environment', 'production') === 'production';
    }

    /**
     * Get database configuration
     * 
     * @return array Database configuration
     */
    public static function getDatabaseConfig(): array
    {
        self::ensureInitialized();
        return self::get('database', []);
    }

    /**
     * Get session configuration
     * 
     * @return array Session configuration
     */
    public static function getSessionConfig(): array
    {
        self::ensureInitialized();
        return self::get('session', []);
    }

    /**
     * Get all configuration
     * 
     * @return array Complete configuration
     */
    public static function getAll(): array
    {
        self::ensureInitialized();
        return self::$config;
    }

    /**
     * Check if configuration is initialized
     * 
     * @return bool
     */
    public static function isInitialized(): bool
    {
        return self::$initialized;
    }

    /**
     * Reset configuration (useful for testing)
     */
    public static function reset(): void
    {
        self::$config = [];
        self::$baseUrl = null;
        self::$basePath = null;
        self::$initialized = false;
    }
}