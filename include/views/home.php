<?php
/**
 * Homepage template for PHP UI Template System - BBB Style
 */

// Load necessary components
$nav_items = [
    ['url' => '/', 'label' => 'Home', 'active' => true],
    ['url' => 'business-profiles', 'label' => 'Business Profiles', 'active' => false],
    ['url' => 'complaints', 'label' => 'Complaints', 'active' => false],
    ['url' => 'reviews', 'label' => 'Reviews', 'active' => false],
    ['url' => 'scam-tracker', 'label' => 'Scam Tracker', 'active' => false],
    ['url' => 'consumer-resources', 'label' => 'Consumer Resources', 'active' => false],
];

$footer_items = [
    ['url' => 'about', 'label' => 'About BBB'],
    ['url' => 'privacy', 'label' => 'Privacy Policy'],
    ['url' => 'terms', 'label' => 'Terms of Service'],
    ['url' => 'contact', 'label' => 'Contact Us'],
];

// Sample categories for the homepage
$categories = [
    ['name' => 'Contractors', 'url' => '/category/contractors', 'count' => 1250, 'icon' => '🏗️'],
    ['name' => 'Automotive', 'url' => '/category/automotive', 'count' => 890, 'icon' => '🚗'],
    ['name' => 'Home Services', 'url' => '/category/home-services', 'count' => 1560, 'icon' => '🏠'],
    ['name' => 'Healthcare', 'url' => '/category/healthcare', 'count' => 720, 'icon' => '🏥'],
    ['name' => 'Legal Services', 'url' => '/category/legal', 'count' => 430, 'icon' => '⚖️'],
    ['name' => 'Restaurants', 'url' => '/category/restaurants', 'count' => 2100, 'icon' => '🍽️'],
];

// Sample featured businesses
$featured_businesses = [
    [
        'name' => 'Quality Home Services LLC',
        'rating' => 'A+',
        'accredited' => true,
        'address' => '123 Main St, Anytown, ST 12345',
        'phone' => '(555) 123-4567',
        'website' => 'https://qualityhomeservices.com',
        'reviews' => 47,
        'complaints' => 2,
        'categories' => ['Contractors', 'Home Repair']
    ],
    [
        'name' => 'Reliable Auto Care',
        'rating' => 'A',
        'accredited' => true,
        'address' => '456 Oak Ave, Somewhere, ST 67890',
        'phone' => '(555) 987-6543',
        'reviews' => 89,
        'complaints' => 5,
        'categories' => ['Automotive', 'Auto Repair']
    ]
];

// Output document head
echo document_head([
    'title' => 'Better Business Bureau - Find Trusted Businesses',
    'metaTags' => [
        'description' => 'Check BBB ratings and reviews, file complaints, and find BBB accredited businesses near you.',
        'keywords' => 'BBB, business ratings, customer reviews, file complaint, accredited businesses'
    ]
]);
?>

<body>
    <?php
    // Output BBB header
    echo bbb_header(bbb_default_config());
    ?>

    <main class="bbb-main-content">
        <?php
        // Hero search section
        echo bbb_hero_search([
            'title' => 'Find Trusted Businesses',
            'subtitle' => 'Check reviews, complaints, and BBB ratings before you buy'
        ]);
        ?>
        
        <?php
        // Category grid
        echo bbb_category_grid($categories, 'Popular Business Categories');
        ?>
        
        <section class="bbb-featured-businesses">
            <div class="bbb-container">
                <h2 class="bbb-section-title">Featured BBB Accredited Businesses</h2>
                <div class="bbb-businesses-grid">
                    <?php foreach ($featured_businesses as $business): ?>
                        <?= bbb_business_card($business) ?>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
        
        <section class="bbb-cta-section">
            <div class="bbb-container">
                <div class="bbb-cta-content">
                    <h2>Are You a Business Owner?</h2>
                    <p>Join thousands of trusted businesses with BBB Accreditation</p>
                    <div class="bbb-cta-buttons">
                        <a href="/business-accreditation" class="bbb-cta-button bbb-cta-primary">Learn About Accreditation</a>
                        <a href="/business-login" class="bbb-cta-button">Business Login</a>
                    </div>
                </div>
            </div>
        </section>
    </main>
    
    <?php
    // Output footer
    echo multiColumnFooter([
        'copyright' => '© ' . date('Y') . ' Better Business Bureau. All rights reserved.',
        'columns' => [
            [
                'heading' => 'For Consumers',
                'links' => [
                    ['url' => '/business-profiles', 'label' => 'Find Businesses'],
                    ['url' => '/file-complaint', 'label' => 'File a Complaint'],
                    ['url' => '/write-review', 'label' => 'Write a Review'],
                    ['url' => '/scam-tracker', 'label' => 'Scam Tracker'],
                ]
            ],
            [
                'heading' => 'For Businesses',
                'links' => [
                    ['url' => '/accreditation', 'label' => 'BBB Accreditation'],
                    ['url' => '/business-resources', 'label' => 'Business Resources'],
                    ['url' => '/advertise', 'label' => 'Advertise with BBB'],
                    ['url' => '/business-login', 'label' => 'Business Login'],
                ]
            ],
            [
                'heading' => 'About BBB',
                'links' => [
                    ['url' => '/about', 'label' => 'About Us'],
                    ['url' => '/news', 'label' => 'News & Events'],
                    ['url' => '/careers', 'label' => 'Careers'],
                    ['url' => '/contact', 'label' => 'Contact Us'],
                ]
            ]
        ]
    ]);
    
    // Close document
    echo documentClose([
        'scripts' => ['js/bbb-components.js']
    ]);
    ?>

    
</body>
