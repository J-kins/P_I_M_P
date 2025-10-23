/**
 * P.I.M.P - Business Analytics JavaScript
 * Handles analytics dashboard, charts, and data visualization
 */

class BusinessAnalytics {
    constructor() {
        this.charts = {};
        this.currentDateRange = '30d';
        this.data = {};
        this.initialize();
    }

    initialize() {
        this.bindEventListeners();
        this.loadSampleData();
        this.initializeCharts();
        this.renderKPIs();
        this.renderInsights();
        
        // Initial data load
        this.loadData();
    }

    // Event listeners
    bindEventListeners() {
        // Date range selector
        const dateRangeSelect = document.getElementById('dateRange');
        if (dateRangeSelect) {
            dateRangeSelect.addEventListener('change', (e) => {
                this.handleDateRangeChange(e.target.value);
            });
        }

        // Custom date range
        const applyCustomRange = document.getElementById('applyCustomRange');
        if (applyCustomRange) {
            applyCustomRange.addEventListener('click', () => {
                this.applyCustomDateRange();
            });
        }

        // Refresh button
        const refreshButton = document.getElementById('refreshData');
        if (refreshButton) {
            refreshButton.addEventListener('click', () => {
                this.refreshData();
            });
        }

        // Export button
        const exportButton = document.getElementById('exportData');
        if (exportButton) {
            exportButton.addEventListener('click', () => {
                this.exportData();
            });
        }

        // Metrics tabs
        const tabButtons = document.querySelectorAll('.tab-button');
        tabButtons.forEach(button => {
            button.addEventListener('click', () => {
                this.switchMetricsTab(button.getAttribute('data-tab'));
            });
        });

        // Industry comparison
        const industrySelect = document.getElementById('industryComparison');
        if (industrySelect) {
            industrySelect.addEventListener('change', (e) => {
                this.updateIndustryComparison(e.target.value);
            });
        }

        // Window resize for chart responsiveness
        window.addEventListener('resize', () => {
            this.handleResize();
        });
    }

    // Date range handling
    handleDateRangeChange(range) {
        if (range === 'custom') {
            this.showCustomDateRange();
        } else {
            this.hideCustomDateRange();
            this.currentDateRange = range;
            this.loadData();
        }
    }

    showCustomDateRange() {
        const customRangeSelector = document.getElementById('customRangeSelector');
        if (customRangeSelector) {
            customRangeSelector.style.display = 'block';
        }
    }

    hideCustomDateRange() {
        const customRangeSelector = document.getElementById('customRangeSelector');
        if (customRangeSelector) {
            customRangeSelector.style.display = 'none';
        }
    }

    applyCustomDateRange() {
        const startDate = document.getElementById('startDate').value;
        const endDate = document.getElementById('endDate').value;
        
        if (!startDate || !endDate) {
            alert('Please select both start and end dates.');
            return;
        }

        if (new Date(startDate) > new Date(endDate)) {
            alert('Start date cannot be after end date.');
            return;
        }

        this.currentDateRange = 'custom';
        this.loadData();
    }

    // Data management
    async loadData() {
        this.showLoadingState(true);
        
        try {
            // Simulate API call
            await this.fetchAnalyticsData();
            this.updateCharts();
            this.renderKPIs();
            this.renderInsights();
        } catch (error) {
            console.error('Error loading analytics data:', error);
            this.showError('Failed to load analytics data. Please try again.');
        } finally {
            this.showLoadingState(false);
        }
    }

    async fetchAnalyticsData() {
        // Simulate API delay
        return new Promise((resolve) => {
            setTimeout(() => {
                // Generate sample data based on date range
                this.generateSampleData();
                resolve(this.data);
            }, 1000);
        });
    }

