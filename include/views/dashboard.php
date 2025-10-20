<?php
/**
 * BBB Dashboard template for PHP UI Template System
 */

// Load BBB components
$bbb_config = bbb_default_config();
$bbb_config['mainNavItems'] = [
    [
        'url' => '/dashboard', 
        'label' => 'Dashboard',
        'active' => true
    ],
    [
        'url' => '/business-profiles', 
        'label' => 'Business Profiles',
        'dropdown' => [
            ['url' => '/find-business', 'label' => 'Find a Business'],
            ['url' => '/accredited-business', 'label' => 'BBB Accredited Businesses'],
        ]
    ],
    [
        'url' => '/complaints', 
        'label' => 'Complaints',
        'dropdown' => [
            ['url' => '/file-complaint', 'label' => 'File a Complaint'],
            ['url' => '/check-complaint', 'label' => 'Check Complaint Status'],
        ]
    ],
    [
        'url' => '/reviews', 
        'label' => 'Reviews',
        'active' => false
    ],
    [
        'url' => '/scam-tracker', 
        'label' => 'Scam Tracker',
        'active' => false
    ],
    [
        'url' => '/consumer-resources', 
        'label' => 'Consumer Resources',
        'dropdown' => [
            ['url' => '/tips', 'label' => 'Tips & Guides'],
            ['url' => '/alerts', 'label' => 'Consumer Alerts'],
        ]
    ]
];

// Dashboard data
$stats = [
    [
        'title' => 'Total Complaints',
        'value' => '1,247',
        'change' => 12.5,
        'icon' => '📋',
        'color' => 'blue'
    ],
    [
        'title' => 'Resolved Issues',
        'value' => '89%',
        'change' => 3.2,
        'icon' => '✅',
        'color' => 'green'
    ],
    [
        'title' => 'Avg Response Time',
        'value' => '2.3 days',
        'change' => -8.1,
        'icon' => '⏱️',
        'color' => 'orange'
    ],
    [
        'title' => 'Customer Satisfaction',
        'value' => '4.2/5',
        'change' => 5.7,
        'icon' => '⭐',
        'color' => 'red'
    ]
];

$quick_actions = [
    ['url' => '/file-complaint', 'label' => 'File Complaint', 'icon' => '📝'],
    ['url' => '/add-business', 'label' => 'Add Business', 'icon' => '🏢'],
    ['url' => '/generate-report', 'label' => 'Generate Report', 'icon' => '📊'],
    ['url' => '/manage-reviews', 'label' => 'Manage Reviews', 'icon' => '💬'],
    ['url' => '/scam-alerts', 'label' => 'Scam Alerts', 'icon' => '🚨'],
    ['url' => '/accreditation', 'label' => 'Accreditation', 'icon' => '🛡️']
];

$breadcrumbs = [
    ['url' => '/', 'label' => 'Home'],
    ['label' => 'Business Dashboard']
];

// Output document head with BBB styles
echo document_head([
    'title' => 'Business Dashboard - Better Business Bureau',
    'scripts' => ['js/dashboard.js'],
    'styles' => ['css/component_styles/dashboard.css'],
]);
?>

