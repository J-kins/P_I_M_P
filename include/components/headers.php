
<?php
/**
 * Header components for PHP UI Template System
 */

/**
 * Generates complete document head
 * 
 * @param array $config {
 *   @type string $title Page title
 *   @type array $styles Additional CSS files
 *   @type array $scripts JS files to include
 *   @type array $metaTags Additional meta tags
 *   @type string $canonical Canonical URL
 * }
 * @return string HTML for document head
 */
function document_head(array $config = []): string {
    $theme = get_active_theme();
    ob_start(); ?>
    <!DOCTYPE html>
    <html lang="<?= $config['lang'] ?? 'en' ?>" data-theme="<?= $theme ?>">
    <head>
        <meta charset="<?= $config['charset'] ?? 'UTF-8' ?>">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        
        <?php // Theme color meta ?>
        <meta name="theme-color" content="#8a5cf5">
        
        <?php // Favicon with cache busting ?>
        <link rel="icon" href="<?= asset_url('img/favicon.ico?v=1') ?>">
        <link rel="apple-touch-icon" href="<?= asset_url('img/apple-touch-icon.png') ?>">
        
        <?php // Title and SEO ?>
        <title><?= htmlspecialchars($config['title'] ?? 'PHP UI Template System') ?></title>
        
        <?php if (!empty($config['metaTags'])): ?>
            <?php foreach($config['metaTags'] as $name => $content): ?>
                <meta name="<?= htmlspecialchars($name) ?>" content="<?= htmlspecialchars($content) ?>">
            <?php endforeach; ?>
        <?php endif; ?>
        
        <?php if (!empty($config['canonical'])): ?>
            <link rel="canonical" href="<?= htmlspecialchars($config['canonical']) ?>">
        <?php endif; ?>
        
        <?php // Base CSS file always included ?>
        <link rel="stylesheet" href="<?= asset_url('css/styles.css') ?>">
        
        <?php // Theme-specific CSS ?>
        <link rel="stylesheet" href="<?= asset_url("css/themes/{$theme}.css") ?>">
        
        <?php // Additional CSS files ?>
        <?php if (!empty($config['styles'])): ?>
            <?php foreach ($config['styles'] as $css): ?>
                <link rel="stylesheet" href="<?= asset_url($css) ?>">
            <?php endforeach; ?>
        <?php endif; ?>
        
        <?php // JavaScript files ?>
        <script src="<?= asset_url('lib/jquery.min.js') ?>"></script>
        
        <?php if (!empty($config['scripts'])): ?>
            <?php foreach ($config['scripts'] as $js): ?>
                <script src="<?= asset_url($js) ?>" defer></script>
            <?php endforeach; ?>
        <?php endif; ?>
    </head>
    <?php return ob_get_clean();
}

/**
 * Main header component with navigation
 * 
 * @param array $params {
 *   @type string $title Header title or logo text
 *   @type string $logo Logo image path (optional)
 *   @type array $navItems Navigation items [['url'=>'', 'label'=>'', 'active'=>false]]
 *   @type string $type Header type (default|compact|centered|expanded|minimal)
 *   @type string $class Additional CSS classes
 *   @type array $actions Additional action buttons/elements for the right side
 *   @type string $theme Color theme (dark|light|primary|transparent)
 *   @type string $searchPlaceholder Optional search placeholder text
 *   @type bool $hasSearch Whether to include search box
 * }
 * @return string HTML output
 */
