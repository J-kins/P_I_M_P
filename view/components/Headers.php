<?php
/**
 * P.I.M.P Header Components
 * Comprehensive header system for business repository platform
 */

namespace PIMP\Views\Components;

class Headers
{
    /**
     * Generates complete document head
     * 
     * @param array $config {
     *   @type string $title Page title
     *   @type array $styles Additional CSS files
     *   @type array $scripts JS files to include
     *   @type array $metaTags Additional meta tags
     *   @type string $canonical Canonical URL
     *   @type string $lang Language code
     *   @type string $charset Character set
     *   @type bool $includeFontAwesome Whether to include FontAwesome
     *   @type bool $includeJQuery Whether to include jQuery
     * }
     * @return string HTML for document head
     */
    public static function documentHead(array $config = []): string
    {
        $theme = self::getActiveTheme();
        $includeFontAwesome = $config['includeFontAwesome'] ?? true;
        $includeJQuery = $config['includeJQuery'] ?? true;
        
        ob_start(); ?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($config['lang'] ?? 'en') ?>" data-theme="<?= htmlspecialchars($theme) ?>">
<head>
    <meta charset="<?= htmlspecialchars($config['charset'] ?? 'UTF-8') ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <?php // Theme color meta ?>
    <meta name="theme-color" content="#8a5cf5">
    
    <?php // Favicon ?>
    <link rel="icon" href="<?= self::assetUrl('img/favicon.ico?v=1') ?>">
    <link rel="apple-touch-icon" href="<?= self::assetUrl('img/apple-touch-icon.png') ?>">
    
    <?php if ($includeFontAwesome): ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <?php endif; ?>
    
    <?php // Title and SEO ?>
    <title><?= htmlspecialchars($config['title'] ?? 'PIMP - Business Repository') ?></title>
    
    <?php if (!empty($config['metaTags'])): ?>
        <?php foreach($config['metaTags'] as $name => $content): ?>
            <meta name="<?= htmlspecialchars($name) ?>" content="<?= htmlspecialchars($content) ?>">
        <?php endforeach; ?>
    <?php endif; ?>
    
    <?php if (!empty($config['canonical'])): ?>
        <link rel="canonical" href="<?= htmlspecialchars($config['canonical']) ?>">
    <?php endif; ?>
    
    <?php // Main CSS file ?>
    <link rel="stylesheet" href="<?= self::styleUrl('styles.css') ?>">
    
    <?php // Theme-specific CSS ?>
    <link rel="stylesheet" href="<?= self::styleUrl("themes/{$theme}.css") ?>">
    
    <?php // Additional CSS files ?>
    <?php if (!empty($config['styles'])): ?>
        <?php foreach ($config['styles'] as $css): ?>
            <link rel="stylesheet" href="<?= self::styleUrl($css) ?>">
        <?php endforeach; ?>
    <?php endif; ?>
    
    <?php if ($includeJQuery): ?>
    <script src="<?= self::assetUrl('lib/jquery.min.js') ?>"></script>
    <?php endif; ?>
    
    <?php if (!empty($config['scripts'])): ?>
        <?php foreach ($config['scripts'] as $js): ?>
            <script src="<?= self::assetUrl($js) ?>" defer></script>
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
     *   @type string $searchAction Search form action URL
     *   @type bool $mobileMenuToggle Whether to show mobile menu toggle
     * }
     * @return string HTML output
     */
    public static function headerOne(array $params = []): string
    {
        $title = $params['title'] ?? 'PIMP Business Repository';
        $logo = $params['logo'] ?? '';
        $navItems = $params['navItems'] ?? [];
        $type = $params['type'] ?? 'default';
        $class = $params['class'] ?? '';
        $actions = $params['actions'] ?? [];
        $theme = $params['theme'] ?? 'default';
        $hasSearch = $params['hasSearch'] ?? false;
        $searchPlaceholder = $params['searchPlaceholder'] ?? 'Search businesses...';
        $searchAction = $params['searchAction'] ?? '/search';
        $mobileMenuToggle = $params['mobileMenuToggle'] ?? true;
        
        ob_start(); ?>
<header class="header header-<?= htmlspecialchars($type) ?> header-theme-<?= htmlspecialchars($theme) ?> <?= htmlspecialchars($class) ?>" role="banner">
    <div class="container header-container">
        <div class="header-start">
            <?php if ($mobileMenuToggle): ?>
            <button class="header-mobile-toggle" aria-label="Toggle menu">
                <i class="fas fa-bars"></i>
            </button>
            <?php endif; ?>
            
            <div class="header-logo">
                <?php if (!empty($logo)): ?>
                <a href="<?= self::url('/') ?>" class="logo-link">
                    <img src="<?= self::assetUrl($logo) ?>" alt="<?= htmlspecialchars($title) ?>" class="logo-image">
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
                    <a href="<?= self::url($item['url']) ?>" 
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
                <form action="<?= self::url($searchAction) ?>" method="get" role="search">
                    <input type="search" name="q" placeholder="<?= htmlspecialchars($searchPlaceholder) ?>" 
                           class="search-input" aria-label="Search">
                    <button type="submit" class="search-button" aria-label="Submit search">
                        <i class="fas fa-search"></i>
                    </button>
                </form>
            </div>
            <?php endif; ?>
            
            <?php if (!empty($actions)): ?>
            <div class="header-actions">
                <?php foreach ($actions as $action): ?>
                    <?php if ($action['type'] === 'button'): ?>
                    <a href="<?= self::url($action['url'] ?? '#') ?>" class="button <?= $action['class'] ?? 'button-primary' ?>">
                        <?= htmlspecialchars($action['label']) ?>
                    </a>
                    <?php elseif ($action['type'] === 'icon'): ?>
                    <a href="<?= self::url($action['url'] ?? '#') ?>" class="icon-button" aria-label="<?= htmlspecialchars($action['label']) ?>">
                        <i class="<?= $action['icon'] ?? 'fas fa-question' ?>"></i>
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
    public static function navBar(array $params = []): string
    {
        $title = $params['title'] ?? 'PIMP';
        $logo = $params['logo'] ?? '';
        $navItems = $params['navItems'] ?? [];
        $style = $params['style'] ?? 'minimal';
        $actions = $params['actions'] ?? [];
        $theme = $params['theme'] ?? 'light';
        $hasSearch = $params['hasSearch'] ?? false;
        $searchPlaceholder = $params['searchPlaceholder'] ?? 'Search businesses...';
        
        ob_start(); ?>
<nav class="navbar navbar-<?= htmlspecialchars($style) ?> navbar-theme-<?= htmlspecialchars($theme) ?>">
    <div class="navbar-container">
        <div class="navbar-brand">
            <?php if (!empty($logo)): ?>
            <a href="<?= self::url('/') ?>" class="navbar-logo">
                <img src="<?= self::assetUrl($logo) ?>" alt="<?= htmlspecialchars($title) ?>" class="brand-image">
            </a>
            <?php else: ?>
            <a href="<?= self::url('/') ?>" class="navbar-title"><?= htmlspecialchars($title) ?></a>
            <?php endif; ?>
            
            <button class="navbar-toggle" aria-label="Toggle navigation menu" aria-expanded="false">
                <i class="fas fa-bars"></i>
            </button>
        </div>

        <div class="navbar-collapse">
            <?php if (!empty($navItems)): ?>
            <ul class="navbar-nav">
                <?php foreach ($navItems as $item): ?>
                <li class="nav-item">
                    <a href="<?= self::url($item['url']) ?>" 
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
                <form action="<?= self::url('/search') ?>" method="get">
                    <input type="search" name="q" placeholder="<?= htmlspecialchars($searchPlaceholder) ?>" class="search-input">
                    <button type="submit" class="search-submit" aria-label="Search">
                        <i class="fas fa-search"></i>
                    </button>
                </form>
            </div>
            <?php endif; ?>
            
            <?php if (!empty($actions)): ?>
            <div class="navbar-actions">
                <?php foreach ($actions as $action): ?>
                    <?php if ($action['type'] === 'button'): ?>
                    <a href="<?= self::url($action['url'] ?? '#') ?>" class="navbar-btn <?= $action['class'] ?? 'primary' ?>">
                        <?= htmlspecialchars($action['label']) ?>
                    </a>
                    <?php elseif ($action['type'] === 'icon'): ?>
                    <a href="<?= self::url($action['url'] ?? '#') ?>" class="navbar-icon" aria-label="<?= htmlspecialchars($action['label']) ?>">
                        <i class="<?= $action['icon'] ?? 'fas fa-question' ?>"></i>
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
    public static function pageHeader(string $title, string $subtitle = '', string $class = ''): string
    {
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
    public static function heroHeader(array $params = []): string
    {
        $title = $params['title'] ?? 'Welcome to PIMP';
        $subtitle = $params['subtitle'] ?? 'Business Repository Platform';
        $actions = $params['actions'] ?? [];
        $bgImage = $params['bgImage'] ?? '';
        $overlay = $params['overlay'] ?? 'dark';
        $size = $params['size'] ?? 'md';
        $align = $params['align'] ?? 'center';
        
        ob_start(); ?>
<section class="hero-header hero-<?= htmlspecialchars($size) ?> hero-align-<?= htmlspecialchars($align) ?> hero-overlay-<?= htmlspecialchars($overlay) ?>"
         <?php if (!empty($bgImage)): ?>style="background-image: url('<?= self::assetUrl($bgImage) ?>');"<?php endif; ?>>
    <div class="hero-container">
        <div class="hero-content">
            <h1 class="hero-title"><?= htmlspecialchars($title) ?></h1>
            
            <?php if (!empty($subtitle)): ?>
            <div class="hero-subtitle"><?= $subtitle ?></div>
            <?php endif; ?>
            
            <?php if (!empty($actions)): ?>
            <div class="hero-actions">
                <?php foreach ($actions as $action): ?>
                <a href="<?= self::url($action['url'] ?? '#') ?>" class="button <?= $action['class'] ?? 'button-primary' ?>">
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

    /**
     * Business repository header with top bar and main navigation
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
    public static function businessHeader(array $params = []): string
    {
        $logo = $params['logo'] ?? '';
        $logoAlt = $params['logoAlt'] ?? 'Business Repository';
        $topBarItems = $params['topBarItems'] ?? [];
        $mainNavItems = $params['mainNavItems'] ?? [];
        $userActions = $params['userActions'] ?? [];
        $searchPlaceholder = $params['searchPlaceholder'] ?? 'Search business profiles...';
        $phoneNumber = $params['phoneNumber'] ?? '';
        $ctaText = $params['ctaText'] ?? 'Submit Review';
        $ctaUrl = $params['ctaUrl'] ?? '#';
        $showSearch = $params['showSearch'] ?? true;
        $showPhone = $params['showPhone'] ?? true;
        
        ob_start(); ?>
        
<!-- Top Bar -->
<div class="business-top-bar">
    <div class="business-container">
        <nav class="business-top-nav" aria-label="Secondary navigation">
            <ul class="business-top-nav-list">
                <?php foreach ($topBarItems as $item): ?>
                <li class="business-top-nav-item">
                    <a href="<?= self::url($item['url']) ?>" class="business-top-nav-link">
                        <?= htmlspecialchars($item['label']) ?>
                    </a>
                </li>
                <?php endforeach; ?>
            </ul>
        </nav>
        
        <div class="business-user-actions">
            <?php foreach ($userActions as $action): ?>
            <a href="<?= self::url($action['url']) ?>" class="business-user-action-link">
                <?= htmlspecialchars($action['label']) ?>
            </a>
            <?php if (!empty($action['separator'])): ?>
            <span class="business-separator">|</span>
            <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Main Header -->
<header class="business-main-header" role="banner">
    <div class="business-container">
        <div class="business-header-left">
            <div class="business-logo">
                <a href="<?= self::url('/') ?>" class="business-logo-link">
                    <img src="<?= self::assetUrl($logo) ?>" alt="<?= htmlspecialchars($logoAlt) ?>" class="business-logo-image">
                </a>
            </div>
            
            <?php if (!empty($mainNavItems)): ?>
            <nav class="business-main-nav" aria-label="Main navigation">
                <ul class="business-main-nav-list">
                    <?php foreach ($mainNavItems as $item): ?>
                    <li class="business-main-nav-item">
                        <a href="<?= self::url($item['url']) ?>" 
                           class="business-main-nav-link <?= ($item['active'] ?? false) ? 'active' : '' ?>"
                           <?= ($item['active'] ?? false) ? 'aria-current="page"' : '' ?>">
                            <?= htmlspecialchars($item['label']) ?>
                            <?php if (!empty($item['dropdown'])): ?>
                            <i class="fas fa-chevron-down business-dropdown-arrow"></i>
                            <?php endif; ?>
                        </a>
                        
                        <?php if (!empty($item['dropdown'])): ?>
                        <div class="business-dropdown-menu">
                            <?php foreach ($item['dropdown'] as $dropdownItem): ?>
                            <a href="<?= self::url($dropdownItem['url']) ?>" class="business-dropdown-link">
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
        
        <div class="business-header-right">
            <?php if ($showSearch): ?>
            <div class="business-search-box">
                <form action="<?= self::url('/search') ?>" method="get" role="search" class="business-search-form">
                    <div class="business-search-wrapper">
                        <input type="search" name="q" placeholder="<?= htmlspecialchars($searchPlaceholder) ?>" 
                               class="business-search-input" aria-label="Search businesses">
                        <button type="submit" class="business-search-button" aria-label="Search">
                            <i class="fas fa-search business-search-icon"></i>
                        </button>
                    </div>
                </form>
            </div>
            <?php endif; ?>
            
            <?php if ($showPhone && !empty($phoneNumber)): ?>
            <div class="business-phone-number">
                <span class="business-phone-label">Call Us:</span>
                <a href="tel:<?= preg_replace('/[^0-9+]/', '', $phoneNumber) ?>" class="business-phone-link">
                    <?= htmlspecialchars($phoneNumber) ?>
                </a>
            </div>
            <?php endif; ?>
            
            <div class="business-cta-section">
                <a href="<?= self::url($ctaUrl) ?>" class="business-cta-button">
                    <?= htmlspecialchars($ctaText) ?>
                </a>
            </div>
            
            <!-- Mobile menu toggle -->
            <button class="business-mobile-toggle" aria-label="Toggle mobile menu" aria-expanded="false">
                <i class="fas fa-bars"></i>
            </button>
        </div>
    </div>
</header>
        <?php return ob_get_clean();
    }

    /**
     * Admin Header with User Menu
     * 
     * @param array $params {
     *   @type string $userName Admin user name
     *   @type string $userRole User role
     *   @type string $userAvatar User avatar image path
     *   @type array $notifications Array of notifications
     * }
     * @return string HTML output
     */
    public static function adminHeader(array $params = []): string
    {
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
                            <i class="fas <?= $notification['icon'] ?? 'fa-info-circle' ?>"></i>
                        </div>
                        <div class="notification-content">
                            <p class="notification-text"><?= htmlspecialchars($notification['text'] ?? '') ?></p>
                            <span class="notification-time"><?= htmlspecialchars($notification['time'] ?? '') ?></span>
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
                    <img src="<?= self::assetUrl($userAvatar) ?>" alt="<?= htmlspecialchars($userName) ?>">
                    <?php else: ?>
                    <i class="fas fa-user-circle"></i>
                    <?php endif; ?>
                </div>
                <span class="user-name"><?= htmlspecialchars($userName) ?></span>
                <i class="fas fa-chevron-down"></i>
            </button>
            
            <div class="user-dropdown" id="userDropdown">
                <div class="user-info">
                    <div class="user-avatar-large">
                        <?php if ($userAvatar): ?>
                        <img src="<?= self::assetUrl($userAvatar) ?>" alt="<?= htmlspecialchars($userName) ?>">
                        <?php else: ?>
                        <i class="fas fa-user-circle"></i>
                        <?php endif; ?>
                    </div>
                    <div class="user-details">
                        <strong><?= htmlspecialchars($userName) ?></strong>
                        <span><?= htmlspecialchars($userRole) ?></span>
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

    /**
     * URL helper method - uses PIMP Config class
     */
    private static function url(string $path): string
    {
        if (class_exists('PIMP\\Core\\Config')) {
            return \PIMP\Core\Config::url($path);
        }
        
        // Fallback for development
        return $path;
    }

    /**
     * Asset URL helper method - uses PIMP Config class
     */
    private static function assetUrl(string $path): string
    {
        if (class_exists('PIMP\\Core\\Config')) {
            return \PIMP\Core\Config::assetUrl($path);
        }
        
        // Fallback for development
        return $path;
    }

    /**
     * Style URL helper method - uses PIMP Config class
     */
    private static function styleUrl(string $path): string
    {
        if (class_exists('PIMP\\Core\\Config')) {
            return \PIMP\Core\Config::styleUrl($path);
        }
        
        // Fallback for development
        return 'styles/' . ltrim($path, '/');
    }

    /**
     * Get active theme - uses PIMP Config class
     */
    private static function getActiveTheme(): string
    {
        if (class_exists('PIMP\\Core\\Config')) {
            return \PIMP\Core\Config::getActiveTheme();
        }
        
        // Fallback for development
        return 'purple1';
    }
}