<?php
/**
 * P.I.M.P - Sitemap Controller
 * Handles XML and HTML sitemap generation
 */

namespace PIMP\Controllers;

use PIMP\Models\SitemapModel;
use PIMP\Services\Database\MySQLDatabase;
use PIMP\Core\Config;
use Exception;

class SitemapController
{
    private $sitemapModel;
    private $cacheDir;
    private $baseUrl;

    public function __construct(MySQLDatabase $db)
    {
        $this->sitemapModel = new SitemapModel($db);
        $this->cacheDir = dirname(__DIR__) . '/static/sitemaps/';
        $this->baseUrl = Config::get('base_url', 'https://pimp-platform.com');

        // Ensure cache directory exists
        if (!is_dir($this->cacheDir)) {
            mkdir($this->cacheDir, 0755, true);
        }
    }

    /**
     * Generate and serve main sitemap index
     */
    public function index()
    {
        $cacheFile = $this->cacheDir . 'sitemap.xml';
        $cacheTime = 3600; // 1 hour

        // Check if cache is valid
        if ($this->isCacheValid($cacheFile, $cacheTime)) {
            $this->serveSitemap($cacheFile);
            return;
        }

        // Generate new sitemap index
        $xml = $this->generateSitemapIndex();

        // Save to cache
        file_put_contents($cacheFile, $xml);

        // Serve
        $this->serveSitemap($cacheFile);
    }

    /**
     * Generate and serve main pages sitemap
     */
    public function mainPages()
    {
        $cacheFile = $this->cacheDir . 'sitemap-main.xml';
        $cacheTime = 86400; // 24 hours

        if ($this->isCacheValid($cacheFile, $cacheTime)) {
            $this->serveSitemap($cacheFile);
            return;
        }

        $xml = $this->generateMainPagesSitemap();
        file_put_contents($cacheFile, $xml);
        $this->serveSitemap($cacheFile);
    }

    /**
     * Generate and serve businesses sitemap
     * 
     * @param int $page Page number (1-based)
     */
    public function businesses($page = 1)
    {
        $cacheFile = $this->cacheDir . "sitemap-businesses-{$page}.xml";
        $cacheTime = 3600; // 1 hour

        if ($this->isCacheValid($cacheFile, $cacheTime)) {
            $this->serveSitemap($cacheFile);
            return;
        }

        $xml = $this->generateBusinessesSitemap($page);
        file_put_contents($cacheFile, $xml);
        $this->serveSitemap($cacheFile);
    }

    /**
     * Generate and serve reviews sitemap
     * 
     * @param int $page Page number (1-based)
     */
    public function reviews($page = 1)
    {
        $cacheFile = $this->cacheDir . "sitemap-reviews-{$page}.xml";
        $cacheTime = 3600; // 1 hour

        if ($this->isCacheValid($cacheFile, $cacheTime)) {
            $this->serveSitemap($cacheFile);
            return;
        }

        $xml = $this->generateReviewsSitemap($page);
        file_put_contents($cacheFile, $xml);
        $this->serveSitemap($cacheFile);
    }

    /**
     * Generate and serve categories sitemap
     */
    public function categories()
    {
        $cacheFile = $this->cacheDir . 'sitemap-categories.xml';
        $cacheTime = 86400; // 24 hours

        if ($this->isCacheValid($cacheFile, $cacheTime)) {
            $this->serveSitemap($cacheFile);
            return;
        }

        $xml = $this->generateCategoriesSitemap();
        file_put_contents($cacheFile, $xml);
        $this->serveSitemap($cacheFile);
    }

    /**
     * Display HTML sitemap for users
     */
    public function htmlSitemap()
    {
        $data = [
            'main_pages' => $this->sitemapModel->getMainPages(),
            'categories' => $this->sitemapModel->getCategories(),
            'recent_businesses' => $this->sitemapModel->getRecentBusinesses(50),
            'recent_reviews' => $this->sitemapModel->getRecentReviews(20),
            'total_businesses' => $this->sitemapModel->getTotalBusinesses(),
            'total_reviews' => $this->sitemapModel->getTotalReviews()
        ];

        require_once dirname(__DIR__) . '/views/HtmlSitemap.php';
    }

