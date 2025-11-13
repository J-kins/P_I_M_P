<?php
/**
 * P.I.M.P - Moderation Tools Model
 * Handles content flagging, spam detection, fraud prevention, and quality control
 */

namespace PIMP\Models\Admin;

use PIMP\Services\Database\MySQLDatabase;
use PDOException;
use Exception;

class ModerationTools
{
    /**
     * @var MySQLDatabase Database instance
     */
    private $db;

    /**
     * @var string Flagged content table
     */
    private $flaggedContentTable = 'flagged_content';

    /**
     * @var string Spam detection table
     */
    private $spamDetectionTable = 'spam_detection';

    /**
     * @var string Fraud prevention table
     */
    private $fraudPreventionTable = 'fraud_prevention';

    /**
     * @var string Quality control table
     */
    private $qualityControlTable = 'quality_control';

    /**
     * Content types
     */
    const CONTENT_REVIEW = 'review';
    const CONTENT_COMPLAINT = 'complaint';
    const CONTENT_BUSINESS = 'business';
    const CONTENT_USER = 'user';
    const CONTENT_MESSAGE = 'message';

    /**
     * Flag reasons
     */
    const REASON_SPAM = 'spam';
    const REASON_INAPPROPRIATE = 'inappropriate';
    const REASON_MISINFORMATION = 'misinformation';
    const REASON_FRAUD = 'fraud';
    const REASON_HARASSMENT = 'harassment';
    const REASON_OTHER = 'other';

    /**
     * Spam confidence levels
     */
    const CONFIDENCE_LOW = 'low';
    const CONFIDENCE_MEDIUM = 'medium';
    const CONFIDENCE_HIGH = 'high';
    const CONFIDENCE_VERY_HIGH = 'very_high';

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
     * Flag content for review
     * 
     * @param int $contentId
     * @param string $contentType
     * @param string $reason
     * @param string $description
     * @param int $reporterId
     * @return array
     * @throws Exception
     */
    public function flagContent(int $contentId, string $contentType, string $reason, string $description, int $reporterId): array
    {
        $validTypes = [
            self::CONTENT_REVIEW, self::CONTENT_COMPLAINT, 
            self::CONTENT_BUSINESS, self::CONTENT_USER, self::CONTENT_MESSAGE
        ];

        $validReasons = [
            self::REASON_SPAM, self::REASON_INAPPROPRIATE, self::REASON_MISINFORMATION,
            self::REASON_FRAUD, self::REASON_HARASSMENT, self::REASON_OTHER
        ];

        if (!in_array($contentType, $validTypes)) {
            throw new Exception("Invalid content type: {$contentType}");
        }

        if (!in_array($reason, $validReasons)) {
            throw new Exception("Invalid flag reason: {$reason}");
        }

        // Check if content already flagged
        $existing = $this->db->fetchOne(
            "SELECT id FROM {$this->flaggedContentTable} 
             WHERE content_id = :content_id AND content_type = :content_type AND status = 'pending'",
            ['content_id' => $contentId, 'content_type' => $contentType]
        );

        if ($existing) {
            throw new Exception("Content already flagged for review");
        }

        $flagData = [
            'content_id' => $contentId,
            'content_type' => $contentType,
            'reason' => $reason,
            'description' => $description,
            'reporter_id' => $reporterId,
            'status' => 'pending',
            'created_at' => date('Y-m-d H:i:s')
        ];

        $columns = implode(', ', array_keys($flagData));
        $placeholders = ':' . implode(', :', array_keys($flagData));
        
        $query = "INSERT INTO {$this->flaggedContentTable} ({$columns}) VALUES ({$placeholders})";
        $this->db->query($query, $flagData);

        $flagId = $this->db->lastInsertId();
        return $this->getFlaggedContent($flagId);
    }

