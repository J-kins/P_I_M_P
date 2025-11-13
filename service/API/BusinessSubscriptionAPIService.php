<?php
/**
 * P.I.M.P - Business Subscription API Service
 * Handles business subscriptions and newsletter API operations
 */

namespace PIMP\Services\API;

use PIMP\Models\BusinessSubscription;
use PIMP\Services\Database\MySQLDatabase;
use Exception;

class BusinessSubscriptionAPIService
{
    /**
     * @var BusinessSubscription
     */
    private $subscriptionModel;

    /**
     * Constructor
     * 
     * @param MySQLDatabase $db
     */
    public function __construct(MySQLDatabase $db)
    {
        $this->subscriptionModel = new BusinessSubscription($db);
    }

    /**
     * Update business subscription
     * 
     * @param int $businessId
     * @param string $tier
     * @param array $subscriptionData
     * @return array
     */
    public function updateSubscription(int $businessId, string $tier, array $subscriptionData = []): array
    {
        try {
            $subscription = $this->subscriptionModel->updateSubscription($businessId, $tier, $subscriptionData);
            
            return [
                'success' => true,
                'message' => 'Subscription updated successfully',
                'data' => $subscription
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
     * Get business subscription
     * 
     * @param int $businessId
     * @return array
     */
    public function getBusinessSubscription(int $businessId): array
    {
        try {
            $subscription = $this->subscriptionModel->getBusinessSubscription($businessId);
            
            if (!$subscription) {
                return [
                    'success' => true,
                    'message' => 'No subscription found',
                    'data' => null
                ];
            }

            return [
                'success' => true,
                'message' => 'Subscription retrieved successfully',
                'data' => $subscription
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
     * Update subscription status
     * 
     * @param int $businessId
     * @param string $status
     * @param string $reason
     * @return array
     */
    public function updateSubscriptionStatus(int $businessId, string $status, string $reason = ''): array
    {
        try {
            $this->subscriptionModel->updateSubscriptionStatus($businessId, $status, $reason);

            return [
                'success' => true,
                'message' => 'Subscription status updated successfully',
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
     * Get tier features
     * 
     * @param string $tier
     * @return array
     */
    public function getTierFeatures(string $tier): array
    {
        try {
            $features = $this->subscriptionModel->getTierFeatures($tier);

            return [
                'success' => true,
                'message' => 'Tier features retrieved successfully',
                'data' => $features
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
     * Create newsletter template
     * 
     * @param int $businessId
     * @param array $templateData
     * @return array
     */
    public function createTemplate(int $businessId, array $templateData): array
    {
        try {
            $template = $this->subscriptionModel->createTemplate($businessId, $templateData);

            return [
                'success' => true,
                'message' => 'Template created successfully',
                'data' => $template
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
     * Get business templates
     * 
     * @param int $businessId
     * @param array $filters
     * @return array
     */
    public function getBusinessTemplates(int $businessId, array $filters = []): array
    {
        try {
            $templates = $this->subscriptionModel->getBusinessTemplates($businessId, $filters);

            return [
                'success' => true,
                'message' => 'Templates retrieved successfully',
                'data' => $templates
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
     * Create newsletter campaign
     * 
     * @param int $businessId
     * @param array $campaignData
     * @return array
     */
    public function createCampaign(int $businessId, array $campaignData): array
    {
        try {
            $campaign = $this->subscriptionModel->createCampaign($businessId, $campaignData);

            return [
                'success' => true,
                'message' => 'Campaign created successfully',
                'data' => $campaign
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
     * Get business campaigns
     * 
     * @param int $businessId
     * @param array $filters
     * @return array
     */
    public function getBusinessCampaigns(int $businessId, array $filters = []): array
    {
        try {
            $campaigns = $this->subscriptionModel->getBusinessCampaigns($businessId, $filters);

            return [
                'success' => true,
                'message' => 'Campaigns retrieved successfully',
                'data' => $campaigns
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
     * Schedule campaign
     * 
     * @param int $campaignId
     * @param string $scheduleDate
     * @return array
     */
    public function scheduleCampaign(int $campaignId, string $scheduleDate): array
    {
        try {
            $this->subscriptionModel->scheduleCampaign($campaignId, $scheduleDate);

            return [
                'success' => true,
                'message' => 'Campaign scheduled successfully',
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
     * Add subscriber
     * 
     * @param int $businessId
     * @param string $email
     * @param array $subscriberData
     * @return array
     */
    public function addSubscriber(int $businessId, string $email, array $subscriberData = []): array
    {
        try {
            $subscriber = $this->subscriptionModel->addSubscriber($businessId, $email, $subscriberData);

            return [
                'success' => true,
                'message' => 'Subscriber added successfully',
                'data' => $subscriber
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
     * Get business subscribers
     * 
     * @param int $businessId
     * @param array $filters
     * @return array
     */
    public function getBusinessSubscribers(int $businessId, array $filters = []): array
    {
        try {
            $subscribers = $this->subscriptionModel->getBusinessSubscribers($businessId, $filters);

            return [
                'success' => true,
                'message' => 'Subscribers retrieved successfully',
                'data' => $subscribers
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
     * Unsubscribe email
     * 
     * @param int $businessId
     * @param string $email
     * @return array
     */
    public function unsubscribe(int $businessId, string $email): array
    {
        try {
            $this->subscriptionModel->unsubscribe($businessId, $email);

            return [
                'success' => true,
                'message' => 'Email unsubscribed successfully',
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
     * Get subscription statistics
     * 
     * @param int $businessId
     * @return array
     */
    public function getSubscriptionStatistics(int $businessId): array
    {
        try {
            $statistics = $this->subscriptionModel->getSubscriptionStatistics($businessId);

            return [
                'success' => true,
                'message' => 'Subscription statistics retrieved successfully',
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
