<?php
/**
 * P.I.M.P - Auth Controller
 * Handles authentication endpoints
 */

namespace PIMP\Controllers;

use PIMP\Services\LoginService;
use PIMP\Services\RegisterService;
use PIMP\Services\EmailService;
use PIMP\Services\Database\MySQLDatabase;
use PIMP\Core\Config;

class AuthController
{
    private $loginService;
    private $registerService;
    private $emailService;

    public function __construct(MySQLDatabase $db)
    {
        $this->emailService = new EmailService($db);
        $this->loginService = new LoginService($db);
        $this->registerService = new RegisterService($db, $this->emailService);
    }

    /**
     * Handle user login
     */
    public function login()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            $rememberMe = isset($_POST['remember_me']);

            $result = $this->loginService->authenticateUser($email, $password, $rememberMe);

            if ($result['success']) {
                $_SESSION['user_id'] = $result['user']['id'];
                $_SESSION['user'] = $result['user'];
                
                // Redirect to dashboard
                header('Location: ' . Config::url('/dashboard'));
                exit;
            } else {
                $_SESSION['login_error'] = $result['error'];
                header('Location: ' . Config::url('/login'));
                exit;
            }
        }

        // Show login page
        require_once __DIR__ . '/../views/auth/login.php';
    }

    /**
     * Handle business login
     */
    public function businessLogin()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            $rememberMe = isset($_POST['remember_me']);

            $result = $this->loginService->authenticateBusiness($email, $password, $rememberMe);

            if ($result['success']) {
                $_SESSION['user_id'] = $result['user']['id'];
                $_SESSION['user'] = $result['user'];
                $_SESSION['businesses'] = $result['businesses'];
                $_SESSION['is_business_user'] = true;
                
                // Redirect to business dashboard
                header('Location: ' . Config::url('/business/dashboard'));
                exit;
            } else {
                $_SESSION['login_error'] = $result['error'];
                header('Location: ' . Config::url('/business/login'));
                exit;
            }
        }

        // Show business login page
        require_once __DIR__ . '/../views/auth/business-login.php';
    }

    /**
     * Handle user registration
     */
    public function register()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'first_name' => $_POST['first_name'] ?? '',
                'last_name' => $_POST['last_name'] ?? '',
                'email' => $_POST['email'] ?? '',
                'phone' => $_POST['phone'] ?? '',
                'password' => $_POST['password'] ?? '',
                'confirm_password' => $_POST['confirm_password'] ?? '',
                'user_type' => $_POST['user_type'] ?? 'consumer',
                'terms' => isset($_POST['terms']),
                'newsletter' => isset($_POST['newsletter'])
            ];

            $result = $this->registerService->registerUser($data);

            if ($result['success']) {
                $_SESSION['register_success'] = $result['message'];
                $_SESSION['verification_email'] = $data['email'];
                
                // Redirect to email verification page
                header('Location: ' . Config::url('/verify-email?status=pending'));
                exit;
            } else {
                $_SESSION['register_errors'] = $result['errors'];
                header('Location: ' . Config::url('/register'));
                exit;
            }
        }

        // Show registration page
        require_once __DIR__ . '/../views/auth/register.php';
    }

    /**
     * Handle business registration
     */
    public function businessRegister()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userData = [
                'first_name' => $_POST['first_name'] ?? '',
                'last_name' => $_POST['last_name'] ?? '',
                'email' => $_POST['email'] ?? '',
                'phone' => $_POST['phone'] ?? '',
                'password' => $_POST['password'] ?? '',
                'confirm_password' => $_POST['confirm_password'] ?? '',
                'user_type' => 'business',
                'terms' => isset($_POST['terms'])
            ];

            $businessData = [
                'legal_name' => $_POST['legal_name'] ?? '',
                'trading_name' => $_POST['trading_name'] ?? '',
                'description' => $_POST['description'] ?? '',
                'business_type' => $_POST['business_type'] ?? 'corporation',
                'industry_sector' => $_POST['industry_sector'] ?? '',
                'category_id' => $_POST['category_id'] ?? null
            ];

            $result = $this->registerService->registerBusiness($userData, $businessData);

            if ($result['success']) {
                $_SESSION['register_success'] = $result['message'];
                $_SESSION['verification_email'] = $userData['email'];
                
                // Redirect to email verification page
                header('Location: ' . Config::url('/verify-email?status=pending'));
                exit;
            } else {
                $_SESSION['register_errors'] = $result['errors'];
                header('Location: ' . Config::url('/business/register'));
                exit;
            }
        }

        // Show business registration page
        require_once __DIR__ . '/../views/auth/business-register.php';
    }

    /**
     * Handle email verification
     */
    public function verifyEmail()
    {
        $token = $_GET['token'] ?? '';
        $status = $_GET['status'] ?? 'pending';

        if ($token) {
            $result = $this->emailService->verifyEmail($token);
            
            if ($result['success']) {
                $_SESSION['login_success'] = 'Your email has been verified! You can now log in.';
                header('Location: ' . Config::url('/login'));
                exit;
            } else {
                header('Location: ' . Config::url('/verify-email?status=' . $result['status']));
                exit;
            }
        }

        // Show verification page
        require_once __DIR__ . '/../views/auth/email-verification.php';
    }

    /**
     * Handle resend verification email
     */
    public function resendVerification()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'] ?? $_SESSION['verification_email'] ?? '';

            if (empty($email)) {
                echo json_encode([
                    'success' => false,
                    'error' => 'Email address is required'
                ]);
                exit;
            }

            $result = $this->emailService->resendVerificationEmail($email);
            echo json_encode($result);
            exit;
        }

        echo json_encode([
            'success' => false,
            'error' => 'Invalid request method'
        ]);
    }

    /**
     * Handle logout
     */
    public function logout()
    {
        $sessionToken = $_COOKIE['session_token'] ?? '';

        if ($sessionToken) {
            $this->loginService->logout($sessionToken);
        }

        // Clear session
        session_unset();
        session_destroy();

        // Redirect to home
        header('Location: ' . Config::url('/'));
        exit;
    }

    /**
     * Handle forgot password
     */
    public function forgotPassword()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'] ?? '';

            $result = $this->emailService->sendPasswordResetEmail($email);

            $_SESSION['password_reset_message'] = $result['message'];
            header('Location: ' . Config::url('/forgot-password'));
            exit;
        }

        // Show forgot password page
        require_once __DIR__ . '/../views/auth/forgot-password.php';
    }

    /**
     * Handle password reset
     */
    public function resetPassword()
    {
        $token = $_GET['token'] ?? '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Handle password reset form submission
            // Implementation would go here
        }

        // Show reset password page
        require_once __DIR__ . '/../views/auth/reset-password.php';
    }

    /**
     * Check if user is authenticated
     * 
     * @return bool
     */
    public function isAuthenticated(): bool
    {
        if (isset($_SESSION['user_id'])) {
            return true;
        }

        $sessionToken = $_COOKIE['session_token'] ?? '';
        if ($sessionToken) {
            $session = $this->loginService->validateSession($sessionToken);
            if ($session) {
                $_SESSION['user_id'] = $session['user_id'];
                $_SESSION['user'] = $session;
                return true;
            }
        }

        return false;
    }

    /**
     * Require authentication (middleware)
     */
    public function requireAuth()
    {
        if (!$this->isAuthenticated()) {
            $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
            header('Location: ' . Config::url('/login'));
            exit;
        }
    }

    /**
     * Require business authentication (middleware)
     */
    public function requireBusinessAuth()
    {
        $this->requireAuth();

        if (!isset($_SESSION['is_business_user'])) {
            header('Location: ' . Config::url('/dashboard'));
            exit;
        }
    }
}
