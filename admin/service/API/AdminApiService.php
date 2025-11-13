<?php
/**
 * P.I.M.P - Admin API Services
 * Handles administrative operations, onboarding, and system management
 */

namespace PIMP\Services\Admin;

use PIMP\Services\Database\MySQLDatabase;
use PIMP\Models\Admin\AdminDashboard;
use PIMP\Models\Admin\ModerationTools;
use PDOException;
use Exception;

class AdminApiService
{
    /**
     * @var MySQLDatabase Database instance
     */
    private $db;

    /**
     * @var AdminDashboard Admin dashboard model
     */
    private $dashboard;

    /**
     * @var ModerationTools Moderation tools model
     */
    private $moderation;

    /**
     * @var array Admin permissions
     */
    private $permissions;

    /**
     * Constructor
     * 
     * @param MySQLDatabase $db Database instance
     */
    public function __construct(MySQLDatabase $db)
    {
        $this->db = $db;
        $this->dashboard = new AdminDashboard($db);
        $this->moderation = new ModerationTools($db);
        $this->permissions = $this->loadAdminPermissions();
    }

    /**
     * Initialize admin system and handle onboarding
     * 
     * @param array $adminData
     * @return array
     * @throws Exception
     */
    public function initializeAdminSystem(array $adminData): array
    {
        $requiredFields = ['username', 'email', 'password', 'first_name', 'last_name'];
        foreach ($requiredFields as $field) {
            if (empty($adminData[$field])) {
                throw new Exception("Missing required field: {$field}");
            }
        }

        // Validate email
        if (!filter_var($adminData['email'], FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Invalid email address");
        }

        // Check if admin already exists
        $existingAdmin = $this->db->fetchOne(
            "SELECT id FROM users WHERE email = :email AND user_type = 'admin'",
            ['email' => $adminData['email']]
        );

        if ($existingAdmin) {
            throw new Exception("Admin user already exists with this email");
        }

        // Begin transaction
        $this->db->beginTransaction();

        try {
            // Create admin user
            $adminId = $this->createAdminUser($adminData);
            
            // Generate .htpasswd file for HTTP authentication
            $this->generateHtpasswd($adminData['username'], $adminData['password']);
            
            // Create admin session
            $sessionData = $this->createAdminSession($adminId);
            
            // Log admin activity
            $this->dashboard->logAdminActivity(
                $adminId,
                'system_settings',
                'Initial admin system setup completed',
                ['onboarding_complete' => true]
            );

            $this->db->commit();

            return [
                'success' => true,
                'admin_id' => $adminId,
                'session' => $sessionData,
                'onboarding_complete' => true,
                'message' => 'Admin system initialized successfully'
            ];

        } catch (Exception $e) {
            $this->db->rollback();
            throw new Exception("Admin system initialization failed: " . $e->getMessage());
        }
    }

    /**
     * Process business onboarding
     * 
     * @param int $businessId
     * @param int $adminId
     * @param array $onboardingData
     * @return array
     * @throws Exception
     */
    public function processBusinessOnboarding(int $businessId, int $adminId, array $onboardingData): array
    {
        $business = $this->db->fetchOne(
            "SELECT * FROM business_profiles WHERE id = :id",
            ['id' => $businessId]
        );

        if (!$business) {
            throw new Exception("Business not found");
        }

        $workflowSteps = [
            'document_verification',
            'background_check',
            'accreditation_review',
            'final_approval'
        ];

        $results = [];
        $currentStep = $onboardingData['current_step'] ?? 'document_verification';

        foreach ($workflowSteps as $step) {
            if ($this->shouldProcessStep($step, $currentStep, $onboardingData)) {
                $methodName = 'process' . str_replace('_', '', ucwords($step, '_'));
                if (method_exists($this, $methodName)) {
                    $results[$step] = $this->$methodName($businessId, $adminId, $onboardingData);
                    
                    if (!$results[$step]['success']) {
                        throw new Exception("Onboarding failed at step: {$step}");
                    }
                }
            }
        }

        // Update business status
        if ($this->isOnboardingComplete($results)) {
            $this->updateBusinessStatus($businessId, 'approved', $adminId);
            
            $this->dashboard->logAdminActivity(
                $adminId,
                'business_management',
                "Business onboarding completed for: {$business['business_name']}",
                ['business_id' => $businessId, 'onboarding_results' => $results]
            );
        }

        return [
            'success' => true,
            'business_id' => $businessId,
            'onboarding_steps' => $results,
            'onboarding_complete' => $this->isOnboardingComplete($results),
            'next_steps' => $this->getNextOnboardingSteps($results)
        ];
    }

    /**
     * Handle accreditation review
     * 
     * @param int $accreditationId
     * @param int $adminId
     * @param array $reviewData
     * @return array
     * @throws Exception
     */
    public function processAccreditationReview(int $accreditationId, int $adminId, array $reviewData): array
    {
        $accreditation = $this->db->fetchOne(
            "SELECT * FROM business_accreditations WHERE id = :id",
            ['id' => $accreditationId]
        );

        if (!$accreditation) {
            throw new Exception("Accreditation application not found");
        }

        $requiredFields = ['decision', 'review_notes'];
        foreach ($requiredFields as $field) {
            if (empty($reviewData[$field])) {
                throw new Exception("Missing required field: {$field}");
            }
        }

        $validDecisions = ['approved', 'rejected', 'pending_information'];
        if (!in_array($reviewData['decision'], $validDecisions)) {
            throw new Exception("Invalid decision. Must be: " . implode(', ', $validDecisions));
        }

        $this->db->beginTransaction();

        try {
            // Update accreditation status
            $updateData = [
                'status' => $reviewData['decision'],
                'review_notes' => $reviewData['review_notes'],
                'reviewed_by' => $adminId,
                'reviewed_at' => date('Y-m-d H:i:s'),
                'accreditation_level' => $reviewData['accreditation_level'] ?? $accreditation['accreditation_level']
            ];

            if ($reviewData['decision'] === 'approved') {
                $updateData['accredited_at'] = date('Y-m-d H:i:s');
                $updateData['expires_at'] = date('Y-m-d H:i:s', strtotime('+1 year'));
            }

            $setParts = [];
            foreach (array_keys($updateData) as $field) {
                $setParts[] = "{$field} = :{$field}";
            }

            $query = "UPDATE business_accreditations SET " . implode(', ', $setParts) . " WHERE id = :id";
            $updateData['id'] = $accreditationId;
            $this->db->query($query, $updateData);

            // Update business profile accreditation level
            if ($reviewData['decision'] === 'approved') {
                $this->db->query(
                    "UPDATE business_profiles SET accreditation_level = :level WHERE id = :id",
                    [
                        'level' => $updateData['accreditation_level'],
                        'id' => $accreditation['business_id']
                    ]
                );
            }

            // Log activity
            $this->dashboard->logAdminActivity(
                $adminId,
                'business_management',
                "Accreditation {$reviewData['decision']} for business ID: {$accreditation['business_id']}",
                [
                    'accreditation_id' => $accreditationId,
                    'accreditation_level' => $updateData['accreditation_level'],
                    'decision' => $reviewData['decision']
                ]
            );

            $this->db->commit();

            return [
                'success' => true,
                'accreditation_id' => $accreditationId,
                'decision' => $reviewData['decision'],
                'accreditation_level' => $updateData['accreditation_level'],
                'business_id' => $accreditation['business_id']
            ];

        } catch (Exception $e) {
            $this->db->rollback();
            throw new Exception("Accreditation review failed: " . $e->getMessage());
        }
    }

    /**
     * Moderate complaint
     * 
     * @param int $complaintId
     * @param int $adminId
     * @param array $moderationData
     * @return array
     * @throws Exception
     */
    public function moderateComplaint(int $complaintId, int $adminId, array $moderationData): array
    {
        $complaint = $this->db->fetchOne(
            "SELECT * FROM complaints WHERE id = :id",
            ['id' => $complaintId]
        );

        if (!$complaint) {
            throw new Exception("Complaint not found");
        }

        $requiredFields = ['action', 'resolution_notes'];
        foreach ($requiredFields as $field) {
            if (empty($moderationData[$field])) {
                throw new Exception("Missing required field: {$field}");
            }
        }

        $validActions = ['escalate', 'resolve', 'request_info', 'dismiss'];
        if (!in_array($moderationData['action'], $validActions)) {
            throw new Exception("Invalid action. Must be: " . implode(', ', $validActions));
        }

        $this->db->beginTransaction();

        try {
            $updateData = [
                'status' => $this->getComplaintStatusForAction($moderationData['action']),
                'resolution_notes' => $moderationData['resolution_notes'],
                'moderated_by' => $adminId,
                'moderated_at' => date('Y-m-d H:i:s')
            ];

            if ($moderationData['action'] === 'resolve') {
                $updateData['resolved_at'] = date('Y-m-d H:i:s');
            }

            $setParts = [];
            foreach (array_keys($updateData) as $field) {
                $setParts[] = "{$field} = :{$field}";
            }

            $query = "UPDATE complaints SET " . implode(', ', $setParts) . " WHERE id = :id";
            $updateData['id'] = $complaintId;
            $this->db->query($query, $updateData);

            // Log moderation activity
            $this->dashboard->logAdminActivity(
                $adminId,
                'complaint_moderation',
                "Complaint {$moderationData['action']} for complaint ID: {$complaintId}",
                [
                    'complaint_id' => $complaintId,
                    'action' => $moderationData['action'],
                    'previous_status' => $complaint['status']
                ]
            );

            $this->db->commit();

            return [
                'success' => true,
                'complaint_id' => $complaintId,
                'action' => $moderationData['action'],
                'new_status' => $updateData['status'],
                'resolution_notes' => $moderationData['resolution_notes']
            ];

        } catch (Exception $e) {
            $this->db->rollback();
            throw new Exception("Complaint moderation failed: " . $e->getMessage());
        }
    }

    /**
     * Manage user account
     * 
     * @param int $userId
     * @param int $adminId
     * @param array $managementData
     * @return array
     * @throws Exception
     */
    public function manageUserAccount(int $userId, int $adminId, array $managementData): array
    {
        $user = $this->db->fetchOne(
            "SELECT * FROM users WHERE id = :id",
            ['id' => $userId]
        );

        if (!$user) {
            throw new Exception("User not found");
        }

        $requiredFields = ['action', 'reason'];
        foreach ($requiredFields as $field) {
            if (empty($managementData[$field])) {
                throw new Exception("Missing required field: {$field}");
            }
        }

        $validActions = ['suspend', 'activate', 'verify', 'delete', 'warn'];
        if (!in_array($managementData['action'], $validActions)) {
            throw new Exception("Invalid action. Must be: " . implode(', ', $validActions));
        }

        $this->db->beginTransaction();

        try {
            $updateData = [];
            $actionMethods = [
                'suspend' => 'suspendUser',
                'activate' => 'activateUser',
                'verify' => 'verifyUser',
                'delete' => 'deleteUser',
                'warn' => 'warnUser'
            ];

            $methodName = $actionMethods[$managementData['action']];
            $result = $this->$methodName($userId, $adminId, $managementData);

            // Log management activity
            $this->dashboard->logAdminActivity(
                $adminId,
                'user_management',
                "User {$managementData['action']} for user ID: {$userId}",
                [
                    'user_id' => $userId,
                    'action' => $managementData['action'],
                    'reason' => $managementData['reason'],
                    'previous_status' => $user['status']
                ]
            );

            $this->db->commit();

            return [
                'success' => true,
                'user_id' => $userId,
                'action' => $managementData['action'],
                'result' => $result
            ];

        } catch (Exception $e) {
            $this->db->rollback();
            throw new Exception("User management failed: " . $e->getMessage());
        }
    }

    /**
     * Generate system reports
     * 
     * @param array $reportCriteria
     * @param int $adminId
     * @return array
     * @throws Exception
     */
    public function generateSystemReports(array $reportCriteria, int $adminId): array
    {
        $validReportTypes = [
            'user_analytics',
            'business_analytics', 
            'complaint_analytics',
            'revenue_analytics',
            'system_performance'
        ];

        if (empty($reportCriteria['report_type']) || !in_array($reportCriteria['report_type'], $validReportTypes)) {
            throw new Exception("Invalid report type. Must be: " . implode(', ', $validReportTypes));
        }

        $reportData = [];
        $reportMethods = [
            'user_analytics' => 'generateUserAnalyticsReport',
            'business_analytics' => 'generateBusinessAnalyticsReport',
            'complaint_analytics' => 'generateComplaintAnalyticsReport',
            'revenue_analytics' => 'generateRevenueAnalyticsReport',
            'system_performance' => 'generateSystemPerformanceReport'
        ];

        $methodName = $reportMethods[$reportCriteria['report_type']];
        if (method_exists($this, $methodName)) {
            $reportData = $this->$methodName($reportCriteria);
        }

        // Log report generation
        $this->dashboard->logAdminActivity(
            $adminId,
            'system_settings',
            "Generated {$reportCriteria['report_type']} report",
            [
                'report_criteria' => $reportCriteria,
                'report_timestamp' => date('Y-m-d H:i:s')
            ]
        );

        return [
            'success' => true,
            'report_type' => $reportCriteria['report_type'],
            'generated_at' => date('Y-m-d H:i:s'),
            'criteria' => $reportCriteria,
            'data' => $reportData
        ];
    }

    /**
     * Update system configuration
     * 
     * @param array $configUpdates
     * @param int $adminId
     * @return array
     * @throws Exception
     */
    public function updateSystemConfiguration(array $configUpdates, int $adminId): array
    {
        if (empty($configUpdates)) {
            throw new Exception("No configuration updates provided");
        }

        $results = [];
        $validSections = [
            'general', 'security', 'email', 'payment', 
            'accreditation', 'moderation', 'api'
        ];

        foreach ($configUpdates as $section => $settings) {
            if (!in_array($section, $validSections)) {
                throw new Exception("Invalid configuration section: {$section}");
            }

            foreach ($settings as $key => $value) {
                $result = $this->dashboard->updateSystemSetting(
                    "{$section}.{$key}",
                    $value,
                    $adminId
                );

                $results["{$section}.{$key}"] = $result;
            }
        }

        // Log configuration update
        $this->dashboard->logAdminActivity(
            $adminId,
            'system_settings',
            "System configuration updated",
            [
                'sections_updated' => array_keys($configUpdates),
                'total_updates' => count($results),
                'update_timestamp' => date('Y-m-d H:i:s')
            ]
        );

        return [
            'success' => true,
            'updates_applied' => count($results),
            'results' => $results,
            'updated_at' => date('Y-m-d H:i:s')
        ];
    }

    // Private helper methods

    private function createAdminUser(array $adminData): int
    {
        $userData = [
            'username' => $adminData['username'],
            'email' => $adminData['email'],
            'password_hash' => password_hash($adminData['password'], PASSWORD_DEFAULT),
            'first_name' => $adminData['first_name'],
            'last_name' => $adminData['last_name'],
            'user_type' => 'admin',
            'verification_level' => 'full',
            'status' => 'active',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $columns = implode(', ', array_keys($userData));
        $placeholders = ':' . implode(', :', array_keys($userData));
        
        $query = "INSERT INTO users ({$columns}) VALUES ({$placeholders})";
        $this->db->query($query, $userData);

        return $this->db->lastInsertId();
    }

    private function generateHtpasswd(string $username, string $password): void
    {
        $htpasswdContent = $username . ':' . password_hash($password, PASSWORD_BCRYPT) . "\n";
        $htpasswdPath = dirname(__DIR__, 3) . '/.htpasswd';
        
        if (file_put_contents($htpasswdPath, $htpasswdContent) === false) {
            throw new Exception("Failed to generate .htpasswd file");
        }

        // Set secure permissions
        chmod($htpasswdPath, 0640);
    }

    private function createAdminSession(int $adminId): array
    {
        $sessionToken = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', strtotime('+24 hours'));

        $sessionData = [
            'user_id' => $adminId,
            'session_token' => $sessionToken,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            'expires_at' => $expiresAt,
            'created_at' => date('Y-m-d H:i:s')
        ];

        $columns = implode(', ', array_keys($sessionData));
        $placeholders = ':' . implode(', :', array_keys($sessionData));
        
        $query = "INSERT INTO admin_sessions ({$columns}) VALUES ({$placeholders})";
        $this->db->query($query, $sessionData);

        return [
            'session_token' => $sessionToken,
            'expires_at' => $expiresAt,
            'admin_id' => $adminId
        ];
    }

    private function shouldProcessStep(string $step, string $currentStep, array $onboardingData): bool
    {
        if ($step === $currentStep) {
            return true;
        }

        if (!empty($onboardingData['process_all_steps'])) {
            return true;
        }

        return false;
    }

    private function processDocumentVerification(int $businessId, int $adminId, array $onboardingData): array
    {
        $documents = $this->db->fetchAll(
            "SELECT * FROM business_documents WHERE business_id = :id AND status = 'pending'",
            ['id' => $businessId]
        );

        $verificationResults = [];
        foreach ($documents as $document) {
            $verificationResults[$document['id']] = $this->verifyDocument($document, $adminId);
        }

        return [
            'success' => true,
            'step' => 'document_verification',
            'documents_processed' => count($documents),
            'verification_results' => $verificationResults
        ];
    }

    private function processBackgroundCheck(int $businessId, int $adminId, array $onboardingData): array
    {
        $business = $this->db->fetchOne(
            "SELECT * FROM business_profiles WHERE id = :id",
            ['id' => $businessId]
        );

        $backgroundCheckResult = $this->performBackgroundCheck($business);

        return [
            'success' => $backgroundCheckResult['passed'],
            'step' => 'background_check',
            'check_result' => $backgroundCheckResult,
            'recommendation' => $backgroundCheckResult['passed'] ? 'approve' : 'review'
        ];
    }

    private function processAccreditationReviewStep(int $businessId, int $adminId, array $onboardingData): array
    {
        $accreditation = $this->db->fetchOne(
            "SELECT * FROM business_accreditations WHERE business_id = :id AND status = 'pending'",
            ['id' => $businessId]
        );

        if (!$accreditation) {
            return [
                'success' => false,
                'step' => 'accreditation_review',
                'error' => 'No pending accreditation found'
            ];
        }

        $reviewData = [
            'decision' => $onboardingData['accreditation_decision'] ?? 'approved',
            'review_notes' => $onboardingData['review_notes'] ?? 'Auto-approved during onboarding',
            'accreditation_level' => $onboardingData['accreditation_level'] ?? 'verified'
        ];

        return $this->processAccreditationReview($accreditation['id'], $adminId, $reviewData);
    }

    private function processFinalApproval(int $businessId, int $adminId, array $onboardingData): array
    {
        // Perform final checks
        $finalChecks = $this->performFinalChecks($businessId);

        if (!$finalChecks['all_passed']) {
            return [
                'success' => false,
                'step' => 'final_approval',
                'failed_checks' => $finalChecks['failed_checks']
            ];
        }

        return [
            'success' => true,
            'step' => 'final_approval',
            'approval_granted' => true,
            'approved_at' => date('Y-m-d H:i:s')
        ];
    }

    private function isOnboardingComplete(array $results): bool
    {
        $requiredSteps = ['document_verification', 'background_check', 'accreditation_review', 'final_approval'];
        
        foreach ($requiredSteps as $step) {
            if (!isset($results[$step]) || !$results[$step]['success']) {
                return false;
            }
        }

        return true;
    }

    private function getNextOnboardingSteps(array $results): array
    {
        $allSteps = ['document_verification', 'background_check', 'accreditation_review', 'final_approval'];
        $nextSteps = [];

        foreach ($allSteps as $step) {
            if (!isset($results[$step]) || !$results[$step]['success']) {
                $nextSteps[] = $step;
            }
        }

        return $nextSteps;
    }

    private function getComplaintStatusForAction(string $action): string
    {
        $statusMap = [
            'escalate' => 'escalated',
            'resolve' => 'resolved',
            'request_info' => 'awaiting_info',
            'dismiss' => 'dismissed'
        ];

        return $statusMap[$action] ?? 'under_review';
    }

    private function suspendUser(int $userId, int $adminId, array $managementData): array
    {
        $this->db->query(
            "UPDATE users SET status = 'suspended', suspended_at = :suspended_at WHERE id = :id",
            [
                'suspended_at' => date('Y-m-d H:i:s'),
                'id' => $userId
            ]
        );

        return ['action' => 'suspended', 'suspended_at' => date('Y-m-d H:i:s')];
    }

    private function activateUser(int $userId, int $adminId, array $managementData): array
    {
        $this->db->query(
            "UPDATE users SET status = 'active', suspended_at = NULL WHERE id = :id",
            ['id' => $userId]
        );

        return ['action' => 'activated', 'activated_at' => date('Y-m-d H:i:s')];
    }

    private function verifyUser(int $userId, int $adminId, array $managementData): array
    {
        $this->db->query(
            "UPDATE users SET verification_level = 'full', verified_at = :verified_at WHERE id = :id",
            [
                'verified_at' => date('Y-m-d H:i:s'),
                'id' => $userId
            ]
        );

        return ['action' => 'verified', 'verified_at' => date('Y-m-d H:i:s')];
    }

    private function deleteUser(int $userId, int $adminId, array $managementData): array
    {
        // Soft delete - update status to deleted
        $this->db->query(
            "UPDATE users SET status = 'deleted', deleted_at = :deleted_at WHERE id = :id",
            [
                'deleted_at' => date('Y-m-d H:i:s'),
                'id' => $userId
            ]
        );

        return ['action' => 'deleted', 'deleted_at' => date('Y-m-d H:i:s')];
    }

    private function warnUser(int $userId, int $adminId, array $managementData): array
    {
        // Create user warning record
        $warningData = [
            'user_id' => $userId,
            'admin_id' => $adminId,
            'warning_type' => $managementData['warning_type'] ?? 'general',
            'message' => $managementData['warning_message'] ?? 'Please review our community guidelines',
            'created_at' => date('Y-m-d H:i:s')
        ];

        $columns = implode(', ', array_keys($warningData));
        $placeholders = ':' . implode(', :', array_keys($warningData));
        
        $query = "INSERT INTO user_warnings ({$columns}) VALUES ({$placeholders})";
        $this->db->query($query, $warningData);

        return [
            'action' => 'warned',
            'warning_id' => $this->db->lastInsertId(),
            'warning_type' => $warningData['warning_type']
        ];
    }

    private function updateBusinessStatus(int $businessId, string $status, int $adminId): void
    {
        $this->db->query(
            "UPDATE business_profiles SET status = :status, updated_at = :updated_at WHERE id = :id",
            [
                'status' => $status,
                'updated_at' => date('Y-m-d H:i:s'),
                'id' => $businessId
            ]
        );
    }

    private function verifyDocument(array $document, int $adminId): array
    {
        // Simulate document verification process
        $verificationMethods = [
            'authenticity_check' => ['passed' => true, 'score' => 95],
            'expiry_check' => ['passed' => true, 'score' => 100],
            'format_validation' => ['passed' => true, 'score' => 90]
        ];

        $allPassed = true;
        $totalScore = 0;
        $methodCount = count($verificationMethods);

        foreach ($verificationMethods as $method => $result) {
            if (!$result['passed']) {
                $allPassed = false;
            }
            $totalScore += $result['score'];
        }

        $averageScore = $totalScore / $methodCount;
        $status = $allPassed && $averageScore >= 80 ? 'approved' : 'rejected';

        // Update document status
        $this->db->query(
            "UPDATE business_documents SET status = :status, verified_by = :admin_id, verified_at = :verified_at WHERE id = :id",
            [
                'status' => $status,
                'admin_id' => $adminId,
                'verified_at' => date('Y-m-d H:i:s'),
                'id' => $document['id']
            ]
        );

        return [
            'document_id' => $document['id'],
            'document_type' => $document['document_type'],
            'status' => $status,
            'verification_score' => $averageScore,
            'all_checks_passed' => $allPassed
        ];
    }

    private function performBackgroundCheck(array $business): array
    {
        // Simulate background check process
        $checkResults = [
            'legal_history' => ['passed' => true, 'details' => 'No legal issues found'],
            'financial_stability' => ['passed' => true, 'details' => 'Financially stable'],
            'reputation_check' => ['passed' => true, 'details' => 'Good reputation'],
            'compliance_check' => ['passed' => true, 'details' => 'Compliant with regulations']
        ];

        $passedChecks = 0;
        $totalChecks = count($checkResults);

        foreach ($checkResults as $check) {
            if ($check['passed']) {
                $passedChecks++;
            }
        }

        return [
            'passed' => $passedChecks === $totalChecks,
            'pass_rate' => ($passedChecks / $totalChecks) * 100,
            'check_results' => $checkResults,
            'recommendation' => $passedChecks === $totalChecks ? 'approve' : 'review'
        ];
    }

    private function performFinalChecks(int $businessId): array
    {
        $checks = [
            'documents_approved' => $this->db->fetchColumn(
                "SELECT COUNT(*) FROM business_documents WHERE business_id = :id AND status = 'approved'",
                ['id' => $businessId]
            ) > 0,
            'accreditation_approved' => $this->db->fetchColumn(
                "SELECT COUNT(*) FROM business_accreditations WHERE business_id = :id AND status = 'approved'",
                ['id' => $businessId]
            ) > 0,
            'background_check_passed' => true, // This would come from actual background check
            'payment_verified' => $this->db->fetchColumn(
                "SELECT COUNT(*) FROM business_subscriptions WHERE business_id = :id AND status = 'active'",
                ['id' => $businessId]
            ) > 0
        ];

        $failedChecks = [];
        $allPassed = true;

        foreach ($checks as $checkName => $passed) {
            if (!$passed) {
                $allPassed = false;
                $failedChecks[] = $checkName;
            }
        }

        return [
            'all_passed' => $allPassed,
            'failed_checks' => $failedChecks,
            'check_details' => $checks
        ];
    }

    private function loadAdminPermissions(): array
    {
        return [
            'user_management' => ['view', 'edit', 'delete', 'suspend'],
            'business_management' => ['view', 'edit', 'approve', 'reject'],
            'complaint_moderation' => ['view', 'resolve', 'escalate', 'dismiss'],
            'system_settings' => ['view', 'edit'],
            'reports' => ['generate', 'view'],
            'accreditation' => ['review', 'approve', 'reject']
        ];
    }

    // Report generation methods
    private function generateUserAnalyticsReport(array $criteria): array
    {
        return $this->dashboard->getAnalyticsReporting($criteria['period'] ?? '30days')['user_analytics'];
    }

    private function generateBusinessAnalyticsReport(array $criteria): array
    {
        return $this->dashboard->getAnalyticsReporting($criteria['period'] ?? '30days')['business_analytics'];
    }

    private function generateComplaintAnalyticsReport(array $criteria): array
    {
        return $this->dashboard->getAnalyticsReporting($criteria['period'] ?? '30days')['complaint_analytics'];
    }

    private function generateRevenueAnalyticsReport(array $criteria): array
    {
        return $this->dashboard->getAnalyticsReporting($criteria['period'] ?? '30days')['revenue_analytics'];
    }

    private function generateSystemPerformanceReport(array $criteria): array
    {
        return $this->dashboard->getSystemMonitoring();
    }
}
