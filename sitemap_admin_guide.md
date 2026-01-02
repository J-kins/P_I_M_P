# P.I.M.P Sitemap & Admin System - Complete Implementation Guide

## Table of Contents
1. [Database Setup](#database-setup)
2. [Sitemap System](#sitemap-system)
3. [Admin Panel](#admin-panel)
4. [Router Integration](#router-integration)
5. [Security Setup](#security-setup)
6. [Testing](#testing)

---

## 1. Database Setup

### Run Security Migration

```php
<?php
// run-migrations.php
require_once 'vendor/autoload.php';

use PIMP\Services\Database\MySQLDatabase;

$db = new MySQLDatabase([
    'host' => 'localhost',
    'port' => 3306,
    'username' => 'root',
    'password' => 'root',
    'database' => 'pimp_db'
]);

// Run security tables migration
$result = $db->executeMigration('002_security_tables.sql');
echo "Security tables migration completed\n";

// Create first admin user
require_once 'create-admin.php';
```

### Create First Admin User

```php
<?php
// create-admin.php
require_once 'vendor/autoload.php';

use PIMP\Services\RegisterService;
use PIMP\Services\EmailService;
use PIMP\Services\Database\MySQLDatabase;
use PIMP\Models\UserModel;

$db = new MySQLDatabase([
    'host' => 'localhost',
    'port' => 3306,
    'username' => 'root',
    'password' => 'root',
    'database' => 'pimp_db'
]);

$emailService = new EmailService($db);
$registerService = new RegisterService($db, $emailService);
$userModel = new UserModel($db);

// Create admin user
$result = $registerService->registerUser([
    'first_name' => 'Admin',
    'last_name' => 'User',
    'email' => 'admin@pimp-platform.com',
    'password' => 'AdminPass123!',
    'confirm_password' => 'AdminPass123!',
    'user_type' => 'consumer',
    'terms' => true
]);

if ($result['success']) {
    $userId = $result['user_id'];
    
    // Manually activate account
    $db->query("UPDATE users SET status = 'active', email_verified = 1 WHERE id = ?", [$userId]);
    
    // Get super_admin role
    $role = $db->fetchOne("SELECT id FROM roles WHERE name = 'super_admin'", []);
    
    // Assign super_admin role
    $db->query(
        "INSERT INTO user_roles (user_id, role_id, assigned_by) VALUES (?, ?, ?)",
        [$userId, $role['id'], $userId]
    );
    
    echo "Admin user created successfully!\n";
    echo "Email: admin@pimp-platform.com\n";
    echo "Password: AdminPass123!\n";
} else {
    echo "Error creating admin user:\n";
    print_r($result['errors']);
}
```

---

## 2. Sitemap System

### 2.1 Router Integration

Update your Router class to include sitemap routes:

```php
<?php
// Add to Router.php loadDefaultRoutes() method

'GET' => [
    // ... existing routes ...
    
    // Sitemap routes
    '/sitemap.xml' => 'SitemapController@index',
    '/sitemap' => 'SitemapController@htmlSitemap',
    '/sitemaps/sitemap-main.xml' => 'SitemapController@mainPages',
    '/sitemaps/sitemap-categories.xml' => 'SitemapController@categories',
],
```

### 2.2 Create Sitemap Directory

```bash
mkdir -p /srv/http/P_I_M_P/static/sitemaps
chmod 755 /srv/http/P_I_M_P/static/sitemaps
```

### 2.3 robots.txt

Create `/srv/http/P_I_M_P/public/robots.txt`:

```txt
User-agent: *
Allow: /
Disallow: /admin/
Disallow: /api/private/
Disallow: /config/
Disallow: /vendor/

Sitemap: https://yourdomain.com/sitemap.xml
```

### 2.4 Usage in Your Application

```php
<?php
// Trigger sitemap generation when content changes

use PIMP\Controllers\SitemapController;

// After creating/updating a business
$sitemap = new SitemapController($db);
$sitemap->clearCache(); // Clear cache to regenerate

// Manual generation
$sitemap->index(); // Main sitemap
$sitemap->businesses(1); // First page of businesses
```

---

## 3. Admin Panel

### 3.1 Router Integration for Admin

Add admin routes to Router.php:

```php
<?php
// In Router.php loadDefaultRoutes()

'GET' => [
    // Admin routes
    '/admin' => 'AdminController@dashboard',
    '/admin/login' => 'AdminController@login',
    '/admin/dashboard' => 'AdminController@dashboard',
    '/admin/users' => 'AdminController@users',
    '/admin/users/:id/edit' => 'AdminController@editUser',
    '/admin/businesses' => 'AdminController@businesses',
    '/admin/reviews' => 'AdminController@reviews',
    '/admin/settings' => 'AdminController@settings',
    '/admin/security' => 'AdminController@security',
    '/admin/audit-logs' => 'AdminController@auditLogs',
],

'POST' => [
    '/admin/reviews/:id/approve' => 'AdminController@approveReview',
    '/admin/reviews/:id/reject' => 'AdminController@rejectReview',
    '/admin/api/sitemap/clear-cache' => 'AdminController@clearSitemapCache',
    '/admin/api/database/backup' => 'AdminController@databaseBackup',
],
```

### 3.2 Update Router Controller Handling

Modify the `callController` method in Router.php to work with your structure:

```php
<?php
// In Router.php

private static function callController(string $controllerMethod, array $params = [])
{
    list($controller, $method) = explode('@', $controllerMethod);
    
    // Try PIMP\Controllers namespace first
    $controllerClass = 'PIMP\\Controllers\\' . $controller;
    
    if (!class_exists($controllerClass)) {
        // Fallback to default namespace
        $controllerClass = self::$controllerNamespace . $controller;
    }
    
    if (!class_exists($controllerClass)) {
        error_log("Controller not found: {$controllerClass}");
        return self::handleError(500, "Controller not found");
    }
    
    // Initialize database connection
    $db = $GLOBALS['db'] ?? null;
    if (!$db) {
        throw new \Exception("Database connection not initialized");
    }
    
    $controllerInstance = new $controllerClass($db);
    
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
```

---

## 4. Router Integration

### 4.1 Main Application Entry Point

Create `/srv/http/P_I_M_P/public/index.php`:

```php
<?php
/**
 * P.I.M.P - Main Application Entry Point
 */

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session
session_start();

// Load autoloader
require_once dirname(__DIR__) . '/vendor/autoload.php';

// Initialize Config
PIMP\Core\Config::init();

// Initialize Database
use PIMP\Services\Database\MySQLDatabase;

$dbConfig = [
    'host' => getenv('DB_HOST') ?: 'localhost',
    'port' => getenv('DB_PORT') ?: 3306,
    'username' => getenv('DB_USER') ?: 'root',
    'password' => getenv('DB_PASS') ?: 'root',
    'database' => getenv('DB_NAME') ?: 'pimp_db'
];

$GLOBALS['db'] = new MySQLDatabase($dbConfig);

// Initialize Router and route the request
$response = PIMP\Core\Router::route();

// Output response
if (is_string($response)) {
    echo $response;
} else {
    // If response is not string, convert to JSON
    header('Content-Type: application/json');
    echo json_encode($response);
}
```

### 4.2 .htaccess Configuration

Create `/srv/http/P_I_M_P/public/.htaccess`:

```apache
# Main application .htaccess
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteBase /
    
    # Redirect to HTTPS
    RewriteCond %{HTTPS} off
    RewriteRule ^ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
    
    # Admin area - use separate .htaccess
    RewriteCond %{REQUEST_URI} ^/admin/
    RewriteRule ^admin/(.*)$ admin/$1 [L]
    
    # Static files
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_URI} \.(css|js|png|jpg|jpeg|gif|svg|woff|woff2|ttf|eot|ico|xml|txt)$ [NC]
    RewriteRule ^ - [L]
    
    # Route everything else through index.php
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^ index.php [L]
</IfModule>

# Security Headers
<IfModule mod_headers.c>
    Header always set X-Content-Type-Options "nosniff"
    Header always set X-XSS-Protection "1; mode=block"
    Header always set X-Frame-Options "SAMEORIGIN"
    Header always set Referrer-Policy "strict-origin-when-cross-origin"
</IfModule>

# Prevent directory listings
Options -Indexes

# Protect sensitive files
<FilesMatch "(\.htaccess|\.htpasswd|\.env|\.git|\.log|composer\.(json|lock))$">
    Order Allow,Deny
    Deny from all
</FilesMatch>
```

### 4.3 Admin .htaccess

Update `/srv/http/P_I_M_P/admin/.htaccess`:

```apache
# Admin area - Remove BasicAuth, use application auth instead
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteBase /admin/
    
    # Redirect to HTTPS
    RewriteCond %{HTTPS} off
    RewriteRule ^ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
    
    # Pass through to main router
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^(.*)$ ../public/index.php [L,QSA]
</IfModule>

# Security headers
<IfModule mod_headers.c>
    Header always set X-Content-Type-Options "nosniff"
    Header always set X-XSS-Protection "1; mode=block"
    Header always set X-Frame-Options "DENY"
    Header always set Content-Security-Policy "default-src 'self'; script-src 'self' 'unsafe-inline' https://cdnjs.cloudflare.com; style-src 'self' 'unsafe-inline' https://cdnjs.cloudflare.com; font-src 'self' https://cdnjs.cloudflare.com;"
</IfModule>

# Prevent directory listings
Options -Indexes

# PHP settings
<IfModule mod_php7.c>
    php_flag display_errors Off
    php_flag log_errors On
    php_value max_execution_time 120
    php_value memory_limit 256M
</IfModule>
```

---

## 5. Security Setup

### 5.1 Load Security Config

Create `/srv/http/P_I_M_P/Core/Security.php`:

```php
<?php
namespace PIMP\Core;

class Security
{
    private static $config;
    
    public static function init(): void
    {
        self::$config = require dirname(__DIR__) . '/config/security/admin-config.php';
        self::enforceSecurityHeaders();
        self::checkMaintenanceMode();
    }
    
    private static function enforceSecurityHeaders(): void
    {
        if (self::$config['security']['require_https'] && !self::isHttps()) {
            header('Location: https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
            exit;
        }
        
        if (self::$config['security']['secure_cookies']) {
            ini_set('session.cookie_secure', '1');
        }
        
        if (self::$config['security']['http_only_cookies']) {
            ini_set('session.cookie_httponly', '1');
        }
        
        ini_set('session.cookie_samesite', self::$config['security']['same_site_cookies']);
    }
    
    private static function isHttps(): bool
    {
        return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') 
            || $_SERVER['SERVER_PORT'] == 443;
    }
    
    private static function checkMaintenanceMode(): void
    {
        if (self::$config['maintenance']['mode']) {
            $allowedIps = self::$config['maintenance']['allowed_ips'];
            $currentIp = $_SERVER['REMOTE_ADDR'] ?? '';
            
            if (!in_array($currentIp, $allowedIps)) {
                http_response_code(503);
                header('Retry-After: ' . self::$config['maintenance']['retry_after']);
                echo self::$config['maintenance']['message'];
                exit;
            }
        }
    }
    
    public static function get(string $key, $default = null)
    {
        $keys = explode('.', $key);
        $value = self::$config;
        
        foreach ($keys as $k) {
            if (!isset($value[$k])) {
                return $default;
            }
            $value = $value[$k];
        }
        
        return $value;
    }
}
```

### 5.2 Initialize Security in index.php

Update `/srv/http/P_I_M_P/public/index.php`:

```php
<?php
// ... existing code ...

// Initialize Security
PIMP\Core\Security::init();

// Check if IP is blocked (before routing)
use PIMP\Services\SecurityService;
$securityService = new SecurityService($GLOBALS['db']);

$currentIp = $_SERVER['REMOTE_ADDR'] ?? '';
if ($securityService->isIPBlocked($currentIp)) {
    http_response_code(403);
    echo "Access denied. Your IP address has been blocked.";
    exit;
}

// ... continue with routing ...
```

---

## 6. Testing

### 6.1 Test Sitemap Generation

```php
<?php
// test-sitemap.php
require_once 'vendor/autoload.php';

use PIMP\Controllers\SitemapController;
use PIMP\Services\Database\MySQLDatabase;

$db = new MySQLDatabase([
    'host' => 'localhost',
    'port' => 3306,
    'username' => 'root',
    'password' => 'root',
    'database' => 'pimp_db'
]);

$sitemap = new SitemapController($db);

// Test main sitemap
echo "Generating main sitemap...\n";
ob_start();
$sitemap->index();
$xml = ob_get_clean();
echo "Main sitemap generated: " . strlen($xml) . " bytes\n\n";

// Test HTML sitemap
echo "Generating HTML sitemap...\n";
ob_start();
$sitemap->htmlSitemap();
$html = ob_get_clean();
echo "HTML sitemap generated: " . strlen($html) . " bytes\n";
```

### 6.2 Test Admin Access

```bash
# Test admin login
curl -X POST http://localhost/admin/login \
  -d "email=admin@pimp-platform.com" \
  -d "password=AdminPass123!" \
  -c cookies.txt

# Test admin dashboard (with cookies)
curl http://localhost/admin/dashboard -b cookies.txt

# Test sitemap cache clear
curl -X POST http://localhost/admin/api/sitemap/clear-cache -b cookies.txt
```

### 6.3 Test Security Features

```php
<?php
// test-security.php
require_once 'vendor/autoload.php';

use PIMP\Services\SecurityService;
use PIMP\Services\Database\MySQLDatabase;

$db = new MySQLDatabase([/*...*/]);
$security = new SecurityService($db);

// Test password strength
$result = $security->validatePasswordStrength('WeakPass');
print_r($result);

// Test SQL injection detection
$malicious = "'; DROP TABLE users; --";
if ($security->detectSQLInjection($malicious)) {
    echo "SQL injection detected!\n";
}

// Test XSS detection
$xss = "<script>alert('xss')</script>";
if ($security->detectXSS($xss)) {
    echo "XSS attempt detected!\n";
}
```

---

## 7. Cron Jobs Setup

### 7.1 Sitemap Auto-Generation

Add to crontab:

```bash
# Regenerate sitemaps daily at 2 AM
0 2 * * * cd /srv/http/P_I_M_P && php scripts/generate-sitemaps.php

# Clear old audit logs weekly
0 3 * * 0 cd /srv/http/P_I_M_P && php scripts/clean-audit-logs.php

# Database backup daily at 3 AM
0 3 * * * cd /srv/http/P_I_M_P && php scripts/backup-database.php
```

### 7.2 Create Cron Scripts

```php
<?php
// scripts/generate-sitemaps.php
require_once __DIR__ . '/../vendor/autoload.php';

use PIMP\Controllers\SitemapController;
use PIMP\Services\Database\MySQLDatabase;

$db = new MySQLDatabase([/*...*/]);
$sitemap = new SitemapController($db);

// Clear cache and regenerate
$sitemap->clearCache();
echo "Sitemaps regenerated successfully\n";
```

```php
<?php
// scripts/backup-database.php
require_once __DIR__ . '/../vendor/autoload.php';

use PIMP\Services\Database\MySQLDatabase;

$db = new MySQLDatabase([/*...*/]);

try {
    $backupPath = $db->backup();
    echo "Database backup created: {$backupPath}\n";
} catch (Exception $e) {
    error_log("Backup failed: " . $e->getMessage());
    echo "Backup failed\n";
}
```

---

## 8. Quick Start Commands

```bash
# 1. Run migrations
php run-migrations.php

# 2. Create admin user
php create-admin.php

# 3. Test sitemaps
php test-sitemap.php

# 4. Generate initial sitemaps
php scripts/generate-sitemaps.php

# 5. Start PHP development server
php -S localhost:8000 -t public/

# 6. Access admin panel
open http://localhost:8000/admin
```

---

## 9. Troubleshooting

### Common Issues

1. **404 on admin routes**: Check .htaccess mod_rewrite is enabled
2. **Sitemap not updating**: Clear cache with `$sitemap->clearCache()`
3. **Permission denied**: Check directory permissions `chmod 755 static/sitemaps`
4. **Database connection failed**: Verify credentials in config

### Debug Mode

Enable in Config.php:

```php
public static function isDevelopment(): bool
{
    return true; // Set to false in production
}
```

This will show detailed error messages.
Controller->businesses((int)$matches[1]);
        break;
    
    case (preg_match('/\/sitemaps\/sitemap-reviews-(\d+)\.xml/', $requestUri, $matches) ? true : false):
        $sitemapController->reviews((int)$matches[1]);
        break;
    
    case '/sitemaps/sitemap-categories.xml':
        $sitemapController->categories();
        break;
    
    // HTML Sitemap
    case '/sitemap':
        $sitemapController->htmlSitemap();
        break;
}
```

### 2.3 Google Search Console Setup

1. Go to [Google Search Console](https://search.google.com/search-console)
2. Add your property
3. Submit your sitemap: `https://yourdomain.com/sitemap.xml`

### 2.4 robots.txt Configuration

Create `/srv/http/P_I_M_P/public/robots.txt`:

```
User-agent: *
Allow: /
Disallow: /admin/
Disallow: /api/private/
Disallow: /uploads/private/

Sitemap: https://yourdomain.com/sitemap.xml
```

### 2.5 Usage Examples

```php
<?php
use PIMP\Controllers\SitemapController;

// Generate sitemap
$sitemap = new SitemapController($db);

// Clear cache (when content is updated)
$result = $sitemap->clearCache();
echo "Cleared {$result['count']} cache files";

// Manually generate specific sitemap
$sitemap->businesses(1); // First page of businesses
$sitemap->categories();
```

---

## 3. Admin Panel

### 3.1 Admin Routes

Add to `routes.php`:

```php
<?php
use PIMP\Controllers\AdminController;

$adminController = new AdminController($GLOBALS['db']);

// Admin routes
if (strpos($requestUri, '/admin') === 0) {
    switch ($requestUri) {
        case '/admin':
        case '/admin/dashboard':
            $adminController->dashboard();
            break;
        
        case '/admin/users':
            $adminController->users();
            break;
        
        case (preg_match('/\/admin\/users\/(\d+)\/edit/', $requestUri, $matches) ? true : false):
            $adminController->editUser((int)$matches[1]);
            break;
        
        case '/admin/businesses':
            $adminController->businesses();
            break;
        
        case '/admin/reviews':
            $adminController->reviews();
            break;
        
        case (preg_match('/\/admin\/reviews\/(\d+)\/approve/', $requestUri, $matches) ? true : false):
            $adminController->approveReview((int)$matches[1]);
            break;
        
        case (preg_match('/\/admin\/reviews\/(\d+)\/reject/', $requestUri, $matches) ? true : false):
            $adminController->rejectReview((int)$matches[1]);
            break;
        
        case '/admin/settings':
            $adminController->settings();
            break;
        
        case '/admin/security':
            $adminController->security();
            break;
        
        case '/admin/audit-logs':
            $adminController->auditLogs();
            break;
        
        case '/admin/login':
            $adminController->login();
            break;
        
        // API endpoints
        case '/admin/api/sitemap/clear-cache':
            $adminController->clearSitemapCache();
            break;
        
        case '/admin/api/database/backup':
            $adminController->databaseBackup();
            break;
    }
}
```

### 3.2 Creating Admin User

```php
<?