
<?php
/**
 * Example implementation of Admin UI Template System
 * This showcases how to use the various components in an admin context
 */

// Start session for user preferences like theme
session_start();

// Check authentication (simplified for example)
$is_authenticated = isset($_SESSION['admin_user']);
if (!$is_authenticated) {
    header('Location: login.php');
    exit;
}

// Load configuration
require_once '../config.php';

// Load component system
require_once '../includes/components.php';

// Set page title
$page_title = 'Admin Dashboard';

// Define sidebar navigation items
$sidebar_items = [
    [
        'url' => '/admin/dashboard',
        'label' => 'Dashboard',
        'icon' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="16" height="16"><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9h18v10a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V9Zm0 0V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v4M9 22v-6a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v6"/></svg>',
        'active' => true
    ],
    [
        'url' => '/admin/users',
        'label' => 'Users',
        'icon' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="16" height="16"><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 1 1-8 0 4 4 0 0 1 8 0Zm-4 7a7 7 0 0 0-7 7h14a7 7 0 0 0-7-7Z"/></svg>'
    ],
    [
        'url' => '/admin/content',
        'label' => 'Content',
        'icon' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="16" height="16"><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5.586a1 1 0 0 1 .707.293l5.414 5.414a1 1 0 0 1 .293.707V19a2 2 0 0 1-2 2Z"/></svg>'
    ],
    [
        'url' => '/admin/settings',
        'label' => 'Settings',
        'icon' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="16" height="16"><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 0 0 2.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 0 0 1.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 0 0-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 0 0-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 0 0-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 0 0-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 0 0 1.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065Z"/><circle cx="12" cy="12" r="3"/></svg>'
    ]
];

// Define header actions
$header_actions = [
    [
        'type' => 'text',
        'content' => 'Admin User'
    ],
    [
        'type' => 'icon',
        'label' => 'Logout',
        'url' => '/admin/logout',
        'icon' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9"/></svg>'
    ]
];

// Stats for dashboard
$stats = [
    [
        'title' => 'Total Users',
        'value' => '1,234',
        'change' => '+5.3%',
        'icon' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24"><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2m8-10a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm6.5-5a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5ZM19 13.5a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5Z"/></svg>'
    ],
    [
        'title' => 'New Orders',
        'value' => '56',
        'change' => '+12.4%',
        'icon' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24"><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 6v2m4-2v10a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2ZM9 11h6"/></svg>'
    ],
    [
        'title' => 'Revenue',
        'value' => '$9,876',
        'change' => '+8.2%',
        'icon' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24"><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 1v22m5-22v22M1 6h22M1 18h22M4 6v12m16-12v12"/></svg>'
    ],
    [
        'title' => 'Traffic',
        'value' => '21.5K',
        'change' => '+3.8%',
        'icon' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24"><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3v18h18M18.7 15.3l-3-4.5-5 6-3-3"/></svg>'
    ]
];
?>
<!DOCTYPE html>
<html lang="en" data-theme="<?= get_active_theme() ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title) ?> | Admin</title>
    
    <!-- Core CSS -->
    <link rel="stylesheet" href="../assets/css/styles.css">
    <link rel="stylesheet" href="../assets/css/themes/<?= get_active_theme() ?>.css">
    
    <!-- Admin-specific CSS -->
    <link rel="stylesheet" href="assets/css/admin.css">
    
    <!-- Component-specific CSS -->
    <link rel="stylesheet" href="../assets/css/component_styles/header.css">
    <link rel="stylesheet" href="../assets/css/component_styles/nav.css">
    <link rel="stylesheet" href="../assets/css/component_styles/ui_elements.css">
