<?php
/**
 * Button Component (Atom)
 * Reusable button with multiple variants and sizes
 * 
 * @package UITemplateSystem
 * @category Atoms
 */

/**
 * Render a button component
 * 
 * @param array $params {
 *   @type string $label Button text content
 *   @type string $type Button type (button|submit|reset)
 *   @type string $variant Style variant (primary|secondary|outline|ghost|success|error)
 *   @type string $size Size variant (xs|sm|md|lg)
 *   @type string $icon Optional icon HTML
 *   @type string $iconPosition Icon position (left|right)
 *   @type bool $disabled Whether button is disabled
 *   @type bool $loading Whether button is in loading state
 *   @type string $onClick JavaScript onclick handler
 *   @type string $href Optional link URL (renders as <a> tag)
 *   @type string $class Additional CSS classes
 *   @type string $id Element ID
 *   @type array $attrs Additional HTML attributes
 * }
 * @return string HTML output
 */
function Button(array $params = []): string {
    $label = $params['label'] ?? 'Button';
    $type = $params['type'] ?? 'button';
    $variant = $params['variant'] ?? 'primary';
    $size = $params['size'] ?? 'md';
    $icon = $params['icon'] ?? '';
    $iconPosition = $params['iconPosition'] ?? 'left';
    $disabled = $params['disabled'] ?? false;
    $loading = $params['loading'] ?? false;
    $onClick = $params['onClick'] ?? '';
    $href = $params['href'] ?? '';
    $class = $params['class'] ?? '';
    $id = $params['id'] ?? '';
    $attrs = $params['attrs'] ?? [];
    
    // Build class string
    $classes = [
        'btn',
        "btn-{$size}",
        "btn-{$variant}",
        $class
    ];
    $classString = implode(' ', array_filter($classes));
    
    // Build additional attributes
    $attrString = '';
    foreach ($attrs as $key => $value) {
        $attrString .= sprintf(' %s="%s"', htmlspecialchars($key), htmlspecialchars($value));
    }
    
    // Spinner for loading state
    $spinner = '<span class="spinner spinner-sm"></span>';
    
    // Icon rendering
    $iconHtml = '';
    if ($icon && !$loading) {
        $iconHtml = $icon;
    }
    
    // Content
    $content = '';
    if ($loading) {
        $content = $spinner . ' ' . $label;
    } elseif ($icon) {
        $content = $iconPosition === 'left' 
            ? $iconHtml . ' ' . $label 
            : $label . ' ' . $iconHtml;
    } else {
        $content = $label;
    }
    
    ob_start();
    
    if ($href && !$disabled) {
        // Render as link
        ?>
        <a 
            <?= $id ? 'id="' . htmlspecialchars($id) . '"' : '' ?>
            href="<?= htmlspecialchars($href) ?>"
            class="<?= htmlspecialchars($classString) ?>"
            <?= $onClick ? 'onclick="' . htmlspecialchars($onClick) . '"' : '' ?>
            <?= $attrString ?>
        >
            <?= $content ?>
        </a>
        <?php
    } else {
        // Render as button
        ?>
        <button 
            <?= $id ? 'id="' . htmlspecialchars($id) . '"' : '' ?>
            type="<?= htmlspecialchars($type) ?>"
            class="<?= htmlspecialchars($classString) ?>"
            <?= $disabled || $loading ? 'disabled' : '' ?>
            <?= $onClick ? 'onclick="' . htmlspecialchars($onClick) . '"' : '' ?>
            <?= $attrString ?>
        >
            <?= $content ?>
        </button>
        <?php
    }
    
    return ob_get_clean();
}

/**
 * Render a button group
 * 
 * @param array $buttons Array of button parameter arrays
 * @param string $class Additional CSS classes
 * @return string HTML output
 */
function ButtonGroup(array $buttons = [], string $class = ''): string {
    ob_start();
    ?>
    <div class="flex gap-2 <?= htmlspecialchars($class) ?>">
        <?php foreach ($buttons as $buttonParams): ?>
            <?= Button($buttonParams) ?>
        <?php endforeach; ?>
    </div>
    <?php
    return ob_get_clean();
}
