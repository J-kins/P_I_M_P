<?php
/**
 * P.I.M.P Admin Dashboard
 * Example usage of admin header
 */

require_once '../vendor/autoload.php';

use PIMP\Core\Config;
use PIMP\Views\Components\Headers;

Config::init();

// Check admin authentication (pseudo-code)
// $isAdmin = check_admin_auth();
// if (!$isAdmin) { header('Location: /login'); exit; }

ob_start();

echo Headers::documentHead([
    'title' => 'Admin Dashboard - PIMP Business Repository',
    'styles' => [
        'views/admin-dashboard.css'
    ],
    'includeFontAwesome' => true,
    'includeJQuery' => true
]);
?>

<body class="admin-body">
    <?php
    // Admin Header with notifications
    echo Headers::adminHeader([
        'userName' => 'John Doe',
        'userRole' => 'Administrator',
        'userAvatar' => 'images/avatars/admin.jpg',
        'notifications' => [
            [
                'icon' => 'fa-exclamation-triangle',
                'text' => '5 pending business verifications',
                'time' => '2 hours ago'
            ],
            [
                'icon' => 'fa-flag',
                'text' => '3 new reviews reported',
                'time' => '5 hours ago'
            ],
            [
                'icon' => 'fa-user-plus',
                'text' => '15 new user registrations',
                'time' => '1 day ago'
            ]
        ]
    ]);
    ?>

    <div class="admin-container">
        <aside class="admin-sidebar">
            <!-- Sidebar navigation -->
            <nav class="sidebar-nav">
                <ul>
                    <li><a href="/admin/dashboard" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                    <li><a href="/admin/businesses"><i class="fas fa-building"></i> Businesses</a></li>
                    <li><a href="/admin/reviews"><i class="fas fa-star"></i> Reviews</a></li>
                    <li><a href="/admin/users"><i class="fas fa-users"></i> Users</a></li>
                    <li><a href="/admin/reports"><i class="fas fa-flag"></i> Reports</a></li>
                    <li><a href="/admin/settings"><i class="fas fa-cog"></i> Settings</a></li>
                </ul>
            </nav>
        </aside>

        <main class="admin-main">
            <!-- Dashboard content -->
            <div class="admin-content">
                <h1>Admin Dashboard</h1>
                
                <!-- Stats cards -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-building"></i>
                        </div>
                        <div class="stat-info">
                            <h3>1,247</h3>
                            <p>Total Businesses</p>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-star"></i>
                        </div>
                        <div class="stat-info">
                            <h3>8,956</h3>
                            <p>Total Reviews</p>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-info">
                            <h3>15,432</h3>
                            <p>Registered Users</p>
                        </div>
                    </div>
                </div>
                
                <!-- Recent activity -->
                <div class="recent-activity">
                    <h2>Recent Activity</h2>
                    <!-- Activity list would go here -->
                </div>
            </div>
        </main>
    </div>

    <script>
        // Admin-specific JavaScript
        document.addEventListener('DOMContentLoaded', function() {
            // Notification dropdown
            const notificationBtn = document.getElementById('notificationBtn');
            const notificationDropdown = document.getElementById('notificationDropdown');
            
            if (notificationBtn && notificationDropdown) {
                notificationBtn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    notificationDropdown.classList.toggle('active');
                });
                
                // Close dropdown when clicking outside
                document.addEventListener('click', function() {
                    notificationDropdown.classList.remove('active');
                });
            }
            
            // User menu dropdown
            const userMenuBtn = document.getElementById('userMenuBtn');
            const userDropdown = document.getElementById('userDropdown');
            
            if (userMenuBtn && userDropdown) {
                userMenuBtn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    userDropdown.classList.toggle('active');
                });
                
                // Close dropdown when clicking outside
                document.addEventListener('click', function() {
                    userDropdown.classList.remove('active');
                });
            }
        });
    </script>
</body>
</html>

<?php
echo ob_get_clean();
?>