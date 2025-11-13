<?php
/**
 * P.I.M.P - User Profiles Model
 * Handles user profiles, authentication, and user management
 */

namespace PIMP\Models;

use PIMP\Services\Database\MySQLDatabase;
use PDOException;
use Exception;

class UserProfile
{
    /**
     * @var MySQLDatabase Database instance
     */
    private $db;

    /**
     * @var string Users table name
     */
    private $usersTable = 'users';

    /**
     * @var string User preferences table
     */
    private $preferencesTable = 'user_preferences';

    /**
     * @var string Saved items table
     */
    private $savedItemsTable = 'user_saved_items';

    /**
     * User types
     */
    const TYPE_CONSUMER = 'consumer';
    const TYPE_BUSINESS_OWNER = 'business_owner';
    const TYPE_BUSINESS_REPRESENTATIVE = 'business_representative';
    const TYPE_ADMIN = 'admin';
    const TYPE_MODERATOR = 'moderator';

    /**
     * User status
     */
    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';
    const STATUS_SUSPENDED = 'suspended';
    const STATUS_PENDING = 'pending';
    const STATUS_VERIFIED = 'verified';

    /**
     * Verification levels
     */
    const VERIFICATION_NONE = 'none';
    const VERIFICATION_BASIC = 'basic';
    const VERIFICATION_FULL = 'full';

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
     * Create new user
     * 
     * @param array $userData
     * @return array
     * @throws Exception
     */
    public function createUser(array $userData): array
    {
        $requiredFields = ['email', 'password', 'first_name', 'last_name', 'user_type'];
        foreach ($requiredFields as $field) {
            if (empty($userData[$field])) {
                throw new Exception("Required field missing: {$field}");
            }
        }

        // Validate user type
        $validTypes = [
            self::TYPE_CONSUMER, self::TYPE_BUSINESS_OWNER, 
            self::TYPE_BUSINESS_REPRESENTATIVE, self::TYPE_ADMIN, self::TYPE_MODERATOR
        ];
        if (!in_array($userData['user_type'], $validTypes)) {
            throw new Exception("Invalid user type: {$userData['user_type']}");
        }

        // Check if email already exists
        $existing = $this->getUserByEmail($userData['email']);
        if ($existing) {
            throw new Exception("Email already registered");
        }

        $userData['password'] = password_hash($userData['password'], PASSWORD_DEFAULT);
        $userData['user_id'] = $this->generateUserId();
        $userData['status'] = self::STATUS_ACTIVE;
        $userData['verification_level'] = self::VERIFICATION_NONE;
        $userData['email_verified'] = false;
        $userData['created_at'] = date('Y-m-d H:i:s');
        $userData['updated_at'] = date('Y-m-d H:i:s');

        try {
            $this->db->beginTransaction();

            $columns = implode(', ', array_keys($userData));
            $placeholders = ':' . implode(', :', array_keys($userData));
            
            $query = "INSERT INTO {$this->usersTable} ({$columns}) VALUES ({$placeholders})";
            $this->db->query($query, $userData);

            $userId = $this->db->lastInsertId();

            // Create default preferences
            $this->createDefaultPreferences($userId);
            
            $this->db->commit();

            return $this->getUserById($userId);
            
        } catch (PDOException $e) {
            $this->db->rollback();
            throw new Exception("Failed to create user: " . $e->getMessage());
        }
    }

    /**
     * Get user by ID
     * 
     * @param int $userId
     * @return array|null
     */
    public function getUserById(int $userId): ?array
    {
        $query = "SELECT * FROM {$this->usersTable} WHERE id = :id";
        $user = $this->db->fetchOne($query, ['id' => $userId]);
        
        if ($user) {
            unset($user['password']); // Never return password
            $user['preferences'] = $this->getUserPreferences($userId);
        }
        
        return $user ?: null;
    }