function headerOne(array $params = []): string {
    $title = $params['title'] ?? 'PHP UI Template';
    $logo = $params['logo'] ?? '';
    $navItems = $params['navItems'] ?? [];
    $type = $params['type'] ?? 'default';
    $class = $params['class'] ?? '';
    $actions = $params['actions'] ?? [];
    $theme = $params['theme'] ?? 'default';
    $hasSearch = $params['hasSearch'] ?? false;
    $searchPlaceholder = $params['searchPlaceholder'] ?? 'Search';
    
    ob_start(); ?>
    <header class="header header-<?= htmlspecialchars($type) ?> header-theme-<?= htmlspecialchars($theme) ?> <?= htmlspecialchars($class) ?>" role="banner">
        <div class="container header-container">
            <div class="header-start">
                <?php if (!empty($params['mobileMenuToggle'])): ?>
                <button class="header-mobile-toggle" aria-label="Toggle menu">
                    <span class="toggle-icon"></span>
                </button>
                <?php endif; ?>
                
                <div class="header-logo">
                    <?php if (!empty($logo)): ?>
                    <a href="<?= url('/') ?>" class="logo-link">
                        <img src="<?= asset_url($logo) ?>" alt="<?= htmlspecialchars($title) ?>" class="logo-image">
                    </a>
                    <?php else: ?>
                    <h1 class="logo-text"><?= htmlspecialchars($title) ?></h1>
                    <?php endif; ?>
                </div>
            </div>
            
            <?php if (!empty($navItems)): ?>
            <nav class="header-nav" aria-label="Main navigation">
                <ul class="nav-list">
                    <?php foreach ($navItems as $item): ?>
                    <li class="nav-item">
                        <a href="<?= url($item['url']) ?>" 
                           class="nav-link <?= ($item['active'] ?? false) ? 'active' : '' ?>"
                           <?= ($item['active'] ?? false) ? 'aria-current="page"' : '' ?>>
                            <?= htmlspecialchars($item['label']) ?>
                        </a>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </nav>
            <?php endif; ?>
            
            <div class="header-end">
                <?php if ($hasSearch): ?>
                <div class="header-search">
                    <form action="<?= url('/search') ?>" method="get" role="search">
                        <input type="search" name="q" placeholder="<?= htmlspecialchars($searchPlaceholder) ?>" 
                               class="search-input" aria-label="Search">
                        <button type="submit" class="search-button" aria-label="Submit search">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="11" cy="11" r="8"></circle>
                                <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                            </svg>
                        </button>
                    </form>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($actions)): ?>
                <div class="header-actions">
                    <?php foreach ($actions as $action): ?>
                        <?php if ($action['type'] === 'button'): ?>
                        <a href="<?= url($action['url'] ?? '#') ?>" class="button <?= $action['class'] ?? 'button-primary' ?>">
                            <?= htmlspecialchars($action['label']) ?>
                        </a>
                        <?php elseif ($action['type'] === 'icon'): ?>
                        <a href="<?= url($action['url'] ?? '#') ?>" class="icon-button" aria-label="<?= htmlspecialchars($action['label']) ?>">
                            <?= $action['icon'] ?? '' ?>
                        </a>
                        <?php elseif ($action['type'] === 'text'): ?>
                        <span class="header-text"><?= htmlspecialchars($action['content']) ?></span>
                        <?php elseif ($action['type'] === 'html'): ?>
                        <?= $action['content'] ?>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </header>
    <?php return ob_get_clean();
}

/**
 * Modern mobile-first navigation bar
 * 
 * @param array $params {
 *   @type string $title Brand name/title
 *   @type string $logo Logo image path (optional)
 *   @type array $navItems Navigation items
 *   @type string $style Style variant (minimal|centered|expanded|gradient)
 *   @type array $actions Right side action buttons
 *   @type string $theme Color theme (light|dark|primary)
 * }
 * @return string HTML output
 */
