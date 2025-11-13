<?php
/**
 * P.I.M.P - Admin Model
 * Handles admin-specific data operations
 */

namespace PIMP\Models;

use PIMP\Services\Database\MySQLDatabase;

class AdminModel
{
    private $db;

    public function __construct(MySQLDatabase $db)
    {
        $this->db = $db;
    }

    /**
     * Get dashboard statistics
     * 
     * @return array
     */
    public function getDashboardStatistics(): array
    {
        return [
            'total_users' => $this->db->fetchColumn(
                "SELECT COUNT(*) FROM users WHERE deleted_at IS NULL", []
            ),
            'active_users' => $this->db->fetchColumn(
                "SELECT COUNT(*) FROM users WHERE status = 'active' AND deleted_at IS NULL", []
            ),
            'pending_users' => $this->db->fetchColumn(
                "SELECT COUNT(*) FROM users WHERE status = 'pending_verification' AND deleted_at IS NULL", []
            ),
            'total_businesses' => $this->db->fetchColumn(
                "SELECT COUNT(*) FROM businesses WHERE deleted_at IS NULL", []
            ),
            'active_businesses' => $this->db->fetchColumn(
                "SELECT COUNT(*) FROM businesses WHERE status = 'active' AND deleted_at IS NULL", []
            ),
            'pending_businesses' => $this->db->fetchColumn(
                "SELECT COUNT(*) FROM businesses WHERE status = 'pending' AND deleted_at IS NULL", []
            ),
            'total_reviews' => $this->db->fetchColumn(
                "SELECT COUNT(*) FROM business_reviews", []
            ),
            'pending_reviews' => $this->db->fetchColumn(
                "SELECT COUNT(*) FROM business_reviews WHERE status = 'pending'", []
            ),
            'approved_reviews' => $this->db->fetchColumn(
                "SELECT COUNT(*) FROM business_reviews WHERE status = 'approved'", []
            ),
            'total_complaints' => $this->db->fetchColumn(
                "SELECT COUNT(*) FROM complaints", []
            ),
            'open_complaints' => $this->db->fetchColumn(
                "SELECT COUNT(*) FROM complaints WHERE status = 'open'", []
            ),
            'new_users_today' => $this->db->fetchColumn(
                "SELECT COUNT(*) FROM users WHERE DATE(created_at) = CURDATE() AND deleted_at IS NULL", []
            ),
            'new_businesses_today' => $this->db->fetchColumn(
                "SELECT COUNT(*) FROM businesses WHERE DATE(created_at) = CURDATE() AND deleted_at IS NULL", []
            ),
            'new_reviews_today' => $this->db->fetchColumn(
                "SELECT COUNT(*) FROM business_reviews WHERE DATE(review_date) = CURDATE()", []
            )
        ];
    }

    /**
     * Get recent users
     * 
     * @param int $limit
     * @return array
     */
    public function getRecentUsers(int $limit = 10): array
    {
        return $this->db->fetchAll(
            "SELECT id, uuid, username, email, name_json, status, created_at
             FROM users
             WHERE deleted_at IS NULL
             ORDER BY created_at DESC
             LIMIT ?",
            [$limit]
        );
    }

    /**
     * Get recent businesses
     * 
     * @param int $limit
     * @return array
     */
    public function getRecentBusinesses(int $limit = 10): array
    {
        return $this->db->fetchAll(
            "SELECT id, uuid, legal_name, trading_name, status, verification_level, created_at
             FROM businesses
             WHERE deleted_at IS NULL
             ORDER BY created_at DESC
             LIMIT ?",
            [$limit]
        );
    }

    /**
     * Get recent reviews
     * 
     * @param int $limit
     * @return array
     */
    public function getRecentReviews(int $limit = 10): array
    {
        return $this->db->fetchAll(
            "SELECT br.id, br.uuid, br.title, br.rating, br.status, br.review_date,
                    b.legal_name as business_name, b.uuid as business_uuid,
                    u.username as reviewer_name
             FROM business_reviews br
             JOIN businesses b ON br.business_id = b.id
             JOIN users u ON br.reviewer_user_id = u.id
             ORDER BY br.review_date DESC
             LIMIT ?",
            [$limit]
        );
    }

