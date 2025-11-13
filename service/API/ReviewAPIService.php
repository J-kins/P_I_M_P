<?php
/**
 * P.I.M.P - Review API Service
 * Handles review and rating API operations
 */

namespace PIMP\Services\API;

use PIMP\Models\Review;
use PIMP\Services\Database\MySQLDatabase;
use Exception;

class ReviewAPIService
{
    /**
     * @var Review
     */
    private $reviewModel;

    /**
     * Constructor
     * 
     * @param MySQLDatabase $db
     */
    public function __construct(MySQLDatabase $db)
    {
        $this->reviewModel = new Review($db);
    }

    /**
     * Create new review
     * 
     * @param int $userId
     * @param int $businessId
     * @param array $reviewData
     * @return array
     */
    public function createReview(int $userId, int $businessId, array $reviewData): array
    {
        try {
            $review = $this->reviewModel->createReview($userId, $businessId, $reviewData);
            
            return [
                'success' => true,
                'message' => 'Review submitted successfully',
                'data' => $review
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
     * Get review by ID
     * 
     * @param int $reviewId
     * @return array
     */
    public function getReview(int $reviewId): array
    {
        try {
            $review = $this->reviewModel->getReviewById($reviewId);
            
            if (!$review) {
                throw new Exception("Review not found");
            }

            return [
                'success' => true,
                'message' => 'Review retrieved successfully',
                'data' => $review
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
        try {
            $result = $this->reviewModel->getBusinessReviews($businessId, $filters, $page, $perPage);

            return [
                'success' => true,
                'message' => 'Business reviews retrieved successfully',
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
     * Update review status
     * 
     * @param int $reviewId
     * @param string $status
     * @param string $reason
     * @param int $updatedBy
     * @return array
     */
    public function updateReviewStatus(int $reviewId, string $status, string $reason = '', int $updatedBy = 0): array
    {
        try {
            $this->reviewModel->updateReviewStatus($reviewId, $status, $reason, $updatedBy);

            return [
                'success' => true,
                'message' => 'Review status updated successfully',
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
     * Add review response
     * 
     * @param int $reviewId
     * @param int $businessId
     * @param int $respondentId
     * @param string $response
     * @return array
     */
    public function addReviewResponse(int $reviewId, int $businessId, int $respondentId, string $response): array
    {
        try {
            $reviewResponse = $this->reviewModel->addReviewResponse($reviewId, $businessId, $respondentId, $response);

            return [
                'success' => true,
                'message' => 'Review response added successfully',
                'data' => $reviewResponse
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
     * Get review responses
     * 
     * @param int $reviewId
     * @return array
     */
    public function getReviewResponses(int $reviewId): array
    {
        try {
            $responses = $this->reviewModel->getReviewResponses($reviewId);

            return [
                'success' => true,
                'message' => 'Review responses retrieved successfully',
                'data' => $responses
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
     * Add review media
     * 
     * @param int $reviewId
     * @param array $mediaData
     * @return array
     */
    public function addReviewMedia(int $reviewId, array $mediaData): array
    {
        try {
            $media = $this->reviewModel->addReviewMedia($reviewId, $mediaData);

            return [
                'success' => true,
                'message' => 'Review media added successfully',
                'data' => $media
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
     * Get review media
     * 
     * @param int $reviewId
     * @return array
     */
    public function getReviewMedia(int $reviewId): array
    {
        try {
            $media = $this->reviewModel->getReviewMedia($reviewId);

            return [
                'success' => true,
                'message' => 'Review media retrieved successfully',
                'data' => $media
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
     * Vote on review
     * 
     * @param int $reviewId
     * @param int $userId
     * @param string $voteType
     * @return array
     */
    public function voteOnReview(int $reviewId, int $userId, string $voteType): array
    {
        try {
            $this->reviewModel->voteOnReview($reviewId, $userId, $voteType);

            return [
                'success' => true,
                'message' => 'Vote recorded successfully',
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
     * Get review vote count
     * 
     * @param int $reviewId
     * @return array
     */
    public function getReviewVoteCount(int $reviewId): array
    {
        try {
            $voteCount = $this->reviewModel->getReviewVoteCount($reviewId);

            return [
                'success' => true,
                'message' => 'Review vote count retrieved successfully',
                'data' => $voteCount
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
     * Get review statistics
     * 
     * @param int $businessId
     * @return array
     */
    public function getReviewStatistics(int $businessId): array
    {
        try {
            $statistics = $this->reviewModel->getReviewStatistics($businessId);

            return [
                'success' => true,
                'message' => 'Review statistics retrieved successfully',
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
