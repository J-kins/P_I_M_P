<?php
/**
 * P.I.M.P - Business Subscription and Newsletter Management Model
 * Handles business subscriptions, newsletter management, and communication preferences
 */

namespace PIMP\Models;

use PIMP\Services\Database\MySQLDatabase;
use PDOException;
use Exception;

class BusinessSubscription
{
    /**
     * @var MySQLDatabase Database instance
     */
    private $db;

    /**
     * @var string Subscriptions table name
     */
    private $subscriptionsTable = 'business_subscriptions';

    /**
     * @var string Newsletter templates table
     */
    private $templatesTable = 'newsletter_templates';

    /**
     * @var string Newsletter campaigns table
     */
    private $campaignsTable = 'newsletter_campaigns';

    /**
     * @var string Subscriber list table
     */
    private $subscribersTable = 'newsletter_subscribers';

    /**
     * @var string Campaign sends table
     */
    private $campaignSendsTable = 'campaign_sends';

    /**
     * Subscription status constants
     */
    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';
    const STATUS_SUSPENDED = 'suspended';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_EXPIRED = 'expired';

    /**
     * Subscription tiers
     */
    const TIER_FREE = 'free';
    const TIER_BASIC = 'basic';
    const TIER_PROFESSIONAL = 'professional';
    const TIER_ENTERPRISE = 'enterprise';

    /**
     * Newsletter status
     */
    const NEWSLETTER_DRAFT = 'draft';
    const NEWSLETTER_SCHEDULED = 'scheduled';
    const NEWSLETTER_SENDING = 'sending';
    const NEWSLETTER_SENT = 'sent';
    const NEWSLETTER_CANCELLED = 'cancelled';

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
     * Create or update business subscription
     * 
     * @param int $businessId
     * @param string $tier
     * @param array $subscriptionData
     * @return array
     * @throws Exception
     */
    public function updateSubscription(int $businessId, string $tier, array $subscriptionData = []): array
    {
        $validTiers = [self::TIER_FREE, self::TIER_BASIC, self::TIER_PROFESSIONAL, self::TIER_ENTERPRISE];
        if (!in_array($tier, $validTiers)) {
            throw new Exception("Invalid subscription tier: {$tier}");
        }

        // Check if subscription already exists
        $existing = $this->getBusinessSubscription($businessId);
        
        if ($existing) {
            // Update existing subscription
            $subscriptionData['tier'] = $tier;
            $subscriptionData['updated_at'] = date('Y-m-d H:i:s');
            
            $setParts = [];
            foreach (array_keys($subscriptionData) as $field) {
                $setParts[] = "{$field} = :{$field}";
            }

            $query = "UPDATE {$this->subscriptionsTable} SET " . implode(', ', $setParts) . " WHERE business_id = :business_id";
            $subscriptionData['business_id'] = $businessId;
            $this->db->query($query, $subscriptionData);
        } else {
            // Create new subscription
            $subscriptionData['business_id'] = $businessId;
            $subscriptionData['tier'] = $tier;
            $subscriptionData['status'] = self::STATUS_ACTIVE;
            $subscriptionData['start_date'] = date('Y-m-d H:i:s');
            $subscriptionData['created_at'] = date('Y-m-d H:i:s');
            $subscriptionData['updated_at'] = date('Y-m-d H:i:s');

            // Set end date based on tier
            if ($tier !== self::TIER_FREE) {
                $subscriptionData['end_date'] = date('Y-m-d H:i:s', strtotime('+1 year'));
            }

            $columns = implode(', ', array_keys($subscriptionData));
            $placeholders = ':' . implode(', :', array_keys($subscriptionData));
            
            $query = "INSERT INTO {$this->subscriptionsTable} ({$columns}) VALUES ({$placeholders})";
            $this->db->query($query, $subscriptionData);
        }

        return $this->getBusinessSubscription($businessId);
    }

