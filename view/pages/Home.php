<?php
/**
 * P.I.M.P - Homepage
 * Business Repository Platform Landing Page
 */

use PIMP\Core\Config;
use PIMP\Views\Components;

// Navigation items
$nav_items = [
    ['url' => '/', 'label' => 'Home', 'active' => true],
    ['url' => '/businesses', 'label' => 'Business Directory', 'active' => false],
    ['url' => '/reviews', 'label' => 'Reviews', 'active' => false],
    ['url' => '/categories', 'label' => 'Categories', 'active' => false],
    ['url' => '/scam-alerts', 'label' => 'Scam Alerts', 'active' => false],
    ['url' => '/resources', 'label' => 'Resources', 'active' => false],
];

// Sample categories for homepage
$categories = [
    [
        'name' => 'Restaurants & Dining', 
        'url' => '/category/restaurants', 
        'count' => 1250, 
        'icon' => '<i class="fas fa-utensils"></i>',
        'description' => 'Find the best dining experiences'
    ],
    [
        'name' => 'Retail & Shopping', 
        'url' => '/category/retail', 
        'count' => 890, 
        'icon' => '<i class="fas fa-shopping-cart"></i>',
        'description' => 'Shop with trusted retailers'
    ],
    [
        'name' => 'Home Services', 
        'url' => '/category/home-services', 
        'count' => 1560, 
        'icon' => '<i class="fas fa-home"></i>',
        'description' => 'Professional home service providers'
    ],
    [
        'name' => 'Healthcare', 
        'url' => '/category/healthcare', 
        'count' => 720, 
        'icon' => '<i class="fas fa-heartbeat"></i>',
        'description' => 'Medical and wellness services'
    ],
    [
        'name' => 'Automotive', 
        'url' => '/category/automotive', 
        'count' => 430, 
        'icon' => '<i class="fas fa-car"></i>',
        'description' => 'Auto repair and services'
    ],
    [
        'name' => 'Professional Services', 
        'url' => '/category/professional', 
        'count' => 950, 
        'icon' => '<i class="fas fa-briefcase"></i>',
        'description' => 'Business and professional services'
    ],
];

