// star-rating.js
class StarRating {
    constructor(container) {
        this.container = $(container);
        this.stars = this.container.find('.star');
        this.rating = parseFloat(this.container.data('rating'));
        this.itemId = this.container.data('item-id');
        this.ajaxUrl = this.container.data('ajax-url');
        this.isInteractive = this.container.hasClass('star-rating-interactive');
        
        this.init();
    }
    
    init() {
        if (this.isInteractive) {
            this.bindEvents();
        }
    }
    
    bindEvents() {
        const self = this;
        
        // Hover effects
        this.stars.on('mouseenter', function() {
            if (!self.isInteractive) return;
            
            const value = $(this).data('value');
            self.highlightStars(value);
        });
        
        this.container.on('mouseleave', function() {
            if (!self.isInteractive) return;
            self.resetStars();
        });
        
        // Click to rate
        this.stars.on('click', function() {
            if (!self.isInteractive) return;
            
            const newRating = $(this).data('value');
            self.setRating(newRating);
            self.saveRating(newRating);
        });
    }
    
    highlightStars(upToValue) {
        this.stars.each(function() {
            const starValue = $(this).data('value');
            const star = $(this);
            
            if (starValue <= upToValue) {
                star.removeClass('star-empty star-half').addClass('star-filled');
                star.find('path').attr('fill', '#ffc107');
            } else {
                star.removeClass('star-filled star-half').addClass('star-empty');
                star.find('path').attr('fill', '#e0e0e0');
            }
        });
        
        // Update feedback text
        this.updateFeedback(upToValue);
    }
    
    resetStars() {
        this.highlightStars(this.rating);
    }
    
    setRating(newRating) {
        this.rating = newRating;
        this.container.data('rating', newRating);
        this.updateDisplay();
    }
    
    updateDisplay() {
        this.highlightStars(this.rating);
    }
    
    updateFeedback(rating) {
        const feedback = this.container.find('.star-rating-feedback');
        if (feedback.length) {
            feedback.find('.current-rating').text(rating.toFixed(1));
            feedback.find('.rating-text').text('Click to confirm');
        }
    }
    
    saveRating(rating) {
        if (!this.ajaxUrl || !this.itemId) return;
        
        const self = this;
        
        $.ajax({
            url: this.ajaxUrl,
            type: 'POST',
            data: {
                item_id: this.itemId,
                rating: rating,
                _token: $('meta[name="csrf-token"]').attr('content') // For Laravel, adjust as needed
            },
            beforeSend: function() {
                self.container.addClass('loading');
            },
            success: function(response) {
                self.container.removeClass('loading');
                
                if (response.success) {
                    self.showSuccess('Rating saved successfully!');
                    // Update any other components if needed
                    if (response.average_rating) {
                        self.rating = response.average_rating;
                        self.container.data('rating', response.average_rating);
                    }
                } else {
                    self.showError(response.message || 'Failed to save rating');
                    self.resetStars();
                }
            },
            error: function(xhr, status, error) {
                self.container.removeClass('loading');
                self.showError('Error saving rating: ' + error);
                self.resetStars();
            }
        });
    }
    
    showSuccess(message) {
        this.showMessage(message, 'success');
    }
    
    showError(message) {
        this.showMessage(message, 'error');
    }
    
    showMessage(message, type) {
        // Remove existing messages
        this.container.find('.rating-message').remove();
        
        const messageEl = $('<div class="rating-message rating-message-' + type + '"></div>')
            .text(message)
            .hide()
            .appendTo(this.container);
        
        messageEl.fadeIn(300);
        
        setTimeout(function() {
            messageEl.fadeOut(300, function() {
                $(this).remove();
            });
        }, 3000);
    }
}


// Initialize all star ratings on page load
$(document).ready(function() {
    $('.star-rating').each(function() {
        new StarRating(this);
    });
});

// Global function to update star rating (can be called from other scripts)
function updateStarRating(containerId, newRating) {
    const container = $('#' + containerId);
    if (container.length) {
        const starRating = new StarRating(container[0]);
        starRating.setRating(newRating);
    }
}

// Function to create new star rating dynamically
function createStarRating(container, options) {
    const defaults = {
        rating: 0,
        totalStars: 5,
        interactive: false,
        size: 'md',
        itemId: 0,
        ajaxUrl: '/ajax/rate'
    };
    
    const config = {...defaults, ...options};
    
    // Generate HTML for star rating
    let starsHtml = '';
    for (let i = 1; i <= config.totalStars; i++) {
        const state = i <= config.rating ? 'filled' : 'empty';
        starsHtml += `
            <div class="star star-${state}" data-value="${i}">
                ${getStarSVG(state)}
            </div>
        `;
    }
    
    const html = `
        <div class="star-rating star-rating-${config.size} ${config.interactive ? 'star-rating-interactive' : ''}" 
             data-rating="${config.rating}" 
             data-item-id="${config.itemId}"
             data-ajax-url="${config.ajaxUrl}">
            <div class="star-rating-container">
                ${starsHtml}
            </div>
            <div class="star-rating-${config.interactive ? 'feedback' : 'text'}">
                <span class="${config.interactive ? 'current-rating' : 'rating-value'}">${config.rating.toFixed(1)}</span>
                <span class="${config.interactive ? 'rating-text' : 'rating-count'}">${config.interactive ? 'Click to rate' : `(${config.totalStars} stars)`}</span>
            </div>
        </div>
    `;
    
    container.html(html);
    new StarRating(container[0]);
}

// Helper function to generate SVG (you might want to preload these)
function getStarSVG(state) {
    const fillColor = state === 'filled' ? '#ffc107' : 
                     state === 'half' ? 'url(#half-gradient)' : '#e0e0e0';
    
    return `
    <svg class="star-svg" width="24" height="24" viewBox="0 0 29.018 29.018">
        <defs>
            <linearGradient id="half-gradient">
                <stop offset="50%" stop-color="#ffc107"/>
                <stop offset="50%" stop-color="#e0e0e0"/>
            </linearGradient>
        </defs>
        <path d="M13.645,4.01l-2.057,6.334a1.013,1.013,0,0,1-.962.7H3.967a2.475,2.475,0,0,0-1.456,4.478L7.9,19.435a1.011,1.011,0,0,1,.367,1.131L6.208,26.9a2.476,2.476,0,0,0,3.81,2.768l5.388-3.914a1.012,1.012,0,0,1,1.188,0l5.388,3.914a2.476,2.476,0,0,0,3.81-2.768l-2.058-6.333a1.011,1.011,0,0,1,.367-1.131l5.388-3.914a2.475,2.475,0,0,0-1.456-4.478H21.374a1.013,1.013,0,0,1-.962-.7L18.355,4.01a2.477,2.477,0,0,0-4.71,0Zm1.9.618a.475.475,0,0,1,.9,0l2.058,6.334a3.012,3.012,0,0,0,2.864,2.081h6.659a.475.475,0,0,1,.28.86l-5.387,3.914a3.011,3.011,0,0,0-1.094,3.367l2.058,6.333a.476.476,0,0,1-.733.532L17.77,24.135a3.011,3.011,0,0,0-3.54,0L8.843,28.049a.476.476,0,0,1-.733-.532l2.058-6.333a3.011,3.011,0,0,0-1.094-3.367L3.687,13.9a.475.475,0,0,1,.28-.86h6.659a3.012,3.012,0,0,0,2.864-2.081l2.058-6.334Z" 
              fill="${fillColor}" 
              fill-rule="evenodd"
              transform="translate(-1.491 -2.3)"/>
    </svg>`;
}
