/**
 * P.I.M.P - Analytics Dashboard
 * Admin analytics and reporting functionality
 */

class AnalyticsDashboard {
    constructor(container, pimp) {
        this.container = container || document.querySelector('.analytics-dashboard');
        this.pimp = pimp || window.PIMP;
        this.api = window.ApiService;
        this.charts = new Map();
        this.currentPeriod = '7d';
        this.analyticsData = null;
        this.init();
    }

    async init() {
        if (!this.container) {
            console.error('AnalyticsDashboard: Container not found');
            return;
        }

        this.setupEventHandlers();
        await this.loadAnalyticsData(this.currentPeriod);
    }

    setupEventHandlers() {
        // Period selector
        this.container.querySelectorAll('[data-period]').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const period = btn.getAttribute('data-period');
                this.updateTimeRange(period);
            });
        });

        // Export button
        const exportBtn = this.container.querySelector('[data-export-analytics]');
        if (exportBtn) {
            exportBtn.addEventListener('click', () => this.exportAnalyticsReport());
        }

        // Refresh button
        const refreshBtn = this.container.querySelector('[data-refresh-analytics]');
        if (refreshBtn) {
            refreshBtn.addEventListener('click', () => this.loadAnalyticsData(this.currentPeriod));
        }
    }

    async loadAnalyticsData(period) {
        try {
            this.showLoading();

            const response = await this.api.admin.getAnalytics('dashboard', period);

            if (response.success) {
                this.analyticsData = response.data;
                this.renderCharts();
                this.renderMetrics();
            } else {
                this.showError(response.message || 'Failed to load analytics data');
            }
        } catch (error) {
            console.error('Error loading analytics data:', error);
            this.showError('Error loading analytics data');
        } finally {
            this.hideLoading();
        }
    }

    renderCharts() {
        if (!this.analyticsData) return;

        // User growth chart
        const userGrowthChart = this.container.querySelector('[data-chart="user-growth"]');
        if (userGrowthChart && typeof window.ChartComponent !== 'undefined') {
            const chart = new window.ChartComponent(userGrowthChart);
            chart.renderChart(this.analyticsData.user_growth || [], {
                type: 'line',
                label: 'User Growth'
            });
            this.charts.set('user-growth', chart);
        }

        // Business growth chart
        const businessGrowthChart = this.container.querySelector('[data-chart="business-growth"]');
        if (businessGrowthChart && typeof window.ChartComponent !== 'undefined') {
            const chart = new window.ChartComponent(businessGrowthChart);
            chart.renderChart(this.analyticsData.business_growth || [], {
                type: 'bar',
                label: 'Business Growth'
            });
            this.charts.set('business-growth', chart);
        }

        // Review distribution chart
        const reviewChart = this.container.querySelector('[data-chart="reviews"]');
        if (reviewChart && typeof window.ChartComponent !== 'undefined') {
            const chart = new window.ChartComponent(reviewChart);
            chart.renderChart(this.analyticsData.review_distribution || [], {
                type: 'pie',
                label: 'Review Distribution'
            });
            this.charts.set('reviews', chart);
        }

        // Activity chart
        const activityChart = this.container.querySelector('[data-chart="activity"]');
        if (activityChart && typeof window.ChartComponent !== 'undefined') {
            const chart = new window.ChartComponent(activityChart);
            chart.renderChart(this.analyticsData.activity || [], {
                type: 'line',
                label: 'Activity'
            });
            this.charts.set('activity', chart);
        }
    }

    renderMetrics() {
        if (!this.analyticsData) return;

        const metricsContainer = this.container.querySelector('.analytics-metrics, [data-metrics]');
        if (!metricsContainer) return;

        const metrics = this.analyticsData.metrics || {};

        metricsContainer.innerHTML = `
            <div class="metric-card">
                <div class="metric-label">Total Users</div>
                <div class="metric-value">${this.formatNumber(metrics.total_users || 0)}</div>
                <div class="metric-change ${(metrics.users_change || 0) >= 0 ? 'positive' : 'negative'}">
                    ${metrics.users_change >= 0 ? '+' : ''}${metrics.users_change || 0}%
                </div>
            </div>
            <div class="metric-card">
                <div class="metric-label">Total Businesses</div>
                <div class="metric-value">${this.formatNumber(metrics.total_businesses || 0)}</div>
                <div class="metric-change ${(metrics.businesses_change || 0) >= 0 ? 'positive' : 'negative'}">
                    ${metrics.businesses_change >= 0 ? '+' : ''}${metrics.businesses_change || 0}%
                </div>
            </div>
            <div class="metric-card">
                <div class="metric-label">Total Reviews</div>
                <div class="metric-value">${this.formatNumber(metrics.total_reviews || 0)}</div>
                <div class="metric-change ${(metrics.reviews_change || 0) >= 0 ? 'positive' : 'negative'}">
                    ${metrics.reviews_change >= 0 ? '+' : ''}${metrics.reviews_change || 0}%
                </div>
            </div>
            <div class="metric-card">
                <div class="metric-label">Active Sessions</div>
                <div class="metric-value">${this.formatNumber(metrics.active_sessions || 0)}</div>
            </div>
        `;
    }

    async updateTimeRange(range) {
        this.currentPeriod = range;
        
        // Update active button
        this.container.querySelectorAll('[data-period]').forEach(btn => {
            btn.classList.toggle('active', btn.getAttribute('data-period') === range);
        });

        await this.loadAnalyticsData(range);
    }

    setupDataFilters() {
        // Setup additional filters if needed
        const filterContainer = this.container.querySelector('.analytics-filters');
        if (!filterContainer) return;

        // Add filter UI and handlers
    }

    async exportAnalyticsReport() {
        try {
            if (!this.analyticsData) {
                this.pimp?.showNotification('No data to export', 'warning', 2000);
                return;
            }

            const report = {
                period: this.currentPeriod,
                generated_at: new Date().toISOString(),
                data: this.analyticsData
            };

            const blob = new Blob([JSON.stringify(report, null, 2)], { type: 'application/json' });
            const url = URL.createObjectURL(blob);
            const link = document.createElement('a');
            link.href = url;
            link.download = `analytics-report-${this.currentPeriod}-${Date.now()}.json`;
            link.click();
            URL.revokeObjectURL(url);

            this.pimp?.showNotification('Analytics report exported', 'success', 2000);
        } catch (error) {
            console.error('Error exporting analytics:', error);
            this.showError('Failed to export analytics report');
        }
    }

    showLoading() {
        const loading = this.container.querySelector('.loading, [data-loading]');
        if (loading) loading.style.display = 'block';
    }

    hideLoading() {
        const loading = this.container.querySelector('.loading, [data-loading]');
        if (loading) loading.style.display = 'none';
    }

    showError(message) {
        if (this.pimp) {
            this.pimp.showNotification(message, 'error', 5000);
        }
    }

    formatNumber(num) {
        return new Intl.NumberFormat('en-US').format(num);
    }

    destroy() {
        this.charts.forEach(chart => {
            if (chart.destroy) chart.destroy();
        });
        this.charts.clear();
    }
}

// Auto-initialize
document.addEventListener('DOMContentLoaded', () => {
    const container = document.querySelector('.analytics-dashboard');
    if (container && !container.analyticsDashboard) {
        container.analyticsDashboard = new AnalyticsDashboard(container);
    }
});

// Export
if (typeof window !== 'undefined') {
    window.AnalyticsDashboard = AnalyticsDashboard;
}

if (typeof module !== 'undefined' && module.exports) {
    module.exports = AnalyticsDashboard;
}
