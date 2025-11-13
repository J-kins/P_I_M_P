<?php
/**
 * P.I.M.P - Search History and Preferences Model
 * Handles user search history, preferences, and personalized recommendations
 */

namespace PIMP\Models;

use PIMP\Services\Database\MySQLDatabase;
use PDOException;
use Exception;

class SearchHistory
{
    /**
     * @var MySQLDatabase Database instance
     */
    private $db;

    /**
     * @var string Search history table name
     */
    private $searchHistoryTable = 'user_search_history';

    /**
     * @var string Search preferences table
     */
    private $searchPreferencesTable = 'user_search_preferences';

    /**
     * Search types
     */
    const TYPE_BUSINESS = 'business';
    const TYPE_CATEGORY = 'category';
    const TYPE_LOCATION = 'location';
    const TYPE_REVIEW = 'review';
    const TYPE_COMPLAINT = 'complaint';

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
     * Record search query
     * 
     * @param int $userId
     * @param string $query
     * @param string $searchType
     * @param array $filters
     * @param int $resultCount
     * @return array
     * @throws Exception
     */
    public function recordSearch(int $userId, string $query, string $searchType = self::TYPE_BUSINESS, array $filters = [], int $resultCount = 0): array
    {
        $validTypes = [self::TYPE_BUSINESS, self::TYPE_CATEGORY, self::TYPE_LOCATION, self::TYPE_REVIEW, self::TYPE_COMPLAINT];
        if (!in_array($searchType, $validTypes)) {
            throw new Exception("Invalid search type: {$searchType}");
        }

        $searchData = [
            'user_id' => $userId,
            'search_query' => $query,
            'search_type' => $searchType,
            'filters' => !empty($filters) ? json_encode($filters) : null,
            'result_count' => $resultCount,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            'created_at' => date('Y-m-d H:i:s')
        ];

        $columns = implode(', ', array_keys($searchData));
        $placeholders = ':' . implode(', :', array_keys($searchData));
        
        $query = "INSERT INTO {$this->searchHistoryTable} ({$columns}) VALUES ({$placeholders})";
        $this->db->query($query, $searchData);

        $searchId = $this->db->lastInsertId();
        return $this->getSearchById($searchId);
    }

    /**
     * Get search by ID
     * 
     * @param int $searchId
     * @return array|null
     */
    public function getSearchById(int $searchId): ?array
    {
        $query = "SELECT sh.*, u.first_name, u.last_name 
                  FROM {$this->searchHistoryTable} sh
                  INNER JOIN users u ON sh.user_id = u.id
                  WHERE sh.id = :id";
        
        $search = $this->db->fetchOne($query, ['id' => $searchId]);
        
        if ($search && $search['filters']) {
            $search['filters'] = json_decode($search['filters'], true);
        }
        
        return $search ?: null;
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
        $whereConditions = ["user_id = :user_id"];
        $params = ['user_id' => $userId, 'limit' => $limit];

        if (!empty($filters['search_type'])) {
            $whereConditions[] = "search_type = :search_type";
            $params['search_type'] = $filters['search_type'];
        }

        if (!empty($filters['date_from'])) {
            $whereConditions[] = "created_at >= :date_from";
            $params['date_from'] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $whereConditions[] = "created_at <= :date_to";
            $params['date_to'] = $filters['date_to'];
        }

        $whereClause = implode(' AND ', $whereConditions);
        $query = "SELECT * FROM {$this->searchHistoryTable} 
                  WHERE {$whereClause} 
                  ORDER BY created_at DESC 
                  LIMIT :limit";
        
        $searches = $this->db->fetchAll($query, $params);
        
        foreach ($searches as &$search) {
            if ($search['filters']) {
                $search['filters'] = json_decode($search['filters'], true);
            }
        }
        
        return $searches;
    }

