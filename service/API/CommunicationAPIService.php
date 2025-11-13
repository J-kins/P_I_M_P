<?php
/**
 * P.I.M.P - Communication API Service
 * Handles messaging, notifications, and chat API operations
 */

namespace PIMP\Services\API;

use PIMP\Models\Communication;
use PIMP\Services\Database\MySQLDatabase;
use Exception;

class CommunicationAPIService
{
    /**
     * @var Communication
     */
    private $communicationModel;

    /**
     * Constructor
     * 
     * @param MySQLDatabase $db
     */
    public function __construct(MySQLDatabase $db)
    {
        $this->communicationModel = new Communication($db);
    }

    /**
     * Send message
     * 
     * @param int $fromUserId
     * @param int $toUserId
     * @param string $subject
     * @param string $content
     * @param string $messageType
     * @param array $additionalData
     * @return array
     */
    public function sendMessage(int $fromUserId, int $toUserId, string $subject, string $content, string $messageType = 'email', array $additionalData = []): array
    {
        try {
            $message = $this->communicationModel->sendMessage($fromUserId, $toUserId, $subject, $content, $messageType, $additionalData);

            return [
                'success' => true,
                'message' => 'Message sent successfully',
                'data' => $message
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
     * Get user inbox
     * 
     * @param int $userId
     * @param array $filters
     * @param int $page
     * @param int $perPage
     * @return array
     */
    public function getUserInbox(int $userId, array $filters = [], int $page = 1, int $perPage = 20): array
    {
        try {
            $result = $this->communicationModel->getUserInbox($userId, $filters, $page, $perPage);

            return [
                'success' => true,
                'message' => 'Inbox retrieved successfully',
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
     * Mark message as read
     * 
     * @param int $messageId
     * @param int $userId
     * @return array
     */
    public function markMessageAsRead(int $messageId, int $userId): array
    {
        try {
            $this->communicationModel->markMessageAsRead($messageId, $userId);

            return [
                'success' => true,
                'message' => 'Message marked as read',
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
     * Create notification
     * 
     * @param int $userId
     * @param string $type
     * @param string $title
     * @param string $message
     * @param array $data
     * @return array
     */
    public function createNotification(int $userId, string $type, string $title, string $message, array $data = []): array
    {
        try {
            $notification = $this->communicationModel->createNotification($userId, $type, $title, $message, $data);

            return [
                'success' => true,
                'message' => 'Notification created successfully',
                'data' => $notification
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
     * Get user notifications
     * 
     * @param int $userId
     * @param array $filters
     * @param int $limit
     * @return array
     */
    public function getUserNotifications(int $userId, array $filters = [], int $limit = 50): array
    {
        try {
            $notifications = $this->communicationModel->getUserNotifications($userId, $filters, $limit);

            return [
                'success' => true,
                'message' => 'Notifications retrieved successfully',
                'data' => $notifications
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
     * Mark notification as read
     * 
     * @param int $notificationId
     * @param int $userId
     * @return array
     */
    public function markNotificationAsRead(int $notificationId, int $userId): array
    {
        try {
            $this->communicationModel->markNotificationAsRead($notificationId, $userId);

            return [
                'success' => true,
                'message' => 'Notification marked as read',
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
     * Mark all notifications as read
     * 
     * @param int $userId
     * @return array
     */
    public function markAllNotificationsAsRead(int $userId): array
    {
        try {
            $this->communicationModel->markAllNotificationsAsRead($userId);

            return [
                'success' => true,
                'message' => 'All notifications marked as read',
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
     * Get unread notification count
     * 
     * @param int $userId
     * @return array
     */
    public function getUnreadNotificationCount(int $userId): array
    {
        try {
            $count = $this->communicationModel->getUnreadNotificationCount($userId);

            return [
                'success' => true,
                'message' => 'Unread notification count retrieved',
                'data' => [
                    'count' => $count
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
     * Create chat session
     * 
     * @param int $userId1
     * @param int $userId2
     * @param string $sessionType
     * @param array $sessionData
     * @return array
     */
    public function createChatSession(int $userId1, int $userId2, string $sessionType = 'direct', array $sessionData = []): array
    {
        try {
            $session = $this->communicationModel->createChatSession($userId1, $userId2, $sessionType, $sessionData);

            return [
                'success' => true,
                'message' => 'Chat session created successfully',
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
     * Get user chat sessions
     * 
     * @param int $userId
     * @return array
     */
    public function getUserChatSessions(int $userId): array
    {
        try {
            $sessions = $this->communicationModel->getUserChatSessions($userId);

            return [
                'success' => true,
                'message' => 'Chat sessions retrieved successfully',
                'data' => $sessions
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
     * Send chat message
     * 
     * @param int $sessionId
     * @param int $senderId
     * @param string $message
     * @param string $messageType
     * @return array
     */
    public function sendChatMessage(int $sessionId, int $senderId, string $message, string $messageType = 'text'): array
    {
        try {
            $chatMessage = $this->communicationModel->sendChatMessage($sessionId, $senderId, $message, $messageType);

            return [
                'success' => true,
                'message' => 'Chat message sent successfully',
                'data' => $chatMessage
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
     * Get chat messages
     * 
     * @param int $sessionId
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getChatMessages(int $sessionId, int $limit = 50, int $offset = 0): array
    {
        try {
            $messages = $this->communicationModel->getChatMessages($sessionId, $limit, $offset);

            return [
                'success' => true,
                'message' => 'Chat messages retrieved successfully',
                'data' => $messages
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
     * Mark chat messages as read
     * 
     * @param int $sessionId
     * @param int $userId
     * @return array
     */
    public function markChatMessagesAsRead(int $sessionId, int $userId): array
    {
        try {
            $this->communicationModel->markChatMessagesAsRead($sessionId, $userId);

            return [
                'success' => true,
                'message' => 'Chat messages marked as read',
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
     * Get communication statistics
     * 
     * @param int $userId
     * @return array
     */
    public function getCommunicationStatistics(int $userId): array
    {
        try {
            $statistics = $this->communicationModel->getCommunicationStatistics($userId);

            return [
                'success' => true,
                'message' => 'Communication statistics retrieved successfully',
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
}
