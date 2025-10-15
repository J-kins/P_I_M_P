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
