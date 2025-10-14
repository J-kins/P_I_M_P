
<?php
/**
 * Spreadsheet demo page template for PHP UI Template System
 */

// Load necessary components
$nav_items = [
    ['url' => '/', 'label' => 'Home', 'active' => false],
    ['url' => '?route=dashboard', 'label' => 'Dashboard', 'active' => false],
    ['url' => '?route=spreadsheet', 'label' => 'Spreadsheet', 'active' => true],
    ['url' => '?route=about', 'label' => 'About', 'active' => false],
];

$footer_items = [
    ['url' => '?route=about', 'label' => 'About Us'],
    ['url' => '?route=privacy', 'label' => 'Privacy Policy'],
    ['url' => '?route=terms', 'label' => 'Terms of Service'],
    ['url' => '?route=contact', 'label' => 'Contact'],
];

// Output document head
echo document_head([
    'title' => 'Spreadsheet Demo - PHP UI Template System',
    'scripts' => [
        'https://ajax.googleapis.com/ajax/libs/angularjs/1.8.2/angular.min.js',
        'js/utils/spreadsheetModel.js',
        'js/utils/spreadsheetController.js'
    ],
    'styles' => ['css/component_styles/spreadsheet.css'],
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
        <?php echo pageHeader('Spreadsheet Demo', 'Interactive spreadsheet component with AngularJS'); ?>
        
        <section class="content-section">
            <div class="card">
                <h2>Basic Spreadsheet</h2>
                <p>A simple spreadsheet with 10 columns and 20 rows:</p>
                
                <?php echo spreadsheet([
                    'id' => 'basic-spreadsheet',
                    'rows' => 20,
                    'columns' => 10
                ]); ?>
            </div>
            
            <div class="card mt-4">
                <h2>Formula Tips</h2>
                <p>You can use formulas by typing an equals sign followed by your expression:</p>
                <ul>
                    <li><code>=A1+B1</code> - Add values from cells A1 and B1</li>
                    <li><code>=A1*5</code> - Multiply value in cell A1 by 5</li>
                    <li><code>=A1/B1</code> - Divide value in cell A1 by value in cell B1</li>
                </ul>
            </div>
            
            <div class="card mt-4">
                <h2>Advanced Spreadsheet</h2>
                <p>A multi-sheet spreadsheet with formula support:</p>
                
                <?php echo advancedSpreadsheet(); ?>
            </div>
        </section>
    </main>
    
    <?php
    // Output footer
    echo standardFooter([
        'copyright' => '© ' . date('Y') . ' PHP UI Template System',
        'navItems' => $footer_items
    ]);
    
    // Close document
    echo documentClose();
    ?>
</body>

<style>
.mt-4 {
    margin-top: 2rem;
}

.card {
    background-color: var(--background-light-100);
    border-radius: 8px;
    box-shadow: var(--card-shadow);
    padding: 1.5rem;
    margin-bottom: 1.5rem;
}

code {
    background-color: var(--background-light-200);
    padding: 0.2rem 0.4rem;
    border-radius: 4px;
    font-family: var(--font-mono);
    font-size: 0.9em;
}
</style>

<?php
// Add script for UI enhancements
echo '<script>
document.addEventListener("DOMContentLoaded", function() {
    // Initialize AngularJS application if not already done
    if (typeof angular !== "undefined") {
        angular.bootstrap(document.getElementById("basic-spreadsheet"), ["spreadsheetApp"]);
    }
});
</script>';
?>
