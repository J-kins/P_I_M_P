<?php
/**
 * P.I.M.P - Error Page
 */

use PIMP\Core\Config;
use PIMP\Views\Components;

// Start output
ob_start();
?>

<?php
// Set HTTP status code
http_response_code(404);

// Document head
echo Components::call('Headers', 'documentHead', [[
    'title' => 'Page Not Found - P.I.M.P Business Repository',
    'metaTags' => [
        'description' => 'The page you are looking for does not exist or has been moved.',
        'robots' => 'noindex, nofollow'
    ],
    'styles' => [
        'views/error.css'
    ]
]]);
?>

<body>
    <?php
    // Simple header for error page
    echo Components::call('Headers', 'headerOne', [[
        'title' => 'P.I.M.P',
        'logo' => Config::imageUrl('logo.png'),
        'navItems' => [
            ['url' => '/', 'label' => 'Home', 'active' => false],
            ['url' => '/businesses', 'label' => 'Business Directory', 'active' => false],
            ['url' => '/reviews', 'label' => 'Reviews', 'active' => false],
        ],
        'type' => 'minimal'
    ]]);
    ?>

    <main class="error-main">
        <div class="container">
            <div class="error-content">
                <div class="error-icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <h1>404</h1>
                <h2>Page Not Found</h2>
                <p>The page you are looking for does not exist or has been moved.</p>
                
                <div class="error-actions">
                    <a href="<?= Config::url('/') ?>" class="button button-primary">
                        <i class="fas fa-home"></i>
                        Return to Homepage
                    </a>
                    <a href="<?= Config::url('/businesses') ?>" class="button button-outline">
                        <i class="fas fa-search"></i>
                        Browse Businesses
                    </a>
                </div>
                
                <div class="error-search">
                    <p>Or try searching for what you need:</p>
                    <?php
                    echo Components::call('Navigation', 'searchForm', [[
                        'placeholder' => 'Search for businesses, services...',
                        'action' => '/businesses/search',
                        'layout' => 'inline'
                    ]]);
                    ?>
                </div>
            </div>
        </div>
    </main>

    <style>
    .error-main {
        min-height: 70vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 2rem 0;
    }

    .error-content {
        text-align: center;
        max-width: 600px;
        margin: 0 auto;
    }

    .error-icon {
        font-size: 4rem;
        color: #ff6b6b;
        margin-bottom: 2rem;
    }

    .error-content h1 {
        font-size: 6rem;
        color: #8a5cf5;
        margin: 0;
        font-weight: bold;
    }

    .error-content h2 {
        font-size: 2rem;
        margin-bottom: 1rem;
        color: #333;
    }

    .error-content p {
        font-size: 1.2rem;
        color: #666;
        margin-bottom: 2rem;
    }

    .error-actions {
        display: flex;
        gap: 1rem;
        justify-content: center;
        margin-bottom: 3rem;
        flex-wrap: wrap;
    }

    .error-search {
        background: #f8f9fa;
        padding: 2rem;
        border-radius: 12px;
        border-left: 4px solid #8a5cf5;
    }

    .error-search p {
        margin-bottom: 1rem;
        font-size: 1rem;
    }

    @media (max-width: 768px) {
        .error-content h1 {
            font-size: 4rem;
        }
        
        .error-actions {
            flex-direction: column;
            align-items: center;
        }
        
        .error-actions .button {
            width: 100%;
            max-width: 300px;
        }
    }
    </style>
</body>
</html>

<?php
// Output the complete page
echo ob_get_clean();
?>