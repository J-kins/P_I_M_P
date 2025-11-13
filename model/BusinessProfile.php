<?php
/**
 * P.I.M.P - Business Profile Model
 * Handles business registration, profile management, and accreditation
 */

namespace PIMP\Models;

use PIMP\Services\Database\MySQLDatabase;
use PDO;
use PDOException;
use Exception;

class BusinessProfile
{
    /**
     * @var MySQLDatabase Database instance
     */
    private $db;

    /**
     * @var string Table name
     */
    private $table = 'business_profiles';

    /**
     * Business status constants
     */
    const STATUS_PENDING = 'pending';
    const STATUS_ACTIVE = 'active';
    const STATUS_SUSPENDED = 'suspended';
    const STATUS_REJECTED = 'rejected';
    const STATUS_INACTIVE = 'inactive';

    /**
     * Accreditation levels
     */
    const ACCREDITATION_NONE = 'none';
    const ACCREDITATION_BASIC = 'basic';
    const ACCREDITATION_PREMIUM = 'premium';
    const ACCREDITATION_VERIFIED = 'verified';

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
     * Create new business profile
     * 
     * @param array $businessData
     * @return array
     * @throws Exception
     */
    public function create(array $businessData): array
    {
        $requiredFields = [
            'business_name', 'business_type', 'owner_name', 
            'email', 'phone', 'address', 'city', 'state', 'country'
        ];

        foreach ($requiredFields as $field) {
            if (empty($businessData[$field])) {
                throw new Exception("Required field missing: {$field}");
            }
        }

        // Generate unique business ID
        $businessData['business_id'] = $this->generateBusinessId();
        $businessData['status'] = self::STATUS_PENDING;
        $businessData['accreditation_level'] = self::ACCREDITATION_NONE;
        $businessData['created_at'] = date('Y-m-d H:i:s');
        $businessData['updated_at'] = date('Y-m-d H:i:s');

        try {
            $this->db->beginTransaction();

            $columns = implode(', ', array_keys($businessData));
            $placeholders = ':' . implode(', :', array_keys($businessData));
            
            $query = "INSERT INTO {$this->table} ({$columns}) VALUES ({$placeholders})";
            $stmt = $this->db->query($query, $businessData);

            $businessId = $this->db->lastInsertId();
            
            $this->db->commit();

            return $this->getById($businessId);
            
        } catch (PDOException $e) {
            $this->db->rollback();
            throw new Exception("Failed to create business profile: " . $e->getMessage());
        }
    }

    /**
     * Get business by ID
     * 
     * @param int $businessId
     * @return array|null
     */
    public function getById(int $businessId): ?array
    {
        $query = "SELECT * FROM {$this->table} WHERE id = :id AND status != :deleted_status";
        $result = $this->db->fetchOne($query, [
            'id' => $businessId,
            'deleted_status' => 'deleted'
        ]);

        return $result ?: null;
    }

    /**
     * Get business by business ID
     * 
     * @param string $businessId
     * @return array|null
     */
    public function getByBusinessId(string $businessId): ?array
    {
        $query = "SELECT * FROM {$this->table} WHERE business_id = :business_id AND status != :deleted_status";
        $result = $this->db->fetchOne($query, [
            'business_id' => $businessId,
            'deleted_status' => 'deleted'
        ]);

        return $result ?: null;
    }

    /**
     * Update business profile
     * 
     * @param int $businessId
     * @param array $updateData
     * @return array
     * @throws Exception
     */
    public function update(int $businessId, array $updateData): array
    {
        $business = $this->getById($businessId);
        if (!$business) {
            throw new Exception("Business not found");
        }

        // Remove non-updatable fields
        unset($updateData['id'], $updateData['business_id'], $updateData['created_at']);
        
        $updateData['updated_at'] = date('Y-m-d H:i:s');

        $setParts = [];
        foreach (array_keys($updateData) as $field) {
            $setParts[] = "{$field} = :{$field}";
        }

        $query = "UPDATE {$this->table} SET " . implode(', ', $setParts) . " WHERE id = :id";
        $updateData['id'] = $businessId;

        $this->db->query($query, $updateData);

        return $this->getById($businessId);
    }

    /**
     * Update business status
     * 
     * @param int $businessId
     * @param string $status
     * @return bool
     * @throws Exception
     */
    public function updateStatus(int $businessId, string $status): bool
    {
        $validStatuses = [
            self::STATUS_PENDING, self::STATUS_ACTIVE, 
            self::STATUS_SUSPENDED, self::STATUS_REJECTED, self::STATUS_INACTIVE
        ];

        if (!in_array($status, $validStatuses)) {
            throw new Exception("Invalid status: {$status}");
        }

        $query = "UPDATE {$this->table} SET status = :status, updated_at = :updated_at WHERE id = :id";
        $this->db->query($query, [
            'status' => $status,
            'updated_at' => date('Y-m-d H:i:s'),
            'id' => $businessId
        ]);

        return true;
    }

