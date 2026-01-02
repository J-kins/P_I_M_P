<?php
/**
 * P.I.M.P - Business Hours and Contact Information Model
 * Handles business hours management and contact information
 */

namespace PIMP\Models;

use PIMP\Services\Database\MySQLDatabase;
use PDOException;
use Exception;

class BusinessContact
{
    /**
     * @var MySQLDatabase Database instance
     */
    private $db;

    /**
     * @var string Contact info table name
     */
    private $contactTable = 'business_contact_info';

    /**
     * @var string Special hours table
     */
    private $specialHoursTable = 'business_special_hours';

    /**
     * @var string Contact methods table
     */
    private $contactMethodsTable = 'business_contact_methods';

    /**
     * Contact method types
     */
    const METHOD_PHONE = 'phone';
    const METHOD_EMAIL = 'email';
    const METHOD_WEBSITE = 'website';
    const METHOD_SOCIAL_MEDIA = 'social_media';
    const METHOD_LIVE_CHAT = 'live_chat';
    const METHOD_OTHER = 'other';

    /**
     * Social media platforms
     */
    const SOCIAL_FACEBOOK = 'facebook';
    const SOCIAL_TWITTER = 'twitter';
    const SOCIAL_INSTAGRAM = 'instagram';
    const SOCIAL_LINKEDIN = 'linkedin';
    const SOCIAL_YOUTUBE = 'youtube';
    const SOCIAL_TIKTOK = 'tiktok';

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
     * Update business contact information
     * 
     * @param int $businessId
     * @param array $contactData
     * @return array
     * @throws Exception
     */
    public function updateContactInfo(int $businessId, array $contactData): array
    {
        // Check if contact info already exists
        $existing = $this->getContactInfo($businessId);
        
        if ($existing) {
            // Update existing record
            $contactData['updated_at'] = date('Y-m-d H:i:s');
            
            $setParts = [];
            foreach (array_keys($contactData) as $field) {
                $setParts[] = "{$field} = :{$field}";
            }

            $query = "UPDATE {$this->contactTable} SET " . implode(', ', $setParts) . " WHERE business_id = :business_id";
            $contactData['business_id'] = $businessId;
            $this->db->query($query, $contactData);
        } else {
            // Create new record
            $contactData['business_id'] = $businessId;
            $contactData['created_at'] = date('Y-m-d H:i:s');
            $contactData['updated_at'] = date('Y-m-d H:i:s');

            $columns = implode(', ', array_keys($contactData));
            $placeholders = ':' . implode(', :', array_keys($contactData));
            
            $query = "INSERT INTO {$this->contactTable} ({$columns}) VALUES ({$placeholders})";
            $this->db->query($query, $contactData);
        }

        return $this->getContactInfo($businessId);
    }

    /**
     * Get business contact information
     * 
     * @param int $businessId
     * @return array|null
     */
    public function getContactInfo(int $businessId): ?array
    {
        $query = "SELECT c.*, b.business_name, b.business_id as business_identifier 
                  FROM {$this->contactTable} c
                  INNER JOIN business_profiles b ON c.business_id = b.id
                  WHERE c.business_id = :business_id";
        
        $contactInfo = $this->db->fetchOne($query, ['business_id' => $businessId]);
        
        if ($contactInfo) {
            // Get contact methods
            $contactInfo['contact_methods'] = $this->getContactMethods($businessId);
            
            // Decode JSON fields
            if ($contactInfo['social_media']) {
                $contactInfo['social_media'] = json_decode($contactInfo['social_media'], true);
            }
            if ($contactInfo['metadata']) {
                $contactInfo['metadata'] = json_decode($contactInfo['metadata'], true);
            }
        }
        
        return $contactInfo ?: null;
    }

