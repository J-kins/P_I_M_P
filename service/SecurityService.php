<?php
/**
 * P.I.M.P - Security Service
 * Handles security operations and threat detection
 */

namespace PIMP\Services;

use PIMP\Services\Database\MySQLDatabase;
use Exception;

class SecurityService
{
    private $db;
    private $maxLoginAttempts = 5;
    private $lockoutDuration = 900; // 15 minutes in seconds
    private $suspiciousThreshold = 10;

    public function __construct(MySQLDatabase $db)
    {
        $this->db = $db;
    }

    /**
     * Check session security
     * 
     * @param int $userId
     * @return bool
     */
    public function checkSession(int $userId): bool
    {
        $sessionToken = $_COOKIE['session_token'] ?? '';
        
        if (!$sessionToken) {
            return false;
        }

        // Validate session
        $session = $this->db->fetchOne(
            "SELECT * FROM user_sessions 
             WHERE session_token = ? AND user_id = ? AND is_active = 1 AND expires_at > NOW()",
            [$sessionToken, $userId]
        );

        if (!$session) {
            return false;
        }

        // Check for session hijacking indicators
        $currentIP = $_SERVER['REMOTE_ADDR'] ?? '';
        $currentUA = $_SERVER['HTTP_USER_AGENT'] ?? '';

        if ($session['ip_address'] !== $currentIP) {
            $this->logSuspiciousActivity($userId, 'session_ip_mismatch', [
                'original_ip' => $session['ip_address'],
                'current_ip' => $currentIP
            ]);
        }

        return true;
    }

    /**
     * Check rate limiting for login attempts
     * 
     * @param string $identifier Email or IP address
     * @param string $type 'email' or 'ip'
     * @return bool True if allowed, false if rate limited
     */
    public function checkRateLimit(string $identifier, string $type = 'ip'): bool
    {
        $key = "rate_limit_{$type}_{$identifier}";
        
        // Get recent attempts
        $attempts = $this->db->fetchColumn(
            "SELECT COUNT(*) FROM audit_logs 
             WHERE event_type = 'login_attempt' 
             AND metadata_json LIKE ? 
             AND event_timestamp > DATE_SUB(NOW(), INTERVAL 15 MINUTE)",
            ["%{$identifier}%"]
        );

        if ($attempts >= $this->maxLoginAttempts) {
            $this->blockIP($_SERVER['REMOTE_ADDR'] ?? '');
            return false;
        }

        return true;
    }

    /**
     * Block an IP address
     * 
     * @param string $ipAddress
     * @param int $duration Duration in seconds (0 = permanent)
     * @param string $reason
     * @return bool
     */
    public function blockIP(string $ipAddress, int $duration = 0, string $reason = 'Too many failed login attempts'): bool
    {
        $expiresAt = $duration > 0 ? date('Y-m-d H:i:s', time() + $duration) : null;

        $this->db->query(
            "INSERT INTO blocked_ips (ip_address, reason, expires_at, created_at)
             VALUES (?, ?, ?, NOW())
             ON DUPLICATE KEY UPDATE expires_at = ?, reason = ?",
            [$ipAddress, $reason, $expiresAt, $expiresAt, $reason]
        );

        return true;
    }

    /**
     * Check if IP is blocked
     * 
     * @param string $ipAddress
     * @return bool
     */
    public function isIPBlocked(string $ipAddress): bool
    {
        $blocked = $this->db->fetchOne(
            "SELECT * FROM blocked_ips 
             WHERE ip_address = ? 
             AND (expires_at IS NULL OR expires_at > NOW())",
            [$ipAddress]
        );

        return $blocked !== null;
    }

    /**
     * Unblock an IP address
     * 
     * @param string $ipAddress
     * @return bool
     */
    public function unblockIP(string $ipAddress): bool
    {
        $this->db->query(
            "DELETE FROM blocked_ips WHERE ip_address = ?",
            [$ipAddress]
        );

        return true;
    }

