<?php
/**
 * Divider Component
 * Renders a horizontal or vertical divider
 * 
 * @param array $props Configuration options
 * @return string HTML output
 */

function Divider($props = []) {
    $defaults = [
        'orientation' => 'horizontal', // horizontal, vertical
        'variant' => 'solid',          // solid, dashed, dotted
        'spacing' => 'md',             // none, sm, md, lg, xl
        'color' => 'default',          // default, primary, secondary, muted
        'thickness' => 'thin',         // thin, medium, thick
        'className' => '',
        'label' => '',                 // Optional text label
    ];
    
    $config = array_merge($defaults, $props);
    
    $orientationClasses = [
        'horizontal' => 'divider-horizontal',
        'vertical' => 'divider-vertical',
    ];
    
    $variantClasses = [
        'solid' => 'divider-solid',
        'dashed' => 'divider-dashed',
        'dotted' => 'divider-dotted',
    ];
    
    $spacingClasses = [
        'none' => 'divider-spacing-none',
        'sm' => 'divider-spacing-sm',
        'md' => 'divider-spacing-md',
        'lg' => 'divider-spacing-lg',
        'xl' => 'divider-spacing-xl',
    ];
    
    $colorClasses = [
        'default' => 'divider-default',
        'primary' => 'divider-primary',
        'secondary' => 'divider-secondary',
        'muted' => 'divider-muted',
    ];
    
    $thicknessClasses = [
        'thin' => 'divider-thin',
        'medium' => 'divider-medium',
        'thick' => 'divider-thick',
    ];
    
    $classes = [
        'divider',
        $orientationClasses[$config['orientation']] ?? 'divider-horizontal',
        $variantClasses[$config['variant']] ?? 'divider-solid',
        $spacingClasses[$config['spacing']] ?? 'divider-spacing-md',
        $colorClasses[$config['color']] ?? 'divider-default',
        $thicknessClasses[$config['thickness']] ?? 'divider-thin',
        $config['label'] ? 'divider-with-label' : '',
        $config['className']
    ];
    
    $classString = implode(' ', array_filter($classes));
    
    ob_start();
    ?>
    <?php if ($config['label']): ?>
        <div class="<?= $classString ?>">
            <span class="divider-label"><?= htmlspecialchars($config['label']) ?></span>
        </div>
    <?php else: ?>
        <hr class="<?= $classString ?>" />
    <?php endif; ?>
    <?php
    return ob_get_clean();
}
?>
