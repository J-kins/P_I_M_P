<?php
/**
 * P.I.M.P - Business Dashboard
 * Business control panel and management dashboard
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

// These would come from backend API
$business_data = [
    'name' => 'Business Name',
    'accredited' => false,
    'rating' => '4.5',
    'review_count' => 25,
    'response_rate' => '85%'
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
    'title' => 'Business Dashboard - P.I.M.P',
    'metaTags' => [
        'description' => 'Manage your business profile, respond to reviews, and access business tools on P.I.M.P.',
        'keywords' => 'business dashboard, review management, business tools, PIMP business'
    ],
    'styles' => [
        'views/business-dashboard.css'
    ],
    'scripts' => [
        'js/business-dashboard.js'
    ]
]]);
?>

<body class="business-dashboard">
    <?php
    echo Components::call('Headers', 'businessHeader', [[
        'logo' => Config::imageUrl('logo.png'),
        'logoAlt' => 'P.I.M.P - Business Repository Platform',
        'mainNavItems' => $nav_items,
        'userActions' => [
            ['url' => '/business/profile', 'label' => 'Business Profile'],
            ['url' => '/logout', 'label' => 'Logout', 'separator' => true],
        ],
        'showSearch' => true,
    ]]);
    ?>

    <main class="dashboard-main">
        <div class="dashboard-container">
            <!-- Sidebar -->
            <aside class="dashboard-sidebar">
                <div class="business-profile-card">
                    <div class="business-avatar">
                        <img src="<?= Config::imageUrl('businesses/default.jpg') ?>" alt="Business Logo" id="businessLogo">
                    </div>
                    <div class="business-info">
                        <h2 id="businessName"><?= htmlspecialchars($business_data['name']) ?></h2>
                        <div class="business-rating">
                            <div class="stars">
                                <?php
                                $rating = floatval($business_data['rating']);
                                for ($i = 1; $i <= 5; $i++) {
                                    if ($i <= floor($rating)) {
                                        echo '<i class="fas fa-star"></i>';
                                    } elseif ($i - 0.5 === $rating) {
                                        echo '<i class="fas fa-star-half-alt"></i>';
                                    } else {
                                        echo '<i class="far fa-star"></i>';
                                    }
                                }
                                ?>
                            </div>
                            <span class="rating-value"><?= $business_data['rating'] ?></span>
                            <span class="reviews-count">(<?= $business_data['review_count'] ?> reviews)</span>
                        </div>
                        <?php if ($business_data['accredited']): ?>
                        <div class="accreditation-badge">
                            <i class="fas fa-shield-alt"></i>
                            P.I.M.P Accredited
                        </div>
                        <?php else: ?>
                        <a href="<?= Config::url('/business/accreditation') ?>" class="get-accredited">
                            Get Accredited
                        </a>
                        <?php endif; ?>
                    </div>
                </div>

                <nav class="dashboard-nav">
                    <ul class="nav-menu">
                        <li class="nav-item active">
                            <a href="<?= Config::url('/business/dashboard') ?>" class="nav-link">
                                <i class="fas fa-tachometer-alt"></i>
                                Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= Config::url('/business/profile') ?>" class="nav-link">
                                <i class="fas fa-building"></i>
                                Business Profile
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= Config::url('/business/reviews') ?>" class="nav-link">
                                <i class="fas fa-star"></i>
                                Reviews
                                <span class="nav-badge" id="pendingReviews">0</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= Config::url('/business/analytics') ?>" class="nav-link">
                                <i class="fas fa-chart-bar"></i>
                                Analytics
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= Config::url('/business/accreditation') ?>" class="nav-link">
                                <i class="fas fa-shield-alt"></i>
                                Accreditation
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= Config::url('/business/settings') ?>" class="nav-link">
                                <i class="fas fa-cog"></i>
                                Settings
                            </a>
                        </li>
                    </ul>
                </nav>
            </aside>

            <!-- Main Content -->
            <div class="dashboard-content">
                <!-- Welcome Section -->
                <section class="welcome-section">
                    <h1>Business Dashboard</h1>
                    <p>Welcome back! Here's your business overview.</p>
                </section>

                <!-- Quick Stats -->
                <section class="stats-section">
                    <div class="stats-grid">
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-eye"></i>
                            </div>
                            <div class="stat-content">
                                <div class="stat-number" id="profileViews">0</div>
                                <div class="stat-label">Profile Views</div>
                                <div class="stat-trend positive" id="viewsTrend">
                                    <i class="fas fa-arrow-up"></i>
                                    <span>12%</span>
                                </div>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-star"></i>
                            </div>
                            <div class="stat-content">
                                <div class="stat-number" id="totalReviews"><?= $business_data['review_count'] ?></div>
                                <div class="stat-label">Total Reviews</div>
                                <div class="stat-trend positive" id="reviewsTrend">
                                    <i class="fas fa-arrow-up"></i>
                                    <span>5%</span>
                                </div>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-comments"></i>
                            </div>
                            <div class="stat-content">
                                <div class="stat-number" id="responseRate"><?= $business_data['response_rate'] ?></div>
                                <div class="stat-label">Response Rate</div>
                                <div class="stat-trend neutral" id="responseTrend">
                                    <i class="fas fa-minus"></i>
                                    <span>0%</span>
                                </div>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-share-alt"></i>
                            </div>
                            <div class="stat-content">
                                <div class="stat-number" id="socialMentions">0</div>
                                <div class="stat-label">Social Mentions</div>
                                <div class="stat-trend positive" id="mentionsTrend">
                                    <i class="fas fa-arrow-up"></i>
                                    <span>8%</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Action Cards -->
                <section class="actions-section">
                    <h2>Quick Actions</h2>
                    <div class="actions-grid">
                        <a href="<?= Config::url('/business/reviews') ?>" class="action-card">
                            <div class="action-icon pending">
                                <i class="fas fa-inbox"></i>
                            </div>
                            <div class="action-content">
                                <h3>Respond to Reviews</h3>
                                <p><span id="unansweredReviews">0</span> reviews need your response</p>
                            </div>
                            <div class="action-arrow">
                                <i class="fas fa-chevron-right"></i>
                            </div>
                        </a>
                        <a href="<?= Config::url('/business/profile') ?>" class="action-card">
                            <div class="action-icon update">
                                <i class="fas fa-edit"></i>
                            </div>
                            <div class="action-content">
                                <h3>Update Profile</h3>
                                <p>Keep your business information current</p>
                            </div>
                            <div class="action-arrow">
                                <i class="fas fa-chevron-right"></i>
                            </div>
                        </a>
                        <a href="<?= Config::url('/business/accreditation') ?>" class="action-card">
                            <div class="action-icon accredited">
                                <i class="fas fa-shield-alt"></i>
                            </div>
                            <div class="action-content">
                                <h3>Get Accredited</h3>
                                <p>Build trust with P.I.M.P accreditation</p>
                            </div>
                            <div class="action-arrow">
                                <i class="fas fa-chevron-right"></i>
                            </div>
                        </a>
                        <a href="<?= Config::url('/business/analytics') ?>" class="action-card">
                            <div class="action-icon analytics">
                                <i class="fas fa-chart-line"></i>
                            </div>
                            <div class="action-content">
                                <h3>View Analytics</h3>
                                <p>See detailed performance insights</p>
                            </div>
                            <div class="action-arrow">
                                <i class="fas fa-chevron-right"></i>
                            </div>
                        </a>
                    </div>
                </section>

                <!-- Recent Reviews -->
                <section class="reviews-section">
                    <div class="section-header">
                        <h2>Recent Reviews</h2>
                        <a href="<?= Config::url('/business/reviews') ?>" class="view-all">View All Reviews</a>
                    </div>
                    <div class="reviews-list" id="recentReviews">
                        <!-- Reviews will be populated by JavaScript -->
                        <div class="empty-state">
                            <i class="fas fa-star"></i>
                            <h3>No reviews yet</h3>
                            <p>Customer reviews will appear here</p>
                        </div>
                    </div>
                </section>

                <!-- Accreditation Status -->
                <section class="accreditation-section">
                    <div class="accreditation-card">
                        <div class="accreditation-header">
                            <h3>P.I.M.P Accreditation</h3>
                            <?php if ($business_data['accredited']): ?>
                            <span class="status-badge accredited">Accredited</span>
                            <?php else: ?>
                            <span class="status-badge not-accredited">Not Accredited</span>
                            <?php endif; ?>
                        </div>
                        <div class="accreditation-content">
                            <?php if ($business_data['accredited']): ?>
                            <p>Your business is P.I.M.P accredited! This helps build trust with customers.</p>
                            <div class="accreditation-benefits">
                                <div class="benefit">
                                    <i class="fas fa-check"></i>
                                    <span>Trust badge displayed on profile</span>
                                </div>
                                <div class="benefit">
                                    <i class="fas fa-check"></i>
                                    <span>Higher search ranking</span>
                                </div>
                                <div class="benefit">
                                    <i class="fas fa-check"></i>
                                    <span>Customer confidence boost</span>
                                </div>
                            </div>
                            <?php else: ?>
                            <p>Get P.I.M.P accredited to build trust and stand out to customers.</p>
                            <ul class="accreditation-requirements">
                                <li>Verified business information</li>
                                <li>Good standing with customers</li>
                                <li>Professional response to feedback</li>
                                <li>Transparent business practices</li>
                            </ul>
                            <div class="accreditation-actions">
                                <a href="<?= Config::url('/business/accreditation') ?>" class="button button-primary">
                                    Learn More
                                </a>
                                <a href="<?= Config::url('/business/apply') ?>" class="button button-outline">
                                    Apply Now
                                </a>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </main>

    <?php
    echo Components::call('Footers', 'businessFooter', [$footer_config]);
    echo Components::call('Footers', 'documentClose');
    ?>

    <script>
    // Business dashboard data initialization
    const BUSINESS_DATA = <?= json_encode($business_data) ?>;

    document.addEventListener('DOMContentLoaded', function() {
        initializeBusinessDashboard();
        loadBusinessData();
    });

    function initializeBusinessDashboard() {
        // Navigation active state
        const navItems = document.querySelectorAll('.nav-item');
        navItems.forEach(item => {
            item.addEventListener('click', function() {
                navItems.forEach(i => i.classList.remove('active'));
                this.classList.add('active');
            });
        });
    }

    async function loadBusinessData() {
        try {
            // Show loading states
            showLoadingStates();

            // In a real implementation, this would be an API call
            // const response = await fetch('/api/business/dashboard');
            // const data = await response.json();
            
            // Simulate API call
            setTimeout(() => {
                const data = {
                    ...BUSINESS_DATA,
                    stats: {
                        profileViews: 1247,
                        totalReviews: BUSINESS_DATA.review_count,
                        responseRate: BUSINESS_DATA.response_rate,
                        socialMentions: 23,
                        viewsTrend: 12,
                        reviewsTrend: 5,
                        responseTrend: 0,
                        mentionsTrend: 8
                    },
                    pendingReviews: 3,
                    unansweredReviews: 2,
                    recentReviews: []
                };
                
                updateDashboardUI(data);
            }, 1000);

        } catch (error) {
            console.error('Error loading business data:', error);
            showErrorState();
        }
    }

    function showLoadingStates() {
        // Add loading animation to stat cards
        const statNumbers = document.querySelectorAll('.stat-number');
        statNumbers.forEach(number => {
            if (!number.textContent || number.textContent === '0') {
                number.textContent = '...';
            }
        });
    }

    function updateDashboardUI(data) {
        // Update stats
        if (data.stats) {
            document.getElementById('profileViews').textContent = data.stats.profileViews.toLocaleString();
            document.getElementById('socialMentions').textContent = data.stats.socialMentions;
            
            // Update trends
            updateTrend('viewsTrend', data.stats.viewsTrend);
            updateTrend('reviewsTrend', data.stats.reviewsTrend);
            updateTrend('responseTrend', data.stats.responseTrend);
            updateTrend('mentionsTrend', data.stats.mentionsTrend);
        }

        // Update pending reviews
        if (data.pendingReviews !== undefined) {
            document.getElementById('pendingReviews').textContent = data.pendingReviews;
        }

        // Update unanswered reviews
        if (data.unansweredReviews !== undefined) {
            document.getElementById('unansweredReviews').textContent = data.unansweredReviews;
        }

        // Update recent reviews
        updateRecentReviews(data.recentReviews || []);
    }

    function updateTrend(elementId, trendValue) {
        const trendElement = document.getElementById(elementId);
        if (!trendElement) return;

        const trendClass = trendValue > 0 ? 'positive' : trendValue < 0 ? 'negative' : 'neutral';
        const trendIcon = trendValue > 0 ? 'fa-arrow-up' : trendValue < 0 ? 'fa-arrow-down' : 'fa-minus';

        trendElement.className = `stat-trend ${trendClass}`;
        trendElement.innerHTML = `<i class="fas ${trendIcon}"></i><span>${Math.abs(trendValue)}%</span>`;
    }

    function updateRecentReviews(reviews) {
        const reviewsList = document.getElementById('recentReviews');
        
        if (reviews.length === 0) {
            return; // Keep the empty state
        }

        reviewsList.innerHTML = reviews.map(review => `
            <div class="review-item">
                <div class="review-header">
                    <div class="reviewer-info">
                        <span class="reviewer-name">${review.customerName}</span>
                        <div class="review-rating">
                            ${generateStars(review.rating)}
                        </div>
                    </div>
                    <span class="review-date">${review.date}</span>
                </div>
                <p class="review-content">${review.content}</p>
                <div class="review-actions">
                    ${review.responded ? 
                        '<span class="response-status responded">Responded</span>' :
                        '<button class="button button-small button-outline respond-btn">Respond</button>'
                    }
                    <button class="button button-small report-btn">Report</button>
                </div>
            </div>
        `).join('');

        // Add event listeners for review actions
        document.querySelectorAll('.respond-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                // Implement respond functionality
                alert('Respond to review functionality would be implemented here');
            });
        });

        document.querySelectorAll('.report-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                // Implement report functionality
                alert('Report review functionality would be implemented here');
            });
        });
    }

    function generateStars(rating) {
        let stars = '';
        for (let i = 1; i <= 5; i++) {
            if (i <= rating) {
                stars += '<i class="fas fa-star"></i>';
            } else if (i - 0.5 === rating) {
                stars += '<i class="fas fa-star-half-alt"></i>';
            } else {
                stars += '<i class="far fa-star"></i>';
            }
        }
        return stars;
    }

    function showErrorState() {
        // Show error message to user
        const reviewsList = document.getElementById('recentReviews');
        reviewsList.innerHTML = `
            <div class="error-state">
                <i class="fas fa-exclamation-triangle"></i>
                <h3>Unable to load dashboard data</h3>
                <p>Please try refreshing the page</p>
            </div>
        `;
    }
    </script>
</body>
</html>

<?php echo ob_get_clean(); ?>