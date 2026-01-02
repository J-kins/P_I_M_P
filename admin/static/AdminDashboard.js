/**
 * P.I.M.P - Admin Dashboard
 * Main admin dashboard functionality
 */

class AdminDashboard {
    constructor(container, pimp) {
        this.container = container || document.querySelector('.admin-dashboard');
        this.pimp = pimp || window.PIMP;
        this.api = window.ApiService;
        this.widgets = new Map();
        this.stats = null;
        this.refreshInterval = null;
        this.init();
    }

    async init() {
        if (!this.container) {
            console.error('AdminDashboard: Container not found');
            return;
        }

        this.setupEventHandlers();
        await this.loadDashboardData();
        this.setupRealTimeUpdates();
    }

    setupEventHandlers() {
        // Refresh button
        const refreshBtn = this.container.querySelector('[data-refresh-dashboard]');
        if (refreshBtn) {
            refreshBtn.addEventListener('click', () => this.refreshDashboard());
        }

        // Export button
        const exportBtn = this.container.querySelector('[data-export-dashboard]');
        if (exportBtn) {
            exportBtn.addEventListener('click', () => this.exportDashboardData());
        }

        // Widget toggle buttons
        this.container.querySelectorAll('[data-widget-toggle]').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const widgetId = btn.getAttribute('data-widget-toggle');
                this.toggleWidget(widgetId);
            });
        });
    }

    async loadDashboardData() {
        try {
            this.showLoading();

            const response = await this.api.admin.getDashboardStats();
            
            if (response.success) {
                this.stats = response.data;
                this.renderStatsWidgets();
                this.renderCharts();
                this.renderRecentActivity();
            } else {
                this.showError(response.message || 'Failed to load dashboard data');
            }
        } catch (error) {
            console.error('Error loading dashboard data:', error);
            this.showError('Error loading dashboard data');
        } finally {
            this.hideLoading();
        }
    }

    renderStatsWidgets() {
        if (!this.stats) return;

        const widgetsContainer = this.container.querySelector('.stats-widgets, .dashboard-stats');
        if (!widgetsContainer) return;

        const stats = this.stats.statistics || this.stats;

        const widgets = [
            {
                id: 'total-users',
                title: 'Total Users',
                value: stats.total_users || 0,
                icon: 'fa-users',
                color: 'blue',
                change: stats.users_change || 0
            },
            {
                id: 'total-businesses',
                title: 'Total Businesses',
                value: stats.total_businesses || 0,
                icon: 'fa-building',
                color: 'green',
                change: stats.businesses_change || 0
            },
            {
                id: 'total-reviews',
                title: 'Total Reviews',
                value: stats.total_reviews || 0,
                icon: 'fa-star',
                color: 'yellow',
                change: stats.reviews_change || 0
            },
            {
                id: 'pending-moderation',
                title: 'Pending Moderation',
                value: stats.pending_moderation || 0,
                icon: 'fa-clock',
                color: 'red',
                change: stats.moderation_change || 0
            }
        ];

        widgetsContainer.innerHTML = widgets.map(widget => `
            <div class="stat-widget stat-widget-${widget.color}" data-widget="${widget.id}">
                <div class="stat-widget-icon">
                    <i class="fas ${widget.icon}"></i>
                </div>
                <div class="stat-widget-content">
                    <div class="stat-widget-title">${widget.title}</div>
                    <div class="stat-widget-value">${this.formatNumber(widget.value)}</div>
                    ${widget.change !== 0 ? `
                        <div class="stat-widget-change ${widget.change > 0 ? 'positive' : 'negative'}">
                            <i class="fas fa-arrow-${widget.change > 0 ? 'up' : 'down'}"></i>
                            ${Math.abs(widget.change)}%
                        </div>
                    ` : ''}
                </div>
            </div>
        `).join('');
    }

    renderCharts() {
        if (!this.stats) return;

        // Render activity chart
        const activityChart = this.container.querySelector('[data-chart="activity"]');
        if (activityChart && typeof window.ChartComponent !== 'undefined') {
            const chartData = this.stats.activity_data || [];
            const chart = new window.ChartComponent(activityChart);
            chart.renderChart(chartData, {
                type: 'line',
                label: 'Activity'
            });
            this.widgets.set('activity-chart', chart);
        }

        // Render user growth chart
        const userGrowthChart = this.container.querySelector('[data-chart="user-growth"]');
        if (userGrowthChart && typeof window.ChartComponent !== 'undefined') {
            const chartData = this.stats.user_growth || [];
            const chart = new window.ChartComponent(userGrowthChart);
            chart.renderChart(chartData, {
                type: 'bar',
                label: 'User Growth'
            });
            this.widgets.set('user-growth-chart', chart);
        }
    }

    renderRecentActivity() {
        if (!this.stats) return;

        const activityContainer = this.container.querySelector('.recent-activity, [data-activity-list]');
        if (!activityContainer) return;

        const activities = this.stats.recent_activities || [];

        activityContainer.innerHTML = activities.map(activity => `
            <div class="activity-item">
                <div class="activity-icon">
                    <i class="fas ${this.getActivityIcon(activity.type)}"></i>
                </div>
                <div class="activity-content">
                    <div class="activity-text">${activity.description || activity.text}</div>
                    <div class="activity-time">${this.formatTime(activity.created_at || activity.timestamp)}</div>
                </div>
            </div>
        `).join('');
    }

    getActivityIcon(type) {
        const icons = {
            'user_registered': 'fa-user-plus',
            'business_created': 'fa-building',
            'review_submitted': 'fa-star',
            'complaint_created': 'fa-exclamation-triangle',
            'moderation': 'fa-shield-alt'
        };
        return icons[type] || 'fa-circle';
    }

    setupRealTimeUpdates() {
        // Setup WebSocket for real-time updates
        if (this.pimp?.socket) {
            this.pimp.socket.on('admin:stats:update', (data) => {
                this.stats = { ...this.stats, ...data };
                this.renderStatsWidgets();
            });

            this.pimp.socket.on('admin:activity:new', (activity) => {
                this.addActivityItem(activity);
            });
        }

        // Auto-refresh every 5 minutes
        this.refreshInterval = setInterval(() => {
            this.loadDashboardData();
        }, 5 * 60 * 1000);
    }

    addActivityItem(activity) {
        const activityContainer = this.container.querySelector('.recent-activity, [data-activity-list]');
        if (!activityContainer) return;

        const activityItem = document.createElement('div');
        activityItem.className = 'activity-item';
        activityItem.innerHTML = `
            <div class="activity-icon">
                <i class="fas ${this.getActivityIcon(activity.type)}"></i>
            </div>
            <div class="activity-content">
                <div class="activity-text">${activity.description || activity.text}</div>
                <div class="activity-time">${this.formatTime(activity.created_at || activity.timestamp)}</div>
            </div>
        `;

        activityContainer.insertBefore(activityItem, activityContainer.firstChild);

        // Remove old items if more than 20
        const items = activityContainer.querySelectorAll('.activity-item');
        if (items.length > 20) {
            items[items.length - 1].remove();
        }
    }

    toggleWidget(widgetId) {
        const widget = this.container.querySelector(`[data-widget="${widgetId}"]`);
        if (widget) {
            widget.classList.toggle('collapsed');
        }
    }

    async refreshDashboard() {
        await this.loadDashboardData();
        if (this.pimp) {
            this.pimp.showNotification('Dashboard refreshed', 'success', 2000);
        }
    }

    async exportDashboardData() {
        try {
            const data = {
                stats: this.stats,
                exported_at: new Date().toISOString()
            };

            const blob = new Blob([JSON.stringify(data, null, 2)], { type: 'application/json' });
            const url = URL.createObjectURL(blob);
            const link = document.createElement('a');
            link.href = url;
            link.download = `dashboard-export-${Date.now()}.json`;
            link.click();
            URL.revokeObjectURL(url);

            if (this.pimp) {
                this.pimp.showNotification('Dashboard data exported', 'success', 2000);
            }
        } catch (error) {
            console.error('Error exporting dashboard:', error);
            if (this.pimp) {
                this.pimp.showNotification('Failed to export dashboard data', 'error', 3000);
            }
        }
    }

    showLoading() {
        const loading = this.container.querySelector('.dashboard-loading');
        if (loading) {
            loading.style.display = 'block';
        }
    }

    hideLoading() {
        const loading = this.container.querySelector('.dashboard-loading');
        if (loading) {
            loading.style.display = 'none';
        }
    }

    showError(message) {
        if (this.pimp) {
            this.pimp.showNotification(message, 'error', 5000);
        }
    }

    formatNumber(num) {
        return new Intl.NumberFormat('en-US').format(num);
    }

    formatTime(timestamp) {
        const date = new Date(timestamp);
        const now = new Date();
        const diff = now - date;
        const minutes = Math.floor(diff / 60000);
        const hours = Math.floor(diff / 3600000);
        const days = Math.floor(diff / 86400000);

        if (minutes < 1) return 'Just now';
        if (minutes < 60) return `${minutes}m ago`;
        if (hours < 24) return `${hours}h ago`;
        if (days < 7) return `${days}d ago`;
        return date.toLocaleDateString();
    }

    destroy() {
        if (this.refreshInterval) {
            clearInterval(this.refreshInterval);
        }
        this.widgets.forEach(widget => {
            if (widget.destroy) widget.destroy();
        });
        this.widgets.clear();
    }
}

// Auto-initialize
document.addEventListener('DOMContentLoaded', () => {
    const container = document.querySelector('.admin-dashboard');
    if (container && !container.adminDashboard) {
        container.adminDashboard = new AdminDashboard(container);
    }
});

// Export
if (typeof window !== 'undefined') {
    window.AdminDashboard = AdminDashboard;
}

if (typeof module !== 'undefined' && module.exports) {
    module.exports = AdminDashboard;
}