    /**
     * Get flagged content by ID
     * 
     * @param int $flagId
     * @return array|null
     */
    public function getFlaggedContent(int $flagId): ?array
    {
        $query = "SELECT fc.*, u.first_name as reporter_first_name, u.last_name as reporter_last_name
                  FROM {$this->flaggedContentTable} fc
                  INNER JOIN users u ON fc.reporter_id = u.id
                  WHERE fc.id = :id";
        
        $flagged = $this->db->fetchOne($query, ['id' => $flagId]);
        
        if ($flagged) {
            $flagged['content_details'] = $this->getContentDetails(
                $flagged['content_id'], 
                $flagged['content_type']
            );
        }
        
        return $flagged ?: null;
    }

    /**
     * Get flagged content queue
     * 
     * @param array $filters
     * @param int $page
     * @param int $perPage
     * @return array
     */
    public function getFlaggedContentQueue(array $filters = [], int $page = 1, int $perPage = 20): array
    {
        $whereConditions = ["fc.status = 'pending'"];
        $params = [];

        if (!empty($filters['content_type'])) {
            $whereConditions[] = "fc.content_type = :content_type";
            $params['content_type'] = $filters['content_type'];
        }

        if (!empty($filters['reason'])) {
            $whereConditions[] = "fc.reason = :reason";
            $params['reason'] = $filters['reason'];
        }

        if (!empty($filters['date_from'])) {
            $whereConditions[] = "fc.created_at >= :date_from";
            $params['date_from'] = $filters['date_from'];
        }

        $whereClause = implode(' AND ', $whereConditions);
        $offset = ($page - 1) * $perPage;

        $query = "SELECT fc.*, u.first_name as reporter_first_name, u.last_name as reporter_last_name
                  FROM {$this->flaggedContentTable} fc
                  INNER JOIN users u ON fc.reporter_id = u.id
                  WHERE {$whereClause}
                  ORDER BY 
                    CASE fc.reason
                        WHEN 'fraud' THEN 1
                        WHEN 'harassment' THEN 2
                        WHEN 'inappropriate' THEN 3
                        WHEN 'spam' THEN 4
                        ELSE 5
                    END,
                    fc.created_at ASC
                  LIMIT :limit OFFSET :offset";

        $params['limit'] = $perPage;
        $params['offset'] = $offset;

        $flaggedContent = $this->db->fetchAll($query, $params);

        // Get content details for each flagged item
        foreach ($flaggedContent as &$item) {
            $item['content_details'] = $this->getContentDetails($item['content_id'], $item['content_type']);
        }

        // Get total count for pagination
        $countQuery = "SELECT COUNT(*) as total FROM {$this->flaggedContentTable} fc WHERE {$whereClause}";
        $total = $this->db->fetchColumn($countQuery, $params);

        return [
            'flagged_content' => $flaggedContent,
            'pagination' => [
                'page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'total_pages' => ceil($total / $perPage)
            ]
        ];
    }

    /**
     * Resolve flagged content
     * 
     * @param int $flagId
     * @param string $resolution
     * @param string $notes
     * @param int $moderatorId
     * @param array $actions
     * @return bool
     * @throws Exception
     */
    public function resolveFlaggedContent(int $flagId, string $resolution, string $notes, int $moderatorId, array $actions = []): bool
    {
        $flagged = $this->getFlaggedContent($flagId);
        if (!$flagged) {
            throw new Exception("Flagged content not found");
        }

        $updateData = [
            'status' => 'resolved',
            'resolution' => $resolution,
            'resolution_notes' => $notes,
            'moderator_id' => $moderatorId,
            'resolved_at' => date('Y-m-d H:i:s')
        ];

        $setParts = [];
        foreach (array_keys($updateData) as $field) {
            $setParts[] = "{$field} = :{$field}";
        }

        $query = "UPDATE {$this->flaggedContentTable} SET " . implode(', ', $setParts) . " WHERE id = :id";
        $updateData['id'] = $flagId;

        $this->db->query($query, $updateData);

        // Execute any required actions
        $this->executeModerationActions($flagged, $actions, $moderatorId);

        return true;
    }

