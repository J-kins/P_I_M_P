
<?php
/**
 * Configuration and URL handling for Creative UI Template System
 * 
 * Provides helper functions for path resolution and asset management
 */

// Strict environment checks
if (!isset($_SERVER['HTTP_HOST'], $_SERVER['REQUEST_URI'])) {
    die('Server configuration error: Missing host/URI data');
}

// Protocol detection with fallbacks
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' 
    ? 'https://' 
    : (isset($_SERVER['REQUEST_SCHEME']) ? $_SERVER['REQUEST_SCHEME'] . '://' : 'http://');

// Host with port normalization
$host = $_SERVER['HTTP_HOST'];
$host = str_replace([':80', ':443', ':8080'], '', $host); // Clean common ports

// Subdirectory detection
$script_name = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
$base_path = $script_name === '/' ? '' : $script_name;

// Final BASE_URL construction
define('BASE_URL', rtrim($protocol . $host . $base_path, '/') . '/');

// Default theme setting
define('DEFAULT_THEME', 'purple1');

/**
 * Generates a full URL for the given path
 * 
 * @param string $path Path relative to base URL
 * @return string Full URL
 */
function url($path = '') {
    return BASE_URL . ltrim($path, '/');
}

/**
 * Generates URL for asset files with optional CDN support
 * 
 * @param string $path Path relative to assets directory
 * @param bool $useCdn Whether to use CDN (if configured)
 * @return string Full asset URL
 */
function asset_url($path = '', $useCdn = false) {
    // CDN URL can be configured here for production
    $cdnUrl = getenv('CDN_URL') ?: '';
    
    if ($useCdn && !empty($cdnUrl)) {
        return rtrim($cdnUrl, '/') . './assets/' . ltrim($path, '/');
    }
    
    return url('assets/' . ltrim($path, '/'));
}

/**
 * Gets the current active theme
 * 
 * @return string Theme name
 */
function get_active_theme() {
    return $_SESSION['user_theme'] ?? DEFAULT_THEME;
}
?>
