<?php
/**
 * P.I.M.P - Business Search
 * Advanced search functionality for businesses
 */

use PIMP\Core\Config;
use PIMP\Views\Components;

// Navigation items
$nav_items = [
    ['url' => '/', 'label' => 'Home', 'active' => false],
    ['url' => '/businesses', 'label' => 'Business Directory', 'active' => false],
    ['url' => '/reviews', 'label' => 'Reviews', 'active' => false],
    ['url' => '/categories', 'label' => 'Categories', 'active' => false],
    ['url' => '/scam-alerts', 'label' => 'Scam Alerts', 'active' => false],
    ['url' => '/resources', 'label' => 'Resources', 'active' => false],
];

// Search results
$search_results = [
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
        'match_score' => 95,
        'distance' => '2.3 miles'
    ],
    // ... more results
];

// Search filters
$filters = [
    'categories' => ['Restaurants', 'Retail', 'Home Services', 'Healthcare'],
    'locations' => ['New York, NY', 'Los Angeles, CA', 'Chicago, IL'],
    'ratings' => ['5 stars', '4+ stars', '3+ stars'],
    'features' => ['Open Now', 'Accepts Credit Cards', 'Free Parking', 'Wheelchair Accessible']
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
    'title' => 'Search Businesses - P.I.M.P Business Repository',
    'metaTags' => [
        'description' => 'Search for trusted local businesses by name, category, location, and more. Find ratings, reviews, and contact information.',
        'keywords' => 'business search, find businesses, local search, business directory'
    ],
    'styles' => [
        'views/business-search.css'
    ],
    'scripts' => [
        'js/business-search.js'
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
        'searchPlaceholder' => 'Search for businesses, services, or categories...',
        'showSearch' => true,
    ]]);
    ?>

    <main class="main-content">
        <!-- Search Header -->
        <section class="search-header-section">
            <div class="container">
                <div class="search-header">
                    <h1>Search Businesses</h1>
                    <p class="search-subtitle">Find trusted local businesses in your area</p>
                    
                    <!-- Main Search Bar -->
                    <div class="main-search-container">
                        <form action="<?= Config::url('/businesses/search') ?>" method="get" class="main-search-form">
                            <div class="search-input-group">
                                <div class="input-with-icon">
                                    <i class="fas fa-search"></i>
                                    <input type="text" name="q" placeholder="What are you looking for?" 
                                           value="<?= htmlspecialchars($_GET['q'] ?? '') ?>" class="search-input">
                                </div>
                                <div class="input-with-icon">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <input type="text" name="location" placeholder="City, State or ZIP" 
                                           value="<?= htmlspecialchars($_GET['location'] ?? '') ?>" class="search-input">
                                </div>
                                <button type="submit" class="search-button button-primary">
                                    <i class="fas fa-search"></i>
                                    Search
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </section>

        <!-- Search Results -->
        <section class="search-results-section">
            <div class="container">
                <div class="search-layout">
                    <!-- Filters Sidebar -->
                    <aside class="filters-sidebar">
                        <div class="filters-header">
                            <h3>Filters</h3>
                            <button class="clear-filters">Clear All</button>
                        </div>

                        <!-- Category Filter -->
                        <div class="filter-group">
                            <h4 class="filter-title">Category</h4>
                            <div class="filter-options">
                                <?php foreach ($filters['categories'] as $category): ?>
                                <label class="filter-option">
                                    <input type="checkbox" name="category" value="<?= strtolower($category) ?>">
                                    <span class="checkmark"></span>
                                    <?= htmlspecialchars($category) ?>
                                </label>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- Location Filter -->
                        <div class="filter-group">
                            <h4 class="filter-title">Location</h4>
                            <div class="filter-options">
                                <?php foreach ($filters['locations'] as $location): ?>
                                <label class="filter-option">
                                    <input type="checkbox" name="location" value="<?= strtolower($location) ?>">
                                    <span class="checkmark"></span>
                                    <?= htmlspecialchars($location) ?>
                                </label>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- Rating Filter -->
                        <div class="filter-group">
                            <h4 class="filter-title">Minimum Rating</h4>
                            <div class="filter-options">
                                <?php foreach ($filters['ratings'] as $rating): ?>
                                <label class="filter-option">
                                    <input type="radio" name="rating" value="<?= strtolower($rating) ?>">
                                    <span class="checkmark"></span>
                                    <?= htmlspecialchars($rating) ?>
                                </label>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- Features Filter -->
                        <div class="filter-group">
                            <h4 class="filter-title">Features</h4>
                            <div class="filter-options">
                                <?php foreach ($filters['features'] as $feature): ?>
                                <label class="filter-option">
                                    <input type="checkbox" name="feature" value="<?= strtolower($feature) ?>">
                                    <span class="checkmark"></span>
                                    <?= htmlspecialchars($feature) ?>
                                </label>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <button class="apply-filters button-primary">
                            Apply Filters
                        </button>
                    </aside>

                    <!-- Results Main Content -->
                    <div class="results-main">
                        <div class="results-header">
                            <div class="results-info">
                                <h2>Search Results</h2>
                                <span class="results-count">25 results found</span>
                            </div>
                            <div class="results-sort">
                                <label for="sort-by">Sort by:</label>
                                <select id="sort-by" class="sort-select">
                                    <option value="relevance">Relevance</option>
                                    <option value="rating">Highest Rated</option>
                                    <option value="reviews">Most Reviews</option>
                                    <option value="distance">Distance</option>
                                    <option value="name">Name (A-Z)</option>
                                </select>
                            </div>
                        </div>

                        <div class="search-results-list">
                            <?php foreach ($search_results as $business): ?>
                            <div class="search-result-card">
                                <div class="result-image">
                                    <img src="<?= $business['image'] ?>" alt="<?= htmlspecialchars($business['name']) ?>">
                                </div>
                                
                                <div class="result-content">
                                    <div class="result-header">
                                        <h3 class="business-name">
                                            <a href="<?= Config::url('/business/' . $business['id']) ?>">
                                                <?= htmlspecialchars($business['name']) ?>
                                            </a>
                                        </h3>
                                        <div class="match-score">
                                            <span class="score-badge"><?= $business['match_score'] ?>% match</span>
                                        </div>
                                    </div>

                                    <div class="business-rating">
                                        <div class="stars">
                                            <?php
                                            $rating = floatval($business['rating']);
                                            $fullStars = floor($rating);
                                            $halfStar = $rating - $fullStars >= 0.5;
                                            $emptyStars = 5 - $fullStars - ($halfStar ? 1 : 0);
                                            
                                            for ($i = 0; $i < $fullStars; $i++) echo '<i class="fas fa-star"></i>';
                                            if ($halfStar) echo '<i class="fas fa-star-half-alt"></i>';
                                            for ($i = 0; $i < $emptyStars; $i++) echo '<i class="far fa-star"></i>';
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
                                        <span class="distance">
                                            <i class="fas fa-map-marker-alt"></i>
                                            <?= $business['distance'] ?> away
                                        </span>
                                    </div>

                                    <div class="business-categories">
                                        <?php foreach ($business['categories'] as $category): ?>
                                        <span class="category-tag"><?= htmlspecialchars($category) ?></span>
                                        <?php endforeach; ?>
                                    </div>

                                    <div class="business-contact">
                                        <div class="contact-item">
                                            <i class="fas fa-map-marker-alt"></i>
                                            <span><?= htmlspecialchars($business['address']) ?></span>
                                        </div>
                                        <div class="contact-item">
                                            <i class="fas fa-phone"></i>
                                            <span><?= htmlspecialchars($business['phone']) ?></span>
                                        </div>
                                    </div>
                                </div>

                                <div class="result-actions">
                                    <a href="<?= Config::url('/business/' . $business['id']) ?>" class="button button-primary">
                                        View Profile
                                    </a>
                                    <a href="<?= Config::url('/reviews/write') ?>" class="button button-outline">
                                        Write Review
                                    </a>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- Pagination -->
                        <div class="search-pagination">
                            <button class="pagination-button disabled">
                                <i class="fas fa-chevron-left"></i>
                                Previous
                            </button>
                            <div class="pagination-pages">
                                <button class="page-button active">1</button>
                                <button class="page-button">2</button>
                                <button class="page-button">3</button>
                                <span class="page-ellipsis">...</span>
                                <button class="page-button">5</button>
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