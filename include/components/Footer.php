<?php
/**
 * Footer Component
 * 
 * @param array $props Configuration options
 *   - sections: array - Footer sections [{title, links: [{label, href}]}]
 *   - copyright: string - Copyright text
 *   - social: array - Social links [{icon, href, label}]
 *   - logo: string - Logo HTML
 *   - variant: string - Variant (simple|multi-column) default: 'multi-column'
 *   - className: string - Additional CSS classes
 * @return string HTML markup
 */
function Footer($props = []) {
    $sections = $props['sections'] ?? [];
    $copyright = $props['copyright'] ?? '';
    $social = $props['social'] ?? [];
    $logo = $props['logo'] ?? '';
    $variant = $props['variant'] ?? 'multi-column';
    $className = $props['className'] ?? '';
    
    ob_start();
    ?>
    <footer class="footer footer-<?php echo $variant; ?> <?php echo $className; ?>" role="contentinfo">
        <?php if ($variant === 'multi-column' && !empty($sections)): ?>
            <div class="footer-content">
                <?php if ($logo): ?>
                    <div class="footer-section footer-brand">
                        <div class="footer-logo"><?php echo $logo; ?></div>
                    </div>
                <?php endif; ?>
                
                <?php foreach ($sections as $section): ?>
                    <div class="footer-section">
                        <?php if (!empty($section['title'])): ?>
                            <h3 class="footer-title"><?php echo $section['title']; ?></h3>
                        <?php endif; ?>
                        <?php if (!empty($section['links'])): ?>
                            <ul class="footer-links">
                                <?php foreach ($section['links'] as $link): ?>
                                    <li class="footer-link-item">
                                        <a href="<?php echo $link['href'] ?? '#'; ?>" class="footer-link">
                                            <?php echo $link['label']; ?>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <div class="footer-bottom">
            <?php if ($copyright): ?>
                <p class="footer-copyright"><?php echo $copyright; ?></p>
            <?php endif; ?>
            
            <?php if (!empty($social)): ?>
                <div class="footer-social">
                    <?php foreach ($social as $item): ?>
                        <a href="<?php echo $item['href'] ?? '#'; ?>" 
                           class="footer-social-link" 
                           aria-label="<?php echo $item['label'] ?? ''; ?>">
                            <?php echo $item['icon']; ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </footer>
    <?php
    return ob_get_clean();
}
?>