    /**
     * Add contact method
     * 
     * @param int $businessId
     * @param string $methodType
     * @param string $value
     * @param array $additionalData
     * @return array
     * @throws Exception
     */
    public function addContactMethod(int $businessId, string $methodType, string $value, array $additionalData = []): array
    {
        $validMethods = [
            self::METHOD_PHONE, self::METHOD_EMAIL, self::METHOD_WEBSITE,
            self::METHOD_SOCIAL_MEDIA, self::METHOD_LIVE_CHAT, self::METHOD_OTHER
        ];

        if (!in_array($methodType, $validMethods)) {
            throw new Exception("Invalid contact method type: {$methodType}");
        }

        // Validate based on method type
        switch ($methodType) {
            case self::METHOD_EMAIL:
                if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    throw new Exception("Invalid email address");
                }
                break;
            case self::METHOD_WEBSITE:
                if (!preg_match('/^(https?:\/\/)?([\da-z\.-]+)\.([a-z\.]{2,6})([\/\w \.-]*)*\/?$/', $value)) {
                    throw new Exception("Invalid website URL");
                }
                break;
            case self::METHOD_PHONE:
                // Basic phone validation
                if (!preg_match('/^[\+]?[1-9][\d]{0,15}$/', preg_replace('/\D/', '', $value))) {
                    throw new Exception("Invalid phone number");
                }
                break;
        }

        $contactMethod = [
            'business_id' => $businessId,
            'method_type' => $methodType,
            'value' => $value,
            'is_primary' => $additionalData['is_primary'] ?? false,
            'display_order' => $additionalData['display_order'] ?? 0,
            'description' => $additionalData['description'] ?? '',
            'metadata' => !empty($additionalData['metadata']) ? json_encode($additionalData['metadata']) : null,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        // If this is set as primary, remove primary status from other methods of same type
        if ($contactMethod['is_primary']) {
            $this->removePrimaryStatus($businessId, $methodType);
        }

        $columns = implode(', ', array_keys($contactMethod));
        $placeholders = ':' . implode(', :', array_keys($contactMethod));
        
        $query = "INSERT INTO {$this->contactMethodsTable} ({$columns}) VALUES ({$placeholders})";
        $this->db->query($query, $contactMethod);

        $methodId = $this->db->lastInsertId();
        return $this->getContactMethod($methodId);
    }

    /**
     * Get contact method by ID
     * 
     * @param int $methodId
     * @return array|null
     */
    public function getContactMethod(int $methodId): ?array
    {
        $query = "SELECT * FROM {$this->contactMethodsTable} WHERE id = :id";
        $method = $this->db->fetchOne($query, ['id' => $methodId]);
        
        if ($method && $method['metadata']) {
            $method['metadata'] = json_decode($method['metadata'], true);
        }
        
        return $method ?: null;
    }

    /**
     * Get all contact methods for business
     * 
     * @param int $businessId
     * @param array $filters
     * @return array
     */
    public function getContactMethods(int $businessId, array $filters = []): array
    {
        $whereConditions = ["business_id = :business_id"];
        $params = ['business_id' => $businessId];

        if (!empty($filters['method_type'])) {
            $whereConditions[] = "method_type = :method_type";
            $params['method_type'] = $filters['method_type'];
        }

        if (!empty($filters['is_primary'])) {
            $whereConditions[] = "is_primary = :is_primary";
            $params['is_primary'] = $filters['is_primary'];
        }

        $whereClause = implode(' AND ', $whereConditions);
        $query = "SELECT * FROM {$this->contactMethodsTable} WHERE {$whereClause} ORDER BY display_order ASC, created_at ASC";
        
        $methods = $this->db->fetchAll($query, $params);
        
        foreach ($methods as &$method) {
            if ($method['metadata']) {
                $method['metadata'] = json_decode($method['metadata'], true);
            }
        }
        
        return $methods;
    }

    /**
     * Update contact method
     * 
     * @param int $methodId
     * @param array $updateData
     * @return array
     * @throws Exception
     */
    public function updateContactMethod(int $methodId, array $updateData): array
    {
        $method = $this->getContactMethod($methodId);
        if (!$method) {
            throw new Exception("Contact method not found");
        }

        // If setting as primary, remove primary status from other methods of same type
        if (isset($updateData['is_primary']) && $updateData['is_primary']) {
            $this->removePrimaryStatus($method['business_id'], $method['method_type']);
        }

        $updateData['updated_at'] = date('Y-m-d H:i:s');

        $setParts = [];
        foreach (array_keys($updateData) as $field) {
            $setParts[] = "{$field} = :{$field}";
        }

        $query = "UPDATE {$this->contactMethodsTable} SET " . implode(', ', $setParts) . " WHERE id = :id";
        $updateData['id'] = $methodId;

        $this->db->query($query, $updateData);

        return $this->getContactMethod($methodId);
    }

