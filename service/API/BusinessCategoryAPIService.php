<?php
/**
 * P.I.M.P - Business Categories API Service
 * Handles business categories and tags API operations
 */

namespace PIMP\Services\API;

use PIMP\Models\BusinessCategory;
use PIMP\Services\Database\MySQLDatabase;
use Exception;

class BusinessCategoryAPIService
{
    /**
     * @var BusinessCategory
     */
    private $categoryModel;

    /**
     * Constructor
     * 
     * @param MySQLDatabase $db
     */
    public function __construct(MySQLDatabase $db)
    {
        $this->categoryModel = new BusinessCategory($db);
    }

    /**
     * Create new category
     * 
     * @param array $data
     * @return array
     */
    public function createCategory(array $data): array
    {
        try {
            $category = $this->categoryModel->createCategory($data);
            
            return [
                'success' => true,
                'message' => 'Category created successfully',
                'data' => $category
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
     * Get all categories
     * 
     * @param array $filters
     * @return array
     */
    public function getAllCategories(array $filters = []): array
    {
        try {
            $categories = $this->categoryModel->getAllCategories($filters);

            return [
                'success' => true,
                'message' => 'Categories retrieved successfully',
                'data' => $categories
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
     * Get category by slug
     * 
     * @param string $slug
     * @return array
     */
    public function getCategoryBySlug(string $slug): array
    {
        try {
            $category = $this->categoryModel->getCategoryBySlug($slug);
            
            if (!$category) {
                throw new Exception("Category not found");
            }

            return [
                'success' => true,
                'message' => 'Category retrieved successfully',
                'data' => $category
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
     * Add category to business
     * 
     * @param int $businessId
     * @param int $categoryId
     * @return array
     */
    public function addCategoryToBusiness(int $businessId, int $categoryId): array
    {
        try {
            $this->categoryModel->addCategoryToBusiness($businessId, $categoryId);

            return [
                'success' => true,
                'message' => 'Category added to business successfully',
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
     * Remove category from business
     * 
     * @param int $businessId
     * @param int $categoryId
     * @return array
     */
    public function removeCategoryFromBusiness(int $businessId, int $categoryId): array
    {
        try {
            $this->categoryModel->removeCategoryFromBusiness($businessId, $categoryId);

            return [
                'success' => true,
                'message' => 'Category removed from business successfully',
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
     * Create new tag
     * 
     * @param string $tagName
     * @return array
     */
    public function createTag(string $tagName): array
    {
        try {
            $tag = $this->categoryModel->createTag($tagName);

            return [
                'success' => true,
                'message' => 'Tag created successfully',
                'data' => $tag
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
     * Add tag to business
     * 
     * @param int $businessId
     * @param int $tagId
     * @return array
     */
    public function addTagToBusiness(int $businessId, int $tagId): array
    {
        try {
            $this->categoryModel->addTagToBusiness($businessId, $tagId);

            return [
                'success' => true,
                'message' => 'Tag added to business successfully',
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
     * Search categories
     * 
     * @param string $query
     * @param int $limit
     * @return array
     */
    public function searchCategories(string $query, int $limit = 10): array
    {
        try {
            $categories = $this->categoryModel->searchCategories($query, $limit);

            return [
                'success' => true,
                'message' => 'Categories search completed',
                'data' => $categories
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
     * Search tags
     * 
     * @param string $query
     * @param int $limit
     * @return array
     */
    public function searchTags(string $query, int $limit = 10): array
    {
        try {
            $tags = $this->categoryModel->searchTags($query, $limit);

            return [
                'success' => true,
                'message' => 'Tags search completed',
                'data' => $tags
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
     * Get category statistics
     * 
     * @param int $categoryId
     * @return array
     */
    public function getCategoryStatistics(int $categoryId): array
    {
        try {
            $statistics = $this->categoryModel->getCategoryStatistics($categoryId);

            return [
                'success' => true,
                'message' => 'Category statistics retrieved',
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
