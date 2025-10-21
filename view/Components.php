<?php

/**
 * P.I.M.P Component Loader
 * 
 * Comprehensive component management system for business repository platform
 * Dynamically loads and manages all UI components with theme support
 */

namespace PIMP\Views;

use PIMP\Core\Config;

class Components
{
    /**
     * @var array Loaded component classes
     */
    private static $components = [];

    /**
     * @var array Component directories
     */
    private static $directories = [
        __DIR__ . '/components',
        __DIR__ . '/../admin/view/components'
    ];

    /**
     * @var bool Whether components are initialized
     */
    private static $initialized = false;

    /**
     * Initialize component system
     */
    public static function init(): void
    {
        if (self::$initialized) {
            return;
        }

        // Config will auto-initialize when needed
        self::loadComponentClasses();
        self::registerHelperFunctions();
        self::$initialized = true;
    }

    /**
     * Register global helper functions for components
     */
    private static function registerHelperFunctions(): void
    {
        // Only register if not already defined
        if (!function_exists('url')) {
            /**
             * Generate URL for given path
             * @param string $path Path relative to base URL
             * @return string Full URL
             */
            function url(string $path = ''): string {
                return Config::url($path);
            }
        }

        if (!function_exists('asset_url')) {
            /**
             * Generate asset URL
             * @param string $path Asset path
             * @param bool $useCdn Use CDN if configured
             * @return string Full asset URL
             */
            function asset_url(string $path = '', bool $useCdn = false): string {
                return Config::assetUrl($path, $useCdn);
            }
        }

        if (!function_exists('style_url')) {
            /**
             * Generate style URL
             * @param string $path Style path
             * @param bool $useCdn Use CDN if configured
             * @return string Full style URL
             */
            function style_url(string $path = '', bool $useCdn = false): string {
                return Config::styleUrl($path, $useCdn);
            }
        }

        if (!function_exists('script_url')) {
            /**
             * Generate script URL
             * @param string $path Script path
             * @param bool $useCdn Use CDN if configured
             * @return string Full script URL
             */
            function script_url(string $path = '', bool $useCdn = false): string {
                return Config::scriptUrl($path, $useCdn);
            }
        }

        if (!function_exists('image_url')) {
            /**
             * Generate image URL
             * @param string $path Image path
             * @param bool $useCdn Use CDN if configured
             * @return string Full image URL
             */
            function image_url(string $path = '', bool $useCdn = false): string {
                return Config::imageUrl($path, $useCdn);
            }
        }
    }

    /**
     * Load all component classes from directories
     */
    private static function loadComponentClasses(): void
    {
        foreach (self::$directories as $directory) {
            if (is_dir($directory)) {
                $files = glob($directory . '/*.php');
                foreach ($files as $file) {
                    require_once $file;
                    
                    // Extract class name from file
                    $className = self::getClassNameFromFile($file);
                    if ($className) {
                        $shortName = self::getShortClassName($className);
                        self::$components[$shortName] = $className;
                    }
                }
            }
        }
        
        // Debug output in development mode
        if (Config::isDevelopment()) {
            error_log("Loaded components: " . implode(', ', array_keys(self::$components)));
        }
    }

    /**
     * Extract class name from PHP file using token parsing
     */
    private static function getClassNameFromFile(string $file): ?string
    {
        $content = file_get_contents($file);
        $tokens = token_get_all($content);
        $class = '';
        $namespace = '';

        for ($i = 0; $i < count($tokens); $i++) {
            if ($tokens[$i][0] === T_NAMESPACE) {
                for ($j = $i + 1; $j < count($tokens); $j++) {
                    if ($tokens[$j][0] === T_STRING || $tokens[$j][0] === T_NAME_QUALIFIED) {
                        $namespace .= '\\' . $tokens[$j][1];
                    } else if ($tokens[$j] === '{' || $tokens[$j] === ';') {
                        break;
                    }
                }
            }

            if ($tokens[$i][0] === T_CLASS) {
                for ($j = $i + 1; $j < count($tokens); $j++) {
                    if ($tokens[$j] === '{') {
                        $class = $tokens[$i + 2][1];
                        break 2;
                    }
                }
            }
        }

        if ($class && $namespace) {
            return trim($namespace, '\\') . '\\' . $class;
        }

        return $class ?: null;
    }

    /**
     * Get short class name from fully qualified class name
     */
    private static function getShortClassName(string $className): string
    {
        $parts = explode('\\', $className);
        return end($parts);
    }

    /**
     * Call a component method dynamically
     * 
     * @param string $component Component name (e.g., 'Navigation', 'Headers')
     * @param string $method Method name to call
     * @param array $params Parameters to pass to the method
     * @return mixed Method return value
     */
    public static function call(string $component, string $method, array $params = [])
    {
        self::init();

        // Try theme-specific component first
        $themeResult = self::callThemedComponent($component, $method, $params);
        if ($themeResult !== null) {
            return $themeResult;
        }

        // Fall back to default component
        return self::callDefaultComponent($component, $method, $params);
    }

