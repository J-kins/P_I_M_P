<?php
/**
 * P.I.M.P - Search History API Service
 * Handles search history and preferences API operations
 */

namespace PIMP\Services\API;

use PIMP\Models\SearchHistory;
use PIMP\Services\Database\MySQLDatabase;
use Exception;

class SearchHistoryAPIService
{
    /**
     * @var SearchHistory
     */
    private $searchHistoryModel;

    /**
     * Constructor
     * 
     * @param MySQLDatabase $db
     */
    public function __construct(MySQLDatabase $db)
    {
        $this->searchHistoryModel = new SearchHistory($db);
    }

    /**
     * Record search query
     * 
     * @param int $userId
     * @param string $query
     * @param string $searchType
     * @param array $filters
     * @param int $resultCount
     * @return array
     */
    public function recordSearch(int $userId, string $query, string $searchType = 'business', array $filters = [], int $resultCount = 0): array
    {
        try {
            $search = $this->searchHistoryModel->recordSearch($userId, $query, $searchType, $filters, $resultCount);

            return [
                'success' => true,
                'message' => 'Search recorded successfully',
                'data' => $search
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
     * Get user search history
     * 
     * @param int $userId
     * @param array $filters
     * @param int $limit
     * @return array
     */
    public function getUserSearchHistory(int $userId, array $filters = [], int $limit = 50): array
    {
        try {
            $history = $this->searchHistoryModel->getUserSearchHistory($userId, $filters, $limit);

            return [
                'success' => true,
                'message' => 'Search history retrieved successfully',
                'data' => $history
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
     * Clear search history
     * 
     * @param int $userId
     * @param array $filters
     * @return array
     */
    public function clearSearchHistory(int $userId, array $filters = []): array
    {
        try {
            $this->searchHistoryModel->clearSearchHistory($userId, $filters);

            return [
                'success' => true,
                'message' => 'Search history cleared successfully',
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
     * Get popular searches
     * 
     * @param array $filters
     * @param int $limit
     * @return array
     */
    public function getPopularSearches(array $filters = [], int $limit = 20): array
    {
        try {
            $popularSearches = $this->searchHistoryModel->getPopularSearches($filters, $limit);

            return [
                'success' => true,
                'message' => 'Popular searches retrieved successfully',
                'data' => $popularSearches
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
     * Get search suggestions
     * 
     * @param int $userId
     * @param string $partialQuery
     * @param int $limit
     * @return array
     */
    public function getSearchSuggestions(int $userId, string $partialQuery, int $limit = 10): array
    {
        try {
            $suggestions = $this->searchHistoryModel->getSearchSuggestions($userId, $partialQuery, $limit);

            return [
                'success' => true,
                'message' => 'Search suggestions retrieved successfully',
                'data' => $suggestions
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
     * Update search preferences
     * 
     * @param int $userId
     * @param array $preferences
     * @return array
     */
    public function updateSearchPreferences(int $userId, array $preferences): array
    {
        try {
            $this->searchHistoryModel->updateSearchPreferences($userId, $preferences);

            return [
                'success' => true,
                'message' => 'Search preferences updated successfully',
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
     * Get search preferences
     * 
     * @param int $userId
     * @return array
     */
    public function getSearchPreferences(int $userId): array
    {
        try {
            $preferences = $this->searchHistoryModel->getSearchPreferences($userId);

            return [
                'success' => true,
                'message' => 'Search preferences retrieved successfully',
                'data' => $preferences
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
     * Get user search patterns
     * 
     * @param int $userId
     * @param int $limit
     * @return array
     */
    public function getUserSearchPatterns(int $userId, int $limit = 10): array
    {
        try {
            $patterns = $this->searchHistoryModel->getUserSearchPatterns($userId, $limit);

            return [
                'success' => true,
                'message' => 'User search patterns retrieved successfully',
                'data' => $patterns
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
     * Get search analytics
     * 
     * @param array $filters
     * @return array
     */
    public function getSearchAnalytics(array $filters = []): array
    {
        try {
            $analytics = $this->searchHistoryModel->getSearchAnalytics($filters);

            return [
                'success' => true,
                'message' => 'Search analytics retrieved successfully',
                'data' => $analytics
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
