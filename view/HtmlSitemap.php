<?php
/**
 * P.I.M.P - HTML Sitemap View
 * User-friendly sitemap page
 */

use PIMP\Core\Config;
use PIMP\Views\Components;

$nav_items = [
    ['url' => '/', 'label' => 'Home', 'active' => false],
    ['url' => '/businesses', 'label' => 'Business Directory', 'active' => false],
    ['url' => '/reviews', 'label' => 'Reviews', 'active' => false],
    ['url' => '/categories', 'label' => 'Categories', 'active' => false],
    ['url' => '/scam-alerts', 'label' => 'Scam Alerts', 'active' => false],
    ['url' => '/resources', 'label' => 'Resources', 'active' => false],
];

$footer_config = [
    'logo' => Config::imageUrl('logo.png'),
    'logoAlt' => 'P.I.M.P Business Repository',
    'theme' => 'light'
];

ob_start();
?>

<?php
echo Components::call('Headers', 'documentHead', [[
    'title' => 'Sitemap - P.I.M.P Business Repository',
    'metaTags' => [
        'description' => 'Browse our complete sitemap to find businesses, reviews, categories, and resources on P.I.M.P Business Repository.',
        'keywords' => 'sitemap, site navigation, business directory, P.I.M.P'
    ],
    'styles' => [
        'views/sitemap.css'
    ]
]]);
?>