    /**
     * Detect spam content
     * 
     * @param string $content
     * @param string $contentType
     * @param int $authorId
     * @return array
     */
    public function detectSpam(string $content, string $contentType, int $authorId): array
    {
        $spamScore = $this->calculateSpamScore($content, $contentType, $authorId);
        $confidence = $this->getSpamConfidence($spamScore);

        $detectionData = [
            'content' => $content,
            'content_type' => $contentType,
            'author_id' => $authorId,
            'spam_score' => $spamScore,
            'confidence_level' => $confidence,
            'detected_at' => date('Y-m-d H:i:s')
        ];

        // Only log high confidence spam
        if ($confidence === self::CONFIDENCE_HIGH || $confidence === self::CONFIDENCE_VERY_HIGH) {
            $columns = implode(', ', array_keys($detectionData));
            $placeholders = ':' . implode(', :', array_keys($detectionData));
            
            $query = "INSERT INTO {$this->spamDetectionTable} ({$columns}) VALUES ({$placeholders})";
            $this->db->query($query, $detectionData);
        }

        return [
            'spam_score' => $spamScore,
            'confidence_level' => $confidence,
            'is_spam' => $spamScore > 70,
            'recommended_action' => $this->getRecommendedAction($spamScore)
        ];
    }

    /**
     * Check for fraud patterns
     * 
     * @param int $userId
     * @param string $activityType
     * @param array $activityData
     * @return array
     */
    public function checkFraudPatterns(int $userId, string $activityType, array $activityData = []): array
    {
        $fraudScore = 0;
        $patterns = [];

        // Check for multiple accounts from same IP
        $ipPattern = $this->checkIPPattern($userId, $activityData['ip_address'] ?? '');
        if ($ipPattern['score'] > 0) {
            $fraudScore += $ipPattern['score'];
            $patterns[] = $ipPattern;
        }

        // Check for suspicious behavior patterns
        $behaviorPattern = $this->checkBehaviorPattern($userId, $activityType);
        if ($behaviorPattern['score'] > 0) {
            $fraudScore += $behaviorPattern['score'];
            $patterns[] = $behaviorPattern;
        }

        // Check for financial fraud patterns
        if (in_array($activityType, ['payment', 'subscription', 'refund'])) {
            $financialPattern = $this->checkFinancialPattern($userId, $activityData);
            if ($financialPattern['score'] > 0) {
                $fraudScore += $financialPattern['score'];
                $patterns[] = $financialPattern;
            }
        }

        $riskLevel = $this->getFraudRiskLevel($fraudScore);

        // Log high-risk fraud detection
        if ($riskLevel === 'high' || $riskLevel === 'very_high') {
            $this->logFraudDetection($userId, $activityType, $fraudScore, $patterns, $activityData);
        }

        return [
            'fraud_score' => $fraudScore,
            'risk_level' => $riskLevel,
            'detected_patterns' => $patterns,
            'recommended_actions' => $this->getFraudPreventionActions($riskLevel)
        ];
    }

    /**
     * Run quality control checks
     * 
     * @param string $contentType
     * @param int $contentId
     * @return array
     */
    public function runQualityControl(string $contentType, int $contentId): array
    {
        $qualityScore = 100; // Start with perfect score
        $issues = [];

        switch ($contentType) {
            case self::CONTENT_REVIEW:
                $qualityData = $this->checkReviewQuality($contentId);
                break;
            case self::CONTENT_BUSINESS:
                $qualityData = $this->checkBusinessProfileQuality($contentId);
                break;
            case self::CONTENT_COMPLAINT:
                $qualityData = $this->checkComplaintQuality($contentId);
                break;
            default:
                $qualityData = ['score' => 100, 'issues' => []];
        }

        $qualityScore = $qualityData['score'];
        $issues = $qualityData['issues'];

        // Log quality control result
        $this->logQualityControl($contentType, $contentId, $qualityScore, $issues);

        return [
            'quality_score' => $qualityScore,
            'quality_rating' => $this->getQualityRating($qualityScore),
            'issues_found' => $issues,
            'improvement_suggestions' => $this->getImprovementSuggestions($issues)
        ];
    }

