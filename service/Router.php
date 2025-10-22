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

        // Ensure Config is initialized
        Config::init();
        
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
                '/categories' => 'BusinessCategories.php',
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
        return self::handleError(404);
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
        try {
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
        } catch (\Exception $e) {
            // Log the error
            error_log("Route Handler Error: " . $e->getMessage());
            
            // Return appropriate error page
            if ($e instanceof \RuntimeException && strpos($e->getMessage(), 'View file not found') !== false) {
                return self::handleError(404);
            }
            
            return self::handleError(500, $e->getMessage());
        }
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
            // Instead of throwing exception, return 404 error page
            return self::handleError(404);
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
            error_log("View Rendering Error: " . $e->getMessage());
            return self::handleError(500, "Error rendering view");
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
            error_log("Controller not found: {$controllerClass}");
            return self::handleError(500, "Controller not found");
        }
        
        $controllerInstance = new $controllerClass();
        
        if (!method_exists($controllerInstance, $method)) {
            error_log("Method not found: {$controllerClass}@{$method}");
            return self::handleError(500, "Method not found");
        }
        
        try {
            return call_user_func_array([$controllerInstance, $method], $params);
        } catch (\Exception $e) {
            error_log("Controller Method Error: " . $e->getMessage());
            return self::handleError(500, "Error executing controller method");
        }
    }

    /**
     * Handle HTTP errors with appropriate error pages
     * 
     * @param int $errorCode HTTP error code
     * @param string|null $customMessage Custom error message
     * @return string Error page HTML
     */
    private static function handleError(int $errorCode, ?string $customMessage = null): string
    {
        http_response_code($errorCode);
        
        $errorMessages = [
            // Client errors (4xx)
            400 => [
                'title' => 'Bad Request',
                'message' => 'The server cannot understand your request due to incorrect syntax.',
                'icon' => 'fa-exclamation-circle'
            ],
            401 => [
                'title' => 'Unauthorized',
                'message' => 'You need to authenticate to access this resource.',
                'icon' => 'fa-lock'
            ],
            403 => [
                'title' => 'Forbidden',
                'message' => 'You do not have permission to access this resource.',
                'icon' => 'fa-ban'
            ],
            404 => [
                'title' => 'Page Not Found',
                'message' => 'The page you are looking for does not exist or has been moved.',
                'icon' => 'fa-exclamation-triangle'
            ],
            429 => [
                'title' => 'Too Many Requests',
                'message' => 'You have sent too many requests. Please try again later.',
                'icon' => 'fa-hourglass-half'
            ],
            
            // Server errors (5xx)
            500 => [
                'title' => 'Internal Server Error',
                'message' => 'Something went wrong on our end. Please try again later.',
                'icon' => 'fa-server'
            ],
            502 => [
                'title' => 'Bad Gateway',
                'message' => 'The server received an invalid response. Please try again later.',
                'icon' => 'fa-network-wired'
            ],
            503 => [
                'title' => 'Service Unavailable',
                'message' => 'The server is temporarily unavailable. Please try again later.',
                'icon' => 'fa-tools'
            ],
            504 => [
                'title' => 'Gateway Timeout',
                'message' => 'The server did not receive a timely response. Please try again.',
                'icon' => 'fa-clock'
            ],
            505 => [
                'title' => 'HTTP Version Not Supported',
                'message' => 'The HTTP version used in the request is not supported.',
                'icon' => 'fa-code'
            ]
        ];
        
        $errorInfo = $errorMessages[$errorCode] ?? [
            'title' => 'Error',
            'message' => 'An unexpected error occurred.',
            'icon' => 'fa-exclamation-triangle'
        ];
        
        if ($customMessage && Config::isDevelopment()) {
            $errorInfo['message'] = $customMessage;
        }
        
        return self::renderErrorPage(
            $errorCode,
            $errorInfo['title'],
            $errorInfo['message'],
            $errorInfo['icon']
        );
    }

    /**
     * Render error page HTML
     * 
     * @param int $errorCode HTTP error code
     * @param string $title Error title
     * @param string $message Error message
     * @param string $icon Font Awesome icon class
     * @return string Error page HTML
     */
    private static function renderErrorPage(int $errorCode, string $title, string $message, string $icon): string
    {
        $isServerError = $errorCode >= 500;
        $iconColor = $isServerError ? '#ff6b6b' : '#ffa726';
        
        ob_start();
        ?>
<!DOCTYPE html>
<html lang="en" data-theme="purple1">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $errorCode ?> - <?= htmlspecialchars($title) ?> | P.I.M.P</title>
    <meta name="robots" content="noindex, nofollow">
    <link rel="stylesheet" href="<?= Config::styleUrl('theme.css') ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        
        .error-header {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            padding: 1rem 2rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .error-header h1 {
            color: white;
            font-size: 1.5rem;
            font-weight: 600;
        }
        
        .error-main {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }
        
        .error-content {
            background: white;
            border-radius: 20px;
            padding: 3rem;
            max-width: 600px;
            width: 100%;
            text-align: center;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }
        
        .error-icon {
            font-size: 5rem;
            color: <?= $iconColor ?>;
            margin-bottom: 1.5rem;
            animation: pulse 2s ease-in-out infinite;
        }
        
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }
        
        .error-code {
            font-size: 6rem;
            font-weight: bold;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 1rem;
        }
        
        .error-title {
            font-size: 2rem;
            color: #2c3e50;
            margin-bottom: 1rem;
            font-weight: 600;
        }
        
        .error-message {
            font-size: 1.1rem;
            color: #7f8c8d;
            margin-bottom: 2rem;
            line-height: 1.6;
        }
        
        .error-actions {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        .button {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.875rem 1.75rem;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            font-size: 1rem;
        }
        
        .button-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .button-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.4);
        }
        
        .button-outline {
            background: transparent;
            color: #667eea;
            border: 2px solid #667eea;
        }
        
        .button-outline:hover {
            background: #667eea;
            color: white;
            transform: translateY(-2px);
        }
        
        .error-help {
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 1px solid #ecf0f1;
        }
        
        .error-help p {
            color: #95a5a6;
            font-size: 0.9rem;
        }
        
        .error-help a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
        }
        
        .error-help a:hover {
            text-decoration: underline;
        }
        
        @media (max-width: 768px) {
            .error-content {
                padding: 2rem;
            }
            
            .error-code {
                font-size: 4rem;
            }
            
            .error-title {
                font-size: 1.5rem;
            }
            
            .error-actions {
                flex-direction: column;
            }
            
            .button {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <header class="error-header">
        <h1>P.I.M.P Business Repository</h1>
    </header>
    
    <main class="error-main">
        <div class="error-content">
            <div class="error-icon">
                <i class="fas <?= $icon ?>"></i>
            </div>
            <div class="error-code"><?= $errorCode ?></div>
            <h2 class="error-title"><?= htmlspecialchars($title) ?></h2>
            <p class="error-message"><?= htmlspecialchars($message) ?></p>
            
            <div class="error-actions">
                <a href="<?= Config::url('/') ?>" class="button button-primary">
                    <i class="fas fa-home"></i>
                    Return to Homepage
                </a>
                <?php if ($errorCode === 404): ?>
                <a href="<?= Config::url('/businesses') ?>" class="button button-outline">
                    <i class="fas fa-search"></i>
                    Browse Businesses
                </a>
                <?php elseif ($isServerError): ?>
                <a href="javascript:location.reload()" class="button button-outline">
                    <i class="fas fa-redo"></i>
                    Try Again
                </a>
                <?php endif; ?>
            </div>
            
            <div class="error-help">
                <p>
                    <?php if ($errorCode === 404): ?>
                        Looking for something specific? <a href="<?= Config::url('/contact') ?>">Contact us</a> for help.
                    <?php else: ?>
                        If this problem persists, please <a href="<?= Config::url('/contact') ?>">contact support</a>.
                    <?php endif; ?>
                </p>
            </div>
        </div>
    </main>
</body>
</html>
        <?php
        return ob_get_clean();
    }

    /**
     * Handle 404 Not Found (legacy method for backward compatibility)
     * 
     * @return string
     */
    private static function handleNotFound(): string
    {
        return self::handleError(404);
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