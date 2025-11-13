<?php
/**
 * P.I.M.P - Homepage
 * Business Repository Platform Landing Page
 * Refactored to use APIs and dynamic database data
 */

use PIMP\Core\Config;
use PIMP\Views\Components;
use PIMP\Services\DatabaseFactory;
use PIMP\Services\API\BusinessAPIService;
use PIMP\Services\API\BusinessCategoryAPIService;

// Initialize database connection
$db = null;
$businessAPI = null;
$categoryAPI = null;

try {
    $db = DatabaseFactory::default();
    if ($db) {
        try {
            $businessAPI = new BusinessAPIService($db);
            $categoryAPI = new BusinessCategoryAPIService($db);
        } catch (\Exception $e) {
            error_log("API service initialization error: " . $e->getMessage());
            // Continue without API services - page will show empty data
        }
    }
} catch (\Exception $e) {
    error_log("Database connection error: " . $e->getMessage());
    // Continue without database - page will show empty data
    $db = null;
}

// Fetch data from database
$categories = [];
$featured_businesses = [];
$statistics = [
    'total_businesses' => 0,
    'total_reviews' => 0,
    'total_cities' => 0,
    'satisfaction_rate' => 99
];

try {
    // Get popular categories with business counts
    if ($categoryAPI) {
        $categoriesResponse = $categoryAPI->getAllCategories(['featured' => true]);
        if ($categoriesResponse['success'] && !empty($categoriesResponse['data'])) {
            $allCategories = $categoriesResponse['data'];
            
            // Get top 6 categories with business counts
            foreach (array_slice($allCategories, 0, 6) as $cat) {
                // Get business count for this category
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
                    } catch (Exception $e) {
                        error_log("Error getting business count for category: " . $e->getMessage());
                    }
                }
            
            // Map category icon based on name
            $iconMap = [
                'restaurant' => 'fa-utensils',
                'dining' => 'fa-utensils',
                'retail' => 'fa-shopping-cart',
                'shopping' => 'fa-shopping-cart',
                'home' => 'fa-home',
                'healthcare' => 'fa-heartbeat',
                'health' => 'fa-heartbeat',
                'automotive' => 'fa-car',
                'auto' => 'fa-car',
                'professional' => 'fa-briefcase',
                'service' => 'fa-briefcase'
            ];
            
            $icon = 'fa-building';
            foreach ($iconMap as $keyword => $iconClass) {
                if (stripos($cat['name'] ?? '', $keyword) !== false) {
                    $icon = $iconClass;
                    break;
                }
            }
            
            $categories[] = [
                'id' => $cat['id'] ?? null,
                'name' => $cat['name'] ?? 'Unnamed Category',
                'slug' => $cat['slug'] ?? '',
                'url' => '/category/' . ($cat['slug'] ?? ''),
                'count' => $businessCount,
                'icon' => '<i class="fas ' . $icon . '"></i>',
                'description' => $cat['description'] ?? 'Browse businesses in this category'
            ];
            }
        }
    }
    
    // Get featured businesses (high rating, accredited, active)
    if ($businessAPI) {
        try {
            $featuredResponse = $businessAPI->getFeaturedBusinesses(3);
            
            if ($featuredResponse['success'] && !empty($featuredResponse['data']['businesses'])) {
                $businesses = $featuredResponse['data']['businesses'];
                
                foreach ($businesses as $business) {
                    // Get business categories
                    $businessCategories = [];
                    if (isset($business['category_id']) && $categoryAPI) {
                        try {
                            $catResponse = $categoryAPI->getCategoryBySlug($business['category_id']);
                            if ($catResponse['success']) {
                                $businessCategories[] = $catResponse['data']['name'] ?? '';
                            }
                        } catch (\Exception $e) {
                            error_log("Error getting category: " . $e->getMessage());
                        }
                    }
            
                    $featured_businesses[] = [
                        'id' => $business['id'] ?? null,
                        'business_id' => $business['business_id'] ?? '',
                        'name' => $business['business_name'] ?? 'Unnamed Business',
                        'rating' => number_format((float)($business['rating'] ?? 0), 1),
                        'accredited' => in_array($business['accreditation_level'] ?? 'none', ['premium', 'verified']),
                        'address' => trim(($business['address'] ?? '') . ', ' . ($business['city'] ?? '') . ', ' . ($business['state'] ?? '')),
                        'phone' => $business['phone'] ?? '',
                        'website' => $business['website'] ?? '',
                        'reviews' => (int)($business['total_reviews'] ?? 0),
                        'categories' => $businessCategories,
                        'image' => !empty($business['logo_url']) ? $business['logo_url'] : Config::imageUrl('businesses/default.jpg')
                    ];
                }
            }
        } catch (\Exception $e) {
            error_log("Error getting featured businesses: " . $e->getMessage());
        }
    }
    
    // Get statistics
    if ($db) {
        try {
            $statsQuery = "
                SELECT 
                    (SELECT COUNT(*) FROM business_profiles WHERE status = 'active') as total_businesses,
                    (SELECT COUNT(*) FROM business_reviews WHERE status = 'approved') as total_reviews,
                    (SELECT COUNT(DISTINCT city) FROM business_profiles WHERE status = 'active' AND city IS NOT NULL) as total_cities
            ";
            $statsResult = $db->fetchOne($statsQuery);
            
            if ($statsResult) {
                $statistics['total_businesses'] = (int)($statsResult['total_businesses'] ?? 0);
                $statistics['total_reviews'] = (int)($statsResult['total_reviews'] ?? 0);
                $statistics['total_cities'] = (int)($statsResult['total_cities'] ?? 0);
            }
        } catch (\Exception $e) {
            error_log("Error getting statistics: " . $e->getMessage());
        }
    }
    
} catch (\Exception $e) {
    // Log error but don't break the page
    error_log("Home page data fetch error: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    
    // Use fallback empty data
    if (empty($categories)) {
        $categories = [];
    }
    if (empty($featured_businesses)) {
        $featured_businesses = [];
    }
} catch (\Throwable $e) {
    // Catch any other errors (PHP 7+)
    error_log("Home page fatal error: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    
    // Use fallback empty data
    if (empty($categories)) {
        $categories = [];
    }
    if (empty($featured_businesses)) {
        $featured_businesses = [];
    }
}

// Navigation items
$nav_items = [
    ['url' => '/', 'label' => 'Home', 'active' => true],
    ['url' => '/businesses', 'label' => 'Business Directory', 'active' => false],
    ['url' => '/reviews', 'label' => 'Reviews', 'active' => false],
    ['url' => '/categories', 'label' => 'Categories', 'active' => false],
    ['url' => '/scam-alerts', 'label' => 'Scam Alerts', 'active' => false],
    ['url' => '/resources', 'label' => 'Resources', 'active' => false],
];

// Footer configuration
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
    'social' => [
        [
            'platform' => 'facebook',
            'url' => 'https://facebook.com/pimpbusiness',
            'icon' => '<i class="fab fa-facebook-f"></i>',
            'name' => 'Facebook',
            'newTab' => true
        ],
        [
            'platform' => 'twitter',
            'url' => 'https://twitter.com/pimpbusiness',
            'icon' => '<i class="fab fa-twitter"></i>',
            'name' => 'Twitter',
            'newTab' => true
        ],
        [
            'platform' => 'linkedin',
            'url' => 'https://linkedin.com/company/pimp-business',
            'icon' => '<i class="fab fa-linkedin-in"></i>',
            'name' => 'LinkedIn',
            'newTab' => true
        ],
        [
            'platform' => 'instagram',
            'url' => 'https://instagram.com/pimpbusiness',
            'icon' => '<i class="fab fa-instagram"></i>',
            'name' => 'Instagram',
            'newTab' => true
        ]
    ],
    'contact' => [
        ['label' => 'Phone', 'value' => '1-800-PIMP-HELP'],
        ['label' => 'Email', 'value' => 'support@pimp-business.com'],
        ['label' => 'Address', 'value' => '123 Business Ave, Suite 100, New York, NY 10001']
    ],
    'theme' => 'light'
];

// Start output
ob_start();
?>

<?php
// Document head
echo Components::call('Headers', 'documentHead', [[
    'title' => 'P.I.M.P - Trusted Business Directory & Reviews',
    'metaTags' => [
        'description' => 'Find trusted local businesses, read genuine customer reviews, and make informed decisions with P.I.M.P Business Repository.',
        'keywords' => 'business directory, reviews, local businesses, ratings, PIMP, trusted businesses',
        'author' => 'P.I.M.P Business Repository',
        'robots' => 'index, follow'
    ],
    'canonical' => Config::url('/'),
    'styles' => [
        'views/landing-page.css'
    ],
    'scripts' => [
        'static/js/main.js',
        'static/js/homepage.js'
    ],
    'includeFontAwesome' => true,
    'includeJQuery' => true
]]);
?>

<body>
    <?php
    // Business Header
    echo Components::call('Headers', 'businessHeader', [[
        'logo' => Config::imageUrl('logo.png'),
        'logoAlt' => 'P.I.M.P - Business Repository Platform',
        'topBarItems' => [
            ['url' => '/about', 'label' => 'About PIMP'],
            ['url' => '/news', 'label' => 'News & Updates'],
            ['url' => '/careers', 'label' => 'Careers'],
            ['url' => '/contact', 'label' => 'Contact Us'],
        ],
        'mainNavItems' => $nav_items,
        'userActions' => [
            ['url' => '/login', 'label' => 'Log In'],
            ['url' => '/register', 'label' => 'Register', 'separator' => true],
            ['url' => '/for-business', 'label' => 'For Business'],
        ],
        'searchPlaceholder' => 'Search for businesses, services, or categories...',
        'phoneNumber' => '1-800-PIMP-HELP',
        'ctaText' => 'Submit a Review',
        'ctaUrl' => '/reviews/write',
        'showSearch' => true,
        'showPhone' => true,
    ]]);
    ?>

    <!-- Hero Section -->
    <?php
    echo Components::call('Headers', 'heroHeader', [[
        'title' => 'Find Trusted Businesses in Your Area',
        'subtitle' => 'Read genuine customer reviews, compare businesses, and make informed decisions with P.I.M.P Business Repository.',
        'bgImage' => Config::imageUrl('hero-bg.jpg'),
        'overlay' => 'dark',
        'size' => 'lg',
        'align' => 'center',
        'actions' => [
            [
                'url' => '/businesses/search',
                'label' => 'Search Businesses',
                'class' => 'button-primary'
            ],
            [
                'url' => '/reviews/write',
                'label' => 'Write a Review',
                'class' => 'button-secondary'
            ]
        ]
    ]]);
    ?>

    <main class="main-content">
        <!-- Categories Section -->
        <section class="categories-section" data-section="categories">
            <div class="container">
                <?php
                echo Components::call('Headers', 'pageHeader', [
                    'Popular Business Categories',
                    'Browse businesses by category to find exactly what you\'re looking for',
                    'text-center'
                ]);
                ?>
                
                <div class="categories-grid" id="categoriesGrid">
                    <?php if (!empty($categories)): ?>
                        <?php foreach ($categories as $category): ?>
                        <div class="category-card" data-category-id="<?= htmlspecialchars($category['id'] ?? '') ?>">
                            <div class="category-icon">
                                <?= $category['icon'] ?>
                            </div>
                            <h3><?= htmlspecialchars($category['name']) ?></h3>
                            <p><?= htmlspecialchars($category['description']) ?></p>
                            <div class="category-meta">
                                <span class="business-count">
                                    <i class="fas fa-building"></i>
                                    <span class="count-value"><?= number_format($category['count']) ?></span> businesses
                                </span>
                            </div>
                            <a href="<?= Config::url($category['url']) ?>" class="button button-outline">
                                Browse Category
                                <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="loading-categories">
                            <i class="fas fa-spinner fa-spin"></i>
                            <p>Loading categories...</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <!-- Featured Businesses Section -->
        <section class="featured-businesses-section" data-section="featured-businesses">
            <div class="container">
                <?php
                echo Components::call('Headers', 'pageHeader', [
                    'Featured Trusted Businesses',
                    'Businesses with excellent ratings and customer satisfaction',
                    'text-center'
                ]);
                ?>
                
                <div class="businesses-grid" id="featuredBusinessesGrid">
                    <?php if (!empty($featured_businesses)): ?>
                        <?php foreach ($featured_businesses as $business): ?>
                        <div class="business-card" data-business-id="<?= htmlspecialchars($business['business_id'] ?? $business['id'] ?? '') ?>">
                            <div class="business-header">
                                <?php if (!empty($business['image'])): ?>
                                <div class="business-image">
                                    <img src="<?= htmlspecialchars($business['image']) ?>" 
                                         alt="<?= htmlspecialchars($business['name']) ?>"
                                         onerror="this.src='<?= Config::imageUrl('businesses/default.jpg') ?>'">
                                </div>
                                <?php endif; ?>
                                <div class="business-info">
                                    <h3><?= htmlspecialchars($business['name']) ?></h3>
                                    <div class="business-rating">
                                        <div class="stars" data-rating="<?= htmlspecialchars($business['rating']) ?>">
                                            <?php
                                            $rating = floatval($business['rating']);
                                            $fullStars = floor($rating);
                                            $halfStar = $rating - $fullStars >= 0.5;
                                            $emptyStars = 5 - $fullStars - ($halfStar ? 1 : 0);
                                            
                                            // Full stars
                                            for ($i = 0; $i < $fullStars; $i++) {
                                                echo '<i class="fas fa-star"></i>';
                                            }
                                            
                                            // Half star
                                            if ($halfStar) {
                                                echo '<i class="fas fa-star-half-alt"></i>';
                                            }
                                            
                                            // Empty stars
                                            for ($i = 0; $i < $emptyStars; $i++) {
                                                echo '<i class="far fa-star"></i>';
                                            }
                                            ?>
                                        </div>
                                        <span class="rating-value"><?= htmlspecialchars($business['rating']) ?></span>
                                        <span class="reviews-count">(<span class="review-count-value"><?= $business['reviews'] ?></span> reviews)</span>
                                    </div>
                                    <?php if ($business['accredited']): ?>
                                    <div class="accredited-badge">
                                        <i class="fas fa-check-circle"></i>
                                        PIMP Verified
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="business-details">
                                <?php if (!empty($business['categories'])): ?>
                                <div class="business-categories">
                                    <?php foreach ($business['categories'] as $category): ?>
                                    <span class="category-tag"><?= htmlspecialchars($category) ?></span>
                                    <?php endforeach; ?>
                                </div>
                                <?php endif; ?>
                                
                                <div class="business-contact">
                                    <?php if (!empty($business['address'])): ?>
                                    <div class="contact-item">
                                        <i class="fas fa-map-marker-alt"></i>
                                        <span><?= htmlspecialchars($business['address']) ?></span>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($business['phone'])): ?>
                                    <div class="contact-item">
                                        <i class="fas fa-phone"></i>
                                        <span><?= htmlspecialchars($business['phone']) ?></span>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($business['website'])): ?>
                                    <div class="contact-item">
                                        <i class="fas fa-globe"></i>
                                        <a href="<?= htmlspecialchars($business['website']) ?>" target="_blank" rel="noopener noreferrer">Visit Website</a>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="business-actions">
                                <a href="<?= Config::url('/business/' . ($business['business_id'] ?? $business['id'] ?? '')) ?>" 
                                   class="button button-primary view-profile-btn"
                                   data-business-id="<?= htmlspecialchars($business['business_id'] ?? $business['id'] ?? '') ?>">
                                    View Profile
                                </a>
                                <a href="<?= Config::url('/reviews/write?business=' . urlencode($business['business_id'] ?? $business['id'] ?? '')) ?>" 
                                   class="button button-outline">
                                    Write Review
                                </a>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="loading-businesses">
                            <i class="fas fa-spinner fa-spin"></i>
                            <p>Loading featured businesses...</p>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="section-actions text-center">
                    <a href="<?= Config::url('/businesses/featured') ?>" class="button button-primary">
                        View All Featured Businesses
                        <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        </section>

        <!-- Statistics Section -->
        <section class="stats-section" data-section="statistics">
            <div class="container">
                <div class="stats-grid" id="statsGrid">
                    <div class="stat-item">
                        <div class="stat-icon">
                            <i class="fas fa-building"></i>
                        </div>
                        <div class="stat-number" data-stat="businesses">
                            <?= number_format($statistics['total_businesses']) ?>+
                        </div>
                        <div class="stat-label">Business Listings</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-icon">
                            <i class="fas fa-star"></i>
                        </div>
                        <div class="stat-number" data-stat="reviews">
                            <?= number_format($statistics['total_reviews']) ?>+
                        </div>
                        <div class="stat-label">Customer Reviews</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-icon">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <div class="stat-number" data-stat="cities">
                            <?= number_format($statistics['total_cities']) ?>+
                        </div>
                        <div class="stat-label">Cities Covered</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-number" data-stat="satisfaction">
                            <?= $statistics['satisfaction_rate'] ?>%
                        </div>
                        <div class="stat-label">Satisfied Users</div>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA Section -->
        <section class="cta-section">
            <div class="container">
                <div class="cta-content">
                    <h2>Are You a Business Owner?</h2>
                    <p>Join thousands of trusted businesses in our repository and connect with potential customers</p>
                    <div class="cta-buttons">
                        <a href="<?= Config::url('/business/claim') ?>" class="button button-primary">
                            <i class="fas fa-plus-circle"></i>
                            Claim Your Business
                        </a>
                        <a href="<?= Config::url('/for-business') ?>" class="button button-outline">
                            Learn More for Businesses
                        </a>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <!-- Footer -->
    <?php
    echo Components::call('Footers', 'businessFooter', [$footer_config]);
    ?>

    <?php
    // Document close with scripts
    echo Components::call('Footers', 'documentClose', [[
        'scripts' => [
            'static/js/homepage.js'
        ],
        'includeMainJs' => true
    ]]);
    ?>

    <!-- Pass data to JavaScript -->
    <script>
    window.homepageData = {
        categories: <?= json_encode($categories) ?>,
        featuredBusinesses: <?= json_encode($featured_businesses) ?>,
        statistics: <?= json_encode($statistics) ?>,
        apiBaseUrl: '<?= Config::url('/api/v1') ?>'
    };
    </script>
</body>
</html>

<?php
// Output the complete page
echo ob_get_clean();
?>
