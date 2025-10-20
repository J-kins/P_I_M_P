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

/**
 * BBB Business Profile Card Component
 * 
 * @param array $business {
 *   @type string $name Business name
 *   @type string $rating BBB rating (A+ through F)
 *   @type bool $accredited Whether business is BBB accredited
 *   @type string $address Business address
 *   @type string $phone Phone number
 *   @type string $website Website URL
 *   @type int $reviews Number of customer reviews
 *   @type int $complaints Number of complaints
 *   @type string $image Business image URL
 *   @type array $categories Business categories
 * }
 * @return string HTML output
 */
function bbb_business_card(array $business = []): string {
    $name = $business['name'] ?? 'Business Name';
    $rating = $business['rating'] ?? 'A+';
    $accredited = $business['accredited'] ?? false;
    $address = $business['address'] ?? '';
    $phone = $business['phone'] ?? '';
    $website = $business['website'] ?? '';
    $reviews = $business['reviews'] ?? 0;
    $complaints = $business['complaints'] ?? 0;
    $image = $business['image'] ?? '';
    $categories = $business['categories'] ?? [];
    
    ob_start(); ?>
    <div class="bbb-business-card">
        <div class="bbb-business-header">
            <?php if (!empty($image)): ?>
            <div class="bbb-business-image">
                <img src="<?= asset_url($image) ?>" alt="<?= htmlspecialchars($name) ?>">
            </div>
            <?php endif; ?>
            
            <div class="bbb-business-info">
                <div class="bbb-business-title">
                    <h3 class="bbb-business-name"><?= htmlspecialchars($name) ?></h3>
                    <?php if ($accredited): ?>
                    <span class="bbb-accredited-badge">BBB Accredited</span>
                    <?php endif; ?>
                </div>
                
                <div class="bbb-business-rating">
                    <div class="bbb-rating-display bbb-rating-<?= strtolower($rating) ?>">
                        <span class="bbb-rating-letter"><?= htmlspecialchars($rating) ?></span>
                    </div>
                    <div class="bbb-rating-info">
                        <span class="bbb-rating-text">BBB Rating</span>
                        <div class="bbb-rating-stars">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <span class="bbb-star <?= $i <= get_star_count($rating) ? 'filled' : '' ?>">★</span>
                            <?php endfor; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="bbb-business-details">
            <?php if (!empty($categories)): ?>
            <div class="bbb-business-categories">
                <?php foreach ($categories as $category): ?>
                <span class="bbb-category-tag"><?= htmlspecialchars($category) ?></span>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
            
            <div class="bbb-contact-info">
                <?php if (!empty($address)): ?>
                <div class="bbb-contact-item">
                    <span class="bbb-contact-label">Address:</span>
                    <span class="bbb-contact-value"><?= htmlspecialchars($address) ?></span>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($phone)): ?>
                <div class="bbb-contact-item">
                    <span class="bbb-contact-label">Phone:</span>
                    <a href="tel:<?= preg_replace('/[^0-9+]/', '', $phone) ?>" class="bbb-contact-value">
                        <?= htmlspecialchars($phone) ?>
                    </a>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($website)): ?>
                <div class="bbb-contact-item">
                    <span class="bbb-contact-label">Website:</span>
                    <a href="<?= htmlspecialchars($website) ?>" target="_blank" class="bbb-contact-value">
                        Visit Website
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="bbb-business-stats">
            <div class="bbb-stat">
                <span class="bbb-stat-number"><?= number_format($reviews) ?></span>
                <span class="bbb-stat-label">Customer Reviews</span>
            </div>
            <div class="bbb-stat">
                <span class="bbb-stat-number"><?= number_format($complaints) ?></span>
                <span class="bbb-stat-label">Complaints</span>
            </div>
        </div>
        
        <div class="bbb-business-actions">
            <a href="#" class="bbb-action-button bbb-action-primary">View Business Profile</a>
            <a href="#" class="bbb-action-button">Write a Review</a>
            <a href="#" class="bbb-action-button">File a Complaint</a>
        </div>
    </div>
    <?php return ob_get_clean();
}

// Helper function to convert BBB rating to star count
function get_star_count($rating) {
    $ratingMap = [
        'A+' => 5, 'A' => 4, 'A-' => 4,
        'B+' => 3, 'B' => 3, 'B-' => 3,
        'C+' => 2, 'C' => 2, 'C-' => 2,
        'D+' => 1, 'D' => 1, 'D-' => 1,
        'F' => 0
    ];
    return $ratingMap[$rating] ?? 3;
}
?>
