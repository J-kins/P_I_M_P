<?php
/**
 * Icon Component
 * 
 * @param array $props Configuration options
 *   - name: string - Icon name
 *   - size: string - Size: 'xs', 'sm', 'md', 'lg', 'xl'
 *   - color: string - Icon color
 *   - className: string - Additional CSS classes
 * @return string HTML markup
 */
function Icon($props = []) {
    $name = $props['name'] ?? 'default';
    $size = $props['size'] ?? 'md';
    $color = $props['color'] ?? 'currentColor';
    $className = $props['className'] ?? '';
    
    $classes = "icon icon-{$size}";
    if ($className) $classes .= " {$className}";
    
    $icons = [
        'check' => '<path d="M20 6L9 17l-5-5" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"/>',
        'close' => '<path d="M18 6L6 18M6 6l12 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>',
        'arrow-right' => '<path d="M5 12h14M12 5l7 7-7 7" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"/>',
        'arrow-left' => '<path d="M19 12H5M12 19l-7-7 7-7" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"/>',
        'search' => '<circle cx="11" cy="11" r="8" stroke="currentColor" stroke-width="2" fill="none"/><path d="M21 21l-4.35-4.35" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>',
        'user' => '<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2M12 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8z" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"/>',
        'settings' => '<circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="2" fill="none"/><path d="M12 1v6m0 6v6M23 12h-6m-6 0H1" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>',
        'default' => '<circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2" fill="none"/>'
    ];
    
    $iconPath = $icons[$name] ?? $icons['default'];
    
    ob_start();
    ?>
    <svg class="<?php echo $classes; ?>" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" style="color: <?php echo $color; ?>">
        <?php echo $iconPath; ?>
    </svg>
    <?php
    return ob_get_clean();
}
?>
