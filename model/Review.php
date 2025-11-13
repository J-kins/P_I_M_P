<?php
/**
 * P.I.M.P - Review and Rating Model
 * Handles business reviews, ratings, and response management
 */

namespace PIMP\Models;

use PIMP\Services\Database\MySQLDatabase;
use PDOException;
use Exception;

class Review
{
    /**
     * @var MySQLDatabase Database instance
     */
    private $db;

    /**
     * @var string Reviews table name
     */
    private $reviewsTable = 'business_reviews';

    /**
     * @var string Review responses table
     */
    private $responsesTable = 'review_responses';

    /**
     * @var string Review media table
     */
    private $mediaTable = 'review_media';

    /**
     * @var string Review votes table
     */
    private $votesTable = 'review_votes';

    /**
     * Review status constants
     */
    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';
    const STATUS_FLAGGED = 'flagged';
    const STATUS_EDITED = 'edited';

    /**
     * Rating criteria
     */
    const CRITERIA_OVERALL = 'overall';
    const CRITERIA_SERVICE = 'service';
    const CRITERIA_QUALITY = 'quality';
    const CRITERIA_VALUE = 'value';
    const CRITERIA_COMMUNICATION = 'communication';

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
     * Create new review
     * 
     * @param int $userId
     * @param int $businessId
     * @param array $reviewData
     * @return array
     * @throws Exception
     */
    public function createReview(int $userId, int $businessId, array $reviewData): array
    {
        $requiredFields = ['title', 'content', 'rating'];
        foreach ($requiredFields as $field) {
            if (empty($reviewData[$field])) {
                throw new Exception("Required field missing: {$field}");
            }
        }

        // Validate rating
        $rating = (float)$reviewData['rating'];
        if ($rating < 1 || $rating > 5) {
            throw new Exception("Rating must be between 1 and 5");
        }

        // Check if user has already reviewed this business
        $existing = $this->db->fetchOne(
            "SELECT id FROM {$this->reviewsTable} WHERE user_id = :user_id AND business_id = :business_id",
            ['user_id' => $userId, 'business_id' => $businessId]
        );

        if ($existing) {
            throw new Exception("You have already reviewed this business");
        }

        $reviewData['user_id'] = $userId;
        $reviewData['business_id'] = $businessId;
        $reviewData['review_id'] = $this->generateReviewId();
        $reviewData['status'] = self::STATUS_PENDING;
        $reviewData['created_at'] = date('Y-m-d H:i:s');
        $reviewData['updated_at'] = date('Y-m-d H:i:s');

        // Calculate average rating if multiple criteria provided
        if (!empty($reviewData['rating_breakdown'])) {
            $ratings = array_values($reviewData['rating_breakdown']);
            $reviewData['rating'] = round(array_sum($ratings) / count($ratings), 1);
        }

        try {
            $this->db->beginTransaction();

            $columns = implode(', ', array_keys($reviewData));
            $placeholders = ':' . implode(', :', array_keys($reviewData));
            
            $query = "INSERT INTO {$this->reviewsTable} ({$columns}) VALUES ({$placeholders})";
            $this->db->query($query, $reviewData);

            $reviewId = $this->db->lastInsertId();

            // Update business rating
            $this->updateBusinessRating($businessId);
            
            $this->db->commit();

            return $this->getReviewById($reviewId);
            
        } catch (PDOException $e) {
            $this->db->rollback();
            throw new Exception("Failed to create review: " . $e->getMessage());
        }
    }

