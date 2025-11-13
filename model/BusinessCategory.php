<?php
/**
 * P.I.M.P - Business Categories and Tags Model
 * Handles business categorization and tagging system
 */

namespace PIMP\Models;

use PIMP\Services\Database\MySQLDatabase;
use PDOException;
use Exception;

class BusinessCategory
{
    /**
     * @var MySQLDatabase Database instance
     */
    private $db;

    /**
     * @var string Categories table name
     */
    private $categoriesTable = 'business_categories';

    /**
     * @var string Tags table name
     */
    private $tagsTable = 'business_tags';

    /**
     * @var string Business-category mapping table
     */
    private $businessCategoriesTable = 'business_category_mapping';

    /**
     * @var string Business-tags mapping table
     */
    private $businessTagsTable = 'business_tags_mapping';

    /**
     * Category status constants
     */
    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';
    const STATUS_PENDING = 'pending';

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
     * Create new category
     * 
     * @param array $categoryData
     * @return array
     * @throws Exception
     */
    public function createCategory(array $categoryData): array
    {
        $requiredFields = ['name', 'slug', 'description'];
        foreach ($requiredFields as $field) {
            if (empty($categoryData[$field])) {
                throw new Exception("Required field missing: {$field}");
            }
        }

        $categoryData['slug'] = $this->generateSlug($categoryData['name']);
        $categoryData['status'] = self::STATUS_ACTIVE;
        $categoryData['created_at'] = date('Y-m-d H:i:s');
        $categoryData['updated_at'] = date('Y-m-d H:i:s');

        $columns = implode(', ', array_keys($categoryData));
        $placeholders = ':' . implode(', :', array_keys($categoryData));
        
        $query = "INSERT INTO {$this->categoriesTable} ({$columns}) VALUES ({$placeholders})";
        $this->db->query($query, $categoryData);

        $categoryId = $this->db->lastInsertId();
        return $this->getCategoryById($categoryId);
    }

    /**
     * Get category by ID
     * 
     * @param int $categoryId
     * @return array|null
     */
    public function getCategoryById(int $categoryId): ?array
    {
        $query = "SELECT * FROM {$this->categoriesTable} WHERE id = :id AND status != 'deleted'";
        return $this->db->fetchOne($query, ['id' => $categoryId]) ?: null;
    }

    /**
     * Get category by slug
     * 
     * @param string $slug
     * @return array|null
     */
    public function getCategoryBySlug(string $slug): ?array
    {
        $query = "SELECT * FROM {$this->categoriesTable} WHERE slug = :slug AND status = :status";
        return $this->db->fetchOne($query, [
            'slug' => $slug,
            'status' => self::STATUS_ACTIVE
        ]) ?: null;
    }

    /**
     * Get all categories
     * 
     * @param array $filters
     * @return array
     */
    public function getAllCategories(array $filters = []): array
    {
        $whereConditions = ["status = 'active'"];
        $params = [];

        if (!empty($filters['parent_id'])) {
            $whereConditions[] = "parent_id = :parent_id";
            $params['parent_id'] = $filters['parent_id'];
        }

        if (!empty($filters['featured'])) {
            $whereConditions[] = "is_featured = :featured";
            $params['featured'] = 1;
        }

        $whereClause = implode(' AND ', $whereConditions);
        $query = "SELECT * FROM {$this->categoriesTable} WHERE {$whereClause} ORDER BY display_order ASC, name ASC";
        
        return $this->db->fetchAll($query, $params);
    }