    /**
     * Get moderation statistics
     * 
     * @param string $period
     * @return array
     */
    public function getModerationStatistics(string $period = '30days'): array
    {
        $dateRange = $this->getDateRange($period);

        return [
            'flagged_content' => $this->getFlaggedContentStats($dateRange['start'], $dateRange['end']),
            'spam_detection' => $this->getSpamDetectionStats($dateRange['start'], $dateRange['end']),
            'fraud_prevention' => $this->getFraudPreventionStats($dateRange['start'], $dateRange['end']),
            'quality_control' => $this->getQualityControlStats($dateRange['start'], $dateRange['end']),
            'moderator_performance' => $this->getModeratorPerformanceStats($dateRange['start'], $dateRange['end'])
        ];
    }

    /**
     * Get automated moderation rules
     * 
     * @return array
     */
    public function getAutomatedModerationRules(): array
    {
        return [
            'spam_detection' => [
                'enabled' => true,
                'min_confidence' => self::CONFIDENCE_HIGH,
                'auto_action' => 'flag'
            ],
            'fraud_prevention' => [
                'enabled' => true,
                'risk_threshold' => 'high',
                'auto_action' => 'suspend'
            ],
            'quality_control' => [
                'enabled' => true,
                'min_quality_score' => 60,
                'auto_action' => 'notify'
            ],
            'content_filtering' => [
                'enabled' => true,
                'blocked_terms' => $this->getBlockedTerms(),
                'auto_action' => 'reject'
            ]
        ];
    }

    /**
     * Update moderation rules
     * 
     * @param array $rules
     * @param int $updatedBy
     * @return bool
     */
    public function updateModerationRules(array $rules, int $updatedBy): bool
    {
        // This would typically save to a configuration table
        // For now, we'll just validate the rules
        $this->validateModerationRules($rules);
        
        // Log the rule update
        $this->logRuleUpdate($rules, $updatedBy);
        
        return true;
    }

    // Private helper methods

    private function getContentDetails(int $contentId, string $contentType): ?array
    {
        switch ($contentType) {
            case self::CONTENT_REVIEW:
                return $this->db->fetchOne(
                    "SELECT r.*, u.first_name, u.last_name, b.business_name 
                     FROM business_reviews r
                     INNER JOIN users u ON r.user_id = u.id
                     INNER JOIN business_profiles b ON r.business_id = b.id
                     WHERE r.id = :id",
                    ['id' => $contentId]
                );
            case self::CONTENT_COMPLAINT:
                return $this->db->fetchOne(
                    "SELECT c.*, u.first_name, u.last_name, b.business_name 
                     FROM complaints c
                     INNER JOIN users u ON c.user_id = u.id
                     INNER JOIN business_profiles b ON c.business_id = b.id
                     WHERE c.id = :id",
                    ['id' => $contentId]
                );
            case self::CONTENT_BUSINESS:
                return $this->db->fetchOne(
                    "SELECT * FROM business_profiles WHERE id = :id",
                    ['id' => $contentId]
                );
            case self::CONTENT_USER:
                return $this->db->fetchOne(
                    "SELECT id, first_name, last_name, email, user_type, status FROM users WHERE id = :id",
                    ['id' => $contentId]
                );
            default:
                return null;
        }
    }

    private function executeModerationActions(array $flagged, array $actions, int $moderatorId): void
    {
        foreach ($actions as $action) {
            switch ($action['type']) {
                case 'remove_content':
                    $this->removeContent($flagged['content_id'], $flagged['content_type'], $moderatorId);
                    break;
                case 'suspend_user':
                    $this->suspendUser($this->getContentAuthorId($flagged), $moderatorId, $action['reason'] ?? '');
                    break;
                case 'warn_user':
                    $this->sendUserWarning($this->getContentAuthorId($flagged), $moderatorId, $action['message'] ?? '');
                    break;
                case 'update_status':
                    $this->updateContentStatus($flagged['content_id'], $flagged['content_type'], $action['status'], $moderatorId);
                    break;
            }
        }
    }

