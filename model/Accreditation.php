<?php
/**
 * P.I.M.P - Accreditation Status & History Model
 * Handles business accreditation tracking and history
 */

namespace PIMP\Models;

use PIMP\Services\Database\MySQLDatabase;
use PDOException;
use Exception;

class Accreditation
{
    /**
     * @var MySQLDatabase Database instance
     */
    private $db;

    /**
     * @var string Accreditation table name
     */
    private $accreditationTable = 'business_accreditations';

    /**
     * @var string Accreditation history table
     */
    private $historyTable = 'accreditation_history';

    /**
     * Accreditation status constants
     */
    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';
    const STATUS_EXPIRED = 'expired';
    const STATUS_SUSPENDED = 'suspended';
    const STATUS_RENEWAL_PENDING = 'renewal_pending';

    /**
     * Accreditation levels
     */
    const LEVEL_BASIC = 'basic';
    const LEVEL_PREMIUM = 'premium';
    const LEVEL_VERIFIED = 'verified';

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
     * Apply for accreditation
     * 
     * @param int $businessId
     * @param string $accreditationLevel
     * @param array $documentData
     * @return array
     * @throws Exception
     */
    public function applyForAccreditation(int $businessId, string $accreditationLevel, array $documentData = []): array
    {
        // Validate accreditation level
        $validLevels = [self::LEVEL_BASIC, self::LEVEL_PREMIUM, self::LEVEL_VERIFIED];
        if (!in_array($accreditationLevel, $validLevels)) {
            throw new Exception("Invalid accreditation level: {$accreditationLevel}");
        }

        // Check for existing pending application
        $existing = $this->getPendingApplication($businessId);
        if ($existing) {
            throw new Exception("Business already has a pending accreditation application");
        }

        $accreditationData = [
            'business_id' => $businessId,
            'accreditation_level' => $accreditationLevel,
            'status' => self::STATUS_PENDING,
            'application_date' => date('Y-m-d H:i:s'),
            'documents' => json_encode($documentData),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        try {
            $this->db->beginTransaction();

            // Insert accreditation record
            $columns = implode(', ', array_keys($accreditationData));
            $placeholders = ':' . implode(', :', array_keys($accreditationData));
            
            $query = "INSERT INTO {$this->accreditationTable} ({$columns}) VALUES ({$placeholders})";
            $this->db->query($query, $accreditationData);

            $accreditationId = $this->db->lastInsertId();

            // Add to history
            $this->addHistory($accreditationId, self::STATUS_PENDING, "Accreditation application submitted for {$accreditationLevel} level");

            $this->db->commit();

            return $this->getAccreditationById($accreditationId);
            
        } catch (PDOException $e) {
            $this->db->rollback();
            throw new Exception("Failed to submit accreditation application: " . $e->getMessage());
        }
    }

    /**
     * Get accreditation by ID
     * 
     * @param int $accreditationId
     * @return array|null
     */
    public function getAccreditationById(int $accreditationId): ?array
    {
        $query = "SELECT a.*, b.business_name, b.business_id as business_identifier 
                  FROM {$this->accreditationTable} a
                  INNER JOIN business_profiles b ON a.business_id = b.id
                  WHERE a.id = :id";
        
        $result = $this->db->fetchOne($query, ['id' => $accreditationId]);
        
        if ($result && $result['documents']) {
            $result['documents'] = json_decode($result['documents'], true);
        }
        
        return $result ?: null;
    }

    /**
     * Get business accreditation
     * 
     * @param int $businessId
     * @return array|null
     */
    public function getBusinessAccreditation(int $businessId): ?array
    {
        $query = "SELECT * FROM {$this->accreditationTable} 
                  WHERE business_id = :business_id 
                  AND status IN (:approved, :renewal_pending)
                  ORDER BY created_at DESC 
                  LIMIT 1";
        
        $result = $this->db->fetchOne($query, [
            'business_id' => $businessId,
            'approved' => self::STATUS_APPROVED,
            'renewal_pending' => self::STATUS_RENEWAL_PENDING
        ]);
        
        if ($result && $result['documents']) {
            $result['documents'] = json_decode($result['documents'], true);
        }
        
        return $result ?: null;
    }

    /**
     * Get pending application for business
     * 
     * @param int $businessId
     * @return array|null
     */
    public function getPendingApplication(int $businessId): ?array
    {
        $query = "SELECT * FROM {$this->accreditationTable} 
                  WHERE business_id = :business_id 
                  AND status = :status 
                  ORDER BY created_at DESC 
                  LIMIT 1";
        
        $result = $this->db->fetchOne($query, [
            'business_id' => $businessId,
            'status' => self::STATUS_PENDING
        ]);
        
        return $result ?: null;
    }

    /**
     * Update accreditation status
     * 
     * @param int $accreditationId
     * @param string $status
     * @param string $reviewNotes
     * @param int $reviewedBy
     * @return bool
     * @throws Exception
     */
    public function updateStatus(int $accreditationId, string $status, string $reviewNotes = '', int $reviewedBy = 0): bool
    {
        $validStatuses = [
            self::STATUS_APPROVED, self::STATUS_REJECTED, 
            self::STATUS_SUSPENDED, self::STATUS_EXPIRED
        ];

        if (!in_array($status, $validStatuses)) {
            throw new Exception("Invalid status: {$status}");
        }

        $accreditation = $this->getAccreditationById($accreditationId);
        if (!$accreditation) {
            throw new Exception("Accreditation record not found");
        }

        $updateData = [
            'status' => $status,
            'reviewed_by' => $reviewedBy,
            'reviewed_at' => date('Y-m-d H:i:s'),
            'review_notes' => $reviewNotes,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        // Set approval/expiry dates if approved
        if ($status === self::STATUS_APPROVED) {
            $updateData['approved_date'] = date('Y-m-d H:i:s');
            $updateData['expiry_date'] = date('Y-m-d H:i:s', strtotime('+1 year'));
        }

        try {
            $this->db->beginTransaction();

            // Update accreditation
            $setParts = [];
            foreach (array_keys($updateData) as $field) {
                $setParts[] = "{$field} = :{$field}";
            }

            $query = "UPDATE {$this->accreditationTable} SET " . implode(', ', $setParts) . " WHERE id = :id";
            $updateData['id'] = $accreditationId;
            $this->db->query($query, $updateData);

            // Update business profile accreditation level
            if ($status === self::STATUS_APPROVED) {
                $this->updateBusinessAccreditationLevel($accreditation['business_id'], $accreditation['accreditation_level']);
            } elseif (in_array($status, [self::STATUS_REJECTED, self::STATUS_EXPIRED, self::STATUS_SUSPENDED])) {
                $this->updateBusinessAccreditationLevel($accreditation['business_id'], 'none');
            }

            // Add to history
            $this->addHistory($accreditationId, $status, $reviewNotes ?: "Status updated to {$status}");

            $this->db->commit();

            return true;
            
        } catch (PDOException $e) {
            $this->db->rollback();
            throw new Exception("Failed to update accreditation status: " . $e->getMessage());
        }
    }

    /**
     * Renew accreditation
     * 
     * @param int $accreditationId
     * @return bool
     * @throws Exception
     */
    public function renewAccreditation(int $accreditationId): bool
    {
        $accreditation = $this->getAccreditationById($accreditationId);
        if (!$accreditation) {
            throw new Exception("Accreditation record not found");
        }

        if ($accreditation['status'] !== self::STATUS_APPROVED) {
            throw new Exception("Only approved accreditations can be renewed");
        }

        // Check if renewal is due (within 30 days of expiry)
        $expiryDate = new \DateTime($accreditation['expiry_date']);
        $now = new \DateTime();
        $daysUntilExpiry = $now->diff($expiryDate)->days;

        if ($daysUntilExpiry > 30) {
            throw new Exception("Accreditation can only be renewed within 30 days of expiry");
        }

        $updateData = [
            'status' => self::STATUS_RENEWAL_PENDING,
            'renewal_applied_date' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $setParts = [];
        foreach (array_keys($updateData) as $field) {
            $setParts[] = "{$field} = :{$field}";
        }

        $query = "UPDATE {$this->accreditationTable} SET " . implode(', ', $setParts) . " WHERE id = :id";
        $updateData['id'] = $accreditationId;
        $this->db->query($query, $updateData);

        // Add to history
        $this->addHistory($accreditationId, self::STATUS_RENEWAL_PENDING, "Accreditation renewal requested");

        return true;
    }

    /**
     * Get accreditation history
     * 
     * @param int $accreditationId
     * @return array
     */
    public function getAccreditationHistory(int $accreditationId): array
    {
        $query = "SELECT * FROM {$this->historyTable} 
                  WHERE accreditation_id = :accreditation_id 
                  ORDER BY created_at DESC";
        
        return $this->db->fetchAll($query, ['accreditation_id' => $accreditationId]);
    }

    /**
     * Get business accreditation history
     * 
     * @param int $businessId
     * @return array
     */
    public function getBusinessAccreditationHistory(int $businessId): array
    {
        $query = "SELECT a.*, h.status, h.notes, h.created_at as history_date
                  FROM {$this->accreditationTable} a
                  INNER JOIN {$this->historyTable} h ON a.id = h.accreditation_id
                  WHERE a.business_id = :business_id 
                  ORDER BY h.created_at DESC";
        
        return $this->db->fetchAll($query, ['business_id' => $businessId]);
    }

    /**
     * Get accreditations requiring review
     * 
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getAccreditationsForReview(int $limit = 20, int $offset = 0): array
    {
        $query = "SELECT a.*, b.business_name, b.business_id as business_identifier 
                  FROM {$this->accreditationTable} a
                  INNER JOIN business_profiles b ON a.business_id = b.id
                  WHERE a.status IN (:pending, :renewal_pending)
                  ORDER BY a.application_date ASC 
                  LIMIT :limit OFFSET :offset";
        
        return $this->db->fetchAll($query, [
            'pending' => self::STATUS_PENDING,
            'renewal_pending' => self::STATUS_RENEWAL_PENDING,
            'limit' => $limit,
            'offset' => $offset
        ]);
    }

    /**
     * Get expiring accreditations
     * 
     * @param int $daysThreshold
     * @return array
     */
    public function getExpiringAccreditations(int $daysThreshold = 30): array
    {
        $query = "SELECT a.*, b.business_name, b.email, b.phone 
                  FROM {$this->accreditationTable} a
                  INNER JOIN business_profiles b ON a.business_id = b.id
                  WHERE a.status = :approved 
                  AND a.expiry_date BETWEEN :start_date AND :end_date
                  ORDER BY a.expiry_date ASC";
        
        $startDate = date('Y-m-d');
        $endDate = date('Y-m-d', strtotime("+{$daysThreshold} days"));
        
        return $this->db->fetchAll($query, [
            'approved' => self::STATUS_APPROVED,
            'start_date' => $startDate,
            'end_date' => $endDate
        ]);
    }

    /**
     * Add entry to accreditation history
     * 
     * @param int $accreditationId
     * @param string $status
     * @param string $notes
     * @return bool
     */
    private function addHistory(int $accreditationId, string $status, string $notes = ''): bool
    {
        $historyData = [
            'accreditation_id' => $accreditationId,
            'status' => $status,
            'notes' => $notes,
            'created_at' => date('Y-m-d H:i:s')
        ];

        $columns = implode(', ', array_keys($historyData));
        $placeholders = ':' . implode(', :', array_keys($historyData));
        
        $query = "INSERT INTO {$this->historyTable} ({$columns}) VALUES ({$placeholders})";
        $this->db->query($query, $historyData);

        return true;
    }

    /**
     * Update business profile accreditation level
     * 
     * @param int $businessId
     * @param string $accreditationLevel
     * @return bool
     */
    private function updateBusinessAccreditationLevel(int $businessId, string $accreditationLevel): bool
    {
        $query = "UPDATE business_profiles SET accreditation_level = :level, updated_at = :updated_at WHERE id = :id";
        $this->db->query($query, [
            'level' => $accreditationLevel,
            'updated_at' => date('Y-m-d H:i:s'),
            'id' => $businessId
        ]);

        return true;
    }

    /**
     * Get accreditation statistics
     * 
     * @return array
     */
    public function getAccreditationStatistics(): array
    {
        $totalApplications = $this->db->fetchColumn(
            "SELECT COUNT(*) FROM {$this->accreditationTable}"
        );

        $pendingApplications = $this->db->fetchColumn(
            "SELECT COUNT(*) FROM {$this->accreditationTable} WHERE status = :status",
            ['status' => self::STATUS_PENDING]
        );

        $approvedApplications = $this->db->fetchColumn(
            "SELECT COUNT(*) FROM {$this->accreditationTable} WHERE status = :status",
            ['status' => self::STATUS_APPROVED]
        );

        $expiringSoon = $this->db->fetchColumn(
            "SELECT COUNT(*) FROM {$this->accreditationTable} 
             WHERE status = :status 
             AND expiry_date BETWEEN :start_date AND :end_date",
            [
                'status' => self::STATUS_APPROVED,
                'start_date' => date('Y-m-d'),
                'end_date' => date('Y-m-d', strtotime('+30 days'))
            ]
        );

        return [
            'total_applications' => (int)$totalApplications,
            'pending_applications' => (int)$pendingApplications,
            'approved_applications' => (int)$approvedApplications,
            'expiring_soon' => (int)$expiringSoon,
            'rejection_rate' => $totalApplications > 0 ? 
                round(($this->db->fetchColumn("SELECT COUNT(*) FROM {$this->accreditationTable} WHERE status = 'rejected'") / $totalApplications) * 100, 2) : 0
        ];
    }
}