function navBar(array $params = []): string {
    $title = $params['title'] ?? 'Brand';
    $logo = $params['logo'] ?? '';
    $navItems = $params['navItems'] ?? [];
    $style = $params['style'] ?? 'minimal';
    $actions = $params['actions'] ?? [];
    $theme = $params['theme'] ?? 'light';
    $hasSearch = $params['hasSearch'] ?? false;
    $searchPlaceholder = $params['searchPlaceholder'] ?? 'Search';
    
    ob_start(); ?>
    <nav class="navbar navbar-<?= htmlspecialchars($style) ?> navbar-theme-<?= htmlspecialchars($theme) ?>">
        <div class="navbar-container">
            <div class="navbar-brand">
                <?php if (!empty($logo)): ?>
                <a href="<?= url('/') ?>" class="navbar-logo">
                    <img src="<?= asset_url($logo) ?>" alt="<?= htmlspecialchars($title) ?>" class="brand-image">
                </a>
                <?php else: ?>
                <a href="<?= url('/') ?>" class="navbar-title"><?= htmlspecialchars($title) ?></a>
                <?php endif; ?>
                
                <button class="navbar-toggle" aria-label="Toggle navigation menu" aria-expanded="false">
                    <span class="toggle-bar"></span>
                    <span class="toggle-bar"></span>
                    <span class="toggle-bar"></span>
                </button>
            </div>

            <div class="navbar-collapse">
                <?php if (!empty($navItems)): ?>
                <ul class="navbar-nav">
                    <?php foreach ($navItems as $item): ?>
                    <li class="nav-item">
                        <a href="<?= url($item['url']) ?>" 
                           class="nav-link <?= ($item['active'] ?? false) ? 'active' : '' ?>"
                           <?= ($item['active'] ?? false) ? 'aria-current="page"' : '' ?>>
                            <?= htmlspecialchars($item['label']) ?>
                        </a>
                    </li>
                    <?php endforeach; ?>
                </ul>
                <?php endif; ?>
                
                <?php if ($hasSearch): ?>
                <div class="navbar-search">
                    <form action="<?= url('/search') ?>" method="get">
                        <input type="search" name="q" placeholder="<?= htmlspecialchars($searchPlaceholder) ?>" class="search-input">
                        <button type="submit" class="search-submit" aria-label="Search">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" 
                                 stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="11" cy="11" r="8"></circle>
                                <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                            </svg>
                        </button>
                    </form>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($actions)): ?>
                <div class="navbar-actions">
                    <?php foreach ($actions as $action): ?>
                        <?php if ($action['type'] === 'button'): ?>
                        <a href="<?= url($action['url'] ?? '#') ?>" class="navbar-btn <?= $action['class'] ?? 'primary' ?>">
                            <?= htmlspecialchars($action['label']) ?>
                        </a>
                        <?php elseif ($action['type'] === 'icon'): ?>
                        <a href="<?= url($action['url'] ?? '#') ?>" class="navbar-icon" aria-label="<?= htmlspecialchars($action['label']) ?>">
                            <?= $action['icon'] ?? '' ?>
                        </a>
                        <?php elseif ($action['type'] === 'text'): ?>
                        <span class="navbar-text"><?= htmlspecialchars($action['content']) ?></span>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </nav>
    <?php return ob_get_clean();
}

/**
 * Simple page header with title and subtitle
 * 
 * @param string $title Page title
 * @param string $subtitle Optional subtitle
 * @param string $class Additional CSS classes
 * @return string HTML output
 */
function pageHeader($title, $subtitle = '', $class = ''): string {
    ob_start(); ?>
    <div class="page-header <?= htmlspecialchars($class) ?>">
        <h1><?= htmlspecialchars($title) ?></h1>
        <?php if (!empty($subtitle)): ?>
            <p class="subtitle"><?= htmlspecialchars($subtitle) ?></p>
        <?php endif; ?>
    </div>
    <?php return ob_get_clean();
}

/**
 * Hero header with background image and call to action
 * 
 * @param array $params {
 *   @type string $title Main title text
 *   @type string $subtitle Subtitle or description
 *   @type array $actions Call to action buttons
 *   @type string $bgImage Background image path
 *   @type string $overlay Overlay style (dark|light|gradient|none)
 *   @type string $size Size variant (sm|md|lg|xl)
 *   @type string $align Text alignment (left|center|right)
 * }
 * @return string HTML output
 */
