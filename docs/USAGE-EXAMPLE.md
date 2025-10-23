# P.I.M.P Authentication System - Setup Guide

## File Structure

```
/srv/http/P_I_M_P/
├── Controllers/
│   └── AuthController.php
├── Models/
│   ├── UserModel.php
│   └── BusinessModel.php
├── Services/
│   ├── LoginService.php
│   ├── RegisterService.php
│   └── EmailService.php
├── views/
│   └── auth/
│       ├── login.php
│       ├── register.php
│       ├── business-login.php
│       ├── business-register.php
│       └── email-verification.php
├── public/
│   └── js/
│       ├── login.js
│       ├── register.js
│       └── email-verification.js
└── config/
    └── database/
        └── MySQL/
            └── migrations/
                └── 001_init.sql
```

## 1. Database Setup

Run the migration to create all tables:

```php
<?php
require_once 'vendor/autoload.php';

use PIMP\Services\Database\MySQLDatabase;

$config = [
    'host' => 'localhost',
    'port' => 3306,
    'username' => 'root',
    'password' => 'root',
    'database' => 'pimp_db'
];

$db = new MySQLDatabase($config);

// Run migrations
$results = $db->migrateAll();

// Check results
foreach ($results as $migration => $result) {
    echo "Migration {$migration}: " . ($result ? 'Success' : 'Failed') . "\n";
}
```

## 2. Initialize Session

Create `init.php` in your root directory:

```php
<?php
session_start();

require_once __DIR__ . '/vendor/autoload.php';

use PIMP\Services\Database\MySQLDatabase;

// Database configuration
$dbConfig = [
    'host' => 'localhost',
    'port' => 3306,
    'username' => 'root',
    'password' => 'root',
    'database' => 'pimp_db'
];

// Initialize database connection
$db = new MySQLDatabase($dbConfig);

// Make database available globally
$GLOBALS['db'] = $db;
```

## 3. Routing Setup

Create `routes.php`:

```php
<?php
require_once 'init.php';

use PIMP\Controllers\AuthController;

// Initialize controller
$authController = new AuthController($GLOBALS['db']);

// Get request URI
$requestUri = $_SERVER['REQUEST_URI'];
$requestUri = strtok($requestUri, '?'); // Remove query string

// Route handling
switch ($requestUri) {
    // User Authentication
    case '/login':
        $authController->login();
        break;
    
    case '/register':
        $authController->register();
        break;
    
    case '/logout':
        $authController->logout();
        break;
    
    case '/verify-email':
        $authController->verifyEmail();
        break;
    
    case '/forgot-password':
        $authController->forgotPassword();
        break;
    
    case '/reset-password':
        $authController->resetPassword();
        break;
    
    // Business Authentication
    case '/business/login':
        $authController->businessLogin();
        break;
    
    case '/business/register':
        $authController->businessRegister();
        break;
    
    // API Endpoints
    case '/api/resend-verification':
        header('Content-Type: application/json');
        $authController->resendVerification();
        break;
    
    // Protected Routes
    case '/dashboard':
        $authController->requireAuth();
        require_once 'views/dashboard.php';
        break;
    
    case '/business/dashboard':
        $authController->requireBusinessAuth();
        require_once 'views/business/dashboard.php';
        break;
    
    // Default
    default:
        require_once 'views/home.php';
        break;
}
```

## 4. Using the Authentication System

### User Login Example

```php
<?php
// In login.php view
require_once 'init.php';

use PIMP\Controllers\AuthController;

$authController = new AuthController($GLOBALS['db']);
$authController->login();
```

### User Registration Example

```php
<?php
// In register.php view
require_once 'init.php';

use PIMP\Controllers\AuthController;

$authController = new AuthController($GLOBALS['db']);
$authController->register();
```

### Protected Page Example

```php
<?php
// In any protected page
require_once 'init.php';

use PIMP\Controllers\AuthController;

$authController = new AuthController($GLOBALS['db']);
$authController->requireAuth();

// Your protected content here
echo "Welcome, " . $_SESSION['user']['username'];
```

## 5. Frontend Integration

### Include JavaScript Files

In your HTML templates:

```html
<!-- For Login Page -->
<script src="/public/js/login.js"></script>

<!-- For Register Page -->
<script src="/public/js/register.js"></script>

<!-- For Email Verification Page -->
<script src="/public/js/email-verification.js"></script>
```

## 6. API Usage Examples

### Check if User is Authenticated

