<?php
/**
 * Progress Component
 * 
 * @param array $props Configuration options
 *   - value: int - Progress value (0-100) default: 0
 *   - max: int - Maximum value, default: 100
 *   - size: string - Size (sm|md|lg) default: 'md'
 *   - variant: string - Variant (default|success|warning|error|info) default: 'default'
 *   - showLabel: bool - Show percentage label, default: false
 *   - striped: bool - Striped pattern, default: false
 *   - animated: bool - Animated stripes, default: false
 *   - className: string - Additional CSS classes
 * @return string HTML markup
 */
function Progress($props = []) {
    $value = $props['value'] ?? 0;
    $max = $props['max'] ?? 100;
    $size = $props['size'] ?? 'md';
    $variant = $props['variant'] ?? 'default';
    $showLabel = $props['showLabel'] ?? false;
    $striped = $props['striped'] ?? false;
    $animated = $props['animated'] ?? false;
    $className = $props['className'] ?? '';
    
    $percentage = ($value / $max) * 100;
    $stripedClass = $striped ? 'progress-striped' : '';
    $animatedClass = $animated ? 'progress-animated' : '';
    
    ob_start();
    ?>
    <div class="progress progress-<?php echo $size; ?> <?php echo $className; ?>" 
         role="progressbar" 
         aria-valuenow="<?php echo $value; ?>" 
         aria-valuemin="0" 
         aria-valuemax="<?php echo $max; ?>">
        <div class="progress-bar progress-<?php echo $variant; ?> <?php echo $stripedClass; ?> <?php echo $animatedClass; ?>" 
             style="width: <?php echo $percentage; ?>%">
            <?php if ($showLabel): ?>
                <span class="progress-label"><?php echo round($percentage); ?>%</span>
            <?php endif; ?>
        </div>
    </div>
    <?php
    return ob_get_clean();
}
?>
