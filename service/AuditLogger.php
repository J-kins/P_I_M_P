<?php
/**
 * P.I.M.P - Audit Logger
 * Comprehensive audit logging system
 */

namespace PIMP\Services;

use PIMP\Services\Database\MySQLDatabase;

class AuditLogger
{
    private $db;

    public function __construct(MySQLDatabase $db)
    {
        $this->db = $db;
    }

    /**
     * Log an audit event
     * 
     * @param string $eventType Type of event
     * @param string $entityType Type of entity (user, business, review, etc.)
     * @param int $entityId ID of the entity
     * @param array $metadata Additional metadata
     * @param int|null $userId User performing the action
     * @return bool
     */
    public function log(
        string $eventType,
        string $entityType,
        int $entityId,
        array $metadata = [],
        ?int $userId = null
    ): bool {
        // Get user ID from session if not provided
        if ($userId === null && isset($_SESSION['user_id'])) {
            $userId = $_SESSION['user_id'];
        }

        // Capture request context
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? null;
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;
        $requestId = $this->generateRequestId();

        $this->db->query(
            "INSERT INTO audit_logs 
            (event_type, user_id, entity_type, entity_id, action, metadata_json, ip_address, user_agent, request_id)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)",
            [
                $eventType,
                $userId,
                $entityType,
                $entityId,
                $metadata['action'] ?? 'unknown',
                json_encode($metadata),
                $ipAddress,
                $userAgent,
                $requestId
            ]
        );