function heroHeader(array $params = []): string {
    $title = $params['title'] ?? 'Welcome';
    $subtitle = $params['subtitle'] ?? '';
    $actions = $params['actions'] ?? [];
    $bgImage = $params['bgImage'] ?? '';
    $overlay = $params['overlay'] ?? 'dark';
    $size = $params['size'] ?? 'md';
    $align = $params['align'] ?? 'center';
    
    ob_start(); ?>
    <section class="hero-header hero-<?= htmlspecialchars($size) ?> hero-align-<?= htmlspecialchars($align) ?> hero-overlay-<?= htmlspecialchars($overlay) ?>"
             <?php if (!empty($bgImage)): ?>style="background-image: url('<?= asset_url($bgImage) ?>');"<?php endif; ?>>
        <div class="hero-container">
            <div class="hero-content">
                <h1 class="hero-title"><?= htmlspecialchars($title) ?></h1>
                
                <?php if (!empty($subtitle)): ?>
                <div class="hero-subtitle"><?= $subtitle ?></div>
                <?php endif; ?>
                
                <?php if (!empty($actions)): ?>
                <div class="hero-actions">
                    <?php foreach ($actions as $action): ?>
                    <a href="<?= url($action['url'] ?? '#') ?>" class="button <?= $action['class'] ?? 'button-primary' ?>">
                        <?= htmlspecialchars($action['label']) ?>
                    </a>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </section>
    <?php return ob_get_clean();
}
?>


<?php
/**
 * BBB-style header components
 */

/**
 * BBB-style header with top bar and main navigation
 * 
 * @param array $params {
 *   @type string $logo Logo image path
 *   @type string $logoAlt Logo alt text
 *   @type array $topBarItems Top bar navigation items
 *   @type array $mainNavItems Main navigation items
 *   @type array $userActions User action links (login, register, etc.)
 *   @type string $searchPlaceholder Search placeholder text
 *   @type string $phoneNumber Phone number to display
 *   @type string $ctaText Call to action button text
 *   @type string $ctaUrl Call to action button URL
 *   @type bool $showSearch Whether to show search box
 *   @type bool $showPhone Whether to show phone number
 * }
 * @return string HTML output
 */
