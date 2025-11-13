/**
 * P.I.M.P - Headers JavaScript
 * Handles header navigation, mobile menus, dropdowns, and interactive elements
 */

class PIMPHeaders {
    constructor() {
        this.isMobileMenuOpen = false;
        this.isUserMenuOpen = false;
        this.isNotificationMenuOpen = false;
        this.scrollThreshold = 100;
        this.header = null;
        this.initialize();
    }

    initialize() {
        this.bindEventListeners();
        this.initializeMobileMenu();
        this.initializeDropdowns();
        this.initializeSearch();
        this.handleScrollEffects();
        this.initializeAdminHeader();
        this.initializeBusinessHeader();
        this.setupTouchGestures();
        this.setupStickyHeader();
        this.handleInitialViewport();
    }

    handleInitialViewport() {
        // Set initial viewport classes
        this.handleResize();
    }

    setupStickyHeader() {
        const headers = document.querySelectorAll('header.sticky, .navbar.sticky, .business-main-header.sticky');
        
        headers.forEach(header => {
            let lastScrollTop = 0;
            const scrollThreshold = 100;

            window.addEventListener('scroll', () => {
                const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
                
                if (scrollTop > scrollThreshold) {
                    header.classList.add('sticky-active');
                    
                    // Hide/show on scroll direction (mobile only)
                    if (window.innerWidth < 768) {
                        if (scrollTop > lastScrollTop && scrollTop > 200) {
                            header.classList.add('header-hidden');
                        } else {
                            header.classList.remove('header-hidden');
                        }
                    }
                } else {
                    header.classList.remove('sticky-active', 'header-hidden');
                }
                
                lastScrollTop = scrollTop;
            }, { passive: true });
        });
    }

