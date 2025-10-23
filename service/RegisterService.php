<?php
/**
 * P.I.M.P - Register Service
 * Handles user and business registration logic
 */

namespace PIMP\Services;

use PIMP\Models\UserModel;
use PIMP\Models\BusinessModel;
use PIMP\Services\Database\MySQLDatabase;
use PIMP\Services\EmailService;
use Exception;

class RegisterService
{
    private $db;
    private $userModel;
    private $businessModel;
    private $emailService;

    public function __construct(MySQLDatabase $db, EmailService $emailService)
    {
        $this->db = $db;
        $this->userModel = new UserModel($db);
        $this->businessModel = new BusinessModel($db);
        $this->emailService = $emailService;
    }

    /**
     * Register a new user
     * 
     * @param array $data
     * @return array
     */
    public function registerUser(array $data): array
    {
        try {
            // Validate input
            $validation = $this->validateUserRegistration($data);
            if (!$validation['valid']) {
                return [
                    'success' => false,
                    'errors' => $validation['errors']
                ];
            }

            // Check if email already exists
            if ($this->userModel->findByEmail($data['email'])) {
                return [
                    'success' => false,
                    'errors' => ['email' => 'Email address is already registered']
                ];
            }

            // Check if username exists (generate from email if not provided)
            $username = $data['username'] ?? $this->generateUsername($data['email']);
            if ($this->userModel->findByUsername($username)) {
                $username = $this->generateUsername($data['email'], true);
            }

            // Begin transaction
            $this->db->beginTransaction();

            try {
                // Prepare name JSON
                $nameJson = json_encode([
                    'first_name' => $data['first_name'],
                    'last_name' => $data['last_name'],
                    'full_name' => $data['first_name'] . ' ' . $data['last_name']
                ]);

                // Prepare phone JSON if provided
                $phoneJson = null;
                if (!empty($data['phone'])) {
                    $phoneJson = json_encode([
                        'primary' => $data['phone'],
                        'verified' => false
                    ]);
                }

                // Create user
                $userId = $this->userModel->create([
                    'username' => $username,
                    'name_json' => $nameJson,
                    'email' => $data['email'],
                    'phone_json' => $phoneJson,
                    'status' => 'pending_verification'
                ]);

                // Store password (plain text as requested)
                $this->userModel->storeCredentials($userId, 'password', $data['password']);

                // Assign default role
                $this->assignDefaultRole($userId, $data['user_type'] ?? 'consumer');

                // Generate verification token
                $verificationToken = $this->generateVerificationToken($userId);

                // Send verification email
                $this->emailService->sendVerificationEmail(
                    $data['email'],
                    $data['first_name'],
                    $verificationToken
                );

                // Commit transaction
                $this->db->commit();

                // Log audit
                $this->logAudit('user_registration', $userId, [
                    'email' => $data['email'],
                    'user_type' => $data['user_type'] ?? 'consumer'
                ]);

                return [
                    'success' => true,
                    'user_id' => $userId,
                    'message' => 'Registration successful. Please check your email to verify your account.'
                ];

            } catch (Exception $e) {
                $this->db->rollback();
                throw $e;
            }

        } catch (Exception $e) {
            error_log("Registration error: " . $e->getMessage());
            return [
                'success' => false,
                'errors' => ['general' => 'An error occurred during registration. Please try again.']
            ];
        }
    }

    /**
     * Register a new business
     * 
     * @param array $userData
     * @param array $businessData
     * @return array
     */
    public function registerBusiness(array $userData, array $businessData): array
    {
        try {
            // Validate user data
            $userValidation = $this->validateUserRegistration($userData);
            if (!$userValidation['valid']) {
                return [
                    'success' => false,
                    'errors' => $userValidation['errors']
                ];
            }

            // Validate business data
            $businessValidation = $this->validateBusinessData($businessData);
            if (!$businessValidation['valid']) {
                return [
                    'success' => false,
                    'errors' => array_merge(
                        $userValidation['errors'] ?? [],
                        $businessValidation['errors']
                    )
                ];
            }

            // Begin transaction
            $this->db->beginTransaction();

            try {
                // Register user first
                $userResult = $this->registerUser($userData);
                if (!$userResult['success']) {
                    $this->db->rollback();
                    return $userResult;
                }

                $userId = $userResult['user_id'];

                // Create business
                $businessId = $this->businessModel->create([
                    'legal_name' => $businessData['legal_name'],
                    'trading_name' => $businessData['trading_name'] ?? null,
                    'description' => $businessData['description'] ?? null,
                    'business_type' => $businessData['business_type'] ?? 'corporation',
                    'industry_sector' => $businessData['industry_sector'] ?? null,
                    'category_id' => $businessData['category_id'] ?? null,
                    'contact_info_json' => json_encode([
                        'email' => $userData['email'],
                        'phone' => $userData['phone'] ?? null
                    ]),
                    'owner_user_id' => $userId,
                    'status' => 'pending'
                ]);

                // Assign business owner role
                $this->assignBusinessOwnerRole($userId, $businessId);

                // Commit transaction
                $this->db->commit();

                // Log audit
                $this->logAudit('business_registration', $businessId, [
                    'user_id' => $userId,
                    'business_name' => $businessData['legal_name']
                ]);

                return [
                    'success' => true,
                    'user_id' => $userId,
                    'business_id' => $businessId,
                    'message' => 'Business registration successful. Please verify your email to activate your account.'
                ];

            } catch (Exception $e) {
                $this->db->rollback();
                throw $e;
            }

        } catch (Exception $e) {
            error_log("Business registration error: " . $e->getMessage());
            return [
                'success' => false,
                'errors' => ['general' => 'An error occurred during business registration. Please try again.']
            ];
        }
    }

