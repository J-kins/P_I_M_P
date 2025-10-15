<?php
/**
 * Checkbox Component
 * Renders a styled checkbox input
 * 
 * @param array $props Configuration options
 * @return string HTML output
 */

function Checkbox($props = []) {
    $defaults = [
        'id' => 'checkbox-' . uniqid(),
        'name' => '',
        'value' => '',
        'checked' => false,
        'disabled' => false,
        'label' => '',
        'size' => 'md',         // sm, md, lg
        'color' => 'primary',   // primary, success, error, warning, info
        'className' => '',
        'onChange' => '',
    ];
    
    $config = array_merge($defaults, $props);
    
    $sizeClasses = [
        'sm' => 'checkbox-sm',
        'md' => 'checkbox-md',
        'lg' => 'checkbox-lg',
    ];
    
    $colorClasses = [
        'primary' => 'checkbox-primary',
        'success' => 'checkbox-success',
        'error' => 'checkbox-error',
        'warning' => 'checkbox-warning',
        'info' => 'checkbox-info',
    ];
    
    $classes = [
        'checkbox',
        $sizeClasses[$config['size']] ?? 'checkbox-md',
        $colorClasses[$config['color']] ?? 'checkbox-primary',
        $config['disabled'] ? 'checkbox-disabled' : '',
        $config['className']
    ];
    
    $classString = implode(' ', array_filter($classes));
    $checkedAttr = $config['checked'] ? ' checked' : '';
    $disabledAttr = $config['disabled'] ? ' disabled' : '';
    $nameAttr = $config['name'] ? " name=\"{$config['name']}\"" : '';
    $valueAttr = $config['value'] ? " value=\"{$config['value']}\"" : '';
    $onChangeAttr = $config['onChange'] ? " onchange=\"{$config['onChange']}\"" : '';
    
    ob_start();
    ?>
    <div class="checkbox-wrapper <?= $config['className'] ?>">
        <input 
            type="checkbox" 
            id="<?= $config['id'] ?>"
            class="<?= $classString ?>"
            <?= $nameAttr ?>
            <?= $valueAttr ?>
            <?= $checkedAttr ?>
            <?= $disabledAttr ?>
            <?= $onChangeAttr ?>
        />
        <?php if ($config['label']): ?>
            <label for="<?= $config['id'] ?>" class="checkbox-label">
                <?= $config['label'] ?>
            </label>
        <?php endif; ?>
    </div>
    <?php
    return ob_get_clean();
}
?>
