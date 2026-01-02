<?php
/**
 * P.I.M.P - User API Service
 * Handles user-related API operations
 */

namespace PIMP\Services\API;

use PIMP\Models\UserProfile;
use PIMP\Models\Auth;
use PIMP\Services\Database\MySQLDatabase;
use Exception;

class UserAPIService
{
    /**
     * @var UserProfile
     */
    private $userModel;

    /**
     * @var Auth
     */
    private $authModel;

    /**
     * Constructor
     * 
     * @param MySQLDatabase $db
     */
    public function __construct(MySQLDatabase $db)
    {
        $this->userModel = new UserProfile($db);
        $this->authModel = new Auth($db);
    }

    /**
     * Register new user
     * 
     * @param array $userData
     * @return array
     */
    public function registerUser(array $userData): array
    {
        try {
            $user = $this->userModel->createUser($userData);
            
            return [
                'success' => true,
                'message' => 'User registered successfully',
                'data' => $user
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Login user
     * 
     * @param string $email
     * @param string $password
     * @return array
     */
    public function loginUser(string $email, string $password): array
    {
        try {
            $result = $this->authModel->authenticate($email, $password);

            return [
                'success' => true,
                'message' => 'Login successful',
                'data' => $result
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Validate session
     * 
     * @param string $sessionToken
     * @return array
     */
    public function validateSession(string $sessionToken): array
    {
        try {
            $session = $this->authModel->validateSession($sessionToken);
            
            if (!$session) {
                throw new Exception("Invalid or expired session");
            }

            return [
                'success' => true,
                'message' => 'Session valid',
                'data' => $session
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Logout user
     * 
     * @param string $sessionToken
     * @return array
     */
    public function logoutUser(string $sessionToken): array
    {
        try {
            $this->authModel->logout($sessionToken);

            return [
                'success' => true,
                'message' => 'Logout successful',
                'data' => null
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Get user profile
     * 
     * @param int $userId
     * @return array
     */
    public function getUserProfile(int $userId): array
    {
        try {
            $user = $this->userModel->getUserById($userId);
            
            if (!$user) {
                throw new Exception("User not found");
            }

            return [
                'success' => true,
                'message' => 'User profile retrieved successfully',
                'data' => $user
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Update user profile
     * 
     * @param int $userId
     * @param array $updateData
     * @return array
     */
    public function updateUserProfile(int $userId, array $updateData): array
    {
        try {
            $user = $this->userModel->updateUser($userId, $updateData);

            return [
                'success' => true,
                'message' => 'User profile updated successfully',
                'data' => $user
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Update user preference
     * 
     * @param int $userId
     * @param string $key
     * @param string $value
     * @return array
     */
    public function updateUserPreference(int $userId, string $key, string $value): array
    {
        try {
            $this->userModel->updatePreference($userId, $key, $value);

            return [
                'success' => true,
                'message' => 'User preference updated successfully',
                'data' => null
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Save business for user
     * 
     * @param int $userId
     * @param int $businessId
     * @param string $category
     * @return array
     */
    public function saveBusiness(int $userId, int $businessId, string $category = 'favorites'): array
    {
        try {
            $this->userModel->saveBusiness($userId, $businessId, $category);

            return [
                'success' => true,
                'message' => 'Business saved successfully',
                'data' => null
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Remove saved business
     * 
     * @param int $userId
     * @param int $businessId
     * @param string $category
     * @return array
     */
    public function removeSavedBusiness(int $userId, int $businessId, string $category = 'favorites'): array
    {
        try {
            $this->userModel->removeSavedBusiness($userId, $businessId, $category);

            return [
                'success' => true,
                'message' => 'Business removed from saved items',
                'data' => null
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Get saved businesses
     * 
     * @param int $userId
     * @param array $filters
     * @return array
     */
    public function getSavedBusinesses(int $userId, array $filters = []): array
    {
        try {
            $businesses = $this->userModel->getSavedBusinesses($userId, $filters);

            return [
                'success' => true,
                'message' => 'Saved businesses retrieved successfully',
                'data' => $businesses
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Initiate password reset
     * 
     * @param string $email
     * @return array
     */
    public function initiatePasswordReset(string $email): array
    {
        try {
            $result = $this->authModel->initiatePasswordReset($email);

            return [
                'success' => true,
                'message' => $result['message'],
                'data' => [
                    'reset_token' => $result['reset_token'] ?? null,
                    'expires_at' => $result['expires_at'] ?? null
                ]
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Reset password
     * 
     * @param string $resetToken
     * @param string $newPassword
     * @return array
     */
    public function resetPassword(string $resetToken, string $newPassword): array
    {
        try {
            $this->authModel->resetPassword($resetToken, $newPassword);

            return [
                'success' => true,
                'message' => 'Password reset successfully',
                'data' => null
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Verify user email
     * 
     * @param int $userId
     * @return array
     */
    public function verifyEmail(int $userId): array
    {
        try {
            $this->userModel->verifyEmail($userId);

            return [
                'success' => true,
                'message' => 'Email verified successfully',
                'data' => null
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Get user statistics
     * 
     * @param int $userId
     * @return array
     */
    public function getUserStatistics(int $userId): array
    {
        try {
            $statistics = $this->userModel->getUserStatistics($userId);

            return [
                'success' => true,
                'message' => 'User statistics retrieved successfully',
                'data' => $statistics
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Search users (admin function)
     * 
     * @param array $filters
     * @param int $page
     * @param int $perPage
     * @return array
     */
    public function searchUsers(array $filters = [], int $page = 1, int $perPage = 20): array
    {
        try {
            $result = $this->userModel->searchUsers($filters, $page, $perPage);

            return [
                'success' => true,
                'message' => 'Users search completed',
                'data' => $result
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'data' => null
            ];
        }
    }
}