    /**
     * Get pending approvals
     * 
     * @return array
     */
    public function getPendingApprovals(): array
    {
        return [
            'businesses' => $this->db->fetchColumn(
                "SELECT COUNT(*) FROM businesses WHERE status = 'pending' AND deleted_at IS NULL", []
            ),
            'reviews' => $this->db->fetchColumn(
                "SELECT COUNT(*) FROM business_reviews WHERE status = 'pending'", []
            ),
            'complaints' => $this->db->fetchColumn(
                "SELECT COUNT(*) FROM complaints WHERE status = 'open'", []
            ),
            'accreditations' => $this->db->fetchColumn(
                "SELECT COUNT(*) FROM accreditations WHERE verification_status = 'pending'", []
            )
        ];
    }

    /**
     * Get system health status
     * 
     * @return array
     */
    public function getSystemHealth(): array
    {
        return [
            'database' => $this->db->isConnected() ? 'healthy' : 'error',
            'database_size' => $this->getDatabaseSize(),
            'table_count' => $this->getTableCount(),
            'disk_space' => $this->getDiskSpace(),
            'php_version' => phpversion(),
            'memory_usage' => $this->getMemoryUsage()
        ];
    }

    /**
     * Get businesses with filters
     * 
     * @param int $page
     * @param int $perPage
     * @param array $filters
     * @return array
     */
    public function getBusinesses(int $page = 1, int $perPage = 20, array $filters = []): array
    {
        $offset = ($page - 1) * $perPage;
        $where = ["deleted_at IS NULL"];
        $params = [];

        if (!empty($filters['status'])) {
            $where[] = "status = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['search'])) {
            $where[] = "(legal_name LIKE ? OR trading_name LIKE ?)";
            $search = "%{$filters['search']}%";
            $params[] = $search;
            $params[] = $search;
        }

        $whereClause = implode(' AND ', $where);

        $businesses = $this->db->fetchAll(
            "SELECT * FROM businesses WHERE {$whereClause} 
             ORDER BY created_at DESC LIMIT ? OFFSET ?",
            array_merge($params, [$perPage, $offset])
        );

        $total = $this->db->fetchColumn(
            "SELECT COUNT(*) FROM businesses WHERE {$whereClause}",
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
     * Get reviews with filters
     * 
     * @param int $page
     * @param int $perPage
     * @param array $filters
     * @return array
     */
    public function getReviews(int $page = 1, int $perPage = 20, array $filters = []): array
    {
        $offset = ($page - 1) * $perPage;
        $where = ["1=1"];
        $params = [];

        if (!empty($filters['status'])) {
            $where[] = "br.status = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['business_id'])) {
            $where[] = "br.business_id = ?";
            $params[] = $filters['business_id'];
        }

        $whereClause = implode(' AND ', $where);

        $reviews = $this->db->fetchAll(
            "SELECT br.*, b.legal_name as business_name, u.username as reviewer_name
             FROM business_reviews br
             JOIN businesses b ON br.business_id = b.id
             JOIN users u ON br.reviewer_user_id = u.id
             WHERE {$whereClause}
             ORDER BY br.review_date DESC
             LIMIT ? OFFSET ?",
            array_merge($params, [$perPage, $offset])
        );

        $total = $this->db->fetchColumn(
            "SELECT COUNT(*) FROM business_reviews br WHERE {$whereClause}",
            $params
        );

        return [
            'data' => $reviews,
            'total' => (int)$total,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => ceil($total / $perPage)
        ];
    }

    /**
     * Update review status
     * 
     * @param int $reviewId
     * @param string $status
     * @param int $moderatorId
     * @param string|null $notes
     * @return bool
     */
    public function updateReviewStatus(int $reviewId, string $status, int $moderatorId, ?string $notes = null): bool
    {
        $this->db->query(
            "UPDATE business_reviews 
             SET status = ?, moderated_by = ?, moderated_at = NOW(), moderator_notes = ?
             WHERE id = ?",
            [$status, $moderatorId, $notes, $reviewId]
        );

        return true;
    }

    /**
     * Get all roles
     * 
     * @return array
     */
    public function getAllRoles(): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM roles ORDER BY hierarchy_level DESC",
            []
        );
    }