    /**
     * Get user by email
     * 
     * @param string $email
     * @return array|null
     */
    public function getUserByEmail(string $email): ?array
    {
        $query = "SELECT * FROM {$this->usersTable} WHERE email = :email";
        $user = $this->db->fetchOne($query, ['email' => $email]);
        
        if ($user) {
            unset($user['password']);
            $user['preferences'] = $this->getUserPreferences($user['id']);
        }
        
        return $user ?: null;
    }

    /**
     * Get user with password for authentication
     * 
     * @param string $email
     * @return array|null
     */
    public function getUserForAuth(string $email): ?array
    {
        $query = "SELECT * FROM {$this->usersTable} WHERE email = :email AND status = :status";
        return $this->db->fetchOne($query, [
            'email' => $email,
            'status' => self::STATUS_ACTIVE
        ]) ?: null;
    }

    /**
     * Update user profile
     * 
     * @param int $userId
     * @param array $updateData
     * @return array
     * @throws Exception
     */
    public function updateUser(int $userId, array $updateData): array
    {
        $user = $this->getUserById($userId);
        if (!$user) {
            throw new Exception("User not found");
        }

        // Remove non-updatable fields
        unset($updateData['id'], $updateData['user_id'], $updateData['email'], $updateData['created_at']);
        
        // Handle password update
        if (!empty($updateData['password'])) {
            $updateData['password'] = password_hash($updateData['password'], PASSWORD_DEFAULT);
        } else {
            unset($updateData['password']);
        }

        $updateData['updated_at'] = date('Y-m-d H:i:s');

        $setParts = [];
        foreach (array_keys($updateData) as $field) {
            $setParts[] = "{$field} = :{$field}";
        }

        $query = "UPDATE {$this->usersTable} SET " . implode(', ', $setParts) . " WHERE id = :id";
        $updateData['id'] = $userId;

        $this->db->query($query, $updateData);

        return $this->getUserById($userId);
    }

    /**
     * Update user status
     * 
     * @param int $userId
     * @param string $status
     * @param string $reason
     * @return bool
     * @throws Exception
     */
    public function updateUserStatus(int $userId, string $status, string $reason = ''): bool
    {
        $validStatuses = [
            self::STATUS_ACTIVE, self::STATUS_INACTIVE, 
            self::STATUS_SUSPENDED, self::STATUS_PENDING, self::STATUS_VERIFIED
        ];

        if (!in_array($status, $validStatuses)) {
            throw new Exception("Invalid user status: {$status}");
        }

        $query = "UPDATE {$this->usersTable} SET status = :status, status_reason = :reason, 
                  updated_at = :updated_at WHERE id = :id";
        
        $this->db->query($query, [
            'status' => $status,
            'reason' => $reason,
            'updated_at' => date('Y-m-d H:i:s'),
            'id' => $userId
        ]);

        return true;
    }

    /**
     * Update verification level
     * 
     * @param int $userId
     * @param string $verificationLevel
     * @return bool
     * @throws Exception
     */
    public function updateVerificationLevel(int $userId, string $verificationLevel): bool
    {
        $validLevels = [self::VERIFICATION_NONE, self::VERIFICATION_BASIC, self::VERIFICATION_FULL];
        if (!in_array($verificationLevel, $validLevels)) {
            throw new Exception("Invalid verification level: {$verificationLevel}");
        }

        $query = "UPDATE {$this->usersTable} SET verification_level = :level, updated_at = :updated_at WHERE id = :id";
        $this->db->query($query, [
            'level' => $verificationLevel,
            'updated_at' => date('Y-m-d H:i:s'),
            'id' => $userId
        ]);

        return true;
    }

    /**
     * Verify user email
     * 
     * @param int $userId
     * @return bool
     * @throws Exception
     */
    public function verifyEmail(int $userId): bool
    {
        $query = "UPDATE {$this->usersTable} SET email_verified = TRUE, updated_at = :updated_at WHERE id = :id";
        $this->db->query($query, [
            'updated_at' => date('Y-m-d H:i:s'),
            'id' => $userId
        ]);

        return true;
    }

