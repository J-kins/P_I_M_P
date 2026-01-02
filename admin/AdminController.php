<?php
/**
 * P.I.M.P - Admin Controller
 * Handles admin panel operations
 */

namespace PIMP\Controllers;

use PIMP\Models\AdminModel;
use PIMP\Models\UserModel;
use PIMP\Services\SecurityService;
use PIMP\Services\AuditLogger;
use PIMP\Services\Database\MySQLDatabase;
use PIMP\Core\Config;

class AdminController
{
    private $adminModel;
    private $userModel;
    private $securityService;
    private $auditLogger;
    private $db;

    public function __construct(MySQLDatabase $db)
    {
        $this->db = $db;
        $this->adminModel = new AdminModel($db);
        $this->userModel = new UserModel($db);
        $this->securityService = new SecurityService($db);
        $this->auditLogger = new AuditLogger($db);

        // Start session if not started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Admin dashboard
     */
    public function dashboard()
    {
        $this->requireAdminAuth();

        $data = [
            'statistics' => $this->adminModel->getDashboardStatistics(),
            'recent_users' => $this->adminModel->getRecentUsers(10),
            'recent_businesses' => $this->adminModel->getRecentBusinesses(10),
            'recent_reviews' => $this->adminModel->getRecentReviews(10),
            'pending_approvals' => $this->adminModel->getPendingApprovals(),
            'system_health' => $this->adminModel->getSystemHealth()
        ];

        $this->auditLogger->log('admin_dashboard_view', 'system', 0, [
            'admin_id' => $_SESSION['user_id']
        ]);

        require_once dirname(__DIR__) . '/views/admin/dashboard.php';
    }

    /**
     * User management
     */
    public function users()
    {
        $this->requireAdminAuth();
        $this->requirePermission('users.manage');

        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $search = $_GET['search'] ?? '';
        $status = $_GET['status'] ?? '';

        $filters = [];
        if ($search) $filters['search'] = $search;
        if ($status) $filters['status'] = $status;

        $data = [
            'users' => $this->userModel->getAll($page, 20, $filters),
            'filters' => $filters
        ];

        require_once dirname(__DIR__) . '/views/admin/users.php';
    }

    /**
     * Edit user
     */
    public function editUser($userId)
    {
        $this->requireAdminAuth();
        $this->requirePermission('users.manage');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            return $this->handleUserUpdate($userId);
        }

        $user = $this->userModel->findById($userId);
        if (!$user) {
            $_SESSION['error'] = 'User not found';
            header('Location: ' . Config::url('/admin/users'));
            exit;
        }

        $data = [
            'user' => $user,
            'roles' => $this->adminModel->getAllRoles(),
            'user_roles' => $this->userModel->getRoles($userId)
        ];

        require_once dirname(__DIR__) . '/views/admin/edit-user.php';
    }

    /**
     * Business management
     */
    public function businesses()
    {
        $this->requireAdminAuth();
        $this->requirePermission('businesses.manage');

        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $search = $_GET['search'] ?? '';
        $status = $_GET['status'] ?? '';

        $filters = [];
        if ($search) $filters['search'] = $search;
        if ($status) $filters['status'] = $status;

        $data = [
            'businesses' => $this->adminModel->getBusinesses($page, 20, $filters),
            'filters' => $filters
        ];

        require_once dirname(__DIR__) . '/views/admin/businesses.php';
    }

    /**
     * Review moderation
     */
    public function reviews()
    {
        $this->requireAdminAuth();
        $this->requirePermission('reviews.moderate');

        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $status = $_GET['status'] ?? 'pending';

        $data = [
            'reviews' => $this->adminModel->getReviews($page, 20, ['status' => $status]),
            'status' => $status
        ];

        require_once dirname(__DIR__) . '/views/admin/reviews.php';
    }

    /**
     * Approve review
     */
    public function approveReview($reviewId)
    {
        $this->requireAdminAuth();
        $this->requirePermission('reviews.moderate');

        $result = $this->adminModel->updateReviewStatus($reviewId, 'approved', $_SESSION['user_id']);

        if ($result) {
            $this->auditLogger->log('review_approved', 'review', $reviewId, [
                'admin_id' => $_SESSION['user_id']
            ]);

            $_SESSION['success'] = 'Review approved successfully';
        } else {
            $_SESSION['error'] = 'Failed to approve review';
        }

        header('Location: ' . Config::url('/admin/reviews'));
        exit;
    }

    /**
     * Reject review
     */
    public function rejectReview($reviewId)
    {
        $this->requireAdminAuth();
        $this->requirePermission('reviews.moderate');

        $notes = $_POST['notes'] ?? '';

        $result = $this->adminModel->updateReviewStatus($reviewId, 'rejected', $_SESSION['user_id'], $notes);

        if ($result) {
            $this->auditLogger->log('review_rejected', 'review', $reviewId, [
                'admin_id' => $_SESSION['user_id'],
                'notes' => $notes
            ]);

            $_SESSION['success'] = 'Review rejected';
        } else {
            $_SESSION['error'] = 'Failed to reject review';
        }

        header('Location: ' . Config::url('/admin/reviews'));
        exit;
    }

    /**
     * System settings
     */
    public function settings()
    {
        $this->requireAdminAuth();
        $this->requirePermission('system.settings.manage');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            return $this->handleSettingsUpdate();
        }

