/**
 * P.I.M.P - Core Framework
 * Main application framework initialization and utilities
 */

class PIMP {
    constructor() {
        this.config = {
            apiBaseUrl: '/api/v1',
            wsUrl: window.location.origin.replace('http', 'ws'),
            socketPath: '/socket.io',
            maxRetries: 3,
            retryDelay: 1000
        };
        this.socket = null;
        this.authToken = localStorage.getItem('auth_token');
        this.userData = JSON.parse(localStorage.getItem('user_data') || '{}');
        this.eventHandlers = new Map();
        this.components = new Map();
        this.init();
    }

    init() {
        // Initialize framework
        this.setupEventSystem();
        this.initSocket();
        this.setupGlobalErrorHandling();
        this.loadUserPreferences();
        
        // Emit ready event
        this.emitEvent('pimp:ready', { pimp: this });
    }

    setupEventSystem() {
        // Event system is ready
        if (typeof window.EventBus !== 'undefined') {
            this.eventBus = window.EventBus;
        }
    }

    async apiCall(endpoint, options = {}) {
        const url = endpoint.startsWith('http') ? endpoint : `${this.config.apiBaseUrl}${endpoint}`;
        const method = options.method || 'GET';
        const headers = {
            'Content-Type': 'application/json',
            ...options.headers
        };

        // Add auth token
        if (this.authToken) {
            headers['Authorization'] = `Bearer ${this.authToken}`;
        }

        const config = {
            method,
            headers,
            ...options
        };

        // Handle FormData
        if (options.body instanceof FormData) {
            delete config.headers['Content-Type'];
            config.body = options.body;
        } else if (options.body && typeof options.body === 'object') {
            config.body = JSON.stringify(options.body);
        }

        let lastError = null;
        const maxRetries = options.retries || this.config.maxRetries;

        for (let attempt = 0; attempt < maxRetries; attempt++) {
            try {
                const response = await fetch(url, config);
                const data = await response.json();

                if (!response.ok) {
                    // Handle 401 Unauthorized - clear auth
                    if (response.status === 401) {
                        this.logout();
                        throw new Error('Session expired. Please login again.');
                    }

                    throw new Error(data.message || `HTTP error! status: ${response.status}`);
                }

                return data;
            } catch (error) {
                lastError = error;
                
                // Don't retry on client errors (4xx)
                if (error.message.includes('4')) {
                    throw error;
                }

                // Wait before retry
                if (attempt < maxRetries - 1) {
                    await this.sleep(this.config.retryDelay * (attempt + 1));
                }
            }
        }

        throw lastError;
    }

    initSocket() {
        // Initialize WebSocket if available
        if (typeof window.WebSocketService !== 'undefined') {
            this.socket = window.WebSocketService;
            
            // Setup socket event handlers
            this.socket.on('connect', () => {
                this.emitEvent('socket:connected', {});
            });

            this.socket.on('disconnect', () => {
                this.emitEvent('socket:disconnected', {});
            });
        }
    }

    on(event, handler) {
        if (!this.eventHandlers.has(event)) {
            this.eventHandlers.set(event, []);
        }
        this.eventHandlers.get(event).push(handler);

        // Also register with EventBus if available
        if (this.eventBus) {
            this.eventBus.on(event, handler);
        }
    }

    off(event, handler) {
        if (this.eventHandlers.has(event)) {
            const handlers = this.eventHandlers.get(event);
            const index = handlers.indexOf(handler);
            if (index > -1) {
                handlers.splice(index, 1);
            }
        }

        // Also unregister from EventBus if available
        if (this.eventBus) {
            this.eventBus.off(event, handler);
        }
    }

    emitEvent(event, data = {}) {
        // Trigger local handlers
        if (this.eventHandlers.has(event)) {
            this.eventHandlers.get(event).forEach(handler => {
                try {
                    handler(data);
                } catch (error) {
                    console.error(`Error in event handler for ${event}:`, error);
                }
            });
        }

        // Emit via EventBus if available
        if (this.eventBus) {
            this.eventBus.emit(event, data);
        }

        // Dispatch custom event
        const customEvent = new CustomEvent(event, {
            detail: data,
            bubbles: true
        });
        document.dispatchEvent(customEvent);
    }

