<?php
/**
 * P.I.M.P - Authentication and Authorization Model
 * Handles user authentication, sessions, and permissions
 */

namespace PIMP\Models;

use PIMP\Services\Database\MySQLDatabase;
use PDOException;
use Exception;

class Auth
{
    /**
     * @var MySQLDatabase Database instance
     */
    private $db;

    /**
     * @var string Sessions table name
     */
    private $sessionsTable = 'user_sessions';

    /**
     * @var string Password reset table
     */
    private $passwordResetTable = 'password_resets';

    /**
     * @var string Permissions table
     */
    private $permissionsTable = 'user_permissions';

    /**
     * @var string Login attempts table
     */
    private $loginAttemptsTable = 'login_attempts';

    /**
     * Session expiry (30 days)
     */
    const SESSION_EXPIRY = 2592000; // 30 days in seconds

    /**
     * Max login attempts
     */
    const MAX_LOGIN_ATTEMPTS = 5;

    /**
     * Login lockout time (15 minutes)
     */
    const LOCKOUT_TIME = 900;

    /**
     * Constructor
     * 
     * @param MySQLDatabase $db Database instance
     */
    public function __construct(MySQLDatabase $db)
    {
        $this->db = $db;
    }

    /**
     * Authenticate user
     * 
     * @param string $email
     * @param string $password
     * @return array
     * @throws Exception
     */
    public function authenticate(string $email, string $password): array
    {
        // Check for login attempts and lockout
        if ($this->isAccountLocked($email)) {
            throw new Exception("Account temporarily locked due to too many failed login attempts");
        }

        $userModel = new UserProfile($this->db);
        $user = $userModel->getUserForAuth($email);

        if (!$user || !password_verify($password, $user['password'])) {
            $this->recordFailedAttempt($email);
            throw new Exception("Invalid email or password");
        }

        // Clear failed attempts on successful login
        $this->clearFailedAttempts($email);

        // Create session
        $session = $this->createSession($user['id']);

        // Remove password from user data
        unset($user['password']);

        return [
            'user' => $user,
            'session' => $session
        ];
    }

    /**
     * Create user session
     * 
     * @param int $userId
     * @return array
     * @throws Exception
     */
    public function createSession(int $userId): array
    {
        $sessionToken = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', time() + self::SESSION_EXPIRY);

        $sessionData = [
            'user_id' => $userId,
            'session_token' => $sessionToken,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            'expires_at' => $expiresAt,
            'created_at' => date('Y-m-d H:i:s')
        ];

        // Invalidate previous sessions for this user if needed
        // $this->invalidateUserSessions($userId);

        $columns = implode(', ', array_keys($sessionData));
        $placeholders = ':' . implode(', :', array_keys($sessionData));
        
        $query = "INSERT INTO {$this->sessionsTable} ({$columns}) VALUES ({$placeholders})";
        $this->db->query($query, $sessionData);

        return [
            'session_token' => $sessionToken,
            'expires_at' => $expiresAt
        ];
    }

    /**
     * Validate session token
     * 
     * @param string $sessionToken
     * @return array|null
     */
    public function validateSession(string $sessionToken): ?array
    {
        $query = "SELECT s.*, u.* 
                  FROM {$this->sessionsTable} s
                  INNER JOIN users u ON s.user_id = u.id
                  WHERE s.session_token = :token AND s.expires_at > :now AND u.status = :status";
        
        $session = $this->db->fetchOne($query, [
            'token' => $sessionToken,
            'now' => date('Y-m-d H:i:s'),
            'status' => UserProfile::STATUS_ACTIVE
        ]);

        if ($session) {
            unset($session['password']);
            
            // Update session expiry
            $this->updateSessionExpiry($sessionToken);
            
            return $session;
        }

        return null;
    }

    /**
     * Update session expiry
     * 
     * @param string $sessionToken
     * @return bool
     */
    private function updateSessionExpiry(string $sessionToken): bool
    {
        $newExpiry = date('Y-m-d H:i:s', time() + self::SESSION_EXPIRY);
        
        $query = "UPDATE {$this->sessionsTable} SET expires_at = :expires_at WHERE session_token = :token";
        $this->db->query($query, [
            'expires_at' => $newExpiry,
            'token' => $sessionToken
        ]);

        return true;
    }

