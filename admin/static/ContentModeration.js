/**
 * P.I.M.P - Content Moderation
 * Admin content moderation functionality
 */

class ContentModeration {
    constructor(container, pimp) {
        this.container = container || document.querySelector('.content-moderation');
        this.pimp = pimp || window.PIMP;
        this.api = window.ApiService;
        this.queue = [];
        this.stats = null;
        this.init();
    }

    async init() {
        if (!this.container) {
            console.error('ContentModeration: Container not found');
            return;
        }

        this.setupEventHandlers();
        await this.loadModerationQueue();
        await this.getModerationStats();
    }

    setupEventHandlers() {
        // Filter buttons
        this.container.querySelectorAll('[data-filter-type]').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const type = btn.getAttribute('data-filter-type');
                this.filterByType(type);
            });
        });

        // Refresh button
        const refreshBtn = this.container.querySelector('[data-refresh-queue]');
        if (refreshBtn) {
            refreshBtn.addEventListener('click', () => this.loadModerationQueue());
        }
    }

    async loadModerationQueue() {
        try {
            this.showLoading();

            const response = await this.api.admin.getModerationQueue();

            if (response.success) {
                this.queue = response.data.queue || response.data || [];
                this.renderQueue();
            } else {
                this.showError(response.message || 'Failed to load moderation queue');
            }
        } catch (error) {
            console.error('Error loading moderation queue:', error);
            this.showError('Error loading moderation queue');
        } finally {
            this.hideLoading();
        }
    }

    renderQueue() {
        const queueContainer = this.container.querySelector('.moderation-queue, [data-queue]');
        if (!queueContainer) return;

        if (this.queue.length === 0) {
            queueContainer.innerHTML = '<div class="empty-queue">No items pending moderation</div>';
            return;
        }

        queueContainer.innerHTML = this.queue.map(item => `
            <div class="moderation-item" data-content-id="${item.id}" data-content-type="${item.content_type}">
                <div class="moderation-item-header">
                    <div class="content-type-badge type-${item.content_type}">
                        ${item.content_type}
                    </div>
                    <div class="content-date">${this.formatDate(item.created_at)}</div>
                </div>
                <div class="moderation-item-content">
                    ${this.renderContentPreview(item)}
                </div>
                <div class="moderation-item-actions">
                    <button class="btn btn-success" data-action="approve" data-id="${item.id}">
                        <i class="fas fa-check"></i> Approve
                    </button>
                    <button class="btn btn-danger" data-action="reject" data-id="${item.id}">
                        <i class="fas fa-times"></i> Reject
                    </button>
                    <button class="btn btn-warning" data-action="flag" data-id="${item.id}">
                        <i class="fas fa-flag"></i> Flag
                    </button>
                    <button class="btn btn-secondary" data-action="view" data-id="${item.id}">
                        <i class="fas fa-eye"></i> View Details
                    </button>
                </div>
            </div>
        `).join('');

        // Attach action handlers
        queueContainer.querySelectorAll('[data-action]').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const action = btn.getAttribute('data-action');
                const id = btn.getAttribute('data-id');
                this.handleAction(action, id);
            });
        });
    }

    renderContentPreview(item) {
        switch (item.content_type) {
            case 'review':
                return `
                    <div class="review-preview">
                        <div class="review-author">${item.author_name || 'Anonymous'}</div>
                        <div class="review-rating">
                            ${this.renderStars(item.rating || 0)}
                        </div>
                        <div class="review-text">${item.content || item.text || ''}</div>
                    </div>
                `;
            case 'business':
                return `
                    <div class="business-preview">
                        <div class="business-name">${item.business_name || item.name}</div>
                        <div class="business-description">${item.description || ''}</div>
                    </div>
                `;
            default:
                return `<div class="content-text">${item.content || item.text || ''}</div>`;
        }
    }

    renderStars(rating) {
        const fullStars = Math.floor(rating);
        const hasHalfStar = rating % 1 >= 0.5;
        let html = '';

        for (let i = 0; i < fullStars; i++) {
            html += '<i class="fas fa-star"></i>';
        }
        if (hasHalfStar) {
            html += '<i class="fas fa-star-half-alt"></i>';
        }
        const emptyStars = 5 - fullStars - (hasHalfStar ? 1 : 0);
        for (let i = 0; i < emptyStars; i++) {
            html += '<i class="far fa-star"></i>';
        }

        return html;
    }

    async handleAction(action, contentId) {
        const item = this.queue.find(i => i.id == contentId);
        if (!item) return;

        switch (action) {
            case 'approve':
                await this.approveContent(contentId, item.content_type);
                break;
            case 'reject':
                await this.rejectContent(contentId, item.content_type);
                break;
            case 'flag':
                await this.flagContent(contentId, item.content_type);
                break;
            case 'view':
                this.viewContentDetails(item);
                break;
        }
    }

    async approveContent(contentId, contentType) {
        try {
            const response = await this.api.admin.moderateContent(
                contentType,
                contentId,
                'approve'
            );

            if (response.success) {
                this.pimp?.showNotification('Content approved', 'success', 2000);
                await this.loadModerationQueue();
                await this.getModerationStats();
            } else {
                this.showError(response.message || 'Failed to approve content');
            }
        } catch (error) {
            console.error('Error approving content:', error);
            this.showError('Error approving content');
        }
    }

    async rejectContent(contentId, contentType) {
        const reason = prompt('Please provide a reason for rejection:');
        if (!reason) return;

        try {
            const response = await this.api.admin.moderateContent(
                contentType,
                contentId,
                'reject',
                reason
            );

            if (response.success) {
                this.pimp?.showNotification('Content rejected', 'success', 2000);
                await this.loadModerationQueue();
                await this.getModerationStats();
            } else {
                this.showError(response.message || 'Failed to reject content');
            }
        } catch (error) {
            console.error('Error rejecting content:', error);
            this.showError('Error rejecting content');
        }
    }

    async flagContent(contentId, contentType) {
        const reason = prompt('Please provide a reason for flagging:');
        if (!reason) return;

        try {
            const response = await this.api.admin.moderateContent(
                contentType,
                contentId,
                'flag',
                reason
            );

            if (response.success) {
                this.pimp?.showNotification('Content flagged', 'success', 2000);
                await this.loadModerationQueue();
            } else {
                this.showError(response.message || 'Failed to flag content');
            }
        } catch (error) {
            console.error('Error flagging content:', error);
            this.showError('Error flagging content');
        }
    }

    viewContentDetails(item) {
        // Show details in modal
        if (typeof window.ModalComponent !== 'undefined') {
            const modal = document.querySelector('#contentDetailsModal');
            if (modal && modal.modalComponent) {
                modal.modalComponent.setContent(this.renderContentDetails(item));
                modal.modalComponent.show();
            }
        }
    }

    renderContentDetails(item) {
        return `
            <div class="content-details">
                <h3>${item.content_type} Details</h3>
                <div class="detail-item">
                    <strong>ID:</strong> ${item.id}
                </div>
                <div class="detail-item">
                    <strong>Created:</strong> ${this.formatDate(item.created_at)}
                </div>
                <div class="detail-item">
                    <strong>Content:</strong>
                    <div class="content-text">${item.content || item.text || 'N/A'}</div>
                </div>
            </div>
        `;
    }

    filterByType(type) {
        const items = this.container.querySelectorAll('.moderation-item');
        items.forEach(item => {
            if (type === 'all' || item.getAttribute('data-content-type') === type) {
                item.style.display = '';
            } else {
                item.style.display = 'none';
            }
        });
    }

    async getModerationStats() {
        try {
            const response = await this.api.admin.getModerationQueue();
            // Calculate stats from queue
            const stats = {
                pending: this.queue.length,
                reviews: this.queue.filter(i => i.content_type === 'review').length,
                businesses: this.queue.filter(i => i.content_type === 'business').length
            };

            this.stats = stats;
            this.renderStats();
        } catch (error) {
            console.error('Error getting moderation stats:', error);
        }
    }

    renderStats() {
        if (!this.stats) return;

        const statsContainer = this.container.querySelector('.moderation-stats, [data-stats]');
        if (!statsContainer) return;

        statsContainer.innerHTML = `
            <div class="stat-item">
                <div class="stat-value">${this.stats.pending}</div>
                <div class="stat-label">Pending</div>
            </div>
            <div class="stat-item">
                <div class="stat-value">${this.stats.reviews}</div>
                <div class="stat-label">Reviews</div>
            </div>
            <div class="stat-item">
                <div class="stat-value">${this.stats.businesses}</div>
                <div class="stat-label">Businesses</div>
            </div>
        `;
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

    formatDate(date) {
        if (!date) return 'N/A';
        return new Date(date).toLocaleString();
    }
}

// Auto-initialize
document.addEventListener('DOMContentLoaded', () => {
    const container = document.querySelector('.content-moderation');
    if (container && !container.contentModeration) {
        container.contentModeration = new ContentModeration(container);
    }
});

// Export
if (typeof window !== 'undefined') {
    window.ContentModeration = ContentModeration;
}

if (typeof module !== 'undefined' && module.exports) {
    module.exports = ContentModeration;
}
