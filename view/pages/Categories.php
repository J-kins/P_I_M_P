<?php
/**
 * P.I.M.P - Categories Page
 * Browse all business categories
 */

use PIMP\Core\Config;
use PIMP\Views\Components;
use PIMP\Services\DatabaseFactory;
use PIMP\Services\API\BusinessCategoryAPIService;
use PIMP\Services\API\BusinessAPIService;

// Initialize database connection
$db = null;
$categoryAPI = null;
$businessAPI = null;

try {
    $db = DatabaseFactory::default();
    if ($db) {
        try {
            $categoryAPI = new BusinessCategoryAPIService($db);
            $businessAPI = new BusinessAPIService($db);
        } catch (\Exception $e) {
            error_log("API service initialization error: " . $e->getMessage());
        }
    }
} catch (\Exception $e) {
    error_log("Database connection error: " . $e->getMessage());
    $db = null;
}

// Fetch categories
$categories = [];
$selected_category = $_GET['category'] ?? null;

try {
    if ($categoryAPI) {
        $categoriesResponse = $categoryAPI->getAllCategories([]);
        if ($categoriesResponse['success'] && !empty($categoriesResponse['data'])) {
            $allCategories = $categoriesResponse['data'];
            
            // Get business count for each category
            foreach ($allCategories as $cat) {
                $businessCount = 0;
                if ($businessAPI && isset($cat['id'])) {
                    try {
                        $businessCountResponse = $businessAPI->searchBusinesses([
                            'category_id' => $cat['id'],
                            'status' => 'active'
                        ], 1, 1);
                        
                        if ($businessCountResponse['success'] && isset($businessCountResponse['data']['pagination'])) {
                            $businessCount = $businessCountResponse['data']['pagination']['total'] ?? 0;
                        }
                    } catch (\Exception $e) {
                        error_log("Error getting business count: " . $e->getMessage());
                    }
                }
                
                $categories[] = [
                    'id' => $cat['id'] ?? null,
                    'name' => $cat['name'] ?? 'Unnamed Category',
                    'slug' => $cat['slug'] ?? '',
                    'description' => $cat['description'] ?? '',
                    'count' => $businessCount,
                    'icon' => $cat['icon'] ?? 'fa-building'
                ];
            }
        }
    }
} catch (\Exception $e) {
    error_log("Error fetching categories: " . $e->getMessage());
}

$nav_items = [
    ['url' => '/', 'label' => 'Home', 'active' => false],
    ['url' => '/businesses', 'label' => 'Business Directory', 'active' => false],
    ['url' => '/reviews', 'label' => 'Reviews', 'active' => false],
    ['url' => '/categories', 'label' => 'Categories', 'active' => true],
    ['url' => '/scam-alerts', 'label' => 'Scam Alerts', 'active' => false],
    ['url' => '/resources', 'label' => 'Resources', 'active' => false],
];

$footer_config = [
    'logo' => Config::imageUrl('logo.png'),
    'logoAlt' => 'P.I.M.P Business Repository',
    'links' => [
        [
            'title' => 'For Consumers',
            'links' => [
                ['url' => '/businesses', 'label' => 'Find Businesses'],
                ['url' => '/reviews/write', 'label' => 'Write a Review'],
                ['url' => '/scam-alerts', 'label' => 'Scam Alerts'],
                ['url' => '/resources/tips', 'label' => 'Consumer Tips'],
            ]
        ],
        [
            'title' => 'For Businesses',
            'links' => [
                ['url' => '/business/claim', 'label' => 'Claim Your Business'],
                ['url' => '/business/advertise', 'label' => 'Advertise With Us'],
                ['url' => '/business/resources', 'label' => 'Business Resources'],
                ['url' => '/for-business', 'label' => 'For Business Home'],
            ]
        ],
        [
            'title' => 'Company',
            'links' => [
                ['url' => '/about', 'label' => 'About Us'],
                ['url' => '/news', 'label' => 'News & Updates'],
                ['url' => '/careers', 'label' => 'Careers'],
                ['url' => '/contact', 'label' => 'Contact Us'],
            ]
        ]
    ],
    'theme' => 'light'
];

ob_start();
?>

<?php
echo Components::call('Headers', 'documentHead', [[
    'title' => 'Business Categories - P.I.M.P Business Repository',
    'metaTags' => [
        'description' => 'Browse businesses by category. Find restaurants, retail stores, services, and more in our comprehensive business directory.',
        'keywords' => 'business categories, browse businesses, business directory, categories',
        'author' => 'P.I.M.P Business Repository'
    ],
    'canonical' => Config::url('/categories'),
    'styles' => [
        'views/categories.css'
    ],
    'scripts' => [
        'static/js/categories.js'
    ]
]]);
?>

<body>
    <?php
    echo Components::call('Headers', 'businessHeader', [[
        'logo' => Config::imageUrl('logo.png'),
        'logoAlt' => 'P.I.M.P - Business Repository Platform',
        'mainNavItems' => $nav_items,
        'userActions' => [
            ['url' => '/login', 'label' => 'Log In'],
            ['url' => '/register', 'label' => 'Register', 'separator' => true],
            ['url' => '/for-business', 'label' => 'For Business'],
        ],
        'showSearch' => true,
    ]]);
    ?>

    <!-- Hero Section -->
    <?php
    echo Components::call('Headers', 'heroHeader', [[
        'title' => 'Browse by Category',
        'subtitle' => 'Find businesses organized by category',
        'bgImage' => Config::imageUrl('hero-bg.jpg'),
        'overlay' => 'dark',
        'size' => 'md',
        'align' => 'center'
    ]]);
    ?>

    <main class="main-content">
        <div class="categories-page">
            <div class="container">
                <!-- Search/Filter Bar -->
                <div class="categories-filter">
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" id="categorySearch" placeholder="Search categories...">
                    </div>
                </div>

                <!-- Categories Grid -->
                <?php if (!empty($categories)): ?>
                <div class="categories-grid" id="categoriesGrid">
                    <?php foreach ($categories as $category): ?>
                    <a href="<?= Config::url('/category/' . $category['slug']) ?>" class="category-card">
                        <div class="category-icon">
                            <i class="fas <?= htmlspecialchars($category['icon']) ?>"></i>
                        </div>
                        <div class="category-info">
                            <h3><?= htmlspecialchars($category['name']) ?></h3>
                            <?php if (!empty($category['description'])): ?>
                            <p class="category-description"><?= htmlspecialchars($category['description']) ?></p>
                            <?php endif; ?>
                            <div class="category-meta">
                                <span class="business-count">
                                    <i class="fas fa-building"></i>
                                    <?= number_format($category['count']) ?> businesses
                                </span>
                            </div>
                        </div>
                        <div class="category-arrow">
                            <i class="fas fa-chevron-right"></i>
                        </div>
                    </a>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <div class="no-categories">
                    <i class="fas fa-folder-open"></i>
                    <h3>No Categories Available</h3>
                    <p>Categories will appear here once they are added to the system.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <?php
    echo Components::call('Footers', 'businessFooter', [$footer_config]);
    ?>

    <?php
    echo Components::call('Footers', 'documentClose', [[
        'includeMainJs' => true
    ]]);
    ?>
</body>
</html>

<?php
echo ob_get_clean();
?>