    /**
     * Logout user
     * 
     * @param string $sessionToken
     * @return bool
     */
    public function logout(string $sessionToken): bool
    {
        $query = "DELETE FROM {$this->sessionsTable} WHERE session_token = :token";
        $this->db->query($query, ['token' => $sessionToken]);

        return true;
    }

    /**
     * Logout all user sessions
     * 
     * @param int $userId
     * @return bool
     */
    public function logoutAllSessions(int $userId): bool
    {
        $query = "DELETE FROM {$this->sessionsTable} WHERE user_id = :user_id";
        $this->db->query($query, ['user_id' => $userId]);

        return true;
    }

    /**
     * Initiate password reset
     * 
     * @param string $email
     * @return array
     * @throws Exception
     */
    public function initiatePasswordReset(string $email): array
    {
        $userModel = new UserProfile($this->db);
        $user = $userModel->getUserByEmail($email);

        if (!$user) {
            // Don't reveal whether email exists
            return [
                'success' => true,
                'message' => 'If the email exists, a reset link has been sent'
            ];
        }

        $resetToken = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', time() + 3600); // 1 hour

        // Invalidate any existing reset tokens
        $this->invalidateResetTokens($user['id']);

        $resetData = [
            'user_id' => $user['id'],
            'reset_token' => $resetToken,
            'expires_at' => $expiresAt,
            'created_at' => date('Y-m-d H:i:s')
        ];

        $columns = implode(', ', array_keys($resetData));
        $placeholders = ':' . implode(', :', array_keys($resetData));
        
        $query = "INSERT INTO {$this->passwordResetTable} ({$columns}) VALUES ({$placeholders})";
        $this->db->query($query, $resetData);

        return [
            'success' => true,
            'reset_token' => $resetToken,
            'expires_at' => $expiresAt
        ];
    }

    /**
     * Reset password using token
     * 
     * @param string $resetToken
     * @param string $newPassword
     * @return bool
     * @throws Exception
     */
    public function resetPassword(string $resetToken, string $newPassword): bool
    {
        $resetRecord = $this->db->fetchOne(
            "SELECT * FROM {$this->passwordResetTable} WHERE reset_token = :token AND expires_at > :now",
            [
                'token' => $resetToken,
                'now' => date('Y-m-d H:i:s')
            ]
        );

        if (!$resetRecord) {
            throw new Exception("Invalid or expired reset token");
        }

        // Update password
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        
        $query = "UPDATE users SET password = :password, updated_at = :updated_at WHERE id = :id";
        $this->db->query($query, [
            'password' => $hashedPassword,
            'updated_at' => date('Y-m-d H:i:s'),
            'id' => $resetRecord['user_id']
        ]);

        // Invalidate reset token and all sessions
        $this->invalidateResetTokens($resetRecord['user_id']);
        $this->logoutAllSessions($resetRecord['user_id']);

        return true;
    }

    /**
     * Check if user has permission
     * 
     * @param int $userId
     * @param string $permission
     * @return bool
     */
    public function hasPermission(int $userId, string $permission): bool
    {
        $userModel = new UserProfile($this->db);
        $user = $userModel->getUserById($userId);

        if (!$user) {
            return false;
        }

        // Admin users have all permissions
        if ($user['user_type'] === UserProfile::TYPE_ADMIN) {
            return true;
        }

        // Check specific permissions
        $query = "SELECT COUNT(*) FROM {$this->permissionsTable} 
                  WHERE user_id = :user_id AND permission = :permission AND granted = TRUE";
        
        $count = $this->db->fetchColumn($query, [
            'user_id' => $userId,
            'permission' => $permission
        ]) ?: 0;

        return $count > 0;
    }

