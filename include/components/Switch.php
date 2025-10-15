<?php
/**
 * Switch Component
 * A toggle switch for binary on/off states
 * 
 * @param array $props Configuration options
 *   - id: string - Unique identifier
 *   - name: string - Form field name
 *   - checked: bool - Whether switch is on (default: false)
 *   - disabled: bool - Whether switch is disabled (default: false)
 *   - size: string - Size variant: 'sm', 'md', 'lg' (default: 'md')
 *   - label: string - Label text (optional)
 *   - labelPosition: string - 'left' or 'right' (default: 'right')
 *   - color: string - Color variant: 'primary', 'success', 'warning', 'danger' (default: 'primary')
 *   - onChange: string - JavaScript function to call on change (optional)
 * 
 * @return string HTML markup
 */
function Switchd($props = []) {
    $id = $props['id'] ?? 'switch-' . uniqid();
    $name = $props['name'] ?? '';
    $checked = $props['checked'] ?? false;
    $disabled = $props['disabled'] ?? false;
    $size = $props['size'] ?? 'md';
    $label = $props['label'] ?? '';
    $labelPosition = $props['labelPosition'] ?? 'right';
    $color = $props['color'] ?? 'primary';
    $onChange = $props['onChange'] ?? '';
    
    $sizeClass = "switch-{$size}";
    $colorClass = "switch-{$color}";
    $disabledClass = $disabled ? 'switch-disabled' : '';
    $checkedAttr = $checked ? 'checked' : '';
    $disabledAttr = $disabled ? 'disabled' : '';
    $onChangeAttr = $onChange ? "onchange=\"{$onChange}\"" : '';
    
    ob_start();
    ?>
    <label class="switch-container <?php echo $sizeClass; ?> <?php echo $disabledClass; ?>">
        <?php if ($label && $labelPosition === 'left'): ?>
            <span class="switch-label switch-label-left"><?php echo htmlspecialchars($label); ?></span>
        <?php endif; ?>
        
        <div class="switch">
            <input 
                type="checkbox" 
                id="<?php echo $id; ?>"
                name="<?php echo $name; ?>"
                class="switch-input <?php echo $colorClass; ?>"
                <?php echo $checkedAttr; ?>
                <?php echo $disabledAttr; ?>
                <?php echo $onChangeAttr; ?>
            />
            <span class="switch-slider"></span>
        </div>
        
        <?php if ($label && $labelPosition === 'right'): ?>
            <span class="switch-label switch-label-right"><?php echo htmlspecialchars($label); ?></span>
        <?php endif; ?>
    </label>
    <?php
    return ob_get_clean();
}
?>
