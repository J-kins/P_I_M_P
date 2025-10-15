<?php
/**
 * Input Component (Atom)
 * Form input field with validation states
 * 
 * @package UITemplateSystem
 * @category Atoms
 */

/**
 * Render an input field
 * 
 * @param array $params {
 *   @type string $type Input type (text|email|password|number|tel|url|search)
 *   @type string $name Input name attribute
 *   @type string $value Input value
 *   @type string $placeholder Placeholder text
 *   @type string $size Size variant (sm|md|lg)
 *   @type bool $disabled Whether input is disabled
 *   @type bool $readonly Whether input is readonly
 *   @type bool $required Whether input is required
 *   @type bool $error Whether input has error state
 *   @type string $class Additional CSS classes
 *   @type string $id Element ID
 *   @type array $attrs Additional HTML attributes
 * }
 * @return string HTML output
 */
function Input(array $params = []): string {
    $type = $params['type'] ?? 'text';
    $name = $params['name'] ?? '';
    $value = $params['value'] ?? '';
    $placeholder = $params['placeholder'] ?? '';
    $size = $params['size'] ?? 'md';
    $disabled = $params['disabled'] ?? false;
    $readonly = $params['readonly'] ?? false;
    $required = $params['required'] ?? false;
    $error = $params['error'] ?? false;
    $class = $params['class'] ?? '';
    $id = $params['id'] ?? '';
    $attrs = $params['attrs'] ?? [];
    
    // Build class string
    $classes = [
        'input',
        $size !== 'md' ? "input-{$size}" : '',
        $error ? 'input-error' : '',
        $class
    ];
    $classString = implode(' ', array_filter($classes));
    
    // Build additional attributes
    $attrString = '';
    foreach ($attrs as $key => $attrValue) {
        $attrString .= sprintf(' %s="%s"', htmlspecialchars($key), htmlspecialchars($attrValue));
    }
    
    ob_start();
    ?>
    <input 
        <?= $id ? 'id="' . htmlspecialchars($id) . '"' : '' ?>
        type="<?= htmlspecialchars($type) ?>"
        <?= $name ? 'name="' . htmlspecialchars($name) . '"' : '' ?>
        value="<?= htmlspecialchars($value) ?>"
        <?= $placeholder ? 'placeholder="' . htmlspecialchars($placeholder) . '"' : '' ?>
        class="<?= htmlspecialchars($classString) ?>"
        <?= $disabled ? 'disabled' : '' ?>
        <?= $readonly ? 'readonly' : '' ?>
        <?= $required ? 'required' : '' ?>
        <?= $attrString ?>
    />
    <?php
    return ob_get_clean();
}

/**
 * Render a textarea field
 * 
 * @param array $params Similar to Input with additional rows parameter
 * @return string HTML output
 */
function Textarea(array $params = []): string {
    $name = $params['name'] ?? '';
    $value = $params['value'] ?? '';
    $placeholder = $params['placeholder'] ?? '';
    $rows = $params['rows'] ?? 4;
    $disabled = $params['disabled'] ?? false;
    $readonly = $params['readonly'] ?? false;
    $required = $params['required'] ?? false;
    $error = $params['error'] ?? false;
    $class = $params['class'] ?? '';
    $id = $params['id'] ?? '';
    
    $classes = [
        'input',
        $error ? 'input-error' : '',
        $class
    ];
    $classString = implode(' ', array_filter($classes));
    
    ob_start();
    ?>
    <textarea 
        <?= $id ? 'id="' . htmlspecialchars($id) . '"' : '' ?>
        <?= $name ? 'name="' . htmlspecialchars($name) . '"' : '' ?>
        <?= $placeholder ? 'placeholder="' . htmlspecialchars($placeholder) . '"' : '' ?>
        rows="<?= intval($rows) ?>"
        class="<?= htmlspecialchars($classString) ?>"
        <?= $disabled ? 'disabled' : '' ?>
        <?= $readonly ? 'readonly' : '' ?>
        <?= $required ? 'required' : '' ?>
    ><?= htmlspecialchars($value) ?></textarea>
    <?php
    return ob_get_clean();
}