        $data = [
            'settings' => $this->adminModel->getSystemSettings(),
            'categories' => $this->adminModel->getSettingCategories()
        ];

        require_once dirname(__DIR__) . '/views/admin/settings.php';
    }

    /**
     * Audit logs
     */
    public function auditLogs()
    {
        $this->requireAdminAuth();
        $this->requirePermission('audit.view');

        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $filters = [
            'event_type' => $_GET['event_type'] ?? '',
            'user_id' => $_GET['user_id'] ?? '',
            'date_from' => $_GET['date_from'] ?? '',
            'date_to' => $_GET['date_to'] ?? ''
        ];

        $data = [
            'logs' => $this->auditLogger->getLogs($page, 50, $filters),
            'filters' => $filters,
            'event_types' => $this->auditLogger->getEventTypes()
        ];

        require_once dirname(__DIR__) . '/views/admin/audit-logs.php';
    }

    /**
     * Security management
     */
    public function security()
    {
        $this->requireAdminAuth();
        $this->requirePermission('security.manage');

        $data = [
            'security_status' => $this->securityService->getSecurityStatus(),
            'blocked_ips' => $this->securityService->getBlockedIPs(),
            'failed_logins' => $this->securityService->getRecentFailedLogins(),
            'suspicious_activities' => $this->securityService->getSuspiciousActivities()
        ];

        require_once dirname(__DIR__) . '/views/admin/security.php';
    }

    /**
     * Clear sitemap cache
     */
    public function clearSitemapCache()
    {
        $this->requireAdminAuth();
        $this->requirePermission('system.cache.manage');

        $sitemapController = new SitemapController($this->db);
        $result = $sitemapController->clearCache();

        $this->auditLogger->log('sitemap_cache_cleared', 'system', 0, [
            'admin_id' => $_SESSION['user_id'],
            'files_cleared' => $result['count']
        ]);

        header('Content-Type: application/json');
        echo json_encode($result);
        exit;
    }

    /**
     * Database backup
     */
    public function databaseBackup()
    {
        $this->requireAdminAuth();
        $this->requirePermission('system.backup.create');

        try {
            $backupPath = $this->db->backup();

            $this->auditLogger->log('database_backup_created', 'system', 0, [
                'admin_id' => $_SESSION['user_id'],
                'backup_path' => $backupPath
            ]);

            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'message' => 'Database backup created successfully',
                'backup_path' => $backupPath
            ]);
        } catch (\Exception $e) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'error' => 'Failed to create backup: ' . $e->getMessage()
            ]);
        }
        exit;
    }

    /**
     * Handle user update
     */
    private function handleUserUpdate($userId)
    {
        $data = [
            'status' => $_POST['status'] ?? null,
            'email_verified' => isset($_POST['email_verified']) ? 1 : 0
        ];

        // Remove null values
        $data = array_filter($data, function($value) {
            return $value !== null;
        });

        if ($this->userModel->update($userId, $data)) {
            // Update roles if provided
            if (isset($_POST['roles'])) {
                $this->adminModel->updateUserRoles($userId, $_POST['roles'], $_SESSION['user_id']);
            }

            $this->auditLogger->log('user_updated', 'user', $userId, [
                'admin_id' => $_SESSION['user_id'],
                'changes' => $data
            ]);

            $_SESSION['success'] = 'User updated successfully';
        } else {
            $_SESSION['error'] = 'Failed to update user';
        }

        header('Location: ' . Config::url('/admin/users'));
        exit;
    }

    /**
     * Handle settings update
     */
    private function handleSettingsUpdate()
    {
        $settings = $_POST['settings'] ?? [];

        foreach ($settings as $key => $value) {
            $this->adminModel->updateSystemSetting($key, $value, $_SESSION['user_id']);
        }

        $this->auditLogger->log('system_settings_updated', 'system', 0, [
            'admin_id' => $_SESSION['user_id'],
            'settings_count' => count($settings)
        ]);

        $_SESSION['success'] = 'Settings updated successfully';
        header('Location: ' . Config::url('/admin/settings'));
        exit;
    }

    /**
     * Require admin authentication
     */
    private function requireAdminAuth()
    {
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
            header('Location: ' . Config::url('/admin/login'));
            exit;
        }

        $user = $this->userModel->findById($_SESSION['user_id']);
        if (!$user) {
            session_destroy();
            header('Location: ' . Config::url('/admin/login'));
            exit;
        }

        // Check if user has admin role
        $roles = $this->userModel->getRoles($user['id']);
        $hasAdminRole = false;

        foreach ($roles as $role) {
            if (in_array($role['name'], ['super_admin', 'admin'])) {
                $hasAdminRole = true;
                break;
            }
        }

        if (!$hasAdminRole) {
            header('Location: ' . Config::url('/'));
            exit;
        }

        // Security check
        $this->securityService->checkSession($_SESSION['user_id']);
    }

    /**
     * Require specific permission
     */
    private function requirePermission($permission)
    {
        if (!$this->userModel->hasPermission($_SESSION['user_id'], $permission)) {
            $_SESSION['error'] = 'You do not have permission to access this page';
            header('Location: ' . Config::url('/admin/dashboard'));
            exit;
        }
    }

    /**
     * Admin login
     */
    public function login()
    {
        // Use the same login logic but redirect to admin dashboard
        // This is handled by AuthController with a flag
        require_once dirname(__DIR__) . '/views/admin/login.php';
    }
}
