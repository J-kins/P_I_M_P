<?php
/**
 * P.I.M.P - Write Review Page
 * Page for users to write and submit business reviews
 */

use PIMP\Core\Config;
use PIMP\Views\Components;
use PIMP\Services\DatabaseFactory;
use PIMP\Services\API\BusinessAPIService;

// Initialize database connection
$db = null;
$businessAPI = null;

try {
    $db = DatabaseFactory::default();
    if ($db) {
        try {
            $businessAPI = new BusinessAPIService($db);
        } catch (\Exception $e) {
            error_log("API service initialization error: " . $e->getMessage());
        }
    }
} catch (\Exception $e) {
    error_log("Database connection error: " . $e->getMessage());
    $db = null;
}

// Get business ID from query string
$business_id = $_GET['business'] ?? null;
$business = null;

if ($business_id && $businessAPI) {
    try {
        $businessResponse = $businessAPI->getBusinessProfile($business_id);
        if ($businessResponse['success'] && !empty($businessResponse['data'])) {
            $business = $businessResponse['data'];
        }
    } catch (\Exception $e) {
        error_log("Error fetching business: " . $e->getMessage());
    }
}

// Get form data
$form_data = [
    'rating' => $_POST['rating'] ?? 5,
    'title' => $_POST['title'] ?? '',
    'review_text' => $_POST['review_text'] ?? '',
    'business_id' => $_POST['business_id'] ?? $business_id,
    'recommend' => $_POST['recommend'] ?? 'yes'
];
$errors = $_SESSION['review_errors'] ?? [];
$success = $_SESSION['review_success'] ?? '';

// Clear session messages
unset($_SESSION['review_errors']);
unset($_SESSION['review_success']);

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

ob_start();
?>

<?php
echo Components::call('Headers', 'documentHead', [[
    'title' => 'Write a Review - P.I.M.P Business Repository',
    'metaTags' => [
        'description' => 'Share your experience and help others make informed decisions. Write a review for a business on P.I.M.P.',
        'keywords' => 'write review, business review, rate business, customer review',
        'author' => 'P.I.M.P Business Repository'
    ],
    'canonical' => Config::url('/reviews/write'),
    'styles' => [
        'views/write-review.css'
    ],
    'scripts' => [
        'static/js/write-review.js'
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
        'title' => 'Write a Review',
        'subtitle' => 'Share your experience and help others make informed decisions',
        'bgImage' => Config::imageUrl('hero-bg.jpg'),
        'overlay' => 'dark',
        'size' => 'md',
        'align' => 'center'
    ]]);
    ?>

    <main class="main-content">
        <div class="write-review-page">
            <div class="container">
                <div class="review-form-wrapper">
                    <?php if ($success): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        <?= htmlspecialchars($success) ?>
                    </div>
                    <?php endif; ?>

                    <?php if (!empty($errors)): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i>
                        <ul>
                            <?php foreach ($errors as $error): ?>
                            <li><?= htmlspecialchars($error) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <?php endif; ?>

                    <form action="<?= Config::url('/reviews/write') ?>" method="POST" class="review-form" id="reviewForm">
                        <!-- Business Selection -->
                        <div class="form-section">
                            <h2>Select Business</h2>
                            <?php if ($business): ?>
                            <div class="selected-business">
                                <div class="business-info">
                                    <h3><?= htmlspecialchars($business['business_name'] ?? $business['trading_name'] ?? 'Unknown Business') ?></h3>
                                    <?php if (!empty($business['address'])): ?>
                                    <p><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($business['address']) ?></p>
                                    <?php endif; ?>
                                </div>
                                <input type="hidden" name="business_id" value="<?= htmlspecialchars($business['id'] ?? $business_id) ?>">
                            </div>
                            <?php else: ?>
                            <div class="form-group">
                                <label for="business_search">Search for Business <span class="required">*</span></label>
                                <div class="search-input-wrapper">
                                    <input type="text" id="business_search" name="business_search" placeholder="Type business name..." autocomplete="off">
                                    <div id="businessSearchResults" class="search-results"></div>
                                </div>
                                <input type="hidden" name="business_id" id="selected_business_id" value="<?= htmlspecialchars($form_data['business_id']) ?>" required>
                            </div>
                            <?php endif; ?>
                        </div>

                        <!-- Rating -->
                        <div class="form-section">
                            <h2>Your Rating</h2>
                            <div class="rating-input">
                                <div class="star-rating" id="starRating">
                                    <?php for ($i = 5; $i >= 1; $i--): ?>
                                    <input type="radio" name="rating" id="star<?= $i ?>" value="<?= $i ?>" <?= $form_data['rating'] == $i ? 'checked' : '' ?>>
                                    <label for="star<?= $i ?>" class="star-label">
                                        <i class="fas fa-star"></i>
                                    </label>
                                    <?php endfor; ?>
                                </div>
                                <div class="rating-text">
                                    <span id="ratingText">Excellent</span>
                                </div>
                            </div>
                        </div>

                        <!-- Review Details -->
                        <div class="form-section">
                            <h2>Review Details</h2>
                            
                            <div class="form-group">
                                <label for="title">Review Title <span class="required">*</span></label>
                                <input type="text" name="title" id="title" value="<?= htmlspecialchars($form_data['title']) ?>" 
                                       placeholder="Summarize your experience in a few words" required maxlength="100">
                                <span class="char-count"><span id="titleCount">0</span>/100</span>
                            </div>

                            <div class="form-group">
                                <label for="review_text">Your Review <span class="required">*</span></label>
                                <textarea name="review_text" id="review_text" rows="8" 
                                          placeholder="Share your experience with this business. Be specific and helpful to other consumers." 
                                          required><?= htmlspecialchars($form_data['review_text']) ?></textarea>
                                <span class="char-count"><span id="reviewCount">0</span>/2000</span>
                            </div>

                            <div class="form-group">
                                <label>Would you recommend this business?</label>
                                <div class="radio-group">
                                    <label class="radio-label">
                                        <input type="radio" name="recommend" value="yes" <?= $form_data['recommend'] === 'yes' ? 'checked' : '' ?>>
                                        <span>Yes, I recommend</span>
                                    </label>
                                    <label class="radio-label">
                                        <input type="radio" name="recommend" value="no" <?= $form_data['recommend'] === 'no' ? 'checked' : '' ?>>
                                        <span>No, I don't recommend</span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Guidelines -->
                        <div class="form-section guidelines-section">
                            <h3><i class="fas fa-info-circle"></i> Review Guidelines</h3>
                            <ul>
                                <li>Be honest and accurate in your review</li>
                                <li>Focus on your personal experience</li>
                                <li>Avoid personal attacks or offensive language</li>
                                <li>Don't include personal information or contact details</li>
                                <li>Reviews must be based on actual experiences</li>
                            </ul>
                        </div>

                        <!-- Submit Button -->
                        <div class="form-actions">
                            <button type="submit" class="button button-primary button-large">
                                <i class="fas fa-paper-plane"></i>
                                Submit Review
                            </button>
                            <a href="<?= Config::url('/businesses') ?>" class="button button-outline">
                                Cancel
                            </a>
                        </div>
                    </form>
                </div>
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

