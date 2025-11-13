/**
 * P.I.M.P - Homepage JavaScript
 * Handles dynamic data loading and interactions for the homepage
 */

class HomepageHandler {
    constructor() {
        this.api = window.ApiService;
        this.pimp = window.PIMP;
        this.categories = [];
        this.featuredBusinesses = [];
        this.statistics = {};
        this.init();
    }

    async init() {
        // Load data from window.homepageData if available (server-side rendered)
        if (window.homepageData) {
            this.categories = window.homepageData.categories || [];
            this.featuredBusinesses = window.homepageData.featuredBusinesses || [];
            this.statistics = window.homepageData.statistics || {};
        }

        // If data is empty or incomplete, fetch from API
        if (this.categories.length === 0 || this.featuredBusinesses.length === 0) {
            await this.loadDataFromAPI();
        }

        this.setupEventHandlers();
        this.setupBusinessCardInteractions();
        this.setupCategoryInteractions();
        this.animateStatistics();
        this.setupLazyLoading();
    }

    async loadDataFromAPI() {
        try {
            // Load categories
            if (this.categories.length === 0) {
                await this.loadCategories();
            }

            // Load featured businesses
            if (this.featuredBusinesses.length === 0) {
                await this.loadFeaturedBusinesses();
            }

            // Load statistics
            if (!this.statistics.total_businesses) {
                await this.loadStatistics();
            }
        } catch (error) {
            console.error('Error loading homepage data:', error);
            this.showError('Failed to load some content. Please refresh the page.');
        }
    }

    async loadCategories() {
        try {
            const response = await this.api.category.getAll({ featured: true });
            
            if (response.success && response.data) {
                this.categories = response.data.slice(0, 6);
                // Update category counts
                this.updateCategoryCounts();
            }
        } catch (error) {
            console.error('Error loading categories:', error);
        }
    }

    async loadFeaturedBusinesses() {
        try {
            const response = await this.api.business.getFeatured(3);

            if (response.success && response.data && response.data.businesses) {
                this.featuredBusinesses = response.data.businesses;
                this.renderFeaturedBusinesses();
            }
        } catch (error) {
            console.error('Error loading featured businesses:', error);
        }
    }

    async loadStatistics() {
        try {
            // Get statistics from multiple API calls
            const [businessesRes, reviewsRes] = await Promise.all([
                this.api.business.search({ status: 'active' }, 1, 1),
                this.api.review.getStatistics ? this.api.review.getStatistics() : Promise.resolve({ success: false })
            ]);

            if (businessesRes.success && businessesRes.data && businessesRes.data.pagination) {
                this.statistics.total_businesses = businessesRes.data.pagination.total || 0;
            }

            // Update statistics display
            this.updateStatisticsDisplay();
        } catch (error) {
            console.error('Error loading statistics:', error);
        }
    }

    renderFeaturedBusinesses() {
        const grid = document.getElementById('featuredBusinessesGrid');
        if (!grid || this.featuredBusinesses.length === 0) return;

        // Remove loading indicator
        const loading = grid.querySelector('.loading-businesses');
        if (loading) loading.remove();

        // Render businesses
        grid.innerHTML = this.featuredBusinesses.map(business => this.renderBusinessCard(business)).join('');

        // Reattach event handlers
        this.setupBusinessCardInteractions();
    }

    renderBusinessCard(business) {
        const rating = parseFloat(business.rating || 0);
        const fullStars = Math.floor(rating);
        const hasHalfStar = rating % 1 >= 0.5;
        const emptyStars = 5 - fullStars - (hasHalfStar ? 1 : 0);

        let starsHTML = '';
        for (let i = 0; i < fullStars; i++) {
            starsHTML += '<i class="fas fa-star"></i>';
        }
        if (hasHalfStar) {
            starsHTML += '<i class="fas fa-star-half-alt"></i>';
        }
        for (let i = 0; i < emptyStars; i++) {
            starsHTML += '<i class="far fa-star"></i>';
        }

        const businessId = business.business_id || business.id || '';
        const imageUrl = business.logo_url || business.image || '/static/img/businesses/default.jpg';

        return `
            <div class="business-card" data-business-id="${businessId}">
                <div class="business-header">
                    <div class="business-image">
                        <img src="${imageUrl}" 
                             alt="${this.escapeHtml(business.business_name || business.name || 'Business')}"
                             onerror="this.src='/static/img/businesses/default.jpg'">
                    </div>
                    <div class="business-info">
                        <h3>${this.escapeHtml(business.business_name || business.name || 'Unnamed Business')}</h3>
                        <div class="business-rating">
                            <div class="stars" data-rating="${rating}">
                                ${starsHTML}
                            </div>
                            <span class="rating-value">${rating.toFixed(1)}</span>
                            <span class="reviews-count">(${business.total_reviews || 0} reviews)</span>
                        </div>
                        ${business.accreditation_level && business.accreditation_level !== 'none' ? `
                            <div class="accredited-badge">
                                <i class="fas fa-check-circle"></i>
                                PIMP Verified
                            </div>
                        ` : ''}
                    </div>
                </div>
                <div class="business-details">
                    ${business.categories && business.categories.length > 0 ? `
                        <div class="business-categories">
                            ${business.categories.map(cat => `
                                <span class="category-tag">${this.escapeHtml(cat)}</span>
                            `).join('')}
                        </div>
                    ` : ''}
                    <div class="business-contact">
                        ${business.address ? `
                            <div class="contact-item">
                                <i class="fas fa-map-marker-alt"></i>
                                <span>${this.escapeHtml(business.address)}</span>
                            </div>
                        ` : ''}
                        ${business.phone ? `
                            <div class="contact-item">
                                <i class="fas fa-phone"></i>
                                <span>${this.escapeHtml(business.phone)}</span>
                            </div>
                        ` : ''}
                        ${business.website ? `
                            <div class="contact-item">
                                <i class="fas fa-globe"></i>
                                <a href="${this.escapeHtml(business.website)}" target="_blank" rel="noopener noreferrer">Visit Website</a>
                            </div>
                        ` : ''}
                    </div>
                </div>
                <div class="business-actions">
                    <a href="/business/${businessId}" 
                       class="button button-primary view-profile-btn"
                       data-business-id="${businessId}">
                        View Profile
                    </a>
                    <a href="/reviews/write?business=${encodeURIComponent(businessId)}" 
                       class="button button-outline">
                        Write Review
                    </a>
                </div>
            </div>
        `;
    }