    /**
     * Add category to business
     * 
     * @param int $businessId
     * @param int $categoryId
     * @return bool
     * @throws Exception
     */
    public function addCategoryToBusiness(int $businessId, int $categoryId): bool
    {
        // Check if category exists
        $category = $this->getCategoryById($categoryId);
        if (!$category) {
            throw new Exception("Category not found");
        }

        // Check if already assigned
        $existing = $this->db->fetchOne(
            "SELECT id FROM {$this->businessCategoriesTable} WHERE business_id = :business_id AND category_id = :category_id",
            ['business_id' => $businessId, 'category_id' => $categoryId]
        );

        if ($existing) {
            throw new Exception("Category already assigned to this business");
        }

        $query = "INSERT INTO {$this->businessCategoriesTable} (business_id, category_id, created_at) VALUES (:business_id, :category_id, :created_at)";
        $this->db->query($query, [
            'business_id' => $businessId,
            'category_id' => $categoryId,
            'created_at' => date('Y-m-d H:i:s')
        ]);

        return true;
    }

    /**
     * Remove category from business
     * 
     * @param int $businessId
     * @param int $categoryId
     * @return bool
     */
    public function removeCategoryFromBusiness(int $businessId, int $categoryId): bool
    {
        $query = "DELETE FROM {$this->businessCategoriesTable} WHERE business_id = :business_id AND category_id = :category_id";
        $this->db->query($query, [
            'business_id' => $businessId,
            'category_id' => $categoryId
        ]);

        return true;
    }

    /**
     * Get business categories
     * 
     * @param int $businessId
     * @return array
     */
    public function getBusinessCategories(int $businessId): array
    {
        $query = "SELECT c.* FROM {$this->categoriesTable} c
                  INNER JOIN {$this->businessCategoriesTable} bcm ON c.id = bcm.category_id
                  WHERE bcm.business_id = :business_id AND c.status = :status
                  ORDER BY c.display_order ASC, c.name ASC";
        
        return $this->db->fetchAll($query, [
            'business_id' => $businessId,
            'status' => self::STATUS_ACTIVE
        ]);
    }