    generateSampleData() {
        const dataPoints = this.getDataPointsForRange();
        
        this.data = {
            kpis: {
                totalViews: Math.floor(Math.random() * 5000) + 2000,
                totalReviews: Math.floor(Math.random() * 200) + 50,
                averageRating: (Math.random() * 2 + 3.5).toFixed(1),
                responseRate: Math.floor(Math.random() * 40) + 60,
                engagementRate: (Math.random() * 20 + 10).toFixed(1),
                customerSatisfaction: Math.floor(Math.random() * 30) + 70
            },
            performance: this.generateTimeSeriesData(dataPoints),
            ratings: this.generateRatingDistribution(),
            sources: this.generateSourceDistribution(),
            metrics: this.generateMetricsData(dataPoints),
            insights: this.generateInsights()
        };
    }

    getDataPointsForRange() {
        const ranges = {
            '7d': 7,
            '30d': 30,
            '90d': 90,
            '1y': 12,
            'custom': 30 // Default for custom
        };
        return ranges[this.currentDateRange] || 30;
    }

    generateTimeSeriesData(points) {
        const labels = this.generateDateLabels(points);
        const datasets = [
            {
                label: 'Profile Views',
                data: Array.from({ length: points }, () => Math.floor(Math.random() * 100) + 50),
                borderColor: '#8a5cf5',
                backgroundColor: 'rgba(138, 92, 245, 0.1)',
                tension: 0.4,
                fill: true
            },
            {
                label: 'New Reviews',
                data: Array.from({ length: points }, () => Math.floor(Math.random() * 20) + 5),
                borderColor: '#10b981',
                backgroundColor: 'rgba(16, 185, 129, 0.1)',
                tension: 0.4,
                fill: true
            },
            {
                label: 'Customer Engagement',
                data: Array.from({ length: points }, () => Math.floor(Math.random() * 30) + 20),
                borderColor: '#f59e0b',
                backgroundColor: 'rgba(245, 158, 11, 0.1)',
                tension: 0.4,
                fill: true
            }
        ];

        return { labels, datasets };
    }

    generateDateLabels(points) {
        const now = new Date();
        return Array.from({ length: points }, (_, i) => {
            const date = new Date(now);
            if (points <= 31) {
                // Daily labels
                date.setDate(date.getDate() - (points - i - 1));
                return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
            } else {
                // Monthly labels
                date.setMonth(date.getMonth() - (points - i - 1));
                return date.toLocaleDateString('en-US', { month: 'short', year: 'numeric' });
            }
        });
    }

    generateRatingDistribution() {
        return {
            labels: ['5 Stars', '4 Stars', '3 Stars', '2 Stars', '1 Star'],
            datasets: [{
                data: [45, 30, 15, 7, 3],
                backgroundColor: [
                    '#10b981',
                    '#34d399',
                    '#f59e0b',
                    '#f97316',
                    '#ef4444'
                ],
                borderWidth: 2,
                borderColor: '#ffffff'
            }]
        };
    }

    generateSourceDistribution() {
        return {
            labels: ['Direct', 'Search', 'Social Media', 'Referral', 'Email'],
            datasets: [{
                data: [35, 25, 20, 15, 5],
                backgroundColor: [
                    '#8a5cf5',
                    '#a78bfa',
                    '#c4b5fd',
                    '#ddd6fe',
                    '#f3f4f6'
                ],
                borderWidth: 2,
                borderColor: '#ffffff'
            }]
        };
    }

    generateMetricsData(points) {
        const metrics = {};
        const metricTypes = ['views', 'ctr', 'time', 'shares', 'responseTime', 'responseRate', 'sentiment', 'satisfaction', 'followers', 'reviewGrowth', 'completion', 'referral'];
        
        metricTypes.forEach(metric => {
            metrics[metric] = {
                current: Math.floor(Math.random() * 1000) + 500,
                trend: (Math.random() * 30 - 15).toFixed(1),
                data: Array.from({ length: points }, () => Math.floor(Math.random() * 100) + 50)
            };
        });

        return metrics;
    }

