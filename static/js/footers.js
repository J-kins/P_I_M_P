/**
 * P.I.M.P - Footers JavaScript
 * Handles footer interactions, newsletter forms, and dynamic content
 */

class PIMPFooters {
    constructor() {
        this.newsletterForms = new Map();
        this.initialize();
    }

    initialize() {
        this.bindEventListeners();
        this.initializeNewsletterForms();
        this.initializeSocialLinks();
        this.initializeAccordions();
        this.initializeBackToTop();
        this.initializeAppDownloadLinks();
        this.handleFooterReveal();
    }

    // Main event listeners
    bindEventListeners() {
        // Lazy load images in footer
        this.lazyLoadFooterImages();
        
        // Intersection Observer for footer animations
        this.observeFooterSections();
    }

    // Newsletter form functionality
    initializeNewsletterForms() {
        const newsletterForms = document.querySelectorAll('.newsletter-form');
        
        newsletterForms.forEach((form, index) => {
            const formId = `newsletter-${index}`;
            this.newsletterForms.set(formId, {
                element: form,
                submitted: false
            });
            
            this.setupNewsletterForm(form, formId);
        });
    }

    setupNewsletterForm(form, formId) {
        const emailInput = form.querySelector('input[type="email"]');
        const submitButton = form.querySelector('button[type="submit"]');
        const originalButtonText = submitButton?.textContent || 'Subscribe';
        
        if (emailInput) {
            // Real-time validation
            emailInput.addEventListener('input', () => {
                this.validateEmailInput(emailInput);
            });
            
            // Focus effects
            emailInput.addEventListener('focus', () => {
                form.classList.add('focused');
            });
            
            emailInput.addEventListener('blur', () => {
                form.classList.remove('focused');
            });
        }
        
        if (form) {
            form.addEventListener('submit', (e) => {
                e.preventDefault();
                this.handleNewsletterSubmission(form, formId, originalButtonText);
            });
        }
    }

    validateEmailInput(input) {
        const value = input.value.trim();
        const form = input.closest('form');
        let errorElement = form.querySelector('.newsletter-error');
        
        // Remove existing error
        if (errorElement) {
            errorElement.remove();
        }
        
        // Basic email validation
        if (value && !this.isValidEmail(value)) {
            input.classList.add('error');
            
            errorElement = document.createElement('div');
            errorElement.className = 'newsletter-error';
            errorElement.textContent = 'Please enter a valid email address';
            errorElement.style.cssText = `
                color: #dc2626;
                font-size: 0.8rem;
                margin-top: 0.5rem;
            `;
            
            form.appendChild(errorElement);
            return false;
        } else {
            input.classList.remove('error');
            return true;
        }
    }

    async handleNewsletterSubmission(form, formId, originalButtonText) {
        const emailInput = form.querySelector('input[type="email"]');
        const submitButton = form.querySelector('button[type="submit"]');
        const email = emailInput.value.trim();
        
        // Validate email
        if (!this.validateEmailInput(emailInput)) {
            return;
        }
        
        if (!email) {
            this.showNewsletterMessage(form, 'Please enter your email address', 'error');
            return;
        }
        
        // Show loading state
        if (submitButton) {
            submitButton.disabled = true;
            submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Subscribing...';
        }
        
        try {
            // Simulate API call
            await this.subscribeToNewsletter(email);
            
            // Success
            this.showNewsletterMessage(form, 'Thank you for subscribing!', 'success');
            form.reset();
            
            // Update form state
            const formData = this.newsletterForms.get(formId);
            if (formData) {
                formData.submitted = true;
            }
            
        } catch (error) {
            console.error('Newsletter subscription error:', error);
            this.showNewsletterMessage(form, 'Subscription failed. Please try again.', 'error');
        } finally {
            // Reset button
            if (submitButton) {
                submitButton.disabled = false;
                submitButton.textContent = originalButtonText;
            }
        }
    }

    async subscribeToNewsletter(email) {
        // Simulate API call
        return new Promise((resolve, reject) => {
            setTimeout(() => {
                // Simulate random failures (5% chance)
                if (Math.random() < 0.05) {
                    reject(new Error('Network error'));
                } else {
                    resolve({ success: true, email: email });
                }
            }, 1500);
        });
    }

