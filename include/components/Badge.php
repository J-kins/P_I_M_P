<?php
/**
 * Badge Component (Atom)
 * Small status indicator or label
 * 
 * @package UITemplateSystem
 * @category Atoms
 */

/**
 * Render a badge component
 * 
 * @param array $params {
 *   @type string $text Badge text content
 *   @type string $variant Color variant (primary|success|error|warning|info|neutral)
 *   @type string $icon Optional icon HTML
 *   @type string $class Additional CSS classes
 *   @type string $id Element ID
 * }
 * @return string HTML output
 */
function Badge(array $params = []): string {
    $text = $params['text'] ?? '';
    $variant = $params['variant'] ?? 'neutral';
    $icon = $params['icon'] ?? '';
    $class = $params['class'] ?? '';
    $id = $params['id'] ?? '';
    
    $classes = [
        'badge',
        "badge-{$variant}",
        $class
    ];
    $classString = implode(' ', array_filter($classes));
    
    ob_start();
    ?>
    <span 
        <?= $id ? 'id="' . htmlspecialchars($id) . '"' : '' ?>
        class="<?= htmlspecialchars($classString) ?>"
    >
        <?php if ($icon): ?>
            <?= $icon ?>
        <?php endif; ?>
        <?= htmlspecialchars($text) ?>
    </span>
    <?php
    return ob_get_clean();
}