    /**
     * Get blocked IPs
     * 
     * @return array
     */
    public function getBlockedIPs(): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM blocked_ips ORDER BY created_at DESC LIMIT 100",
            []
        );
    }

    /**
     * Log suspicious activity
     * 
     * @param int $userId
     * @param string $activityType
     * @param array $metadata
     * @return bool
     */
    public function logSuspiciousActivity(int $userId, string $activityType, array $metadata = []): bool
    {
        $this->db->query(
            "INSERT INTO audit_logs (event_type, user_id, entity_type, entity_id, action, metadata_json, ip_address, user_agent)
             VALUES ('suspicious_activity', ?, 'security', 0, ?, ?, ?, ?)",
            [
                $userId,
                $activityType,
                json_encode($metadata),
                $_SERVER['REMOTE_ADDR'] ?? null,
                $_SERVER['HTTP_USER_AGENT'] ?? null
            ]
        );

        // Check if user has too many suspicious activities
        $count = $this->db->fetchColumn(
            "SELECT COUNT(*) FROM audit_logs 
             WHERE event_type = 'suspicious_activity' 
             AND user_id = ? 
             AND event_timestamp > DATE_SUB(NOW(), INTERVAL 1 HOUR)",
            [$userId]
        );

        if ($count >= $this->suspiciousThreshold) {
            $this->flagUserAccount($userId, 'Multiple suspicious activities detected');
        }

        return true;
    }

    /**
     * Flag user account for review
     * 
     * @param int $userId
     * @param string $reason
     * @return bool
     */
    public function flagUserAccount(int $userId, string $reason): bool
    {
        $this->db->query(
            "UPDATE users SET status = 'suspended' WHERE id = ?",
            [$userId]
        );

        $this->db->query(
            "INSERT INTO audit_logs (event_type, user_id, entity_type, entity_id, action, metadata_json)
             VALUES ('account_flagged', ?, 'user', ?, 'flag', ?)",
            [$userId, $userId, json_encode(['reason' => $reason])]
        );

        return true;
    }

    /**
     * Get recent failed login attempts
     * 
     * @param int $limit
     * @return array
     */
    public function getRecentFailedLogins(int $limit = 50): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM audit_logs 
             WHERE event_type = 'login_failed' 
             ORDER BY event_timestamp DESC 
             LIMIT ?",
            [$limit]
        );
    }

    /**
     * Get suspicious activities
     * 
     * @param int $limit
     * @return array
     */
    public function getSuspiciousActivities(int $limit = 50): array
    {
        return $this->db->fetchAll(
            "SELECT al.*, u.username, u.email 
             FROM audit_logs al
             LEFT JOIN users u ON al.user_id = u.id
             WHERE al.event_type = 'suspicious_activity' 
             ORDER BY al.event_timestamp DESC 
             LIMIT ?",
            [$limit]
        );
    }

    /**
     * Get security status
     * 
     * @return array
     */
    public function getSecurityStatus(): array
    {
        return [
            'blocked_ips_count' => $this->db->fetchColumn(
                "SELECT COUNT(*) FROM blocked_ips WHERE expires_at IS NULL OR expires_at > NOW()",
                []
            ),
            'failed_logins_today' => $this->db->fetchColumn(
                "SELECT COUNT(*) FROM audit_logs 
                 WHERE event_type = 'login_failed' 
                 AND DATE(event_timestamp) = CURDATE()",
                []
            ),
            'suspicious_activities_today' => $this->db->fetchColumn(
                "SELECT COUNT(*) FROM audit_logs 
                 WHERE event_type = 'suspicious_activity' 
                 AND DATE(event_timestamp) = CURDATE()",
                []
            ),
            'suspended_accounts' => $this->db->fetchColumn(
                "SELECT COUNT(*) FROM users WHERE status = 'suspended'",
                []
            ),
            'active_sessions' => $this->db->fetchColumn(
                "SELECT COUNT(*) FROM user_sessions WHERE is_active = 1 AND expires_at > NOW()",
                []
            )
        ];
    }

    /**
     * Validate password strength
     * 
     * @param string $password
     * @return array
     */
    public function validatePasswordStrength(string $password): array
    {
        $errors = [];
        $score = 0;

        if (strlen($password) < 8) {
            $errors[] = 'Password must be at least 8 characters long';
        } else {
            $score += 25;
        }

        if (strlen($password) >= 12) {
            $score += 25;
        }

        if (preg_match('/[a-z]/', $password)) {
            $score += 15;
        } else {
            $errors[] = 'Password must contain at least one lowercase letter';
        }

        if (preg_match('/[A-Z]/', $password)) {
            $score += 15;
        } else {
            $errors[] = 'Password must contain at least one uppercase letter';
        }

        if (preg_match('/[0-9]/', $password)) {
            $score += 10;
        } else {
            $errors[] = 'Password must contain at least one number';
        }

        if (preg_match('/[^a-zA-Z0-9]/', $password)) {
            $score += 10;
        } else {
            $errors[] = 'Password must contain at least one special character';
        }

        // Check against common passwords
        if ($this->isCommonPassword($password)) {
            $errors[] = 'Password is too common. Please choose a stronger password';
            $score = max(0, $score - 50);
        }

        $strength = 'weak';
        if ($score >= 80) {
            $strength = 'strong';
        } elseif ($score >= 60) {
            $strength = 'good';
        } elseif ($score >= 40) {
            $strength = 'fair';
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'score' => $score,
            'strength' => $strength
        ];
    }

    /**
     * Check if password is in common passwords list
     * 
     * @param string $password
     * @return bool
     */
    private function isCommonPassword(string $password): bool
    {
        $commonPasswords = [
            'password', '123456', '12345678', 'qwerty', 'abc123',
            'monkey', '1234567', 'letmein', 'trustno1', 'dragon',
            'baseball', 'iloveyou', 'master', 'sunshine', 'ashley',
            'bailey', 'passw0rd', 'shadow', '123123', '654321'
        ];

        return in_array(strtolower($password), $commonPasswords);
    }

    /**
     * Sanitize user input
     * 
     * @param string $input
     * @return string
     */
    public function sanitizeInput(string $input): string
    {
        // Remove any null bytes
        $input = str_replace(chr(0), '', $input);
        
        // Trim whitespace
        $input = trim($input);
        
        // Remove control characters
        $input = preg_replace('/[\x00-\x1F\x7F]/u', '', $input);
        
        return $input;
    }

    /**
     * Detect SQL injection attempts
     * 
     * @param string $input
     * @return bool
     */
    public function detectSQLInjection(string $input): bool
    {
        $patterns = [
            '/(\bUNION\b.*\bSELECT\b)/i',
            '/(\bSELECT\b.*\bFROM\b)/i',
            '/(\bINSERT\b.*\bINTO\b)/i',
            '/(\bUPDATE\b.*\bSET\b)/i',
            '/(\bDELETE\b.*\bFROM\b)/i',
            '/(\bDROP\b.*\bTABLE\b)/i',
            '/(--|\#|\/\*|\*\/)/i'
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $input)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Detect XSS attempts
     * 
     * @param string $input
     * @return bool
     */
    public function detectXSS(string $input): bool
    {
        $patterns = [
            '/<script\b[^>]*>(.*?)<\/script>/is',
            '/<iframe\b[^>]*>(.*?)<\/iframe>/is',
            '/javascript:/i',
            '/on\w+\s*=/i'
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $input)) {
                return true;
            }
        }

        return false;
    }
}
