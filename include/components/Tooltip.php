<?php
/**
 * Tooltip Component
 * 
 * @param array $props Configuration options
 *   - content: string - Tooltip content (required)
 *   - position: string - Tooltip position (top|right|bottom|left) default: 'top'
 *   - trigger: string - Trigger element content (required)
 *   - delay: int - Show delay in ms, default: 200
 *   - className: string - Additional CSS classes
 * @return string HTML markup
 */
function Tooltip($props = []) {
    $content = $props['content'] ?? '';
    $position = $props['position'] ?? 'top';
    $trigger = $props['trigger'] ?? '';
    $delay = $props['delay'] ?? 200;
    $className = $props['className'] ?? '';
    
    $id = 'tooltip-' . uniqid();
    
    ob_start();
    ?>
    <div class="tooltip-wrapper <?php echo $className; ?>">
        <div class="tooltip-trigger" 
             aria-describedby="<?php echo $id; ?>"
             data-tooltip-delay="<?php echo $delay; ?>">
            <?php echo $trigger; ?>
        </div>
        <div id="<?php echo $id; ?>" 
             class="tooltip tooltip-<?php echo $position; ?>" 
             role="tooltip">
            <?php echo $content; ?>
        </div>
    </div>
    <?php
    return ob_get_clean();
}
?>
