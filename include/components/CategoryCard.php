<?php
/**
 * CategoryCard Component
 * Displays a business category with icon and count
 * 
 * @param array $props Configuration options
 *   - name: string - Category name (required)
 *   - count: int - Number of businesses in category
 *   - icon: string - Icon/emoji for category
 *   - url: string - Link to category page
 * 
 * @return string HTML markup
 */
function CategoryCard($props = []) {
    $name = $props['name'] ?? '';
    $count = $props['count'] ?? 0;
    $icon = $props['icon'] ?? '📁';
    $url = $props['url'] ?? '#';
    
    ob_start();
    ?>
    <a href="<?php echo htmlspecialchars($url); ?>" class="category-card">
        <div class="category-card__icon"><?php echo $icon; ?></div>
        <h3 class="category-card__name"><?php echo htmlspecialchars($name); ?></h3>
        <?php if ($count > 0): ?>
            <p class="category-card__count"><?php echo number_format($count); ?> businesses</p>
        <?php endif; ?>
    </a>
    <?php
    return ob_get_clean();
}