    /**
     * Clear user search history
     * 
     * @param int $userId
     * @param array $filters
     * @return bool
     */
    public function clearSearchHistory(int $userId, array $filters = []): bool
    {
        $whereConditions = ["user_id = :user_id"];
        $params = ['user_id' => $userId];

        if (!empty($filters['search_type'])) {
            $whereConditions[] = "search_type = :search_type";
            $params['search_type'] = $filters['search_type'];
        }

        if (!empty($filters['date_from'])) {
            $whereConditions[] = "created_at >= :date_from";
            $params['date_from'] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $whereConditions[] = "created_at <= :date_to";
            $params['date_to'] = $filters['date_to'];
        }

        $whereClause = implode(' AND ', $whereConditions);
        $query = "DELETE FROM {$this->searchHistoryTable} WHERE {$whereClause}";
        
        $this->db->query($query, $params);

        return true;
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
        $whereConditions = ["1=1"];
        $params = ['limit' => $limit];

        if (!empty($filters['search_type'])) {
            $whereConditions[] = "search_type = :search_type";
            $params['search_type'] = $filters['search_type'];
        }

        if (!empty($filters['date_from'])) {
            $whereConditions[] = "created_at >= :date_from";
            $params['date_from'] = $filters['date_from'];
        }

        $whereClause = implode(' AND ', $whereConditions);
        
        $query = "SELECT search_query, search_type, COUNT(*) as search_count, 
                         COUNT(DISTINCT user_id) as unique_users,
                         AVG(result_count) as avg_results
                  FROM {$this->searchHistoryTable} 
                  WHERE {$whereClause}
                  GROUP BY search_query, search_type 
                  ORDER BY search_count DESC 
                  LIMIT :limit";
        
        return $this->db->fetchAll($query, $params);
    }

    /**
     * Get search suggestions for user
     * 
     * @param int $userId
     * @param string $partialQuery
     * @param int $limit
     * @return array
     */
    public function getSearchSuggestions(int $userId, string $partialQuery, int $limit = 10): array
    {
        // Get user's previous searches
        $userSearches = $this->db->fetchAll(
            "SELECT DISTINCT search_query 
             FROM {$this->searchHistoryTable} 
             WHERE user_id = :user_id AND search_query LIKE :query 
             ORDER BY created_at DESC 
             LIMIT :limit",
            [
                'user_id' => $userId,
                'query' => $partialQuery . '%',
                'limit' => $limit
            ]
        );

        // Get popular searches matching the query
        $popularSearches = $this->db->fetchAll(
            "SELECT search_query, COUNT(*) as search_count 
             FROM {$this->searchHistoryTable} 
             WHERE search_query LIKE :query 
             GROUP BY search_query 
             ORDER BY search_count DESC 
             LIMIT :limit",
            [
                'query' => $partialQuery . '%',
                'limit' => $limit
            ]
        );

        $suggestions = [];

        // Add user's previous searches first
        foreach ($userSearches as $search) {
            $suggestions[$search['search_query']] = [
                'query' => $search['search_query'],
                'type' => 'user_history',
                'weight' => 2 // Higher weight for user's own searches
            ];
        }

        // Add popular searches
        foreach ($popularSearches as $search) {
            if (!isset($suggestions[$search['search_query']])) {
                $suggestions[$search['search_query']] = [
                    'query' => $search['search_query'],
                    'type' => 'popular',
                    'weight' => 1,
                    'search_count' => $search['search_count']
                ];
            }
        }

        // Sort by weight and return only the query strings
        usort($suggestions, function($a, $b) {
            return $b['weight'] - $a['weight'];
        });

        return array_slice($suggestions, 0, $limit);
    }

    /**
     * Update search preferences
     * 
     * @param int $userId
     * @param array $preferences
     * @return bool
     */
    public function updateSearchPreferences(int $userId, array $preferences): bool
    {
        foreach ($preferences as $key => $value) {
            $this->updateSearchPreference($userId, $key, $value);
        }

        return true;
    }