    /**
     * Clear sitemap cache
     */
    public function clearCache()
    {
        $files = glob($this->cacheDir . '*.xml');
        $cleared = 0;

        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
                $cleared++;
            }
        }

        return [
            'success' => true,
            'message' => "Cleared {$cleared} sitemap cache files",
            'count' => $cleared
        ];
    }

    /**
     * Generate sitemap index (main sitemap file)
     * 
     * @return string XML content
     */
    private function generateSitemapIndex(): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        // Main pages sitemap
        $xml .= $this->addSitemapEntry('sitemap-main.xml', date('Y-m-d'));

        // Categories sitemap
        $xml .= $this->addSitemapEntry('sitemap-categories.xml', date('Y-m-d'));

        // Business sitemaps (split into pages of 5000)
        $totalBusinesses = $this->sitemapModel->getTotalBusinesses();
        $businessPages = ceil($totalBusinesses / 5000);

        for ($i = 1; $i <= $businessPages; $i++) {
            $xml .= $this->addSitemapEntry("sitemap-businesses-{$i}.xml", date('Y-m-d'));
        }

        // Review sitemaps (split into pages of 5000)
        $totalReviews = $this->sitemapModel->getTotalReviews();
        $reviewPages = ceil($totalReviews / 5000);

        for ($i = 1; $i <= $reviewPages; $i++) {
            $xml .= $this->addSitemapEntry("sitemap-reviews-{$i}.xml", date('Y-m-d'));
        }

        $xml .= '</sitemapindex>';

        return $xml;
    }

    /**
     * Generate main pages sitemap
     * 
     * @return string XML content
     */
    private function generateMainPagesSitemap(): string
    {
        $pages = $this->sitemapModel->getMainPages();

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        foreach ($pages as $page) {
            $xml .= $this->addUrlEntry(
                $page['url'],
                $page['lastmod'] ?? date('Y-m-d'),
                $page['changefreq'] ?? 'weekly',
                $page['priority'] ?? '0.8'
            );
        }

        $xml .= '</urlset>';

        return $xml;
    }

    /**
     * Generate businesses sitemap
     * 
     * @param int $page Page number
     * @return string XML content
     */
    private function generateBusinessesSitemap(int $page): string
    {
        $perPage = 5000;
        $businesses = $this->sitemapModel->getBusinesses($page, $perPage);

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        foreach ($businesses as $business) {
            $url = $this->baseUrl . '/business/' . $business['uuid'];
            $xml .= $this->addUrlEntry(
                $url,
                $business['updated_at'] ?? $business['created_at'],
                'weekly',
                '0.7'
            );
        }

        $xml .= '</urlset>';

        return $xml;
    }

    /**
     * Generate reviews sitemap
     * 
     * @param int $page Page number
     * @return string XML content
     */
    private function generateReviewsSitemap(int $page): string
    {
        $perPage = 5000;
        $reviews = $this->sitemapModel->getReviews($page, $perPage);

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        foreach ($reviews as $review) {
            $url = $this->baseUrl . '/review/' . $review['uuid'];
            $xml .= $this->addUrlEntry(
                $url,
                $review['updated_at'] ?? $review['review_date'],
                'monthly',
                '0.5'
            );
        }

        $xml .= '</urlset>';

        return $xml;
    }

    /**
     * Generate categories sitemap
     * 
     * @return string XML content
     */
    private function generateCategoriesSitemap(): string
    {
        $categories = $this->sitemapModel->getCategories();

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        foreach ($categories as $category) {
            $url = $this->baseUrl . '/category/' . $category['uuid'];
            $xml .= $this->addUrlEntry(
                $url,
                $category['updated_at'] ?? date('Y-m-d'),
                'weekly',
                '0.6'
            );
        }

        $xml .= '</urlset>';

        return $xml;
    }

    /**
     * Add sitemap entry to index
     * 
     * @param string $loc Sitemap location
     * @param string $lastmod Last modification date
     * @return string XML fragment
     */
    private function addSitemapEntry(string $loc, string $lastmod): string
    {
        $url = $this->baseUrl . '/sitemaps/' . $loc;
        
        $xml = "  <sitemap>\n";
        $xml .= "    <loc>" . htmlspecialchars($url) . "</loc>\n";
        $xml .= "    <lastmod>" . date('Y-m-d', strtotime($lastmod)) . "</lastmod>\n";
        $xml .= "  </sitemap>\n";

        return $xml;
    }

    /**
     * Add URL entry to sitemap
     * 
     * @param string $loc URL location
     * @param string $lastmod Last modification date
     * @param string $changefreq Change frequency
     * @param string $priority Priority (0.0 to 1.0)
     * @return string XML fragment
     */
    private function addUrlEntry(
        string $loc,
        string $lastmod,
        string $changefreq = 'weekly',
        string $priority = '0.5'
    ): string {
        $xml = "  <url>\n";
        $xml .= "    <loc>" . htmlspecialchars($loc) . "</loc>\n";
        $xml .= "    <lastmod>" . date('Y-m-d', strtotime($lastmod)) . "</lastmod>\n";
        $xml .= "    <changefreq>" . $changefreq . "</changefreq>\n";
        $xml .= "    <priority>" . $priority . "</priority>\n";
        $xml .= "  </url>\n";

        return $xml;
    }

    /**
     * Check if cache file is valid
     * 
     * @param string $file Cache file path
     * @param int $maxAge Maximum age in seconds
     * @return bool
     */
    private function isCacheValid(string $file, int $maxAge): bool
    {
        if (!file_exists($file)) {
            return false;
        }

        $fileAge = time() - filemtime($file);
        return $fileAge < $maxAge;
    }

    /**
     * Serve sitemap file
     * 
     * @param string $file File path
     */
    private function serveSitemap(string $file)
    {
        header('Content-Type: application/xml; charset=utf-8');
        header('X-Robots-Tag: noindex');
        readfile($file);
        exit;
    }
}
