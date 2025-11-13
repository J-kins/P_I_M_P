<?php
/**
 * P.I.M.P - Complaint System Model
 * Handles complaint cases, threads, evidence, and resolution tracking
 */

namespace PIMP\Models;

use PIMP\Services\Database\MySQLDatabase;
use PDOException;
use Exception;

class Complaint
{
    /**
     * @var MySQLDatabase Database instance
     */
    private $db;

    /**
     * @var string Complaints table name
     */
    private $complaintsTable = 'complaints';

    /**
     * @var string Complaint threads table
     */
    private $threadsTable = 'complaint_threads';

    /**
     * @var string Complaint evidence table
     */
    private $evidenceTable = 'complaint_evidence';

    /**
     * @var string Complaint categories table
     */
    private $categoriesTable = 'complaint_categories';

    /**
     * Complaint status constants
     */
    const STATUS_NEW = 'new';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_UNDER_REVIEW = 'under_review';
    const STATUS_RESOLVED = 'resolved';
    const STATUS_CLOSED = 'closed';
    const STATUS_REJECTED = 'rejected';
    const STATUS_ESCALATED = 'escalated';

    /**
     * Complaint priority levels
     */
    const PRIORITY_LOW = 'low';
    const PRIORITY_MEDIUM = 'medium';
    const PRIORITY_HIGH = 'high';
    const PRIORITY_URGENT = 'urgent';

    /**
     * Complaint types
     */
    const TYPE_SERVICE_QUALITY = 'service_quality';
    const TYPE_PRODUCT_ISSUE = 'product_issue';
    const TYPE_BILLING = 'billing';
    const TYPE_FRAUD = 'fraud';
    const TYPE_MISREPRESENTATION = 'misrepresentation';
    const TYPE_SAFETY = 'safety';
    const TYPE_OTHER = 'other';

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
     * Create new complaint
     * 
     * @param int $userId
     * @param int $businessId
     * @param array $complaintData
     * @return array
     * @throws Exception
     */
    public function createComplaint(int $userId, int $businessId, array $complaintData): array
    {
        $requiredFields = ['title', 'description', 'complaint_type'];
        foreach ($requiredFields as $field) {
            if (empty($complaintData[$field])) {
                throw new Exception("Required field missing: {$field}");
            }
        }

        // Validate complaint type
        $validTypes = [
            self::TYPE_SERVICE_QUALITY, self::TYPE_PRODUCT_ISSUE, self::TYPE_BILLING,
            self::TYPE_FRAUD, self::TYPE_MISREPRESENTATION, self::TYPE_SAFETY, self::TYPE_OTHER
        ];
        if (!in_array($complaintData['complaint_type'], $validTypes)) {
            throw new Exception("Invalid complaint type: {$complaintData['complaint_type']}");
        }

        $complaintData['user_id'] = $userId;
        $complaintData['business_id'] = $businessId;
        $complaintData['complaint_id'] = $this->generateComplaintId();
        $complaintData['status'] = self::STATUS_NEW;
        $complaintData['priority'] = $complaintData['priority'] ?? self::PRIORITY_MEDIUM;
        $complaintData['created_at'] = date('Y-m-d H:i:s');
        $complaintData['updated_at'] = date('Y-m-d H:i:s');

        try {
            $this->db->beginTransaction();

            $columns = implode(', ', array_keys($complaintData));
            $placeholders = ':' . implode(', :', array_keys($complaintData));
            
            $query = "INSERT INTO {$this->complaintsTable} ({$columns}) VALUES ({$placeholders})";
            $this->db->query($query, $complaintData);

            $complaintId = $this->db->lastInsertId();

            // Create initial thread message
            $this->addThreadMessage($complaintId, $userId, $complaintData['description'], 'complaint_created');
            
            $this->db->commit();

            return $this->getComplaintById($complaintId);
            
        } catch (PDOException $e) {
            $this->db->rollback();
            throw new Exception("Failed to create complaint: " . $e->getMessage());
        }
    }

