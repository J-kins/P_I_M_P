<?php
/**
 * Link Component
 * 
 * @param array $props Configuration options
 *   - href: string - Link URL
 *   - text: string - Link text
 *   - variant: string - Style variant: 'default', 'primary', 'secondary', 'underline', 'muted'
 *   - size: string - Size: 'sm', 'md', 'lg'
 *   - external: bool - Opens in new tab, default: false
 *   - disabled: bool - Disabled state, default: false
 *   - className: string - Additional CSS classes
 * @return string HTML markup
 */
function Linx($props = []) {
    $href = $props['href'] ?? '#';
    $text = $props['text'] ?? 'Link';
    $variant = $props['variant'] ?? 'default';
    $size = $props['size'] ?? 'md';
    $external = $props['external'] ?? false;
    $disabled = $props['disabled'] ?? false;
    $className = $props['className'] ?? '';
    
    $classes = "link link-{$variant} link-{$size}";
    if ($disabled) $classes .= ' link-disabled';
    if ($className) $classes .= " {$className}";
    
    $target = $external ? 'target="_blank" rel="noopener noreferrer"' : '';
    $ariaDisabled = $disabled ? 'aria-disabled="true"' : '';
    
    ob_start();
    ?>
    <a href="<?php echo $disabled ? 'javascript:void(0)' : htmlspecialchars($href); ?>" 
       class="<?php echo $classes; ?>" 
       <?php echo $target; ?>
       <?php echo $ariaDisabled; ?>>
        <?php echo htmlspecialchars($text); ?>
        <?php if ($external): ?>
            <svg class="link-external-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path>
                <polyline points="15 3 21 3 21 9"></polyline>
                <line x1="10" y1="14" x2="21" y2="3"></line>
            </svg>
        <?php endif; ?>
    </a>
    <?php
    return ob_get_clean();
}
?>