// Sample featured businesses
$featured_businesses = [
    [
        'name' => 'Quality Home Services LLC',
        'rating' => '4.8',
        'accredited' => true,
        'address' => '123 Main St, Anytown, ST 12345',
        'phone' => '(555) 123-4567',
        'website' => 'https://qualityhomeservices.com',
        'reviews' => 47,
        'categories' => ['Contractors', 'Home Repair'],
        'image' => Config::imageUrl('businesses/quality-home.jpg')
    ],
    [
        'name' => 'Reliable Auto Care',
        'rating' => '4.6',
        'accredited' => true,
        'address' => '456 Oak Ave, Somewhere, ST 67890',
        'phone' => '(555) 987-6543',
        'reviews' => 89,
        'categories' => ['Automotive', 'Auto Repair'],
        'image' => Config::imageUrl('businesses/reliable-auto.jpg')
    ],
    [
        'name' => 'City Dental Clinic',
        'rating' => '4.9',
        'accredited' => true,
        'address' => '789 Pine Blvd, Downtown, ST 11223',
        'phone' => '(555) 456-7890',
        'reviews' => 124,
        'categories' => ['Healthcare', 'Dental'],
        'image' => Config::imageUrl('businesses/city-dental.jpg')
    ]
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
        'js/main.js',
        'js/search.js'
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
        <section class="categories-section">
            <div class="container">
                <?php
                echo Components::call('Headers', 'pageHeader', [
                    'Popular Business Categories',
                    'Browse businesses by category to find exactly what you\'re looking for',
                    'text-center'
                ]);
                ?>
                
                <div class="categories-grid">
                    <?php foreach ($categories as $category): ?>
                    <div class="category-card">
                        <div class="category-icon">
                            <?= $category['icon'] ?>
                        </div>
                        <h3><?= htmlspecialchars($category['name']) ?></h3>
                        <p><?= htmlspecialchars($category['description']) ?></p>
                        <div class="category-meta">
                            <span class="business-count">
                                <i class="fas fa-building"></i>
                                <?= number_format($category['count']) ?> businesses
                            </span>
                        </div>
                        <a href="<?= Config::url($category['url']) ?>" class="button button-outline">
                            Browse Category
                            <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <!-- Featured Businesses Section -->
        <section class="featured-businesses-section">
            <div class="container">
                <?php
                echo Components::call('Headers', 'pageHeader', [
                    'Featured Trusted Businesses',
                    'Businesses with excellent ratings and customer satisfaction',
                    'text-center'
                ]);
                ?>
                
                <div class="businesses-grid">
                    <?php foreach ($featured_businesses as $business): ?>
                    <div class="business-card">
                        <div class="business-header">
                            <?php if ($business['image']): ?>
                            <div class="business-image">
                                <img src="<?= $business['image'] ?>" alt="<?= htmlspecialchars($business['name']) ?>">
                            </div>
                            <?php endif; ?>
                            <div class="business-info">
                                <h3><?= htmlspecialchars($business['name']) ?></h3>
                                <div class="business-rating">
                                    <div class="stars">
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
                                    <span class="rating-value"><?= $business['rating'] ?></span>
                                    <span class="reviews-count">(<?= $business['reviews'] ?> reviews)</span>
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
                            <div class="business-categories">
                                <?php foreach ($business['categories'] as $category): ?>
                                <span class="category-tag"><?= htmlspecialchars($category) ?></span>
                                <?php endforeach; ?>
                            </div>
                            
                            <div class="business-contact">
                                <?php if ($business['address']): ?>
                                <div class="contact-item">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <span><?= htmlspecialchars($business['address']) ?></span>
                                </div>
                                <?php endif; ?>
                                
                                <?php if ($business['phone']): ?>
                                <div class="contact-item">
                                    <i class="fas fa-phone"></i>
                                    <span><?= htmlspecialchars($business['phone']) ?></span>
                                </div>
                                <?php endif; ?>
                                
                                <?php if ($business['website']): ?>
                                <div class="contact-item">
                                    <i class="fas fa-globe"></i>
                                    <a href="<?= htmlspecialchars($business['website']) ?>" target="_blank">Visit Website</a>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="business-actions">
                            <a href="<?= Config::url('/business/1') ?>" class="button button-primary">
                                View Profile
                            </a>
                            <a href="<?= Config::url('/reviews/write') ?>" class="button button-outline">
                                Write Review
                            </a>
                        </div>
                    </div>
                    <?php endforeach; ?>
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
        <section class="stats-section">
            <div class="container">
                <div class="stats-grid">
                    <div class="stat-item">
                        <div class="stat-icon">
                            <i class="fas fa-building"></i>
                        </div>
                        <div class="stat-number">10,000+</div>
                        <div class="stat-label">Business Listings</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-icon">
                            <i class="fas fa-star"></i>
                        </div>
                        <div class="stat-number">50,000+</div>
                        <div class="stat-label">Customer Reviews</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-icon">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <div class="stat-number">500+</div>
                        <div class="stat-label">Cities Covered</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-number">99%</div>
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
            'js/homepage.js'
        ],
        'includeMainJs' => true
    ]]);
    ?>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Business card interactions
        document.querySelectorAll('.business-card').forEach(card => {
            card.addEventListener('click', function(e) {
                if (!e.target.closest('a, button')) {
                    const profileLink = this.querySelector('.button-primary');
                    if (profileLink) {
                        window.location.href = profileLink.href;
                    }
                }
            });
        });

        // Smooth scrolling for navigation
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Initialize any homepage-specific functionality
        if (typeof initHomepage === 'function') {
            initHomepage();
        }
    });
    </script>
</body>
</html>

<?php
// Output the complete page
echo ob_get_clean();
?>