<body class="sitemap-page">
    <?php
    echo Components::call('Headers', 'businessHeader', [[
        'logo' => Config::imageUrl('logo.png'),
        'logoAlt' => 'P.I.M.P - Business Repository Platform',
        'mainNavItems' => $nav_items,
        'userActions' => [
            ['url' => '/login', 'label' => 'Login', 'separator' => false],
            ['url' => '/register', 'label' => 'Register', 'separator' => false],
        ],
        'showSearch' => true,
    ]]);
    ?>

    <main class="sitemap-main">
        <!-- Hero Section -->
        <section class="sitemap-hero">
            <div class="container">
                <div class="hero-content">
                    <h1>Site Navigation</h1>
                    <p>Browse our complete directory of businesses, reviews, and resources</p>
                    <div class="sitemap-stats">
                        <div class="stat-item">
                            <span class="stat-number"><?= number_format($total_businesses) ?></span>
                            <span class="stat-label">Businesses</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-number"><?= number_format($total_reviews) ?></span>
                            <span class="stat-label">Reviews</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-number"><?= count($categories) ?></span>
                            <span class="stat-label">Categories</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Main Pages Section -->
        <section class="sitemap-section">
            <div class="container">
                <div class="section-header">
                    <i class="fas fa-home"></i>
                    <h2>Main Pages</h2>
                </div>
                <div class="sitemap-grid">
                    <?php foreach ($main_pages as $page): ?>
                    <div class="sitemap-item">
                        <a href="<?= Config::url($page['url']) ?>" class="sitemap-link">
                            <span class="link-title"><?= htmlspecialchars($page['title']) ?></span>
                            <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div class="view-all-link">
                    <a href="<?= Config::url('/businesses') ?>" class="button button-outline">
                        View All Businesses
                        <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        </section>

        <!-- Recent Reviews Section -->
        <section class="sitemap-section">
            <div class="container">
                <div class="section-header">
                    <i class="fas fa-star"></i>
                    <h2>Recent Reviews</h2>
                </div>
                <div class="review-list">
                    <?php foreach ($recent_reviews as $review): ?>
                    <div class="review-item">
                        <a href="<?= Config::url('/review/' . $review['uuid']) ?>" class="review-link">
                            <div class="review-info">
                                <span class="review-title"><?= htmlspecialchars($review['title']) ?></span>
                                <span class="review-business">
                                    for <a href="<?= Config::url('/business/' . $review['business_uuid']) ?>">
                                        <?= htmlspecialchars($review['business_name']) ?>
                                    </a>
                                </span>
                            </div>
                            <div class="review-meta">
                                <span class="review-rating">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <i class="fas fa-star <?= $i <= $review['rating'] ? 'filled' : 'empty' ?>"></i>
                                    <?php endfor; ?>
                                </span>
                                <span class="review-date">
                                    <?= date('M d, Y', strtotime($review['review_date'])) ?>
                                </span>
                            </div>
                        </a>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div class="view-all-link">
                    <a href="<?= Config::url('/reviews') ?>" class="button button-outline">
                        View All Reviews
                        <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        </section>

        <!-- Search Section -->
        <section class="sitemap-cta">
            <div class="container">
                <div class="cta-content">
                    <h2>Can't find what you're looking for?</h2>
                    <p>Use our search feature to quickly find businesses, reviews, or resources</p>
                    <div class="search-box">
                        <form action="<?= Config::url('/search') ?>" method="GET">
                            <input type="text" name="q" placeholder="Search businesses, categories, or reviews..." required>
                            <button type="submit">
                                <i class="fas fa-search"></i>
                                Search
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <?php
    echo Components::call('Footers', 'businessFooter', [$footer_config]);
    echo Components::call('Footers', 'documentClose');
    ?>

    <style>
        .sitemap-page {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }

        .sitemap-hero {
            background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%);
            color: white;
            padding: 60px 0;
            text-align: center;
        }

        .hero-content h1 {
            font-size: 48px;
            font-weight: 700;
            margin-bottom: 16px;
        }

        .hero-content p {
            font-size: 20px;
            opacity: 0.9;
            margin-bottom: 40px;
        }

        .sitemap-stats {
            display: flex;
            justify-content: center;
            gap: 60px;
            margin-top: 40px;
        }

        .stat-item {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .stat-number {
            font-size: 36px;
            font-weight: 700;
        }

        .stat-label {
            font-size: 14px;
            opacity: 0.8;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .sitemap-section {
            padding: 60px 0;
            border-bottom: 1px solid #e5e7eb;
        }

        .section-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 30px;
        }

        .section-header i {
            font-size: 24px;
            color: #2563eb;
        }

        .section-header h2 {
            font-size: 32px;
            font-weight: 700;
            margin: 0;
        }

        .sitemap-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 16px;
        }

        .sitemap-item {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            transition: all 0.2s;
        }

        .sitemap-item:hover {
            border-color: #2563eb;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.1);
        }

        .sitemap-link {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 16px;
            text-decoration: none;
            color: #1f2937;
        }

        .sitemap-link:hover {
            color: #2563eb;
        }

        .link-title {
            font-weight: 500;
        }

        .category-tree {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .category-item {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            overflow: hidden;
        }

        .category-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 16px;
            text-decoration: none;
            color: #1f2937;
            font-weight: 500;
            transition: all 0.2s;
        }

        .category-link:hover {
            background: #f3f4f6;
            color: #2563eb;
        }

        .category-link i {
            color: #6b7280;
        }

        .category-children {
            background: #f9fafb;
            border-top: 1px solid #e5e7eb;
        }

        .category-children .category-item {
            border: none;
            border-bottom: 1px solid #e5e7eb;
            border-radius: 0;
        }

        .category-children .category-item:last-child {
            border-bottom: none;
        }

        .category-children .category-link {
            padding-left: 32px;
        }

        .business-list, .review-list {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .business-item, .review-item {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            transition: all 0.2s;
        }

        .business-item:hover, .review-item:hover {
            border-color: #2563eb;
            box-shadow: 0 2px 8px rgba(37, 99, 235, 0.1);
        }

        .business-link, .review-link {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 16px;
            text-decoration: none;
            color: #1f2937;
        }

        .business-info, .review-info {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .business-name, .review-title {
            font-weight: 500;
            font-size: 16px;
        }

        .business-rating {
            display: flex;
            align-items: center;
            gap: 4px;
            color: #f59e0b;
            font-size: 14px;
        }

        .review-business {
            font-size: 14px;
            color: #6b7280;
        }

        .review-business a {
            color: #2563eb;
            text-decoration: none;
        }

        .review-business a:hover {
            text-decoration: underline;
        }

        .review-meta {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 4px;
        }

        .review-rating {
            display: flex;
            gap: 2px;
        }

        .review-rating i.filled {
            color: #f59e0b;
        }

        .review-rating i.empty {
            color: #d1d5db;
        }

        .business-date, .review-date {
            font-size: 14px;
            color: #6b7280;
        }

        .view-all-link {
            text-align: center;
            margin-top: 30px;
        }

        .button {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 24px;
            border-radius: 8px;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.2s;
        }

        .button-outline {
            border: 2px solid #2563eb;
            color: #2563eb;
            background: white;
        }

        .button-outline:hover {
            background: #2563eb;
            color: white;
        }

        .sitemap-cta {
            background: #f9fafb;
            padding: 80px 0;
            text-align: center;
        }

        .cta-content h2 {
            font-size: 36px;
            font-weight: 700;
            margin-bottom: 12px;
        }

        .cta-content p {
            font-size: 18px;
            color: #6b7280;
            margin-bottom: 30px;
        }

        .search-box form {
            display: flex;
            max-width: 600px;
            margin: 0 auto;
            gap: 12px;
        }

        .search-box input {
            flex: 1;
            padding: 16px;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            font-size: 16px;
        }

        .search-box input:focus {
            outline: none;
            border-color: #2563eb;
        }

        .search-box button {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 16px 32px;
            background: #2563eb;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
        }

        .search-box button:hover {
            background: #1e40af;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        @media (max-width: 768px) {
            .hero-content h1 {
                font-size: 32px;
            }

            .sitemap-stats {
                gap: 30px;
            }

            .stat-number {
                font-size: 28px;
            }

            .sitemap-grid {
                grid-template-columns: 1fr;
            }

            .section-header h2 {
                font-size: 24px;
            }

            .search-box form {
                flex-direction: column;
            }
        }
    </style>
</body>
</html>

<?php echo ob_get_clean(); ?> ?>
                </div>
            </div>
        </section>

        <!-- Categories Section -->
        <section class="sitemap-section">
            <div class="container">
                <div class="section-header">
                    <i class="fas fa-folder-open"></i>
                    <h2>Business Categories</h2>
                </div>
                <div class="category-tree">
                    <?php foreach ($categories as $category): ?>
                        <?php if ($category['parent_id'] === null || $category['parent_id'] == 0): ?>
                        <div class="category-item category-level-<?= $category['depth'] ?>">
                            <a href="<?= Config::url('/category/' . $category['uuid']) ?>" class="category-link">
                                <i class="fas fa-tag"></i>
                                <span><?= htmlspecialchars($category['name']) ?></span>
                            </a>
                            <?php
                            // Show child categories
                            $children = array_filter($categories, function($c) use ($category) {
                                return $c['parent_id'] == $category['id'];
                            });
                            if (!empty($children)):
                            ?>
                            <div class="category-children">
                                <?php foreach ($children as $child): ?>
                                <div class="category-item category-level-<?= $child['depth'] ?>">
                                    <a href="<?= Config::url('/category/' . $child['uuid']) ?>" class="category-link">
                                        <i class="fas fa-angle-right"></i>
                                        <span><?= htmlspecialchars($child['name']) ?></span>
                                    </a>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
                <div class="view-all-link">
                    <a href="<?= Config::url('/categories') ?>" class="button button-outline">
                        View All Categories
                        <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        </section>

        <!-- Recent Businesses Section -->
        <section class="sitemap-section">
            <div class="container">
                <div class="section-header">
                    <i class="fas fa-building"></i>
                    <h2>Recently Added Businesses</h2>
                </div>
                <div class="business-list">
                    <?php foreach ($recent_businesses as $business): ?>
                    <div class="business-item">
                        <a href="<?= Config::url('/business/' . $business['uuid']) ?>" class="business-link">
                            <div class="business-info">
                                <span class="business-name">
                                    <?= htmlspecialchars($business['trading_name'] ?: $business['legal_name']) ?>
                                </span>
                                <?php if ($business['average_rating'] > 0): ?>
                                <span class="business-rating">
                                    <i class="fas fa-star"></i>
                                    <?= number_format($business['average_rating'], 1) ?>
                                </span>
                                <?php endif; ?>
                            </div>
                            <span class="business-date">
                                <?= date('M d, Y', strtotime($business['created_at'])) ?>
                            </span>
                        </a>
                    </div>
                    <?php endforeach;