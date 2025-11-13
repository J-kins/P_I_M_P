<?php
/**
 * P.I.M.P - Business Analytics
 * Performance analytics and insights dashboard
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
    'title' => 'Business Analytics - P.I.M.P Business Dashboard',
    'metaTags' => [
        'description' => 'Track your business performance, customer engagement, and growth metrics',
        'keywords' => 'business analytics, performance metrics, customer insights, business intelligence'
    ],
    'styles' => [
        'views/business-analytics.css'
    ],
    'scripts' => [
        'js/business-analytics.js'
    ]
]]);
?>

<body class="business-analytics-page">
    <?php
    echo Components::call('Headers', 'businessHeader', [[
        'logo' => Config::imageUrl('logo.png'),
        'logoAlt' => 'P.I.M.P - Business Repository Platform',
        'mainNavItems' => $nav_items,
        'userActions' => [
            ['url' => '/business/dashboard', 'label' => 'Dashboard', 'separator' => false],
            ['url' => '/business/reviews', 'label' => 'Reviews', 'separator' => false],
            ['url' => '/business/settings', 'label' => 'Settings', 'separator' => false],
            ['url' => '/logout', 'label' => 'Logout', 'separator' => true],
        ],
        'showSearch' => false,
    ]]);
    ?>

    <main class="business-analytics-main">
        <!-- Page Header -->
        <div class="analytics-page-header">
            <div class="container">
                <div class="page-header-content">
                    <h1>Business Analytics</h1>
                    <p>Track your performance and make data-driven decisions</p>
                </div>
            </div>
        </div>

        <!-- Controls Section -->
        <section class="analytics-controls-section">
            <div class="container">
                <div class="controls-card">
                    <div class="controls-header">
                        <div class="date-range-selector">
                            <label for="dateRange">Date Range:</label>
                            <select id="dateRange" class="range-select">
                                <option value="7d">Last 7 Days</option>
                                <option value="30d" selected>Last 30 Days</option>
                                <option value="90d">Last 90 Days</option>
                                <option value="1y">Last Year</option>
                                <option value="custom">Custom Range</option>
                            </select>
                        </div>
                        <div class="controls-actions">
                            <button class="export-button button-secondary" id="exportData">
                                <i class="fas fa-download"></i>
                                Export Data
                            </button>
                            <button class="refresh-button button-primary" id="refreshData">
                                <i class="fas fa-sync-alt"></i>
                                Refresh
                            </button>
                        </div>
                    </div>
                    
                    <!-- Custom Date Range (Initially Hidden) -->
                    <div class="custom-range-selector" id="customRangeSelector" style="display: none;">
                        <div class="custom-range-fields">
                            <div class="form-group">
                                <label for="startDate">From</label>
                                <input type="date" id="startDate" class="form-input">
                            </div>
                            <div class="form-group">
                                <label for="endDate">To</label>
                                <input type="date" id="endDate" class="form-input">
                            </div>
                            <button class="apply-range-button button-primary" id="applyCustomRange">
                                Apply
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- KPI Overview -->
        <section class="kpi-overview-section">
            <div class="container">
                <div class="kpi-grid" id="kpiGrid">
                    <!-- KPI cards will be populated by JavaScript -->
                </div>
            </div>
        </section>

        <!-- Charts Section -->
        <section class="charts-section">
            <div class="container">
                <div class="charts-grid">
                    <!-- Main Chart -->
                    <div class="chart-card main-chart">
                        <div class="chart-header">
                            <h3>Performance Overview</h3>
                            <div class="chart-legend" id="mainChartLegend"></div>
                        </div>
                        <div class="chart-container">
                            <canvas id="performanceChart" width="800" height="400"></canvas>
                        </div>
                    </div>

                    <!-- Secondary Charts -->
                    <div class="chart-card">
                        <div class="chart-header">
                            <h3>Rating Distribution</h3>
                        </div>
                        <div class="chart-container">
                            <canvas id="ratingChart" width="400" height="300"></canvas>
                        </div>
                    </div>

                    <div class="chart-card">
                        <div class="chart-header">
                            <h3>Review Sources</h3>
                        </div>
                        <div class="chart-container">
                            <canvas id="sourcesChart" width="400" height="300"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Metrics Breakdown -->
        <section class="metrics-section">
            <div class="container">
                <div class="metrics-tabs">
                    <div class="tabs-header">
                        <h2>Detailed Metrics</h2>
                        <div class="tabs-navigation">
                            <button class="tab-button active" data-tab="engagement">Engagement</button>
                            <button class="tab-button" data-tab="reputation">Reputation</button>
                            <button class="tab-button" data-tab="growth">Growth</button>
                        </div>
                    </div>

                    <div class="tabs-content">
                        <!-- Engagement Tab -->
                        <div class="tab-panel active" id="engagementTab">
                            <div class="metrics-grid">
                                <div class="metric-card">
                                    <div class="metric-header">
                                        <h4>Profile Views</h4>
                                        <span class="metric-trend positive">+12.5%</span>
                                    </div>
                                    <div class="metric-value">2,847</div>
                                    <div class="metric-chart mini-chart">
                                        <canvas id="viewsChart" width="200" height="60"></canvas>
                                    </div>
                                </div>

                                <div class="metric-card">
                                    <div class="metric-header">
                                        <h4>Click-Through Rate</h4>
                                        <span class="metric-trend positive">+8.3%</span>
                                    </div>
                                    <div class="metric-value">4.2%</div>
                                    <div class="metric-chart mini-chart">
                                        <canvas id="ctrChart" width="200" height="60"></canvas>
                                    </div>
                                </div>

                                <div class="metric-card">
                                    <div class="metric-header">
                                        <h4>Time on Profile</h4>
                                        <span class="metric-trend negative">-2.1%</span>
                                    </div>
                                    <div class="metric-value">2.3m</div>
                                    <div class="metric-chart mini-chart">
                                        <canvas id="timeChart" width="200" height="60"></canvas>
                                    </div>
                                </div>

                                <div class="metric-card">
                                    <div class="metric-header">
                                        <h4>Social Shares</h4>
                                        <span class="metric-trend positive">+15.7%</span>
                                    </div>
                                    <div class="metric-value">156</div>
                                    <div class="metric-chart mini-chart">
                                        <canvas id="sharesChart" width="200" height="60"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Reputation Tab -->
                        <div class="tab-panel" id="reputationTab">
                            <div class="metrics-grid">
                                <div class="metric-card">
                                    <div class="metric-header">
                                        <h4>Average Response Time</h4>
                                        <span class="metric-trend positive">-15.2%</span>
                                    </div>
                                    <div class="metric-value">6.3h</div>
                                    <div class="metric-chart mini-chart">
                                        <canvas id="responseTimeChart" width="200" height="60"></canvas>
                                    </div>
                                </div>

                                <div class="metric-card">
                                    <div class="metric-header">
                                        <h4>Response Rate</h4>
                                        <span class="metric-trend positive">+5.8%</span>
                                    </div>
                                    <div class="metric-value">87%</div>
                                    <div class="metric-chart mini-chart">
                                        <canvas id="responseRateChart" width="200" height="60"></canvas>
                                    </div>
                                </div>

                                <div class="metric-card">
                                    <div class="metric-header">
                                        <h4>Review Sentiment</h4>
                                        <span class="metric-trend positive">+3.4%</span>
                                    </div>
                                    <div class="metric-value">82%</div>
                                    <div class="metric-chart mini-chart">
                                        <canvas id="sentimentChart" width="200" height="60"></canvas>
                                    </div>
                                </div>

                                <div class="metric-card">
                                    <div class="metric-header">
                                        <h4>Customer Satisfaction</h4>
                                        <span class="metric-trend neutral">0.0%</span>
                                    </div>
                                    <div class="metric-value">4.2/5</div>
                                    <div class="metric-chart mini-chart">
                                        <canvas id="satisfactionChart" width="200" height="60"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Growth Tab -->
                        <div class="tab-panel" id="growthTab">
                            <div class="metrics-grid">
                                <div class="metric-card">
                                    <div class="metric-header">
                                        <h4>New Followers</h4>
                                        <span class="metric-trend positive">+22.1%</span>
                                    </div>
                                    <div class="metric-value">342</div>
                                    <div class="metric-chart mini-chart">
                                        <canvas id="followersChart" width="200" height="60"></canvas>
                                    </div>
                                </div>

                                <div class="metric-card">
                                    <div class="metric-header">
                                        <h4>Review Growth</h4>
                                        <span class="metric-trend positive">+18.7%</span>
                                    </div>
                                    <div class="metric-value">127</div>
                                    <div class="metric-chart mini-chart">
                                        <canvas id="reviewGrowthChart" width="200" height="60"></canvas>
                                    </div>
                                </div>

                                <div class="metric-card">
                                    <div class="metric-header">
                                        <h4>Profile Completions</h4>
                                        <span class="metric-trend positive">+7.3%</span>
                                    </div>
                                    <div class="metric-value">94%</div>
                                    <div class="metric-chart mini-chart">
                                        <canvas id="completionChart" width="200" height="60"></canvas>
                                    </div>
                                </div>

                                <div class="metric-card">
                                    <div class="metric-header">
                                        <h4>Referral Traffic</h4>
                                        <span class="metric-trend positive">+11.5%</span>
                                    </div>
                                    <div class="metric-value">845</div>
                                    <div class="metric-chart mini-chart">
                                        <canvas id="referralChart" width="200" height="60"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Insights Section -->
        <section class="insights-section">
            <div class="container">
                <div class="insights-header">
                    <h2>AI-Powered Insights</h2>
                    <p>Actionable recommendations based on your data</p>
                </div>

                <div class="insights-grid" id="insightsGrid">
                    <!-- Insights will be populated by JavaScript -->
                </div>
            </div>
        </section>

        <!-- Comparison Section -->
        <section class="comparison-section">
            <div class="container">
                <div class="comparison-card">
                    <div class="comparison-header">
                        <h2>Industry Comparison</h2>
                        <select id="industryComparison" class="comparison-select">
                            <option value="all">All Industries</option>
                            <option value="technology">Technology</option>
                            <option value="retail">Retail</option>
                            <option value="services">Professional Services</option>
                        </select>
                    </div>
                    <div class="comparison-content">
                        <div class="comparison-metrics">
                            <div class="comparison-item">
                                <div class="metric-info">
                                    <span class="metric-name">Average Rating</span>
                                    <span class="metric-value">4.2</span>
                                </div>
                                <div class="comparison-bar">
                                    <div class="your-score" style="width: 84%"></div>
                                    <div class="industry-average" style="width: 76%"></div>
                                </div>
                                <div class="industry-value">3.8</div>
                            </div>

                            <div class="comparison-item">
                                <div class="metric-info">
                                    <span class="metric-name">Response Rate</span>
                                    <span class="metric-value">87%</span>
                                </div>
                                <div class="comparison-bar">
                                    <div class="your-score" style="width: 87%"></div>
                                    <div class="industry-average" style="width: 65%"></div>
                                </div>
                                <div class="industry-value">65%</div>
                            </div>

                            <div class="comparison-item">
                                <div class="metric-info">
                                    <span class="metric-name">Profile Views</span>
                                    <span class="metric-value">2,847</span>
                                </div>
                                <div class="comparison-bar">
                                    <div class="your-score" style="width: 71%"></div>
                                    <div class="industry-average" style="width: 50%"></div>
                                </div>
                                <div class="industry-value">2,000</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <?php
    echo Components::call('Footers', 'businessFooter', [$footer_config]);
    echo Components::call('Footers', 'documentClose');
    ?>

    <!-- Chart.js Library -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</body>
</html>

<?php echo ob_get_clean(); ?>