function bbb_header(array $params = []): string {
    $logo = $params['logo'] ?? '';
    $logoAlt = $params['logoAlt'] ?? 'Better Business Bureau';
    $topBarItems = $params['topBarItems'] ?? [];
    $mainNavItems = $params['mainNavItems'] ?? [];
    $userActions = $params['userActions'] ?? [];
    $searchPlaceholder = $params['searchPlaceholder'] ?? 'Search BBB Business Profiles';
    $phoneNumber = $params['phoneNumber'] ?? '';
    $ctaText = $params['ctaText'] ?? 'Submit a Complaint';
    $ctaUrl = $params['ctaUrl'] ?? '#';
    $showSearch = $params['showSearch'] ?? true;
    $showPhone = $params['showPhone'] ?? true;
    
    ob_start(); ?>
    
    <!-- BBB Top Bar -->
    <div class="bbb-top-bar">
        <div class="bbb-container">
            <nav class="bbb-top-nav" aria-label="Secondary navigation">
                <ul class="bbb-top-nav-list">
                    <?php foreach ($topBarItems as $item): ?>
                    <li class="bbb-top-nav-item">
                        <a href="<?= url($item['url']) ?>" class="bbb-top-nav-link">
                            <?= htmlspecialchars($item['label']) ?>
                        </a>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </nav>
            
            <div class="bbb-user-actions">
                <?php foreach ($userActions as $action): ?>
                <a href="<?= url($action['url']) ?>" class="bbb-user-action-link">
                    <?= htmlspecialchars($action['label']) ?>
                </a>
                <?php if (!empty($action['separator'])): ?>
                <span class="bbb-separator">|</span>
                <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    
    <!-- BBB Main Header -->
    <header class="bbb-main-header" role="banner">
        <div class="bbb-container">
            <div class="bbb-header-left">
                <div class="bbb-logo">
                    <a href="<?= url('/') ?>" class="bbb-logo-link">
                        <img src="<?= asset_url($logo) ?>" alt="<?= htmlspecialchars($logoAlt) ?>" class="bbb-logo-image">
                    </a>
                </div>
                
                <?php if (!empty($mainNavItems)): ?>
                <nav class="bbb-main-nav" aria-label="Main navigation">
                    <ul class="bbb-main-nav-list">
                        <?php foreach ($mainNavItems as $item): ?>
                        <li class="bbb-main-nav-item">
                            <a href="<?= url($item['url']) ?>" 
                               class="bbb-main-nav-link <?= ($item['active'] ?? false) ? 'active' : '' ?>"
                               <?= ($item['active'] ?? false) ? 'aria-current="page"' : '' ?>>
                                <?= htmlspecialchars($item['label']) ?>
                                <?php if (!empty($item['dropdown'])): ?>
                                <span class="bbb-dropdown-arrow">▼</span>
                                <?php endif; ?>
                            </a>
                            
                            <?php if (!empty($item['dropdown'])): ?>
                            <div class="bbb-dropdown-menu">
                                <?php foreach ($item['dropdown'] as $dropdownItem): ?>
                                <a href="<?= url($dropdownItem['url']) ?>" class="bbb-dropdown-link">
                                    <?= htmlspecialchars($dropdownItem['label']) ?>
                                </a>
                                <?php endforeach; ?>
                            </div>
                            <?php endif; ?>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </nav>
                <?php endif; ?>
            </div>
            
            <div class="bbb-header-right">
                <?php if ($showSearch): ?>
                <div class="bbb-search-box">
                    <form action="<?= url('/search') ?>" method="get" role="search" class="bbb-search-form">
                        <div class="bbb-search-wrapper">
                            <input type="search" name="q" placeholder="<?= htmlspecialchars($searchPlaceholder) ?>" 
                                   class="bbb-search-input" aria-label="Search businesses">
                            <button type="submit" class="bbb-search-button" aria-label="Search">
                                <svg class="bbb-search-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" 
                                     stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="11" cy="11" r="8"></circle>
                                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                                </svg>
                            </button>
                        </div>
                    </form>
                </div>
                <?php endif; ?>
                
                <?php if ($showPhone && !empty($phoneNumber)): ?>
                <div class="bbb-phone-number">
                    <span class="bbb-phone-label">Call Us:</span>
                    <a href="tel:<?= preg_replace('/[^0-9+]/', '', $phoneNumber) ?>" class="bbb-phone-link">
                        <?= htmlspecialchars($phoneNumber) ?>
                    </a>
                </div>
                <?php endif; ?>
                
                <div class="bbb-cta-section">
                    <a href="<?= url($ctaUrl) ?>" class="bbb-cta-button">
                        <?= htmlspecialchars($ctaText) ?>
                    </a>
                </div>
                
                <!-- Mobile menu toggle -->
                <button class="bbb-mobile-toggle" aria-label="Toggle mobile menu" aria-expanded="false">
                    <span class="bbb-toggle-bar"></span>
                    <span class="bbb-toggle-bar"></span>
                    <span class="bbb-toggle-bar"></span>
                </button>
            </div>
        </div>
    </header>
    
    <?php return ob_get_clean();
}

/**
 * Default BBB header configuration
 * 
 * @return array Pre-configured BBB header parameters
 */
