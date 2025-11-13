<?php
/**
 * P.I.M.P - Admin Dashboard Model
 * Handles administrative operations, analytics, and system monitoring
 */

namespace PIMP\Models\Admin;

use PIMP\Services\Database\MySQLDatabase;
use PDOException;
use Exception;

class AdminDashboard
{
    /**
     * @var MySQLDatabase Database instance
     */
    private $db;

    /**
     * @var string System logs table
     */
    private $systemLogsTable = 'system_logs';

    /**
     * @var string Admin activities table
     */
    private $adminActivitiesTable = 'admin_activities';

    /**
     * @var string System settings table
     */
    private $systemSettingsTable = 'system_settings';

    /**
     * Log levels
     */
    const LOG_INFO = 'info';
    const LOG_WARNING = 'warning';
    const LOG_ERROR = 'error';
    const LOG_CRITICAL = 'critical';

    /**
     * Activity types
     */
    const ACTIVITY_USER_MANAGEMENT = 'user_management';
    const ACTIVITY_BUSINESS_MANAGEMENT = 'business_management';
    const ACTIVITY_COMPLAINT_MODERATION = 'complaint_moderation';
    const ACTIVITY_REVIEW_MODERATION = 'review_moderation';
    const ACTIVITY_SYSTEM_SETTINGS = 'system_settings';
    const ACTIVITY_CONTENT_MODERATION = 'content_moderation';

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
     * Get system overview statistics
     * 
     * @return array
     */
    public function getSystemOverview(): array
    {
        // User statistics
        $totalUsers = $this->db->fetchColumn("SELECT COUNT(*) FROM users") ?: 0;
        $newUsersToday = $this->db->fetchColumn(
            "SELECT COUNT(*) FROM users WHERE DATE(created_at) = CURDATE()"
        ) ?: 0;
        $verifiedUsers = $this->db->fetchColumn(
            "SELECT COUNT(*) FROM users WHERE verification_level = 'full'"
        ) ?: 0;

        // Business statistics
        $totalBusinesses = $this->db->fetchColumn("SELECT COUNT(*) FROM business_profiles") ?: 0;
        $pendingBusinesses = $this->db->fetchColumn(
            "SELECT COUNT(*) FROM business_profiles WHERE status = 'pending'"
        ) ?: 0;
        $accreditedBusinesses = $this->db->fetchColumn(
            "SELECT COUNT(*) FROM business_profiles WHERE accreditation_level != 'none'"
        ) ?: 0;

        // Complaint statistics
        $totalComplaints = $this->db->fetchColumn("SELECT COUNT(*) FROM complaints") ?: 0;
        $openComplaints = $this->db->fetchColumn(
            "SELECT COUNT(*) FROM complaints WHERE status IN ('new', 'in_progress', 'under_review')"
        ) ?: 0;
        $resolvedComplaints = $this->db->fetchColumn(
            "SELECT COUNT(*) FROM complaints WHERE status = 'resolved'"
        ) ?: 0;

        // Review statistics
        $totalReviews = $this->db->fetchColumn("SELECT COUNT(*) FROM business_reviews") ?: 0;
        $pendingReviews = $this->db->fetchColumn(
            "SELECT COUNT(*) FROM business_reviews WHERE status = 'pending'"
        ) ?: 0;
        $verifiedReviews = $this->db->fetchColumn(
            "SELECT COUNT(*) FROM business_reviews WHERE is_verified = TRUE"
        ) ?: 0;

        // Revenue statistics (if applicable)
        $totalRevenue = $this->db->fetchColumn(
            "SELECT SUM(monthly_price) FROM business_subscriptions WHERE status = 'active'"
        ) ?: 0;

        return [
            'users' => [
                'total' => (int)$totalUsers,
                'new_today' => (int)$newUsersToday,
                'verified' => (int)$verifiedUsers,
                'growth_rate' => $this->calculateUserGrowthRate()
            ],
            'businesses' => [
                'total' => (int)$totalBusinesses,
                'pending_approval' => (int)$pendingBusinesses,
                'accredited' => (int)$accreditedBusinesses,
                'growth_rate' => $this->calculateBusinessGrowthRate()
            ],
            'complaints' => [
                'total' => (int)$totalComplaints,
                'open' => (int)$openComplaints,
                'resolved' => (int)$resolvedComplaints,
                'resolution_rate' => $totalComplaints > 0 ? 
                    round(($resolvedComplaints / $totalComplaints) * 100, 2) : 0
            ],
            'reviews' => [
                'total' => (int)$totalReviews,
                'pending_moderation' => (int)$pendingReviews,
                'verified' => (int)$verifiedReviews,
                'average_rating' => $this->db->fetchColumn(
                    "SELECT AVG(rating) FROM business_reviews WHERE status = 'approved'"
                ) ?: 0
            ],
            'revenue' => [
                'monthly_recurring' => (float)$totalRevenue,
                'projected_annual' => (float)$totalRevenue * 12
            ],
            'system_health' => $this->getSystemHealthStatus()
        ];
    }

