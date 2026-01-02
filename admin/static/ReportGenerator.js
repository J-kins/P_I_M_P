/**
 * P.I.M.P - Report Generator
 * Admin report generation and export functionality
 */

class ReportGenerator {
    constructor(container, pimp) {
        this.container = container || document.querySelector('.report-generator');
        this.pimp = pimp || window.PIMP;
        this.api = window.ApiService;
        this.templates = new Map();
        this.currentReport = null;
        this.init();
    }

    async init() {
        if (!this.container) {
            console.error('ReportGenerator: Container not found');
            return;
        }

        this.setupEventHandlers();
        await this.loadReportTemplates();
    }

    setupEventHandlers() {
        // Report type selector
        const reportTypeSelect = this.container.querySelector('[data-report-type]');
        if (reportTypeSelect) {
            reportTypeSelect.addEventListener('change', (e) => {
                this.onReportTypeChange(e.target.value);
            });
        }

        // Generate button
        const generateBtn = this.container.querySelector('[data-generate-report]');
        if (generateBtn) {
            generateBtn.addEventListener('click', () => this.handleGenerateReport());
        }

        // Export button
        const exportBtn = this.container.querySelector('[data-export-report]');
        if (exportBtn) {
            exportBtn.addEventListener('click', () => this.handleExportReport());
        }

        // Schedule button
        const scheduleBtn = this.container.querySelector('[data-schedule-report]');
        if (scheduleBtn) {
            scheduleBtn.addEventListener('click', () => this.handleScheduleReport());
        }
    }

    async loadReportTemplates() {
        // Define available report templates
        const templates = {
            'user-report': {
                name: 'User Report',
                description: 'Detailed user statistics and information',
                fields: ['user_id', 'name', 'email', 'status', 'created_at', 'last_login']
            },
            'business-report': {
                name: 'Business Report',
                description: 'Business listings and statistics',
                fields: ['business_id', 'name', 'type', 'status', 'rating', 'reviews_count']
            },
            'review-report': {
                name: 'Review Report',
                description: 'Review statistics and analysis',
                fields: ['review_id', 'business_id', 'user_id', 'rating', 'created_at', 'status']
            },
            'activity-report': {
                name: 'Activity Report',
                description: 'Platform activity and engagement metrics',
                fields: ['date', 'users', 'businesses', 'reviews', 'complaints']
            },
            'financial-report': {
                name: 'Financial Report',
                description: 'Revenue and financial metrics',
                fields: ['period', 'revenue', 'subscriptions', 'transactions']
            }
        };

        templates.forEach((template, key) => {
            this.templates.set(key, template);
        });

        this.renderTemplateSelector();
    }

