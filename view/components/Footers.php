<?php
/**
 * P.I.M.P Footer Components
 * Comprehensive footer system for business repository platform
 */

namespace PIMP\Views\Components;

class Footers
{
    /**
     * Standard footer component with navigation and copyright
     * 
     * @param array $params {
     *   @type string $copyright Copyright text
     *   @type array $navItems Footer navigation items
     *   @type string $class Additional CSS classes
     *   @type array $socialLinks Social media links with icons
     *   @type string $theme Theme variant (light|dark|primary)
     *   @type string $logo Logo image or text for brand in footer
     * }
     * @return string HTML output
     */
    public static function standardFooter(array $params = []): string
    {
        $copyright = $params['copyright'] ?? '© ' . date('Y') . ' P.I.M.P Business Repository';
        $navItems = $params['navItems'] ?? [];
        $class = $params['class'] ?? '';
        $socialLinks = $params['socialLinks'] ?? [];
        $theme = $params['theme'] ?? 'light';
        $logo = $params['logo'] ?? '';
        
        ob_start(); ?>
        <footer class="footer footer-standard footer-theme-<?= htmlspecialchars($theme) ?> <?= htmlspecialchars($class) ?>">
            <div class="container">
                <?php if (!empty($logo)): ?>
                <div class="footer-logo">
                    <?php if (filter_var($logo, FILTER_VALIDATE_URL) || strpos($logo, '/') !== false): ?>
                    <img src="<?= self::assetUrl($logo) ?>" alt="Logo" class="logo-image">
                    <?php else: ?>
                    <div class="logo-text"><?= htmlspecialchars($logo) ?></div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($navItems)): ?>
                <nav class="footer-nav" aria-label="Footer navigation">
                    <ul class="footer-nav-list">
                        <?php foreach ($navItems as $item): ?>
                        <li class="footer-nav-item">
                            <a href="<?= self::url($item['url']) ?>" class="footer-link">
                                <?= htmlspecialchars($item['label']) ?>
                            </a>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </nav>
                <?php endif; ?>
                
                <?php if (!empty($socialLinks)): ?>
                <div class="footer-social">
                    <?php foreach ($socialLinks as $social): ?>
                    <a href="<?= htmlspecialchars($social['url']) ?>" 
                       class="social-link social-<?= htmlspecialchars($social['platform']) ?>"
                       aria-label="<?= htmlspecialchars($social['platform']) ?>"
                       <?= (!empty($social['newTab'])) ? 'target="_blank" rel="noopener"' : '' ?>>
                        <?= $social['icon'] ?>
                    </a>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
                
