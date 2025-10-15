<?php
/**
 * Radio Component
 * Renders a styled radio input
 * 
 * @param array $props Configuration options
 * @return string HTML output
 */

function Radio($props = []) {
    $defaults = [
        'id' => 'radio-' . uniqid(),
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
        'sm' => 'radio-sm',
        'md' => 'radio-md',
        'lg' => 'radio-lg',
    ];
    
    $colorClasses = [
        'primary' => 'radio-primary',
        'success' => 'radio-success',
        'error' => 'radio-error',
        'warning' => 'radio-warning',
        'info' => 'radio-info',
    ];
    
    $classes = [
        'radio',
        $sizeClasses[$config['size']] ?? 'radio-md',
        $colorClasses[$config['color']] ?? 'radio-primary',
        $config['disabled'] ? 'radio-disabled' : '',
    ];
    
    $classString = implode(' ', array_filter($classes));
    $checkedAttr = $config['checked'] ? ' checked' : '';
    $disabledAttr = $config['disabled'] ? ' disabled' : '';
    $nameAttr = $config['name'] ? " name=\"{$config['name']}\"" : '';
    $valueAttr = $config['value'] ? " value=\"{$config['value']}\"" : '';
    $onChangeAttr = $config['onChange'] ? " onchange=\"{$config['onChange']}\"" : '';
    
    ob_start();
    ?>
    <div class="radio-wrapper <?= $config['className'] ?>">
        <input 
            type="radio" 
            id="<?= $config['id'] ?>"
            class="<?= $classString ?>"
            <?= $nameAttr ?>
            <?= $valueAttr ?>
            <?= $checkedAttr ?>
            <?= $disabledAttr ?>
            <?= $onChangeAttr ?>
        />
        <?php if ($config['label']): ?>
            <label for="<?= $config['id'] ?>" class="radio-label">
                <?= $config['label'] ?>
            </label>
        <?php endif; ?>
    </div>
    <?php
    return ob_get_clean();
}
?>
