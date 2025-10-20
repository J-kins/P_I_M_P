<?php
/**
 * Sidebar Component
 * 
 * @param array $props Configuration options
 *   - items: array - Sidebar items [{label, href, active, icon, badge}]
 *   - header: string - Sidebar header HTML
 *   - footer: string - Sidebar footer HTML
 *   - collapsible: bool - Collapsible sidebar, default: false
 *   - collapsed: bool - Initial collapsed state, default: false
 *   - position: string - Position (left|right) default: 'left'
 *   - className: string - Additional CSS classes
 * @return string HTML markup
 */
function Sidebar($props = []) {
    $items = $props['items'] ?? [];
    $header = $props['header'] ?? '';
    $footer = $props['footer'] ?? '';
    $collapsible = $props['collapsible'] ?? false;
    $collapsed = $props['collapsed'] ?? false;
    $position = $props['position'] ?? 'left';
    $className = $props['className'] ?? '';
    
    $collapsedClass = $collapsed ? 'sidebar-collapsed' : '';
    
    ob_start();
    ?>
    <aside class="sidebar sidebar-<?php echo $position; ?> <?php echo $collapsedClass; ?> <?php echo $className; ?>" 
           role="complementary">
        <?php if ($header): ?>
            <div class="sidebar-header"><?php echo $header; ?></div>
        <?php endif; ?>
        
        <nav class="sidebar-nav">
            <ul class="sidebar-menu">
                <?php foreach ($items as $item): ?>
                    <li class="sidebar-item <?php echo ($item['active'] ?? false) ? 'sidebar-item-active' : ''; ?>">
                        <a href="<?php echo $item['href'] ?? '#'; ?>" class="sidebar-link">
                            <?php if (!empty($item['icon'])): ?>
                                <span class="sidebar-icon"><?php echo $item['icon']; ?></span>
                            <?php endif; ?>
                            <span class="sidebar-label"><?php echo $item['label']; ?></span>
                            <?php if (!empty($item['badge'])): ?>
                                <span class="sidebar-badge"><?php echo $item['badge']; ?></span>
                            <?php endif; ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </nav>
        
        <?php if ($footer): ?>
            <div class="sidebar-footer"><?php echo $footer; ?></div>
        <?php endif; ?>
        
        <?php if ($collapsible): ?>
            <button class="sidebar-toggle" aria-label="Toggle sidebar">
                <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                    <path d="M8 4L14 10L8 16" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                </svg>
            </button>
        <?php endif; ?>
    </aside>
    <?php
    return ob_get_clean();
}
?>

<?php

/**
 * Admin Sidebar Navigation
 */
function admin_sidebar(array $params = []): string {
    $menuItems = $params['menuItems'] ?? [];
    $activeItem = $params['activeItem'] ?? '';
    
    ob_start(); ?>
<aside class="admin-sidebar" id="adminSidebar">
    <div class="sidebar-header">
        <div class="sidebar-logo">
            <svg width="32" height="32" viewBox="0 0 32 32" fill="currentColor">
                <path d="M16 2C8.28 2 2 8.28 2 16s6.28 14 14 14 14-6.28 14-14S23.72 2 16 2zm0 26C9.38 28 4 22.62 4 16S9.38 4 16 4s12 5.38 12 12-5.38 12-12 12z"/>
                <path d="M21 12H11c-.55 0-1 .45-1 1v6c0 .55.45 1 1 1h10c.55 0 1-.45 1-1v-6c0-.55-.45-1-1-1zm-1 6H12v-4h8v4z"/>
            </svg>
            <span class="logo-text">BBB Admin</span>
        </div>
    </div>
    
    <nav class="sidebar-nav">
        <ul class="sidebar-menu">
            <?php foreach ($menuItems as $item): ?>
            <li class="sidebar-item <?= ($item['id'] === $activeItem) ? 'active' : '' ?>">
                <a href="<?= url($item['url']) ?>" class="sidebar-link">
                    <span class="sidebar-icon">
                        <?php if (strpos($item['icon'], 'fas') === 0): ?>
                            <i class="<?= $item['icon'] ?>"></i>
                        <?php else: ?>
                            <i class="lnr <?= $item['icon'] ?>"></i>
                        <?php endif; ?>
                    </span>
                    <span class="sidebar-label"><?= sanitize_output($item['label']) ?></span>
                    <?php if (!empty($item['badge'])): ?>
                    <span class="sidebar-badge"><?= $item['badge'] ?></span>
                    <?php endif; ?>
                    <?php if (!empty($item['children'])): ?>
                    <span class="sidebar-arrow">
                        <i class="fas fa-chevron-down"></i>
                    </span>
                    <?php endif; ?>
                </a>
                
                <?php if (!empty($item['children'])): ?>
                <ul class="sidebar-submenu">
                    <?php foreach ($item['children'] as $child): ?>
                    <li class="sidebar-subitem">
                        <a href="<?= url($child['url']) ?>" class="sidebar-sublink">
                            <?= sanitize_output($child['label']) ?>
                            <?php if (!empty($child['badge'])): ?>
                            <span class="sidebar-badge"><?= $child['badge'] ?></span>
                            <?php endif; ?>
                        </a>
                    </li>
                    <?php endforeach; ?>
                </ul>
                <?php endif; ?>
            </li>
            <?php endforeach; ?>
        </ul>
    </nav>
    
    <div class="sidebar-footer">
        <div class="sidebar-theme-toggle">
            <i class="fas fa-moon"></i>
            <span>Dark Mode</span>
            <div class="theme-switch">
                <input type="checkbox" id="themeSwitch" class="theme-checkbox">
                <label for="themeSwitch" class="theme-label"></label>
            </div>
        </div>
    </div>
</aside>
<?php return ob_get_clean();
}


?>