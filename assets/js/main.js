
/**
 * Main JavaScript for PHP UI Template System
 */

document.addEventListener('DOMContentLoaded', function() {
    // Theme switcher
    initThemeSwitcher();
    
    // Mobile navigation
    initMobileNav();
    
    // Sidebar navigation
    initSidebarNav();
    
    // Tab navigation
    initTabs();
    
    // Toast notifications
    initToasts();
    
    // Modals/dialogs
    initModals();
    
    // Expandable mobile navigation items
    initMobileNavExpanders();
});

/**
 * Initialize theme switcher
 */
function initThemeSwitcher() {
    const themeButtons = document.querySelectorAll('.theme-option');
    
    if (themeButtons.length > 0) {
        themeButtons.forEach(button => {
            button.addEventListener('click', function() {
                const theme = this.dataset.theme;
                document.documentElement.setAttribute('data-theme', theme);
                
                // Update active state
                themeButtons.forEach(btn => btn.classList.remove('active'));
                this.classList.add('active');
                
                // Store preference in session via AJAX
                if (window.jQuery) {
                    jQuery.post('/admin/ajax/updateTheme.php', { theme }, function(data) {
                        console.log('Theme updated:', data);
                    });
                } else {
                    // Fallback to fetch API if jQuery is not available
                    fetch('/admin/ajax/updateTheme.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: 'theme=' + theme
                    }).then(response => response.json())
                      .then(data => console.log('Theme updated:', data))
                      .catch(error => console.error('Error updating theme:', error));
                }
            });
        });
    }
}

/**
 * Initialize mobile navigation
 */
function initMobileNav() {
    const mobileNavContainers = document.querySelectorAll('.mobile-nav-container');
    
    mobileNavContainers.forEach(container => {
        const toggle = container.querySelector('.mobile-nav-toggle');
        const close = container.querySelector('.mobile-nav-close');
        const backdrop = container.querySelector('.mobile-nav-backdrop');
        
        if (toggle && backdrop) {
            toggle.addEventListener('click', function() {
                container.classList.add('active');
                toggle.setAttribute('aria-expanded', 'true');
                document.body.style.overflow = 'hidden'; // Prevent scrolling
            });
            
            if (close) {
                close.addEventListener('click', function() {
                    container.classList.remove('active');
                    toggle.setAttribute('aria-expanded', 'false');
                    document.body.style.overflow = '';
                });
            }
            
            backdrop.addEventListener('click', function() {
                container.classList.remove('active');
                toggle.setAttribute('aria-expanded', 'false');
                document.body.style.overflow = '';
            });
        }
    });
}

/**
 * Initialize mobile nav expanders for submenus
 */
function initMobileNavExpanders() {
    const expanders = document.querySelectorAll('.mobile-nav-expander');
    
    expanders.forEach(expander => {
        expander.addEventListener('click', function() {
            const parent = this.closest('.mobile-nav-item');
            const submenu = parent.querySelector('.mobile-subnav-list');
            
            if (submenu) {
                this.classList.toggle('active');
                submenu.classList.toggle('active');
                
                // Set appropriate aria attributes
                const expanded = submenu.classList.contains('active');
                this.setAttribute('aria-expanded', expanded ? 'true' : 'false');
            }
        });
    });
}

/**
 * Initialize sidebar navigation
 */
function initSidebarNav() {
    // Handle sidebar toggle
    const sidebarToggles = document.querySelectorAll('.sidebar-toggle');
    
    sidebarToggles.forEach(toggle => {
        toggle.addEventListener('click', function() {
            const sidebar = this.closest('.sidebar-nav');
            if (sidebar) {
                sidebar.classList.toggle('collapsed');
                
                // Store sidebar state in localStorage
                const isCollapsed = sidebar.classList.contains('collapsed');
                localStorage.setItem('sidebar-collapsed', isCollapsed);
            }
        });
    });
    
    // Handle sidebar submenu expanders
    const sidebarExpanders = document.querySelectorAll('.sidebar-expander');
    
    sidebarExpanders.forEach(expander => {
        expander.addEventListener('click', function() {
            const parent = this.closest('.sidebar-item');
            parent.classList.toggle('expanded');
            
            // Set appropriate aria attributes
            const expanded = parent.classList.contains('expanded');
            this.setAttribute('aria-expanded', expanded ? 'true' : 'false');
        });
    });
    
    // Restore sidebar state
    const sidebar = document.querySelector('.sidebar-nav');
    if (sidebar) {
        const isCollapsed = localStorage.getItem('sidebar-collapsed') === 'true';
        if (isCollapsed) {
            sidebar.classList.add('collapsed');
        }
    }
}

/**
 * Initialize tabbed interface
 */
function initTabs() {
    const tabContainers = document.querySelectorAll('.tabs');
    
    tabContainers.forEach(container => {
        const tabs = container.querySelectorAll('.tab-button');
        
        tabs.forEach(tab => {
            tab.addEventListener('click', function() {
                const tabId = this.dataset.tab;
                
                // Update active tab
                tabs.forEach(t => {
                    t.classList.remove('active');
                    t.setAttribute('aria-selected', 'false');
                });
                this.classList.add('active');
                this.setAttribute('aria-selected', 'true');
                
                // Show active panel
                const panels = container.querySelectorAll('.tab-panel');
                panels.forEach(panel => {
                    panel.classList.remove('active');
                });
                
                const activePanel = container.querySelector(`.tab-panel[data-panel="${tabId}"]`);
                if (activePanel) {
                    activePanel.classList.add('active');
                }
            });
        });
    });
}

