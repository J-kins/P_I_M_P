/**
 * P.I.M.P - System Monitor
 * Admin system monitoring and health checks
 */

class SystemMonitor {
    constructor(container, pimp) {
        this.container = container || document.querySelector('.system-monitor');
        this.pimp = pimp || window.PIMP;
        this.api = window.ApiService;
        this.metrics = new Map();
        this.monitorInterval = null;
        this.alerts = [];
        this.init();
    }

    async init() {
        if (!this.container) {
            console.error('SystemMonitor: Container not found');
            return;
        }

        this.setupEventHandlers();
        await this.loadSystemMetrics();
        this.monitorSystemHealth();
        this.setupAlerts();
    }

    setupEventHandlers() {
        // Refresh button
        const refreshBtn = this.container.querySelector('[data-refresh-metrics]');
        if (refreshBtn) {
            refreshBtn.addEventListener('click', () => this.loadSystemMetrics());
        }

        // Auto-refresh toggle
        const autoRefreshToggle = this.container.querySelector('[data-auto-refresh]');
        if (autoRefreshToggle) {
            autoRefreshToggle.addEventListener('change', (e) => {
                if (e.target.checked) {
                    this.startAutoRefresh();
                } else {
                    this.stopAutoRefresh();
                }
            });
        }
    }

    async loadSystemMetrics() {
        try {
            this.showLoading();

            const response = await this.api.admin.getSystemHealth();

            if (response.success) {
                const health = response.data;
                this.metrics.set('health', health);
                this.renderMetricsDashboard();
            } else {
                this.showError(response.message || 'Failed to load system metrics');
            }
        } catch (error) {
            console.error('Error loading system metrics:', error);
            this.showError('Error loading system metrics');
        } finally {
            this.hideLoading();
        }
    }

    renderMetricsDashboard() {
        const health = this.metrics.get('health');
        if (!health) return;

        const dashboard = this.container.querySelector('.metrics-dashboard, [data-metrics-dashboard]');
        if (!dashboard) return;

        dashboard.innerHTML = `
            <div class="health-status status-${health.status || 'unknown'}">
                <div class="status-indicator"></div>
                <div class="status-text">${(health.status || 'unknown').toUpperCase()}</div>
            </div>
            
            <div class="metrics-grid">
                <div class="metric-card">
                    <div class="metric-label">CPU Usage</div>
                    <div class="metric-value">${health.cpu_usage || 0}%</div>
                    <div class="metric-bar">
                        <div class="metric-bar-fill" style="width: ${health.cpu_usage || 0}%"></div>
                    </div>
                </div>
                
                <div class="metric-card">
                    <div class="metric-label">Memory Usage</div>
                    <div class="metric-value">${health.memory_usage || 0}%</div>
                    <div class="metric-bar">
                        <div class="metric-bar-fill" style="width: ${health.memory_usage || 0}%"></div>
                    </div>
                </div>
                
                <div class="metric-card">
                    <div class="metric-label">Disk Usage</div>
                    <div class="metric-value">${health.disk_usage || 0}%</div>
                    <div class="metric-bar">
                        <div class="metric-bar-fill" style="width: ${health.disk_usage || 0}%"></div>
                    </div>
                </div>
                
                <div class="metric-card">
                    <div class="metric-label">Database Connections</div>
                    <div class="metric-value">${health.db_connections || 0}</div>
                </div>
                
                <div class="metric-card">
                    <div class="metric-label">Active Sessions</div>
                    <div class="metric-value">${health.active_sessions || 0}</div>
                </div>
                
                <div class="metric-card">
                    <div class="metric-label">Uptime</div>
                    <div class="metric-value">${this.formatUptime(health.uptime || 0)}</div>
                </div>
            </div>
            
            ${health.services ? `
                <div class="services-status">
                    <h3>Services Status</h3>
                    <div class="services-list">
                        ${Object.entries(health.services).map(([name, status]) => `
                            <div class="service-item status-${status}">
                                <span class="service-name">${name}</span>
                                <span class="service-status">${status}</span>
                            </div>
                        `).join('')}
                    </div>
                </div>
            ` : ''}
            
            ${health.alerts && health.alerts.length > 0 ? `
                <div class="system-alerts">
                    <h3>Active Alerts</h3>
                    <div class="alerts-list">
                        ${health.alerts.map(alert => `
                            <div class="alert-item alert-${alert.severity}">
                                <i class="fas fa-exclamation-triangle"></i>
                                <span class="alert-message">${alert.message}</span>
                                <span class="alert-time">${this.formatTime(alert.timestamp)}</span>
                            </div>
                        `).join('')}
                    </div>
                </div>
            ` : ''}
        `;
    }