    /**
     * Get business subscription
     * 
     * @param int $businessId
     * @return array|null
     */
    public function getBusinessSubscription(int $businessId): ?array
    {
        $query = "SELECT s.*, b.business_name, b.business_id as business_identifier 
                  FROM {$this->subscriptionsTable} s
                  INNER JOIN business_profiles b ON s.business_id = b.id
                  WHERE s.business_id = :business_id";
        
        $subscription = $this->db->fetchOne($query, ['business_id' => $businessId]);
        
        if ($subscription && $subscription['features']) {
            $subscription['features'] = json_decode($subscription['features'], true);
        }
        
        return $subscription ?: null;
    }

    /**
     * Update subscription status
     * 
     * @param int $businessId
     * @param string $status
     * @param string $reason
     * @return bool
     * @throws Exception
     */
    public function updateSubscriptionStatus(int $businessId, string $status, string $reason = ''): bool
    {
        $validStatuses = [
            self::STATUS_ACTIVE, self::STATUS_INACTIVE, 
            self::STATUS_SUSPENDED, self::STATUS_CANCELLED, self::STATUS_EXPIRED
        ];

        if (!in_array($status, $validStatuses)) {
            throw new Exception("Invalid subscription status: {$status}");
        }

        $query = "UPDATE {$this->subscriptionsTable} SET status = :status, updated_at = :updated_at, 
                  status_reason = :reason WHERE business_id = :business_id";
        
        $this->db->query($query, [
            'status' => $status,
            'updated_at' => date('Y-m-d H:i:s'),
            'reason' => $reason,
            'business_id' => $businessId
        ]);

        return true;
    }

    /**
     * Get subscription features by tier
     * 
     * @param string $tier
     * @return array
     */
    public function getTierFeatures(string $tier): array
    {
        $features = [
            self::TIER_FREE => [
                'basic_listing' => true,
                'reviews' => true,
                'contact_info' => true,
                'newsletter_subscribers' => 100,
                'monthly_newsletters' => 1,
                'analytics' => 'basic',
                'support' => 'community'
            ],
            self::TIER_BASIC => [
                'basic_listing' => true,
                'reviews' => true,
                'contact_info' => true,
                'multiple_locations' => 3,
                'newsletter_subscribers' => 1000,
                'monthly_newsletters' => 5,
                'custom_templates' => true,
                'analytics' => 'standard',
                'support' => 'email',
                'accreditation_eligibility' => true
            ],
            self::TIER_PROFESSIONAL => [
                'basic_listing' => true,
                'reviews' => true,
                'contact_info' => true,
                'multiple_locations' => 10,
                'newsletter_subscribers' => 5000,
                'monthly_newsletters' => 20,
                'custom_templates' => true,
                'advanced_analytics' => true,
                'support' => 'priority',
                'accreditation_eligibility' => true,
                'featured_listing' => true,
                'promoted_content' => true,
                'api_access' => true
            ],
            self::TIER_ENTERPRISE => [
                'basic_listing' => true,
                'reviews' => true,
                'contact_info' => true,
                'multiple_locations' => -1, // unlimited
                'newsletter_subscribers' => -1, // unlimited
                'monthly_newsletters' => -1, // unlimited
                'custom_templates' => true,
                'advanced_analytics' => true,
                'support' => 'dedicated',
                'accreditation_eligibility' => true,
                'featured_listing' => true,
                'promoted_content' => true,
                'api_access' => true,
                'white_label' => true,
                'custom_integrations' => true,
                'dedicated_account_manager' => true
            ]
        ];

        return $features[$tier] ?? [];
    }

    /**
     * Create newsletter template
     * 
     * @param int $businessId
     * @param array $templateData
     * @return array
     * @throws Exception
     */
    public function createTemplate(int $businessId, array $templateData): array
    {
        $requiredFields = ['name', 'subject', 'content'];
        foreach ($requiredFields as $field) {
            if (empty($templateData[$field])) {
                throw new Exception("Required field missing: {$field}");
            }
        }

        $templateData['business_id'] = $businessId;
        $templateData['is_active'] = true;
        $templateData['created_at'] = date('Y-m-d H:i:s');
        $templateData['updated_at'] = date('Y-m-d H:i:s');

        $columns = implode(', ', array_keys($templateData));
        $placeholders = ':' . implode(', :', array_keys($templateData));
        
        $query = "INSERT INTO {$this->templatesTable} ({$columns}) VALUES ({$placeholders})";
        $this->db->query($query, $templateData);

        $templateId = $this->db->lastInsertId();
        return $this->getTemplate($templateId);
    }

