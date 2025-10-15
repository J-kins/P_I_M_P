<?php
/**
 * Avatar Component
 * 
 * @param array $props Configuration options
 *   - src: string - Image source URL
 *   - alt: string - Alt text
 *   - initials: string - Fallback initials
 *   - size: string - Size: 'xs', 'sm', 'md', 'lg', 'xl', '2xl'
 *   - shape: string - Shape: 'circle', 'square'
 *   - status: string - Status indicator: 'online', 'offline', 'away', 'busy'
 *   - className: string - Additional CSS classes
 * @return string HTML markup
 */
function Avatar($props = []) {
    $src = $props['src'] ?? null;
    $alt = $props['alt'] ?? 'Avatar';
    $initials = $props['initials'] ?? '';
    $size = $props['size'] ?? 'md';
    $shape = $props['shape'] ?? 'circle';
    $status = $props['status'] ?? null;
    $className = $props['className'] ?? '';
    
    $classes = "avatar avatar-{$size} avatar-{$shape}";
    if ($className) $classes .= " {$className}";
    
    ob_start();
    ?>
    <div class="<?php echo $classes; ?>">
        <?php if ($src): ?>
            <img src="<?php echo htmlspecialchars($src); ?>" alt="<?php echo htmlspecialchars($alt); ?>" class="avatar-image">
        <?php elseif ($initials): ?>
            <span class="avatar-initials"><?php echo htmlspecialchars($initials); ?></span>
        <?php else: ?>
            <svg class="avatar-placeholder" viewBox="0 0 24 24" fill="currentColor">
                <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
            </svg>
        <?php endif; ?>
        <?php if ($status): ?>
            <span class="avatar-status avatar-status-<?php echo $status; ?>" aria-label="<?php echo ucfirst($status); ?>"></span>
        <?php endif; ?>
    </div>
    <?php
    return ob_get_clean();
}
?>