function bbb_default_config(): array {
    return [
        'logo' => 'img/bbb-logo.png',
        'logoAlt' => 'Better Business Bureau',
        'topBarItems' => [
            ['url' => '/about', 'label' => 'About BBB'],
            ['url' => '/news', 'label' => 'News & Events'],
            ['url' => '/careers', 'label' => 'Careers'],
            ['url' => '/contact', 'label' => 'Contact Us'],
        ],
        'mainNavItems' => [
            [
                'url' => '/business-profiles', 
                'label' => 'Business Profiles',
                'dropdown' => [
                    ['url' => '/find-business', 'label' => 'Find a Business'],
                    ['url' => '/accredited-business', 'label' => 'BBB Accredited Businesses'],
                ]
            ],
            [
                'url' => '/complaints', 
                'label' => 'Complaints',
                'dropdown' => [
                    ['url' => '/file-complaint', 'label' => 'File a Complaint'],
                    ['url' => '/check-complaint', 'label' => 'Check Complaint Status'],
                ]
            ],
            [
                'url' => '/reviews', 
                'label' => 'Reviews',
                'active' => false
            ],
            [
                'url' => '/scam-tracker', 
                'label' => 'Scam Tracker',
                'active' => false
            ],
            [
                'url' => '/consumer-resources', 
                'label' => 'Consumer Resources',
                'dropdown' => [
                    ['url' => '/tips', 'label' => 'Tips & Guides'],
                    ['url' => '/alerts', 'label' => 'Consumer Alerts'],
                ]
            ],
            [
                'url' => '/for-businesses', 
                'label' => 'For Businesses',
                'dropdown' => [
                    ['url' => '/accreditation', 'label' => 'BBB Accreditation'],
                    ['url' => '/business-resources', 'label' => 'Business Resources'],
                ]
            ],
        ],
        'userActions' => [
            ['url' => '/login', 'label' => 'Log In'],
            ['url' => '/register', 'label' => 'Register', 'separator' => true],
        ],
        'searchPlaceholder' => 'Search BBB Business Profiles',
        'phoneNumber' => '1-800-123-4567',
        'ctaText' => 'Submit a Complaint',
        'ctaUrl' => '/file-complaint',
        'showSearch' => true,
        'showPhone' => true,
    ];
}

/**
 * Quick BBB header with minimal configuration
 * 
 * @param string $logo Logo path
 * @param array $mainNav Main navigation items
 * @param string $phone Phone number
 * @return string HTML output
 */
function bbb_simple_header(string $logo, array $mainNav, string $phone = ''): string {
    return bbb_header([
        'logo' => $logo,
        'mainNavItems' => $mainNav,
        'phoneNumber' => $phone,
        'topBarItems' => [
            ['url' => '/about', 'label' => 'About Us'],
            ['url' => '/contact', 'label' => 'Contact'],
        ],
        'userActions' => [
            ['url' => '/login', 'label' => 'Log In'],
            ['url' => '/register', 'label' => 'Register', 'separator' => true],
        ],
    ]);
}

/**
 * BBB Mobile Header (Simplified version)
 * 
 * @param array $params Configuration parameters
 * @return string HTML output
 */
function bbb_mobile_header(array $params = []): string {
    $logo = $params['logo'] ?? '';
    $logoAlt = $params['logoAlt'] ?? 'Better Business Bureau';
    $phoneNumber = $params['phoneNumber'] ?? '';
    $ctaText = $params['ctaText'] ?? 'Submit a Complaint';
    $ctaUrl = $params['ctaUrl'] ?? '#';
    
    ob_start(); ?>
    
    <header class="bbb-mobile-header">
        <div class="bbb-mobile-container">
            <button class="bbb-mobile-menu-toggle" aria-label="Open menu" aria-expanded="false">
                <span class="bbb-toggle-bar"></span>
                <span class="bbb-toggle-bar"></span>
                <span class="bbb-toggle-bar"></span>
            </button>
            
            <div class="bbb-mobile-logo">
                <a href="<?= url('/') ?>">
                    <img src="<?= asset_url($logo) ?>" alt="<?= htmlspecialchars($logoAlt) ?>">
                </a>
            </div>
            
            <div class="bbb-mobile-actions">
                <?php if (!empty($phoneNumber)): ?>
                <a href="tel:<?= preg_replace('/[^0-9+]/', '', $phoneNumber) ?>" class="bbb-mobile-phone" aria-label="Call <?= htmlspecialchars($phoneNumber) ?>">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M6.62 10.79c1.44 2.83 3.76 5.14 6.59 6.59l2.2-2.2c.27-.27.67-.36 1.02-.24 1.12.37 2.33.57 3.57.57.55 0 1 .45 1 1V20c0 .55-.45 1-1 1-9.39 0-17-7.61-17-17 0-.55.45-1 1-1h3.5c.55 0 1 .45 1 1 0 1.25.2 2.45.57 3.57.11.35.03.74-.25 1.02l-2.2 2.2z"/>
                    </svg>
                </a>
                <?php endif; ?>
                
                <a href="<?= url($ctaUrl) ?>" class="bbb-mobile-cta">
                    <?= htmlspecialchars($ctaText) ?>
                </a>
            </div>
        </div>
    </header>
    
    <?php return ob_get_clean();
}
?>

