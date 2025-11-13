<?php
/**
 * P.I.M.P - Sitemap Model
 * Handles sitemap data retrieval
 */

namespace PIMP\Models;

use PIMP\Services\Database\MySQLDatabase;

class SitemapModel
{
    private $db;

    public function __construct(MySQLDatabase $db)
    {
        $this->db = $db;
    }

    /**
     * Get main pages for sitemap
     * 
     * @return array
     */
    public function getMainPages(): array
    {
        return [
            [
                'url' => '/',
                'lastmod' => date('Y-m-d'),
                'changefreq' => 'daily',
                'priority' => '1.0',
                'title' => 'Home'
            ],
            [
                'url' => '/businesses',
                'lastmod' => date('Y-m-d'),
                'changefreq' => 'daily',
                'priority' => '0.9',
                'title' => 'Business Directory'
            ],
            [
                'url' => '/reviews',
                'lastmod' => date('Y-m-d'),
                'changefreq' => 'daily',
                'priority' => '0.8',
                'title' => 'Reviews'
            ],
            [
                'url' => '/categories',
                'lastmod' => date('Y-m-d'),
                'changefreq' => 'weekly',
                'priority' => '0.8',
                'title' => 'Categories'
            ],
            [
                'url' => '/scam-alerts',
                'lastmod' => date('Y-m-d'),
                'changefreq' => 'daily',
                'priority' => '0.7',
                'title' => 'Scam Alerts'
            ],
            [
                'url' => '/resources',
                'lastmod' => date('Y-m-d'),
                'changefreq' => 'weekly',
                'priority' => '0.6',
                'title' => 'Resources'
            ],
            [
                'url' => '/about',
                'lastmod' => date('Y-m-d'),
                'changefreq' => 'monthly',
                'priority' => '0.5',
                'title' => 'About Us'
            ],
            [
                'url' => '/contact',
                'lastmod' => date('Y-m-d'),
                'changefreq' => 'monthly',
                'priority' => '0.5',
                'title' => 'Contact'
            ],
            [
                'url' => '/faq',
                'lastmod' => date('Y-m-d'),
                'changefreq' => 'monthly',
                'priority' => '0.4',
                'title' => 'FAQ'
            ],
            [
                'url' => '/terms',
                'lastmod' => date('Y-m-d'),
                'changefreq' => 'monthly',
                'priority' => '0.3',
                'title' => 'Terms of Service'
            ],
            [
                'url' => '/privacy',
                'lastmod' => date('Y-m-d'),
                'changefreq' => 'monthly',
                'priority' => '0.3',
                'title' => 'Privacy Policy'
            ],
            [
                'url' => '/sitemap',
                'lastmod' => date('Y-m-d'),
                'changefreq' => 'daily',
                'priority' => '0.3',
                'title' => 'HTML Sitemap'
            ]
        ];
    }

    /**
     * Get businesses for sitemap
     * 
     * @param int $page Page number (1-based)
     * @param int $perPage Items per page
     * @return array
     */
    public function getBusinesses(int $page = 1, int $perPage = 5000): array
    {
        $offset = ($page - 1) * $perPage;

        return $this->db->fetchAll(
            "SELECT uuid, updated_at, created_at, legal_name, trading_name
             FROM businesses
             WHERE status IN ('active', 'verified') AND deleted_at IS NULL
             ORDER BY updated_at DESC, created_at DESC
             LIMIT ? OFFSET ?",
            [$perPage, $offset]
        );
    }

    /**
     * Get recent businesses for HTML sitemap
     * 
     * @param int $limit
     * @return array
     */
    public function getRecentBusinesses(int $limit = 50): array
    {
        return $this->db->fetchAll(
            "SELECT uuid, legal_name, trading_name, category_id, average_rating, created_at
             FROM businesses
             WHERE status IN ('active', 'verified') AND deleted_at IS NULL
             ORDER BY created_at DESC
             LIMIT ?",
            [$limit]
        );
    }

    /**
     * Get reviews for sitemap
     * 
     * @param int $page Page number (1-based)
     * @param int $perPage Items per page
     * @return array
     */
    public function getReviews(int $page = 1, int $perPage = 5000): array
    {
        $offset = ($page - 1) * $perPage;

        return $this->db->fetchAll(
            "SELECT uuid, updated_at, review_date, title
             FROM business_reviews
             WHERE status = 'approved'
             ORDER BY updated_at DESC, review_date DESC
             LIMIT ? OFFSET ?",
            [$perPage, $offset]
        );
    }

    /**
     * Get recent reviews for HTML sitemap
     * 
     * @param int $limit
     * @return array
     */
    public function getRecentReviews(int $limit = 20): array
    {
        return $this->db->fetchAll(
            "SELECT br.uuid, br.title, br.rating, br.review_date,
                    b.legal_name as business_name, b.uuid as business_uuid
             FROM business_reviews br
             JOIN businesses b ON br.business_id = b.id
             WHERE br.status = 'approved'
             ORDER BY br.review_date DESC
             LIMIT ?",
            [$limit]
        );
    }

    /**
     * Get categories for sitemap
     * 
     * @return array
     */
    public function getCategories(): array
    {
        return $this->db->fetchAll(
            "SELECT uuid, name, description, updated_at, parent_id, depth
             FROM business_categories
             WHERE is_active = 1
             ORDER BY depth ASC, sort_order ASC, name ASC",
            []
        );
    }

    /**
     * Get categories hierarchically for HTML sitemap
     * 
     * @return array
     */
    public function getCategoriesHierarchical(): array
    {
        $categories = $this->getCategories();
        return $this->buildCategoryTree($categories);
    }

    /**
     * Build category tree from flat array
     * 
     * @param array $categories
     * @param int|null $parentId
     * @return array
     */
    private function buildCategoryTree(array $categories, ?int $parentId = null): array
    {
        $tree = [];

        foreach ($categories as $category) {
            if ($category['parent_id'] == $parentId) {
                $children = $this->buildCategoryTree($categories, $category['id']);
                if ($children) {
                    $category['children'] = $children;
                }
                $tree[] = $category;
            }
        }

        return $tree;
    }

    /**
     * Get total number of businesses
     * 
     * @return int
     */
    public function getTotalBusinesses(): int
    {
        return (int)$this->db->fetchColumn(
            "SELECT COUNT(*) FROM businesses WHERE status IN ('active', 'verified') AND deleted_at IS NULL",
            []
        );
    }

    /**
     * Get total number of reviews
     * 
     * @return int
     */
    public function getTotalReviews(): int
    {
        return (int)$this->db->fetchColumn(
            "SELECT COUNT(*) FROM business_reviews WHERE status = 'approved'",
            []
        );
    }

    /**
     * Get total number of categories
     * 
     * @return int
     */
    public function getTotalCategories(): int
    {
        return (int)$this->db->fetchColumn(
            "SELECT COUNT(*) FROM business_categories WHERE is_active = 1",
            []
        );
    }

    /**
     * Get sitemap statistics
     * 
     * @return array
     */
    public function getStatistics(): array
    {
        return [
            'total_businesses' => $this->getTotalBusinesses(),
            'total_reviews' => $this->getTotalReviews(),
            'total_categories' => $this->getTotalCategories(),
            'last_business_update' => $this->db->fetchColumn(
                "SELECT MAX(updated_at) FROM businesses WHERE deleted_at IS NULL",
                []
            ),
            'last_review_update' => $this->db->fetchColumn(
                "SELECT MAX(updated_at) FROM business_reviews",
                []
            )
        ];
    }
}
