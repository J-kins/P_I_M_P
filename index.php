<?php
/**
 * P.I.M.P - Business Repository Platform
 * Main Entry Point
 */

require_once __DIR__ . '/vendor/autoload.php';

use PIMP\Core\Config;
use PIMP\Core\Router;

// Initialize configuration
Config::init();

// Route the request
try {
    $response = Router::route();
    echo $response;
} catch (Exception $e) {
    // Handle errors gracefully
    if (Config::isDevelopment()) {
        throw $e;
    }
    
    http_response_code(500);
    echo "An error occurred. Please try again later.";
    
    // Log error
    error_log("PIMP Router Error: " . $e->getMessage());
}