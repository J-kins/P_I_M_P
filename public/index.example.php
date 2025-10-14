
<?php
/**
 * Example implementation of PHP UI Template System
 * This showcases how to use the various components together
 */

// Start session for user preferences like theme
session_start();

// Load configuration
require_once 'config.php';

// Load component system
require_once 'includes/components.php';

// Set page title
$page_title = 'PHP UI Template Demo';

// Define navigation items
$nav_items = [
    ['url' => '/', 'label' => 'Home', 'active' => true],
    ['url' => '/dashboard', 'label' => 'Dashboard'],
    ['url' => '/spreadsheet', 'label' => 'Spreadsheet'],
    ['url' => '/settings', 'label' => 'Settings'],
];

// Define header actions
$header_actions = [
    ['type' => 'button', 'label' => 'Sign In', 'url' => '/login', 'class' => 'button-outline'],
    ['type' => 'button', 'label' => 'Register', 'url' => '/register'],
];

// Output the full HTML
?>
<!DOCTYPE html>
<html lang="en" data-theme="<?= get_active_theme() ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title) ?> | Template System</title>
    
    <!-- Core CSS -->
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" href="assets/css/themes/<?= get_active_theme() ?>.css">
    
    <!-- Component-specific CSS -->
    <link rel="stylesheet" href="assets/css/component_styles/header.css">
    <link rel="stylesheet" href="assets/css/component_styles/nav.css">
    <link rel="stylesheet" href="assets/css/component_styles/footer.css">
</head>
<body>
    <div class="app-container">
        <!-- Header Component -->
        <?= headerOne([
            'title' => 'PHP UI Kit',
            'navItems' => $nav_items,
            'actions' => $header_actions,
            'hasSearch' => true,
            'theme' => 'light',
            'mobileMenuToggle' => true,
        ]) ?>
        
        <!-- Main Content -->
        <main class="main-content">
            <div class="container">
                <!-- Page Header -->
                <?= pageHeader('Welcome to PHP UI Template System', 'A flexible component library for PHP applications') ?>
                
                <!-- Navigation Component -->
                <div class="section">
                    <h2>Navigation Example</h2>
                    <?= navigationBar([
                        'items' => $nav_items,
                        'activeUrl' => '/',
                        'position' => 'horizontal',
                        'align' => 'center',
                    ]) ?>
                </div>
                
                <!-- Breadcrumbs Component -->
                <div class="section">
                    <h2>Breadcrumbs Example</h2>
                    <?= breadcrumbs([
                        ['url' => '/', 'label' => 'Home'],
                        ['url' => '/components', 'label' => 'Components'],
                        ['url' => '/components/nav', 'label' => 'Navigation'],
                    ], '›') ?>
                </div>
                
                <!-- Tabbed Navigation Component -->
                <div class="section">
                    <h2>Tabbed Navigation Example</h2>
                    <?= tabbedNav([
                        [
                            'id' => 'tab1',
                            'label' => 'Overview',
                            'active' => true,
                            'content' => '<p>This is the overview content. The component system allows for flexible and reusable UI elements.</p>'
                        ],
                        [
                            'id' => 'tab2',
                            'label' => 'Features',
                            'content' => '<p>Features include themeable components, responsive design, and multiple format support.</p>'
                        ],
                        [
                            'id' => 'tab3',
                            'label' => 'Documentation',
                            'content' => '<p>Complete documentation with examples for all available components and their options.</p>'
                        ],
                    ], 'demo-tabs') ?>
                </div>
            </div>
        </main>
        
        <!-- Theme Selector -->
        <div class="theme-selector-container container">
            <?= theme_selector() ?>
        </div>
    </div>
    
    <!-- JavaScript for interactivity -->
    <script src="assets/js/main.js"></script>
</body>
</html>