    /**
     * Call theme-specific component if available
     */
    private static function callThemedComponent(string $component, string $method, array $params)
    {
        $theme = Config::getActiveTheme();
        $themeComponentPath = __DIR__ . "/components/templates/{$theme}/{$component}.php";
        
        if (file_exists($themeComponentPath)) {
            // For theme template files, we expect them to define functions
            require_once $themeComponentPath;
            
            $themeFunction = $component . '_' . $method;
            if (function_exists($themeFunction)) {
                return call_user_func_array($themeFunction, $params);
            }
        }

        return null;
    }

    /**
     * Call default component class method
     */
    private static function callDefaultComponent(string $component, string $method, array $params)
    {
        // Check if we have the component class loaded
        if (isset(self::$components[$component])) {
            $className = self::$components[$component];
            if (class_exists($className) && method_exists($className, $method)) {
                return call_user_func_array([$className, $method], $params);
            }
        }

        // Fallback to function-based components
        $functionName = $component . '_' . $method;
        if (function_exists($functionName)) {
            return call_user_func_array($functionName, $params);
        }

        // Provide helpful error message
        $available = implode(', ', self::getAvailableComponents());
        throw new \InvalidArgumentException(
            "Component {$component}::{$method} not found. " .
            "Available components: {$available}. " .
            "Make sure the component file is in one of these directories: " . 
            implode(', ', self::$directories)
        );
    }

    /**
     * Magic method for static calls to components
     * 
     * @param string $name Component name
     * @param array $arguments [method, params] or just [params] for default method
     * @return mixed
     */
    public static function __callStatic(string $name, array $arguments)
    {
        self::init();

        if (empty($arguments)) {
            throw new \InvalidArgumentException("Component method required for {$name}");
        }

        $method = $arguments[0];
        $params = $arguments[1] ?? [];

        return self::call($name, $method, $params);
    }

    /**
     * Get all available components
     * 
     * @return array List of available component names
     */
    public static function getAvailableComponents(): array
    {
        self::init();
        return array_keys(self::$components);
    }

    /**
     * Get detailed component information
     * 
     * @return array Detailed information about loaded components
     */
    public static function getComponentInfo(): array
    {
        self::init();
        
        $info = [];
        foreach (self::$components as $shortName => $className) {
            $methods = [];
            if (class_exists($className)) {
                $reflection = new \ReflectionClass($className);
                foreach ($reflection->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
                    if (!$method->isConstructor() && !$method->isDestructor()) {
                        $methods[] = $method->getName();
                    }
                }
            }
            
            $info[$shortName] = [
                'class' => $className,
                'methods' => $methods,
                'exists' => class_exists($className)
            ];
        }
        
        return $info;
    }

    /**
     * Check if component exists
     * 
     * @param string $component Component name
     * @param string $method Optional method name to check
     * @return bool
     */
    public static function componentExists(string $component, string $method = null): bool
    {
        self::init();

        // Check theme component first
        $theme = Config::getActiveTheme();
        $themeComponentPath = __DIR__ . "/components/templates/{$theme}/{$component}.php";
        if (file_exists($themeComponentPath)) {
            if ($method) {
                require_once $themeComponentPath;
                $themeFunction = $component . '_' . $method;
                if (function_exists($themeFunction)) {
                    return true;
                }
            } else {
                return true;
            }
        }

        // Check class component
        if (isset(self::$components[$component])) {
            $className = self::$components[$component];
            if ($method) {
                return class_exists($className) && method_exists($className, $method);
            }
            return class_exists($className);
        }

        // Check function component
        if ($method) {
            $functionName = $component . '_' . $method;
            return function_exists($functionName);
        }

        return false;
    }

    /**
     * Get component class by name
     * 
     * @param string $component Component name
     * @return string|null Fully qualified class name or null if not found
     */
    public static function getComponentClass(string $component): ?string
    {
        self::init();
        return self::$components[$component] ?? null;
    }

    /**
     * Add custom component directory
     * 
     * @param string $directory Path to component directory
     */
    public static function addComponentDirectory(string $directory): void
    {
        if (!in_array($directory, self::$directories)) {
            self::$directories[] = $directory;
            self::$initialized = false; // Force re-initialization
        }
    }

    /**
     * Get all component directories
     * 
     * @return array List of component directories
     */
    public static function getComponentDirectories(): array
    {
        return self::$directories;
    }

    /**
     * Remove a component directory
     * 
     * @param string $directory Directory to remove
     */
    public static function removeComponentDirectory(string $directory): void
    {
        $key = array_search($directory, self::$directories);
        if ($key !== false) {
            unset(self::$directories[$key]);
            self::$initialized = false; // Force re-initialization
        }
    }

    /**
     * Reload all components (useful during development)
     */
    public static function reload(): void
    {
        self::$components = [];
        self::$initialized = false;
        self::init();
    }

    /**
     * Get base URL (shortcut to Config)
     * 
     * @return string Base URL
     */
    public static function getBaseUrl(): string
    {
        return Config::getBaseUrl();
    }

    /**
     * Get asset URL (shortcut to Config)
     * 
     * @param string $path Asset path
     * @param bool $useCdn Use CDN if configured
     * @return string Full asset URL
     */
    public static function assetUrl(string $path = '', bool $useCdn = false): string
    {
        return Config::assetUrl($path, $useCdn);
    }
}

/**
 * Initialize components automatically when included
 */
Components::init();