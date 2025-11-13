/**
 * P.I.M.P - Settings Manager
 * Admin system settings management
 */

class SettingsManager {
    constructor(container, pimp) {
        this.container = container || document.querySelector('.settings-manager');
        this.pimp = pimp || window.PIMP;
        this.api = window.ApiService;
        this.settings = new Map();
        this.originalSettings = new Map();
        this.init();
    }

    async init() {
        if (!this.container) {
            console.error('SettingsManager: Container not found');
            return;
        }

        this.setupEventHandlers();
        await this.loadSystemSettings();
    }

    setupEventHandlers() {
        // Save button
        const saveBtn = this.container.querySelector('[data-save-settings]');
        if (saveBtn) {
            saveBtn.addEventListener('click', () => this.saveSettings());
        }

        // Reset button
        const resetBtn = this.container.querySelector('[data-reset-settings]');
        if (resetBtn) {
            resetBtn.addEventListener('click', () => this.resetSettings());
        }

        // Backup button
        const backupBtn = this.container.querySelector('[data-backup-settings]');
        if (backupBtn) {
            backupBtn.addEventListener('click', () => this.backupSettings());
        }

        // Setting change handlers
        this.container.querySelectorAll('[data-setting]').forEach(input => {
            input.addEventListener('change', (e) => {
                const key = input.getAttribute('data-setting');
                const value = this.getInputValue(input);
                this.settings.set(key, value);
                this.markChanged(key);
            });
        });
    }

    async loadSystemSettings() {
        try {
            this.showLoading();

            const response = await this.api.admin.getSettings();

            if (response.success) {
                const settings = response.data.settings || response.data || {};
                
                // Convert to Map
                Object.entries(settings).forEach(([key, value]) => {
                    this.settings.set(key, value);
                    this.originalSettings.set(key, value);
                });

                this.renderSettings();
            } else {
                this.showError(response.message || 'Failed to load settings');
            }
        } catch (error) {
            console.error('Error loading settings:', error);
            this.showError('Error loading settings');
        } finally {
            this.hideLoading();
        }
    }

    renderSettings() {
        const settingsContainer = this.container.querySelector('.settings-list, [data-settings-list]');
        if (!settingsContainer) return;

        // Group settings by category
        const categories = this.groupSettingsByCategory();

        settingsContainer.innerHTML = Object.entries(categories).map(([category, settings]) => `
            <div class="settings-category">
                <h3 class="category-title">${category}</h3>
                <div class="settings-group">
                    ${Object.entries(settings).map(([key, value]) => `
                        <div class="setting-item">
                            <label for="setting-${key}">${this.formatSettingKey(key)}</label>
                            ${this.renderSettingInput(key, value)}
                        </div>
                    `).join('')}
                </div>
            </div>
        `).join('');

        // Reattach handlers
        this.container.querySelectorAll('[data-setting]').forEach(input => {
            input.addEventListener('change', (e) => {
                const key = input.getAttribute('data-setting');
                const value = this.getInputValue(input);
                this.settings.set(key, value);
                this.markChanged(key);
            });
        });
    }

    renderSettingInput(key, value) {
        const type = this.getSettingType(key, value);
        
        switch (type) {
            case 'boolean':
                return `<input type="checkbox" id="setting-${key}" data-setting="${key}" ${value ? 'checked' : ''}>`;
            case 'number':
                return `<input type="number" id="setting-${key}" data-setting="${key}" value="${value}">`;
            case 'textarea':
                return `<textarea id="setting-${key}" data-setting="${key}">${value}</textarea>`;
            default:
                return `<input type="text" id="setting-${key}" data-setting="${key}" value="${value}">`;
        }
    }

    getSettingType(key, value) {
        if (typeof value === 'boolean') return 'boolean';
        if (typeof value === 'number') return 'number';
        if (typeof value === 'string' && value.length > 100) return 'textarea';
        return 'text';
    }

    getInputValue(input) {
        if (input.type === 'checkbox') {
            return input.checked;
        }
        if (input.type === 'number') {
            return parseFloat(input.value) || 0;
        }
        return input.value;
    }

    groupSettingsByCategory() {
        const categories = {};

        this.settings.forEach((value, key) => {
            const category = this.getSettingCategory(key);
            if (!categories[category]) {
                categories[category] = {};
            }
            categories[category][key] = value;
        });

        return categories;
    }