    /**
     * Get complaint by ID
     * 
     * @param int $complaintId
     * @return array|null
     */
    public function getComplaintById(int $complaintId): ?array
    {
        $query = "SELECT c.*, u.first_name, u.last_name, u.email as user_email,
                         b.business_name, b.business_id as business_identifier
                  FROM {$this->complaintsTable} c
                  INNER JOIN users u ON c.user_id = u.id
                  INNER JOIN business_profiles b ON c.business_id = b.id
                  WHERE c.id = :id";
        
        $complaint = $this->db->fetchOne($query, ['id' => $complaintId]);
        
        if ($complaint) {
            $complaint['thread_messages'] = $this->getThreadMessages($complaintId);
            $complaint['evidence'] = $this->getComplaintEvidence($complaintId);
            
            if ($complaint['metadata']) {
                $complaint['metadata'] = json_decode($complaint['metadata'], true);
            }
        }
        
        return $complaint ?: null;
    }

    /**
     * Get complaint by complaint ID
     * 
     * @param string $complaintId
     * @return array|null
     */
    public function getComplaintByComplaintId(string $complaintId): ?array
    {
        $query = "SELECT c.*, u.first_name, u.last_name, b.business_name 
                  FROM {$this->complaintsTable} c
                  INNER JOIN users u ON c.user_id = u.id
                  INNER JOIN business_profiles b ON c.business_id = b.id
                  WHERE c.complaint_id = :complaint_id";
        
        return $this->db->fetchOne($query, ['complaint_id' => $complaintId]) ?: null;
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
        $whereConditions = ["c.user_id = :user_id"];
        $params = ['user_id' => $userId];

        if (!empty($filters['status'])) {
            $whereConditions[] = "c.status = :status";
            $params['status'] = $filters['status'];
        }

        if (!empty($filters['business_id'])) {
            $whereConditions[] = "c.business_id = :business_id";
            $params['business_id'] = $filters['business_id'];
        }

        if (!empty($filters['complaint_type'])) {
            $whereConditions[] = "c.complaint_type = :complaint_type";
            $params['complaint_type'] = $filters['complaint_type'];
        }

        $whereClause = implode(' AND ', $whereConditions);
        $offset = ($page - 1) * $perPage;

        $query = "SELECT c.*, b.business_name, b.business_id as business_identifier 
                  FROM {$this->complaintsTable} c
                  INNER JOIN business_profiles b ON c.business_id = b.id
                  WHERE {$whereClause} 
                  ORDER BY c.created_at DESC 
                  LIMIT :limit OFFSET :offset";
        
        $params['limit'] = $perPage;
        $params['offset'] = $offset;

        $complaints = $this->db->fetchAll($query, $params);

        // Get total count for pagination
        $countQuery = "SELECT COUNT(*) as total FROM {$this->complaintsTable} c WHERE {$whereClause}";
        $total = $this->db->fetchColumn($countQuery, $params);

        return [
            'complaints' => $complaints,
            'pagination' => [
                'page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'total_pages' => ceil($total / $perPage)
            ]
        ];
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
        $whereConditions = ["c.business_id = :business_id"];
        $params = ['business_id' => $businessId];

        if (!empty($filters['status'])) {
            $whereConditions[] = "c.status = :status";
            $params['status'] = $filters['status'];
        }

        if (!empty($filters['priority'])) {
            $whereConditions[] = "c.priority = :priority";
            $params['priority'] = $filters['priority'];
        }

        $whereClause = implode(' AND ', $whereConditions);
        $offset = ($page - 1) * $perPage;

        $query = "SELECT c.*, u.first_name, u.last_name, u.email as user_email 
                  FROM {$this->complaintsTable} c
                  INNER JOIN users u ON c.user_id = u.id
                  WHERE {$whereClause} 
                  ORDER BY 
                    CASE c.priority 
                        WHEN 'urgent' THEN 1
                        WHEN 'high' THEN 2
                        WHEN 'medium' THEN 3
                        WHEN 'low' THEN 4
                    END,
                    c.created_at DESC 
                  LIMIT :limit OFFSET :offset";
        
        $params['limit'] = $perPage;
        $params['offset'] = $offset;

        $complaints = $this->db->fetchAll($query, $params);

        // Get total count for pagination
        $countQuery = "SELECT COUNT(*) as total FROM {$this->complaintsTable} c WHERE {$whereClause}";
        $total = $this->db->fetchColumn($countQuery, $params);

        return [
            'complaints' => $complaints,
            'pagination' => [
                'page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'total_pages' => ceil($total / $perPage)
            ]
        ];
    }

