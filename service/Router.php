<?php
/**
 * P.I.M.P - Business Repository Platform
 * Advanced Router System
 */

namespace PIMP\Core;

class Router
{
    /**
     * @var array Registered routes
     */
    private static $routes = [];

    /**
     * @var string Default controller namespace
     */
    private static $controllerNamespace = 'PIMP\\Controller\\';

    /**
     * @var string Default view directory
     */
    private static $viewDirectory = __DIR__ . '/../view/pages/';

    /**
     * @var array Route patterns
     */
    private static $patterns = [
        ':id' => '([0-9]+)',
        ':slug' => '([a-zA-Z0-9-]+)',
        ':any' => '([^/]+)',
        ':all' => '(.*)'
    ];

    /**
     * @var bool Whether router is initialized
     */
    private static $initialized = false;

    /**
     * Initialize the router
     */
    public static function init(): void
    {
        if (self::$initialized) {
            return;
        }

        self::loadDefaultRoutes();
        self::$initialized = true;
    }

    /**
     * Load default application routes
     */
    private static function loadDefaultRoutes(): void
    {
        // Main application routes
        self::$routes = [
            // Home and core pages
            'GET' => [
                '/' => 'Home.php',
                '/home' => 'Home.php',
                '/dashboard' => 'Dashboard.php',
                '/businesses' => 'BusinessDirectory.php',
                '/businesses/search' => 'BusinessSearch.php',
                '/businesses/categories' => 'BusinessCategories.php',
                '/businesses/featured' => 'FeaturedBusinesses.php',
                '/business/:id' => 'BusinessProfile.php',
                '/business/:id/reviews' => 'BusinessReviews.php',
                
                // Reviews
                '/reviews' => 'Reviews.php',
                '/reviews/latest' => 'LatestReviews.php',
                '/reviews/trending' => 'TrendingReviews.php',
                '/reviews/write' => 'WriteReview.php',
                '/review/:id' => 'ReviewDetail.php',
                
                // Categories
                '/categories' => 'Categories.php',
                '/category/:slug' => 'CategoryDetail.php',
                
                // User routes
                '/login' => 'Login.php',
                '/register' => 'Register.php',
                '/profile' => 'UserProfile.php',
                '/profile/settings' => 'UserSettings.php',
                
                // Resources
                '/scam-alerts' => 'ScamAlerts.php',
                '/resources' => 'Resources.php',
                '/resources/guides' => 'BusinessGuides.php',
                '/resources/tips' => 'ConsumerTips.php',
                '/resources/blog' => 'Blog.php',
                
                // Business owner routes
                '/for-business' => 'ForBusiness.php',
                '/business/claim' => 'ClaimBusiness.php',
                '/business/advertise' => 'Advertise.php',
                '/business/resources' => 'BusinessResources.php',
                
                // Static pages
                '/about' => 'About.php',
                '/contact' => 'Contact.php',
                '/privacy' => 'Privacy.php',
                '/terms' => 'Terms.php',
                '/faq' => 'FAQ.php',
                
                // Admin routes
                '/admin' => 'admin/Dashboard.php',
                '/admin/businesses' => 'admin/BusinessManagement.php',
                '/admin/reviews' => 'admin/ReviewManagement.php',
                '/admin/users' => 'admin/UserManagement.php',
                '/admin/settings' => 'admin/Settings.php',
            ],
            
            'POST' => [
                '/login' => 'AuthController@login',
                '/register' => 'AuthController@register',
                '/reviews/submit' => 'ReviewController@submit',
                '/business/claim' => 'BusinessController@claim',
                '/contact/submit' => 'ContactController@submit',
            ],
            
            'PUT' => [
                '/review/:id' => 'ReviewController@update',
                '/profile' => 'UserController@update',
            ],
            
            'DELETE' => [
                '/review/:id' => 'ReviewController@delete',
            ]
        ];
    }

    /**
     * Add a custom route
     * 
     * @param string $method HTTP method
     * @param string $route Route pattern
     * @param mixed $handler Route handler
     */
    public static function addRoute(string $method, string $route, $handler): void
    {
        $method = strtoupper($method);
        
        if (!isset(self::$routes[$method])) {
            self::$routes[$method] = [];
        }
        
        self::$routes[$method][$route] = $handler;
    }

    /**
     * Route the current request
     * 
     * @return mixed Route response
     */
    public static function route()
    {
        self::init();
        
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $path = self::getCurrentPath();
        
        return self::resolve($method, $path);
    }

    /**
     * Get current request path
     * 
     * @return string
     */
    private static function getCurrentPath(): string
    {
        $path = $_SERVER['REQUEST_URI'] ?? '/';
        $path = parse_url($path, PHP_URL_PATH);
        $path = rtrim($path, '/');
        
        // Remove base path if running in subdirectory
        $basePath = Config::getBasePath();
        if ($basePath !== '/' && strpos($path, $basePath) === 0) {
            $path = substr($path, strlen($basePath));
        }
        
        return $path ?: '/';
    }

    /**
     * Resolve a route
     * 
     * @param string $method HTTP method
     * @param string $path Request path
     * @return mixed
     */
    private static function resolve(string $method, string $path)
    {
        $methodRoutes = self::$routes[$method] ?? [];
        
        // Exact match
        if (isset($methodRoutes[$path])) {
            return self::handleRoute($methodRoutes[$path]);
        }
        
        // Pattern matching
        foreach ($methodRoutes as $route => $handler) {
            $pattern = self::convertRouteToPattern($route);
            
            if (preg_match($pattern, $path, $matches)) {
                array_shift($matches); // Remove full match
                return self::handleRoute($handler, $matches);
            }
        }
        
        // Route not found
        return self::handleNotFound();
    }