    /**
     * Delete contact method
     * 
     * @param int $methodId
     * @return bool
     * @throws Exception
     */
    public function deleteContactMethod(int $methodId): bool
    {
        $method = $this->getContactMethod($methodId);
        if (!$method) {
            throw new Exception("Contact method not found");
        }

        $query = "DELETE FROM {$this->contactMethodsTable} WHERE id = :id";
        $this->db->query($query, ['id' => $methodId]);

        return true;
    }

    /**
     * Remove primary status from other contact methods of same type
     * 
     * @param int $businessId
     * @param string $methodType
     * @return bool
     */
    private function removePrimaryStatus(int $businessId, string $methodType): bool
    {
        $query = "UPDATE {$this->contactMethodsTable} SET is_primary = FALSE, updated_at = :updated_at 
                  WHERE business_id = :business_id AND method_type = :method_type";
        
        $this->db->query($query, [
            'updated_at' => date('Y-m-d H:i:s'),
            'business_id' => $businessId,
            'method_type' => $methodType
        ]);

        return true;
    }

    /**
     * Add special hours (holidays, events, etc.)
     * 
     * @param int $businessId
     * @param array $specialHoursData
     * @return array
     * @throws Exception
     */
    public function addSpecialHours(int $businessId, array $specialHoursData): array
    {
        $requiredFields = ['date', 'description'];
        foreach ($requiredFields as $field) {
            if (empty($specialHoursData[$field])) {
                throw new Exception("Required field missing: {$field}");
            }
        }

        $specialHoursData['business_id'] = $businessId;
        $specialHoursData['created_at'] = date('Y-m-d H:i:s');
        $specialHoursData['updated_at'] = date('Y-m-d H:i:s');

        $columns = implode(', ', array_keys($specialHoursData));
        $placeholders = ':' . implode(', :', array_keys($specialHoursData));
        
        $query = "INSERT INTO {$this->specialHoursTable} ({$columns}) VALUES ({$placeholders})";
        $this->db->query($query, $specialHoursData);

        $specialHoursId = $this->db->lastInsertId();
        return $this->getSpecialHours($specialHoursId);
    }

    /**
     * Get special hours by ID
     * 
     * @param int $specialHoursId
     * @return array|null
     */
    public function getSpecialHours(int $specialHoursId): ?array
    {
        $query = "SELECT * FROM {$this->specialHoursTable} WHERE id = :id";
        return $this->db->fetchOne($query, ['id' => $specialHoursId]) ?: null;
    }

    /**
     * Get special hours for business
     * 
     * @param int $businessId
     * @param array $filters
     * @return array
     */
    public function getSpecialHoursForBusiness(int $businessId, array $filters = []): array
    {
        $whereConditions = ["business_id = :business_id"];
        $params = ['business_id' => $businessId];

        if (!empty($filters['start_date'])) {
            $whereConditions[] = "date >= :start_date";
            $params['start_date'] = $filters['start_date'];
        }

        if (!empty($filters['end_date'])) {
            $whereConditions[] = "date <= :end_date";
            $params['end_date'] = $filters['end_date'];
        }

        if (!empty($filters['is_recurring'])) {
            $whereConditions[] = "is_recurring = :is_recurring";
            $params['is_recurring'] = $filters['is_recurring'];
        }

        $whereClause = implode(' AND ', $whereConditions);
        $query = "SELECT * FROM {$this->specialHoursTable} WHERE {$whereClause} ORDER BY date ASC";
        
        return $this->db->fetchAll($query, $params);
    }