    getSettingCategory(key) {
        if (key.includes('email') || key.includes('mail')) return 'Email';
        if (key.includes('database') || key.includes('db')) return 'Database';
        if (key.includes('security') || key.includes('auth')) return 'Security';
        if (key.includes('payment') || key.includes('billing')) return 'Payment';
        if (key.includes('notification')) return 'Notifications';
        return 'General';
    }

    formatSettingKey(key) {
        return key
            .replace(/_/g, ' ')
            .replace(/\b\w/g, l => l.toUpperCase());
    }

    markChanged(key) {
        const input = this.container.querySelector(`[data-setting="${key}"]`);
        if (input) {
            const originalValue = this.originalSettings.get(key);
            const currentValue = this.settings.get(key);
            
            if (originalValue !== currentValue) {
                input.classList.add('changed');
            } else {
                input.classList.remove('changed');
            }
        }
    }

    async saveSettings() {
        try {
            const changedSettings = {};
            
            this.settings.forEach((value, key) => {
                if (this.originalSettings.get(key) !== value) {
                    changedSettings[key] = value;
                }
            });

            if (Object.keys(changedSettings).length === 0) {
                this.pimp?.showNotification('No changes to save', 'info', 2000);
                return;
            }

            // Validate settings
            if (!this.validateSettings(changedSettings)) {
                return;
            }

            this.showLoading();

            // Save each setting
            const promises = Object.entries(changedSettings).map(([key, value]) =>
                this.api.admin.updateSetting(key, value)
            );

            await Promise.all(promises);

            // Update original settings
            Object.entries(changedSettings).forEach(([key, value]) => {
                this.originalSettings.set(key, value);
            });

            // Remove changed markers
            this.container.querySelectorAll('.changed').forEach(el => {
                el.classList.remove('changed');
            });

            this.pimp?.showNotification('Settings saved successfully', 'success', 2000);
        } catch (error) {
            console.error('Error saving settings:', error);
            this.showError('Failed to save settings');
        } finally {
            this.hideLoading();
        }
    }

    validateSettings(settings) {
        const errors = [];

        // Add validation rules
        Object.entries(settings).forEach(([key, value]) => {
            if (key.includes('email') && value && !this.isValidEmail(value)) {
                errors.push(`${key} is not a valid email address`);
            }
            if (key.includes('url') && value && !this.isValidUrl(value)) {
                errors.push(`${key} is not a valid URL`);
            }
            if (key.includes('port') && value && (value < 1 || value > 65535)) {
                errors.push(`${key} must be between 1 and 65535`);
            }
        });

        if (errors.length > 0) {
            this.showError(errors.join(', '));
            return false;
        }

        return true;
    }

    isValidEmail(email) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    }

    isValidUrl(url) {
        try {
            new URL(url);
            return true;
        } catch {
            return false;
        }
    }

    async resetSettings() {
        if (!confirm('Are you sure you want to reset all settings to defaults?')) {
            return;
        }

        try {
            this.showLoading();

            // Reset to original values
            this.settings.clear();
            this.originalSettings.forEach((value, key) => {
                this.settings.set(key, value);
            });

            this.renderSettings();
            this.pimp?.showNotification('Settings reset to saved values', 'success', 2000);
        } catch (error) {
            console.error('Error resetting settings:', error);
            this.showError('Failed to reset settings');
        } finally {
            this.hideLoading();
        }
    }

    async backupSettings() {
        try {
            const settingsObj = {};
            this.settings.forEach((value, key) => {
                settingsObj[key] = value;
            });

            const backup = {
                settings: settingsObj,
                backed_up_at: new Date().toISOString(),
                version: '1.0'
            };

            const blob = new Blob([JSON.stringify(backup, null, 2)], { type: 'application/json' });
            const url = URL.createObjectURL(blob);
            const link = document.createElement('a');
            link.href = url;
            link.download = `settings-backup-${Date.now()}.json`;
            link.click();
            URL.revokeObjectURL(url);

            this.pimp?.showNotification('Settings backed up', 'success', 2000);
        } catch (error) {
            console.error('Error backing up settings:', error);
            this.showError('Failed to backup settings');
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
    const container = document.querySelector('.settings-manager');
    if (container && !container.settingsManager) {
        container.settingsManager = new SettingsManager(container);
    }
});

// Export
if (typeof window !== 'undefined') {
    window.SettingsManager = SettingsManager;
}

if (typeof module !== 'undefined' && module.exports) {
    module.exports = SettingsManager;
}
