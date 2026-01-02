<?php
/**
 * P.I.M.P - Business Model
 * Handles business data operations
 */

namespace PIMP\Models;

use PIMP\Services\Database\MySQLDatabase;
use Exception;

class BusinessModel
{
    private $db;
    private $table = 'businesses';

    public function __construct(MySQLDatabase $db)
    {
        $this->db = $db;
    }

    /**
     * Create a new business
     * 
     * @param array $data
     * @return int Business ID
     */
    public function create(array $data): int
    {
        $query = "INSERT INTO {$this->table} 
                  (legal_name, trading_name, description, business_type, industry_sector, 
                   category_id, contact_info_json, owner_user_id, status)
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $this->db->query($query, [
            $data['legal_name'],
            $data['trading_name'] ?? null,
            $data['description'] ?? null,
            $data['business_type'] ?? 'corporation',
            $data['industry_sector'] ?? null,
            $data['category_id'] ?? null,
            $data['contact_info_json'],
            $data['owner_user_id'],
            $data['status'] ?? 'pending'
        ]);

        return (int)$this->db->lastInsertId();
    }

    /**
     * Find business by ID
     * 
     * @param int $id
     * @return array|null
     */
    public function findById(int $id): ?array
    {
        return $this->db->fetchOne(
            "SELECT * FROM {$this->table} WHERE id = ? AND deleted_at IS NULL",
            [$id]
        );
    }

    /**
     * Find business by UUID
     * 
     * @param string $uuid
     * @return array|null
     */
    public function findByUuid(string $uuid): ?array
    {
        return $this->db->fetchOne(
            "SELECT * FROM {$this->table} WHERE uuid = ? AND deleted_at IS NULL",
            [$uuid]
        );
    }

