<?php
/**
 * SearchBox Component
 * A search input with location field for finding businesses
 * 
 * @param array $props Configuration options
 *   - placeholder: string - Search input placeholder (default: "Find a business or category")
 *   - locationPlaceholder: string - Location input placeholder (default: "City, State or ZIP")
 *   - buttonText: string - Search button text (default: "Search")
 *   - variant: string - Style variant: 'default', 'hero' (default: 'default')
 *   - action: string - Form action URL (default: "/search")
 *   - method: string - Form method (default: "GET")
 * 
 * @return string HTML markup
 */
function SearchBox($props = []) {
    $placeholder = $props['placeholder'] ?? 'Find a business or category';
    $locationPlaceholder = $props['locationPlaceholder'] ?? 'City, State or ZIP';
    $buttonText = $props['buttonText'] ?? 'Search';
    $variant = $props['variant'] ?? 'default';
    $action = $props['action'] ?? '/search';
    $method = $props['method'] ?? 'GET';
    
    ob_start();
    ?>
    <form class="search-box search-box--<?php echo htmlspecialchars($variant); ?>" action="<?php echo htmlspecialchars($action); ?>" method="<?php echo htmlspecialchars($method); ?>">
        <div class="search-box__inputs">
            <div class="search-box__field search-box__field--query">
                <input 
                    type="text" 
                    name="query" 
                    class="search-box__input" 
                    placeholder="<?php echo htmlspecialchars($placeholder); ?>"
                    required
                />
            </div>
            <div class="search-box__field search-box__field--location">
                <input 
                    type="text" 
                    name="location" 
                    class="search-box__input" 
                    placeholder="<?php echo htmlspecialchars($locationPlaceholder); ?>"
                    required
                />
            </div>
            <button type="submit" class="search-box__button">
                <?php echo htmlspecialchars($buttonText); ?>
            </button>
        </div>
    </form>
    <?php
    return ob_get_clean();
}

/**
 * BBB Search Results Component
 * 
 * @param array $results Array of business results
 * @param int $totalResults Total number of results
 * @param string $query Search query
 * @param string $location Search location
 * @return string HTML output
 */
function bbb_search_results(array $results = [], int $totalResults = 0, string $query = '', string $location = ''): string {
    ob_start(); ?>
    <div class="bbb-search-results">
        <div class="bbb-results-header">
            <h2 class="bbb-results-title">
                <?php if (!empty($query)): ?>
                    Businesses matching "<?= htmlspecialchars($query) ?>"
                    <?php if (!empty($location)): ?>
                        in <?= htmlspecialchars($location) ?>
                    <?php endif; ?>
                <?php else: ?>
                    All Businesses
                <?php endif; ?>
            </h2>
            <div class="bbb-results-count">
                <?= number_format($totalResults) ?> results found
            </div>
        </div>
        
        <div class="bbb-results-filters">
            <div class="bbb-filter-group">
                <label for="bbb-sort-by" class="bbb-filter-label">Sort by:</label>
                <select id="bbb-sort-by" class="bbb-filter-select">
                    <option value="relevance">Relevance</option>
                    <option value="rating">BBB Rating</option>
                    <option value="reviews">Customer Reviews</option>
                    <option value="name">Business Name</option>
                </select>
            </div>
            
            <div class="bbb-filter-group">
                <label for="bbb-filter-rating" class="bbb-filter-label">BBB Rating:</label>
                <select id="bbb-filter-rating" class="bbb-filter-select">
                    <option value="all">All Ratings</option>
                    <option value="a-plus">A+</option>
                    <option value="a">A</option>
                    <option value="b">B</option>
                    <option value="c">C</option>
                    <option value="f">F</option>
                </select>
            </div>
            
            <div class="bbb-filter-group">
                <label class="bbb-filter-label">
                    <input type="checkbox" class="bbb-filter-checkbox">
                    BBB Accredited Only
                </label>
            </div>
        </div>
        
        <div class="bbb-results-list">
            <?php if (empty($results)): ?>
                <div class="bbb-no-results">
                    <h3>No businesses found</h3>
                    <p>Try adjusting your search criteria or browse by category.</p>
                </div>
            <?php else: ?>
                <?php foreach ($results as $business): ?>
                    <?= bbb_business_card($business) ?>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        
        <?php if (count($results) > 0): ?>
        <div class="bbb-results-pagination">
            <button class="bbb-pagination-button bbb-pagination-prev" disabled>Previous</button>
            <div class="bbb-pagination-pages">
                <button class="bbb-page-button active">1</button>
                <button class="bbb-page-button">2</button>
                <button class="bbb-page-button">3</button>
                <span class="bbb-page-ellipsis">...</span>
                <button class="bbb-page-button">10</button>
            </div>
            <button class="bbb-pagination-button bbb-pagination-next">Next</button>
        </div>
        <?php endif; ?>
    </div>
    <?php return ob_get_clean();
}
?>

<?php
/**
 * BBB Hero Search Component (for homepage)
 * 
 * @param array $config Configuration options
 * @return string HTML output
 */
function bbb_hero_search(array $config = []): string {
    $title = $config['title'] ?? 'Find Trusted Businesses';
    $subtitle = $config['subtitle'] ?? 'Check reviews, complaints, and BBB ratings before you buy';
    $searchPlaceholder = $config['searchPlaceholder'] ?? 'Find a business or category';
    $locationPlaceholder = $config['locationPlaceholder'] ?? 'City, State or ZIP';
    
    ob_start(); ?>
    <section class="bbb-hero-search">
        <div class="bbb-hero-container">
            <div class="bbb-hero-content">
                <h1 class="bbb-hero-title"><?= htmlspecialchars($title) ?></h1>
                <p class="bbb-hero-subtitle"><?= htmlspecialchars($subtitle) ?></p>
                
                <div class="bbb-hero-search-box">
                    <?= SearchBox([
                        'placeholder' => $searchPlaceholder,
                        'locationPlaceholder' => $locationPlaceholder,
                        'buttonText' => 'Search',
                        'variant' => 'hero',
                        'action' => '/search'
                    ]) ?>
                </div>
                
                <div class="bbb-hero-features">
                    <div class="bbb-feature">
                        <span class="bbb-feature-icon">⭐</span>
                        <span class="bbb-feature-text">BBB Ratings & Reviews</span>
                    </div>
                    <div class="bbb-feature">
                        <span class="bbb-feature-icon">🛡️</span>
                        <span class="bbb-feature-text">Accredited Businesses</span>
                    </div>
                    <div class="bbb-feature">
                        <span class="bbb-feature-icon">📝</span>
                        <span class="bbb-feature-text">File Complaints</span>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <?php return ob_get_clean();
}
?>