    showNewsletterMessage(form, message, type = 'info') {
        // Remove existing messages
        const existingMessage = form.querySelector('.newsletter-message');
        if (existingMessage) {
            existingMessage.remove();
        }
        
        // Create new message
        const messageElement = document.createElement('div');
        messageElement.className = `newsletter-message newsletter-${type}`;
        messageElement.innerHTML = `
            <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'}"></i>
            <span>${message}</span>
        `;
        
        // Add styles
        messageElement.style.cssText = `
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem;
            margin-top: 1rem;
            border-radius: 6px;
            font-size: 0.9rem;
            background: ${type === 'success' ? '#f0fdf4' : type === 'error' ? '#fef2f2' : '#eff6ff'};
            color: ${type === 'success' ? '#065f46' : type === 'error' ? '#dc2626' : '#1e40af'};
            border: 1px solid ${type === 'success' ? '#a7f3d0' : type === 'error' ? '#fecaca' : '#dbeafe'};
        `;
        
        form.appendChild(messageElement);
        
        // Auto-remove after 5 seconds
        setTimeout(() => {
            messageElement.remove();
        }, 5000);
    }

    // Social links functionality
    initializeSocialLinks() {
        const socialLinks = document.querySelectorAll('.social-link');
        
        socialLinks.forEach(link => {
            // Add click tracking (analytics would go here)
            link.addEventListener('click', (e) => {
                this.trackSocialClick(link);
            });
            
            // Add tooltip on hover
            this.addSocialLinkTooltip(link);
        });
    }

    trackSocialClick(link) {
        const platform = link.classList.contains('social-') ? 
            Array.from(link.classList).find(cls => cls.startsWith('social-'))?.replace('social-', '') : 
            'unknown';
        
        console.log(`Social link clicked: ${platform}`, link.href);
        
        // In a real implementation, this would send to analytics
        // gtag('event', 'social_click', { platform: platform, url: link.href });
    }

    addSocialLinkTooltip(link) {
        let tooltip = link.querySelector('.social-tooltip');
        
        if (!tooltip) {
            const platform = link.getAttribute('aria-label') || 
                           link.classList.contains('social-') ? 
                           Array.from(link.classList).find(cls => cls.startsWith('social-'))?.replace('social-', '') : 
                           'Social Media';
            
            tooltip = document.createElement('span');
            tooltip.className = 'social-tooltip';
            tooltip.textContent = platform.charAt(0).toUpperCase() + platform.slice(1);
            tooltip.style.cssText = `
                position: absolute;
                bottom: 100%;
                left: 50%;
                transform: translateX(-50%);
                background: #1f2937;
                color: white;
                padding: 0.25rem 0.5rem;
                border-radius: 4px;
                font-size: 0.75rem;
                white-space: nowrap;
                opacity: 0;
                visibility: hidden;
                transition: all 0.2s ease;
                z-index: 1000;
            `;
            
            link.style.position = 'relative';
            link.appendChild(tooltip);
            
            // Show/hide tooltip
            link.addEventListener('mouseenter', () => {
                tooltip.style.opacity = '1';
                tooltip.style.visibility = 'visible';
            });
            
            link.addEventListener('mouseleave', () => {
                tooltip.style.opacity = '0';
                tooltip.style.visibility = 'hidden';
            });
        }
    }

    // Accordion functionality for mobile footer
    initializeAccordions() {
        const footerHeadings = document.querySelectorAll('.footer-column h3, .footer-title, .footer-heading');
        
        footerHeadings.forEach(heading => {
            // Only make accordion on mobile
            if (window.innerWidth < 768) {
                this.setupFooterAccordion(heading);
            }
        });
        
        // Re-initialize on resize
        window.addEventListener('resize', () => {
            footerHeadings.forEach(heading => {
                const isAccordion = heading.hasAttribute('data-accordion');
                
                if (window.innerWidth < 768 && !isAccordion) {
                    this.setupFooterAccordion(heading);
                } else if (window.innerWidth >= 768 && isAccordion) {
                    this.removeFooterAccordion(heading);
                }
            });
        });
    }

    setupFooterAccordion(heading) {
        const column = heading.closest('.footer-column');
        const links = column?.querySelector('.footer-links, .footer-list');
        
        if (!column || !links) return;
        
        heading.setAttribute('data-accordion', 'true');
        heading.style.cursor = 'pointer';
        heading.innerHTML = `
            <span>${heading.textContent}</span>
            <i class="fas fa-chevron-down accordion-arrow"></i>
        `;
        
        // Initially closed on mobile
        links.style.display = 'none';
        heading.classList.remove('active');
        
        heading.addEventListener('click', () => {
            const isActive = heading.classList.contains('active');
            
            // Close other accordions
            document.querySelectorAll('.footer-column h3[data-accordion].active').forEach(otherHeading => {
                if (otherHeading !== heading) {
                    otherHeading.classList.remove('active');
                    const otherLinks = otherHeading.closest('.footer-column').querySelector('.footer-links, .footer-list');
                    if (otherLinks) otherLinks.style.display = 'none';
                }
            });
            
            // Toggle current
            if (isActive) {
                heading.classList.remove('active');
                links.style.display = 'none';
            } else {
                heading.classList.add('active');
                links.style.display = 'block';
            }
        });
    }