    /**
     * Update individual search preference
     * 
     * @param int $userId
     * @param string $key
     * @param string $value
     * @return bool
     */
    public function updateSearchPreference(int $userId, string $key, string $value): bool
    {
        // Check if preference exists
        $existing = $this->db->fetchOne(
            "SELECT id FROM {$this->searchPreferencesTable} WHERE user_id = :user_id AND preference_key = :key",
            ['user_id' => $userId, 'key' => $key]
        );

        if ($existing) {
            // Update existing
            $query = "UPDATE {$this->searchPreferencesTable} SET preference_value = :value, updated_at = :updated_at 
                      WHERE user_id = :user_id AND preference_key = :key";
        } else {
            // Create new
            $query = "INSERT INTO {$this->searchPreferencesTable} (user_id, preference_key, preference_value, created_at, updated_at) 
                      VALUES (:user_id, :key, :value, :created_at, :updated_at)";
        }

        $this->db->query($query, [
            'user_id' => $userId,
            'key' => $key,
            'value' => $value,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        return true;
    }

    /**
     * Get search preferences
     * 
     * @param int $userId
     * @return array
     */
    public function getSearchPreferences(int $userId): array
    {
        $query = "SELECT * FROM {$this->searchPreferencesTable} WHERE user_id = :user_id";
        $preferences = $this->db->fetchAll($query, ['user_id' => $userId]);
        
        $formatted = [];
        foreach ($preferences as $pref) {
            $formatted[$pref['preference_key']] = $pref['preference_value'];
        }
        
        return $formatted;
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
        // Get most frequent search types
        $searchTypes = $this->db->fetchAll(
            "SELECT search_type, COUNT(*) as count 
             FROM {$this->searchHistoryTable} 
             WHERE user_id = :user_id 
             GROUP BY search_type 
             ORDER BY count DESC",
            ['user_id' => $userId]
        );

        // Get preferred search times
        $searchTimes = $this->db->fetchAll(
            "SELECT HOUR(created_at) as hour, COUNT(*) as count 
             FROM {$this->searchHistoryTable} 
             WHERE user_id = :user_id 
             GROUP BY HOUR(created_at) 
             ORDER BY count DESC 
             LIMIT 5",
            ['user_id' => $userId]
        );

        // Get common filter patterns
        $filterPatterns = $this->db->fetchAll(
            "SELECT filters, COUNT(*) as count 
             FROM {$this->searchHistoryTable} 
             WHERE user_id = :user_id AND filters IS NOT NULL 
             GROUP BY filters 
             ORDER BY count DESC 
             LIMIT 5",
            ['user_id' => $userId]
        );

        // Decode filter patterns
        foreach ($filterPatterns as &$pattern) {
            if ($pattern['filters']) {
                $pattern['filters'] = json_decode($pattern['filters'], true);
            }
        }

        return [
            'search_types' => $searchTypes,
            'preferred_times' => $searchTimes,
            'common_filters' => $filterPatterns,
            'total_searches' => $this->getUserSearchCount($userId)
        ];
    }

    /**
     * Get user search count
     * 
     * @param int $userId
     * @return int
     */
    public function getUserSearchCount(int $userId): int
    {
        return $this->db->fetchColumn(
            "SELECT COUNT(*) FROM {$this->searchHistoryTable} WHERE user_id = :user_id",
            ['user_id' => $userId]
        ) ?: 0;
    }

    /**
     * Get search analytics
     * 
     * @param array $filters
     * @return array
     */
    public function getSearchAnalytics(array $filters = []): array
    {
        $whereConditions = ["1=1"];
        $params = [];

        if (!empty($filters['date_from'])) {
            $whereConditions[] = "created_at >= :date_from";
            $params['date_from'] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $whereConditions[] = "created_at <= :date_to";
            $params['date_to'] = $filters['date_to'];
        }

        $whereClause = implode(' AND ', $whereConditions);

        $totalSearches = $this->db->fetchColumn(
            "SELECT COUNT(*) FROM {$this->searchHistoryTable} WHERE {$whereClause}",
            $params
        ) ?: 0;

        $uniqueUsers = $this->db->fetchColumn(
            "SELECT COUNT(DISTINCT user_id) FROM {$this->searchHistoryTable} WHERE {$whereClause}",
            $params
        ) ?: 0;

        $avgResults = $this->db->fetchColumn(
            "SELECT AVG(result_count) FROM {$this->searchHistoryTable} WHERE {$whereClause} AND result_count > 0",
            $params
        ) ?: 0;

        $searchesByType = $this->db->fetchAll(
            "SELECT search_type, COUNT(*) as count 
             FROM {$this->searchHistoryTable} 
             WHERE {$whereClause}
             GROUP BY search_type 
             ORDER BY count DESC",
            $params
        );

        $searchesByHour = $this->db->fetchAll(
            "SELECT HOUR(created_at) as hour, COUNT(*) as count 
             FROM {$this->searchHistoryTable} 
             WHERE {$whereClause}
             GROUP BY HOUR(created_at) 
             ORDER BY hour ASC",
            $params
        );

        return [
            'total_searches' => (int)$totalSearches,
            'unique_users' => (int)$uniqueUsers,
            'avg_results_per_search' => round($avgResults, 2),
            'searches_by_type' => $searchesByType,
            'searches_by_hour' => $searchesByHour,
            'avg_searches_per_user' => $uniqueUsers > 0 ? round($totalSearches / $uniqueUsers, 2) : 0
        ];
    }

    /**
     * Clean up old search history
     * 
     * @param int $daysToKeep
     * @return bool
     */
    public function cleanupOldSearches(int $daysToKeep = 365): bool
    {
        $cutoffDate = date('Y-m-d H:i:s', strtotime("-{$daysToKeep} days"));
        
        $query = "DELETE FROM {$this->searchHistoryTable} WHERE created_at < :cutoff_date";
        $this->db->query($query, ['cutoff_date' => $cutoffDate]);

        return true;
    }
}
