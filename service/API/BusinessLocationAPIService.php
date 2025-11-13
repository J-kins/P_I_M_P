<?php
/**
 * P.I.M.P - Business Locations API Service
 * Handles business locations and branches API operations
 */

namespace PIMP\Services\API;

use PIMP\Models\BusinessLocation;
use PIMP\Services\Database\MySQLDatabase;
use Exception;

class BusinessLocationAPIService
{
    /**
     * @var BusinessLocation
     */
    private $locationModel;

    /**
     * Constructor
     * 
     * @param MySQLDatabase $db
     */
    public function __construct(MySQLDatabase $db)
    {
        $this->locationModel = new BusinessLocation($db);
    }

    /**
     * Add business location
     * 
     * @param int $businessId
     * @param array $locationData
     * @return array
     */
    public function addLocation(int $businessId, array $locationData): array
    {
        try {
            $location = $this->locationModel->addLocation($businessId, $locationData);
            
            return [
                'success' => true,
                'message' => 'Business location added successfully',
                'data' => $location
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'data' => null
            ];
        }
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
        try {
            $locations = $this->locationModel->getBusinessLocations($businessId, $filters);

            return [
                'success' => true,
                'message' => 'Business locations retrieved successfully',
                'data' => $locations
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Update location
     * 
     * @param int $locationId
     * @param array $updateData
     * @return array
     */
    public function updateLocation(int $locationId, array $updateData): array
    {
        try {
            $location = $this->locationModel->updateLocation($locationId, $updateData);

            return [
                'success' => true,
                'message' => 'Location updated successfully',
                'data' => $location
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Set primary location
     * 
     * @param int $locationId
     * @return array
     */
    public function setPrimaryLocation(int $locationId): array
    {
        try {
            $this->locationModel->setPrimaryLocation($locationId);

            return [
                'success' => true,
                'message' => 'Primary location set successfully',
                'data' => null
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Update location status
     * 
     * @param int $locationId
     * @param string $status
     * @return array
     */
    public function updateLocationStatus(int $locationId, string $status): array
    {
        try {
            $this->locationModel->updateLocationStatus($locationId, $status);

            return [
                'success' => true,
                'message' => 'Location status updated successfully',
                'data' => null
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Delete location
     * 
     * @param int $locationId
     * @return array
     */
    public function deleteLocation(int $locationId): array
    {
        try {
            $this->locationModel->deleteLocation($locationId);

            return [
                'success' => true,
                'message' => 'Location deleted successfully',
                'data' => null
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'data' => null
            ];
        }
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
        try {
            $locations = $this->locationModel->searchLocationsByProximity($latitude, $longitude, $radiusKm, $filters);

            return [
                'success' => true,
                'message' => 'Locations search completed',
                'data' => $locations
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Get location statistics
     * 
     * @param int $businessId
     * @return array
     */
    public function getLocationStatistics(int $businessId): array
    {
        try {
            $statistics = $this->locationModel->getLocationStatistics($businessId);

            return [
                'success' => true,
                'message' => 'Location statistics retrieved successfully',
                'data' => $statistics
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Check if location is open
     * 
     * @param int $locationId
     * @return array
     */
    public function isLocationOpen(int $locationId): array
    {
        try {
            $isOpen = $this->locationModel->isLocationOpen($locationId);

            return [
                'success' => true,
                'message' => 'Location status retrieved',
                'data' => [
                    'is_open' => $isOpen,
                    'timestamp' => date('Y-m-d H:i:s')
                ]
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Get business hours for location
     * 
     * @param int $locationId
     * @return array
     */
    public function getBusinessHours(int $locationId): array
    {
        try {
            $hours = $this->locationModel->getBusinessHours($locationId);

            return [
                'success' => true,
                'message' => 'Business hours retrieved successfully',
                'data' => $hours
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'data' => null
            ];
        }
    }
}