    /**
     * Convert route pattern to regex
     * 
     * @param string $route Route pattern
     * @return string Regex pattern
     */
    private static function convertRouteToPattern(string $route): string
    {
        $pattern = preg_quote($route, '#');
        
        foreach (self::$patterns as $key => $regex) {
            $pattern = str_replace(preg_quote($key, '#'), $regex, $pattern);
        }
        
        return '#^' . $pattern . '$#';
    }

    /**
     * Handle route execution
     * 
     * @param mixed $handler Route handler
     * @param array $params Route parameters
     * @return mixed
     */
    private static function handleRoute($handler, array $params = [])
    {
        if (is_string($handler)) {
            // View file
            if (strpos($handler, '.php') !== false) {
                return self::renderView($handler, $params);
            }
            
            // Controller method
            if (strpos($handler, '@') !== false) {
                return self::callController($handler, $params);
            }
        }
        
        // Callable handler
        if (is_callable($handler)) {
            return call_user_func_array($handler, $params);
        }
        
        throw new \InvalidArgumentException("Invalid route handler: " . print_r($handler, true));
    }

    /**
     * Render a view file
     * 
     * @param string $viewFile View file name
     * @param array $params View parameters
     * @return string
     */
    private static function renderView(string $viewFile, array $params = []): string
    {
        $viewPath = self::$viewDirectory . $viewFile;
        
        // Check admin views
        if (strpos($viewFile, 'admin/') === 0) {
            $viewPath = __DIR__ . '/../admin/view/' . substr($viewFile, 6);
        }
        
        if (!file_exists($viewPath)) {
            throw new \RuntimeException("View file not found: {$viewPath}");
        }
        
        // Extract parameters for the view
        extract($params);
        
        // Start output buffering
        ob_start();
        
        try {
            include $viewPath;
            return ob_get_clean();
        } catch (\Exception $e) {
            ob_end_clean();
            throw $e;
        }
    }

    /**
     * Call controller method
     * 
     * @param string $controllerMethod Controller@method string
     * @param array $params Method parameters
     * @return mixed
     */
    private static function callController(string $controllerMethod, array $params = [])
    {
        list($controller, $method) = explode('@', $controllerMethod);
        $controllerClass = self::$controllerNamespace . $controller;
        
        if (!class_exists($controllerClass)) {
            throw new \RuntimeException("Controller not found: {$controllerClass}");
        }
        
        $controllerInstance = new $controllerClass();
        
        if (!method_exists($controllerInstance, $method)) {
            throw new \RuntimeException("Method not found: {$controllerClass}@{$method}");
        }
        
        return call_user_func_array([$controllerInstance, $method], $params);
    }

    /**
     * Handle 404 Not Found
     * 
     * @return string
     */
    private static function handleNotFound(): string
    {
        http_response_code(404);
        return self::renderView('Error.php', [
            'errorCode' => 404,
            'errorMessage' => 'Page Not Found',
            'errorDescription' => 'The page you are looking for does not exist or has been moved.'
        ]);
    }

    /**
     * Generate URL for a route
     * 
     * @param string $route Route name
     * @param array $params Route parameters
     * @return string
     */
    public static function url(string $route, array $params = []): string
    {
        self::init();
        
        // Replace parameters in route
        foreach ($params as $key => $value) {
            $route = str_replace(':' . $key, $value, $route);
        }
        
        return Config::url($route);
    }

    /**
     * Redirect to a route
     * 
     * @param string $route Route to redirect to
     * @param array $params Route parameters
     * @param int $status HTTP status code
     */
    public static function redirect(string $route, array $params = [], int $status = 302): void
    {
        $url = self::url($route, $params);
        header("Location: {$url}", true, $status);
        exit;
    }

    /**
     * Get all registered routes
     * 
     * @return array
     */
    public static function getRoutes(): array
    {
        self::init();
        return self::$routes;
    }

    /**
     * Check if route exists
     * 
     * @param string $method HTTP method
     * @param string $path Route path
     * @return bool
     */
    public static function routeExists(string $method, string $path): bool
    {
        self::init();
        
        $method = strtoupper($method);
        $methodRoutes = self::$routes[$method] ?? [];
        
        // Exact match
        if (isset($methodRoutes[$path])) {
            return true;
        }
        
        // Pattern matching
        foreach ($methodRoutes as $route => $handler) {
            $pattern = self::convertRouteToPattern($route);
            if (preg_match($pattern, $path)) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Set controller namespace
     * 
     * @param string $namespace
     */
    public static function setControllerNamespace(string $namespace): void
    {
        self::$controllerNamespace = rtrim($namespace, '\\') . '\\';
    }

    /**
     * Set view directory
     * 
     * @param string $directory
     */
    public static function setViewDirectory(string $directory): void
    {
        self::$viewDirectory = rtrim($directory, '/') . '/';
    }

    /**
     * Add custom pattern
     * 
     * @param string $key Pattern key
     * @param string $regex Pattern regex
     */
    public static function addPattern(string $key, string $regex): void
    {
        self::$patterns[$key] = $regex;
    }

    /**
     * Get current route information
     * 
     * @return array
     */
    public static function getCurrentRoute(): array
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $path = self::getCurrentPath();
        
        return [
            'method' => $method,
            'path' => $path,
            'exists' => self::routeExists($method, $path)
        ];
    }
}