    removeFooterAccordion(heading) {
        const column = heading.closest('.footer-column');
        const links = column?.querySelector('.footer-links, .footer-list');
        
        if (links) {
            links.style.display = '';
        }
        
        heading.removeAttribute('data-accordion');
        heading.style.cursor = '';
        heading.innerHTML = heading.textContent;
        heading.classList.remove('active');
    }

    // Back to top functionality
    initializeBackToTop() {
        const backToTopButton = document.createElement('button');
        backToTopButton.className = 'back-to-top';
        backToTopButton.innerHTML = '<i class="fas fa-chevron-up"></i>';
        backToTopButton.setAttribute('aria-label', 'Back to top');
        backToTopButton.style.cssText = `
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            width: 50px;
            height: 50px;
            background: #8a5cf5;
            color: white;
            border: none;
            border-radius: 50%;
            cursor: pointer;
            display: none;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            box-shadow: 0 4px 12px rgba(138, 92, 245, 0.3);
            transition: all 0.3s ease;
            z-index: 1000;
        `;
        
        backToTopButton.addEventListener('click', () => {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
        
        backToTopButton.addEventListener('mouseenter', () => {
            backToTopButton.style.transform = 'translateY(-2px)';
            backToTopButton.style.boxShadow = '0 6px 16px rgba(138, 92, 245, 0.4)';
        });
        
        backToTopButton.addEventListener('mouseleave', () => {
            backToTopButton.style.transform = 'translateY(0)';
            backToTopButton.style.boxShadow = '0 4px 12px rgba(138, 92, 245, 0.3)';
        });
        
        document.body.appendChild(backToTopButton);
        
        // Show/hide based on scroll
        window.addEventListener('scroll', () => {
            if (window.pageYOffset > 300) {
                backToTopButton.style.display = 'flex';
            } else {
                backToTopButton.style.display = 'none';
            }
        });
    }

    // App download links
    initializeAppDownloadLinks() {
        const downloadButtons = document.querySelectorAll('.download-button');
        
        downloadButtons.forEach(button => {
            button.addEventListener('click', (e) => {
                const store = button.classList.contains('download-ios') ? 'app_store' : 
                            button.classList.contains('download-android') ? 'google_play' : 'unknown';
                
                this.trackAppDownload(store, button.href);
            });
        });
    }

    trackAppDownload(store, url) {
        console.log(`App download initiated: ${store}`, url);
        
        // In a real implementation, this would send to analytics
        // gtag('event', 'app_download', { store: store, url: url });
    }

    // Footer reveal animation
    handleFooterReveal() {
        const footer = document.querySelector('footer');
        if (!footer) return;
        
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    footer.classList.add('revealed');
                }
            });
        }, {
            threshold: 0.1
        });
        
        observer.observe(footer);
    }

    // Lazy loading for footer images
    lazyLoadFooterImages() {
        const footerImages = document.querySelectorAll('footer img[data-src]');
        
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.getAttribute('data-src');
                    img.removeAttribute('data-src');
                    img.classList.add('loaded');
                    imageObserver.unobserve(img);
                }
            });
        });
        
        footerImages.forEach(img => imageObserver.observe(img));
    }

    // Observe footer sections for animations
    observeFooterSections() {
        const footerSections = document.querySelectorAll('.footer-column, .footer-brand, .footer-newsletter');
        
        const sectionObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate-in');
                }
            });
        }, {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        });
        
        footerSections.forEach(section => sectionObserver.observe(section));
    }

    // Utility methods
    isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }

    // Public methods
    subscribeEmail(email) {
        // Programmatic subscription
        return this.subscribeToNewsletter(email);
    }

    refreshFooter() {
        // Refresh footer content if needed
        this.initializeNewsletterForms();
        this.initializeSocialLinks();
    }

    // Admin footer specific methods
    initializeAdminFooter() {
        const adminFooter = document.querySelector('.footer-admin');
        if (adminFooter) {
            // Add timestamp
            const timestamp = document.createElement('div');
            timestamp.className = 'footer-timestamp';
            timestamp.textContent = `Last updated: ${new Date().toLocaleString()}`;
            timestamp.style.cssText = `
                font-size: 0.8rem;
                color: #6b7280;
                margin-top: 0.5rem;
            `;
            
            adminFooter.querySelector('.footer-left').appendChild(timestamp);
        }
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.pimpFooters = new PIMPFooters();
});

// Export for module usage
if (typeof module !== 'undefined' && module.exports) {
    module.exports = PIMPFooters;
}