
<?php
/**
 * Navigation components for PHP UI Template System
 */

/**
 * 📍 Generate a navigation bar with flexible positioning
 * 
 * @param array $params {
 *   @type array $items Navigation items
 *   @type string $activeUrl Current active URL
 *   @type string $position Position (horizontal|vertical)
 *   @type string $align Alignment (start|center|end)
 *   @type string $class Additional CSS classes
 * }
 * @return string HTML output
 */
function navigationBar(array $params = []): string {
    $items = $params['items'] ?? [];
    $activeUrl = $params['activeUrl'] ?? '';
    $position = $params['position'] ?? 'horizontal';
    $align = $params['align'] ?? 'start';
    $class = $params['class'] ?? '';
    
    ob_start(); ?>
    <nav class="navigation navigation-<?= htmlspecialchars($position) ?> navigation-align-<?= htmlspecialchars($align) ?> <?= htmlspecialchars($class) ?>">
        <ul class="navigation-list">
            <?php foreach ($items as $item): ?>
            <li class="navigation-item">
                <a href="<?= url($item['url']) ?>" 
                   class="navigation-link <?= ($item['url'] === $activeUrl || ($item['active'] ?? false)) ? 'active' : '' ?>"
                   <?= ($item['url'] === $activeUrl || ($item['active'] ?? false)) ? 'aria-current="page"' : '' ?>>
                    <?php if (!empty($item['icon'])): ?>
                    <span class="navigation-icon"><?= $item['icon'] ?></span>
                    <?php endif; ?>
                    <span class="navigation-label"><?= htmlspecialchars($item['label']) ?></span>
                </a>
            </li>
            <?php endforeach; ?>
        </ul>
    </nav>
    <?php return ob_get_clean();
}

/**
 * 🧭 Breadcrumbs navigation
 * 
 * @param array $paths Array of path items [['url' => '', 'label' => '']]
 * @param string $separator Custom separator (optional)
 * @param string $class Additional CSS classes
 * @return string HTML output
 */
function breadcrumbs(array $paths, string $separator = '/', string $class = ''): string {
    ob_start(); ?>
    <nav class="breadcrumbs <?= htmlspecialchars($class) ?>" aria-label="Breadcrumbs">
        <ol class="breadcrumbs-list">
            <?php foreach ($paths as $index => $path): ?>
                <li class="breadcrumbs-item">
                    <?php if ($index === count($paths) - 1): ?>
                        <span class="breadcrumbs-current" aria-current="page">
                            <?= htmlspecialchars($path['label']) ?>
                        </span>
                    <?php else: ?>
                        <a href="<?= url($path['url']) ?>" class="breadcrumbs-link">
                            <?= htmlspecialchars($path['label']) ?>
                        </a>
                        <span class="breadcrumbs-separator" aria-hidden="true"><?= $separator ?></span>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ol>
    </nav>
    <?php return ob_get_clean();
}

/**
 * 📑 Tabbed navigation with content panels
 * 
 * @param array $tabs {
 *   @type string $id Tab ID
 *   @type string $label Tab label
 *   @type string $content Tab content
 *   @type bool $active Whether tab is active
 * }
 * @param string $tabsId Unique ID for the tabs component
 * @param string $class Additional CSS classes
 * @return string HTML output
 */
function tabbedNav(array $tabs, string $tabsId = 'tabs', string $class = ''): string {
    // Ensure at least one tab is active
    $hasActive = false;
    foreach ($tabs as $tab) {
        if ($tab['active'] ?? false) {
            $hasActive = true;
            break;
        }
    }
    
    if (!$hasActive && !empty($tabs)) {
        $tabs[0]['active'] = true;
    }
    
    ob_start(); ?>
    <div class="tabs <?= htmlspecialchars($class) ?>" id="<?= htmlspecialchars($tabsId) ?>">
        <div class="tabs-nav" role="tablist">
            <?php foreach ($tabs as $tab): ?>
            <button class="tab-button <?= ($tab['active'] ?? false) ? 'active' : '' ?>" 
                    id="tab-<?= htmlspecialchars($tab['id']) ?>" 
                    role="tab" 
                    aria-controls="panel-<?= htmlspecialchars($tab['id']) ?>"
                    aria-selected="<?= ($tab['active'] ?? false) ? 'true' : 'false' ?>"
                    data-tab="<?= htmlspecialchars($tab['id']) ?>">
                <?php if (!empty($tab['icon'])): ?>
                <span class="tab-icon"><?= $tab['icon'] ?></span>
                <?php endif; ?>
                <span class="tab-label"><?= htmlspecialchars($tab['label']) ?></span>
            </button>
            <?php endforeach; ?>
        </div>
        
        <div class="tabs-content">
            <?php foreach ($tabs as $tab): ?>
            <div class="tab-panel <?= ($tab['active'] ?? false) ? 'active' : '' ?>" 
                 id="panel-<?= htmlspecialchars($tab['id']) ?>" 
                 role="tabpanel" 
                 aria-labelledby="tab-<?= htmlspecialchars($tab['id']) ?>"
                 data-panel="<?= htmlspecialchars($tab['id']) ?>">
                <?= $tab['content'] ?>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php return ob_get_clean();
}

