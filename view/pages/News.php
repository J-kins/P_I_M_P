<?php
/**
 * P.I.M.P - News Page
 * News and updates about P.I.M.P platform
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

// Sample news articles (in production, these would come from database)
$news_articles = [
    [
        'id' => 1,
        'title' => 'P.I.M.P Launches New Accreditation Program',
        'excerpt' => 'We\'re excited to announce our new business accreditation program designed to help businesses build trust with consumers.',
        'date' => '2024-01-15',
        'category' => 'Announcements',
        'image' => Config::imageUrl('news/accreditation.jpg'),
        'author' => 'P.I.M.P Team'
    ],
    [
        'id' => 2,
        'title' => 'Consumer Protection Tips for 2024',
        'excerpt' => 'Learn how to protect yourself from scams and make informed decisions when choosing businesses.',
        'date' => '2024-01-10',
        'category' => 'Tips',
        'image' => Config::imageUrl('news/protection.jpg'),
        'author' => 'P.I.M.P Team'
    ],
    [
        'id' => 3,
        'title' => 'New Features: Enhanced Business Search',
        'excerpt' => 'We\'ve improved our search functionality to help you find businesses faster and more accurately.',
        'date' => '2024-01-05',
        'category' => 'Updates',
        'image' => Config::imageUrl('news/search.jpg'),
        'author' => 'P.I.M.P Team'
    ],
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
        [
            'title' => 'For Businesses',
            'links' => [
                ['url' => '/business/claim', 'label' => 'Claim Your Business'],
                ['url' => '/business/advertise', 'label' => 'Advertise With Us'],
                ['url' => '/business/resources', 'label' => 'Business Resources'],
                ['url' => '/for-business', 'label' => 'For Business Home'],
            ]
        ],
        [
            'title' => 'Company',
            'links' => [
                ['url' => '/about', 'label' => 'About Us'],
                ['url' => '/news', 'label' => 'News & Updates', 'active' => true],
                ['url' => '/careers', 'label' => 'Careers'],
                ['url' => '/contact', 'label' => 'Contact Us'],
            ]
        ]
    ],
    'theme' => 'light'
];

ob_start();
?>

<?php
echo Components::call('Headers', 'documentHead', [[
    'title' => 'News & Updates - P.I.M.P Business Repository',
    'metaTags' => [
        'description' => 'Stay updated with the latest news, announcements, and updates from P.I.M.P Business Repository.',
        'keywords' => 'PIMP news, updates, announcements, business directory news',
        'author' => 'P.I.M.P Business Repository'
    ],
    'canonical' => Config::url('/news'),
    'styles' => [
        'views/news.css'
    ]
]]);
?>

<body>
    <?php
    echo Components::call('Headers', 'businessHeader', [[
        'logo' => Config::imageUrl('logo.png'),
        'logoAlt' => 'P.I.M.P - Business Repository Platform',
        'topBarItems' => [
            ['url' => '/about', 'label' => 'About PIMP'],
            ['url' => '/news', 'label' => 'News & Updates', 'active' => true],
            ['url' => '/careers', 'label' => 'Careers'],
            ['url' => '/contact', 'label' => 'Contact Us'],
        ],
        'mainNavItems' => $nav_items,
        'userActions' => [
            ['url' => '/login', 'label' => 'Log In'],
            ['url' => '/register', 'label' => 'Register', 'separator' => true],
            ['url' => '/for-business', 'label' => 'For Business'],
        ],
        'showSearch' => true,
    ]]);
    ?>

    <!-- Hero Section -->
    <?php
    echo Components::call('Headers', 'heroHeader', [[
        'title' => 'News & Updates',
        'subtitle' => 'Stay informed about the latest from P.I.M.P',
        'bgImage' => Config::imageUrl('hero-bg.jpg'),
        'overlay' => 'dark',
        'size' => 'md',
        'align' => 'center'
    ]]);
    ?>

    <main class="main-content">
        <div class="news-page">
            <div class="container">
                <!-- Featured Article -->
                <?php if (!empty($news_articles)): ?>
                <div class="featured-article">
                    <?php $featured = $news_articles[0]; ?>
                    <div class="featured-image">
                        <img src="<?= htmlspecialchars($featured['image']) ?>" alt="<?= htmlspecialchars($featured['title']) ?>">
                        <div class="featured-overlay">
                            <div class="featured-category"><?= htmlspecialchars($featured['category']) ?></div>
                        </div>
                    </div>
                    <div class="featured-content">
                        <div class="article-meta">
                            <span class="article-date">
                                <i class="fas fa-calendar"></i>
                                <?= date('F d, Y', strtotime($featured['date'])) ?>
                            </span>
                            <span class="article-author">
                                <i class="fas fa-user"></i>
                                <?= htmlspecialchars($featured['author']) ?>
                            </span>
                        </div>
                        <h2><?= htmlspecialchars($featured['title']) ?></h2>
                        <p><?= htmlspecialchars($featured['excerpt']) ?></p>
                        <a href="<?= Config::url('/news/' . $featured['id']) ?>" class="button button-primary">
                            Read More <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>

                <!-- News Grid -->
                <div class="news-grid">
                    <?php foreach (array_slice($news_articles, 1) as $article): ?>
                    <article class="news-card">
                        <div class="news-image">
                            <img src="<?= htmlspecialchars($article['image']) ?>" alt="<?= htmlspecialchars($article['title']) ?>">
                            <div class="news-category"><?= htmlspecialchars($article['category']) ?></div>
                        </div>
                        <div class="news-content">
                            <div class="article-meta">
                                <span class="article-date">
                                    <i class="fas fa-calendar"></i>
                                    <?= date('M d, Y', strtotime($article['date'])) ?>
                                </span>
                            </div>
                            <h3><?= htmlspecialchars($article['title']) ?></h3>
                            <p><?= htmlspecialchars($article['excerpt']) ?></p>
                            <a href="<?= Config::url('/news/' . $article['id']) ?>" class="news-link">
                                Read More <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    </article>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <div class="no-news">
                    <i class="fas fa-newspaper"></i>
                    <h3>No News Available</h3>
                    <p>Check back soon for the latest updates and announcements.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <?php
    echo Components::call('Footers', 'businessFooter', [$footer_config]);
    ?>

    <?php
    echo Components::call('Footers', 'documentClose', [[
        'includeMainJs' => true
    ]]);
    ?>
</body>
</html>

<?php
echo ob_get_clean();
?>

