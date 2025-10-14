
<?php
/**
 * Entry point for PHP UI Template System
 * 
 * This file loads all required components and routes the request
 */

// Start session for user preferences like theme
session_start();

// Load configuration
require_once './config.php';

// Load component system
require_once './include/components.php';

// Load router
require_once 'include/router.php';

// Get the view file based on the route
$view_file = route();

// Render the view
render_view($view_file);
?>