/**
 * 📲 Mobile navigation menu (hamburger menu)
 * 
 * @param array $params {
 *   @type array $items Navigation items
 *   @type string $activeUrl Current active URL
 *   @type string $breakpoint Breakpoint for mobile menu (sm|md|lg)
 *   @type string $position Menu position (left|right)
 *   @type string $theme Color theme (light|dark|primary)
 * }
 * @return string HTML output
 */
function mobileNav(array $params = []): string {
    $items = $params['items'] ?? [];
    $activeUrl = $params['activeUrl'] ?? '';
    $breakpoint = $params['breakpoint'] ?? 'md';
    $position = $params['position'] ?? 'right';
    $theme = $params['theme'] ?? 'light';
    
    ob_start(); ?>
    <div class="mobile-nav-container mobile-nav-bp-<?= htmlspecialchars($breakpoint) ?> mobile-nav-pos-<?= htmlspecialchars($position) ?> mobile-nav-theme-<?= htmlspecialchars($theme) ?>">
        <button class="mobile-nav-toggle" aria-label="Toggle menu" aria-expanded="false">
            <span class="mobile-nav-icon"></span>
        </button>
        
        <div class="mobile-nav-backdrop"></div>
        
        <nav class="mobile-nav" aria-label="Mobile navigation">
            <div class="mobile-nav-header">
                <button class="mobile-nav-close" aria-label="Close menu">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>
            
            <ul class="mobile-nav-list">
                <?php foreach ($items as $item): ?>
                <li class="mobile-nav-item">
                    <a href="<?= url($item['url']) ?>" 
                       class="mobile-nav-link <?= ($item['url'] === $activeUrl || ($item['active'] ?? false)) ? 'active' : '' ?>"
                       <?= ($item['url'] === $activeUrl || ($item['active'] ?? false)) ? 'aria-current="page"' : '' ?>>
                        <?php if (!empty($item['icon'])): ?>
                        <span class="mobile-nav-icon"><?= $item['icon'] ?></span>
                        <?php endif; ?>
                        <span class="mobile-nav-label"><?= htmlspecialchars($item['label']) ?></span>
                    </a>
                    
                    <?php if (!empty($item['children'])): ?>
                    <button class="mobile-nav-expander" aria-label="Toggle submenu">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="6 9 12 15 18 9"></polyline>
                        </svg>
                    </button>
                    
                    <ul class="mobile-subnav-list">
                        <?php foreach ($item['children'] as $child): ?>
                        <li class="mobile-subnav-item">
                            <a href="<?= url($child['url']) ?>" 
                               class="mobile-subnav-link <?= ($child['url'] === $activeUrl || ($child['active'] ?? false)) ? 'active' : '' ?>"
                               <?= ($child['url'] === $activeUrl || ($child['active'] ?? false)) ? 'aria-current="page"' : '' ?>>
                                <?= htmlspecialchars($child['label']) ?>
                            </a>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                    <?php endif; ?>
                </li>
                <?php endforeach; ?>
            </ul>
        </nav>
    </div>
    <?php return ob_get_clean();
}

/**
 * 🧠 Sidebar navigation component
 * 
 * @param array $params {
 *   @type array $items Navigation items
 *   @type string $activeUrl Current active URL
 *   @type bool $collapsed Initial collapsed state
 *   @type string $theme Color theme (light|dark|primary)
 *   @type string $logo Logo or title in sidebar (optional)
 * }
 * @return string HTML output
 */