/**
 * Initialize toast notifications
 */
function initToasts() {
    // Close button functionality for toasts
    document.addEventListener('click', function(e) {
        if (e.target && e.target.classList.contains('toast-close')) {
            const toast = e.target.closest('.toast');
            if (toast) {
                toast.style.opacity = '0';
                setTimeout(() => {
                    toast.remove();
                }, 300);
            }
        }
    });
    
    // Auto-hide toasts after duration
    const autoHideToasts = document.querySelectorAll('.toast[data-auto-hide]');
    autoHideToasts.forEach(toast => {
        const duration = parseInt(toast.dataset.autoHide, 10) || 5000;
        
        setTimeout(() => {
            toast.style.opacity = '0';
            setTimeout(() => {
                toast.remove();
            }, 300);
        }, duration);
    });
}

/**
 * Initialize modal/dialog functionality
 */
function initModals() {
    // Open modal triggers
    const modalTriggers = document.querySelectorAll('[data-modal-target]');
    
    modalTriggers.forEach(trigger => {
        trigger.addEventListener('click', function(e) {
            e.preventDefault();
            const targetId = this.dataset.modalTarget;
            const modal = document.getElementById(targetId);
            
            if (modal) {
                const backdrop = modal.querySelector('.modal-backdrop');
                if (backdrop) {
                    backdrop.classList.add('show');
                    document.body.style.overflow = 'hidden'; // Prevent scrolling
                }
            }
        });
    });
    
    // Close modal buttons
    const closeButtons = document.querySelectorAll('.modal-close, [data-modal-close]');
    
    closeButtons.forEach(button => {
        button.addEventListener('click', function() {
            const modal = this.closest('.modal-backdrop');
            if (modal) {
                modal.classList.remove('show');
                document.body.style.overflow = ''; // Restore scrolling
            }
        });
    });
    
    // Close on backdrop click
    const modalBackdrops = document.querySelectorAll('.modal-backdrop');
    
    modalBackdrops.forEach(backdrop => {
        backdrop.addEventListener('click', function(e) {
            if (e.target === this) {
                this.classList.remove('show');
                document.body.style.overflow = ''; // Restore scrolling
            }
        });
    });
    
    // Handle Escape key to close modal
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const activeModal = document.querySelector('.modal-backdrop.show');
            if (activeModal) {
                activeModal.classList.remove('show');
                document.body.style.overflow = ''; // Restore scrolling
            }
        }
    });
}

/**
 * Show a toast notification
 * 
 * @param {Object} options Toast options
 * @param {string} options.title Title text
 * @param {string} options.message Message text
 * @param {string} options.type Type (success|error|warning|info)
 * @param {string} options.position Position (top-right|top-left|bottom-right|bottom-left)
 * @param {number} options.duration Auto-hide duration in ms (0 to disable)
 */
function showToast(options) {
    const defaults = {
        title: '',
        message: '',
        type: 'info',
        position: 'top-right',
        duration: 5000
    };
    
    const settings = Object.assign({}, defaults, options);
    
    // Get or create toast container
    let container = document.querySelector(`.toast-container.${settings.position}`);
    
    if (!container) {
        container = document.createElement('div');
        container.className = `toast-container ${settings.position}`;
        document.body.appendChild(container);
    }
    
    // Create toast element
    const toast = document.createElement('div');
    toast.className = `toast ${settings.type}`;
    
    if (settings.duration > 0) {
        toast.dataset.autoHide = settings.duration;
    }
    
    // Toast icon based on type
    let iconSvg = '';
    switch (settings.type) {
        case 'success':
            iconSvg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>';
            break;
        case 'error':
            iconSvg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>';
            break;
        case 'warning':
            iconSvg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>';
            break;
        case 'info':
            iconSvg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>';
            break;
    }
    
    // Toast content
    toast.innerHTML = `
        <div class="toast-icon">${iconSvg}</div>
        <div class="toast-content">
            ${settings.title ? `<div class="toast-title">${settings.title}</div>` : ''}
            ${settings.message ? `<div class="toast-message">${settings.message}</div>` : ''}
        </div>
        <button class="toast-close" aria-label="Close">&times;</button>
        ${settings.duration > 0 ? '<div class="toast-progress"></div>' : ''}
    `;
    
    // Add toast to container
    container.appendChild(toast);
    
    // Trigger reflow to enable transitions
    void toast.offsetWidth;
    toast.classList.add('show');
    
    // Auto-hide toast
    if (settings.duration > 0) {
        setTimeout(() => {
            toast.style.opacity = '0';
            setTimeout(() => {
                toast.remove();
                
                // Remove container if empty
                if (container.children.length === 0) {
                    container.remove();
                }
            }, 300);
        }, settings.duration);
    }
    
    // Set progress animation duration
    if (settings.duration > 0) {
        const progressBar = toast.querySelector('.toast-progress::after');
        if (progressBar) {
            progressBar.style.animationDuration = `${settings.duration}ms`;
        }
    }
    
    return toast;
}
