<?php
/**
 * P.I.M.P - Reviews
 * Comprehensive reviews listing page
 */

use PIMP\Core\Config;
use PIMP\Views\Components;

$nav_items = [
    ['url' => '/', 'label' => 'Home', 'active' => false],
    ['url' => '/businesses', 'label' => 'Business Directory', 'active' => false],
    ['url' => '/reviews', 'label' => 'Reviews', 'active' => true],
    ['url' => '/categories', 'label' => 'Categories', 'active' => false],
    ['url' => '/scam-alerts', 'label' => 'Scam Alerts', 'active' => false],
    ['url' => '/resources', 'label' => 'Resources', 'active' => false],
];

// Sample reviews data
$reviews = [
    [
        'id' => 1,
        'business_name' => 'Quality Home Services LLC',
        'business_id' => 1,
        'customer_name' => 'Sarah Johnson',
        'rating' => 5,
        'date' => '2024-01-15',
        'title' => 'Excellent Kitchen Remodel',
        'content' => 'Quality Home Services did an amazing job remodeling our kitchen. They were professional, on time, and the quality of work exceeded our expectations. Highly recommended!',
        'verified' => true,
        'business_response' => 'Thank you, Sarah! We\'re thrilled you love your new kitchen.',
        'categories' => ['Contractors', 'Home Repair']
    ],
    // ... more reviews
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
        // ... more footer links
    ],
    'theme' => 'light'
];

ob_start();
?>