    /**
     * Update accreditation level
     * 
     * @param int $businessId
     * @param string $accreditationLevel
     * @return bool
     * @throws Exception
     */
    public function updateAccreditation(int $businessId, string $accreditationLevel): bool
    {
        $validLevels = [
            self::ACCREDITATION_NONE, self::ACCREDITATION_BASIC,
            self::ACCREDITATION_PREMIUM, self::ACCREDITATION_VERIFIED
        ];

        if (!in_array($accreditationLevel, $validLevels)) {
            throw new Exception("Invalid accreditation level: {$accreditationLevel}");
        }

        $query = "UPDATE {$this->table} SET accreditation_level = :level, updated_at = :updated_at WHERE id = :id";
        $this->db->query($query, [
            'level' => $accreditationLevel,
            'updated_at' => date('Y-m-d H:i:s'),
            'id' => $businessId
        ]);

        return true;
    }

    /**
     * Search businesses
     * 
     * @param array $filters
     * @param int $page
     * @param int $perPage
     * @return array
     */
    public function search(array $filters = [], int $page = 1, int $perPage = 20): array
    {
        $whereConditions = ["status != 'deleted'"];
        $params = [];

        // Apply filters
        if (!empty($filters['business_name'])) {
            $whereConditions[] = "business_name LIKE :business_name";
            $params['business_name'] = '%' . $filters['business_name'] . '%';
        }

        if (!empty($filters['business_type'])) {
            $whereConditions[] = "business_type = :business_type";
            $params['business_type'] = $filters['business_type'];
        }

        if (!empty($filters['city'])) {
            $whereConditions[] = "city = :city";
            $params['city'] = $filters['city'];
        }

        if (!empty($filters['state'])) {
            $whereConditions[] = "state = :state";
            $params['state'] = $filters['state'];
        }

        if (!empty($filters['country'])) {
            $whereConditions[] = "country = :country";
            $params['country'] = $filters['country'];
        }

        if (!empty($filters['status'])) {
            $whereConditions[] = "status = :status";
            $params['status'] = $filters['status'];
        }

        if (!empty($filters['accreditation_level'])) {
            $whereConditions[] = "accreditation_level = :accreditation_level";
            $params['accreditation_level'] = $filters['accreditation_level'];
        }

        // Build query
        $whereClause = implode(' AND ', $whereConditions);
        $offset = ($page - 1) * $perPage;

        $query = "SELECT * FROM {$this->table} WHERE {$whereClause} ORDER BY created_at DESC LIMIT :limit OFFSET :offset";
        $params['limit'] = $perPage;
        $params['offset'] = $offset;

        $businesses = $this->db->fetchAll($query, $params);

        // Get total count for pagination
        $countQuery = "SELECT COUNT(*) as total FROM {$this->table} WHERE {$whereClause}";
        $total = $this->db->fetchColumn($countQuery, $params);

        return [
            'businesses' => $businesses,
            'pagination' => [
                'page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'total_pages' => ceil($total / $perPage)
            ]
        ];
    }

    /**
     * Get businesses by owner
     * 
     * @param int $ownerId
     * @return array
     */
    public function getByOwner(int $ownerId): array
    {
        $query = "SELECT * FROM {$this->table} WHERE owner_id = :owner_id AND status != 'deleted' ORDER BY created_at DESC";
        return $this->db->fetchAll($query, ['owner_id' => $ownerId]);
    }

    /**
     * Generate unique business ID
     * 
     * @return string
     */
    private function generateBusinessId(): string
    {
        $prefix = 'BUS';
        $timestamp = time();
        $random = mt_rand(1000, 9999);
        
        return $prefix . $timestamp . $random;
    }

    /**
     * Get business statistics
     * 
     * @param int $businessId
     * @return array
     */
    public function getStatistics(int $businessId): array
    {
        $business = $this->getById($businessId);
        if (!$business) {
            throw new Exception("Business not found");
        }

        // These would be calculated from other models (reviews, complaints, etc.)
        return [
            'total_reviews' => 0,
            'average_rating' => 0,
            'total_complaints' => 0,
            'resolved_complaints' => 0,
            'response_rate' => 0,
            'accreditation_score' => 0
        ];
    }

    /**
     * Verify business ownership
     * 
     * @param int $businessId
     * @param int $userId
     * @return bool
     */
    public function verifyOwnership(int $businessId, int $userId): bool
    {
        $business = $this->getById($businessId);
        return $business && $business['owner_id'] === $userId;
    }
}
