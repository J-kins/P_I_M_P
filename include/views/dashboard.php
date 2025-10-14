
<?php
/**
 * Dashboard template for PHP UI Template System
 */

// Load necessary components
$nav_items = [
    ['url' => '/', 'label' => 'Home', 'active' => false],
    ['url' => '?route=dashboard', 'label' => 'Dashboard', 'active' => true],
    ['url' => '?route=about', 'label' => 'About', 'active' => false],
    ['url' => '?route=contact', 'label' => 'Contact', 'active' => false],
];

// Output document head
echo document_head([
    'title' => 'Dashboard - PHP UI Template System',
    'scripts' => ['js/dashboard.js'],
    'styles' => ['css/component_styles/dashboard.css'],
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
        <?php echo pageHeader('Dashboard', 'Your application control panel'); ?>
        
        <div class="dashboard-grid">
            <div class="card stats-card">
                <h3>Statistics</h3>
                <div class="stat-value">1,024</div>
                <div class="stat-label">Total Users</div>
            </div>
            
            <div class="card stats-card">
                <h3>Revenue</h3>
                <div class="stat-value">$8,540</div>
                <div class="stat-label">This Month</div>
            </div>
            
            <div class="card stats-card">
                <h3>Engagement</h3>
                <div class="stat-value">87%</div>
                <div class="stat-label">Active Users</div>
            </div>
            
            <div class="card theme-card">
                <h3>Theme Selection</h3>
                <div class="theme-options">
                    <button class="theme-option active" data-theme="purple1">Purple</button>
                    <button class="theme-option" data-theme="blue1">Blue</button>
                    <button class="theme-option" data-theme="green1">Green</button>
                </div>
            </div>
        </div>
    </main>
    
    <?php
    // Output footer and close document
    echo standardFooter();
    echo documentClose();
    ?>
</body>

<style>
/* Dashboard specific styles */
.dashboard-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 1.5rem;
    margin-top: 2rem;
}

.card {
    background-color: var(--background-light-100);
    border-radius: 8px;
    box-shadow: var(--card-shadow);
    padding: 1.5rem;
}

.stats-card {
    text-align: center;
}

.stat-value {
    font-size: 2.5rem;
    font-weight: bold;
    color: var(--primary-500);
    line-height: 1.2;
    margin: 1rem 0 0.5rem;
}

.stat-label {
    color: var(--text-secondary);
    font-size: 0.875rem;
}

.theme-options {
    display: flex;
    gap: 0.5rem;
    margin-top: 1rem;
}

.theme-option {
    background: none;
    border: 2px solid var(--border-color);
    border-radius: 4px;
    padding: 0.5rem 1rem;
    cursor: pointer;
    transition: all 0.2s;
}

.theme-option.active {
    background-color: var(--primary-500);
    color: white;
    border-color: var(--primary-500);
}

.theme-option:hover {
    border-color: var(--primary-400);
}
</style>