    /**
     * Get newsletter template
     * 
     * @param int $templateId
     * @return array|null
     */
    public function getTemplate(int $templateId): ?array
    {
        $query = "SELECT t.*, b.business_name 
                  FROM {$this->templatesTable} t
                  INNER JOIN business_profiles b ON t.business_id = b.id
                  WHERE t.id = :id";
        
        return $this->db->fetchOne($query, ['id' => $templateId]) ?: null;
    }

    /**
     * Get business templates
     * 
     * @param int $businessId
     * @param array $filters
     * @return array
     */
    public function getBusinessTemplates(int $businessId, array $filters = []): array
    {
        $whereConditions = ["business_id = :business_id"];
        $params = ['business_id' => $businessId];

        if (!empty($filters['is_active'])) {
            $whereConditions[] = "is_active = :is_active";
            $params['is_active'] = $filters['is_active'];
        }

        $whereClause = implode(' AND ', $whereConditions);
        $query = "SELECT * FROM {$this->templatesTable} WHERE {$whereClause} ORDER BY created_at DESC";
        
        return $this->db->fetchAll($query, $params);
    }

    /**
     * Create newsletter campaign
     * 
     * @param int $businessId
     * @param array $campaignData
     * @return array
     * @throws Exception
     */
    public function createCampaign(int $businessId, array $campaignData): array
    {
        $requiredFields = ['name', 'subject', 'content', 'template_id'];
        foreach ($requiredFields as $field) {
            if (empty($campaignData[$field])) {
                throw new Exception("Required field missing: {$field}");
            }
        }

        // Check template belongs to business
        $template = $this->getTemplate($campaignData['template_id']);
        if (!$template || $template['business_id'] !== $businessId) {
            throw new Exception("Invalid template or template does not belong to business");
        }

        // Check subscription limits
        if (!$this->canSendNewsletter($businessId)) {
            throw new Exception("Newsletter limit reached for current subscription tier");
        }

        $campaignData['business_id'] = $businessId;
        $campaignData['status'] = self::NEWSLETTER_DRAFT;
        $campaignData['created_at'] = date('Y-m-d H:i:s');
        $campaignData['updated_at'] = date('Y-m-d H:i:s');

        $columns = implode(', ', array_keys($campaignData));
        $placeholders = ':' . implode(', :', array_keys($campaignData));
        
        $query = "INSERT INTO {$this->campaignsTable} ({$columns}) VALUES ({$placeholders})";
        $this->db->query($query, $campaignData);

        $campaignId = $this->db->lastInsertId();
        return $this->getCampaign($campaignId);
    }

    /**
     * Get newsletter campaign
     * 
     * @param int $campaignId
     * @return array|null
     */
    public function getCampaign(int $campaignId): ?array
    {
        $query = "SELECT c.*, b.business_name, t.name as template_name 
                  FROM {$this->campaignsTable} c
                  INNER JOIN business_profiles b ON c.business_id = b.id
                  LEFT JOIN {$this->templatesTable} t ON c.template_id = t.id
                  WHERE c.id = :id";
        
        return $this->db->fetchOne($query, ['id' => $campaignId]) ?: null;
    }

    /**
     * Get business campaigns
     * 
     * @param int $businessId
     * @param array $filters
     * @return array
     */
    public function getBusinessCampaigns(int $businessId, array $filters = []): array
    {
        $whereConditions = ["c.business_id = :business_id"];
        $params = ['business_id' => $businessId];

        if (!empty($filters['status'])) {
            $whereConditions[] = "c.status = :status";
            $params['status'] = $filters['status'];
        }

        $whereClause = implode(' AND ', $whereConditions);
        $query = "SELECT c.*, t.name as template_name 
                  FROM {$this->campaignsTable} c
                  LEFT JOIN {$this->templatesTable} t ON c.template_id = t.id
                  WHERE {$whereClause} 
                  ORDER BY c.created_at DESC";
        
        return $this->db->fetchAll($query, $params);
    }

