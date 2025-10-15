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