    async monitorSystemHealth() {
        // Monitor every 30 seconds
        this.monitorInterval = setInterval(async () => {
            await this.loadSystemMetrics();
            this.checkAlerts();
        }, 30000);
    }

    checkAlerts() {
        const health = this.metrics.get('health');
        if (!health) return;

        // Check CPU
        if (health.cpu_usage > 90) {
            this.addAlert('high_cpu', 'CPU usage is above 90%', 'warning');
        }

        // Check Memory
        if (health.memory_usage > 90) {
            this.addAlert('high_memory', 'Memory usage is above 90%', 'warning');
        }

        // Check Disk
        if (health.disk_usage > 90) {
            this.addAlert('high_disk', 'Disk usage is above 90%', 'critical');
        }

        // Check Database
        if (health.db_connections > 100) {
            this.addAlert('high_db_connections', 'Database connections are high', 'warning');
        }
    }

    addAlert(id, message, severity) {
        // Check if alert already exists
        if (this.alerts.find(a => a.id === id)) {
            return;
        }

        const alert = {
            id,
            message,
            severity,
            timestamp: new Date().toISOString()
        };

        this.alerts.push(alert);

        // Show notification
        if (this.pimp) {
            this.pimp.showNotification(message, severity === 'critical' ? 'error' : 'warning', 5000);
        }

        // Render alerts
        this.renderAlerts();
    }

    removeAlert(id) {
        this.alerts = this.alerts.filter(a => a.id !== id);
        this.renderAlerts();
    }

    renderAlerts() {
        const alertsContainer = this.container.querySelector('.system-alerts, [data-alerts]');
        if (!alertsContainer) return;

        if (this.alerts.length === 0) {
            alertsContainer.innerHTML = '<div class="no-alerts">No active alerts</div>';
            return;
        }

        alertsContainer.innerHTML = `
            <h3>Active Alerts</h3>
            <div class="alerts-list">
                ${this.alerts.map(alert => `
                    <div class="alert-item alert-${alert.severity}">
                        <i class="fas fa-exclamation-triangle"></i>
                        <span class="alert-message">${alert.message}</span>
                        <span class="alert-time">${this.formatTime(alert.timestamp)}</span>
                        <button class="alert-dismiss" data-alert-id="${alert.id}">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                `).join('')}
            </div>
        `;

        // Attach dismiss handlers
        alertsContainer.querySelectorAll('[data-alert-id]').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const alertId = btn.getAttribute('data-alert-id');
                this.removeAlert(alertId);
            });
        });
    }

    setupAlerts() {
        // Setup alert thresholds and handlers
        // This can be extended with custom alert rules
    }

    async getPerformanceData() {
        try {
            const response = await this.api.admin.getSystemHealth();
            return response.data;
        } catch (error) {
            console.error('Error getting performance data:', error);
            return null;
        }
    }

    startAutoRefresh() {
        if (this.monitorInterval) {
            clearInterval(this.monitorInterval);
        }
        this.monitorSystemHealth();
    }

    stopAutoRefresh() {
        if (this.monitorInterval) {
            clearInterval(this.monitorInterval);
            this.monitorInterval = null;
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

    formatUptime(seconds) {
        const days = Math.floor(seconds / 86400);
        const hours = Math.floor((seconds % 86400) / 3600);
        const minutes = Math.floor((seconds % 3600) / 60);
        
        if (days > 0) return `${days}d ${hours}h`;
        if (hours > 0) return `${hours}h ${minutes}m`;
        return `${minutes}m`;
    }

    formatTime(timestamp) {
        return new Date(timestamp).toLocaleString();
    }

    destroy() {
        this.stopAutoRefresh();
        this.alerts = [];
    }
}

// Auto-initialize
document.addEventListener('DOMContentLoaded', () => {
    const container = document.querySelector('.system-monitor');
    if (container && !container.systemMonitor) {
        container.systemMonitor = new SystemMonitor(container);
    }
});

// Export
if (typeof window !== 'undefined') {
    window.SystemMonitor = SystemMonitor;
}

if (typeof module !== 'undefined' && module.exports) {
    module.exports = SystemMonitor;
}