    /**
     * Get businesses by owner ID
     * 
     * @param int $ownerId
     * @return array
     */
    public function getByOwnerId(int $ownerId): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM {$this->table} WHERE owner_user_id = ? AND deleted_at IS NULL ORDER BY created_at DESC",
            [$ownerId]
        );
    }

    /**
     * Update business
     * 
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update(int $id, array $data): bool
    {
        $fields = [];
        $values = [];

        foreach ($data as $key => $value) {
            $fields[] = "{$key} = ?";
            $values[] = $value;
        }

        $values[] = $id;

        $query = "UPDATE {$this->table} SET " . implode(', ', $fields) . " WHERE id = ?";
        $this->db->query($query, $values);

        return true;
    }

    /**
     * Delete business (soft delete)
     * 
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        $this->db->query(
            "UPDATE {$this->table} SET deleted_at = NOW() WHERE id = ?",
            [$id]
        );

        return true;
    }

    /**
     * Get business reviews
     * 
     * @param int $businessId
     * @param int $page
     * @param int $perPage
     * @return array
     */
    public function getReviews(int $businessId, int $page = 1, int $perPage = 10): array
    {
        $offset = ($page - 1) * $perPage;

        $reviews = $this->db->fetchAll(
            "SELECT br.*, u.username, u.name_json 
             FROM business_reviews br
             JOIN users u ON br.reviewer_user_id = u.id
             WHERE br.business_id = ? AND br.status = 'approved'
             ORDER BY br.review_date DESC
             LIMIT ? OFFSET ?",
            [$businessId, $perPage, $offset]
        );

        $total = $this->db->fetchColumn(
            "SELECT COUNT(*) FROM business_reviews WHERE business_id = ? AND status = 'approved'",
            [$businessId]
        );

        return [
            'data' => $reviews,
            'total' => (int)$total,
            'page' => $page,
            'per_page' => $perPage
        ];
    }

    /**
     * Get business statistics
     * 
     * @param int $businessId
     * @return array
     */
    public function getStatistics(int $businessId): array
    {
        return [
            'total_reviews' => $this->db->fetchColumn(
                "SELECT COUNT(*) FROM business_reviews WHERE business_id = ?",
                [$businessId]
            ),
            'average_rating' => $this->db->fetchColumn(
                "SELECT AVG(rating) FROM business_reviews WHERE business_id = ? AND status = 'approved'",
                [$businessId]
            ),
            'total_complaints' => $this->db->fetchColumn(
                "SELECT COUNT(*) FROM complaints WHERE business_id = ?",
                [$businessId]
            ),
            'total_branches' => $this->db->fetchColumn(
                "SELECT COUNT(*) FROM business_branches WHERE business_id = ?",
                [$businessId]
            )
        ];
    }

    /**
     * Update business ratings
     * 
     * @param int $businessId
     * @return bool
     */
    public function updateRatings(int $businessId): bool
    {
        $stats = $this->db->fetchOne(
            "SELECT COUNT(*) as total, AVG(rating) as average
             FROM business_reviews 
             WHERE business_id = ? AND status = 'approved'",
            [$businessId]
        );

        if ($stats) {
            $this->db->query(
                "UPDATE {$this->table} 
                 SET total_reviews = ?, average_rating = ?
                 WHERE id = ?",
                [$stats['total'], round($stats['average'], 1), $businessId]
            );
        }

        return true;
    }

    /**
     * Get all businesses (paginated)
     * 
     * @param int $page
     * @param int $perPage
     * @param array $filters
     * @return array
     */
    public function getAll(int $page = 1, int $perPage = 20, array $filters = []): array
    {
        $offset = ($page - 1) * $perPage;
        $where = ["deleted_at IS NULL"];
        $params = [];

        if (!empty($filters['status'])) {
            $where[] = "status = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['category_id'])) {
            $where[] = "category_id = ?";
            $params[] = $filters['category_id'];
        }

        if (!empty($filters['search'])) {
            $where[] = "(legal_name LIKE ? OR trading_name LIKE ?)";
            $search = "%{$filters['search']}%";
            $params[] = $search;
            $params[] = $search;
        }

        $whereClause = implode(' AND ', $where);

        $businesses = $this->db->fetchAll(
            "SELECT * FROM {$this->table} WHERE {$whereClause} 
             ORDER BY created_at DESC LIMIT ? OFFSET ?",
            array_merge($params, [$perPage, $offset])
        );

        $total = $this->db->fetchColumn(
            "SELECT COUNT(*) FROM {$this->table} WHERE {$whereClause}",
            $params
        );

        return [
            'data' => $businesses,
            'total' => (int)$total,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => ceil($total / $perPage)
        ];
    }

    /**
     * Search businesses
     * 
     * @param string $query
     * @param array $filters
     * @return array
     */
    public function search(string $query, array $filters = []): array
    {
        $where = ["deleted_at IS NULL", "status = 'active'"];
        $params = [];

        if (!empty($query)) {
            $where[] = "(legal_name LIKE ? OR trading_name LIKE ? OR description LIKE ?)";
            $search = "%{$query}%";
            $params[] = $search;
            $params[] = $search;
            $params[] = $search;
        }

        if (!empty($filters['category_id'])) {
            $where[] = "category_id = ?";
            $params[] = $filters['category_id'];
        }

        if (!empty($filters['min_rating'])) {
            $where[] = "average_rating >= ?";
            $params[] = $filters['min_rating'];
        }

        $whereClause = implode(' AND ', $where);

        return $this->db->fetchAll(
            "SELECT * FROM {$this->table} 
             WHERE {$whereClause} 
             ORDER BY trust_score DESC, average_rating DESC 
             LIMIT 50",
            $params
        );
    }

    /**
     * Verify business
     * 
     * @param int $businessId
     * @param int $verifiedBy
     * @param string $verificationLevel
     * @return bool
     */
    public function verify(int $businessId, int $verifiedBy, string $verificationLevel = 'basic'): bool
    {
        $this->db->query(
            "UPDATE {$this->table} 
             SET status = 'verified', 
                 verification_level = ?,
                 verified_at = NOW(),
                 verified_by = ?
             WHERE id = ?",
            [$verificationLevel, $verifiedBy, $businessId]
        );

        return true;
    }

    /**
     * Get business branches
     * 
     * @param int $businessId
     * @return array
     */
    public function getBranches(int $businessId): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM business_branches WHERE business_id = ? ORDER BY is_headquarters DESC, branch_name ASC",
            [$businessId]
        );
    }

    /**
     * Get business accreditations
     * 
     * @param int $businessId
     * @return array
     */
    public function getAccreditations(int $businessId): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM accreditations WHERE business_id = ? ORDER BY issue_date DESC",
            [$businessId]
        );
    }
}
