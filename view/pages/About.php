<?php
/**
 * P.I.M.P - About Page
 * Information about the P.I.M.P Business Repository Platform
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
                ['url' => '/about', 'label' => 'About Us', 'active' => true],
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

ob_start();
?>

<?php
echo Components::call('Headers', 'documentHead', [[
    'title' => 'About Us - P.I.M.P Business Repository',
    'metaTags' => [
        'description' => 'Learn about P.I.M.P Business Repository - our mission to connect consumers with trusted businesses through verified reviews and comprehensive business information.',
        'keywords' => 'about PIMP, business directory, trusted reviews, company information',
        'author' => 'P.I.M.P Business Repository'
    ],
    'canonical' => Config::url('/about'),
    'styles' => [
        'views/about.css'
    ]
]]);
?>

<body>
    <?php
    echo Components::call('Headers', 'businessHeader', [[
        'logo' => Config::imageUrl('logo.png'),
        'logoAlt' => 'P.I.M.P - Business Repository Platform',
        'topBarItems' => [
            ['url' => '/about', 'label' => 'About PIMP', 'active' => true],
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
        'showSearch' => true,
        'showPhone' => true,
    ]]);
    ?>

    <!-- Hero Section -->
    <?php
    echo Components::call('Headers', 'heroHeader', [[
        'title' => 'About P.I.M.P',
        'subtitle' => 'Connecting consumers with trusted businesses through verified reviews and comprehensive information',
        'bgImage' => Config::imageUrl('hero-bg.jpg'),
        'overlay' => 'dark',
        'size' => 'md',
        'align' => 'center'
    ]]);
    ?>

    <main class="main-content">
        <!-- Mission Section -->
        <section class="about-section mission-section">
            <div class="container">
                <div class="section-content">
                    <h2>Our Mission</h2>
                    <p class="lead">
                        P.I.M.P Business Repository is dedicated to creating a trusted platform where consumers can find reliable businesses 
                        and make informed decisions. We believe in transparency, accountability, and the power of genuine customer feedback.
                    </p>
                    <p>
                        Our mission is to bridge the gap between businesses and consumers by providing a comprehensive directory 
                        that includes verified reviews, business accreditations, and detailed information to help people make 
                        confident choices about the services they use.
                    </p>
                </div>
            </div>
        </section>

        <!-- Values Section -->
        <section class="about-section values-section">
            <div class="container">
                <h2 class="section-title text-center">Our Core Values</h2>
                <div class="values-grid">
                    <div class="value-card">
                        <div class="value-icon">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <h3>Trust & Transparency</h3>
                        <p>We verify business information and reviews to ensure authenticity and build trust between consumers and businesses.</p>
                    </div>
                    <div class="value-card">
                        <div class="value-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <h3>Consumer Protection</h3>
                        <p>We prioritize consumer safety by providing scam alerts, complaint resolution, and verified business information.</p>
                    </div>
                    <div class="value-card">
                        <div class="value-icon">
                            <i class="fas fa-balance-scale"></i>
                        </div>
                        <h3>Fair & Balanced</h3>
                        <p>We provide a platform where both positive and negative reviews are welcome, ensuring balanced and honest feedback.</p>
                    </div>
                    <div class="value-card">
                        <div class="value-icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <h3>Continuous Improvement</h3>
                        <p>We constantly enhance our platform based on user feedback and industry best practices to serve you better.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- What We Do Section -->
        <section class="about-section what-we-do-section">
            <div class="container">
                <h2 class="section-title text-center">What We Do</h2>
                <div class="features-list">
                    <div class="feature-item">
                        <div class="feature-number">01</div>
                        <div class="feature-content">
                            <h3>Business Directory</h3>
                            <p>We maintain a comprehensive directory of businesses across various categories, complete with contact information, hours, and location details.</p>
                        </div>
                    </div>
                    <div class="feature-item">
                        <div class="feature-number">02</div>
                        <div class="feature-content">
                            <h3>Verified Reviews</h3>
                            <p>Our review system ensures that only genuine customer experiences are published, helping consumers make informed decisions.</p>
                        </div>
                    </div>
                    <div class="feature-item">
                        <div class="feature-number">03</div>
                        <div class="feature-content">
                            <h3>Business Accreditation</h3>
                            <p>We offer accreditation programs that help businesses demonstrate their commitment to quality and customer satisfaction.</p>
                        </div>
                    </div>
                    <div class="feature-item">
                        <div class="feature-number">04</div>
                        <div class="feature-content">
                            <h3>Complaint Resolution</h3>
                            <p>We provide a platform for consumers to file complaints and work with businesses to resolve issues fairly and transparently.</p>
                        </div>
                    </div>
                    <div class="feature-item">
                        <div class="feature-number">05</div>
                        <div class="feature-content">
                            <h3>Scam Alerts</h3>
                            <p>We actively monitor and alert consumers about potential scams and fraudulent businesses to protect the community.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Stats Section -->
        <section class="about-section stats-section">
            <div class="container">
                <div class="stats-grid">
                    <div class="stat-item">
                        <div class="stat-number">10,000+</div>
                        <div class="stat-label">Business Listings</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number">50,000+</div>
                        <div class="stat-label">Customer Reviews</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number">500+</div>
                        <div class="stat-label">Cities Covered</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number">99%</div>
                        <div class="stat-label">Satisfied Users</div>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA Section -->
        <section class="about-section cta-section">
            <div class="container">
                <div class="cta-content text-center">
                    <h2>Join Our Community</h2>
                    <p>Whether you're a consumer looking for trusted businesses or a business owner wanting to connect with customers, P.I.M.P is here for you.</p>
                    <div class="cta-buttons">
                        <a href="<?= Config::url('/register') ?>" class="button button-primary">
                            Get Started
                        </a>
                        <a href="<?= Config::url('/contact') ?>" class="button button-outline">
                            Contact Us
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
    echo Components::call('Footers', 'documentClose', [[
        'includeMainJs' => true
    ]]);
    ?>
</body>
</html>

<?php
echo ob_get_clean();
?>