    /**
     * Schedule newsletter campaign
     * 
     * @param int $campaignId
     * @param string $scheduleDate
     * @return bool
     * @throws Exception
     */
    public function scheduleCampaign(int $campaignId, string $scheduleDate): bool
    {
        $campaign = $this->getCampaign($campaignId);
        if (!$campaign) {
            throw new Exception("Campaign not found");
        }

        if ($campaign['status'] !== self::NEWSLETTER_DRAFT) {
            throw new Exception("Only draft campaigns can be scheduled");
        }

        $query = "UPDATE {$this->campaignsTable} SET status = :status, scheduled_at = :scheduled_at, 
                  updated_at = :updated_at WHERE id = :id";
        
        $this->db->query($query, [
            'status' => self::NEWSLETTER_SCHEDULED,
            'scheduled_at' => $scheduleDate,
            'updated_at' => date('Y-m-d H:i:s'),
            'id' => $campaignId
        ]);

        return true;
    }

    /**
     * Add subscriber to business newsletter
     * 
     * @param int $businessId
     * @param string $email
     * @param array $subscriberData
     * @return array
     * @throws Exception
     */
    public function addSubscriber(int $businessId, string $email, array $subscriberData = []): array
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Invalid email address");
        }

        // Check subscription limits
        $subscriberCount = $this->getSubscriberCount($businessId);
        $subscription = $this->getBusinessSubscription($businessId);
        $maxSubscribers = $this->getTierFeatures($subscription['tier'])['newsletter_subscribers'] ?? 100;

        if ($maxSubscribers > 0 && $subscriberCount >= $maxSubscribers) {
            throw new Exception("Subscriber limit reached for current subscription tier");
        }

        // Check if already subscribed
        $existing = $this->db->fetchOne(
            "SELECT id FROM {$this->subscribersTable} WHERE business_id = :business_id AND email = :email",
            ['business_id' => $businessId, 'email' => $email]
        );

        if ($existing) {
            throw new Exception("Email already subscribed to this newsletter");
        }

        $subscriberData['business_id'] = $businessId;
        $subscriberData['email'] = $email;
        $subscriberData['subscription_token'] = bin2hex(random_bytes(32));
        $subscriberData['is_active'] = true;
        $subscriberData['created_at'] = date('Y-m-d H:i:s');
        $subscriberData['updated_at'] = date('Y-m-d H:i:s');

        $columns = implode(', ', array_keys($subscriberData));
        $placeholders = ':' . implode(', :', array_keys($subscriberData));
        
        $query = "INSERT INTO {$this->subscribersTable} ({$columns}) VALUES ({$placeholders})";
        $this->db->query($query, $subscriberData);

        $subscriberId = $this->db->lastInsertId();
        return $this->getSubscriber($subscriberId);
    }

    /**
     * Get subscriber
     * 
     * @param int $subscriberId
     * @return array|null
     */
    public function getSubscriber(int $subscriberId): ?array
    {
        $query = "SELECT s.*, b.business_name 
                  FROM {$this->subscribersTable} s
                  INNER JOIN business_profiles b ON s.business_id = b.id
                  WHERE s.id = :id";
        
        $subscriber = $this->db->fetchOne($query, ['id' => $subscriberId]);
        
        if ($subscriber && $subscriber['metadata']) {
            $subscriber['metadata'] = json_decode($subscriber['metadata'], true);
        }
        
        return $subscriber ?: null;
    }

    /**
     * Get business subscribers
     * 
     * @param int $businessId
     * @param array $filters
     * @return array
     */
    public function getBusinessSubscribers(int $businessId, array $filters = []): array
    {
        $whereConditions = ["business_id = :business_id"];
        $params = ['business_id' => $businessId];

        if (!empty($filters['is_active'])) {
            $whereConditions[] = "is_active = :is_active";
            $params['is_active'] = $filters['is_active'];
        }

        $whereClause = implode(' AND ', $whereConditions);
        $query = "SELECT * FROM {$this->subscribersTable} WHERE {$whereClause} ORDER BY created_at DESC";
        
        return $this->db->fetchAll($query, $params);
    }

    /**
     * Get subscriber count
     * 
     * @param int $businessId
     * @return int
     */
    public function getSubscriberCount(int $businessId): int
    {
        return $this->db->fetchColumn(
            "SELECT COUNT(*) FROM {$this->subscribersTable} WHERE business_id = :business_id AND is_active = TRUE",
            ['business_id' => $businessId]
        ) ?: 0;
    }

    /**
     * Unsubscribe email from business newsletter
     * 
     * @param int $businessId
     * @param string $email
     * @return bool
     */
    public function unsubscribe(int $businessId, string $email): bool
    {
        $query = "UPDATE {$this->subscribersTable} SET is_active = FALSE, unsubscribed_at = :unsubscribed_at 
                  WHERE business_id = :business_id AND email = :email";
        
        $this->db->query($query, [
            'unsubscribed_at' => date('Y-m-d H:i:s'),
            'business_id' => $businessId,
            'email' => $email
        ]);

        return true;
    }

    /**
     * Check if business can send newsletter
     * 
     * @param int $businessId
     * @return bool
     */
    public function canSendNewsletter(int $businessId): bool
    {
        $subscription = $this->getBusinessSubscription($businessId);
        if (!$subscription) {
            return false;
        }

        $tierFeatures = $this->getTierFeatures($subscription['tier']);
        $monthlyLimit = $tierFeatures['monthly_newsletters'] ?? 1;

        if ($monthlyLimit === -1) {
            return true; // Unlimited
        }

        // Count newsletters sent this month
        $monthStart = date('Y-m-01');
        $newslettersThisMonth = $this->db->fetchColumn(
            "SELECT COUNT(*) FROM {$this->campaignsTable} 
             WHERE business_id = :business_id 
             AND status = :sent 
             AND sent_at >= :month_start",
            [
                'business_id' => $businessId,
                'sent' => self::NEWSLETTER_SENT,
                'month_start' => $monthStart
            ]
        ) ?: 0;

        return $newslettersThisMonth < $monthlyLimit;
    }

    /**
     * Get subscription statistics
     * 
     * @param int $businessId
     * @return array
     */
    public function getSubscriptionStatistics(int $businessId): array
    {
        $subscription = $this->getBusinessSubscription($businessId);
        $subscriberCount = $this->getSubscriberCount($businessId);
        
        $campaignCount = $this->db->fetchColumn(
            "SELECT COUNT(*) FROM {$this->campaignsTable} WHERE business_id = :business_id",
            ['business_id' => $businessId]
        ) ?: 0;

        $sentCampaigns = $this->db->fetchColumn(
            "SELECT COUNT(*) FROM {$this->campaignsTable} WHERE business_id = :business_id AND status = :sent",
            [
                'business_id' => $businessId,
                'sent' => self::NEWSLETTER_SENT
            ]
        ) ?: 0;

        $tierFeatures = $subscription ? $this->getTierFeatures($subscription['tier']) : [];

        return [
            'current_tier' => $subscription['tier'] ?? self::TIER_FREE,
            'subscription_status' => $subscription['status'] ?? self::STATUS_INACTIVE,
            'subscriber_count' => $subscriberCount,
            'subscriber_limit' => $tierFeatures['newsletter_subscribers'] ?? 100,
            'campaign_count' => $campaignCount,
            'sent_campaigns' => $sentCampaigns,
            'monthly_newsletter_limit' => $tierFeatures['monthly_newsletters'] ?? 1,
            'can_send_newsletter' => $this->canSendNewsletter($businessId),
            'features' => $tierFeatures
        ];
    }
}