    /**
     * Grant permission to user
     * 
     * @param int $userId
     * @param string $permission
     * @return bool
     */
    public function grantPermission(int $userId, string $permission): bool
    {
        // Check if permission already exists
        $existing = $this->db->fetchOne(
            "SELECT id FROM {$this->permissionsTable} WHERE user_id = :user_id AND permission = :permission",
            ['user_id' => $userId, 'permission' => $permission]
        );

        if ($existing) {
            // Update existing
            $query = "UPDATE {$this->permissionsTable} SET granted = TRUE, updated_at = :updated_at 
                      WHERE user_id = :user_id AND permission = :permission";
        } else {
            // Create new
            $query = "INSERT INTO {$this->permissionsTable} (user_id, permission, granted, created_at, updated_at) 
                      VALUES (:user_id, :permission, TRUE, :created_at, :updated_at)";
        }

        $this->db->query($query, [
            'user_id' => $userId,
            'permission' => $permission,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        return true;
    }

    /**
     * Revoke permission from user
     * 
     * @param int $userId
     * @param string $permission
     * @return bool
     */
    public function revokePermission(int $userId, string $permission): bool
    {
        $query = "UPDATE {$this->permissionsTable} SET granted = FALSE, updated_at = :updated_at 
                  WHERE user_id = :user_id AND permission = :permission";
        
        $this->db->query($query, [
            'updated_at' => date('Y-m-d H:i:s'),
            'user_id' => $userId,
            'permission' => $permission
        ]);

        return true;
    }

    /**
     * Check if account is locked due to failed attempts
     * 
     * @param string $email
     * @return bool
     */
    private function isAccountLocked(string $email): bool
    {
        $query = "SELECT COUNT(*) as attempts 
                  FROM {$this->loginAttemptsTable} 
                  WHERE email = :email AND attempt_time > :time_threshold";
        
        $attempts = $this->db->fetchColumn($query, [
            'email' => $email,
            'time_threshold' => date('Y-m-d H:i:s', time() - self::LOCKOUT_TIME)
        ]) ?: 0;

        return $attempts >= self::MAX_LOGIN_ATTEMPTS;
    }

    /**
     * Record failed login attempt
     * 
     * @param string $email
     * @return bool
     */
    private function recordFailedAttempt(string $email): bool
    {
        $attemptData = [
            'email' => $email,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            'attempt_time' => date('Y-m-d H:i:s')
        ];

        $columns = implode(', ', array_keys($attemptData));
        $placeholders = ':' . implode(', :', array_keys($attemptData));
        
        $query = "INSERT INTO {$this->loginAttemptsTable} ({$columns}) VALUES ({$placeholders})";
        $this->db->query($query, $attemptData);

        return true;
    }

    /**
     * Clear failed login attempts
     * 
     * @param string $email
     * @return bool
     */
    private function clearFailedAttempts(string $email): bool
    {
        $query = "DELETE FROM {$this->loginAttemptsTable} WHERE email = :email";
        $this->db->query($query, ['email' => $email]);

        return true;
    }

    /**
     * Invalidate password reset tokens
     * 
     * @param int $userId
     * @return bool
     */
    private function invalidateResetTokens(int $userId): bool
    {
        $query = "DELETE FROM {$this->passwordResetTable} WHERE user_id = :user_id";
        $this->db->query($query, ['user_id' => $userId]);

        return true;
    }

    /**
     * Clean up expired sessions and reset tokens
     * 
     * @return bool
     */
    public function cleanupExpired(): bool
    {
        $now = date('Y-m-d H:i:s');

        // Clean expired sessions
        $this->db->query("DELETE FROM {$this->sessionsTable} WHERE expires_at <= :now", ['now' => $now]);

        // Clean expired password reset tokens
        $this->db->query("DELETE FROM {$this->passwordResetTable} WHERE expires_at <= :now", ['now' => $now]);

        // Clean old login attempts (older than lockout time * 2)
        $oldAttempts = date('Y-m-d H:i:s', time() - (self::LOCKOUT_TIME * 2));
        $this->db->query("DELETE FROM {$this->loginAttemptsTable} WHERE attempt_time <= :old", ['old' => $oldAttempts]);

        return true;
    }
}
