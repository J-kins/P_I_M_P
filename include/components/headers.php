
<?php
/**
 * Header components for PHP UI Template System
 */

/**
 * 🏷️ Generates complete document head
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
        
        <?php // 🎨 Theme color meta ?>
        <meta name="theme-color" content="#8a5cf5">
        
        <?php // 🔗 Favicon with cache busting ?>
        <link rel="icon" href="<?= asset_url('img/favicon.ico?v=1') ?>">
        <link rel="apple-touch-icon" href="<?= asset_url('img/apple-touch-icon.png') ?>">
        
        <?php // 📄 Title and SEO ?>
        <title><?= htmlspecialchars($config['title'] ?? 'PHP UI Template System') ?></title>
        
        <?php if (!empty($config['metaTags'])): ?>
            <?php foreach($config['metaTags'] as $name => $content): ?>
                <meta name="<?= htmlspecialchars($name) ?>" content="<?= htmlspecialchars($content) ?>">
            <?php endforeach; ?>
        <?php endif; ?>
        
        <?php if (!empty($config['canonical'])): ?>
            <link rel="canonical" href="<?= htmlspecialchars($config['canonical']) ?>">
        <?php endif; ?>
        
        <?php // 🎨 Base CSS file always included ?>
        <link rel="stylesheet" href="<?= asset_url('css/styles.css') ?>">
        
        <?php // 🎨 Theme-specific CSS ?>
        <link rel="stylesheet" href="<?= asset_url("css/themes/{$theme}.css") ?>">
        
        <?php // 🎨 Additional CSS files ?>
        <?php if (!empty($config['styles'])): ?>
            <?php foreach ($config['styles'] as $css): ?>
                <link rel="stylesheet" href="<?= asset_url($css) ?>">
            <?php endforeach; ?>
        <?php endif; ?>
        
        <?php // 📜 JavaScript files ?>
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
 * 🏗️ Main header component with navigation
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
 * 📱 Modern mobile-first navigation bar
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
 * 🎭 Simple page header with title and subtitle
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
 * 🧭 Hero header with background image and call to action
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
