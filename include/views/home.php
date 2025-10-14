
<?php
/**
 * Homepage template for PHP UI Template System
 */

// Load necessary components
$nav_items = [
    ['url' => '/', 'label' => 'Home', 'active' => true],
    ['url' => '?route=dashboard', 'label' => 'Dashboard', 'active' => false],
    ['url' => '?route=about', 'label' => 'About', 'active' => false],
    ['url' => '?route=contact', 'label' => 'Contact', 'active' => false],
];

$footer_items = [
    ['url' => '?route=about', 'label' => 'About Us'],
    ['url' => '?route=privacy', 'label' => 'Privacy Policy'],
    ['url' => '?route=terms', 'label' => 'Terms of Service'],
    ['url' => '?route=contact', 'label' => 'Contact'],
];

// Output document head
echo document_head([
    'title' => 'P . I . M . P',
    'metaTags' => [
        'description' => 'A business repository based on trust',
        'keywords' => 'PHP, UI, UX, Template, Theming, HTML, CSS, JS'
    ]
]);
?>

<body>
    <?php
    // Output header
    echo headerOne([
        'title' => 'Public Interest in Market Practices',
        'navItems' => $nav_items,
        'type' => 'default'
    ]);
    ?>

    <main class="container">
        <?php echo pageHeader('Welcome to P . I . M . P (Public Interest in Market Practices)', 'A business repository platform that\'ll bring you good tidings and say riddens to the bad businesses'); ?>
        
        <section class="content-section">
            
        </section>
    </main>
    
    <?php
    // Output footer
    echo standardFooter([
        'copyright' => '© ' . date('Y') . ' P . I . M . P, GROUP-E 2\'24',
        'navItems' => $footer_items
    ]);
    
    // Close document
    echo documentClose([
        'scripts' => ['js/theme-loader.js']
    ]);
    ?>
</body>

<style>
.button-group {
    display: flex;
    gap: 1rem;
    margin-top: 1.5rem;
}

.button {
    display: inline-block;
    padding: 0.5rem 1rem;
    background-color: var(--background-light-300);
    color: var(--text-primary);
    border: 1px solid var(--border-color);
    border-radius: 4px;
    text-decoration: none;
    font-weight: 500;
    transition: all 0.2s ease;
}

.button:hover {
    background-color: var(--background-light-400);
    text-decoration: none;
}

.button-primary {
    background-color: var(--primary-500);
    border-color: var(--primary-600);
    color: white;
}

.button-primary:hover {
    background-color: var(--primary-600);
    color: white;
}
</style>
