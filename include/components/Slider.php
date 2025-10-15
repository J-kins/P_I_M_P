<?php
/**
 * Slider Component
 * 
 * @param array $props Configuration options
 *   - min: int - Minimum value, default: 0
 *   - max: int - Maximum value, default: 100
 *   - value: int - Current value, default: 50
 *   - step: int - Step increment, default: 1
 *   - showValue: bool - Show current value, default: false
 *   - disabled: bool - Disabled state, default: false
 *   - name: string - Input name
 *   - className: string - Additional CSS classes
 * @return string HTML markup
 */
function Slider($props = []) {
    $min = $props['min'] ?? 0;
    $max = $props['max'] ?? 100;
    $value = $props['value'] ?? 50;
    $step = $props['step'] ?? 1;
    $showValue = $props['showValue'] ?? false;
    $disabled = $props['disabled'] ?? false;
    $name = $props['name'] ?? '';
    $className = $props['className'] ?? '';
    
    $disabledAttr = $disabled ? 'disabled' : '';
    
    ob_start();
    ?>
    <div class="slider-wrapper <?php echo $className; ?>">
        <input type="range" 
               class="slider" 
               min="<?php echo $min; ?>" 
               max="<?php echo $max; ?>" 
               value="<?php echo $value; ?>" 
               step="<?php echo $step; ?>"
               <?php if ($name): ?>name="<?php echo $name; ?>"<?php endif; ?>
               <?php echo $disabledAttr; ?>
               aria-valuemin="<?php echo $min; ?>"
               aria-valuemax="<?php echo $max; ?>"
               aria-valuenow="<?php echo $value; ?>">
        <?php if ($showValue): ?>
            <span class="slider-value"><?php echo $value; ?></span>
        <?php endif; ?>
    </div>
    <?php
    return ob_get_clean();
}
?>
