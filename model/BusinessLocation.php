<?php
/**
 * P.I.M.P - Business Locations and Branches Model
 * Handles multi-location business management
 */

namespace PIMP\Models;

use PIMP\Services\Database\MySQLDatabase;
use PDOException;
use Exception;

class BusinessLocation
{
    /**
     * @var MySQLDatabase Database instance
     */
    private $db;

    /**
     * @var string Locations table name
     */
    private $locationsTable = 'business_locations';

    /**
     * @var string Business hours table
     */
    private $hoursTable = 'business_hours';

    /**
     * Location status constants
     */
    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';
    const STATUS_PENDING = 'pending';
    const STATUS_CLOSED = 'closed';

    /**
     * Location types
     */
    const TYPE_HEADQUARTERS = 'headquarters';
    const TYPE_BRANCH = 'branch';
    const TYPE_FRANCHISE = 'franchise';
    const TYPE_PARTNER = 'partner';

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
     * Add business location
     * 
     * @param int $businessId
     * @param array $locationData
     * @return array
     * @throws Exception
     */
    public function addLocation(int $businessId, array $locationData): array
    {
        $requiredFields = ['name', 'address', 'city', 'state', 'country', 'location_type'];
        foreach ($requiredFields as $field) {
            if (empty($locationData[$field])) {
                throw new Exception("Required field missing: {$field}");
            }
        }

        // Validate location type
        $validTypes = [self::TYPE_HEADQUARTERS, self::TYPE_BRANCH, self::TYPE_FRANCHISE, self::TYPE_PARTNER];
        if (!in_array($locationData['location_type'], $validTypes)) {
            throw new Exception("Invalid location type: {$locationData['location_type']}");
        }

        // Check if this is the first location (should be headquarters)
        $existingLocations = $this->getBusinessLocations($businessId);
        if (empty($existingLocations) {
            $locationData['location_type'] = self::TYPE_HEADQUARTERS;
            $locationData['is_primary'] = true;
        }

        $locationData['business_id'] = $businessId;
        $locationData['status'] = self::STATUS_ACTIVE;
        $locationData['created_at'] = date('Y-m-d H:i:s');
        $locationData['updated_at'] = date('Y-m-d H:i:s');

        try {
            $this->db->beginTransaction();

            $columns = implode(', ', array_keys($locationData));
            $placeholders = ':' . implode(', :', array_keys($locationData));
            
            $query = "INSERT INTO {$this->locationsTable} ({$columns}) VALUES ({$placeholders})";
            $this->db->query($query, $locationData);

            $locationId = $this->db->lastInsertId();

            // Set business hours if provided
            if (!empty($locationData['business_hours'])) {
                $this->setBusinessHours($locationId, $locationData['business_hours']);
            }
            
            $this->db->commit();

            return $this->getLocationById($locationId);
            
        } catch (PDOException $e) {
            $this->db->rollback();
            throw new Exception("Failed to add business location: " . $e->getMessage());
        }
    }

    /**
     * Get location by ID
     * 
     * @param int $locationId
     * @return array|null
     */
    public function getLocationById(int $locationId): ?array
    {
        $query = "SELECT l.*, b.business_name, b.business_id as business_identifier 
                  FROM {$this->locationsTable} l
                  INNER JOIN business_profiles b ON l.business_id = b.id
                  WHERE l.id = :id";
        
        $location = $this->db->fetchOne($query, ['id' => $locationId]);
        
        if ($location) {
            $location['business_hours'] = $this->getBusinessHours($locationId);
            if ($location['metadata']) {
                $location['metadata'] = json_decode($location['metadata'], true);
            }
        }
        
        return $location ?: null;
    }

    /**
     * Get business locations
     * 
     * @param int $businessId
     * @param array $filters
     * @return array
     */
    public function getBusinessLocations(int $businessId, array $filters = []): array
    {
        $whereConditions = ["l.business_id = :business_id"];
        $params = ['business_id' => $businessId];

        if (!empty($filters['status'])) {
            $whereConditions[] = "l.status = :status";
            $params['status'] = $filters['status'];
        }

        if (!empty($filters['location_type'])) {
            $whereConditions[] = "l.location_type = :location_type";
            $params['location_type'] = $filters['location_type'];
        }

        if (!empty($filters['is_primary'])) {
            $whereConditions[] = "l.is_primary = :is_primary";
            $params['is_primary'] = $filters['is_primary'];
        }

        $whereClause = implode(' AND ', $whereConditions);
        
        $query = "SELECT l.* FROM {$this->locationsTable} l 
                  WHERE {$whereClause} 
                  ORDER BY l.is_primary DESC, l.created_at ASC";
        
        $locations = $this->db->fetchAll($query, $params);
        
        // Add business hours and decode metadata for each location
        foreach ($locations as &$location) {
            $location['business_hours'] = $this->getBusinessHours($location['id']);
            if ($location['metadata']) {
                $location['metadata'] = json_decode($location['metadata'], true);
            }
        }
        
        return $locations;
    }

    /**
     * Update location
     * 
     * @param int $locationId
     * @param array $updateData
     * @return array
     * @throws Exception
     */
    public function updateLocation(int $locationId, array $updateData): array
    {
        $location = $this->getLocationById($locationId);
        if (!$location) {
            throw new Exception("Location not found");
        }

        // Remove non-updatable fields
        unset($updateData['id'], $updateData['business_id'], $updateData['created_at']);
        
        $updateData['updated_at'] = date('Y-m-d H:i:s');

        // Update business hours if provided
        if (isset($updateData['business_hours'])) {
            $this->setBusinessHours($locationId, $updateData['business_hours']);
            unset($updateData['business_hours']);
        }

        $setParts = [];
        foreach (array_keys($updateData) as $field) {
            $setParts[] = "{$field} = :{$field}";
        }

        $query = "UPDATE {$this->locationsTable} SET " . implode(', ', $setParts) . " WHERE id = :id";
        $updateData['id'] = $locationId;

        $this->db->query($query, $updateData);

        return $this->getLocationById($locationId);
    }

    /**
     * Set location as primary
     * 
     * @param int $locationId
     * @return bool
     * @throws Exception
     */
    public function setPrimaryLocation(int $locationId): bool
    {
        $location = $this->getLocationById($locationId);
        if (!$location) {
            throw new Exception("Location not found");
        }

        try {
            $this->db->beginTransaction();

            // Remove primary status from all locations of this business
            $query = "UPDATE {$this->locationsTable} SET is_primary = FALSE, updated_at = :updated_at 
                      WHERE business_id = :business_id";
            $this->db->query($query, [
                'updated_at' => date('Y-m-d H:i:s'),
                'business_id' => $location['business_id']
            ]);

            // Set new primary location
            $query = "UPDATE {$this->locationsTable} SET is_primary = TRUE, updated_at = :updated_at 
                      WHERE id = :id";
            $this->db->query($query, [
                'updated_at' => date('Y-m-d H:i:s'),
                'id' => $locationId
            ]);

            $this->db->commit();

            return true;
            
        } catch (PDOException $e) {
            $this->db->rollback();
            throw new Exception("Failed to set primary location: " . $e->getMessage());
        }
    }

    /**
     * Update location status
     * 
     * @param int $locationId
     * @param string $status
     * @return bool
     * @throws Exception
     */
    public function updateLocationStatus(int $locationId, string $status): bool
    {
        $validStatuses = [self::STATUS_ACTIVE, self::STATUS_INACTIVE, self::STATUS_PENDING, self::STATUS_CLOSED];
        if (!in_array($status, $validStatuses)) {
            throw new Exception("Invalid status: {$status}");
        }

        $query = "UPDATE {$this->locationsTable} SET status = :status, updated_at = :updated_at WHERE id = :id";
        $this->db->query($query, [
            'status' => $status,
            'updated_at' => date('Y-m-d H:i:s'),
            'id' => $locationId
        ]);

        return true;
    }

    /**
     * Delete location
     * 
     * @param int $locationId
     * @return bool
     * @throws Exception
     */
    public function deleteLocation(int $locationId): bool
    {
        $location = $this->getLocationById($locationId);
        if (!$location) {
            throw new Exception("Location not found");
        }

        // Cannot delete primary location if it's the only one
        $allLocations = $this->getBusinessLocations($location['business_id']);
        if (count($allLocations) === 1 && $location['is_primary']) {
            throw new Exception("Cannot delete the only location of a business");
        }

        $query = "DELETE FROM {$this->locationsTable} WHERE id = :id";
        $this->db->query($query, ['id' => $locationId]);

        // Also delete associated business hours
        $this->deleteBusinessHours($locationId);

        return true;
    }

    /**
     * Set business hours for location
     * 
     * @param int $locationId
     * @param array $hoursData
     * @return bool
     */
    public function setBusinessHours(int $locationId, array $hoursData): bool
    {
        // First delete existing hours
        $this->deleteBusinessHours($locationId);

        foreach ($hoursData as $day => $hours) {
            if (!empty($hours['open_time']) && !empty($hours['close_time'])) {
                $hourData = [
                    'location_id' => $locationId,
                    'day_of_week' => $day,
                    'open_time' => $hours['open_time'],
                    'close_time' => $hours['close_time'],
                    'is_closed' => false,
                    'created_at' => date('Y-m-d H:i:s')
                ];

                $columns = implode(', ', array_keys($hourData));
                $placeholders = ':' . implode(', :', array_keys($hourData));
                
                $query = "INSERT INTO {$this->hoursTable} ({$columns}) VALUES ({$placeholders})";
                $this->db->query($query, $hourData);
            } else {
                // Mark as closed
                $hourData = [
                    'location_id' => $locationId,
                    'day_of_week' => $day,
                    'open_time' => null,
                    'close_time' => null,
                    'is_closed' => true,
                    'created_at' => date('Y-m-d H:i:s')
                ];

                $columns = implode(', ', array_keys($hourData));
                $placeholders = ':' . implode(', :', array_keys($hourData));
                
                $query = "INSERT INTO {$this->hoursTable} ({$columns}) VALUES ({$placeholders})";
                $this->db->query($query, $hourData);
            }
        }

        return true;
    }

    /**
     * Get business hours for location
     * 
     * @param int $locationId
     * @return array
     */
    public function getBusinessHours(int $locationId): array
    {
        $query = "SELECT day_of_week, open_time, close_time, is_closed 
                  FROM {$this->hoursTable} 
                  WHERE location_id = :location_id 
                  ORDER BY FIELD(day_of_week, 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday')";
        
        $hours = $this->db->fetchAll($query, ['location_id' => $locationId]);
        
        $formattedHours = [];
        foreach ($hours as $hour) {
            $formattedHours[$hour['day_of_week']] = [
                'open_time' => $hour['open_time'],
                'close_time' => $hour['close_time'],
                'is_closed' => (bool)$hour['is_closed']
            ];
        }
        
        return $formattedHours;
    }

    /**
     * Delete business hours for location
     * 
     * @param int $locationId
     * @return bool
     */
    private function deleteBusinessHours(int $locationId): bool
    {
        $query = "DELETE FROM {$this->hoursTable} WHERE location_id = :location_id";
        $this->db->query($query, ['location_id' => $locationId]);
        return true;
    }

    /**
     * Search locations by proximity
     * 
     * @param float $latitude
     * @param float $longitude
     * @param float $radiusKm
     * @param array $filters
     * @return array
     */
    public function searchLocationsByProximity(float $latitude, float $longitude, float $radiusKm = 10, array $filters = []): array
    {
        $whereConditions = ["l.status = 'active'"];
        $params = [
            'latitude' => $latitude,
            'longitude' => $longitude,
            'radius' => $radiusKm
        ];

        if (!empty($filters['business_id'])) {
            $whereConditions[] = "l.business_id = :business_id";
            $params['business_id'] = $filters['business_id'];
        }

        if (!empty($filters['city'])) {
            $whereConditions[] = "l.city = :city";
            $params['city'] = $filters['city'];
        }

        if (!empty($filters['state'])) {
            $whereConditions[] = "l.state = :state";
            $params['state'] = $filters['state'];
        }

        $whereClause = implode(' AND ', $whereConditions);

        // Haversine formula for distance calculation
        $query = "SELECT l.*, b.business_name, b.accreditation_level, b.rating,
                  (6371 * ACOS(COS(RADIANS(:latitude)) * COS(RADIANS(l.latitude)) * 
                  COS(RADIANS(l.longitude) - RADIANS(:longitude)) + 
                  SIN(RADIANS(:latitude)) * SIN(RADIANS(l.latitude)))) AS distance
                  FROM {$this->locationsTable} l
                  INNER JOIN business_profiles b ON l.business_id = b.id
                  WHERE {$whereClause}
                  HAVING distance <= :radius
                  ORDER BY distance ASC, b.rating DESC";

        $locations = $this->db->fetchAll($query, $params);
        
        // Add business hours to each location
        foreach ($locations as &$location) {
            $location['business_hours'] = $this->getBusinessHours($location['id']);
            if ($location['metadata']) {
                $location['metadata'] = json_decode($location['metadata'], true);
            }
        }
        
        return $locations;
    }

    /**
     * Get location statistics
     * 
     * @param int $businessId
     * @return array
     */
    public function getLocationStatistics(int $businessId): array
    {
        $totalLocations = $this->db->fetchColumn(
            "SELECT COUNT(*) FROM {$this->locationsTable} WHERE business_id = :business_id",
            ['business_id' => $businessId]
        );

        $activeLocations = $this->db->fetchColumn(
            "SELECT COUNT(*) FROM {$this->locationsTable} WHERE business_id = :business_id AND status = :status",
            [
                'business_id' => $businessId,
                'status' => self::STATUS_ACTIVE
            ]
        );

        $cities = $this->db->fetchAll(
            "SELECT DISTINCT city, state, country 
             FROM {$this->locationsTable} 
             WHERE business_id = :business_id AND status = :status",
            [
                'business_id' => $businessId,
                'status' => self::STATUS_ACTIVE
            ]
        );

        return [
            'total_locations' => (int)$totalLocations,
            'active_locations' => (int)$activeLocations,
            'inactive_locations' => (int)$totalLocations - $activeLocations,
            'cities_covered' => count($cities),
            'locations_by_type' => $this->getLocationsByType($businessId)
        ];
    }

    /**
     * Get locations count by type
     * 
     * @param int $businessId
     * @return array
     */
    private function getLocationsByType(int $businessId): array
    {
        $query = "SELECT location_type, COUNT(*) as count 
                  FROM {$this->locationsTable} 
                  WHERE business_id = :business_id 
                  GROUP BY location_type";
        
        $results = $this->db->fetchAll($query, ['business_id' => $businessId]);
        
        $counts = [];
        foreach ($results as $result) {
            $counts[$result['location_type']] = (int)$result['count'];
        }
        
        return $counts;
    }

    /**
     * Check if location is open now
     * 
     * @param int $locationId
     * @return bool
     */
    public function isLocationOpen(int $locationId): bool
    {
        $currentDay = strtolower(date('l')); // Monday, Tuesday, etc.
        $currentTime = date('H:i:s');

        $query = "SELECT open_time, close_time, is_closed 
                  FROM {$this->hoursTable} 
                  WHERE location_id = :location_id AND day_of_week = :day_of_week";
        
        $hours = $this->db->fetchOne($query, [
            'location_id' => $locationId,
            'day_of_week' => $currentDay
        ]);

        if (!$hours || $hours['is_closed']) {
            return false;
        }

        return $currentTime >= $hours['open_time'] && $currentTime <= $hours['close_time'];
    }
}