    async login(credentials) {
        try {
            const response = await this.apiCall('/auth/login', {
                method: 'POST',
                body: credentials
            });

            if (response.success && response.data) {
                this.authToken = response.data.token || response.data.auth_token;
                this.userData = response.data.user || response.data;

                // Store in localStorage
                localStorage.setItem('auth_token', this.authToken);
                localStorage.setItem('user_data', JSON.stringify(this.userData));

                // Reinitialize socket with new token
                if (this.socket) {
                    this.socket.disconnect();
                    this.socket.connect();
                }

                this.emitEvent('pimp:login', { user: this.userData });
                return response;
            }

            throw new Error(response.message || 'Login failed');
        } catch (error) {
            this.emitEvent('pimp:login:error', { error: error.message });
            throw error;
        }
    }

    logout() {
        this.authToken = null;
        this.userData = {};

        // Clear localStorage
        localStorage.removeItem('auth_token');
        localStorage.removeItem('user_data');

        // Disconnect socket
        if (this.socket) {
            this.socket.disconnect();
        }

        this.emitEvent('pimp:logout', {});
    }

    showNotification(message, type = 'info', duration = 5000) {
        // Use NotificationComponent if available
        if (typeof window.NotificationComponent !== 'undefined') {
            window.NotificationComponent.show(message, type, duration);
            return;
        }

        // Fallback: create simple notification
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.textContent = message;
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 1rem 1.5rem;
            background: ${type === 'success' ? '#10b981' : type === 'error' ? '#ef4444' : type === 'warning' ? '#f59e0b' : '#3b82f6'};
            color: white;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            z-index: 10000;
            animation: slideIn 0.3s ease;
        `;

        document.body.appendChild(notification);

        setTimeout(() => {
            notification.style.animation = 'slideOut 0.3s ease';
            setTimeout(() => notification.remove(), 300);
        }, duration);

        this.emitEvent('pimp:notification', { message, type, duration });
    }

    debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    throttle(func, limit) {
        let inThrottle;
        return function executedFunction(...args) {
            if (!inThrottle) {
                func.apply(this, args);
                inThrottle = true;
                setTimeout(() => inThrottle = false, limit);
            }
        };
    }

    sleep(ms) {
        return new Promise(resolve => setTimeout(resolve, ms));
    }

    setupGlobalErrorHandling() {
        // Global error handler
        window.addEventListener('error', (event) => {
            console.error('Global error:', event.error);
            this.emitEvent('pimp:error', { error: event.error, message: event.message });
        });

        // Unhandled promise rejection
        window.addEventListener('unhandledrejection', (event) => {
            console.error('Unhandled promise rejection:', event.reason);
            this.emitEvent('pimp:error', { error: event.reason, type: 'promise' });
        });
    }

    loadUserPreferences() {
        // Load user preferences from localStorage
        const preferences = localStorage.getItem('user_preferences');
        if (preferences) {
            try {
                this.preferences = JSON.parse(preferences);
            } catch (e) {
                this.preferences = {};
            }
        } else {
            this.preferences = {};
        }
    }

    saveUserPreferences(preferences) {
        this.preferences = { ...this.preferences, ...preferences };
        localStorage.setItem('user_preferences', JSON.stringify(this.preferences));
        this.emitEvent('pimp:preferences:updated', { preferences: this.preferences });
    }

    registerComponent(name, component) {
        this.components.set(name, component);
        this.emitEvent('pimp:component:registered', { name, component });
    }

    getComponent(name) {
        return this.components.get(name);
    }

    // Utility methods
    formatDate(date, format = 'YYYY-MM-DD') {
        const d = new Date(date);
        const year = d.getFullYear();
        const month = String(d.getMonth() + 1).padStart(2, '0');
        const day = String(d.getDate()).padStart(2, '0');
        const hours = String(d.getHours()).padStart(2, '0');
        const minutes = String(d.getMinutes()).padStart(2, '0');
        const seconds = String(d.getSeconds()).padStart(2, '0');

        return format
            .replace('YYYY', year)
            .replace('MM', month)
            .replace('DD', day)
            .replace('HH', hours)
            .replace('mm', minutes)
            .replace('ss', seconds);
    }

    formatCurrency(amount, currency = 'USD') {
        return new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: currency
        }).format(amount);
    }

    formatNumber(number, decimals = 0) {
        return new Intl.NumberFormat('en-US', {
            minimumFractionDigits: decimals,
            maximumFractionDigits: decimals
        }).format(number);
    }
}

// Initialize when DOM is ready
if (typeof window !== 'undefined') {
    window.PIMP = new PIMP();
}

// Export for module usage
if (typeof module !== 'undefined' && module.exports) {
    module.exports = PIMP;
}
