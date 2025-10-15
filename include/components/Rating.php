<?php
/**
 * Rating Component
 * 
 * @param array $props Configuration options
 *   - value: float - Rating value (0-5) default: 0
 *   - max: int - Maximum rating, default: 5
 *   - size: string - Size (sm|md|lg) default: 'md'
 *   - readonly: bool - Read-only mode, default: false
 *   - showValue: bool - Show numeric value, default: false
 *   - name: string - Input name
 *   - className: string - Additional CSS classes
 * @return string HTML markup
 */
function Rating($props = []) {
    $value = $props['value'] ?? 0;
    $max = $props['max'] ?? 5;
    $size = $props['size'] ?? 'md';
    $readonly = $props['readonly'] ?? false;
    $showValue = $props['showValue'] ?? false;
    $name = $props['name'] ?? '';
    $className = $props['className'] ?? '';
    
    $readonlyClass = $readonly ? 'rating-readonly' : '';
    
    ob_start();
    ?>
    <div class="rating rating-<?php echo $size; ?> <?php echo $readonlyClass; ?> <?php echo $className; ?>" 
         role="img" 
         aria-label="Rating: <?php echo $value; ?> out of <?php echo $max; ?>">
        <?php for ($i = 1; $i <= $max; $i++): ?>
            <?php
            $filled = $i <= $value;
            $partial = $i > $value && $i - 1 < $value;
            $fillPercentage = $partial ? (($value - floor($value)) * 100) : 0;
            ?>
            <span class="rating-star <?php echo $filled ? 'rating-star-filled' : ''; ?> <?php echo $partial ? 'rating-star-partial' : ''; ?>"
                  data-value="<?php echo $i; ?>"
                  <?php if ($partial): ?>style="--fill-percentage: <?php echo $fillPercentage; ?>%"<?php endif; ?>>
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                    <path d="M12 2L15.09 8.26L22 9.27L17 14.14L18.18 21.02L12 17.77L5.82 21.02L7 14.14L2 9.27L8.91 8.26L12 2Z" 
                          stroke="currentColor" 
                          stroke-width="2" 
                          stroke-linecap="round" 
                          stroke-linejoin="round"/>
                </svg>
            </span>
            <?php if (!$readonly): ?>
                <input type="radio" 
                       name="<?php echo $name; ?>" 
                       value="<?php echo $i; ?>" 
                       <?php echo $i <= $value ? 'checked' : ''; ?>
                       class="rating-input">
            <?php endif; ?>
        <?php endfor; ?>
        <?php if ($showValue): ?>
            <span class="rating-value"><?php echo number_format($value, 1); ?></span>
        <?php endif; ?>
    </div>
    <?php
    return ob_get_clean();
}
?>