    /**
     * Update complaint status
     * 
     * @param int $complaintId
     * @param string $status
     * @param string $notes
     * @param int $updatedBy
     * @return bool
     * @throws Exception
     */
    public function updateComplaintStatus(int $complaintId, string $status, string $notes = '', int $updatedBy = 0): bool
    {
        $validStatuses = [
            self::STATUS_NEW, self::STATUS_IN_PROGRESS, self::STATUS_UNDER_REVIEW,
            self::STATUS_RESOLVED, self::STATUS_CLOSED, self::STATUS_REJECTED, self::STATUS_ESCALATED
        ];

        if (!in_array($status, $validStatuses)) {
            throw new Exception("Invalid complaint status: {$status}");
        }

        $complaint = $this->getComplaintById($complaintId);
        if (!$complaint) {
            throw new Exception("Complaint not found");
        }

        $updateData = [
            'status' => $status,
            'updated_by' => $updatedBy,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        // Set resolution date if resolved
        if ($status === self::STATUS_RESOLVED) {
            $updateData['resolved_at'] = date('Y-m-d H:i:s');
        }

        $setParts = [];
        foreach (array_keys($updateData) as $field) {
            $setParts[] = "{$field} = :{$field}";
        }

        $query = "UPDATE {$this->complaintsTable} SET " . implode(', ', $setParts) . " WHERE id = :id";
        $updateData['id'] = $complaintId;

        $this->db->query($query, $updateData);

        // Add status update to thread
        $this->addThreadMessage($complaintId, $updatedBy, "Status updated to: {$status}. " . $notes, 'status_update');

        return true;
    }

    /**
     * Update complaint priority
     * 
     * @param int $complaintId
     * @param string $priority
     * @param string $reason
     * @param int $updatedBy
     * @return bool
     * @throws Exception
     */
    public function updateComplaintPriority(int $complaintId, string $priority, string $reason = '', int $updatedBy = 0): bool
    {
        $validPriorities = [self::PRIORITY_LOW, self::PRIORITY_MEDIUM, self::PRIORITY_HIGH, self::PRIORITY_URGENT];
        if (!in_array($priority, $validPriorities)) {
            throw new Exception("Invalid complaint priority: {$priority}");
        }

        $query = "UPDATE {$this->complaintsTable} SET priority = :priority, updated_by = :updated_by, 
                  updated_at = :updated_at WHERE id = :id";
        
        $this->db->query($query, [
            'priority' => $priority,
            'updated_by' => $updatedBy,
            'updated_at' => date('Y-m-d H:i:s'),
            'id' => $complaintId
        ]);

        // Add priority update to thread
        $this->addThreadMessage($complaintId, $updatedBy, "Priority updated to: {$priority}. " . $reason, 'priority_update');

        return true;
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
     * @throws Exception
     */
    public function addThreadMessage(int $complaintId, int $userId, string $message, string $messageType = 'message', bool $isInternal = false): array
    {
        $validTypes = ['message', 'status_update', 'priority_update', 'resolution_note', 'internal_note'];
        if (!in_array($messageType, $validTypes)) {
            throw new Exception("Invalid message type: {$messageType}");
        }

        $messageData = [
            'complaint_id' => $complaintId,
            'user_id' => $userId,
            'message' => $message,
            'message_type' => $messageType,
            'is_internal' => $isInternal,
            'created_at' => date('Y-m-d H:i:s')
        ];

        $columns = implode(', ', array_keys($messageData));
        $placeholders = ':' . implode(', :', array_keys($messageData));
        
        $query = "INSERT INTO {$this->threadsTable} ({$columns}) VALUES ({$placeholders})";
        $this->db->query($query, $messageData);

        $messageId = $this->db->lastInsertId();
        return $this->getThreadMessage($messageId);
    }

    /**
     * Get thread message
     * 
     * @param int $messageId
     * @return array|null
     */
    public function getThreadMessage(int $messageId): ?array
    {
        $query = "SELECT t.*, u.first_name, u.last_name, u.user_type 
                  FROM {$this->threadsTable} t
                  INNER JOIN users u ON t.user_id = u.id
                  WHERE t.id = :id";
        
        return $this->db->fetchOne($query, ['id' => $messageId]) ?: null;
    }

    /**
     * Get thread messages for complaint
     * 
     * @param int $complaintId
     * @param bool $includeInternal
     * @return array
     */
    public function getThreadMessages(int $complaintId, bool $includeInternal = false): array
    {
        $whereConditions = ["complaint_id = :complaint_id"];
        $params = ['complaint_id' => $complaintId];

        if (!$includeInternal) {
            $whereConditions[] = "is_internal = FALSE";
        }

        $whereClause = implode(' AND ', $whereConditions);
        $query = "SELECT t.*, u.first_name, u.last_name, u.user_type 
                  FROM {$this->threadsTable} t
                  INNER JOIN users u ON t.user_id = u.id
                  WHERE {$whereClause} 
                  ORDER BY t.created_at ASC";
        
        return $this->db->fetchAll($query, $params);
    }

    /**
     * Add evidence to complaint
     * 
     * @param int $complaintId
     * @param int $userId
     * @param array $evidenceData
     * @return array
     * @throws Exception
     */
    public function addEvidence(int $complaintId, int $userId, array $evidenceData): array
    {
        $requiredFields = ['file_name', 'file_path', 'file_type'];
        foreach ($requiredFields as $field) {
            if (empty($evidenceData[$field])) {
                throw new Exception("Required field missing: {$field}");
            }
        }

        $evidenceData['complaint_id'] = $complaintId;
        $evidenceData['uploaded_by'] = $userId;
        $evidenceData['created_at'] = date('Y-m-d H:i:s');

        $columns = implode(', ', array_keys($evidenceData));
        $placeholders = ':' . implode(', :', array_keys($evidenceData));
        
        $query = "INSERT INTO {$this->evidenceTable} ({$columns}) VALUES ({$placeholders})";
        $this->db->query($query, $evidenceData);

        $evidenceId = $this->db->lastInsertId();
        return $this->getEvidence($evidenceId);
    }

    /**
     * Get evidence by ID
     * 
     * @param int $evidenceId
     * @return array|null
     */
    public function getEvidence(int $evidenceId): ?array
    {
        $query = "SELECT e.*, u.first_name, u.last_name 
                  FROM {$this->evidenceTable} e
                  INNER JOIN users u ON e.uploaded_by = u.id
                  WHERE e.id = :id";
        
        $evidence = $this->db->fetchOne($query, ['id' => $evidenceId]);
        
        if ($evidence && $evidence['metadata']) {
            $evidence['metadata'] = json_decode($evidence['metadata'], true);
        }
        
        return $evidence ?: null;
    }

    /**
     * Get complaint evidence
     * 
     * @param int $complaintId
     * @return array
     */
    public function getComplaintEvidence(int $complaintId): array
    {
        $query = "SELECT e.*, u.first_name, u.last_name 
                  FROM {$this->evidenceTable} e
                  INNER JOIN users u ON e.uploaded_by = u.id
                  WHERE e.complaint_id = :complaint_id 
                  ORDER BY e.created_at ASC";
        
        $evidence = $this->db->fetchAll($query, ['complaint_id' => $complaintId]);
        
        foreach ($evidence as &$item) {
            if ($item['metadata']) {
                $item['metadata'] = json_decode($item['metadata'], true);
            }
        }
        
        return $evidence;
    }

    /**
     * Escalate complaint
     * 
     * @param int $complaintId
     * @param string $escalationReason
     * @param int $escalatedBy
     * @return bool
     * @throws Exception
     */
    public function escalateComplaint(int $complaintId, string $escalationReason, int $escalatedBy): bool
    {
        $complaint = $this->getComplaintById($complaintId);
        if (!$complaint) {
            throw new Exception("Complaint not found");
        }

        $updateData = [
            'status' => self::STATUS_ESCALATED,
            'escalated_at' => date('Y-m-d H:i:s'),
            'escalated_by' => $escalatedBy,
            'escalation_reason' => $escalationReason,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $setParts = [];
        foreach (array_keys($updateData) as $field) {
            $setParts[] = "{$field} = :{$field}";
        }

        $query = "UPDATE {$this->complaintsTable} SET " . implode(', ', $setParts) . " WHERE id = :id";
        $updateData['id'] = $complaintId;

        $this->db->query($query, $updateData);

        // Add escalation note to thread
        $this->addThreadMessage($complaintId, $escalatedBy, "Complaint escalated. Reason: {$escalationReason}", 'status_update');

        return true;
    }

    /**
     * Get complaint statistics
     * 
     * @param int $businessId
     * @return array
     */
    public function getComplaintStatistics(int $businessId): array
    {
        $totalComplaints = $this->db->fetchColumn(
            "SELECT COUNT(*) FROM {$this->complaintsTable} WHERE business_id = :business_id",
            ['business_id' => $businessId]
        );

        $resolvedComplaints = $this->db->fetchColumn(
            "SELECT COUNT(*) FROM {$this->complaintsTable} WHERE business_id = :business_id AND status = :status",
            [
                'business_id' => $businessId,
                'status' => self::STATUS_RESOLVED
            ]
        );

        $openComplaints = $this->db->fetchColumn(
            "SELECT COUNT(*) FROM {$this->complaintsTable} WHERE business_id = :business_id AND status IN (:new, :in_progress, :under_review)",
            [
                'business_id' => $businessId,
                'new' => self::STATUS_NEW,
                'in_progress' => self::STATUS_IN_PROGRESS,
                'under_review' => self::STATUS_UNDER_REVIEW
            ]
        );

        $avgResolutionTime = $this->db->fetchColumn(
            "SELECT AVG(TIMESTAMPDIFF(HOUR, created_at, resolved_at)) 
             FROM {$this->complaintsTable} 
             WHERE business_id = :business_id AND status = :status AND resolved_at IS NOT NULL",
            [
                'business_id' => $businessId,
                'status' => self::STATUS_RESOLVED
            ]
        ) ?: 0;

        $complaintsByType = $this->db->fetchAll(
            "SELECT complaint_type, COUNT(*) as count 
             FROM {$this->complaintsTable} 
             WHERE business_id = :business_id 
             GROUP BY complaint_type 
             ORDER BY count DESC",
            ['business_id' => $businessId]
        );

        $complaintsByPriority = $this->db->fetchAll(
            "SELECT priority, COUNT(*) as count 
             FROM {$this->complaintsTable} 
             WHERE business_id = :business_id 
             GROUP BY priority 
             ORDER BY 
                CASE priority 
                    WHEN 'urgent' THEN 1
                    WHEN 'high' THEN 2
                    WHEN 'medium' THEN 3
                    WHEN 'low' THEN 4
                END",
            ['business_id' => $businessId]
        );

        return [
            'total_complaints' => (int)$totalComplaints,
            'resolved_complaints' => (int)$resolvedComplaints,
            'open_complaints' => (int)$openComplaints,
            'resolution_rate' => $totalComplaints > 0 ? round(($resolvedComplaints / $totalComplaints) * 100, 2) : 0,
            'avg_resolution_time_hours' => round($avgResolutionTime, 2),
            'complaints_by_type' => $complaintsByType,
            'complaints_by_priority' => $complaintsByPriority
        ];
    }

    /**
     * Generate unique complaint ID
     * 
     * @return string
     */
    private function generateComplaintId(): string
    {
        $prefix = 'CMP';
        $timestamp = time();
        $random = mt_rand(1000, 9999);
        
        return $prefix . $timestamp . $random;
    }
}
