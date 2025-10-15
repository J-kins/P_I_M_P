<?php
/**
 * Select Component
 * 
 * @param array $props Configuration options
 *   - options: array - Array of options [{value, label}]
 *   - value: string - Selected value
 *   - placeholder: string - Placeholder text
 *   - size: string - Size (sm|md|lg) default: 'md'
 *   - disabled: bool - Disabled state, default: false
 *   - required: bool - Required field, default: false
 *   - name: string - Input name
 *   - className: string - Additional CSS classes
 * @return string HTML markup
 */
function Select($props = []) {
    $options = $props['options'] ?? [];
    $value = $props['value'] ?? '';
    $placeholder = $props['placeholder'] ?? 'Select an option';
    $size = $props['size'] ?? 'md';
    $disabled = $props['disabled'] ?? false;
    $required = $props['required'] ?? false;
    $name = $props['name'] ?? '';
    $className = $props['className'] ?? '';
    
    $disabledAttr = $disabled ? 'disabled' : '';
    $requiredAttr = $required ? 'required' : '';
    
    ob_start();
    ?>
    <select class="select select-<?php echo $size; ?> <?php echo $className; ?>" 
            <?php if ($name): ?>name="<?php echo $name; ?>"<?php endif; ?>
            <?php echo $disabledAttr; ?>
            <?php echo $requiredAttr; ?>>
        <?php if ($placeholder): ?>
            <option value="" disabled <?php echo empty($value) ? 'selected' : ''; ?>>
                <?php echo $placeholder; ?>
            </option>
        <?php endif; ?>
        <?php foreach ($options as $option): ?>
            <option value="<?php echo $option['value']; ?>" 
                    <?php echo $value === $option['value'] ? 'selected' : ''; ?>>
                <?php echo $option['label']; ?>
            </option>
        <?php endforeach; ?>
    </select>
    <?php
    return ob_get_clean();
}
?>