    private function calculateSpamScore(string $content, string $contentType, int $authorId): int
    {
        $score = 0;

        // Check for spam keywords
        $spamKeywords = $this->getSpamKeywords();
        foreach ($spamKeywords as $keyword) {
            if (stripos($content, $keyword) !== false) {
                $score += 10;
            }
        }

        // Check for excessive links
        $linkCount = preg_match_all('/https?:\/\/[^\s]+/', $content, $matches);
        if ($linkCount > 3) {
            $score += $linkCount * 5;
        }

        // Check for repetitive content
        if ($this->isRepetitiveContent($content)) {
            $score += 20;
        }

        // Check author's spam history
        $authorSpamHistory = $this->getAuthorSpamHistory($authorId);
        if ($authorSpamHistory > 0) {
            $score += $authorSpamHistory * 15;
        }

        // Check content length (too short or too long)
        $contentLength = strlen($content);
        if ($contentLength < 10 || $contentLength > 1000) {
            $score += 10;
        }

        return min($score, 100);
    }

    private function getSpamConfidence(int $spamScore): string
    {
        if ($spamScore >= 80) return self::CONFIDENCE_VERY_HIGH;
        if ($spamScore >= 60) return self::CONFIDENCE_HIGH;
        if ($spamScore >= 40) return self::CONFIDENCE_MEDIUM;
        return self::CONFIDENCE_LOW;
    }

    private function checkIPPattern(int $userId, string $ipAddress): array
    {
        if (empty($ipAddress)) {
            return ['type' => 'ip_pattern', 'score' => 0, 'details' => 'No IP address provided'];
        }

        $usersFromSameIP = $this->db->fetchColumn(
            "SELECT COUNT(DISTINCT user_id) FROM user_sessions 
             WHERE ip_address = :ip AND user_id != :user_id 
             AND created_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)",
            ['ip' => $ipAddress, 'user_id' => $userId]
        ) ?: 0;

        $score = 0;
        if ($usersFromSameIP >= 3) {
            $score = 30;
        } elseif ($usersFromSameIP >= 2) {
            $score = 15;
        }

