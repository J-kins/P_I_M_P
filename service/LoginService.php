<?php
/**
 * P.I.M.P - Login Service
 * Handles user and business authentication logic
 */

namespace PIMP\Services;

use PIMP\Models\UserModel;
use PIMP\Models\BusinessModel;
use PIMP\Services\Database\MySQLDatabase;
use Exception;

class LoginService
{
    private $db;
    private $userModel;
    private $businessModel;

    public function __construct(MySQLDatabase $db)
    {
        $this->db = $db;
        $this->userModel = new UserModel($db);
        $this->businessModel = new BusinessModel($db);
    }

    /**
     * Authenticate user login
     * 
     * @param string $email
     * @param string $password
     * @param bool $rememberMe
     * @return array
     */
    public function authenticateUser(string $email, string $password, bool $rememberMe = false): array
    {
        try {
            // Validate inputs
            if (empty($email) || empty($password)) {
                return [
                    'success' => false,
                    'error' => 'Email and password are required'
                ];
            }

            // Find user by email
            $user = $this->userModel->findByEmail($email);

            if (!$user) {
                return [
                    'success' => false,
                    'error' => 'Invalid email or password'
                ];
            }

            // Verify password
            if (!$this->verifyPassword($password, $user)) {
                // Increment login attempts
                $this->userModel->incrementLoginAttempts($user['id']);
                
                return [
                    'success' => false,
                    'error' => 'Invalid email or password'
                ];
            }

            // Check account status
            if ($user['status'] !== 'active') {
                return [
                    'success' => false,
                    'error' => 'Account is not active. Please verify your email or contact support.',
                    'status' => $user['status']
                ];
            }

            // Create session
            $sessionToken = $this->createSession($user['id'], $rememberMe);

            // Update last login
            $this->userModel->updateLastLogin($user['id']);

            // Reset login attempts
            $this->userModel->resetLoginAttempts($user['id']);

            return [
                'success' => true,
                'user' => [
                    'id' => $user['id'],
                    'uuid' => $user['uuid'],
                    'username' => $user['username'],
                    'email' => $user['email'],
                    'name_json' => json_decode($user['name_json'], true),
                    'status' => $user['status']
                ],
                'session_token' => $sessionToken
            ];

        } catch (Exception $e) {
            error_log("Login error: " . $e->getMessage());
            return [
                'success' => false,
                'error' => 'An error occurred during login. Please try again.'
            ];
        }
    }

    /**
     * Authenticate business login
     * 
     * @param string $email
     * @param string $password
     * @param bool $rememberMe
     * @return array
     */
    public function authenticateBusiness(string $email, string $password, bool $rememberMe = false): array
    {
        try {
            // Validate inputs
            if (empty($email) || empty($password)) {
                return [
                    'success' => false,
                    'error' => 'Email and password are required'
                ];
            }

            // Find user by email (business owner)
            $user = $this->userModel->findByEmail($email);

            if (!$user) {
                return [
                    'success' => false,
                    'error' => 'Invalid business credentials'
                ];
            }

            // Verify password
            if (!$this->verifyPassword($password, $user)) {
                $this->userModel->incrementLoginAttempts($user['id']);
                
                return [
                    'success' => false,
                    'error' => 'Invalid business credentials'
                ];
            }

            // Check if user owns a business
            $businesses = $this->businessModel->getByOwnerId($user['id']);

            if (empty($businesses)) {
                return [
                    'success' => false,
                    'error' => 'No business associated with this account'
                ];
            }

            // Create session
            $sessionToken = $this->createSession($user['id'], $rememberMe, 'business');

            // Update last login
            $this->userModel->updateLastLogin($user['id']);
            $this->userModel->resetLoginAttempts($user['id']);

            return [
                'success' => true,
                'user' => [
                    'id' => $user['id'],
                    'uuid' => $user['uuid'],
                    'username' => $user['username'],
                    'email' => $user['email']
                ],
                'businesses' => $businesses,
                'session_token' => $sessionToken
            ];

        } catch (Exception $e) {
            error_log("Business login error: " . $e->getMessage());
            return [
                'success' => false,
                'error' => 'An error occurred during login. Please try again.'
            ];
        }
    }