    // Main event listeners
    bindEventListeners() {
        // Window resize handler
        window.addEventListener('resize', () => {
            this.handleResize();
        });

        // Click outside handler for dropdowns
        document.addEventListener('click', (e) => {
            this.handleClickOutside(e);
        });

        // Escape key handler
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                this.closeAllDropdowns();
            }
        });

        // Scroll handler
        window.addEventListener('scroll', () => {
            this.handleScroll();
        });
    }

    // Mobile menu functionality
    initializeMobileMenu() {
        const mobileToggles = document.querySelectorAll(
            '.header-mobile-toggle, .navbar-toggle, .business-mobile-toggle, .sidebar-toggle'
        );

        mobileToggles.forEach(toggle => {
            toggle.addEventListener('click', (e) => {
                e.stopPropagation();
                this.toggleMobileMenu(toggle);
            });
        });

        // Close mobile menu when clicking on links
        document.querySelectorAll('.nav-link, .navbar-nav .nav-link, .business-main-nav-link').forEach(link => {
            link.addEventListener('click', () => {
                if (window.innerWidth < 768) {
                    this.closeMobileMenu();
                }
            });
        });
    }

    toggleMobileMenu(toggle) {
        const header = toggle.closest('header, .navbar, .business-main-header');
        
        if (!header) return;

        // Determine which type of header we're dealing with
        if (header.classList.contains('business-main-header')) {
            this.toggleBusinessMobileMenu(header);
        } else if (header.classList.contains('navbar')) {
            this.toggleNavbarMobileMenu(header);
        } else if (header.classList.contains('admin-header')) {
            this.toggleAdminSidebar();
        } else {
            this.toggleStandardMobileMenu(header);
        }
    }

    toggleBusinessMobileMenu(header) {
        const nav = header.querySelector('.business-main-nav');
        const toggle = header.querySelector('.business-mobile-toggle');
        
        if (!nav || !toggle) return;

        this.isMobileMenuOpen = !this.isMobileMenuOpen;
        
        if (this.isMobileMenuOpen) {
            nav.classList.add('mobile-open');
            toggle.classList.add('active');
            toggle.setAttribute('aria-expanded', 'true');
            toggle.innerHTML = '<i class="fas fa-times"></i>';
            document.body.style.overflow = 'hidden';
        } else {
            nav.classList.remove('mobile-open');
            toggle.classList.remove('active');
            toggle.setAttribute('aria-expanded', 'false');
            toggle.innerHTML = '<i class="fas fa-bars"></i>';
            document.body.style.overflow = '';
        }
    }

    toggleNavbarMobileMenu(navbar) {
        const collapse = navbar.querySelector('.navbar-collapse');
        const toggle = navbar.querySelector('.navbar-toggle');
        
        if (!collapse || !toggle) return;

        this.isMobileMenuOpen = !this.isMobileMenuOpen;
        
        if (this.isMobileMenuOpen) {
            collapse.classList.add('show');
            toggle.classList.add('active');
            toggle.setAttribute('aria-expanded', 'true');
            toggle.innerHTML = '<i class="fas fa-times"></i>';
        } else {
            collapse.classList.remove('show');
            toggle.classList.remove('active');
            toggle.setAttribute('aria-expanded', 'false');
            toggle.innerHTML = '<i class="fas fa-bars"></i>';
        }
    }

    toggleAdminSidebar() {
        const sidebar = document.querySelector('.admin-sidebar');
        const toggle = document.querySelector('.sidebar-toggle');
        
        if (!sidebar || !toggle) return;

        const isOpen = sidebar.classList.contains('open');
        
        if (isOpen) {
            sidebar.classList.remove('open');
            toggle.classList.remove('active');
            toggle.setAttribute('aria-expanded', 'false');
        } else {
            sidebar.classList.add('open');
            toggle.classList.add('active');
            toggle.setAttribute('aria-expanded', 'true');
        }
    }

    toggleStandardMobileMenu(header) {
        const nav = header.querySelector('.header-nav');
        const toggle = header.querySelector('.header-mobile-toggle');
        
        if (!nav || !toggle) return;

        this.isMobileMenuOpen = !this.isMobileMenuOpen;
        
        if (this.isMobileMenuOpen) {
            nav.classList.add('mobile-open');
            toggle.classList.add('active');
            toggle.setAttribute('aria-expanded', 'true');
            toggle.innerHTML = '<i class="fas fa-times"></i>';
        } else {
            nav.classList.remove('mobile-open');
            toggle.classList.remove('active');
            toggle.setAttribute('aria-expanded', 'false');
            toggle.innerHTML = '<i class="fas fa-bars"></i>';
        }
    }

    closeMobileMenu() {
        this.isMobileMenuOpen = false;
        
        // Close all mobile menus
        document.querySelectorAll('.header-nav, .navbar-collapse, .business-main-nav').forEach(nav => {
            nav.classList.remove('mobile-open', 'show');
        });
        
        // Reset all toggle buttons
        document.querySelectorAll('.header-mobile-toggle, .navbar-toggle, .business-mobile-toggle').forEach(toggle => {
            toggle.classList.remove('active');
            toggle.setAttribute('aria-expanded', 'false');
            toggle.innerHTML = '<i class="fas fa-bars"></i>';
        });
        
        document.body.style.overflow = '';
    }

    // Dropdown functionality
    initializeDropdowns() {
        // Business header dropdowns
        document.querySelectorAll('.business-main-nav-item').forEach(item => {
            const link = item.querySelector('.business-main-nav-link');
            const dropdown = item.querySelector('.business-dropdown-menu');
            
            if (dropdown) {
                // Desktop hover
                item.addEventListener('mouseenter', () => {
                    if (window.innerWidth >= 768) {
                        this.showDropdown(item, dropdown);
                    }
                });
                
                item.addEventListener('mouseleave', () => {
                    if (window.innerWidth >= 768) {
                        this.hideDropdown(item, dropdown);
                    }
                });
                
                // Mobile click
                link.addEventListener('click', (e) => {
                    if (window.innerWidth < 768) {
                        e.preventDefault();
                        this.toggleMobileDropdown(item, dropdown);
                    }
                });
            }
        });

        // Admin header dropdowns
        this.initializeAdminDropdowns();
    }

    showDropdown(parent, dropdown) {
        // Close other dropdowns first
        this.closeAllDropdowns();
        
        parent.classList.add('dropdown-open');
        dropdown.classList.add('show');
        dropdown.setAttribute('aria-hidden', 'false');
    }

    hideDropdown(parent, dropdown) {
        parent.classList.remove('dropdown-open');
        dropdown.classList.remove('show');
        dropdown.setAttribute('aria-hidden', 'true');
    }

    toggleMobileDropdown(parent, dropdown) {
        const isOpen = dropdown.classList.contains('show');
        
        if (isOpen) {
            this.hideDropdown(parent, dropdown);
        } else {
            // Close other dropdowns
            document.querySelectorAll('.business-dropdown-menu.show').forEach(otherDropdown => {
                if (otherDropdown !== dropdown) {
                    otherDropdown.classList.remove('show');
                    otherDropdown.closest('.business-main-nav-item').classList.remove('dropdown-open');
                }
            });
            
            this.showDropdown(parent, dropdown);
        }
    }

    // Admin header functionality
    initializeAdminHeader() {
        this.initializeAdminDropdowns();
        this.initializeAdminSearch();
    }

    initializeAdminDropdowns() {
        // Notification dropdown
        const notificationBtn = document.getElementById('notificationBtn');
        const notificationDropdown = document.getElementById('notificationDropdown');
        
        if (notificationBtn && notificationDropdown) {
            notificationBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                this.toggleNotificationDropdown();
            });
        }

        // User menu dropdown
        const userMenuBtn = document.getElementById('userMenuBtn');
        const userDropdown = document.getElementById('userDropdown');
        
        if (userMenuBtn && userDropdown) {
            userMenuBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                this.toggleUserDropdown();
            });
        }
    }

    toggleNotificationDropdown() {
        const dropdown = document.getElementById('notificationDropdown');
        if (!dropdown) return;

        this.isNotificationMenuOpen = !this.isNotificationMenuOpen;
        
        if (this.isNotificationMenuOpen) {
            dropdown.classList.add('show');
            this.closeUserDropdown();
        } else {
            dropdown.classList.remove('show');
        }
    }

    toggleUserDropdown() {
        const dropdown = document.getElementById('userDropdown');
        if (!dropdown) return;

        this.isUserMenuOpen = !this.isUserMenuOpen;
        
        if (this.isUserMenuOpen) {
            dropdown.classList.add('show');
            this.closeNotificationDropdown();
        } else {
            dropdown.classList.remove('show');
        }
    }

    closeNotificationDropdown() {
        const dropdown = document.getElementById('notificationDropdown');
        if (dropdown) {
            dropdown.classList.remove('show');
            this.isNotificationMenuOpen = false;
        }
    }

    closeUserDropdown() {
        const dropdown = document.getElementById('userDropdown');
        if (dropdown) {
            dropdown.classList.remove('show');
            this.isUserMenuOpen = false;
        }
    }

    closeAllDropdowns() {
        this.closeNotificationDropdown();
        this.closeUserDropdown();
        
        // Close business dropdowns
        document.querySelectorAll('.business-dropdown-menu').forEach(dropdown => {
            dropdown.classList.remove('show');
            dropdown.closest('.business-main-nav-item').classList.remove('dropdown-open');
        });
    }

    initializeAdminSearch() {
        const searchInput = document.querySelector('.admin-search .search-input');
        if (searchInput) {
            searchInput.addEventListener('focus', () => {
                searchInput.closest('.search-box').classList.add('focused');
            });
            
            searchInput.addEventListener('blur', () => {
                searchInput.closest('.search-box').classList.remove('focused');
            });
        }
    }

    // Search functionality
    initializeSearch() {
        const searchForms = document.querySelectorAll('.header-search form, .navbar-search form, .business-search-form');
        
        searchForms.forEach(form => {
            const input = form.querySelector('input[type="search"]');
            const button = form.querySelector('button[type="submit"]');
            
            if (input) {
                // Clear button functionality
                input.addEventListener('input', () => {
                    this.toggleSearchClearButton(input);
                });
                
                // Enter key submission
                input.addEventListener('keydown', (e) => {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        this.submitSearch(form);
                    }
                });
            }
            
            if (button) {
                button.addEventListener('click', (e) => {
                    e.preventDefault();
                    this.submitSearch(form);
                });
            }
        });
    }

    toggleSearchClearButton(input) {
        const form = input.closest('form');
        let clearBtn = form.querySelector('.search-clear');
        
        if (input.value && !clearBtn) {
            clearBtn = document.createElement('button');
            clearBtn.type = 'button';
            clearBtn.className = 'search-clear';
            clearBtn.innerHTML = '<i class="fas fa-times"></i>';
            clearBtn.setAttribute('aria-label', 'Clear search');
            
            clearBtn.addEventListener('click', () => {
                input.value = '';
                input.focus();
                clearBtn.remove();
            });
            
            form.querySelector('.search-input').parentNode.appendChild(clearBtn);
        } else if (!input.value && clearBtn) {
            clearBtn.remove();
        }
    }

    submitSearch(form) {
        const input = form.querySelector('input[type="search"]');
        if (input && input.value.trim()) {
            form.submit();
        } else {
            input.focus();
        }
    }

    // Business header specific functionality
    initializeBusinessHeader() {
        this.handleBusinessHeaderScroll();
        this.initializeBusinessSearch();
    }

    handleBusinessHeaderScroll() {
        const header = document.querySelector('.business-main-header');
        if (!header) return;

        let lastScrollTop = 0;
        
        window.addEventListener('scroll', () => {
            const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
            
            if (scrollTop > this.scrollThreshold) {
                header.classList.add('scrolled');
                
                // Hide/show on scroll direction
                if (scrollTop > lastScrollTop && scrollTop > 200) {
                    header.classList.add('hidden');
                } else {
                    header.classList.remove('hidden');
                }
            } else {
                header.classList.remove('scrolled', 'hidden');
            }
            
            lastScrollTop = scrollTop;
        });
    }

    initializeBusinessSearch() {
        const searchInput = document.querySelector('.business-search-input');
        if (searchInput) {
            // Focus effect
            searchInput.addEventListener('focus', () => {
                searchInput.closest('.business-search-wrapper').classList.add('focused');
            });
            
            searchInput.addEventListener('blur', () => {
                searchInput.closest('.business-search-wrapper').classList.remove('focused');
            });
            
            // Quick search suggestions (optional)
            searchInput.addEventListener('input', this.debounce(() => {
                this.showSearchSuggestions(searchInput);
            }, 300));
        }
    }

    showSearchSuggestions(input) {
        const query = input.value.trim();
        if (query.length < 2) return;
        
        // In a real implementation, this would fetch from an API
        console.log('Searching for:', query);
        
        // Example: Show/hide suggestion dropdown
        const wrapper = input.closest('.business-search-wrapper');
        let suggestions = wrapper.querySelector('.search-suggestions');
        
        if (!suggestions) {
            suggestions = document.createElement('div');
            suggestions.className = 'search-suggestions';
            wrapper.appendChild(suggestions);
        }
        
        // Mock suggestions
        if (query) {
            suggestions.innerHTML = `
                <div class="suggestion-item">Search for "${query}" in businesses</div>
                <div class="suggestion-item">Search for "${query}" in reviews</div>
                <div class="suggestion-item">Search for "${query}" in categories</div>
            `;
            suggestions.classList.add('show');
        } else {
            suggestions.classList.remove('show');
        }
    }

    // Scroll effects
    handleScrollEffects() {
        const headers = document.querySelectorAll('header, .navbar');
        
        headers.forEach(header => {
            if (header.classList.contains('header-theme-transparent')) {
                this.handleTransparentHeader(header);
            }
        });
    }

    handleTransparentHeader(header) {
        window.addEventListener('scroll', () => {
            if (window.scrollY > 50) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }
        });
    }

    handleScroll() {
        // Generic scroll handling for all headers
        const headers = document.querySelectorAll('header, .navbar');
        
        headers.forEach(header => {
            if (window.scrollY > 10) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }
        });
    }

    // Utility methods
    handleResize() {
        const isMobile = window.innerWidth < 768;
        const isTablet = window.innerWidth >= 768 && window.innerWidth < 1024;
        const isDesktop = window.innerWidth >= 1024;

        // Close mobile menu on resize to desktop
        if (isDesktop && this.isMobileMenuOpen) {
            this.closeMobileMenu();
        }

        // Update header classes based on viewport
        document.querySelectorAll('header, .navbar, .business-main-header').forEach(header => {
            header.classList.toggle('mobile-view', isMobile);
            header.classList.toggle('tablet-view', isTablet);
            header.classList.toggle('desktop-view', isDesktop);
        });

        // Adjust search behavior based on viewport
        this.adjustSearchForViewport(isMobile);
    }

    adjustSearchForViewport(isMobile) {
        const searchBoxes = document.querySelectorAll('.business-search-box, .header-search, .navbar-search');
        
        searchBoxes.forEach(searchBox => {
            if (isMobile) {
                // On mobile, make search collapsible
                const searchInput = searchBox.querySelector('input[type="search"]');
                const searchWrapper = searchInput?.closest('.business-search-wrapper, .search-wrapper');
                
                if (searchWrapper && !searchWrapper.classList.contains('mobile-search')) {
                    searchWrapper.classList.add('mobile-search');
                    
                    // Add toggle button for mobile search
                    if (!searchWrapper.querySelector('.mobile-search-toggle')) {
                        const toggle = document.createElement('button');
                        toggle.className = 'mobile-search-toggle';
                        toggle.innerHTML = '<i class="fas fa-search"></i>';
                        toggle.setAttribute('aria-label', 'Toggle search');
                        toggle.addEventListener('click', (e) => {
                            e.stopPropagation();
                            searchWrapper.classList.toggle('expanded');
                            if (searchWrapper.classList.contains('expanded')) {
                                searchInput.focus();
                            }
                        });
                        searchWrapper.insertBefore(toggle, searchInput);
                    }
                }
            } else {
                // On desktop, ensure search is always visible
                const searchWrapper = searchBox.querySelector('.mobile-search');
                if (searchWrapper) {
                    searchWrapper.classList.remove('mobile-search', 'expanded');
                    const toggle = searchWrapper.querySelector('.mobile-search-toggle');
                    if (toggle) toggle.remove();
                }
            }
        });
    }

    // Touch/swipe gestures for mobile
    setupTouchGestures() {
        const headers = document.querySelectorAll('header, .navbar, .business-main-header');
        
        headers.forEach(header => {
            let touchStartX = 0;
            let touchEndX = 0;
            const nav = header.querySelector('.business-main-nav, .header-nav, .navbar-nav');

            header.addEventListener('touchstart', (e) => {
                touchStartX = e.changedTouches[0].screenX;
            }, { passive: true });

            header.addEventListener('touchend', (e) => {
                touchEndX = e.changedTouches[0].screenX;
                this.handleSwipe(touchStartX, touchEndX, header, nav);
            }, { passive: true });
        });
    }

    handleSwipe(startX, endX, header, nav) {
        const swipeThreshold = 50;
        const diff = startX - endX;

        // Swipe left to close menu
        if (diff > swipeThreshold && this.isMobileMenuOpen && nav) {
            this.closeMobileMenu();
        }
        // Swipe right to open menu (only if menu exists and is closed)
        else if (diff < -swipeThreshold && !this.isMobileMenuOpen && nav && window.innerWidth < 768) {
            const toggle = header.querySelector('.business-mobile-toggle, .header-mobile-toggle, .navbar-toggle');
            if (toggle) {
                this.toggleMobileMenu(toggle);
            }
        }
    }

    handleClickOutside(e) {
        // Close dropdowns when clicking outside
        if (!e.target.closest('.business-main-nav-item') && !e.target.closest('.admin-user') && !e.target.closest('.admin-notifications')) {
            this.closeAllDropdowns();
        }
        
        // Close mobile menu when clicking outside
        if (this.isMobileMenuOpen && !e.target.closest('header') && !e.target.closest('.navbar')) {
            this.closeMobileMenu();
        }
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

    // Public methods for external access
    openMobileMenu() {
        this.isMobileMenuOpen = true;
        const toggle = document.querySelector('.header-mobile-toggle, .navbar-toggle, .business-mobile-toggle');
        if (toggle) this.toggleMobileMenu(toggle);
    }

    closeAllMenus() {
        this.closeMobileMenu();
        this.closeAllDropdowns();
    }

    // Notification methods
    addNotification(notification) {
        const notificationList = document.querySelector('.notification-list');
        if (!notificationList) return;

        const notificationItem = document.createElement('div');
        notificationItem.className = 'notification-item';
        notificationItem.innerHTML = `
            <div class="notification-icon">
                <i class="fas ${notification.icon || 'fa-info-circle'}"></i>
            </div>
            <div class="notification-content">
                <p class="notification-text">${notification.text}</p>
                <span class="notification-time">${notification.time || 'Just now'}</span>
            </div>
        `;

        notificationList.insertBefore(notificationItem, notificationList.firstChild);
        
        // Update badge count
        this.updateNotificationBadge();
    }

    updateNotificationBadge() {
        const badge = document.querySelector('.notification-badge');
        const notificationCount = document.querySelectorAll('.notification-item').length;
        
        if (badge) {
            badge.textContent = notificationCount;
            if (notificationCount === 0) {
                badge.style.display = 'none';
            } else {
                badge.style.display = 'flex';
            }
        }
    }

    clearNotifications() {
        const notificationList = document.querySelector('.notification-list');
        if (notificationList) {
            notificationList.innerHTML = '';
            this.updateNotificationBadge();
        }
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.pimpHeaders = new PIMPHeaders();
});

// Export for module usage
if (typeof module !== 'undefined' && module.exports) {
    module.exports = PIMPHeaders;
}