    /**
     * Get user preferences
     * 
     * @param int $userId
     * @return array
     */
    public function getUserPreferences(int $userId): array
    {
        $query = "SELECT * FROM {$this->preferencesTable} WHERE user_id = :user_id";
        $preferences = $this->db->fetchAll($query, ['user_id' => $userId]);
        
        $formatted = [];
        foreach ($preferences as $pref) {
            $formatted[$pref['preference_key']] = $pref['preference_value'];
        }
        
        return $formatted;
    }

    /**
     * Update user preference
     * 
     * @param int $userId
     * @param string $key
     * @param string $value
     * @return bool
     */
    public function updatePreference(int $userId, string $key, string $value): bool
    {
        // Check if preference exists
        $existing = $this->db->fetchOne(
            "SELECT id FROM {$this->preferencesTable} WHERE user_id = :user_id AND preference_key = :key",
            ['user_id' => $userId, 'key' => $key]
        );

        if ($existing) {
            // Update existing
            $query = "UPDATE {$this->preferencesTable} SET preference_value = :value, updated_at = :updated_at 
                      WHERE user_id = :user_id AND preference_key = :key";
        } else {
            // Create new
            $query = "INSERT INTO {$this->preferencesTable} (user_id, preference_key, preference_value, created_at, updated_at) 
                      VALUES (:user_id, :key, :value, :created_at, :updated_at)";
        }

        $this->db->query($query, [
            'user_id' => $userId,
            'key' => $key,
            'value' => $value,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        return true;
    }

    /**
     * Save business for user
     * 
     * @param int $userId
     * @param int $businessId
     * @param string $category
     * @return bool
     * @throws Exception
     */
    public function saveBusiness(int $userId, int $businessId, string $category = 'favorites'): bool
    {
        // Check if already saved
        $existing = $this->db->fetchOne(
            "SELECT id FROM {$this->savedItemsTable} 
             WHERE user_id = :user_id AND business_id = :business_id AND category = :category",
            ['user_id' => $userId, 'business_id' => $businessId, 'category' => $category]
        );

        if ($existing) {
            throw new Exception("Business already saved in this category");
        }

        $saveData = [
            'user_id' => $userId,
            'business_id' => $businessId,
            'item_type' => 'business',
            'category' => $category,
            'created_at' => date('Y-m-d H:i:s')
        ];

        $columns = implode(', ', array_keys($saveData));
        $placeholders = ':' . implode(', :', array_keys($saveData));
        
        $query = "INSERT INTO {$this->savedItemsTable} ({$columns}) VALUES ({$placeholders})";
        $this->db->query($query, $saveData);

        return true;
    }

    /**
     * Remove saved business
     * 
     * @param int $userId
     * @param int $businessId
     * @param string $category
     * @return bool
     */
    public function removeSavedBusiness(int $userId, int $businessId, string $category = 'favorites'): bool
    {
        $query = "DELETE FROM {$this->savedItemsTable} 
                  WHERE user_id = :user_id AND business_id = :business_id AND category = :category";
        
        $this->db->query($query, [
            'user_id' => $userId,
            'business_id' => $businessId,
            'category' => $category
        ]);

        return true;
    }

    /**
     * Get user's saved businesses
     * 
     * @param int $userId
     * @param array $filters
     * @return array
     */
    public function getSavedBusinesses(int $userId, array $filters = []): array
    {
        $whereConditions = ["si.user_id = :user_id", "si.item_type = 'business'"];
        $params = ['user_id' => $userId];

        if (!empty($filters['category'])) {
            $whereConditions[] = "si.category = :category";
            $params['category'] = $filters['category'];
        }

        $whereClause = implode(' AND ', $whereConditions);
        
        $query = "SELECT si.*, b.business_name, b.business_id, b.city, b.state, b.rating, b.accreditation_level 
                  FROM {$this->savedItemsTable} si
                  INNER JOIN business_profiles b ON si.business_id = b.id
                  WHERE {$whereClause}
                  ORDER BY si.created_at DESC";
        
        return $this->db->fetchAll($query, $params);
    }

    /**
     * Search users
     * 
     * @param array $filters
     * @param int $page
     * @param int $perPage
     * @return array
     */
    public function searchUsers(array $filters = [], int $page = 1, int $perPage = 20): array
    {
        $whereConditions = ["1=1"];
        $params = [];

        if (!empty($filters['user_type'])) {
            $whereConditions[] = "user_type = :user_type";
            $params['user_type'] = $filters['user_type'];
        }

        if (!empty($filters['status'])) {
            $whereConditions[] = "status = :status";
            $params['status'] = $filters['status'];
        }

        if (!empty($filters['verification_level'])) {
            $whereConditions[] = "verification_level = :verification_level";
            $params['verification_level'] = $filters['verification_level'];
        }

        if (!empty($filters['email'])) {
            $whereConditions[] = "email LIKE :email";
            $params['email'] = '%' . $filters['email'] . '%';
        }

        if (!empty($filters['name'])) {
            $whereConditions[] = "(first_name LIKE :name OR last_name LIKE :name)";
            $params['name'] = '%' . $filters['name'] . '%';
        }

        $whereClause = implode(' AND ', $whereConditions);
        $offset = ($page - 1) * $perPage;

        $query = "SELECT id, user_id, first_name, last_name, email, user_type, status, 
                         verification_level, email_verified, created_at 
                  FROM {$this->usersTable} 
                  WHERE {$whereClause} 
                  ORDER BY created_at DESC 
                  LIMIT :limit OFFSET :offset";
        
        $params['limit'] = $perPage;
        $params['offset'] = $offset;

        $users = $this->db->fetchAll($query, $params);

        // Get total count for pagination
        $countQuery = "SELECT COUNT(*) as total FROM {$this->usersTable} WHERE {$whereClause}";
        $total = $this->db->fetchColumn($countQuery, $params);

        return [
            'users' => $users,
            'pagination' => [
                'page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'total_pages' => ceil($total / $perPage)
            ]
        ];
    }

    /**
     * Get user statistics
     * 
     * @param int $userId
     * @return array
     */
    public function getUserStatistics(int $userId): array
    {
        $savedBusinesses = $this->db->fetchColumn(
            "SELECT COUNT(*) FROM {$this->savedItemsTable} WHERE user_id = :user_id AND item_type = 'business'",
            ['user_id' => $userId]
        ) ?: 0;

        // These would be calculated from other models
        $reviewsWritten = 0;
        $complaintsFiled = 0;
        $newslettersSubscribed = 0;

        return [
            'saved_businesses' => (int)$savedBusinesses,
            'reviews_written' => $reviewsWritten,
            'complaints_filed' => $complaintsFiled,
            'newsletters_subscribed' => $newslettersSubscribed,
            'account_age_days' => $this->getAccountAge($userId)
        ];
    }

    /**
     * Get account age in days
     * 
     * @param int $userId
     * @return int
     */
    private function getAccountAge(int $userId): int
    {
        $user = $this->getUserById($userId);
        if (!$user) {
            return 0;
        }

        $created = new \DateTime($user['created_at']);
        $now = new \DateTime();
        $interval = $now->diff($created);

        return (int)$interval->format('%a');
    }

    /**
     * Generate unique user ID
     * 
     * @return string
     */
    private function generateUserId(): string
    {
        $prefix = 'USR';
        $timestamp = time();
        $random = mt_rand(1000, 9999);
        
        return $prefix . $timestamp . $random;
    }

    /**
     * Create default user preferences
     * 
     * @param int $userId
     * @return bool
     */
    private function createDefaultPreferences(int $userId): bool
    {
        $defaultPreferences = [
            'email_notifications' => 'true',
            'newsletter_subscription' => 'true',
            'privacy_level' => 'standard',
            'theme' => 'purple1',
            'language' => 'en',
            'timezone' => 'UTC',
            'search_radius' => '10',
            'rating_display' => 'stars'
        ];

        foreach ($defaultPreferences as $key => $value) {
            $this->updatePreference($userId, $key, $value);
        }

        return true;
    }
}
