<?php
/**
 * P.I.M.P - Scam Alerts Page
 * Information about scams and fraudulent businesses
 */

use PIMP\Core\Config;
use PIMP\Views\Components;
use PIMP\Services\DatabaseFactory;
use PIMP\Services\API\ComplaintAPIService;

// Initialize database connection
$db = null;
$complaintAPI = null;

try {
    $db = DatabaseFactory::default();
    if ($db) {
        try {
            $complaintAPI = new ComplaintAPIService($db);
        } catch (\Exception $e) {
            error_log("API service initialization error: " . $e->getMessage());
        }
    }
} catch (\Exception $e) {
    error_log("Database connection error: " . $e->getMessage());
    $db = null;
}

// Fetch scam alerts
$scam_alerts = [];
$filters = ['status' => 'active', 'type' => 'scam'];

try {
    if ($complaintAPI && $db) {
        // Get recent scam alerts
        $alertsQuery = "
            SELECT c.*, b.business_name, b.trading_name 
            FROM complaints c
            LEFT JOIN business_profiles b ON c.business_id = b.id
            WHERE c.status = 'active' 
            AND c.complaint_type = 'scam'
            ORDER BY c.created_at DESC
            LIMIT 20
        ";
        $scam_alerts = $db->fetchAll($alertsQuery) ?: [];
    }
} catch (\Exception $e) {
    error_log("Error fetching scam alerts: " . $e->getMessage());
}

$nav_items = [
    ['url' => '/', 'label' => 'Home', 'active' => false],
    ['url' => '/businesses', 'label' => 'Business Directory', 'active' => false],
    ['url' => '/reviews', 'label' => 'Reviews', 'active' => false],
    ['url' => '/categories', 'label' => 'Categories', 'active' => false],
    ['url' => '/scam-alerts', 'label' => 'Scam Alerts', 'active' => true],
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
                ['url' => '/scam-alerts', 'label' => 'Scam Alerts', 'active' => true],
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
    'theme' => 'light'
];

ob_start();
?>

<?php
echo Components::call('Headers', 'documentHead', [[
    'title' => 'Scam Alerts - P.I.M.P Business Repository',
    'metaTags' => [
        'description' => 'Stay informed about scams and fraudulent businesses. Report suspicious activity and protect yourself from fraud.',
        'keywords' => 'scam alerts, fraud, consumer protection, scam reports, fraudulent businesses',
        'author' => 'P.I.M.P Business Repository'
    ],
    'canonical' => Config::url('/scam-alerts'),
    'styles' => [
        'views/scam-alerts.css'
    ],
    'scripts' => [
        'static/js/scam-alerts.js'
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
        'title' => 'Scam Alerts',
        'subtitle' => 'Stay informed and protect yourself from fraudulent businesses',
        'bgImage' => Config::imageUrl('hero-bg.jpg'),
        'overlay' => 'dark',
        'size' => 'md',
        'align' => 'center'
    ]]);
    ?>

    <main class="main-content">
        <!-- Warning Banner -->
        <section class="warning-banner">
            <div class="container">
                <div class="warning-content">
                    <div class="warning-icon">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div class="warning-text">
                        <h3>Report Suspicious Activity</h3>
                        <p>If you encounter a scam or fraudulent business, report it immediately. Your report helps protect others in our community.</p>
                        <a href="<?= Config::url('/complaints/new') ?>" class="button button-primary">
                            <i class="fas fa-flag"></i>
                            Report a Scam
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <!-- Prevention Tips -->
        <section class="prevention-section">
            <div class="container">
                <h2 class="section-title">How to Protect Yourself</h2>
                <div class="tips-grid">
                    <div class="tip-card">
                        <div class="tip-icon">
                            <i class="fas fa-search"></i>
                        </div>
                        <h3>Research Before You Buy</h3>
                        <p>Always check business reviews, ratings, and accreditation status before making a purchase or committing to a service.</p>
                    </div>

                    <div class="tip-card">
                        <div class="tip-icon">
                            <i class="fas fa-credit-card"></i>
                        </div>
                        <h3>Use Secure Payment Methods</h3>
                        <p>Use credit cards or secure payment platforms that offer buyer protection. Avoid wire transfers or gift cards for payments.</p>
                    </div>

                    <div class="tip-card">
                        <div class="tip-icon">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <h3>Verify Business Information</h3>
                        <p>Check that the business has a physical address, valid contact information, and proper licensing or accreditation.</p>
                    </div>

                    <div class="tip-card">
                        <div class="tip-icon">
                            <i class="fas fa-exclamation-circle"></i>
                        </div>
                        <h3>Watch for Red Flags</h3>
                        <p>Be wary of businesses that pressure you to act quickly, ask for payment upfront, or refuse to provide written contracts.</p>
                    </div>

                    <div class="tip-card">
                        <div class="tip-icon">
                            <i class="fas fa-file-contract"></i>
                        </div>
                        <h3>Get Everything in Writing</h3>
                        <p>Always get contracts, receipts, and agreements in writing. Verbal promises are difficult to enforce.</p>
                    </div>

                    <div class="tip-card">
                        <div class="tip-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <h3>Trust Your Instincts</h3>
                        <p>If something seems too good to be true, it probably is. Trust your instincts and walk away if you feel uncomfortable.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Recent Scam Alerts -->
        <section class="alerts-section">
            <div class="container">
                <h2 class="section-title">Recent Scam Alerts</h2>
                
                <?php if (!empty($scam_alerts)): ?>
                <div class="alerts-list">
                    <?php foreach ($scam_alerts as $alert): ?>
                    <div class="alert-card">
                        <div class="alert-header">
                            <div class="alert-badge">
                                <i class="fas fa-exclamation-triangle"></i>
                                Scam Alert
                            </div>
                            <div class="alert-date">
                                <?= date('M d, Y', strtotime($alert['created_at'] ?? 'now')) ?>
                            </div>
                        </div>
                        <div class="alert-content">
                            <h3><?= htmlspecialchars($alert['business_name'] ?? $alert['trading_name'] ?? 'Unknown Business') ?></h3>
                            <p><?= htmlspecialchars($alert['description'] ?? $alert['complaint_text'] ?? 'No description available') ?></p>
                            <?php if (!empty($alert['location'])): ?>
                            <div class="alert-location">
                                <i class="fas fa-map-marker-alt"></i>
                                <?= htmlspecialchars($alert['location']) ?>
                            </div>
                            <?php endif; ?>
                        </div>
                        <div class="alert-actions">
                            <a href="<?= Config::url('/complaints/' . ($alert['id'] ?? '')) ?>" class="alert-link">
                                View Details <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <div class="no-alerts">
                    <i class="fas fa-check-circle"></i>
                    <h3>No Active Scam Alerts</h3>
                    <p>There are currently no active scam alerts. Stay vigilant and report any suspicious activity you encounter.</p>
                </div>
                <?php endif; ?>
            </div>
        </section>

        <!-- Report Section -->
        <section class="report-section">
            <div class="container">
                <div class="report-content">
                    <h2>Have You Been Scammed?</h2>
                    <p>If you've been a victim of fraud or encountered a scam, we're here to help. Report it to protect others and get assistance.</p>
                    <div class="report-buttons">
                        <a href="<?= Config::url('/complaints/new') ?>" class="button button-primary">
                            <i class="fas fa-flag"></i>
                            File a Complaint
                        </a>
                        <a href="<?= Config::url('/resources/consumer-protection') ?>" class="button button-outline">
                            <i class="fas fa-book"></i>
                            Learn More
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

