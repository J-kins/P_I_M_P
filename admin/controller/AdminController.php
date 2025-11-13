<?php
/**
 * P.I.M.P - Admin Main Controller
 * Handles admin dashboard and system management
 */

namespace PIMP\Controllers\Admin;

use PIMP\Core\Config;
use PIMP\Services\Database\MySQLDatabase;
use PIMP\Services\Admin\AdminApiService;

class AdminController
{
    /**
     * @var MySQLDatabase Database instance
     */
    private $db;

    /**
     * @var AdminApiService Admin API service
     */
    private $adminService;

    /**
     * @var array Admin user data
     */
    private $adminUser;

    /**
     * Constructor
     * 
     * @param MySQLDatabase $db Database instance
     */
    public function __construct(MySQLDatabase $db)
    {
        $this->db = $db;
        $this->adminService = new AdminApiService($db);
        $this->authenticateAdmin();
    }

    /**
     * Admin dashboard
     */
    public function dashboard(): string
    {
        $dashboardData = $this->adminService->getDashboardData($this->adminUser['id']);
        
        return $this->renderView('admin/dashboard', [
            'title' => 'Admin Dashboard',
            'adminUser' => $this->adminUser,
            'dashboardData' => $dashboardData,
            'systemHealth' => $this->adminService->getSystemHealth(),
            'recentActivities' => $this->adminService->getRecentActivities()
        ]);
    }

    /**
     * Business management
     */
    public function businessManagement(): string
    {
        $page = $_GET['page'] ?? 1;
        $filters = $this->getFiltersFromRequest();
        
        $businesses = $this->adminService->getBusinessManagementData($filters, $page);
        
        return $this->renderView('admin/business_management', [
            'title' => 'Business Management',
            'adminUser' => $this->adminUser,
            'businesses' => $businesses['businesses'],
            'pagination' => $businesses['pagination'],
            'filters' => $filters
        ]);
    }

    /**
     * User management
     */
    public function userManagement(): string
    {
        $page = $_GET['page'] ?? 1;
        $filters = $this->getFiltersFromRequest();
        
        $users = $this->adminService->getUserManagementData($filters, $page);
        
        return $this->renderView('admin/user_management', [
            'title' => 'User Management',
            'adminUser' => $this->adminUser,
            'users' => $users['users'],
            'pagination' => $users['pagination'],
            'filters' => $filters
        ]);
    }

    /**
     * Moderation queue
     */
    public function moderationQueue(): string
    {
        $queueData = $this->adminService->getModerationQueue();
        
        return $this->renderView('admin/moderation_queue', [
            'title' => 'Moderation Queue',
            'adminUser' => $this->adminUser,
            'moderationQueue' => $queueData,
            'priorityItems' => $this->adminService->getPriorityModerationItems()
        ]);
    }

    /**
     * System settings
     */
    public function systemSettings(): string
    {
        $settings = $this->adminService->getSystemSettings();
        
        return $this->renderView('admin/system_settings', [
            'title' => 'System Settings',
            'adminUser' => $this->adminUser,
            'settings' => $settings,
            'settingCategories' => $this->adminService->getSettingCategories()
        ]);
    }

    /**
     * Reports and analytics
     */
    public function reports(): string
    {
        $reportType = $_GET['report'] ?? 'overview';
        $period = $_GET['period'] ?? '30days';
        
        $reportData = $this->adminService->generateSystemReports([
            'report_type' => $reportType,
            'period' => $period
        ], $this->adminUser['id']);
        
        return $this->renderView('admin/reports', [
            'title' => 'Reports & Analytics',
            'adminUser' => $this->adminUser,
            'reportData' => $reportData,
            'currentReport' => $reportType,
            'currentPeriod' => $period
        ]);
    }

    /**
     * API endpoint for admin actions
     */
    public function api(): void
    {
        header('Content-Type: application/json');
        
        $action = $_POST['action'] ?? $_GET['action'] ?? '';
        $response = ['success' => false, 'message' => 'Invalid action'];
        
        try {
            $actionHandlers = [
                'update_setting' => 'handleUpdateSetting',
                'process_onboarding' => 'handleProcessOnboarding',
                'moderate_content' => 'handleModerateContent',
                'manage_user' => 'handleManageUser',
                'generate_report' => 'handleGenerateReport',
                'system_health' => 'handleSystemHealth'
            ];
            
            foreach ($actionHandlers as $actionKey => $handlerMethod) {
                if ($action === $actionKey && method_exists($this, $handlerMethod)) {
                    $response = $this->$handlerMethod();
                    break;
                }
            }
        } catch (Exception $e) {
            $response = [
                'success' => false,
                'message' => $e->getMessage(),
                'error_code' => $e->getCode()
            ];
        }
        
        echo json_encode($response);
        exit;
    }

    /**
     * Admin login
     */
    public function login(): string
    {
        if ($this->isAdminLoggedIn()) {
            header('Location: ' . Config::url('/admin/dashboard'));
            exit;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $result = $this->handleLogin($_POST);
            if ($result['success']) {
                header('Location: ' . Config::url('/admin/dashboard'));
                exit;
            }
        }
        
        return $this->renderView('admin/login', [
            'title' => 'Admin Login',
            'error' => $result['message'] ?? null
        ]);
    }

