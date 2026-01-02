<?php
/**
 * P.I.M.P - Accreditation API Service
 * Handles business accreditation API operations
 */

namespace PIMP\Services\API;

use PIMP\Models\Accreditation;
use PIMP\Services\Database\MySQLDatabase;
use Exception;

class AccreditationAPIService
{
    /**
     * @var Accreditation
     */
    private $accreditationModel;

    /**
     * Constructor
     * 
     * @param MySQLDatabase $db
     */
    public function __construct(MySQLDatabase $db)
    {
        $this->accreditationModel = new Accreditation($db);
    }

    /**
     * Apply for accreditation
     * 
     * @param int $businessId
     * @param string $accreditationLevel
     * @param array $documents
     * @return array
     */
    public function applyForAccreditation(int $businessId, string $accreditationLevel, array $documents = []): array
    {
        try {
            $accreditation = $this->accreditationModel->applyForAccreditation($businessId, $accreditationLevel, $documents);
            
            return [
                'success' => true,
                'message' => 'Accreditation application submitted successfully',
                'data' => $accreditation
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
     * Get business accreditation
     * 
     * @param int $businessId
     * @return array
     */
    public function getBusinessAccreditation(int $businessId): array
    {
        try {
            $accreditation = $this->accreditationModel->getBusinessAccreditation($businessId);
            
            return [
                'success' => true,
                'message' => 'Business accreditation retrieved successfully',
                'data' => $accreditation
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
     * Update accreditation status
     * 
     * @param int $accreditationId
     * @param string $status
     * @param string $reviewNotes
     * @param int $reviewedBy
     * @return array
     */
    public function updateAccreditationStatus(int $accreditationId, string $status, string $reviewNotes = '', int $reviewedBy = 0): array
    {
        try {
            $this->accreditationModel->updateStatus($accreditationId, $status, $reviewNotes, $reviewedBy);

            return [
                'success' => true,
                'message' => 'Accreditation status updated successfully',
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
     * Renew accreditation
     * 
     * @param int $accreditationId
     * @return array
     */
    public function renewAccreditation(int $accreditationId): array
    {
        try {
            $this->accreditationModel->renewAccreditation($accreditationId);

            return [
                'success' => true,
                'message' => 'Accreditation renewal requested successfully',
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
     * Get accreditation history
     * 
     * @param int $accreditationId
     * @return array
     */
    public function getAccreditationHistory(int $accreditationId): array
    {
        try {
            $history = $this->accreditationModel->getAccreditationHistory($accreditationId);

            return [
                'success' => true,
                'message' => 'Accreditation history retrieved successfully',
                'data' => $history
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
     * Get accreditations for review
     * 
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getAccreditationsForReview(int $limit = 20, int $offset = 0): array
    {
        try {
            $accreditations = $this->accreditationModel->getAccreditationsForReview($limit, $offset);

            return [
                'success' => true,
                'message' => 'Accreditations for review retrieved successfully',
                'data' => $accreditations
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
     * Get expiring accreditations
     * 
     * @param int $daysThreshold
     * @return array
     */
    public function getExpiringAccreditations(int $daysThreshold = 30): array
    {
        try {
            $accreditations = $this->accreditationModel->getExpiringAccreditations($daysThreshold);

            return [
                'success' => true,
                'message' => 'Expiring accreditations retrieved successfully',
                'data' => $accreditations
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
     * Get accreditation statistics
     * 
     * @return array
     */
    public function getAccreditationStatistics(): array
    {
        try {
            $statistics = $this->accreditationModel->getAccreditationStatistics();

            return [
                'success' => true,
                'message' => 'Accreditation statistics retrieved successfully',
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
