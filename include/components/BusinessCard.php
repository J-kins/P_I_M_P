<?php
/**
 * BusinessCard Component
 * Displays business information in a card format
 * 
 * @param array $props Configuration options
 *   - name: string - Business name (required)
 *   - rating: float - Business rating (0-5)
 *   - reviewCount: int - Number of reviews
 *   - category: string - Business category
 *   - address: string - Business address
 *   - phone: string - Business phone number
 *   - website: string - Business website URL
 *   - accredited: bool - BBB accredited status
 *   - image: string - Business logo/image URL
 *   - url: string - Link to business profile
 * 
 * @return string HTML markup
 */
function BusinessCard($props = []) {
    $name = $props['name'] ?? '';
    $rating = $props['rating'] ?? 0;
    $reviewCount = $props['reviewCount'] ?? 0;
    $category = $props['category'] ?? '';
    $address = $props['address'] ?? '';
    $phone = $props['phone'] ?? '';
    $website = $props['website'] ?? '';
    $accredited = $props['accredited'] ?? false;
    $image = $props['image'] ?? '/placeholder.svg?height=80&width=80';
    $url = $props['url'] ?? '#';
    
    ob_start();
    ?>
    <div class="business-card">
        <a href="<?php echo htmlspecialchars($url); ?>" class="business-card__link">
            <div class="business-card__header">
                <div class="business-card__image">
                    <img src="<?php echo htmlspecialchars($image); ?>" alt="<?php echo htmlspecialchars($name); ?>" />
                </div>
                <div class="business-card__info">
                    <h3 class="business-card__name"><?php echo htmlspecialchars($name); ?></h3>
                    <?php if ($accredited): ?>
                        <span class="business-card__badge">Accredited Business</span>
                    <?php endif; ?>
                    <?php if ($category): ?>
                        <p class="business-card__category"><?php echo htmlspecialchars($category); ?></p>
                    <?php endif; ?>
                </div>
            </div>
            
            <?php if ($rating > 0): ?>
                <div class="business-card__rating">
                    <div class="business-card__stars">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <span class="star <?php echo $i <= $rating ? 'star--filled' : ''; ?>">★</span>
                        <?php endfor; ?>
                    </div>
                    <span class="business-card__review-count"><?php echo number_format($reviewCount); ?> reviews</span>
                </div>
            <?php endif; ?>
            
            <div class="business-card__details">
                <?php if ($address): ?>
                    <p class="business-card__address"><?php echo htmlspecialchars($address); ?></p>
                <?php endif; ?>
                <?php if ($phone): ?>
                    <p class="business-card__phone"><?php echo htmlspecialchars($phone); ?></p>
                <?php endif; ?>
                <?php if ($website): ?>
                    <p class="business-card__website"><?php echo htmlspecialchars($website); ?></p>
                <?php endif; ?>
            </div>
        </a>
    </div>
    <?php
    return ob_get_clean();
}
