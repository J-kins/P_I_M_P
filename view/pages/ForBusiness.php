<?php
/**
 * P.I.M.P - For Business
 * Business portal landing page
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

ob_start();
?>

<?php
echo Components::call('Headers', 'documentHead', [[
    'title' => 'For Business - P.I.M.P Business Repository',
    'metaTags' => [
        'description' => 'Grow your business with P.I.M.P. Claim your business profile, respond to reviews, and get accredited to build trust with customers.',
        'keywords' => 'business portal, claim business, business accreditation, review management'
    ],
    'styles' => [
        'views/for-business.css'
    ],
    'scripts' => [
        'js/for-business.js'
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
            ['url' => '/login', 'label' => 'Business Login'],
            ['url' => '/business/claim', 'label' => 'Claim Your Business', 'separator' => true],
        ],
        'showSearch' => true,
    ]]);
    ?>

    <main class="for-business-main">
        <!-- Hero Section -->
        <section class="business-hero">
            <div class="container">
                <div class="hero-content">
                    <h1>Grow Your Business with P.I.M.P</h1>
                    <p class="hero-subtitle">Connect with customers, build trust, and manage your online reputation with our comprehensive business tools.</p>
                    <div class="hero-actions">
                        <a href="<?= Config::url('/business/claim') ?>" class="button button-primary button-large">
                            Claim Your Business
                        </a>
                        <a href="<?= Config::url('/business/login') ?>" class="button button-outline button-large">
                            Business Login
                        </a>
                    </div>
                </div>
                <div class="hero-stats">
                    <div class="stat">
                        <div class="stat-number">10,000+</div>
                        <div class="stat-label">Business Partners</div>
                    </div>
                    <div class="stat">
                        <div class="stat-number">50M+</div>
                        <div class="stat-label">Monthly Visitors</div>
                    </div>
                    <div class="stat">
                        <div class="stat-number">98%</div>
                        <div class="stat-label">Customer Satisfaction</div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Features Section -->
        <section class="features-section">
            <div class="container">
                <h2 class="section-title">Why Choose P.I.M.P for Your Business?</h2>
                <div class="features-grid">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <h3>P.I.M.P Accreditation</h3>
                        <p>Show customers you meet high standards of trust and reliability with our accreditation program.</p>
                        <ul class="feature-benefits">
                            <li><i class="fas fa-check"></i> Build customer confidence</li>
                            <li><i class="fas fa-check"></i> Stand out from competitors</li>
                            <li><i class="fas fa-check"></i> Display trust badges</li>
                        </ul>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-comments"></i>
                        </div>
                        <h3>Review Management</h3>
                        <p>Respond to reviews, address customer concerns, and showcase your commitment to service.</p>
                        <ul class="feature-benefits">
                            <li><i class="fas fa-check"></i> Respond to all reviews</li>
                            <li><i class="fas fa-check"></i> Monitor customer feedback</li>
                            <li><i class="fas fa-check"></i> Improve your services</li>
                        </ul>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <h3>Business Insights</h3>
                        <p>Get valuable analytics about your profile views, customer engagement, and market trends.</p>
                        <ul class="feature-benefits">
                            <li><i class="fas fa-check"></i> Track profile performance</li>
                            <li><i class="fas fa-check"></i> Understand customer needs</li>
                            <li><i class="fas fa-check"></i> Make data-driven decisions</li>
                        </ul>
                    </div>
                </div>
            </div>
        </section>

        <!-- How It Works -->
        <section class="how-it-works">
            <div class="container">
                <h2 class="section-title">Get Started in 3 Easy Steps</h2>
                <div class="steps-container">
                    <div class="step">
                        <div class="step-number">1</div>
                        <div class="step-content">
                            <h3>Claim Your Business</h3>
                            <p>Verify ownership and claim your business profile to unlock management features.</p>
                            <a href="<?= Config::url('/business/claim') ?>" class="step-link">Start Claim Process →</a>
                        </div>
                    </div>
                    <div class="step">
                        <div class="step-number">2</div>
                        <div class="step-content">
                            <h3>Complete Your Profile</h3>
                            <p>Add photos, update business information, and showcase your services.</p>
                            <a href="<?= Config::url('/business/resources') ?>" class="step-link">View Profile Tips →</a>
                        </div>
                    </div>
                    <div class="step">
                        <div class="step-number">3</div>
                        <div class="step-content">
                            <h3>Get Accredited</h3>
                            <p>Apply for P.I.M.P accreditation to build trust and stand out to customers.</p>
                            <a href="<?= Config::url('/business/accreditation') ?>" class="step-link">Learn About Accreditation →</a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Accreditation Benefits -->
        <section class="accreditation-section">
            <div class="container">
                <div class="accreditation-content">
                    <div class="accreditation-text">
                        <h2>P.I.M.P Accreditation</h2>
                        <p class="accreditation-subtitle">The mark of trust that customers look for</p>
                        <div class="accreditation-benefits">
                            <div class="benefit">
                                <i class="fas fa-check-circle"></i>
                                <span>Verified business information</span>
                            </div>
                            <div class="benefit">
                                <i class="fas fa-check-circle"></i>
                                <span>Commitment to customer service</span>
                            </div>
                            <div class="benefit">
                                <i class="fas fa-check-circle"></i>
                                <span>Address complaints professionally</span>
                            </div>
                            <div class="benefit">
                                <i class="fas fa-check-circle"></i>
                                <span>Transparent business practices</span>
                            </div>
                        </div>
                        <div class="accreditation-actions">
                            <a href="<?= Config::url('/business/accreditation') ?>" class="button button-primary">
                                Learn About Accreditation
                            </a>
                            <a href="<?= Config::url('/business/apply') ?>" class="button button-outline">
                                Apply Now
                            </a>
                        </div>
                    </div>
                    <div class="accreditation-badge">
                        <div class="badge-display">
                            <i class="fas fa-shield-alt"></i>
                            <span>P.I.M.P Accredited Business</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Testimonials -->
        <section class="testimonials-section">
            <div class="container">
                <h2 class="section-title">Trusted by Businesses Like Yours</h2>
                <div class="testimonials-grid">
                    <div class="testimonial-card">
                        <div class="testimonial-content">
                            <p>"P.I.M.P accreditation helped us build trust with new customers. Our inquiries increased by 40% after getting accredited."</p>
                        </div>
                        <div class="testimonial-author">
                            <div class="author-avatar">
                                <img src="<?= Config::imageUrl('testimonials/business-1.jpg') ?>" alt="Sarah Johnson">
                            </div>
                            <div class="author-info">
                                <h4>Sarah Johnson</h4>
                                <p>Owner, Quality Home Services</p>
                            </div>
                        </div>
                    </div>
                    <div class="testimonial-card">
                        <div class="testimonial-content">
                            <p>"The review management tools are fantastic. We can quickly address customer concerns and showcase our commitment to service."</p>
                        </div>
                        <div class="testimonial-author">
                            <div class="author-avatar">
                                <img src="<?= Config::imageUrl('testimonials/business-2.jpg') ?>" alt="Mike Chen">
                            </div>
                            <div class="author-info">
                                <h4>Mike Chen</h4>
                                <p>Manager, City Dental Clinic</p>
                            </div>
                        </div>
                    </div>
                    <div class="testimonial-card">
                        <div class="testimonial-content">
                            <p>"The analytics helped us understand what customers value most. We've improved our services based on the insights."</p>
                        </div>
                        <div class="testimonial-author">
                            <div class="author-avatar">
                                <img src="<?= Config::imageUrl('testimonials/business-3.jpg') ?>" alt="Emily Rodriguez">
                            </div>
                            <div class="author-info">
                                <h4>Emily Rodriguez</h4>
                                <p>Director, Reliable Auto Care</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA Section -->
        <section class="cta-section">
            <div class="container">
                <div class="cta-content">
                    <h2>Ready to Grow Your Business?</h2>
                    <p>Join thousands of businesses already using P.I.M.P to connect with customers and build trust.</p>
                    <div class="cta-actions">
                        <a href="<?= Config::url('/business/claim') ?>" class="button button-primary button-large">
                            Claim Your Business Today
                        </a>
                        <a href="<?= Config::url('/business/contact') ?>" class="button button-outline button-large">
                            Contact Sales
                        </a>
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