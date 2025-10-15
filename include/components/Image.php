<?php
/**
 * Image Component
 * 
 * @param array $props Configuration options
 *   - src: string - Image source URL
 *   - alt: string - Alt text for accessibility
 *   - width: int - Image width
 *   - height: int - Image height
 *   - objectFit: string - Object fit: 'cover', 'contain', 'fill', 'none', 'scale-down'
 *   - rounded: string - Border radius: 'none', 'sm', 'md', 'lg', 'full'
 *   - loading: string - Loading strategy: 'lazy', 'eager'
 *   - className: string - Additional CSS classes
 * @return string HTML markup
 */
function Image($props = []) {
    $src = $props['src'] ?? '/placeholder.svg';
    $alt = $props['alt'] ?? '';
    $width = $props['width'] ?? null;
    $height = $props['height'] ?? null;
    $objectFit = $props['objectFit'] ?? 'cover';
    $rounded = $props['rounded'] ?? 'none';
    $loading = $props['loading'] ?? 'lazy';
    $className = $props['className'] ?? '';
    
    $classes = "image image-{$objectFit} image-rounded-{$rounded}";
    if ($className) $classes .= " {$className}";
    
    $widthAttr = $width ? "width=\"{$width}\"" : '';
    $heightAttr = $height ? "height=\"{$height}\"" : '';
    
    ob_start();
    ?>
    <img src="<?php echo htmlspecialchars($src); ?>" 
         alt="<?php echo htmlspecialchars($alt); ?>" 
         class="<?php echo $classes; ?>"
         <?php echo $widthAttr; ?>
         <?php echo $heightAttr; ?>
         loading="<?php echo $loading; ?>">
    <?php
    return ob_get_clean();
}
?>
