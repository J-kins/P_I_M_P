<?php
/**
 * P.I.M.P - Business Documents API Service
 * Handles business documents API operations
 */

namespace PIMP\Services\API;

use PIMP\Models\BusinessDocument;
use PIMP\Services\Database\MySQLDatabase;
use Exception;

class BusinessDocumentAPIService
{
    /**
     * @var BusinessDocument
     */
    private $documentModel;

    /**
     * Constructor
     * 
     * @param MySQLDatabase $db
     */
    public function __construct(MySQLDatabase $db)
    {
        $this->documentModel = new BusinessDocument($db);
    }

    /**
     * Upload business document
     * 
     * @param int $businessId
     * @param array $documentData
     * @return array
     */
    public function uploadDocument(int $businessId, array $documentData): array
    {
        try {
            $document = $this->documentModel->uploadDocument($businessId, $documentData);
            
            return [
                'success' => true,
                'message' => 'Document uploaded successfully',
                'data' => $document
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
     * Get business documents
     * 
     * @param int $businessId
     * @param array $filters
     * @return array
     */
    public function getBusinessDocuments(int $businessId, array $filters = []): array
    {
        try {
            $documents = $this->documentModel->getBusinessDocuments($businessId, $filters);

            return [
                'success' => true,
                'message' => 'Business documents retrieved successfully',
                'data' => $documents
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
     * Update document status
     * 
     * @param int $documentId
     * @param string $status
     * @param string $reviewNotes
     * @param int $reviewedBy
     * @return array
     */
    public function updateDocumentStatus(int $documentId, string $status, string $reviewNotes = '', int $reviewedBy = 0): array
    {
        try {
            $this->documentModel->updateDocumentStatus($documentId, $status, $reviewNotes, $reviewedBy);

            return [
                'success' => true,
                'message' => 'Document status updated successfully',
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
     * Delete document
     * 
     * @param int $documentId
     * @return array
     */
    public function deleteDocument(int $documentId): array
    {
        try {
            $this->documentModel->deleteDocument($documentId);

            return [
                'success' => true,
                'message' => 'Document deleted successfully',
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
     * Get document types
     * 
     * @param array $filters
     * @return array
     */
    public function getDocumentTypes(array $filters = []): array
    {
        try {
            $documentTypes = $this->documentModel->getAllDocumentTypes($filters);

            return [
                'success' => true,
                'message' => 'Document types retrieved successfully',
                'data' => $documentTypes
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
     * Check required documents for accreditation
     * 
     * @param int $businessId
     * @param string $accreditationLevel
     * @return array
     */
    public function checkRequiredDocuments(int $businessId, string $accreditationLevel): array
    {
        try {
            $result = $this->documentModel->checkRequiredDocuments($businessId, $accreditationLevel);

            return [
                'success' => true,
                'message' => 'Required documents check completed',
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
     * Get expiring documents
     * 
     * @param int $daysThreshold
     * @return array
     */
    public function getExpiringDocuments(int $daysThreshold = 30): array
    {
        try {
            $documents = $this->documentModel->getExpiringDocuments($daysThreshold);

            return [
                'success' => true,
                'message' => 'Expiring documents retrieved successfully',
                'data' => $documents
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
     * Get documents for review
     * 
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getDocumentsForReview(int $limit = 20, int $offset = 0): array
    {
        try {
            $documents = $this->documentModel->getDocumentsForReview($limit, $offset);

            return [
                'success' => true,
                'message' => 'Documents for review retrieved successfully',
                'data' => $documents
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
     * Get document statistics
     * 
     * @param int $businessId
     * @return array
     */
    public function getDocumentStatistics(int $businessId): array
    {
        try {
            $statistics = $this->documentModel->getDocumentStatistics($businessId);

            return [
                'success' => true,
                'message' => 'Document statistics retrieved successfully',
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