<?php

/**
 * Admin Header with User Menu
 */
function admin_header(array $params = []): string {
    $userName = $params['userName'] ?? 'Admin User';
    $userRole = $params['userRole'] ?? 'Administrator';
    $userAvatar = $params['userAvatar'] ?? '';
    $notifications = $params['notifications'] ?? [];
    
    ob_start(); ?>
<header class="admin-header">
    <div class="admin-header-left">
        <button class="sidebar-toggle" id="sidebarToggle">
            <i class="fas fa-bars"></i>
        </button>
        <div class="admin-breadcrumb">
            <span class="breadcrumb-item">Admin Dashboard</span>
        </div>
    </div>
    
    <div class="admin-header-right">
        <div class="admin-search">
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" placeholder="Search..." class="search-input">
            </div>
        </div>
        
        <div class="admin-notifications">
            <button class="notification-btn" id="notificationBtn">
                <i class="fas fa-bell"></i>
                <?php if (!empty($notifications)): ?>
                <span class="notification-badge"><?= count($notifications) ?></span>
                <?php endif; ?>
            </button>
            
            <div class="notification-dropdown" id="notificationDropdown">
                <div class="notification-header">
                    <h4>Notifications</h4>
                    <span class="notification-count"><?= count($notifications) ?> unread</span>
                </div>
                <div class="notification-list">
                    <?php foreach (array_slice($notifications, 0, 5) as $notification): ?>
                    <div class="notification-item">
                        <div class="notification-icon">
                            <i class="fas <?= $notification['icon'] ?>"></i>
                        </div>
                        <div class="notification-content">
                            <p class="notification-text"><?= sanitize_output($notification['text']) ?></p>
                            <span class="notification-time"><?= sanitize_output($notification['time']) ?></span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div class="notification-footer">
                    <a href="/admin/notifications" class="view-all-link">View All Notifications</a>
                </div>
            </div>
        </div>
        
        <div class="admin-user">
            <button class="user-menu-btn" id="userMenuBtn">
                <div class="user-avatar">
                    <?php if ($userAvatar): ?>
                    <img src="<?= asset_url($userAvatar) ?>" alt="<?= sanitize_output($userName) ?>">
                    <?php else: ?>
                    <i class="fas fa-user-circle"></i>
                    <?php endif; ?>
                </div>
                <span class="user-name"><?= sanitize_output($userName) ?></span>
                <i class="fas fa-chevron-down"></i>
            </button>
            
            <div class="user-dropdown" id="userDropdown">
                <div class="user-info">
                    <div class="user-avatar-large">
                        <?php if ($userAvatar): ?>
                        <img src="<?= asset_url($userAvatar) ?>" alt="<?= sanitize_output($userName) ?>">
                        <?php else: ?>
                        <i class="fas fa-user-circle"></i>
                        <?php endif; ?>
                    </div>
                    <div class="user-details">
                        <strong><?= sanitize_output($userName) ?></strong>
                        <span><?= sanitize_output($userRole) ?></span>
                    </div>
                </div>
                <div class="user-links">
                    <a href="/admin/profile" class="user-link">
                        <i class="fas fa-user"></i>
                        My Profile
                    </a>
                    <a href="/admin/settings" class="user-link">
                        <i class="fas fa-cog"></i>
                        Settings
                    </a>
                    <a href="/admin/help" class="user-link">
                        <i class="fas fa-question-circle"></i>
                        Help & Support
                    </a>
                    <div class="user-link-divider"></div>
                    <a href="/logout" class="user-link text-danger">
                        <i class="fas fa-sign-out-alt"></i>
                        Logout
                    </a>
                </div>
            </div>
        </div>
    </div>
</header>
<?php return ob_get_clean();
}


?>