        return [
            'type' => 'ip_pattern',
            'score' => $score,
            'details' => "{$usersFromSameIP} users from same IP in last 24 hours",
            'ip_address' => $ipAddress
        ];
    }

    private function checkBehaviorPattern(int $userId, string $activityType): array
    {
        $score = 0;
        $details = [];

        // Check for rapid activity
        $recentActivities = $this->db->fetchColumn(
            "SELECT COUNT(*) FROM admin_activities 
             WHERE admin_id = :user_id AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)",
            ['user_id' => $userId]
        ) ?: 0;

        if ($recentActivities > 10) {
            $score += 20;
            $details[] = "High activity rate: {$recentActivities} actions in last hour";
        }

        // Check for unusual time patterns
        $currentHour = (int)date('H');
        if ($currentHour < 6 || $currentHour > 22) {
            $score += 10;
            $details[] = "Unusual activity time: {$currentHour}:00";
        }

        return [
            'type' => 'behavior_pattern',
            'score' => $score,
            'details' => implode('; ', $details)
        ];
    }

    private function checkFinancialPattern(int $userId, array $activityData): array
    {
        $score = 0;
        $details = [];

        // Check for multiple payment attempts
        if (!empty($activityData['payment_attempts'])) {
            $attempts = (int)$activityData['payment_attempts'];
            if ($attempts > 3) {
                $score += 25;
                $details[] = "Multiple payment attempts: {$attempts}";
            }
        }

        // Check for unusual amounts
        if (!empty($activityData['amount'])) {
            $amount = (float)$activityData['amount'];
            if ($amount > 1000 || $amount < 1) {
                $score += 15;
                $details[] = "Unusual transaction amount: {$amount}";
            }
        }

        return [
            'type' => 'financial_pattern',
            'score' => $score,
            'details' => implode('; ', $details)
        ];
    }

    private function getFraudRiskLevel(int $fraudScore): string
    {
        if ($fraudScore >= 70) return 'very_high';
        if ($fraudScore >= 50) return 'high';
        if ($fraudScore >= 30) return 'medium';
        if ($fraudScore >= 15) return 'low';
        return 'very_low';
    }

    private function checkReviewQuality(int $reviewId): array
    {
        $review = $this->db->fetchOne("SELECT * FROM business_reviews WHERE id = :id", ['id' => $reviewId]);
        if (!$review) {
            return ['score' => 0, 'issues' => ['Review not found']];
        }

        $score = 100;
        $issues = [];

        // Check content length
        if (strlen($review['content']) < 20) {
            $score -= 20;
            $issues[] = 'Review content is too short';
        }

        // Check for meaningful content (not just "good" or "bad")
        $meaningfulWords = ['because', 'however', 'although', 'specifically'];
        $hasMeaningfulContent = false;
        foreach ($meaningfulWords as $word) {
            if (stripos($review['content'], $word) !== false) {
                $hasMeaningfulContent = true;
                break;
            }
        }

        if (!$hasMeaningfulContent && strlen($review['content']) < 50) {
            $score -= 15;
            $issues[] = 'Review lacks detailed explanation';
        }

        // Check rating consistency
        if ($review['rating'] >= 4 && stripos($review['content'], 'bad') !== false) {
            $score -= 10;
            $issues[] = 'Rating inconsistent with content';
        }

        return ['score' => max($score, 0), 'issues' => $issues];
    }

    private function checkBusinessProfileQuality(int $businessId): array
    {
        $business = $this->db->fetchOne("SELECT * FROM business_profiles WHERE id = :id", ['id' => $businessId]);
        if (!$business) {
            return ['score' => 0, 'issues' => ['Business not found']];
        }

        $score = 100;
        $issues = [];

        // Check completeness
        $requiredFields = ['business_name', 'description', 'address', 'city', 'state', 'country'];
        foreach ($requiredFields as $field) {
            if (empty($business[$field])) {
                $score -= 10;
                $issues[] = "Missing required field: {$field}";
            }
        }

        // Check description length
        if (strlen($business['description'] ?? '') < 50) {
            $score -= 15;
            $issues[] = 'Business description is too short';
        }

        // Check contact information
        $contactInfo = $this->db->fetchOne(
            "SELECT COUNT(*) as count FROM business_contact_info WHERE business_id = :id",
            ['id' => $businessId]
        ) ?: 0;

        if ($contactInfo === 0) {
            $score -= 20;
            $issues[] = 'No contact information provided';
        }

        return ['score' => max($score, 0), 'issues' => $issues];
    }

    private function checkComplaintQuality(int $complaintId): array
    {
        $complaint = $this->db->fetchOne("SELECT * FROM complaints WHERE id = :id", ['id' => $complaintId]);
        if (!$complaint) {
            return ['score' => 0, 'issues' => ['Complaint not found']];
        }

        $score = 100;
        $issues = [];

        // Check description quality
        if (strlen($complaint['description']) < 50) {
            $score -= 25;
            $issues[] = 'Complaint description is too brief';
        }

        // Check for specific details
        $detailIndicators = ['date', 'time', 'specific', 'exactly', 'precisely'];
        $hasSpecificDetails = false;
        foreach ($detailIndicators as $indicator) {
            if (stripos($complaint['description'], $indicator) !== false) {
                $hasSpecificDetails = true;
                break;
            }
        }

        if (!$hasSpecificDetails) {
            $score -= 15;
            $issues[] = 'Complaint lacks specific details';
        }

        // Check desired resolution
        if (empty($complaint['desired_resolution'])) {
            $score -= 10;
            $issues[] = 'No desired resolution specified';
        }

        return ['score' => max($score, 0), 'issues' => $issues];
    }

    private function getQualityRating(int $score): string
    {
        if ($score >= 90) return 'excellent';
        if ($score >= 80) return 'good';
        if ($score >= 70) return 'fair';
        if ($score >= 60) return 'poor';
        return 'unacceptable';
    }

    private function getRecommendedAction(int $spamScore): string
    {
        if ($spamScore >= 80) return 'auto_reject';
        if ($spamScore >= 60) return 'flag_for_review';
        if ($spamScore >= 40) return 'monitor';
        return 'no_action';
    }

    private function getFraudPreventionActions(string $riskLevel): array
    {
        $actions = [
            'very_low' => ['monitor'],
            'low' => ['monitor', 'verify_identity'],
            'medium' => ['flag', 'require_verification', 'limit_activity'],
            'high' => ['suspend', 'investigate', 'notify_admin'],
            'very_high' => ['block', 'investigate', 'notify_admin', 'report_authorities']
        ];

        return $actions[$riskLevel] ?? ['monitor'];
    }

    private function getImprovementSuggestions(array $issues): array
    {
        $suggestions = [];
        $issueMap = [
            'too short' => 'Provide more detailed information',
            'lacks specific details' => 'Include specific examples and dates',
            'missing required field' => 'Complete all required profile fields',
            'no contact information' => 'Add contact information for better credibility',
            'inconsistent rating' => 'Ensure rating matches the content description'
        ];

        foreach ($issues as $issue) {
            foreach ($issueMap as $key => $suggestion) {
                if (stripos($issue, $key) !== false) {
                    $suggestions[] = $suggestion;
                    break;
                }
            }
        }

        return array_unique($suggestions);
    }

    private function getDateRange(string $period): array
    {
        $endDate = date('Y-m-d H:i:s');
        
        switch ($period) {
            case '7days':
                $startDate = date('Y-m-d H:i:s', strtotime('-7 days'));
                break;
            case '30days':
                $startDate = date('Y-m-d H:i:s', strtotime('-30 days'));
                break;
            case '90days':
                $startDate = date('Y-m-d H:i:s', strtotime('-90 days'));
                break;
            default:
                $startDate = date('Y-m-d H:i:s', strtotime('-30 days'));
        }

        return ['start' => $startDate, 'end' => $endDate];
    }

    private function getFlaggedContentStats(string $startDate, string $endDate): array
    {
        return [
            'total_flagged' => $this->db->fetchColumn(
                "SELECT COUNT(*) FROM {$this->flaggedContentTable} WHERE created_at BETWEEN :start AND :end",
                ['start' => $startDate, 'end' => $endDate]
            ) ?: 0,
            'resolved' => $this->db->fetchColumn(
                "SELECT COUNT(*) FROM {$this->flaggedContentTable} 
                 WHERE created_at BETWEEN :start AND :end AND status = 'resolved'",
                ['start' => $startDate, 'end' => $endDate]
            ) ?: 0,
            'average_resolution_time' => $this->db->fetchColumn(
                "SELECT AVG(TIMESTAMPDIFF(MINUTE, created_at, resolved_at)) 
                 FROM {$this->flaggedContentTable} 
                 WHERE resolved_at BETWEEN :start AND :end",
                ['start' => $startDate, 'end' => $endDate]
            ) ?: 0
        ];
    }

    // ... Additional private helper methods for statistics and logging

    private function getSpamKeywords(): array
    {
        return [
            'buy now', 'click here', 'discount', 'free', 'make money', 'opportunity',
            'cash', 'earn', 'extra income', 'financial freedom', 'get paid',
            'income from home', 'money making', 'online biz', 'work from home',
            'viagra', 'casino', 'loan', 'mortgage', 'insurance'
        ];
    }

    private function getBlockedTerms(): array
    {
        return [
            'hate speech' => ['racist', 'sexist', 'homophobic', 'transphobic'],
            'harassment' => ['threat', 'bully', 'stalk', 'intimidate'],
            'illegal' => ['drugs', 'weapons', 'fraud', 'scam']
        ];
    }

    private function validateModerationRules(array $rules): void
    {
        $requiredSections = ['spam_detection', 'fraud_prevention', 'quality_control', 'content_filtering'];
        
        foreach ($requiredSections as $section) {
            if (!isset($rules[$section])) {
                throw new Exception("Missing moderation rules section: {$section}");
            }
        }
    }
}