function sidebarNav(array $params = []): string {
    $items = $params['items'] ?? [];
    $activeUrl = $params['activeUrl'] ?? '';
    $collapsed = $params['collapsed'] ?? false;
    $theme = $params['theme'] ?? 'light';
    $logo = $params['logo'] ?? '';
    
    ob_start(); ?>
    <aside class="sidebar-nav sidebar-theme-<?= htmlspecialchars($theme) ?> <?= $collapsed ? 'collapsed' : '' ?>">
        <?php if (!empty($logo)): ?>
        <div class="sidebar-header">
            <div class="sidebar-logo">
                <?php if (filter_var($logo, FILTER_VALIDATE_URL) || strpos($logo, '/') !== false): ?>
                <img src="<?= asset_url($logo) ?>" alt="Logo" class="logo-image">
                <?php else: ?>
                <div class="logo-text"><?= htmlspecialchars($logo) ?></div>
                <?php endif; ?>
            </div>
            
            <button class="sidebar-toggle" aria-label="Toggle sidebar">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M15 18l-6-6 6-6"></path>
                </svg>
            </button>
        </div>
        <?php endif; ?>
        
        <nav class="sidebar-menu">
            <ul class="sidebar-list">
                <?php foreach ($items as $item): ?>
                <li class="sidebar-item 
                    <?= ($item['url'] === $activeUrl || ($item['active'] ?? false)) ? 'active' : '' ?> 
                    <?= !empty($item['children']) ? 'has-children' : '' ?>">
                    
                    <a href="<?= url($item['url']) ?>" class="sidebar-link">
                        <?php if (!empty($item['icon'])): ?>
                        <span class="sidebar-icon"><?= $item['icon'] ?></span>
                        <?php endif; ?>
                        <span class="sidebar-label"><?= htmlspecialchars($item['label']) ?></span>
                    </a>
                    
                    <?php if (!empty($item['children'])): ?>
                    <button class="sidebar-expander" aria-label="Toggle submenu">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="6 9 12 15 18 9"></polyline>
                        </svg>
                    </button>
                    
                    <ul class="sidebar-submenu">
                        <?php foreach ($item['children'] as $child): ?>
                        <li class="sidebar-submenu-item <?= ($child['url'] === $activeUrl || ($child['active'] ?? false)) ? 'active' : '' ?>">
                            <a href="<?= url($child['url']) ?>" class="sidebar-submenu-link">
                                <?= htmlspecialchars($child['label']) ?>
                            </a>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                    <?php endif; ?>
                </li>
                <?php endforeach; ?>
            </ul>
        </nav>
    </aside>
    <?php return ob_get_clean();
}

/**
 * 🌐 Social media links bar
 * 
 * @param array $links Social media links with platform, url and optional icon
 * @param string $layout Layout style (horizontal|vertical|grid)
 * @param string $theme Color theme (light|dark|primary|custom)
 * @return string HTML output
 */
function socialLinks(array $links, string $layout = 'horizontal', string $theme = 'light'): string {
    ob_start(); ?>
    <div class="social-links social-<?= htmlspecialchars($layout) ?> social-theme-<?= htmlspecialchars($theme) ?>">
        <?php foreach ($links as $link): ?>
        <a href="<?= htmlspecialchars($link['url']) ?>" 
           class="social-link <?= htmlspecialchars($link['platform']) ?>"
           aria-label="<?= htmlspecialchars($link['label'] ?? ucfirst($link['platform'])) ?>"
           <?= (!empty($link['newTab'])) ? 'target="_blank" rel="noopener"' : '' ?>>
            <?php if (!empty($link['icon'])): ?>
                <?= $link['icon'] ?>
            <?php else: ?>
                <span class="social-platform-name"><?= htmlspecialchars(ucfirst($link['platform'])) ?></span>
            <?php endif; ?>
        </a>
        <?php endforeach; ?>
    </div>
    <?php return ob_get_clean();
}

/**
 * 🔍 Search form component
 * 
 * @param array $params {
 *   @type string $placeholder Placeholder text
 *   @type string $action Form action URL
 *   @type string $layout Layout style (inline|compact|expanded|overlay)
 *   @type array $categories Optional search categories for dropdown
 * }
 * @return string HTML output
 */
function searchForm(array $params = []): string {
    $placeholder = $params['placeholder'] ?? 'Search';
    $action = $params['action'] ?? '/search';
    $layout = $params['layout'] ?? 'inline';
    $categories = $params['categories'] ?? [];
    
    ob_start(); ?>
    <div class="search-form search-<?= htmlspecialchars($layout) ?>">
        <form action="<?= url($action) ?>" method="get" role="search">
            <div class="search-wrapper">
                <?php if (!empty($categories)): ?>
                <div class="search-category">
                    <select name="category" aria-label="Search category">
                        <?php foreach ($categories as $category): ?>
                        <option value="<?= htmlspecialchars($category['value']) ?>">
                            <?= htmlspecialchars($category['label']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php endif; ?>
                
                <div class="search-input-wrapper">
                    <input type="search" name="q" placeholder="<?= htmlspecialchars($placeholder) ?>" 
                           class="search-input" aria-label="Search input">
                    <button type="submit" class="search-button" aria-label="Submit search">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="11" cy="11" r="8"></circle>
                            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                        </svg>
                    </button>
                </div>
            </div>
        </form>
    </div>
    <?php return ob_get_clean();
}
?>