                <div class="footer-copyright">
                    <p><?= htmlspecialchars($copyright) ?></p>
                </div>
            </div>
        </footer>
        <?php return ob_get_clean();
    }

    /**
     * Multi-column footer with sections for different link categories
     * 
     * @param array $params {
     *   @type string $copyright Copyright text
     *   @type array $columns Column data with heading and links
     *   @type array $socialLinks Social media icons
     *   @type string $theme Theme variant (light|dark|primary)
     *   @type string $logo Brand logo or text
     *   @type string $description Short company/brand description
     *   @type array $newsletterForm Newsletter subscription form config
     *   @type array $bottomLinks Additional links for bottom section
     * }
     * @return string HTML output
     */
    public static function multiColumnFooter(array $params = []): string
    {
        $copyright = $params['copyright'] ?? '© ' . date('Y') . ' P.I.M.P Business Repository';
        $columns = $params['columns'] ?? [];
        $socialLinks = $params['socialLinks'] ?? [];
        $theme = $params['theme'] ?? 'light';
        $logo = $params['logo'] ?? '';
        $description = $params['description'] ?? '';
        $newsletterForm = $params['newsletterForm'] ?? false;
        $bottomLinks = $params['bottomLinks'] ?? [];
        
        ob_start(); ?>
        <footer class="footer footer-multi-column footer-theme-<?= htmlspecialchars($theme) ?>">
            <div class="container">
                <div class="footer-main">
                    <?php if (!empty($logo) || !empty($description)): ?>
                    <div class="footer-brand">
                        <?php if (!empty($logo)): ?>
                        <div class="footer-logo">
                            <?php if (filter_var($logo, FILTER_VALIDATE_URL) || strpos($logo, '/') !== false): ?>
                            <img src="<?= self::assetUrl($logo) ?>" alt="Logo" class="logo-image">
                            <?php else: ?>
                            <div class="logo-text"><?= htmlspecialchars($logo) ?></div>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($description)): ?>
                        <div class="footer-description">
                            <p><?= htmlspecialchars($description) ?></p>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($socialLinks)): ?>
                        <div class="footer-social">
                            <?php foreach ($socialLinks as $social): ?>
                            <a href="<?= htmlspecialchars($social['url']) ?>" 
                               class="social-link social-<?= htmlspecialchars($social['platform']) ?>"
                               aria-label="<?= htmlspecialchars($social['platform']) ?>"
                               <?= (!empty($social['newTab'])) ? 'target="_blank" rel="noopener"' : '' ?>>
                                <?= $social['icon'] ?>
                            </a>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($columns)): ?>
                    <div class="footer-columns">
                        <?php foreach ($columns as $column): ?>
                        <div class="footer-column">
                            <h3 class="footer-heading"><?= htmlspecialchars($column['heading']) ?></h3>
                            <?php if (!empty($column['links'])): ?>
                            <ul class="footer-links">
                                <?php foreach ($column['links'] as $link): ?>
                                <li class="footer-link-item">
                                    <a href="<?= self::url($link['url']) ?>" class="footer-link">
                                        <?= htmlspecialchars($link['label']) ?>
                                    </a>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($newsletterForm)): ?>
                    <div class="footer-newsletter">
                        <h3 class="newsletter-heading"><?= htmlspecialchars($newsletterForm['heading'] ?? 'Subscribe to our newsletter') ?></h3>
                        <?php if (!empty($newsletterForm['description'])): ?>
                        <p class="newsletter-description"><?= htmlspecialchars($newsletterForm['description']) ?></p>
                        <?php endif; ?>
                        
                        <form action="<?= self::url($newsletterForm['action'] ?? '#') ?>" method="post" class="newsletter-form">
                            <div class="form-group">
                                <input type="email" name="email" placeholder="<?= htmlspecialchars($newsletterForm['placeholder'] ?? 'Enter your email') ?>" 
                                       required class="newsletter-input">
                                <button type="submit" class="newsletter-button">
                                    <?= htmlspecialchars($newsletterForm['buttonText'] ?? 'Subscribe') ?>
                                </button>
                            </div>
                        </form>
                    </div>
                    <?php endif; ?>
                </div>
                
                <div class="footer-bottom">
                    <div class="footer-copyright">
                        <p><?= htmlspecialchars($copyright) ?></p>
                    </div>
                    
                    <?php if (!empty($bottomLinks)): ?>
                    <div class="footer-bottom-links">
                        <ul class="bottom-link-list">
                            <?php foreach ($bottomLinks as $link): ?>
                            <li class="bottom-link-item">
                                <a href="<?= self::url($link['url']) ?>" class="bottom-link">
                                    <?= htmlspecialchars($link['label']) ?>
                                </a>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </footer>
        <?php return ob_get_clean();
    }

    /**
     * Modern footer with app download links and contact info
     * 
     * @param array $params {
     *   @type string $copyright Copyright text
     *   @type array $links Footer links
     *   @type array $contactInfo Contact information
     *   @type array $appDownloads App download links
     *   @type string $theme Theme variant (light|dark|primary)
     *   @type string $logo Logo image or text
     *   @type string $description Brand description
     *   @type array $socialLinks Social media links
     * }
     * @return string HTML output
     */
    public static function modernFooter(array $params = []): string
    {
        $copyright = $params['copyright'] ?? '© ' . date('Y') . ' P.I.M.P Business Repository';
        $links = $params['links'] ?? [];
        $contactInfo = $params['contactInfo'] ?? [];
        $appDownloads = $params['appDownloads'] ?? [];
        $theme = $params['theme'] ?? 'light';
        $logo = $params['logo'] ?? '';
        $description = $params['description'] ?? '';
        $socialLinks = $params['socialLinks'] ?? [];
        
        ob_start(); ?>
        <footer class="footer footer-modern footer-theme-<?= htmlspecialchars($theme) ?>">
            <div class="container">
                <div class="footer-main">
                    <div class="footer-brand">
                        <?php if (!empty($logo)): ?>
                        <div class="footer-logo">
                            <?php if (filter_var($logo, FILTER_VALIDATE_URL) || strpos($logo, '/') !== false): ?>
                            <img src="<?= self::assetUrl($logo) ?>" alt="Logo" class="logo-image">
                            <?php else: ?>
                            <div class="logo-text"><?= htmlspecialchars($logo) ?></div>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($description)): ?>
                        <div class="footer-description">
                            <p><?= htmlspecialchars($description) ?></p>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <?php if (!empty($contactInfo)): ?>
                    <div class="footer-contact">
                        <?php if (!empty($contactInfo['email'])): ?>
                        <div class="contact-item contact-email">
                            <span class="contact-label">Email:</span>
                            <a href="mailto:<?= htmlspecialchars($contactInfo['email']) ?>" class="contact-value">
                                <?= htmlspecialchars($contactInfo['email']) ?>
                            </a>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($contactInfo['phone'])): ?>
                        <div class="contact-item contact-phone">
                            <span class="contact-label">Phone:</span>
                            <a href="tel:<?= preg_replace('/[^0-9+]/', '', $contactInfo['phone']) ?>" class="contact-value">
                                <?= htmlspecialchars($contactInfo['phone']) ?>
                            </a>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($links)): ?>
                    <nav class="footer-links">
                        <ul class="footer-links-list">
                            <?php foreach ($links as $link): ?>
                            <li class="footer-link-item">
                                <a href="<?= self::url($link['url']) ?>" class="footer-link">
                                    <?= htmlspecialchars($link['label']) ?>
                                </a>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </nav>
                    <?php endif; ?>
                    
                    <?php if (!empty($appDownloads)): ?>
                    <div class="footer-downloads">
                        <?php if (!empty($appDownloads['heading'])): ?>
                        <h3 class="downloads-heading"><?= htmlspecialchars($appDownloads['heading']) ?></h3>
                        <?php endif; ?>
                        
                        <div class="download-buttons">
                            <?php if (!empty($appDownloads['ios'])): ?>
                            <a href="<?= htmlspecialchars($appDownloads['ios']) ?>" class="download-button download-ios">
                                <img src="<?= self::assetUrl('img/app-store-badge.svg') ?>" alt="Download on App Store">
                            </a>
                            <?php endif; ?>
                            
                            <?php if (!empty($appDownloads['android'])): ?>
                            <a href="<?= htmlspecialchars($appDownloads['android']) ?>" class="download-button download-android">
                                <img src="<?= self::assetUrl('img/google-play-badge.svg') ?>" alt="Get it on Google Play">
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
                
                <div class="footer-bottom">
                    <div class="footer-copyright">
                        <p><?= htmlspecialchars($copyright) ?></p>
                    </div>
                    
                    <?php if (!empty($socialLinks)): ?>
                    <div class="footer-social">
                        <?php foreach ($socialLinks as $social): ?>
                        <a href="<?= htmlspecialchars($social['url']) ?>" 
                           class="social-link social-<?= htmlspecialchars($social['platform']) ?>"
                           aria-label="<?= htmlspecialchars($social['platform']) ?>"
                           <?= (!empty($social['newTab'])) ? 'target="_blank" rel="noopener"' : '' ?>>
                            <?= $social['icon'] ?>
                        </a>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </footer>
        <?php return ob_get_clean();
    }

    /**
     * Minimal footer with just copyright and optional links
     * 
     * @param array $params {
     *   @type string $copyright Copyright text
     *   @type array $links Footer links
     *   @type string $theme Theme variant (light|dark|primary)
     * }
     * @return string HTML output
     */
    public static function minimalFooter(array $params = []): string
    {
        $copyright = $params['copyright'] ?? '© ' . date('Y') . ' P.I.M.P Business Repository';
        $links = $params['links'] ?? [];
        $theme = $params['theme'] ?? 'light';
        
        ob_start(); ?>
        <footer class="footer footer-minimal footer-theme-<?= htmlspecialchars($theme) ?>">
            <div class="container">
                <div class="footer-content">
                    <div class="footer-copyright">
                        <p><?= htmlspecialchars($copyright) ?></p>
                    </div>
                    
                    <?php if (!empty($links)): ?>
                    <nav class="footer-links">
                        <ul class="footer-links-list">
                            <?php foreach ($links as $link): ?>
                            <li class="footer-link-item">
                                <a href="<?= self::url($link['url']) ?>" class="footer-link">
                                    <?= htmlspecialchars($link['label']) ?>
                                </a>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </nav>
                    <?php endif; ?>
                </div>
            </div>
        </footer>
        <?php return ob_get_clean();
    }

    /**
     * Newsletter subscription-focused footer
     * 
     * @param array $params {
     *   @type string $heading Newsletter heading
     *   @type string $description Newsletter description
     *   @type string $placeholder Input placeholder
     *   @type string $buttonText Button text
     *   @type string $action Form action URL
     *   @type string $copyright Copyright text
     *   @type array $links Footer links
     *   @type string $theme Theme variant (light|dark|primary)
     * }
     * @return string HTML output
     */
    public static function newsletterFooter(array $params = []): string
    {
        $heading = $params['heading'] ?? 'Join our newsletter';
        $description = $params['description'] ?? 'Stay updated with our latest news and offers.';
        $placeholder = $params['placeholder'] ?? 'Enter your email';
        $buttonText = $params['buttonText'] ?? 'Subscribe';
        $action = $params['action'] ?? '#';
        $copyright = $params['copyright'] ?? '© ' . date('Y') . ' P.I.M.P Business Repository';
        $links = $params['links'] ?? [];
        $theme = $params['theme'] ?? 'light';
        
        ob_start(); ?>
        <footer class="footer footer-newsletter footer-theme-<?= htmlspecialchars($theme) ?>">
            <div class="container">
                <div class="newsletter-container">
                    <div class="newsletter-content">
                        <h3 class="newsletter-heading"><?= htmlspecialchars($heading) ?></h3>
                        <p class="newsletter-description"><?= htmlspecialchars($description) ?></p>
                    </div>
                    
                    <div class="newsletter-form-container">
                        <form action="<?= self::url($action) ?>" method="post" class="newsletter-form">
                            <div class="form-group">
                                <input type="email" name="email" placeholder="<?= htmlspecialchars($placeholder) ?>" required class="newsletter-input">
                                <button type="submit" class="newsletter-button"><?= htmlspecialchars($buttonText) ?></button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <div class="footer-bottom">
                    <div class="footer-copyright">
                        <p><?= htmlspecialchars($copyright) ?></p>
                    </div>
                    
                    <?php if (!empty($links)): ?>
                    <nav class="footer-links">
                        <ul class="footer-links-list">
                            <?php foreach ($links as $link): ?>
                            <li class="footer-link-item">
                                <a href="<?= self::url($link['url']) ?>" class="footer-link">
                                    <?= htmlspecialchars($link['label']) ?>
                                </a>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </nav>
                    <?php endif; ?>
                </div>
            </div>
        </footer>
        <?php return ob_get_clean();
    }

    /**
     * Business Repository Footer (BBB-style)
     * 
     * @param array $params {
     *   @type string $logo Logo image path
     *   @type string $logoAlt Logo alt text
     *   @type array $links Footer link columns
     *   @type array $social Social media links
     *   @type array $contact Contact information
     *   @type string $theme Theme variant (light|dark|primary)
     * }
     * @return string HTML output
     */
    public static function businessFooter(array $params = []): string
    {
        $logo = $params['logo'] ?? '';
        $logoAlt = $params['logoAlt'] ?? 'P.I.M.P Business Repository';
        $links = $params['links'] ?? [];
        $social = $params['social'] ?? [];
        $contact = $params['contact'] ?? [];
        $theme = $params['theme'] ?? 'light';
        
        ob_start(); ?>
        <footer class="footer footer-business footer-theme-<?= htmlspecialchars($theme) ?>">
            <div class="container">
                <div class="footer-main">
                    <div class="footer-brand">
                        <?php if (!empty($logo)): ?>
                        <img src="<?= self::assetUrl($logo) ?>" alt="<?= htmlspecialchars($logoAlt) ?>" class="footer-logo">
                        <?php endif; ?>
                        <p class="footer-description">
                            P.I.M.P Business Repository helps consumers find businesses and charities they can trust.
                        </p>
                    </div>
                    
                    <?php if (!empty($links)): ?>
                    <div class="footer-links">
                        <?php foreach ($links as $column): ?>
                        <div class="footer-column">
                            <h4 class="footer-title"><?= htmlspecialchars($column['title']) ?></h4>
                            <ul class="footer-list">
                                <?php foreach ($column['links'] as $link): ?>
                                <li><a href="<?= self::url($link['url']) ?>" class="footer-link"><?= htmlspecialchars($link['label']) ?></a></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                    
                    <div class="footer-contact">
                        <h4 class="footer-title">Contact Us</h4>
                        <?php foreach ($contact as $item): ?>
                        <div class="contact-item">
                            <strong><?= htmlspecialchars($item['label']) ?>:</strong>
                            <span><?= htmlspecialchars($item['value']) ?></span>
                        </div>
                        <?php endforeach; ?>
                        
                        <?php if (!empty($social)): ?>
                        <div class="social-links">
                            <?php foreach ($social as $platform): ?>
                            <a href="<?= self::url($platform['url']) ?>" class="social-link" aria-label="<?= htmlspecialchars($platform['name']) ?>"
                               <?= (!empty($platform['newTab'])) ? 'target="_blank" rel="noopener"' : '' ?>>
                                <?= $platform['icon'] ?? '🔗' ?>
                            </a>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="footer-bottom">
                    <p>&copy; <?= date('Y') ?> P.I.M.P Business Repository. All rights reserved.</p>
                </div>
            </div>
        </footer>
        <?php return ob_get_clean();
    }

    /**
     * Admin dashboard footer
     * 
     * @param array $params {
     *   @type string $version System version
     *   @type array $links Admin footer links
     *   @type string $copyright Copyright text
     * }
     * @return string HTML output
     */
    public static function adminFooter(array $params = []): string
    {
        $version = $params['version'] ?? '1.0.0';
        $links = $params['links'] ?? [];
        $copyright = $params['copyright'] ?? '© ' . date('Y') . ' P.I.M.P Business Repository';
        
        ob_start(); ?>
        <footer class="footer footer-admin">
            <div class="container">
                <div class="footer-content">
                    <div class="footer-left">
                        <span class="version">v<?= htmlspecialchars($version) ?></span>
                        <span class="copyright"><?= htmlspecialchars($copyright) ?></span>
                    </div>
                    
                    <?php if (!empty($links)): ?>
                    <div class="footer-right">
                        <nav class="footer-nav">
                            <ul class="footer-nav-list">
                                <?php foreach ($links as $link): ?>
                                <li class="footer-nav-item">
                                    <a href="<?= self::url($link['url']) ?>" class="footer-link">
                                        <?= htmlspecialchars($link['label']) ?>
                                    </a>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                        </nav>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </footer>
        <?php return ob_get_clean();
    }

    /**
     * Document closing tags with scripts
     * 
     * @param array $params {
     *   @type array $scripts Additional scripts to include at end of body
     *   @type bool $includeMainJs Whether to include main.js
     * }
     * @return string HTML output
     */
    public static function documentClose(array $params = []): string
    {
        $scripts = $params['scripts'] ?? [];
        $includeMainJs = $params['includeMainJs'] ?? true;
        
        ob_start(); ?>
        <?php if (!empty($scripts)): ?>
            <?php foreach ($scripts as $script): ?>
            <script src="<?= self::assetUrl($script) ?>"></script>
            <?php endforeach; ?>
        <?php endif; ?>
        
        <?php if ($includeMainJs): ?>
        <script src="<?= self::url('/static/js/headers.js') ?>"></script>
        <?php endif; ?>
        
        </body>
        </html>
        <?php return ob_get_clean();
    }

    /**
     * URL helper method - uses PIMP Config class
     */
    private static function url(string $path): string
    {
        if (class_exists('PIMP\\Core\\Config')) {
            return \PIMP\Core\Config::url($path);
        }
        
        // Fallback for development
        return $path;
    }

    /**
     * Asset URL helper method - uses PIMP Config class
     */
    private static function assetUrl(string $path): string
    {
        if (class_exists('PIMP\\Core\\Config')) {
            return \PIMP\Core\Config::assetUrl($path);
        }
        
        // Fallback for development
        return $path;
    }
}

// Optional: Create aliases for easier usage
class Footer extends Footers
{
    // Alias class for shorter usage
}