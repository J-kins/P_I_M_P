<?php
/**
 * P.I.M.P - Business Categories
 * Browse businesses by category
 */

use PIMP\Core\Config;
use PIMP\Views\Components;

$nav_items = [
    ['url' => '/', 'label' => 'Home', 'active' => false],
    ['url' => '/businesses', 'label' => 'Business Directory', 'active' => false],
    ['url' => '/reviews', 'label' => 'Reviews', 'active' => false],
    ['url' => '/categories', 'label' => 'Categories', 'active' => true],
    ['url' => '/scam-alerts', 'label' => 'Scam Alerts', 'active' => false],
    ['url' => '/resources', 'label' => 'Resources', 'active' => false],
];

// Categories data
$categories = [
    [
        'name' => 'Restaurants & Dining',
        'slug' => 'restaurants',
        'count' => 1250,
        'icon' => 'fas fa-utensils',
        'description' => 'Find the best dining experiences, from fine dining to casual eateries',
        'subcategories' => ['Fine Dining', 'Casual Dining', 'Fast Food', 'Cafes', 'Bars & Pubs']
    ],
    [
        'name' => 'Retail & Shopping',
        'slug' => 'retail',
        'count' => 890,
        'icon' => 'fas fa-shopping-cart',
        'description' => 'Shop with trusted retailers for all your needs',
        'subcategories' => ['Clothing Stores', 'Electronics', 'Home Goods', 'Specialty Shops']
    ],
    // ... more categories
];

$footer_config = [
    'logo' => Config::imageUrl('logo.png'),
    'logoAlt' => 'P.I.M.P Business Repository',
    'links' => [
        [
            'title' => 'For Consumers',
            'links' => [
                ['url' => '/businesses', 'label' => 'Find Businesses'],
                ['url' => '/reviews/write', 'label' => 'Write a Review'],
                ['url' => '/scam-alerts', 'label' => 'Scam Alerts'],
                ['url' => '/resources/tips', 'label' => 'Consumer Tips'],
            ]
        ],
        // ... more footer links
    ],
    'theme' => 'light'
];

ob_start();
?>

<?php
echo Components::call('Headers', 'documentHead', [[
    'title' => 'Business Categories - P.I.M.P Business Repository',
    'metaTags' => [
        'description' => 'Browse businesses by category. Find trusted local businesses in specific industries and services.',
        'keywords' => 'business categories, industry categories, business types, PIMP categories'
    ],
    'styles' => [
        'views/business-categories.css'
    ]
]]);
?>

<body>
    <?php
    echo Components::call('Headers', 'businessHeader', [[
        'logo' => Config::imageUrl('logo.png'),
        'logoAlt' => 'P.I.M.P - Business Repository Platform',
        'mainNavItems' => $nav_items,
        'userActions' => [
            ['url' => '/login', 'label' => 'Log In'],
            ['url' => '/register', 'label' => 'Register', 'separator' => true],
            ['url' => '/business', 'label' => 'For Business'],
        ],
        'showSearch' => true,
    ]]);
    ?>

    <main class="main-content">
        <!-- Categories Header -->
        <section class="categories-header-section">
            <div class="container">
                <?php
                echo Components::call('Headers', 'pageHeader', [
                    'Business Categories',
                    'Browse businesses by industry and service type',
                    'text-center'
                ]);
                ?>
            </div>
        </section>

        <!-- Categories Grid -->
        <section class="categories-grid-section">
            <div class="container">
                <div class="categories-grid">
                    <?php foreach ($categories as $category): ?>
                    <div class="category-card">
                        <div class="category-header">
                            <div class="category-icon">
                                <i class="<?= $category['icon'] ?>"></i>
                            </div>
                            <h3 class="category-name">
                                <a href="<?= Config::url('/category/' . $category['slug']) ?>">
                                    <?= htmlspecialchars($category['name']) ?>
                                </a>
                            </h3>
                        </div>
                        
                        <p class="category-description">
                            <?= htmlspecialchars($category['description']) ?>
                        </p>
                        
                        <div class="category-meta">
                            <span class="business-count">
                                <i class="fas fa-building"></i>
                                <?= number_format($category['count']) ?> businesses
                            </span>
                        </div>

                        <?php if (!empty($category['subcategories'])): ?>
                        <div class="subcategories">
                            <h4 class="subcategories-title">Popular in this category:</h4>
                            <div class="subcategories-list">
                                <?php foreach (array_slice($category['subcategories'], 0, 3) as $subcategory): ?>
                                <a href="<?= Config::url('/category/' . $category['slug'] . '?subcategory=' . urlencode($subcategory)) ?>" 
                                   class="subcategory-tag">
                                    <?= htmlspecialchars($subcategory) ?>
                                </a>
                                <?php endforeach; ?>
                                <?php if (count($category['subcategories']) > 3): ?>
                                <span class="more-subcategories">
                                    +<?= count($category['subcategories']) - 3 ?> more
                                </span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endif; ?>

                        <div class="category-actions">
                            <a href="<?= Config::url('/category/' . $category['slug']) ?>" class="button button-primary">
                                Browse Category
                                <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <!-- Popular Categories -->
        <section class="popular-categories-section">
            <div class="container">
                <h2 class="section-title">Most Popular Categories</h2>
                <div class="popular-categories">
                    <?php foreach (array_slice($categories, 0, 6) as $category): ?>
                    <a href="<?= Config::url('/category/' . $category['slug']) ?>" class="popular-category">
                        <div class="popular-category-icon">
                            <i class="<?= $category['icon'] ?>"></i>
                        </div>
                        <div class="popular-category-info">
                            <h4><?= htmlspecialchars($category['name']) ?></h4>
                            <span class="business-count"><?= number_format($category['count']) ?> businesses</span>
                        </div>
                        <i class="fas fa-chevron-right"></i>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
    </main>

    <?php
    echo Components::call('Footers', 'businessFooter', [$footer_config]);
    echo Components::call('Footers', 'documentClose');
    ?>
</body>
</html>

<?php echo ob_get_clean(); ?>