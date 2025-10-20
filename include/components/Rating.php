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

<?php
/**
 * BBB Rating Display Component
 * 
 * @param string $rating BBB rating (A+ through F)
 * @param bool $showStars Whether to show star rating
 * @param bool $showText Whether to show "BBB Rating" text
 * @param string $size Size variant (sm, md, lg)
 * @return string HTML output
 */
function bbb_rating_display(string $rating = 'A+', bool $showStars = true, bool $showText = true, string $size = 'md'): string {
    ob_start(); ?>
    <div class="bbb-rating bbb-rating-<?= htmlspecialchars(strtolower($rating)) ?> bbb-rating-<?= htmlspecialchars($size) ?>">
        <div class="bbb-rating-main">
            <div class="bbb-rating-circle">
                <span class="bbb-rating-letter"><?= htmlspecialchars($rating) ?></span>
            </div>
            <?php if ($showText): ?>
            <div class="bbb-rating-info">
                <span class="bbb-rating-label">BBB Rating</span>
                <?php if ($showStars): ?>
                <div class="bbb-rating-stars">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                        <span class="bbb-star <?= $i <= get_star_count($rating) ? 'filled' : '' ?>">★</span>
                    <?php endfor; ?>
                </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?php return ob_get_clean();
}
?>

<?php
/**
 * Dynamic Star Rating Component
 * 
 * @param float $rating Current rating (0-5)
 * @param int $totalStars Total number of stars (default: 5)
 * @param bool $interactive Whether stars are clickable for rating
 * @param string $size Size variant (sm, md, lg)
 * @param string $ajaxUrl AJAX endpoint for saving ratings
 * @param int $itemId ID of the item being rated
 * @return string HTML output
 */
function dynamic_star_rating(float $rating = 0, int $totalStars = 5, bool $interactive = false, string $size = 'md', string $ajaxUrl = '/ajax/rate', int $itemId = 0): string {
    $filledStars = floor($rating);
    $hasHalfStar = ($rating - $filledStars) >= 0.5;
    $emptyStars = $totalStars - $filledStars - ($hasHalfStar ? 1 : 0);
    
    $starId = 'star-rating-' . uniqid();
    
    ob_start(); ?>
    <div class="star-rating star-rating-<?= htmlspecialchars($size) ?> <?= $interactive ? 'star-rating-interactive' : '' ?>" 
         id="<?= $starId ?>" 
         data-rating="<?= $rating ?>" 
         data-item-id="<?= $itemId ?>"
         data-ajax-url="<?= htmlspecialchars($ajaxUrl) ?>">
        
        <div class="star-rating-container">
            <?php for ($i = 1; $i <= $filledStars; $i++): ?>
                <div class="star star-filled" data-value="<?= $i ?>">
                    <?= get_star_svg('filled') ?>
                </div>
            <?php endfor; ?>
            
            <?php if ($hasHalfStar): ?>
                <div class="star star-half" data-value="<?= $filledStars + 1 ?>">
                    <?= get_star_svg('half') ?>
                </div>
            <?php endif; ?>
            
            <?php for ($i = 1; $i <= $emptyStars; $i++): ?>
                <div class="star star-empty" data-value="<?= $filledStars + ($hasHalfStar ? 2 : 1) + $i - 1 ?>">
                    <?= get_star_svg('empty') ?>
                </div>
            <?php endfor; ?>
        </div>
        
        <?php if ($interactive): ?>
        <div class="star-rating-feedback">
            <span class="current-rating"><?= number_format($rating, 1) ?></span>
            <span class="rating-text">Click to rate</span>
        </div>
        <?php else: ?>
        <div class="star-rating-text">
            <span class="rating-value"><?= number_format($rating, 1) ?></span>
            <span class="rating-count">(<?= $totalStars ?> stars)</span>
        </div>
        <?php endif; ?>
    </div>
    <?php return ob_get_clean();
}

/**
 * Get star SVG based on fill state
 * 
 * @param string $state filled, half, or empty
 * @return string SVG HTML
 */
function get_star_svg(string $state = 'empty'): string {
    $fillColor = '';
    
    switch ($state) {
        case 'filled':
            $fillColor = '#ffc107'; // Yellow for filled stars
            break;
        case 'half':
            $fillColor = 'url(#half-gradient)';
            break;
        case 'empty':
        default:
            $fillColor = '#e0e0e0'; // Light gray for empty stars
            break;
    }
    
    return '
    <svg class="star-svg" width="24" height="24" viewBox="0 0 29.018 29.018">
        <defs>
            <linearGradient id="half-gradient">
                <stop offset="50%" stop-color="#ffc107"/>
                <stop offset="50%" stop-color="#e0e0e0"/>
            </linearGradient>
        </defs>
        <path d="M13.645,4.01l-2.057,6.334a1.013,1.013,0,0,1-.962.7H3.967a2.475,2.475,0,0,0-1.456,4.478L7.9,19.435a1.011,1.011,0,0,1,.367,1.131L6.208,26.9a2.476,2.476,0,0,0,3.81,2.768l5.388-3.914a1.012,1.012,0,0,1,1.188,0l5.388,3.914a2.476,2.476,0,0,0,3.81-2.768l-2.058-6.333a1.011,1.011,0,0,1,.367-1.131l5.388-3.914a2.475,2.475,0,0,0-1.456-4.478H21.374a1.013,1.013,0,0,1-.962-.7L18.355,4.01a2.477,2.477,0,0,0-4.71,0Zm1.9.618a.475.475,0,0,1,.9,0l2.058,6.334a3.012,3.012,0,0,0,2.864,2.081h6.659a.475.475,0,0,1,.28.86l-5.387,3.914a3.011,3.011,0,0,0-1.094,3.367l2.058,6.333a.476.476,0,0,1-.733.532L17.77,24.135a3.011,3.011,0,0,0-3.54,0L8.843,28.049a.476.476,0,0,1-.733-.532l2.058-6.333a3.011,3.011,0,0,0-1.094-3.367L3.687,13.9a.475.475,0,0,1,.28-.86h6.659a3.012,3.012,0,0,0,2.864-2.081l2.058-6.334Z" 
              fill="' . $fillColor . '" 
              fill-rule="evenodd"
              transform="translate(-1.491 -2.3)"/>
    </svg>';
}
?>
