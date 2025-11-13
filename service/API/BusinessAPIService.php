<?php
/**
 * P.I.M.P - Business API Service
 * Handles business-related API operations
 */

namespace PIMP\Services\API;

use PIMP\Models\BusinessProfile;
use PIMP\Services\Database\MySQLDatabase;
use PIMP\Core\Config;
use Exception;

class BusinessAPIService
{
    /**
     * @var BusinessProfile
     */
    private $businessModel;

    /**
     * @var MySQLDatabase
     */
    private $db;

    /**
     * Constructor
     * 
     * @param MySQLDatabase $db
     */
    public function __construct(MySQLDatabase $db)
    {
        $this->db = $db;
        $this->businessModel = new BusinessProfile($db);
    }

    /**
     * Register new business
     * 
     * @param array $data
     * @return array
     */
    public function registerBusiness(array $data): array
    {
        try {
            $business = $this->businessModel->create($data);
            
            return [
                'success' => true,
                'message' => 'Business registered successfully',
                'data' => $business
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
     * Get business profile
     * 
     * @param string $businessId
     * @return array
     */
    public function getBusinessProfile(string $businessId): array
    {
        try {
            $business = $this->businessModel->getByBusinessId($businessId);
            
            if (!$business) {
                throw new Exception("Business not found");
            }

            return [
                'success' => true,
                'message' => 'Business profile retrieved successfully',
                'data' => $business
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
     * Update business profile
     * 
     * @param string $businessId
     * @param array $updateData
     * @return array
     */
    public function updateBusinessProfile(string $businessId, array $updateData): array
    {
        try {
            $business = $this->businessModel->getByBusinessId($businessId);
            if (!$business) {
                throw new Exception("Business not found");
            }

            $updatedBusiness = $this->businessModel->update($business['id'], $updateData);

            return [
                'success' => true,
                'message' => 'Business profile updated successfully',
                'data' => $updatedBusiness
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
     * Search businesses
     * 
     * @param array $filters
     * @param int $page
     * @param int $perPage
     * @return array
     */
    public function searchBusinesses(array $filters = [], int $page = 1, int $perPage = 20): array
    {
        try {
            $result = $this->businessModel->search($filters, $page, $perPage);

            return [
                'success' => true,
                'message' => 'Businesses retrieved successfully',
                'data' => $result
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
     * Update business status (admin function)
     * 
     * @param string $businessId
     * @param string $status
     * @return array
     */
    public function updateBusinessStatus(string $businessId, string $status): array
    {
        try {
            $business = $this->businessModel->getByBusinessId($businessId);
            if (!$business) {
                throw new Exception("Business not found");
            }

            $this->businessModel->updateStatus($business['id'], $status);

            return [
                'success' => true,
                'message' => 'Business status updated successfully',
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
     * Update accreditation level
     * 
     * @param string $businessId
     * @param string $accreditationLevel
     * @return array
     */
    public function updateAccreditation(string $businessId, string $accreditationLevel): array
    {
        try {
            $business = $this->businessModel->getByBusinessId($businessId);
            if (!$business) {
                throw new Exception("Business not found");
            }

            $this->businessModel->updateAccreditation($business['id'], $accreditationLevel);

            return [
                'success' => true,
                'message' => 'Accreditation level updated successfully',
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
     * Get business statistics
     * 
     * @param string $businessId
     * @return array
     */
    public function getBusinessStatistics(string $businessId): array
    {
        try {
            $business = $this->businessModel->getByBusinessId($businessId);
            if (!$business) {
                throw new Exception("Business not found");
            }

            $statistics = $this->businessModel->getStatistics($business['id']);

            return [
                'success' => true,
                'message' => 'Business statistics retrieved successfully',
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
}