    /**
     * Get review by ID
     * 
     * @param int $reviewId
     * @return array|null
     */
    public function getReviewById(int $reviewId): ?array
    {
        $query = "SELECT r.*, u.first_name, u.last_name, u.verification_level,
                         b.business_name, b.business_id as business_identifier
                  FROM {$this->reviewsTable} r
                  INNER JOIN users u ON r.user_id = u.id
                  INNER JOIN business_profiles b ON r.business_id = b.id
                  WHERE r.id = :id";
        
        $review = $this->db->fetchOne($query, ['id' => $reviewId]);
        
        if ($review) {
            $review['responses'] = $this->getReviewResponses($reviewId);
            $review['media'] = $this->getReviewMedia($reviewId);
            $review['vote_count'] = $this->getReviewVoteCount($reviewId);
            
            if ($review['rating_breakdown']) {
                $review['rating_breakdown'] = json_decode($review['rating_breakdown'], true);
            }
            if ($review['metadata']) {
                $review['metadata'] = json_decode($review['metadata'], true);
            }
        }
        
        return $review ?: null;
    }

    /**
     * Get business reviews
     * 
     * @param int $businessId
     * @param array $filters
     * @param int $page
     * @param int $perPage
     * @return array
     */
    public function getBusinessReviews(int $businessId, array $filters = [], int $page = 1, int $perPage = 20): array
    {
        $whereConditions = ["r.business_id = :business_id"];
        $params = ['business_id' => $businessId];

        if (!empty($filters['status'])) {
            $whereConditions[] = "r.status = :status";
            $params['status'] = $filters['status'];
        } else {
            $whereConditions[] = "r.status = :status";
            $params['status'] = self::STATUS_APPROVED;
        }

        if (!empty($filters['min_rating'])) {
            $whereConditions[] = "r.rating >= :min_rating";
            $params['min_rating'] = $filters['min_rating'];
        }

        if (!empty($filters['max_rating'])) {
            $whereConditions[] = "r.rating <= :max_rating";
            $params['max_rating'] = $filters['max_rating'];
        }

        if (!empty($filters['has_media'])) {
            $whereConditions[] = "r.id IN (SELECT DISTINCT review_id FROM {$this->mediaTable})";
        }

        if (!empty($filters['verified_only'])) {
            $whereConditions[] = "u.verification_level = :verification_level";
            $params['verification_level'] = 'full';
        }

        $whereClause = implode(' AND ', $whereConditions);
        $offset = ($page - 1) * $perPage;

        // Determine sort order
        $sortOrder = 'r.created_at DESC';
        if (!empty($filters['sort_by'])) {
            switch ($filters['sort_by']) {
                case 'newest':
                    $sortOrder = 'r.created_at DESC';
                    break;
                case 'oldest':
                    $sortOrder = 'r.created_at ASC';
                    break;
                case 'highest_rating':
                    $sortOrder = 'r.rating DESC, r.created_at DESC';
                    break;
                case 'lowest_rating':
                    $sortOrder = 'r.rating ASC, r.created_at DESC';
                    break;
                case 'most_helpful':
                    $sortOrder = '(SELECT COUNT(*) FROM {$this->votesTable} WHERE review_id = r.id AND vote_type = "helpful") DESC, r.created_at DESC';
                    break;
            }
        }

        $query = "SELECT r.*, u.first_name, u.last_name, u.verification_level,
                         (SELECT COUNT(*) FROM {$this->votesTable} WHERE review_id = r.id AND vote_type = 'helpful') as helpful_count,
                         (SELECT COUNT(*) FROM {$this->responsesTable} WHERE review_id = r.id) as response_count
                  FROM {$this->reviewsTable} r
                  INNER JOIN users u ON r.user_id = u.id
                  WHERE {$whereClause} 
                  ORDER BY {$sortOrder} 
                  LIMIT :limit OFFSET :offset";
        
        $params['limit'] = $perPage;
        $params['offset'] = $offset;

        $reviews = $this->db->fetchAll($query, $params);

        // Get total count for pagination
        $countQuery = "SELECT COUNT(*) as total FROM {$this->reviewsTable} r 
                       INNER JOIN users u ON r.user_id = u.id 
                       WHERE {$whereClause}";
        $total = $this->db->fetchColumn($countQuery, $params);

        return [
            'reviews' => $reviews,
            'pagination' => [
                'page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'total_pages' => ceil($total / $perPage)
            ]
        ];
    }