    renderTemplateSelector() {
        const selector = this.container.querySelector('.report-templates, [data-templates]');
        if (!selector) return;

        selector.innerHTML = Array.from(this.templates.entries()).map(([key, template]) => `
            <div class="report-template-card" data-template="${key}">
                <h4>${template.name}</h4>
                <p>${template.description}</p>
                <button class="btn btn-primary" data-select-template="${key}">
                    Select Template
                </button>
            </div>
        `).join('');

        // Attach select handlers
        selector.querySelectorAll('[data-select-template]').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const templateKey = btn.getAttribute('data-select-template');
                this.selectTemplate(templateKey);
            });
        });
    }

    selectTemplate(templateKey) {
        const template = this.templates.get(templateKey);
        if (!template) return;

        // Update UI
        this.container.querySelectorAll('.report-template-card').forEach(card => {
            card.classList.toggle('selected', card.getAttribute('data-template') === templateKey);
        });

        // Show parameters form
        this.renderParametersForm(templateKey, template);
    }

    renderParametersForm(templateKey, template) {
        const formContainer = this.container.querySelector('.report-parameters, [data-parameters]');
        if (!formContainer) return;

        formContainer.innerHTML = `
            <h3>Report Parameters</h3>
            <form class="report-params-form" data-template="${templateKey}">
                <div class="form-group">
                    <label>Date Range</label>
                    <div class="date-range-inputs">
                        <input type="date" name="date_from" required>
                        <span>to</span>
                        <input type="date" name="date_to" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Format</label>
                    <select name="format" required>
                        <option value="json">JSON</option>
                        <option value="csv">CSV</option>
                        <option value="pdf">PDF</option>
                        <option value="xlsx">Excel</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Include Fields</label>
                    <div class="fields-checkboxes">
                        ${template.fields.map(field => `
                            <label>
                                <input type="checkbox" name="fields[]" value="${field}" checked>
                                ${this.formatFieldName(field)}
                            </label>
                        `).join('')}
                    </div>
                </div>
                
                <div class="form-group">
                    <label>
                        <input type="checkbox" name="include_charts">
                        Include Charts
                    </label>
                </div>
                
                <button type="submit" class="btn btn-primary">Generate Report</button>
            </form>
        `;

        // Attach form submit handler
        const form = formContainer.querySelector('form');
        form.addEventListener('submit', (e) => {
            e.preventDefault();
            this.handleGenerateReport();
        });
    }

    formatFieldName(field) {
        return field
            .replace(/_/g, ' ')
            .replace(/\b\w/g, l => l.toUpperCase());
    }

    async handleGenerateReport() {
        try {
            const form = this.container.querySelector('.report-params-form');
            if (!form) {
                this.pimp?.showNotification('Please select a report template', 'warning', 2000);
                return;
            }

            const templateKey = form.getAttribute('data-template');
            const formData = new FormData(form);
            const parameters = {
                template: templateKey,
                date_from: formData.get('date_from'),
                date_to: formData.get('date_to'),
                format: formData.get('format'),
                fields: formData.getAll('fields[]'),
                include_charts: formData.get('include_charts') === 'on'
            };

            this.showLoading();

            const report = await this.generateReport(templateKey, parameters);
            this.currentReport = report;

            this.renderReportPreview(report);
            this.pimp?.showNotification('Report generated successfully', 'success', 2000);
        } catch (error) {
            console.error('Error generating report:', error);
            this.showError('Failed to generate report');
        } finally {
            this.hideLoading();
        }
    }

    async generateReport(type, parameters) {
        // Simulate report generation - in real implementation, this would call the API
        // For now, we'll create a mock report structure
        
        const report = {
            id: `report-${Date.now()}`,
            type: type,
            parameters: parameters,
            generated_at: new Date().toISOString(),
            data: this.generateMockReportData(type, parameters)
        };

        return report;
    }

    generateMockReportData(type, parameters) {
        // Generate mock data based on report type
        const data = [];
        const dateFrom = new Date(parameters.date_from);
        const dateTo = new Date(parameters.date_to);
        const days = Math.ceil((dateTo - dateFrom) / (1000 * 60 * 60 * 24));

        for (let i = 0; i < Math.min(days, 30); i++) {
            const date = new Date(dateFrom);
            date.setDate(date.getDate() + i);

            const row = {};
            parameters.fields.forEach(field => {
                row[field] = this.generateMockFieldValue(field, date);
            });
            data.push(row);
        }

        return data;
    }

    generateMockFieldValue(field, date) {
        if (field.includes('date') || field.includes('created_at') || field.includes('last_login')) {
            return date.toISOString().split('T')[0];
        }
        if (field.includes('id')) {
            return Math.floor(Math.random() * 1000) + 1;
        }
        if (field.includes('name')) {
            return `Sample ${field.replace('_', ' ')}`;
        }
        if (field.includes('email')) {
            return 'sample@example.com';
        }
        if (field.includes('rating')) {
            return (Math.random() * 4 + 1).toFixed(1);
        }
        if (field.includes('status')) {
            return ['active', 'inactive', 'pending'][Math.floor(Math.random() * 3)];
        }
        if (field.includes('count') || field.includes('revenue') || field.includes('users') || field.includes('businesses')) {
            return Math.floor(Math.random() * 1000);
        }
        return 'N/A';
    }

    renderReportPreview(report) {
        const previewContainer = this.container.querySelector('.report-preview, [data-preview]');
        if (!previewContainer) return;

        previewContainer.innerHTML = `
            <div class="report-preview-header">
                <h3>Report Preview</h3>
                <div class="report-meta">
                    <span>Generated: ${new Date(report.generated_at).toLocaleString()}</span>
                    <span>Type: ${report.type}</span>
                    <span>Records: ${report.data.length}</span>
                </div>
            </div>
            
            <div class="report-preview-table">
                <table>
                    <thead>
                        <tr>
                            ${report.parameters.fields.map(field => `
                                <th>${this.formatFieldName(field)}</th>
                            `).join('')}
                        </tr>
                    </thead>
                    <tbody>
                        ${report.data.slice(0, 10).map(row => `
                            <tr>
                                ${report.parameters.fields.map(field => `
                                    <td>${row[field] || 'N/A'}</td>
                                `).join('')}
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
                ${report.data.length > 10 ? `<p class="preview-note">Showing first 10 of ${report.data.length} records</p>` : ''}
            </div>
            
            <div class="report-actions">
                <button class="btn btn-primary" data-export-report>
                    Export Report (${report.parameters.format.toUpperCase()})
                </button>
                <button class="btn btn-secondary" data-schedule-report>
                    Schedule Report
                </button>
            </div>
        `;

        // Reattach handlers
        previewContainer.querySelector('[data-export-report]')?.addEventListener('click', () => {
            this.handleExportReport();
        });

        previewContainer.querySelector('[data-schedule-report]')?.addEventListener('click', () => {
            this.handleScheduleReport();
        });
    }

    async handleExportReport() {
        if (!this.currentReport) {
            this.pimp?.showNotification('No report to export', 'warning', 2000);
            return;
        }

        try {
            await this.exportReport(this.currentReport, this.currentReport.parameters.format);
        } catch (error) {
            console.error('Error exporting report:', error);
            this.showError('Failed to export report');
        }
    }

    async exportReport(report, format) {
        switch (format) {
            case 'json':
                this.exportJSON(report);
                break;
            case 'csv':
                this.exportCSV(report);
                break;
            case 'pdf':
                this.exportPDF(report);
                break;
            case 'xlsx':
                this.exportExcel(report);
                break;
            default:
                this.exportJSON(report);
        }

        this.pimp?.showNotification(`Report exported as ${format.toUpperCase()}`, 'success', 2000);
    }

    exportJSON(report) {
        const blob = new Blob([JSON.stringify(report, null, 2)], { type: 'application/json' });
        this.downloadBlob(blob, `report-${report.id}.json`);
    }

    exportCSV(report) {
        const headers = report.parameters.fields.join(',');
        const rows = report.data.map(row =>
            report.parameters.fields.map(field => `"${row[field] || ''}"`).join(',')
        );
        const csv = [headers, ...rows].join('\n');
        const blob = new Blob([csv], { type: 'text/csv' });
        this.downloadBlob(blob, `report-${report.id}.csv`);
    }

    exportPDF(report) {
        // PDF export would require a library like jsPDF
        // For now, we'll show a message
        this.pimp?.showNotification('PDF export requires additional setup', 'info', 3000);
    }

    exportExcel(report) {
        // Excel export would require a library like xlsx
        // For now, we'll export as CSV
        this.exportCSV(report);
    }

    downloadBlob(blob, filename) {
        const url = URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = url;
        link.download = filename;
        link.click();
        URL.revokeObjectURL(url);
    }

    async handleScheduleReport() {
        if (!this.currentReport) {
            this.pimp?.showNotification('No report to schedule', 'warning', 2000);
            return;
        }

        const frequency = prompt('Enter schedule frequency (daily, weekly, monthly):');
        if (!frequency) return;

        try {
            await this.scheduleReport({
                report_config: this.currentReport,
                frequency: frequency
            });

            this.pimp?.showNotification('Report scheduled successfully', 'success', 2000);
        } catch (error) {
            console.error('Error scheduling report:', error);
            this.showError('Failed to schedule report');
        }
    }

    async scheduleReport(reportConfig) {
        // In real implementation, this would call an API to schedule the report
        console.log('Scheduling report:', reportConfig);
        return { success: true };
    }

    customizeReport(format, options) {
        // Customize report format and options
        if (this.currentReport) {
            this.currentReport.parameters.format = format;
            this.currentReport.parameters = { ...this.currentReport.parameters, ...options };
            this.renderReportPreview(this.currentReport);
        }
    }

    onReportTypeChange(type) {
        const template = this.templates.get(type);
        if (template) {
            this.selectTemplate(type);
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
}

// Auto-initialize
document.addEventListener('DOMContentLoaded', () => {
    const container = document.querySelector('.report-generator');
    if (container && !container.reportGenerator) {
        container.reportGenerator = new ReportGenerator(container);
    }
});

// Export
if (typeof window !== 'undefined') {
    window.ReportGenerator = ReportGenerator;
}

if (typeof module !== 'undefined' && module.exports) {
    module.exports = ReportGenerator;
}
