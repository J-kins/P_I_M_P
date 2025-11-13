<?php
/**
 * P.I.M.P - Business Profile
 * Detailed business profile page
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

// Sample business data
$business = [
    'id' => 1,
    'name' => 'Quality Home Services LLC',
    'rating' => '4.8',
    'reviews' => 47,
    'accredited' => true,
    'accredited_since' => '2018',
    'address' => '123 Main St, Anytown, ST 12345',
    'phone' => '(555) 123-4567',
    'website' => 'https://qualityhomeservices.com',
    'email' => 'contact@qualityhomeservices.com',
    'hours' => [
        'Monday' => '8:00 AM - 5:00 PM',
        'Tuesday' => '8:00 AM - 5:00 PM',
        'Wednesday' => '8:00 AM - 5:00 PM',
        'Thursday' => '8:00 AM - 5:00 PM',
        'Friday' => '8:00 AM - 5:00 PM',
        'Saturday' => '9:00 AM - 2:00 PM',
        'Sunday' => 'Closed'
    ],
    'categories' => ['Contractors', 'Home Repair', 'Renovation'],
    'services' => ['Kitchen Remodeling', 'Bathroom Renovation', 'Flooring Installation', 'Painting Services'],
    'years_in_business' => 15,
    'employees' => '10-20',
    'response_rate' => '98%',
    'response_time' => 'Within 24 hours',
    'image' => Config::imageUrl('businesses/quality-home.jpg'),
    'gallery' => [
        Config::imageUrl('businesses/gallery/quality-home-1.jpg'),
        Config::imageUrl('businesses/gallery/quality-home-2.jpg'),
        Config::imageUrl('businesses/gallery/quality-home-3.jpg')
    ],
    'description' => 'Quality Home Services LLC has been providing exceptional home repair and renovation services for over 15 years. Our team of skilled professionals is committed to delivering high-quality workmanship and outstanding customer service.',
    'payment_methods' => ['Cash', 'Check', 'Credit Cards', 'Financing Available'],
    'licenses' => ['State Contractor License #12345', 'Insured & Bonded']
];

// Sample reviews
$reviews = [
    [
        'id' => 1,
        'customer_name' => 'Sarah Johnson',
        'rating' => 5,
        'date' => '2024-01-15',
        'title' => 'Excellent Kitchen Remodel',
        'content' => 'Quality Home Services did an amazing job remodeling our kitchen. They were professional, on time, and the quality of work exceeded our expectations.',
        'verified' => true,
        'response' => [
            'date' => '2024-01-16',
            'content' => 'Thank you, Sarah! We\'re thrilled you love your new kitchen. It was a pleasure working with you.'
        ]
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
    'title' => $business['name'] . ' - P.I.M.P Business Repository',
    'metaTags' => [
        'description' => $business['description'],
        'keywords' => $business['name'] . ', ' . implode(', ', $business['categories'])
    ],
    'styles' => [
        'views/business-profile.css'
    ],
    'scripts' => [
        'js/business-profile.js'
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
        <!-- Business Header -->
        <section class="business-header-section">
            <div class="container">
                <div class="business-header">
                    <div class="business-main-info">
                        <div class="business-image">
                            <img src="<?= $business['image'] ?>" alt="<?= htmlspecialchars($business['name']) ?>">
                        </div>
                        <div class="business-details">
                            <h1 class="business-name"><?= htmlspecialchars($business['name']) ?></h1>
                            
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
                                <div class="accredited-badge">
                                    <i class="fas fa-check-circle"></i>
                                    PIMP Accredited Business since <?= $business['accredited_since'] ?>
                                </div>
                                <?php endif; ?>
                                
                                <div class="business-stats">
                                    <span class="stat-item">
                                        <i class="fas fa-calendar-alt"></i>
                                        <?= $business['years_in_business'] ?> years in business
                                    </span>
                                    <span class="stat-item">
                                        <i class="fas fa-users"></i>
                                        <?= $business['employees'] ?> employees
                                    </span>
                                    <span class="stat-item">
                                        <i class="fas fa-comment-dots"></i>
                                        <?= $business['response_rate'] ?> response rate
                                    </span>
                                </div>
                            </div>

                            <div class="business-categories">
                                <?php foreach ($business['categories'] as $category): ?>
                                <span class="category-tag"><?= htmlspecialchars($category) ?></span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                    <div class="business-actions">
                        <div class="action-buttons">
                            <a href="<?= Config::url('/reviews/write?business=' . $business['id']) ?>" class="button button-primary">
                                <i class="fas fa-pencil-alt"></i>
                                Write a Review
                            </a>
                            <a href="#contact" class="button button-outline">
                                <i class="fas fa-phone"></i>
                                Contact Business
                            </a>
                            <button class="button button-outline" id="shareButton">
                                <i class="fas fa-share"></i>
                                Share
                            </button>
                        </div>
                        
                        <div class="business-contact-quick">
                            <div class="contact-item">
                                <i class="fas fa-map-marker-alt"></i>
                                <span><?= htmlspecialchars($business['address']) ?></span>
                            </div>
                            <div class="contact-item">
                                <i class="fas fa-phone"></i>
                                <span><?= htmlspecialchars($business['phone']) ?></span>
                            </div>
                            <?php if ($business['website']): ?>
                            <div class="contact-item">
                                <i class="fas fa-globe"></i>
                                <a href="<?= htmlspecialchars($business['website']) ?>" target="_blank">Visit Website</a>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Business Navigation -->
        <nav class="business-nav">
            <div class="container">
                <ul class="business-nav-list">
                    <li><a href="#overview" class="nav-link active">Overview</a></li>
                    <li><a href="#reviews" class="nav-link">Reviews (<?= $business['reviews'] ?>)</a></li>
                    <li><a href="#services" class="nav-link">Services</a></li>
                    <li><a href="#photos" class="nav-link">Photos</a></li>
                    <li><a href="#contact" class="nav-link">Contact & Hours</a></li>
                </ul>
            </div>
        </nav>

        <!-- Business Content -->
        <div class="business-content">
            <div class="container">
                <div class="content-layout">
                    <!-- Main Content -->
                    <div class="main-content-column">
                        <!-- Overview Section -->
                        <section id="overview" class="content-section">
                            <h2>Business Overview</h2>
                            <div class="overview-content">
                                <p class="business-description"><?= htmlspecialchars($business['description']) ?></p>
                                
                                <div class="overview-details">
                                    <div class="detail-group">
                                        <h3>Services Offered</h3>
                                        <div class="services-list">
                                            <?php foreach ($business['services'] as $service): ?>
                                            <span class="service-tag"><?= htmlspecialchars($service) ?></span>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                    
                                    <div class="detail-group">
                                        <h3>Payment Methods</h3>
                                        <div class="payment-methods">
                                            <?php foreach ($business['payment_methods'] as $method): ?>
                                            <span class="payment-method"><?= htmlspecialchars($method) ?></span>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                    
                                    <div class="detail-group">
                                        <h3>Licenses & Insurance</h3>
                                        <div class="licenses-list">
                                            <?php foreach ($business['licenses'] as $license): ?>
                                            <div class="license-item">
                                                <i class="fas fa-check-circle"></i>
                                                <span><?= htmlspecialchars($license) ?></span>
                                            </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </section>

                        <!-- Reviews Section -->
                        <section id="reviews" class="content-section">
                            <div class="reviews-header">
                                <h2>Customer Reviews</h2>
                                <div class="reviews-summary">
                                    <div class="overall-rating">
                                        <div class="rating-number"><?= $business['rating'] ?></div>
                                        <div class="stars">
                                            <?php
                                            for ($i = 0; $i < 5; $i++) {
                                                if ($i < floor($business['rating'])) {
                                                    echo '<i class="fas fa-star"></i>';
                                                } elseif ($i < $business['rating']) {
                                                    echo '<i class="fas fa-star-half-alt"></i>';
                                                } else {
                                                    echo '<i class="far fa-star"></i>';
                                                }
                                            }
                                            ?>
                                        </div>
                                        <div class="reviews-count"><?= $business['reviews'] ?> reviews</div>
                                    </div>
                                    <a href="<?= Config::url('/reviews/write?business=' . $business['id']) ?>" class="button button-primary">
                                        Write a Review
                                    </a>
                                </div>
                            </div>

                            <div class="reviews-list">
                                <?php foreach ($reviews as $review): ?>
                                <div class="review-card">
                                    <div class="review-header">
                                        <div class="reviewer-info">
                                            <div class="reviewer-name"><?= htmlspecialchars($review['customer_name']) ?></div>
                                            <?php if ($review['verified']): ?>
                                            <span class="verified-badge">
                                                <i class="fas fa-check-circle"></i>
                                                Verified Customer
                                            </span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="review-date"><?= date('F j, Y', strtotime($review['date'])) ?></div>
                                    </div>
                                    
                                    <div class="review-rating">
                                        <div class="stars">
                                            <?php
                                            for ($i = 0; $i < 5; $i++) {
                                                echo $i < $review['rating'] ? '<i class="fas fa-star"></i>' : '<i class="far fa-star"></i>';
                                            }
                                            ?>
                                        </div>
                                    </div>
                                    
                                    <h3 class="review-title"><?= htmlspecialchars($review['title']) ?></h3>
                                    <p class="review-content"><?= htmlspecialchars($review['content']) ?></p>
                                    
                                    <?php if (!empty($review['response'])): ?>
                                    <div class="business-response">
                                        <div class="response-header">
                                            <strong>Business Response</strong>
                                            <span class="response-date"><?= date('F j, Y', strtotime($review['response']['date'])) ?></span>
                                        </div>
                                        <p class="response-content"><?= htmlspecialchars($review['response']['content']) ?></p>
                                    </div>
                                    <?php endif; ?>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </section>
                    </div>

                    <!-- Sidebar -->
                    <div class="sidebar-column">
                        <!-- Contact Info -->
                        <section id="contact" class="sidebar-section">
                            <h3>Contact Information</h3>
                            <div class="contact-details">
                                <div class="contact-item">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <div>
                                        <strong>Address</strong>
                                        <p><?= htmlspecialchars($business['address']) ?></p>
                                    </div>
                                </div>
                                <div class="contact-item">
                                    <i class="fas fa-phone"></i>
                                    <div>
                                        <strong>Phone</strong>
                                        <p><?= htmlspecialchars($business['phone']) ?></p>
                                    </div>
                                </div>
                                <?php if ($business['email']): ?>
                                <div class="contact-item">
                                    <i class="fas fa-envelope"></i>
                                    <div>
                                        <strong>Email</strong>
                                        <p><?= htmlspecialchars($business['email']) ?></p>
                                    </div>
                                </div>
                                <?php endif; ?>
                                <?php if ($business['website']): ?>
                                <div class="contact-item">
                                    <i class="fas fa-globe"></i>
                                    <div>
                                        <strong>Website</strong>
                                        <p><a href="<?= htmlspecialchars($business['website']) ?>" target="_blank">Visit Website</a></p>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </div>
                        </section>

                        <!-- Business Hours -->
                        <section class="sidebar-section">
                            <h3>Business Hours</h3>
                            <div class="business-hours">
                                <?php foreach ($business['hours'] as $day => $hours): ?>
                                <div class="hours-item">
                                    <span class="day"><?= $day ?></span>
                                    <span class="hours"><?= $hours ?></span>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </section>

                        <!-- Accreditation -->
                        <?php if ($business['accredited']): ?>
                        <section class="sidebar-section">
                            <h3>PIMP Accreditation</h3>
                            <div class="accreditation-info">
                                <div class="accreditation-badge">
                                    <i class="fas fa-shield-alt"></i>
                                    <span>Accredited Business</span>
                                </div>
                                <p>This business meets PIMP accreditation standards, which include a commitment to make a good faith effort to resolve any consumer complaints.</p>
                                <div class="accreditation-details">
                                    <div class="detail-item">
                                        <strong>Accredited Since:</strong>
                                        <span><?= $business['accredited_since'] ?></span>
                                    </div>
                                    <div class="detail-item">
                                        <strong>Years in Business:</strong>
                                        <span><?= $business['years_in_business'] ?></span>
                                    </div>
                                </div>
                            </div>
                        </section>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php
    echo Components::call('Footers', 'businessFooter', [$footer_config]);
    echo Components::call('Footers', 'documentClose');
    ?>
</body>
</html>

<?php echo ob_get_clean(); ?>