    /**
     * Get business onboarding workflow data
     * 
     * @param array $filters
     * @param int $page
     * @param int $perPage
     * @return array
     */
    public function getBusinessOnboardingWorkflow(array $filters = [], int $page = 1, int $perPage = 20): array
    {
        $whereConditions = ["1=1"];
        $params = [];

        if (!empty($filters['status'])) {
            $whereConditions[] = "bp.status = :status";
            $params['status'] = $filters['status'];
        }

        if (!empty($filters['accreditation_level'])) {
            $whereConditions[] = "bp.accreditation_level = :accreditation_level";
            $params['accreditation_level'] = $filters['accreditation_level'];
        }

        if (!empty($filters['date_from'])) {
            $whereConditions[] = "bp.created_at >= :date_from";
            $params['date_from'] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $whereConditions[] = "bp.created_at <= :date_to";
            $params['date_to'] = $filters['date_to'];
        }

        $whereClause = implode(' AND ', $whereConditions);
        $offset = ($page - 1) * $perPage;

        $query = "SELECT bp.*, u.first_name as owner_first_name, u.last_name as owner_last_name,
                         u.email as owner_email, u.verification_level as owner_verification,
                         (SELECT COUNT(*) FROM business_documents WHERE business_id = bp.id) as document_count,
                         (SELECT COUNT(*) FROM business_locations WHERE business_id = bp.id) as location_count
                  FROM business_profiles bp
                  INNER JOIN users u ON bp.owner_id = u.id
                  WHERE {$whereClause}
                  ORDER BY bp.created_at DESC
                  LIMIT :limit OFFSET :offset";

        $params['limit'] = $perPage;
        $params['offset'] = $offset;

        $businesses = $this->db->fetchAll($query, $params);

        // Get total count for pagination
        $countQuery = "SELECT COUNT(*) as total FROM business_profiles bp WHERE {$whereClause}";
        $total = $this->db->fetchColumn($countQuery, $params);

        return [
            'businesses' => $businesses,
            'pagination' => [
                'page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'total_pages' => ceil($total / $perPage)
            ]
        ];
    }

    /**
     * Get accreditation review queue
     * 
     * @param array $filters
     * @param int $page
     * @param int $perPage
     * @return array
     */
    public function getAccreditationReviewQueue(array $filters = [], int $page = 1, int $perPage = 20): array
    {
        $whereConditions = ["ba.status IN ('pending', 'renewal_pending')"];
        $params = [];

        if (!empty($filters['accreditation_level'])) {
            $whereConditions[] = "ba.accreditation_level = :accreditation_level";
            $params['accreditation_level'] = $filters['accreditation_level'];
        }

        if (!empty($filters['date_from'])) {
            $whereConditions[] = "ba.application_date >= :date_from";
            $params['date_from'] = $filters['date_from'];
        }

        $whereClause = implode(' AND ', $whereConditions);
        $offset = ($page - 1) * $perPage;

        $query = "SELECT ba.*, bp.business_name, bp.business_id, bp.city, bp.state,
                         u.first_name as owner_first_name, u.last_name as owner_last_name,
                         (SELECT COUNT(*) FROM business_documents 
                          WHERE business_id = ba.business_id AND status = 'approved') as approved_documents,
                         (SELECT COUNT(*) FROM business_documents 
                          WHERE business_id = ba.business_id) as total_documents
                  FROM business_accreditations ba
                  INNER JOIN business_profiles bp ON ba.business_id = bp.id
                  INNER JOIN users u ON bp.owner_id = u.id
                  WHERE {$whereClause}
                  ORDER BY 
                    CASE ba.priority 
                        WHEN 'urgent' THEN 1
                        WHEN 'high' THEN 2
                        WHEN 'medium' THEN 3
                        WHEN 'low' THEN 4
                    END,
                    ba.application_date ASC
                  LIMIT :limit OFFSET :offset";

        $params['limit'] = $perPage;
        $params['offset'] = $offset;

        $accreditations = $this->db->fetchAll($query, $params);

        // Get total count for pagination
        $countQuery = "SELECT COUNT(*) as total FROM business_accreditations ba WHERE {$whereClause}";
        $total = $this->db->fetchColumn($countQuery, $params);

        return [
            'accreditations' => $accreditations,
            'pagination' => [
                'page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'total_pages' => ceil($total / $perPage)
            ]
        ];
    }

