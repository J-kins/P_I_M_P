<?php
/**
 * P.I.M.P - User Model
 * Handles user data operations
 */

namespace PIMP\Models;

use PIMP\Services\Database\MySQLDatabase;
use Exception;

class UserModel
{
    private $db;
    private $table = 'users';

    public function __construct(MySQLDatabase $db)
    {
        $this->db = $db;
    }

    /**
     * Create a new user
     * 
     * @param array $data
     * @return int User ID
     */
    public function create(array $data): int
    {
        $query = "INSERT INTO {$this->table} 
                  (username, name_json, email, phone_json, status, email_verified)
                  VALUES (?, ?, ?, ?, ?, ?)";

        $this->db->query($query, [
            $data['username'],
            $data['name_json'],
            $data['email'],
            $data['phone_json'] ?? null,
            $data['status'] ?? 'pending_verification',
            $data['email_verified'] ?? 0
        ]);

        return (int)$this->db->lastInsertId();
    }

    /**
     * Find user by ID
     * 
     * @param int $id
     * @return array|null
     */
    public function findById(int $id): ?array
    {
        return $this->db->fetchOne(
            "SELECT * FROM {$this->table} WHERE id = ? AND deleted_at IS NULL",
            [$id]
        );
    }

    /**
     * Find user by UUID
     * 
     * @param string $uuid
     * @return array|null
     */
    public function findByUuid(string $uuid): ?array
    {
        return $this->db->fetchOne(
            "SELECT * FROM {$this->table} WHERE uuid = ? AND deleted_at IS NULL",
            [$uuid]
        );
    }

    /**
     * Find user by email
     * 
     * @param string $email
     * @return array|null
     */
    public function findByEmail(string $email): ?array
    {
        return $this->db->fetchOne(
            "SELECT * FROM {$this->table} WHERE email = ? AND deleted_at IS NULL",
            [$email]
        );
    }

    /**
     * Find user by username
     * 
     * @param string $username
     * @return array|null
     */
    public function findByUsername(string $username): ?array
    {
        return $this->db->fetchOne(
            "SELECT * FROM {$this->table} WHERE username = ? AND deleted_at IS NULL",
            [$username]
        );
    }

    /**
     * Update user
     * 
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update(int $id, array $data): bool
    {
        $fields = [];
        $values = [];

        foreach ($data as $key => $value) {
            $fields[] = "{$key} = ?";
            $values[] = $value;
        }

        $values[] = $id;

        $query = "UPDATE {$this->table} SET " . implode(', ', $fields) . " WHERE id = ?";
        $this->db->query($query, $values);

        return true;
    }

    /**
     * Delete user (soft delete)
     * 
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        $this->db->query(
            "UPDATE {$this->table} SET deleted_at = NOW() WHERE id = ?",
            [$id]
        );

        return true;
    }

    /**
     * Store user credentials
     * 
     * @param int $userId
     * @param string $type
     * @param string $value
     * @return int
     */
    public function storeCredentials(int $userId, string $type, string $value): int
    {
        $query = "INSERT INTO user_credentials 
                  (user_id, credential_type, credential_value, is_primary)
                  VALUES (?, ?, ?, 1)";

        $this->db->query($query, [$userId, $type, $value]);

        return (int)$this->db->lastInsertId();
    }

    /**
     * Get user credentials
     * 
     * @param int $userId
     * @param string $type
     * @return array|null
     */
    public function getCredentials(int $userId, string $type = 'password'): ?array
    {
        return $this->db->fetchOne(
            "SELECT * FROM user_credentials WHERE user_id = ? AND credential_type = ?",
            [$userId, $type]
        );
    }

    /**
     * Update user password
     * 
     * @param int $userId
     * @param string $newPassword
     * @return bool
     */
    public function updatePassword(int $userId, string $newPassword): bool
    {
        $this->db->query(
            "UPDATE user_credentials SET credential_value = ?, updated_at = NOW() 
             WHERE user_id = ? AND credential_type = 'password'",
            [$newPassword, $userId]
        );

        $this->db->query(
            "UPDATE {$this->table} SET password_changed_at = NOW() WHERE id = ?",
            [$userId]
        );

        return true;
    }

    /**
     * Update last login timestamp
     * 
     * @param int $userId
     * @return bool
     */
    public function updateLastLogin(int $userId): bool
    {
        $this->db->query(
            "UPDATE {$this->table} SET last_login_at = NOW() WHERE id = ?",
            [$userId]
        );

        return true;
    }

    /**
     * Increment login attempts
     * 
     * @param int $userId
     * @return bool
     */
    public function incrementLoginAttempts(int $userId): bool
    {
        $this->db->query(
            "UPDATE {$this->table} SET login_attempts = login_attempts + 1 WHERE id = ?",
            [$userId]
        );

        return true;
    }

