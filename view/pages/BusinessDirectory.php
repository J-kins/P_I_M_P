<?php
/**
 * P.I.M.P - Business Directory
 * Comprehensive business listing page
 */

use PIMP\Core\Config;
use PIMP\Views\Components;

// Navigation items
$nav_items = [
    ['url' => '/', 'label' => 'Home', 'active' => false],
    ['url' => '/businesses', 'label' => 'Business Directory', 'active' => true],
    ['url' => '/reviews', 'label' => 'Reviews', 'active' => false],
    ['url' => '/categories', 'label' => 'Categories', 'active' => false],
    ['url' => '/scam-alerts', 'label' => 'Scam Alerts', 'active' => false],
    ['url' => '/resources', 'label' => 'Resources', 'active' => false],
];

// Sample businesses data
$businesses = [
    [
        'id' => 1,
        'name' => 'Quality Home Services LLC',
        'rating' => '4.8',
        'reviews' => 47,
        'accredited' => true,
        'address' => '123 Main St, Anytown, ST 12345',
        'phone' => '(555) 123-4567',
        'categories' => ['Contractors', 'Home Repair'],
        'image' => Config::imageUrl('businesses/quality-home.jpg'),
        'description' => 'Professional home repair and renovation services with over 15 years of experience.',
        'years_in_business' => 15,
        'response_rate' => '98%',
        'verified' => true
    ],
    // ... more businesses
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
        // ... more footer links
    ],
    'social' => [
        // ... social links
    ],
    'contact' => [
        ['label' => 'Phone', 'value' => '1-800-PIMP-HELP'],
        ['label' => 'Email', 'value' => 'support@pimp-business.com'],
        ['label' => 'Address', 'value' => '123 Business Ave, Suite 100, New York, NY 10001']
    ],
    'theme' => 'light'
];

ob_start();
?>

<?php
echo Components::call('Headers', 'documentHead', [[
    'title' => 'Business Directory - P.I.M.P Business Repository',
    'metaTags' => [
        'description' => 'Browse our comprehensive directory of trusted local businesses. Find ratings, reviews, and contact information.',
        'keywords' => 'business directory, local businesses, ratings, reviews, PIMP directory',
        'author' => 'P.I.M.P Business Repository'
    ],
    'styles' => [
        'views/business-directory.css'
    ],
    'scripts' => [
        'js/business-directory.js'
    ]
]]);
?>

