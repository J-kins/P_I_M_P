<?php
/**
 * P.I.M.P - Communication Models
 * Handles inbox system, chat sessions, notifications, and newsletters
 */

namespace PIMP\Models;

use PIMP\Services\Database\MySQLDatabase;
use PDOException;
use Exception;

class Communication
{
    /**
     * @var MySQLDatabase Database instance
     */
    private $db;

    /**
     * @var string Messages table name
     */
    private $messagesTable = 'messages';

    /**
     * @var string Conversations table
     */
    private $conversationsTable = 'conversations';

    /**
     * @var string Notifications table
     */
    private $notificationsTable = 'notifications';

    /**
     * @var string Chat sessions table
     */
    private $chatSessionsTable = 'chat_sessions';

    /**
     * @var string Chat messages table
     */
    private $chatMessagesTable = 'chat_messages';

    /**
     * Message types
     */
    const TYPE_EMAIL = 'email';
    const TYPE_SYSTEM = 'system';
    const TYPE_NOTIFICATION = 'notification';
    const TYPE_CHAT = 'chat';

    /**
     * Notification types
     */
    const NOTIFICATION_NEW_REVIEW = 'new_review';
    const NOTIFICATION_NEW_COMPLAINT = 'new_complaint';
    const NOTIFICATION_STATUS_UPDATE = 'status_update';
    const NOTIFICATION_MESSAGE = 'new_message';
    const NOTIFICATION_SYSTEM = 'system_alert';
    const NOTIFICATION_PROMOTIONAL = 'promotional';