    generateInsights() {
        return [
            {
                type: 'positive',
                title: 'High Engagement Rate',
                description: 'Your profile engagement is 25% higher than industry average. Consider promoting your most popular services.',
                actions: ['Create featured section', 'Share success stories', 'Optimize service descriptions']
            },
            {
                type: 'warning',
                title: 'Response Time Increased',
                description: 'Average response time has increased by 15% this month. Quick responses improve customer satisfaction.',
                actions: ['Set up response templates', 'Enable notifications', 'Assign team members']
            },
            {
                type: 'info',
                title: 'Seasonal Traffic Pattern',
                description: 'You typically see a 30% increase in traffic during this season. Prepare your profile for increased visibility.',
                actions: ['Update seasonal offers', 'Prepare response templates', 'Review profile completeness']
            }
        ];
    }

    loadSampleData() {
        this.generateSampleData();
    }

    // Chart initialization and management
    initializeCharts() {
        this.initializePerformanceChart();
        this.initializeRatingChart();
        this.initializeSourcesChart();
        this.initializeMiniCharts();
    }

    initializePerformanceChart() {
        const ctx = document.getElementById('performanceChart').getContext('2d');
        
        this.charts.performance = new Chart(ctx, {
            type: 'line',
            data: this.data.performance,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false
                    }
                },
                scales: {
                    x: {
                        grid: {
                            display: false
                        }
                    },
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.1)'
                        }
                    }
                },
                interaction: {
                    mode: 'nearest',
                    axis: 'x',
                    intersect: false
                }
            }
        });

        this.updateChartLegend('performance');
    }

    initializeRatingChart() {
        const ctx = document.getElementById('ratingChart').getContext('2d');
        
        this.charts.ratings = new Chart(ctx, {
            type: 'doughnut',
            data: this.data.ratings,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                },
                cutout: '60%'
            }
        });
    }

    initializeSourcesChart() {
        const ctx = document.getElementById('sourcesChart').getContext('2d');
        
        this.charts.sources = new Chart(ctx, {
            type: 'pie',
            data: this.data.sources,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    }

    initializeMiniCharts() {
        const miniChartIds = [
            'viewsChart', 'ctrChart', 'timeChart', 'sharesChart',
            'responseTimeChart', 'responseRateChart', 'sentimentChart', 'satisfactionChart',
            'followersChart', 'reviewGrowthChart', 'completionChart', 'referralChart'
        ];

        miniChartIds.forEach(chartId => {
            const ctx = document.getElementById(chartId).getContext('2d');
            const metricName = chartId.replace('Chart', '');
            const metricData = this.data.metrics[metricName];

            this.charts[chartId] = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: Array.from({ length: metricData.data.length }, (_, i) => i + 1),
                    datasets: [{
                        data: metricData.data,
                        borderColor: this.getTrendColor(parseFloat(metricData.trend)),
                        backgroundColor: 'transparent',
                        borderWidth: 2,
                        tension: 0.4,
                        pointRadius: 0,
                        pointHoverRadius: 3
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            enabled: false
                        }
                    },
                    scales: {
                        x: {
                            display: false
                        },
                        y: {
                            display: false
                        }
                    },
                    interaction: {
                        intersect: false
                    }
                }
            });
        });
    }

    getTrendColor(trend) {
        if (trend > 0) return '#10b981';
        if (trend < 0) return '#ef4444';
        return '#6b7280';
    }

    updateCharts() {
        if (this.charts.performance) {
            this.charts.performance.data = this.data.performance;
            this.charts.performance.update();
            this.updateChartLegend('performance');
        }

        if (this.charts.ratings) {
            this.charts.ratings.data = this.data.ratings;
            this.charts.ratings.update();
        }

        if (this.charts.sources) {
            this.charts.sources.data = this.data.sources;
            this.charts.sources.update();
        }

        // Update mini charts
        Object.keys(this.data.metrics).forEach(metricName => {
            const chartId = `${metricName}Chart`;
            if (this.charts[chartId]) {
                this.charts[chartId].data.datasets[0].data = this.data.metrics[metricName].data;
                this.charts[chartId].data.datasets[0].borderColor = this.getTrendColor(parseFloat(this.data.metrics[metricName].trend));
                this.charts[chartId].update();
            }
        });
    }

    updateChartLegend(chartName) {
        if (chartName === 'performance' && this.data.performance) {
            const legendContainer = document.getElementById('mainChartLegend');
            if (legendContainer) {
                legendContainer.innerHTML = this.data.performance.datasets.map(dataset => `
                    <div class="legend-item">
                        <span class="legend-color" style="background-color: ${dataset.borderColor}"></span>
                        <span>${dataset.label}</span>
                    </div>
                `).join('');
            }
        }
    }

    // KPI rendering
    renderKPIs() {
        const kpiGrid = document.getElementById('kpiGrid');
        if (!kpiGrid || !this.data.kpis) return;

        const kpis = [
            { key: 'totalViews', label: 'Total Profile Views', format: 'number' },
            { key: 'totalReviews', label: 'New Reviews', format: 'number' },
            { key: 'averageRating', label: 'Average Rating', format: 'rating' },
            { key: 'responseRate', label: 'Response Rate', format: 'percentage' },
            { key: 'engagementRate', label: 'Engagement Rate', format: 'percentage' },
            { key: 'customerSatisfaction', label: 'Customer Satisfaction', format: 'percentage' }
        ];

        kpiGrid.innerHTML = kpis.map(kpi => {
            const value = this.data.kpis[kpi.key];
            const trend = (Math.random() * 20 - 10).toFixed(1);
            const trendClass = trend > 0 ? 'positive' : trend < 0 ? 'negative' : 'neutral';
            const trendIcon = trend > 0 ? 'fa-arrow-up' : trend < 0 ? 'fa-arrow-down' : 'fa-minus';

            let formattedValue = value;
            if (kpi.format === 'percentage') formattedValue = `${value}%`;
            if (kpi.format === 'rating') formattedValue = `${value}/5`;

            return `
                <div class="kpi-card">
                    <div class="kpi-label">${kpi.label}</div>
                    <div class="kpi-value">${formattedValue}</div>
                    <div class="kpi-trend trend-${trendClass}">
                        <i class="fas ${trendIcon}"></i>
                        ${Math.abs(trend)}%
                    </div>
                </div>
            `;
        }).join('');
    }

    // Insights rendering
    renderInsights() {
        const insightsGrid = document.getElementById('insightsGrid');
        if (!insightsGrid || !this.data.insights) return;

        insightsGrid.innerHTML = this.data.insights.map(insight => {
            const iconClass = {
                positive: 'fa-chart-line',
                warning: 'fa-exclamation-triangle',
                info: 'fa-lightbulb'
            }[insight.type];

            return `
                <div class="insight-card">
                    <div class="insight-header">
                        <div class="insight-icon ${insight.type}">
                            <i class="fas ${iconClass}"></i>
                        </div>
                        <div class="insight-content">
                            <h4>${insight.title}</h4>
                            <p>${insight.description}</p>
                        </div>
                    </div>
                    <div class="insight-actions">
                        ${insight.actions.map(action => 
                            `<button class="action-button button-secondary">${action}</button>`
                        ).join('')}
                    </div>
                </div>
            `;
        }).join('');

        // Bind action buttons
        document.querySelectorAll('.insight-actions .action-button').forEach(button => {
            button.addEventListener('click', () => {
                this.handleInsightAction(button.textContent);
            });
        });
    }

    // Tab management
    switchMetricsTab(tabName) {
        // Update tab buttons
        document.querySelectorAll('.tab-button').forEach(button => {
            button.classList.remove('active');
        });
        document.querySelector(`[data-tab="${tabName}"]`).classList.add('active');

        // Update tab panels
        document.querySelectorAll('.tab-panel').forEach(panel => {
            panel.classList.remove('active');
        });
        document.getElementById(`${tabName}Tab`).classList.add('active');
    }

    // Industry comparison
    updateIndustryComparison(industry) {
        // In a real app, this would fetch comparison data for the selected industry
        console.log('Updating industry comparison for:', industry);
        
        // Simulate data update
        const comparisonItems = document.querySelectorAll('.comparison-item');
        comparisonItems.forEach(item => {
            const yourScore = item.querySelector('.your-score');
            const industryAverage = item.querySelector('.industry-average');
            
            // Randomize widths for demo
            yourScore.style.width = `${Math.floor(Math.random() * 30) + 70}%`;
            industryAverage.style.width = `${Math.floor(Math.random() * 30) + 50}%`;
        });
    }

    // Data export
    exportData() {
        const exportFormats = [
            { format: 'csv', label: 'CSV Format', description: 'Comma-separated values for spreadsheets' },
            { format: 'json', label: 'JSON Format', description: 'Structured data for developers' },
            { format: 'pdf', label: 'PDF Report', description: 'Formatted report for sharing' }
        ];

        // Create modal for export options
        const modal = document.createElement('div');
        modal.className = 'export-modal active';
        modal.innerHTML = `
            <div class="export-modal-content">
                <div class="modal-header">
                    <h3>Export Analytics Data</h3>
                    <button class="close-modal">&times;</button>
                </div>
                <div class="export-options">
                    ${exportFormats.map(option => `
                        <label class="export-option">
                            <input type="radio" name="exportFormat" value="${option.format}">
                            <span class="checkmark"></span>
                            <div class="export-option-label">
                                <h4>${option.label}</h4>
                                <p>${option.description}</p>
                            </div>
                        </label>
                    `).join('')}
                </div>
                <div class="modal-actions">
                    <button class="cancel-export button-secondary">Cancel</button>
                    <button class="confirm-export button-primary">Export</button>
                </div>
            </div>
        `;

        document.body.appendChild(modal);

        // Bind modal events
        modal.querySelector('.close-modal').addEventListener('click', () => {
            modal.remove();
        });

        modal.querySelector('.cancel-export').addEventListener('click', () => {
            modal.remove();
        });

        modal.querySelector('.confirm-export').addEventListener('click', () => {
            const selectedFormat = modal.querySelector('input[name="exportFormat"]:checked');
            if (selectedFormat) {
                this.downloadExport(selectedFormat.value);
                modal.remove();
            } else {
                alert('Please select an export format.');
            }
        });

        // Close modal on background click
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                modal.remove();
            }
        });
    }

    downloadExport(format) {
        // Simulate export download
        const blob = new Blob([`Sample ${format.toUpperCase()} export data`], { type: 'text/plain' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `analytics-export-${new Date().toISOString().split('T')[0]}.${format}`;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);

        this.showMessage(`Export completed in ${format.toUpperCase()} format`, 'success');
    }

    // Utility methods
    refreshData() {
        this.loadData();
        this.showMessage('Data refreshed successfully', 'success');
    }

    showLoadingState(show) {
        const mainElement = document.querySelector('.business-analytics-main');
        if (show) {
            mainElement.classList.add('loading');
        } else {
            mainElement.classList.remove('loading');
        }
    }

    showMessage(message, type) {
        // Remove existing messages
        const existingMessages = document.querySelectorAll('.analytics-message');
        existingMessages.forEach(msg => msg.remove());

        // Create message element
        const messageElement = document.createElement('div');
        messageElement.className = `analytics-message ${type}-message`;
        messageElement.innerHTML = `
            <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
            ${message}
        `;

        // Style and position the message
        messageElement.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 1rem 1.5rem;
            background: ${type === 'success' ? '#10b981' : '#ef4444'};
            color: white;
            border-radius: 6px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            z-index: 1000;
            animation: slideIn 0.3s ease;
        `;

        document.body.appendChild(messageElement);

        // Auto-remove after 5 seconds
        setTimeout(() => {
            messageElement.remove();
        }, 5000);
    }

    showError(message) {
        this.showMessage(message, 'error');
    }

    handleResize() {
        // Update charts on window resize
        Object.values(this.charts).forEach(chart => {
            if (chart && typeof chart.resize === 'function') {
                chart.resize();
            }
        });
    }

    handleInsightAction(action) {
        console.log('Insight action triggered:', action);
        this.showMessage(`Action "${action}" would be implemented here`, 'success');
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.businessAnalytics = new BusinessAnalytics();
});

// Export for module usage
if (typeof module !== 'undefined' && module.exports) {
    module.exports = BusinessAnalytics;
}
