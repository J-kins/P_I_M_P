<?php
/**
 * Simple router for PHP UI Template System
 */

/**
 * Routes a request to the appropriate view file
 * 
 * @param string $route Requested route
 * @return string Path to the view file
 */
function route($route = null) {
    // Get route from URL if not provided
    if ($route === null) {
        $route = $_GET['route'] ?? '';
    }
    
    // Clean up route
    $route = trim($route, '/');
    $route = filter_var($route, FILTER_SANITIZE_URL);
    
    // Map routes to views
    $routes = [
        '' => 'home.php',
        'home' => 'home.php',
        'dashboard' => 'dashboard.php',
        'spreadsheet' => 'spreadsheet.php',
        'contact' => 'contact.php',
        'about' => 'about.php',
        // Add more routes as needed
    ];
    
    // Check if route exists
    if (isset($routes[$route])) {
        $view_file = __DIR__ . '/views/' . $routes[$route];
        if (file_exists($view_file)) {
            return $view_file;
        }
    }
    
    // Route not found, return 404 page
    return __DIR__ . '/views/error.php';
}

/**
 * Renders a view with optional parameters
 * 
 * @param string $view_file Path to the view file
 * @param array $params Parameters to pass to the view
 * @return void Outputs the view content
 */
function render_view($view_file, $params = []) {
    // Extract parameters to make them available in the view
    extract($params);
    
    // Include the view file
    include $view_file;
}
?>
