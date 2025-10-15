<?php
/**
 * Hero Component
 * Large hero section with title, description, and search
 * 
 * @param array $props Configuration options
 *   - title: string - Hero title (required)
 *   - description: string - Hero description
 *   - showSearch: bool - Show search box (default: true)
 *   - backgroundImage: string - Background image URL
 *   - variant: string - Style variant: 'default', 'gradient' (default: 'default')
 * 
 * @return string HTML markup
 */
function Hero($props = []) {
    $title = $props['title'] ?? '';
    $description = $props['description'] ?? '';
    $showSearch = $props['showSearch'] ?? true;
    $backgroundImage = $props['backgroundImage'] ?? '';
    $variant = $props['variant'] ?? 'default';
    
    require_once __DIR__ . '/../molecules/SearchBox.php';
    
    ob_start();
    ?>
    <section class="hero hero--<?php echo htmlspecialchars($variant); ?>" <?php if ($backgroundImage): ?>style="background-image: url('<?php echo htmlspecialchars($backgroundImage); ?>');"<?php endif; ?>>
        <div class="hero__container">
            <div class="hero__content">
                <h1 class="hero__title"><?php echo htmlspecialchars($title); ?></h1>
                <?php if ($description): ?>
                    <p class="hero__description"><?php echo htmlspecialchars($description); ?></p>
                <?php endif; ?>
                
                <?php if ($showSearch): ?>
                    <div class="hero__search">
                        <?php echo SearchBox(['variant' => 'hero']); ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>
    <?php
    return ob_get_clean();
}