    /**
     * Update special hours
     * 
     * @param int $specialHoursId
     * @param array $updateData
     * @return array
     * @throws Exception
     */
    public function updateSpecialHours(int $specialHoursId, array $updateData): array
    {
        $specialHours = $this->getSpecialHours($specialHoursId);
        if (!$specialHours) {
            throw new Exception("Special hours not found");
        }

        $updateData['updated_at'] = date('Y-m-d H:i:s');

        $setParts = [];
        foreach (array_keys($updateData) as $field) {
            $setParts[] = "{$field} = :{$field}";
        }

        $query = "UPDATE {$this->specialHoursTable} SET " . implode(', ', $setParts) . " WHERE id = :id";
        $updateData['id'] = $specialHoursId;

        $this->db->query($query, $updateData);

        return $this->getSpecialHours($specialHoursId);
    }

    /**
     * Delete special hours
     * 
     * @param int $specialHoursId
     * @return bool
     * @throws Exception
     */
    public function deleteSpecialHours(int $specialHoursId): bool
    {
        $specialHours = $this->getSpecialHours($specialHoursId);
        if (!$specialHours) {
            throw new Exception("Special hours not found");
        }

        $query = "DELETE FROM {$this->specialHoursTable} WHERE id = :id";
        $this->db->query($query, ['id' => $specialHoursId]);

        return true;
    }

    /**
     * Get business hours for a specific date
     * 
     * @param int $businessId
     * @param string $date
     * @return array
     */
    public function getHoursForDate(int $businessId, string $date): array
    {
        // First check for special hours
        $specialHours = $this->db->fetchOne(
            "SELECT * FROM {$this->specialHoursTable} 
             WHERE business_id = :business_id AND date = :date",
            ['business_id' => $businessId, 'date' => $date]
        );

        if ($specialHours) {
            return [
                'type' => 'special',
                'date' => $date,
                'open_time' => $specialHours['open_time'],
                'close_time' => $specialHours['close_time'],
                'is_closed' => (bool)$specialHours['is_closed'],
                'description' => $specialHours['description'],
                'notes' => $specialHours['notes']
            ];
        }

        // Get regular hours for the day of week
        $dayOfWeek = strtolower(date('l', strtotime($date)));
        
        // This would integrate with the BusinessLocation model's hours
        // For now, return basic structure
        return [
            'type' => 'regular',
            'date' => $date,
            'day_of_week' => $dayOfWeek,
            'open_time' => '09:00:00',
            'close_time' => '17:00:00',
            'is_closed' => false
        ];
    }

    /**
     * Get contact statistics
     * 
     * @param int $businessId
     * @return array
     */
    public function getContactStatistics(int $businessId): array
    {
        $totalContactMethods = $this->db->fetchColumn(
            "SELECT COUNT(*) FROM {$this->contactMethodsTable} WHERE business_id = :business_id",
            ['business_id' => $businessId]
        );

        $methodsByType = $this->db->fetchAll(
            "SELECT method_type, COUNT(*) as count 
             FROM {$this->contactMethodsTable} 
             WHERE business_id = :business_id 
             GROUP BY method_type",
            ['business_id' => $businessId]
        );

        $upcomingSpecialHours = $this->db->fetchColumn(
            "SELECT COUNT(*) FROM {$this->specialHoursTable} 
             WHERE business_id = :business_id AND date >= :today",
            [
                'business_id' => $businessId,
                'today' => date('Y-m-d')
            ]
        );

        $methodCounts = [];
        foreach ($methodsByType as $method) {
            $methodCounts[$method['method_type']] = (int)$method['count'];
        }

        return [
            'total_contact_methods' => (int)$totalContactMethods,
            'methods_by_type' => $methodCounts,
            'upcoming_special_hours' => (int)$upcomingSpecialHours,
            'has_primary_contact' => $this->hasPrimaryContact($businessId)
        ];
    }

    /**
     * Check if business has primary contact methods
     * 
     * @param int $businessId
     * @return bool
     */
    private function hasPrimaryContact(int $businessId): bool
    {
        $primaryMethods = $this->db->fetchColumn(
            "SELECT COUNT(*) FROM {$this->contactMethodsTable} 
             WHERE business_id = :business_id AND is_primary = TRUE",
            ['business_id' => $businessId]
        );

        return $primaryMethods > 0;
    }
}