<body>
    <?php
    // Output BBB header
    echo bbb_header($bbb_config);
    echo bbb_header_styles();
    ?>

    <main class="bbb-dashboard">
        <?php 
        echo bbb_dashboard_header(
            'Business Dashboard', 
            'Monitor your business performance and customer interactions',
            $breadcrumbs
        );
        ?>
        
        <div class="bbb-stats-grid">
            <?php foreach ($stats as $stat): ?>
                <?php echo bbb_stat_card($stat); ?>
            <?php endforeach; ?>
        </div>
        
        <?php echo bbb_quick_actions($quick_actions); ?>
        
        <div class="bbb-dashboard-grid">
            <div class="bbb-main-content">
                <div class="bbb-card">
                    <h3 class="bbb-section-title">Recent Complaints</h3>
                    <div class="bbb-complaints-list">
                        <div class="bbb-complaint-item">
                            <div class="bbb-complaint-header">
                                <span class="bbb-complaint-id">#COMP-2024-001</span>
                                <span class="bbb-complaint-status bbb-status-pending">Pending</span>
                            </div>
                            <p class="bbb-complaint-desc">Customer reported delayed shipping and poor communication</p>
                            <div class="bbb-complaint-meta">
                                <span>Received: 2 hours ago</span>
                                <span>Business: ABC Electronics</span>
                            </div>
                        </div>
                        
                        <div class="bbb-complaint-item">
                            <div class="bbb-complaint-header">
                                <span class="bbb-complaint-id">#COMP-2024-002</span>
                                <span class="bbb-complaint-status bbb-status-resolved">Resolved</span>
                            </div>
                            <p class="bbb-complaint-desc">Issue with product quality and refund request</p>
                            <div class="bbb-complaint-meta">
                                <span>Resolved: 1 day ago</span>
                                <span>Business: XYZ Services</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="bbb-card">
                    <h3 class="bbb-section-title">Performance Metrics</h3>
                    <div class="bbb-metrics-grid">
                        <div class="bbb-metric">
                            <span class="bbb-metric-value">94%</span>
                            <span class="bbb-metric-label">Response Rate</span>
                        </div>
                        <div class="bbb-metric">
                            <span class="bbb-metric-value">87%</span>
                            <span class="bbb-metric-label">Resolution Rate</span>
                        </div>
                        <div class="bbb-metric">
                            <span class="bbb-metric-value">4.1</span>
                            <span class="bbb-metric-label">Avg Rating</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="bbb-sidebar">
                <div class="bbb-card">
                    <h3 class="bbb-section-title">Alerts & Notifications</h3>
                    <div class="bbb-alerts-list">
                        <div class="bbb-alert bbb-alert-warning">
                            <strong>3 pending complaints</strong> require your attention
                        </div>
                        <div class="bbb-alert bbb-alert-info">
                            Monthly report ready for review
                        </div>
                        <div class="bbb-alert bbb-alert-success">
                            5 complaints resolved this week
                        </div>
                    </div>
                </div>
                
                <div class="bbb-card">
                    <h3 class="bbb-section-title">Recent Reviews</h3>
                    <div class="bbb-reviews-list">
                        <div class="bbb-review">
                            <div class="bbb-review-rating">★★★★☆</div>
                            <p class="bbb-review-text">"Good service but delivery was late..."</p>
                            <span class="bbb-review-meta">- John D., 2 days ago</span>
                        </div>
                        <div class="bbb-review">
                            <div class="bbb-review-rating">★★★★★</div>
                            <p class="bbb-review-text">"Excellent customer support! Highly recommended."</p>
                            <span class="bbb-review-meta">- Sarah M., 3 days ago</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
    
    <?php
    // Output BBB footer
    echo bbb_footer([
        'logo' => 'img/bbb-logo-white.png',
        'links' => [
            [
                'title' => 'For Consumers',
                'links' => [
                    ['url' => '/find-business', 'label' => 'Find a Business'],
                    ['url' => '/file-complaint', 'label' => 'File a Complaint'],
                    ['url' => '/reviews', 'label' => 'Write a Review'],
                ]
            ],
            [
                'title' => 'For Businesses',
                'links' => [
                    ['url' => '/accreditation', 'label' => 'BBB Accreditation'],
                    ['url' => '/business-resources', 'label' => 'Business Resources'],
                    ['url' => '/advertise', 'label' => 'Advertise with BBB'],
                ]
            ],
            [
                'title' => 'About BBB',
                'links' => [
                    ['url' => '/about', 'label' => 'About Us'],
                    ['url' => '/news', 'label' => 'News & Events'],
                    ['url' => '/careers', 'label' => 'Careers'],
                ]
            ]
        ],
        'social' => [
            ['url' => '#', 'name' => 'Facebook', 'icon' => '📘'],
            ['url' => '#', 'name' => 'Twitter', 'icon' => '🐦'],
            ['url' => '#', 'name' => 'LinkedIn', 'icon' => '💼'],
        ],
        'contact' => [
            ['label' => 'Phone', 'value' => '1-800-123-4567'],
            ['label' => 'Email', 'value' => 'help@bbb.org'],
            ['label' => 'Address', 'value' => '123 Business Ave, City, State 12345'],
        ]
    ]);
    
    echo documentClose();
    ?>