        return true;
    }

    /**
     * Log entity creation
     * 
     * @param string $entityType
     * @param int $entityId
     * @param array $data
     * @param int|null $userId
     * @return bool
     */
    public function logCreate(string $entityType, int $entityId, array $data, ?int $userId = null): bool
    {
        return $this->log($entityType . '_created', $entityType, $entityId, [
            'action' => 'create',
            'new_values' => $data
        ], $userId);
    }

    /**
     * Log entity update
     * 
     * @param string $entityType
     * @param int $entityId
     * @param array $oldData
     * @param array $newData
     * @param int|null $userId
     * @return bool
     */
    public function logUpdate(
        string $entityType,
        int $entityId,
        array $oldData,
        array $newData,
        ?int $userId = null
    ): bool {
        $changes = $this->calculateChanges($oldData, $newData);

        return $this->log($entityType . '_updated', $entityType, $entityId, [
            'action' => 'update',
            'old_values' => $oldData,
            'new_values' => $newData,
            'changes' => $changes
        ], $userId);
    }

    /**
     * Log entity deletion
     * 
     * @param string $entityType
     * @param int $entityId
     * @param array $data
     * @param int|null $userId
     * @return bool
     */
    public function logDelete(string $entityType, int $entityId, array $data, ?int $userId = null): bool
    {
        return $this->log($entityType . '_deleted', $entityType, $entityId, [
            'action' => 'delete',
            'old_values' => $data
        ], $userId);
    }

    /**
     * Log login attempt
     * 
     * @param string $email
     * @param bool $success
     * @param string|null $failureReason
     * @return bool
     */
    public function logLoginAttempt(string $email, bool $success, ?string $failureReason = null): bool
    {
        return $this->log($success ? 'login_success' : 'login_failed', 'user', 0, [
            'action' => 'login',
            'email' => $email,
            'success' => $success,
            'failure_reason' => $failureReason
        ]);
    }

    /**
     * Log logout
     * 
     * @param int $userId
     * @return bool
     */
    public function logLogout(int $userId): bool
    {
        return $this->log('user_logout', 'user', $userId, [
            'action' => 'logout'
        ], $userId);
    }

    /**
     * Log password change
     * 
     * @param int $userId
     * @param bool $forced Whether password change was forced
     * @return bool
     */
    public function logPasswordChange(int $userId, bool $forced = false): bool
    {
        return $this->log('password_changed', 'user', $userId, [
            'action' => 'password_change',
            'forced' => $forced
        ], $userId);
    }

    /**
     * Log permission change
     * 
     * @param int $userId
     * @param array $oldPermissions
     * @param array $newPermissions
     * @param int $changedBy
     * @return bool
     */
    public function logPermissionChange(
        int $userId,
        array $oldPermissions,
        array $newPermissions,
        int $changedBy
    ): bool {
        $added = array_diff($newPermissions, $oldPermissions);
        $removed = array_diff($oldPermissions, $newPermissions);

        return $this->log('permissions_changed', 'user', $userId, [
            'action' => 'permission_change',
            'old_permissions' => $oldPermissions,
            'new_permissions' => $newPermissions,
            'added' => $added,
            'removed' => $removed
        ], $changedBy);
    }

    /**
     * Get audit logs with filters
     * 
     * @param int $page
     * @param int $perPage
     * @param array $filters
     * @return array
     */
    public function getLogs(int $page = 1, int $perPage = 50, array $filters = []): array
    {
        $offset = ($page - 1) * $perPage;
        $where = ["1=1"];
        $params = [];

        if (!empty($filters['event_type'])) {
            $where[] = "event_type = ?";
            $params[] = $filters['event_type'];
        }

        if (!empty($filters['user_id'])) {
            $where[] = "user_id = ?";
            $params[] = $filters['user_id'];
        }

        if (!empty($filters['entity_type'])) {
            $where[] = "entity_type = ?";
            $params[] = $filters['entity_type'];
        }

        if (!empty($filters['date_from'])) {
            $where[] = "DATE(event_timestamp) >= ?";
            $params[] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $where[] = "DATE(event_timestamp) <= ?";
            $params[] = $filters['date_to'];
        }

        if (!empty($filters['ip_address'])) {
            $where[] = "ip_address = ?";
            $params[] = $filters['ip_address'];
        }

        $whereClause = implode(' AND ', $where);

        $logs = $this->db->fetchAll(
            "SELECT al.*, u.username, u.email 
             FROM audit_logs al
             LEFT JOIN users u ON al.user_id = u.id
             WHERE {$whereClause}
             ORDER BY al.event_timestamp DESC
             LIMIT ? OFFSET ?",
            array_merge($params, [$perPage, $offset])
        );

        $total = $this->db->fetchColumn(
            "SELECT COUNT(*) FROM audit_logs WHERE {$whereClause}",
            $params
        );

        return [
            'data' => $logs,
            'total' => (int)$total,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => ceil($total / $perPage)
        ];
    }

    /**
     * Get logs for specific entity
     * 
     * @param string $entityType
     * @param int $entityId
     * @param int $limit
     * @return array
     */
    public function getEntityLogs(string $entityType, int $entityId, int $limit = 50): array
    {
        return $this->db->fetchAll(
            "SELECT al.*, u.username, u.email 
             FROM audit_logs al
             LEFT JOIN users u ON al.user_id = u.id
             WHERE al.entity_type = ? AND al.entity_id = ?
             ORDER BY al.event_timestamp DESC
             LIMIT ?",
            [$entityType, $entityId, $limit]
        );
    }

    /**
     * Get logs for specific user
     * 
     * @param int $userId
     * @param int $limit
     * @return array
     */
    public function getUserLogs(int $userId, int $limit = 50): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM audit_logs 
             WHERE user_id = ?
             ORDER BY event_timestamp DESC
             LIMIT ?",
            [$userId, $limit]
        );
    }

    /**
     * Get event types
     * 
     * @return array
     */
    public function getEventTypes(): array
    {
        $types = $this->db->fetchAll(
            "SELECT DISTINCT event_type FROM audit_logs ORDER BY event_type",
            []
        );

        return array_column($types, 'event_type');
    }

    /**
     * Get audit statistics
     * 
     * @return array
     */
    public function getStatistics(): array
    {
        return [
            'total_events' => $this->db->fetchColumn(
                "SELECT COUNT(*) FROM audit_logs",
                []
            ),
            'events_today' => $this->db->fetchColumn(
                "SELECT COUNT(*) FROM audit_logs WHERE DATE(event_timestamp) = CURDATE()",
                []
            ),
            'events_this_week' => $this->db->fetchColumn(
                "SELECT COUNT(*) FROM audit_logs WHERE YEARWEEK(event_timestamp) = YEARWEEK(NOW())",
                []
            ),
            'events_this_month' => $this->db->fetchColumn(
                "SELECT COUNT(*) FROM audit_logs WHERE MONTH(event_timestamp) = MONTH(NOW()) AND YEAR(event_timestamp) = YEAR(NOW())",
                []
            ),
            'unique_users' => $this->db->fetchColumn(
                "SELECT COUNT(DISTINCT user_id) FROM audit_logs WHERE user_id IS NOT NULL",
                []
            ),
            'unique_ips' => $this->db->fetchColumn(
                "SELECT COUNT(DISTINCT ip_address) FROM audit_logs WHERE ip_address IS NOT NULL",
                []
            )
        ];
    }

    /**
     * Get most active users
     * 
     * @param int $limit
     * @param string $timeframe 'day', 'week', 'month', 'all'
     * @return array
     */
    public function getMostActiveUsers(int $limit = 10, string $timeframe = 'week'): array
    {
        $whereClause = "WHERE al.user_id IS NOT NULL";

        switch ($timeframe) {
            case 'day':
                $whereClause .= " AND DATE(al.event_timestamp) = CURDATE()";
                break;
            case 'week':
                $whereClause .= " AND YEARWEEK(al.event_timestamp) = YEARWEEK(NOW())";
                break;
            case 'month':
                $whereClause .= " AND MONTH(al.event_timestamp) = MONTH(NOW()) AND YEAR(al.event_timestamp) = YEAR(NOW())";
                break;
        }

        return $this->db->fetchAll(
            "SELECT al.user_id, u.username, u.email, COUNT(*) as event_count
             FROM audit_logs al
             JOIN users u ON al.user_id = u.id
             {$whereClause}
             GROUP BY al.user_id
             ORDER BY event_count DESC
             LIMIT ?",
            [$limit]
        );
    }

    /**
     * Get most common events
     * 
     * @param int $limit
     * @param string $timeframe
     * @return array
     */
    public function getMostCommonEvents(int $limit = 10, string $timeframe = 'week'): array
    {
        $whereClause = "WHERE 1=1";

        switch ($timeframe) {
            case 'day':
                $whereClause .= " AND DATE(event_timestamp) = CURDATE()";
                break;
            case 'week':
                $whereClause .= " AND YEARWEEK(event_timestamp) = YEARWEEK(NOW())";
                break;
            case 'month':
                $whereClause .= " AND MONTH(event_timestamp) = MONTH(NOW()) AND YEAR(event_timestamp) = YEAR(NOW())";
                break;
        }

        return $this->db->fetchAll(
            "SELECT event_type, COUNT(*) as count
             FROM audit_logs
             {$whereClause}
             GROUP BY event_type
             ORDER BY count DESC
             LIMIT ?",
            [$limit]
        );
    }

    /**
     * Search audit logs
     * 
     * @param string $query
     * @param int $limit
     * @return array
     */
    public function search(string $query, int $limit = 50): array
    {
        return $this->db->fetchAll(
            "SELECT al.*, u.username, u.email 
             FROM audit_logs al
             LEFT JOIN users u ON al.user_id = u.id
             WHERE al.event_type LIKE ? 
                OR al.action LIKE ? 
                OR al.metadata_json LIKE ?
                OR u.username LIKE ?
                OR u.email LIKE ?
             ORDER BY al.event_timestamp DESC
             LIMIT ?",
            [
                "%{$query}%",
                "%{$query}%",
                "%{$query}%",
                "%{$query}%",
                "%{$query}%",
                $limit
            ]
        );
    }

    /**
     * Clean old logs (for maintenance)
     * 
     * @param int $daysToKeep
     * @return int Number of deleted records
     */
    public function cleanOldLogs(int $daysToKeep = 90): int
    {
        $this->db->query(
            "DELETE FROM audit_logs WHERE event_timestamp < DATE_SUB(NOW(), INTERVAL ? DAY)",
            [$daysToKeep]
        );

        return $this->db->getConnection()->lastInsertId();
    }

    /**
     * Export logs to CSV
     * 
     * @param array $filters
     * @param string $filename
     * @return string Path to exported file
     */
    public function exportToCSV(array $filters = [], string $filename = null): string
    {
        if (!$filename) {
            $filename = 'audit_logs_' . date('Y-m-d_H-i-s') . '.csv';
        }

        $exportPath = dirname(__DIR__, 2) . '/exports/';
        if (!is_dir($exportPath)) {
            mkdir($exportPath, 0755, true);
        }

        $fullPath = $exportPath . $filename;
        $handle = fopen($fullPath, 'w');

        // Write headers
        fputcsv($handle, [
            'ID', 'Timestamp', 'Event Type', 'User ID', 'Username', 'Email',
            'Entity Type', 'Entity ID', 'Action', 'IP Address', 'User Agent', 'Metadata'
        ]);

        // Get logs
        $logs = $this->getLogs(1, 999999, $filters);

        // Write data
        foreach ($logs['data'] as $log) {
            fputcsv($handle, [
                $log['id'],
                $log['event_timestamp'],
                $log['event_type'],
                $log['user_id'],
                $log['username'] ?? '',
                $log['email'] ?? '',
                $log['entity_type'],
                $log['entity_id'],
                $log['action'],
                $log['ip_address'],
                $log['user_agent'],
                $log['metadata_json']
            ]);
        }

        fclose($handle);

        return $fullPath;
    }

    /**
     * Calculate changes between old and new data
     * 
     * @param array $oldData
     * @param array $newData
     * @return array
     */
    private function calculateChanges(array $oldData, array $newData): array
    {
        $changes = [];

        foreach ($newData as $key => $newValue) {
            if (!isset($oldData[$key]) || $oldData[$key] !== $newValue) {
                $changes[$key] = [
                    'old' => $oldData[$key] ?? null,
                    'new' => $newValue
                ];
            }
        }

        return $changes;
    }

    /**
     * Generate unique request ID
     * 
     * @return string
     */
    private function generateRequestId(): string
    {
        return uniqid('req_', true);
    }
}