    /**
     * Verify password (plain text comparison)
     * 
     * @param string $inputPassword
     * @param array $user
     * @return bool
     */
    private function verifyPassword(string $inputPassword, array $user): bool
    {
        // Get user credentials
        $credentials = $this->userModel->getCredentials($user['id'], 'password');
        
        if (!$credentials) {
            return false;
        }

        // Simple plain text comparison (as requested - not secure!)
        return $inputPassword === $credentials['credential_value'];
    }

    /**
     * Create user session
     * 
     * @param int $userId
     * @param bool $rememberMe
     * @param string $sessionType
     * @return string
     */
    private function createSession(int $userId, bool $rememberMe = false, string $sessionType = 'user'): string
    {
        // Generate session token
        $sessionToken = bin2hex(random_bytes(32));

        // Determine expiration
        $expiresIn = $rememberMe ? 30 * 24 * 60 * 60 : 24 * 60 * 60; // 30 days or 1 day
        $expiresAt = date('Y-m-d H:i:s', time() + $expiresIn);

        // Get device info
        $deviceInfo = [
            'browser' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown',
            'platform' => php_uname('s'),
            'type' => $sessionType
        ];

        // Get location info (basic)
        $locationInfo = [
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'Unknown',
            'country' => 'Unknown'
        ];

        // Insert session
        $this->db->query(
            "INSERT INTO user_sessions 
            (user_id, session_token, ip_address, user_agent, device_info_json, location_json, expires_at, is_active)
            VALUES (?, ?, ?, ?, ?, ?, ?, 1)",
            [
                $userId,
                $sessionToken,
                $_SERVER['REMOTE_ADDR'] ?? null,
                $_SERVER['HTTP_USER_AGENT'] ?? null,
                json_encode($deviceInfo),
                json_encode($locationInfo),
                $expiresAt
            ]
        );

        // Set session cookie
        setcookie('session_token', $sessionToken, time() + $expiresIn, '/', '', true, true);

        return $sessionToken;
    }

    /**
     * Validate session token
     * 
     * @param string $token
     * @return array|null
     */
    public function validateSession(string $token): ?array
    {
        try {
            $session = $this->db->fetchOne(
                "SELECT s.*, u.id as user_id, u.email, u.username, u.status, u.name_json
                 FROM user_sessions s
                 JOIN users u ON s.user_id = u.id
                 WHERE s.session_token = ? 
                 AND s.is_active = 1 
                 AND s.expires_at > NOW()",
                [$token]
            );

            if ($session) {
                // Update last activity
                $this->db->query(
                    "UPDATE user_sessions SET last_activity_time = NOW() WHERE id = ?",
                    [$session['id']]
                );

                return [
                    'user_id' => $session['user_id'],
                    'email' => $session['email'],
                    'username' => $session['username'],
                    'status' => $session['status'],
                    'name_json' => json_decode($session['name_json'], true)
                ];
            }

            return null;

        } catch (Exception $e) {
            error_log("Session validation error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Logout user
     * 
     * @param string $token
     * @return bool
     */
    public function logout(string $token): bool
    {
        try {
            $this->db->query(
                "UPDATE user_sessions 
                 SET is_active = 0, logout_time = NOW(), logout_reason = 'user' 
                 WHERE session_token = ?",
                [$token]
            );

            // Clear cookie
            setcookie('session_token', '', time() - 3600, '/', '', true, true);

            return true;

        } catch (Exception $e) {
            error_log("Logout error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Check if email exists
     * 
     * @param string $email
     * @return bool
     */
    public function emailExists(string $email): bool
    {
        return $this->userModel->findByEmail($email) !== null;
    }

    /**
     * Get login attempts
     * 
     * @param string $email
     * @return int
     */
    public function getLoginAttempts(string $email): int
    {
        $user = $this->userModel->findByEmail($email);
        return $user ? (int)$user['login_attempts'] : 0;
    }
}
