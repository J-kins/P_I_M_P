<?php
/**
 * Typography Component
 * Renders text elements with consistent styling
 * 
 * @param array $props Configuration options
 * @return string HTML output
 */

function Typography($props = []) {
    $defaults = [
        'tag' => 'p',           // h1, h2, h3, h4, h5, h6, p, span, label
        'variant' => 'body',    // display, h1, h2, h3, h4, h5, h6, body, small, caption
        'weight' => 'normal',   // light, normal, medium, semibold, bold
        'align' => 'left',      // left, center, right, justify
        'color' => 'primary',   // primary, secondary, muted, accent, success, error, warning, info
        'className' => '',
        'children' => '',
        'id' => '',
    ];
    
    $config = array_merge($defaults, $props);
    
    $variantClasses = [
        'display' => 'text-display',
        'h1' => 'text-h1',
        'h2' => 'text-h2',
        'h3' => 'text-h3',
        'h4' => 'text-h4',
        'h5' => 'text-h5',
        'h6' => 'text-h6',
        'body' => 'text-body',
        'small' => 'text-small',
        'caption' => 'text-caption',
    ];
    
    $weightClasses = [
        'light' => 'font-light',
        'normal' => 'font-normal',
        'medium' => 'font-medium',
        'semibold' => 'font-semibold',
        'bold' => 'font-bold',
    ];
    
    $alignClasses = [
        'left' => 'text-left',
        'center' => 'text-center',
        'right' => 'text-right',
        'justify' => 'text-justify',
    ];
    
    $colorClasses = [
        'primary' => 'text-primary',
        'secondary' => 'text-secondary',
        'muted' => 'text-muted',
        'accent' => 'text-accent',
        'success' => 'text-success',
        'error' => 'text-error',
        'warning' => 'text-warning',
        'info' => 'text-info',
    ];
    
    $classes = [
        $variantClasses[$config['variant']] ?? 'text-body',
        $weightClasses[$config['weight']] ?? 'font-normal',
        $alignClasses[$config['align']] ?? 'text-left',
        $colorClasses[$config['color']] ?? 'text-primary',
        $config['className']
    ];
    
    $classString = implode(' ', array_filter($classes));
    $idAttr = $config['id'] ? " id=\"{$config['id']}\"" : '';
    
    ob_start();
    ?>
    <<?= $config['tag'] ?> class="<?= $classString ?>"<?= $idAttr ?>>
        <?= $config['children'] ?>
    </<?= $config['tag'] ?>>
    <?php
    return ob_get_clean();
}

/**
 * Heading Component - Shorthand for Typography with heading tags
 */
function Heading($props = []) {
    $defaults = [
        'level' => 1,
        'children' => '',
    ];
    
    $config = array_merge($defaults, $props);
    $tag = 'h' . min(6, max(1, $config['level']));
    $variant = 'h' . min(6, max(1, $config['level']));
    
    return Typography(array_merge($config, [
        'tag' => $tag,
        'variant' => $variant,
    ]));
}

/**
 * Text Component - Shorthand for Typography with paragraph tag
 */
function Text($props = []) {
    return Typography(array_merge($props, ['tag' => 'p']));
}
?>
