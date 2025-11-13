<?php
/**
 * P.I.M.P - Resources Page
 * Helpful resources for consumers and businesses
 */

use PIMP\Core\Config;
use PIMP\Views\Components;

$nav_items = [
    ['url' => '/', 'label' => 'Home', 'active' => false],
    ['url' => '/businesses', 'label' => 'Business Directory', 'active' => false],
    ['url' => '/reviews', 'label' => 'Reviews', 'active' => false],
    ['url' => '/categories', 'label' => 'Categories', 'active' => false],
    ['url' => '/scam-alerts', 'label' => 'Scam Alerts', 'active' => false],
    ['url' => '/resources', 'label' => 'Resources', 'active' => true],
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
                ['url' => '/resources/tips', 'label' => 'Consumer Tips', 'active' => true],
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
    'title' => 'Resources - P.I.M.P Business Repository',
    'metaTags' => [
        'description' => 'Helpful resources, guides, and tips for consumers and businesses on the P.I.M.P platform.',
        'keywords' => 'resources, guides, tips, consumer help, business help, PIMP resources',
        'author' => 'P.I.M.P Business Repository'
    ],
    'canonical' => Config::url('/resources'),
    'styles' => [
        'views/resources.css'
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
        'title' => 'Resources & Guides',
        'subtitle' => 'Helpful information to make the most of P.I.M.P',
        'bgImage' => Config::imageUrl('hero-bg.jpg'),
        'overlay' => 'dark',
        'size' => 'md',
        'align' => 'center'
    ]]);
    ?>

    <main class="main-content">
        <!-- Consumer Resources -->
        <section class="resources-section consumer-resources">
            <div class="container">
                <h2 class="section-title">
                    <i class="fas fa-user"></i>
                    Consumer Resources
                </h2>
                <div class="resources-grid">
                    <div class="resource-card">
                        <div class="resource-icon">
                            <i class="fas fa-search"></i>
                        </div>
                        <h3>Finding Businesses</h3>
                        <p>Learn how to search and filter businesses effectively to find exactly what you're looking for.</p>
                        <a href="<?= Config::url('/resources/finding-businesses') ?>" class="resource-link">
                            Read Guide <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>

                    <div class="resource-card">
                        <div class="resource-icon">
                            <i class="fas fa-star"></i>
                        </div>
                        <h3>Writing Reviews</h3>
                        <p>Tips for writing helpful, honest reviews that benefit other consumers and businesses.</p>
                        <a href="<?= Config::url('/resources/writing-reviews') ?>" class="resource-link">
                            Read Guide <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>

                    <div class="resource-card">
                        <div class="resource-icon">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <h3>Consumer Protection</h3>
                        <p>How to protect yourself from scams and what to do if you encounter fraudulent businesses.</p>
                        <a href="<?= Config::url('/resources/consumer-protection') ?>" class="resource-link">
                            Read Guide <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>

                    <div class="resource-card">
                        <div class="resource-icon">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <h3>Filing Complaints</h3>
                        <p>Step-by-step guide on how to file a complaint and what to expect during the resolution process.</p>
                        <a href="<?= Config::url('/resources/filing-complaints') ?>" class="resource-link">
                            Read Guide <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <!-- Business Resources -->
        <section class="resources-section business-resources">
            <div class="container">
                <h2 class="section-title">
                    <i class="fas fa-briefcase"></i>
                    Business Resources
                </h2>
                <div class="resources-grid">
                    <div class="resource-card">
                        <div class="resource-icon">
                            <i class="fas fa-building"></i>
                        </div>
                        <h3>Claiming Your Business</h3>
                        <p>Learn how to claim and verify your business profile to take control of your online presence.</p>
                        <a href="<?= Config::url('/business/claim') ?>" class="resource-link">
                            Get Started <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>

                    <div class="resource-card">
                        <div class="resource-icon">
                            <i class="fas fa-trophy"></i>
                        </div>
                        <h3>Getting Accredited</h3>
                        <p>Discover the benefits of business accreditation and how to achieve verified status.</p>
                        <a href="<?= Config::url('/resources/business-accreditation') ?>" class="resource-link">
                            Learn More <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>

                    <div class="resource-card">
                        <div class="resource-icon">
                            <i class="fas fa-comments"></i>
                        </div>
                        <h3>Managing Reviews</h3>
                        <p>Best practices for responding to reviews and building a positive online reputation.</p>
                        <a href="<?= Config::url('/resources/managing-reviews') ?>" class="resource-link">
                            Read Guide <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>

                    <div class="resource-card">
                        <div class="resource-icon">
                            <i class="fas fa-chart-bar"></i>
                        </div>
                        <h3>Business Analytics</h3>
                        <p>Understand your business analytics and use insights to improve customer satisfaction.</p>
                        <a href="<?= Config::url('/resources/business-analytics') ?>" class="resource-link">
                            Learn More <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <!-- FAQ Section -->
        <section class="resources-section faq-section">
            <div class="container">
                <h2 class="section-title text-center">Frequently Asked Questions</h2>
                <div class="faq-list">
                    <div class="faq-item">
                        <div class="faq-question">
                            <h3>How do I verify my business?</h3>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            <p>To verify your business, claim your business profile and follow the verification process. You'll need to provide business documentation and verify your contact information.</p>
                        </div>
                    </div>

                    <div class="faq-item">
                        <div class="faq-question">
                            <h3>Can I remove negative reviews?</h3>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            <p>We don't remove legitimate reviews. However, you can respond to reviews and report reviews that violate our guidelines. Reviews that are fake, defamatory, or violate our terms may be removed after review.</p>
                        </div>
                    </div>

                    <div class="faq-item">
                        <div class="faq-question">
                            <h3>How do I report a scam?</h3>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            <p>If you encounter a scam or fraudulent business, use our complaint system to file a report. Our team will investigate and take appropriate action, including posting scam alerts if necessary.</p>
                        </div>
                    </div>

                    <div class="faq-item">
                        <div class="faq-question">
                            <h3>Is P.I.M.P free to use?</h3>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            <p>Yes, P.I.M.P is free for consumers to search businesses, read reviews, and write reviews. Businesses can claim their profile for free, with optional paid features for enhanced visibility and analytics.</p>
                        </div>
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
    echo Components::call('Footers', 'documentClose', [[
        'scripts' => [
            'static/js/resources.js'
        ],
        'includeMainJs' => true
    ]]);
    ?>
</body>
</html>

<?php
echo ob_get_clean();
?>

