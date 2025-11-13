<?php
/**
 * P.I.M.P - User Dashboard
 * Generic dashboard that can be populated with backend data
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
$user_data = [
    'name' => 'User Name', // Will be populated by backend
    'member_since' => '2024', // Will be populated by backend
    'review_count' => 0, // Will be populated by backend
    'helpful_votes' => 0, // Will be populated by backend
    'badges' => [] // Will be populated by backend
];

$recent_activity = []; // Will be populated by backend
$stats = []; // Will be populated by backend

$footer_config = [
    'logo' => Config::imageUrl('logo.png'),
    'logoAlt' => 'P.I.M.P Business Repository',
    'theme' => 'light'
];

ob_start();
?>

<?php
echo Components::call('Headers', 'documentHead', [[
    'title' => 'Dashboard - P.I.M.P',
    'metaTags' => [
        'description' => 'Your P.I.M.P dashboard - manage your reviews, activity, and account settings.',
        'keywords' => 'dashboard, user profile, reviews, activity'
    ],
    'styles' => [
        'views/dashboard.css'
    ],
    'scripts' => [
        'js/dashboard.js'
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
            ['url' => '/profile', 'label' => 'My Profile'],
            ['url' => '/logout', 'label' => 'Logout', 'separator' => true],
        ],
        'showSearch' => true,
    ]]);
    ?>

    <main class="dashboard-main">
        <div class="dashboard-container">
            <!-- Sidebar -->
            <aside class="dashboard-sidebar">
                <div class="user-profile-card">
                    <div class="user-avatar">
                        <img src="<?= Config::imageUrl('avatars/default.jpg') ?>" alt="User Avatar" id="userAvatar">
                    </div>
                    <div class="user-info">
                        <h2 id="userName"><?= htmlspecialchars($user_data['name']) ?></h2>
                        <p class="user-meta">Member since <span id="memberSince"><?= $user_data['member_since'] ?></span></p>
                    </div>
                </div>

                <nav class="dashboard-nav">
                    <ul class="nav-menu">
                        <li class="nav-item active">
                            <a href="<?= Config::url('/dashboard') ?>" class="nav-link">
                                <i class="fas fa-tachometer-alt"></i>
                                Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= Config::url('/profile') ?>" class="nav-link">
                                <i class="fas fa-user"></i>
                                My Profile
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= Config::url('/reviews') ?>" class="nav-link">
                                <i class="fas fa-star"></i>
                                My Reviews
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= Config::url('/favorites') ?>" class="nav-link">
                                <i class="fas fa-heart"></i>
                                Favorites
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= Config::url('/settings') ?>" class="nav-link">
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
                    <h1>Welcome back, <span id="welcomeName"><?= htmlspecialchars($user_data['name']) ?></span>!</h1>
                    <p>Here's what's happening with your account today.</p>
                </section>

                <!-- Stats Grid -->
                <section class="stats-section">
                    <div class="stats-grid" id="statsGrid">
                        <!-- Stats will be populated by JavaScript -->
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-star"></i>
                            </div>
                            <div class="stat-content">
                                <div class="stat-number" id="reviewCount">0</div>
                                <div class="stat-label">Reviews Written</div>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-thumbs-up"></i>
                            </div>
                            <div class="stat-content">
                                <div class="stat-number" id="helpfulVotes">0</div>
                                <div class="stat-label">Helpful Votes</div>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-eye"></i>
                            </div>
                            <div class="stat-content">
                                <div class="stat-number" id="profileViews">0</div>
                                <div class="stat-label">Profile Views</div>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-award"></i>
                            </div>
                            <div class="stat-content">
                                <div class="stat-number" id="badgeCount">0</div>
                                <div class="stat-label">Badges Earned</div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Quick Actions -->
                <section class="actions-section">
                    <h2>Quick Actions</h2>
                    <div class="actions-grid">
                        <a href="<?= Config::url('/reviews/write') ?>" class="action-card">
                            <div class="action-icon">
                                <i class="fas fa-pencil-alt"></i>
                            </div>
                            <div class="action-content">
                                <h3>Write a Review</h3>
                                <p>Share your experience with a business</p>
                            </div>
                        </a>
                        <a href="<?= Config::url('/businesses') ?>" class="action-card">
                            <div class="action-icon">
                                <i class="fas fa-search"></i>
                            </div>
                            <div class="action-content">
                                <h3>Find Businesses</h3>
                                <p>Discover new places to review</p>
                            </div>
                        </a>
                        <a href="<?= Config::url('/profile') ?>" class="action-card">
                            <div class="action-icon">
                                <i class="fas fa-user-edit"></i>
                            </div>
                            <div class="action-content">
                                <h3>Update Profile</h3>
                                <p>Keep your information current</p>
                            </div>
                        </a>
                        <a href="<?= Config::url('/settings') ?>" class="action-card">
                            <div class="action-icon">
                                <i class="fas fa-bell"></i>
                            </div>
                            <div class="action-content">
                                <h3>Notification Settings</h3>
                                <p>Manage your preferences</p>
                            </div>
                        </a>
                    </div>
                </section>

                <!-- Recent Activity -->
                <section class="activity-section">
                    <div class="section-header">
                        <h2>Recent Activity</h2>
                        <a href="<?= Config::url('/activity') ?>" class="view-all">View All</a>
                    </div>
                    <div class="activity-list" id="activityList">
                        <!-- Activity items will be populated by JavaScript -->
                        <div class="empty-state">
                            <i class="fas fa-inbox"></i>
                            <h3>No recent activity</h3>
                            <p>Your activity will appear here once you start using P.I.M.P</p>
                        </div>
                    </div>
                </section>

                <!-- Badges Section -->
                <section class="badges-section">
                    <div class="section-header">
                        <h2>Your Badges</h2>
                        <a href="<?= Config::url('/badges') ?>" class="view-all">View All</a>
                    </div>
                    <div class="badges-grid" id="badgesGrid">
                        <!-- Badges will be populated by JavaScript -->
                        <div class="empty-state">
                            <i class="fas fa-award"></i>
                            <h3>No badges yet</h3>
                            <p>Earn badges by writing reviews and being active in the community</p>
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
    // Dashboard data initialization
    const DASHBOARD_DATA = {
        user: <?= json_encode($user_data) ?>,
        stats: <?= json_encode($stats) ?>,
        recentActivity: <?= json_encode($recent_activity) ?>,
        badges: <?= json_encode($user_data['badges']) ?>
    };

    document.addEventListener('DOMContentLoaded', function() {
        initializeDashboard();
        loadDashboardData();
    });

    function initializeDashboard() {
        // Initialize any dashboard-specific UI components
        const navItems = document.querySelectorAll('.nav-item');
        navItems.forEach(item => {
            item.addEventListener('click', function() {
                navItems.forEach(i => i.classList.remove('active'));
                this.classList.add('active');
            });
        });
    }

    async function loadDashboardData() {
        try {
            // Show loading states
            showLoadingStates();

            // In a real implementation, this would be an API call
            // const response = await fetch('/api/dashboard');
            // const data = await response.json();
            
            // For now, we'll use the embedded data
            const data = DASHBOARD_DATA;
            
            updateDashboardUI(data);
        } catch (error) {
            console.error('Error loading dashboard data:', error);
            showErrorState();
        }
    }

    function showLoadingStates() {
        // Add loading animation to stat cards
        const statNumbers = document.querySelectorAll('.stat-number');
        statNumbers.forEach(number => {
            number.textContent = '...';
        });
    }

    function updateDashboardUI(data) {
        // Update user info
        if (data.user) {
            document.getElementById('userName').textContent = data.user.name;
            document.getElementById('welcomeName').textContent = data.user.name;
            document.getElementById('memberSince').textContent = data.user.member_since;
            
            // Update stats
            document.getElementById('reviewCount').textContent = data.user.review_count || 0;
            document.getElementById('helpfulVotes').textContent = data.user.helpful_votes || 0;
            document.getElementById('badgeCount').textContent = data.user.badges ? data.user.badges.length : 0;
        }

        // Update activity
        updateActivityList(data.recentActivity || []);

        // Update badges
        updateBadgesGrid(data.badges || []);
    }

    function updateActivityList(activities) {
        const activityList = document.getElementById('activityList');
        
        if (activities.length === 0) {
            return; // Keep the empty state
        }

        activityList.innerHTML = activities.map(activity => `
            <div class="activity-item">
                <div class="activity-icon">
                    <i class="${getActivityIcon(activity.type)}"></i>
                </div>
                <div class="activity-content">
                    <p class="activity-text">${activity.text}</p>
                    <span class="activity-time">${activity.time}</span>
                </div>
            </div>
        `).join('');
    }

    function updateBadgesGrid(badges) {
        const badgesGrid = document.getElementById('badgesGrid');
        
        if (badges.length === 0) {
            return; // Keep the empty state
        }

        badgesGrid.innerHTML = badges.map(badge => `
            <div class="badge-card">
                <div class="badge-icon">
                    <i class="${badge.icon}"></i>
                </div>
                <div class="badge-content">
                    <h4>${badge.name}</h4>
                    <p>${badge.description}</p>
                </div>
            </div>
        `).join('');
    }

    function getActivityIcon(type) {
        const icons = {
            review: 'fas fa-star',
            comment: 'fas fa-comment',
            like: 'fas fa-thumbs-up',
            follow: 'fas fa-user-plus',
            badge: 'fas fa-award'
        };
        return icons[type] || 'fas fa-circle';
    }

    function showErrorState() {
        // Show error message to user
        const activityList = document.getElementById('activityList');
        activityList.innerHTML = `
            <div class="error-state">
                <i class="fas fa-exclamation-triangle"></i>
                <h3>Unable to load data</h3>
                <p>Please try refreshing the page</p>
            </div>
        `;
    }
    </script>
</body>
</html>

<?php echo ob_get_clean(); ?>