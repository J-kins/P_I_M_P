
<?php
/**
 * Error page (404) for PHP UI Template System
 */

// Set HTTP status code
http_response_code(404);

// Load necessary components
$nav_items = [
    ['url' => '/', 'label' => 'Home', 'active' => false],
    ['url' => '?route=dashboard', 'label' => 'Dashboard', 'active' => false],
];

// Output document head
echo document_head([
    'title' => 'Page Not Found - PHP UI Template System',
]);
?>

<body>
    <?php
    // Output header
    echo headerOne([
        'title' => 'PHP UI Template',
        'navItems' => $nav_items,
    ]);
    ?>

    <main class="container">
        <div class="error-container">
            <h1>404</h1>
            <h2>Page Not Found</h2>
            <p>The page you are looking for does not exist or has been moved.</p>
            <a href="<?= url() ?>" class="button">Return to Homepage</a>
        </div>
    </main>
    
    <?php
    // Output footer and close document
    echo standardFooter();
    echo documentClose();
    ?>
</body>

<style>
.error-container {
    text-align: center;
    padding: 4rem 1rem;
}

.error-container h1 {
    font-size: 6rem;
    color: var(--primary-300);
    margin: 0;
}

.error-container h2 {
    font-size: 2rem;
    margin-bottom: 1rem;
}

.error-container .button {
    display: inline-block;
    margin-top: 2rem;
}
</style>