    updateCategoryCounts() {
        document.querySelectorAll('.category-card').forEach(card => {
            const categoryId = card.getAttribute('data-category-id');
            if (!categoryId) return;

            // Fetch business count for this category
            this.api.business.search({ category_id: categoryId, status: 'active' }, 1, 1)
                .then(response => {
                    if (response.success && response.data && response.data.pagination) {
                        const count = response.data.pagination.total || 0;
                        const countElement = card.querySelector('.count-value');
                        if (countElement) {
                            countElement.textContent = this.formatNumber(count);
                        }
                    }
                })
                .catch(error => {
                    console.error('Error updating category count:', error);
                });
        });
    }

    updateStatisticsDisplay() {
        const stats = {
            businesses: this.statistics.total_businesses || 0,
            reviews: this.statistics.total_reviews || 0,
            cities: this.statistics.total_cities || 0,
            satisfaction: this.statistics.satisfaction_rate || 99
        };

        Object.entries(stats).forEach(([key, value]) => {
            const element = document.querySelector(`[data-stat="${key}"]`);
            if (element) {
                if (key === 'satisfaction') {
                    element.textContent = `${value}%`;
                } else {
                    element.textContent = `${this.formatNumber(value)}+`;
                }
            }
        });
    }

    animateStatistics() {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    this.animateNumber(entry.target);
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.5 });

        document.querySelectorAll('[data-stat]').forEach(stat => {
            observer.observe(stat);
        });
    }

    animateNumber(element) {
        const text = element.textContent;
        const number = parseInt(text.replace(/[^0-9]/g, ''));
        if (isNaN(number)) return;

        const duration = 2000;
        const steps = 60;
        const increment = number / steps;
        let current = 0;
        const isPercentage = text.includes('%');
        const suffix = text.replace(/[0-9%]/g, '');

        const timer = setInterval(() => {
            current += increment;
            if (current >= number) {
                element.textContent = isPercentage 
                    ? `${number}%${suffix}` 
                    : `${this.formatNumber(number)}${suffix}`;
                clearInterval(timer);
            } else {
                element.textContent = isPercentage
                    ? `${Math.floor(current)}%${suffix}`
                    : `${this.formatNumber(Math.floor(current))}${suffix}`;
            }
        }, duration / steps);
    }

    setupEventHandlers() {
        // Smooth scrolling for navigation
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Refresh data button (if exists)
        const refreshBtn = document.querySelector('[data-refresh-homepage]');
        if (refreshBtn) {
            refreshBtn.addEventListener('click', () => {
                this.loadDataFromAPI();
            });
        }
    }

    setupBusinessCardInteractions() {
        // Business card click interactions
        document.querySelectorAll('.business-card').forEach(card => {
            card.addEventListener('click', (e) => {
                if (!e.target.closest('a, button')) {
                    const profileLink = card.querySelector('.view-profile-btn');
                    if (profileLink) {
                        window.location.href = profileLink.href;
                    }
                }
            });

            // Hover effects
            card.addEventListener('mouseenter', () => {
                card.classList.add('hovered');
            });

            card.addEventListener('mouseleave', () => {
                card.classList.remove('hovered');
            });
        });
    }

    setupCategoryInteractions() {
        document.querySelectorAll('.category-card').forEach(card => {
            card.addEventListener('click', (e) => {
                if (!e.target.closest('a')) {
                    const link = card.querySelector('a.button');
                    if (link) {
                        window.location.href = link.href;
                    }
                }
            });
        });
    }

    setupLazyLoading() {
        // Lazy load images
        const images = document.querySelectorAll('img[data-src]');
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.getAttribute('data-src');
                    img.removeAttribute('data-src');
                    img.classList.add('loaded');
                    observer.unobserve(img);
                }
            });
        });

        images.forEach(img => imageObserver.observe(img));
    }

    formatNumber(num) {
        return new Intl.NumberFormat('en-US').format(num);
    }

    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    showError(message) {
        if (this.pimp && this.pimp.showNotification) {
            this.pimp.showNotification(message, 'error', 5000);
        } else {
            console.error(message);
        }
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    window.homepageHandler = new HomepageHandler();
});

// Export for module usage
if (typeof module !== 'undefined' && module.exports) {
    module.exports = HomepageHandler;
}