<body>
    <?php
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
            ['url' => '/business', 'label' => 'For Business'],
        ],
        'searchPlaceholder' => 'Search for businesses, services, or categories...',
        'phoneNumber' => '1-800-PIMP-HELP',
        'ctaText' => 'Submit a Review',
        'ctaUrl' => '/reviews/write',
        'showSearch' => true,
        'showPhone' => true,
    ]]);
    ?>

    <main class="main-content">
        <!-- Page Header -->
        <section class="page-header-section">
            <div class="container">
                <?php
                echo Components::call('Headers', 'pageHeader', [
                    'Business Directory',
                    'Find trusted local businesses with verified reviews and ratings',
                    'text-center'
                ]);
                ?>
            </div>
        </section>

        <!-- Search and Filters -->
        <section class="filters-section">
            <div class="container">
                <div class="filters-container">
                    <div class="search-filters">
                        <div class="filter-group">
                            <label for="category-filter">Category</label>
                            <select id="category-filter" class="filter-select">
                                <option value="">All Categories</option>
                                <option value="restaurants">Restaurants</option>
                                <option value="retail">Retail</option>
                                <option value="home-services">Home Services</option>
                                <option value="healthcare">Healthcare</option>
                            </select>
                        </div>
                        <div class="filter-group">
                            <label for="location-filter">Location</label>
                            <input type="text" id="location-filter" class="filter-input" placeholder="City, State or ZIP">
                        </div>
                        <div class="filter-group">
                            <label for="rating-filter">Minimum Rating</label>
                            <select id="rating-filter" class="filter-select">
                                <option value="">Any Rating</option>
                                <option value="4.5">4.5+ Stars</option>
                                <option value="4.0">4.0+ Stars</option>
                                <option value="3.5">3.5+ Stars</option>
                                <option value="3.0">3.0+ Stars</option>
                            </select>
                        </div>
                        <button class="filter-button button-primary">
                            <i class="fas fa-filter"></i>
                            Apply Filters
                        </button>
                    </div>
                </div>
            </div>
        </section>

        <!-- Business Listings -->
        <section class="business-listings-section">
            <div class="container">
                <div class="listings-header">
                    <h2>Business Listings</h2>
                    <div class="results-count">
                        Showing 1-10 of 10,000+ businesses
                    </div>
                </div>

                <div class="businesses-list">
                    <?php foreach ($businesses as $business): ?>
                    <div class="business-listing-card">
                        <div class="business-main-info">
                            <div class="business-image">
                                <img src="<?= $business['image'] ?>" alt="<?= htmlspecialchars($business['name']) ?>">
                            </div>
                            <div class="business-details">
                                <h3 class="business-name">
                                    <a href="<?= Config::url('/business/' . $business['id']) ?>">
                                        <?= htmlspecialchars($business['name']) ?>
                                    </a>
                                </h3>
                                
                                <div class="business-rating">
                                    <div class="stars">
                                        <?php
                                        $rating = floatval($business['rating']);
                                        $fullStars = floor($rating);
                                        $halfStar = $rating - $fullStars >= 0.5;
                                        $emptyStars = 5 - $fullStars - ($halfStar ? 1 : 0);
                                        
                                        for ($i = 0; $i < $fullStars; $i++) {
                                            echo '<i class="fas fa-star"></i>';
                                        }
                                        if ($halfStar) {
                                            echo '<i class="fas fa-star-half-alt"></i>';
                                        }
                                        for ($i = 0; $i < $emptyStars; $i++) {
                                            echo '<i class="far fa-star"></i>';
                                        }
                                        ?>
                                    </div>
                                    <span class="rating-value"><?= $business['rating'] ?></span>
                                    <span class="reviews-count">(<?= $business['reviews'] ?> reviews)</span>
                                </div>

                                <div class="business-meta">
                                    <?php if ($business['accredited']): ?>
                                    <span class="accredited-badge">
                                        <i class="fas fa-check-circle"></i>
                                        PIMP Verified
                                    </span>
                                    <?php endif; ?>
                                    <span class="years-in-business">
                                        <i class="fas fa-calendar-alt"></i>
                                        <?= $business['years_in_business'] ?> years in business
                                    </span>
                                    <span class="response-rate">
                                        <i class="fas fa-comment-dots"></i>
                                        <?= $business['response_rate'] ?> response rate
                                    </span>
                                </div>

                                <p class="business-description">
                                    <?= htmlspecialchars($business['description']) ?>
                                </p>

                                <div class="business-categories">
                                    <?php foreach ($business['categories'] as $category): ?>
                                    <span class="category-tag"><?= htmlspecialchars($category) ?></span>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>

                        <div class="business-contact-info">
                            <div class="contact-details">
                                <div class="contact-item">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <span><?= htmlspecialchars($business['address']) ?></span>
                                </div>
                                <div class="contact-item">
                                    <i class="fas fa-phone"></i>
                                    <span><?= htmlspecialchars($business['phone']) ?></span>
                                </div>
                            </div>
                            <div class="business-actions">
                                <a href="<?= Config::url('/business/' . $business['id']) ?>" class="button button-primary">
                                    View Profile
                                </a>
                                <a href="<?= Config::url('/reviews/write') ?>" class="button button-outline">
                                    Write Review
                                </a>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- Pagination -->
                <div class="pagination">
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
        </section>
    </main>

    <?php
    echo Components::call('Footers', 'businessFooter', [$footer_config]);
    echo Components::call('Footers', 'documentClose');
    ?>
</body>
</html>

<?php echo ob_get_clean(); ?>