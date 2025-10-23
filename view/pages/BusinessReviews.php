<?php
/**
 * P.I.M.P - Business Reviews Management
 * Review management and response system for business owners
 */

use PIMP\Core\Config;
use PIMP\Views\Components;

$nav_items = [
    ['url' => '/', 'label' => 'Home', 'active' => false],
    ['url' => '/businesses', 'label' => 'Business Directory', 'active' => false],
    ['url' => '/reviews', 'label' => 'Reviews', 'active' => false],
    ['url' => '/categories', 'label' => 'Categories', 'active' => false],
    ['url' => '/scam-alerts', 'label' => 'Scam Alerts', 'active' => false],
    ['url' => '/resources', 'label' => 'Resources', 'active' => false],
];

$footer_config = [
    'logo' => Config::imageUrl('logo.png'),
    'logoAlt' => 'P.I.M.P Business Repository',
    'theme' => 'light'
];

// Mock data for reviews
$reviews = [
    [
        'id' => 1,
        'customer_name' => 'John Smith',
        'rating' => 5,
        'date' => '2024-01-15',
        'title' => 'Excellent Service!',
        'content' => 'The team provided exceptional service and went above and beyond to meet our needs. Highly recommended!',
        'verified' => true,
        'response' => [
            'date' => '2024-01-16',
            'content' => 'Thank you for your kind words, John! We\'re thrilled to hear about your positive experience.'
        ]
    ],
    [
        'id' => 2,
        'customer_name' => 'Sarah Johnson',
        'rating' => 4,
        'date' => '2024-01-10',
        'title' => 'Great work, minor issues',
        'content' => 'Overall great service, but there were some communication delays during the project.',
        'verified' => true,
        'response' => null
    ],
    [
        'id' => 3,
        'customer_name' => 'Mike Davis',
        'rating' => 3,
        'date' => '2024-01-05',
        'title' => 'Average experience',
        'content' => 'The service was okay but took longer than expected. Could be improved.',
        'verified' => false,
        'response' => null
    ]
];

$stats = [
    'total_reviews' => 47,
    'average_rating' => 4.2,
    'response_rate' => 85,
    'reviews_this_month' => 12
];

ob_start();
?>

<?php
echo Components::call('Headers', 'documentHead', [[
    'title' => 'Review Management - P.I.M.P Business Dashboard',
    'metaTags' => [
        'description' => 'Manage and respond to customer reviews for your business on P.I.M.P',
        'keywords' => 'review management, business reviews, customer feedback, PIMP business'
    ],
    'styles' => [
        'views/business-reviews.css'
    ],
    'scripts' => [
        'js/business-reviews.js'
    ]
]]);
?>