    /**
     * Update review status
     * 
     * @param int $reviewId
     * @param string $status
     * @param string $reason
     * @param int $updatedBy
     * @return bool
     * @throws Exception
     */
    public function updateReviewStatus(int $reviewId, string $status, string $reason = '', int $updatedBy = 0): bool
    {
        $validStatuses = [self::STATUS_PENDING, self::STATUS_APPROVED, self::STATUS_REJECTED, self::STATUS_FLAGGED];
        if (!in_array($status, $validStatuses)) {
            throw new Exception("Invalid review status: {$status}");
        }

        $review = $this->getReviewById($reviewId);
        if (!$review) {
            throw new Exception("Review not found");
        }

        $query = "UPDATE {$this->reviewsTable} SET status = :status, status_reason = :reason, 
                  updated_by = :updated_by, updated_at = :updated_at WHERE id = :id";
        
        $this->db->query($query, [
            'status' => $status,
            'reason' => $reason,
            'updated_by' => $updatedBy,
            'updated_at' => date('Y-m-d H:i:s'),
            'id' => $reviewId
        ]);

        // Update business rating if review status changed to/from approved
        if ($review['status'] !== $status && ($review['status'] === self::STATUS_APPROVED || $status === self::STATUS_APPROVED)) {
            $this->updateBusinessRating($review['business_id']);
        }

        return true;
    }