    /**
     * Notification status
     */
    const NOTIFICATION_UNREAD = 'unread';
    const NOTIFICATION_READ = 'read';
    const NOTIFICATION_DISMISSED = 'dismissed';

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
     * Send message
     * 
     * @param int $fromUserId
     * @param int $toUserId
     * @param string $subject
     * @param string $content
     * @param string $messageType
     * @param array $additionalData
     * @return array
     * @throws Exception
     */
    public function sendMessage(int $fromUserId, int $toUserId, string $subject, string $content, string $messageType = self::TYPE_EMAIL, array $additionalData = []): array
    {
        $validTypes = [self::TYPE_EMAIL, self::TYPE_SYSTEM, self::TYPE_NOTIFICATION];
        if (!in_array($messageType, $validTypes)) {
            throw new Exception("Invalid message type: {$messageType}");
        }

        $messageData = [
            'from_user_id' => $fromUserId,
            'to_user_id' => $toUserId,
            'subject' => $subject,
            'content' => $content,
            'message_type' => $messageType,
            'is_read' => false,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        // Merge additional data
        $messageData = array_merge($messageData, $additionalData);

        $columns = implode(', ', array_keys($messageData));
        $placeholders = ':' . implode(', :', array_keys($messageData));
        
        $query = "INSERT INTO {$this->messagesTable} ({$columns}) VALUES ({$placeholders})";
        $this->db->query($query, $messageData);

        $messageId = $this->db->lastInsertId();
        return $this->getMessage($messageId);
    }

    /**
     * Get message by ID
     * 
     * @param int $messageId
     * @return array|null
     */
    public function getMessage(int $messageId): ?array
    {
        $query = "SELECT m.*, 
                         from_user.first_name as from_first_name, from_user.last_name as from_last_name,
                         to_user.first_name as to_first_name, to_user.last_name as to_last_name
                  FROM {$this->messagesTable} m
                  INNER JOIN users from_user ON m.from_user_id = from_user.id
                  INNER JOIN users to_user ON m.to_user_id = to_user.id
                  WHERE m.id = :id";
        
        $message = $this->db->fetchOne($query, ['id' => $messageId]);
        
        if ($message && $message['metadata']) {
            $message['metadata'] = json_decode($message['metadata'], true);
        }
        
        return $message ?: null;
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
        $whereConditions = ["m.to_user_id = :user_id"];
        $params = ['user_id' => $userId];

        if (!empty($filters['is_read'])) {
            $whereConditions[] = "m.is_read = :is_read";
            $params['is_read'] = $filters['is_read'];
        }

        if (!empty($filters['message_type'])) {
            $whereConditions[] = "m.message_type = :message_type";
            $params['message_type'] = $filters['message_type'];
        }

        if (!empty($filters['search'])) {
            $whereConditions[] = "(m.subject LIKE :search OR m.content LIKE :search)";
            $params['search'] = '%' . $filters['search'] . '%';
        }

        $whereClause = implode(' AND ', $whereConditions);
        $offset = ($page - 1) * $perPage;

        $query = "SELECT m.*, from_user.first_name as from_first_name, from_user.last_name as from_last_name
                  FROM {$this->messagesTable} m
                  INNER JOIN users from_user ON m.from_user_id = from_user.id
                  WHERE {$whereClause} 
                  ORDER BY m.created_at DESC 
                  LIMIT :limit OFFSET :offset";
        
        $params['limit'] = $perPage;
        $params['offset'] = $offset;

        $messages = $this->db->fetchAll($query, $params);

        // Get total count for pagination
        $countQuery = "SELECT COUNT(*) as total FROM {$this->messagesTable} m WHERE {$whereClause}";
        $total = $this->db->fetchColumn($countQuery, $params);

        return [
            'messages' => $messages,
            'pagination' => [
                'page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'total_pages' => ceil($total / $perPage)
            ]
        ];
    }

    /**
     * Mark message as read
     * 
     * @param int $messageId
     * @param int $userId
     * @return bool
     * @throws Exception
     */
    public function markMessageAsRead(int $messageId, int $userId): bool
    {
        $message = $this->getMessage($messageId);
        if (!$message || $message['to_user_id'] !== $userId) {
            throw new Exception("Message not found or access denied");
        }

        $query = "UPDATE {$this->messagesTable} SET is_read = TRUE, read_at = :read_at, 
                  updated_at = :updated_at WHERE id = :id";
        
        $this->db->query($query, [
            'read_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
            'id' => $messageId
        ]);

        return true;
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
     * @throws Exception
     */
    public function createNotification(int $userId, string $type, string $title, string $message, array $data = []): array
    {
        $validTypes = [
            self::NOTIFICATION_NEW_REVIEW, self::NOTIFICATION_NEW_COMPLAINT,
            self::NOTIFICATION_STATUS_UPDATE, self::NOTIFICATION_MESSAGE,
            self::NOTIFICATION_SYSTEM, self::NOTIFICATION_PROMOTIONAL
        ];

        if (!in_array($type, $validTypes)) {
            throw new Exception("Invalid notification type: {$type}");
        }

        $notificationData = [
            'user_id' => $userId,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'data' => !empty($data) ? json_encode($data) : null,
            'status' => self::NOTIFICATION_UNREAD,
            'created_at' => date('Y-m-d H:i:s')
        ];

        $columns = implode(', ', array_keys($notificationData));
        $placeholders = ':' . implode(', :', array_keys($notificationData));
        
        $query = "INSERT INTO {$this->notificationsTable} ({$columns}) VALUES ({$placeholders})";
        $this->db->query($query, $notificationData);

        $notificationId = $this->db->lastInsertId();
        return $this->getNotification($notificationId);
    }

    /**
     * Get notification by ID
     * 
     * @param int $notificationId
     * @return array|null
     */
    public function getNotification(int $notificationId): ?array
    {
        $query = "SELECT n.*, u.first_name, u.last_name 
                  FROM {$this->notificationsTable} n
                  INNER JOIN users u ON n.user_id = u.id
                  WHERE n.id = :id";
        
        $notification = $this->db->fetchOne($query, ['id' => $notificationId]);
        
        if ($notification && $notification['data']) {
            $notification['data'] = json_decode($notification['data'], true);
        }
        
        return $notification ?: null;
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
        $whereConditions = ["user_id = :user_id"];
        $params = ['user_id' => $userId, 'limit' => $limit];

        if (!empty($filters['status'])) {
            $whereConditions[] = "status = :status";
            $params['status'] = $filters['status'];
        }

        if (!empty($filters['type'])) {
            $whereConditions[] = "type = :type";
            $params['type'] = $filters['type'];
        }

        if (!empty($filters['unread_only'])) {
            $whereConditions[] = "status = :status";
            $params['status'] = self::NOTIFICATION_UNREAD;
        }

        $whereClause = implode(' AND ', $whereConditions);
        $query = "SELECT * FROM {$this->notificationsTable} 
                  WHERE {$whereClause} 
                  ORDER BY created_at DESC 
                  LIMIT :limit";
        
        $notifications = $this->db->fetchAll($query, $params);
        
        foreach ($notifications as &$notification) {
            if ($notification['data']) {
                $notification['data'] = json_decode($notification['data'], true);
            }
        }
        
        return $notifications;
    }

    /**
     * Mark notification as read
     * 
     * @param int $notificationId
     * @param int $userId
     * @return bool
     * @throws Exception
     */
    public function markNotificationAsRead(int $notificationId, int $userId): bool
    {
        $notification = $this->getNotification($notificationId);
        if (!$notification || $notification['user_id'] !== $userId) {
            throw new Exception("Notification not found or access denied");
        }

        $query = "UPDATE {$this->notificationsTable} SET status = :status, read_at = :read_at 
                  WHERE id = :id";
        
        $this->db->query($query, [
            'status' => self::NOTIFICATION_READ,
            'read_at' => date('Y-m-d H:i:s'),
            'id' => $notificationId
        ]);

        return true;
    }

    /**
     * Mark all notifications as read
     * 
     * @param int $userId
     * @return bool
     */
    public function markAllNotificationsAsRead(int $userId): bool
    {
        $query = "UPDATE {$this->notificationsTable} SET status = :status, read_at = :read_at 
                  WHERE user_id = :user_id AND status = :unread";
        
        $this->db->query($query, [
            'status' => self::NOTIFICATION_READ,
            'read_at' => date('Y-m-d H:i:s'),
            'user_id' => $userId,
            'unread' => self::NOTIFICATION_UNREAD
        ]);

        return true;
    }

    /**
     * Get unread notification count
     * 
     * @param int $userId
     * @return int
     */
    public function getUnreadNotificationCount(int $userId): int
    {
        return $this->db->fetchColumn(
            "SELECT COUNT(*) FROM {$this->notificationsTable} 
             WHERE user_id = :user_id AND status = :status",
            [
                'user_id' => $userId,
                'status' => self::NOTIFICATION_UNREAD
            ]
        ) ?: 0;
    }

    /**
     * Create chat session
     * 
     * @param int $userId1
     * @param int $userId2
     * @param string $sessionType
     * @param array $sessionData
     * @return array
     * @throws Exception
     */
    public function createChatSession(int $userId1, int $userId2, string $sessionType = 'direct', array $sessionData = []): array
    {
        // Check if session already exists
        $existing = $this->db->fetchOne(
            "SELECT id FROM {$this->chatSessionsTable} 
             WHERE ((user_id_1 = :user1 AND user_id_2 = :user2) OR (user_id_1 = :user2 AND user_id_2 = :user1))
             AND session_type = :session_type",
            [
                'user1' => $userId1,
                'user2' => $userId2,
                'session_type' => $sessionType
            ]
        );

        if ($existing) {
            return $this->getChatSession($existing['id']);
        }

        $sessionData = [
            'user_id_1' => $userId1,
            'user_id_2' => $userId2,
            'session_type' => $sessionType,
            'last_activity' => date('Y-m-d H:i:s'),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $columns = implode(', ', array_keys($sessionData));
        $placeholders = ':' . implode(', :', array_keys($sessionData));
        
        $query = "INSERT INTO {$this->chatSessionsTable} ({$columns}) VALUES ({$placeholders})";
        $this->db->query($query, $sessionData);

        $sessionId = $this->db->lastInsertId();
        return $this->getChatSession($sessionId);
    }

    /**
     * Get chat session
     * 
     * @param int $sessionId
     * @return array|null
     */
    public function getChatSession(int $sessionId): ?array
    {
        $query = "SELECT cs.*, 
                         u1.first_name as user1_first_name, u1.last_name as user1_last_name,
                         u2.first_name as user2_first_name, u2.last_name as user2_last_name
                  FROM {$this->chatSessionsTable} cs
                  INNER JOIN users u1 ON cs.user_id_1 = u1.id
                  INNER JOIN users u2 ON cs.user_id_2 = u2.id
                  WHERE cs.id = :id";
        
        return $this->db->fetchOne($query, ['id' => $sessionId]) ?: null;
    }

    /**
     * Get user chat sessions
     * 
     * @param int $userId
     * @return array
     */
    public function getUserChatSessions(int $userId): array
    {
        $query = "SELECT cs.*,
                         CASE 
                             WHEN cs.user_id_1 = :user_id THEN u2.first_name
                             ELSE u1.first_name
                         END as other_user_first_name,
                         CASE 
                             WHEN cs.user_id_1 = :user_id THEN u2.last_name
                             ELSE u1.last_name
                         END as other_user_last_name,
                         (SELECT message FROM {$this->chatMessagesTable} 
                          WHERE chat_session_id = cs.id 
                          ORDER BY created_at DESC 
                          LIMIT 1) as last_message,
                         (SELECT created_at FROM {$this->chatMessagesTable} 
                          WHERE chat_session_id = cs.id 
                          ORDER BY created_at DESC 
                          LIMIT 1) as last_message_time
                  FROM {$this->chatSessionsTable} cs
                  INNER JOIN users u1 ON cs.user_id_1 = u1.id
                  INNER JOIN users u2 ON cs.user_id_2 = u2.id
                  WHERE cs.user_id_1 = :user_id OR cs.user_id_2 = :user_id
                  ORDER BY cs.last_activity DESC";
        
        return $this->db->fetchAll($query, ['user_id' => $userId]);
    }

    /**
     * Send chat message
     * 
     * @param int $sessionId
     * @param int $senderId
     * @param string $message
     * @param string $messageType
     * @return array
     * @throws Exception
     */
    public function sendChatMessage(int $sessionId, int $senderId, string $message, string $messageType = 'text'): array
    {
        $session = $this->getChatSession($sessionId);
        if (!$session) {
            throw new Exception("Chat session not found");
        }

        // Verify sender is part of the session
        if ($session['user_id_1'] !== $senderId && $session['user_id_2'] !== $senderId) {
            throw new Exception("Sender not part of this chat session");
        }

        $messageData = [
            'chat_session_id' => $sessionId,
            'sender_id' => $senderId,
            'message' => $message,
            'message_type' => $messageType,
            'is_read' => false,
            'created_at' => date('Y-m-d H:i:s')
        ];

        try {
            $this->db->beginTransaction();

            $columns = implode(', ', array_keys($messageData));
            $placeholders = ':' . implode(', :', array_keys($messageData));
            
            $query = "INSERT INTO {$this->chatMessagesTable} ({$columns}) VALUES ({$placeholders})";
            $this->db->query($query, $messageData);

            $messageId = $this->db->lastInsertId();

            // Update session last activity
            $this->updateChatSessionActivity($sessionId);
            
            $this->db->commit();

            return $this->getChatMessage($messageId);
            
        } catch (PDOException $e) {
            $this->db->rollback();
            throw new Exception("Failed to send chat message: " . $e->getMessage());
        }
    }

    /**
     * Get chat message
     * 
     * @param int $messageId
     * @return array|null
     */
    public function getChatMessage(int $messageId): ?array
    {
        $query = "SELECT cm.*, u.first_name, u.last_name 
                  FROM {$this->chatMessagesTable} cm
                  INNER JOIN users u ON cm.sender_id = u.id
                  WHERE cm.id = :id";
        
        return $this->db->fetchOne($query, ['id' => $messageId]) ?: null;
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
        $query = "SELECT cm.*, u.first_name, u.last_name 
                  FROM {$this->chatMessagesTable} cm
                  INNER JOIN users u ON cm.sender_id = u.id
                  WHERE cm.chat_session_id = :session_id 
                  ORDER BY cm.created_at DESC 
                  LIMIT :limit OFFSET :offset";
        
        return $this->db->fetchAll($query, [
            'session_id' => $sessionId,
            'limit' => $limit,
            'offset' => $offset
        ]);
    }

    /**
     * Mark chat messages as read
     * 
     * @param int $sessionId
     * @param int $userId
     * @return bool
     */
    public function markChatMessagesAsRead(int $sessionId, int $userId): bool
    {
        $query = "UPDATE {$this->chatMessagesTable} SET is_read = TRUE 
                  WHERE chat_session_id = :session_id AND sender_id != :user_id AND is_read = FALSE";
        
        $this->db->query($query, [
            'session_id' => $sessionId,
            'user_id' => $userId
        ]);

        return true;
    }

    /**
     * Update chat session activity
     * 
     * @param int $sessionId
     * @return bool
     */
    private function updateChatSessionActivity(int $sessionId): bool
    {
        $query = "UPDATE {$this->chatSessionsTable} SET last_activity = :last_activity, 
                  updated_at = :updated_at WHERE id = :id";
        
        $this->db->query($query, [
            'last_activity' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
            'id' => $sessionId
        ]);

        return true;
    }

    /**
     * Get communication statistics
     * 
     * @param int $userId
     * @return array
     */
    public function getCommunicationStatistics(int $userId): array
    {
        $unreadMessages = $this->db->fetchColumn(
            "SELECT COUNT(*) FROM {$this->messagesTable} 
             WHERE to_user_id = :user_id AND is_read = FALSE",
            ['user_id' => $userId]
        ) ?: 0;

        $unreadNotifications = $this->getUnreadNotificationCount($userId);

        $unreadChatMessages = $this->db->fetchColumn(
            "SELECT COUNT(*) FROM {$this->chatMessagesTable} cm
             INNER JOIN {$this->chatSessionsTable} cs ON cm.chat_session_id = cs.id
             WHERE (cs.user_id_1 = :user_id OR cs.user_id_2 = :user_id) 
             AND cm.sender_id != :user_id AND cm.is_read = FALSE",
            ['user_id' => $userId]
        ) ?: 0;

        $activeChatSessions = $this->db->fetchColumn(
            "SELECT COUNT(*) FROM {$this->chatSessionsTable} 
             WHERE (user_id_1 = :user_id OR user_id_2 = :user_id) 
             AND last_activity > :recent_time",
            [
                'user_id' => $userId,
                'recent_time' => date('Y-m-d H:i:s', strtotime('-7 days'))
            ]
        ) ?: 0;

        return [
            'unread_messages' => (int)$unreadMessages,
            'unread_notifications' => $unreadNotifications,
            'unread_chat_messages' => (int)$unreadChatMessages,
            'active_chat_sessions' => (int)$activeChatSessions,
            'total_messages' => $this->db->fetchColumn(
                "SELECT COUNT(*) FROM {$this->messagesTable} WHERE to_user_id = :user_id",
                ['user_id' => $userId]
            ) ?: 0
        ];
    }
}