<?php
echo Components::call('Headers', 'documentHead', [[
    'title' => 'Customer Reviews - P.I.M.P Business Repository',
    'metaTags' => [
        'description' => 'Read genuine customer reviews of local businesses. Make informed decisions with verified reviews from real customers.',
        'keywords' => 'business reviews, customer reviews, ratings, PIMP reviews'
    ],
    'styles' => [
        'views/reviews.css'
    ],
    'scripts' => [
        'js/reviews.js'
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
            ['url' => '/business', 'label' => 'For Business'],
        ],
        'showSearch' => true,
    ]]);
    ?>

    <main class="main-content">
        <!-- Reviews Header -->
        <section class="reviews-header-section">
            <div class="container">
                <div class="reviews-header">
                    <h1>Customer Reviews</h1>
                    <p class="reviews-subtitle">Read genuine reviews from real customers</p>
                    
                    <div class="reviews-stats">
                        <div class="stat-card">
                            <div class="stat-number">50,000+</div>
                            <div class="stat-label">Verified Reviews</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-number">4.5</div>
                            <div class="stat-label">Average Rating</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-number">10,000+</div>
                            <div class="stat-label">Businesses Reviewed</div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Reviews Content -->
        <section class="reviews-content-section">
            <div class="container">
                <div class="reviews-layout">
                    <!-- Filters Sidebar -->
                    <aside class="reviews-filters">
                        <div class="filters-header">
                            <h3>Filter Reviews</h3>
                            <button class="clear-filters">Clear All</button>
                        </div>

                        <div class="filter-group">
                            <h4 class="filter-title">Rating</h4>
                            <div class="filter-options">
                                <label class="filter-option">
                                    <input type="checkbox" name="rating" value="5">
                                    <span class="checkmark"></span>
                                    <div class="rating-stars">
                                        <?php for ($i = 0; $i < 5; $i++): ?>
                                        <i class="fas fa-star"></i>
                                        <?php endfor; ?>
                                    </div>
                                    <span class="filter-count">(12,345)</span>
                                </label>
                                <label class="filter-option">
                                    <input type="checkbox" name="rating" value="4">
                                    <span class="checkmark"></span>
                                    <div class="rating-stars">
                                        <?php for ($i = 0; $i < 4; $i++): ?>
                                        <i class="fas fa-star"></i>
                                        <?php endfor; ?>
                                        <i class="far fa-star"></i>
                                    </div>
                                    <span class="filter-count">(8,765)</span>
                                </label>
                                <!-- More rating options -->
                            </div>
                        </div>

                        <div class="filter-group">
                            <h4 class="filter-title">Category</h4>
                            <div class="filter-options">
                                <label class="filter-option">
                                    <input type="checkbox" name="category" value="restaurants">
                                    <span class="checkmark"></span>
                                    Restaurants
                                    <span class="filter-count">(5,432)</span>
                                </label>
                                <label class="filter-option">
                                    <input type="checkbox" name="category" value="retail">
                                    <span class="checkmark"></span>
                                    Retail
                                    <span class="filter-count">(3,210)</span>
                                </label>
                                <!-- More category options -->
                            </div>
                        </div>

                        <div class="filter-group">
                            <h4 class="filter-title">Date Posted</h4>
                            <div class="filter-options">
                                <label class="filter-option">
                                    <input type="radio" name="date" value="week">
                                    <span class="checkmark"></span>
                                    Past Week
                                </label>
                                <label class="filter-option">
                                    <input type="radio" name="date" value="month">
                                    <span class="checkmark"></span>
                                    Past Month
                                </label>
                                <label class="filter-option">
                                    <input type="radio" name="date" value="year">
                                    <span class="checkmark"></span>
                                    Past Year
                                </label>
                            </div>
                        </div>

                        <button class="apply-filters button-primary">
                            Apply Filters
                        </button>
                    </aside>

                    <!-- Reviews Main Content -->
                    <div class="reviews-main">
                        <div class="reviews-actions">
                            <div class="sort-options">
                                <label for="sort-reviews">Sort by:</label>
                                <select id="sort-reviews" class="sort-select">
                                    <option value="newest">Newest First</option>
                                    <option value="oldest">Oldest First</option>
                                    <option value="highest">Highest Rated</option>
                                    <option value="lowest">Lowest Rated</option>
                                </select>
                            </div>
                            <a href="<?= Config::url('/reviews/write') ?>" class="button button-primary">
                                <i class="fas fa-pencil-alt"></i>
                                Write a Review
                            </a>
                        </div>

                        <div class="reviews-list">
                            <?php foreach ($reviews as $review): ?>
                            <article class="review-card">
                                <div class="review-header">
                                    <div class="business-info">
                                        <h3 class="business-name">
                                            <a href="<?= Config::url('/business/' . $review['business_id']) ?>">
                                                <?= htmlspecialchars($review['business_name']) ?>
                                            </a>
                                        </h3>
                                        <div class="business-categories">
                                            <?php foreach ($review['categories'] as $category): ?>
                                            <span class="category-tag"><?= htmlspecialchars($category) ?></span>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                    <div class="review-meta">
                                        <div class="review-date">
                                            <?= date('F j, Y', strtotime($review['date'])) ?>
                                        </div>
                                        <?php if ($review['verified']): ?>
                                        <div class="verified-badge">
                                            <i class="fas fa-check-circle"></i>
                                            Verified Review
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="review-content">
                                    <div class="review-rating">
                                        <div class="stars">
                                            <?php for ($i = 0; $i < 5; $i++): ?>
                                            <?php if ($i < $review['rating']): ?>
                                            <i class="fas fa-star"></i>
                                            <?php else: ?>
                                            <i class="far fa-star"></i>
                                            <?php endif; ?>
                                            <?php endfor; ?>
                                        </div>
                                        <span class="rating-text">
                                            <?= $review['rating'] ?> out of 5 stars
                                        </span>
                                    </div>

                                    <h4 class="review-title"><?= htmlspecialchars($review['title']) ?></h4>
                                    
                                    <div class="reviewer-info">
                                        <span class="reviewer-name"><?= htmlspecialchars($review['customer_name']) ?></span>
                                    </div>

                                    <p class="review-text"><?= htmlspecialchars($review['content']) ?></p>

                                    <?php if (!empty($review['business_response'])): ?>
                                    <div class="business-response">
                                        <div class="response-header">
                                            <strong>Business Response</strong>
                                        </div>
                                        <p class="response-text"><?= htmlspecialchars($review['business_response']) ?></p>
                                    </div>
                                    <?php endif; ?>
                                </div>

                                <div class="review-actions">
                                    <button class="action-button helpful-button">
                                        <i class="fas fa-thumbs-up"></i>
                                        Helpful (24)
                                    </button>
                                    <button class="action-button share-button">
                                        <i class="fas fa-share"></i>
                                        Share
                                    </button>
                                    <button class="action-button report-button">
                                        <i class="fas fa-flag"></i>
                                        Report
                                    </button>
                                </div>
                            </article>
                            <?php endforeach; ?>
                        </div>

                        <!-- Pagination -->
                        <div class="reviews-pagination">
                            <button class="pagination-button disabled">
                                <i class="fas fa-chevron-left"></i>
                                Previous
                            </button>
                            <div class="pagination-pages">
                                <button class="page-button active">1</button>
                                <button class="page-button">2</button>
                                <button class="page-button">3</button>
                                <span class="page-ellipsis">...</span>
                                <button class="page-button">10</button>
                            </div>
                            <button class="pagination-button">
                                Next
                                <i class="fas fa-chevron-right"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <?php
    echo Components::call('Footers', 'businessFooter', [$footer_config]);
    echo Components::call('Footers', 'documentClose');
    ?>
</body>
</html>

<?php echo ob_get_clean(); ?>