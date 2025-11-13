<?php
/**
 * P.I.M.P - Business Documents and Proof Model
 * Handles business document upload, verification, and management
 */

namespace PIMP\Models;

use PIMP\Services\Database\MySQLDatabase;
use PDOException;
use Exception;

class BusinessDocument
{
    /**
     * @var MySQLDatabase Database instance
     */
    private $db;

    /**
     * @var string Documents table name
     */
    private $documentsTable = 'business_documents';

    /**
     * @var string Document types table
     */
    private $documentTypesTable = 'document_types';

    /**
     * Document status constants
     */
    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';
    const STATUS_EXPIRED = 'expired';

    /**
     * Document categories
     */
    const CATEGORY_IDENTIFICATION = 'identification';
    const CATEGORY_BUSINESS_REGISTRATION = 'business_registration';
    const CATEGORY_TAX_DOCUMENTS = 'tax_documents';
    const CATEGORY_INSURANCE = 'insurance';
    const CATEGORY_CERTIFICATES = 'certificates';
    const CATEGORY_ACCREDITATION = 'accreditation';
    const CATEGORY_OTHER = 'other';

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
     * Upload business document
     * 
     * @param int $businessId
     * @param array $documentData
     * @return array
     * @throws Exception
     */
    public function uploadDocument(int $businessId, array $documentData): array
    {
        $requiredFields = ['document_type', 'file_name', 'file_path', 'file_size'];
        foreach ($requiredFields as $field) {
            if (empty($documentData[$field])) {
                throw new Exception("Required field missing: {$field}");
            }
        }

        // Validate document type
        $documentType = $this->getDocumentType($documentData['document_type']);
        if (!$documentType) {
            throw new Exception("Invalid document type: {$documentData['document_type']}");
        }

        $documentData['business_id'] = $businessId;
        $documentData['status'] = self::STATUS_PENDING;
        $documentData['uploaded_at'] = date('Y-m-d H:i:s');
        $documentData['created_at'] = date('Y-m-d H:i:s');
        $documentData['updated_at'] = date('Y-m-d H:i:s');

        // Set expiry date if required for this document type
        if ($documentType['requires_expiry'] && empty($documentData['expiry_date'])) {
            throw new Exception("Expiry date is required for this document type");
        }

        try {
            $this->db->beginTransaction();

            $columns = implode(', ', array_keys($documentData));
            $placeholders = ':' . implode(', :', array_keys($documentData));
            
            $query = "INSERT INTO {$this->documentsTable} ({$columns}) VALUES ({$placeholders})";
            $this->db->query($query, $documentData);

            $documentId = $this->db->lastInsertId();
            
            $this->db->commit();

            return $this->getDocumentById($documentId);
            
        } catch (PDOException $e) {
            $this->db->rollback();
            throw new Exception("Failed to upload document: " . $e->getMessage());
        }
    }

    /**
     * Get document by ID
     * 
     * @param int $documentId
     * @return array|null
     */
    public function getDocumentById(int $documentId): ?array
    {
        $query = "SELECT d.*, dt.name as document_type_name, dt.category 
                  FROM {$this->documentsTable} d
                  INNER JOIN {$this->documentTypesTable} dt ON d.document_type = dt.id
                  WHERE d.id = :id";
        
        $result = $this->db->fetchOne($query, ['id' => $documentId]);
        
        if ($result && $result['metadata']) {
            $result['metadata'] = json_decode($result['metadata'], true);
        }
        
        return $result ?: null;
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
        $whereConditions = ["d.business_id = :business_id"];
        $params = ['business_id' => $businessId];

        if (!empty($filters['document_type'])) {
            $whereConditions[] = "d.document_type = :document_type";
            $params['document_type'] = $filters['document_type'];
        }

        if (!empty($filters['category'])) {
            $whereConditions[] = "dt.category = :category";
            $params['category'] = $filters['category'];
        }

        if (!empty($filters['status'])) {
            $whereConditions[] = "d.status = :status";
            $params['status'] = $filters['status'];
        }

        $whereClause = implode(' AND ', $whereConditions);
        
        $query = "SELECT d.*, dt.name as document_type_name, dt.category 
                  FROM {$this->documentsTable} d
                  INNER JOIN {$this->documentTypesTable} dt ON d.document_type = dt.id
                  WHERE {$whereClause}
                  ORDER BY d.uploaded_at DESC";
        
        $documents = $this->db->fetchAll($query, $params);
        
        // Decode metadata for each document
        foreach ($documents as &$document) {
            if ($document['metadata']) {
                $document['metadata'] = json_decode($document['metadata'], true);
            }
        }
        
        return $documents;
    }

