<?php
/**
 * Navigation Component
 * 
 * @param array $props Configuration options
 *   - items: array - Navigation items [{label, href, active, icon, children}]
 *   - logo: string - Logo HTML
 *   - variant: string - Variant (horizontal|vertical) default: 'horizontal'
 *   - sticky: bool - Sticky positioning, default: false
 *   - className: string - Additional CSS classes
 * @return string HTML markup
 */
function Navigation($props = []) {
    $items = $props['items'] ?? [];
    $logo = $props['logo'] ?? '';
    $variant = $props['variant'] ?? 'horizontal';
    $sticky = $props['sticky'] ?? false;
    $className = $props['className'] ?? '';
    
    $stickyClass = $sticky ? 'nav-sticky' : '';
    
    function renderNavItems($items, $level = 0) {
        $html = '';
        foreach ($items as $item) {
            $activeClass = ($item['active'] ?? false) ? 'nav-item-active' : '';
            $hasChildren = !empty($item['children']);
            
            $html .= '<li class="nav-item ' . $activeClass . '">';
            $html .= '<a href="' . ($item['href'] ?? '#') . '" class="nav-link">';
            if (!empty($item['icon'])) {
                $html .= '<span class="nav-icon">' . $item['icon'] . '</span>';
            }
            $html .= '<span class="nav-label">' . $item['label'] . '</span>';
            if ($hasChildren) {
                $html .= '<svg class="nav-chevron" width="16" height="16" viewBox="0 0 16 16" fill="none">
                    <path d="M4 6L8 10L12 6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                </svg>';
            }
            $html .= '</a>';
            
            if ($hasChildren) {
                $html .= '<ul class="nav-submenu">';
                $html .= renderNavItems($item['children'], $level + 1);
                $html .= '</ul>';
            }
            
            $html .= '</li>';
        }
        return $html;
    }
    
    ob_start();
    ?>
    <nav class="navigation nav-<?php echo $variant; ?> <?php echo $stickyClass; ?> <?php echo $className; ?>" 
         role="navigation">
        <?php if ($logo): ?>
            <div class="nav-logo"><?php echo $logo; ?></div>
        <?php endif; ?>
        <ul class="nav-menu">
            <?php echo renderNavItems($items); ?>
        </ul>
    </nav>
    <?php
    return ob_get_clean();
}
?>