    /**
     * Update user roles
     * 
     * @param int $userId
     * @param array $roleIds
     * @param int $assignedBy
     * @return bool
     */
    public function updateUserRoles(int $userId, array $roleIds, int $assignedBy): bool
    {
        // Remove all existing roles
        $this->db->query(
            "DELETE FROM user_roles WHERE user_id = ?",
            [$userId]
        );

        // Add new roles
        foreach ($roleIds as $roleId) {
            $this->db->query(
                "INSERT INTO user_roles (user_id, role_id, assigned_by)
                 VALUES (?, ?, ?)",
                [$userId, $roleId, $assignedBy]
            );
        }

        return true;
    }

    /**
     * Get system settings
     * 
     * @return array
     */
    public function getSystemSettings(): array
    {
        $settings = $this->db->fetchAll(
            "SELECT * FROM system_settings ORDER BY category, setting_key",
            []
        );

        $grouped = [];
        foreach ($settings as $setting) {
            $category = $setting['category'];
            if (!isset($grouped[$category])) {
                $grouped[$category] = [];
            }
            $grouped[$category][] = $setting;
        }

        return $grouped;
    }

    /**
     * Get setting categories
     * 
     * @return array
     */
    public function getSettingCategories(): array
    {
        return $this->db->fetchAll(
            "SELECT DISTINCT category FROM system_settings ORDER BY category",
            []
        );
    }

    /**
     * Update system setting
     * 
     * @param string $key
     * @param mixed $value
     * @param int $updatedBy
     * @return bool
     */
    public function updateSystemSetting(string $key, $value, int $updatedBy): bool
    {
        $jsonValue = is_string($value) ? json_encode($value) : json_encode($value);

        $this->db->query(
            "UPDATE system_settings SET setting_value = ?, updated_by = ? WHERE setting_key = ?",
            [$jsonValue, $updatedBy, $key]
        );

        return true;
    }

    /**
     * Get database size
     * 
     * @return string
     */
    private function getDatabaseSize(): string
    {
        $result = $this->db->fetchOne(
            "SELECT 
                ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS size_mb
             FROM information_schema.TABLES
             WHERE table_schema = DATABASE()",
            []
        );

        return ($result['size_mb'] ?? 0) . ' MB';
    }

    /**
     * Get table count
     * 
     * @return int
     */
    private function getTableCount(): int
    {
        return (int)$this->db->fetchColumn(
            "SELECT COUNT(*) FROM information_schema.TABLES WHERE table_schema = DATABASE()",
            []
        );
    }

    /**
     * Get disk space information
     * 
     * @return array
     */
    private function getDiskSpace(): array
    {
        $total = disk_total_space('/');
        $free = disk_free_space('/');
        $used = $total - $free;

        return [
            'total' => $this->formatBytes($total),
            'used' => $this->formatBytes($used),
            'free' => $this->formatBytes($free),
            'percentage' => round(($used / $total) * 100, 2)
        ];
    }

    /**
     * Get memory usage
     * 
     * @return array
     */
    private function getMemoryUsage(): array
    {
        $current = memory_get_usage(true);
        $peak = memory_get_peak_usage(true);

        return [
            'current' => $this->formatBytes($current),
            'peak' => $this->formatBytes($peak)
        ];
    }

    /**
     * Format bytes to human readable
     * 
     * @param int $bytes
     * @return string
     */
    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i = 0;

        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }
}