<body class="business-reviews-page">
    <?php
    echo Components::call('Headers', 'businessHeader', [[
        'logo' => Config::imageUrl('logo.png'),
        'logoAlt' => 'P.I.M.P - Business Repository Platform',
        'mainNavItems' => $nav_items,
        'userActions' => [
            ['url' => '/business/dashboard', 'label' => 'Dashboard', 'separator' => false],
            ['url' => '/business/settings', 'label' => 'Settings', 'separator' => false],
            ['url' => '/logout', 'label' => 'Logout', 'separator' => true],
        ],
        'showSearch' => false,
    ]]);
    ?>

    <main class="business-reviews-main">
        <!-- Page Header -->
        <div class="reviews-page-header">
            <div class="container">
                <div class="page-header-content">
                    <h1>Review Management</h1>
                    <p>Manage and respond to customer reviews</p>
                </div>
            </div>
        </div>

        <!-- Stats Overview -->
        <section class="reviews-stats-section">
            <div class="container">
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-star"></i>
                        </div>
                        <div class="stat-content">
                            <h3><?= $stats['average_rating'] ?></h3>
                            <p>Average Rating</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-comments"></i>
                        </div>
                        <div class="stat-content">
                            <h3><?= $stats['total_reviews'] ?></h3>
                            <p>Total Reviews</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-reply"></i>
                        </div>
                        <div class="stat-content">
                            <h3><?= $stats['response_rate'] ?>%</h3>
                            <p>Response Rate</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-calendar"></i>
                        </div>
                        <div class="stat-content">
                            <h3><?= $stats['reviews_this_month'] ?></h3>
                            <p>This Month</p>
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
                            <h3>Filters</h3>
                            <button type="button" class="clear-filters">Clear All</button>
                        </div>

                        <div class="filter-group">
                            <h4 class="filter-title">Rating</h4>
                            <div class="filter-options">
                                <label class="filter-option">
                                    <input type="checkbox" name="rating" value="5" checked>
                                    <span class="checkmark"></span>
                                    <div class="rating-stars">
                                        <?php for ($i = 0; $i < 5; $i++): ?>
                                            <i class="fas fa-star"></i>
                                        <?php endfor; ?>
                                    </div>
                                    <span class="filter-count">(23)</span>
                                </label>
                                <label class="filter-option">
                                    <input type="checkbox" name="rating" value="4" checked>
                                    <span class="checkmark"></span>
                                    <div class="rating-stars">
                                        <?php for ($i = 0; $i < 4; $i++): ?>
                                            <i class="fas fa-star"></i>
                                        <?php endfor; ?>
                                        <i class="far fa-star"></i>
                                    </div>
                                    <span class="filter-count">(15)</span>
                                </label>
                                <label class="filter-option">
                                    <input type="checkbox" name="rating" value="3">
                                    <span class="checkmark"></span>
                                    <div class="rating-stars">
                                        <?php for ($i = 0; $i < 3; $i++): ?>
                                            <i class="fas fa-star"></i>
                                        <?php endfor; ?>
                                        <?php for ($i = 0; $i < 2; $i++): ?>
                                            <i class="far fa-star"></i>
                                        <?php endfor; ?>
                                    </div>
                                    <span class="filter-count">(6)</span>
                                </label>
                                <label class="filter-option">
                                    <input type="checkbox" name="rating" value="2">
                                    <span class="checkmark"></span>
                                    <div class="rating-stars">
                                        <?php for ($i = 0; $i < 2; $i++): ?>
                                            <i class="fas fa-star"></i>
                                        <?php endfor; ?>
                                        <?php for ($i = 0; $i < 3; $i++): ?>
                                            <i class="far fa-star"></i>
                                        <?php endfor; ?>
                                    </div>
                                    <span class="filter-count">(2)</span>
                                </label>
                                <label class="filter-option">
                                    <input type="checkbox" name="rating" value="1">
                                    <span class="checkmark"></span>
                                    <div class="rating-stars">
                                        <i class="fas fa-star"></i>
                                        <?php for ($i = 0; $i < 4; $i++): ?>
                                            <i class="far fa-star"></i>
                                        <?php endfor; ?>
                                    </div>
                                    <span class="filter-count">(1)</span>
                                </label>
                            </div>
                        </div>

                        <div class="filter-group">
                            <h4 class="filter-title">Status</h4>
                            <div class="filter-options">
                                <label class="filter-option">
                                    <input type="radio" name="status" value="all" checked>
                                    <span class="checkmark"></span>
                                    All Reviews
                                    <span class="filter-count">(<?= $stats['total_reviews'] ?>)</span>
                                </label>
                                <label class="filter-option">
                                    <input type="radio" name="status" value="responded">
                                    <span class="checkmark"></span>
                                    Responded
                                    <span class="filter-count">(<?= round($stats['total_reviews'] * $stats['response_rate'] / 100) ?>)</span>
                                </label>
                                <label class="filter-option">
                                    <input type="radio" name="status" value="pending">
                                    <span class="checkmark"></span>
                                    Pending Response
                                    <span class="filter-count">(<?= round($stats['total_reviews'] * (100 - $stats['response_rate']) / 100) ?>)</span>
                                </label>
                                <label class="filter-option">
                                    <input type="radio" name="status" value="verified">
                                    <span class="checkmark"></span>
                                    Verified Reviews
                                    <span class="filter-count">(<?= $stats['total_reviews'] - 1 ?>)</span>
                                </label>
                            </div>
                        </div>

                        <div class="filter-group">
                            <h4 class="filter-title">Date Range</h4>
                            <div class="filter-options">
                                <label class="filter-option">
                                    <input type="radio" name="date_range" value="all" checked>
                                    <span class="checkmark"></span>
                                    All Time
                                </label>
                                <label class="filter-option">
                                    <input type="radio" name="date_range" value="month">
                                    <span class="checkmark"></span>
                                    This Month
                                </label>
                                <label class="filter-option">
                                    <input type="radio" name="date_range" value="week">
                                    <span class="checkmark"></span>
                                    This Week
                                </label>
                                <label class="filter-option">
                                    <input type="radio" name="date_range" value="today">
                                    <span class="checkmark"></span>
                                    Today
                                </label>
                            </div>
                        </div>

                        <button type="button" class="apply-filters button-primary">Apply Filters</button>
                    </aside>

                    <!-- Main Reviews Content -->
                    <div class="reviews-main">
                        <div class="reviews-actions">
                            <div class="sort-options">
                                <label for="sort-reviews">Sort by:</label>
                                <select id="sort-reviews" class="sort-select">
                                    <option value="newest">Newest First</option>
                                    <option value="oldest">Oldest First</option>
                                    <option value="highest">Highest Rating</option>
                                    <option value="lowest">Lowest Rating</option>
                                </select>
                            </div>
                            <button type="button" class="export-reviews button-secondary">
                                <i class="fas fa-download"></i>
                                Export Reviews
                            </button>
                        </div>

                        <div class="reviews-list">
                            <?php foreach ($reviews as $review): ?>
                                <div class="review-card" data-review-id="<?= $review['id'] ?>">
                                    <div class="review-header">
                                        <div class="reviewer-info">
                                            <div class="reviewer-name"><?= htmlspecialchars($review['customer_name']) ?></div>
                                            <div class="review-meta">
                                                <div class="review-date"><?= date('F j, Y', strtotime($review['date'])) ?></div>
                                                <?php if ($review['verified']): ?>
                                                    <div class="verified-badge">
                                                        <i class="fas fa-check-circle"></i>
                                                        Verified Customer
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <div class="review-rating">
                                            <div class="stars">
                                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                                    <i class="fas fa-star <?= $i <= $review['rating'] ? 'filled' : 'empty' ?>"></i>
                                                <?php endfor; ?>
                                            </div>
                                            <div class="rating-value"><?= $review['rating'] ?>.0</div>
                                        </div>
                                    </div>

                                    <div class="review-content">
                                        <h4 class="review-title"><?= htmlspecialchars($review['title']) ?></h4>
                                        <p class="review-text"><?= htmlspecialchars($review['content']) ?></p>
                                    </div>

                                    <?php if ($review['response']): ?>
                                        <div class="business-response">
                                            <div class="response-header">
                                                <strong>Your Response</strong>
                                                <span class="response-date"><?= date('F j, Y', strtotime($review['response']['date'])) ?></span>
                                            </div>
                                            <p class="response-text"><?= htmlspecialchars($review['response']['content']) ?></p>
                                            <div class="response-actions">
                                                <button type="button" class="edit-response action-button">
                                                    <i class="fas fa-edit"></i>
                                                    Edit Response
                                                </button>
                                                <button type="button" class="delete-response action-button">
                                                    <i class="fas fa-trash"></i>
                                                    Delete Response
                                                </button>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <div class="response-form-container" style="display: none;">
                                            <form class="response-form">
                                                <div class="form-group">
                                                    <label for="response-<?= $review['id'] ?>" class="form-label">Your Response</label>
                                                    <textarea 
                                                        id="response-<?= $review['id'] ?>" 
                                                        class="form-textarea" 
                                                        rows="4" 
                                                        placeholder="Write your response to this review..."
                                                        maxlength="1000"></textarea>
                                                    <div class="char-count">
                                                        <span class="current-chars">0</span>/1000 characters
                                                    </div>
                                                </div>
                                                <div class="form-actions">
                                                    <button type="submit" class="submit-response button-primary">
                                                        Post Response
                                                    </button>
                                                    <button type="button" class="cancel-response button-secondary">
                                                        Cancel
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                        <div class="review-actions">
                                            <button type="button" class="respond-button button-primary">
                                                <i class="fas fa-reply"></i>
                                                Respond to Review
                                            </button>
                                            <button type="button" class="report-review action-button">
                                                <i class="fas fa-flag"></i>
                                                Report
                                            </button>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- Pagination -->
                        <div class="reviews-pagination">
                            <button class="pagination-button prev-button disabled">
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
                            <button class="pagination-button next-button">
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

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Response form toggle
        const respondButtons = document.querySelectorAll('.respond-button');
        const cancelButtons = document.querySelectorAll('.cancel-response');
        
        respondButtons.forEach(button => {
            button.addEventListener('click', function() {
                const reviewCard = this.closest('.review-card');
                const responseForm = reviewCard.querySelector('.response-form-container');
                const reviewActions = reviewCard.querySelector('.review-actions');
                
                responseForm.style.display = 'block';
                reviewActions.style.display = 'none';
            });
        });
        
        cancelButtons.forEach(button => {
            button.addEventListener('click', function() {
                const reviewCard = this.closest('.review-card');
                const responseForm = reviewCard.querySelector('.response-form-container');
                const reviewActions = reviewCard.querySelector('.review-actions');
                
                responseForm.style.display = 'none';
                reviewActions.style.display = 'flex';
            });
        });
        
        // Character count for response textarea
        const textareas = document.querySelectorAll('.form-textarea');
        textareas.forEach(textarea => {
            const charCount = textarea.parentElement.querySelector('.char-count .current-chars');
            
            textarea.addEventListener('input', function() {
                charCount.textContent = this.value.length;
            });
        });
        
        // Filter functionality
        const clearFiltersButton = document.querySelector('.clear-filters');
        const applyFiltersButton = document.querySelector('.apply-filters');
        
        clearFiltersButton.addEventListener('click', function() {
            const checkboxes = document.querySelectorAll('input[type="checkbox"]');
            const radios = document.querySelectorAll('input[type="radio"]');
            
            checkboxes.forEach(checkbox => {
                checkbox.checked = false;
            });
            
            // Reset radio buttons to default
            document.querySelector('input[name="status"][value="all"]').checked = true;
            document.querySelector('input[name="date_range"][value="all"]').checked = true;
        });
        
        applyFiltersButton.addEventListener('click', function() {
            // In a real application, this would filter the reviews via AJAX
            alert('Filters applied! This would refresh the reviews list in a real application.');
        });
        
        // Export reviews
        const exportButton = document.querySelector('.export-reviews');
        exportButton.addEventListener('click', function() {
            alert('Export functionality would be implemented here!');
        });
        
        // Sort functionality
        const sortSelect = document.getElementById('sort-reviews');
        sortSelect.addEventListener('change', function() {
            // In a real application, this would sort the reviews via AJAX
            alert('Sorting reviews by: ' + this.value);
        });
    });
    </script>
</body>
</html>

<?php echo ob_get_clean(); ?>