    /**
     * Add review response
     * 
     * @param int $reviewId
     * @param int $businessId
     * @param int $respondentId
     * @param string $response
     * @return array
     * @throws Exception
     */
    public function addReviewResponse(int $reviewId, int $businessId, int $respondentId, string $response): array
    {
        $review = $this->getReviewById($reviewId);
        if (!$review) {
            throw new Exception("Review not found");
        }

        // Check if business owns the review
        if ($review['business_id'] !== $businessId) {
            throw new Exception("Business does not own this review");
        }

        // Check if response already exists
        $existing = $this->db->fetchOne(
            "SELECT id FROM {$this->responsesTable} WHERE review_id = :review_id",
            ['review_id' => $reviewId]
        );

        if ($existing) {
            throw new Exception("Response already exists for this review");
        }

        $responseData = [
            'review_id' => $reviewId,
            'respondent_id' => $respondentId,
            'response' => $response,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $columns = implode(', ', array_keys($responseData));
        $placeholders = ':' . implode(', :', array_keys($responseData));
        
        $query = "INSERT INTO {$this->responsesTable} ({$columns}) VALUES ({$placeholders})";
        $this->db->query($query, $responseData);

        $responseId = $this->db->lastInsertId();
        return $this->getReviewResponse($responseId);
    }

    /**
     * Get review response
     * 
     * @param int $responseId
     * @return array|null
     */
    public function getReviewResponse(int $responseId): ?array
    {
        $query = "SELECT rr.*, u.first_name, u.last_name, u.user_type 
                  FROM {$this->responsesTable} rr
                  INNER JOIN users u ON rr.respondent_id = u.id
                  WHERE rr.id = :id";
        
        return $this->db->fetchOne($query, ['id' => $responseId]) ?: null;
    }

    /**
     * Get review responses
     * 
     * @param int $reviewId
     * @return array
     */
    public function getReviewResponses(int $reviewId): array
    {
        $query = "SELECT rr.*, u.first_name, u.last_name, u.user_type 
                  FROM {$this->responsesTable} rr
                  INNER JOIN users u ON rr.respondent_id = u.id
                  WHERE rr.review_id = :review_id 
                  ORDER BY rr.created_at ASC";
        
        return $this->db->fetchAll($query, ['review_id' => $reviewId]);
    }

    /**
     * Add media to review
     * 
     * @param int $reviewId
     * @param array $mediaData
     * @return array
     * @throws Exception
     */
    public function addReviewMedia(int $reviewId, array $mediaData): array
    {
        $requiredFields = ['file_name', 'file_path', 'file_type'];
        foreach ($requiredFields as $field) {
            if (empty($mediaData[$field])) {
                throw new Exception("Required field missing: {$field}");
            }
        }

        $mediaData['review_id'] = $reviewId;
        $mediaData['created_at'] = date('Y-m-d H:i:s');

        $columns = implode(', ', array_keys($mediaData));
        $placeholders = ':' . implode(', :', array_keys($mediaData));
        
        $query = "INSERT INTO {$this->mediaTable} ({$columns}) VALUES ({$placeholders})";
        $this->db->query($query, $mediaData);

        $mediaId = $this->db->lastInsertId();
        return $this->getReviewMediaItem($mediaId);
    }

    /**
     * Get review media item
     * 
     * @param int $mediaId
     * @return array|null
     */
    public function getReviewMediaItem(int $mediaId): ?array
    {
        $query = "SELECT * FROM {$this->mediaTable} WHERE id = :id";
        return $this->db->fetchOne($query, ['id' => $mediaId]) ?: null;
    }

    /**
     * Get review media
     * 
     * @param int $reviewId
     * @return array
     */
    public function getReviewMedia(int $reviewId): array
    {
        $query = "SELECT * FROM {$this->mediaTable} WHERE review_id = :review_id ORDER BY created_at ASC";
        return $this->db->fetchAll($query, ['review_id' => $reviewId]);
    }

    /**
     * Vote on review
     * 
     * @param int $reviewId
     * @param int $userId
     * @param string $voteType
     * @return bool
     * @throws Exception
     */
    public function voteOnReview(int $reviewId, int $userId, string $voteType): bool
    {
        $validVoteTypes = ['helpful', 'not_helpful'];
        if (!in_array($voteType, $validVoteTypes)) {
            throw new Exception("Invalid vote type: {$voteType}");
        }

        // Check if user already voted
        $existing = $this->db->fetchOne(
            "SELECT id, vote_type FROM {$this->votesTable} WHERE review_id = :review_id AND user_id = :user_id",
            ['review_id' => $reviewId, 'user_id' => $userId]
        );

        if ($existing) {
            if ($existing['vote_type'] === $voteType) {
                // Remove vote if same type clicked again
                $query = "DELETE FROM {$this->votesTable} WHERE review_id = :review_id AND user_id = :user_id";
            } else {
                // Update vote type
                $query = "UPDATE {$this->votesTable} SET vote_type = :vote_type, updated_at = :updated_at 
                          WHERE review_id = :review_id AND user_id = :user_id";
            }
        } else {
            // Create new vote
            $query = "INSERT INTO {$this->votesTable} (review_id, user_id, vote_type, created_at, updated_at) 
                      VALUES (:review_id, :user_id, :vote_type, :created_at, :updated_at)";
        }

        $this->db->query($query, [
            'review_id' => $reviewId,
            'user_id' => $userId,
            'vote_type' => $voteType,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        return true;
    }

    /**
     * Get review vote count
     * 
     * @param int $reviewId
     * @return array
     */
    public function getReviewVoteCount(int $reviewId): array
    {
        $helpful = $this->db->fetchColumn(
            "SELECT COUNT(*) FROM {$this->votesTable} WHERE review_id = :review_id AND vote_type = 'helpful'",
            ['review_id' => $reviewId]
        ) ?: 0;

        $notHelpful = $this->db->fetchColumn(
            "SELECT COUNT(*) FROM {$this->votesTable} WHERE review_id = :review_id AND vote_type = 'not_helpful'",
            ['review_id' => $reviewId]
        ) ?: 0;

        return [
            'helpful' => (int)$helpful,
            'not_helpful' => (int)$notHelpful,
            'total' => (int)$helpful + (int)$notHelpful
        ];
    }

    /**
     * Update business rating
     * 
     * @param int $businessId
     * @return bool
     */
    private function updateBusinessRating(int $businessId): bool
    {
        $ratingStats = $this->db->fetchOne(
            "SELECT AVG(rating) as avg_rating, COUNT(*) as total_reviews 
             FROM {$this->reviewsTable} 
             WHERE business_id = :business_id AND status = :status",
            [
                'business_id' => $businessId,
                'status' => self::STATUS_APPROVED
            ]
        );

        if ($ratingStats) {
            $query = "UPDATE business_profiles SET rating = :rating, total_reviews = :total_reviews, 
                      updated_at = :updated_at WHERE id = :id";
            
            $this->db->query($query, [
                'rating' => round($ratingStats['avg_rating'], 2),
                'total_reviews' => $ratingStats['total_reviews'],
                'updated_at' => date('Y-m-d H:i:s'),
                'id' => $businessId
            ]);
        }

        return true;
    }

    /**
     * Get review statistics
     * 
     * @param int $businessId
     * @return array
     */
    public function getReviewStatistics(int $businessId): array
    {
        $totalReviews = $this->db->fetchColumn(
            "SELECT COUNT(*) FROM {$this->reviewsTable} WHERE business_id = :business_id AND status = :status",
            [
                'business_id' => $businessId,
                'status' => self::STATUS_APPROVED
            ]
        ) ?: 0;

        $ratingDistribution = $this->db->fetchAll(
            "SELECT FLOOR(rating) as rating, COUNT(*) as count 
             FROM {$this->reviewsTable} 
             WHERE business_id = :business_id AND status = :status 
             GROUP BY FLOOR(rating) 
             ORDER BY rating DESC",
            [
                'business_id' => $businessId,
                'status' => self::STATUS_APPROVED
            ]
        );

        $reviewsWithMedia = $this->db->fetchColumn(
            "SELECT COUNT(DISTINCT r.id) 
             FROM {$this->reviewsTable} r
             INNER JOIN {$this->mediaTable} m ON r.id = m.review_id
             WHERE r.business_id = :business_id AND r.status = :status",
            [
                'business_id' => $businessId,
                'status' => self::STATUS_APPROVED
            ]
        ) ?: 0;

        $responseRate = $this->db->fetchColumn(
            "SELECT COUNT(DISTINCT r.id) / :total_reviews * 100 as response_rate
             FROM {$this->reviewsTable} r
             INNER JOIN {$this->responsesTable} rr ON r.id = rr.review_id
             WHERE r.business_id = :business_id AND r.status = :status",
            [
                'business_id' => $businessId,
                'status' => self::STATUS_APPROVED,
                'total_reviews' => $totalReviews > 0 ? $totalReviews : 1
            ]
        ) ?: 0;

        // Format rating distribution
        $distribution = [];
        for ($i = 5; $i >= 1; $i--) {
            $distribution[$i] = 0;
            foreach ($ratingDistribution as $dist) {
                if ($dist['rating'] == $i) {
                    $distribution[$i] = (int)$dist['count'];
                    break;
                }
            }
        }

        return [
            'total_reviews' => (int)$totalReviews,
            'average_rating' => $this->db->fetchColumn(
                "SELECT AVG(rating) FROM {$this->reviewsTable} WHERE business_id = :business_id AND status = :status",
                [
                    'business_id' => $businessId,
                    'status' => self::STATUS_APPROVED
                ]
            ) ?: 0,
            'rating_distribution' => $distribution,
            'reviews_with_media' => (int)$reviewsWithMedia,
            'response_rate' => round($responseRate, 2),
            'pending_reviews' => $this->db->fetchColumn(
                "SELECT COUNT(*) FROM {$this->reviewsTable} WHERE business_id = :business_id AND status = :status",
                [
                    'business_id' => $businessId,
                    'status' => self::STATUS_PENDING
                ]
            ) ?: 0
        ];
    }

    /**
     * Generate unique review ID
     * 
     * @return string
     */
    private function generateReviewId(): string
    {
        $prefix = 'REV';
        $timestamp = time();
        $random = mt_rand(1000, 9999);
        
        return $prefix . $timestamp . $random;
    }
}