</head>
<body class="admin-body">
    <div class="admin-layout">
        <!-- Admin Header -->
        <?= navBar([
            'title' => 'Admin Panel',
            'logo' => 'assets/images/admin-logo.svg',
            'navItems' => [],
            'actions' => $header_actions,
            'theme' => 'dark',
            'style' => 'minimal'
        ]) ?>
        
        <div class="admin-container">
            <!-- Sidebar Navigation -->
            <aside class="admin-sidebar">
                <?= navigationBar([
                    'items' => $sidebar_items,
                    'position' => 'vertical',
                    'align' => 'start',
                    'class' => 'admin-navigation'
                ]) ?>
            </aside>
            
            <!-- Main Content Area -->
            <main class="admin-content">
                <!-- Breadcrumbs -->
                <?= breadcrumbs([
                    ['url' => '/admin', 'label' => 'Admin'],
                    ['url' => '/admin/dashboard', 'label' => 'Dashboard']
                ], '/', 'admin-breadcrumbs') ?>
                
                <!-- Page Header -->
                <?= pageHeader('Dashboard', 'Welcome to the admin dashboard', 'admin-page-header') ?>
                
                <!-- Stats Grid -->
                <div class="admin-stats-grid">
                    <?php foreach($stats as $stat): ?>
                    <div class="admin-stat-card">
                        <div class="stat-icon">
                            <?= $stat['icon'] ?>
                        </div>
                        <div class="stat-content">
                            <h3 class="stat-title"><?= htmlspecialchars($stat['title']) ?></h3>
                            <div class="stat-value"><?= htmlspecialchars($stat['value']) ?></div>
                            <div class="stat-change <?= strpos($stat['change'], '+') === 0 ? 'positive' : 'negative' ?>">
                                <?= htmlspecialchars($stat['change']) ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Tabbed Content Example -->
                <div class="admin-card">
                    <?= tabbedNav([
                        [
                            'id' => 'recent',
                            'label' => 'Recent Activity',
                            'active' => true,
                            'content' => '
                                <div class="admin-activity-list">
                                    <div class="activity-item">
                                        <div class="activity-icon user-icon"></div>
                                        <div class="activity-content">
                                            <div class="activity-title">New user registered</div>
                                            <div class="activity-details">John Smith created an account</div>
                                            <div class="activity-time">2 hours ago</div>
                                        </div>
                                    </div>
                                    <div class="activity-item">
                                        <div class="activity-icon order-icon"></div>
                                        <div class="activity-content">
                                            <div class="activity-title">New order received</div>
                                            <div class="activity-details">Order #12345 - $78.99</div>
                                            <div class="activity-time">3 hours ago</div>
                                        </div>
                                    </div>
                                    <div class="activity-item">
                                        <div class="activity-icon settings-icon"></div>
                                        <div class="activity-content">
                                            <div class="activity-title">System update completed</div>
                                            <div class="activity-details">Version 2.5.1 installed successfully</div>
                                            <div class="activity-time">5 hours ago</div>
                                        </div>
                                    </div>
                                </div>
                            '
                        ],
                        [
                            'id' => 'tasks',
                            'label' => 'Tasks',
                            'content' => '
                                <div class="admin-tasks-list">
                                    <div class="task-item">
                                        <input type="checkbox" id="task1" class="task-checkbox">
                                        <label for="task1" class="task-label">Review new user registrations</label>
                                        <div class="task-due">Due today</div>
                                    </div>
                                    <div class="task-item">
                                        <input type="checkbox" id="task2" class="task-checkbox">
                                        <label for="task2" class="task-label">Update product inventory</label>
                                        <div class="task-due">Due tomorrow</div>
                                    </div>
                                    <div class="task-item">
                                        <input type="checkbox" id="task3" class="task-checkbox">
                                        <label for="task3" class="task-label">Respond to customer inquiries</label>
                                        <div class="task-due">Due today</div>
                                    </div>
                                </div>
                            '
                        ],
                        [
                            'id' => 'notifications',
                            'label' => 'Notifications',
                            'content' => '
                                <div class="admin-notifications-list">
                                    <div class="notification-item unread">
                                        <div class="notification-icon warning"></div>
                                        <div class="notification-content">
                                            <div class="notification-title">Low inventory alert</div>
                                            <div class="notification-details">Product SKU-12345 is running low</div>
                                            <div class="notification-time">1 hour ago</div>
                                        </div>
                                        <button class="notification-action">Mark read</button>
                                    </div>
                                    <div class="notification-item">
                                        <div class="notification-icon info"></div>
                                        <div class="notification-content">
                                            <div class="notification-title">Weekly report generated</div>
                                            <div class="notification-details">Sales report for last week is available</div>
                                            <div class="notification-time">1 day ago</div>
                                        </div>
                                        <button class="notification-action">View</button>
                                    </div>
                                </div>
                            '
                        ]
                    ], 'admin-tabs', 'admin-tabs') ?>
                </div>
            </main>
        </div>
    </div>
    
    <!-- JavaScript for interactivity -->
    <script src="../assets/js/main.js"></script>
    <script src="assets/js/admin.js"></script>
</body>
</html>