    /**
     * Get complaint moderation queue
     * 
     * @param array $filters
     * @param int $page
     * @param int $perPage
     * @return array
     */
    public function getComplaintModerationQueue(array $filters = [], int $page = 1, int $perPage = 20): array
    {
        $whereConditions = ["c.status IN ('new', 'under_review', 'escalated')"];
        $params = [];

        if (!empty($filters['priority'])) {
            $whereConditions[] = "c.priority = :priority";
            $params['priority'] = $filters['priority'];
        }

        if (!empty($filters['complaint_type'])) {
            $whereConditions[] = "c.complaint_type = :complaint_type";
            $params['complaint_type'] = $filters['complaint_type'];
        }

        if (!empty($filters['date_from'])) {
            $whereConditions[] = "c.created_at >= :date_from";
            $params['date_from'] = $filters['date_from'];
        }

        $whereClause = implode(' AND ', $whereConditions);
        $offset = ($page - 1) * $perPage;

        $query = "SELECT c.*, bp.business_name, u.first_name as user_first_name, u.last_name as user_last_name,
                         (SELECT COUNT(*) FROM complaint_threads WHERE complaint_id = c.id) as message_count,
                         (SELECT COUNT(*) FROM complaint_evidence WHERE complaint_id = c.id) as evidence_count
                  FROM complaints c
                  INNER JOIN business_profiles bp ON c.business_id = bp.id
                  INNER JOIN users u ON c.user_id = u.id
                  WHERE {$whereClause}
                  ORDER BY 
                    CASE c.priority 
                        WHEN 'urgent' THEN 1
                        WHEN 'high' THEN 2
                        WHEN 'medium' THEN 3
                        WHEN 'low' THEN 4
                    END,
                    c.created_at ASC
                  LIMIT :limit OFFSET :offset";

        $params['limit'] = $perPage;
        $params['offset'] = $offset;

        $complaints = $this->db->fetchAll($query, $params);

        // Get total count for pagination
        $countQuery = "SELECT COUNT(*) as total FROM complaints c WHERE {$whereClause}";
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
     * Get user management data
     * 
     * @param array $filters
     * @param int $page
     * @param int $perPage
     * @return array
     */
    public function getUserManagementData(array $filters = [], int $page = 1, int $perPage = 20): array
    {
        $whereConditions = ["1=1"];
        $params = [];

        if (!empty($filters['user_type'])) {
            $whereConditions[] = "u.user_type = :user_type";
            $params['user_type'] = $filters['user_type'];
        }

        if (!empty($filters['status'])) {
            $whereConditions[] = "u.status = :status";
            $params['status'] = $filters['status'];
        }

        if (!empty($filters['verification_level'])) {
            $whereConditions[] = "u.verification_level = :verification_level";
            $params['verification_level'] = $filters['verification_level'];
        }

        if (!empty($filters['search'])) {
            $whereConditions[] = "(u.first_name LIKE :search OR u.last_name LIKE :search OR u.email LIKE :search)";
            $params['search'] = '%' . $filters['search'] . '%';
        }

        $whereClause = implode(' AND ', $whereConditions);
        $offset = ($page - 1) * $perPage;

        $query = "SELECT u.*,
                         (SELECT COUNT(*) FROM business_profiles WHERE owner_id = u.id) as owned_businesses,
                         (SELECT COUNT(*) FROM business_reviews WHERE user_id = u.id) as reviews_written,
                         (SELECT COUNT(*) FROM complaints WHERE user_id = u.id) as complaints_filed
                  FROM users u
                  WHERE {$whereClause}
                  ORDER BY u.created_at DESC
                  LIMIT :limit OFFSET :offset";

        $params['limit'] = $perPage;
        $params['offset'] = $offset;

        $users = $this->db->fetchAll($query, $params);

        // Get total count for pagination
        $countQuery = "SELECT COUNT(*) as total FROM users u WHERE {$whereClause}";
        $total = $this->db->fetchColumn($countQuery, $params);

        return [
            'users' => $users,
            'pagination' => [
                'page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'total_pages' => ceil($total / $perPage)
            ]
        ];
    }

    /**
     * Get analytics reporting data
     * 
     * @param string $period
     * @param array $filters
     * @return array
     */
    public function getAnalyticsReporting(string $period = '30days', array $filters = []): array
    {
        $dateRange = $this->getDateRange($period);
        
        return [
            'user_analytics' => $this->getUserAnalytics($dateRange['start'], $dateRange['end']),
            'business_analytics' => $this->getBusinessAnalytics($dateRange['start'], $dateRange['end']),
            'complaint_analytics' => $this->getComplaintAnalytics($dateRange['start'], $dateRange['end']),
            'review_analytics' => $this->getReviewAnalytics($dateRange['start'], $dateRange['end']),
            'revenue_analytics' => $this->getRevenueAnalytics($dateRange['start'], $dateRange['end']),
            'period' => $period,
            'date_range' => $dateRange
        ];
    }

    /**
     * Get system monitoring data
     * 
     * @return array
     */
    public function getSystemMonitoring(): array
    {
        return [
            'database' => $this->getDatabaseMetrics(),
            'server' => $this->getServerMetrics(),
            'performance' => $this->getPerformanceMetrics(),
            'errors' => $this->getErrorMetrics(),
            'backups' => $this->getBackupStatus()
        ];
    }

    /**
     * Log admin activity
     * 
     * @param int $adminId
     * @param string $activityType
     * @param string $description
     * @param array $metadata
     * @return bool
     */
    public function logAdminActivity(int $adminId, string $activityType, string $description, array $metadata = []): bool
    {
        $activityData = [
            'admin_id' => $adminId,
            'activity_type' => $activityType,
            'description' => $description,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            'metadata' => !empty($metadata) ? json_encode($metadata) : null,
            'created_at' => date('Y-m-d H:i:s')
        ];

        $columns = implode(', ', array_keys($activityData));
        $placeholders = ':' . implode(', :', array_keys($activityData));
        
        $query = "INSERT INTO {$this->adminActivitiesTable} ({$columns}) VALUES ({$placeholders})";
        $this->db->query($query, $activityData);

        return true;
    }

    /**
     * Get admin activity log
     * 
     * @param array $filters
     * @param int $page
     * @param int $perPage
     * @return array
     */
    public function getAdminActivityLog(array $filters = [], int $page = 1, int $perPage = 20): array
    {
        $whereConditions = ["1=1"];
        $params = [];

        if (!empty($filters['admin_id'])) {
            $whereConditions[] = "aa.admin_id = :admin_id";
            $params['admin_id'] = $filters['admin_id'];
        }

        if (!empty($filters['activity_type'])) {
            $whereConditions[] = "aa.activity_type = :activity_type";
            $params['activity_type'] = $filters['activity_type'];
        }

        if (!empty($filters['date_from'])) {
            $whereConditions[] = "aa.created_at >= :date_from";
            $params['date_from'] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $whereConditions[] = "aa.created_at <= :date_to";
            $params['date_to'] = $filters['date_to'];
        }

        $whereClause = implode(' AND ', $whereConditions);
        $offset = ($page - 1) * $perPage;

        $query = "SELECT aa.*, u.first_name, u.last_name, u.email
                  FROM {$this->adminActivitiesTable} aa
                  INNER JOIN users u ON aa.admin_id = u.id
                  WHERE {$whereClause}
                  ORDER BY aa.created_at DESC
                  LIMIT :limit OFFSET :offset";

        $params['limit'] = $perPage;
        $params['offset'] = $offset;

        $activities = $this->db->fetchAll($query, $params);

        // Get total count for pagination
        $countQuery = "SELECT COUNT(*) as total FROM {$this->adminActivitiesTable} aa WHERE {$whereClause}";
        $total = $this->db->fetchColumn($countQuery, $params);

        return [
            'activities' => $activities,
            'pagination' => [
                'page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'total_pages' => ceil($total / $perPage)
            ]
        ];
    }

    /**
     * Get system settings
     * 
     * @return array
     */
    public function getSystemSettings(): array
    {
        $settings = $this->db->fetchAll("SELECT * FROM {$this->systemSettingsTable}");
        
        $formatted = [];
        foreach ($settings as $setting) {
            $formatted[$setting['setting_key']] = $setting['setting_value'];
        }
        
        return $formatted;
    }

    /**
     * Update system setting
     * 
     * @param string $key
     * @param string $value
     * @param int $updatedBy
     * @return bool
     */
    public function updateSystemSetting(string $key, string $value, int $updatedBy): bool
    {
        // Check if setting exists
        $existing = $this->db->fetchOne(
            "SELECT id FROM {$this->systemSettingsTable} WHERE setting_key = :key",
            ['key' => $key]
        );

        if ($existing) {
            $query = "UPDATE {$this->systemSettingsTable} SET setting_value = :value, 
                      updated_by = :updated_by, updated_at = :updated_at WHERE setting_key = :key";
        } else {
            $query = "INSERT INTO {$this->systemSettingsTable} (setting_key, setting_value, created_by, updated_by, created_at, updated_at) 
                      VALUES (:key, :value, :created_by, :updated_by, :created_at, :updated_at)";
        }

        $this->db->query($query, [
            'key' => $key,
            'value' => $value,
            'created_by' => $updatedBy,
            'updated_by' => $updatedBy,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        return true;
    }

    // Private helper methods

    private function calculateUserGrowthRate(): float
    {
        $currentMonth = $this->db->fetchColumn(
            "SELECT COUNT(*) FROM users WHERE MONTH(created_at) = MONTH(CURRENT_DATE()) AND YEAR(created_at) = YEAR(CURRENT_DATE())"
        ) ?: 0;

        $previousMonth = $this->db->fetchColumn(
            "SELECT COUNT(*) FROM users WHERE MONTH(created_at) = MONTH(CURRENT_DATE() - INTERVAL 1 MONTH) AND YEAR(created_at) = YEAR(CURRENT_DATE() - INTERVAL 1 MONTH)"
        ) ?: 0;

        return $previousMonth > 0 ? round((($currentMonth - $previousMonth) / $previousMonth) * 100, 2) : 0;
    }

    private function calculateBusinessGrowthRate(): float
    {
        $currentMonth = $this->db->fetchColumn(
            "SELECT COUNT(*) FROM business_profiles WHERE MONTH(created_at) = MONTH(CURRENT_DATE()) AND YEAR(created_at) = YEAR(CURRENT_DATE())"
        ) ?: 0;

        $previousMonth = $this->db->fetchColumn(
            "SELECT COUNT(*) FROM business_profiles WHERE MONTH(created_at) = MONTH(CURRENT_DATE() - INTERVAL 1 MONTH) AND YEAR(created_at) = YEAR(CURRENT_DATE() - INTERVAL 1 MONTH)"
        ) ?: 0;

        return $previousMonth > 0 ? round((($currentMonth - $previousMonth) / $previousMonth) * 100, 2) : 0;
    }

    private function getSystemHealthStatus(): array
    {
        return [
            'database' => $this->checkDatabaseHealth(),
            'storage' => $this->checkStorageHealth(),
            'performance' => $this->checkPerformanceHealth(),
            'security' => $this->checkSecurityHealth()
        ];
    }

    private function checkDatabaseHealth(): array
    {
        // Check database connection and performance
        $start = microtime(true);
        $this->db->fetchColumn("SELECT 1");
        $responseTime = round((microtime(true) - $start) * 1000, 2);

        $tableCount = $this->db->fetchColumn("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE()") ?: 0;

        return [
            'status' => $responseTime < 100 ? 'healthy' : ($responseTime < 500 ? 'warning' : 'critical'),
            'response_time_ms' => $responseTime,
            'table_count' => $tableCount,
            'connection' => 'connected'
        ];
    }

    private function checkStorageHealth(): array
    {
        // This would typically check disk space, but for simplicity:
        $freeSpace = disk_free_space(__DIR__);
        $totalSpace = disk_total_space(__DIR__);
        $usagePercent = $totalSpace > 0 ? round((($totalSpace - $freeSpace) / $totalSpace) * 100, 2) : 0;

        return [
            'status' => $usagePercent < 80 ? 'healthy' : ($usagePercent < 90 ? 'warning' : 'critical'),
            'usage_percent' => $usagePercent,
            'free_space_gb' => round($freeSpace / (1024 * 1024 * 1024), 2),
            'total_space_gb' => round($totalSpace / (1024 * 1024 * 1024), 2)
        ];
    }

    private function checkPerformanceHealth(): array
    {
        // Simulate performance checks
        $activeUsers = $this->db->fetchColumn(
            "SELECT COUNT(DISTINCT user_id) FROM user_sessions WHERE expires_at > NOW()"
        ) ?: 0;

        $avgResponseTime = 45; // This would come from actual monitoring

        return [
            'status' => $avgResponseTime < 100 ? 'healthy' : ($avgResponseTime < 500 ? 'warning' : 'critical'),
            'active_users' => $activeUsers,
            'avg_response_time_ms' => $avgResponseTime,
            'server_load' => sys_getloadavg()[0] ?? 0
        ];
    }

    private function checkSecurityHealth(): array
    {
        // Check for security issues
        $failedLogins = $this->db->fetchColumn(
            "SELECT COUNT(*) FROM login_attempts WHERE attempt_time > DATE_SUB(NOW(), INTERVAL 1 HOUR)"
        ) ?: 0;

        $expiredCertificates = 0; // This would check SSL certificates

        return [
            'status' => $failedLogins < 10 ? 'healthy' : ($failedLogins < 50 ? 'warning' : 'critical'),
            'failed_logins_last_hour' => $failedLogins,
            'expired_certificates' => $expiredCertificates,
            'security_updates' => 'current' // This would check for system updates
        ];
    }

    private function getDateRange(string $period): array
    {
        $endDate = date('Y-m-d H:i:s');
        
        switch ($period) {
            case '7days':
                $startDate = date('Y-m-d H:i:s', strtotime('-7 days'));
                break;
            case '30days':
                $startDate = date('Y-m-d H:i:s', strtotime('-30 days'));
                break;
            case '90days':
                $startDate = date('Y-m-d H:i:s', strtotime('-90 days'));
                break;
            case '1year':
                $startDate = date('Y-m-d H:i:s', strtotime('-1 year'));
                break;
            default:
                $startDate = date('Y-m-d H:i:s', strtotime('-30 days'));
        }

        return ['start' => $startDate, 'end' => $endDate];
    }

    private function getUserAnalytics(string $startDate, string $endDate): array
    {
        return [
            'new_users' => $this->db->fetchColumn(
                "SELECT COUNT(*) FROM users WHERE created_at BETWEEN :start AND :end",
                ['start' => $startDate, 'end' => $endDate]
            ) ?: 0,
            'active_users' => $this->db->fetchColumn(
                "SELECT COUNT(DISTINCT user_id) FROM user_sessions WHERE created_at BETWEEN :start AND :end",
                ['start' => $startDate, 'end' => $endDate]
            ) ?: 0,
            'user_retention' => $this->calculateUserRetention($startDate, $endDate)
        ];
    }

    private function getBusinessAnalytics(string $startDate, string $endDate): array
    {
        return [
            'new_businesses' => $this->db->fetchColumn(
                "SELECT COUNT(*) FROM business_profiles WHERE created_at BETWEEN :start AND :end",
                ['start' => $startDate, 'end' => $endDate]
            ) ?: 0,
            'accreditation_applications' => $this->db->fetchColumn(
                "SELECT COUNT(*) FROM business_accreditations WHERE application_date BETWEEN :start AND :end",
                ['start' => $startDate, 'end' => $endDate]
            ) ?: 0
        ];
    }

    private function getComplaintAnalytics(string $startDate, string $endDate): array
    {
        return [
            'new_complaints' => $this->db->fetchColumn(
                "SELECT COUNT(*) FROM complaints WHERE created_at BETWEEN :start AND :end",
                ['start' => $startDate, 'end' => $endDate]
            ) ?: 0,
            'avg_resolution_time' => $this->db->fetchColumn(
                "SELECT AVG(TIMESTAMPDIFF(HOUR, created_at, resolved_at)) 
                 FROM complaints 
                 WHERE resolved_at BETWEEN :start AND :end",
                ['start' => $startDate, 'end' => $endDate]
            ) ?: 0
        ];
    }

    private function getReviewAnalytics(string $startDate, string $endDate): array
    {
        return [
            'new_reviews' => $this->db->fetchColumn(
                "SELECT COUNT(*) FROM business_reviews WHERE created_at BETWEEN :start AND :end",
                ['start' => $startDate, 'end' => $endDate]
            ) ?: 0,
            'avg_rating' => $this->db->fetchColumn(
                "SELECT AVG(rating) FROM business_reviews 
                 WHERE created_at BETWEEN :start AND :end AND status = 'approved'",
                ['start' => $startDate, 'end' => $endDate]
            ) ?: 0
        ];
    }

    private function getRevenueAnalytics(string $startDate, string $endDate): array
    {
        return [
            'total_revenue' => $this->db->fetchColumn(
                "SELECT SUM(monthly_price) FROM business_subscriptions 
                 WHERE created_at BETWEEN :start AND :end AND status = 'active'",
                ['start' => $startDate, 'end' => $endDate]
            ) ?: 0,
            'new_subscriptions' => $this->db->fetchColumn(
                "SELECT COUNT(*) FROM business_subscriptions 
                 WHERE created_at BETWEEN :start AND :end",
                ['start' => $startDate, 'end' => $endDate]
            ) ?: 0
        ];
    }

    private function getDatabaseMetrics(): array
    {
        return [
            'size_mb' => $this->db->fetchColumn(
                "SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) 
                 FROM information_schema.tables 
                 WHERE table_schema = DATABASE()"
            ) ?: 0,
            'table_count' => $this->db->fetchColumn(
                "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE()"
            ) ?: 0
        ];
    }

    private function getServerMetrics(): array
    {
        return [
            'memory_usage' => round(memory_get_usage(true) / 1024 / 1024, 2),
            'peak_memory_usage' => round(memory_get_peak_usage(true) / 1024 / 1024, 2),
            'php_version' => PHP_VERSION,
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown'
        ];
    }

    private function getPerformanceMetrics(): array
    {
        return [
            'avg_query_time' => 45, // This would come from query logging
            'concurrent_connections' => $this->db->fetchColumn(
                "SELECT COUNT(*) FROM information_schema.processlist WHERE db = DATABASE()"
            ) ?: 0
        ];
    }

    private function getErrorMetrics(): array
    {
        $last24Hours = date('Y-m-d H:i:s', strtotime('-24 hours'));
        
        return [
            'errors_last_24h' => $this->db->fetchColumn(
                "SELECT COUNT(*) FROM {$this->systemLogsTable} 
                 WHERE level IN ('error', 'critical') AND created_at > :time",
                ['time' => $last24Hours]
            ) ?: 0,
            'warnings_last_24h' => $this->db->fetchColumn(
                "SELECT COUNT(*) FROM {$this->systemLogsTable} 
                 WHERE level = 'warning' AND created_at > :time",
                ['time' => $last24Hours]
            ) ?: 0
        ];
    }

    private function getBackupStatus(): array
    {
        $backupDir = __DIR__ . '/../backups/';
        $latestBackup = null;
        
        if (is_dir($backupDir)) {
            $files = glob($backupDir . '*.sql');
            if (!empty($files)) {
                $latestBackup = [
                    'file' => basename(end($files)),
                    'size' => round(filesize(end($files)) / 1024 / 1024, 2),
                    'modified' => date('Y-m-d H:i:s', filemtime(end($files)))
                ];
            }
        }

        return [
            'last_backup' => $latestBackup,
            'backup_dir_exists' => is_dir($backupDir),
            'backup_count' => count($files ?? [])
        ];
    }

    private function calculateUserRetention(string $startDate, string $endDate): float
    {
        // Simplified retention calculation
        $cohortSize = $this->db->fetchColumn(
            "SELECT COUNT(*) FROM users WHERE created_at BETWEEN :start AND :end",
            ['start' => $startDate, 'end' => $endDate]
        ) ?: 1;

        $retainedUsers = $this->db->fetchColumn(
            "SELECT COUNT(DISTINCT u.id) FROM users u
             INNER JOIN user_sessions us ON u.id = us.user_id
             WHERE u.created_at BETWEEN :start AND :end 
             AND us.created_at BETWEEN DATE_ADD(:start, INTERVAL 7 DAY) AND :end",
            ['start' => $startDate, 'end' => $endDate]
        ) ?: 0;

        return round(($retainedUsers / $cohortSize) * 100, 2);
    }
}
