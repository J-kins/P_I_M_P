<?php
/**
 * Toast Component
 * 
 * @param array $props Configuration options
 *   - message: string - Toast message (required)
 *   - variant: string - Variant (success|error|warning|info) default: 'info'
 *   - position: string - Position (top-left|top-center|top-right|bottom-left|bottom-center|bottom-right) default: 'top-right'
 *   - duration: int - Auto-dismiss duration in ms, default: 3000
 *   - dismissible: bool - Show close button, default: true
 *   - icon: string - Icon HTML
 *   - className: string - Additional CSS classes
 * @return string HTML markup
 */
function Toast($props = []) {
    $message = $props['message'] ?? '';
    $variant = $props['variant'] ?? 'info';
    $position = $props['position'] ?? 'top-right';
    $duration = $props['duration'] ?? 3000;
    $dismissible = $props['dismissible'] ?? true;
    $icon = $props['icon'] ?? '';
    $className = $props['className'] ?? '';
    
    ob_start();
    ?>
    <div class="toast toast-<?php echo $variant; ?> toast-<?php echo $position; ?> <?php echo $className; ?>" 
         role="alert" 
         aria-live="polite"
         data-duration="<?php echo $duration; ?>">
        <?php if ($icon): ?>
            <div class="toast-icon"><?php echo $icon; ?></div>
        <?php endif; ?>
        <div class="toast-content">
            <p class="toast-message"><?php echo $message; ?></p>
        </div>
        <?php if ($dismissible): ?>
            <button class="toast-close" aria-label="Close">
                <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                    <path d="M12 4L4 12M4 4L12 12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                </svg>
            </button>
        <?php endif; ?>
    </div>
    <?php
    return ob_get_clean();
}
?>
