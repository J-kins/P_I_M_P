<?php
/**
 * P.I.M.P - Complaint API Service
 * Handles complaint-related API operations
 */

namespace PIMP\Services\API;

use PIMP\Models\Complaint;
use PIMP\Services\Database\MySQLDatabase;
use Exception;

class ComplaintAPIService
{
    /**
     * @var Complaint
     */
    private $complaintModel;

    /**
     * Constructor
     * 
     * @param MySQLDatabase $db
     */
    public function __construct(MySQLDatabase $db)
    {
        $this->complaintModel = new Complaint($db);
    }

    /**
     * Create new complaint
     * 
     * @param int $userId
     * @param int $businessId
     * @param array $complaintData
     * @return array
     */
    public function createComplaint(int $userId, int $businessId, array $complaintData): array
    {
        try {
            $complaint = $this->complaintModel->createComplaint($userId, $businessId, $complaintData);
            
            return [
                'success' => true,
                'message' => 'Complaint created successfully',
                'data' => $complaint
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
     * Get complaint by ID
     * 
     * @param int $complaintId
     * @return array
     */
    public function getComplaint(int $complaintId): array
    {
        try {
            $complaint = $this->complaintModel->getComplaintById($complaintId);
            
            if (!$complaint) {
                throw new Exception("Complaint not found");
            }

            return [
                'success' => true,
                'message' => 'Complaint retrieved successfully',
                'data' => $complaint
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
     * Get user complaints
     * 
     * @param int $userId
     * @param array $filters
     * @param int $page
     * @param int $perPage
     * @return array
     */
    public function getUserComplaints(int $userId, array $filters = [], int $page = 1, int $perPage = 20): array
    {
        try {
            $result = $this->complaintModel->getUserComplaints($userId, $filters, $page, $perPage);

            return [
                'success' => true,
                'message' => 'User complaints retrieved successfully',
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
     * Get business complaints
     * 
     * @param int $businessId
     * @param array $filters
     * @param int $page
     * @param int $perPage
     * @return array
     */
    public function getBusinessComplaints(int $businessId, array $filters = [], int $page = 1, int $perPage = 20): array
    {
        try {
            $result = $this->complaintModel->getBusinessComplaints($businessId, $filters, $page, $perPage);

            return [
                'success' => true,
                'message' => 'Business complaints retrieved successfully',
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
     * Update complaint status
     * 
     * @param int $complaintId
     * @param string $status
     * @param string $notes
     * @param int $updatedBy
     * @return array
     */
    public function updateComplaintStatus(int $complaintId, string $status, string $notes = '', int $updatedBy = 0): array
    {
        try {
            $this->complaintModel->updateComplaintStatus($complaintId, $status, $notes, $updatedBy);

            return [
                'success' => true,
                'message' => 'Complaint status updated successfully',
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
     * Update complaint priority
     * 
     * @param int $complaintId
     * @param string $priority
     * @param string $reason
     * @param int $updatedBy
     * @return array
     */
    public function updateComplaintPriority(int $complaintId, string $priority, string $reason = '', int $updatedBy = 0): array
    {
        try {
            $this->complaintModel->updateComplaintPriority($complaintId, $priority, $reason, $updatedBy);

            return [
                'success' => true,
                'message' => 'Complaint priority updated successfully',
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
     * Add thread message
     * 
     * @param int $complaintId
     * @param int $userId
     * @param string $message
     * @param string $messageType
     * @param bool $isInternal
     * @return array
     */
    public function addThreadMessage(int $complaintId, int $userId, string $message, string $messageType = 'message', bool $isInternal = false): array
    {
        try {
            $threadMessage = $this->complaintModel->addThreadMessage($complaintId, $userId, $message, $messageType, $isInternal);

            return [
                'success' => true,
                'message' => 'Thread message added successfully',
                'data' => $threadMessage
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
     * Get thread messages
     * 
     * @param int $complaintId
     * @param bool $includeInternal
     * @return array
     */
    public function getThreadMessages(int $complaintId, bool $includeInternal = false): array
    {
        try {
            $messages = $this->complaintModel->getThreadMessages($complaintId, $includeInternal);

            return [
                'success' => true,
                'message' => 'Thread messages retrieved successfully',
                'data' => $messages
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
     * Add evidence
     * 
     * @param int $complaintId
     * @param int $userId
     * @param array $evidenceData
     * @return array
     */
    public function addEvidence(int $complaintId, int $userId, array $evidenceData): array
    {
        try {
            $evidence = $this->complaintModel->addEvidence($complaintId, $userId, $evidenceData);

            return [
                'success' => true,
                'message' => 'Evidence added successfully',
                'data' => $evidence
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
     * Get complaint evidence
     * 
     * @param int $complaintId
     * @return array
     */
    public function getComplaintEvidence(int $complaintId): array
    {
        try {
            $evidence = $this->complaintModel->getComplaintEvidence($complaintId);

            return [
                'success' => true,
                'message' => 'Complaint evidence retrieved successfully',
                'data' => $evidence
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
     * Escalate complaint
     * 
     * @param int $complaintId
     * @param string $escalationReason
     * @param int $escalatedBy
     * @return array
     */
    public function escalateComplaint(int $complaintId, string $escalationReason, int $escalatedBy): array
    {
        try {
            $this->complaintModel->escalateComplaint($complaintId, $escalationReason, $escalatedBy);

            return [
                'success' => true,
                'message' => 'Complaint escalated successfully',
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
     * Get complaint statistics
     * 
     * @param int $businessId
     * @return array
     */
    public function getComplaintStatistics(int $businessId): array
    {
        try {
            $statistics = $this->complaintModel->getComplaintStatistics($businessId);

            return [
                'success' => true,
                'message' => 'Complaint statistics retrieved successfully',
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
