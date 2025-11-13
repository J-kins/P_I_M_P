<?php
/**
 * P.I.M.P - Business Contact API Service
 * Handles business contact information and hours API operations
 */

namespace PIMP\Services\API;

use PIMP\Models\BusinessContact;
use PIMP\Services\Database\MySQLDatabase;
use Exception;

class BusinessContactAPIService
{
    /**
     * @var BusinessContact
     */
    private $contactModel;

    /**
     * Constructor
     * 
     * @param MySQLDatabase $db
     */
    public function __construct(MySQLDatabase $db)
    {
        $this->contactModel = new BusinessContact($db);
    }

    /**
     * Update business contact information
     * 
     * @param int $businessId
     * @param array $contactData
     * @return array
     */
    public function updateContactInfo(int $businessId, array $contactData): array
    {
        try {
            $contactInfo = $this->contactModel->updateContactInfo($businessId, $contactData);
            
            return [
                'success' => true,
                'message' => 'Contact information updated successfully',
                'data' => $contactInfo
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
     * Get business contact information
     * 
     * @param int $businessId
     * @return array
     */
    public function getContactInfo(int $businessId): array
    {
        try {
            $contactInfo = $this->contactModel->getContactInfo($businessId);
            
            if (!$contactInfo) {
                return [
                    'success' => true,
                    'message' => 'No contact information found',
                    'data' => null
                ];
            }

            return [
                'success' => true,
                'message' => 'Contact information retrieved successfully',
                'data' => $contactInfo
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
     * Add contact method
     * 
     * @param int $businessId
     * @param string $methodType
     * @param string $value
     * @param array $additionalData
     * @return array
     */
    public function addContactMethod(int $businessId, string $methodType, string $value, array $additionalData = []): array
    {
        try {
            $contactMethod = $this->contactModel->addContactMethod($businessId, $methodType, $value, $additionalData);

            return [
                'success' => true,
                'message' => 'Contact method added successfully',
                'data' => $contactMethod
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
     * Get contact methods
     * 
     * @param int $businessId
     * @param array $filters
     * @return array
     */
    public function getContactMethods(int $businessId, array $filters = []): array
    {
        try {
            $methods = $this->contactModel->getContactMethods($businessId, $filters);

            return [
                'success' => true,
                'message' => 'Contact methods retrieved successfully',
                'data' => $methods
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
     * Update contact method
     * 
     * @param int $methodId
     * @param array $updateData
     * @return array
     */
    public function updateContactMethod(int $methodId, array $updateData): array
    {
        try {
            $method = $this->contactModel->updateContactMethod($methodId, $updateData);

            return [
                'success' => true,
                'message' => 'Contact method updated successfully',
                'data' => $method
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
     * Delete contact method
     * 
     * @param int $methodId
     * @return array
     */
    public function deleteContactMethod(int $methodId): array
    {
        try {
            $this->contactModel->deleteContactMethod($methodId);

            return [
                'success' => true,
                'message' => 'Contact method deleted successfully',
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
     * Add special hours
     * 
     * @param int $businessId
     * @param array $specialHoursData
     * @return array
     */
    public function addSpecialHours(int $businessId, array $specialHoursData): array
    {
        try {
            $specialHours = $this->contactModel->addSpecialHours($businessId, $specialHoursData);

            return [
                'success' => true,
                'message' => 'Special hours added successfully',
                'data' => $specialHours
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
     * Get special hours
     * 
     * @param int $businessId
     * @param array $filters
     * @return array
     */
    public function getSpecialHours(int $businessId, array $filters = []): array
    {
        try {
            $specialHours = $this->contactModel->getSpecialHoursForBusiness($businessId, $filters);

            return [
                'success' => true,
                'message' => 'Special hours retrieved successfully',
                'data' => $specialHours
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
     * Get hours for specific date
     * 
     * @param int $businessId
     * @param string $date
     * @return array
     */
    public function getHoursForDate(int $businessId, string $date): array
    {
        try {
            $hours = $this->contactModel->getHoursForDate($businessId, $date);

            return [
                'success' => true,
                'message' => 'Hours for date retrieved successfully',
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

    /**
     * Get contact statistics
     * 
     * @param int $businessId
     * @return array
     */
    public function getContactStatistics(int $businessId): array
    {
        try {
            $statistics = $this->contactModel->getContactStatistics($businessId);

            return [
                'success' => true,
                'message' => 'Contact statistics retrieved successfully',
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