    /**
     * Create new tag
     * 
     * @param string $tagName
     * @return array
     * @throws Exception
     */
    public function createTag(string $tagName): array
    {
        $slug = $this->generateSlug($tagName);

        // Check if tag already exists
        $existing = $this->getTagBySlug($slug);
        if ($existing) {
            return $existing;
        }

        $tagData = [
            'name' => $tagName,
            'slug' => $slug,
            'status' => self::STATUS_ACTIVE,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $columns = implode(', ', array_keys($tagData));
        $placeholders = ':' . implode(', :', array_keys($tagData));
        
        $query = "INSERT INTO {$this->tagsTable} ({$columns}) VALUES ({$placeholders})";
        $this->db->query($query, $tagData);

        $tagId = $this->db->lastInsertId();
        return $this->getTagById($tagId);
    }

    /**
     * Get tag by ID
     * 
     * @param int $tagId
     * @return array|null
     */
    public function getTagById(int $tagId): ?array
    {
        $query = "SELECT * FROM {$this->tagsTable} WHERE id = :id AND status = :status";
        return $this->db->fetchOne($query, [
            'id' => $tagId,
            'status' => self::STATUS_ACTIVE
        ]) ?: null;
    }

    /**
     * Get tag by slug
     * 
     * @param string $slug
     * @return array|null
     */
    public function getTagBySlug(string $slug): ?array
    {
        $query = "SELECT * FROM {$this->tagsTable} WHERE slug = :slug AND status = :status";
        return $this->db->fetchOne($query, [
            'slug' => $slug,
            'status' => self::STATUS_ACTIVE
        ]) ?: null;
    }

    /**
     * Add tag to business
     * 
     * @param int $businessId
     * @param int $tagId
     * @return bool
     * @throws Exception
     */
    public function addTagToBusiness(int $businessId, int $tagId): bool
    {
        $tag = $this->getTagById($tagId);
        if (!$tag) {
            throw new Exception("Tag not found");
        }

        // Check if already assigned
        $existing = $this->db->fetchOne(
            "SELECT id FROM {$this->businessTagsTable} WHERE business_id = :business_id AND tag_id = :tag_id",
            ['business_id' => $businessId, 'tag_id' => $tagId]
        );

        if ($existing) {
            throw new Exception("Tag already assigned to this business");
        }

        $query = "INSERT INTO {$this->businessTagsTable} (business_id, tag_id, created_at) VALUES (:business_id, :tag_id, :created_at)";
        $this->db->query($query, [
            'business_id' => $businessId,
            'tag_id' => $tagId,
            'created_at' => date('Y-m-d H:i:s')
        ]);

        return true;
    }

    /**
     * Remove tag from business
     * 
     * @param int $businessId
     * @param int $tagId
     * @return bool
     */
    public function removeTagFromBusiness(int $businessId, int $tagId): bool
    {
        $query = "DELETE FROM {$this->businessTagsTable} WHERE business_id = :business_id AND tag_id = :tag_id";
        $this->db->query($query, [
            'business_id' => $businessId,
            'tag_id' => $tagId
        ]);

        return true;
    }

    /**
     * Get business tags
     * 
     * @param int $businessId
     * @return array
     */
    public function getBusinessTags(int $businessId): array
    {
        $query = "SELECT t.* FROM {$this->tagsTable} t
                  INNER JOIN {$this->businessTagsTable} btm ON t.id = btm.tag_id
                  WHERE btm.business_id = :business_id AND t.status = :status
                  ORDER BY t.name ASC";
        
        return $this->db->fetchAll($query, [
            'business_id' => $businessId,
            'status' => self::STATUS_ACTIVE
        ]);
    }

    /**
     * Search categories
     * 
     * @param string $query
     * @param int $limit
     * @return array
     */
    public function searchCategories(string $query, int $limit = 10): array
    {
        $searchQuery = "SELECT * FROM {$this->categoriesTable} 
                       WHERE (name LIKE :query OR description LIKE :query) 
                       AND status = :status 
                       ORDER BY name ASC 
                       LIMIT :limit";
        
        return $this->db->fetchAll($searchQuery, [
            'query' => '%' . $query . '%',
            'status' => self::STATUS_ACTIVE,
            'limit' => $limit
        ]);
    }

    /**
     * Search tags
     * 
     * @param string $query
     * @param int $limit
     * @return array
     */
    public function searchTags(string $query, int $limit = 10): array
    {
        $searchQuery = "SELECT * FROM {$this->tagsTable} 
                       WHERE name LIKE :query 
                       AND status = :status 
                       ORDER BY name ASC 
                       LIMIT :limit";
        
        return $this->db->fetchAll($searchQuery, [
            'query' => '%' . $query . '%',
            'status' => self::STATUS_ACTIVE,
            'limit' => $limit
        ]);
    }

    /**
     * Generate URL slug
     * 
     * @param string $text
     * @return string
     */
    private function generateSlug(string $text): string
    {
        $slug = strtolower(trim($text));
        $slug = preg_replace('/[^a-z0-9-]/', '-', $slug);
        $slug = preg_replace('/-+/', '-', $slug);
        return trim($slug, '-');
    }

    /**
     * Get category statistics
     * 
     * @param int $categoryId
     * @return array
     */
    public function getCategoryStatistics(int $categoryId): array
    {
        $totalBusinesses = $this->db->fetchColumn(
            "SELECT COUNT(*) FROM {$this->businessCategoriesTable} WHERE category_id = :category_id",
            ['category_id' => $categoryId]
        );

        $averageRating = $this->db->fetchColumn(
            "SELECT AVG(b.rating) FROM business_profiles b
             INNER JOIN {$this->businessCategoriesTable} bcm ON b.id = bcm.business_id
             WHERE bcm.category_id = :category_id AND b.rating > 0",
            ['category_id' => $categoryId]
        ) ?: 0;

        return [
            'total_businesses' => (int)$totalBusinesses,
            'average_rating' => round((float)$averageRating, 2),
            'total_reviews' => 0, // Would be calculated from reviews model
            'total_complaints' => 0 // Would be calculated from complaints model
        ];
    }
}