```php
<?php
use PIMP\Controllers\AuthController;

$authController = new AuthController($GLOBALS['db']);

if ($authController->isAuthenticated()) {
    $user = $_SESSION['user'];
    echo "Logged in as: " . $user['username'];
} else {
    echo "Not logged in";
}
```

### Get Current User

```php
<?php
function getCurrentUser() {
    return $_SESSION['user'] ?? null;
}

$user = getCurrentUser();
if ($user) {
    echo "User ID: " . $user['id'];
    echo "Email: " . $user['email'];
}
```

### Manual Login (Programmatic)

```php
<?php
use PIMP\Services\LoginService;

$loginService = new LoginService($GLOBALS['db']);

$result = $loginService->authenticateUser(
    'user@example.com',
    'password123',
    false // remember me
);

if ($result['success']) {
    $_SESSION['user_id'] = $result['user']['id'];
    $_SESSION['user'] = $result['user'];
    echo "Login successful!";
} else {
    echo "Error: " . $result['error'];
}
```

### Manual Registration (Programmatic)

```php
<?php
use PIMP\Services\RegisterService;
use PIMP\Services\EmailService;

$emailService = new EmailService($GLOBALS['db']);
$registerService = new RegisterService($GLOBALS['db'], $emailService);

$result = $registerService->registerUser([
    'first_name' => 'John',
    'last_name' => 'Doe',
    'email' => 'john@example.com',
    'phone' => '+1234567890',
    'password' => 'SecurePass123',
    'confirm_password' => 'SecurePass123',
    'user_type' => 'consumer',
    'terms' => true
]);

if ($result['success']) {
    echo "Registration successful! User ID: " . $result['user_id'];
} else {
    print_r($result['errors']);
}
```

## 7. Email Configuration

Update `EmailService.php` with your email settings:

```php
// In EmailService.php constructor
$this->fromEmail = 'noreply@yourdomain.com';
$this->fromName = 'Your App Name';

// For production, use a service like SendGrid, Mailgun, or AWS SES
// Example with SendGrid:
private function sendEmail($to, $subject, $htmlBody, $textBody = '') {
    $sendgrid = new \SendGrid(getenv('SENDGRID_API_KEY'));
    $email = new \SendGrid\Mail\Mail();
    $email->setFrom($this->fromEmail, $this->fromName);
    $email->addTo($to);
    $email->setSubject($subject);
    $email->addContent("text/html", $htmlBody);
    
    try {
        $response = $sendgrid->send($email);
        return $response->statusCode() == 202;
    } catch (Exception $e) {
        error_log('Email sending failed: ' . $e->getMessage());
        return false;
    }
}
```

## 8. Security Notes

### Important: Password Storage
**The current implementation stores passwords in plain text as requested.** This is NOT SECURE for production!

For production, update `LoginService.php` and `RegisterService.php`:

```php
// When storing password
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// When verifying
if (password_verify($inputPassword, $storedPassword)) {
    // Password is correct
}
```

### Additional Security Measures

1. **Enable HTTPS** - Always use SSL/TLS in production
2. **CSRF Protection** - Add CSRF tokens to forms
3. **Rate Limiting** - Limit login attempts
4. **Input Sanitization** - Already implemented in models
5. **SQL Injection Protection** - Using prepared statements (already implemented)

## 9. Testing the System

### Test User Registration

```bash
curl -X POST http://localhost/register \
  -d "first_name=John" \
  -d "last_name=Doe" \
  -d "email=john@example.com" \
  -d "password=SecurePass123" \
  -d "confirm_password=SecurePass123" \
  -d "user_type=consumer" \
  -d "terms=1"
```

### Test User Login

```bash
curl -X POST http://localhost/login \
  -d "email=john@example.com" \
  -d "password=SecurePass123" \
  -c cookies.txt
```

### Test Protected Route

```bash
curl http://localhost/dashboard \
  -b cookies.txt
```

## 10. Troubleshooting

### Common Issues

1. **"MySQL backup failed"** - Already fixed with the backup methods provided
2. **Session not persisting** - Check session.save_path permissions
3. **Emails not sending** - Check SMTP settings and firewall
4. **Database connection failed** - Verify credentials in config

### Enable Debug Mode

```php
// In init.php
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Log all queries
$db->setDebugMode(true);
```

## 11. Next Steps

- Implement password reset functionality
- Add two-factor authentication (2FA)
- Implement social login (Google, Facebook)
- Add user profile management
- Implement role-based access control
- Add audit logging for security events
