<?php
/**
 * Spinner Component
 * Renders a loading spinner
 * 
 * @param array $props Configuration options
 * @return string HTML output
 */

function Spinner($props = []) {
    $defaults = [
        'size' => 'md',         // xs, sm, md, lg, xl
        'color' => 'primary',   // primary, secondary, success, error, warning, info, white
        'variant' => 'circular', // circular, dots, pulse
        'className' => '',
        'label' => 'Loading...',
    ];
    
    $config = array_merge($defaults, $props);
    
    $sizeClasses = [
        'xs' => 'spinner-xs',
        'sm' => 'spinner-sm',
        'md' => 'spinner-md',
        'lg' => 'spinner-lg',
        'xl' => 'spinner-xl',
    ];
    
    $colorClasses = [
        'primary' => 'spinner-primary',
        'secondary' => 'spinner-secondary',
        'success' => 'spinner-success',
        'error' => 'spinner-error',
        'warning' => 'spinner-warning',
        'info' => 'spinner-info',
        'white' => 'spinner-white',
    ];
    
    $variantClasses = [
        'circular' => 'spinner-circular',
        'dots' => 'spinner-dots',
        'pulse' => 'spinner-pulse',
    ];
    
    $classes = [
        'spinner',
        $sizeClasses[$config['size']] ?? 'spinner-md',
        $colorClasses[$config['color']] ?? 'spinner-primary',
        $variantClasses[$config['variant']] ?? 'spinner-circular',
        $config['className']
    ];
    
    $classString = implode(' ', array_filter($classes));
    
    ob_start();
    ?>
    <div class="<?= $classString ?>" role="status" aria-label="<?= htmlspecialchars($config['label']) ?>">
        <?php if ($config['variant'] === 'circular'): ?>
            <svg class="spinner-svg" viewBox="0 0 50 50">
                <circle class="spinner-circle" cx="25" cy="25" r="20" fill="none" stroke-width="5"></circle>
            </svg>
        <?php elseif ($config['variant'] === 'dots'): ?>
            <div class="spinner-dots-container">
                <div class="spinner-dot"></div>
                <div class="spinner-dot"></div>
                <div class="spinner-dot"></div>
            </div>
        <?php elseif ($config['variant'] === 'pulse'): ?>
            <div class="spinner-pulse-container">
                <div class="spinner-pulse"></div>
            </div>
        <?php endif; ?>
        <span class="sr-only"><?= htmlspecialchars($config['label']) ?></span>
    </div>
    <?php
    return ob_get_clean();
}
?>