    /**
     * Update document status
     * 
     * @param int $documentId
     * @param string $status
     * @param string $reviewNotes
     * @param int $reviewedBy
     * @return bool
     * @throws Exception
     */
    public function updateDocumentStatus(int $documentId, string $status, string $reviewNotes = '', int $reviewedBy = 0): bool
    {
        $validStatuses = [self::STATUS_APPROVED, self::STATUS_REJECTED, self::STATUS_EXPIRED];
        if (!in_array($status, $validStatuses)) {
            throw new Exception("Invalid status: {$status}");
        }

        $document = $this->getDocumentById($documentId);
        if (!$document) {
            throw new Exception("Document not found");
        }

        $updateData = [
            'status' => $status,
            'reviewed_by' => $reviewedBy,
            'reviewed_at' => date('Y-m-d H:i:s'),
            'review_notes' => $reviewNotes,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $setParts = [];
        foreach (array_keys($updateData) as $field) {
            $setParts[] = "{$field} = :{$field}";
        }

        $query = "UPDATE {$this->documentsTable} SET " . implode(', ', $setParts) . " WHERE id = :id";
        $updateData['id'] = $documentId;
        $this->db->query($query, $updateData);

        return true;
    }

    /**
     * Delete document
     * 
     * @param int $documentId
     * @return bool
     * @throws Exception
     */
    public function deleteDocument(int $documentId): bool
    {
        $document = $this->getDocumentById($documentId);
        if (!$document) {
            throw new Exception("Document not found");
        }

        // Only allow deletion of pending or rejected documents
        if (!in_array($document['status'], [self::STATUS_PENDING, self::STATUS_REJECTED])) {
            throw new Exception("Cannot delete approved or expired documents");
        }

        $query = "DELETE FROM {$this->documentsTable} WHERE id = :id";
        $this->db->query($query, ['id' => $documentId]);

        return true;
    }

    /**
     * Get document type by ID or slug
     * 
     * @param mixed $typeIdentifier
     * @return array|null
     */
    public function getDocumentType($typeIdentifier): ?array
    {
        $field = is_numeric($typeIdentifier) ? 'id' : 'slug';
        $query = "SELECT * FROM {$this->documentTypesTable} WHERE {$field} = :identifier AND status = 'active'";
        
        return $this->db->fetchOne($query, ['identifier' => $typeIdentifier]) ?: null;
    }

    /**
     * Get all document types
     * 
     * @param array $filters
     * @return array
     */
    public function getAllDocumentTypes(array $filters = []): array
    {
        $whereConditions = ["status = 'active'"];
        $params = [];

        if (!empty($filters['category'])) {
            $whereConditions[] = "category = :category";
            $params['category'] = $filters['category'];
        }

        if (!empty($filters['required_for_accreditation'])) {
            $whereConditions[] = "required_for_accreditation = :required";
            $params['required'] = $filters['required_for_accreditation'];
        }

        $whereClause = implode(' AND ', $whereConditions);
        $query = "SELECT * FROM {$this->documentTypesTable} WHERE {$whereClause} ORDER BY display_order ASC, name ASC";
        
        return $this->db->fetchAll($query, $params);
    }

    /**
     * Check if business has required documents for accreditation
     * 
     * @param int $businessId
     * @param string $accreditationLevel
     * @return array
     */
    public function checkRequiredDocuments(int $businessId, string $accreditationLevel): array
    {
        // Get required document types for accreditation level
        $query = "SELECT * FROM {$this->documentTypesTable} 
                  WHERE required_for_accreditation = :required 
                  AND accreditation_level <= :level 
                  AND status = 'active'";
        
        $requiredTypes = $this->db->fetchAll($query, [
            'required' => true,
            'level' => $this->getAccreditationLevelValue($accreditationLevel)
        ]);

        $missingDocuments = [];
        $approvedDocuments = [];

        foreach ($requiredTypes as $docType) {
            $existingDoc = $this->db->fetchOne(
                "SELECT id, status FROM {$this->documentsTable} 
                 WHERE business_id = :business_id 
                 AND document_type = :document_type 
                 AND status = :status",
                [
                    'business_id' => $businessId,
                    'document_type' => $docType['id'],
                    'status' => self::STATUS_APPROVED
                ]
            );

            if ($existingDoc) {
                $approvedDocuments[] = [
                    'document_type' => $docType['name'],
                    'document_id' => $existingDoc['id']
                ];
            } else {
                $missingDocuments[] = [
                    'document_type' => $docType['name'],
                    'document_type_id' => $docType['id'],
                    'category' => $docType['category'],
                    'description' => $docType['description']
                ];
            }
        }

        return [
            'has_all_required' => empty($missingDocuments),
            'approved_documents' => $approvedDocuments,
            'missing_documents' => $missingDocuments,
            'total_required' => count($requiredTypes),
            'total_approved' => count($approvedDocuments)
        ];
    }

    /**
     * Get expiring documents
     * 
     * @param int $daysThreshold
     * @return array
     */
    public function getExpiringDocuments(int $daysThreshold = 30): array
    {
        $query = "SELECT d.*, b.business_name, b.email, dt.name as document_type_name 
                  FROM {$this->documentsTable} d
                  INNER JOIN business_profiles b ON d.business_id = b.id
                  INNER JOIN {$this->documentTypesTable} dt ON d.document_type = dt.id
                  WHERE d.status = :approved 
                  AND d.expiry_date IS NOT NULL
                  AND d.expiry_date BETWEEN :start_date AND :end_date
                  ORDER BY d.expiry_date ASC";
        
        $startDate = date('Y-m-d');
        $endDate = date('Y-m-d', strtotime("+{$daysThreshold} days"));
        
        $documents = $this->db->fetchAll($query, [
            'approved' => self::STATUS_APPROVED,
            'start_date' => $startDate,
            'end_date' => $endDate
        ]);
        
        foreach ($documents as &$document) {
            if ($document['metadata']) {
                $document['metadata'] = json_decode($document['metadata'], true);
            }
        }
        
        return $documents;
    }

    /**
     * Get documents requiring review
     * 
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getDocumentsForReview(int $limit = 20, int $offset = 0): array
    {
        $query = "SELECT d.*, b.business_name, dt.name as document_type_name, dt.category 
                  FROM {$this->documentsTable} d
                  INNER JOIN business_profiles b ON d.business_id = b.id
                  INNER JOIN {$this->documentTypesTable} dt ON d.document_type = dt.id
                  WHERE d.status = :pending
                  ORDER BY d.uploaded_at ASC 
                  LIMIT :limit OFFSET :offset";
        
        return $this->db->fetchAll($query, [
            'pending' => self::STATUS_PENDING,
            'limit' => $limit,
            'offset' => $offset
        ]);
    }

    /**
     * Get document statistics for business
     * 
     * @param int $businessId
     * @return array
     */
    public function getDocumentStatistics(int $businessId): array
    {
        $totalDocuments = $this->db->fetchColumn(
            "SELECT COUNT(*) FROM {$this->documentsTable} WHERE business_id = :business_id",
            ['business_id' => $businessId]
        );

        $approvedDocuments = $this->db->fetchColumn(
            "SELECT COUNT(*) FROM {$this->documentsTable} WHERE business_id = :business_id AND status = :status",
            [
                'business_id' => $businessId,
                'status' => self::STATUS_APPROVED
            ]
        );

        $pendingDocuments = $this->db->fetchColumn(
            "SELECT COUNT(*) FROM {$this->documentsTable} WHERE business_id = :business_id AND status = :status",
            [
                'business_id' => $businessId,
                'status' => self::STATUS_PENDING
            ]
        );

        $expiringSoon = $this->db->fetchColumn(
            "SELECT COUNT(*) FROM {$this->documentsTable} 
             WHERE business_id = :business_id 
             AND status = :status 
             AND expiry_date BETWEEN :start_date AND :end_date",
            [
                'business_id' => $businessId,
                'status' => self::STATUS_APPROVED,
                'start_date' => date('Y-m-d'),
                'end_date' => date('Y-m-d', strtotime('+30 days'))
            ]
        );

        return [
            'total_documents' => (int)$totalDocuments,
            'approved_documents' => (int)$approvedDocuments,
            'pending_documents' => (int)$pendingDocuments,
            'expiring_soon' => (int)$expiringSoon,
            'approval_rate' => $totalDocuments > 0 ? round(($approvedDocuments / $totalDocuments) * 100, 2) : 0
        ];
    }

    /**
     * Convert accreditation level to numeric value for comparison
     * 
     * @param string $accreditationLevel
     * @return int
     */
    private function getAccreditationLevelValue(string $accreditationLevel): int
    {
        $levels = [
            'none' => 0,
            'basic' => 1,
            'premium' => 2,
            'verified' => 3
        ];

        return $levels[$accreditationLevel] ?? 0;
    }
}