    /**
     * Validate user registration data
     * 
     * @param array $data
     * @return array
     */
    private function validateUserRegistration(array $data): array
    {
        $errors = [];

        // First name
        if (empty($data['first_name'])) {
            $errors['first_name'] = 'First name is required';
        } elseif (strlen($data['first_name']) < 2) {
            $errors['first_name'] = 'First name must be at least 2 characters';
        }

        // Last name
        if (empty($data['last_name'])) {
            $errors['last_name'] = 'Last name is required';
        } elseif (strlen($data['last_name']) < 2) {
            $errors['last_name'] = 'Last name must be at least 2 characters';
        }

        // Email
        if (empty($data['email'])) {
            $errors['email'] = 'Email is required';
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Please enter a valid email address';
        }

        // Password
        if (empty($data['password'])) {
            $errors['password'] = 'Password is required';
        } elseif (strlen($data['password']) < 8) {
            $errors['password'] = 'Password must be at least 8 characters';
        }

        // Confirm password
        if (empty($data['confirm_password'])) {
            $errors['confirm_password'] = 'Please confirm your password';
        } elseif ($data['password'] !== $data['confirm_password']) {
            $errors['confirm_password'] = 'Passwords do not match';
        }

        // Terms acceptance
        if (empty($data['terms'])) {
            $errors['terms'] = 'You must accept the terms and conditions';
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }

    /**
     * Validate business data
     * 
     * @param array $data
     * @return array
     */
    private function validateBusinessData(array $data): array
    {
        $errors = [];

        if (empty($data['legal_name'])) {
            $errors['legal_name'] = 'Business legal name is required';
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }

    /**
     * Generate username from email
     * 
     * @param string $email
     * @param bool $addRandom
     * @return string
     */
    private function generateUsername(string $email, bool $addRandom = false): string
    {
        $username = explode('@', $email)[0];
        $username = preg_replace('/[^a-zA-Z0-9_]/', '_', $username);
        
        if ($addRandom) {
            $username .= '_' . substr(md5(microtime()), 0, 6);
        }
        
        return $username;
    }

    /**
     * Generate verification token
     * 
     * @param int $userId
     * @return string
     */
    private function generateVerificationToken(int $userId): string
    {
        $token = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', strtotime('+24 hours'));

        $this->db->query(
            "INSERT INTO user_settings (user_id, setting_key, setting_value, data_type)
             VALUES (?, 'verification_token', ?, 'string')
             ON DUPLICATE KEY UPDATE setting_value = ?",
            [$userId, json_encode(['token' => $token, 'expires_at' => $expiresAt]), json_encode(['token' => $token, 'expires_at' => $expiresAt])]
        );

        return $token;
    }

    /**
     * Assign default role to user
     * 
     * @param int $userId
     * @param string $userType
     * @return void
     */
    private function assignDefaultRole(int $userId, string $userType): void
    {
        $roleName = $userType === 'business' ? 'business_owner' : 'user';
        
        $role = $this->db->fetchOne(
            "SELECT id FROM roles WHERE name = ?",
            [$roleName]
        );

        if ($role) {
            $this->db->query(
                "INSERT INTO user_roles (user_id, role_id, assigned_by)
                 VALUES (?, ?, ?)",
                [$userId, $role['id'], $userId]
            );
        }
    }

    /**
     * Assign business owner role
     * 
     * @param int $userId
     * @param int $businessId
     * @return void
     */
    private function assignBusinessOwnerRole(int $userId, int $businessId): void
    {
        $role = $this->db->fetchOne(
            "SELECT id FROM roles WHERE name = 'business_owner'",
            []
        );

        if ($role) {
            $this->db->query(
                "INSERT INTO user_roles (user_id, role_id, assigned_by, metadata_json)
                 VALUES (?, ?, ?, ?)",
                [$userId, $role['id'], $userId, json_encode(['business_id' => $businessId])]
            );
        }
    }

    /**
     * Log audit event
     * 
     * @param string $eventType
     * @param int $entityId
     * @param array $metadata
     * @return void
     */
    private function logAudit(string $eventType, int $entityId, array $metadata): void
    {
        $this->db->query(
            "INSERT INTO audit_logs (event_type, entity_type, entity_id, action, metadata_json, ip_address, user_agent)
             VALUES (?, ?, ?, 'create', ?, ?, ?)",
            [
                $eventType,
                $eventType === 'user_registration' ? 'user' : 'business',
                $entityId,
                json_encode($metadata),
                $_SERVER['REMOTE_ADDR'] ?? null,
                $_SERVER['HTTP_USER_AGENT'] ?? null
            ]
        );
    }
}