    /**
     * Admin logout
     */
    public function logout(): void
    {
        $this->destroyAdminSession();
        header('Location: ' . Config::url('/admin/login'));
        exit;
    }

    // Private methods

    private function authenticateAdmin(): void
    {
        if ($this->isAdminLoggedIn()) {
            $this->adminUser = $_SESSION['admin_user'];
            return;
        }
        
        // Check for API token
        $apiToken = $_SERVER['HTTP_X_ADMIN_TOKEN'] ?? null;
        if ($apiToken) {
            $this->authenticateViaApiToken($apiToken);
            return;
        }
        
        // Redirect to login if not authenticated
        if (!$this->isLoginPage()) {
            header('Location: ' . Config::url('/admin/login'));
            exit;
        }
    }

    private function isAdminLoggedIn(): bool
    {
        return isset($_SESSION['admin_user']) && !empty($_SESSION['admin_user']['id']);
    }

    private function isLoginPage(): bool
    {
        return strpos($_SERVER['REQUEST_URI'], '/admin/login') !== false;
    }

    private function handleLogin(array $credentials): array
    {
        $required = ['username', 'password'];
        foreach ($required as $field) {
            if (empty($credentials[$field])) {
                return ['success' => false, 'message' => "Missing {$field}"];
            }
        }
        
        $admin = $this->db->fetchOne(
            "SELECT * FROM users WHERE username = :username AND user_type = 'admin' AND status = 'active'",
            ['username' => $credentials['username']]
        );
        
        if (!$admin || !password_verify($credentials['password'], $admin['password_hash'])) {
            return ['success' => false, 'message' => 'Invalid credentials'];
        }
        
        // Create admin session
        $_SESSION['admin_user'] = [
            'id' => $admin['id'],
            'username' => $admin['username'],
            'email' => $admin['email'],
            'first_name' => $admin['first_name'],
            'last_name' => $admin['last_name'],
            'permissions' => $this->adminService->getAdminPermissions($admin['id'])
        ];
        
        return ['success' => true, 'message' => 'Login successful'];
    }

    private function destroyAdminSession(): void
    {
        unset($_SESSION['admin_user']);
        session_destroy();
    }

    private function renderView(string $view, array $data = []): string
    {
        extract($data);
        ob_start();
        include __DIR__ . "/../../admin/view/{$view}.php";
        return ob_get_clean();
    }

    private function getFiltersFromRequest(): array
    {
        $filters = [];
        $possibleFilters = ['status', 'type', 'date_from', 'date_to', 'search', 'category'];
        
        foreach ($possibleFilters as $filter) {
            if (!empty($_GET[$filter])) {
                $filters[$filter] = $_GET[$filter];
            }
        }
        
        return $filters;
    }

    // API handler methods
    private function handleUpdateSetting(): array
    {
        $key = $_POST['key'] ?? '';
        $value = $_POST['value'] ?? '';
        
        if (empty($key)) {
            return ['success' => false, 'message' => 'Setting key required'];
        }
        
        return $this->adminService->updateSystemConfiguration(
            [$key => $value],
            $this->adminUser['id']
        );
    }

    private function handleProcessOnboarding(): array
    {
        $businessId = (int)($_POST['business_id'] ?? 0);
        $step = $_POST['step'] ?? '';
        
        if (!$businessId) {
            return ['success' => false, 'message' => 'Business ID required'];
        }
        
        return $this->adminService->processBusinessOnboarding(
            $businessId,
            $this->adminUser['id'],
            ['current_step' => $step]
        );
    }

    private function handleModerateContent(): array
    {
        $contentId = (int)($_POST['content_id'] ?? 0);
        $contentType = $_POST['content_type'] ?? '';
        $action = $_POST['action'] ?? '';
        
        $required = ['content_id', 'content_type', 'action'];
        foreach ($required as $field) {
            if (empty($$field)) {
                return ['success' => false, 'message' => "Missing {$field}"];
            }
        }
        
        if ($contentType === 'complaint') {
            return $this->adminService->moderateComplaint(
                $contentId,
                $this->adminUser['id'],
                [
                    'action' => $action,
                    'resolution_notes' => $_POST['notes'] ?? ''
                ]
            );
        }
        
        return ['success' => false, 'message' => 'Unsupported content type'];
    }

    private function handleManageUser(): array
    {
        $userId = (int)($_POST['user_id'] ?? 0);
        $action = $_POST['action'] ?? '';
        $reason = $_POST['reason'] ?? '';
        
        if (!$userId || !$action) {
            return ['success' => false, 'message' => 'User ID and action required'];
        }
        
        return $this->adminService->manageUserAccount(
            $userId,
            $this->adminUser['id'],
            [
                'action' => $action,
                'reason' => $reason,
                'warning_message' => $_POST['warning_message'] ?? ''
            ]
        );
    }

    private function handleGenerateReport(): array
    {
        $reportType = $_POST['report_type'] ?? 'overview';
        $period = $_POST['period'] ?? '30days';
        
        return $this->adminService->generateSystemReports(
            [
                'report_type' => $reportType,
                'period' => $period
            ],
            $this->adminUser['id']
        );
    }

    private function handleSystemHealth(): array
    {
        return [
            'success' => true,
            'data' => $this->adminService->getSystemHealth()
        ];
    }
}