</body>

<style>
<?php echo bbb_dashboard_styles(); ?>

/* Additional Dashboard Styles */
.bbb-card {
    background: var(--bbb-white);
    border-radius: 8px;
    padding: var(--bbb-spacing-lg);
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.bbb-complaints-list {
    display: flex;
    flex-direction: column;
    gap: var(--bbb-spacing-md);
}

.bbb-complaint-item {
    padding: var(--bbb-spacing-md);
    border: 1px solid var(--bbb-gray-light);
    border-radius: 6px;
    transition: border-color 0.2s;
}

.bbb-complaint-item:hover {
    border-color: var(--bbb-blue);
}

.bbb-complaint-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: var(--bbb-spacing-sm);
}

.bbb-complaint-id {
    font-weight: 600;
    color: var(--bbb-gray-dark);
}

.bbb-complaint-status {
    padding: var(--bbb-spacing-xs) var(--bbb-spacing-sm);
    border-radius: 12px;
    font-size: var(--bbb-font-size-sm);
    font-weight: 500;
}

.bbb-status-pending {
    background-color: var(--bbb-warning);
    color: var(--bbb-gray-dark);
}

.bbb-status-resolved {
    background-color: var(--bbb-success);
    color: var(--bbb-white);
}

.bbb-complaint-desc {
    color: var(--bbb-gray-dark);
    margin-bottom: var(--bbb-spacing-sm);
}

.bbb-complaint-meta {
    display: flex;
    gap: var(--bbb-spacing-lg);
    font-size: var(--bbb-font-size-sm);
    color: var(--bbb-gray);
}

.bbb-metrics-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: var(--bbb-spacing-lg);
    text-align: center;
}

.bbb-metric {
    display: flex;
    flex-direction: column;
    align-items: center;
}

.bbb-metric-value {
    font-size: 2rem;
    font-weight: bold;
    color: var(--bbb-blue);
    margin-bottom: var(--bbb-spacing-xs);
}

.bbb-metric-label {
    color: var(--bbb-gray);
    font-size: var(--bbb-font-size-sm);
}

.bbb-alerts-list {
    display: flex;
    flex-direction: column;
    gap: var(--bbb-spacing-md);
}

.bbb-alert {
    padding: var(--bbb-spacing-md);
    border-radius: 6px;
    border-left: 4px solid;
}

.bbb-alert-warning {
    background-color: #fff3cd;
    border-left-color: var(--bbb-warning);
    color: #856404;
}

.bbb-alert-info {
    background-color: #d1ecf1;
    border-left-color: var(--bbb-blue);
    color: #0c5460;
}

.bbb-alert-success {
    background-color: #d4edda;
    border-left-color: var(--bbb-success);
    color: #155724;
}

.bbb-reviews-list {
    display: flex;
    flex-direction: column;
    gap: var(--bbb-spacing-md);
}

.bbb-review {
    padding: var(--bbb-spacing-md);
    border: 1px solid var(--bbb-gray-light);
    border-radius: 6px;
}

.bbb-review-rating {
    color: var(--bbb-warning);
    margin-bottom: var(--bbb-spacing-sm);
}

.bbb-review-text {
    color: var(--bbb-gray-dark);
    margin-bottom: var(--bbb-spacing-sm);
    font-style: italic;
}

.bbb-review-meta {
    font-size: var(--bbb-font-size-sm);
    color: var(--bbb-gray);
}
</style>
</html>