    /**
     * Reset login attempts
     * 
     * @param int $userId
     * @return bool
     */
    public function resetLoginAttempts(int $userId): bool
    {
        $this->db->query(
            "UPDATE {$this->table} SET login_attempts = 0 WHERE id = ?",
            [$userId]
        );

        return true;
    }

    /**
     * Get user roles
     * 
     * @param int $userId
     * @return array
     */
    public function getRoles(int $userId): array
    {
        return $this->db->fetchAll(
            "SELECT r.* FROM roles r
             JOIN user_roles ur ON r.id = ur.role_id
             WHERE ur.user_id = ? 
             AND (ur.effective_until IS NULL OR ur.effective_until > NOW())",
            [$userId]
        );
    }

    /**
     * Get user permissions
     * 
     * @param int $userId
     * @return array
     */
    public function getPermissions(int $userId): array
    {
        $roles = $this->getRoles($userId);
        $permissions = [];

        foreach ($roles as $role) {
            $rolePermissions = json_decode($role['permissions_json'], true);
            $permissions = array_merge($permissions, $rolePermissions);
        }

        return array_unique($permissions);
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
        $permissions = $this->getPermissions($userId);
        
        // Check for wildcard permission
        if (in_array('*', $permissions)) {
            return true;
        }

        return in_array($permission, $permissions);
    }

    /**
     * Get user settings
     * 
     * @param int $userId
     * @param string|null $key
     * @return array|mixed
     */
    public function getSettings(int $userId, ?string $key = null)
    {
        if ($key) {
            $setting = $this->db->fetchOne(
                "SELECT setting_value, data_type FROM user_settings WHERE user_id = ? AND setting_key = ?",
                [$userId, $key]
            );

            return $setting ? json_decode($setting['setting_value'], true) : null;
        }

        $settings = $this->db->fetchAll(
            "SELECT setting_key, setting_value, data_type FROM user_settings WHERE user_id = ?",
            [$userId]
        );

        $result = [];
        foreach ($settings as $setting) {
            $result[$setting['setting_key']] = json_decode($setting['setting_value'], true);
        }

        return $result;
    }

    /**
     * Set user setting
     * 
     * @param int $userId
     * @param string $key
     * @param mixed $value
     * @param string $dataType
     * @return bool
     */
    public function setSetting(int $userId, string $key, $value, string $dataType = 'string'): bool
    {
        $jsonValue = json_encode($value);

        $this->db->query(
            "INSERT INTO user_settings (user_id, setting_key, setting_value, data_type)
             VALUES (?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE setting_value = ?, data_type = ?",
            [$userId, $key, $jsonValue, $dataType, $jsonValue, $dataType]
        );

        return true;
    }

    /**
     * Get user statistics
     * 
     * @param int $userId
     * @return array
     */
    public function getStatistics(int $userId): array
    {
        return [
            'total_reviews' => $this->db->fetchColumn(
                "SELECT COUNT(*) FROM business_reviews WHERE reviewer_user_id = ?",
                [$userId]
            ),
            'total_complaints' => $this->db->fetchColumn(
                "SELECT COUNT(*) FROM complaints WHERE complainant_user_id = ?",
                [$userId]
            ),
            'account_age_days' => $this->db->fetchColumn(
                "SELECT DATEDIFF(NOW(), created_at) FROM {$this->table} WHERE id = ?",
                [$userId]
            )
        ];
    }

    /**
     * Get all users (paginated)
     * 
     * @param int $page
     * @param int $perPage
     * @param array $filters
     * @return array
     */
    public function getAll(int $page = 1, int $perPage = 20, array $filters = []): array
    {
        $offset = ($page - 1) * $perPage;
        $where = ["deleted_at IS NULL"];
        $params = [];

        if (!empty($filters['status'])) {
            $where[] = "status = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['search'])) {
            $where[] = "(username LIKE ? OR email LIKE ?)";
            $search = "%{$filters['search']}%";
            $params[] = $search;
            $params[] = $search;
        }

        $whereClause = implode(' AND ', $where);

        $users = $this->db->fetchAll(
            "SELECT * FROM {$this->table} WHERE {$whereClause} 
             ORDER BY created_at DESC LIMIT ? OFFSET ?",
            array_merge($params, [$perPage, $offset])
        );

        $total = $this->db->fetchColumn(
            "SELECT COUNT(*) FROM {$this->table} WHERE {$whereClause}",
            $params
        );

        return [
            'data' => $users,
            'total' => (int)$total,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => ceil($total / $perPage)
        ];
    }
}
